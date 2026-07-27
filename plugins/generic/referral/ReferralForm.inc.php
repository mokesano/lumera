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
 */

import('lib.pkp.classes.form.Form');

class ReferralForm extends Form {
    
    /** @var int|null The ID of the referral being edited */
    protected $_referralId;

    /** @var Article The article this referral refers to */
    protected $_article;

    /**
     * Constructor.
     * @param object $plugin
     * @param Article $article
     * @param int|null $referralId
     */
    public function __construct($plugin, $article, $referralId = null) {
        $this->_referralId = $referralId !== null ? (int) $referralId : null;
        $this->_article = $article;

        parent::__construct($plugin->getTemplatePath() . 'referralForm.tpl');

        // Name is provided
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'plugins.generic.referral.nameRequired'));
        $this->addCheck(new FormValidatorURL($this, 'url', 'required', 'plugins.generic.referral.urlRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param object $plugin
     * @param Article $article
     * @param int|null $referralId
     */
    public function ReferralForm($plugin, $article, $referralId = null) {
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
     * Get a list of localized field names for this form.
     * @return array
     */
    public function getLocaleFieldNames() {
        /** @var ReferralDAO $referralDao */
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        return $referralDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param mixed $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('referralId', $this->_referralId);
        $templateMgr->assign('article', $this->_article);

        parent::display($request, $template);
    }

    /**
     * Initialize form data from current referral.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        if ($this->_referralId !== null) {
            /** @var ReferralDAO $referralDao */
            $referralDao = DAORegistry::getDAO('ReferralDAO');
            $referral = $referralDao->getReferral($this->_referralId);

            if ($referral !== null) {
                $this->_data = [
                    'name' => $referral->getName(null),
                    'status' => (int) $referral->getStatus(),
                    'url' => (string) $referral->getURL()
                ];
            } else {
                $this->_referralId = null;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['name', 'url', 'status']);
    }

    /**
     * Save referral.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        /** @var ReferralDAO $referralDao */
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        $referral = null;
        
        if ($this->_referralId !== null) {
            $referral = $referralDao->getReferral($this->_referralId);
        }

        if ($referral === null) {
            $referral = new Referral();
            $referral->setDateAdded(Core::getCurrentDate());
            $referral->setLinkCount(0);
        }

        $referral->setArticleId((int) $this->_article->getId());
        $referral->setName((string) $this->getData('name'), null);
        $referral->setURL((string) $this->getData('url'));
        $referral->setStatus((int) $this->getData('status'));

        // Update or insert referral
        if ($referral->getId() !== null && $referral->getId() !== 0) {
            $referralDao->updateReferral($referral);
        } else {
            $referralDao->replaceReferral($referral);
        }
    }

}
?>