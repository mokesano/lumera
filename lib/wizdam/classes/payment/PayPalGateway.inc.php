<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/payment/PayPalGateway.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class PayPalGateway
 * 
 * @brief Adapter PayPal Standard Checkout untuk dunia Invoice/BillingHandler.
 * Plugin lama plugins/paymethod/paypal/PayPalPlugin.inc.php TIDAK disentuh
 * -- tetap melayani alur queued_payments lama (SubmissionEditHandler dst).
 * Class ini murni jembatan baru, mengimplementasikan PaymentGatewayInterface
 * yang sama seperti MidtransGateway/XenditGateway, supaya konvergen ke
 * WebhookHandler yang sudah ada tanpa perubahan struktural.
 */

import('lib.wizdam.classes.payment.PaymentGatewayInterface');
import('lib.wizdam.classes.invoice.Invoice');
import('lib.wizdam.classes.invoice.InvoiceDAO');

class PayPalGateway implements PaymentGatewayInterface {

    private string $sellerEmail;
    private bool $isProduction;

    /**
     * Constructor
     * @param string $sellerEmail Email akun PayPal penerima dana
     * @param bool $isProduction false = pakai endpoint verifikasi Sandbox
     */
    public function __construct(string $sellerEmail, bool $isProduction = false) {
        $this->sellerEmail = $sellerEmail;
        $this->isProduction = $isProduction;
    }

    /**
     * Membangun URL redirect PayPal Standard Checkout untuk invoice tertentu.
     * @param Invoice $invoice
     * @param array $customerData
     * @param string $paymentType Diabaikan -- PayPal Standard Checkout tidak
     * punya konsep sub-filter metode seperti QRIS/VA milik Midtrans/Xendit.
     * @return array
     */
    public function getPaymentCheckoutData(Invoice $invoice, array $customerData = [], string $paymentType = 'all'): array {
        if ($this->sellerEmail === '') {
            throw new \RuntimeException('PayPal seller email belum dikonfigurasi.');
        }

        $request = Application::get()->getRequest();
        $orderId = 'WIZDAM-PP-' . $invoice->getInvoiceId() . '-' . time();

        $returnUrl = $request->url(null, 'billing', 'invoice', [$this->_dummySecurePath($invoice)]);
        $notifyUrl = $request->url(null, 'billing', 'webhook', ['paypal']);

        $params = [
            'cmd'            => '_xclick',
            'business'       => $this->sellerEmail,
            'item_name'      => 'Wizdam: ' . $invoice->getFeeType(),
            'item_number'    => (string) $invoice->getInvoiceId(),
            'amount'         => number_format($invoice->getAmount(), 2, '.', ''),
            'currency_code'  => $invoice->getCurrencyCode(),
            'custom'         => $orderId,
            'notify_url'     => $notifyUrl,
            'return'         => $returnUrl,
            'cancel_return'  => $returnUrl,
            'no_shipping'    => '1',
            'rm'             => '2', // POST kembali data ke return URL
        ];

        if (!empty($customerData['email'])) {
            $params['payer_email'] = (string) $customerData['email'];
        }
        if (!empty($customerData['first_name'])) {
            $params['first_name'] = (string) $customerData['first_name'];
        }
        if (!empty($customerData['last_name'])) {
            $params['last_name'] = (string) $customerData['last_name'];
        }

        $baseUrl = $this->isProduction
            ? 'https://www.paypal.com/cgi-bin/webscr'
            : 'https://www.sandbox.paypal.com/cgi-bin/webscr';

        return [
            'gateway' => 'paypal',
            'token'   => '',
            'url'     => $baseUrl . '?' . http_build_query($params),
        ];
    }

