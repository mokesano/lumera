<?php
declare(strict_types=1);

/**
 * @file pages/about/PublisherAboutHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class PublisherAboutHandler
 * @ingroup pages_about
 *
 * @brief Handler KHUSUS level Publisher/Site -- terpisah total dari
 * AboutHandler (yang berurusan dengan konteks Jurnal). Op di sini TIDAK
 * PERNAH bercabang berdasarkan jurnal, karena secara desain memang hanya
 * relevan di level root/Penerbit.
 */

import('classes.handler.Handler');

class PublisherAboutHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

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
            $templateMgr->assign('pageHierarchy', [[$request->url(null, 'about'), 'about.aboutTheJournal']]);
        }
    }

    /**
     * Menampilkan halaman statis Penerbit (Misi).
     * Rute: /about/mission
     * @param array $args
     * @param PKPRequest $request
     */
    public function mission($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.mission', 'publisherMission');
    }

    /**
     * Menampilkan halaman statis Penerbit (Sejarah).
     * Rute: /about/history
     * @param array $args
     * @param PKPRequest $request
     */
    public function history($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.history', 'publisherHistory');
    }

    /**
     * Menampilkan halaman statis Penerbit (Kepemimpinan).
     * Rute: /about/leadership
     * @param array $args
     * @param PKPRequest $request
     */
    public function leadership($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.leaderships', 'publisherLeaderships');
    }

    /**
     * Menampilkan halaman statis Penerbit (Penghargaan).
     * Rute: /about/award
     * @param array $args
     * @param PKPRequest $request
     */
    public function award($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_renderPublisherPage($request, 'about.awards', 'publisherAwards');
    }

    /**
     * Helper bersama untuk keempat halaman statis Penerbit di atas --
     * satu template generik (about/publisherPage.tpl), dibedakan lewat
     * $pageTitleKey + $pageContent.
     * @param PKPRequest $request
     * @param string $pageTitleKey
     * @param string $settingName
     */
    private function _renderPublisherPage($request, string $pageTitleKey, string $settingName): void {
        $this->validate();
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager($request);
        $site = $request->getSite();

        $templateMgr->assign('pageTitleKey', $pageTitleKey);
        $templateMgr->assign('pageContent', $site->getLocalizedSetting($settingName));

        $templateMgr->display('about/publisherPage.tpl');
    }

}
?>