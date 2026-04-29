<?php
declare(strict_types=1);

/**
 * @file classes/plugins/PubIdPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubIdPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for public identifiers plugins
 */

import('classes.plugins.Plugin');

class PubIdPlugin extends Plugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PubIdPlugin() {
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
    // Implement template methods from PKPPlugin
    //
    
    /**
     * Registers the plugin and its hooks.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        if ($success) {
            // Enable storage of additional fields.
            foreach($this->_getDAOs() as $daoName) {
                HookRegistry::register(strtolower_codesafe($daoName).'::getAdditionalFieldNames', [$this, 'getAdditionalFieldNames']);
            }
            // Exclude issue articles
            HookRegistry::register('Editor::IssueManagementHandler::editIssue', [$this, 'editIssue']);
        }
        return $success;
    }

    /**
     * Get the management verbs for this plugin.
     * @see PKPPlugin::getManagementVerbs()
     * @param array $verbs
     * @param Request|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        if ($this->getEnabled()) {
            $verbs = [
                [
                    'disable',
                    __('manager.plugins.disable')
                ],
                [
                    'settings',
                    __('manager.plugins.settings')
                ]
            ];
        } else {
            $verbs = [
                [
                    'enable',
                    __('manager.plugins.enable')
                ]
            ];
        }
        return $verbs;
    }

    /**
     * Handle management actions for this plugin.
     * @see PKPPlugin::manage()
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param Request|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message = null, array $messageParams = null, $request = null): bool {
        $templateManager = TemplateManager::getManager();
        $templateManager->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        if (!$this->getEnabled() && $verb != 'enable') return false;
        
        switch ($verb) {
            case 'enable':
                $this->setEnabled(true);
                return false;

            case 'disable':
                $this->setEnabled(false);
                return false;

            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $journal = Request::getJournal();

                $settingsFormName = $this->getSettingsFormName();
                $settingsFormNameParts = explode('.', $settingsFormName);
                $settingsFormClassName = array_pop($settingsFormNameParts);
                $this->import($settingsFormName);
                $form = new $settingsFormClassName($this, (int) $journal->getId());
                
                if (Request::getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        Request::redirect(null, 'manager', 'plugin');
                        return false;
                    } else {
                        $this->_setBreadcrumbs();
                        $form->display();
                    }
                } elseif (Request::getUserVar('clearPubIds')) {
                    $form->readInputData();
                    $journalDao = DAORegistry::getDAO('JournalDAO'); /* @var $journalDao JournalDAO */
                    $journalDao->deleteAllPubIds($journal->getId(), $this->getPubIdType());
                    $this->_setBreadcrumbs();
                    $form->display();
                } else {
                    $this->_setBreadcrumbs();
                    $form->initData();
                    $form->display();
                }
                return true;

            default:
                // Unknown management verb
                assert(false);
                return false;
        }
    }


    //
    // Protected template methods to be implemented by sub-classes.
    //

    /**
     * Get the public identifier.
     * @param object $pubObject (Issue, Article, PublishedArticle, ArticleGalley, SuppFile)
     * @param bool $preview when true, the public identifier will not be stored
     * @return string|null
     */
    public function getPubId($pubObject, $preview = false) {
        assert(false); // Should always be overridden
        return null;
    }

    /**
     * Public identifier type, see
     * http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html
     * @return string
     */
    public function getPubIdType() {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Public identifier type that will be displayed to the reader.
     * @return string
     */
    public function getPubIdDisplayType() {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Full name of the public identifier.
     * @return string
     */
    public function getPubIdFullName() {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Get the whole resolving URL.
     * @param int $journalId
     * @param string $pubId
     * @return string resolving URL
     */
    public function getResolvingURL($journalId, $pubId) {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Get the file (path + filename)
     * to be included into the object's
     * metadata pages.
     * @return string
     */
    public function getPubIdMetadataFile() {
        assert(false); // Should be overridden
        return '';
    }

    /**
     * Get the class name of the settings form.
     * @return string
     */
    public function getSettingsFormName() {
        assert(false); // Should be overridden
        return '';
    }

    /**
     * Verify form data.
     * @param string $fieldName The form field to be checked.
     * @param string $fieldValue The value of the form field.
     * @param object $pubObject
     * @param int $journalId
     * @param string $errorMsg Return validation error messages here.
     * @return bool
     */
    public function verifyData($fieldName, $fieldValue, $pubObject, $journalId, &$errorMsg) {
        assert(false); // Should be overridden
        return false;
    }

    /**
     * Check whether the given pubId is valid.
     * @param string $pubId
     * @return bool
     */
    public function validatePubId($pubId) {
        return true; // Assume a valid ID by default;
    }

    /**
     * Get the additional form field names.
     * @return array
     */
    public function getFormFieldNames() {
        assert(false); // Should be overridden
        return [];
    }

    /**
     * Get the checkbox form field name that is used to define
     * if a pub object should be excluded from assigning the pub id to it.
     * @return string
     */
    public function getExcludeFormFieldName() {
        assert(false); // Should be overridden
        return '';
    }

    /**
     * Should the object be excluded from assigning the pub id
     * @param object $pubObject
     * @return bool
     */
    public function isExcluded($pubObject) {
        $excludeFormFieldName = $this->getExcludeFormFieldName();
        $excluded = $pubObject->getData($excludeFormFieldName);
        return (bool) $excluded;
    }

    /**
     * Is this object type enabled in plugin settings
     * @param string $pubObjectType (Issue, Article, Galley, SuppFile)
     * @param int $journalId
     * @return bool
     */
    public function isEnabled($pubObjectType, $journalId) {
        assert(false); // Should be overridden
        return false;
    }

    /**
     * Get additional field names to be considered for storage.
     * @return array
     */
    public function getDAOFieldNames() {
        assert(false); // Should be overridden
        return [];
    }

    /**
     * Get the journal object.
     * @param int $journalId
     * @return Journal
     */
    public function getJournal($journalId) {
        assert(is_numeric($journalId));

        // Get the journal object from the context (optimized).
        $request = Application::getRequest();
        $router = $request->getRouter();
        $journal = $router->getContext($request); /* @var $journal Journal */

        // Check whether we still have to retrieve the journal from the database.
        if (!$journal || $journal->getId() != $journalId) {
            $journalDao = DAORegistry::getDAO('JournalDAO'); /* @var $journalDao JournalDAO */
            $journal = $journalDao->getById($journalId);
        }

        return $journal;
    }

    //
    // Public API
    //

    /**
     * Check for duplicate public identifiers.
     * @param string $pubId
     * @param object $pubObject
     * @param int $journalId
     * @return bool
     */
    public function checkDuplicate($pubId, $pubObject, $journalId) {

        // Check all objects of the journal whether they have
        // the same pubId. This includes pubIds that are not yet generated
        // but could be generated at any moment if someone accessed
        // the object publicly. We have to check "real" pubIds rather than
        // the pubId suffixes only as a pubId with the given suffix may exist
        // (e.g. through import) even if the suffix itself is not in the
        // database.
        $typesToCheck = ['Issue', 'Article', 'ArticleGalley', 'SuppFile'];
        foreach($typesToCheck as $pubObjectType) {
            $objectsToCheck = null;
            switch($pubObjectType) {
                case 'Issue':
                    $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
                    $objectsToCheck = $issueDao->getIssues($journalId);
                    break;

                case 'Article':
                    $articleDao = DAORegistry::getDAO('ArticleDAO'); /* @var $articleDao ArticleDAO */
                    $objectsToCheck = $articleDao->getArticlesByJournalId($journalId);
                    break;

                case 'ArticleGalley':
                    $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $galleyDao ArticleGalleyDAO */
                    $objectsToCheck = $galleyDao->getGalleysByJournalId($journalId);
                    break;

                case 'SuppFile':
                    $suppFileDao = DAORegistry::getDAO('SuppFileDAO'); /* @var $suppFileDao SuppFileDAO */
                    $objectsToCheck = $suppFileDao->getSuppFilesByJournalId($journalId);
                    break;
            }

            // Replacing is_a() with instanceof. Note: string class comparison not needed here
            // because we are checking object types conceptually mapped to strings above.
            // However, checking if $pubObject matches the current DAO type context.
            $isSameType = false;
            if ($pubObjectType === 'Issue' && $pubObject instanceof Issue) $isSameType = true;
            elseif ($pubObjectType === 'Article' && $pubObject instanceof Article) $isSameType = true;
            elseif ($pubObjectType === 'ArticleGalley' && $pubObject instanceof ArticleGalley) $isSameType = true;
            elseif ($pubObjectType === 'SuppFile' && $pubObject instanceof SuppFile) $isSameType = true;

            $excludedId = ($isSameType ? $pubObject->getId() : null);

            while ($objectToCheck = $objectsToCheck->next()) {
                // The publication object for which the new pubId
                // should be admissible is to be ignored. Otherwise
                // we might get false positives by checking against
                // a pubId that we're about to change anyway.
                if ($objectToCheck->getId() == $excludedId) continue;

                // Check for ID clashes.
                $existingPubId = $this->getPubId($objectToCheck, true);
                if ($pubId == $existingPubId) return false;

                unset($objectToCheck);
            }

            unset($objectsToCheck);
        }

        // We did not find any ID collision, so go ahead.
        return true;
    }

    /**
     * Add the suffix element and the public identifier
     * to the object (issue, article, galley, supplementary file).
     * @param string $hookName (daoName::getAdditionalFieldNames)
     * @param array $params (DAO, array of additional fields)
     */
    public function getAdditionalFieldNames($hookName, $params) {
        $fields =& $params[1]; // Reference required here to modify the array
        $formFieldNames = $this->getFormFieldNames();
        foreach ($formFieldNames as $formFieldName) {
            $fields[] = $formFieldName;
        }
        $daoFieldNames = $this->getDAOFieldNames();
        foreach ($daoFieldNames as $daoFieldName) {
            $fields[] = $daoFieldName;
        }
        return false;
    }

    /**
     * Exclude all issue objects (articles, galley, supp files)
     * from assigning them the pubId or
     * clear DOIs of all issue objects (articles, galley, supp files)
     * @param string $hookName (Editor::IssueManagementHandler::editIssue)
     * @param array $params (Issue, IssueForm)
     */
    public function editIssue($hookName, $params) {
        $issue = $params[0];
        $issueId = $issue->getId();

        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        if (is_array($pubIdPlugins)) {
            foreach ($pubIdPlugins as $pubIdPlugin) {
                $excludeSubmittName = 'excludeIssueObjects_' . $pubIdPlugin->getPubIdType();
                $clearSubmittName = 'clearIssueObjects_' . $pubIdPlugin->getPubIdType();
                $exclude = $clear = false;
                if (Request::getUserVar($excludeSubmittName)) $exclude = true;
                if (Request::getUserVar($clearSubmittName)) $clear = true;
                if ($exclude || $clear) {
                    $articlePubIdEnabled = $pubIdPlugin->isEnabled('Article', $issue->getJournalId());
                    $galleyPubIdEnabled = $pubIdPlugin->isEnabled('Galley', $issue->getJournalId());
                    $suppFilePubIdEnabled = $pubIdPlugin->isEnabled('SuppFile', $issue->getJournalId());
                    if (!$articlePubIdEnabled && !$galleyPubIdEnabled && !$suppFilePubIdEnabled) return false;

                    $settingName = $pubIdPlugin->getExcludeFormFieldName();
                    $pubIdType = $pubIdPlugin->getPubIdType();

                    $articleDao = DAORegistry::getDAO('ArticleDAO'); /* @var $articleDao ArticleDAO */
                    $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
                    $publishedArticles = $publishedArticleDao->getPublishedArticles($issueId);
                    foreach ($publishedArticles as $publishedArticle) {
                        if ($articlePubIdEnabled) {
                            if ($exclude && !$publishedArticle->getStoredPubId($pubIdType)) {
                                $publishedArticle->setData($settingName, 1);
                                $articleDao->updateArticle($publishedArticle);
                            } else if ($clear) {
                                $articleDao->deletePubId($publishedArticle->getId(), $pubIdType);
                            }
                        }
                        if ($galleyPubIdEnabled) {
                            $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $articleGalleyDao ArticleGalleyDAO */
                            $articleGalleys = $articleGalleyDao->getGalleysByArticle($publishedArticle->getId());
                            foreach ($articleGalleys as $articleGalley) {
                                if ($exclude && !$articleGalley->getStoredPubId($pubIdType)) {
                                    $articleGalley->setData($settingName, 1);
                                    $articleGalleyDao->updateGalley($articleGalley);
                                } else if ($clear) {
                                    $articleGalleyDao->deletePubId($articleGalley->getId(), $pubIdType);
                                }
                            }
                        }
                        if ($suppFilePubIdEnabled) {
                            $articleSuppFileDao = DAORegistry::getDAO('SuppFileDAO'); /* @var $articleSuppFileDao SuppFileDAO */
                            $articleSuppFiles = $articleSuppFileDao->getSuppFilesByArticle($publishedArticle->getId());
                            foreach ($articleSuppFiles as $articleSuppFile) {
                                if ($exclude && !$articleSuppFile->getStoredPubId($pubIdType)) {
                                    $articleSuppFile->setData($settingName, 1);
                                    $articleSuppFileDao->updateSuppFile($articleSuppFile);
                                } else if ($clear) {
                                    // Fix: Original code had typo $articleGalley->getId(), changed to $articleSuppFile->getId()
                                    $articleSuppFileDao->deletePubId($articleSuppFile->getId(), $pubIdType);
                                }
                            }
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return the object type.
     * @param object $pubObject (Issue, Article, PublishedArticle, ArticleGalley, SuppFile)
     * @return string|null
     */
    public function getPubObjectType($pubObject) {
        $allowedTypes = [
            'Issue' => 'Issue',
            'Article' => 'Article',
            'ArticleGalley' => 'Galley',
            'SuppFile' => 'SuppFile'
        ];
        $pubObjectType = null;
        foreach ($allowedTypes as $allowedType => $pubObjectTypeCandidate) {
            // Using instanceof instead of is_a
            if ($pubObject instanceof $allowedType) {
                $pubObjectType = $pubObjectTypeCandidate;
                break;
            }
        }
        if (is_null($pubObjectType)) {
            // This must be a dev error, so bail with an assertion.
            assert(false);
            return null;
        }
        return $pubObjectType;
    }

    /**
     * Set and store a public identifier.
     * @param Issue|Article|ArticleGalley|SuppFile $pubObject
     * @param string $pubObjectType As returned from self::getPubObjectType()
     * @param string $pubId
     */
    public function setStoredPubId($pubObject, $pubObjectType, $pubId) {
        $dao = $this->getDAO($pubObjectType);
        $dao->changePubId($pubObject->getId(), $this->getPubIdType(), $pubId);
        $pubObject->setStoredPubId($this->getPubIdType(), $pubId);
    }

    /**
     * Return the name of the corresponding DAO.
     * @param string $pubObjectType
     * @return DAO
     */
    public function getDAO($pubObjectType) {
        $daos = [
            'Issue' => 'IssueDAO',
            'Article' => 'ArticleDAO',
            'Galley' => 'ArticleGalleyDAO',
            'SuppFile' => 'SuppFileDAO'
        ];
        $daoName = $daos[$pubObjectType];
        assert(!empty($daoName));
        return DAORegistry::getDAO($daoName);
    }

    /**
     * Determine whether or not this plugin is enabled.
     * @param int|null $journalId
     * @return bool
     */
    public function getEnabled($journalId = null) {
        if (!$journalId) {
            $request = Application::getRequest();
            $router = $request->getRouter();
            $journal = $router->getContext($request);

            if (!$journal) return false;
            $journalId = $journal->getId();
        }
        return (bool) $this->getSetting($journalId, 'enabled');
    }

    /**
     * Set the enabled/disabled state of this plugin.
     * @param bool $enabled
     * @return bool
     */
    public function setEnabled($enabled) {
        $journal = Request::getJournal();
        if ($journal) {
            $this->updateSetting(
                $journal->getId(),
                'enabled',
                $enabled ? true : false
            );
            return true;
        }
        return false;
    }


    //
    // Private helper methods
    //
    
    /**
     * Return an array of the corresponding DAOs.
     * @return array
     */
    protected function _getDAOs() {
        return ['IssueDAO', 'ArticleDAO', 'ArticleGalleyDAO', 'SuppFileDAO'];
    }

    /**
     * Set the breadcrumbs, given the plugin's tree of items to append.
     */
    protected function _setBreadcrumbs() {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                Request::url(null, 'user'),
                'navigation.user'
            ],
            [
                Request::url(null, 'manager'),
                'user.role.manager'
            ],
            [
                Request::url(null, 'manager', 'plugins'),
                'manager.plugins'
            ]
        ];
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }
}

?>