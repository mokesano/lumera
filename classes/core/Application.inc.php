<?php
declare(strict_types=1);

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Application
 * @ingroup core
 * @see PKPApplication
 *
 * @brief Class describing this application.
 */

import('lib.pkp.classes.core.PKPApplication');
import('classes.statistics.StatisticsHelper');
import('classes.core.Request');

define('PHP_REQUIRED_VERSION', '7.4.0');

// --- IDENTITY CONSTANTS (ASSOC TYPES) ---
define('ASSOC_TYPE_JOURNAL',        0x0000100); // 256
define('ASSOC_TYPE_ARTICLE',        0x0000101); // 257
define('ASSOC_TYPE_ANNOUNCEMENT',   0x0000102); // 258

// [PERBAIKAN KONFLIK ID]
// Issue dibiarkan menggunakan ID asli OJS (0x103)
define('ASSOC_TYPE_ISSUE',          0x0000103); // 259

// Section digeser ke ID baru agar tidak bentrok dengan Issue.
// PENTING untuk rencana "Section sebagai Mini Jurnal".
define('ASSOC_TYPE_SECTION',        0x0000107); // 263

define('ASSOC_TYPE_GALLEY',         0x0000104);
define('ASSOC_TYPE_ISSUE_GALLEY',   0x0000105);
define('ASSOC_TYPE_SUPP_FILE',      0x0000106);

// [FITUR BARU] Research Topic (Publisher Level)
define('ASSOC_TYPE_RESEARCH_TOPIC', 0x0000108);

define('CONTEXT_JOURNAL', 1);

class Application extends PKPApplication {
    
    /** @var Application|null Instance singleton */
    private static ?Application $instance = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        // [GOLDEN BRIDGE] Simpan referensi 'this' ke instance statis.
        self::$instance = $this;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Application() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the singleton instance of the application.
     * @return Application
     */
    public static function get(): Application {
        if (self::$instance === null) {
            self::$instance = new Application();
        }
        return self::$instance;
    }

    /**
     * Get the application request implementation singleton.
     * [WIZDAM ARCHITECTURE] Covariance Override & Self-Healing Request
     * @return Request
     */
    public static function getRequest(): Request {
        // Panggil dari induknya (Registry)
        $request = parent::getRequest();

        // Jika request kosong atau tertinggal di fase "amnesia" (hanya PKPRequest dasar),
        // lakukan Upcasting/Healing DI SINI (Lapisan Aplikasi).
        if ($request === null || !($request instanceof Request)) {
            $request = new Request();
            Registry::set('request', $request);
        }

        return $request;
    }

    /**
     * Get the "context depth" of this application.
     * @return int
     */
    public function getContextDepth(): int {
        return 1;
    }

    /**
     * Get the list of contexts.
     * @return array
     */
    public static function getContextList(): array {
        return ['journal'];
    }
    
    /**
     * Get the symbolic name of this application
     * @return string
     */
    public function getName(): string {
        // [PERINGATAN] Jika ubah 'ojs2' maka ubah pula DB tabel 'versions'.
        // Jika diubah, Application::getCurrentVersion() gagal, dan TemplateManager crash.
        return 'ojs2'; 
    }

    /**
     * Get the locale key for the name of this application.
     * @return string
     */
    public function getNameKey(): string {
        // [PERINGATAN] Jika ubah 'ojs2' ubah pemanggilan nama aplikasi.
        // Menjadi common.wizdamEditionSystems
        return 'common.openJournalSystems';
    }

    /**
     * Get the URL to the XML descriptor for the current version of this application.
     * @return string
     */
    public function getVersionDescriptorUrl(): string {
        // [PERINGATAN] Jika ubah 'ojs2' siapkan url cek versi aplikasi.
        // Gunakan url wizdamEditin untuk versi fork Wizdam Fork Edition
        return 'http://pkp.sfu.ca/ojs/xml/ojs-version.xml';
    }

