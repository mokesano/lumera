<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/PLNStatusForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PLNStatusForm
 * @ingroup plugins_generic_pln
 *
 * @brief Form for journal managers to check PLN plugin status
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class PLNStatusForm extends Form {

    /**
     * @var int
     */
    protected $_journalId;

    /**
     * @var object
     */
    protected $_plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = $journalId;
        $this->_plugin = $plugin;            
        parent::__construct($plugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'status.tpl');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PLNStatusForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::PLNStatusForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param PKPRequest $request
     * @param string $template
     */
    public function display($request = null, $template = null) {
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $journal = Request::getJournal();
        $networkStatus = $this->_plugin->getSetting($journal->getId(), 'pln_accepting');
        $networkStatusMessage = $this->_plugin->getSetting($journal->getId(), 'pln_accepting_message');
        $rangeInfo = Handler::getRangeInfo('deposits');
        
        if (!$networkStatusMessage) {
            if ($networkStatus === true) {
                $networkStatusMessage = __('plugins.generic.pln.notifications.pln_accepting');
            } else {
                $networkStatusMessage = __('plugins.generic.pln.notifications.pln_not_accepting');
            }
        }
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('deposits', $depositDao->getDepositsByJournalId($journal->getId(),$rangeInfo));
        $templateMgr->assign('networkStatus', $networkStatus);
        $templateMgr->assign('networkStatusMessage', $networkStatusMessage);
        $templateMgr->assign('plnStatusDocs', $this->_plugin->getSetting($journal->getId(), 'pln_status_docs'));
        parent::display($request, $template);
    }
}
?>