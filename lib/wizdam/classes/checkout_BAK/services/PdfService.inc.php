<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/services/PdfService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class PdfService
 * @brief Wrapper independen untuk library mPDF (v8.1.0)
 */

require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');
import('lib.wizdam.classes.checkout.Invoice');

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfService {

    private Mpdf $mpdf;

    /**
     * Constructor for PdfService
     */
    public function __construct() {
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'Noto Sans' // Font standar yang elegan
        ]);
    }

    /**
     * Mencetak Invoice dalam bentuk PDF
     * @param Invoice $invoice Objek Invoice yang berisi data tagihan
     * @param string $qrCodeBase64 Data QR code dalam format base64
     */
    public function generateInvoicePdf(Invoice $invoice, string $qrCodeBase64): void {
        // 1. Panggil InvoiceService untuk merakit Data Transfer Object Array
        import('lib.wizdam.classes.checkout.services.InvoiceService');
        $invoiceService = new InvoiceService();
        $flatData = $invoiceService->getInvoiceSummary($invoice);

        // 2. Gabungkan string QR Code ke dalam array agar dikirim bersamaan
        $flatData['qrCodeBase64'] = $qrCodeBase64;

        // 3. Siapkan Template Manager
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign($flatData);

        // 4. Render HTML
        $html = $templateMgr->fetch('checkout/invoicePdf.tpl');

        // 5. Cetak ke PDF
        $this->mpdf->WriteHTML($html);
        
        // Buat nama file aman (Akses data menggunakan array access karena $flatData adalah array)
        $safeFilename = preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $flatData['wizdamInvoiceNumber']);
        $this->mpdf->Output("Invoice_{$safeFilename}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    /**
     * Mencetak LoA dalam bentuk PDF
     * @param array $loaData Data LoA yang berisi informasi tentang penerimaan
     * @param string $qrCodeBase64 Data QR code dalam format base64
     */
    public function generateLoAPdf(array $loaData, string $qrCodeBase64): void {
        // Panggil mesin template
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        // Lempar data ke template
        $templateMgr->assign([
            'loaData' => $loaData,
            'qrCodeBase64' => $qrCodeBase64,
        ]);

        // Render file desain PDF yang terpisah
        $html = $templateMgr->fetch('loa/pdf.tpl');

        // Cetak
        $this->mpdf->WriteHTML($html);
        $this->mpdf->Output("LoA_{$loaData['submissionId']}.pdf", Destination::INLINE);
    }
}
?>