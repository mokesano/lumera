<?php
declare(strict_types=1);

/**
 * @file plugins/reports/counter/classes/CounterReport.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class CounterReport
 * @ingroup plugins_reports_counter
 *
 * @brief A COUNTER report, base class.
 */

require_once __DIR__ . '/../../classes/COUNTER/COUNTER.php';

define('COUNTER_EXCEPTION_WARNING', 0);
define('COUNTER_EXCEPTION_ERROR', 1);
define('COUNTER_EXCEPTION_PARTIAL_DATA', 4);
define('COUNTER_EXCEPTION_NO_DATA', 8);
define('COUNTER_EXCEPTION_BAD_COLUMNS', 16);
define('COUNTER_EXCEPTION_BAD_FILTERS', 32);
define('COUNTER_EXCEPTION_BAD_ORDERBY', 64);
define('COUNTER_EXCEPTION_BAD_RANGE', 128);
define('COUNTER_EXCEPTION_INTERNAL', 256);

define('COUNTER_CLASS_PREFIX', 'CounterReport');

// COUNTER as of yet is not internationalized and requires English constants
define('COUNTER_LITERAL_ARTICLE', 'Article');
define('COUNTER_LITERAL_JOURNAL', 'Journal');
define('COUNTER_LITERAL_PROPRIETARY', 'Proprietary');

class CounterReport {

    /**
     * @var string A COUNTER release number.
     */
    protected string $_release;

    /**
     * @var array An array of accumulated Exceptions.
     */
    protected array $_errors = [];
    