    /**
     * Get the map of DAOName => full.class.Path for this application.
     * @return array
     */
    public function getDAOMap(): array {
        return array_merge(parent::getDAOMap(), [
            'AnnouncementDAO' => 'classes.announcement.AnnouncementDAO',
            'AnnouncementTypeDAO' => 'classes.announcement.AnnouncementTypeDAO',
            'ArticleEmailLogDAO' => 'classes.article.log.ArticleEmailLogDAO',
            'ArticleEventLogDAO' => 'classes.article.log.ArticleEventLogDAO',
            'ArticleCommentDAO' => 'classes.article.ArticleCommentDAO',
            'ArticleDAO' => 'classes.article.ArticleDAO',
            'ArticleFileDAO' => 'classes.article.ArticleFileDAO',
            'ArticleGalleyDAO' => 'classes.article.ArticleGalleyDAO',
            'ArticleNoteDAO' => 'classes.article.ArticleNoteDAO',
            'ArticleSearchDAO' => 'classes.search.ArticleSearchDAO',
            'AuthorDAO' => 'classes.article.AuthorDAO',
            'AuthorSubmissionDAO' => 'classes.submission.author.AuthorSubmissionDAO',
            'CategoryDAO' => 'classes.journal.categories.CategoryDAO',
            'CommentDAO' => 'lib.pkp.classes.comment.CommentDAO',
            'CopyeditorSubmissionDAO' => 'classes.submission.copyeditor.CopyeditorSubmissionDAO',
            'EditAssignmentDAO' => 'classes.submission.editAssignment.EditAssignmentDAO',
            'EditorSubmissionDAO' => 'classes.submission.editor.EditorSubmissionDAO',
            'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
            'GiftDAO' => 'classes.gift.GiftDAO',
            'GroupDAO' => 'lib.pkp.classes.group.GroupDAO',
            'GroupMembershipDAO' => 'lib.pkp.classes.group.GroupMembershipDAO',
            'IndividualSubscriptionDAO' => 'classes.subscription.IndividualSubscriptionDAO',
            'InstitutionalSubscriptionDAO' => 'classes.subscription.InstitutionalSubscriptionDAO',
            'IssueDAO' => 'classes.issue.IssueDAO',
            'IssueGalleyDAO' => 'classes.issue.IssueGalleyDAO',
            'IssueFileDAO' => 'classes.issue.IssueFileDAO',
            'JournalDAO' => 'classes.journal.JournalDAO',
            'JournalSettingsDAO' => 'classes.journal.JournalSettingsDAO',
            'JournalStatisticsDAO' => 'classes.journal.JournalStatisticsDAO',
            'LayoutEditorSubmissionDAO' => 'classes.submission.layoutEditor.LayoutEditorSubmissionDAO',
            'MetricsDAO' => 'classes.statistics.MetricsDAO',
            'NoteDAO' => 'classes.note.NoteDAO',
            'OAIDAO' => 'classes.oai.ojs.OAIDAO',
            'OJSCompletedPaymentDAO' => 'classes.payment.ojs.OJSCompletedPaymentDAO',
            'PluginSettingsDAO' => 'classes.plugins.PluginSettingsDAO',
            'ProofreaderSubmissionDAO' => 'classes.submission.proofreader.ProofreaderSubmissionDAO',
            'PublishedArticleDAO' => 'classes.article.PublishedArticleDAO',
            'QueuedPaymentDAO' => 'lib.pkp.classes.payment.QueuedPaymentDAO',
            'ReviewAssignmentDAO' => 'classes.submission.reviewAssignment.ReviewAssignmentDAO',
            'ReviewerSubmissionDAO' => 'classes.submission.reviewer.ReviewerSubmissionDAO',
            'ReviewFormDAO' => 'lib.pkp.classes.reviewForm.ReviewFormDAO',
            'ReviewFormElementDAO' => 'lib.pkp.classes.reviewForm.ReviewFormElementDAO',
            'ReviewFormResponseDAO' => 'lib.pkp.classes.reviewForm.ReviewFormResponseDAO',
            'RoleDAO' => 'classes.security.RoleDAO',
            'RTDAO' => 'classes.rt.ojs.RTDAO',
            'ScheduledTaskDAO' => 'lib.pkp.classes.scheduledTask.ScheduledTaskDAO',
            'SectionDAO' => 'classes.journal.SectionDAO',
            'SectionEditorsDAO' => 'classes.journal.SectionEditorsDAO',
            'SectionEditorSubmissionDAO' => 'classes.submission.sectionEditor.SectionEditorSubmissionDAO',
            'SignoffDAO' => 'classes.signoff.SignoffDAO',
            'SubscriptionDAO' => 'classes.subscription.SubscriptionDAO',
            'SubscriptionTypeDAO' => 'classes.subscription.SubscriptionTypeDAO',
            'SuppFileDAO' => 'classes.article.SuppFileDAO',
            'UserDAO' => 'classes.user.UserDAO',
            'UserSettingsDAO' => 'classes.user.UserSettingsDAO'
        ]);
    }

