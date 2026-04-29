<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/ExternalFeedForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExternalFeedForm
 * @ingroup plugins_generic_externalFeed
 *
 * @brief Form for journal managers to modify external feed plugin settings
 * * MODERNIZED FOR PHP 8.x & OJS FORK (Wizdam Edition)
 * - Implemented proper __construct.
 * - Removed obsolete var keywords.
 * - Strict type casting for security.
 */

import('lib.pkp.classes.form.Form');

class ExternalFeedForm extends Form {

    /** @var object The parent plugin object */
    public $plugin;

    /** @var int The feed ID being edited */
    public $feedId;

    /**
     * Constructor
     * @param $plugin object
     * @param $feedId int
     */
    public function __construct($plugin, $feedId) {
        $this->plugin = $plugin;
        $this->feedId = isset($feedId) ? (int) $feedId : null;

        // [WIZDAM FIX] Change parent::Form to parent::__construct
        parent::__construct($plugin->getTemplatePath() . 'templates/externalFeedForm.tpl');

        // Feed URL is provided
        $this->addCheck(new FormValidatorUrl($this, 'feedUrl', 'required', 'plugins.generic.externalFeed.form.feedUrlValid'));

        // Feed title is provided
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'plugins.generic.externalFeed.form.titleRequired'));

        // CSRF Protection
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ExternalFeedForm($plugin, $feedId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ExternalFeedForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($plugin, $feedId);
    }

    /** 
     * Get the names of fields for which localized data is allowed.
     * @return array
     */
    public function getLocaleFieldNames() {
        $feedDao = DAORegistry::getDAO('ExternalFeedDAO');
        return $feedDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('feedId', $this->feedId);

        $plugin = $this->plugin; 
        $plugin->import('ExternalFeed');

        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        if (isset($this->feedId)) {
            $feedDao = DAORegistry::getDAO('ExternalFeedDAO');
            $feed = $feedDao->getExternalFeed($this->feedId);

            if ($feed != null) {
                $this->_data = array(
                    'feedUrl' => $feed->getUrl(),
                    'title' => $feed->getTitle(null), // Localized title
                    'displayHomepage' => $feed->getDisplayHomepage(),
                    'displayBlock' => $feed->getDisplayBlock(),
                    'limitItems' => $feed->getLimitItems(),
                    'recentItems' => $feed->getRecentItems()
                );
            } else {
                $this->feedId = null;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            array(
                'feedUrl',
                'title',
                'displayHomepage',
                'displayBlock',
                'limitItems',
                'recentItems'
            )
        );

        // Security: Ensure strict integer casting
        if ((int) $this->getData('recentItems') <= 0) {
            $this->setData('recentItems', '');
        }

        // If limit items is selected, check that we have a value
        if ($this->getData('limitItems')) {
            $this->addCheck(new FormValidator($this, 'recentItems', 'required', 'plugins.generic.externalFeed.settings.recentItemsRequired'));
        }
    }

    /**
     * Save settings. 
     */
    public function execute($object = null) {
        $journal = Request::getJournal();
        $journalId = $journal->getId();
        $plugin = $this->plugin;

        $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
        $plugin->import('ExternalFeed');

        // Logic penentuan Update atau Insert
        $feed = null;
        if (isset($this->feedId)) {
            $feed = $externalFeedDao->getExternalFeed($this->feedId);
        }

        if (!$feed) {
            $feed = new ExternalFeed();
        }

        // Data Assignment with Type Casting (Security Best Practice)
        $feed->setJournalId((int) $journalId);
        $feed->setUrl($this->getData('feedUrl')); // URL is validated by FormValidatorUrl
        $feed->setTitle($this->getData('title'), null); // Localized
        
        $feed->setDisplayHomepage($this->getData('displayHomepage') ? 1 : 0);
        $feed->setDisplayBlock($this->getData('displayBlock') ? (int) $this->getData('displayBlock') : EXTERNAL_FEED_DISPLAY_BLOCK_NONE);
        $feed->setLimitItems($this->getData('limitItems') ? 1 : 0);
        
        // Ensure recentItems is integer
        $recentItems = $this->getData('recentItems') ? (int) $this->getData('recentItems') : 0;
        $feed->setRecentItems($recentItems);

        // Update or insert external feed
        if ($feed->getId() != null) {
            $externalFeedDao->updateExternalFeed($feed);
        } else {
            // Set initial sequence
            $feed->setSeq(REALLY_BIG_NUMBER);
            $externalFeedDao->insertExternalFeed($feed);

            // Re-order the feeds so the new one is at the end of the list.
            $externalFeedDao->resequenceExternalFeeds($feed->getJournalId());
        }
    }
}
?>