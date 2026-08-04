<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/ExternalFeedBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExternalFeedBlockPlugin
 * @ingroup plugins_generic_externalFeed
 *
 * @brief Class for block component of external feed plugin.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class ExternalFeedBlockPlugin extends BlockPlugin {
    
    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        $this->_parentPluginName = (string) $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function ExternalFeedBlockPlugin($parentPluginName) {
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
     * Hide this plugin from the management interface.
     * @return bool
     */
    public function getHideManagement(): bool {
        return true;
    }

    /**
     * Get the name of this plugin.
     * @return string
     */
    public function getName(): string {
        return 'ExternalFeedBlockPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.externalFeed.block.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.externalFeed.description');
    }

    /**
     * Get the external feed plugin.
     * @return object|null
     */
    public function getExternalFeedPlugin() {
        return PluginRegistry::getPlugin('generic', $this->_parentPluginName);
    }

    /**
     * Override the builtin to get the correct plugin path.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getExternalFeedPlugin();
        return $plugin !== null ? $plugin->getPluginPath() : '';
    }

    /**
     * Override Template Filename to point to 'templates/' folder.
     * @param object|null $request
     * @return string
     */
    public function getBlockTemplateFilename($request = null) {
        return 'templates/block.tpl';
    }

    /**
     * Get the HTML contents for this block.
     * @param object $templateMgr
     * @param object|null $request
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();
        
        if ($journal === null) {
            return '';
        }

        $plugin = $this->getExternalFeedPlugin();
        if ($plugin === null || !$plugin->getEnabled($request)) {
            return '';
        }

        $requestedPage = $request->getRequestedPage();
        /** @var ExternalFeedDAO $externalFeedDao */
        $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');

        require_once Core::getBaseDir() . '/lib/wizdam/library/autoload.php';
        if (!class_exists('SimplePie') && class_exists('SimplePie\SimplePie')) {
            class_alias('SimplePie\SimplePie', 'SimplePie');
        }
        import('lib.pkp.classes.core.PKPString');

        $feeds = $externalFeedDao->getExternalFeedsByJournalId($journal->getId());
        $externalFeeds = [];

        while ($currentFeed = $feeds->next()) {
            $displayBlock = (int) $currentFeed->getDisplayBlock();
            
            if ($displayBlock === EXTERNAL_FEED_DISPLAY_BLOCK_NONE ||
                ($displayBlock === EXTERNAL_FEED_DISPLAY_BLOCK_HOMEPAGE && !empty($requestedPage) && $requestedPage !== 'index')
            ) {
                continue;
            }

            $feed = new SimplePie();
            $feedUrl = (string) $currentFeed->getUrl();
            $feedParts = parse_url($feedUrl);
            $feedHost = $feedParts['host'] ?? '';
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';

            $cleanFeedHost = str_replace('www.', '', $feedHost);
            $cleanCurrentHost = str_replace('www.', '', $currentHost);

            $curlOptions = [
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_ENCODING       => '',
            ];

            if ($feedHost !== '' && stripos($cleanCurrentHost, $cleanFeedHost) !== false) {
                $serverIP = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
                $curlOptions[CURLOPT_RESOLVE] = [
                    $feedHost . ":443:" . $serverIP,
                    $feedHost . ":80:" . $serverIP
                ];
                $feed->set_useragent('SangiaFeedSystem_Internal_Verif');
            } else {
                $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            }

            $feed->set_curl_options($curlOptions);
            $feed->set_feed_url($feedUrl);
            $feed->enable_order_by_date(false);
            $feed->set_cache_location(CacheManager::getFileCachePath());
            $feed->init();

            $recentItems = $currentFeed->getLimitItems() ? (int) $currentFeed->getRecentItems() : 0;

            if ($feed->get_item_quantity() > 0) {
                $externalFeeds[] = [
                    'title' => (string) $currentFeed->getLocalizedTitle(),
                    'items' => $feed->get_items(0, $recentItems)
                ];
            }
        }

        if (empty($externalFeeds)) {
            return '';
        }

        $templateMgr->addStyleSheet(
            'externalFeedBlockCss',
            $request->getBaseUrl() . '/' . $this->getPluginPath() . '/css/externalFeedBlock.css'
        );
        $templateMgr->assign('externalFeeds', $externalFeeds);

        return $templateMgr->fetch(
            $this->getTemplatePath() . $this->getBlockTemplateFilename($request)
        );
    }
    
}
?>