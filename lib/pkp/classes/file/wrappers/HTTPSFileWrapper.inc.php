<?php
declare(strict_types=1);

/**
 * @file classes/file/wrappers/HTTPSFileWrapper.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HTTPSFileWrapper
 * @ingroup file_wrappers
 *
 * @brief Class abstracting operations for reading remote files using various protocols.
 * (for when allow_url_fopen is disabled).
 *
 */

import('lib.pkp.classes.file.wrappers.HTTPFileWrapper');

class HTTPSFileWrapper extends HTTPFileWrapper {
    /**
     * Constructor.
     * @param $url string
     * @param $info array
     */
    public function __construct($url, $info) {
        parent::__construct($url, $info);
        $this->setDefaultPort(443);
        $this->setDefaultHost('ssl://localhost');
        if (isset($this->info['host'])) {
            $this->info['host'] = 'ssl://' . $this->info['host'];
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HTTPSFileWrapper($url, $info) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HTTPSFileWrapper(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($url, $info);
    }
}

?>