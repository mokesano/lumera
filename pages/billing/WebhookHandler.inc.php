<?php
declare(strict_types=1);

/**
 * @file pages/billing/WebhookHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WebhookHandler
 * 
 * @brief Menerima HTTP POST diam-diam dari Payment Gateway (Server-to-Server).
 * Dilengkapi dengan pengamanan Signature, Idempotency, dan Retry-Handling.
 */

import('classes.handler.Handler');

import('lib.wizdam.classes.services.InvoiceService');
import('lib.wizdam.classes.services.PaymentSettingsService');

class WebhookHandler extends Handler {
    
    /** @var InvoiceService */
    private InvoiceService $invoiceService;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->invoiceService = new InvoiceService();
    }

    /**
     * Menangani request webhook yang masuk dari Payment Gateway.
     * URL contoh: /billing/webhook/midtrans, /billing/webhook/xendit, /billing/webhook/paypal
     * 
     * @param array $args
     * @param Request|null $request
     */
    public function webhook(array $args = [], $request = null): void {
        $gatewayName = isset($args[0]) ? strtolower($args[0]) : '';

        if ($gatewayName === 'paypal') {
            $payload = $_POST;
        } else {
            $jsonPayload = file_get_contents('php://input');
            $payload = json_decode((string) $jsonPayload, true);
        }

        if (!is_array($payload) || empty($payload)) {
            header("HTTP/1.1 400 Bad Request");
            exit('Invalid Payload');
        }

        // [FIX] Tentukan scope kredensial (Publisher/Partner) berdasarkan jurnal
        $rawInvoiceId = $this->_peekInvoiceIdFromPayload($gatewayName, $payload);
        $settingsService = new PaymentSettingsService();

        if ($rawInvoiceId > 0) {
            $peekedInvoice = $this->invoiceService->getInvoiceById($rawInvoiceId);
            if ($peekedInvoice) {
                /** @var JournalDAO $journalDao */
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journal = $journalDao->getById((int) $peekedInvoice->getData('journalId'));
                $settingsService = PaymentSettingsService::resolveForJournal($journal);
            }
        }

        $gateway = null;

        if ($gatewayName === 'midtrans') {
            import('lib.wizdam.classes.payment.MidtransGateway');
            $gateway = new MidtransGateway(
                $settingsService->getMidtransServerKey(), 
                $settingsService->isProduction()
            );
        } elseif ($gatewayName === 'xendit') {
            import('lib.wizdam.classes.payment.XenditGateway');
            $gateway = new XenditGateway(
                $settingsService->getXenditApiKey(),
                $settingsService->getXenditWebhookToken()
            );
        } elseif ($gatewayName === 'paypal') {
            import('lib.wizdam.classes.payment.PayPalGateway');
            $gateway = new PayPalGateway(
                $settingsService->getPayPalSellerEmail(),
                $settingsService->isProduction()
            );
        } else {
            header("HTTP/1.1 404 Not Found");
            exit('Payment Gateway Not Supported');
        }

        try {
            $result = $gateway->processWebhook($payload);

            if (!$result) {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown_IP';
                error_log("WIZDAM SECURITY ALERT: Invalid Webhook Signature attempt at {$gatewayName} from IP {$ipAddress}.");
                
                header("HTTP/1.1 403 Forbidden");
                exit('Forbidden: Invalid Security Signature');
            }

            $invoiceId = (int) $result['invoiceId'];
            
            if ($result['status'] === 'PAID') {
                $success = $this->invoiceService->markAsPaid($invoiceId, $result['method']);
                
                if ($success) {
                    error_log("WIZDAM PAYMENT SUCCESS: Invoice #{$invoiceId} cleared via {$result['method']} ({$gatewayName})");
                }
            } elseif ($result['status'] === 'CANCELLED') {
                if (method_exists($this->invoiceService, 'markAsCancelled')) {
                    $this->invoiceService->markAsCancelled($invoiceId);
                    error_log("WIZDAM PAYMENT CANCELLED: Invoice #{$invoiceId} expired/voided via {$gatewayName}");
                }
            }

            header("HTTP/1.1 200 OK");
            echo "OK";
            exit;

        } catch (\Throwable $e) {
            error_log("WIZDAM WEBHOOK CRITICAL ERROR: " . $e->getMessage());
            
            header("HTTP/1.1 500 Internal Server Error");
            exit('Internal Server Error');
        }
    }

    /**
     * [BARU] Mengintip invoiceId dari payload webhook TANPA verifikasi keamanan
     * apapun -- semata untuk menentukan scope kredensial (Publisher/Partner)
     * yang benar SEBELUM verifikasi signature sesungguhnya dijalankan oleh
     * processWebhook(). Mengembalikan 0 kalau format tidak dikenali/tidak ada
     * -- di titik itu kode di atas jatuh ke scope Publisher (perilaku lama).
     * @param string $gatewayName
     * @param array $payload
     * @return int
     */
    private function _peekInvoiceIdFromPayload(string $gatewayName, array $payload): int {
        $rawId = '';
        if ($gatewayName === 'xendit' && isset($payload['external_id'])) {
            $rawId = (string) $payload['external_id'];
        } elseif ($gatewayName === 'midtrans' && isset($payload['order_id'])) {
            $rawId = (string) $payload['order_id'];
        } elseif ($gatewayName === 'paypal' && isset($payload['custom'])) {
            $rawId = (string) $payload['custom'];
        }

        if ($rawId === '') return 0;

        // Format: WIZDAM-{invoiceId}-{time} / WIZDAM-X-{invoiceId}-{time} / WIZDAM-PP-{invoiceId}-{time}
        // -- segmen digit murni PERTAMA yang ditemui adalah invoiceId (selalu
        // muncul sebelum segmen timestamp).
        foreach (explode('-', $rawId) as $part) {
            if (ctype_digit($part) && (int) $part > 0) {
                return (int) $part;
            }
        }
        return 0;
    }
    
}
?>