<?php
declare(strict_types=1);

/**
 * @file plugins/reports/subscriptions/SubscriptionReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionReportPlugin
 * @ingroup plugins_reports_subscription
 *
 * @brief Subscription report plugin
 */

import('classes.plugins.ReportPlugin');

class SubscriptionReportPlugin extends ReportPlugin {
    
    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path The path the plugin was found in
     * @param int|null $mainContextId
     * @return bool True if plugin initialized successfully; if false, the plugin will not be registered.
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'SubscriptionReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string display name of plugin
     */
    public function getDisplayName(): string {
        return __('plugins.reports.subscriptions.displayName');
    }

    /**
     * Get the description text for this plugin.
     * @return string description text for this plugin
     */
    public function getDescription(): string {
        return __('plugins.reports.subscriptions.description');
    }

    /**
     * Generate the subscription report and write CSV contents to file
     * @param array $args Request arguments 
     * @param PKPRequest $request Request object
     */
    public function display($args, $request) {
        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
        $countryDao = DAORegistry::getDAO('CountryDAO'); /* @var $countryDao CountryDAO */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO'); /* @var $subscriptionTypeDao SubscriptionTypeDAO */
        $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO'); /* @var $individualSubscriptionDao IndividualSubscriptionDAO */
        $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO'); /* @var $institutionalSubscriptionDao InstitutionalSubscriptionDAO */

        header('content-type: text/comma-separated-values');
        header('content-disposition: attachment; filename=subscriptions-' . date('Ymd') . '.csv');
        $fp = fopen('php://output', 'wt');

        // Columns for individual subscriptions
        $columns = [__('subscriptionManager.individualSubscriptions')];
        PKPString::fputcsv($fp, array_values($columns));

        $columnsCommon = [
            'subscription_id' => __('common.id'),
            'status' => __('subscriptions.status'),
            'type' => __('common.type'),
            'format' => __('subscriptionTypes.format'),
            'date_start' => __('manager.subscriptions.dateStart'),
            'date_end' => __('manager.subscriptions.dateEnd'),
            'membership' => __('manager.subscriptions.membership'),
            'reference_number' => __('manager.subscriptions.referenceNumber'),
            'notes' => __('common.notes')
        ];

        $columnsIndividual = [
            'name' => __('user.name'),
            'mailing_address' => __('common.mailingAddress'),
            'country' => __('common.country'),
            'email' => __('user.email'),
            'phone' => __('user.phone'),
            'fax' => __('user.fax')
        ];

        $columns = array_merge($columnsCommon, $columnsIndividual);

        // Write out individual subscription column headings to file
        PKPString::fputcsv($fp, array_values($columns));

        // Iterate over individual subscriptions and write out each to file
        $individualSubscriptions = $individualSubscriptionDao->getSubscriptionsByJournalId($journalId);
        while ($subscription = $individualSubscriptions->next()) {
            $user = $userDao->getUser($subscription->getUserId());
            $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());

            foreach ($columns as $index => $junk) {
                switch ($index) {
                    case 'subscription_id':
                        $columns[$index] = $subscription->getId();
                        break;
                    case 'status':
                        $columns[$index] = $subscription->getStatusString();
                        break;
                    case 'type':
                        $columns[$index] = $subscription->getSubscriptionTypeSummaryString();
                        break;
                    case 'format':
                        $columns[$index] = __($subscriptionType->getFormatString());
                        break;
                    case 'date_start':
                        $columns[$index] = $subscription->getDateStart();
                        break;
                    case 'date_end':
                        $columns[$index] = $subscription->getDateEnd();
                        break;
                    case 'membership':
                        $columns[$index] = $subscription->getMembership();
                        break;
                    case 'reference_number':
                        $columns[$index] = $subscription->getReferenceNumber();
                        break;
                    case 'notes':
                        $columns[$index] = $this->_html2text($subscription->getNotes());
                        break;
                    case 'name':
                        $columns[$index] = $user->getFullName();
                        break;
                    case 'mailing_address':
                        $columns[$index] = $this->_html2text($user->getMailingAddress());
                        break;
                    case 'country':
                        $columns[$index] = $countryDao->getCountry($user->getCountry());
                        break;
                    case 'email':
                        $columns[$index] = $user->getEmail();
                        break;
                    case 'phone':
                        $columns[$index] = $user->getPhone();
                        break;
                    case 'fax':
                        $columns[$index] = $user->getFax();
                        break;
                    default:
                        $columns[$index] = '';
                }
            }

            PKPString::fputcsv($fp, $columns);
        }

        // Columns for institutional subscriptions
        $columns = [''];
        PKPString::fputcsv($fp, array_values($columns));

