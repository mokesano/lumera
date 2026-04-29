<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPApplication.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPApplication
 * @ingroup core
 * @brief Class describing this application.
 *
 * WIZDAM FORK v3.4 MODIFICATIONS:
 * - Added Publisher/Site Centric Constants
 * - Strict Typing enforced
 */

if (!headers_sent()) {
    ob_start();
}

define_exposed('REALLY_BIG_NUMBER', 10000);

const ROUTE_COMPONENT = 'component';
const ROUTE_PAGE = 'page';

const CONTEXT_SITE = 0;
const CONTEXT_ID_NONE = 0;
const REVIEW_ROUND_NONE = 0;

// --- [WIZDAM FORK ARCHITECTURE: PUBLISHER CENTRIC CONSTANTS] ---
// Kami menetapkan Site/Publisher sebagai Root Entity dengan ID 1 (Hex 0x1).
// Ini membedakan antara "Tidak ada data" (0) dengan "Milik Publisher" (1).
const ASSOC_TYPE_SITE = 0x00000001;      // Decimal: 1
const ASSOC_TYPE_PUBLISHER = 0x00000001; // Alias Semantik

// --- [STANDARD ENTITIES] ---
const ASSOC_TYPE_USER = 0x00001000;
const ASSOC_TYPE_USER_GROUP = 0x0100002;
const ASSOC_TYPE_CITATION = 0x0100003;
const ASSOC_TYPE_AUTHOR = 0x0100004;
const ASSOC_TYPE_EDITOR = 0x0100005;
const ASSOC_TYPE_SIGNOFF = 0x0100006;
const ASSOC_TYPE_USER_ROLES = 0x0100007;
const ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES = 0x0100008;
const ASSOC_TYPE_PLUGIN = 0x0000211;

class PKPApplication {
    /** @var array<string, mixed>|null */
    public ?array $enabledProducts = null;
    
    /** @var array<string, mixed>|null */
    public ?array $allProducts = null;

