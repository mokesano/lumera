<?php
declare(strict_types=1);

/**
 * @file classes/session/SessionManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SessionManager
 * @ingroup session
 *
 * @brief Implements PHP methods for a custom session storage handler 
 * (see http://php.net/session).
 */

class SessionManager {

    /** @var object The DAO for accessing Session objects */
    public $sessionDao;

    /** @var object The Session associated with the current request */
    public $userSession;

    /** @var array Parameter cookie session (Lifetime, Path, Domain, Secure, dll) */
    protected $cookieParams;

    /**
     * Constructor.
     */
    public function __construct($sessionDao, $request) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_abort();
        }

        $this->sessionDao = $sessionDao;

        // --- PERBAIKAN V12: PROXY SSL ---
        // JANGAN percaya $request->getProtocol(). Percayai config.inc.php
        // Ini adalah satu-satunya cara untuk menangani proxy/load balancer.
        $isSecure = (strtolower(substr(Config::getVar('general', 'base_url'), 0, 5)) == 'https');
        // --- AKHIR PERBAIKAN V12 ---

        // (Kode dari v10/v11 yang sudah benar, membaca config atau menebak)
        $cookiePath = Config::getVar('general', 'session_cookie_path');
        $cookieDomain = Config::getVar('general', 'session_cookie_domain');

        if (empty($cookiePath)) $cookiePath = $request->getBasePath() . '/';
        
        if (empty($cookieDomain)) {
            $cookieDomain = $request->getServerHost(null, false);
            // Validasi domain agar tidak crash pada localhost atau IP raw
            if ($cookieDomain == 'localhost' || filter_var($cookieDomain, FILTER_VALIDATE_IP)) {
                $cookieDomain = false; 
            }
        }
        
        $sessionLifetime = (int) Config::getVar('general', 'session_lifetime');
        $cookieLifetimeInSeconds = ($sessionLifetime == 0) ? 0 : $sessionLifetime * 86400;

        $this->cookieParams = [
            'lifetime' => $cookieLifetimeInSeconds,
            'path' => $cookiePath,
            'domain' => $cookieDomain,
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        // --- Akhir v10/v11 ---

        session_set_cookie_params($this->cookieParams);

        ini_set('session.use_trans_sid', (string) 0);
        ini_set('session.serialize_handler', 'php');
        ini_set('session.use_cookies', (string) 1);
        ini_set('session.name', (string) Config::getVar('general', 'session_cookie_name'));
        ini_set('session.gc_probability', (string) 1);
        ini_set('session.gc_maxlifetime', (string) (60 * 60));
        ini_set('session.auto_start', (string) 0); 
        ini_set('session.cache_limiter', 'none');

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // [WIZDAM FIX] Gunakan register_shutdown_function, bukan __destruct
        // shutdown function dipanggil sebelum destruksi objek,
        // sehingga DB connection masih hidup saat session ditulis
        register_shutdown_function('session_write_close');
        
        $sessionId = session_id();
        $ip = $request->getRemoteAddr();
        $userAgent = $request->getUserAgent();
        $now = time();

        // Check domain consistency
        if (isset($this->userSession) && $this->userSession->getDomain() && $this->userSession->getDomain() != $request->getServerHost(null, false)) {
            if (strtolower(substr($request->getServerHost(null, false), -1 - strlen($this->userSession->getDomain()))) == '.'.strtolower($this->userSession->getDomain())) {
                ini_set('session.cookie_domain', $this->userSession->getDomain());
            }
        }

        // (Ini adalah blok perbaikan Duplicate Entry & v11)
        if (!isset($this->userSession) || (Config::getVar('security', 'session_check_ip') && $this->userSession->getIpAddress() != $ip) || $this->userSession->getUserAgent() != substr($userAgent, 0, 255)) {
            
            if (isset($this->userSession)) {
                $this->sessionDao->deleteSessionById($this->userSession->getId());
                session_regenerate_id(true); 
                $sessionId = session_id(); 
                unset($this->userSession);
                
                // (Perbaikan v11: menimpa cookie cacat)
                $this->updateSessionCookie($sessionId); 
            }

            $this->userSession = $this->sessionDao->newDataObject();
            $this->userSession->setId($sessionId);
            $this->userSession->setIpAddress($ip);
            $this->userSession->setUserAgent($userAgent);
            $this->userSession->setSecondsCreated($now);
            $this->userSession->setSecondsLastUsed($now);
            $this->userSession->setDomain($this->cookieParams['domain']);
            $this->userSession->setSessionData('');

            $this->sessionDao->insertSession($this->userSession);

        } else {
            if ($this->userSession->getRemember()) {
                if (Config::getVar('general', 'session_lifetime') > 0) {
                    $this->updateSessionLifetime(time() + Config::getVar('general', 'session_lifetime') * 86400);
                } else {
                    $this->userSession->setRemember(0);
                    $this->updateSessionLifetime(0);
                }
            }
            $this->userSession->setSecondsLastUsed($now);
        }

        // [WIZDAM ARCHITECTURE] 
        // MATIKAN INI. Kita menggunakan __destruct() di bawah.
        // register_shutdown_function('session_write_close'); 
    }

    /**
     * [WIZDAM ARCHITECTURE FIX]
     * Destructor: Ensures session write happens before DB connection closes.
     */
    public function __destruct() {
        // sudah didaftarkan via register_shutdown_function di constructor
        //session_write_close();  // ← DIMATIKAN (benar)
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SessionManager($sessionDao, $request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::SessionManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($sessionDao, $request);
    }

    /**
     * Return an instance of the session manager.
     * @return SessionManager
     */
    public static function getManager() {
        $instance = Registry::get('sessionManager', true, null);

        if (is_null($instance)) {
            $application = Registry::get('application');
            // [WIZDAM REFACTOR] assert() → instanceof
            if ($application instanceof PKPApplication) {
                $request = $application->getRequest();
            } else {
                $request = PKPApplication::getRequest();
            }

            // Implicitly set session manager by ref in the registry
            $instance = new SessionManager(DAORegistry::getDAO('SessionDAO'), $request);
            Registry::set('sessionManager', $instance);
        }

        return $instance;
    }

    /**
     * Get the session associated with the current request.
     * @return Session
     */
    public function getUserSession() {
        return $this->userSession;
    }

    /**
     * Open a session.
     * Does nothing; only here to satisfy PHP session handler requirements.
     * @return boolean
     */
    public function open() {
        return true;
    }

    /**
     * Close a session.
     * Does nothing; only here to satisfy PHP session handler requirements.
     * @return boolean
     */
    public function close() {
        return true;
    }

    /**
     * Read session data from database.
     * @param $sessionId string
     * @return string
     */
    public function read($sessionId) {
        if (!isset($this->userSession)) {
            // Hapus '&'
            $this->userSession = $this->sessionDao->getSession($sessionId);
            if (isset($this->userSession)) {
                $data = $this->userSession->getSessionData();
            }
        }
        return isset($data) ? $data : '';
    }

    /**
     * Save session data to database.
     * @param $sessionId string
     * @param $data array
     * @return boolean
     */
    public function write($sessionId, $data) {
        if (isset($this->userSession)) {
            $this->userSession->setSessionData($data);
            return $this->sessionDao->updateObject($this->userSession);
        } else {
            return true;
        }
    }

    /**
     * Destroy (delete) a session.
     * @param $sessionId string
     * @return boolean
     */
    public function destroy($sessionId) {
        return $this->sessionDao->deleteSessionById($sessionId);
    }

    /**
     * Garbage collect unused session data.
     * @param $maxlifetime int
     * @return boolean
     */
    public function gc($maxlifetime) {
        return $this->sessionDao->deleteSessionByLastUsed(time() - 86400, Config::getVar('general', 'session_lifetime') <= 0 ? 0 : time() - Config::getVar('general', 'session_lifetime') * 86400);
    }

    /**
     * Resubmit the session cookie.
     * (Dimodernisasi v9 untuk menangani lifetime dari config)
     * @param $sessionId string new session ID (or false to keep current ID)
     * @param $expireTime int new expiration time in seconds (0 = current session)
     * @return boolean
     */
    public function updateSessionCookie($sessionId = false, $expireTime = 0) {
        $params = $this->cookieParams;

        // Tentukan timestamp kedaluwarsa
        $expiresTimestamp = 0; // Default = sesi browser
        
        if ($expireTime != 0) {
            // $expireTime disediakan (misal: time() + 30 hari)
            $expiresTimestamp = $expireTime;
        } else if ($params['lifetime'] != 0) {
            // Gunakan durasi dari config (misal: 30 hari * 86400 detik)
            $expiresTimestamp = time() + $params['lifetime'];
        }

        return setcookie(
            session_name(),
            ($sessionId === false) ? session_id() : $sessionId,
            [
                'expires' => $expiresTimestamp, // Timestamp kedaluwarsa
                'path' => $params['path'],
                'domain' => $params['domain'], // Domain induk
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite']
            ]
        );
    }

    /**
     * Regenerate the session ID for the current user session.
     * @return boolean
     */
    public function regenerateSessionId() {
        $success = false;
        
        // Ambil ID sesi SAAT INI (sebelum regenerasi) untuk dihapus dari DB
        $currentSessionId = session_id();

        // Pastikan session sudah start
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Coba regenerasi ID.
        if (@session_regenerate_id() && isset($this->userSession)) {
            
            // Hapus data sesi LAMA dari database
            $this->sessionDao->deleteSessionById($currentSessionId);
            
            // Setel ID sesi BARU pada objek sesi
            $this->userSession->setId(session_id());
            
            // Simpan data sesi BARU ke database
            $this->sessionDao->insertSession($this->userSession);
            
            // Perbarui cookie di browser pengguna
            $this->updateSessionCookie(); 
            
            $success = true;
        
        } else if (!isset($this->userSession)) {
            $success = false;
        }

        return $success;
    }

    /**
     * Change the lifetime of the current session cookie.
     * @param $expireTime int new expiration time in seconds (0 = current session)
     * @return boolean
     */
    public function updateSessionLifetime($expireTime = 0) {
        return $this->updateSessionCookie(false, $expireTime);
    }
    
    /**
     * Memperbarui sesi pengguna saat login (Regenerasi ID, Set Data, dan Simpan ke DB).
     * Metode ini menjamin data sesi tersimpan atomic sebelum redirect.
     * * @param $userId int
     * @param $username string
     * @param $remember boolean
     */
    public function renewUserSession($userId, $username, $remember) {
        // 1. Regenerasi ID Sesi (Standar Keamanan)
        $this->regenerateSessionId();

        // 2. Set Data pada Objek Sesi di Memori
        $this->userSession->setUserId($userId);
        $this->userSession->setSessionVar('username', $username);
        $this->userSession->setRemember($remember);

        if ($remember && Config::getVar('general', 'session_lifetime') > 0) {
            $this->updateSessionLifetime(time() + Config::getVar('general', 'session_lifetime') * 86400);
        }

        // 3. Explicit Persistence (Simpan ke Database)
        // Manager bertanggung jawab menyimpan state yang dia kelola.
        $this->sessionDao->updateObject($this->userSession);
    }
}

?>