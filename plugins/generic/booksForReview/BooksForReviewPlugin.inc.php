<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/BooksForReviewPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooksForReviewPlugin
 * @ingroup plugins_generic_booksForReview
 *
 * @brief Books for review plugin class
 * [WIZDAM EDITION] Modernized. PHP 8 Safe & Resource Optimized.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

define('BFR_MODE_FULL',         0x01);
define('BFR_MODE_METADATA',     0x02);

class BooksForReviewPlugin extends GenericPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BooksForReviewPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BooksForReviewPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path of plugin
     * @return bool True if plugin initialized successfully; if false, the plugin will not be registered.
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        if ($success && $this->getEnabled()) {
            $this->import('classes.BookForReviewDAO');
            $this->import('classes.BookForReviewAuthorDAO');

            // [MODERNISASI] Hapus referensi &
            $bfrAuthorDao = new BookForReviewAuthorDAO($this->getName());
            DAORegistry::registerDAO('BookForReviewAuthorDAO', $bfrAuthorDao);

            $bfrDao = new BookForReviewDAO($this->getName());
            DAORegistry::registerDAO('BookForReviewDAO', $bfrDao);

            $journal = Request::getJournal();
            $mode = null;
            $coverPageIssue = false;
            $coverPageAbstract = false;

            if ($journal) {
                $mode = $this->getSetting($journal->getId(), 'mode');
                $coverPageIssue = $this->getSetting($journal->getId(), 'coverPageIssue');
                $coverPageAbstract = $this->getSetting($journal->getId(), 'coverPageAbstract');
            }

            // [MODERNISASI] Register Hook tanpa & pada callback array
            HookRegistry::register('LoadHandler', array($this, 'setupEditorHandler'));
            HookRegistry::register('Templates::Editor::Index::AdditionalItems', array($this, 'displayEditorHomeLink'));
            HookRegistry::register('Templates::Submission::Metadata::Metadata::AdditionalEditItems', array($this, 'displayEditorMetadataLink'));
            HookRegistry::register('Templates::Article::Header::Metadata', array($this, 'displayBookMetadata'));
            HookRegistry::register('TinyMCEPlugin::getEnableFields', array($this, 'enableTinyMCE'));
            HookRegistry::register('UserAction::mergeUsers', array($this, 'mergeBooksForReviewAuthors'));

            if ($coverPageIssue) {
                HookRegistry::register('Templates::Issue::Issue::ArticleCoverImage', array($this, 'displayArticleCoverPageIssue'));
            }

            if ($coverPageAbstract) {
                HookRegistry::register('Templates::Article::Article::ArticleCoverImage', array($this, 'displayArticleCoverPageAbstract'));
            }

            if ($mode == BFR_MODE_FULL) {
                HookRegistry::register('LoadHandler', array($this, 'setupPublicHandler'));
                HookRegistry::register('Templates::Common::Header::Navbar::CurrentJournal', array($this, 'displayHeaderLink'));
                HookRegistry::register('LoadHandler', array($this, 'setupAuthorHandler'));
                HookRegistry::register('Author::SubmitHandler::saveSubmit', array($this, 'saveSubmitHandler'));
                HookRegistry::register('Templates::Author::Submit::Step5::AdditionalItems', array($this, 'displayAuthorBooksForReview'));
                HookRegistry::register('Templates::Author::Index::AdditionalItems', array($this, 'displayAuthorHomeLink'));
            }
        }
        return $success;
    }

    /**
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.booksForReview.displayName');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.booksForReview.description');
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     */
    public function getInstallSchemaFile(): string {
        return $this->getPluginPath() . '/xml/schema.xml';
    }

    /**
     * Get the filename of the email keys for this plugin.
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return $this->getPluginPath() . '/xml/emailTemplates.xml';
    }

    /**
     * Get the filename of the email locale data for this plugin.
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return $this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml';
    }

    /**
     * Get the template path for this plugin.
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Get the handler path for this plugin.
     */
    public function getHandlerPath() {
        return $this->getPluginPath() . '/pages/';
    }

    /**
     * Get the stylesheet for this plugin.
     */
    public function getStyleSheet() {
        return $this->getPluginPath() . '/styles/booksForReview.css';
    }

    /**
     * Set the page's breadcrumbs
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = array(
            array(
                Request::url(null, 'user'),
                'navigation.user'
            ),
            array(
                Request::url(null, 'manager'),
                'user.role.manager'
            )
        );
        if ($isSubclass) $pageCrumbs[] = array(
            Request::url(null, 'manager', 'plugin', array('generic', $this->getName(), 'booksForReview')),
            $this->getDisplayName(),
            true
        );

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Allow author to specify book for review during article submission.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function saveSubmitHandler($hookName, $params) {
        $article = $params[1];
        $journal = Request::getJournal();
        $user = Request::getUser();

        if ($journal && $user) {
            $journalId = $journal->getId();
            $userId = $user->getId();
            $bookId = Request::getUserVar('bookForReviewId') == null ? null : (int) Request::getUserVar('bookForReviewId');

            if ($bookId) {
                // [MODERNISASI] Hapus referensi &
                $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

                if ($bfrDao->getBookForReviewJournalId($bookId) == $journalId) {
                    $book = $bfrDao->getBookForReview($bookId);
                    $authorId = $book->getUserId();

                    if ($authorId == $userId) {
                        $status = $book->getStatus();
                        $this->import('classes.BookForReview');

                        if ($status == BFR_STATUS_ASSIGNED || $status == BFR_STATUS_MAILED) {
                            $book->setStatus(BFR_STATUS_SUBMITTED);
                            $book->setDateSubmitted(date('Y-m-d H:i:s', time()));
                            $book->setArticleId($article->getId());
                            $bfrDao->updateObject($book);
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Enable editor book for review management.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function setupEditorHandler($hookName, $params) {
        $page = $params[0];

        if ($page == 'editor') {
            $op = $params[1];

            if ($op) {
                $editorPages = array(
                    'createBookForReview',
                    'editBookForReview',
                    'updateBookForReview',
                    'deleteBookForReview',
                    'booksForReview',
                    'booksForReviewSettings',
                    'selectBookForReviewAuthor',
                    'selectBookForReviewSubmission',
                    'assignBookForReviewAuthor',
                    'assignBookForReviewSubmission',
                    'denyBookForReviewAuthor',
                    'notifyBookForReviewMailed',
                    'removeBookForReviewAuthor',
                    'removeBookForReviewCoverPage'
                );

                if (in_array($op, $editorPages)) {
                    define('HANDLER_CLASS', 'BooksForReviewEditorHandler');
                    define('BOOKS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OJS_EDITOR);
                    
                    // [WIZDAM NOTE] Hook LoadHandler arguments: $page, $op, &$sourceFile
                    // Harus menggunakan & di sini karena sourceFile perlu diubah agar router memuat handler dari plugin ini.
                    $handlerFile =& $params[2]; 
                    $handlerFile = $this->getHandlerPath() . 'BooksForReviewEditorHandler.inc.php';
                }
            }
        }
        return false;
    }

    /**
     * Enable author book for review management.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function setupAuthorHandler($hookName, $params) {
        $page = $params[0];
        if ($page == 'author') {
            $op = $params[1];

            if ($op) {
                $authorPages = array(
                    'booksForReview',
                    'requestBookForReview'
                );

                if (in_array($op, $authorPages)) {
                    define('HANDLER_CLASS', 'BooksForReviewAuthorHandler');
                    define('BOOKS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OJS_AUTHOR);
                    
                    // [WIZDAM NOTE] Reference assignment required for HookRegistry modification
                    $handlerFile =& $params[2];
                    $handlerFile = $this->getHandlerPath() . 'BooksForReviewAuthorHandler.inc.php';
                }
            }
        }
        return false;
    }

    /**
     * Enable public book for review pages.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function setupPublicHandler($hookName, $params) {
        $page = $params[0];
        if ($page == 'booksForReview') {
            $op = $params[1];

            if ($op) {
                $publicPages = array(
                    'index',
                    'viewBookForReview'
                );

                if (in_array($op, $publicPages)) {
                    define('HANDLER_CLASS', 'BooksForReviewHandler');
                    define('BOOKS_FOR_REVIEW_PLUGIN_NAME', $this->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                    
                    // [WIZDAM NOTE] Reference assignment required
                    $handlerFile =& $params[2];
                    $handlerFile = $this->getHandlerPath() . 'BooksForReviewHandler.inc.php';
                }
            }
        }
        return false;
    }

    /**
     * Enable TinyMCE support for book for review text fields.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function enableTinyMCE($hookName, $params) {
        $fields =& $params[1]; // [WIZDAM NOTE] Reference required to modify $fields array
        
        $page = Request::getRequestedPage();
        $op = Request::getRequestedOp();
        
        if ($page == 'editor' && ($op == 'createBookForReview' || $op == 'editBookForReview' || $op == 'updateBookForReview')) {
            $fields[] = 'description';
            $fields[] = 'notes';
        } elseif ($page == 'editor' && $op == 'booksForReviewSettings') {
            $fields[] = 'additionalInformation';
        }
        return false;
    }

    /**
     * Transfer book for review user assignments when merging users.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function mergeBooksForReviewAuthors($hookName, $params) {
        $oldUserId = $params[0];
        $newUserId = $params[1];

        $journal = Request::getJournal();

        // [MODERNISASI] Hapus &
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        $oldUserBooksForReview = $bfrDao->getBooksForReviewByAuthor($journal->getId(), $oldUserId);

        while ($bookForReview = $oldUserBooksForReview->next()) {
            $bookForReview->setUserId($newUserId);
            $bfrDao->updateObject($bookForReview);
            unset($bookForReview);
        }

        return false;
    }

    /**
     * Display an author's books for review during submission step 5.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayAuthorBooksForReview($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required to modify output

            $journal = Request::getJournal();
            $user = Request::getUser();

            if ($journal && $user) {
                // [MODERNISASI] Hapus &
                $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
                // Handler class is static, ensuring non-reference return if possible
                $rangeInfo = Handler::getRangeInfo('booksForReview');
                $booksForReview = $bfrDao->getBooksForReviewAssignedByAuthor($journal->getId(), $user->getId(), $rangeInfo);

                if (!$booksForReview->wasEmpty()) {
                    $smarty->assign('booksForReview', $booksForReview);
                    $output .= $smarty->fetch($this->getTemplatePath() . 'author' . '/' . 'submissionBooksForReview.tpl');
                }
            }
        }
        return false;
    }

    /**
     * Display book for review cover page in issue toc.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayArticleCoverPageIssue($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required to modify output

            $journal = Request::getJournal();

            if ($journal) {
                $journalId = $journal->getId();
            } else {
                return false;
            }

            $article = $smarty->get_template_vars('article');
            if ($article) {
                $articleId = $article->getId();
            } else {
                return false;
            }

            $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
            $book = $bfrDao->getSubmittedBookForReviewByArticle($journalId, $articleId);

            if ($book) {
                $smarty->assign('book', $book);
                $output .= $smarty->fetch($this->getTemplatePath() . 'coverPageIssue.tpl');
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Display book for review cover page in article abstract.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayArticleCoverPageAbstract($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required

            $journal = Request::getJournal();

            if ($journal) {
                $journalId = $journal->getId();
            } else {
                return false;
            }

            $article = $smarty->get_template_vars('article');
            if ($article) {
                $articleId = $article->getId();
            } else {
                return false;
            }

            $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
            $book = $bfrDao->getSubmittedBookForReviewByArticle($journalId, $articleId);

            if ($book) {
                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $baseCoverPagePath = Request::getBaseUrl() . '/';
                $baseCoverPagePath .= $publicFileManager->getJournalFilesPath($journalId) . '/';
                $smarty->assign('baseCoverPagePath', $baseCoverPagePath);
                $smarty->assign('locale', AppLocale::getLocale());
                $smarty->assign('book', $book);
                $output .= $smarty->fetch($this->getTemplatePath() . 'coverPageAbstract.tpl');
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Append book for review metadata to article metadata.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayBookMetadata($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required

            $journal = Request::getJournal();

            if ($journal) {
                $journalId = $journal->getId();
            } else {
                return false;
            }

            $article = $smarty->get_template_vars('article');
            if ($article) {
                $articleId = $article->getId();
            } else {
                return false;
            }

            $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
            $book = $bfrDao->getSubmittedBookForReviewByArticle($journalId, $articleId);

            if ($book) {
                $smarty->assign('book', $book);
                $citation = trim(trim($smarty->fetch($this->getTemplatePath() . 'citation.tpl')));
                $smarty->assign('citation', $citation);
                $output .= $smarty->fetch($this->getTemplatePath() . 'metadata.tpl');
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Display books for review link in header menu bar.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayHeaderLink($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required
            
            $templateMgr = TemplateManager::getManager();
            $output .= '<li><a href="' . $templateMgr->smartyUrl(array('page'=>'booksForReview'), $smarty) . '" target="_parent">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.headerLink'), $smarty) . '</a></li>';
        }
        return false;
    }

    /**
     * Display books for review management link in editor home.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayEditorHomeLink($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required
            
            $templateMgr = TemplateManager::getManager();
            $output .= '<h3>' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.editor.booksForReview'), $smarty) . '</h3><ul><li><a href="' . $templateMgr->smartyUrl(array('op'=>'booksForReview'), $smarty) . '">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.editor.booksForReview'), $smarty) . '</a></li></ul>';
        }
        return false;
    }

    /**
     * Display book for review metadata link in submission summary page.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayEditorMetadataLink($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required
            
            $submission = $smarty->get_template_vars("submission");

            if ($submission) {
                $articleId = $submission->getId();
                $journal = Request::getJournal();
                $journalId = $journal->getId();
                $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
                $bookId = $bfrDao->getSubmittedBookForReviewIdByArticle($journalId, $articleId);
                if ($bookId) {
                    $templateMgr = TemplateManager::getManager();
                    $output = '<p><a href="' . $templateMgr->smartyUrl(array('page'=>'editor', 'op'=>'editBookForReview', 'path'=>$bookId), $smarty) . '" class="action">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.editor.editBookForReviewMetadata'), $smarty) . '</a></p>';
                }
            }
        }
        return false;
    }

    /**
     * Display books for review links in author home.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayAuthorHomeLink($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output =& $params[2]; // [WIZDAM NOTE] Reference required
            
            $templateMgr = TemplateManager::getManager();
            $output .= '<br /><div class="separator"></div><h3>' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.author.booksForReview'), $smarty) . '</h3><ul><li><a href="' . $templateMgr->smartyUrl(array('page'=>'author', 'op'=>'booksForReview'), $smarty) . '">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.author.myBooksForReview'), $smarty) . '</a></li><li><a href="' . $templateMgr->smartyUrl(array('page'=>'booksForReview'), $smarty) . '">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.booksForReview.author.availableBooksForReview'), $smarty) . '</a></li></ul><br />';
        }
        return false;
    }
}
?>