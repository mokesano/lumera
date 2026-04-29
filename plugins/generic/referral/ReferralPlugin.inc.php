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
 * @brief Referral plugin to track and maintain potential references to published articles
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ReferralPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReferralPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ReferralPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Register the plugin, if enabled; note that this plugin
     * runs under both Journal and Site contexts.
     * @param string $category
     * @param string $path
     * @return boolean
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                // [PHP 8 NOTE] Removed & from $this as objects are passed by reference implicitly
                HookRegistry::register ('TemplateManager::display', [$this, 'handleTemplateDisplay']);
                HookRegistry::register ('LoadHandler', [$this, 'handleLoadHandler']);
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
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array { 
        $verbs = parent::getManagementVerbs($verbs, $request); 

        if ($this->getEnabled($request)) { 
            $verbs[] = ['settings', __('plugins.generic.referral.settings')];
        }
        
        return $verbs;
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

                $this->import('ReferralPluginSettingsForm');
                $form = new ReferralPluginSettingsForm($this, $journal->getId());
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
                assert(false);
        }
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items
     * to append.
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
        if ($isSubclass) $pageCrumbs[] = [
            Request::url(null, 'manager', 'plugins'),
            'manager.plugins'
        ];

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Intercept the load handler hook to present the user-facing
     * referrals list if necessary.
     */
    public function handleLoadHandler($hookName, $args) {
        $page = $args[0];
        $op = $args[1];
        $sourceFile = $args[2];

        if ($page === 'referral') {
            $this->import('ReferralHandler');
            Registry::set('plugin', $this);
            define('HANDLER_CLASS', 'ReferralHandler');
            return true;
        }

        return false;
    }

    /**
     * Intercept the author index page to add referral content
     */
    public function handleAuthorTemplateInclude($hookName, $args) {
        $templateMgr = $args[0];
        $params = $args[1];
        if (!isset($params['smarty_include_tpl_file'])) return false;
        switch ($params['smarty_include_tpl_file']) {
            case 'common/footer.tpl':
                $referralDao = DAORegistry::getDAO('ReferralDAO');
                $user = Request::getUser();
                $rangeInfo = Handler::getRangeInfo('referrals');
                $referralFilter = (int) Request::getUserVar('referralFilter');
                if ($referralFilter == 0) $referralFilter = null;

                // Fetch article titles
                $journal = Request::getJournal();
                $referrals = $referralDao->getByUserId($user->getId(), $journal->getId(), $referralFilter, $rangeInfo);
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $articleTitles = $referralsArray = [];
                
                // Using next() properly for DAOResultFactory
                while ($referral = $referrals->next()) {
                    $article = $articleDao->getArticle($referral->getArticleId());
                    if (!$article) continue;
                    $articleTitles[$article->getId()] = $article->getLocalizedTitle();
                    $referralsArray[] = $referral;
                }
                // Turn the array back into an interator for display
                import('lib.pkp.classes.core.VirtualArrayIterator');
                $referrals = new VirtualArrayIterator($referralsArray, $referrals->getCount(), $referrals->getPage(), $rangeInfo->getCount());

                $templateMgr->assign('articleTitles', $articleTitles);
                $templateMgr->assign('referrals', $referrals);
                $templateMgr->assign('referralFilter', $referralFilter);
                $templateMgr->display($this->getTemplatePath() . 'authorReferrals.tpl', 'text/html', 'ReferralPlugin::addAuthorReferralContent');
                break;
        }
        return false;
    }

    /**
     * Intercept the article comments template to add referral content
     */
    public function handleReaderTemplateInclude($hookName, $args) {
        $templateMgr = $args[0];
        $params = $args[1];
        if (!isset($params['smarty_include_tpl_file'])) return false;
        switch ($params['smarty_include_tpl_file']) {
            case 'article/comments.tpl':
                $referralDao = DAORegistry::getDAO('ReferralDAO');
                $article = $templateMgr->get_template_vars('article');
                $referrals = $referralDao->getPublishedReferralsForArticle($article->getId());

                $templateMgr->assign('referrals', $referrals);
                $templateMgr->display($this->getTemplatePath() . 'readerReferrals.tpl', 'text/html', 'ReferralPlugin::addReaderReferralContent');
                break;
        }
        return false;
    }

    /**
     * Hook callback: Handle requests.
     */
    public function handleTemplateDisplay($hookName, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        switch ($template) {
            case 'article/article.tpl':
                HookRegistry::register ('TemplateManager::include', [$this, 'handleReaderTemplateInclude']);
                // fall-through
            case 'article/interstitial.tpl':
            case 'article/pdfInterstitial.tpl':
                $this->logArticleRequest($templateMgr);
                break;
            case 'author/index.tpl':
                // Slightly convoluted: register a hook to
                // display the administration options at the
                // end of the normal content
                HookRegistry::register ('TemplateManager::include', [$this, 'handleAuthorTemplateInclude']);
                break;
        }
        return false;
    }

    /**
     * Intercept requests for article display to collect and record
     * incoming referrals.
     */
    public function logArticleRequest($templateMgr) {
        $article = $templateMgr->get_template_vars('article');
        if (!$article) return false;
        $articleId = $article->getId();

        $referrer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:null;

        // Check if referrer is empty or is the local journal
        if (empty($referrer) || strpos($referrer, Request::getIndexUrl()) !== false) return false;

        $referralDao = DAORegistry::getDAO('ReferralDAO');
        if ($referralDao->referralExistsByUrl($articleId, $referrer)) {
            // It exists -- increment the count
            $referralDao->incrementReferralCount($article->getId(), $referrer);
        } else {
            // It's a new referral. Log it unless it's excluded.
            $journal = $templateMgr->get_template_vars('currentJournal');
            $exclusions = $this->getSetting($journal->getId(), 'exclusions');
            // PHP 8: Ensure $exclusions is string before explode
            $exclusionsString = (string)$exclusions;
            foreach (array_map('trim', explode("\n", $exclusionsString)) as $exclusion) {
                if (empty($exclusion)) continue;
                if (preg_match($exclusion, $referrer)) return false;
            }
            $referral = new Referral();
            $referral->setArticleId($article->getId());
            $referral->setLinkCount(1);
            $referral->setUrl($referrer);
            $referral->setStatus(REFERRAL_STATUS_NEW);
            $referral->setDateAdded(Core::getCurrentDate());
            $referralDao->replaceReferral($referral);
        }
        return false;
    }

    /**
     * Get the name of the settings file to be installed on new journal
     * creation.
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.referral.name');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.referral.description');
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/' . 'schema.xml';
    }
}

?>