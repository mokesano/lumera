<?php
declare(strict_types=1);

/**
 * @file classes/db/DBConnection.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DBConnection
 * @ingroup db
 *
 * @brief Class for accessing the low-level database connection.
 * Currently integrated with ADOdb (from http://adodb.sourceforge.net).
 */

class DBConnection {
    
    /** @var DBConnection|null Singleton Instance */
    private static ?DBConnection $instance = null;

    /** @var \ADOConnection|null The underlying database connection object */
    public $dbconn;

    /** @var string Database driver */
    public $driver;

    /** @var string Database host */
    public $host;

    /** @var string Database username */
    public $username;

    /** @var string Database password */
    public $password;

    /** @var string Database name */
    public $databaseName;

    /** @var bool Use persistent connections */
    public $persistent;

    /** @var string|null Character set to use for the connection */
    public $connectionCharset;

    /** @var bool Force a new connection */
    public $forceNew;

    /** @var bool Establish connection on initiation */
    public $connectOnInit;

    /** @var bool Enable debugging output */
    public $debug;

    /** @var bool Indicate connection status */
    public $connected;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->connected = false;

        if (func_num_args() === 0) {
            $this->initDefaultDBConnection();
        } else {
            $args = func_get_args();
            call_user_func_array([$this, 'initCustomDBConnection'], $args);
        }
    }

    /**
     * [SHIM] Backward compatibility.
     */
    public function DBConnection() {
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
     * Create new database connection with the connection parameters from
     * the system configuration.
     * @return bool
     */
    public function initDefaultDBConnection() {
        $this->driver = (string) Config::getVar('database', 'driver');
        $this->host = (string) Config::getVar('database', 'host');
        $this->username = (string) Config::getVar('database', 'username');
        $this->password = (string) Config::getVar('database', 'password');
        $this->databaseName = (string) Config::getVar('database', 'name');
        $this->persistent = (bool) Config::getVar('database', 'persistent');
        $this->connectionCharset = Config::getVar('i18n', 'connection_charset') ?: null;
        $this->debug = (bool) Config::getVar('database', 'debug');
        $this->connectOnInit = true;
        $this->forceNew = false;

        return $this->initConn();
    }

    /**
     * Create new database connection with the specified connection parameters.
     * @param string $driver
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $databaseName
     * @param bool $persistent use persistent connections
     * @param string|null $connectionCharset character set to use for the connection
     * @param bool $connectOnInit establish database connection on initiation
     * @param bool $debug enable verbose debug output
     * @param bool $forceNew force a new connection
     * @return bool
     */
    public function initCustomDBConnection(
        string $driver, 
        string $host, 
        string $username, 
        string $password, 
        string $databaseName, 
        bool $persistent = false, 
        ?string $connectionCharset = null, 
        bool $connectOnInit = true, 
        bool $debug = false, 
        bool $forceNew = false
    ) {
        $this->driver = $driver;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->databaseName = $databaseName;
        $this->persistent = $persistent;
        $this->connectionCharset = $connectionCharset;
        $this->connectOnInit = $connectOnInit;
        $this->debug = $debug;
        $this->forceNew = $forceNew;

        return $this->initConn();
    }

    /**
     * Initialize database connection object and establish connection to the database.
     * @return bool
     */
    public function initConn() {
        if (!function_exists('\ADONewConnection')) {
            require_once('lib/pkp/lib/adodb/adodb.inc.php');
        }

        // Internal coercion: Call global ADOdb factory function
        $this->dbconn = \ADONewConnection($this->driver);

        if ($this->connectOnInit) {
            return $this->connect();
        }
        
        return true;
    }

    /**
     * Establish connection to the database.
     * @return bool
     */
    public function connect() {
        if (!$this->dbconn) {
            return false;
        }

        if ($this->persistent) {
            $this->connected = (bool) @$this->dbconn->PConnect(
                $this->host,
                $this->username,
                $this->password,
                $this->databaseName
            );
        } else {
            $this->connected = (bool) @$this->dbconn->Connect(
                $this->host,
                $this->username,
                $this->password,
                $this->databaseName,
                $this->forceNew
            );
        }

        if ($this->debug) {
            $this->dbconn->debug = true;
        }

        if ($this->connected && $this->connectionCharset) {
            $this->dbconn->SetCharSet($this->connectionCharset);
        }

        return $this->connected;
    }

    /**
     * Disconnect from the database.
     */
    public function disconnect(): void {
        if ($this->connected && $this->dbconn) {
            $this->dbconn->Disconnect();
            $this->connected = false;
        }
    }

    /**
     * Reconnect to the database.
     * @param bool $forceNew force a new connection
     * @return bool
     */
    public function reconnect(bool $forceNew = false) {
        $this->disconnect();
        if ($forceNew) {
            $this->persistent = false;
        }
        $this->forceNew = $forceNew;
        return $this->connect();
    }

    /**
     * Return the database connection object.
     * @return \ADOConnection|null
     */
    public function getDBConn() {
        return $this->dbconn;
    }

    /**
     * Check if a database connection has been established.
     * 
     * @return bool
     */
    public function isConnected() {
        return $this->connected;
    }

    /**
     * Get number of database queries executed.
     * @return int
     */
    public function getNumQueries() {
        return $this->dbconn ? (int) $this->dbconn->numQueries : 0;
    }

    /** @var int[] MySQL error codes that indicate a dead/stale connection worth retrying. */
    private const TRANSIENT_CONNECTION_ERRORS = [
        2006, // CR_SERVER_GONE_ERROR ("MySQL server has gone away")
        2013, // CR_SERVER_LOST ("Lost connection to MySQL server during query")
        2003, // CR_CONNECTION_ERROR
    ];

    /**
     * Proactively verify the current DB connection is alive and reconnect if stale.
     * Designed for long-running tasks to prevent MySQL wait_timeout drops.
     * @return bool
     */
    public static function ensureConnection(): bool {
        $instance = self::getInstance();
        $conn = $instance->getDBConn();

        if (!$conn) {
            return $instance->reconnect(true);
        }

        // Lightweight liveness check. Suppress warnings as a dead connection is handled.
        $ok = @$conn->Execute('SELECT 1');
        if ($ok !== false) {
            return true;
        }

        $errno = (int) $conn->ErrorNo();
        if ($errno === 0 || in_array($errno, self::TRANSIENT_CONNECTION_ERRORS, true)) {
            // [LUMERA FIX] Explicit (string) cast on ErrorMsg() to satisfy strict linters 
            // that flag ADOdb's loosely typed return values in string concatenation.
            error_log('DBConnection: connection appears stale (errno=' . $errno . ', ' . (string) $conn->ErrorMsg() . '), reconnecting.');
            return $instance->reconnect(true);
        }

        // Non-connection-related error from the liveness query. Report as unusable.
        return false;
    }

    /**
     * Run a database operation with automatic reconnect-and-retry on a
     * transient "gone away" / "lost connection" error.
     * @param callable $operation
     * @param int $maxAttempts
     * @return mixed
     */
    public static function executeWithRetry(callable $operation, int $maxAttempts = 2) {
        $attempt = 0;
        $result = false;

        while ($attempt < $maxAttempts) {
            $attempt++;
            $result = $operation();

            if ($result !== false) {
                return $result;
            }

            $conn = self::getConn();
            $errno = $conn ? (int) $conn->ErrorNo() : 0;

            if (!in_array($errno, self::TRANSIENT_CONNECTION_ERRORS, true)) {
                // Genuine query/business error, or no connection-level error at all
                // (e.g. operation() itself returned false for its own reasons) —
                // not a transient connection issue, so don't retry.
                return $result;
            }

            if ($attempt >= $maxAttempts) {
                break;
            }

            error_log('DBConnection: transient error ' . $errno . ' on attempt ' . $attempt . '/' . $maxAttempts . ', reconnecting and retrying.');
            self::getInstance()->reconnect(true);
        }

        return $result;
    }

    /**
     * Return a reference to a single static instance of the database connection manager.
     * @param DBConnection|null $setInstance
     * @return DBConnection
     */
    public static function getInstance(?DBConnection $setInstance = null) {
        if ($setInstance instanceof DBConnection) {
            self::$instance = $setInstance;
            Registry::set('dbInstance', $setInstance);
            return self::$instance;
        }

        if (self::$instance !== null) {
            return self::$instance;
        }

        $registryInstance = Registry::get('dbInstance', true, null);
        
        if ($registryInstance instanceof DBConnection) {
            self::$instance = $registryInstance;
        } else {
            self::$instance = new DBConnection();
            Registry::set('dbInstance', self::$instance);
        }

        return self::$instance;
    }

    /**
     * Return a reference to a single static instance of the database connection.
     * @return \ADOConnection|null
     */
    public static function getConn() {
        $conn = self::getInstance();
        return $conn ? $conn->getDBConn() : null;
    }

    /**
     * Return the name of the driver used for this connection.
     * @return string
     */
    public function getDriver() {
        return $this->driver;
    }

    /**
     * Log a SQL query and execution time in the PKPProfiler debug log.
     * @param string $sql SQL statement being run
     * @param float $start A float representing the unix microtime the query started
     * @param array $params Parameters for the prepared statement
     */
    public static function logQuery(string $sql, float $start, array $params = []) {
        if (!Config::getVar('debug', 'show_stats')) {
            return;
        }

        $queries = Registry::get('queries', true, []);

        $preparedSql = '';
        foreach (explode('?', $sql) as $key => $val) {
            if (isset($params[$key])) {
                $preparedSql .= $val . $params[$key];
            } else {
                $preparedSql .= $val;
            }
        }

        $query = [
            'sql' => $preparedSql,
            'time' => (Core::microtime() - $start) * 1000
        ];
        
        $queries[] = $query;
        Registry::set('queries', $queries);
    }
    
}
?>