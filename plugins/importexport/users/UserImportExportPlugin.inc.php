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
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin.
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
     * @param Request $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager($request);
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

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journal = $request->getJournal();
        if (!$journal) {
            return; 
        }
        
        set_time_limit(0);

        $command = array_shift($args);

        switch ($command) {
            case 'confirm':
                $this->import('UserXMLParser');
                $templateMgr->assign('helpTopicId', 'journal.users.importUsers');

                $sendNotify = (bool) $request->getUserVar('sendNotify');
                $continueOnError = (bool) $request->getUserVar('continueOnError');

                import('lib.pkp.classes.file.FileManager');
                $fileManager = new FileManager();
                
                if (($userFile = $fileManager->getUploadedFilePath('userFile')) !== false) {
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

                    $templateMgr->assign([
                        'users' => $users,
                        'usersRoles' => $usersRoles,
                        'sendNotify' => $sendNotify,
                        'continueOnError' => $continueOnError,
                        'errors' => $parser->getErrors()
                    ]);

                    $templateMgr->display($this->getTemplatePath() . 'importUsersConfirm.tpl');
                }
                break;

            case 'import':
                $this->import('UserXMLParser');
                $userKeys = (array) $request->getUserVar('userKeys');
                if (empty($userKeys)) $userKeys = [];
                
                $sendNotify = (bool) $request->getUserVar('sendNotify');
                $continueOnError = (bool) $request->getUserVar('continueOnError');

                $users = [];
                foreach ($userKeys as $i) {
                    $newUser = new ImportedUser();
                    $newUser->setFirstName((string) $request->getUserVar($i . '_firstName'));
                    $newUser->setMiddleName((string) $request->getUserVar($i . '_middleName'));
                    $newUser->setLastName((string) $request->getUserVar($i . '_lastName'));
                    $newUser->setUsername((string) $request->getUserVar($i . '_username'));
                    $newUser->setEmail((string) $request->getUserVar($i . '_email'));

                    $locales = [];
                    $userLocales = $request->getUserVar($i . '_locales');
                    if (is_array($userLocales)) {
                        foreach ($userLocales as $locale) {
                            $locales[] = $locale;
                        }
                    }
                    $newUser->setLocales($locales);
                    
                    $newUser->setSignature($request->getUserVar($i . '_signature'), null);
                    $newUser->setBiography($request->getUserVar($i . '_biography'), null);
                    $newUser->setTemporaryInterests((string) $request->getUserVar($i . '_interests'));
                    $newUser->setGossip($request->getUserVar($i . '_gossip'), null);
                    $newUser->setCountry((string) $request->getUserVar($i . '_country'));
                    $newUser->setMailingAddress((string) $request->getUserVar($i . '_mailingAddress'));
                    $newUser->setFax((string) $request->getUserVar($i . '_fax'));
                    $newUser->setPhone((string) $request->getUserVar($i . '_phone'));
                    $newUser->setUrl((string) $request->getUserVar($i . '_url'));
                    $newUser->setAffiliation($request->getUserVar($i . '_affiliation'), null);
                    $newUser->setGender((string) $request->getUserVar($i . '_gender'));
                    $newUser->setInitials((string) $request->getUserVar($i . '_initials'));
                    $newUser->setSalutation((string) $request->getUserVar($i . '_salutation'));
                    $newUser->setPassword((string) $request->getUserVar($i . '_password'));
                    $newUser->setMustChangePassword((bool) $request->getUserVar($i . '_mustChangePassword'));
                    $newUser->setUnencryptedPassword((string) $request->getUserVar($i . '_unencryptedPassword'));

                    $newUserRoles = $request->getUserVar($i . '_roles');
                    if (is_array($newUserRoles)) {
                        foreach ($newUserRoles as $newUserRole) {
                            if ($newUserRole !== '') {
                                $role = new Role();
                                $role->setRoleId(RoleDAO::getRoleIdFromPath($newUserRole));
                                $newUser->addRole($role);
                            }
                        }
                    }
                    $users[] = $newUser;
                }

                $parser = new UserXMLParser($journal->getId());
                $parser->setUsersToImport($users);
                
                if (!$parser->importUsers($sendNotify, $continueOnError)) {
                    $templateMgr->assign([
                        'isError' => true,
                        'errors' => $parser->getErrors()
                    ]);
                }
                
                $templateMgr->assign('importedUsers', $parser->getImportedUsers());
                $templateMgr->display($this->getTemplatePath() . 'importUsersResults.tpl');
                break;

            case 'exportAll':
                $this->import('UserExportDom');
                $usersResult = $roleDao->getUsersByJournalId($journal->getId());
                $users = $usersResult ? $usersResult->toArray() : [];
                
                $userExportDom = new UserExportDom();
                $doc = $userExportDom->exportUsers($journal, $users);
                
                if (!headers_sent()) {
                    header("Content-Type: application/xml");
                    header("Cache-Control: private");
                    header("Content-Disposition: attachment; filename=\"users.xml\"");
                }
                echo XMLCustomWriter::getXML($doc);
                break;

            case 'exportByRole':
                $this->import('UserExportDom');
                $users = [];
                $rolePaths = [];
                
                $roles = (array) $request->getUserVar('roles');
                foreach ($roles as $rolePath) {
                    $roleId = $roleDao->getRoleIdFromPath($rolePath);
                    $thisRoleUsers = $roleDao->getUsersByRoleId($roleId, $journal->getId());
                    $roleUsersArray = $thisRoleUsers ? $thisRoleUsers->toArray() : [];
                    
                    foreach ($roleUsersArray as $user) {
                        $users[$user->getId()] = $user;
                    }
                    $rolePaths[] = $rolePath;
                }
                
                $users = array_values($users);
                $userExportDom = new UserExportDom();
                $doc = $userExportDom->exportUsers($journal, $users, $rolePaths);
                
                if (!headers_sent()) {
                    header("Content-Type: application/xml");
                    header("Cache-Control: private");
                    header("Content-Disposition: attachment; filename=\"users.xml\"");
                }
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
     * @param array $args
     * @return bool
     */
    public function executeCLI($scriptName, $args) {
        $command = array_shift($args);
        $xmlFile = array_shift($args);
        $journalPath = array_shift($args);
        $flags = $args;

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        if (!$journalDao) {
            echo "Error: JournalDAO not found.\n";
            return false;
        }

        $journal = $journalDao->getJournalByPath($journalPath);
        if (!$journal) {
            if ($journalPath !== '') {
                echo __('plugins.importexport.users.import.errorsOccurred') . ":\n";
                echo __('plugins.importexport.users.unknownJournal', ['journalPath' => $journalPath]) . "\n\n";
            }
            $this->usage($scriptName);
            return false;
        }

        switch ($command) {
            case 'import':
                $this->import('UserXMLParser');

                $sendNotify = in_array('send_notify', $flags, true);
                $continueOnError = in_array('continue_on_error', $flags, true);

                import('lib.pkp.classes.file.FileManager');

                $parser = new UserXMLParser($journal->getId());
                $users = $parser->parseData($xmlFile);

                if (!$parser->importUsers($sendNotify, $continueOnError)) {
                    echo __('plugins.importexport.users.import.errorsOccurred') . ":\n";
                    foreach ($parser->getErrors() as $error) {
                        echo "\t$error\n";
                    }
                    return false;
                }

                echo __('plugins.importexport.users.import.usersWereImported') . ":\n";
                foreach ($parser->getImportedUsers() as $user) {
                    echo "\t" . $user->getUsername() . "\n";
                }

                return true;

            case 'export':
                $this->import('UserExportDom');
                /** @var RoleDAO $roleDao */
                $roleDao = DAORegistry::getDAO('RoleDAO');
                if (!$roleDao) {
                    echo "Error: RoleDAO not found.\n";
                    return false;
                }

                $rolePaths = null;
                
                if (empty($args)) {
                    $usersResult = $roleDao->getUsersByJournalId($journal->getId());
                    $users = $usersResult ? $usersResult->toArray() : [];
                } else {
                    $users = [];
                    $rolePaths = [];
                    foreach ($args as $rolePath) {
                        $roleId = $roleDao->getRoleIdFromPath($rolePath);
                        $thisRoleUsers = $roleDao->getUsersByRoleId($roleId, $journal->getId());
                        $roleUsersArray = $thisRoleUsers ? $thisRoleUsers->toArray() : [];
                        
                        foreach ($roleUsersArray as $user) {
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

            default:
                $this->usage($scriptName);
                return false;
        }
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.users.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }

}
?>