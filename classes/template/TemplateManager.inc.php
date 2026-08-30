<?php
declare(strict_types=1);

/**
 * @file classes/template/TemplateManager.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class TemplateManager
 * @ingroup template
 *
 * @brief Class for accessing the underlying template engine.
 * Currently integrated with Smarty.
 */

import('classes.search.ArticleSearch');
import('classes.file.PublicFileManager');
import('lib.pkp.classes.template.PKPTemplateManager');
import('classes.plugins.ThemePlugin');

class TemplateManager extends PKPTemplateManager {
    
    /**
     * Constructor.
     * @param PKPRequest|null $request
     */
    public function __construct(?PKPRequest $request = null) {
        parent::__construct($request);

        if (!$this->request) {
            $this->request = Registry::get('request');
        }

        $router = $this->request->getRouter();
        if (!is_a($router, 'PKPRouter')) {
            $router = $this->request->getRouter(); 
        }

        $this->assign('implicitAuth', strtolower((string) Config::getVar('security', 'implicit_auth')));

        if (!defined('SESSION_DISABLE_INIT')) {
            $journal = $router ? $router->getContext($this->request) : null;
            $site = $this->request->getSite();

            $publicFileManager = new PublicFileManager();
            
            // Use root-relative paths for public assets
            $siteFilesDir = $this->request->getBasePath() . '/public/site';
            
            $this->assign('sitePublicFilesDir', $siteFilesDir);
            $this->assign('publicFilesDir', $siteFilesDir);

            $siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
            if (file_exists($siteStyleFilename)) {
                $this->addStyleSheet($this->request->getBasePath() . '/public/site/' . $site->getSiteStyleFilename());
            }

            $this->assign('homeContext', []);
            $this->assign('siteCategoriesEnabled', $site->getSetting('categoriesEnabled'));

            if ($journal) {
                $this->assign('currentJournal', $journal);
                $this->assign('siteTitle', $journal->getLocalizedTitle());
                $this->assign('publicFilesDir', $this->request->getBasePath() . '/public/journals/' . $journal->getId());

                $this->assign('primaryLocale', $journal->getPrimaryLocale());
                $this->assign('alternateLocales', $journal->getSetting('alternateLocales'));
                $this->assign('navMenuItems', $journal->getLocalizedSetting('navItems'));

                $this->assign('displayPageHeaderTitle', $journal->getLocalizedPageHeaderTitle());
                $this->assign('displayPageHeaderLogo', $journal->getLocalizedPageHeaderLogo());
                $this->assign('displayPageHeaderTitleAltText', $journal->getLocalizedSetting('pageHeaderTitleImageAltText'));
                $this->assign('displayPageHeaderLogoAltText', $journal->getLocalizedSetting('pageHeaderLogoImageAltText'));
                $this->assign('displayFavicon', $journal->getLocalizedFavicon());
                $this->assign('faviconDir', $this->request->getBasePath() . '/public/journals/' . $journal->getId());
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

                $themePluginPath = $journal->getSetting('journalTheme');
                if (!empty($themePluginPath)) {
                    $themePlugin = PluginRegistry::loadPlugin('themes', $themePluginPath);
                    if ($themePlugin instanceof ThemePlugin) {
                        $themePlugin->activate($this);
                    }
                }

                $journalStyleSheet = $journal->getSetting('journalStyleSheet');
                if (is_array($journalStyleSheet) && isset($journalStyleSheet['uploadName'])) {
                    $this->addStyleSheet(
                        $this->request->getBasePath() . '/public/journals/' . $journal->getId() . '/' . $journalStyleSheet['uploadName']
                    );
                }

                import('classes.payment.ojs.OJSPaymentManager');
                $paymentManager = new OJSPaymentManager($this->request);
                $this->assign('journalPaymentsEnabled', $paymentManager->isConfigured());

                $this->assign('pageFooter', $journal->getLocalizedSetting('journalPageFooter'));
            } else {
                $displayPageHeaderTitle = $site->getLocalizedPageHeaderTitle();
                $this->assign('displayPageHeaderTitle', $displayPageHeaderTitle);

                if (is_array($displayPageHeaderTitle) && isset($displayPageHeaderTitle['altText'])) {
                    $this->assign('displayPageHeaderTitleAltText', $displayPageHeaderTitle['altText']);
                }

                $this->assign('siteTitle', $site->getLocalizedTitle());

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

            $user = $this->request->getUser();
            if ($user) {
                $this->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.pnotify.js');
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param PKPRequest|null $request
     */
    public function TemplateManager($request = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Return an instance of the TemplateManager.
     * @param PKPRequest|null $request
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
     * Custom Smarty function for retrieving help topic ids.
     * Usage: {get_help_id key="(dir)*.page.topic" url="boolean"}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyGetHelpId($params, &$smarty) {
        import('classes.help.Help');
        $help = Help::getHelp();
        
        if (!empty($params)) {
            $translatedKey = isset($params['key']) ? $help->translate($params['key']) : $help->translate('');

            if (isset($params['url']) && $params['url'] === 'true') {
                $request = Application::get()->getRequest();
                return $request->url(null, 'help', 'view', explode('/', $translatedKey));
            }
            return $translatedKey;
        }
        return '';
    }

    /**
     * Custom Smarty function for creating help topic anchor tags.
     * Usage: {help_topic key="(dir)*.page.topic" text="foo"}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyHelpTopic($params, &$smarty) {
        import('classes.help.Help');
        $help = Help::getHelp();
        
        if (!empty($params)) {
            $translatedKey = isset($params['key']) ? $help->translate($params['key']) : $help->translate('');
            $request = Application::get()->getRequest();
            $link = $request->url(null, 'help', 'view', explode('/', $translatedKey));
            $text = $params['text'] ?? '';
            
            return "<a href=\"{$link}\">{$text}</a>";
        }
        return '';
    }

    /**
     * Display page links for a listing of items divided onto multiple pages.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyPageLinks($params, &$smarty) {
        if (!isset($params['iterator']) || !isset($params['name'])) {
            return '';
        }

        $request = Application::get()->getRequest();
        $iterator = $params['iterator'];
        $name = $params['name'];
        
        if (isset($params['params']) && is_array($params['params'])) {
            $extraParams = $params['params'];
            unset($params['params']);
            $params = array_merge($params, $extraParams);
        }
        
        $anchor = $params['anchor'] ?? null;
        unset($params['anchor']);
        
        $allExtra = !empty($params['all_extra']) ? ' ' . $params['all_extra'] : '';
        unset($params['all_extra']);

        unset($params['iterator'], $params['name']);

        $numPageLinks = $smarty->get_template_vars('numPageLinks');
        if (!is_numeric($numPageLinks)) {
            $numPageLinks = 10;
        }

        $page = (int) $iterator->getPage();
        $pageCount = (int) $iterator->getPageCount();

        $pageBase = max($page - (int) floor($numPageLinks / 2), 1);
        $paramName = $name . 'Page';

        if ($pageCount <= 1) {
            return '';
        }

        $value = '';
        $requestedArgs = $request->getRequestedArgs();

        if ($page > 1) {
            $params[$paramName] = 1;
            $value .= '<a href="' . $request->url(null, null, null, $requestedArgs, $params, $anchor, true) . '"' . $allExtra . '>&lt;&lt;</a>&nbsp;';
            $params[$paramName] = $page - 1;
            $value .= '<a href="' . $request->url(null, null, null, $requestedArgs, $params, $anchor, true) . '"' . $allExtra . '>&lt;</a>&nbsp;';
        }

        for ($i = $pageBase; $i < min($pageBase + $numPageLinks, $pageCount + 1); $i++) {
            if ($i === $page) {
                $value .= "<strong>$i</strong>&nbsp;";
            } else {
                $params[$paramName] = $i;
                $value .= '<a href="' . $request->url(null, null, null, $requestedArgs, $params, $anchor, true) . '"' . $allExtra . '>' . $i . '</a>&nbsp;';
            }
        }
        
        if ($page < $pageCount) {
            $params[$paramName] = $page + 1;
            $value .= '<a href="' . $request->url(null, null, null, $requestedArgs, $params, $anchor, true) . '"' . $allExtra . '>&gt;</a>&nbsp;';
            $params[$paramName] = $pageCount;
            $value .= '<a href="' . $request->url(null, null, null, $requestedArgs, $params, $anchor, true) . '"' . $allExtra . '>&gt;&gt;</a>&nbsp;';
        }

        return $value;
    }

    /**
     * Display the template.
     * @param string $template
     * @param string|null $sendContentType
     * @param string|null $hookName
     * @param bool $display
     * @return string|null
     */
    public function display($template, $sendContentType = null, $hookName = null, $display = true) {
        $this->assign('stylesheets', $this->styleSheets);

        $request = Application::get()->getRequest();
        $user = $request->getUser();
        if ($user) {
            /** @var NotificationDAO $notificationDao */
            $notificationDao = DAORegistry::getDAO('NotificationDAO');
            $this->assign(
                'unreadNotifications',
                $notificationDao->getNotificationCount((int) $user->getId(), null, NOTIFICATION_LEVEL_NORMAL, false)
            );
        }

        return parent::display($template, $sendContentType, $hookName, $display);
    }

}
?>