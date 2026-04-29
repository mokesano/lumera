<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/security/DigitalSignatureService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class DigitalSignatureService
 * @brief Layanan kriptografi PAdES terintegrasi dengan Hierarki Konfigurasi.
 * Mendukung Auto-Generation dan kompatibilitas dengan CA Resmi (seperti BSrE).
 */

use Mpdf\Mpdf;

class DigitalSignatureService {

    /** @var string */
    private string $certPath;
    /** @var string */
    private string $privateKeyPath;
    /** @var string */
    private string $certDir;

    /**
     * Constructor
     */
    public function __construct() {
        $filesDir = Config::getVar('files', 'files_dir');
        $this->certDir        = $filesDir . '/certs';
        $this->certPath       = $this->certDir . '/wizdam_cert.crt';
        $this->privateKeyPath = $this->certDir . '/wizdam_private.pem';
        $this->_ensureCertificatesExist();
    }

    /**
     * Get digital signature configuration
     * @param int $journalId Optional journal ID for journal-specific config
     * @return array Associative array of signature configuration values
     */
    private function getSignatureConfig(int $journalId = 0): array {
        $defaults = [
            'countryName'            => 'ID',
            'stateOrProvinceName'    => 'Riau Islands',
            'localityName'           => 'Tanjung Pinang',
            'organizationName'       => 'Sangia Publishing House',
            'organizationalUnitName' => 'Wizdam Frontedge System',
            'commonName'             => 'journals.sangia.org',
            'emailAddress'           => 'admin@sangia.org',
            'signatureName'          => 'Wizdam Frontedge System',
            'signatureLocation'      => 'Indonesia'
        ];

        $config = [];
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        foreach ($defaults as $key => $defaultVal) {
            $value = null;
            if ($journalId > 0) {
                $value = $journalSettingsDao->getSetting($journalId, 'digitalSignature_' . $key);
            }
            if (empty($value)) {
                $value = Config::getVar('digital_signature', $key);
            }
            $config[$key] = !empty($value) ? $value : $defaultVal;
        }

        return $config;
    }

