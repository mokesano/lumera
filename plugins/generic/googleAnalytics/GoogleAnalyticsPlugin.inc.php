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
 * @brief Google Analytics plugin class.
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
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        
        if ($success && $this->getEnabled()) {
            // Insert field into author submission page and metadata form
            HookRegistry::register('Templates::Author::Submit::Authors', [$this, 'metadataField']);
            HookRegistry::register('Templates::Submission::MetadataEdit::Authors', [$this, 'metadataField']);

            // Hook for initData in two forms
            HookRegistry::register('metadataform::initdata', [$this, 'metadataInitData']);
            // [WIZDAM] Diperbarui dari 'authorsubmitstep3form::initdata' --
            // wizard submit direstrukturisasi, bagian authors (tempat
            // field custom 'gs'/Google Scholar ID ini disimpan) sekarang
            // ada di AuthorSubmitStep2Form (bukan lagi Step3Form). Hook
            // otomatis ini namanya mengikuti strtolower(nama_class), jadi
            // HARUS diperbarui bersamaan dengan pemindahan class.
            HookRegistry::register('authorsubmitstep2form::initdata', [$this, 'metadataInitData']);

            // Hook for execute in two forms
            // [WIZDAM] Diperbarui dari 'Author::Form::Submit::AuthorSubmitStep3Form::Execute'
            // -- lihat penjelasan sama seperti di atas.
            HookRegistry::register('Author::Form::Submit::AuthorSubmitStep2Form::Execute', [$this, 'metadataExecute']);
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
     * @param array $params
     * @param $smarty Smarty
     * @return string
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
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $pageCrumbs = [
            [$request->url(null, 'user'), 'navigation.user'],
            [$request->url(null, 'manager'), 'user.role.manager']
        ];
        
        if ($isSubclass) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param PKPRequest|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        
        if ($this->getEnabled($request)) {
            $verbs[] = ['settings', __('plugins.generic.googleAnalytics.manager.settings')];
        }
        
        return $verbs;
    }

    /**
     * Insert Google Scholar account info into author submission step 3 and metadata edit forms
     * @param string $hookName
     * @param array $params
     * @return bool
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
     * @param array $params
     * @return bool
     */
    public function authorSubmitGetFieldNames($hookName, $params) {
        $fields =& $params[1]; // Reference needed for array modification
        $fields[] = 'gs';
        return false;
    }

    /**
     * Execute metadata changes
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function metadataExecute($hookName, $params) {
        $author = $params[0];
        $formAuthor = $params[1];
        
        if (isset($formAuthor['gs'])) {
            $author->setData('gs', $formAuthor['gs']);
        }
        return false;
    }

    /**
     * Initialize metadata form data
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function metadataInitData($hookName, $params) {
        $form = $params[0];
        $article = $form->getArticle();

        if (!is_object($article)) {
            return false;
        }
        
        $formAuthors = $form->getData('authors');
        $articleAuthors = $article->getAuthors();

        if (is_array($articleAuthors)) {
            foreach ($articleAuthors as $i => $author) {
                if (isset($formAuthors[$i])) {
                    $formAuthors[$i]['gs'] = $author->getData('gs');
                }
            }
        }

        $form->setData('authors', $formAuthors);
        return false;
    }

    /**
     * Insert Google Analytics page tag to footer
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function insertFooter($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2]; 
        
        $templateMgr = TemplateManager::getManager();
        $currentJournal = $templateMgr->get_template_vars('currentJournal');
        
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $contextId = CONTEXT_ID_NONE;
        if ($journal) {
            $contextId = $journal->getId();
        }
        
        if ($contextId !== CONTEXT_ID_NONE || $this->getSetting($contextId, 'enabled')) {
            $googleAnalyticsSiteId = $this->getSetting($contextId, 'googleAnalyticsSiteId');

            $article = $templateMgr->get_template_vars('article');
            $authorAccounts = [];

            if ($request->getRequestedPage() === 'article' && is_object($article)) {
                $authors = $article->getAuthors();
                if (is_array($authors)) {
                    foreach ($authors as $author) {
                        $account = $author->getData('gs');
                        if (!empty($account)) {
                            $authorAccounts[] = $account;
                        }
                    }
                }
                $templateMgr->assign('gsAuthorAccounts', $authorAccounts);
            }

            if (!empty($googleAnalyticsSiteId) || !empty($authorAccounts)) {
                $templateMgr->assign('googleAnalyticsSiteId', $googleAnalyticsSiteId);
                $trackingCode = $this->getSetting($contextId, 'trackingCode');
                
                if ($trackingCode === 'ga') {
                    $output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagGa.tpl');
                } elseif ($trackingCode === 'urchin') {
                    $output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagUrchin.tpl');
                } elseif ($trackingCode === 'analytics') {
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
     * @param string|null $message
     * @param array|null $messageParams
     * @param PKPRequest|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) {
            return false;
        }

        // [WIZDAM] FIX: Singleton fallback untuk request
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = $request->getJournal();
                
                if (!$journal) {
                    return false;
                }

                $this->import('GoogleAnalyticsSettingsForm');
                $form = new GoogleAnalyticsSettingsForm($this, $journal->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                        return false;
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display($request);
                    }
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display($request);
                }
                return true;
            default:
                return false;
        }
    }
    
}
?>