    /**
     * Constructor
     */
    public function __construct() {
        $errorReportingLevel = E_ALL;
        if (defined('E_DEPRECATED')) $errorReportingLevel &= ~E_DEPRECATED;
        if (defined('E_NOTICE')) $errorReportingLevel &= ~E_NOTICE;
        @error_reporting($errorReportingLevel);

        import('lib.pkp.classes.core.PKPProfiler');
        $pkpProfiler = new PKPProfiler();

        Console::logMemory('', 'PKPApplication::construct');
        Console::logSpeed('PKPApplication::construct');

        mt_srand((int) ((double) microtime() * 1000000));

        import('lib.pkp.classes.core.Core');
        import('lib.pkp.classes.core.PKPString');
        import('lib.pkp.classes.core.Registry');
        import('lib.pkp.classes.config.Config');

        if ((bool) Config::getVar('debug', 'display_errors')) {
            @ini_set('display_errors', '0');
        }

        Registry::set('application', $this);
        
        // Request dibuat DI SINI, sebelum komponen lain memintanya.
        import('classes.core.Request');
        $request = new Request();
        Registry::set('request', $request);
        // ---------------------------

        import('lib.pkp.classes.db.DAORegistry');
        import('lib.pkp.classes.db.XMLDAO');
        import('lib.pkp.classes.cache.CacheManager');
        import('classes.security.Validation');
        import('lib.pkp.classes.session.SessionManager');
        import('classes.template.TemplateManager');
        import('classes.notification.NotificationManager');
        import('lib.pkp.classes.plugins.PluginRegistry');
        import('lib.pkp.classes.plugins.HookRegistry');
        import('classes.i18n.AppLocale');

        PKPString::init();
        set_error_handler([$this, 'errorHandler']);

        $microTime = Core::microtime();
        Registry::set('system.debug.startTime', $microTime);

        $notes = [];
        Registry::set('system.debug.notes', $notes);
        Registry::set('system.debug.profiler', $pkpProfiler);

        if ((bool) Config::getVar('general', 'installed')) {
            $conn = DBConnection::getInstance();

            if (!$conn->isConnected()) {
                if ((bool) Config::getVar('database', 'debug')) {
                    $dbconn = $conn->getDBConn();
                    fatalError('Database connection failed: ' . $dbconn->errorMsg());
                } else {
                    fatalError('Database connection failed!');
                }
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPApplication() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPApplication(). Please refactor to use parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the current application object
     * @return PKPApplication|object|null
     */
    public static function getApplication() {
        return Registry::get('application');
    }

    /**
     * Get the request implementation singleton
     * @return Request
     */
    public static function getRequest(): PKPRequest {
        return Registry::get('request', true, null);
    }

    /**
     * Get the dispatcher implementation singleton
     * @return Dispatcher
     */
    public static function getDispatcher(): Dispatcher {
        $dispatcher = Registry::get('dispatcher', true, null);

        if ($dispatcher === null) {
            import('lib.pkp.classes.core.Dispatcher');
            $dispatcher = new Dispatcher();
            Registry::set('dispatcher', $dispatcher);

            $application = self::getApplication();
            if (is_object($application)) {
                $dispatcher->setApplication($application);
            }

            $dispatcher->addRouterName('lib.pkp.classes.core.PKPComponentRouter', ROUTE_COMPONENT);
            $dispatcher->addRouterName('classes.core.PageRouter', ROUTE_PAGE);
        }

        return $dispatcher;
    }

    /**
     * This executes the application by delegating the request to the dispatcher.
     * @return void
     */
    public function execute(): void {
        $dispatcher = self::getDispatcher(); 
        $dispatcher->dispatch(self::getRequest());
    }

    /**
     * Get the symbolic name of this application
     * @return string
     */
    public function getName(): string {
        return 'pkp-lib';
    }

    /**
     * Get the locale key for the name of this application.
     * @return string
     */
    public function getNameKey(): string {
        assert(false);
        return '';
    }

    /**
     * Get the "context depth" of this application.
     * @return int
     */
    public function getContextDepth(): int {
        assert(false);
        return 0;
    }

    /**
     * Get the list of the contexts available for this application
     * @return array
     */
    public static function getContextList(): array {
        assert(false);
        return [];
    }

    /**
     * Get the URL to the XML descriptor for the current version of this application.
     * @return string
     */
    public function getVersionDescriptorUrl(): string {
        assert(false);
        return '';
    }

    /**
     * This function retrieves all enabled product versions.
     * @param string|null $category
     * @param int|null $mainContextId
     * @return array
     */
    public function getEnabledProducts(?string $category = null, ?int $mainContextId = null): array {
        if ($this->enabledProducts === null || $mainContextId !== null) {
            $contextDepth = $this->getContextDepth();

            $settingContext = [];
            if ($contextDepth > 0) {
                $request = self::getRequest();
                $router = $request->getRouter();

                if ($mainContextId === null && !is_null($router)) {
                    $mainContext = $router->getContext($request, 1);
                    if (is_object($mainContext) && method_exists($mainContext, 'getId')) {
                        $mainContextId = (int) $mainContext->getId();
                    }
                }

                if ($mainContextId !== null) {
                    $settingContext[] = $mainContextId;
                }
                
                $settingContext = array_pad($settingContext, $contextDepth, 0);
                $settingContext = array_combine($this->getContextList(), $settingContext);
            }

            $versionDao = DAORegistry::getDAO('VersionDAO'); /* @var $versionDao VersionDAO */
            $this->enabledProducts = (array) $versionDao->getCurrentProducts($settingContext);
        }

        if ($category === null) {
            return $this->enabledProducts;
        } elseif (isset($this->enabledProducts[$category])) {
            return (array) $this->enabledProducts[$category];
        } else {
            return [];
        }
    }

    /**
     * Get the list of plugin categories for this application.
     * @return array
     */
    public function getPluginCategories(): array {
        assert(false);
        return [];
    }

    /**
     * Return the current version of the application.
     * @return Version
     */
    public function getCurrentVersion(): Version {
        $currentVersion = $this->getEnabledProducts('core');
        return $currentVersion[$this->getName()];
    }

    /**
     * Get the map of DAOName => full.class.Path for this application.
     * @return array
     */
    public function getDAOMap(): array {
        return [
            'AccessKeyDAO' => 'lib.pkp.classes.security.AccessKeyDAO',
            'AuthSourceDAO' => 'lib.pkp.classes.security.AuthSourceDAO',
            'CaptchaDAO' => 'lib.pkp.classes.captcha.CaptchaDAO',
            'CitationDAO' => 'lib.pkp.classes.citation.CitationDAO',
            'ControlledVocabDAO' => 'lib.pkp.classes.controlledVocab.ControlledVocabDAO',
            'ControlledVocabEntryDAO' => 'lib.pkp.classes.controlledVocab.ControlledVocabEntryDAO',
            'CountryDAO' => 'lib.pkp.classes.i18n.CountryDAO',
            'CurrencyDAO' => 'lib.pkp.classes.currency.CurrencyDAO',
            'DataObjectTombstoneDAO' => 'lib.pkp.classes.tombstone.DataObjectTombstoneDAO',
            'DataObjectTombstoneSettingsDAO' => 'lib.pkp.classes.tombstone.DataObjectTombstoneSettingsDAO',
            'FilterDAO' => 'lib.pkp.classes.filter.FilterDAO',
            'FilterGroupDAO' => 'lib.pkp.classes.filter.FilterGroupDAO',
            'GroupDAO' => 'lib.pkp.classes.group.GroupDAO',
            'GroupMembershipDAO' => 'lib.pkp.classes.group.GroupMembershipDAO',
            'HelpTocDAO' => 'lib.pkp.classes.help.HelpTocDAO',
            'HelpTopicDAO' => 'lib.pkp.classes.help.HelpTopicDAO',
            'InterestDAO' => 'lib.pkp.classes.user.InterestDAO',
            'InterestEntryDAO' => 'lib.pkp.classes.user.InterestEntryDAO',
            'LanguageDAO' => 'lib.pkp.classes.language.LanguageDAO',
            'MetadataDescriptionDAO' => 'lib.pkp.classes.metadata.MetadataDescriptionDAO',
            'NotificationDAO' => 'lib.pkp.classes.notification.NotificationDAO',
            'NotificationMailListDAO' => 'lib.pkp.classes.notification.NotificationMailListDAO',
            'NotificationSettingsDAO' => 'lib.pkp.classes.notification.NotificationSettingsDAO',
            'NotificationSubscriptionSettingsDAO' => 'lib.pkp.classes.notification.NotificationSubscriptionSettingsDAO',
            'ONIXCodelistItemDAO' => 'lib.pkp.classes.codelist.ONIXCodelistItemDAO',
            'ProcessDAO' => 'lib.pkp.classes.process.ProcessDAO',
            'QualifierDAO' => 'lib.pkp.classes.codelist.QualifierDAO',
            'ScheduledTaskDAO' => 'lib.pkp.classes.scheduledTask.ScheduledTaskDAO',
            'SessionDAO' => 'lib.pkp.classes.session.SessionDAO',
            'SiteDAO' => 'lib.pkp.classes.site.SiteDAO',
            'SiteSettingsDAO' => 'lib.pkp.classes.site.SiteSettingsDAO',
            'SubjectDAO' => 'lib.pkp.classes.codelist.SubjectDAO',
            'TimeZoneDAO' => 'lib.pkp.classes.i18n.TimeZoneDAO',
            'TemporaryFileDAO' => 'lib.pkp.classes.file.TemporaryFileDAO',
            'VersionDAO' => 'lib.pkp.classes.site.VersionDAO',
            'XMLDAO' => 'lib.pkp.classes.db.XMLDAO'
        ];
    }

    /**
     * Return the fully-qualified (e.g. page.name.ClassNameDAO) name of the given DAO.
     * @param string $name
     * @return string|null
     */
    public function getQualifiedDAOName(string $name): ?string {
        $map = Registry::get('daoMap', true, $this->getDAOMap());
        if (isset($map[$name])) return (string) $map[$name];
        return null;
    }

    /**
     * Instantiate the help object for this application.
     * @return object
     */
    public function instantiateHelp(): object {
        assert(false);
        return new stdClass(); 
    }

    /**
     * Custom error handler
     * @param int $errorno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     */
    public function errorHandler(int $errorno, string $errstr, string $errfile, int $errline): void {
        if (error_reporting() & $errorno) {
            if ($errorno === E_ERROR) {
                echo 'An error has occurred. Please check your PHP log file.';
            } elseif ((bool) Config::getVar('debug', 'display_errors')) {
                if (!headers_sent()) {
                    echo $this->buildErrorMessage($errorno, $errstr, $errfile, $errline) . "<br/>\n";
                }
            }
            error_log($this->buildErrorMessage($errorno, $errstr, $errfile, $errline), 0);
        }
    }

    /**
     * Auxiliary function to errorHandler that returns a formatted error message.
     * @param int $errorno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return string
     */
    public function buildErrorMessage(int $errorno, string $errstr, string $errfile, int $errline): string {
        $message = [];
        $errorType = [
            E_ERROR             => 'ERROR',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSING ERROR',
            E_NOTICE            => 'NOTICE',
            E_CORE_ERROR        => 'CORE ERROR',
            E_CORE_WARNING      => 'CORE WARNING',
            E_COMPILE_ERROR     => 'COMPILE ERROR',
            E_COMPILE_WARNING   => 'COMPILE WARNING',
            E_USER_ERROR        => 'USER ERROR',
            E_USER_WARNING      => 'USER WARNING',
            E_USER_NOTICE       => 'USER NOTICE',
        ];

        $type = $errorType[$errorno] ?? 'CAUGHT EXCEPTION';

        // Return abridged message if strict error or notice
        $shortErrors = E_NOTICE;
        if (defined('E_DEPRECATED')) $shortErrors |= E_DEPRECATED;
        
        if ($errorno & $shortErrors) {
            return $type . ': ' . $errstr . ' (' . $errfile . ':' . $errline . ')';
        }

        $message[] = $this->getName() . ' has produced an error';
        $message[] = '  Message: ' . $type . ': ' . $errstr;
        $message[] = '  In file: ' . $errfile;
        $message[] = '  At line: ' . $errline;
        $message[] = '  Stacktrace: ';

        if ((bool) Config::getVar('debug', 'show_stacktrace')) {
            $trace = debug_backtrace();
            array_shift($trace);
            foreach ($trace as $bt) {
                $args = '';
                if (isset($bt['args'])) foreach ($bt['args'] as $a) {
                    if (!empty($args)) {
                        $args .= ', ';
                    }
                    switch (gettype($a)) {
                        case 'integer':
                        case 'double':
                            $args .= $a;
                            break;
                        case 'string':
                            $a = htmlspecialchars((string) $a);
                            $args .= "\"$a\"";
                            break;
                        case 'array':
                            $args .= 'Array('.count($a).')';
                            break;
                        case 'object':
                            $args .= 'Object('.get_class($a).')';
                            break;
                        case 'resource':
                            $args .= 'Resource()';
                            break;
                        case 'boolean':
                            $args .= $a ? 'True' : 'False';
                            break;
                        case 'NULL':
                            $args .= 'Null';
                            break;
                        default:
                            $args .= 'Unknown';
                    }
                }
                $class = $bt['class'] ?? '';
                $type = $bt['type'] ?? '';
                $function = $bt['function'] ?? '';
                $file = $bt['file'] ?? '(unknown)';
                $line = $bt['line'] ?? '(unknown)';

                $message[] = "   File: {$file} line {$line}";
                $message[] = "     Function: {$class}{$type}{$function}($args)";
            }
        }

        static $dbServerInfo;
        if (!isset($dbServerInfo) && (bool) Config::getVar('general', 'installed')) {
            $conn = DBConnection::getInstance();
            if ($conn->isConnected()) {
                $dbconn = $conn->getDBConn();
                $dbServerInfo = $dbconn->ServerInfo();
            }
        }

        $message[] = "  Server info:";
        $message[] = "   OS: " . Core::serverPHPOS();
        $message[] = "   PHP Version: " . Core::serverPHPVersion();
        $message[] = "   Apache Version: " . (function_exists('apache_get_version') ? apache_get_version() : 'N/A');
        $message[] = "   DB Driver: " . Config::getVar('database', 'driver');
        if (isset($dbServerInfo)) {
            $message[] = "   DB server version: " . (empty($dbServerInfo['description']) ? $dbServerInfo['version'] : $dbServerInfo['description']);
        }

        return implode("\n", $message);
    }

    /**
     * Send a flash notification to the current user interface.
     * @param string $message The localized message to display
     * @param string $type 'success', 'warning', 'error', 'info'
     * @param bool $blocked If true, shows a modal overlay instead of a toast
     * @return void
     */
    public static function notifyUser(string $message, string $type = 'success', bool $blocked = false): void {
        $request = self::getRequest();
        $user = $request->getUser();

        if ($user instanceof User) {
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            
            switch ($type) {
                case 'error':
                    $notificationType = NOTIFICATION_TYPE_ERROR;
                    break;
                case 'warning':
                    $notificationType = NOTIFICATION_TYPE_WARNING;
                    break;
                case 'info':
                    $notificationType = NOTIFICATION_TYPE_INFO;
                    break;
                default:
                    $notificationType = NOTIFICATION_TYPE_SUCCESS;
            }

            $notificationManager->createTrivialNotification(
                $user->getId(),
                $notificationType,
                ['contents' => $message]
            );
        }
    }

    /**
     * Define a constant so that it can be exposed to the JS front-end.
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function defineExposedConstant(string $name, $value): void {
        if (!defined($name)) {
            define($name, $value);
        }
        assert((bool) preg_match('/^[a-zA-Z_]+$/', $name));
        
        $constants =& self::getExposedConstants();
        $constants[$name] = $value;
    }

    /**
     * Get an associative array of defined constants that should be exposed
     * to the JS front-end.
     * Returns REFERENCE to support defineExposedConstant modification.
     * @return array
     */
    public static function &getExposedConstants(): array {
        static $exposedConstants = [];
        return $exposedConstants;
    }

    /**
     * Get an array of locale keys that define strings that should be made available to
     * JavaScript classes in the JS front-end.
     * @return array<string>
     */
    public function getJSLocaleKeys(): array {
        return ['form.dataHasChanged'];
    }
}

/**
 * Helper function outside class
 * @see PKPApplication::defineExposedConstant
 * @param string $name
 * @param mixed $value
 * @return void
 */
function define_exposed(string $name, $value): void {
    PKPApplication::defineExposedConstant($name, $value);
}
?>