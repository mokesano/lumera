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
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

class EmbeddedServer {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmbeddedServer() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::EmbeddedServer(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Public API
    //
    /**
     * Start the embedded server.
     *
     * NB: The web service can take quite a bit longer than the
     * process to start. So if you want to be sure you should
     * instantiate SolrWebService and wait until it's status is
     * SOLR_STATUS_ONLINE.
     *
     * @return boolean true if the server started, otherwise false.
     */
    public function start() {
        // Run the start command.
        return $this->_runScript('start.sh');
    }

    /**
     * Stop the embedded server.
     *
     * @return boolean true if the server stopped, otherwise false.
     */
    public function stop() {
        // Run the stop command.
        return $this->_runScript('stop.sh');
    }

    /**
     * Stop the embedded server and wait until it actually exited.
     *
     * @return boolean true if the server stopped, otherwise false.
     */
    public function stopAndWait() {
        $running = $this->isRunning();
        if ($running) {
            // Stop the server.
            $success = $this->stop();
            if (!$success) return false;

            // Give the server time to actually go down.
            while($this->isRunning()) sleep(1);
        }
        return true;
    }

    /**
     * Check whether the embedded server is currently running.
     *
     * @return boolean true, if the server is running, otherwise false.
     */
    public function isRunning() {
        $returnValue = $this->_runScript('check.sh');
        return ($returnValue === true);
    }


    //
    // Private helper methods
    //
    /**
     * Find the script directory.
     *
     * @return string
     */
    protected function _getScriptDirectory() {
        // Modernized path resolution
        return dirname(__DIR__) . '/embedded/bin/';
    }

    /**
     * Run the given script.
     *
     * @param string $command The script to be executed.
     *
     * @return boolean true if the command executed successfully, otherwise false.
     */
    protected function _runScript($command) {
        // Get the log file name.
        $logFile = Config::getVar('files', 'files_dir') . '/lucene/solr-php.log';

        // [SECURITY FIX] Ensure the script directory exists before changing context
        $scriptDirectory = $this->_getScriptDirectory();
        if (!is_dir($scriptDirectory)) {
            error_log("Lucene Plugin Error: Script directory not found at " . $scriptDirectory);
            return false;
        }

        // [SECURITY FIX] Use escapeshellarg for the log path to prevent injection if path has spaces/special chars
        // Original: $command = $scriptDirectory . $command . ' 2>&1 >>"' . $logFile . '" </dev/null';
        $fullCommand = './' . $command . ' 2>&1 >>' . escapeshellarg($logFile) . ' </dev/null';

        // Execute the command.
        $workingDirectory = getcwd();
        
        // Change dir, execute, and revert
        if (chdir($scriptDirectory)) {
            // $dummy is initialized to avoid notice in PHP 8 if exec fills it
            $dummy = []; 
            exec($fullCommand, $dummy, $returnStatus);
            chdir($workingDirectory);
            
            // Return the result.
            return ($returnStatus === 0);
        }

        return false;
    }
}

?>