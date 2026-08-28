<?php
declare(strict_types=1);

/**
 * @file pages/manager/ArticleTypeCustomHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleTypeCustomHandler
 * @ingroup pages_manager
 *
 * @brief [WIZDAM] Handle requests for custom article type management
 * functions, PLUS pengelolaan aktif/nonaktif tipe artikel BAKU level
 * JURNAL (kewenangan Journal Manager -- level SECTION oleh Section
 * Editor ditangani terpisah di pages/sectionEditor/
 * SectionEditorArticleTypeHandler.inc.php, lihat
 * ArticleTypeAvailabilityDAO.inc.php untuk penjelasan hierarki
 * lengkap).
 *
 * Pola CRUD tipe kustom SAMA PERSIS SectionHandler.inc.php (list/
 * create/edit/update/delete/move) -- TAPI jauh lebih sederhana karena
 * ArticleTypeCustom cuma punya nama (localized) + urutan, tidak ada
 * section editor/abstract requirement/dsb seperti Section.
 *
 * INI TIDAK menggantikan/memodifikasi SectionHandler -- murni handler
 * BARU untuk konsep tipe artikel kustom yang terpisah dari Section.
 */

import('pages.manager.ManagerHandler');

class ArticleTypeCustomHandler extends ManagerHandler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display a list of the custom article types within the current
     * journal, plus editable checkboxes for standard PUBLIC type
     * availability at the journal level.
     * @param array $args
     * @param PKPRequest $request
     */
    public function articleTypes($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        /** @var ArticleTypeCustomDAO $articleTypeCustomDao */
        $articleTypeCustomDao = DAORegistry::getDAO('ArticleTypeCustomDAO');
        $customTypes = $articleTypeCustomDao->getByJournalId($journal->getId())->toArray();

        import('classes.article.ArticleType');
        /** @var ArticleTypeAvailabilityDAO $availabilityDao */
        $availabilityDao = DAORegistry::getDAO('ArticleTypeAvailabilityDAO');
        $disabledJournalTypes = $availabilityDao->getDisabledTypesForJournal($journal->getId());

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'manager'), 'manager.journalManagement']]);
        $templateMgr->assign('customTypes', $customTypes);
        $templateMgr->assign('standardPublicTypes', ArticleType::getPublicTypes());
        $templateMgr->assign('standardEditorialOnlyTypes', ArticleType::getEditorialOnlyTypes());
        $templateMgr->assign('disabledJournalTypes', $disabledJournalTypes);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.articleTypes');

        $templateMgr->display('manager/articleTypes/articleTypes.tpl');
    }

    /**
     * [WIZDAM] Simpan status aktif/nonaktif tipe artikel BAKU untuk
     * SELURUH jurnal -- kewenangan Journal Manager (ManagerHandler
     * mensyaratkan role Journal Manager untuk seluruh handler ini,
     * jadi Section Editor tidak bisa mengakses op ini sama sekali,
     * dikonfirmasi lewat pembacaan langsung ManagerHandler::__construct()).
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveArticleTypeAvailability($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        // [WIZDAM] Checkbox yang TIDAK dicentang tidak ikut terkirim
        // dalam POST -- yang terkirim di sini adalah daftar tipe yang
        // MASIH dicentang (aktif). Kita balik logikanya jadi daftar
        // yang TIDAK dicentang (dinonaktifkan), karena itulah yang
        // disimpan (pola blacklist).
        import('classes.article.ArticleType');
        $enabledTypes = (array) $request->getUserVar('enabledTypes');
        $disabledTypes = array_values(array_diff(ArticleType::getPublicTypes(), $enabledTypes));

        /** @var ArticleTypeAvailabilityDAO $availabilityDao */
        $availabilityDao = DAORegistry::getDAO('ArticleTypeAvailabilityDAO');
        $availabilityDao->setDisabledTypesForJournal($journal->getId(), $disabledTypes);

        $request->redirect(null, null, 'articleTypes');
    }

    /**
     * Display form to create a new custom article type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createArticleType($args, $request) {
        $this->editArticleType($args, $request);
    }

    /**
     * Display form to create/edit a custom article type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editArticleType($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.manager.form.ArticleTypeCustomForm');

        $articleTypeForm = new ArticleTypeCustomForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
        if ($articleTypeForm->isLocaleResubmit()) {
            $articleTypeForm->readInputData();
        } else {
            $articleTypeForm->initData();
        }
        $articleTypeForm->display();
    }

    /**
     * Save changes to a custom article type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateArticleType($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.manager.form.ArticleTypeCustomForm');
        $articleTypeForm = new ArticleTypeCustomForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
        $articleTypeForm->readInputData();

        if ($articleTypeForm->validate()) {
            $articleTypeForm->execute();
            $request->redirect(null, null, 'articleTypes');
        } else {
            $articleTypeForm->display();
        }
    }

    /**
     * Delete a custom article type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteArticleType($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            $journal = $request->getJournal();
            /** @var ArticleTypeCustomDAO $articleTypeCustomDao */
            $articleTypeCustomDao = DAORegistry::getDAO('ArticleTypeCustomDAO');
            $articleTypeCustomDao->deleteById((int) $args[0], $journal->getId());
        }
        $request->redirect(null, null, 'articleTypes');
    }

    /**
     * Change the sequence of a custom article type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveArticleType($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        /** @var ArticleTypeCustomDAO $articleTypeCustomDao */
        $articleTypeCustomDao = DAORegistry::getDAO('ArticleTypeCustomDAO');
        $customTypeId = (int) trim((string) $request->getUserVar('id'));
        $customType = $articleTypeCustomDao->getById($customTypeId, $journal->getId());

        if ($customType != null) {
            $direction = trim((string) $request->getUserVar('d'));

            if (!empty($direction)) {
                if ($direction == 'u') {
                    $customType->setSequence($customType->getSequence() - 1.5);
                } elseif ($direction == 'd') {
                    $customType->setSequence($customType->getSequence() + 1.5);
                }
            } else {
                // [WIZDAM] Kasus drag-and-drop (tablednd.js).
                $prevId = (int) trim((string) $request->getUserVar('prevId'));

                if ($prevId == 0) {
                    $prevSeq = 0;
                } else {
                    $prevCustomType = $articleTypeCustomDao->getById($prevId, $journal->getId());
                    $prevSeq = $prevCustomType ? $prevCustomType->getSequence() : 0;
                }

                $customType->setSequence($prevSeq + .5);
            }

            $articleTypeCustomDao->updateCustomType($customType);
        }

        if (isset($direction) && $direction != null) {
            $request->redirect(null, null, 'articleTypes');
        }
    }

    /**
     * Configure the template.
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate(true);
        if ($subclass) {
            $templateMgr = TemplateManager::getManager();
            $request = Application::get()->getRequest();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'articleTypes'), 'article.type.customType']);
        }
    }

}
?>