<?php
declare(strict_types=1);

/**
 * @defgroup oai_format
 */

/**
 * @file plugins/oaiMetadataFormats/dc/OAIMetadataFormat_DC.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_DC
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- Dublin Core.
 * [WIZDAM EDITION] REFACTOR: PHP 8.1+ Compatibility
 */

import('lib.pkp.plugins.oaiMetadataFormats.dc.PKPOAIMetadataFormat_DC');

class OAIMetadataFormat_DC extends PKPOAIMetadataFormat_DC {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'oai_dc',
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            'http://www.openarchives.org/OAI/2.0/oai_dc/'
        );
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormat_DC() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Convert an article to an OAI Dublin Core XML document.
     * @see lib/pkp/plugins/oaiMetadataFormats/dc/PKPOAIMetadataFormat_DC::toXml()
     *
     * [WIZDAM NOTE]
     * Signature disesuaikan persis dengan Parent:
     * - Hapus type hint '?string' pada $format
     * - Parameter $record dibiarkan mixed
     *
     * @param mixed $record
     * @param mixed $format (Type hint dihapus agar match dengan parent)
     * @return string
     */
    public function toXml($record, $format = null): string {
        // [WIZDAM LOGIC] Extraction
        $article = null;
        if ($record instanceof OAIRecord) {
            $article = $record->getData('article');
        } elseif ($record instanceof DataObject) {
            $article = $record;
        }

        // Panggil Parent HANYA jika datanya valid
        if ($article) {
             return parent::toXml($article, $format);
        }
        
        return '';
    }
}
?>