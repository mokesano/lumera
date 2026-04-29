<?php
declare(strict_types=1);

/**
 * @file pages/author/SubmitHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmitHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for author article submission.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.author.AuthorHandler');

class SubmitHandler extends AuthorHandler {
    
    /** @var Article|null article associated with the request */
    public $article;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmitHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::SubmitHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display journal author article submission.
     * Displays author index page if a valid step is not specified.
     * @param array $args optional, if set the first parameter is the step to display
     * @param PKPRequest $request
     */
    public function submit($args, $request) {
        $step = (int) array_shift($args);
        $articleId = (int) $request->getUserVar('articleId');
        $journal = $request->getJournal();

        // Pass $step explicitly to validate, since $args is shifted
        $this->validate(null, $request, $articleId, null, $step);
        
        // Re-check step logic inside this method scope if validate() adjusted redirection logic
        // But validate() handles redirection if step is invalid.
        // We need to ensure we have the correct step for form loading.
        // If step wasn't in URL, validate() might redirect to step 1.
        // If we are here, we proceed.
        
        // Re-calculate step from args if validate passed (though args was shifted)
        // Actually, $step is local variable.
        if ($step < 1 || $step > 5) {
             // If step is invalid/missing, usually validate() redirects to step 1.
             // If we reach here with invalid step, force step 1? 
             // Or rely on validate().
             // validate() logic below handles specific step constraints.
             $step = 1; 
        }

        $article = $this->article;
        $this->setupTemplate($request, true);

        $formClass = "AuthorSubmitStep{$step}Form";
        import("classes.author.form.submit.$formClass");

        $submitForm = new $formClass($article, $journal, $request);
        if ($submitForm->isLocaleResubmit()) {
            $submitForm->readInputData();
        } else {
            $submitForm->initData();
        }
        $submitForm->display();
    }

    /**
     * Save a submission step.
     * @param array $args first parameter is the step being saved
     * @param PKPRequest $request
     */
    public function saveSubmit($args, $request) {
        $step = (int) array_shift($args);
        $articleId = (int) $request->getUserVar('articleId');
        $journal = $request->getJournal();

        $this->validate(null, $request, $articleId, null); // Pass explicit args to avoid confusion
        $this->setupTemplate($request, true);
        $article = $this->article;

        $formClass = "AuthorSubmitStep{$step}Form";
        import("classes.author.form.submit.$formClass");

        $submitForm = new $formClass($article, $journal, $request);
        $submitForm->readInputData();

        // [WIZDAM] Check hook signature compatibility (references)
        if (!HookRegistry::dispatch('SubmitHandler::saveSubmit', [$step, &$article, &$submitForm])) {

            // Check for any special cases before trying to save
            switch ($step) {
                case 2:
                    if ($request->getUserVar('uploadSubmissionFile')) {
                        $submitForm->uploadSubmissionFile('submissionFile');
                        $editData = true;
                    }
                    break;

                case 3:
                    if ($request->getUserVar('addAuthor')) {
                        // Add a author
                        $editData = true;
                        $authors = $submitForm->getData('authors');
                        array_push($authors, []);
                        $submitForm->setData('authors', $authors);

                    } elseif (($delAuthor = $request->getUserVar('delAuthor')) && count($delAuthor) == 1) {
                        // Delete an author
                        $editData = true;
                        list($delAuthor) = array_keys($delAuthor);
                        $delAuthor = (int) $delAuthor;
                        $authors = $submitForm->getData('authors');
                        if (isset($authors[$delAuthor]['authorId']) && !empty($authors[$delAuthor]['authorId'])) {
                            $deletedAuthors = explode(':', $submitForm->getData('deletedAuthors'));
                            array_push($deletedAuthors, $authors[$delAuthor]['authorId']);
                            $submitForm->setData('deletedAuthors', join(':', $deletedAuthors));
                        }
                        array_splice($authors, $delAuthor, 1);
                        $submitForm->setData('authors', $authors);

                        if ($submitForm->getData('primaryContact') == $delAuthor) {
                            $submitForm->setData('primaryContact', 0);
                        }

                    } elseif ($request->getUserVar('moveAuthor')) {
                        // Move an author up/down
                        $editData = true;
                        $moveAuthorDir = $request->getUserVar('moveAuthorDir');
                        $moveAuthorDir = $moveAuthorDir == 'u' ? 'u' : 'd';
                        $moveAuthorIndex = (int) $request->getUserVar('moveAuthorIndex');
                        $authors = $submitForm->getData('authors');

                        if (!(($moveAuthorDir == 'u' && $moveAuthorIndex <= 0) || ($moveAuthorDir == 'd' && $moveAuthorIndex >= count($authors) - 1))) {
                            $tmpAuthor = $authors[$moveAuthorIndex];
                            $primaryContact = $submitForm->getData('primaryContact');
                            if ($moveAuthorDir == 'u') {
                                $authors[$moveAuthorIndex] = $authors[$moveAuthorIndex - 1];
                                $authors[$moveAuthorIndex - 1] = $tmpAuthor;
                                if ($primaryContact == $moveAuthorIndex) {
                                    $submitForm->setData('primaryContact', $moveAuthorIndex - 1);
                                } elseif ($primaryContact == ($moveAuthorIndex - 1)) {
                                    $submitForm->setData('primaryContact', $moveAuthorIndex);
                                }
                            } else {
                                $authors[$moveAuthorIndex] = $authors[$moveAuthorIndex + 1];
                                $authors[$moveAuthorIndex + 1] = $tmpAuthor;
                                if ($primaryContact == $moveAuthorIndex) {
                                    $submitForm->setData('primaryContact', $moveAuthorIndex + 1);
                                } elseif ($primaryContact == ($moveAuthorIndex + 1)) {
                                    $submitForm->setData('primaryContact', $moveAuthorIndex);
                                }
                            }
                        }
                        $submitForm->setData('authors', $authors);
                    }
                    break;

                case 4:
                    if ($request->getUserVar('submitUploadSuppFile')) {
                        $this->submitUploadSuppFile([], $request);
                        return;
                    }
                    break;
            }

            if (!isset($editData) && $submitForm->validate()) {
                $articleId = $submitForm->execute();
                
                // [WIZDAM] Check hook signature compatibility (references)
                HookRegistry::dispatch('Author::SubmitHandler::saveSubmit', [&$step, &$article, &$submitForm]);

                if ($step == 5) {
                    // Send a notification to associated users
                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $articleDao = DAORegistry::getDAO('ArticleDAO');
                    $article = $articleDao->getArticle($articleId);
                    $roleDao = DAORegistry::getDAO('RoleDAO');
                    $editors = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journal->getId());
                    while ($editor = $editors->next()) {
                        $notificationManager->createNotification(
                            $request, $editor->getId(), NOTIFICATION_TYPE_ARTICLE_SUBMITTED,
                            $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                        );
                        unset($editor);
                    }

                    $journal = $request->getJournal();
                    $templateMgr = TemplateManager::getManager();
                    // [WIZDAM] Removed assign_by_ref
                    $templateMgr->assign('journal', $journal);
                    // If this is an editor and there is a
                    // submission file, article can be expedited.
                    if (Validation::isEditor($journal->getId()) && $article->getSubmissionFileId()) {
                        $templateMgr->assign('canExpedite', true);
                    }
                    $templateMgr->assign('articleId', $articleId);
                    $templateMgr->assign('helpTopicId','submission.index');
                    $templateMgr->display('author/submit/complete.tpl');

                } else {
                    $request->redirect(null, null, 'submit', $step+1, ['articleId' => $articleId]);
                }
            } else {
                $submitForm->display();
            }
        }
    }

    /**
     * Create new supplementary file with a uploaded file.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submitUploadSuppFile($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $journal = $request->getJournal();

        $this->validate(null, $request, $articleId); // Pass null first to skip context check or handle in validate
        $article = $this->article;
        $this->setupTemplate($request, true);

        import('classes.author.form.submit.AuthorSubmitSuppFileForm');
        $submitForm = new AuthorSubmitSuppFileForm($article, $journal);
        $submitForm->setData('title', [$article->getLocale() => __('common.untitled')]);
        $suppFileId = $submitForm->execute();

        $request->redirect(null, null, 'submitSuppFile', $suppFileId, ['articleId' => $articleId]);
    }

    /**
     * Display supplementary file submission form.
     * @param array $args optional, if set the first parameter is the supplementary file to edit
     * @param PKPRequest $request
     */
    public function submitSuppFile($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $suppFileId = isset($args[0]) ? (int) $args[0] : 0;
        $journal = $request->getJournal();

        $this->validate(null, $request, $articleId);
        $article = $this->article;
        $this->setupTemplate($request, true);

        import('classes.author.form.submit.AuthorSubmitSuppFileForm');
        $submitForm = new AuthorSubmitSuppFileForm($article, $journal, $suppFileId);

        if ($submitForm->isLocaleResubmit()) {
            $submitForm->readInputData();
        } else {
            $submitForm->initData();
        }
        $submitForm->display();
    }

    /**
     * Save a supplementary file.
     * @param array $args optional, if set the first parameter is the supplementary file to update
     * @param PKPRequest $request
     */
    public function saveSubmitSuppFile($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $suppFileId = isset($args[0]) ? (int) $args[0] : 0;
        $journal = $request->getJournal();

        $this->validate(null, $request, $articleId);
        $article = $this->article;
        $this->setupTemplate($request, true);

        import('classes.author.form.submit.AuthorSubmitSuppFileForm');
        $submitForm = new AuthorSubmitSuppFileForm($article, $journal, $suppFileId);
        $submitForm->readInputData();

        if ($submitForm->validate()) {
            $submitForm->execute();
            $request->redirect(null, null, 'submit', '4', ['articleId' => $articleId]);
        } else {
            $submitForm->display();
        }
    }

    /**
     * Delete a supplementary file.
     * @param array $args, the first parameter is the supplementary file to delete
     * @param PKPRequest $request
     */
    public function deleteSubmitSuppFile($args, $request) {
        import('classes.file.ArticleFileManager');

        $articleId = (int) $request->getUserVar('articleId');
        $suppFileId = (int) array_shift($args);

        $this->validate(null, $request, $articleId);
        $article = $this->article;
        $this->setupTemplate($request, true);

        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFile = $suppFileDao->getSuppFile($suppFileId, $articleId);
        $suppFileDao->deleteSuppFileById($suppFileId, $articleId);

        if ($suppFile->getFileId()) {
            $articleFileManager = new ArticleFileManager($articleId);
            $articleFileManager->deleteFile($suppFile->getFileId());
        }

        $request->redirect(null, null, 'submit', '4', ['articleId' => $articleId]);
    }

    /**
     * Expedite a submission -- rush it through the editorial process, for
     * users who are both authors and editors.
     * @param array $args
     * @param PKPRequest $request
     */
    public function expediteSubmission($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $journal = $request->getJournal();
        $article = $this->article;

        // The author must also be an editor to perform this task.
        if (Validation::isEditor($journal->getId()) && $article->getSubmissionFileId()) {
            import('classes.submission.editor.EditorAction');
            EditorAction::expediteSubmission($article, $request);
            $request->redirect(null, 'editor', 'submissionEditing', [$article->getId()]);
        }

        $request->redirect(null, null, 'track');
    }

    /**
     * Validation check for submission.
     * Checks that article ID is valid, if specified.
     * @param mixed $requiredContexts (Legacy)
     * @param PKPRequest $request
     * @param int|null $articleId
     * @param string|null $reason (Legacy)
     * @param int|bool $step (Additional param for SubmitHandler logic)
     * @return bool
     */
    public function validate($requiredContexts = null, $request = null, $articleId = null, $reason = null, $step = false) {
        // 1. Normalisasi Request (Wizdam Core Security)
        // Jika argumen pertama adalah Request, geser semua
        if ($requiredContexts instanceof PKPRequest) {
            $realRequest = $requiredContexts;
            $realArticleId = (int) $request; 
            $realStep = (int) $articleId;
        } else {
            $realRequest = ($request instanceof PKPRequest) ? $request : Application::get()->getRequest();
            $realArticleId = (int) $articleId;
            $realStep = (int) $step;
        }

        // 2. Jalankan validasi dasar parent (User Login & Journal Context)
        parent::validate(null, $realRequest);

        $journal = $realRequest->getJournal();
        $user = $realRequest->getUser();

        if (!$journal || !$user) {
            $realRequest->redirect(null, 'login');
            return false;
        }

        // 3. Ambil data artikel jika ada ID
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = null;

        if ($realArticleId > 0) {
            $article = $articleDao->getArticle($realArticleId);
            
            // Keamanan: Pastikan artikel milik user dan jurnal yang benar
            if (!$article || $article->getUserId() !== $user->getId() || $article->getJournalId() !== $journal->getId()) {
                $realRequest->redirect(null, 'author');
                return false;
            }

            // Validasi Step vs Progress (Penyebab Loop)
            $progress = (int) $article->getSubmissionProgress();
            if ($progress == 0) $progress = 1; // Jika baru mulai

            // Jika user mencoba akses step lebih tinggi dari yang sudah dicapai
            if ($realStep > 0 && $realStep > $progress) {
                $realRequest->redirect(null, null, 'submit', [$progress], ['articleId' => $realArticleId]);
                return false;
            }
        } elseif ($realStep > 1) {
            // Jika tidak ada ID tapi mencoba akses step > 1
            $realRequest->redirect(null, null, 'submit', [1]);
            return false;
        }

        $this->article = $article;
        return true;
    }
}
?>