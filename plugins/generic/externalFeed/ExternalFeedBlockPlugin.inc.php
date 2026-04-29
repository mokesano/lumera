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
 * @brief Class for block component of external feed plugin
 * * MODERNIZED FOR PHP 8.x & OJS FORK (Wizdam Edition)
 * - Added Block-Specific CSS injection.
 * - Implemented Firewall Bypass logic.
 * - Redirected template to 'templates/' folder.
 * - Strict Syntax Compliance.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class ExternalFeedBlockPlugin extends BlockPlugin {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        $this->parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ExternalFeedBlockPlugin($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ExternalFeedBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }

    /**
     * Hide this plugin from the management interface (it's subsidiary)
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
     * Get the external feed plugin
     * @return object
     */
    public function getExternalFeedPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        return $plugin;
    }

    /**
     * Override the builtin to get the correct plugin path.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getExternalFeedPlugin();
        return $plugin->getPluginPath();
    }

    /**
     * Override Template Filename to point to 'templates/' folder.
     * COMPATIBILITY FIX: Added $request = null to match Parent Class signature.
     */
    public function getBlockTemplateFilename($request = null) {
        return 'templates/block.tpl';
    }

    /**
     * Get the HTML contents for this block.
     * [WIZDAM LOGIC PRESERVED] Firewall Bypass & CSS Injection
     */
    public function getContents($templateMgr, $request = null) {
        // FIX: Gunakan $request jika tersedia, fallback ke Registry
        if (!$request) {
            $request = Registry::get('request');
        }
        
        $journal = $request ? $request->getJournal() : Request::getJournal();
        if (!$journal) return '';
    
        $plugin = $this->getExternalFeedPlugin();
        
        // FIX: Teruskan $request ke getEnabled()
        if (!$plugin->getEnabled($request)) return '';
    
        $requestedPage = Request::getRequestedPage();
        $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
        
        $plugin->import('simplepie.SimplePie');
        import('lib.pkp.classes.core.PKPString');
    
        $feeds = $externalFeedDao->getExternalFeedsByJournalId($journal->getId());
        $externalFeeds = array();
    
        while ($currentFeed = $feeds->next()) {
            $displayBlock = $currentFeed->getDisplayBlock();
            
            if (($displayBlock == EXTERNAL_FEED_DISPLAY_BLOCK_NONE) ||
                (($displayBlock == EXTERNAL_FEED_DISPLAY_BLOCK_HOMEPAGE &&
                (!empty($requestedPage)) && $requestedPage != 'index'))
            ) continue;
    
            $feed = new SimplePie();
            $feedUrl = $currentFeed->getUrl();
    
            $feedParts = parse_url($feedUrl);
            $feedHost = isset($feedParts['host']) ? $feedParts['host'] : '';
            $currentHost = $_SERVER['HTTP_HOST'];
    
            $cleanFeedHost = str_replace('www.', '', $feedHost);
            $cleanCurrentHost = str_replace('www.', '', $currentHost);
    
            $curlOptions = array(
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_ENCODING       => '',
            );
    
            if ($feedHost && stripos($cleanCurrentHost, $cleanFeedHost) !== false) {
                $serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
                $curlOptions[CURLOPT_RESOLVE] = array(
                    $feedHost . ":443:" . $serverIP,
                    $feedHost . ":80:" . $serverIP
                );
                $feed->set_useragent('SangiaFeedSystem_Internal_Verif');
            } else {
                $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            }
    
            $feed->set_curl_options($curlOptions);
            $feed->set_feed_url($feedUrl);
            $feed->enable_order_by_date(false);
            $feed->set_cache_location(CacheManager::getFileCachePath());
            $feed->init();
    
            if ($currentFeed->getLimitItems()) {
                $recentItems = $currentFeed->getRecentItems();
            } else {
                $recentItems = 0;
            }
    
            if ($feed->get_item_quantity() > 0) {
                $externalFeeds[] = array(
                    'title' => $currentFeed->getLocalizedTitle(),
                    'items' => $feed->get_items(0, $recentItems)
                );
            }
        }
    
        if (empty($externalFeeds)) return '';
    
        $templateMgr->addStyleSheet(
            Request::getBaseUrl() . '/' . $this->getPluginPath() . '/css/externalFeedBlock.css'
        );
        $templateMgr->assign('externalFeeds', $externalFeeds);
    
        // FIX: Fetch template sendiri, jangan panggil parent yang return ''
        return $templateMgr->fetch(
            $this->getTemplatePath() . $this->getBlockTemplateFilename($request)
        );
    }
}
?>