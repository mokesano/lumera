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
 * @brief Form for journal managers to modify external feed plugin settings.
 */

import('lib.pkp.classes.form.Form');

class ExternalFeedForm extends Form {

    /** @var object */
    protected $_plugin;

    /** @var int|null */
    protected $_feedId;

    /**
     * Constructor.
     * @param object $plugin
     * @param int|null $feedId
     */
    public function __construct($plugin, $feedId) {
        $this->_plugin = $plugin;
        $this->_feedId = $feedId !== null ? (int) $feedId : null;

        parent::__construct($plugin->getTemplatePath() . 'templates/externalFeedForm.tpl');

        $this->addCheck(new FormValidatorUrl($this, 'feedUrl', 'required', 'plugins.generic.externalFeed.form.feedUrlValid'));
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'plugins.generic.externalFeed.form.titleRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param object $plugin
     * @param int|null $feedId
     */
    public function ExternalFeedForm($plugin, $feedId) {
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
     * Get the names of fields for which localized data is allowed.
     * @return array
     */
    public function getLocaleFieldNames() {
        /** @var ExternalFeedDAO $feedDao */
        $feedDao = DAORegistry::getDAO('ExternalFeedDAO');
        return $feedDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('feedId', $this->_feedId);

        $this->_plugin->import('ExternalFeed');

        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @return void
     */
    public function initData() {
        if ($this->_feedId !== null) {
            /** @var ExternalFeedDAO $feedDao */
            $feedDao = DAORegistry::getDAO('ExternalFeedDAO');
            $feed = $feedDao->getExternalFeed($this->_feedId);

            if ($feed !== null) {
                $this->_data = [
                    'feedUrl' => (string) $feed->getUrl(),
                    'title' => $feed->getTitle(null),
                    'displayHomepage' => (int) $feed->getDisplayHomepage(),
                    'displayBlock' => (int) $feed->getDisplayBlock(),
                    'limitItems' => (int) $feed->getLimitItems(),
                    'recentItems' => (int) $feed->getRecentItems()
                ];
            } else {
                $this->_feedId = null;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'feedUrl',
            'title',
            'displayHomepage',
            'displayBlock',
            'limitItems',
            'recentItems'
        ]);

        $recentItems = $this->getData('recentItems');
        if ($recentItems !== null && (int) $recentItems <= 0) {
            $this->setData('recentItems', '');
        }

        if ($this->getData('limitItems')) {
            $this->addCheck(new FormValidator($this, 'recentItems', 'required', 'plugins.generic.externalFeed.settings.recentItemsRequired'));
        }
    }

    /**
     * Save settings.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;
        $plugin = $this->_plugin;

        /** @var ExternalFeedDAO $externalFeedDao */
        $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
        $plugin->import('ExternalFeed');

        $feed = null;
        if ($this->_feedId !== null) {
            $feed = $externalFeedDao->getExternalFeed($this->_feedId);
        }

        if ($feed === null) {
            $feed = new ExternalFeed();
        }

        $feed->setJournalId($journalId);
        $feed->setUrl((string) $this->getData('feedUrl'));
        $feed->setTitle($this->getData('title'), null);
        
        $feed->setDisplayHomepage($this->getData('displayHomepage') ? 1 : 0);
        
        $displayBlock = $this->getData('displayBlock');
        $feed->setDisplayBlock($displayBlock ? (int) $displayBlock : EXTERNAL_FEED_DISPLAY_BLOCK_NONE);
        
        $feed->setLimitItems($this->getData('limitItems') ? 1 : 0);
        
        $recentItems = $this->getData('recentItems');
        $feed->setRecentItems($recentItems !== null && $recentItems !== '' ? (int) $recentItems : 0);

        if ($feed->getId() !== null) {
            $externalFeedDao->updateExternalFeed($feed);
        } else {
            $feed->setSeq(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
            $externalFeedDao->insertExternalFeed($feed);
            $externalFeedDao->resequenceExternalFeeds($feed->getJournalId());
        }
    }
    
}
?>