    /**
     * Memproses IPN dari PayPal. Payload di sini adalah $_POST mentah
     * (form-urlencoded, BUKAN JSON -- lihat catatan di WebhookHandler).
     * @param array $payload
     * @return array|null
     */
    public function processWebhook(array $payload): ?array {
        if (!$this->verifyIpn($payload)) {
            return null;
        }

        $receiverEmail = (string) ($payload['business'] ?? $payload['receiver_email'] ?? '');
        if ($receiverEmail === '' || !hash_equals(strtolower($this->sellerEmail), strtolower($receiverEmail))) {
            $this->_alertSiteAdmins('PayPal', "IPN receiver tidak cocok. Diharapkan {$this->sellerEmail}, diterima {$receiverEmail}.");
            return null;
        }

        $custom = (string) ($payload['custom'] ?? '');
        $parts = explode('-', $custom);
        if (count($parts) < 3 || $parts[0] !== 'WIZDAM' || $parts[1] !== 'PP') {
            return null;
        }

        $invoiceId = (int) $parts[2];
        /** @var InvoiceDAO $invoiceDao */
        $invoiceDao = DAORegistry::getDAO('InvoiceDAO');
        $paidInvoice = $invoiceDao->getById($invoiceId);
        if (!$paidInvoice) {
            return null;
        }

        $paidGross = (float) ($payload['mc_gross'] ?? 0);
        $paidCurrency = strtoupper((string) ($payload['mc_currency'] ?? ''));
        $expectedAmount = $paidInvoice->getAmount();
        $expectedCurrency = strtoupper($paidInvoice->getCurrencyCode());

        if (abs($paidGross - $expectedAmount) > 0.01 || $paidCurrency !== $expectedCurrency) {
            $this->_alertSiteAdmins('PayPal', "Nominal IPN tidak cocok untuk invoice #{$invoiceId}. Diharapkan {$expectedAmount} {$expectedCurrency}, diterima {$paidGross} {$paidCurrency}.");
            return null;
        }

        $paymentStatus = (string) ($payload['payment_status'] ?? '');

        $status = 'UNPAID';
        if ($paymentStatus === 'Completed') {
            $status = 'PAID';
        } elseif (in_array($paymentStatus, ['Denied', 'Expired', 'Failed', 'Voided'], true)) {
            $status = 'CANCELLED';
        } else {
            return null;
        }

        return [
            'invoiceId' => $invoiceId,
            'status'    => $status,
            'method'    => 'PayPal',
            'reference' => (string) ($payload['txn_id'] ?? '')
        ];
    }

    /**
     * Verifikasi keaslian IPN dengan mengirim balik payload ke server PayPal
     * (pola cmd=_notify-validate) -- logika identik dengan yang sudah
     * dipakai PayPalPlugin::handle() case 'ipn', diekstrak jadi reusable
     * di sini supaya tidak ditulis ulang berbeda di dua tempat.
     * @param array $payload
     * @return bool
     */
    private function verifyIpn(array $payload): bool {
        if (empty($payload)) return false;

        $req = 'cmd=_notify-validate';
        foreach ($payload as $key => $value) {
            $req .= '&' . urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        $verifyUrl = $this->isProduction
            ? 'https://ipnpb.paypal.com/cgi-bin/webscr'
            : 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

        $ch = curl_init();

        $httpProxyHost = Config::getVar('proxy', 'http_host');
        if ($httpProxyHost) {
            curl_setopt($ch, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($ch, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $username = Config::getVar('proxy', 'username');
            if ($username) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }

        curl_setopt($ch, CURLOPT_URL, $verifyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Lumera-Wizdam-PayPal-Verification',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($req),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlErrno !== 0) {
            error_log("WIZDAM PayPal IPN verify cURL error: {$curlError}");
            return false;
        }

        return is_string($response) && strcmp(trim($response), 'VERIFIED') === 0;
    }

    /**
     * Helper kecil untuk membangun return-URL yang valid tanpa perlu hash
     * penuh (dipakai hanya sebagai halaman pendaratan umum setelah checkout,
     * bukan untuk validasi keamanan -- validasi sesungguhnya tetap terjadi
     * lewat IPN, bukan lewat return URL ini).
     * @param Invoice $invoice
     * @return string
     */
    private function _dummySecurePath(Invoice $invoice): string {
        import('lib.wizdam.classes.security.SecurityHashService');
        $hashService = new SecurityHashService();
        $invoiceId = $invoice->getInvoiceId();
        $hash = $hashService->generateHash('invoice', $invoiceId);
        return "{$hash}-{$invoiceId}";
    }

    /**
     * [SECURITY SHIELD] Kirim notifikasi ke semua Site Admin jika ada
     * percobaan webhook palsu dari Xendit.
     * @param string $message
     */
    private function _alertSiteAdmins(string $gatewayLabel, string $message): void {
        import('classes.notification.NotificationManager');
        $roleDao = DAORegistry::getDAO('RoleDAO'); /** @var RoleDAO $roleDao */
        $result = $roleDao->getUsersByRoleId(ROLE_ID_SITE_ADMIN, null);
        $notificationManager = new NotificationManager();
        while ($result && ($admin = $result->next())) {
            $notificationManager->createTrivialNotification(
                (int) $admin->getId(),
                NOTIFICATION_TYPE_ERROR,
                ['contents' => '[Xendit] ' . $message]
            );
        }
    }

}
?>