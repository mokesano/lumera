<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/payment/XenditGateway.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class XenditGateway
 * 
 * @brief Adapter spesifik untuk Xendit PHP Library (v7.0.0+)
 */

require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');

import('lib.wizdam.classes.payment.PaymentGatewayInterface');
import('lib.wizdam.classes.invoice.Invoice');

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditGateway implements PaymentGatewayInterface {
    
    // [SECURITY SHIELD] Simpan instance API dan webhook token secara privat
    private InvoiceApi $apiInstance;
    private string $webhookToken; // Token dari dashboard Xendit untuk verifikasi

    /**
     * Constructor: Inisialisasi API Key dan Webhook Token
     * @param string $apiKey
     * @param string $webhookToken
     * @throws \RuntimeException
     */
    public function __construct(string $apiKey, string $webhookToken = '') {
        Configuration::getDefaultConfiguration()->setApiKey($apiKey);
        $this->apiInstance = new InvoiceApi();
        $this->webhookToken = $webhookToken;
    }

    /**
     * Buat URL pembayaran untuk invoice tertentu
     * @param Invoice $invoice
     * @param array $customerData
     * @return array
     * @throws \RuntimeException
     */
    public function getPaymentCheckoutData(Invoice $invoice, array $customerData = [], string $paymentType = 'all'): array {
        $invoiceData = [
            'external_id' => 'WIZDAM-X-' . $invoice->getInvoiceId() . '-' . time(),
            'amount' => (float) $invoice->getAmount(),
            'description' => 'Wizdam Billing: ' . $invoice->getFeeType(),
            'payer_email' => $customerData['email'] ?? 'no-reply@wizdam.com',
            'customer' => [
                'given_names' => $customerData['first_name'] ?? 'User',
            ],
            'currency' => $invoice->getCurrencyCode() ?: 'IDR'
        ];

        // [WIZDAM UX] Kunci Iframe Xendit hanya pada metode yang dipilih user
        if ($paymentType === 'qris') {
            $invoiceData['payment_methods'] = ['QRIS'];
        } elseif ($paymentType === 'bank_transfer') {
            $invoiceData['payment_methods'] = ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BSI'];
        }

        $createInvoiceRequest = new CreateInvoiceRequest($invoiceData);

        try {
            $result = $this->apiInstance->createInvoice($createInvoiceRequest);
            return [
                'gateway' => 'xendit',
                'token' => '',
                'url' => $result->getInvoiceUrl()
            ];
        } catch (\Exception $e) {
            error_log("WIZDAM Xendit Error: " . $e->getMessage());
            throw new \RuntimeException("Gagal membuat link pembayaran Xendit.");
        }
    }

    /**
     * Proses webhook callback dari Xendit
     * @param array $payload
     * @return array|null
     */
    public function processWebhook(array $payload): ?array {
        // [SECURITY SHIELD] Validasi Xendit Callback Token dari HTTP Header
        $incomingToken = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';

        if ($this->webhookToken === '' || !hash_equals($this->webhookToken, $incomingToken)) {
            $this->_alertSiteAdmins(
                $this->webhookToken === ''
                    ? 'Xendit webhook token belum dikonfigurasi -- webhook ditolak.'
                    : 'Percobaan webhook Xendit palsu terdeteksi dan ditolak.'
            );
            return null;
        }

        if (!isset($payload['external_id']) || !isset($payload['status'])) {
            return null;
        }

        $orderParts = explode('-', $payload['external_id']);
        if (count($orderParts) < 3 || $orderParts[0] !== 'WIZDAM' || $orderParts[1] !== 'X') {
            return null;
        }

        $invoiceId = (int) $orderParts[2];
        $xenditStatus = $payload['status'];
        
        $wizdamStatus = 'UNPAID';
        if ($xenditStatus === 'PAID' || $xenditStatus === 'SETTLED') {
            $wizdamStatus = 'PAID';
        } elseif ($xenditStatus === 'EXPIRED') {
            $wizdamStatus = 'CANCELLED';
        }

        return [
            'invoiceId' => $invoiceId,
            'status' => $wizdamStatus,
            'method' => 'Xendit - ' . ($payload['payment_method'] ?? 'Unknown'),
            'reference' => (string) ($payload['id'] ?? '')
        ];
    }

    /**
     * Kirim notifikasi ke semua admin situs jika ada masalah keamanan
     * @param string $message
     * @return void
     */
    private function _alertSiteAdmins(string $message): void {
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