    /**
     * Get the list of plugin categories for this application.
     * @return array
     */
    public function getPluginCategories(): array {
        return [
            'metadata',
            'auth',
            'blocks',
            'citationFormats',
            'citationLookup',
            'citationOutput',
            'citationParser',
            'gateways',
            'generic',
            'implicitAuth',
            'importexport',
            'oaiMetadataFormats',
            'paymethod',
            'pubIds',
            'reports',
            'themes'
        ];
    }

    /**
     * Instantiate the help object for this application.
     * @return object
     */
    public function instantiateHelp(): object {
        import('classes.help.Help');
        return new Help();
    }

    //
    // Statistics API
    //
    /**
     * Return all metric types supported by this application.
     * @param bool $withDisplayNames
     * @return array
     */
    public function getMetricTypes(bool $withDisplayNames = false): array {
        $reportPlugins = PluginRegistry::loadCategory('reports', true, CONTEXT_SITE);
        
        if (!is_array($reportPlugins)) return [];

        $metricTypes = [];
        foreach ($reportPlugins as $reportPlugin) { /* @var $reportPlugin ReportPlugin */
            $pluginMetricTypes = $reportPlugin->getMetricTypes();
            if ($withDisplayNames) {
                foreach ($pluginMetricTypes as $metricType) {
                    $metricTypes[$metricType] = $reportPlugin->getMetricDisplayType($metricType);
                }
            } else {
                $metricTypes = array_merge($metricTypes, $pluginMetricTypes);
            }
        }

        return $metricTypes;
    }

    /**
     * Returns the currently configured default metric type for this site.
     * @return string|null
     */
    public function getDefaultMetricType(): ?string {
        $request = $this->getRequest();
        $site = $request->getSite();
        
        if (!($site instanceof Site)) return null;
        $defaultMetricType = $site->getSetting('defaultMetricType');

        $availableMetrics = $this->getMetricTypes();
        if (empty($defaultMetricType)) {
            if (count($availableMetrics) === 1) {
                return $availableMetrics[0];
            } else {
                return null;
            }
        } else {
            if (!in_array($defaultMetricType, $availableMetrics)) return null;
        }
        
        return (string) $defaultMetricType;
    }

    /**
     * Main entry point for OJS statistics reports.
     * @param string|array|null $metricType
     * @param array|string $columns
     * @param array $filter
     * @param array $orderBy
     * @param mixed $range
     * @return array|null
     */
    public function getMetrics($metricType = null, $columns = [], array $filter = [], array $orderBy = [], $range = null): ?array {
        $journal = StatisticsHelper::getContext($filter);

        $defaultSiteMetricType = $this->getDefaultMetricType();
        $siteMetricTypes = $this->getMetricTypes();
        $metricType = StatisticsHelper::canonicalizeMetricTypes($metricType, $journal, $defaultSiteMetricType, $siteMetricTypes);
        
        if (!is_array($metricType)) return null;
        $metricTypeCount = count($metricType);

        if (is_scalar($columns)) $columns = [$columns];

        if ($metricTypeCount === 0) return null;
        
        if ($metricTypeCount > 1) {
            if (!in_array(STATISTICS_DIMENSION_METRIC_TYPE, $columns)) {
                array_push($columns, STATISTICS_DIMENSION_METRIC_TYPE);
            }
        }

        $contextId = ($journal instanceof Journal) ? $journal->getId() : CONTEXT_SITE;
        
        $reportPlugins = PluginRegistry::loadCategory('reports', true, $contextId);
        if (!is_array($reportPlugins)) return null;

        $report = [];
        foreach ($reportPlugins as $reportPlugin) {
            $availableMetrics = $reportPlugin->getMetricTypes();
            $availableMetrics = array_intersect($availableMetrics, $metricType);
            if (empty($availableMetrics)) continue;

            $partialReport = $reportPlugin->getMetrics($availableMetrics, $columns, $filter, $orderBy, $range);
            $report = array_merge($report, $partialReport);

            $metricType = array_diff($metricType, $availableMetrics);
        }

        if (count($metricType) > 0) return null;

        return $report;
    }

