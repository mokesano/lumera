<?php
declare(strict_types=1);

/**
 * @file pages/billing/WebhookHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class WebhookHandler
 * @brief Menerima HTTP POST diam-diam dari Payment Gateway (Server-to-Server).
 * Dilengkapi dengan pengamanan Signature, Idempotency, dan Retry-Handling.
 */

import('classes.handler.Handler');

// Memanggil WIZDAM Services dari folder semantik
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
        // Handler ini publik (S2S), tidak menggunakan HandlerValidator login.
        $this->invoiceService = new InvoiceService();
    }

    /**
     * Menangani request webhook yang masuk dari Payment Gateway.
     * URL contoh: /billing/webhook/midtrans
     * @param array $args URL segments setelah /webhook/, misal ['midtrans']
     * @param mixed $request Objek request bawaan OJS
     */
    public function index(array $args = [], $request = null): void {
        $gatewayName = isset($args[0]) ? strtolower($args[0]) : '';
        
        $jsonPayload = file_get_contents('php://input');
        $payload = json_decode((string) $jsonPayload, true);

        // Validasi Payload JSON
        if (!is_array($payload)) {
            header("HTTP/1.1 400 Bad Request");
            exit('Invalid JSON Payload');
        }

        $settingsService = new PaymentSettingsService();
        $gateway = null;

        // Inisialisasi Gateway menggunakan Factory Pattern sederhana
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
        } else {
            header("HTTP/1.1 404 Not Found");
            exit('Payment Gateway Not Supported');
        }

        try {
            // 1. Suruh Gateway menerjemahkan bahasa bank & validasi Signature
            $result = $gateway->processWebhook($payload);

            // [SECURITY SHIELD] Jika signature salah / payload dimanipulasi
            if (!$result) {
                // Log IP dan gateway untuk audit keamanan server
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown_IP';
                error_log("WIZDAM SECURITY ALERT: Invalid Webhook Signature attempt at {$gatewayName} from IP {$ipAddress}.");
                
                header("HTTP/1.1 403 Forbidden");
                exit('Forbidden: Invalid Security Signature');
            }

            // 2. Proses Berdasarkan Status Terstandar WIZDAM
            $invoiceId = (int) $result['invoiceId'];
            
            if ($result['status'] === 'PAID') {
                // Eksekusi penandaan lunas. Fungsi markAsPaid() sudah memiliki perlindungan 
                // idempotent di dalamnya (mengecek status sebelum update).
                $success = $this->invoiceService->markAsPaid($invoiceId, $result['method']);
                
                if ($success) {
                    error_log("WIZDAM PAYMENT SUCCESS: Invoice #{$invoiceId} cleared via {$result['method']} ({$gatewayName})");
                }
            } elseif ($result['status'] === 'CANCELLED') {
                // Tangani tagihan Virtual Account/Qris yang kedaluwarsa atau dibatalkan oleh bank
                if (method_exists($this->invoiceService, 'markAsCancelled')) {
                    $this->invoiceService->markAsCancelled($invoiceId);
                    error_log("WIZDAM PAYMENT CANCELLED: Invoice #{$invoiceId} expired/voided via {$gatewayName}");
                }
            }

            // 3. Sukses memproses tanpa ada error database. Balas 200 OK agar gateway berhenti melakukan retry.
            header("HTTP/1.1 200 OK");
            echo "OK";
            exit;

        } catch (\Throwable $e) {
            // [FAIL-SAFE] Jika Database / Server WIZDAM Error, RAM penuh, atau query gagal.
            error_log("WIZDAM WEBHOOK CRITICAL ERROR: " . $e->getMessage());
            
            // Wajib berikan 500 Server Error agar Midtrans/Xendit MENGIRIM ULANG webhook ini secara berkala (Retry Mechanism).
            header("HTTP/1.1 500 Internal Server Error");
            exit('Internal Server Error');
        }
    }
}
?>