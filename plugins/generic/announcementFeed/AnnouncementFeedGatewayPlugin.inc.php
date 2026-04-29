<?php
declare(strict_types=1);

/**
 * @file plugins/generic/announcementFeed/AnnouncementFeedGatewayPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementFeedGatewayPlugin
 * @ingroup plugins_generic_announcementFeed
 *
 * @brief Gateway component of announcement feed plugin
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('classes.plugins.GatewayPlugin');

class AnnouncementFeedGatewayPlugin extends GatewayPlugin {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->parentPluginName = $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementFeedGatewayPlugin($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AnnouncementFeedGatewayPlugin(). Please refactor to parent::__construct().", 
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
        return 'AnnouncementFeedGatewayPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.announcementfeed.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.announcementfeed.description');
    }

    /**
     * Get the web feed plugin
     * @return AnnouncementFeedPlugin
     */
    public function getAnnouncementFeedPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        return $plugin;
    }

    /**
     * Override the builtin to get the correct plugin path.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getAnnouncementFeedPlugin();
        return $plugin->getPluginPath();
    }

    /**
     * Override the builtin to get the correct template path.
     * @return string
     */
    public function getTemplatePath(): string {
        $plugin = $this->getAnnouncementFeedPlugin();
        return $plugin->getTemplatePath() . 'templates/';
    }

    /**
     * Get whether or not this plugin is enabled.
     * @return string
     */
    public function getEnabled(): bool {
        $plugin = $this->getAnnouncementFeedPlugin();
        return $plugin->getEnabled(); 
    }

    /**
     * Get the management verbs for this plugin
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return array();
    }

    /**
     * Handle fetch requests for this plugin.
     * @param $args array
     * @param $request PKPRequest
     */
    public function fetch($args, $request = null) {
        // Make sure we're within a Journal context
        // [WIZDAM] Prioritaskan inject $request
        $journal = ($request) ? $request->getJournal() : Request::getJournal();
        
        if (!$journal) return false;

        // Make sure announcements and plugin are enabled
        $announcementsEnabled = $journal->getSetting('enableAnnouncements');
        $announcementFeedPlugin = $this->getAnnouncementFeedPlugin();
        
        if (!$announcementsEnabled || !$announcementFeedPlugin->getEnabled()) return false;

        // Make sure the feed type is specified and valid
        $type = array_shift($args);
        $typeMap = array(
            'rss' => 'rss.tpl',
            'rss2' => 'rss2.tpl',
            'atom' => 'atom.tpl'
        );
        $mimeTypeMap = array(
            'rss' => 'application/rdf+xml',
            'rss2' => 'application/rss+xml',
            'atom' => 'application/atom+xml'
        );
        if (!isset($typeMap[$type])) return false;

        // Get limit setting, if any 
        $limitRecentItems = $announcementFeedPlugin->getSetting($journal->getId(), 'limitRecentItems');
        $recentItems = (int) $announcementFeedPlugin->getSetting($journal->getId(), 'recentItems');

        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        $journalId = $journal->getId();
        
        if ($limitRecentItems && $recentItems > 0) {
            import('lib.pkp.classes.db.DBResultRange');
            $rangeInfo = new DBResultRange($recentItems, 1);
            $announcements = $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $journalId, $rangeInfo);
        } else {
            $announcements = $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $journalId);
        }

        // Get date of most recent announcement
        $lastDateUpdated = $announcementFeedPlugin->getSetting($journal->getId(), 'dateUpdated');
        if ($announcements->wasEmpty()) {
            if (empty($lastDateUpdated)) { 
                $dateUpdated = Core::getCurrentDate(); 
                $announcementFeedPlugin->updateSetting($journal->getId(), 'dateUpdated', $dateUpdated, 'string');             
            } else {
                $dateUpdated = $lastDateUpdated;
            }
        } else {
            $mostRecentAnnouncement = $announcementDao->getMostRecentPublishedAnnouncementByAssocId(ASSOC_TYPE_JOURNAL, $journalId);
            if ($mostRecentAnnouncement) {
                $dateUpdated = $mostRecentAnnouncement->getDatetimePosted();
                if (empty($lastDateUpdated) || (strtotime($dateUpdated) > strtotime($lastDateUpdated))) { 
                    $announcementFeedPlugin->updateSetting($journal->getId(), 'dateUpdated', $dateUpdated, 'string');             
                }
            } else {
                $dateUpdated = Core::getCurrentDate();
            }
        }

        $versionDao = DAORegistry::getDAO('VersionDAO');
        $version = $versionDao->getCurrentVersion();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('ojsVersion', $version->getVersionString());
        $templateMgr->assign('selfUrl', Request::getCompleteUrl()); 
        $templateMgr->assign('dateUpdated', $dateUpdated);
        
        // [MODERNISASI] Gunakan assign untuk array
        $templateMgr->assign('announcements', $announcements->toArray());
        $templateMgr->assign('journal', $journal);

        $templateMgr->display($this->getTemplatePath() . $typeMap[$type], $mimeTypeMap[$type]);

        return true;
    }
}

?>