        $columns = [__('subscriptionManager.institutionalSubscriptions')];
        PKPString::fputcsv($fp, array_values($columns));

        $columnsInstitution = [
            'institution_name' => __('manager.subscriptions.institutionName'),
            'institution_mailing_address' => __('plugins.reports.subscriptions.institutionMailingAddress'),
            'domain' => __('manager.subscriptions.domain'),
            'ip_ranges' => __('plugins.reports.subscriptions.ipRanges'),
            'contact' => __('manager.subscriptions.contact'),
            'mailing_address' => __('common.mailingAddress'),
            'country' => __('common.country'),
            'email' => __('user.email'),
            'phone' => __('user.phone'),
            'fax' => __('user.fax')
        ];

        $columns = array_merge($columnsCommon, $columnsInstitution);

        // Write out institutional subscription column headings to file
        PKPString::fputcsv($fp, array_values($columns));

        // Iterate over institutional subscriptions and write out each to file
        $institutionalSubscriptions = $institutionalSubscriptionDao->getSubscriptionsByJournalId($journalId);
        while ($subscription = $institutionalSubscriptions->next()) {
            $user = $userDao->getUser($subscription->getUserId());
            $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());

            foreach ($columns as $index => $junk) {
                switch ($index) {
                    case 'subscription_id':
                        $columns[$index] = $subscription->getId();
                        break;
                    case 'status':
                        $columns[$index] = $subscription->getStatusString();
                        break;
                    case 'type':
                        $columns[$index] = $subscription->getSubscriptionTypeSummaryString();
                        break;
                    case 'format':
                        $columns[$index] = __($subscriptionType->getFormatString());
                        break;
                    case 'date_start':
                        $columns[$index] = $subscription->getDateStart();
                        break;
                    case 'date_end':
                        $columns[$index] = $subscription->getDateEnd();
                        break;
                    case 'membership':
                        $columns[$index] = $subscription->getMembership();
                        break;
                    case 'reference_number':
                        $columns[$index] = $subscription->getReferenceNumber();
                        break;
                    case 'notes':
                        $columns[$index] = $this->_html2text($subscription->getNotes());
                        break;
                    case 'institution_name':
                        $columns[$index] = $subscription->getInstitutionName();
                        break;
                    case 'institution_mailing_address':
                        $columns[$index] = $this->_html2text($subscription->getInstitutionMailingAddress());
                        break;
                    case 'domain':
                        $columns[$index] = $subscription->getDomain();
                        break;
                    case 'ip_ranges':
                        $columns[$index] = $this->_formatIPRanges($subscription->getIPRanges());
                        break;
                    case 'contact':
                        $columns[$index] = $user->getFullName();
                        break;
                    case 'mailing_address':
                        $columns[$index] = $this->_html2text($user->getMailingAddress());
                        break;
                    case 'country':
                        $columns[$index] = $countryDao->getCountry($user->getCountry());
                        break;
                    case 'email':
                        $columns[$index] = $user->getEmail();
                        break;
                    case 'phone':
                        $columns[$index] = $user->getPhone();
                        break;
                    case 'fax':
                        $columns[$index] = $user->getFax();
                        break;
                    default:
                        $columns[$index] = '';
                }
            }

            PKPString::fputcsv($fp, $columns);
        }

        fclose($fp);
    }

    /**
     * Replace HTML "newline" tags (p, li, br) with line feeds. Strip all other tags.
     * @param string|null $html Input HTML string
     * @return string Text with replaced and stripped HTML tags
     */
    private function _html2text($html) {
        $html = (string) $html; // Safety cast for PHP 8.1+
        $html = PKPString::regexp_replace('/<[\/]?p>/', chr(13) . chr(10), $html);
        $html = PKPString::regexp_replace('/<li>/', '&bull; ', $html);
        $html = PKPString::regexp_replace('/<\/li>/', chr(13) . chr(10), $html);
        $html = PKPString::regexp_replace('/<br[ ]?[\/]?>/', chr(13) . chr(10), $html);
        $html = PKPString::html2utf(strip_tags($html));
        return $html;
    }

    /**
     * Pretty format IP ranges, one per line via line feeds.
     * @param array $ipRanges IP ranges
     * @return string Text of IP ranges formatted with newlines
     */
    private function _formatIPRanges(array $ipRanges) {
        $numRanges = count($ipRanges);
        $ipRangesString = '';

        for($i=0; $i<$numRanges; $i++) {
            $ipRangesString .= $ipRanges[$i];
            if ( $i+1 < $numRanges) $ipRangesString .= chr(13) . chr(10);
        }

        return $ipRangesString;
    }
}

?>