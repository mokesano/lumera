<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/PdfService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class PdfService
 * @brief Wrapper independen untuk library mPDF yang murni menangani 
 * layouting dan rendering PDF. Digital signature via PHP OpenSSL native.
 */

require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');
import('lib.wizdam.classes.invoice.Invoice');
import('lib.wizdam.classes.security.DigitalSignatureService');

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Mpdf\MpdfException;

class PdfService {

    /** @var Mpdf */
    private Mpdf $mpdf;

    /** @var DigitalSignatureService */
    private DigitalSignatureService $signatureService;

    /**
     * Constructor.
     * Menggunakan sys_get_temp_dir() sebagai tempDir mPDF —
     * tidak membuat folder apapun di files_dir.
     */
    public function __construct() {
        $this->mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 15,
            'margin_right'  => 15,
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'default_font'  => 'Noto Sans',
            'tempDir'       => sys_get_temp_dir(), // Pakai temp sistem OS, tidak buat folder baru
        ]);

        $this->signatureService = new DigitalSignatureService();
    }

    /**
     * Generates PDF for the given invoice, with PHP OpenSSL digital signature.
     * Dokumen dibuat sepenuhnya on-the-fly di memory — tidak ada file yang disimpan ke disk.
     * @param Invoice $invoice
     * @param string  $qrCodeBase64
     * @return void
     */
    public function generateInvoicePdf(Invoice $invoice, string $qrCodeBase64): void {
        import('lib.wizdam.classes.services.InvoiceService');
        $invoiceService = new InvoiceService();
        $flatData = $invoiceService->getInvoiceSummary($invoice);

        // Tambahkan variabel tambahan yang dibutuhkan template PDF
        $flatData['qrCodeBase64']   = $qrCodeBase64;
        $flatData['wizdamSignedBy'] = 'Wizdam Frontedge System';

        $request     = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign($flatData);

        $html = $templateMgr->fetch('billing/invoicePdf.tpl');

        if (empty(trim($html))) {
            error_log('[WIZDAM PDF] Template menghasilkan output kosong: billing/invoicePdf.tpl');
            throw new \Exception(__('billing.error.pdfGenerationFailed'));
        }

        try {
            $safeFilename = preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $flatData['wizdamInvoiceNumber']);
            $filename     = "Invoice_{$safeFilename}.pdf";

            // 1. Inisiasi Digital Signature pada mPDF
            $this->signatureService->signPdf(
                $this->mpdf,
                'Invoice - ' . $flatData['wizdamInvoiceNumber'],
                'Automated Invoice Integrity Verification'
            );

            // 2. Tulis HTML ke dalam mPDF
            $this->mpdf->WriteHTML($html);

            // 3. Jika menggunakan mPDF native signature
            if (method_exists($this->mpdf, 'setSignature')) {
                $this->mpdf->Output($filename, Destination::INLINE);
                exit;
            }

            // 4. Fallback: Ekstrak PDF menjadi string dan tandatangani secara manual
            $pdfString = $this->mpdf->Output('', Destination::STRING_RETURN);
            $signedPdf = $this->signatureService->signPdfString($pdfString, 'Automated Invoice Integrity Verification');

            // 5. Lempar ke browser
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($signedPdf));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $signedPdf;
            exit;

        } catch (MpdfException $e) {
            error_log('[WIZDAM mPDF FATAL] ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            throw new \Exception(__('billing.error.pdfGenerationFailed'));
        }
    }

    /**
     * Generates a PDF for the given Letter of Acceptance.
     * @param array  $loaData
     * @param string $qrCodeBase64
     * @return void
     */
    public function generateLoAPdf(array $loaData, string $qrCodeBase64): void {
        $request     = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'loaData'      => $loaData,
            'qrCodeBase64' => $qrCodeBase64,
        ]);

        $html = $templateMgr->fetch('document/loa/LoAPdf.tpl');

        try {
            $submissionId = $loaData['submissionId'] ?? 'Unknown';

            $this->signatureService->signPdf(
                $this->mpdf,
                'Letter of Acceptance - ' . $submissionId,
                'Automated LoA Integrity Verification'
            );

            $this->mpdf->WriteHTML($html);

            if (method_exists($this->mpdf, 'setSignature')) {
                $this->mpdf->Output("LoA_{$submissionId}.pdf", Destination::INLINE);
                exit;
            }

            $pdfString = $this->mpdf->Output('', Destination::STRING_RETURN);
            $signedPdf = $this->signatureService->signPdfString($pdfString, 'Automated LoA Integrity Verification');

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="LoA_' . $submissionId . '.pdf"');
            header('Content-Length: ' . strlen($signedPdf));
            echo $signedPdf;
            exit;

        } catch (MpdfException $e) {
            error_log('[WIZDAM mPDF Error (LoA)] ' . $e->getMessage());
            throw new \Exception(__('loa.error.pdfGenerationFailed'));
        }
    }

    /**
     * Generates a PDF for the given Certificate.
     * @param array  $certData
     * @param string $qrCodeBase64
     * @return void
     */
    public function generateCertificatePdf(array $certData, string $qrCodeBase64): void {
        $request     = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign(['certData' => $certData, 'qrCodeBase64' => $qrCodeBase64]);

        $this->mpdf->AddPage('L');
        $html = $templateMgr->fetch('document/certificate/certificatePdf.tpl');

        try {
            $this->signatureService->signPdf(
                $this->mpdf,
                'Certificate - ' . $certData['certificateNumber'],
                'Automated Certificate Integrity Verification'
            );

            $this->mpdf->WriteHTML($html);

            if (method_exists($this->mpdf, 'setSignature')) {
                $this->mpdf->Output("Certificate_{$certData['certificateNumber']}.pdf", Destination::INLINE);
                exit;
            }

            $pdfString = $this->mpdf->Output('', Destination::STRING_RETURN);
            $signedPdf = $this->signatureService->signPdfString($pdfString, 'Automated Certificate Integrity Verification');

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Certificate_' . $certData['certificateNumber'] . '.pdf"');
            header('Content-Length: ' . strlen($signedPdf));
            echo $signedPdf;
            exit;

        } catch (MpdfException $e) {
            error_log('[WIZDAM mPDF Error (Certificate)] ' . $e->getMessage());
            throw new \Exception(__('document.error.pdfGenerationFailed'));
        }
    }
}
?>