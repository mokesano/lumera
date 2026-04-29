<?php
declare(strict_types=1);

/**
 * @defgroup template
 */

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
 *
 * [WIZDAM EDITION] FULL REFACTOR: PHP 8.1+ Strict Types, Reference Fixes, Native URL Routing
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
        // [Wizdam] Fetch Singleton Request (Pass by Value)
        if (!isset($request)) {
            $this->request = Registry::get('request');
        } else {
            $this->request = $request;
        }
        
        // [Modern PHP] assert expects a boolean or assertion string
        assert($this->request instanceof PKPRequest);

        // [Wizdam] Fetch Router
        $router = $this->request->getRouter();

        parent::__construct();

        // Set up Smarty configuration
        $baseDir = Core::getBaseDir();
        $cachePath = CacheManager::getFileCachePath();

        // Set the default template dir (app's template dir)
        $this->app_template_dir = $baseDir . DIRECTORY_SEPARATOR . 'templates';
        // Set fallback template dir (core's template dir)
        $this->core_template_dir = $baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'templates';

        $this->template_dir = [$this->app_template_dir, $this->core_template_dir];
        $this->compile_dir = $cachePath . DIRECTORY_SEPARATOR . 't_compile';
        $this->config_dir = $cachePath . DIRECTORY_SEPARATOR . 't_config';
        $this->cache_dir = $cachePath . DIRECTORY_SEPARATOR . 't_cache';

        // [Wizdam] Prevent PHP execution in templates &lt;?xml → <?xml 
        // without public function fixXmlOutput but must doit: clear cache
        //$this->php_handling = defined('SMARTY_PHP_PASSTHRU') ? SMARTY_PHP_PASSTHRU : 0;
        
        // [Wizdam] Prevent PHP execution in templates <?xml → &lt;?xml → <?xml
        // 1. Kode mengakibatkan error sintaks pada rss feed <?xml → &lt;?xml
        $this->php_handling = defined('SMARTY_PHP_QUOTE') ? SMARTY_PHP_QUOTE : 1;
        // 2. Gunakan fungsi fixXmlOutput sebagai Output Filter → <?xml
        if (method_exists($this, 'registerFilter')) {
            $this->registerFilter('output', array($this, 'fixXmlOutput'));
        } else {
            $this->register_outputfilter(array($this, 'fixXmlOutput'));
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

        if (($localeStyleSheet = AppLocale::getLocaleStyleSheet($locale)) != null) {
            $this->addStyleSheet($this->request->getBaseUrl() . '/' . $localeStyleSheet);
        }

        // [Wizdam Fix] Fetch Application by Value (No &)
        $application = PKPApplication::getApplication();
        
        // [Wizdam Fix] Page Title Logic
        $siteTitle = $application->getNameKey();
        $this->assign('pageTitle', $siteTitle);
        
        $this->assign('exposedConstants', $application->getExposedConstants());
        $this->assign('jsLocaleKeys', $application->getJSLocaleKeys());
        
        // Register custom functions
        // [Wizdam] Register Custom Email Masking Modifier
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
        
        // Daftarkan fungsi agar Smarty mengenali tag {form_language_chooser}
        $this->register_function('form_language_chooser', array($this, 'smartyFormLanguageChooser'));
        
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

            $application = PKPApplication::getApplication();
            $currentVersion = $application->getCurrentVersion();
            $this->assign('currentVersionString', $currentVersion->getVersionString());

            $this->assign('itemsPerPage', Config::getVar('interface', 'items_per_page'));
            $this->assign('numPageLinks', Config::getVar('interface', 'page_links'));
        }

        $this->assign('stylesheets', $this->styleSheets); // Reference not needed for arrays in PHP 7+

        $this->initialized = false;
    }
    
    /**
     * [SHIM] Backward Compatibility
     * Jembatan Emas untuk plugin lama yang memanggil new PKPTemplateManager()
     * @param PKPRequest|null $request
     */
    public function PKPTemplateManager($request = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Deprecated constructor called: PKPTemplateManager(). Please use new PKPTemplateManager().", E_USER_DEPRECATED);
        }
        self::__construct($request);
    }

    /**
     * Override the Smarty {include ...} function to allow hooks to be called.
     * @param $params array of parameters passed to the {include} function
     * @return string the rendered template content
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
     */
    public function setCacheability(string $cacheability = CACHEABILITY_PUBLIC) {
        $this->cacheability = $cacheability;
    }

    /**
     * Initialize the template.
     */
    public function initialize() {
        // This code cannot be called in the constructor because of
        // reference problems, i.e. callers that need getManager fail.

        // Load enabled block plugins.
        $plugins = PluginRegistry::loadCategory('blocks', true);

        if (!defined('SESSION_DISABLE_INIT')) {
            
            // --- MODIFIKASI DIMULAI (Injeksi $membershipGroups) ---
            $journal = $this->request->getJournal();
            $displayGroups = []; 

            if ($journal) {
                // Panggil DAO yang bertanggung jawab (Model)
                $groupDao = DAORegistry::getDAO('GroupDAO');
                
                // Ambil data yang sudah diproses oleh DAO
                $displayGroups = $groupDao->getBoardGroupsForDisplay($journal->getId());
            }
                
            // Assign ke Smarty
            $this->assign('membershipGroups', $displayGroups);
            // --- MODIFIKASI SELESAI ---
            
            // Kode asli berlanjut di bawah ini:
            $user = $this->request->getUser();
            $hasSystemNotifications = false;
            
            // Ambil session dari Request dan assign ke Smarty
            $session = $this->request->getSession();
            $this->assign('userSession', $session);
                        
            if ($user) {
                // --- AWAL MODIFIKASI GABUNGAN ---

                // 1. Assign variabel login dasar (dibutuhkan oleh template)
                $this->assign('isUserLoggedIn', true); 
                                
                // 2. Kode Asli Anda (Username & Help)
                $this->assign('loggedInUsername', $user->getUserName());
                $this->assign('initialHelpState', (int) $user->getInlineHelp());

                // 3. Kode Asli Anda (Notifikasi)
                $notificationDao = DAORegistry::getDAO('NotificationDAO');
                $notifications = $notificationDao->getByUserId($user->getId(), NOTIFICATION_LEVEL_TRIVIAL);
                if ($notifications->getCount() > 0) {
                    $hasSystemNotifications = true;
                }

                // 4. Kode Baru Anda (dari blok {php})
                $dateValidated = $user->getDateValidated();
                $dateRegistered = $user->getDateRegistered();
                
                // Perubahan Data lastLogin sebelumnya v1
                // $dateLastLogin = $user->getDateLastLogin();
                
                // Ambil 'last login' (previous_login) permanen dari database
                $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
                $dateLastLogin = $userSettingsDao->getSetting($user->getId(), 'previous_login');
                
                // Waktu login saat ini murni dari core OJS
                $dateCurrentLogin = $user->getDateLastLogin();
                                
                // Siapkan status verifikasi
                $verificationStatus = $dateValidated ? __('user.profile.verified') : __('user.profile.unverified');
                                
                // Buat satu array $userData yang lengkap
                $userData = [
                    'id' => $user->getId(),
                    'firstName' => htmlspecialchars($user->getFirstName() ?? '', ENT_QUOTES, 'UTF-8'),
                    'middleName' => htmlspecialchars($user->getMiddleName() ?? '', ENT_QUOTES, 'UTF-8'),
                    'lastName' => htmlspecialchars($user->getLastName() ?? '', ENT_QUOTES, 'UTF-8'),
                    'suffix' => htmlspecialchars($user->getSuffix() ?? '', ENT_QUOTES, 'UTF-8'),
                    'profileImage' => $this->_sanitizeProfileImage($user->getSetting('profileImage')), // Panggil helper
                    'gender' => $user->getGender(),
                    'salutation' => htmlspecialchars($user->getSalutation() ?? '', ENT_QUOTES, 'UTF-8'),
                    'email' => filter_var($user->getEmail(), FILTER_SANITIZE_EMAIL),
                                        
                    // Info Verifikasi
                    'verification_status' => $verificationStatus,
                    'is_verified' => ($dateValidated ? true : false),
                    'validated' => $dateValidated,
                    'registered' => $dateRegistered,
                    'last_login' => $dateLastLogin,
                    'current_login' => $dateCurrentLogin
                ];
                                
                // Assign HANYA $userData ke Smarty
                $this->assign('userData', $userData);
                                
                // --- AKHIR MODIFIKASI GABUNGAN ---

            } else {
                // --- MODIFIKASI UNTUK USER ANONIM ---
                // Pastikan template tahu user TIDAK login
                $this->assign('isUserLoggedIn', false);
                $this->assign('userData', null);
                // --- AKHIR MODIFIKASI ANONIM ---
            }

            $this->assign('hasSystemNotifications', $hasSystemNotifications);
        }
        
        // REGISTRASI CUSTOM SMARTY MODIFIER
        $this->register_modifier('dotat_mail', [$this, 'smartyDotatEmail']);
        $this->register_modifier('mask_phone', [$this, 'smartyMaskPhone']);
        $this->register_modifier('file_size', [$this, 'smartyFileSize']);
        $this->register_modifier('time_ago', [$this, 'smartyTimeAgo']);
        
        // Mendaftarkan modifier untuk angka e.g. 100K
        $this->register_modifier('short_number', [$this, 'smartyShortMetric']);
        $this->register_modifier('ShortNumber', [$this, 'smartyShortMetricNumber']);
        $this->register_modifier('ShortSuffix', [$this, 'smartyShortMetricSuffix']);
        
        // Mendaftarkan modifier untuk angka e.g. 1 million
        $this->register_modifier('metric_number', [$this, 'smartyMetricNumber']);
        // Mendaftarkan modifier untuk kata imbuhannya
        $this->register_modifier('metric_suffix', [$this, 'smartyMetricSuffix']);
        
        // [WIZDAM CSRF] - AUTOMATIC GLOBAL TOKEN INJECTION
        import('lib.pkp.classes.validation.ValidatorCSRF');
        
        // Cukup gunakan konteks 'global'. Tidak perlu pusing dengan $op GET/POST yang berbeda.
        $this->assign(ValidatorCSRF::FIELD_NAME, ValidatorCSRF::generateToken('global'));
        // [WIZDAM CSRF] -- AUTOMATIC GLOBAL TOKEN INJECTION

        $this->initialized = true;
    }

    /**
     * Add a page-specific style sheet.
     * @param string $url the URL to the style sheet
     */
    public function addStyleSheet($url) {
        array_push($this->styleSheets, $url);
    }

    /**
     * Add a page-specific script.
     * @param string $url the URL to be included
     */
    public function addJavaScript($url) {
        array_push($this->javaScripts, $url);
    }

    /**
     * Fetch a rendered template.
     * @param string $resource_name the name of the template resource
     * @param string|null $cache_id optional cache ID
     * @param string|null $compile_id optional compile ID
     * @param boolean $display optional whether to display the output instead of returning it
     * @return string the rendered template
     * @see Smarty::fetch()
     */
    public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
        if (!$this->initialized) {
            $this->initialize();
        }

        // Add additional java script URLs
        if (!empty($this->javaScripts)) {
            $baseUrl = $this->get_template_vars('baseUrl');
            $scriptOpen = '    <script type="text/javascript" src="';
            $scriptClose = '"></script>';
            $javaScript = '';
            foreach ($this->javaScripts as $script) {
                $javaScript .= $scriptOpen . $baseUrl . '/' . $script . $scriptClose . "\n";
            }

            $additionalHeadData = $this->get_template_vars('additionalHeadData');
            $this->assign('additionalHeadData', $additionalHeadData."\n".$javaScript);

            // Empty the java scripts array so that we don't include
            // the same scripts twice in case the template manager is called again.
            $this->javaScripts = [];
        }
        
        // [WIZDAM FIX] str_contains() hanya PHP 8.0+ — gunakan strpos() untuk PHP 7.4 kompatibilitas
        set_error_handler(function($errno, $errstr) {
            if ($errno === E_WARNING && strpos($errstr, 'Undefined array key') !== false) {
                return true;
            }
            return false;
        });
        
        return parent::fetch($resource_name, $cache_id, $compile_id, $display);
        
        restore_error_handler();
    
        return $result;
    }

    /**
     * Returns the template results as a JSON message.
     * @param string $template
     * @param boolean $status
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
     * Mengakses properti internal Smarty 2.x $this->_tpl_vars yang
     * menyimpan semua variabel hasil assign().
     * Kompatibel dengan signature getTemplateVars() di Smarty 3.x/4.x.
     *
     * @param string|null $varName Nama variabel. Null = kembalikan semua.
     * @return mixed Nilai variabel, null jika tidak ada, 
     * atau array semua variabel jika $varName null.
     */
    public function getTemplateVars($varName = null) {
        if ($varName === null) {
            // Kembalikan seluruh array variabel template
            return $this->_tpl_vars;
        }

        // Kembalikan nilai spesifik, atau null jika key tidak ada
        return isset($this->_tpl_vars[$varName]) ? $this->_tpl_vars[$varName] : null;
    }

    /**
     * Alias eksplisit untuk mengambil satu variabel template.
     * Lebih deskriptif dari getTemplateVars($key) ketika hanya butuh satu nilai
     *
     * @param string $varName Nama variabel template.
     * @param mixed $default Nilai default jika variabel tidak ada (opsional).
     * @return mixed
     */
    public function getSingleTemplateVar($varName, $default = null) {
        if (isset($this->_tpl_vars[$varName])) {
            return $this->_tpl_vars[$varName];
        }
        return $default;
    }

    /**
     * Display the template.
     * @param string $template the name of the template resource
     * @param string|null $sendContentType optional content type to send in the header (defaults to text/html)
     * @param string|null $hookName optional hook name to allow overriding the display (defaults to TemplateManager::display)
     * @param boolean $display optional whether to display the output instead of returning it
     * @return string the rendered template if $display is false, otherwise null
     */
    public function display($template, $sendContentType = null, $hookName = null, $display = true) {
        // Special error suppression for login template
        if (strpos($template, 'login.tpl') !== false) {
            $oldErrorReporting = error_reporting();
            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
        }
        
        if (is_null($sendContentType)) {
            $sendContentType = 'text/html';
        }
        if (is_null($hookName)) {
            $hookName = 'TemplateManager::display';
        }

        $charset = Config::getVar('i18n', 'client_charset');

        $output = null;
        // HookRegistry uses pass-by-reference for array elements
        if (!HookRegistry::dispatch($hookName, [&$this, &$template, &$sendContentType, &$charset, &$output])) {
            if ($hookName == 'TemplateManager::display') {
                header('Content-Type: ' . $sendContentType . '; charset=' . $charset);
                header('Cache-Control: ' . $this->cacheability);
            }

            return $this->fetch($template, null, null, $display);
        } else {
            echo $output;
        }
    }

    /**
     * Display templates from Smarty and allow hook overrides
     * Smarty usage: {display_template template="name.tpl" hookname="My::Hook::Name"}
     * @param $params array of parameters passed to the {display_template} function
     * @param $smarty Smarty
     * @return string the rendered template if not overridden by hook, otherwise null
     */
    public function smartyDisplayTemplate($params, $smarty) {
        $templateMgr = TemplateManager::getManager();
        if (isset($params['template'])) {
            $templateMgr->display($params['template'], "", $params['hookname']);
        }
    }

    /**
     * Clear template compile and cache directories.
     * @param $smarty Smarty
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
        if(!$this->fbv) {
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
	 * @param $template string
	 * @param $templateSource string reference
	 * @param $smarty Smarty
	 * @return boolean
	 */
    public function smartyResourceCoreGetTemplate($template, &$templateSource, &$smarty) {
        $templateSource = file_get_contents($this->core_template_dir . DIRECTORY_SEPARATOR . $template);
        return ($templateSource !== false);
    }

	/**
	 * Resource function to get the timestamp of a "core" (pkp-lib)
	 * template.
	 * @param $template string
	 * @param $templateTimestamp int reference
	 * @return boolean
	 */
    public function smartyResourceCoreGetTimestamp($template, &$templateTimestamp, &$smarty) {
        $templateSource = $this->core_template_dir . DIRECTORY_SEPARATOR . $template;
        if (!file_exists($templateSource)) return false;
        $templateTimestamp = filemtime($templateSource);
        return true;
    }

	/**
	 * Resource function to determine whether a "core" (pkp-lib) template
	 * is secure.
     * @param $template string
     * @param $smarty Smarty
	 * @return boolean
	 */
    public function smartyResourceCoreGetSecure($template, &$smarty) {
        return true;
    }

	/**
	 * Resource function to determine whether a "core" (pkp-lib) template
	 * is trusted.
     * @param $template string
     * @param $smarty Smarty
     * @return boolean
	 */
    public function smartyResourceCoreGetTrusted($template, &$smarty) {
        $trustedDirs = [
            realpath($this->core_template_dir), // lib/pkp/templates
            realpath($this->app_template_dir),  // root/templates
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
     * @param $params array of parameters passed to the {form_language_chooser} function
     * @param $smarty Smarty
     * @return string the rendered form language chooser HTML, or an empty string if there is only one locale available
     */
    public function smartyFormLanguageChooser($params, &$smarty) {
        $form = isset($params['form']) ? $params['form'] : null;
        $url = isset($params['url']) ? $params['url'] : null;
        
        // Pastikan TemplateManager memiliki data locales
        $formLocales = $smarty->get_template_vars('formLocales');
        if (!$formLocales || count($formLocales) <= 1) return '';
    
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
     * @param $smarty Smarty
	 * @return string the localized string, including any parameter substitutions
     */
    public function smartyTranslate($params, &$smarty) {
        if (isset($params) && !empty($params)) {
            if (!isset($params['key'])) return __('');

            $key = $params['key'];
            unset($params['key']);
            if (isset($params['params']) && is_array($params['params'])) {
                $paramsArray = $params['params'];
                unset($params['params']);
                $params = array_merge($params, $paramsArray);
            }
            return __($key, $params);
        }
    }

    /**
     * Smarty usage: {null_link_action id="linkId" key="localization.key.name" image="imageClassName"}
     * @return string the rendered link action
     * @param $smarty Smarty
     */
    public function smartyNullLinkAction($params, &$smarty) {
        assert(isset($params['id']));

        $id = $params['id'];
        $key = isset($params['key'])?$params['key']:null;
        $hoverTitle = isset($params['hoverTitle'])?true:false;
        $image = isset($params['image'])?$params['image']:null;
        $translate = isset($params['translate'])?false:true;

        import('lib.pkp.classes.linkAction.request.NullAction');
        $key = $translate ? __($key) : $key;
        
        // Smarty assignment
        $this->assign('action', new LinkAction(
            $id, new NullAction(), $key, $image
        ));

        $this->assign('hoverTitle', $hoverTitle);
        return $this->fetch('linkAction/linkAction.tpl');
    }

    /**
     * Smarty usage: {assign_mailto var="varName" address="email@address.com" ...]}
     * @param $smarty Smarty
     * @return void
     */
    public function smartyAssignMailto($params, &$smarty) {
        if (isset($params['var']) && isset($params['address'])) {
            $address = $params['address'];
            $address_encode = '';
            for ($x=0; $x < strlen($address); $x++) {
                if(preg_match('!\w!',$address[$x])) {
                    $address_encode .= '%' . bin2hex($address[$x]);
                } else {
                    $address_encode .= $address[$x];
                }
            }

			$text_encode = '';
			for ($x=0; $x < strlen($text); $x++) {
				$text_encode .= '&#x' . bin2hex($text[$x]).';';
			}

            $mailto = "&#109;&#97;&#105;&#108;&#116;&#111;&#58;";
            $smarty->assign($params['var'], $mailto . $address_encode);
        }
    }

    /**
     * Smarty usage: {html_options_translate ...}
     * @param $smarty Smarty
     * @return string the rendered HTML select options
     */
    public function smartyHtmlOptionsTranslate($params, &$smarty) {
        if (isset($params['options'])) {
            if (isset($params['translateValues'])) {
                // Translate values AND output
                $newOptions = [];
                foreach ($params['options'] as $k => $v) {
                    $newOptions[__($k)] = __($v);
                }
                $params['options'] = $newOptions;
            } else {
                // Just translate output
                $params['options'] = array_map(['AppLocale', 'translate'], $params['options']);
            }
        }

        if (isset($params['output'])) {
            $params['output'] = array_map(['AppLocale', 'translate'], $params['output']);
        }

        if (isset($params['values']) && isset($params['translateValues'])) {
            $params['values'] = array_map(['AppLocale', 'translate'], $params['values']);
        }

        require_once($this->_get_plugin_filepath('function','html_options'));
        return smarty_function_html_options($params, $smarty);
    }

    /**
     * Iterator function for looping through objects extending the ItemIterator class.
     * Smarty usage:
     * {iterate from="myIterator" item="item" [key="key"]}
     * @endcode
     * @param $smarty Smarty
     * @return string the content of the block
     * @param $repeat boolean reference
     */
    public function smartyIterate($params, $content, &$smarty, &$repeat) {
        $iterator = $smarty->get_template_vars($params['from']);

        if (isset($params['key'])) {
            if (empty($content)) {
                $smarty->assign($params['key'], 1);
            } else {
                // Ambil nilai saat ini
                $currentVal = $smarty->get_template_vars($params['key']);
                // Paksa jadi integer sebelum ditambah 1 untuk mencegah Warning
                $smarty->assign($params['key'], (int)$currentVal + 1);
            }
        }

        // If the iterator is empty, we're finished.
        if (!$iterator || $iterator->eof()) {
            if (!$repeat) return $content;
            $repeat = false;
            return '';
        }

        $repeat = true;

        if (isset($params['key'])) {
            list($key, $value) = $iterator->nextWithKey();
            $smarty->assign($params['item'], $value);
            $smarty->assign($params['key'], $key);
        } else {
            $smarty->assign($params['item'], $iterator->next());
        }
        return $content;
    }

    /**
     * Smarty usage: {icon name="image name" alt="alternative name" url="url path"}
     * @param $smarty Smarty
     * @return string the rendered icon HTML
     */
    public function smartyIcon($params, &$smarty) {
        if (isset($params) && !empty($params)) {
            $iconHtml = '';
            if (isset($params['name'])) {
                $disabled = (isset($params['disabled']) && !empty($params['disabled']));
                if (!isset($params['path'])) $params['path'] = 'lib/pkp/templates/images/icons/';
                $iconHtml = '<img src="' . $smarty->get_template_vars('baseUrl') . '/' . $params['path'];
                $iconHtml .= $params['name'] . ($disabled ? '_disabled' : '') . '.gif" width="16" height="14" alt="';

                if (isset($params['alt'])) {
                    $iconHtml .= $params['alt'];
                } else {
                    $iconHtml .= __('icon.'.$params['name'].'.alt');
                }
                $iconHtml .= '" ';

                if (isset($params['onclick'])) {
                    $iconHtml .= 'onclick="' . $params['onclick'] . '" ';
                }

                $iconHtml .= '/>';

                if (!$disabled && isset($params['url'])) {
                    $iconHtml = '<a href="' . $params['url'] . '" class="icon">' . $iconHtml . '</a>';
                }
            }
            return $iconHtml;
        }
    }

    /**
     * Usage: {page_info iterator=$myIterator}
     * @param $smarty Smarty
     * @return string the page information text
     * @param $params array
     * @type ItemIterator $iterator The iterator to generate page info for
     */
    public function smartyPageInfo($params, &$smarty) {
        $iterator = $params['iterator'];

        $itemsPerPage = $smarty->get_template_vars('itemsPerPage');
        if (!is_numeric($itemsPerPage)) $itemsPerPage=25;

        $page = $iterator->getPage();
        // $pageCount = $iterator->getPageCount();
        $itemTotal = $iterator->getCount();

        // if ($pageCount<1) return '';

        $from = (($page - 1) * $itemsPerPage) + 1;
        $to = min($itemTotal, $page * $itemsPerPage);

        return __('navigation.items', [
            'from' => ($to===0?0:$from),
            'to' => $to,
            'total' => $itemTotal
        ]);
    }

    /**
     * Flush the output buffer.
     * @param $smarty Smarty
     * @return void
     * @param $params array
     */
    public function smartyFlush($params, &$smarty) {
        $smarty->flush();
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
     * [WIZDAM] Menangkap 'echo' ke variabel output)
     * @param $smarty Smarty
     * @return string the output generated by the hook
     * @param $params array
     * @type string $name The name of the hook to call
     */
    public function smartyCallHook($params, &$smarty) {
        $hookName = $params['name'];
        unset($params['name']);
        $args = [&$params, &$smarty];
    
        $level = ob_get_level(); // Catat level sebelum
        ob_start();
    
        HookRegistry::dispatch($hookName, $args);
    
        // Tutup hanya buffer yang kita buka, tidak lebih
        while (ob_get_level() > $level + 1) {
            ob_end_flush(); // Flush buffer yang tidak tertutup dari hook
        }
    
        $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }

    /**
     * Get debugging information and assign it to the template.
     * @param $smarty Smarty
     * @return void
     * @param $params array
     * @type string $name The name of the hook to call
     */
    public function smartyGetDebugInfo($params, &$smarty) {
        if (Config::getVar('debug', 'show_stats')) {
            $smarty->assign('enableDebugStats', true);

            $pkpProfiler = Registry::get('system.debug.profiler');
            foreach ($pkpProfiler->getData() as $output => $value) {
                $smarty->assign($output, $value);
            }
            $smarty->assign('pqpCss', $this->request->getBaseUrl() . '/lib/pkp/lib/pqp/css/pQp.css');
            $smarty->assign('pqpTemplate', BASE_SYS_DIR . '/lib/pkp/lib/pqp/pqp.tpl');
        }
    }

    /**
     * Generate a URL into a PKPApp.
     * Smarty usage: {url router=ROUTE_PAGE page="myPage" op="myOp" path="extra/path" anchor="myAnchor" escape=true params=$myParams context=$myContext}
     * @param $smarty Smarty
     * @return string the generated URL
     * @param $parameters array associative array with the following possible keys:
     *  - router: ROUTE_PAGE or ROUTE_COMPONENT (default: current router)
     *  - context: array with context variables (if not set, will be built from other context parameters)
     *  - page: the page to call (for ROUTE_PAGE)
     *  - component: the component to call (for ROUTE_COMPONENT)
     *  - op: the operation to call
     */
    public function smartyUrl($parameters, &$smarty) {
        if ( !isset($parameters['context']) ) {
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

        $parameters = array_merge($parameters, (array) $params);

        $router = $router ?? (($this->request->getRouter() instanceof PKPComponentRouter) ? ROUTE_COMPONENT : ROUTE_PAGE);

        $dispatcher = PKPApplication::getDispatcher();
        
        switch($router) {
            case ROUTE_PAGE:
                $handler = $page;
                break;
            case ROUTE_COMPONENT:
                $handler = $component;
                break;
            default:
                assert(false);
        }

        return $dispatcher->url($this->request, $router, $context, $handler, $op, $path, $parameters, $anchor, !isset($escape) || $escape);
    }

    /**
     * Load a URL into a div via AJAX.
     * Smarty usage: {load_url_in_div id="myDivId" url="my/url/path" [method="get|post"] [loadText="Loading..."]}
     * @param $smarty Smarty
     * @return string the generated HTML and JavaScript
     */
    public function setProgressFunction($progressFunction) {
        Registry::set('progressFunctionCallback', $progressFunction);
    }

    /**
     * Smarty usage: {call_progress_function}
     * @param $smarty Smarty
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
     */
    public function updateProgressBar($progress, $total) {
        static $lastPercent;
        $percent = round($progress * 100 / $total);
        if (!isset($lastPercent) || $lastPercent != $percent) {
            for($i=1; $i <= $percent-$lastPercent; $i++) {
                echo '<img src="' . $this->request->getBaseUrl() . '/templates/images/progbar.gif" width="5" height="15">';
            }
        }
        $lastPercent = $percent;

        $templateMgr = TemplateManager::getManager();
        $templateMgr->flush();
    }

    /**
     * Display page links.
     * Smarty usage: {page_links iterator=$myIterator name="myParamName" [params=$extraParams] [anchor="myAnchor"] [all_extra="extraAttributes"]}
     * @param $smarty Smarty
     * @return string the page links HTML
     * @param $params array
     */
    public function smartyPageLinks($params, &$smarty) {
        $iterator = $params['iterator'];
        $name = $params['name'];
    
        if (isset($params['params']) && is_array($params['params'])) {
            $extraParams = $params['params'];
            unset($params['params']);
            $params = array_merge($params, $extraParams);
        }
        $anchor = isset($params['anchor']) ? $params['anchor'] : null;
        unset($params['anchor']);

        $allExtra = isset($params['all_extra']) ? ' ' . $params['all_extra'] : '';
        unset($params['all_extra']);

        unset($params['iterator']);
        unset($params['name']);
    
        $page = $iterator->getPage();
        $pageCount = $iterator->getPageCount();
        $paramName = $name . 'Page';
    
        if ($pageCount <= 1) return '';
    
        $request = Application::get()->getRequest();
        
        $onEitherSide = 2;
        $value = '<nav class="pagination" role="navigation" aria-label="Pagination">';
    
        // Tombol Previous
        if ($page > 1) {
            $params[$paramName] = $page - 1;
            $value .= '<a class="pagination-link pagination-arrow" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>&laquo; ' . __('common.previous') . '</a>';
        } else {
            $value .= '<span class="pagination-disabled pagination-arrow">&laquo; ' . 
                      __('common.previous') . '</span>';
        }
    
        $start = max(1, $page - $onEitherSide);
        $end = min($pageCount, $page + $onEitherSide);
        
        if ($start > 2) {
            $params[$paramName] = 1;
            $value .= '<a class="pagination-link" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>1</a>';
            $value .= '<span class="pagination-ellipsis">...</span>';
        } elseif ($start == 2) {
            $params[$paramName] = 1;
            $value .= '<a class="pagination-link" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>1</a>';
        }
    
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) {
                $value .= "<span class=\"pagination-current\">$i</span>";
            } else {
                $params[$paramName] = $i;
                $value .= '<a class="pagination-link" href="' . 
                          $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                          '"' . $allExtra . '>' . $i . '</a>';
            }
        }
    
        if ($end < $pageCount - 1) {
            $value .= '<span class="pagination-ellipsis">...</span>';
            $params[$paramName] = $pageCount;
            $value .= '<a class="pagination-link" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>' . $pageCount . '</a>';
        } elseif ($end == $pageCount - 1) {
            $params[$paramName] = $pageCount;
            $value .= '<a class="pagination-link" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>' . $pageCount . '</a>';
        }
        
        // Tombol Next
        if ($page < $pageCount) {
            $params[$paramName] = $page + 1;
            $value .= '<a class="pagination-link pagination-arrow" href="' . 
                      $request->url(null, null, null, $request->getRequestedArgs(), $params, $anchor) . 
                      '"' . $allExtra . '>' . __('common.next') . ' &raquo;</a>';
        } else {
            $value .= '<span class="pagination-disabled pagination-arrow">' . 
                      __('common.next') . ' &raquo;</span>';
        }
    
        $value .= '</nav>';
        return $value;
    }

    /**
     * Convert Smarty arguments to an array.
     * Smarty usage: {to_array arg1 arg2 ...}
     * @return array the arguments as an array
     */
    public function smartyToArray() {
        return func_get_args();
    }

    /**
     * Concatenate Smarty arguments.
     * Smarty usage: {concat arg1 arg2 ...}
     * @return string the concatenated arguments
     */
    public function smartyConcat() {
        $args = func_get_args();
        return implode('', $args);
    }

    /**
     * Convert a date string to a Unix timestamp.
     * Smarty usage: {strtotime dateString}
     * @param $string string
     * @return int the Unix timestamp
     */
    public function smartyStrtotime($string) {
        return strtotime($string);
    }

    /**
     * Get a template variable's value.
     * Smarty usage: {get_value name="variableName"}
     * @param $name string the variable name
     * @return mixed the variable value
     */
    public function smartyGetValue($name) {
        $templateMgr = TemplateManager::getManager();
        return $templateMgr->get_template_vars($name);
    }

    /**
     * Escape a string.
     * Smarty usage: {escape string="myString" esc_type="html|htmlall|url|quotes|hex|hexentity|javascript|mail|jsparam" char_set="UTF-8"}
     * @param $string string the string to escape
     * @param $esc_type string the type of escaping
     * @param $char_set string the character set
     * @return string the escaped string
     */
    public function smartyEscape($string, $esc_type = 'html', $char_set = null) {
        if ($char_set === null) $char_set = LOCALE_ENCODING;
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
     * Smarty usage: {truncate string="myString" length=80 etc="..." break_words=false middle=false skip_tags=true}
     * @param $string string the string to truncate
     * @param $length int the length to truncate to
     * @param $etc string the string to append to the truncated string
     * @param $break_words boolean whether to break words
     * @param $middle boolean whether to truncate in the middle
     * @param $skip_tags boolean whether to skip HTML tags
     * @return string the truncated string
     */
    public function smartyTruncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false, $skip_tags = true) {
        if ($length == 0) return '';

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
                if(!$break_words) {
                    $string = PKPString::regexp_replace('/\s+?(\S+)?$/', '', PKPString::substr($string, 0, $length+1));
                } else $string = PKPString::substr($string, 0, $length+1);
                if ($skip_tags) $string = $this->_reinsertTags($string, $tags);
                return $this->_closeTags($string) . $etc;
            } else {
                $firstHalf = PKPString::substr($string, 0, (int)($length/2));
                $secondHalf = PKPString::substr($string, -(int)($length/2));

                if($break_words) {
                    if($skip_tags) {
                        $firstHalf = $this->_reinsertTags($firstHalf, $tags);
                        $secondHalf = $this->reinsertTags($secondHalf, $tagsReverse, true);
                        return $this->_closeTags($firstHalf) . $etc . $this->_closeTags($secondHalf, true);
                    } else {
                        return $firstHalf . $etc . $secondHalf;
                    }
                } else {
                    for($i=(int)($length/2); $string[$i] != ' '; $i++) {
                        $firstHalf = PKPString::substr($string, 0, $i+1);
                    }
                    for($i=(int)($length/2); PKPString::substr($string, -$i, 1) != ' '; $i++) {
                        $secondHalf = PKPString::substr($string, -$i-1);
                    }

                    if ($skip_tags) {
                        $firstHalf = $this->_reinsertTags($firstHalf, $tags);
                        $secondHalf = $this->reinsertTags($secondHalf, $tagsReverse, strlen($string));
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

    // 
    // Private helper functions for truncating with HTML tags
    //

    /**
     * Remove HTML tags from a string, storing them in an array for later reinsertion.
     * @param $string string the input string
     * @param $tags array reference to store removed tags
     * @param $reverse boolean whether to process the string in reverse
     * @param $length int maximum number of tags to remove
     * @return string the string without HTML tags
     */
    public function _removeTags($string, &$tags, $reverse = false, $length) {
        if($reverse) {
            return $this->_removeTagsAuxReverse($string, 0, $tags, $length);
        } else {
            return $this->_removeTagsAux($string, 0, $tags, $length);
        }
    }

    /**
     * Auxiliary function to remove HTML tags from the start of a string.
     * @param $string string the input string
     * @param $loc int the current location in the original string
     * @param $tags array reference to store removed tags
     * @param $length int maximum number of tags to remove
     * @return string the string without HTML tags
     */
    public function _removeTagsAux($string, $loc, &$tags, $length) {
        if(strlen($string) > 0 && $length > 0) {
            $length--;
            if(PKPString::substr($string, 0, 1) == '<') {
                $closeBrack = PKPString::strpos($string, '>')+1;
                if($closeBrack) {
                    $tags[] = [PKPString::substr($string, 0, $closeBrack), $loc];
                    return $this->_removeTagsAux(PKPString::substr($string, $closeBrack), $loc+$closeBrack, $tags, $length);
                }
            }
            return PKPString::substr($string, 0, 1) . $this->_removeTagsAux(PKPString::substr($string, 1), $loc+1, $tags, $length);
        }
        return '';
    }

    /**
     * Auxiliary function to remove HTML tags from the end of a string.
     * @param $string string the input string
     * @param $loc int the current location in the original string
     * @param $tags array reference to store removed tags
     * @param $length int maximum number of tags to remove
     * @return string the string without HTML tags
     */
    public function _removeTagsAuxReverse($string, $loc, &$tags, $length) {
        $backLoc = PKPString::strlen($string)-1;
        if($backLoc >= 0 && $length > 0) {
            $length--;
            if(PKPString::substr($string, $backLoc, 1) == '>') {
                $tag = '>';
                $openBrack = 1;
                while (PKPString::substr($string, $backLoc-$openBrack, 1) != '<') {
                    $tag = PKPString::substr($string, $backLoc-$openBrack, 1) . $tag;
                    $openBrack++;
                }
                $tag = '<' . $tag;
                $openBrack++;

                $tags[] = [$tag, $loc];
                return $this->_removeTagsAuxReverse(PKPString::substr($string, 0, -$openBrack), $loc+$openBrack, $tags, $length);
            }
            return $this->_removeTagsAuxReverse(PKPString::substr($string, 0, -1), $loc+1, $tags, $length) . PKPString::substr($string, $backLoc, 1);
        }
        return '';
    }

    /**
     * Reinsert HTML tags into a string at their original locations.
     * @param $string string the input string
     * @param $tags array reference to the removed tags
     * @param $reverse boolean whether to process the string in reverse
     * @return string the string with HTML tags reinserted
     */
    public function _reinsertTags($string, &$tags, $reverse = false) {
        if(empty($tags)) return $string;

        for($i = 0; $i < count($tags); $i++) {
            $length = PKPString::strlen($string);
            if ($tags[$i][1] < PKPString::strlen($string)) {
                if ($reverse) {
                    if ($tags[$i][1] == 0) {
                        $string = PKPString::substr_replace($string, $tags[$i][0], $length, 0);
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
     * @param $string string the input string
     * @param $open boolean whether to close open tags (false) or open closed tags (true)
     * @return string the string with HTML tags closed
     */
    public function _closeTags($string, $open = false){
        PKPString::regexp_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $string, $result);
        $openedtags = $result[1];

        PKPString::regexp_match_all("#</([a-z]+)>#iU", $string, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        $len_closed = count($closedtags);
        
        if(count($closedtags) == $len_opened){
            return $string;
        }

        $openedtags = array_reverse($openedtags);
        $closedtags = array_reverse($closedtags);

        if ($open) {
            for($i=0; $i < $len_closed; $i++) {
                if (!in_array($closedtags[$i],$openedtags)){
                    $string = '<'.$closedtags[$i].'>' . $string;
                } else {
                    unset($openedtags[array_search($closedtags[$i],$openedtags)]);
                }
            }
            return $string;
        } else {
            for($i=0; $i < $len_opened; $i++) {
                if (!in_array($openedtags[$i],$closedtags)){
                    $string .= '</'.$openedtags[$i].'>';
                } else {
                    unset($closedtags[array_search($openedtags[$i],$closedtags)]);
                }
            }
            return $string;
        }
    }

    /**
     * Split a string into an array.
     * Smarty usage: {explode string="myString" separator=","}
     * @param $string string the input string
     * @param $separator string the separator
     * @return array the resulting array
     */
    public function smartyExplode($string, $separator) {
        return explode($separator, $string);
    }

    /**
     * Assign a value to a template variable.
     * Smarty usage: {assign var="variableName" value=$myValue [passThru=true]}
     * @param $value mixed the value to assign
     * @param $varName string the variable name
     * @param $passThru boolean whether to return the value
     * @return mixed the value if passThru is true
     */
    public function smartyAssign($value, $varName, $passThru = false) {
        if (isset($varName)) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign($varName, $value);
        }
        if ($passThru) return $value;
    }

	/**
	 * Smarty usage: {sort_heading key="localization.key.name" sort="foo"}
	 * Custom Smarty function for creating heading links to sort tables by
	 * @param $params array associative array
	 * @param $smarty Smarty
	 * @return string heading link to sort table by
	 */
    public function smartySortHeading($params, &$smarty) {
        if (isset($params) && !empty($params)) {
            $sortParams = $this->request->getQueryArray();
            isset($params['sort'])? ($sortParams['sort'] = $params['sort']) : null;
            $sortDirection = $smarty->get_template_vars('sortDirection');
            $sort = $smarty->get_template_vars('sort');

            if($params['sort'] == $sort) {
                if ($sortDirection == SORT_DIRECTION_ASC) {
                    $sortParams['sortDirection'] = SORT_DIRECTION_DESC;
                } else {
                    $sortParams['sortDirection'] = SORT_DIRECTION_ASC;
                }
            } else {
                $sortParams['sortDirection'] = SORT_DIRECTION_ASC;
            }

            $link = $this->request->url(null, null, null, $this->request->getRequestedArgs(), $sortParams, null, true);
            $text = isset($params['key']) ? __($params['key']) : '';
            $style = (isset($sort) && isset($params['sort']) && ($sort == $params['sort'])) ? ' style="font-weight:bold"' : '';

            return "<a href=\"$link\"$style>$text</a>";
        }
    }

    /**
     * Smarty usage: {sort_search key="localization.key.name" sort="foo"}
     * Custom Smarty function for creating heading links to sort search results by
     * @param $params array associative array
     * @param $smarty Smarty
     * @return string heading link to sort search results by
     */
    public function smartySortSearch(array $params, object $smarty): string {
        if (empty($params)) {
            return '';
        }
    
        $sort = $smarty->get_template_vars('sort');
        $sortDirection = $smarty->get_template_vars('sortDirection');
    
        $isCurrentSort = ($params['sort'] ?? null) === $sort;
        $direction = ($isCurrentSort && $sortDirection === SORT_DIRECTION_ASC) 
            ? SORT_DIRECTION_DESC 
            : SORT_DIRECTION_ASC;
    
        // Root cause solver: Inisialisasi awal
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
     * Smarty usage: {load_url_in_div id="myDivId" url="my/url/path" [class="myCssClass"] [loadMessageId="loading.message.id"]}
     * @param $smarty Smarty
     * @return string the generated HTML and JavaScript
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
        if (isset($params['class'])) $this->assign('inDivClass', $params['class']);

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
     * Smarty usage: {modal url="my/url/path" actOnId="elementId" [actOnType="elementType"] button="buttonId" [dialogTitle="Dialog Title"]}
     * @param $smarty Smarty
     * @return string the generated JavaScript
     * @param $params array
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
            $actOnType = isset($params['actOnType'])?$params['actOnType']:'';
            $actOnId = $params['actOnId'];
            $button = $params['button'];
            $dialogTitle = isset($params['dialogTitle'])?$params['dialogTitle']: false;
        }

        $submitButton = __('common.ok');
        $cancelButton = __('common.cancel');

        foreach (['submitButton', 'cancelButton', 'url', 'actOnType', 'actOnId', 'button'] as $varName) {
            $$varName = $this->smartyEscape($$varName, 'javascript');
        }

        $dialogTitle = isset($dialogTitle) ? ", '$dialogTitle'" : "";
        $modalCode = "<script type='text/javascript'>
			<!--
			var localizedButtons = ['$submitButton', '$cancelButton'];
			modal('$url', '$actOnType', '$actOnId', localizedButtons, '$button'$dialogTitle);
			// -->
        </script>\n";

        return $modalCode;
    }

    /**
     * Generate a confirmation dialog.
     * Smarty usage: {confirm [url="my/url/path"] [actOnType="elementType"] [actOnId="elementId"] button="buttonId" [dialogText="Are you sure?"] [translate=false]}
     * @param $smarty Smarty
     * @return string the generated JavaScript
     * @param $params array
     */
    public function smartyConfirm($params, &$smarty) {
        if (!isset($params['button'])) {
            $smarty->trigger_error("Button parameter is missing from confirm");
        } else {
            $button = $params['button'];
        }

        $url = isset($params['url']) ? $params['url'] : null;
        $actOnType = isset($params['actOnType']) ? $params['actOnType'] : '';
        $actOnId = isset($params['actOnId'])?$params['actOnId']:'';

        if (isset($params['dialogText']))  {
            $showDialog = true;
            if(isset($params['translate']) && $params['translate'] == false) {
                $dialogText = $params['dialogText'];
            } else {
                $dialogText = __($params['dialogText']);
            }
        } else {
            $showDialog = false;
        }

        if (!$showDialog && !$url) {
            $smarty->trigger_error("Both URL and dialogText parameters are missing from confirm");
        }

        $submitButton = __('common.ok');
        $cancelButton = __('common.cancel');

        foreach (['button', 'url', 'actOnType', 'actOnId', 'dialogText', 'submitButton', 'cancelButton'] as $varName) {
            $$varName = $this->smartyEscape($$varName, 'javascript');
        }

        if ($showDialog) {
            $confirmCode = "<script type='text/javascript'>
			<!--
			var localizedButtons = ['$submitButton', '$cancelButton'];
			modalConfirm('$url', '$actOnType', '$actOnId', '$dialogText', localizedButtons, '$button');
			// -->
            </script>\n";
        } else {
            $confirmCode = "<script type='text/javascript'>
			<!--
			buttonPost('$url', '$button');
			// -->
            </script>";
        }

        return $confirmCode;
    }

    /**
     * Generate a modal title bar.
     * Smarty usage: {modal_title id="modalId" [iconClass="icon-class"] [key="localization.key"] [keyTranslated="Pre-translated Title"] [canClose=true]}
     * @param $smarty Smarty
     * @return string the generated HTML
     * @param $params array
     */
    public function smartyModalTitle($params, &$smarty) {
        if (!isset($params['id'])) {
            $smarty->trigger_error("Selector missing for title bar initialization");
        } else {
            $id = $params['id'];
        }

        $iconClass = isset($params['iconClass']) ? $params['iconClass'] : '';
        if(isset($params['iconClass'])) {
            $iconClass = $params['iconClass'];
            $iconHtml = "<span class='icon $iconClass' />";
        } else $iconHtml = "";

        if(isset($params['key'])) {
            $keyHtml = "<span class='text'>" . __($params['key']) . "</span>";
        } elseif(isset($params['keyTranslated'])) {
            $keyHtml = "<span class='text'>" . $params['keyTranslated'] . "</span>";
        } else $keyHtml = "";


        if(isset($params['canClose'])) {
            $canClose = $params['canClose'];
            $canCloseHtml = "<a class='close ui-corner-all' href='#'><span class='ui-icon ui-icon-closethick'>close</span></a>";
        } else $canCloseHtml = "";

        return "<script type='text/javascript'>
			<!--
			$(function() {
				$('$id').last().parent().prev('.ui-dialog-titlebar').remove();
				$('a.close').live('click', function() { $(this).parent().parent().dialog('close'); return false; });
				return false;
			});
			// -->
            </script>
            <div class='pkp_controllers_modal_titleBar'>" .
                $iconHtml .
                $keyHtml .
                $canCloseHtml .
                "<span style='clear:both' />
            </div>";
    }

    /**
     * Mengembalikan tag &lt;?xml menjadi <?xml
     */
    public function fixXmlOutput($output, $smarty) {
        return preg_replace('/&lt;\?xml(.*?)\?&gt;/is', '<?xml$1?>', $output);
    }
    
    /**
     * [Wizdam] Custom Smarty Modifier: Mask Email ***
     * @param string $email
     * @return string
     */
    public function smartyMaskEmail($email) {
        if (empty($email) || strpos($email, '@') === false) {
            return $email;
        }

        list($local, $domain) = explode('@', $email, 2);
        
        // Cek separator (prioritas titik)
        $separatorPos = strpos($local, '.');
        
        if ($separatorPos !== false) {
            // Ada titik: sembunyikan semua setelah titik pertama
            $visible = substr($local, 0, $separatorPos + 1); 
            $hiddenPart = substr($local, $separatorPos + 1);
            $masked = $visible . str_repeat('*', strlen($hiddenPart));
        } else {
            // Tidak ada titik: logika panjang string
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
     * [Wizdam] Custom Smarty Modifier: Mask Email: dot at
     * @param string $email
     * @return string
     */
    public function smartyDotatEmail(?string $email): string {
        if (empty($email)) {
            return '';
        }
        
        return str_replace(['@', '.'], [' [at] ', ' [dot] '], (string) $email);
    }
    
    /**
     * [Wizdam] Custom Smarty Modifier: Mask Phone: ***
     * @param string $phone
     * @return string
     */
    public function smartyMaskPhone(?string $phone): string {
        if (empty($phone)) {
            return '';
        }

        $len = strlen($phone);

        // Jika nomor terlalu pendek (kurang dari 6 karakter), kembalikan aslinya
        if ($len < 6) {
            return (string) $phone;
        }

        // Pengaturan default: tampilkan 4 di awal, 3 di akhir
        $visibleStart = 4;
        $visibleEnd = 3;

        // Jika panjang nomor nanggung (misal nomor lokal pendek 6-8 digit),
        // kurangi bagian yang terlihat agar bintang (*) tetap muncul
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
     * @param array|null $imageData
     * @return array|null
     */
    public function _sanitizeProfileImage($imageData) {
        if (!is_array($imageData)) {
            return null;
        }
                
        $safeData = [
            'uploadName' => isset($imageData['uploadName']) 
                ? preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $imageData['uploadName']) 
                : null,
            'width' => isset($imageData['width']) ? (int)$imageData['width'] : 0,
            'height' => isset($imageData['height']) ? (int)$imageData['height'] : 0
        ];
                
        return (!empty($safeData['uploadName'])) ? $safeData : null;
    }
    
    /**
     * Helper function to calculate time ago.
     * Smarty modifier: Menghitung waktu berlalu (Time Ago) dengan dukungan Minggu.
     * @param string|null $datetime
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

        // PERBAIKAN: Jika kosong, kembalikan string kosong (bukan terjemahan kaku)
        if (empty($datetime)) {
            return ''; 
        }
                
        $time = time() - strtotime($datetime);
                
        if ($time < 60) {
            return PKPLocale::translate('common.timeAgo.now');
        } elseif ($time < 3600) {
            $minutes = round($time / 60);
            return PKPLocale::translate('common.timeAgo.minutesAgo', ['count' => $minutes]);
        } elseif ($time < 86400) {
            $hours = round($time / 3600);
            return PKPLocale::translate('common.timeAgo.hoursAgo', ['count' => $hours]);
        } elseif ($time < 604800) { 
            // Kurang dari 7 hari (1 minggu = 604.800 detik)
            $days = round($time / 86400);
            return PKPLocale::translate('common.timeAgo.daysAgo', ['count' => $days]);
        } elseif ($time < 2592000) { 
            // Kurang dari 30 hari (1 bulan = 2.592.000 detik)
            $weeks = round($time / 604800);
            return PKPLocale::translate('common.timeAgo.weeksAgo', ['count' => $weeks]);
        } elseif ($time < 31536000) { 
            // Kurang dari 365 hari (1 tahun = 31.536.000 detik)
            $months = round($time / 2592000);
            return PKPLocale::translate('common.timeAgo.monthsAgo', ['count' => $months]);
        } else {
            $years = round($time / 31536000);
            return PKPLocale::translate('common.timeAgo.yearsAgo', ['count' => $years]);
        }
    }
    
    /**
     * Mesin Utama (Private)
     * Smarty modifier: Format angka besar bergaya statistik global
     * (contoh: 3 juta, 12 million) (kode turunan: smartyMetricNumber & suffix)
     * @param $number int the number to format
     * @param $precision int the number of decimal places to include
     * @return string the formatted number with magnitude suffix (K, M, B)
     */
    private function calculateMetricData($number, $precision = 1) {
        if (!is_numeric($number)) {
            return ['number' => $number, 'suffix' => ''];
        }

        $val = $number;
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
     */
    public function smartyMetricNumber($number) {
        $data = $this->calculateMetricData($number);
        return $data['number'];
    }

    /**
     * Modifier Smarty 2: Kata Imbuhan (Suffix)
     */
    public function smartyMetricSuffix($number) {
        $data = $this->calculateMetricData($number);
        return $data['suffix'];
    }
    
    /**
     * Smarty modifier: Format Ukuran File (Bytes ke KB / MB)
     * Menggunakan basis 1024.
     * @param $bytes int the file size in bytes
     * @param $precision int the number of decimal places to include for MB
     * @return string the formatted file size with appropriate unit (B, KB, MB)
     */
    public function smartyFileSize($bytes, $precision = 1) {
        if (!is_numeric($bytes)) return $bytes;

        if ($bytes >= 1048576) {
            // Konversi ke MB (1 MB = 1.048.576 bytes)
            $val = round($bytes / 1048576, $precision);
            // Ubah titik desimal menjadi koma (contoh: 1.2 menjadi 1,2 MB)
            return str_replace('.', ',', $val) . ' MB';
        } elseif ($bytes >= 1024) {
            // Konversi ke KB (1 KB = 1024 bytes)
            $val = round($bytes / 1024, 0);
            // Format ribuan (contoh: 1200 menjadi 1.200 KB)
            return number_format($val, 0, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    /** Smarty modifier: Format Singkatan Angka Statistik (K, M, B) */
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

        // Format desimal koma
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
    /** END: Smarty modifier: Format Singkatan Angka Statistik (K, M, B) */
    
    /**
     * FUNGSI SMARTY KUSTOM (WIZDAM PLUGIN)
     * Membangun URL "native" kita secara "smooth" TANPA TANDA AMPERSAND.
     * Smarty usage: {native_url page="archive|volume|issue" [volume=volumeId] [slug=issueSlug] [showToc=true]}
     * @param $params array
     * @param $smarty Smarty
     * @return string the constructed URL
     */
    public function smartyNativeUrl($params, $smarty) {
        $request = $this->request;
        $journal = $request->getJournal();
        $baseUrl = $request->getBaseUrl();
        $journalPath = $journal ? $journal->getPath() : '';
        
        $page = isset($params['page']) ? $params['page'] : '';
        $url = $baseUrl . '/' . $journalPath;

        // Di sinilah "hardcode" arsitektur Anda berada
        switch ($page) {
            case 'archive':
                $url .= '/volumes'; // Hasil: .../volumes
                break;
            
            // --- AWAL PERBAIKAN ---
            case 'volume':
                // Cek apakah ID volume SPESIFIK diberikan
                if (isset($params['volume']) && $params['volume']) {
                    // Jika YA, buat URL spesifik: .../volumes/10
                    $url .= '/volumes/' . $params['volume'];
                } else {
                    // Jika TIDAK, buat URL arsip utama: .../volumes
                    $url .= '/volumes';
                }
                break;
            // --- AKHIR PERBAIKAN ---

            case 'issue':
                // Ini BUKAN default '0', ini mengambil ID yang *diperlukan*
                $volumeId = isset($params['volume']) ? $params['volume'] : '0'; 
                $slug = isset($params['slug']) ? $params['slug'] : '';
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