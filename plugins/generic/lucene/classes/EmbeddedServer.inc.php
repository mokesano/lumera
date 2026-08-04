<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/classes/EmbeddedServer.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmbeddedServer
 * @ingroup plugins_generic_lucene_classes
 *
 * @brief Implements a PHP interface to administer the embedded solr server.
 */

class EmbeddedServer {

    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function EmbeddedServer() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Public API
    //

    /**
     * Start the embedded server.
     * @return bool
     */
    public function start() {
        return $this->_runScript('start.sh');
    }

    /**
     * Stop the embedded server.
     * @return bool
     */
    public function stop() {
        return $this->_runScript('stop.sh');
    }

    /**
     * Stop the embedded server and wait until it exits.
     * @return bool
     */
    public function stopAndWait() {
        if ($this->isRunning()) {
            if (!$this->stop()) {
                return false;
            }
            while ($this->isRunning()) {
                sleep(1);
            }
        }
        return true;
    }

    /**
     * Check whether the embedded server is currently running.
     * @return bool
     */
    public function isRunning() {
        return $this->_runScript('check.sh');
    }

    //
    // Private helper methods
    //

    /**
     * Get the script directory path.
     * @return string
     */
    protected function _getScriptDirectory() {
        return dirname(__DIR__) . '/embedded/bin/';
    }

    /**
     * Execute a shell script.
     * @param string $command
     * @return bool
     */
    protected function _runScript($command) {
        $logFile = (string) Config::getVar('files', 'files_dir') . '/lucene/solr-php.log';
        $scriptDirectory = $this->_getScriptDirectory();
        
        if (!is_dir($scriptDirectory)) {
            error_log("Lucene Plugin Error: Script directory not found at " . $scriptDirectory);
            return false;
        }

        $fullCommand = './' . escapeshellcmd($command) . ' 2>&1 >>' . escapeshellarg($logFile) . ' </dev/null';
        $workingDirectory = getcwd();
        if ($workingDirectory === false) {
            return false;
        }

        if (chdir($scriptDirectory)) {
            $output = []; 
            $returnStatus = -1;
            exec($fullCommand, $output, $returnStatus);
            chdir($workingDirectory);
            
            return ($returnStatus === 0);
        }

        return false;
    }
    
}
?>