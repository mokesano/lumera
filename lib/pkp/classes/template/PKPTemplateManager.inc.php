<?php
declare(strict_types=1);

/**
 * @file classes/template/PKPTemplateManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemplateManager
 * @ingroup template
 *
 * @brief Class for accessing the underlying template engine.
 * Currently integrated with Smarty (from http://smarty.php.net/).
 */

/* This definition is required by Smarty */
define('SMARTY_DIR', Core::getBaseDir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR);

require_once('./lib/pkp/lib/smarty/Smarty.class.php');
require_once('./lib/pkp/lib/smarty/plugins/modifier.escape.php');

define('CACHEABILITY_NO_CACHE', 'no-cache');
define('CACHEABILITY_NO_STORE', 'no-store');
define('CACHEABILITY_PUBLIC', 'public');
define('CACHEABILITY_MUST_REVALIDATE', 'must-revalidate');
define('CACHEABILITY_PROXY_REVALIDATE', 'proxy-revalidate');

define('CDN_JQUERY_VERSION', '1.4.4');
define('CDN_JQUERY_UI_VERSION', '1.8.6');

class PKPTemplateManager extends Smarty {

    /** @var array of URLs to stylesheets */
    public array $styleSheets = [];

    /** @var array of URLs to javascript files */
    public array $javaScripts = [];

    /** @var bool Initialized flag */
    public bool $initialized = false;

    /** @var string Type of cacheability (Cache-Control). */
    public string $cacheability;

    /** @var mixed The form builder vocabulary class. */
    public $fbv;

    /** @var PKPRequest|null */
    public ?PKPRequest $request;

    // Smarty specific path properties
    public string $app_template_dir;
    public string $core_template_dir;

    /**
     * Constructor.
     * Initialize template engine and assign basic template variables.
     * @param PKPRequest|null $request
     */
    public function __construct(?PKPRequest $request = null) {
        // [LUMERA] Fetch Singleton Request
        if ($request === null) {
            $this->request = Application::get()->getRequest();
        } else {
            $this->request = $request;
        }
        
        assert($this->request instanceof PKPRequest);

        $router = $this->request->getRouter();
        parent::__construct();

        // Set up Smarty configuration
        $baseDir = Core::getBaseDir();
        $cachePath = CacheManager::getFileCachePath();

        $this->app_template_dir = $baseDir . DIRECTORY_SEPARATOR . 'templates';
        $this->core_template_dir = $baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'templates';

        $this->template_dir = [$this->app_template_dir, $this->core_template_dir];
        $this->compile_dir = $cachePath . DIRECTORY_SEPARATOR . 't_compile';
        $this->config_dir = $cachePath . DIRECTORY_SEPARATOR . 't_config';
        $this->cache_dir = $cachePath . DIRECTORY_SEPARATOR . 't_cache';

        // Prevent PHP execution in templates <?xml → &lt;?xml → <?xml
        $this->php_handling = defined('SMARTY_PHP_QUOTE') ? SMARTY_PHP_QUOTE : 1;
        if (method_exists($this, 'registerFilter')) {
            $this->registerFilter('output', [$this, 'fixXmlOutput']);
        } else {
            $this->register_outputfilter([$this, 'fixXmlOutput']);
        }
        
        // Assign common variables
        $this->styleSheets = [];
        $this->javaScripts = [];
        $this->cacheability = CACHEABILITY_NO_STORE; 

        $this->assign('defaultCharset', Config::getVar('i18n', 'client_charset'));
        $this->assign('basePath', $this->request->getBasePath());
        $this->assign('baseUrl', $this->request->getBaseUrl());
        $this->assign('requiresFormRequest', $this->request->isPost());
        
        if ($router instanceof PKPPageRouter) {
            $this->assign('requestedPage', $router->getRequestedPage($this->request));
        }
        
        $this->assign('currentUrl', $this->request->getCompleteUrl());
        $this->assign('dateFormatTrunc', Config::getVar('general', 'date_format_trunc'));
        $this->assign('dateFormatShort', Config::getVar('general', 'date_format_short'));
        $this->assign('dateFormatLong', Config::getVar('general', 'date_format_long'));
        $this->assign('datetimeFormatShort', Config::getVar('general', 'datetime_format_short'));
        $this->assign('datetimeFormatLong', Config::getVar('general', 'datetime_format_long'));
        $this->assign('timeFormat', Config::getVar('general', 'time_format'));
        $this->assign('allowCDN', Config::getVar('general', 'enable_cdn'));
        $this->assign('useMinifiedJavaScript', Config::getVar('general', 'enable_minified'));
        $this->assign('toggleHelpOnText', __('help.toggleInlineHelpOn'));
        $this->assign('toggleHelpOffText', __('help.toggleInlineHelpOff'));

        $locale = AppLocale::getLocale();
        $this->assign('currentLocale', $locale);

        $localeStyleSheet = AppLocale::getLocaleStyleSheet($locale);
        if ($localeStyleSheet !== null && $localeStyleSheet !== '') {
            $this->addStyleSheet($this->request->getBaseUrl() . '/' . $localeStyleSheet);
        }

        $application = PKPApplication::getApplication();
        $siteTitle = $application->getNameKey();
        $this->assign('pageTitle', $siteTitle);
        
        $this->assign('exposedConstants', $application->getExposedConstants());
        $this->assign('jsLocaleKeys', $application->getJSLocaleKeys());
        
        // Register custom functions
        $this->register_modifier('mask_email', [$this, 'smartyMaskEmail']);
        $this->register_modifier('translate', ['AppLocale', 'translate']);
        $this->register_modifier('get_value', [$this, 'smartyGetValue']);
        $this->register_modifier('strip_unsafe_html', ['PKPString', 'stripUnsafeHtml']);
        $this->register_modifier('String_substr', ['PKPString', 'substr']);
        $this->register_modifier('to_array', [$this, 'smartyToArray']);
        $this->register_modifier('concat', [$this, 'smartyConcat']);
        $this->register_modifier('escape', [$this, 'smartyEscape']);
        $this->register_modifier('strtotime', [$this, 'smartyStrtotime']);
        $this->register_modifier('explode', [$this, 'smartyExplode']);
        $this->register_modifier('assign', [$this, 'smartyAssign']);
        $this->register_modifier('slugify', ['PKPString', 'slugify']);
        $this->register_function('native_url', [$this, 'smartyNativeUrl']);
        $this->register_function('form_language_chooser', [$this, 'smartyFormLanguageChooser']);
        $this->register_function('translate', [$this, 'smartyTranslate']);
        $this->register_function('null_link_action', [$this, 'smartyNullLinkAction']);
        $this->register_function('flush', [$this, 'smartyFlush']);
        $this->register_function('call_hook', [$this, 'smartyCallHook']);
        $this->register_function('html_options_translate', [$this, 'smartyHtmlOptionsTranslate']);
        $this->register_block('iterate', [$this, 'smartyIterate']);
        $this->register_function('call_progress_function', [$this, 'smartyCallProgressFunction']);
        $this->register_function('page_links', [$this, 'smartyPageLinks']);
        $this->register_function('page_info', [$this, 'smartyPageInfo']);
        $this->register_function('get_help_id', [$this, 'smartyGetHelpId']);
        $this->register_function('icon', [$this, 'smartyIcon']);
        $this->register_function('help_topic', [$this, 'smartyHelpTopic']);
        $this->register_function('sort_heading', [$this, 'smartySortHeading']);
        $this->register_function('sort_search', [$this, 'smartySortSearch']);
        $this->register_function('get_debug_info', [$this, 'smartyGetDebugInfo']);
        $this->register_function('assign_mailto', [$this, 'smartyAssignMailto']);
        $this->register_function('display_template', [$this, 'smartyDisplayTemplate']);
        $this->register_modifier('truncate', [$this, 'smartyTruncate']);
        $this->register_function('modal', [$this, 'smartyModal']);
        $this->register_function('confirm', [$this, 'smartyConfirm']);
        $this->register_function('confirm_submit', [$this, 'smartyConfirmSubmit']);
        $this->register_function('modal_title', [$this, 'smartyModalTitle']);

        $fbv = $this->getFBV();
        $this->register_block('fbvFormSection', [$fbv, 'smartyFBVFormSection']);
        $this->register_block('fbvFormArea', [$fbv, 'smartyFBVFormArea']);
        $this->register_function('fbvFormButtons', [$fbv, 'smartyFBVFormButtons']);
        $this->register_function('fbvElement', [$fbv, 'smartyFBVElement']);
        $this->assign('fbvStyles', $fbv->getStyles());
        $this->register_function('fieldLabel', [$fbv, 'smartyFieldLabel']);

        $this->register_resource('core', [
            [$this, 'smartyResourceCoreGetTemplate'],
            [$this, 'smartyResourceCoreGetTimestamp'],
            [$this, 'smartyResourceCoreGetSecure'],
            [$this, 'smartyResourceCoreGetTrusted']
        ]);

        $this->register_function('url', [$this, 'smartyUrl']);
        $this->register_function('load_url_in_div', [$this, 'smartyLoadUrlInDiv']);

        if (!defined('SESSION_DISABLE_INIT')) {
            $this->assign('isUserLoggedIn', Validation::isLoggedIn());

            $currentVersion = $application->getCurrentVersion();
            $this->assign('currentVersionString', $currentVersion->getVersionString());

            $this->assign('itemsPerPage', Config::getVar('interface', 'items_per_page'));
            $this->assign('numPageLinks', Config::getVar('interface', 'page_links'));
        }

        $this->assign('stylesheets', $this->styleSheets);
        $this->initialized = false;
    }
    
