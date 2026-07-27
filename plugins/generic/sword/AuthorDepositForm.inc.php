<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sword/AuthorDepositForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDepositForm
 * @ingroup plugins_generic_sword
 *
 * @brief Form to perform an author's SWORD deposit(s).
 */

import('lib.pkp.classes.form.Form');

class AuthorDepositForm extends Form {
    
    /** @var PublishedArticle */
    protected $_article;

    /** @var SwordPlugin */
    protected $_swordPlugin;

    /**
     * Constructor.
     * @param SwordPlugin $swordPlugin
     * @param PublishedArticle $article
     */
    public function __construct($swordPlugin, $article) {
        parent::__construct($swordPlugin->getTemplatePath() . '/authorDepositForm.tpl');

        $this->_swordPlugin = $swordPlugin;
        $this->_article = $article;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param SwordPlugin $swordPlugin
     * @param PublishedArticle $article
     */
    public function AuthorDepositForm($swordPlugin, $article) {
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
     * Display the form.
     * @see Form::display()
     * @param PKPRequest|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);

        $depositPoints = $this->_getDepositableDepositPoints();
        // For the sake of the UI, figure out whether we're dealing with any
        // sword URLs where deposit points are to be chosen by the author.
        $hasFlexible = false;
        if (is_array($depositPoints)) {
            foreach ($depositPoints as $depositPoint) {
                if ($depositPoint['type'] === SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION) {
                    $hasFlexible = true;
                }
            }
        }
        $templateMgr->assign('depositPoints', $depositPoints);
        $templateMgr->assign('article', $this->_article);
        $templateMgr->assign('hasFlexible', $hasFlexible);
        $templateMgr->assign('allowAuthorSpecify', $this->_swordPlugin->getSetting((int) $this->_article->getJournalId(), 'allowAuthorSpecify'));
        parent::display($request, $template);
    }

    /**
     * Initialize form data from default settings.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        $this->_data = [];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'authorDepositUrl',
            'authorDepositUsername',
            'authorDepositPassword',
            'depositPoint'
        ]);
    }

    /**
     * Perform SWORD deposit.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $user = $request->getUser();
        if (!$user) {
            return;
        }

        import('classes.sword.OJSSwordDeposit');
        $deposit = new OJSSwordDeposit($this->_article);
        $deposit->setMetadata();
        $deposit->addEditorial();
        $deposit->createPackage();

        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        $allowAuthorSpecify = $this->_swordPlugin->getSetting((int) $this->_article->getJournalId(), 'allowAuthorSpecify');
        $authorDepositUrl = $this->getData('authorDepositUrl');
        
        if ($allowAuthorSpecify && $authorDepositUrl !== '') {
            $deposit->deposit(
                (string) $this->getData('authorDepositUrl'),
                (string) $this->getData('authorDepositUsername'),
                (string) $this->getData('authorDepositPassword')
            );

            $params = [
                'itemTitle' => $this->_article->getLocalizedTitle(), 
                'repositoryName' => (string) $this->getData('authorDepositUrl')
            ];
            $notificationManager->createTrivialNotification((int) $user->getId(), NOTIFICATION_TYPE_SWORD_DEPOSIT_COMPLETE, $params);
        }

        $depositableDepositPoints = $this->_getDepositableDepositPoints();
        $depositPoints = $this->getData('depositPoint');
        
        if (is_array($depositableDepositPoints) && is_array($depositPoints)) {
            foreach ($depositableDepositPoints as $key => $depositPoint) {
                if (!isset($depositPoints[$key]['enabled'])) {
                    continue;
                }

                if ($depositPoint['type'] === SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION) {
                    $url = (string) $depositPoints[$key]['depositPoint'];
                } else { // SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED
                    $url = (string) $depositPoint['url'];
                }

                $deposit->deposit(
                    $url,
                    (string) $depositPoint['username'],
                    (string) $depositPoint['password']
                );

                $params = [
                    'itemTitle' => $this->_article->getLocalizedTitle(), 
                    'repositoryName' => (string) $depositPoint['name']
                ];
                $notificationManager->createTrivialNotification((int) $user->getId(), NOTIFICATION_TYPE_SWORD_DEPOSIT_COMPLETE, $params);
            }
        }

        $deposit->cleanup();
    }

    /**
     * Get list of depositable points.
     * @return array
     */
    public function _getDepositableDepositPoints() {
        import('classes.sword.OJSSwordDeposit');
        $depositPoints = $this->_swordPlugin->getSetting((int) $this->_article->getJournalId(), 'depositPoints');
        
        if (!is_array($depositPoints)) {
            return [];
        }

        foreach ($depositPoints as $key => $depositPoint) {
            $type = $depositPoint['type'];
            if ($type === SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION) {
                // Get a list of supported deposit points
                $client = new SWORDAPPClient();
                $doc = $client->servicedocument(
                    (string) $depositPoint['url'],
                    (string) $depositPoint['username'],
                    (string) $depositPoint['password'],
                    ''
                );
                $points = [];
                // Ensure $doc has property before iteration
                if (isset($doc->sac_workspaces)) {
                    foreach ($doc->sac_workspaces as $workspace) {
                        if (isset($workspace->sac_collections)) {
                            foreach ($workspace->sac_collections as $collection) {
                                $points[(string) $collection->sac_href] = (string) $collection->sac_colltitle;
                            }
                        }
                    }
                }
                $depositPoints[$key]['depositPoints'] = $points;
            } elseif ($type === SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED) {
                // Don't need to do anything special
            } else {
                unset($depositPoints[$key]);
            }
        }
        return $depositPoints;
    }

}
?>