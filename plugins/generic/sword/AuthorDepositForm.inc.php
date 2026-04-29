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
 * @brief Form to perform an author's SWORD deposit(s)
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class AuthorDepositForm extends Form {
    
    /** @var object */
    public $article;

    /** @var object */
    public $swordPlugin;

    /**
     * Constructor.
     * @param object $swordPlugin
     * @param object $article
     */
    public function __construct($swordPlugin, $article) {
        parent::__construct($swordPlugin->getTemplatePath() . '/authorDepositForm.tpl');

        $this->swordPlugin = $swordPlugin;
        $this->article = $article;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorDepositForm($swordPlugin, $article) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::AuthorDepositForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display the form.
     * @param PKPRequest $request
     * @param string $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();

        $depositPoints = $this->_getDepositableDepositPoints();
        // For the sake of the UI, figure out whether we're dealing with any
        // sword URLs where deposit points are to be chosen by the author.
        $hasFlexible = false;
        if (is_array($depositPoints)) {
            foreach ($depositPoints as $depositPoint) {
                if ($depositPoint['type'] == SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION) {
                    $hasFlexible = true;
                }
            }
        }
        $templateMgr->assign('depositPoints', $depositPoints);
        $templateMgr->assign('article', $this->article);
        $templateMgr->assign('hasFlexible', $hasFlexible);
        $templateMgr->assign('allowAuthorSpecify', $this->swordPlugin->getSetting($this->article->getJournalId(), 'allowAuthorSpecify'));
        parent::display($request, $template);
    }

    /**
     * Initialize form data from default settings.
     * @see Form::initData()
     */
    public function initData() {
        $this->_data = [];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
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
     * Perform SWORD deposit
     * @param PKPRequest $request
     * @see Form::execute()
     */
    public function execute($object = null) {
        $user = $request->getUser();
        import('classes.sword.OJSSwordDeposit');
        $deposit = new OJSSwordDeposit($this->article);
        $deposit->setMetadata();
        $deposit->addEditorial();
        $deposit->createPackage();

        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        $allowAuthorSpecify = $this->swordPlugin->getSetting($this->article->getJournalId(), 'allowAuthorSpecify');
        $authorDepositUrl = $this->getData('authorDepositUrl');
        if ($allowAuthorSpecify && $authorDepositUrl != '') {
            $deposit->deposit(
                $this->getData('authorDepositUrl'),
                $this->getData('authorDepositUsername'),
                $this->getData('authorDepositPassword')
            );

            $params = ['itemTitle' => $this->article->getLocalizedTitle(), 'repositoryName' => $this->getData('authorDepositUrl')];
            $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SWORD_DEPOSIT_COMPLETE, $params);
        }

        $depositableDepositPoints = $this->_getDepositableDepositPoints();
        $depositPoints = $this->getData('depositPoint');
        
        if (is_array($depositableDepositPoints) && is_array($depositPoints)) {
            foreach ($depositableDepositPoints as $key => $depositPoint) {
                if (!isset($depositPoints[$key]['enabled'])) continue;

                if ($depositPoint['type'] == SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION) {
                    $url = $depositPoints[$key]['depositPoint'];
                } else { // SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED
                    $url = $depositPoint['url'];
                }

                $deposit->deposit(
                    $url,
                    $depositPoint['username'],
                    $depositPoint['password']
                );

                $user = $request->getUser();
                $params = ['itemTitle' => $this->article->getLocalizedTitle(), 'repositoryName' => $depositPoint['name']];
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SWORD_DEPOSIT_COMPLETE, $params);
            }
        }

        $deposit->cleanup();
    }

    /**
     * Get list of depositable points
     * @return array
     */
    public function _getDepositableDepositPoints() {
        import('classes.sword.OJSSwordDeposit');
        $depositPoints = $this->swordPlugin->getSetting($this->article->getJournalId(), 'depositPoints');
        
        if (!is_array($depositPoints)) return [];

        foreach ($depositPoints as $key => $depositPoint) {
            $type = $depositPoint['type'];
            if ($type == SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION) {
                // Get a list of supported deposit points
                $client = new SWORDAPPClient();
                $doc = $client->servicedocument(
                    $depositPoint['url'],
                    $depositPoint['username'],
                    $depositPoint['password'],
                    ''
                );
                $points = [];
                // [PHP 8 FIX] Ensure $doc has property before iteration
                if (isset($doc->sac_workspaces)) {
                    foreach ($doc->sac_workspaces as $workspace) {
                        if (isset($workspace->sac_collections)) {
                            foreach ($workspace->sac_collections as $collection) {
                                $points["$collection->sac_href"] = "$collection->sac_colltitle";
                            }
                        }
                    }
                }
                unset($client);
                unset($doc);
                $depositPoints[$key]['depositPoints'] = $points;
            } elseif ($type == SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED) {
                // Don't need to do anything special
            } else {
                unset($depositPoints[$key]);
            }
        }
        return $depositPoints;
    }
}
?>