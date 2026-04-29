<?php
declare(strict_types=1);

/**
 * @file plugins/generic/announcementFeed/AnnouncementFeedBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementFeedBlockPlugin
 * @ingroup plugins_generic_announcementFeed
 *
 * @brief Class for block component of announcement feed plugin
 * [WIZDAM EDITION] Modernized & Safe Parent Bypass
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class AnnouncementFeedBlockPlugin extends BlockPlugin {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;
    
    /**
     * Constructor.
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->parentPluginName = $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementFeedBlockPlugin($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AnnouncementFeedBlockPlugin(). Please refactor to parent::__construct().", 
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
     * @return String name of plugin
     */
    public function getName(): string {
        return 'AnnouncementFeedBlockPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.generic.announcementfeed.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String
     */
    public function getDescription(): string {
        return __('plugins.generic.announcementfeed.description');
    }

    /**
     * Get the announcement feed plugin
     * @return object
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
     * Get the HTML contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // [WIZDAM] Prioritaskan inject $request, fallback ke Request::
        $journal = ($request) ? $request->getJournal() : Request::getJournal();
        
        if (!$journal) return '';

        if (!$journal->getSetting('enableAnnouncements')) return '';

        $plugin = $this->getAnnouncementFeedPlugin();
        $displayPage = $plugin->getSetting($journal->getId(), 'displayPage');
        
        // Ambil requestedPage dengan aman
        $requestedPage = ($request) ? $request->getRequestedPage() : Request::getRequestedPage();

        if (($displayPage == 'all') || ($displayPage == 'homepage' && (empty($requestedPage) || $requestedPage == 'index' || $requestedPage == 'announcement')) || ($displayPage == $requestedPage)) {
            
            // [WIZDAM] SOLUSI BYPASS PARENT (WAJIB)
            // Kita render manual agar konsisten dengan BlockPlugin modern lainnya
            $templateFilename = $this->getBlockTemplateFilename($request);
            if ($templateFilename === null) return '';
            
            return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
        } else {
            return '';
        }
    }
}

?>