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
 * of process fields per pool. Once these fields are occupied, no
 * new processes can be spawned for a given process type.
 *
 * The process ID is not an integer but a globally unique string
 * identifier that has to fulfill the following additional functions:
 * 1) It is used as a one-time-key to authorize the web
 * request spawning a new process. It therefore has to be
 * random enough to avoid it being guessed by an outsider.
 * 2) We also use the process ID as a unique token to implement
 * an atomic locking strategy to avoid race conditions when
 * executing processes in parallel.
 *
 * We use the uniqid() method to generate one-time keys. This is not
 * really cryptographically secure but it probably makes it difficult
 * enough to guess the key to avoid abuse.
 * This assumes that we don't start using processes for more sensitive
 * tasks. If that happens we'd need to improve the randomness of the
 * process id (e.g. via /dev/urandom or similar).
 *
 * This usage of the processes table also explains why there is no
 * updateObject() method in this DAO. If you need a process with different
 * characteristics then insert a new one and delete stale processes.
 */

// Define the max number of seconds a process is allowed to run.
define('PROCESS_MAX_EXECUTION_TIME', 900);

// Cap the max. number of parallel process to avoid server flooding.
define('PROCESS_MAX_PARALLELISM', 20);

// The max. number of seconds a one-time-key will be kept valid.
define('PROCESS_MAX_KEY_VALID', 10);

import('lib.pkp.classes.process.Process');

class ProcessDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ProcessDAO() {
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
     * Insert a new process.
     * @param int $processType
     * @param int $maxParallelism
     * @return Process|bool
     */
    public function insertObject($processType, $maxParallelism) {
        // Free processing fields occupied by zombie processes.
        $this->deleteZombies();

        // Cap the parallelism to the max. parallelism.
        $maxParallelism = min($maxParallelism, PROCESS_MAX_PARALLELISM);

        // Check whether we're allowed to spawn another process.
        $currentParallelism = $this->getNumberOfObjectsByProcessType($processType);
        if ($currentParallelism >= $maxParallelism) {
            return false;
        }

        /** @var Process $process */
        $process = $this->newDataObject();
        $process->setProcessType($processType);

        // Generate a new process ID. See classdoc for process ID requirements.
        $process->setId(uniqid('', true));

        // Generate the timestamp.
        $process->setTimeStarted(time());

        // Persist the process.
        $this->update(
            'INSERT INTO processes (process_id, process_type, time_started, obliterated) VALUES (?, ?, ?, 0)',
            [
                $process->getId(),
                $process->getProcessType(),
                $process->getTimeStarted()
            ]
        );
        
        $process->setObliterated(false);
        return $process;
    }

    /**
     * Get a process by ID.
     * @param string $processId
     * @return Process|null
     */
    public function getObjectById($processId) {
        $result = $this->retrieve(
            'SELECT process_id, process_type, time_started, obliterated FROM processes WHERE process_id = ?',
            [(string) $processId]
        );

        $process = null;
        if ($result && !$result->EOF) {
            $process = $this->_fromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }

        return $process;
    }