    /**
     * [SHIM] Backward Compatibility.
     * @param PKPRequest|null $request
     */
    public function PKPTemplateManager($request = null) {
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
     * Override the Smarty {include ...} function to allow hooks to be called.
     * @param array $params
     * @return string|false
     */
    public function _smarty_include($params) {
        if (!HookRegistry::dispatch('TemplateManager::include', [&$this, &$params])) {
            return parent::_smarty_include($params);
        }
        return false;
    }

    /**
     * Flag the page as cacheable (or not).
     * @param string $cacheability optional
     * @return void
     */
    public function setCacheability(string $cacheability = CACHEABILITY_PUBLIC) {
        $this->cacheability = $cacheability;
    }

    /**
     * Initialize the template.
     * @return void
     */
    public function initialize() {
        PluginRegistry::loadCategory('blocks', true);

        if (!defined('SESSION_DISABLE_INIT')) {
            $journal = $this->request->getJournal();
            $displayGroups = []; 

            if ($journal !== null) {
                /** @var GroupDAO $groupDao */
                $groupDao = DAORegistry::getDAO('GroupDAO');
                $displayGroups = $groupDao->getBoardGroupsForDisplay((int) $journal->getId());
            }
                
            $this->assign('membershipGroups', $displayGroups);
            
            import('lib.wizdam.classes.services.PublisherProfileService');
            $this->assign('publisher', (new PublisherProfileService())->getProfile());

            if ($journal !== null) {
                import('lib.wizdam.sinta.SintaScoreService');
                SintaScoreService::ensureScoreExists($journal);

                $this->assign('sintaScore', $journal->getSetting('sintaScore'));
                $this->assign('sintaGrade', $journal->getSetting('sintaGrade'));
                $this->assign('sintaLastUpdate', $journal->getSetting('sintaLastUpdate'));
            }

            $user = $this->request->getUser();
            $hasSystemNotifications = false;
            
            $session = $this->request->getSession();
            $this->assign('userSession', $session);
                        
            if ($user !== null) {
                $this->assign('isUserLoggedIn', true); 
                $this->assign('loggedInUsername', (string) $user->getUserName());
                $this->assign('initialHelpState', (int) $user->getInlineHelp());

                /** @var NotificationDAO $notificationDao */
                $notificationDao = DAORegistry::getDAO('NotificationDAO');
                $notifications = $notificationDao->getByUserId((int) $user->getId(), NOTIFICATION_LEVEL_TRIVIAL);
                if ($notifications->getCount() > 0) {
                    $hasSystemNotifications = true;
                }

                $dateValidated = $user->getDateValidated();
                $dateRegistered = $user->getDateRegistered();
                
                /** @var UserSettingsDAO $userSettingsDao */
                $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
                $dateLastLogin = $userSettingsDao->getSetting((int) $user->getId(), 'previous_login');
                $dateCurrentLogin = $user->getDateLastLogin();
                                
                $verificationStatus = $dateValidated ? __('user.profile.verified') : __('user.profile.unverified');
                                
                $userData = [
                    'id' => (int) $user->getId(),
                    'firstName' => htmlspecialchars((string) $user->getFirstName(), ENT_QUOTES, 'UTF-8'),
                    'middleName' => htmlspecialchars((string) $user->getMiddleName(), ENT_QUOTES, 'UTF-8'),
                    'lastName' => htmlspecialchars((string) $user->getLastName(), ENT_QUOTES, 'UTF-8'),
                    'suffix' => htmlspecialchars((string) $user->getSuffix(), ENT_QUOTES, 'UTF-8'),
                    'profileImage' => $this->_sanitizeProfileImage($user->getSetting('profileImage')),
                    'gender' => (string) $user->getGender(),
                    'salutation' => htmlspecialchars((string) $user->getSalutation(), ENT_QUOTES, 'UTF-8'),
                    'email' => (string) $user->getEmail(),
                    'verification_status' => $verificationStatus,
                    'is_verified' => (bool) $dateValidated,
                    'validated' => $dateValidated,
                    'registered' => $dateRegistered,
                    'last_login' => $dateLastLogin,
                    'current_login' => $dateCurrentLogin
                ];
                                
                $this->assign('userData', $userData);
            } else {
                $this->assign('isUserLoggedIn', false);
                $this->assign('userData', null);
            }

            $this->assign('hasSystemNotifications', $hasSystemNotifications);
        }
        
        $this->register_modifier('dotat_mail', [$this, 'smartyDotatEmail']);
        $this->register_modifier('mask_phone', [$this, 'smartyMaskPhone']);
        $this->register_modifier('file_size', [$this, 'smartyFileSize']);
        $this->register_modifier('time_ago', [$this, 'smartyTimeAgo']);
        $this->register_modifier('short_number', [$this, 'smartyShortMetric']);
        $this->register_modifier('ShortNumber', [$this, 'smartyShortMetricNumber']);
        $this->register_modifier('ShortSuffix', [$this, 'smartyShortMetricSuffix']);
        $this->register_modifier('metric_number', [$this, 'smartyMetricNumber']);
        $this->register_modifier('metric_suffix', [$this, 'smartyMetricSuffix']);
        
        import('lib.pkp.classes.validation.ValidatorCSRF');
        $this->assign(ValidatorCSRF::FIELD_NAME, ValidatorCSRF::generateToken('global'));

        $this->initialized = true;
    }

    /**
     * Add a page-specific style sheet.
     * @param string $url the URL to the style sheet
     * @return void
     */
    public function addStyleSheet($url) {
        $this->styleSheets[] = (string) $url;
    }

    /**
     * Add a page-specific script.
     * @param string $url the URL to be included
     * @return void
     */
    public function addJavaScript($url) {
        $this->javaScripts[] = (string) $url;
    }

    /**
     * Fetch a rendered template.
     * @param string $resource_name the name of the template resource
     * @param string|null $cache_id optional cache ID
     * @param string|null $compile_id optional compile ID
     * @param bool $display
     * @return string
     */
    public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
        if (!$this->initialized) {
            $this->initialize();
        }

        if (!empty($this->javaScripts)) {
            $baseUrl = (string) $this->get_template_vars('baseUrl');
            $scriptOpen = '    <script type="text/javascript" src="';
            $scriptClose = '"></script>';
            $javaScript = '';
            foreach ($this->javaScripts as $script) {
                $javaScript .= $scriptOpen . $baseUrl . '/' . (string) $script . $scriptClose . "\n";
            }

            $additionalHeadData = (string) $this->get_template_vars('additionalHeadData');
            $this->assign('additionalHeadData', $additionalHeadData . "\n" . $javaScript);
            $this->javaScripts = [];
        }

        set_error_handler(function($errno, $errstr) {
            if ($errno === E_WARNING && strpos((string) $errstr, 'Undefined array key') !== false) {
                return true;
            }
            return false;
        });
        
        try {
            $result = parent::fetch($resource_name, $cache_id, $compile_id, $display);
        } finally {
            restore_error_handler();
        }
    
        return $result;
    }

