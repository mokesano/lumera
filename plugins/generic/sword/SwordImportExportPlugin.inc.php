<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sword/SwordImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SwordPlugin
 * @ingroup plugins_importexport_sword
 *
 * @brief Sword deposit plugin
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.plugins.ImportExportPlugin');

class SwordImportExportPlugin extends ImportExportPlugin {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SwordImportExportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::SwordImportExportPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path
     * @return boolean True iff plugin initialized successfully; if false,
     * the plugin will not be registered.
     */
    public function register(string $category, string $path): bool {
        import('classes.sword.OJSSwordDeposit');
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return String name of plugin
     */
    public function getName(): string {
        return 'SwordImportExportPlugin';
    }

    /**
     * Get the display name of the plugin.
     * @return String display name of plugin
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.sword.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String description of plugin
     */
    public function getDescription(): string {
        return __('plugins.importexport.sword.description');
    }

    /**
     * Get the sword plugin
     * @return object
     */
    public function getSwordPlugin() {
        // Menggunakan property parentPluginName untuk mencari induknya
        return PluginRegistry::getPlugin('generic', $this->parentPluginName); 
    }

    /**
     * Override the builtin to get the correct plugin path.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getSwordPlugin();
        // [FIX] Tambahkan pengecekan agar tidak error fatal jika plugin null
        if (!$plugin) return ''; 
        return $plugin->getPluginPath();
    }

    /**
     * Deposit an article via SWORD
     * @param string $url SWORD deposit URL
     * @param string $username SWORD username
     * @param string $password SWORD password
     * @param int $articleId ID of the article to deposit
     * @param bool $depositEditorial Whether to deposit the editorial
     * @param bool $depositGalleys Whether to deposit the galleys
     * @return string SWORD deposit ID
     */
    public function deposit($url, $username, $password, $articleId, $depositEditorial, $depositGalleys) {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($articleId);
        $journal = Request::getJournal();

        $deposit = new OJSSwordDeposit($publishedArticle);
        $deposit->setMetadata();
        if ($depositGalleys) $deposit->addGalleys();
        if ($depositEditorial) $deposit->addEditorial();
        $deposit->createPackage();
        $response = $deposit->deposit($url, $username, $password);
        $deposit->cleanup();
        return $response->sac_id;
    }

    /**
     * Display the plugin interface.
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request = null) {
        $templateMgr = TemplateManager::getManager();
        parent::display($args, $request);
        $this->setBreadcrumbs();
        $journal = Request::getJournal();
        $plugin = $this->getSwordPlugin();

        $swordUrl = Request::getUserVar('swordUrl');

        $depositPointKey = Request::getUserVar('depositPoint');
        $depositPoints = $plugin->getSetting($journal->getId(), 'depositPoints');
        $username = Request::getUserVar('swordUsername');
        $password = Request::getUserVar('swordPassword');

        if (isset($depositPoints[$depositPointKey])) {
            $selectedDepositPoint = $depositPoints[$depositPointKey];
            if ($selectedDepositPoint['username'] != '') $username = $selectedDepositPoint['username'];
            if ($selectedDepositPoint['password'] != '') $password = $selectedDepositPoint['password'];
        }

        $swordDepositPoint = Request::getUserVar('swordDepositPoint');
        $depositEditorial = Request::getUserVar('depositEditorial');
        $depositGalleys = Request::getUserVar('depositGalleys');

        switch (array_shift($args)) {
            case 'deposit':
                $depositIds = [];
                try {
                    $articleIds = Request::getUserVar('articleId');
                    if (is_array($articleIds)) {
                        foreach ($articleIds as $articleId) {
                            $depositIds[] = $this->deposit(
                                $swordDepositPoint,
                                $username,
                                $password,
                                $articleId,
                                $depositEditorial,
                                $depositGalleys
                            );
                        }
                    }
                } catch (Exception $e) {
                    // Deposit failed
                    $templateMgr->assign([
                        'pageTitle' => 'plugins.importexport.sword.depositFailed',
                        'messageTranslated' => $e->getMessage(),
                        'backLink' => Request::url(
                            null, null, null,
                            ['plugin', $this->getName()],
                            [
                                'swordUrl' => $swordUrl,
                                'swordUsername' => $username,
                                'swordDepositPoint' => $swordDepositPoint,
                                'depositEditorial' => $depositEditorial,
                                'depositGalleys' => $depositGalleys,
                            ]
                        ),
                        'backLinkLabel' => 'common.back'
                    ]);
                    return $templateMgr->display('common/message.tpl');
                }
                // Deposit was successful
                $templateMgr->assign([
                    'pageTitle' => 'plugins.importexport.sword.depositSuccessful',
                    'message' => 'plugins.importexport.sword.depositSuccessfulDescription',
                    'backLink' => Request::url(
                        null, null, null,
                        ['plugin', $this->getName()],
                        [
                            'swordUrl' => $swordUrl,
                            'swordUsername' => $username,
                            'swordDepositPoint' => $swordDepositPoint,
                            'depositEditorial' => $depositEditorial,
                            'depositGalleys' => $depositGalleys
                        ]
                    ),
                    'backLinkLabel' => 'common.continue'
                ]);
                return $templateMgr->display('common/message.tpl');
                break;
            default:
                $journal = Request::getJournal();
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $rangeInfo = Handler::getRangeInfo('articles');
                $articleIds = $publishedArticleDao->getPublishedArticleIdsAlphabetizedByJournal($journal->getId(), false);
                $totalArticles = count($articleIds);
                if ($rangeInfo->isValid()) $articleIds = array_slice($articleIds, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
                import('lib.pkp.classes.core.VirtualArrayIterator');
                $iterator = new VirtualArrayIterator(ArticleSearch::formatResults($articleIds), $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());
                foreach (['swordUrl', 'swordUsername', 'swordPassword', 'depositEditorial', 'depositGalleys', 'swordDepositPoint'] as $var) {
                    $templateMgr->assign($var, Request::getUserVar($var));
                }
                $templateMgr->assign('depositPoints', $depositPoints);
                if (!empty($swordUrl)) {
                    $client = new SWORDAPPClient();
                    $doc = $client->servicedocument($swordUrl, $username, $password, '');
                    $depositPoints = [];
                    if (isset($doc->sac_workspaces) && is_array($doc->sac_workspaces)) {
                        foreach ($doc->sac_workspaces as $workspace) {
                            if (isset($workspace->sac_collections) && is_array($workspace->sac_collections)) {
                                foreach ($workspace->sac_collections as $collection) {
                                    $depositPoints["$collection->sac_href"] = "$collection->sac_colltitle";
                                }
                            }
                        }
                    }
                    $templateMgr->assign('swordDepositPoints', $depositPoints);
                }
                $templateMgr->assign('articles', $iterator);
                $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
                break;
        }
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, $args) {
        die('executeCLI unimplemented');
    }
}

?>