    /**
     * Determine the number of currently running processes for a given process type.
     * @param int $processType
     * @return int
     */
    public function getNumberOfObjectsByProcessType($processType) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS running_processes FROM processes WHERE process_type = ?',
            [(int) $processType]
        );

        $runningProcesses = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $runningProcesses = (int) $row['running_processes'];
        }
        if ($result) {
            $result->Close();
        }
        
        return $runningProcesses;
    }

    /**
     * Delete a process.
     * @param Process $process
     * @return bool
     */
    public function deleteObject($process) {
        return $this->deleteObjectById($process->getId());
    }

    /**
     * Delete a process by ID.
     * @param string $processId
     * @return bool
     */
    public function deleteObjectById($processId) {
        assert(!empty($processId));
        return (bool) $this->update('DELETE FROM processes WHERE process_id = ?', [(string) $processId]);
    }

    /**
     * Delete stale processes.
     * @param bool $force
     * @return bool
     * @see PROCESS_MAX_EXECUTION_TIME
     */
    public function deleteZombies($force = false) {
        static $zombiesDeleted = false;
        if ($zombiesDeleted && !$force) {
            return true;
        }
        $zombiesDeleted = true;

        $maxTimestamp = time() - PROCESS_MAX_EXECUTION_TIME;
        return (bool) $this->update(
            'DELETE FROM processes WHERE time_started < ?',
            [(int) $maxTimestamp]
        );
    }

    /**
     * Spawn new processes via web requests.
     * @param object $request
     * @param string $handler A fully qualified handler class name
     * @param string $op The operation to be called on the handler
     * @param int $processType One of the PROCESS_TYPE_* constants
     * @param int $noOfProcesses The number of processes to be spawned
     * @return int The actual number of spawned processes
     */
    public function spawnProcesses($request, $handler, $op, $processType, $noOfProcesses) {
        $urlParts = $this->_parseProcessUrl($request, $handler, $op);
        if ($urlParts === null) {
            return 0;
        }
    
        [$transport, $port] = $this->_getTransportSettings($urlParts);
        $this->deleteZombies();
    
        $noOfProcesses = min($noOfProcesses, PROCESS_MAX_PARALLELISM);
        $currentParallelism = $this->getNumberOfObjectsByProcessType($processType);
    
        $spawnedProcesses = 0;
        while ($currentParallelism < $noOfProcesses) {
            $process = $this->insertObject($processType, $noOfProcesses);
            if (!($process instanceof Process)) {
                break;
            }
    
            $success = $this->_spawnSingleProcess(
                $transport,
                $urlParts['host'],
                $port,
                $urlParts,
                $process->getId()
            );
    
            if (!$success) {
                error_log("ProcessDAO: Failed to spawn process {$process->getId()}");
            }
    
            $currentParallelism++;
            $spawnedProcesses++;
        }
    
        return $spawnedProcesses;
    }
    
    /**
     * Parse and validate the process URL.
     * @param object $request
     * @param string $handler
     * @param string $op
     * @return array|null URL parts or null if invalid
     */
    private function _parseProcessUrl($request, $handler, $op) {
        $router = $request->getRouter();
        $dispatcher = $router->getDispatcher();
        $processUrl = $dispatcher->url($request, ROUTE_COMPONENT, null, $handler, $op);
    
        $urlParts = parse_url($processUrl);
        
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

        $stream = @fsockopen($socketAddress, $port, $errno, $errstr, 5);
        if ($stream === false) {
            error_log("ProcessDAO: Failed to open socket to {$socketAddress}:{$port} - Error {$errno}: {$errstr}");
            return false;
        }

        try {
            $httpRequest = $this->_buildHttpRequest($urlParts, $oneTimeKey);
            $bytesWritten = fwrite($stream, $httpRequest);
            
            if ($bytesWritten === false) {
                error_log("ProcessDAO: Failed to write to socket");
                return false;
            }

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
        $query = $urlParts['query'] ?? '';
        $encodedKey = urlencode($oneTimeKey);

        $separator = empty($query) ? '?' : '&';
        $fullPath = $path . (!empty($query) ? '?' . $query : '') . $separator . 'authToken=' . $encodedKey;

        return "GET {$fullPath} HTTP/1.1\r\n"
             . "Host: {$host}\r\n"
             . "User-Agent: Frontedge-Scholar-Wizdam\r\n"
             . "Connection: Close\r\n\r\n";
    }

    /**
     * Check the one-time-key of a process. If the key has not been checked 
     * before then this call will mark it as used.
     * @param string $processId The unique process ID
     * @return bool
     */
    public function authorizeProcess($processId) {
        $process = $this->getObjectById($processId);
        if ($process instanceof Process && $process->getObliterated() === false) {
            $success = $this->update(
                'UPDATE processes SET obliterated = 1 WHERE process_id = ?',
                [(string) $processId]
            );
            if (!$success) {
                return false;
            }

            $minTimestamp = time() - PROCESS_MAX_KEY_VALID;
            $authorized = ($process->getTimeStarted() > $minTimestamp);

            if (!$authorized) {
                $this->deleteObjectById($processId);
            }

            return $authorized;
        }

        return false;
    }

    /**
     * Check whether a process identified by its ID can continue to run.
     * @param string $processId
     * @return bool
     */
    public function canContinue($processId) {
        $minTimestamp = time() - PROCESS_MAX_EXECUTION_TIME;
        $process = $this->getObjectById($processId);
        $canContinue = ($process instanceof Process && $process->getTimeStarted() > $minTimestamp);
        if (!$canContinue) {
            $this->deleteObjectById($processId);
        }

        return $canContinue;
    }

    /**
     * Instantiate and return a new data object.
     * @return Process
     */
    public function newDataObject() {
        return new Process();
    }

    //
    // Private helper methods
    //
    
    /**
     * Internal function to return a process object from a row.
     * @param array $row
     * @return Process
     */
    public function _fromRow($row) {
        /** @var Process $process */
        $process = $this->newDataObject();
        
        $process->setId((string) $row['process_id']);
        $process->setProcessType((int) $row['process_type']);
        $process->setTimeStarted((int) $row['time_started']);
        $process->setObliterated((bool) $row['obliterated']);
        
        return $process;
    }
    
}
?>