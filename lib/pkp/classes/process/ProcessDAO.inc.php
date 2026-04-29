<?php
declare(strict_types=1);

/**
 * @file classes/process/ProcessDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProcessDAO
 * @ingroup process
 * @see Process
 *
 * @brief Operations for retrieving and modifying process data.
 *
 * Parallel processes are pooled. This defines a given number
 * of process slots per pool. Once these slots are occupied, no
 * new processes can be spawned for a given process type.
 *
 * The process ID is not an integer but a globally unique string
 * identifier that has to fulfill the following additional functions:
 * 1) It is used as a one-time-key to authorize the the web
 * request spawning a new process. It therefore has to be
 * random enough to avoid it being guessed by an outsider.
 * 2) We also use the process ID as a unique token to implement
 * an atomic locking strategy to avoid race conditions when
 * executing processes in parallel.
 *
 * We use the uniqid() method to genereate one-time keys. This is not
 * really cryptographically secure but it probably makes it difficult
 * enough to guess the key to avoid abuse.
 * This assumes that we don't start using processes for more sensitive
 * tasks. If that happens we'd need to improve the randomness of the
 * process id (e.g. via /dev/urandom or similar).
 *
 * This usage of the processes table also explains why there is no
 * updateObject() method in this DAO. If you need a process with different
 * characteristics then insert a new one and delete stale processes.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility)
 */


// Define the max number of seconds a process is allowed to run.
// We assume that no process should run longer than
// 15 minutes. So we clean all processes that have a time
// stamp of more than 15 minutes ago. Running processes should check
// regularly (about once per minute) whether "their" process entry
// is still their. If not they are required to halt immediately.
// NB: Don't set this timeout much shorter as this may
// potentially cause more parallel processes being spawned
// than allowed.
define('PROCESS_MAX_EXECUTION_TIME', 900);

// Cap the max. number of parallel process to avoid server
// flooding in case of an error.
define('PROCESS_MAX_PARALLELISM', 20);

// The max. number of seconds a one-time-key will be kept valid.
// This defines the potential window of attack if an attacker
// manages to guess a key. Defining this time too short can lead
// to problems when networks are slow.
define('PROCESS_MAX_KEY_VALID', 10);

import('lib.pkp.classes.process.Process');

class ProcessDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ProcessDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ProcessDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Insert a new process.
     * @param $processType integer one of the PROCESS_TYPE_* constants
     * @param $maxParallelism integer the max. number
     * of parallel processes allowed for the given
     * process type.
     * @return Process|bool the new process instance, boolean
     * false if there are too many parallel processes.
     */
    public function insertObject($processType, $maxParallelism) {
        // Free processing slots occupied by zombie processes.
        $this->deleteZombies();

        // Cap the parallelism to the max. parallelism.
        $maxParallelism = min($maxParallelism, PROCESS_MAX_PARALLELISM);

        // Check whether we're allowed to spawn another process.
        $currentParallelism = $this->getNumberOfObjectsByProcessType($processType);
        if ($currentParallelism >= $maxParallelism) {
            return false;
        }

        // We create a process instance from the given data.
        $process = $this->newDataObject();
        $process->setProcessType($processType);

        // Generate a new process ID. See classdoc for process ID
        // requirements.
        $process->setId(uniqid('', true));

        // Generate the timestamp.
        $process->setTimeStarted(time());

        // Persist the process.
        $this->update(
            sprintf('INSERT INTO processes
                (process_id, process_type, time_started, obliterated)
                VALUES
                (?, ?, ?, 0)'),
            array(
                $process->getId(),
                (int) $process->getProcessType(),
                (int) $process->getTimeStarted(),
            )
        );
        $process->setObliterated(false);
        return $process;
    }

    /**
     * Get a process by ID.
     * @param $processId string
     * @return Process
     */
    public function getObjectById($processId) {
        $result = $this->retrieve(
            'SELECT process_id, process_type, time_started, obliterated FROM processes WHERE process_id = ?',
            $processId
        );

        $process = null;
        if ($result->RecordCount() != 0) {
            $process = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $process;
    }

    /**
     * Determine the number of currently running
     * processes for a given process type.
     * @param $processType
     * @return integer
     */
    public function getNumberOfObjectsByProcessType($processType) {
        // Find the number of processes for the
        // given process type.
        $result = $this->retrieve(
            'SELECT COUNT(*) AS running_processes
             FROM processes
             WHERE process_type = ?',
            (int) $processType
        );

        $runningProcesses = 0;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $runningProcesses = (int)$row['running_processes'];
        }
        return $runningProcesses;
    }

    /**
     * Delete a process.
     * @param $process Process
     */
    public function deleteObject($process) {
        return $this->deleteObjectById($process->getId());
    }

    /**
     * Delete a process by ID.
     * @param $processId string
     */
    public function deleteObjectById($processId) {
        assert(!empty($processId));

        // Delete process
        return $this->update('DELETE FROM processes WHERE process_id = ?', $processId);
    }

    /**
     * Delete stale processes.
     *
     * Zombie processes are remnants of process executions
     * that for some reason died. We have to regularly remove
     * them so that the process slots they occupy are freed
     * for new processes.
     * @param $force whether to force zombie removal, even
     * if they have been removed before.
     *
     * @see PROCESS_MAX_EXECUTION_TIME
     */
    public function deleteZombies($force = false) {
        static $zombiesDeleted = false;

        // For performance reasons don't delete zombies
        // more than once per request.
        if ($zombiesDeleted && !$force) {
            return;
        } else {
            $zombiesDeleted = true;
        }

        // Calculate the max timestamp that is considered ok.
        $maxTimestamp = time() - PROCESS_MAX_EXECUTION_TIME;

        // Delete all processes with a timestamp older than
        // the max. timestamp.
        return $this->update(
            'DELETE FROM processes
            WHERE time_started < ?',
            (int) $maxTimestamp
        );
    }

    /**
     * Spawn new processes via web requests.
     * @param $request Request
     * @param $handler string a fully qualified handler class name
     * @param $op string the operation to be called on the handler
     * @param $processType integer one of the PROCESS_TYPE_* constants
     * @param $noOfProcesses integer the number of processes to be spawned.
     * @return integer the actual number of spawned processes.
     */
    public function spawnProcesses($request, $handler, $op, $processType, $noOfProcesses) {
        // Parse URL once
        $urlParts = $this->_parseProcessUrl($request, $handler, $op);
        if (!$urlParts) {
            return 0;
        }
    
        // Determine protocol settings
        [$transport, $port] = $this->_getTransportSettings($urlParts);
    
        // Clean up zombies
        $this->deleteZombies();
    
        // Calculate max parallelism
        $noOfProcesses = min($noOfProcesses, PROCESS_MAX_PARALLELISM);
        $currentParallelism = $this->getNumberOfObjectsByProcessType($processType);
    
        $spawnedProcesses = 0;
        while ($currentParallelism < $noOfProcesses) {
            $process = $this->insertObject($processType, $noOfProcesses);
            if (!is_a($process, 'Process')) {
                break;
            }
    
            // Attempt to spawn the process
            $success = $this->_spawnSingleProcess(
                $transport,
                $urlParts['host'],
                $port,
                $urlParts,
                $process->getId()
            );
    
            if (!$success) {
                // Log failure but continue trying other processes
                error_log("ProcessDAO: Failed to spawn process {$process->getId()}");
            }
    
            $currentParallelism++;
            $spawnedProcesses++;
        }
    
        return $spawnedProcesses;
    }
    
    /**
     * Parse and validate the process URL.
     * @return array|null URL parts or null if invalid
     */
    private function _parseProcessUrl($request, $handler, $op) {
        $router = $request->getRouter();
        $dispatcher = $router->getDispatcher();
        $processUrl = $dispatcher->url($request, ROUTE_COMPONENT, null, $handler, $op);
    
        $urlParts = parse_url($processUrl);
        
        // Validate URL structure
        if (!isset($urlParts['scheme'], $urlParts['host'], $urlParts['path'])) {
            error_log("ProcessDAO: Invalid process URL: {$processUrl}");
            return null;
        }
    
        if (isset($urlParts['fragment'])) {
            error_log("ProcessDAO: URL fragments not allowed: {$processUrl}");
            return null;
        }
    
        return $urlParts;
    }
    
    /**
     * Determine transport protocol and port from parsed URL.
     * @param array $urlParts Parsed URL components
     * @return array [transport, port]
     */
    private function _getTransportSettings(array $urlParts): array {
        $scheme = $urlParts['scheme'] ?? 'http';
        $port = $urlParts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $transport = ($scheme === 'https') ? 'ssl://' : '';
        
        return [$transport, (int) $port];
    }
    
    /**
     * Attempt to spawn a single background process via HTTP request.
     * @param string $transport SSL transport prefix or empty
     * @param string $host Target host
     * @param int $port Target port
     * @param array $urlParts The parsed URL parts array
     * @param string $oneTimeKey Process authorization key
     * @return bool True if successful, false otherwise
     */
    private function _spawnSingleProcess($transport, $host, $port, $urlParts, $oneTimeKey) {
        $socketAddress = $transport . $host;
        
        // Timeout 5 detik hanya untuk proses koneksi awal (Handshake)
        $stream = @fsockopen($socketAddress, $port, $errno, $errstr, 5);
        
        if (!is_resource($stream)) {
            error_log(
                "ProcessDAO: Failed to open socket to {$socketAddress}:{$port} - Error {$errno}: {$errstr}"
            );
            return false;
        }

        try {
            // [WIZDAM FIX] Biarkan stream secara default (Blocking) untuk memastikan TCP Buffer terisi penuh
            // sebelum koneksi ditutup. Penulisan HTTP header instan, tidak akan menyebabkan lag.
            $httpRequest = $this->_buildHttpRequest($urlParts, $oneTimeKey);
            
            $bytesWritten = fwrite($stream, $httpRequest);
            if ($bytesWritten === false) {
                error_log("ProcessDAO: Failed to write to socket");
                return false;
            }

            // Fire-and-forget: Kita sukses mengirim instruksi penuh, jangan baca responsenya.
            return true;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
    
    /**
     * Build HTTP GET request string ensuring existing query strings are preserved.
     * @param array $urlParts Parsed URL components
     * @param string $oneTimeKey Authorization Token
     * @return string HTTP request
     */
    private function _buildHttpRequest($urlParts, $oneTimeKey) {
        $host = $urlParts['host'];
        $path = $urlParts['path'];
        $query = isset($urlParts['query']) ? $urlParts['query'] : '';
        $encodedKey = urlencode($oneTimeKey);

        // [WIZDAM FIX] Pertahankan URL bawaan OJS Router. Gabungkan authToken dengan aman.
        $separator = empty($query) ? '?' : '&';
        $fullPath = $path . (!empty($query) ? '?' . $query : '') . $separator . 'authToken=' . $encodedKey;

        return "GET {$fullPath} HTTP/1.1\r\n"
             . "Host: {$host}\r\n"
             . "User-Agent: Frontedge-Scholar-Wizdam\r\n"
             . "Connection: Close\r\n\r\n";
    }

    /**
     * Check the one-time-key of a process. If the
     * key has not been checked before then this call
     * will mark it as used.
     * @param $processId string the unique process ID
     * which is being used as one-time-key.
     * @return boolean
     */
    public function authorizeProcess($processId) {
        $process = $this->getObjectById($processId);
        // [WIZDAM FIX] Strict Standard
        if ($process instanceof Process && $process->getObliterated() === false) {
            // The one time key has not been used yet.
            // Mark it as used.
            $success = $this->update(
                'UPDATE processes
                 SET obliterated = 1
                 WHERE process_id = ?',
                $processId
            );
            if (!$success) return false;

            // Only authorize process if its one-time-key has not expired yet.
            $minTimestamp = time() - PROCESS_MAX_KEY_VALID;
            $authorized = ($process->getTimeStarted() > $minTimestamp);

            // Delete the process entry if the process was
            // not authorized due to an expired key.
            if (!$authorized) $this->deleteObjectById($processId);

            return $authorized;
        }

        // Deny access if the process entry doesn't exist...
        return false;
    }

    /**
     * Check whether a process identified by its ID
     * can continue to run. This should be called
     * about once a minute by running processes.
     * If this method returns false then the
     * process is required to halt immediately.
     * @param $processId string
     * @return boolean
     */
    public function canContinue($processId) {
        // Calculate the max timestamp that is considered ok.
        $minTimestamp = time() - PROCESS_MAX_EXECUTION_TIME;

        // Check whether the process is still allowed to run.
        $process = $this->getObjectById($processId);
        
        // [WIZDAM FIX] Strict Standard
        $canContinue = ($process instanceof Process && $process->getTimeStarted() > $minTimestamp);

        // Delete the process entry if the process is not allowed to continue.
        if (!$canContinue) $this->deleteObjectById($processId);

        return $canContinue;
    }

    /**
     * Instantiate and return a new data object.
     * @return DataObject
     */
    public function newDataObject() {
        return new Process();
    }

    //
    // Private helper methods
    //
    
    /**
     * Internal function to return a process object from a row.
     * @param $row array
     * @return Process
     */
    public function _fromRow($row) {
        $process = $this->newDataObject();
        $process->setId($row['process_id']);
        $process->setProcessType((int) $row['process_type']);
        $process->setTimeStarted((int) $row['time_started']);
        $process->setObliterated((bool) $row['obliterated']);
        return $process;
    }
}

?>