<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/users/UserXMLParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserXMLParser
 * @ingroup plugins_importexport_users
 *
 * @brief Class to import and export user data from an XML format.
 * See dbscripts/xml/dtd/users.dtd for the XML schema used.
 */

import('lib.pkp.classes.xml.XMLParser');

class UserXMLParser {

    /** @var XMLParser the parser to use */
    public XMLParser $parser;

    /** @var array ImportedUsers users to import */
    public array $usersToImport = [];

    /** @var array ImportedUsers imported users */
    public array $importedUsers = [];

    /** @var array error messages that occurred during import */
    public array $errors = [];

    /** @var int the ID of the journal to import users into */
    public int $journalId;

    /**
     * Constructor.
     * @param int $journalId assumed to be a valid journal ID
     */
    public function __construct(int $journalId) {
        $this->parser = new XMLParser();
        $this->journalId = $journalId;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserXMLParser() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Parse an XML users file into a set of users to import.
     * @param string $file path to the XML file to parse
     * @return array ImportedUsers the collection of users read from the file
     */
    public function parseData(string $file): array {
        $roleDao = DAORegistry::getDAO('RoleDAO');

        $this->usersToImport = [];
        $tree = $this->parser->parse($file);

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($this->journalId);
        $journalPrimaryLocale = AppLocale::getPrimaryLocale();

        $site = Request::getSite();
        $siteSupportedLocales = $site->getSupportedLocales();

        if ($tree !== false) {
            foreach ($tree->getChildren() as $user) {
                if ($user->getName() == 'user') {
                    // Match user element
                    $newUser = new ImportedUser();

                    foreach ($user->getChildren() as $attrib) {
                        switch ($attrib->getName()) {
                            case 'username':
                                // Usernames must be lowercase
                                $newUser->setUsername(strtolower_codesafe($attrib->getValue()));
                                break;
                            case 'password':
                                $newUser->setMustChangePassword($attrib->getAttribute('change') == 'true' ? 1 : 0);
                                $encrypted = $attrib->getAttribute('encrypted');
                                if (isset($encrypted) && $encrypted !== 'plaintext') {
                                    $ojsEncryptionScheme = Config::getVar('security', 'encryption');
                                    if ($encrypted != $ojsEncryptionScheme) {
                                        $this->errors[] = __('plugins.importexport.users.import.encryptionMismatch', ['importHash' => $encrypted, 'ojsHash' => $ojsEncryptionScheme]);
                                    }
                                    $newUser->setPassword($attrib->getValue());
                                } else {
                                    $newUser->setUnencryptedPassword($attrib->getValue());
                                }
                                break;
                            case 'salutation':
                                $newUser->setSalutation($attrib->getValue());
                                break;
                            case 'first_name':
                                $newUser->setFirstName($attrib->getValue());
                                break;
                            case 'middle_name':
                                $newUser->setMiddleName($attrib->getValue());
                                break;
                            case 'last_name':
                                $newUser->setLastName($attrib->getValue());
                                break;
                            case 'initials':
                                $newUser->setInitials($attrib->getValue());
                                break;
                            case 'gender':
                                $newUser->setGender($attrib->getValue());
                                break;
                            case 'affiliation':
                                $locale = $attrib->getAttribute('locale');
                                if (empty($locale)) $locale = $journalPrimaryLocale;
                                $newUser->setAffiliation($attrib->getValue(), $locale);
                                break;
                            case 'email':
                                $newUser->setEmail($attrib->getValue());
                                break;
                            case 'url':
                                $newUser->setUrl($attrib->getValue());
                                break;
                            case 'phone':
                                $newUser->setPhone($attrib->getValue());
                                break;
                            case 'fax':
                                $newUser->setFax($attrib->getValue());
                                break;
                            case 'mailing_address':
                                $newUser->setMailingAddress($attrib->getValue());
                                break;
                            case 'country':
                                $newUser->setCountry($attrib->getValue());
                                break;
                            case 'signature':
                                $locale = $attrib->getAttribute('locale');
                                if (empty($locale)) $locale = $journalPrimaryLocale;
                                $newUser->setSignature($attrib->getValue(), $locale);
                                break;
                            case 'interests':
                                $interests = $attrib->getValue(); // Bug #9054
                                $oldInterests = $newUser->getTemporaryInterests();
                                if ($oldInterests) $interests = $oldInterests . ',' . $interests;
                                $newUser->setTemporaryInterests($interests);
                                break;
                            case 'gossip':
                                $locale = $attrib->getAttribute('locale');
                                if (empty($locale)) $locale = $journalPrimaryLocale;
                                $newUser->setGossip($attrib->getValue(), $locale);
                                break;
                            case 'biography':
                                $locale = $attrib->getAttribute('locale');
                                if (empty($locale)) $locale = $journalPrimaryLocale;
                                $newUser->setBiography($attrib->getValue(), $locale);
                                break;
                            case 'locales':
                                $locales = [];
                                foreach (explode(':', $attrib->getValue()) as $locale) {
                                    if (AppLocale::isLocaleValid($locale) && in_array($locale, $siteSupportedLocales)) {
                                        $locales[] = $locale;
                                    }
                                }
                                $newUser->setLocales($locales);
                                break;
                            case 'role':
                                $roleType = $attrib->getAttribute('type');
                                if ($this->validRole($roleType)) {
                                    $role = new Role();
                                    $role->setRoleId($roleDao->getRoleIdFromPath($roleType));
                                    $newUser->addRole($role);
                                }
                                break;
                        }
                    }
                    $this->usersToImport[] = $newUser;
                }
            }
        }

        return $this->usersToImport;
    }

    /**
     * Import the parsed users into the system.
     * @param bool $sendNotify send an email notification to each imported user containing their username and password
     * @param bool $continueOnError continue to import remaining users if a failure occurs
     * @return bool success
     */
    public function importUsers(bool $sendNotify = false, bool $continueOnError = false): bool {
        $success = true;
        $this->importedUsers = [];
        $this->errors = [];

        $userDao = DAORegistry::getDAO('UserDAO');
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $mail = null;

        if ($sendNotify) {
            // Set up mail template to send to added users
            import('classes.mail.MailTemplate');
            $mail = new MailTemplate('USER_REGISTER');

            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($this->journalId);
            $mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
        }

        $count = count($this->usersToImport);
        for ($i = 0; $i < $count; $i++) {
            $user = $this->usersToImport[$i];
            
            // If the email address already exists in the system,
            // then assign the user the username associated with that email address.
            if ($user->getEmail() != null) {
                $emailExists = $userDao->getUserByEmail($user->getEmail(), true);
                if ($emailExists != null) {
                    $user->setUsername($emailExists->getUsername());
                }
            }
            
            if ($user->getUsername() == null) {
                $newUsername = true;
                $this->generateUsername($user);
            } else {
                $newUsername = false;
            }
            
            if ($user->getUnencryptedPassword() != null) {
                $user->setPassword(Validation::encryptCredentials($user->getUsername(), $user->getUnencryptedPassword()));
            } elseif ($user->getPassword() == null) {
                $this->generatePassword($user);
            }

            $userExists = null;
            if (!$newUsername) {
                // Check if user already exists
                $userExists = $userDao->getByUsername($user->getUsername(), true);
                if ($userExists != null) {
                    $user->setId($userExists->getId());
                }
            } else {
                $userExists = false;
            }

            if ($newUsername || !$userExists) {
                // Create new user account
                // If the user's username was specified in the data file and
                // the username already exists, only the new roles are added for that user
                if (!$userDao->insertUser($user)) {
                    // Failed to add user!
                    $this->errors[] = sprintf('%s: %s (%s)',
                        __('manager.people.importUsers.failedToImportUser'),
                        $user->getFullName(), $user->getUsername());

                    if ($continueOnError) {
                        // Skip to next user
                        $success = false;
                        continue;
                    } else {
                        return false;
                    }
                }
            }

            // Add reviewing interests to interests table
            $interestDao = DAORegistry::getDAO('InterestDAO');
            $interests = $user->getTemporaryInterests();
            if ($interests) {
                $interestArray = explode(',', $interests);
                $interestArray = array_map('trim', $interestArray); // Trim leading whitespace
                if (is_array($interestArray) && !empty($interestArray)) {
                    $interestDao->setUserInterests($interestArray, $user->getId());
                }
            }

            // Enroll user in specified roles
            // If the user is already enrolled in a role, that role is skipped
            foreach ($user->getRoles() as $role) {
                $role->setUserId($user->getId());
                $role->setJournalId($this->journalId);
                if (!$roleDao->userHasRole($role->getJournalId(), $role->getUserId(), $role->getRoleId())) {
                    if (!$roleDao->insertRole($role)) {
                        // Failed to add role!
                        $this->errors[] = sprintf('%s: %s - %s (%s)',
                            __('manager.people.importUsers.failedToImportRole'),
                            $role->getRoleName(),
                            $user->getFullName(), $user->getUsername());

                        if ($continueOnError) {
                            // Continue to insert other roles for this user
                            $success = false;
                            continue;
                        } else {
                            return false;
                        }
                    }
                }
            }

            if ($sendNotify && !$userExists && $mail) {
                // Send email notification to user as if user just registered themselves
                $mail->addRecipient($user->getEmail(), $user->getFullName());
                $mail->sendWithParams([
                    'journalName' => $journal->getTitle($journal->getPrimaryLocale()),
                    'username' => $user->getUsername(),
                    'password' => $user->getUnencryptedPassword() == null ? '-' : $user->getUnencryptedPassword(),
                    'userFullName' => $user->getFullName()
                ]);
                $mail->clearRecipients();
            }

            $this->importedUsers[] = $user;
        }

        return $success;
    }

    /**
     * Return the set of parsed users.
     * @return array ImportedUsers
     */
    public function getUsersToImport(): array {
        return $this->usersToImport;
    }

    /**
     * Specify the set of parsed users.
     * @param array $users ImportedUsers
     * @return void
     */
    public function setUsersToImport(array $users): void {
        $this->usersToImport = $users;
    }

    /**
     * Return the set of users who were successfully imported.
     * @return array ImportedUsers
     */
    public function getImportedUsers(): array {
        return $this->importedUsers;
    }

    /**
     * Return an array of error messages that occurred during the import.
     * @return array string
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Check if a role type value identifies a valid role that can be imported.
     * Note we do not allow users to be imported into the "admin" role.
     * @param string $roleType
     * @return bool
     */
    public function validRole(?string $roleType): bool {
        return isset($roleType) && in_array($roleType, ['manager', 'editor', 'sectionEditor', 'layoutEditor', 'reviewer', 'copyeditor', 'proofreader', 'author', 'reader', 'subscriptionManager']);
    }

    /**
     * Generate a unique username for a user based on the user's name.
     * @param ImportedUser $user the user to be modified by this function
     */
    public function generateUsername(ImportedUser $user): void {
        $userDao = DAORegistry::getDAO('UserDAO');
        $baseUsername = PKPString::regexp_replace('/[^A-Z0-9]/i', '', $user->getLastName());
        if (empty($baseUsername)) {
            $baseUsername = PKPString::regexp_replace('/[^A-Z0-9]/i', '', $user->getFirstName());
        }
        if (empty($baseUsername)) {
            // Default username if we can't use the user's last or first name
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $i = 1;
        while ($userDao->userExistsByUsername($username, true)) {
            $username = $baseUsername . $i;
            $i++;
        }
        $user->setUsername($username);
    }

    /**
     * Generate a random password for a user.
     * @param ImportedUser $user the user to be modified by this function
     */
    public function generatePassword(ImportedUser $user): void {
        $password = Validation::generatePassword();
        $user->setUnencryptedPassword($password);
        $user->setPassword(Validation::encryptCredentials($user->getUsername(), $password));
    }
}


/**
 * Helper class representing a user imported from a user data file.
 */
import('classes.user.User');

class ImportedUser extends User {

    /** @var array Roles of this user */
    public array $roles = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->roles = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ImportedUser() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Set the unencrypted form of the user's password.
     * @param string $unencryptedPassword
     * @return void
     */
    public function setUnencryptedPassword(string $unencryptedPassword): void {
        $this->setData('unencryptedPassword', $unencryptedPassword);
    }

    /**
     * Get the user's unencrypted password.
     * @return string|null
     */
    public function getUnencryptedPassword(): ?string {
        return $this->getData('unencryptedPassword');
    }

    /**
     * Add a new role to this user.
     * @param Role $role
     * @return void
     */
    public function addRole(Role $role): void {
        $this->roles[] = $role;
    }

    /**
     * Get this user's roles.
     * @return array Roles
     */
    public function getRoles(): array {
        return $this->roles;
    }

    /**
     * Set the interests to be inserted after we have a user ID
     * @param string $interests
     * @return void
     */
    public function setTemporaryInterests(string $interests): void {
        $this->setData('interests', $interests);
    }

    /**
     * Get the interests to be inserted after we have a user ID
     * @return string|null
     */
    public function getTemporaryInterests(): ?string {
        return $this->getData('interests');
    }
}

?>