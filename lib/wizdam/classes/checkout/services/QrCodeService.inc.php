<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/services/QrCodeService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class QrCodeService
 * @brief Wrapper independen untuk library chillerlan/php-qrcode (v5.0.5)
 */

// Panggil library murni tanpa merusak autoloader OJS
require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService {
    
    private QROptions $options;

    public function __construct() {
        $this->options = new QROptions([
            'version'      => QRCode::VERSION_AUTO,
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_L,
            'scale'        => 5,
            'imageBase64'  => true, 
        ]);
    }

    /**
     * Menghasilkan string gambar base64 dari sebuah teks/URL
     */
    public function generateBase64(string $data): string {
        $qrcode = new QRCode($this->options);
        return $qrcode->render($data);
    }
}
?>