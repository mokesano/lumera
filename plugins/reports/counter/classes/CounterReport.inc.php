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
 * @brief A COUNTER report, base class
 */
require_once(dirname(dirname(__FILE__)).'/classes/COUNTER/COUNTER.php');

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
     * @var string $_release A COUNTER release number
     */
    protected string $_release;

    /**
     * @var array $_errors An array of accumulated Exceptions
     */
    protected array $_errors = [];
    
    /**
     * Constructor
     * @param string $release
     */
    public function __construct($release) {
        $this->_release = $release;
    }

    /**
     * [SHIM] Backward Compatibility
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
     * Get the COUNTER Release
     * @return string
     */
    public function getRelease() {
        return $this->_release;
    }

    /**
     * Get the report code
     * @return string
     */
    public function getCode() {
        return substr(get_class($this), strlen(COUNTER_CLASS_PREFIX));
    }

    /**
     * Get the COUNTER metric type for an Statistics file type
     * @param string $filetype
     * @return string
     */
    public function getKeyForFiletype($filetype) {
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
     * Abstract method must be implemented in the child class
     * Get the report title
     * @return string
     */
    public function getTitle() {
        assert(false);
        return '';
    }

    /*
     * Convert an OJS metrics request to COUNTER ReportItems
     * Abstract method must be implemented by subclass
     * @param string|array $columns column (aggregation level) selection
     * @param array $filters report-level filter selection
     * @param array $orderBy order criteria
     * @param null|DBResultRange $range paging specification
     * @see ReportPlugin::getMetrics for more details on parameters
     * @return array COUNTER\ReportItem array
     */
    public function getReportItems($columns = [], $filters = [], $orderBy = [], $range = null) {
        assert(false);
        return [];
    }

    /**
     * Get an array of errors
     * @return array of Exceptions
     */
    public function getErrors() {
        return $this->_errors ? $this->_errors : [];
    }

    /**
     * Set an errors condition; Proper Exception handling is deferred until the OJS 3.0 Release
     * @param Exception $error
     */
    public function setError($error) {
        if (!$this->_errors) {
            $this->_errors = [];
        }
        array_push($this->_errors, $error);
    }

    /**
     * Ensure that the $filters do not exceed the current Context
     * @param array $filters
     * @return array
     */
    protected function filterForContext($filters) {
        $request = PKPApplication::getRequest();
        $journal = $request->getJournal();
        $journalId = $journal ? $journal->getJournalId() : '';
        // If the request context is at the journal level, the dimension context id must be that same journal id
        if ($journalId) {
            if (isset($filters[STATISTICS_DIMENSION_CONTEXT_ID]) && $filters[STATISTICS_DIMENSION_CONTEXT_ID] != $journalId) {
                // @phpstan-ignore-next-line
                $this->setError(new Exception(__('plugins.reports.counter.generic.exception.filter'), COUNTER_EXCEPTION_WARNING | COUNTER_EXCEPTION_BAD_FILTERS));
            }
            $filters[STATISTICS_DIMENSION_CONTEXT_ID] = $journalId;
        }
        return $filters;
    }

    /**
     * Given a Year-Month period and array of COUNTER\PerformanceCounters, create a COUNTER\Metric
     * @param string $period Date in Ym format
     * @param array $counters COUNTER\PerformanceCounter array
     * @return COUNTER\Metric|array
     */
    protected function createMetricByMonth($period, $counters) {
        $metric = [];
        try {
            $metric = new COUNTER\Metric(
                // Date range for JR1 is beginning of the month to end of the month
                new COUNTER\DateRange(
                    DateTime::createFromFormat('Ymd His', $period.'01 000000'),
                    DateTime::createFromFormat('Ymd His', $period.date('t', strtotime(substr($period, 0, 4).'-'.substr($period, 4).'-01')).' 235959')
                ),
                'Requests',
                $counters
            );
        } catch (Exception $e) {
            $this->setError($e, COUNTER_EXCEPTION_ERROR | COUNTER_EXCEPTION_INTERNAL);
        }
        return $metric;
    }

    /**
     * Construct a Reports result containing the provided performance metrics
     * @param array $reportItems COUNTER\ReportItem
     * @return string|null xml
     */
    public function createXML($reportItems) {
        $errors = $this->getErrors();
        $fatal = false;
        foreach ($errors as $error) {
            if ($error->getCode() & COUNTER_EXCEPTION_ERROR) {
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
                            $this->getVendorID(),
                            $this->getVendorName(),
                            $this->getVendorContacts(),
                            $this->getVendorWebsiteUrl(),
                            $this->getVendorLogoUrl()
                        )
                    )
                );
            } catch (Exception $e) {
                $this->setError($e, COUNTER_EXCEPTION_ERROR | COUNTER_EXCEPTION_INTERNAL);
            }
            if (isset($report)) {
                return (string) $report;
            }
        }
        return null;
    }

    /**
     * Get the Vendor Id
     * @return string
     */
    public function getVendorId() {
        return (string) $this->_getVendorComponent('id');
    }

    /**
     * Get the Vendor Name
     * @return string
     */
    public function getVendorName() {
        return (string) $this->_getVendorComponent('name');
    }

    /**
     * Get the Vendor Contacts
     * @return array|COUNTER\Contact
     */
    public function getVendorContacts() {
        return $this->_getVendorComponent('contacts');
    }

    /**
     * Get the Vendor Website URL
     * @return string
     */
    public function getVendorWebsiteUrl() {
        return (string) $this->_getVendorComponent('website');
    }

    /**
     * Get the Vendor Logo URL
     * @return string
     */
    public function getVendorLogoUrl() {
        return (string) $this->_getVendorComponent('logo');
    }

    /**
     * Get the Vendor Componet by key
     * @param string $key
     * @return mixed
     */
    public function _getVendorComponent($key) {
        $request = PKPApplication::getRequest();
        $site = $request->getSite();
        switch ($key) {
            case 'name':
                return $site->getLocalizedTitle();
            case 'id':
                return $request->getBaseUrl();
            case 'contacts':
                try {
                    $contact = new COUNTER\Contact($site->getLocalizedContactName(), $site->getLocalizedContactEmail());
                } catch (Exception $e) {
                    $this->setError($e);
                    $contact = [];
                }
                return $contact;
            case 'website':
                return $request->getBaseUrl();
            case 'logo':
                return '';
            default:
                return null;
        }
    }

}
?>