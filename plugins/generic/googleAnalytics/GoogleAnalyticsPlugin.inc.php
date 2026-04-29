<?php
declare(strict_types=1);

/**
 * @file plugins/generic/googleAnalytics/GoogleAnalyticsPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GoogleAnalyticsPlugin
 * @ingroup plugins_generic_googleAnalytics
 *
 * @brief Google Analytics plugin class
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class GoogleAnalyticsPlugin extends GenericPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GoogleAnalyticsPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::GoogleAnalyticsPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path
     * @return boolean True iff plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
        
        if ($success && $this->getEnabled()) {
            // Insert field into author submission page and metadata form
            HookRegistry::register('Templates::Author::Submit::Authors', [$this, 'metadataField']);
            HookRegistry::register('Templates::Submission::MetadataEdit::Authors', [$this, 'metadataField']);

            // Hook for initData in two forms
            HookRegistry::register('metadataform::initdata', [$this, 'metadataInitData']);
            HookRegistry::register('authorsubmitstep3form::initdata', [$this, 'metadataInitData']);

            // Hook for execute in two forms
            HookRegistry::register('Author::Form::Submit::AuthorSubmitStep3Form::Execute', [$this, 'metadataExecute']);
            HookRegistry::register('Submission::Form::MetadataForm::Execute', [$this, 'metadataExecute']);

            // Add element for AuthorDAO for storage
            HookRegistry::register('authordao::getAdditionalFieldNames', [$this, 'authorSubmitGetFieldNames']);

            // Insert Google Analytics page tag to common footer
            HookRegistry::register('Templates::Common::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert Google Analytics page tag to article footer
            HookRegistry::register('Templates::Article::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert Google Analytics page tag to article interstitial footer
            HookRegistry::register('Templates::Article::Interstitial::PageFooter', [$this, 'insertFooter']);

            // Insert Google Analytics page tag to article pdf interstitial footer
            HookRegistry::register('Templates::Article::PdfInterstitial::PageFooter', [$this, 'insertFooter']);

            // Insert Google Analytics page tag to reading tools footer
            HookRegistry::register('Templates::Rt::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert Google Analytics page tag to help footer
            HookRegistry::register('Templates::Help::Footer::PageFooter', [$this, 'insertFooter']);
        }
        return $success;
    }

    /**
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.googleAnalytics.displayName');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.googleAnalytics.description');
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * @param array $params Parameters passed from Smarty
     * @param Smarty $smarty Smarty instance
     * @return string Generated URL
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [$params['id']]);
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @param boolean $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                Request::url(null, 'user'),
                'navigation.user'
            ],
            [
                Request::url(null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) {
            $pageCrumbs[] = [
                Request::url(null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs An array of existing management verbs to add to
     * @return array The management verbs to display
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        
        if ($this->getEnabled()) {
            $verbs[] = ['settings', __('plugins.generic.googleAnalytics.manager.settings')];
        }
        
        return $verbs; // [WIZDAM FIX] Hapus pemanggilan parent:: di sini
    }

    /**
     * Insert Google Scholar account info into author submission step 3
     * and metadata edit forms
     * @param string $hookName
     * @param array $params Parameters passed by the hook, including:
     *  - $params[1] Smarty instance
     *  - $params[2] Output string to append to
     * @return boolean False to indicate that the hook handler should not be called again
     */
    public function metadataField($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2]; // Reference needed for string concatenation on output

        $output .= $smarty->fetch($this->getTemplatePath() . 'authorSubmit.tpl');
        return false;
    }

    /**
     * Add Google Scholar to additional author fields
     * @param string $hookName
     * @param array $params Parameters passed by the hook, including:
     * - $params[1] Array of additional field names to modify
     * @return boolean
     */
    public function authorSubmitGetFieldNames($hookName, $params) {
        $fields =& $params[1]; // Reference needed for array modification
        $fields[] = 'gs';
        return false;
    }

    /**
     * Execute metadata changes
     * @param string $hookName
     * @param array $params Parameters passed by the hook, including:
     * - $params[0] Author object to modify
     * - $params[1] Array of form data to read from
     * @return boolean False to indicate that the hook handler should not be called again
     */
    public function metadataExecute($hookName, $params) {
        $author = $params[0];
        $formAuthor = $params[1];
        // Safe access to array index
        if (isset($formAuthor['gs'])) {
            $author->setData('gs', $formAuthor['gs']);
        }
        return false;
    }

    /**
     * Initialize metadata form data
     * @param string $hookName
     * @param array $params Parameters passed by the hook, including:
     * - $params[0] Form object to read from and modify
     * @return boolean False to indicate that the hook handler should not be called again
     */
    public function metadataInitData($hookName, $params) {
        $form = $params[0];
        $article = $form->getArticle();
        $formAuthors = $form->getData('authors');
        $articleAuthors = $article->getAuthors();

        for ($i=0; $i<count($articleAuthors); $i++) {
            $formAuthors[$i]['gs'] = $articleAuthors[$i]->getData('gs');
        }

        $form->setData('authors', $formAuthors);
        return false;
    }

    /**
     * Insert Google Analytics page tag to footer
     * @param string $hookName
     * @param array $params Parameters passed by the hook, including:
     * - $params[1] Smarty instance
     * - $params[2] Output string to append to
     * @return boolean False to indicate that the hook handler should not be called again
     */
    public function insertFooter($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2]; // Reference needed for string concatenation on output
        
        $templateMgr = TemplateManager::getManager();
        $currentJournal = $templateMgr->get_template_vars('currentJournal');

        $contextId = CONTEXT_ID_NONE;
        if (!empty($currentJournal)) {
            $journal = Request::getJournal();
            if ($journal) {
                $contextId = $journal->getId();
            }
        }
        
        if ($contextId || $this->getSetting($contextId, 'enabled')) {
            $googleAnalyticsSiteId = $this->getSetting($contextId, 'googleAnalyticsSiteId');

            $article = $templateMgr->get_template_vars('article');
            $authorAccounts = [];
            
            if (Request::getRequestedPage() == 'article' && $article) {
                foreach ($article->getAuthors() as $author) {
                    $account = $author->getData('gs');
                    if (!empty($account)) $authorAccounts[] = $account;
                }
                $templateMgr->assign('gsAuthorAccounts', $authorAccounts);
            }

            if (!empty($googleAnalyticsSiteId) || !empty($authorAccounts)) {
                $templateMgr->assign('googleAnalyticsSiteId', $googleAnalyticsSiteId);
                $trackingCode = $this->getSetting($contextId, 'trackingCode');
                
                if ($trackingCode == "ga") {
                    $output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagGa.tpl');
                } elseif ($trackingCode == "urchin") {
                    $output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagUrchin.tpl');
                } elseif ($trackingCode == "analytics") {
                    $output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagAnalytics.tpl');
                }
            }
        }
        return false;
    }

    /**
     * Execute a management verb on this plugin
     * @param string $verb
     * @param array $args
     * @param string $message Result status message
     * @param array $messageParams Parameters for the message key
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams)) return false;

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = Request::getJournal();

                $this->import('GoogleAnalyticsSettingsForm');
                $form = new GoogleAnalyticsSettingsForm($this, $journal->getId());
                
                if (Request::getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                        return false;
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display();
                    }
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display();
                }
                return true;
            default:
                // Unknown management verb
                return false;
        }
    }
}
?>