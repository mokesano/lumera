<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/users/UserImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserImportExportPlugin
 * @ingroup plugins_importexport_users
 *
 * @brief Users import/export plugin
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

class UserImportExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path to plugin
     * @return bool True iff plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'UserImportExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.users.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.users.description');
    }

    /**
     * Display the plugin UI.
     * @param array $args
     * @param object $request
     * @return void
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager();
        parent::display($args, $request);

        $templateMgr->assign('roleOptions', [
            '' => 'manager.people.doNotEnroll',
            'manager' => 'user.role.manager',
            'editor' => 'user.role.editor',
            'sectionEditor' => 'user.role.sectionEditor',
            'layoutEditor' => 'user.role.layoutEditor',
            'reviewer' => 'user.role.reviewer',
            'copyeditor' => 'user.role.copyeditor',
            'proofreader' => 'user.role.proofreader',
            'author' => 'user.role.author',
            'reader' => 'user.role.reader',
            'subscriptionManager' => 'user.role.subscriptionManager'
        ]);

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journal = Request::getJournal();
        
        // Ensure strictly typed time limit
        set_time_limit(0);

        $command = array_shift($args);

        switch ($command) {
            case 'confirm':
                $this->import('UserXMLParser');
                $templateMgr->assign('helpTopicId', 'journal.users.importUsers');

                $sendNotify = (bool) Request::getUserVar('sendNotify');
                $continueOnError = (bool) Request::getUserVar('continueOnError');

                import('lib.pkp.classes.file.FileManager');
                $fileManager = new FileManager();
                
                if (($userFile = $fileManager->getUploadedFilePath('userFile')) !== false) {
                    // Import the uploaded file
                    $parser = new UserXMLParser($journal->getId());
                    $users = $parser->parseData($userFile);

                    $usersRoles = [];
                    foreach ($users as $user) {
                        $currentRoles = [];
                        foreach ($user->getRoles() as $role) {
                            $currentRoles[] = $role->getRoleName();
                        }
                        $usersRoles[] = $currentRoles;
                    }

                    // Use assign instead of assign_by_ref
                    $templateMgr->assign('users', $users);
                    $templateMgr->assign('usersRoles', $usersRoles);
                    $templateMgr->assign('sendNotify', $sendNotify);
                    $templateMgr->assign('continueOnError', $continueOnError);
                    $templateMgr->assign('errors', $parser->errors);

                    // Show confirmation form
                    $templateMgr->display($this->getTemplatePath() . 'importUsersConfirm.tpl');
                }
                break;

            case 'import':
                $this->import('UserXMLParser');
                $userKeys = (array) Request::getUserVar('userKeys');
                if (empty($userKeys)) $userKeys = [];
                
                $sendNotify = (bool) Request::getUserVar('sendNotify');
                $continueOnError = (bool) Request::getUserVar('continueOnError');

                $users = [];
                foreach ($userKeys as $i) {
                    $newUser = new ImportedUser();
                    $newUser->setFirstName((string) Request::getUserVar($i . '_firstName'));
                    $newUser->setMiddleName((string) Request::getUserVar($i . '_middleName'));
                    $newUser->setLastName((string) Request::getUserVar($i . '_lastName'));
                    $newUser->setUsername((string) Request::getUserVar($i . '_username'));
                    $newUser->setEmail((string) Request::getUserVar($i . '_email'));

                    $locales = [];
                    $userLocales = Request::getUserVar($i . '_locales');
                    if ($userLocales !== null && is_array($userLocales)) {
                        foreach ($userLocales as $locale) {
                            $locales[] = $locale;
                        }
                    }
                    $newUser->setLocales($locales);
                    
                    $newUser->setSignature(Request::getUserVar($i . '_signature'), null);
                    $newUser->setBiography(Request::getUserVar($i . '_biography'), null);
                    $newUser->setTemporaryInterests((string) Request::getUserVar($i . '_interests'));
                    $newUser->setGossip(Request::getUserVar($i . '_gossip'), null);
                    $newUser->setCountry((string) Request::getUserVar($i . '_country'));
                    $newUser->setMailingAddress((string) Request::getUserVar($i . '_mailingAddress'));
                    $newUser->setFax((string) Request::getUserVar($i . '_fax'));
                    $newUser->setPhone((string) Request::getUserVar($i . '_phone'));
                    $newUser->setUrl((string) Request::getUserVar($i . '_url'));
                    $newUser->setAffiliation(Request::getUserVar($i . '_affiliation'), null);
                    $newUser->setGender((string) Request::getUserVar($i . '_gender'));
                    $newUser->setInitials((string) Request::getUserVar($i . '_initials'));
                    $newUser->setSalutation((string) Request::getUserVar($i . '_salutation'));
                    $newUser->setPassword((string) Request::getUserVar($i . '_password'));
                    $newUser->setMustChangePassword((bool) Request::getUserVar($i . '_mustChangePassword'));
                    $newUser->setUnencryptedPassword((string) Request::getUserVar($i . '_unencryptedPassword'));

                    $newUserRoles = Request::getUserVar($i . '_roles');
                    if (is_array($newUserRoles) && count($newUserRoles) > 0) {
                        foreach ($newUserRoles as $newUserRole) {
                            if ($newUserRole != '') {
                                $role = new Role();
                                $role->setRoleId(RoleDAO::getRoleIdFromPath($newUserRole));
                                $newUser->AddRole($role);
                            }
                        }
                    }
                    $users[] = $newUser;
                }

                $parser = new UserXMLParser($journal->getId());
                $parser->setUsersToImport($users);
                
                if (!$parser->importUsers($sendNotify, $continueOnError)) {
                    // Failures occurred
                    $templateMgr->assign('isError', true);
                    $templateMgr->assign('errors', $parser->getErrors());
                }
                
                $templateMgr->assign('importedUsers', $parser->getImportedUsers());
                $templateMgr->display($this->getTemplatePath() . 'importUsersResults.tpl');
                break;

            case 'exportAll':
                $this->import('UserExportDom');
                $usersResult = $roleDao->getUsersByJournalId($journal->getId());
                $users = $usersResult->toArray();
                
                $userExportDom = new UserExportDom();
                $doc = $userExportDom->exportUsers($journal, $users);
                
                header("Content-Type: application/xml");
                header("Cache-Control: private");
                header("Content-Disposition: attachment; filename=\"users.xml\"");
                echo XMLCustomWriter::getXML($doc);
                break;

            case 'exportByRole':
                $this->import('UserExportDom');
                $users = [];
                $rolePaths = [];
                
                $roles = (array) Request::getUserVar('roles');
                foreach ($roles as $rolePath) {
                    $roleId = $roleDao->getRoleIdFromPath($rolePath);
                    $thisRoleUsers = $roleDao->getUsersByRoleId($roleId, $journal->getId());
                    foreach ($thisRoleUsers->toArray() as $user) {
                        $users[$user->getId()] = $user;
                    }
                    $rolePaths[] = $rolePath;
                }
                
                $users = array_values($users);
                $userExportDom = new UserExportDom();
                $doc = $userExportDom->exportUsers($journal, $users, $rolePaths);
                
                header("Content-Type: application/xml");
                header("Cache-Control: private");
                header("Content-Disposition: attachment; filename=\"users.xml\"");
                echo XMLCustomWriter::getXML($doc);
                break;

            default:
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
        }
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     * @return bool True on success, false on failure
     */
    public function executeCLI($scriptName, $args) {
        $command = array_shift($args);
        $xmlFile = array_shift($args);
        $journalPath = array_shift($args);
        $flags = $args;

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $journalDao->getJournalByPath($journalPath);

        if (!$journal) {
            if ($journalPath != '') {
                echo __('plugins.importexport.users.import.errorsOccurred') . ":\n";
                echo __('plugins.importexport.users.unknownJournal', ['journalPath' => $journalPath]) . "\n\n";
            }
            $this->usage($scriptName);
            return;
        }

        switch ($command) {
            case 'import':
                $this->import('UserXMLParser');

                $sendNotify = in_array('send_notify', $flags);
                $continueOnError = in_array('continue_on_error', $flags);

                import('lib.pkp.classes.file.FileManager');

                // Import the uploaded file
                $parser = new UserXMLParser($journal->getId());
                $users = $parser->parseData($xmlFile);

                if (!$parser->importUsers($sendNotify, $continueOnError)) {
                    // Failure.
                    echo __('plugins.importexport.users.import.errorsOccurred') . ":\n";
                    foreach ($parser->getErrors() as $error) {
                        echo "\t$error\n";
                    }
                    return false;
                }

                // Success.
                echo __('plugins.importexport.users.import.usersWereImported') . ":\n";
                foreach ($parser->getImportedUsers() as $user) {
                    echo "\t" . $user->getUserName() . "\n";
                }

                return true;

            case 'export':
                $this->import('UserExportDom');
                $roleDao = DAORegistry::getDAO('RoleDAO');
                $rolePaths = null;
                
                if (empty($args)) {
                    $usersResult = $roleDao->getUsersByJournalId($journal->getId());
                    $users = $usersResult->toArray();
                } else {
                    $users = [];
                    $rolePaths = [];
                    foreach ($args as $rolePath) {
                        $roleId = $roleDao->getRoleIdFromPath($rolePath);
                        $thisRoleUsers = $roleDao->getUsersByRoleId($roleId, $journal->getId());
                        foreach ($thisRoleUsers->toArray() as $user) {
                            $users[$user->getId()] = $user;
                        }
                        $rolePaths[] = $rolePath;
                    }
                    $users = array_values($users);
                }

                $userExportDom = new UserExportDom();
                $doc = $userExportDom->exportUsers($journal, $users, $rolePaths);
                
                $h = fopen($xmlFile, 'wb');
                if ($h === false) {
                    echo __('plugins.importexport.users.export.errorsOccurred') . ":\n";
                    echo __('plugins.importexport.users.export.couldNotWriteFile', ['fileName' => $xmlFile]) . "\n";
                    return false;
                }
                fwrite($h, XMLCustomWriter::getXML($doc));
                fclose($h);
                return true;
        }
        $this->usage($scriptName);
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     * @return void
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.users.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }
}

?>