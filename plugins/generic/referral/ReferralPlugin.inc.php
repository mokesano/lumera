<?php
declare(strict_types=1);

/**
 * @file plugins/generic/referral/ReferralPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReferralPlugin
 * @ingroup plugins_generic_referral
 *
 * @brief Referral plugin to track and maintain potential references to published articles.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ReferralPlugin extends GenericPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReferralPlugin() {
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
     * Register the plugin, if enabled; note that this plugin
     * runs under both Journal and Site contexts.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                HookRegistry::register('TemplateManager::display', [$this, 'handleTemplateDisplay']);
                HookRegistry::register('LoadHandler', [$this, 'handleLoadHandler']);
                $this->import('Referral');
                $this->import('ReferralDAO');
                $referralDao = new ReferralDAO();
                DAORegistry::registerDAO('ReferralDAO', $referralDao);
            }
            return true;
        }
        return false;
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array { 
        $verbs = parent::getManagementVerbs($verbs, $request); 

        if ($this->getEnabled($request)) { 
            $verbs[] = ['settings', __('plugins.generic.referral.settings')];
        }
        
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param mixed $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) {
            return false;
        }

        // Lumera Singleton Fallback
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

                $this->import('ReferralPluginSettingsForm');
                $form = new ReferralPluginSettingsForm($this, (int) $journal->getId());
                
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', [$this->getCategory()]);
                        return false;
                    } else {
                        $this->setBreadcrumbs(true, $request);
                        $form->display($request);
                    }
                } else {
                    $this->setBreadcrumbs(true, $request);
                    $form->initData();
                    $form->display($request);
                }
                return true;
            default:
                throw new \BadMethodCallException('Unknown management verb');
        }
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @param bool $isSubclass
     * @param mixed $request
     */
    public function setBreadcrumbs($isSubclass = false, $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'manager'),
                'user.role.manager'
            ]
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
     * Intercept the load handler hook to present the user-facing referrals list if necessary.
     * @param mixed $hookName
     * @param array $args
     * @return bool
     */
    public function handleLoadHandler($hookName, $args) {
        $page = $args[0] ?? '';
        $op = $args[1] ?? '';

        if ($page === 'referral') {
            $this->import('ReferralHandler');
            Registry::set('plugin', $this);
            define('HANDLER_CLASS', 'ReferralHandler');

            return true;
        }

        return false;
    }

    /**
     * Intercept the author index page to add referral content.
     * @param mixed $hookName
     * @param array $args
     * @return bool
     */
    public function handleAuthorTemplateInclude($hookName, $args) {
        $templateMgr = $args[0];
        $params = $args[1];

        if (!isset($params['smarty_include_tpl_file'])) {
            return false;
        }
        
        if ($params['smarty_include_tpl_file'] === 'common/footer.tpl') {
            $request = Application::get()->getRequest();
            
            /** @var ReferralDAO $referralDao */
            $referralDao = DAORegistry::getDAO('ReferralDAO');
            $user = $request->getUser();
            $rangeInfo = Handler::getRangeInfo('referrals');
            
            $referralFilterVal = $request->getUserVar('referralFilter');
            $referralFilter = ($referralFilterVal !== null && $referralFilterVal !== '') ? (int) $referralFilterVal : null;
            if ($referralFilter === 0) {
                $referralFilter = null;
            }

            $journal = $request->getJournal();
            $referrals = $referralDao->getByUserId((int) $user->getId(), (int) $journal->getId(), $referralFilter, $rangeInfo);
            
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $articleTitles = [];
            $referralsArray = [];
            
            while ($referral = $referrals->next()) {
                $article = $articleDao->getArticle((int) $referral->getArticleId());
                if (!$article) {
                    continue;
                }
                $articleTitles[(int) $article->getId()] = (string) $article->getLocalizedTitle();
                $referralsArray[] = $referral;
            }
            
            // Turn the array back into an iterator for display
            import('lib.pkp.classes.core.VirtualArrayIterator');
            $referralsIterator = new VirtualArrayIterator(
                $referralsArray, 
                $referrals->getCount(), 
                $referrals->getPage(), 
                $rangeInfo ? $rangeInfo->getCount() : 0
            );

            $templateMgr->assign('articleTitles', $articleTitles);
            $templateMgr->assign('referrals', $referralsIterator);
            $templateMgr->assign('referralFilter', $referralFilter);

            $templateMgr->display($this->getTemplatePath() . 'authorReferrals.tpl', 'text/html', 'ReferralPlugin::addAuthorReferralContent');
        }
        return false;
    }

    /**
     * Intercept the article comments template to add referral content.
     * @param mixed $hookName
     * @param array $args
     * @return bool
     */
    public function handleReaderTemplateInclude($hookName, $args) {
        $templateMgr = $args[0];
        $params = $args[1];

        if (!isset($params['smarty_include_tpl_file'])) {
            return false;
        }
        
        if ($params['smarty_include_tpl_file'] === 'article/comments.tpl') {
            /** @var ReferralDAO $referralDao */
            $referralDao = DAORegistry::getDAO('ReferralDAO');
            $article = $templateMgr->get_template_vars('article');
            
            if ($article) {
                $referrals = $referralDao->getPublishedReferralsForArticle((int) $article->getId());
                $templateMgr->assign('referrals', $referrals);
                $templateMgr->display($this->getTemplatePath() . 'readerReferrals.tpl', 'text/html', 'ReferralPlugin::addReaderReferralContent');
            }
        }
        return false;
    }

    /**
     * Hook callback: Handle requests.
     * @param mixed $hookName
     * @param array $args
     * @return bool
     */
    public function handleTemplateDisplay($hookName, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        switch ($template) {
            case 'article/article.tpl':
                HookRegistry::register('TemplateManager::include', [$this, 'handleReaderTemplateInclude']);
                // fall-through
            case 'article/interstitial.tpl':
            case 'article/pdfInterstitial.tpl':
                $this->logArticleRequest($templateMgr);
                break;
            case 'author/index.tpl':
                HookRegistry::register('TemplateManager::include', [$this, 'handleAuthorTemplateInclude']);
                break;
        }
        return false;
    }

    /**
     * Intercept requests for article display to collect and record incoming referrals.
     * @param mixed $templateMgr
     * @return bool
     */
    public function logArticleRequest($templateMgr) {
        $request = Application::get()->getRequest();
        $article = $templateMgr->get_template_vars('article');
        
        if (!$article) {
            return false;
        }
        $articleId = (int) $article->getId();

        $referrer = $_SERVER['HTTP_REFERER'] ?? null;

        // Check if referrer is empty or is the local journal
        if (empty($referrer) || strpos($referrer, $request->getIndexUrl()) !== false) {
            return false;
        }

        /** @var ReferralDAO $referralDao */
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        if ($referralDao->referralExistsByUrl($articleId, $referrer)) {
            // It exists -- increment the count
            $referralDao->incrementReferralCount($articleId, $referrer);
        } else {
            // It's a new referral. Log it unless it's excluded.
            $journal = $templateMgr->get_template_vars('currentJournal');
            if (!$journal) {
                return false;
            }
            
            $exclusions = $this->getSetting((int) $journal->getId(), 'exclusions');
            $exclusionsString = (string) $exclusions;

            foreach (array_map('trim', explode("\n", $exclusionsString)) as $exclusion) {
                if ($exclusion === '') {
                    continue;
                }
                if (preg_match($exclusion, $referrer)) {
                    return false;
                }
            }

            $referral = new Referral();
            $referral->setArticleId($articleId);
            $referral->setLinkCount(1);
            $referral->setURL($referrer);
            $referral->setStatus(REFERRAL_STATUS_NEW);
            $referral->setDateAdded(Core::getCurrentDate());
            $referralDao->replaceReferral($referral);
        }
        return false;
    }

    /**
     * Get the name of the settings file to be installed on new journal creation.
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.referral.name');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.referral.description');
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml';
    }

}
?>