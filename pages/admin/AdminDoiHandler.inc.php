<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminDoiHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class AdminDoiHandler
 *
 * @brief Handler khusus untuk Site Administrator mengelola kredensial DOI
 * (Crossref, dst) level Publisher/Site.
 */

import('classes.handler.Handler');

class AdminDoiHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        // Kunci pintu rapat-rapat: HANYA Site Admin yang boleh masuk --
        // DOI adalah prefix milik penerbit, kredensialnya tidak boleh
        // diakses/diubah oleh Journal Manager manapun.
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn() && Validation::isSiteAdmin();
        }));
    }

    /**
     * Memuat dependensi antarmuka dan Locale
     * [BUGFIX] parent::setupTemplate() (AdminHandler) menerima parameter
     * boolean $subclass, BUKAN $request -- sebelumnya $this->setupTemplate()
     * dipanggil tanpa argumen (jadi $request=null diteruskan ke parent, yang
     * falsy), sehingga breadcrumb cuma tampil "User" tanpa "Site
     * Administration" maupun halaman saat ini.
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate(true); // true = sertakan "Site Administration" di breadcrumb
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->append('pageHierarchy', [
            $request->url(null, 'admin', 'doi-settings'),
            'admin.doi.settings'
        ]);

        AppLocale::requireComponents(
            [
                LOCALE_COMPONENT_CORE_COMMON,
                LOCALE_COMPONENT_CORE_USER,
                LOCALE_COMPONENT_APPLICATION_COMMON,
            ]
        );
    }

    /**
     * Menampilkan halaman Form Pengaturan Kredensial DOI
     * @param array $args
     * @param Request|null $request
     */
    public function doiSettings(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();

        if (!$request) $request = Application::get()->getRequest();

        import('lib.wizdam.classes.doi.form.DoiSettingsForm');
        $settingsForm = new DoiSettingsForm();
        $settingsForm->initData();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', 'admin.doi.settings');

        $settingsForm->display();
    }

    /**
     * Memproses penyimpanan form
     * @param array $args
     * @param Request|null $request
     */
    public function saveDoiSettings(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();

        if (!$request) $request = Application::get()->getRequest();

        import('lib.wizdam.classes.doi.form.DoiSettingsForm');
        $settingsForm = new DoiSettingsForm();
        $settingsForm->readInputData();

        if ($settingsForm->validate()) {
            $settingsForm->execute();

            $request->redirect(null, 'admin', 'doi-settings', null, ['saved' => 1]);
        } else {
            // Jika ada error (misal CSRF gagal), tampilkan ulang formnya
            $settingsForm->display();
        }
    }

}
?>