<?php
declare(strict_types=1);

/**
 * @file plugins/auth/ldap/LDAPAuthPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LDAPAuthPlugin
 * @ingroup plugins_auth_ldap
 *
 * @brief LDAP authentication plugin.
 */

import('classes.plugins.AuthPlugin');

class LDAPAuthPlugin extends AuthPlugin {
    
    /** @var resource|\LDAP\Connection|null The LDAP connection */
    protected $conn = null;

    /**
     * Constructor.
     * @param array|null $settings
     * @param int|null $authId
     */
    public function __construct($settings = null, $authId = null) {
        parent::__construct($settings, $authId);
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param array|null $settings
     * @param int|null $authId
     */
    public function LDAPAuthPlugin($settings = null, $authId = null) {
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
     * Called as a plugin is registered to the registry.
     * @param string $category Name of category plugin was registered to
     * @param string $path The path the plugin was found in
     * @return bool True iff plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Return the name of this plugin.
     * @return string
     */
    public function getName(): string {
        return 'ldap';
    }

    /**
     * Return the localized name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.auth.ldap.displayName');
    }

    /**
     * Return the localized description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.auth.ldap.description');
    }

    //
    // Core Plugin Functions
    //

    /**
     * Returns an instance of the authentication plugin.
     * @param array|null $settings Settings specific to this instance.
     * @param int|null $authId Identifier for this instance.
     * @return LDAPAuthPlugin
     */
    public function getInstance($settings, $authId): LDAPAuthPlugin {
        return new LDAPAuthPlugin($settings, $authId);
    }

    /**
     * Authenticate a username and password.
     * @param string $username
     * @param string $password
     * @return bool True if authentication is successful
     */
    public function authenticate(string $username, string $password): bool {
        $valid = false;
        if ($password !== '') {
            if ($this->open()) {
                $entry = $this->getUserEntry($username);
                if ($entry && $this->conn) {
                    $userdn = @ldap_get_dn($this->conn, $entry);
                    if ($userdn !== false && $this->bind($userdn, $password)) {
                        $valid = true;
                    }
                }
                $this->close();
            }
        }
        return $valid;
    }

    //
    // Optional Plugin Functions
    //

    /**
     * Check if a username exists.
     * @param string $username
     * @return bool
     */
    public function userExists(string $username): bool {
        $exists = false;
        if ($this->open()) {
            if ($this->bind()) {
                $result = @ldap_search($this->conn, (string) ($this->settings['basedn'] ?? ''), (string) ($this->settings['uid'] ?? 'uid') . '=' . $username);
                if ($result !== false) {
                    $exists = (@ldap_count_entries($this->conn, $result) > 0);
                }
            }
            $this->close();
        }
        return $exists;
    }

    /**
     * Retrieve user profile information from the LDAP server.
     * @param object $user User to update
     * @return bool True if successful
     */
    public function getUserInfo($user): bool {
        $valid = false;
        if ($this->open()) {
            $entry = $this->getUserEntry((string) $user->getUsername());
            if ($entry && $this->conn) {
                $valid = true;
                $attr = @ldap_get_attributes($this->conn, $entry);
                if (is_array($attr)) {
                    $this->userFromAttr($user, $attr);
                }
            }
            $this->close();
        }
        return $valid;
    }

    /**
     * Store user profile information on the LDAP server.
     * @param object $user User to store
     * @return bool True if successful
     */
    public function setUserInfo($user): bool {
        $valid = false;
        if ($this->open()) {
            $entry = $this->getUserEntry((string) $user->getUsername());
            if ($entry && $this->conn) {
                $userdn = @ldap_get_dn($this->conn, $entry);
                if ($userdn !== false && $this->bind((string) ($this->settings['managerdn'] ?? ''), (string) ($this->settings['managerpwd'] ?? ''))) {
                    $attr = [];
                    $this->userToAttr($user, $attr);
                    $valid = (bool) @ldap_modify($this->conn, $userdn, $attr);
                }
            }
            $this->close();
        }
        return $valid;
    }

    /**
     * Change a user's password on the LDAP server.
     * @param string $username User to update
     * @param string $password The new password
     * @return bool
     */
    public function setUserPassword(string $username, string $password): bool {
        $success = false;
        if ($this->open()) {
            $entry = $this->getUserEntry($username);
            if ($entry && $this->conn) {
                $userdn = @ldap_get_dn($this->conn, $entry);
                if ($userdn !== false && $this->bind((string) ($this->settings['managerdn'] ?? ''), (string) ($this->settings['managerpwd'] ?? ''))) {
                    $attr = ['userPassword' => $this->encodePassword($password)];
                    $success = (bool) @ldap_modify($this->conn, $userdn, $attr);
                }
            }
            $this->close();
        }
        return $success;
    }

    /**
     * Create a user on the LDAP server.
     * @param object $user User to create
     * @return bool True if successful
     */
    public function createUser($user): bool {
        $valid = false;
        if ($this->open()) {
            if (!$this->getUserEntry((string) $user->getUsername())) {
                if ($this->bind((string) ($this->settings['managerdn'] ?? ''), (string) ($this->settings['managerpwd'] ?? ''))) {
                    $userdn = (string) ($this->settings['uid'] ?? 'uid') . '=' . $user->getUsername() . ',' . (string) ($this->settings['basedn'] ?? '');
                    $attr = [
                        'objectclass' => ['top', 'person', 'organizationalPerson', 'inetorgperson'],
                        (string) ($this->settings['uid'] ?? 'uid') => $user->getUsername(),
                        'userPassword' => $this->encodePassword((string) $user->getPassword())
                    ];
                    $this->userToAttr($user, $attr);
                    $valid = (bool) @ldap_add($this->conn, $userdn, $attr);
                }
            }
            $this->close();
        }
        return $valid;
    }

    /**
     * Delete a user from the LDAP server.
     * @param string $username User to delete
     * @return bool True if successful
     */
    public function deleteUser(string $username): bool {
        $valid = false;
        if ($this->open()) {
            $entry = $this->getUserEntry($username);
            if ($entry && $this->conn) {
                $userdn = @ldap_get_dn($this->conn, $entry);
                if ($userdn !== false && $this->bind((string) ($this->settings['managerdn'] ?? ''), (string) ($this->settings['managerpwd'] ?? ''))) {
                    $valid = (bool) @ldap_delete($this->conn, $userdn);
                }
            }
            $this->close();
        }
        return $valid;
    }

    //
    // LDAP Helper Functions
    //

    /**
     * Open connection to the server.
     * @return resource|\LDAP\Connection|false
     */
    public function open() {
        $hostname = (string) ($this->settings['hostname'] ?? '');
        $port = (int) ($this->settings['port'] ?? 389);

        if (strpos($hostname, 'ldap://') === false && strpos($hostname, 'ldaps://') === false) {
            $protocol = ($port === 636) ? 'ldaps://' : 'ldap://';
            $uri = $protocol . $hostname . ':' . $port;
            $this->conn = @ldap_connect($uri);
        } else {
            $this->conn = @ldap_connect($hostname, $port);
        }

        if ($this->conn) {
            @ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
        }
        return $this->conn;
    }

    /**
     * Close connection.
     * @return void
     */
    public function close(): void {
        if ($this->conn) {
            @ldap_unbind($this->conn);
            $this->conn = null;
        }
    }

    /**
     * Bind to a directory.
     * @param string|null $binddn Directory to bind (optional)
     * @param string|null $password Password (optional)
     * @return bool
     */
    public function bind($binddn = null, $password = null): bool {
        if (!$this->conn) {
            return false;
        }

        $binddnStr = $binddn !== null ? (string) $binddn : null;
        $passwordStr = $password !== null ? (string) $password : null;

        if (isset($this->settings['sasl']) && $this->settings['sasl']) {
            return (bool) @ldap_sasl_bind(
                $this->conn, 
                $binddnStr, 
                $passwordStr, 
                (string) ($this->settings['saslmech'] ?? ''), 
                (string) ($this->settings['saslrealm'] ?? ''), 
                (string) ($this->settings['saslauthzid'] ?? ''), 
                (string) ($this->settings['saslprop'] ?? '')
            );
        }
        return (bool) @ldap_bind($this->conn, $binddnStr, $passwordStr);
    }

    /**
     * Lookup a user entry in the directory.
     * @param string $username
     * @return resource|\LDAP\ResultEntry|false
     */
    public function getUserEntry(string $username) {
        $entry = false;
        if ($this->bind((string) ($this->settings['managerdn'] ?? ''), (string) ($this->settings['managerpwd'] ?? ''))) {
            $result = @ldap_search($this->conn, (string) ($this->settings['basedn'] ?? ''), (string) ($this->settings['uid'] ?? 'uid') . '=' . $username);
            if ($result !== false && @ldap_count_entries($this->conn, $result) === 1) {
                $entry = @ldap_first_entry($this->conn, $result);
            }
        }
        return $entry;
    }

    /**
     * Update User object from entry attributes.
     * @param object $user
     * @param array $uattr
     * @return void
     */
    public function userFromAttr($user, array $uattr): void {
        $attr = array_change_key_case($uattr, CASE_LOWER);

        $firstName = $attr['givenname'][0] ?? null;
        $lastName = $attr['sn'][0] ?? $attr['surname'][0] ?? null;
        $affiliation = $attr['o'][0] ?? $attr['organizationname'][0] ?? null;
        $email = $attr['mail'][0] ?? $attr['email'][0] ?? null;
        $phone = $attr['telephonenumber'][0] ?? null;
        $fax = $attr['facsimiletelephonenumber'][0] ?? $attr['fax'][0] ?? null;
        $mailingAddress = $attr['postaladdress'][0] ?? $attr['registeredaddress'][0] ?? null;

        /** @var PKPUser $user */
        if ($firstName) {
            $user->setFirstName((string) $firstName);
        }
        if ($lastName) {
            $user->setLastName((string) $lastName);
        }
        if ($affiliation) {
            $user->setAffiliation((string) $affiliation, AppLocale::getLocale());
        }
        if ($email) {
            $user->setEmail((string) $email);
        }
        if ($phone) {
            $user->setPhone((string) $phone);
        }
        if ($fax) {
            $user->setFax((string) $fax);
        }
        if ($mailingAddress) {
            $user->setMailingAddress((string) $mailingAddress);
        }
    }

    /**
     * Update entry attributes from User object.
     * @param object $user
     * @param array $attr
     * @return void
     */
    public function userToAttr($user, array &$attr): void {
        if ($user->getFullName()) {
            $attr['cn'] = (string) $user->getFullName();
        }
        if ($user->getFirstName()) {
            $attr['givenName'] = (string) $user->getFirstName();
        }
        if ($user->getLastName()) {
            $attr['sn'] = (string) $user->getLastName();
        }
        if ($user->getAffiliation(AppLocale::getLocale())) {
            $attr['organizationName'] = (string) $user->getAffiliation(AppLocale::getLocale());
        }
        if ($user->getEmail()) {
            $attr['mail'] = (string) $user->getEmail();
        }
        if ($user->getPhone()) {
            $attr['telephoneNumber'] = (string) $user->getPhone();
        }
        if ($user->getFax()) {
            $attr['facsimileTelephoneNumber'] = (string) $user->getFax();
        }
        if ($user->getMailingAddress()) {
            $attr['postalAddress'] = (string) $user->getMailingAddress();
        }
    }

    /**
     * Encode password for the 'userPassword' field using the specified hash.
     * @param string $password
     * @return string Hashed string (with prefix).
     */
    public function encodePassword(string $password): string {
        $hashType = (string) ($this->settings['pwhash'] ?? 'md5');
        
        switch ($hashType) {
            case 'md5':
                return '{MD5}' . base64_encode(pack('H*', md5($password)));
            case 'smd5':
                $salt = random_bytes(6);
                return '{SMD5}' . base64_encode(pack('H*', md5($password . $salt)) . $salt);
            case 'sha':
                return '{SHA}' . base64_encode(pack('H*', sha1($password)));
            case 'ssha':
                $salt = random_bytes(6);
                return '{SSHA}' . base64_encode(pack('H*', sha1($password . $salt)) . $salt);
            case 'crypt':
                return '{CRYPT}' . crypt($password, '$1$' . uniqid() . '$');
            default:
                return $password;
        }
    }

}
?>