    /**
     * Return metric in the primary metric type for the passed associated object.
     * @param int $assocType
     * @param int $assocId
     * @return int
     */
    public function getPrimaryMetricByAssoc(int $assocType, int $assocId): int {
        $filter = [
            STATISTICS_DIMENSION_ASSOC_ID => $assocId,
            STATISTICS_DIMENSION_ASSOC_TYPE => $assocType
        ];

        $request = $this->getRequest();
        $journal = $request->getJournal();
        if ($journal) {
            $filter[STATISTICS_DIMENSION_CONTEXT_ID] = $journal->getId();
        }

        $metric = $this->getMetrics(null, [], $filter);
        if (is_array($metric) && !empty($metric)) {
            if (isset($metric[0][STATISTICS_METRIC]) && is_numeric($metric[0][STATISTICS_METRIC])) {
                return (int) $metric[0][STATISTICS_METRIC];
            }
        }

        return 0;
    }

    /**
     * Get a mapping of license URL to license locale key.
     * @return array
     */
    public static function getCCLicenseOptions(): array {
        return [
            'https://creativecommons.org/licenses/by-nc-nd/4.0' => 'submission.license.cc.by-nc-nd4',
            'https://creativecommons.org/licenses/by-nc/4.0' => 'submission.license.cc.by-nc4',
            'https://creativecommons.org/licenses/by-nc-sa/4.0' => 'submission.license.cc.by-nc-sa4',
            'https://creativecommons.org/licenses/by-nd/4.0' => 'submission.license.cc.by-nd4',
            'https://creativecommons.org/licenses/by/4.0' => 'submission.license.cc.by4',
            'https://creativecommons.org/licenses/by-sa/4.0' => 'submission.license.cc.by-sa4'
        ];
    }

    /**
     * Get the Creative Commons license badge associated with a given license URL.
     * @param string $ccLicenseURL
     * @return string|null
     */
    public static function getCCLicenseBadge(string $ccLicenseURL): ?string {
        $licenseKeyMap = [
            'http://creativecommons.org/licenses/by-nc-nd/4.0' => 'submission.license.cc.by-nc-nd4.footer',
            'http://creativecommons.org/licenses/by-nc/4.0' => 'submission.license.cc.by-nc4.footer',
            'http://creativecommons.org/licenses/by-nc-sa/4.0' => 'submission.license.cc.by-nc-sa4.footer',
            'http://creativecommons.org/licenses/by-nd/4.0' => 'submission.license.cc.by-nd4.footer',
            'http://creativecommons.org/licenses/by/4.0' => 'submission.license.cc.by4.footer',
            'http://creativecommons.org/licenses/by-sa/4.0' => 'submission.license.cc.by-sa4.footer',
            'https://creativecommons.org/licenses/by-nc-nd/4.0' => 'submission.license.cc.by-nc-nd4.footer',
            'https://creativecommons.org/licenses/by-nc/4.0' => 'submission.license.cc.by-nc4.footer',
            'https://creativecommons.org/licenses/by-nc-sa/4.0' => 'submission.license.cc.by-nc-sa4.footer',
            'https://creativecommons.org/licenses/by-nd/4.0' => 'submission.license.cc.by-nd4.footer',
            'https://creativecommons.org/licenses/by/4.0' => 'submission.license.cc.by4.footer',
            'https://creativecommons.org/licenses/by-sa/4.0' => 'submission.license.cc.by-sa4.footer'
        ];
        
        if (isset($licenseKeyMap[$ccLicenseURL])) {
            return __($licenseKeyMap[$ccLicenseURL]);
        }
        
        return null;
    }

    /**
     * Get the payment manager for this application.
     * [WIZDAM] Re-implemented: method ini dihapus saat modernisasi
     * tapi masih dipanggil oleh NotificationMailingListForm dan komponen lain.
     * @param $journal Journal|null objek journal aktif
     * @return OJSPaymentManager
     */
    public static function getPaymentManager($journal = null) {
        import('classes.payment.ojs.OJSPaymentManager');
        
        // Resolve journal jika tidak disuplai
        if ($journal === null) {
            $request = Registry::get('request');
            if ($request !== null) {
                $journal = $request->getJournal();
            }
        }
        
        return new OJSPaymentManager($journal);
    }
}
?>