    /**
     * Returns the template results as a JSON message.
     * @param string $template
     * @param bool $status
     * @return string JSON message with the template rendered
     */
    public function fetchJson($template, $status = true) {
        import('lib.pkp.classes.core.JSONMessage');

        $json = new JSONMessage($status, $this->fetch($template));
        header('Content-Type: application/json');
        return $json->getString();
    }
    
    /**
     * Membaca kembali nilai variabel template yang sudah di-assign.
     * @param string|null $varName
     * @return mixed
     */
    public function getTemplateVars($varName = null) {
        if ($varName === null) {
            return $this->_tpl_vars;
        }
        return $this->_tpl_vars[$varName] ?? null;
    }

    /**
     * Alias eksplisit untuk mengambil satu variabel template.
     * @param string $varName
     * @param mixed $default
     * @return mixed
     */
    public function getSingleTemplateVar($varName, $default = null) {
        return $this->_tpl_vars[$varName] ?? $default;
    }

    /**
     * Display the template.
     * @param string $template the name of the template resource
     * @param string|null $sendContentType
     * @param string|null $hookName
     * @param bool $display
     * @return string|null
     */
    public function display($template, $sendContentType = null, $hookName = null, $display = true) {
        $oldErrorReporting = error_reporting();
        
        if (strpos((string) $template, 'login.tpl') !== false) {
            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
        }
        
        if ($sendContentType === null) {
            $sendContentType = 'text/html';
        }
        if ($hookName === null) {
            $hookName = 'TemplateManager::display';
        }

        $charset = Config::getVar('i18n', 'client_charset');
        $output = null;
        
        if (!HookRegistry::dispatch($hookName, [&$this, &$template, &$sendContentType, &$charset, &$output])) {
            if ($hookName === 'TemplateManager::display') {
                header('Content-Type: ' . (string) $sendContentType . '; charset=' . (string) $charset);
                header('Cache-Control: ' . $this->cacheability);
            }
            return $this->fetch($template, null, null, $display);
        } else {
            echo $output;
        }
        
        if (strpos((string) $template, 'login.tpl') !== false) {
            error_reporting($oldErrorReporting);
        }
        
        return null;
    }

    /**
     * Display templates from Smarty and allow hook overrides.
     * @param array $params
     * @param Smarty $smarty
     * @return void
     */
    public function smartyDisplayTemplate($params, $smarty) {
        $templateMgr = TemplateManager::getManager();
        if (isset($params['template'])) {
            $templateMgr->display((string) $params['template'], "", $params['hookname'] ?? null);
        }
    }

    /**
     * Clear template compile and cache directories.
     * @return void
     */
    public function clearTemplateCache() {
        $this->clear_compiled_tpl();
        $this->clear_all_cache();
    }

    /**
     * Return an instance of the Form Builder Vocabulary class.
     * @return mixed FormBuilderVocabulary object
     */
    public function &getFBV() {
        if ($this->fbv === null) {
            import('lib.pkp.classes.form.FormBuilderVocabulary');
            $this->fbv = new FormBuilderVocabulary();
        }
        return $this->fbv;
    }

    //
    // Custom Template Resource "Core"
    //

    /**
     * Resource function to get a "core" (pkp-lib) template.
     * @param string $template
     * @param string $templateSource
     * @param Smarty $smarty
     * @return bool
     */
    public function smartyResourceCoreGetTemplate($template, &$templateSource, &$smarty) {
        $templateSource = file_get_contents($this->core_template_dir . DIRECTORY_SEPARATOR . $template);
        return $templateSource !== false;
    }

    /**
     * Resource function to get the timestamp of a "core" (pkp-lib) template.
     * @param string $template
     * @param int $templateTimestamp
     * @return bool
     */
    public function smartyResourceCoreGetTimestamp($template, &$templateTimestamp, &$smarty) {
        $templateSource = $this->core_template_dir . DIRECTORY_SEPARATOR . $template;
        if (!file_exists($templateSource)) {
            return false;
        }
        $templateTimestamp = filemtime($templateSource);
        return true;
    }

    /**
     * Resource function to determine whether a "core" (pkp-lib) template is secure.
     * @param string $template
     * @param Smarty $smarty
     * @return bool
     */
    public function smartyResourceCoreGetSecure($template, &$smarty) {
        return true;
    }

    /**
     * Resource function to determine whether a "core" (pkp-lib) template is trusted.
     * @param string $template
     * @param Smarty $smarty
     * @return bool
     */
    public function smartyResourceCoreGetTrusted($template, &$smarty) {
        $trustedDirs = [
            realpath($this->core_template_dir),
            realpath($this->app_template_dir),
        ];
    
        $realTemplate = realpath($template);
        if ($realTemplate === false) {
            return false;
        }
    
        foreach ($trustedDirs as $dir) {
            if ($dir !== false && strpos($realTemplate, $dir . DIRECTORY_SEPARATOR) === 0) {
                return true;
            }
        }
    
        if (Config::getVar('debug', 'show_stats')) {
            error_log('[Wizdam Security] Untrusted template blocked: ' . $template);
        }
    
        return false;
    }
    
    /**
     * Smarty function untuk menampilkan pemilih bahasa di form.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyFormLanguageChooser($params, &$smarty) {
        $url = $params['url'] ?? null;
        $formLocales = $smarty->get_template_vars('formLocales');
        
        if (!$formLocales || count($formLocales) <= 1) {
            return '';
        }
    
        $smarty->assign('formLocales', $formLocales);
        $smarty->assign('formLocale', $smarty->get_template_vars('formLocale'));
        $smarty->assign('formLanguageChooserUrl', $url);
        
        return $smarty->fetch('common/formLanguageChooser.tpl');
    }

    //
    // Custom template functions, modifiers, etc.
    //

    /**
     * Smarty usage: {translate key="localization.key.name" [paramName="paramValue" ...]}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyTranslate($params, &$smarty) {
        if (!empty($params)) {
            if (!isset($params['key'])) {
                return __('');
            }

            $key = $params['key'];
            unset($params['key']);
            
            if (isset($params['params']) && is_array($params['params'])) {
                $paramsArray = $params['params'];
                unset($params['params']);
                $params = array_merge($params, $paramsArray);
            }
            return __($key, $params);
        }
        return '';
    }

    /**
     * Smarty usage: {null_link_action id="linkId" key="localization.key.name" image="imageClassName"}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyNullLinkAction($params, &$smarty) {
        assert(isset($params['id']));

        $id = $params['id'];
        $key = $params['key'] ?? null;
        $hoverTitle = isset($params['hoverTitle']);
        $image = $params['image'] ?? null;
        $translate = !isset($params['translate']) || $params['translate'] !== false;

        import('lib.pkp.classes.linkAction.request.NullAction');
        $key = $translate ? __((string) $key) : (string) $key;
        
        $this->assign('action', new LinkAction($id, new NullAction(), $key, $image));
        $this->assign('hoverTitle', $hoverTitle);
        
        return $this->fetch('linkAction/linkAction.tpl');
    }

    /**
     * Smarty usage: {assign_mailto var="varName" address="email@address.com" ...]}
     * @param array $params
     * @param Smarty $smarty
     * @return void
     */
    public function smartyAssignMailto($params, &$smarty) {
        if (isset($params['var']) && isset($params['address'])) {
            $address = (string) $params['address'];
            $address_encode = '';
            for ($x = 0; $x < strlen($address); $x++) {
                if (preg_match('!\w!', $address[$x])) {
                    $address_encode .= '%' . bin2hex($address[$x]);
                } else {
                    $address_encode .= $address[$x];
                }
            }

            $text_encode = '';
            for ($x = 0; $x < strlen($address); $x++) {
                $text_encode .= '&#x' . bin2hex($address[$x]) . ';';
            }

            $mailto = "&#109;&#97;&#105;&#108;&#116;&#111;&#58;";
            $smarty->assign($params['var'], $mailto . $address_encode);
        }
    }

