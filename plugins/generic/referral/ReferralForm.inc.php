<?php
declare(strict_types=1);

/**
 * @file plugins/generic/referral/ReferralForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReferralForm
 * @ingroup manager_form
 * @see AnnouncementForm
 *
 * @brief Form for authors to create/edit referrals.
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class ReferralForm extends Form {
    
    /** @var int the ID of the referral being edited */
    public $referralId;

    /** @var object the article this referral refers to */
    public $article;

    /**
     * Constructor
     * @param object $plugin
     * @param Article $article
     * @param int|null $referralId leave as default for new referral
     */
    public function __construct($plugin, $article, $referralId = null) {
        $this->referralId = isset($referralId) ? (int) $referralId : null;
        $this->article = $article;

        parent::__construct($plugin->getTemplatePath() . 'referralForm.tpl');

        // Name is provided
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'plugins.generic.referral.nameRequired'));
        $this->addCheck(new FormValidatorURL($this, 'url', 'required', 'plugins.generic.referral.urlRequired'));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReferralForm($plugin, $article, $referralId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ReferralForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get a list of localized field names for this form
     * @return array
     */
    public function getLocaleFieldNames() {
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        return $referralDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('referralId', $this->referralId);
        $templateMgr->assign('article', $this->article);
        // $templateMgr->assign('helpTopicId', 'FIXME');

        parent::display($request, $template);
    }

    /**
     * Initialize form data from current referral.
     */
    public function initData() {
        if (isset($this->referralId)) {
            $referralDao = DAORegistry::getDAO('ReferralDAO');
            $referral = $referralDao->getReferral($this->referralId);

            if ($referral != null) {
                $this->_data = [
                    'name' => $referral->getName(null), // Localized
                    'status' => $referral->getStatus(),
                    'url' => $referral->getUrl()
                ];

            } else {
                $this->referralId = null;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['name', 'url', 'status']);
    }

    /**
     * Save referral. 
     */
    public function execute($object = null) {
        $referralDao = DAORegistry::getDAO('ReferralDAO');

        $referral = null;
        if (isset($this->referralId)) {
            $referral = $referralDao->getReferral($this->referralId);
        }

        if (!isset($referral)) {
            $referral = new Referral();
            $referral->setDateAdded(Core::getCurrentDate());
            $referral->setLinkCount(0);
        }

        $referral->setArticleId($this->article->getId());
        $referral->setName($this->getData('name'), null); // Localized
        $referral->setUrl($this->getData('url'));
        $referral->setStatus($this->getData('status'));

        // Update or insert referral
        if ($referral->getId() != null) {
            $referralDao->updateReferral($referral);
        } else {
            $referralDao->insertReferral($referral);
        }
    }
}

?>