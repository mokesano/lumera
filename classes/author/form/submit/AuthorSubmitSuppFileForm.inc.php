<?php
declare(strict_types=1);

/**
 * @file classes/author/form/submit/AuthorSubmitSuppFileForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitSuppFileForm
 * @ingroup author_form_submit
 *
 * @brief Supplementary file author submission form.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.Form');

class AuthorSubmitSuppFileForm extends Form {
    /** @var int the ID of the article */
    public int $articleId;

    /** @var int|null the ID of the supplementary file */
    public ?int $suppFileId = null;

    /** @var Article current article */
    public Article $article;

    /** @var SuppFile|null current file */
    public ?SuppFile $suppFile = null;

    /**
     * Constructor.
     * @param Article $article
     * @param Journal $journal
     * @param int|null $suppFileId
     */
    public function __construct($article, $journal, $suppFileId = null) {
        $supportedSubmissionLocales = $journal->getSetting('supportedSubmissionLocales');
        if (empty($supportedSubmissionLocales)) $supportedSubmissionLocales = [$journal->getPrimaryLocale()];

        parent::__construct(
            'author/submit/suppFile.tpl',
            true,
            $article->getLocale(),
            array_flip(array_intersect(
                array_flip(AppLocale::getAllLocales()),
                $supportedSubmissionLocales
            ))
        );
        
        $this->article = $article;
        $this->articleId = (int) $article->getId();

        if (isset($suppFileId) && !empty($suppFileId)) {
            $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
            $this->suppFile = $suppFileDao->getSuppFile($suppFileId, $article->getId());
            if (isset($this->suppFile)) {
                $this->suppFileId = $suppFileId;
            }
        }

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.suppFile.form.titleRequired', $this->getRequiredLocale()));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorSubmitSuppFileForm($article, $journal, $suppFileId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($article, $journal, $suppFileId);
    }

    /**
     * Get the names of fields for which data should be localized
     * @return array
     */
    public function getLocaleFieldNames() {
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        return $suppFileDao->getLocaleFieldNames();
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
        $templateMgr->assign('suppFileId', $this->suppFileId);
        $templateMgr->assign('submitStep', 4);

        $typeOptionsOutput = [
            'author.submit.suppFile.researchInstrument',
            'author.submit.suppFile.researchMaterials',
            'author.submit.suppFile.researchResults',
            'author.submit.suppFile.transcripts',
            'author.submit.suppFile.dataAnalysis',
            'author.submit.suppFile.dataSet',
            'author.submit.suppFile.sourceText'
        ];
        $typeOptionsValues = $typeOptionsOutput;
        $typeOptionsOutput[] = 'common.other';
        $typeOptionsValues[] = '';

        $templateMgr->assign('typeOptionsOutput', $typeOptionsOutput);
        $templateMgr->assign('typeOptionsValues', $typeOptionsValues);

        if (isset($this->article)) {
            $templateMgr->assign('submissionProgress', $this->article->getSubmissionProgress());
        }

        if (isset($this->suppFile)) {
            // [WIZDAM] Use assign instead of assign_by_ref
            $templateMgr->assign('suppFile', $this->suppFile);
        }
        $templateMgr->assign('helpTopicId','submission.supplementaryFiles');
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current supplementary file (if applicable).
     */
    public function initData() {
        if (isset($this->suppFile)) {
            $suppFile = $this->suppFile;
            $this->_data = [
                'title' => $suppFile->getTitle(null), // Localized
                'creator' => $suppFile->getCreator(null), // Localized
                'subject' => $suppFile->getSubject(null), // Localized
                'type' => $suppFile->getType(),
                'typeOther' => $suppFile->getTypeOther(null), // Localized
                'description' => $suppFile->getDescription(null), // Localized
                'publisher' => $suppFile->getPublisher(null), // Localized
                'sponsor' => $suppFile->getSponsor(null), // Localized
                'dateCreated' => $suppFile->getDateCreated(),
                'source' => $suppFile->getSource(null), // Localized
                'language' => $suppFile->getLanguage(),
                'showReviewers' => $suppFile->getShowReviewers()
            ];

        } else {
            $this->_data = [
                'type' => ''
            ];
        }
        return parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'title',
            'creator',
            'subject',
            'type',
            'typeOther',
            'description',
            'publisher',
            'sponsor',
            'dateCreated',
            'source',
            'language',
            'showReviewers'
        ]);
    }

    /**
     * Save changes to the supplementary file.
     * @param object|null $object
     * @return int the supplementary file ID
     */
    public function execute($object = null) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($this->articleId);
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');

        $fileName = 'uploadSuppFile';

        // edit an existing supp file, otherwise create new supp file entry
        if (isset($this->suppFile)) {
            parent::execute();

            // Remove old file and upload new, if file is selected.
            if ($articleFileManager->uploadedFileExists($fileName)) {
                $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
                $suppFileId = $articleFileManager->uploadSuppFile($fileName, $this->suppFile->getFileId(), true);
                $this->suppFile->setFileId($suppFileId);
            }

            // Update existing supplementary file
            $this->setSuppFileData($this->suppFile);
            $suppFileDao->updateSuppFile($this->suppFile);

        } else {
            // Upload file, if file selected.
            if ($articleFileManager->uploadedFileExists($fileName)) {
                $fileId = $articleFileManager->uploadSuppFile($fileName);
            } else {
                $fileId = 0;
            }

            // Insert new supplementary file
            $this->suppFile = new SuppFile();
            $this->suppFile->setArticleId($this->articleId);
            $this->suppFile->setFileId($fileId);

            parent::execute();

            $this->setSuppFileData($this->suppFile);
            $suppFileDao->insertSuppFile($this->suppFile);
            $this->suppFileId = $this->suppFile->getId();
        }

        return $this->suppFileId;
    }

    /**
     * Assign form data to a SuppFile.
     * @param SuppFile $suppFile
     */
    public function setSuppFileData($suppFile) {
        $suppFile->setTitle($this->getData('title'), null); // Null
        $suppFile->setCreator($this->getData('creator'), null); // Null
        $suppFile->setSubject($this->getData('subject'), null); // Null
        $suppFile->setType($this->getData('type'));
        $suppFile->setTypeOther($this->getData('typeOther'), null); // Null
        $suppFile->setDescription($this->getData('description'), null); // Null
        $suppFile->setPublisher($this->getData('publisher'), null); // Null
        $suppFile->setSponsor($this->getData('sponsor'), null); // Null
        $suppFile->setDateCreated($this->getData('dateCreated') == '' ? Core::getCurrentDate() : $this->getData('dateCreated'));
        $suppFile->setSource($this->getData('source'), null); // Null
        $suppFile->setLanguage($this->getData('language'));
        $suppFile->setShowReviewers($this->getData('showReviewers'));
    }
}

?>