    /**
     * Smarty usage: {html_options_translate ...}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyHtmlOptionsTranslate($params, &$smarty) {
        if (isset($params['options'])) {
            if (isset($params['translateValues'])) {
                $newOptions = [];
                foreach ($params['options'] as $k => $v) {
                    $newOptions[__((string) $k)] = __((string) $v);
                }
                $params['options'] = $newOptions;
            } else {
                $params['options'] = array_map(['AppLocale', 'translate'], $params['options']);
            }
        }

        if (isset($params['output'])) {
            $params['output'] = array_map(['AppLocale', 'translate'], $params['output']);
        }

        if (isset($params['values']) && isset($params['translateValues'])) {
            $params['values'] = array_map(['AppLocale', 'translate'], $params['values']);
        }

        require_once($this->_get_plugin_filepath('function', 'html_options'));
        return smarty_function_html_options($params, $smarty);
    }

    /**
     * Iterator function for looping through objects extending the ItemIterator class.
     * Smarty usage: {iterate from="myIterator" item="item" [key="key"]}
     * @param array $params
     * @param mixed $content
     * @param Smarty $smarty
     * @param bool $repeat
     * @return string
     */
    public function smartyIterate($params, $content, &$smarty, &$repeat) {
        $iterator = $smarty->get_template_vars($params['from']);
        if (isset($params['key'])) {
            if (empty($content)) {
                $smarty->assign($params['key'], 1);
            } else {
                $currentVal = $smarty->get_template_vars($params['key']);
                $smarty->assign($params['key'], (int) $currentVal + 1);
            }
        }

        if (!is_object($iterator) || $iterator->eof()) {
            if (!$repeat) {
                return $content;
            }
            $repeat = false;
            return '';
        }

        $repeat = true;

        if (isset($params['key'])) {
            list($key, $value) = $iterator->nextWithKey();
            $smarty->assign($params['item'], $value);
            $smarty->assign($params['key'], $key);
        } else {
            $smarty->assign($params['item'], method_exists($iterator, 'next') ? $iterator->next() : null);
        }
        return $content;
    }

    /**
     * Smarty usage: {icon name="image name" alt="alternative name" url="url path"}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyIcon($params, &$smarty) {
        if (!empty($params) && isset($params['name'])) {
            $disabled = isset($params['disabled']) && !empty($params['disabled']);
            $path = $params['path'] ?? 'lib/pkp/templates/images/icons/';
            
            $iconHtml = '<img src="' . (string) $smarty->get_template_vars('baseUrl') . '/' . $path;
            $iconHtml .= $params['name'] . ($disabled ? '_disabled' : '') . '.gif" width="16" height="14" alt="';

            $iconHtml .= isset($params['alt']) ? $params['alt'] : __('icon.' . $params['name'] . '.alt');
            $iconHtml .= '" ';

            if (isset($params['onclick'])) {
                $iconHtml .= 'onclick="' . $params['onclick'] . '" ';
            }
            $iconHtml .= '/>';

            if (!$disabled && isset($params['url'])) {
                $iconHtml = '<a href="' . $params['url'] . '" class="icon">' . $iconHtml . '</a>';
            }
            return $iconHtml;
        }
        return '';
    }

    /**
     * Usage: {page_info iterator=$myIterator}
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyPageInfo($params, &$smarty) {
        $iterator = $params['iterator'];
        $itemsPerPage = $smarty->get_template_vars('itemsPerPage');
        
        if (!is_numeric($itemsPerPage)) {
            $itemsPerPage = 25;
        }

        $page = $iterator->getPage();
        $itemTotal = $iterator->getCount();

        $from = (($page - 1) * $itemsPerPage) + 1;
        $to = min($itemTotal, $page * $itemsPerPage);

        return __('navigation.items', [
            'from' => ($to === 0 ? 0 : $from),
            'to' => $to,
            'total' => $itemTotal
        ]);
    }

    /**
     * Flush the output buffer.
     * @param array $params
     * @param Smarty $smarty
     * @return void
     */
    public function smartyFlush($params, &$smarty) {
        if (method_exists($smarty, 'flush')) {
            $smarty->flush();
        } else {
            flush();
        }
    }

    /**
     * Flush the output buffer.
     * @return void
     */
    public function flush() {
        while (ob_get_level()) {
            ob_end_flush();
        }
        flush();
    }

    /**
     * Call hooks from a template.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyCallHook($params, &$smarty) {
        $hookName = $params['name'];
        unset($params['name']);
        $args = [&$params, &$smarty];
    
        $level = ob_get_level();
        ob_start();
    
        HookRegistry::dispatch($hookName, $args);
    
        while (ob_get_level() > $level + 1) {
            ob_end_flush();
        }
    
        $result = ob_get_contents();
        ob_end_clean();
        
        return (string) $result;
    }

    /**
     * Get debugging information and assign it to the template.
     * @param array $params
     * @param Smarty $smarty
     * @return void
     */
    public function smartyGetDebugInfo($params, &$smarty) {
        if (Config::getVar('debug', 'show_stats')) {
            $smarty->assign('enableDebugStats', true);

            $pkpProfiler = Registry::get('system.debug.profiler');
            if ($pkpProfiler !== null) {
                foreach ($pkpProfiler->getData() as $output => $value) {
                    $smarty->assign($output, $value);
                }
            }
            $smarty->assign('pqpCss', $this->request->getBaseUrl() . '/lib/pkp/lib/pqp/css/pQp.css');
            $smarty->assign('pqpTemplate', BASE_SYS_DIR . '/lib/pkp/lib/pqp/pqp.tpl');
        }
    }

    /**
     * Generate a URL into a PKPApp.
     * @param array $parameters
     * @param Smarty $smarty
     * @return string 
     */
    public function smartyUrl($parameters, &$smarty) {
        if (!isset($parameters['context'])) {
            $context = [];
            $contextList = Application::getContextList();
            foreach ($contextList as $contextName) {
                if (isset($parameters[$contextName])) {
                    $context[$contextName] = $parameters[$contextName];
                    unset($parameters[$contextName]);
                } else {
                    $context[$contextName] = null;
                }
            }
            $parameters['context'] = $context;
        }

        $paramList = ['params', 'router', 'context', 'page', 'component', 'op', 'path', 'anchor', 'escape'];
        foreach ($paramList as $parameter) {
            if (isset($parameters[$parameter])) {
                $$parameter = $parameters[$parameter];
                unset($parameters[$parameter]);
            } else {
                $$parameter = null;
            }
        }

        $extraParams = $parameters['params'] ?? [];
        $parameters = array_merge($parameters, (array) $extraParams);

        $router = $router ?? (($this->request->getRouter() instanceof PKPComponentRouter) ? ROUTE_COMPONENT : ROUTE_PAGE);
        $dispatcher = PKPApplication::getDispatcher();
        
        switch ($router) {
            case ROUTE_PAGE:
                $handler = $page;
                break;
            case ROUTE_COMPONENT:
                $handler = $component;
                break;
            default:
                assert(false);
                $handler = null;
        }

        return $dispatcher->url($this->request, $router, $context, $handler, $op, $path, $parameters, $anchor, !isset($escape) || $escape);
    }

