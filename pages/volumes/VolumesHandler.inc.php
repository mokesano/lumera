<?php
declare(strict_types=1);

/**
 * @file pages/volumes/VolumesHandler.inc.php
 *
 * Copyright (c) 2025 Wizdam Team
 * Copyright (c) 2025 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class VolumesHandler
 * @ingroup pages_volumes
 *
 * @brief Handle requests for custom volume functions.
 *
 * [WIZDAM EDITION] Custom Handler - Refactored for PHP 8.1+ Strict Compliance
 * [WIZDAM v3] view() mendeteksi issue null dan merender konten artikel langsung
 *             sebagai pengganti sejati detail issue (bukan sekadar daftar issue).
 *
 * LOGIKA UTAMA view():
 *
 *   Kondisi A — Semua issue di volume memiliki number kosong/NULL:
 *       Volume IS the issue. Render konten artikel langsung via viewPage.tpl.
 *       Navigasi menggunakan Prev/Next VOLUME (bukan issue).
 *       $prevIssue / $nextIssue = null agar header.tpl tidak render nav issue
 *       yang akan menghasilkan link loop ke /volumes/{vol} lagi.
 *
 *   Kondisi B — Ada issue dengan number valid ("0", "1", "Supplement", dst):
 *       Tampilkan daftar issue dalam volume via viewVolume.tpl.
 *       User dapat klik issue individual → IssueHandler::view().
 *
 *   Kondisi C — Volume tidak punya issue sama sekali:
 *       Redirect ke halaman arsip (/volumes/).
 *
 * DEFINISI SCHLARWIZDAM (penting untuk klasifikasi):
 *   number = ""   (NULL di DB → PHP cast ke "")  = TIDAK ADA nomor → Kondisi A
 *   number = "0"  (integer 0 yang valid)          = VALID          → Kondisi B
 *   number = "1", "2", "Supplement", dst          = VALID          → Kondisi B
 */

import('classes.handler.Handler');
import('classes.issue.IssueAction');

class VolumesHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function VolumesHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    // =================================================================
    // PUBLIC ACTIONS
    // =================================================================

    /**
     * Tampilkan halaman arsip (daftar semua volume/issue).
     * URL: /{journal}/volumes/
     *
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function displayArchive($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->setupTemplate($request);
        $journal = $request->getJournal();

        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issues   = $issueDao->getPublishedIssues($journal->getId(), null);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', 'archive.archives');
        $templateMgr->assign('pageHierarchy', []);
        $templateMgr->assign('issues', $issues);
        $templateMgr->display('issue/archive.tpl');
    }

    /**
     * Tampilkan detail volume.
     *
     * Perilaku ditentukan oleh kondisi number pada issue dalam volume:
     *   Kondisi A: Semua number kosong → render konten artikel langsung
     *   Kondisi B: Ada number valid    → tampilkan daftar issue
     *   Kondisi C: Tidak ada issue     → redirect ke arsip
     *
     * URL: /{journal}/volumes/{volumeId}
     *
     * @param array $args  [0] = volumeId
     * @param PKPRequest|null $request
     */
    public function view($args, $request = null) {
        $request  = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $volumeId = isset($args[0]) ? (int) $args[0] : 0;
        $journal  = $request->getJournal();

        $this->setupTemplate($request);

        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        $issueDao    = DAORegistry::getDAO('IssueDAO');
        $templateMgr = TemplateManager::getManager($request);

        // ---------------------------------------------------------------
        // Ambil semua issue yang terbit dalam volume ini
        // ---------------------------------------------------------------
        $issuesIterator = $issueDao->getPublishedIssuesByVolume($journal->getId(), $volumeId);
        $issuesArray    = $issuesIterator->toArray();

        // Kondisi C: Tidak ada issue → ke arsip
        if (empty($issuesArray)) {
            $request->redirect(null, 'volumes');
            return;
        }

        // ---------------------------------------------------------------
        // Klasifikasi: apakah SEMUA issue memiliki number kosong?
        //
        // Iterasi seluruh array untuk menemukan issue yang memiliki
        // number valid. Jika ditemukan → Kondisi B.
        // Jika tidak ada satupun yang valid → Kondisi A.
        // ---------------------------------------------------------------
        $allNumbersEmpty = true;
        foreach ($issuesArray as $iss) {
            // Cast ke string: NULL DB → "", "0" → "0" (valid), "1" → "1" (valid)
            if ((string) $iss->getNumber() !== '') {
                $allNumbersEmpty = false;
                break;
            }
        }

        // ---------------------------------------------------------------
        // Variabel umum (dipakai di Kondisi A maupun B)
        // ---------------------------------------------------------------
        $volumeTitleString = AppLocale::translate('issue.volume') . ' ' . $volumeId;

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/'
            . $publicFileManager->getJournalFilesPath($journal->getId()) . '/';

        // Navigasi Prev/Next Volume
        list($prevVolumeId, $nextVolumeId) = $issueDao->getSurroundingVolumeIds(
            $journal->getId(),
            $volumeId
        );

        $templateMgr->assign('coverPagePath',           $coverPagePath);
        $templateMgr->assign('locale',                  AppLocale::getLocale());
        $templateMgr->assign('journal',                 $journal);
        $templateMgr->assign('volumeId',                $volumeId);
        $templateMgr->assign('nextVolumeId',             $nextVolumeId);
        $templateMgr->assign('prevVolumeId',             $prevVolumeId);
        $templateMgr->assign('pageTitleTranslated',      $volumeTitleString);
        $templateMgr->assign('pageCrumbTitleTranslated', $volumeTitleString);
        $templateMgr->assign('pageHierarchy', [
            [$request->url(null, 'volumes'), 'archive.archives']
        ]);

        // =================================================================
        // KONDISI A: Semua issue tidak memiliki number (number kosong/NULL)
        //
        // Volume menggantikan detail issue secara penuh.
        // Konten artikel dari issue pertama ditampilkan langsung.
        //
        // MENGAPA $prevIssue/$nextIssue = null?
        //   Jika diisi, header.tpl akan generate URL /volumes/{vol} untuk
        //   issue dengan number kosong → user klik → kembali ke halaman sama
        //   → loop yang dialami user. Dengan null, tombol nav issue tidak
        //   ditampilkan; navigasi dilakukan via tombol Prev/Next VOLUME.
        // =================================================================
        if ($allNumbersEmpty) {
            $representativeIssue = $issuesArray[0];

            $templateMgr->assign('issue',   $representativeIssue);
            $templateMgr->assign('issueId', $representativeIssue->getBestIssueId());

            // Matikan navigasi issue untuk mencegah loop
            $templateMgr->assign('prevIssue', null);
            $templateMgr->assign('nextIssue', null);

            // Flag untuk template: ini adalah mode "volume-sebagai-issue"
            $templateMgr->assign('isVolumeAsIssue', true);

            // Setup konten artikel (TOC, galley, subscription, dll.)
            $this->_setupIssueContentForVolume($request, $representativeIssue, $journal);

            $templateMgr->display('issue/viewPage.tpl');
            return;
        }

        // =================================================================
        // KONDISI B: Ada issue dengan number valid
        //
        // Tampilkan daftar issue dalam volume.
        // Setiap issue di template akan memiliki link ke /volumes/{vol}/issue/{slug}.
        // =================================================================
        $firstIssue = $issuesArray[0];

        import('lib.pkp.classes.core.ArrayItemIterator');
        $issuesTemplateIterator = new ArrayItemIterator($issuesArray);

        $templateMgr->assign('issue',  $firstIssue);
        $templateMgr->assign('issues', $issuesTemplateIterator);

        $templateMgr->display('issue/viewVolume.tpl');
    }

    /**
     * Tampilkan daftar issue berdasarkan tahun (Level 3 Degradasi).
     *
     * Digunakan ketika issue->getVolume() kosong/NULL.
     * URL: /{journal}/year/{year}
     *
     * @param array $args  [0] = tahun (4 digit, contoh: 2023)
     * @param PKPRequest|null $request
     */
    public function year($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $year    = isset($args[0]) ? (int) $args[0] : 0;
        $journal = $request->getJournal();

        $this->setupTemplate($request);

        if (!$journal || $year < 1000 || $year > 9999) {
            $request->redirect(null, 'volumes');
            return;
        }

        $issueDao    = DAORegistry::getDAO('IssueDAO');
        $templateMgr = TemplateManager::getManager($request);

        // Cari issue berdasarkan kolom year
        $issuesFactory = $issueDao->getPublishedIssuesByNumber(
            $journal->getId(),
            null,   // volume: tidak difilter
            null,   // number: tidak difilter
            $year
        );
        $issuesArray = $issuesFactory->toArray();

        // Fallback: jika kolom year tidak terisi, cari dari date_published
        if (empty($issuesArray)) {
            $allIssues = $issueDao->getPublishedIssues($journal->getId());
            while ($iss = $allIssues->next()) {
                if ($iss->getDatePublished()) {
                    $issueYear = (int) date('Y', strtotime($iss->getDatePublished()));
                    if ($issueYear === $year) {
                        $issuesArray[] = $iss;
                    }
                }
            }
        }

        if (empty($issuesArray)) {
            $request->redirect(null, 'volumes');
            return;
        }

        import('lib.pkp.classes.core.ArrayItemIterator');
        $issuesTemplateIterator = new ArrayItemIterator($issuesArray);

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/'
            . $publicFileManager->getJournalFilesPath($journal->getId()) . '/';

        // Judul: gunakan angka tahun saja jika key locale tidak ada
        $yearTitleString = AppLocale::translate('issue.year') . ' ' . $year;
        if ($yearTitleString === 'issue.year ' . $year) {
            $yearTitleString = (string) $year;
        }

        list($prevYear, $nextYear) = $this->_getSurroundingYears(
            $journal->getId(), $year, $issueDao
        );

        $templateMgr->assign('coverPagePath',            $coverPagePath);
        $templateMgr->assign('locale',                   AppLocale::getLocale());
        $templateMgr->assign('pageTitleTranslated',       $yearTitleString);
        $templateMgr->assign('pageCrumbTitleTranslated',  $yearTitleString);
        $templateMgr->assign('pageHierarchy', [
            [$request->url(null, 'volumes'), 'archive.archives']
        ]);
        $templateMgr->assign('prevYear',  $prevYear);
        $templateMgr->assign('nextYear',  $nextYear);
        $templateMgr->assign('journal',   $journal);
        $templateMgr->assign('yearId',    $year);
        $templateMgr->assign('issues',    $issuesTemplateIterator);
        $templateMgr->assign('isYearView', true);

        $templateMgr->display('issue/viewYear.tpl');
    }

    // =================================================================
    // PRIVATE HELPERS
    // =================================================================

    /**
     * Setup variabel template untuk konten issue (artikel, galley, subscription).
     *
     * Dipanggil hanya pada Kondisi A (semua issue number kosong).
     * Logika sama dengan IssueHandler::_setupIssueTemplate(), tetapi
     * TIDAK meng-override variabel breadcrumb/pageTitle yang sudah di-set
     * oleh view() dengan konteks volume.
     *
     * @param PKPRequest $request
     * @param Issue      $issue    Issue representatif (pertama dalam volume)
     * @param Journal    $journal
     */
    private function _setupIssueContentForVolume($request, $issue, $journal) {
        $journalId   = $journal->getId();
        $templateMgr = TemplateManager::getManager($request);

        // Cek visibilitas
        $isVisible = $issue->getPublished()
            || Validation::isEditor($journalId)
            || Validation::isLayoutEditor($journalId)
            || Validation::isProofreader($journalId);

        if (!$isVisible || $issue->getJournalId() != $journalId) {
            $templateMgr->assign('showToc', false);
            $templateMgr->assign('issueHeadingTitle', __('archive.issueUnavailable'));
            return;
        }

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();

        $locale      = AppLocale::getLocale();
        $coverLocale = $issue->getFileName($locale) ? $locale : $journal->getPrimaryLocale();

        // Tentukan tampilan cover vs TOC
        // $showToc disimpan di variabel lokal PHP agar tersedia untuk logika
        // subscription di bawah tanpa round-trip ke template engine.
        // Setelah PKPTemplateManager di-patch, nilai ini juga bisa dibaca via
        // $templateMgr->getTemplateVars('showToc') dari kode eksternal.
        if ($issue->getFileName($coverLocale)
            && $issue->getShowCoverPage($coverLocale)
            && !$issue->getHideCoverPageCover($coverLocale)
        ) {
            // Ada cover page
            $templateMgr->assign('fileName',         $issue->getFileName($coverLocale));
            $templateMgr->assign('width',             $issue->getWidth($coverLocale));
            $templateMgr->assign('height',            $issue->getHeight($coverLocale));
            $templateMgr->assign('coverPageAltText',  $issue->getCoverPageAltText($coverLocale));
            $templateMgr->assign('originalFileName',  $issue->getOriginalFileName($coverLocale));
            $templateMgr->assign('coverLocale',       $coverLocale);
            $showToc = false;
        } else {
            // Tidak ada cover → langsung TOC
            $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
            $issueGalleys   = $issueGalleyDao->getGalleysByIssue($issue->getId());
            $templateMgr->assign('issueGalleys', $issueGalleys);

            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $publishedArticles   = $publishedArticleDao->getPublishedArticlesInSections(
                $issue->getId(), true
            );
            $templateMgr->assign('publishedArticles', $publishedArticles);
            $showToc = true;
        }

        // Assign $showToc ke template setelah nilainya pasti
        $templateMgr->assign('showToc', $showToc);
        $templateMgr->assign('issue', $issue);

        // Subscription
        import('classes.issue.IssueAction');
        $subscriptionRequired      = IssueAction::subscriptionRequired($issue);
        $subscribedUser            = IssueAction::subscribedUser($journal);
        $subscribedDomain          = IssueAction::subscribedDomain($journal);
        $subscriptionExpiryPartial = $journal->getSetting('subscriptionExpiryPartial');

        // Gunakan variabel lokal $showToc — lebih efisien dari getTemplateVars('showToc')
        // karena nilai sudah ada di scope PHP tanpa perlu membaca dari Smarty.
        if ($showToc
            && $subscriptionRequired
            && !$subscribedUser
            && !$subscribedDomain
            && $subscriptionExpiryPartial
        ) {
            $templateMgr->assign('subscriptionExpiryPartial', true);

            $partial = IssueAction::subscribedUser($journal, $issue->getId());
            if (!$partial) IssueAction::subscribedDomain($journal, $issue->getId());
            $templateMgr->assign('issueExpiryPartial', $partial);

            $publishedArticleDao   = DAORegistry::getDAO('PublishedArticleDAO');
            $publishedArticlesTemp = $publishedArticleDao->getPublishedArticles($issue->getId());
            $articleExpiryPartial  = [];
            foreach ($publishedArticlesTemp as $publishedArticle) {
                $partial = IssueAction::subscribedUser(
                    $journal, $issue->getId(), $publishedArticle->getId()
                );
                if (!$partial) IssueAction::subscribedDomain(
                    $journal, $issue->getId(), $publishedArticle->getId()
                );
                $articleExpiryPartial[$publishedArticle->getId()] = $partial;
            }
            $templateMgr->assign('articleExpiryPartial', $articleExpiryPartial);
        }

        $templateMgr->assign('subscriptionRequired',  $subscriptionRequired);
        $templateMgr->assign('subscribedUser',         $subscribedUser);
        $templateMgr->assign('subscribedDomain',       $subscribedDomain);
        $templateMgr->assign('showGalleyLinks',        $journal->getSetting('showGalleyLinks'));

        // Payment
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        if ($paymentManager->onlyPdfEnabled()) {
            $templateMgr->assign('restrictOnlyPdf', true);
        }
        if ($paymentManager->purchaseArticleEnabled()) {
            $templateMgr->assign('purchaseArticleEnabled', true);
        }

        // Style sheet khusus issue
        if ($styleFileName = $issue->getStyleFileName()) {
            $coverPagePathBase = $request->getBaseUrl() . '/'
                . $publicFileManager->getJournalFilesPath($journalId) . '/';
            $templateMgr->addStyleSheet($coverPagePathBase . $styleFileName);
        }

        $templateMgr->assign(
            'issueHeadingTitle',
            $issue->getIssueIdentification(false, true)
        );
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');

        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
    }

    /**
     * Cari tahun terdekat sebelum dan sesudah yang memiliki issue terbit.
     *
     * @param int      $journalId
     * @param int      $currentYear
     * @param IssueDAO $issueDao
     * @return array   [int|null $prevYear, int|null $nextYear]
     */
    private function _getSurroundingYears($journalId, $currentYear, $issueDao) {
        $prevYear = null;
        $nextYear = null;

        $resultNext = $issueDao->retrieve(
            'SELECT MIN(year) FROM issues
             WHERE journal_id = ? AND published = 1 AND year > ?',
            array((int) $journalId, (int) $currentYear)
        );
        if (isset($resultNext->fields[0]) && $resultNext->fields[0] !== null) {
            $nextYear = (int) $resultNext->fields[0];
        }
        $resultNext->Close();

        $resultPrev = $issueDao->retrieve(
            'SELECT MAX(year) FROM issues
             WHERE journal_id = ? AND published = 1 AND year < ?',
            array((int) $journalId, (int) $currentYear)
        );
        if (isset($resultPrev->fields[0]) && $resultPrev->fields[0] !== null) {
            $prevYear = (int) $resultPrev->fields[0];
        }
        $resultPrev->Close();

        return array($prevYear, $nextYear);
    }
}
?>