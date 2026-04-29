<?php
declare(strict_types=1);

/**
 * @file pages/oai/OAIHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIHandler
 * @ingroup pages_oai
 *
 * @brief Handle OAI protocol requests.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

define('SESSION_DISABLE_INIT', 1);

import('classes.oai.ojs.JournalOAI');
import('classes.handler.Handler');

class OAIHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Entry point utama untuk menangani request OAI.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard & DI
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        PluginRegistry::loadCategory('oaiMetadataFormats', true);

        // Membuat instance OAI; menggunakan try/catch untuk menangkap error fatal.
        try {
            $oai = new JournalOAI(
                new OAIConfig(
                    $request->url(null, 'oai'),
                    Config::getVar('oai', 'repository_id')
                )
            );
        } catch (Throwable $e) {
            // Jika gagal instansiasi, endpoint tidak dapat melanjutkan.
            if (Config::getVar('debug', 'display_errors')) {
                echo "<h1>OAI CONFIG ERROR</h1><pre>" . $e->getMessage() . "</pre>";
            }
            return;
        }

        // Jika request bukan untuk jurnal valid, kembalikan 404.
        if (!$request->getJournal() && $request->getRequestedJournalPath() != 'index') {
            $dispatcher = $request->getDispatcher();
            $dispatcher->handle404($request);
            return;
        }

        // Menjalankan OAI — ini area paling rawan crash, sehingga dibungkus try/catch.
        try {
            $oai->execute();
        } catch (Throwable $e) {
            // Untuk debugging/diagnostik: error ditampilkan ke browser jika debug mode.
            // OAI response harus valid XML, jadi echo error HTML akan merusak format, 
            // tapi ini darurat (system error).
            echo "<h1>OAI SYSTEM ERROR</h1>";
            echo "<pre>" . $e->getMessage() . "</pre>";
        }
    }

    /**
     * Validasi OAI.
     * Fungsi ini memastikan bahwa fitur OAI diaktifkan di config.inc.php.
     * @param mixed $requiredContexts
     * @param object|null $request
     */
    public function validate($requiredContexts = null, $request = null) {
        // [WIZDAM] Request Fallback
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // Site validation checks not applicable
        // parent::validate();

        if (!Config::getVar('oai', 'oai')) {
            $request->redirect(null, 'index');
        }
    }

    /**
     * Menentukan apakah endpoint ini memerlukan SSL.
     * @return bool
     */
    public function requireSSL() {
        return false;
    }
}
?>