    /**
     * Load a URL into a div via AJAX.
     * @param callable $progressFunction
     * @return void
     */
    public function setProgressFunction($progressFunction) {
        Registry::set('progressFunctionCallback', $progressFunction);
    }

    /**
     * Smarty usage: {call_progress_function}
     * @param array $params
     * @param Smarty $smarty
     * @return void
     */
    public function smartyCallProgressFunction($params, &$smarty) {
        $progressFunctionCallback = Registry::get('progressFunctionCallback');
        if ($progressFunctionCallback) {
            call_user_func($progressFunctionCallback);
        }
    }

    /**
     * Update the progress bar.
     * @param int $progress
     * @param int $total
     * @return void
     */
    public function updateProgressBar($progress, $total) {
        static $lastPercent = 0;
        $percent = (int) round($progress * 100 / $total);
        
        if ($lastPercent === null || $lastPercent !== $percent) {
            for ($i = 1; $i <= $percent - $lastPercent; $i++) {
                echo '<img src="' . $this->request->getBaseUrl() . '/templates/images/progbar.gif" width="5" height="15">';
            }
        }
        $lastPercent = $percent;

        $templateMgr = TemplateManager::getManager();
        $templateMgr->flush();
    }

    /**
     * Display page links.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyPageLinks($params, &$smarty) {
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
    
        $page = $iterator->getPage();
        $pageCount = $iterator->getPageCount();
        $paramName = $name . 'Page';
    
        if ($pageCount <= 1) {
            return '';
        }
    
        $request = Application::get()->getRequest();
        $onEitherSide = 2;
        $value = '<nav class="pagination" role="navigation" aria-label="Pagination">';
    
        if ($page > 1) {
            $params[$paramName] = $page - 1;
            $value .= '<a class="pagination-link pagination-arrow" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>&laquo; ' . __('common.previous') . '</a>';
        } else {
            $value .= '<span class="pagination-disabled pagination-arrow">&laquo; ' . __('common.previous') . '</span>';
        }
    
        $start = max(1, $page - $onEitherSide);
        $end = min($pageCount, $page + $onEitherSide);
        
        if ($start > 2) {
            $params[$paramName] = 1;
            $value .= '<a class="pagination-link" href="' . $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . '"' . $allExtra . '>1</a>';
            $value .= '<span class="pagination-ellipsis">...</span>';
        } elseif ($start === 2) {
            $params[$paramName] = 1;
            $value .= '<a class="pagination-link" href="' . $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . '"' . $allExtra . '>1</a>';
        }
    
        for ($i = $start; $i <= $end; $i++) {
            if ($i === $page) {
                $value .= "<span class=\"pagination-current\">$i</span>";
            } else {
                $params[$paramName] = $i;
                $value .= '<a class="pagination-link" href="' . $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . '"' . $allExtra . '>' . $i . '</a>';
            }
        }
    
        if ($end < $pageCount - 1) {
            $value .= '<span class="pagination-ellipsis">...</span>';
            $params[$paramName] = $pageCount;
            $value .= '<a class="pagination-link" href="' . $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . '"' . $allExtra . '>' . $pageCount . '</a>';
        } elseif ($end === $pageCount - 1) {
            $params[$paramName] = $pageCount;
            $value .= '<a class="pagination-link" href="' . $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . '"' . $allExtra . '>' . $pageCount . '</a>';
        }
        
        if ($page < $pageCount) {
            $params[$paramName] = $page + 1;
            $value .= '<a class="pagination-link pagination-arrow" href="' . $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . '"' . $allExtra . '>' . __('common.next') . ' &raquo;</a>';
        } else {
            $value .= '<span class="pagination-disabled pagination-arrow">' . __('common.next') . ' &raquo;</span>';
        }
    
        $value .= '</nav>';
        return $value;
    }

    /**
     * Convert Smarty arguments to an array.
     * @return array
     */
    public function smartyToArray() {
        return func_get_args();
    }

    /**
     * Concatenate Smarty arguments.
     * @return string
     */
    public function smartyConcat() {
        $args = func_get_args();
        return implode('', $args);
    }

    /**
     * Convert a date string to a Unix timestamp.
     * @param string $string
     * @return int
     */
    public function smartyStrtotime($string) {
        return strtotime((string) $string);
    }

    /**
     * Get a template variable's value.
     * @param string $name
     * @return mixed
     */
    public function smartyGetValue($name) {
        $templateMgr = TemplateManager::getManager();
        return $templateMgr->get_template_vars($name);
    }

    /**
     * Escape a string.
     * @param string $string
     * @param string $esc_type
     * @param string|null $char_set
     * @return string
     */
    public function smartyEscape($string, $esc_type = 'html', $char_set = null) {
        if ($char_set === null) {
            $char_set = LOCALE_ENCODING;
        }
        switch ($esc_type) {
            case 'jsparam':
                $value = smarty_modifier_escape($string, 'html', $char_set);
                return str_replace('&#039;', '\\\'', $value);
            default:
                return smarty_modifier_escape($string, $esc_type, $char_set);
        }
    }

