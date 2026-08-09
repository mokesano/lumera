<?php
declare(strict_types=1);

/**
 * @file pages/about/AboutPublisherHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class AboutPublisherHandler
 * @ingroup pages_about
 *
 * @brief Handler KHUSUS level Publisher/Site -- terpisah total dari
 * AboutJournalHandler (yang berurusan dengan konteks Jurnal). Op di sini
 * TIDAK PERNAH bercabang berdasarkan jurnal, karena secara desain memang
 * hanya relevan di level root/Penerbit. Semua tugas "about" untuk
 * konteks site/publisher (index, contact, sitemap, mission, history,
 * leadership, award) ditangani di sini -- AboutJournalHandler tidak lagi
 * punya kode untuk konteks ini sama sekali.
 */

import('pages.about.AboutHandler');

class AboutPublisherHandler extends AboutHandler {

    /**
     * Memuat dependensi antarmuka dan Locale
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();

        AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_CORE_MANAGER);
        $templateMgr->setCacheability(CACHEABILITY_PUBLIC);

        if ($subclass) {
            $templateMgr->assign('pageHierarchy', [[$request->url(null, 'about'), 'about.aboutThePublisher']]);
        }
    }

    /**
     * Display about index page (Site/Publisher root).
     * HANYA UNTUK KONTEKS SITE/PUBLISHER. Versi Jurnal ada di
     * AboutJournalHandler::index().
     * @param array $args
     * @param PKPRequest $request
     */
    public function contact($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        $site = $request->getSite();

        $templateMgr->assign([
            'sitePrincipalContactName'  => $site->getLocalizedData('contactName'),
            'sitePrincipalContactEmail' => $site->getLocalizedData('contactEmail'),
            'siteMailingAddress'        => $site->getLocalizedData('contactMailingAddress'),
        ]);

        $templateMgr->display('about/contact.tpl');
    }

    /**
     * Menangkap URL lama 'aboutThisPublishingSystem' dalam konteks
     * Site/Publisher dan mengalihkannya (301) ke halaman utama situs.
     * HANYA UNTUK KONTEKS SITE/PUBLISHER. Versi Jurnal ada di
     * AboutJournalHandler::aboutThisPublishingSystem().
     * @param array $args
     * @param PKPRequest $request
     */
    public function aboutThisPublishingSystem($args, $request = null) {
        $this->validate();

        if (!$request) $request = Application::get()->getRequest();

        $baseUrl = $request->getBaseUrl();
        header("Location: $baseUrl", true, 301);
        exit();
    }

    /**
     * Menampilkan halaman statis Penerbit (Misi).
     * Rute: /about/mission
     * @param array $args
     * @param PKPRequest $request
     */
    public function mission($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.publisher.mission', 'publisherMission');
    }

    /**
     * Menampilkan halaman statis Penerbit (Sejarah).
     * Rute: /about/history
     * @param PKPRequest $request
     */
    public function history($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.publisher.history', 'publisherHistory');
    }

    /**
     * Menampilkan halaman statis Penerbit (Kepemimpinan).
     * Rute: /about/leadership
     * @param array $args
     * @param PKPRequest $request
     */
    public function leadership($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.publisher.leadership', 'publisherLeaderships');
    }

    /**
     * Menampilkan halaman statis Penerbit (Penghargaan).
     * Rute: /about/award
     * @param array $args
     * @param PKPRequest $request
     */
    public function awards($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.publisher.award', 'publisherAwards');
    }

    /**
     * Helper bersama untuk keempat halaman statis Penerbit di atas --
     * satu template generik (about/publisherPage.tpl), dibedakan lewat
     * $pageTitleKey + $pageContent.
     * [FIX] Sebelumnya memakai key 'about.mission'/'about.leaderships'/
     * 'about.awards' yang TIDAK terdaftar di locale mana pun (tampil
     * ##key## mentah), dan 'about.history' yang SUDAH dipakai untuk History
     * level Jurnal (lihat AboutJournalHandler::history()) -- menimbulkan judul
     * halaman yang salah/tertukar. Diganti ke prefix about.publisher, yang
     * khusus dan sudah didefinisikan di locale.xml (en_US dan id_ID).
     * @param PKPRequest $request
     * @param string $pageTitleKey
     * @param string $settingName
     */
    private function _renderPublisherPage($request, string $pageTitleKey, string $settingName): void {
        $this->validate();
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager($request);
        $site = $request->getSite();

        import('lib.wizdam.classes.services.PublisherProfileService');

        $templateMgr->assign('pageTitleKey', $pageTitleKey);
        $templateMgr->assign('pageContent', $site->getLocalizedSetting($settingName));
        // [BARU] Identitas resmi Penerbit -- konsisten dengan Invoice/LoA/Sertifikat.
        $templateMgr->assign('publisher', (new PublisherProfileService())->getProfile());

        $templateMgr->display('about/publisherPage.tpl');
    }

}
?>