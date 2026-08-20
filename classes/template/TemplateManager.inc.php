<?php
declare(strict_types=1);

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemplateManager
 * @ingroup template
 *
 * @brief Class for accessing the underlying template engine.
 * Currently integrated with Smarty (from http://smarty.php.net/).
 */

import('classes.search.ArticleSearch');
import('classes.file.PublicFileManager');
import('lib.pkp.classes.template.PKPTemplateManager');
import('classes.plugins.ThemePlugin');

class TemplateManager extends PKPTemplateManager {
    
    /**
     * Constructor.
     * Initialize template engine and assign basic template variables.
     * @param PKPRequest|null $request
     */
    public function __construct(?PKPRequest $request = null) {
        parent::__construct($request);

        if (!$this->request) {
            $this->request = Registry::get('request');
        }

        // Retrieve the router
        $router = $this->request->getRouter();
        if (!is_a($router, 'PKPRouter')) {
            // Fallback aman jika router tidak sesuai, mencegah fatal error di baris berikutnya
            $router = $this->request->getRouter(); 
        }

        // Are we using implicit authentication?
        $this->assign('implicitAuth', strtolower((string) Config::getVar('security', 'implicit_auth')));

        if (!defined('SESSION_DISABLE_INIT')) {
            /**
             * Kludge to make sure no code that tries to connect to
             * the database is executed (e.g., when loading
             * installer pages).
             */
            $journal = $router->getContext($this->request);
            $site = $this->request->getSite();

            $publicFileManager = new PublicFileManager();
            $siteFilesDir = $this->request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath();
            
            $this->assign('sitePublicFilesDir', $siteFilesDir);
            $this->assign('publicFilesDir', $siteFilesDir); // May be overridden by journal

            $siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
            if (file_exists($siteStyleFilename)) {
                $this->addStyleSheet($this->request->getBaseUrl() . '/' . $siteStyleFilename);
            }

            $this->assign('homeContext', []);
            $this->assign('siteCategoriesEnabled', $site->getSetting('categoriesEnabled'));

            if (isset($journal)) {
                $this->assign('currentJournal', $journal);
                
                $journalTitle = $journal->getLocalizedTitle();
                $this->assign('siteTitle', $journalTitle);
                $this->assign('publicFilesDir', $this->request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()));

                $this->assign('primaryLocale', $journal->getPrimaryLocale());
                $this->assign('alternateLocales', $journal->getSetting('alternateLocales'));

                // Assign additional navigation bar items
                $navMenuItems = $journal->getLocalizedSetting('navItems');
                $this->assign('navMenuItems', $navMenuItems);

                // Assign journal page header
                $this->assign('displayPageHeaderTitle', $journal->getLocalizedPageHeaderTitle());
                $this->assign('displayPageHeaderLogo', $journal->getLocalizedPageHeaderLogo());
                $this->assign('displayPageHeaderTitleAltText', $journal->getLocalizedSetting('pageHeaderTitleImageAltText'));
                $this->assign('displayPageHeaderLogoAltText', $journal->getLocalizedSetting('pageHeaderLogoImageAltText'));
                $this->assign('displayFavicon', $journal->getLocalizedFavicon());
                $this->assign('faviconDir', $this->request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()));
                $this->assign('alternatePageHeader', $journal->getLocalizedSetting('journalPageHeader'));
                $this->assign('metaSearchDescription', $journal->getLocalizedSetting('searchDescription'));
                $this->assign('metaSearchKeywords', $journal->getLocalizedSetting('searchKeywords'));
                $this->assign('metaCustomHeaders', $journal->getLocalizedSetting('customHeaders'));
                $this->assign('numPageLinks', $journal->getSetting('numPageLinks'));
                $this->assign('itemsPerPage', $journal->getSetting('itemsPerPage'));
                $this->assign('enableAnnouncements', $journal->getSetting('enableAnnouncements'));
                $this->assign(
                    'hideRegisterLink',
                    !$journal->getSetting('allowRegReviewer') &&
                    !$journal->getSetting('allowRegReader') &&
                    !$journal->getSetting('allowRegAuthor')
                );

                // Load and apply theme plugin, if chosen
                $themePluginPath = $journal->getSetting('journalTheme');
                if (!empty($themePluginPath)) {
                    $themePlugin = PluginRegistry::loadPlugin('themes', $themePluginPath);
                    if ($themePlugin instanceof ThemePlugin) {
                        $themePlugin->activate($this);
                    }
                }

                // Assign stylesheets and footer
                $journalStyleSheet = $journal->getSetting('journalStyleSheet');
                if (is_array($journalStyleSheet) && isset($journalStyleSheet['uploadName'])) {
                    $this->addStyleSheet(
                        $this->request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()) . '/' . $journalStyleSheet['uploadName']
                    );
                }

                import('classes.payment.ojs.OJSPaymentManager');
                $paymentManager = new OJSPaymentManager($this->request);
                $this->assign('journalPaymentsEnabled', $paymentManager->isConfigured());

                $this->assign('pageFooter', $journal->getLocalizedSetting('journalPageFooter'));
            } else {
                // Add the site-wide logo, if set for this locale or the primary locale
                $displayPageHeaderTitle = $site->getLocalizedPageHeaderTitle();
                $this->assign('displayPageHeaderTitle', $displayPageHeaderTitle);

                if (is_array($displayPageHeaderTitle) && isset($displayPageHeaderTitle['altText'])) {
                    $this->assign('displayPageHeaderTitleAltText', $displayPageHeaderTitle['altText']);
                }

                $this->assign('siteTitle', $site->getLocalizedTitle());

                // Load and apply theme plugin, if chosen
                $themePluginPath = $site->getSetting('siteTheme');
                if (!empty($themePluginPath)) {
                    $themePlugin = PluginRegistry::loadPlugin('themes', $themePluginPath);
                    if ($themePlugin instanceof ThemePlugin) {
                        $themePlugin->activate($this);
                    }
                }
            }

            if (!$site->getRedirect()) {
                $this->assign('hasOtherJournals', true);
            }

            // Add java script for notifications
            $user = $this->request->getUser();
            if ($user) {
                $this->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.pnotify.js');
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TemplateManager($request = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        $this->__construct($request);
    }

    /**
     * Return an instance of the TemplateManager.
     * @param PKPRequest|null $request optional
     * @return TemplateManager
     */
    public static function getManager(?PKPRequest $request = null): TemplateManager {
        $instance = Registry::get('templateManager', true, null);
        if ($instance === null) {
            $instance = new TemplateManager($request);
            Registry::set('templateManager', $instance);
        }

        return $instance;
    }

    /**
     * Smarty usage: {get_help_id key="(dir)*.page.topic" url="boolean"}
     *
     * Custom Smarty function for retrieving help topic ids.
     * @param array $params
     * @param Smarty $smarty
     */
    public function smartyGetHelpId($params, &$smarty) {
        import('classes.help.Help');
        $help = Help::getHelp();
        
        if (!empty($params)) {
            $translatedKey = isset($params['key']) ? $help->translate($params['key']) : $help->translate('');

            if (isset($params['url']) && $params['url'] === 'true') {
                return Request::url(null, 'help', 'view', explode('/', $translatedKey));
            }
            return $translatedKey;
        }
        return '';
    }

    /**
     * Smarty usage: {help_topic key="(dir)*.page.topic" text="foo"}
     *
     * Custom Smarty function for creating anchor tags
     * @param array $params associative array
     * @param Smarty $smarty
     */
    public function smartyHelpTopic($params, &$smarty) {
        import('classes.help.Help');
        $help = Help::getHelp();
        
        if (!empty($params)) {
            $translatedKey = isset($params['key']) ? $help->translate($params['key']) : $help->translate('');
            $link = Request::url(null, 'help', 'view', explode('/', $translatedKey));
            $text = $params['text'] ?? '';
            
            return "<a href=\"$link\">$text</a>";
        }
        return '';
    }

    /**
     * Display page links for a listing of items that has been
     * divided onto multiple pages.
     * @param array $params
     * @param Smarty $smarty
     */
    public function smartyPageLinks($params, &$smarty) {
        if (!isset($params['iterator']) || !isset($params['name'])) {
            return '';
        }

        $iterator = $params['iterator'];
        $name = $params['name'];
        
        if (isset($params['params']) && is_array($params['params'])) {
            $extraParams = $params['params'];
            unset($params['params']);
            $params = array_merge($params, $extraParams);
        }
        
        $anchor = $params['anchor'] ?? null;
        unset($params['anchor']);
        
        $allExtra = isset($params['all_extra']) ? ' ' . $params['all_extra'] : '';
        unset($params['all_extra']);

        unset($params['iterator']);
        unset($params['name']);

        $numPageLinks = $smarty->get_template_vars('numPageLinks');
        if (!is_numeric($numPageLinks)) {
            $numPageLinks = 10;
        }

        $page = $iterator->getPage();
        $pageCount = $iterator->getPageCount();
        // $itemTotal = $iterator->getCount();

        $pageBase = max($page - floor($numPageLinks / 2), 1);
        $paramName = $name . 'Page';

        if ($pageCount <= 1) {
            return '';
        }

        $value = '';

        if ($page > 1) {
            $params[$paramName] = 1;
            $value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&lt;&lt;</a>&nbsp;';
            $params[$paramName] = $page - 1;
            $value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&lt;</a>&nbsp;';
        }

        for ($i = $pageBase; $i < min($pageBase + $numPageLinks, $pageCount + 1); $i++) {
            if ($i == $page) {
                $value .= "<strong>$i</strong>&nbsp;";
            } else {
                $params[$paramName] = $i;
                $value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>' . $i . '</a>&nbsp;';
            }
        }
        
        if ($page < $pageCount) {
            $params[$paramName] = $page + 1;
            $value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&gt;</a>&nbsp;';
            $params[$paramName] = $pageCount;
            $value .= '<a href="' . Request::url(null, null, null, Request::getRequestedArgs(), $params, $anchor, true) . '"' . $allExtra . '>&gt;&gt;</a>&nbsp;';
        }

        return $value;
    }

    /**
     * [WIZDAM BUGFIX] PKPTemplateManager::__construct() melakukan
     * $this->assign('stylesheets', $this->styleSheets) SEKALI SAJA, saat
     * instance singleton pertama kali dibuat lewat getManager() -- di
     * titik itu $this->styleSheets MASIH KOSONG. Smarty::assign() memakai
     * assignment PHP biasa (SALINAN NILAI untuk array, BUKAN referensi).
     *
     * Akibatnya: addStyleSheet() yang dipanggil BELAKANGAN dari method
     * handler mana pun (mis. ArticleHandler::view()/viewArticleGalley()
     * memanggil addStyleSheet('styles/pdfView.css')) menambah
     * $this->styleSheets, tapi TIDAK PERNAH tercermin ke variabel Smarty
     * 'stylesheets' yang sudah kadung ter-assign SEBELUM handler manapun
     * sempat berjalan -- stylesheet itu lenyap dari <head> tanpa error
     * apa pun, terlepas method mana yang memanggil addStyleSheet().
     *
     * Perbaikan: re-assign 'stylesheets' di SINI, tepat sebelum setiap
     * render, dengan isi $this->styleSheets TERKINI -- menangkap SEMUA
     * addStyleSheet() yang sudah dipanggil sampai titik ini, dari
     * manapun asalnya (view(), viewArticleGalley(), atau method lain).
     * @param string $template
     * @param string|null $sendContentType
     * @param string|null $hookName
     * @param bool $display
     * @return string|null
     */
    public function display($template, $sendContentType = null, $hookName = null, $display = true) {
        $this->assign('stylesheets', $this->styleSheets);
        return parent::display($template, $sendContentType, $hookName, $display);
    }

}
?>