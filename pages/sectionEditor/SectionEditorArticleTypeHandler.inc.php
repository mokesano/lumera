<?php
declare(strict_types=1);

/**
 * @file pages/sectionEditor/SectionEditorArticleTypeHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class SectionEditorArticleTypeHandler
 * @ingroup pages_sectionEditor
 *
 * @brief [WIZDAM] Halaman KHUSUS Section Editor untuk mengatur
 * aktif/nonaktif tipe artikel BAKU pada Section yang menjadi
 * tanggung jawabnya -- Section Editor di sini berperan sebagai
 * semacam "Editor-in-chief" dari Section (Mini Jurnal) yang
 * dikelolanya.
 *
 * SENGAJA dibuat sebagai handler BARU dan TERPISAH di namespace
 * sectionEditor/ (BUKAN menambahkan akses ke manager/sections yang
 * sudah ada) -- ManagerHandler (induk SectionHandler) membatasi akses
 * HANYA untuk ROLE_ID_JOURNAL_MANAGER/ROLE_ID_SITE_ADMIN, Section
 * Editor SAMA SEKALI tidak bisa mengaksesnya. Pendekatan ini
 * TIDAK MENYENTUH sama sekali mekanisme akses manager/sections yang
 * sudah ada -- mengikuti konvensi arsitektur OJS yang sudah ada
 * (pages/sectionEditor/ terpisah dari pages/manager/).
 *
 * Section Editor HANYA bisa mengonfigurasi Section yang dia
 * DITUGASKAN di dalamnya (dicek lewat SectionEditorsDAO::
 * getSectionIdsByUserId()) -- dan HANYA bisa mempersempit dari tipe
 * yang SUDAH diizinkan Journal Manager di level jurnal (lihat
 * ArticleTypeAvailabilityDAO.inc.php untuk penjelasan hierarki
 * lengkap).
 */

import('classes.handler.Handler');

class SectionEditorArticleTypeHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SECTION_EDITOR]));
    }

    /**
     * [WIZDAM] Cek apakah user yang sedang login benar-benar
     * ditugaskan sebagai Section Editor untuk section_id tertentu --
     * dipanggil di AWAL sectionArticleTypes()/
     * saveSectionArticleTypes() sebagai pengaman TAMBAHAN (bukan
     * cuma bergantung role SECTION_EDITOR global, tapi benar-benar
     * dicek section spesifiknya).
     * @param int $sectionId
     * @param PKPRequest $request
     * @return bool
     */
    private function _isAssignedToSection($sectionId, $request) {
        $user = $request->getUser();
        $journal = $request->getJournal();
        if (!$user || !$journal) return false;

        /** @var SectionEditorsDAO $sectionEditorsDao */
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $assignedSectionIds = $sectionEditorsDao->getSectionIdsByUserId($user->getId(), $journal->getId());
        return in_array((int) $sectionId, $assignedSectionIds, true);
    }

    /**
     * Tampilkan daftar Section yang jadi tanggung jawab Section
     * Editor yang sedang login, dengan tautan ke halaman pengaturan
     * tipe artikel masing-masing.
     * @param array $args
     * @param PKPRequest $request
     */
    public function mySections($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $user = $request->getUser();
        $journal = $request->getJournal();

        /** @var SectionEditorsDAO $sectionEditorsDao */
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $assignedSectionIds = $sectionEditorsDao->getSectionIdsByUserId($user->getId(), $journal->getId());

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections = [];
        foreach ($assignedSectionIds as $sectionId) {
            $section = $sectionDao->getSection($sectionId, $journal->getId());
            if ($section) $sections[] = $section;
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('mySections', $sections);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.articleTypes');
        $templateMgr->display('sectionEditor/articleTypes/mySections.tpl');
    }

    /**
     * Tampilkan form checkbox aktif/nonaktif tipe artikel untuk SATU
     * Section tertentu.
     * @param array $args
     * @param PKPRequest $request
     */
    public function sectionArticleTypes($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $sectionId = (int) (isset($args[0]) ? $args[0] : 0);

        // [WIZDAM] Pengaman -- pastikan SE ini SUNGGUH ditugaskan ke
        // section ini, bukan cuma sekadar punya role SECTION_EDITOR
        // di jurnal manapun.
        if ($sectionId <= 0 || !$this->_isAssignedToSection($sectionId, $request)) {
            $request->redirect(null, 'sectionEditor', 'mySections');
            return;
        }

        $journal = $request->getJournal();
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($sectionId, $journal->getId());
        if (!$section) {
            $request->redirect(null, 'sectionEditor', 'mySections');
            return;
        }

        import('classes.article.ArticleType');
        /** @var ArticleTypeAvailabilityDAO $availabilityDao */
        $availabilityDao = DAORegistry::getDAO('ArticleTypeAvailabilityDAO');

        // [WIZDAM] Cuma tipe yang MASIH aktif di level jurnal yang
        // ditampilkan di sini -- SE tidak bisa mengaktifkan kembali
        // yang sudah dinonaktifkan JM (lihat penjelasan lengkap di
        // ArticleTypeAvailabilityDAO.inc.php).
        $journalEnabledTypes = $availabilityDao->getEnabledTypesForJournal($journal->getId());
        $disabledSectionTypes = $availabilityDao->getDisabledTypesForSection($sectionId);
        $journalDisabledTypes = $availabilityDao->getDisabledTypesForJournal($journal->getId());

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('section', $section);
        $templateMgr->assign('journalEnabledTypes', $journalEnabledTypes);
        $templateMgr->assign('journalDisabledTypes', $journalDisabledTypes);
        $templateMgr->assign('disabledSectionTypes', $disabledSectionTypes);
        $templateMgr->assign('standardEditorialOnlyTypes', ArticleType::getEditorialOnlyTypes());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.articleTypes');
        $templateMgr->display('sectionEditor/articleTypes/sectionArticleTypes.tpl');
    }

    /**
     * Simpan status aktif/nonaktif tipe artikel untuk SATU Section.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSectionArticleTypes($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $sectionId = (int) $request->getUserVar('sectionId');

        // [WIZDAM] Pengaman yang SAMA seperti sectionArticleTypes() --
        // dicek ULANG di sini juga (bukan cuma di halaman tampil),
        // supaya POST langsung tanpa lewat form tetap aman.
        if ($sectionId <= 0 || !$this->_isAssignedToSection($sectionId, $request)) {
            $request->redirect(null, 'sectionEditor', 'mySections');
            return;
        }

        $journal = $request->getJournal();

        // [WIZDAM] Checkbox yang TIDAK dicentang tidak ikut terkirim --
        // yang terkirim adalah daftar tipe yang MASIH dicentang (aktif
        // untuk section ini). Dibalik jadi daftar yang dinonaktifkan
        // (pola blacklist), HANYA dari kandidat tipe yang MEMANG masih
        // aktif di level jurnal (tipe yang sudah nonaktif di level
        // jurnal tidak pernah ditampilkan sebagai checkbox sama sekali,
        // jadi otomatis tidak mungkin "dicentang").
        import('classes.article.ArticleType');
        /** @var ArticleTypeAvailabilityDAO $availabilityDao */
        $availabilityDao = DAORegistry::getDAO('ArticleTypeAvailabilityDAO');
        $journalEnabledTypes = $availabilityDao->getEnabledTypesForJournal($journal->getId());

        $enabledTypes = (array) $request->getUserVar('enabledTypes');
        $disabledTypes = array_values(array_diff($journalEnabledTypes, $enabledTypes));

        $availabilityDao->setDisabledTypesForSection($sectionId, $journal->getId(), $disabledTypes);

        $request->redirect(null, 'sectionEditor', 'sectionArticleTypes', $sectionId);
    }

    /**
     * Configure the template.
     * @param PKPRequest|null $request
     */
    public function setupTemplate($request = null) {
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_SUBMISSION);
        parent::setupTemplate($request);
    }

}
?>