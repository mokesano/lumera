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
 * [WIZDAM EDITION] Refactored for PHP 8.x
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
        
        parent::__construct(
            sprintf('author/submit/step%d.tpl', $step),
            true,
            $article ? $article->getLocale() : AppLocale::getLocale(),
            array_flip(array_intersect(
                array_flip(AppLocale::getAllLocales()),
                $supportedSubmissionLocales
            ))
        );
        $this->addCheck(new FormValidatorPost($this));
        $this->step = (int) $step;
        $this->article = $article;
        $this->articleId = $article ? $article->getId() : null;
        $this->request = $request;
    }

    /**
     * [SHIM] Backward Compatibility
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

        if (isset($this->article)) {
            $templateMgr->assign('submissionProgress', $this->article->getSubmissionProgress());
        }

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
        
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        // [WIZDAM] Use assign instead of assign_by_ref
        $templateMgr->assign('journalSettings', $settingsDao->getJournalSettings($journal->getId()));

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

        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO'); /* @var $editAssignmentDao EditAssignmentDAO */
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