    /**
     * Constructor.
     * @param string $release
     */
    public function __construct($release) {
        $this->_release = (string) $release;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $release
     */
    public function CounterReport($release) {
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
     * Get the COUNTER Release.
     * @return string
     */
    public function getRelease(): string {
        return $this->_release;
    }

    /**
     * Get the report code.
     * @return string
     */
    public function getCode(): string {
        return substr(get_class($this), strlen(COUNTER_CLASS_PREFIX));
    }

    /**
     * Get the COUNTER metric type for a Statistics file type.
     * @param string $filetype
     * @return string
     */
    public function getKeyForFiletype($filetype): string {
        $metricTypeKey = 'other';
        switch ($filetype) {
            case STATISTICS_FILE_TYPE_HTML:
                $metricTypeKey = 'ft_html';
                break;
            case STATISTICS_FILE_TYPE_PDF:
                $metricTypeKey = 'ft_pdf';
                break;
            case STATISTICS_FILE_TYPE_OTHER:
            default:
                $metricTypeKey = 'other';
        }
        return $metricTypeKey;
    }

    /**
     * Abstract method must be implemented in the child class.
     * Get the report title.
     * @return string
     */
    public function getTitle(): string {
        throw new \BadMethodCallException('Must be implemented by subclass.');
    }

    /**
     * Convert an OJS metrics request to COUNTER ReportItems.
     * Abstract method must be implemented by subclass.
     * @param string|array $columns
     * @param array $filters
     * @param array $orderBy
     * @param mixed $range
     * @see ReportPlugin::getMetrics for more details on parameters
     * @return array
     */
    public function getReportItems($columns = [], $filters = [], $orderBy = [], $range = null) {
        throw new \BadMethodCallException('Must be implemented by subclass.');
    }

    /**
     * Get an array of errors.
     * @return array of Exceptions
     */
    public function getErrors(): array {
        return is_array($this->_errors) ? $this->_errors : [];
    }

    /**
     * Set an errors condition.
     * @param \Exception $error
     */
    public function setError($error): void {
        if (!is_array($this->_errors)) {
            $this->_errors = [];
        }
        $this->_errors[] = $error;
    }

    /**
     * Ensure that the $filters do not exceed the current Context.
     * @param array $filters
     * @return array
     */
    protected function filterForContext(array $filters): array {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;

        if ($journalId > 0) {
            if (isset($filters[STATISTICS_DIMENSION_CONTEXT_ID]) && (int) $filters[STATISTICS_DIMENSION_CONTEXT_ID] !== $journalId) {
                $this->setError(new \Exception(__('plugins.reports.counter.generic.exception.filter'), COUNTER_EXCEPTION_WARNING | COUNTER_EXCEPTION_BAD_FILTERS));
            }
            $filters[STATISTICS_DIMENSION_CONTEXT_ID] = $journalId;
        }
        return $filters;
    }

    /**
     * Given a Year-Month period and array of COUNTER\PerformanceCounters, create a COUNTER\Metric.
     * @param string $period
     * @param array $counters
     * @return COUNTER\Metric|array
     */
    protected function createMetricByMonth(string $period, array $counters) {
        $metric = [];
        try {
            $startDate = \DateTime::createFromFormat('Ymd His', $period . '01 000000');
            $endDateStr = $period . date('t', strtotime(substr($period, 0, 4) . '-' . substr($period, 4) . '-01')) . ' 235959';
            $endDate = \DateTime::createFromFormat('Ymd His', $endDateStr);
            
            if ($startDate && $endDate) {
                $metric = new COUNTER\Metric(
                    // Date range for JR1 is beginning of the month to end of the month
                    new COUNTER\DateRange($startDate, $endDate),
                    'Requests',
                    $counters
                );
            }
        } catch (\Exception $e) {
            $this->setError($e);
        }
        return $metric;
    }

    /**
     * Construct a Reports result containing the provided performance metrics.
     * @param array $reportItems
     * @return string|null xml
     */
    public function createXML($reportItems) {
        $errors = $this->getErrors();
        $fatal = false;
        
        foreach ($errors as $error) {
            if ($error instanceof \Exception && ($error->getCode() & COUNTER_EXCEPTION_ERROR)) {
                $fatal = true;
            }
        }
        
        if (!$fatal) {
            try {
                $report = new COUNTER\Reports(
                    new COUNTER\Report(
                        PKPString::generateUUID(),
                        $this->getRelease(),
                        $this->getCode(),
                        $this->getTitle(),
                        new COUNTER\Customer(
                            '0', // customer id is unused
                            $reportItems,
                            __('plugins.reports.counter.allCustomers')
                        ),
                        new COUNTER\Vendor(
                            $this->getVendorId(),
                            $this->getVendorName(),
                            $this->getVendorContacts(),
                            $this->getVendorWebsiteUrl(),
                            $this->getVendorLogoUrl()
                        )
                    )
                );
            } catch (\Exception $e) {
                $this->setError($e);
            }
            
            if (isset($report)) {
                return (string) $report;
            }
        }
        return null;
    }

    /**
     * Get the Vendor Id.
     * @return string
     */
    public function getVendorId(): string {
        return (string) $this->_getVendorComponent('id');
    }

    /**
     * Get the Vendor Name.
     * @return string
     */
    public function getVendorName(): string {
        return (string) $this->_getVendorComponent('name');
    }

    /**
     * Get the Vendor Contacts.
     * @return array|COUNTER\Contact
     */
    public function getVendorContacts() {
        return $this->_getVendorComponent('contacts');
    }

    /**
     * Get the Vendor Website URL.
     * @return string
     */
    public function getVendorWebsiteUrl(): string {
        return (string) $this->_getVendorComponent('website');
    }

    /**
     * Get the Vendor Logo URL.
     * @return string
     */
    public function getVendorLogoUrl(): string {
        return (string) $this->_getVendorComponent('logo');
    }

    /**
     * Get the Vendor Component by key.
     * @param string $key
     * @return mixed
     */
    public function _getVendorComponent(string $key) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $site = $request->getSite();
        
        switch ($key) {
            case 'name':
                return (string) $site->getLocalizedTitle();
            case 'id':
                return (string) $request->getBaseUrl();
            case 'contacts':
                try {
                    return new COUNTER\Contact((string) $site->getLocalizedContactName(), (string) $site->getLocalizedContactEmail());
                } catch (\Exception $e) {
                    $this->setError($e);
                    return [];
                }
            case 'website':
                return (string) $request->getBaseUrl();
            case 'logo':
                return '';
            default:
                return null;
        }
    }

}
?>