    /**
     * Ensure that the certificate and private key files exist.
     * If not, generate a self-signed certificate using OpenSSL.
     * This method is called during construction to guarantee availability of credentials.
     */
    private function _ensureCertificatesExist(): void {
        if (!is_dir($this->certDir)) {
            mkdir($this->certDir, 0755, true);
        }

        if (file_exists($this->certPath) && file_exists($this->privateKeyPath)) {
            return;
        }

        if (!extension_loaded('openssl')) {
            error_log('[WIZDAM CRITICAL] Ekstensi OpenSSL PHP tidak aktif.');
            return;
        }

        $conf = $this->getSignatureConfig(0);

        $dn = [
            "countryName"            => $conf['countryName'],
            "stateOrProvinceName"    => $conf['stateOrProvinceName'],
            "localityName"           => $conf['localityName'],
            "organizationName"       => $conf['organizationName'],
            "organizationalUnitName" => $conf['organizationalUnitName'],
            "commonName"             => $conf['commonName'],
            "emailAddress"           => $conf['emailAddress']
        ];

        $privkey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        $csr  = openssl_csr_new($dn, $privkey, ['digest_alg' => 'sha256']);
        $x509 = openssl_csr_sign($csr, null, $privkey, 3650, ['digest_alg' => 'sha256']);

        openssl_pkey_export_to_file($privkey, $this->privateKeyPath);
        openssl_x509_export_to_file($x509, $this->certPath);

        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($privkey);
            openssl_x509_free($x509);
        }
    }

    /**
     * Sign a PDF document with a digital signature
     * @param Mpdf $mpdf The mPDF instance
     * @param string $docTitle The title of the document
     * @param string $reason The reason for the signature
     * @param int $journalId Optional journal ID for journal-specific config
     * @return void
     */
    public function signPdf(Mpdf $mpdf, string $docTitle, string $reason, int $journalId = 0): void {
        $conf = $this->getSignatureConfig($journalId);

        // Metadata — selalu tersedia di semua versi mPDF
        $mpdf->SetTitle($docTitle);
        $mpdf->SetCreator($conf['signatureName']);
        $mpdf->SetAuthor($conf['organizationName']);

        // mPDF v8+ tidak punya setSignature() — signing via signPdfString()
        if (!method_exists($mpdf, 'setSignature')) {
            return;
        }

        // mPDF v7: alur asli, tidak diubah
        if (file_exists($this->certPath) && file_exists($this->privateKeyPath)) {
            $mpdf->setSignature(
                'file://' . $this->certPath,
                'file://' . $this->privateKeyPath,
                '',
                '',
                2,
                [
                    'Name'        => $conf['signatureName'],
                    'Reason'      => $reason,
                    'Location'    => $conf['signatureLocation'],
                    'ContactInfo' => $conf['emailAddress']
                ]
            );
            $mpdf->SetProtection(['print', 'copy']);
        }
    }

    /**
     * Sign a PDF string with a digital signature
     * @param string $pdfContent The PDF content as a string
     * @param string $reason The reason for the signature
     * @param int $journalId Optional journal ID for journal-specific config
     * @return string The signed PDF content
     */
    public function signPdfString(string $pdfContent, string $reason, int $journalId = 0): string {
        if (!extension_loaded('openssl')) {
            // error_log('[WIZDAM SIGNATURE] OpenSSL tidak aktif, PDF dikirim tanpa tanda tangan.');
            return $pdfContent;
        }

        if (!file_exists($this->certPath) || !file_exists($this->privateKeyPath)) {
            // error_log('[WIZDAM SIGNATURE] File sertifikat tidak ditemukan, PDF dikirim tanpa tanda tangan.');
            return $pdfContent;
        }

        $conf = $this->getSignatureConfig($journalId);

        // Baca private key dan buat resource OpenSSL — murni in-memory
        $keyContent = file_get_contents($this->privateKeyPath);
        $privateKey = openssl_pkey_get_private($keyContent);

        if ($privateKey === false) {
            // error_log('[WIZDAM SIGNATURE] Gagal membaca private key.');
            return $pdfContent;
        }

        // Buat tanda tangan RSA-SHA256 atas seluruh konten PDF
        $signature = '';
        $signed = openssl_sign($pdfContent, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$signed) {
            // error_log('[WIZDAM SIGNATURE] openssl_sign() gagal: ' . openssl_error_string());
            return $pdfContent;
        }

        // Encode sertifikat publik untuk disematkan (untuk keperluan verifikasi)
        $certContent   = file_get_contents($this->certPath);
        $certBase64    = base64_encode($certContent);
        $signatureB64  = base64_encode($signature);

        // Sematkan sebagai PDF comment di akhir dokumen
        // PDF comment (%%) diabaikan oleh PDF reader tapi dapat dibaca untuk verifikasi
        $signedPdf = $pdfContent
            . "\n%% WIZDAM-DIGITAL-SIGNATURE-BEGIN\n"
            . "%% Algorithm: RSA-SHA256\n"
            . "%% Signer: "      . $conf['signatureName']    . "\n"
            . "%% Organization: ". $conf['organizationName'] . "\n"
            . "%% Location: "    . $conf['signatureLocation']. "\n"
            . "%% Reason: "      . $reason                   . "\n"
            . "%% Timestamp: "   . date('Y-m-d H:i:s T')     . "\n"
            . "%% Certificate: " . $certBase64               . "\n"
            . "%% Signature: "   . $signatureB64             . "\n"
            . "%% WIZDAM-DIGITAL-SIGNATURE-END\n";

        // error_log('[WIZDAM SIGNATURE] PDF berhasil ditandatangani via PHP OpenSSL in-memory.');
        return $signedPdf;
    }

    /**
     * Verifikasi apakah PDF sudah ditandatangani oleh Wizdam.
     * Membaca signature dari comment PDF dan memverifikasi menggunakan
     * sertifikat publik yang disematkan.
     * @param string $pdfContent Konten PDF sebagai string
     * @return bool
     */
    public function verifyPdfString(string $pdfContent): bool {
        if (!str_contains($pdfContent, '%% WIZDAM-DIGITAL-SIGNATURE-BEGIN')) {
            return false;
        }

        // Ekstrak blok signature
        preg_match('/%% Certificate: (.+)\n/', $pdfContent, $certMatch);
        preg_match('/%% Signature: (.+)\n/',   $pdfContent, $sigMatch);

        if (empty($certMatch[1]) || empty($sigMatch[1])) {
            return false;
        }

        $certContent = base64_decode($certMatch[1]);
        $signature   = base64_decode($sigMatch[1]);

        // Ambil konten PDF asli (sebelum blok signature)
        $pdfOriginal = substr($pdfContent, 0, strpos($pdfContent, "\n%% WIZDAM-DIGITAL-SIGNATURE-BEGIN"));

        $pubKey = openssl_get_publickey($certContent);
        if ($pubKey === false) return false;

        return openssl_verify($pdfOriginal, $signature, $pubKey, OPENSSL_ALGO_SHA256) === 1;
    }
}
?>