    /**
     * Truncate a string to a certain length.
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param bool $break_words
     * @param bool $middle
     * @param bool $skip_tags
     * @return string
     */
    public function smartyTruncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false, $skip_tags = true) {
        if ($length === 0) {
            return '';
        }

        if (PKPString::strlen($string) > $length) {
            if ($skip_tags) {
                if ($middle) {
                    $tagsReverse = [];
                    $this->_removeTags($string, $tagsReverse, true, $length);
                }
                $tags = [];
                $string = $this->_removeTags($string, $tags, false, $length);
            }
            $length -= min($length, PKPString::strlen($etc));
            if (!$middle) {
                if (!$break_words) {
                    $string = PKPString::regexp_replace('/\s+?(\S+)?$/', '', PKPString::substr($string, 0, $length + 1));
                } else {
                    $string = PKPString::substr($string, 0, $length + 1);
                }
                if ($skip_tags) {
                    $string = $this->_reinsertTags($string, $tags);
                }
                return $this->_closeTags($string) . $etc;
            } else {
                $firstHalf = PKPString::substr($string, 0, (int) ($length / 2));
                $secondHalf = PKPString::substr($string, -(int) ($length / 2));

                if ($break_words) {
                    if ($skip_tags) {
                        $firstHalf = $this->_reinsertTags($firstHalf, $tags);
                        $secondHalf = $this->_reinsertTags($secondHalf, $tagsReverse, true);
                        return $this->_closeTags($firstHalf) . $etc . $this->_closeTags($secondHalf, true);
                    } else {
                        return $firstHalf . $etc . $secondHalf;
                    }
                } else {
                    for ($i = (int) ($length / 2); PKPString::substr($string, $i, 1) !== ' '; $i++) {
                        $firstHalf = PKPString::substr($string, 0, $i + 1);
                    }
                    for ($i = (int) ($length / 2); PKPString::substr($string, -$i, 1) !== ' '; $i++) {
                        $secondHalf = PKPString::substr($string, -$i - 1);
                    }

                    if ($skip_tags) {
                        $firstHalf = $this->_reinsertTags($firstHalf, $tags);
                        $secondHalf = $this->_reinsertTags($secondHalf, $tagsReverse, strlen($string));
                        return $this->_closeTags($firstHalf) . $etc . $this->_closeTags($secondHalf, true);
                    } else {
                        return $firstHalf . $etc . $secondHalf;
                    }
                }
            }
        } else {
            return $string;
        }
    }

    /**
     * Remove HTML tags from a string, storing them in an array for later reinsertion.
     * @param string $string
     * @param array $tags
     * @param bool $reverse
     * @param int $length
     * @return string
     */
    public function _removeTags($string, &$tags, $reverse = false, $length) {
        if ($reverse) {
            return $this->_removeTagsAuxReverse($string, 0, $tags, $length);
        } else {
            return $this->_removeTagsAux($string, 0, $tags, $length);
        }
    }

    /**
     * Auxiliary function to remove HTML tags from the start of a string.
     * @param string $string
     * @param int $loc
     * @param array $tags
     * @param int $length
     * @return string
     */
    public function _removeTagsAux($string, $loc, &$tags, $length) {
        if (strlen($string) > 0 && $length > 0) {
            $length--;
            if (PKPString::substr($string, 0, 1) === '<') {
                $closeBrack = PKPString::strpos($string, '>') + 1;
                if ($closeBrack) {
                    $tags[] = [PKPString::substr($string, 0, $closeBrack), $loc];
                    return $this->_removeTagsAux(PKPString::substr($string, $closeBrack), $loc + $closeBrack, $tags, $length);
                }
            }
            return PKPString::substr($string, 0, 1) . $this->_removeTagsAux(PKPString::substr($string, 1), $loc + 1, $tags, $length);
        }
        return '';
    }

    /**
     * Auxiliary function to remove HTML tags from the end of a string.
     * @param string $string
     * @param int $loc  
     * @param array $tags
     * @param int $length
     * @return string
     */
    public function _removeTagsAuxReverse($string, $loc, &$tags, $length) {
        $backLoc = PKPString::strlen($string) - 1;
        if ($backLoc >= 0 && $length > 0) {
            $length--;
            if (PKPString::substr($string, $backLoc, 1) === '>') {
                $tag = '>';
                $openBrack = 1;
                while (PKPString::substr($string, $backLoc - $openBrack, 1) !== '<') {
                    $tag = PKPString::substr($string, $backLoc - $openBrack, 1) . $tag;
                    $openBrack++;
                }
                $tag = '<' . $tag;
                $openBrack++;

                $tags[] = [$tag, $loc];
                return $this->_removeTagsAuxReverse(PKPString::substr($string, 0, -$openBrack), $loc + $openBrack, $tags, $length);
            }
            return $this->_removeTagsAuxReverse(PKPString::substr($string, 0, -1), $loc + 1, $tags, $length) . PKPString::substr($string, $backLoc, 1);
        }
        return '';
    }

    /**
     * Reinsert HTML tags into a string at their original locations.
     * @param string $string
     * @param array $tags
     * @param bool $reverse
     * @return string
     */
    public function _reinsertTags($string, &$tags, $reverse = false) {
        if (empty($tags)) {
            return $string;
        }

        for ($i = 0; $i < count($tags); $i++) {
            if ($tags[$i][1] < PKPString::strlen($string)) {
                if ($reverse) {
                    if ($tags[$i][1] === 0) {
                        $string = PKPString::substr_replace($string, $tags[$i][0], PKPString::strlen($string), 0);
                    } else {
                        $string = PKPString::substr_replace($string, $tags[$i][0], -$tags[$i][1], 0);
                    }
                } else {
                    $string = PKPString::substr_replace($string, $tags[$i][0], $tags[$i][1], 0);
                }
            }
        }
        return $string;
    }

    /**
     * Close any unclosed HTML tags in a string.
     * @param string $string
     * @param bool $open
     * @return string
     */
    public function _closeTags($string, $open = false) {
        PKPString::regexp_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $string, $result);
        $openedtags = $result[1] ?? [];

        PKPString::regexp_match_all("#</([a-z]+)>#iU", $string, $result);
        $closedtags = $result[1] ?? [];
        $len_opened = count($openedtags);
        $len_closed = count($closedtags);
        
        if (count($closedtags) === $len_opened) {
            return $string;
        }

        $openedtags = array_reverse($openedtags);
        $closedtags = array_reverse($closedtags);

        if ($open) {
            for ($i = 0; $i < $len_closed; $i++) {
                if (!in_array($closedtags[$i], $openedtags)) {
                    $string = '<' . $closedtags[$i] . '>' . $string;
                } else {
                    unset($openedtags[array_search($closedtags[$i], $openedtags)]);
                }
            }
            return $string;
        } else {
            for ($i = 0; $i < $len_opened; $i++) {
                if (!in_array($openedtags[$i], $closedtags)) {
                    $string .= '</' . $openedtags[$i] . '>';
                } else {
                    unset($closedtags[array_search($openedtags[$i], $closedtags)]);
                }
            }
            return $string;
        }
    }

    /**
     * Split a string into an array.
     * @param string $string
     * @param string $separator
     * @return array
     */
    public function smartyExplode($string, $separator) {
        return explode((string) $separator, (string) $string);
    }

    /**
     * Assign a value to a template variable.
     * @param mixed $value 
     * @param string $varName
     * @param bool $passThru
     * @return mixed
     */
    public function smartyAssign($value, $varName, $passThru = false) {
        if (isset($varName)) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign($varName, $value);
        }
        if ($passThru) {
            return $value;
        }
        return null;
    }

    /**
     * Custom Smarty function for creating heading links to sort tables by.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartySortHeading($params, &$smarty) {
        if (!empty($params)) {
            $sortParams = $this->request->getQueryArray();
            if (isset($params['sort'])) {
                $sortParams['sort'] = $params['sort'];
            }
            $sortDirection = $smarty->get_template_vars('sortDirection');
            $sort = $smarty->get_template_vars('sort');

            if (isset($params['sort']) && $params['sort'] === $sort) {
                if ($sortDirection === SORT_DIRECTION_ASC) {
                    $sortParams['sortDirection'] = SORT_DIRECTION_DESC;
                } else {
                    $sortParams['sortDirection'] = SORT_DIRECTION_ASC;
                }
            } else {
                $sortParams['sortDirection'] = SORT_DIRECTION_ASC;
            }

            $link = $this->request->url(null, null, null, $this->request->getRequestedArgs(), $sortParams, null, true);
            $text = isset($params['key']) ? __($params['key']) : '';
            $style = (isset($sort) && isset($params['sort']) && ($sort === $params['sort'])) ? ' style="font-weight:bold"' : '';

            return "<a href=\"$link\"$style>$text</a>";
        }
        return '';
    }

    /**
     * Custom Smarty function for creating heading links to sort search results by.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartySortSearch(array $params, object $smarty): string {
        if (empty($params)) {
            return '';
        }
    
        $sort = $smarty->get_template_vars('sort');
        $sortDirection = $smarty->get_template_vars('sortDirection');
    
        $isCurrentSort = ($params['sort'] ?? null) === $sort;
        $direction = ($isCurrentSort && $sortDirection === SORT_DIRECTION_ASC) ? SORT_DIRECTION_DESC : SORT_DIRECTION_ASC;
    
        $rawHeading = $params['sort'] ?? $sort ?? '';
        $escapedHeading = $this->smartyEscape((string) $rawHeading, 'javascript');
        $escapedDirection = $this->smartyEscape((string) $direction, 'javascript');
    
        $text = isset($params['key']) ? __($params['key']) : '';
        $style = $isCurrentSort ? ' style="font-weight:bold"' : '';
    
        return sprintf(
            '<a href="javascript:sortSearch(\'%s\', \'%s\')"%s>%s</a>',
            $escapedHeading,
            $escapedDirection,
            $style,
            (string) $text
        );
    }

    /**
     * Load a URL into a div via AJAX.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyLoadUrlInDiv($params, &$smarty) {
        if (!isset($params['url'])) {
            $smarty->trigger_error("url parameter is missing from load_url_in_div");
        }
        if (!isset($params['id'])) {
            $smarty->trigger_error("id parameter is missing from load_url_in_div");
        }
        
        $this->clear_assign(['inDivClass']);

        $this->assign('inDivUrl', $params['url']);
        $this->assign('inDivDivId', $params['id']);
        if (isset($params['class'])) {
            $this->assign('inDivClass', $params['class']);
        }

        if (isset($params['loadMessageId'])) {
            $loadMessageId = $params['loadMessageId'];
            unset($params['url'], $params['id'], $params['loadMessageId'], $params['class']);
            $this->assign('inDivLoadMessage', __($loadMessageId, $params));
        } else {
            $this->assign('inDivLoadMessage', $this->fetch('common/loadingContainer.tpl'));
        }

        return $this->fetch('common/urlInDiv.tpl');
    }

    /**
     * Generate a modal dialog.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyModal($params, &$smarty) {
        if (!isset($params['url'])) {
            $smarty->trigger_error("URL parameter is missing from modal");
        } elseif (!isset($params['actOnId'])) {
            $smarty->trigger_error("actOnId parameter is missing from modal");
        } elseif (!isset($params['button'])) {
            $smarty->trigger_error("Button parameter is missing from modal");
        } else {
            $url = $params['url'];
            $actOnType = $params['actOnType'] ?? '';
            $actOnId = $params['actOnId'];
            $button = $params['button'];
            $dialogTitle = $params['dialogTitle'] ?? false;
        }

        $submitButton = __('common.ok');
        $cancelButton = __('common.cancel');

        $varsToEscape = ['submitButton', 'cancelButton', 'url', 'actOnType', 'actOnId', 'button'];
        foreach ($varsToEscape as $varName) {
            $$varName = $this->smartyEscape($$varName, 'javascript');
        }

        $dialogTitleStr = isset($dialogTitle) ? ", '$dialogTitle'" : "";
        return "<script type='text/javascript'>\n\t<!--\n\tvar localizedButtons = ['$submitButton', '$cancelButton'];\n\tmodal('$url', '$actOnType', '$actOnId', localizedButtons, '$button'$dialogTitleStr);\n\t// -->\n</script>\n";
    }

    /**
     * Generate a confirmation dialog.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyConfirm($params, &$smarty) {
        if (!isset($params['button'])) {
            $smarty->trigger_error("Button parameter is missing from confirm");
        } else {
            $button = $params['button'];
        }

        $url = $params['url'] ?? null;
        $actOnType = $params['actOnType'] ?? '';
        $actOnId = $params['actOnId'] ?? '';

        $showDialog = false;
        $dialogText = '';
        if (isset($params['dialogText'])) {
            $showDialog = true;
            $dialogText = (isset($params['translate']) && $params['translate'] === false) ? $params['dialogText'] : __($params['dialogText']);
        }

        if (!$showDialog && !$url) {
            $smarty->trigger_error("Both URL and dialogText parameters are missing from confirm");
        }

        $submitButton = __('common.ok');
        $cancelButton = __('common.cancel');

        $varsToEscape = ['button', 'url', 'actOnType', 'actOnId', 'dialogText', 'submitButton', 'cancelButton'];
        foreach ($varsToEscape as $varName) {
            $$varName = $this->smartyEscape($$varName, 'javascript');
        }

        if ($showDialog) {
            return "<script type='text/javascript'>\n\t<!--\n\tvar localizedButtons = ['$submitButton', '$cancelButton'];\n\tmodalConfirm('$url', '$actOnType', '$actOnId', '$dialogText', localizedButtons, '$button');\n\t// -->\n</script>\n";
        } else {
            return "<script type='text/javascript'>\n\t<!--\n\tbuttonPost('$url', '$button');\n\t// -->\n</script>";
        }
    }

    /**
     * Generate a modal title bar.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyModalTitle($params, &$smarty) {
        if (!isset($params['id'])) {
            $smarty->trigger_error("Selector missing for title bar initialization");
        } else {
            $id = $params['id'];
        }

        $iconClass = $params['iconClass'] ?? '';
        $iconHtml = $iconClass !== '' ? "<span class='icon $iconClass' />" : "";

        if (isset($params['key'])) {
            $keyHtml = "<span class='text'>" . __($params['key']) . "</span>";
        } elseif (isset($params['keyTranslated'])) {
            $keyHtml = "<span class='text'>" . $params['keyTranslated'] . "</span>";
        } else {
            $keyHtml = "";
        }

        $canCloseHtml = isset($params['canClose']) ? "<a class='close ui-corner-all' href='#'><span class='ui-icon ui-icon-closethick'>close</span></a>" : "";

        return "<script type='text/javascript'>\n\t<!--\n\t\$(function() {\n\t\t\$('$id').last().parent().prev('.ui-dialog-titlebar').remove();\n\t\t\$('a.close').live('click', function() { \$(this).parent().parent().dialog('close'); return false; });\n\t\treturn false;\n\t});\n\t// -->\n</script>\n<div class='pkp_controllers_modal_titleBar'>" .
                $iconHtml . $keyHtml . $canCloseHtml . "<span style='clear:both' />\n</div>";
    }

    /**
     * Mengembalikan tag &lt;?xml menjadi <?xml
     * @param mixed $output
     * @param Smarty $smarty
     * @return mixed
     */
    public function fixXmlOutput($output, $smarty) {
        if (!is_string($output)) {
            return $output;
        }
        return preg_replace('/&lt;\?xml(.*?)\?&gt;/is', '<?xml$1?>', $output);
    }
    
    /**
     * Custom Smarty Modifier: Mask Email ***
     * @param string $email
     * @return string
     */
    public function smartyMaskEmail($email) {
        if (empty($email) || strpos($email, '@') === false) {
            return (string) $email;
        }

        list($local, $domain) = explode('@', $email, 2);
        $separatorPos = strpos($local, '.');
        
        if ($separatorPos !== false) {
            $visible = substr($local, 0, $separatorPos + 1); 
            $hiddenPart = substr($local, $separatorPos + 1);
            $masked = $visible . str_repeat('*', strlen($hiddenPart));
        } else {
            $len = strlen($local);
            if ($len <= 3) {
                $visible = substr($local, 0, 1);
                $masked = $visible . str_repeat('*', $len - 1);
            } else {
                $visible = substr($local, 0, 3);
                $masked = $visible . str_repeat('*', $len - 3);
            }
        }

        return $masked . '@' . $domain;
    }
    
    /**
     * Custom Smarty Modifier: Mask Email: dot at
     * @param string|null $email
     * @return string
     */
    public function smartyDotatEmail(?string $email): string {
        if (empty($email)) {
            return '';
        }
        return str_replace(['@', '.'], [' [at] ', ' [dot] '], (string) $email);
    }
    
    /**
     * Custom Smarty Modifier: Mask Phone: ***
     * @param string|null $phone
     * @return string
     */
    public function smartyMaskPhone(?string $phone): string {
        if (empty($phone)) {
            return '';
        }

        $len = strlen($phone);
        if ($len < 6) {
            return (string) $phone;
        }

        $visibleStart = 4;
        $visibleEnd = 3;

        if ($len <= 8) {
            $visibleStart = 2;
            $visibleEnd = 2;
        }

        $start = substr($phone, 0, $visibleStart);
        $end = substr($phone, -$visibleEnd);
        $maskedPart = str_repeat('*', $len - ($visibleStart + $visibleEnd));

        return $start . $maskedPart . $end;
    }
    
    /**
     * Helper function to sanitize profile image data.
     * @param mixed $imageData
     * @return array|null
     */
    public function _sanitizeProfileImage($imageData) {
        if (!is_array($imageData)) {
            return null;
        }
                
        $safeData = [
            'uploadName' => isset($imageData['uploadName']) ? preg_replace('/[^a-zA-Z0-9_\-\.]/', '', (string) $imageData['uploadName']) : null,
            'width' => isset($imageData['width']) ? (int) $imageData['width'] : 0,
            'height' => isset($imageData['height']) ? (int) $imageData['height'] : 0
        ];
                
        return (!empty($safeData['uploadName'])) ? $safeData : null;
    }
    
    /**
     * Custom Smarty Modifier: Menghitung waktu berlalu (Time Ago)
     * @param mixed $datetime
     * @return string
     */
    public function smartyTimeAgo($datetime) {
        if (is_array($datetime)) {
            $datetime = reset($datetime); 
        }
        if (is_object($datetime) && method_exists($datetime, 'format')) {
            $datetime = $datetime->format('Y-m-d H:i:s');
        }
        $datetime = (string) $datetime;

        if (empty($datetime)) {
            return ''; 
        }
                
        $time = time() - strtotime($datetime);
                
        if ($time < 60) {
            return PKPLocale::translate('common.timeAgo.now');
        } elseif ($time < 3600) {
            $minutes = (int) round($time / 60);
            return PKPLocale::translate('common.timeAgo.minutesAgo', ['count' => $minutes]);
        } elseif ($time < 86400) {
            $hours = (int) round($time / 3600);
            return PKPLocale::translate('common.timeAgo.hoursAgo', ['count' => $hours]);
        } elseif ($time < 604800) { 
            $days = (int) round($time / 86400);
            return PKPLocale::translate('common.timeAgo.daysAgo', ['count' => $days]);
        } elseif ($time < 2592000) { 
            $weeks = (int) round($time / 604800);
            return PKPLocale::translate('common.timeAgo.weeksAgo', ['count' => $weeks]);
        } elseif ($time < 31536000) { 
            $months = (int) round($time / 2592000);
            return PKPLocale::translate('common.timeAgo.monthsAgo', ['count' => $months]);
        } else {
            $years = (int) round($time / 31536000);
            return PKPLocale::translate('common.timeAgo.yearsAgo', ['count' => $years]);
        }
    }
    
    /**
     * Mesin Utama (Private): Format angka besar bergaya statistik global
     * @param mixed $number
     * @param int $precision
     * @return array
     */
    private function calculateMetricData($number, $precision = 1) {
        if (!is_numeric($number)) {
            return ['number' => $number, 'suffix' => ''];
        }

        $val = (float) $number;
        $suffix = '';

        if ($number >= 1000000000) {
            $val = round($number / 1000000000, $precision);
            $suffix = PKPLocale::translate('common.numeric.billionWord');
        } elseif ($number >= 1000000) {
            $val = round($number / 1000000, $precision);
            $suffix = PKPLocale::translate('common.numeric.millionWord');
        } elseif ($number >= 1000) {
            $val = round($number / 1000, $precision);
            $suffix = PKPLocale::translate('common.numeric.thousandWord');
        }
        
        if ($val != $number) {
            $val = (floor($val) == $val ? floor($val) : $val);
        }

        return [
            'number' => $val,
            'suffix' => $suffix
        ];
    }

    /**
     * Modifier Smarty 1: Angka Utama
     * @param mixed $number
     * @return mixed
     */
    public function smartyMetricNumber($number) {
        $data = $this->calculateMetricData($number);
        return $data['number'];
    }

    /**
     * Modifier Smarty 2: Kata Imbuhan (Suffix)
     * @param mixed $number
     * @return string
     */
    public function smartyMetricSuffix($number) {
        $data = $this->calculateMetricData($number);
        return (string) $data['suffix'];
    }
    
    /**
     * Custom Smarty Modifier: Format Ukuran File (Bytes ke KB / MB)
     * @param mixed $bytes
     * @param int $precision
     * @return string
     */
    public function smartyFileSize($bytes, $precision = 1) {
        if (!is_numeric($bytes)) {
            return (string) $bytes;
        }

        if ($bytes >= 1048576) {
            $val = round($bytes / 1048576, $precision);
            return str_replace('.', ',', (string) $val) . ' MB';
        } elseif ($bytes >= 1024) {
            $val = round($bytes / 1024, 0);
            return number_format((int) $val, 0, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Fungsi Helper (Private): Menghasilkan array berisi angka dan suffix
     * @param mixed $number 
     * @param int $precision 
     * @return array
     */
    private function getShortNumberData(mixed $number, int $precision = 1): array {
        if (!is_numeric($number)) {
            return ['number' => $number, 'suffix' => ''];
        }

        $number = (float) $number;

        if ($number >= 1000000000) {
            $val = round($number / 1000000000, $precision);
            $suffix = 'B';
        } elseif ($number >= 1000000) {
            $val = round($number / 1000000, $precision);
            $suffix = 'M';
        } elseif ($number >= 1000) {
            $val = round($number / 1000, $precision);
            $suffix = 'K';
        } else {
            $val = round($number, $precision);
            $suffix = '';
        }

        $formattedVal = str_replace('.', ',', (string) $val);
        return ['number' => $formattedVal, 'suffix' => $suffix];
    }

    /**
     * Modifier Smarty 1: Hanya Angka Utama (Terpisah)
     * @param mixed $number
     * @param int $precision
     * @return string
     */
    public function smartyShortMetricNumber(mixed $number, int $precision = 1): string {
        $data = $this->getShortNumberData($number, $precision);
        return (string) $data['number'];
    }

    /**
     * Modifier Smarty 2: Hanya Suffix / Imbuhan (Terpisah)
     * @param mixed $number
     * @param int $precision
     * @return string
     */
    public function smartyShortMetricSuffix(mixed $number, int $precision = 1): string {
        $data = $this->getShortNumberData($number, $precision);
        return (string) $data['suffix'];
    }

    /**
     * Modifier Smarty 3: Gabungan Angka & Suffix (Sekaligus)
     * @param mixed $number
     * @param int $precision
     * @return string
     */
    public function smartyShortMetric(mixed $number, int $precision = 1): string {
        $data = $this->getShortNumberData($number, $precision);
        return $data['number'] . $data['suffix'];
    }
    
    /**
     * FUNGSI SMARTY KUSTOM (WIZDAM PLUGIN)
     * Membangun URL "native" kita secara "smooth" TANPA TANDA AMPERSAND.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyNativeUrl($params, $smarty) {
        $request = $this->request;
        $journal = $request->getJournal();
        $baseUrl = $request->getBaseUrl();
        $journalPath = $journal ? $journal->getPath() : '';
        
        $page = $params['page'] ?? '';
        $url = $baseUrl . '/' . $journalPath;

        switch ($page) {
            case 'archive':
                $url .= '/volumes';
                break;
            
            case 'volume':
                if (isset($params['volume']) && $params['volume']) {
                    $url .= '/volumes/' . $params['volume'];
                } else {
                    $url .= '/volumes';
                }
                break;

            case 'issue':
                $volumeId = $params['volume'] ?? '0'; 
                $slug = $params['slug'] ?? '';
                $url .= '/volumes/' . $volumeId . '/issue/' . $slug;
                break;
        }
        
        if (isset($params['showToc']) && $params['showToc']) {
            $url .= '/showToc';
        }

        return $url;
    }
    
}
?>