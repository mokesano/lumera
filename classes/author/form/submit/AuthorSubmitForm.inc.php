<?php
declare(strict_types=1);

/**
 * @defgroup author_form_submit
 */

/**
 * @file classes/author/form/submit/AuthorSubmitForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitForm
 * @ingroup author_form_submit
 *
 * @brief Base class for journal author submit forms.
 */

import('lib.pkp.classes.form.Form');

class AuthorSubmitForm extends Form {

    /** @var PKPRequest|null */
    protected $request = null;

    /** @var int|null the ID of the article */
    protected $articleId = null;

    /** @var Article|null current article */
    protected $article = null;

    /** @var int the current step */
    protected $step = 0;

    /** @var array daftar kode locale yang didukung untuk submission ini */
    protected $submissionLocales = [];

    /**
     * Constructor.
     * @param Article|null $article
     * @param int $step
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function __construct($article, $step, $journal, $request) {
        // Provide available submission languages. (Convert the array
        // of locale symbolic names xx_XX into an associative array
        // of symbolic names => readable names.)
        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (empty($supportedSubmissionLocales)) $supportedSubmissionLocales = array($journal->getPrimaryLocale());
        
        $formLocales = array_flip(array_intersect(
            array_flip(AppLocale::getAllLocales()),
            $supportedSubmissionLocales
        ));
        $this->submissionLocales = array_keys($formLocales);

        parent::__construct(
            sprintf('author/submit/step%d.tpl', $step),
            true,
            $article ? $article->getLocale() : AppLocale::getLocale(),
            $formLocales
        );
        $this->addCheck(new FormValidatorPost($this));
        $this->step = (int) $step;
        $this->article = $article;
        $this->articleId = $article ? $article->getId() : null;
        $this->request = $request;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param Article|null $article
     * @param int $step
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public function AuthorSubmitForm($article, $step, $journal, $request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($article, $step, $journal, $request);
    }

    /**
     * Get list of locale codes supported for this submission.
     * @return array
     */
    public function getSubmissionLocales() {
        return $this->submissionLocales;
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('articleId', $this->articleId);
        $templateMgr->assign('submitStep', $this->step);

        $templateMgr->assign(
            'submissionProgress',
            $this->article ? $this->article->getSubmissionProgress() : 0
        );
        switch($this->step) {
            case 3:
                $helpTopicId = 'submission.indexingAndMetadata';
                break;
            case 4:
                $helpTopicId = 'submission.supplementaryFiles';
                break;
            default:
                $helpTopicId = 'submission.index';
        }
        $templateMgr->assign('helpTopicId', $helpTopicId);

        // [WIZDAM] Use properties or passed request, ensure consistency
        $journal = $this->request ? $this->request->getJournal() : $request->getJournal();

        /** @var JournalSettingsDAO $settingsDao  */
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettings = $journal ? $settingsDao->getJournalSettings($journal->getId()) : array();
        $templateMgr->assign('journalSettings', is_array($journalSettings) ? $journalSettings : array());

        parent::display($request, $template);
    }

    /**
     * Get the default form locale.
     * @return string
     */
    public function getDefaultFormLocale() {
        if ($this->article) return $this->article->getLocale();
        return parent::getDefaultFormLocale();
    }

    /**
     * Automatically assign Section Editors to new submissions.
     * @param Article $article
     * @return array of section editors
     */
    public static function assignEditors($article) {
        $sectionId = $article->getSectionId();
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO'); /** @var SectionEditorsDAO $sectionEditorsDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO'); /** @var EditAssignmentDAO $editAssignmentDao */
        $sectionEditors = $sectionEditorsDao->getEditorsBySectionId($journal->getId(), $sectionId);

        foreach ($sectionEditors as $sectionEditorEntry) {
            $editAssignment = $editAssignmentDao->newDataObject();
            $editAssignment->setArticleId($article->getId());
            $editAssignment->setEditorId($sectionEditorEntry['user']->getId());
            $editAssignment->setCanReview($sectionEditorEntry['canReview']);
            $editAssignment->setCanEdit($sectionEditorEntry['canEdit']);
            $editAssignmentDao->insertEditAssignment($editAssignment);
            unset($editAssignment);
        }

        return $sectionEditors;
    }

}
?>