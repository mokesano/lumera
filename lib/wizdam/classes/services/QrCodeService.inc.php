<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/QrCodeService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class QrCodeService
 * 
 * @brief Wrapper independen untuk library chillerlan/php-qrcode (v5.0.5)
 */

require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService {
    
    /** @var \chillerlan\QRCode\QROptions $options */
    private QROptions $options;

    /**
     * Contructor
     */
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
     * @param string $data
     */
    public function generateBase64(string $data): string {
        $qrcode = new QRCode($this->options);
        return $qrcode->render($data);
    }

}
?>