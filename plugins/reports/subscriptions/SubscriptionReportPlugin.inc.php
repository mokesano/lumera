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
 * @brief Subscription report plugin.
 */

import('classes.plugins.ReportPlugin');

class SubscriptionReportPlugin extends ReportPlugin {
    
    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within its category.
     * @return string
     */
    public function getName(): string {
        return 'SubscriptionReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.subscriptions.displayName');
    }

    /**
     * Get the description text for this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.reports.subscriptions.description');
    }

    /**
     * Generate the subscription report and write CSV contents to file.
     * @param array $args 
     * @param mixed $request
     */
    public function display($args, $request) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        if (!$journal) {
            return;
        }
        $journalId = (int) $journal->getId();

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
        $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
        $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');

        header('Content-Type: text/comma-separated-values');
        header('Content-Disposition: attachment; filename=subscriptions-' . date('Ymd') . '.csv');
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
            $user = $userDao->getUser((int) $subscription->getUserId());
            $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());

            foreach ($columns as $index => $junk) {
                switch ($index) {
                    case 'subscription_id':
                        $columns[$index] = (string) $subscription->getId();
                        break;
                    case 'status':
                        $columns[$index] = (string) $subscription->getStatusString();
                        break;
                    case 'type':
                        $columns[$index] = (string) $subscription->getSubscriptionTypeSummaryString();
                        break;
                    case 'format':
                        $columns[$index] = $subscriptionType ? __($subscriptionType->getFormatString()) : '';
                        break;
                    case 'date_start':
                        $columns[$index] = (string) $subscription->getDateStart();
                        break;
                    case 'date_end':
                        $columns[$index] = (string) $subscription->getDateEnd();
                        break;
                    case 'membership':
                        $columns[$index] = (string) $subscription->getMembership();
                        break;
                    case 'reference_number':
                        $columns[$index] = (string) $subscription->getReferenceNumber();
                        break;
                    case 'notes':
                        $columns[$index] = $this->_html2text($subscription->getNotes());
                        break;
                    case 'name':
                        $columns[$index] = $user ? (string) $user->getFullName() : '';
                        break;
                    case 'mailing_address':
                        $columns[$index] = $user ? $this->_html2text($user->getMailingAddress()) : '';
                        break;
                    case 'country':
                        $countryObj = $user ? $countryDao->getCountry($user->getCountry()) : null;
                        $columns[$index] = $countryObj ? (is_object($countryObj) ? (string) $countryObj->getCountry() : (string) $countryObj) : '';
                        break;
                    case 'email':
                        $columns[$index] = $user ? (string) $user->getEmail() : '';
                        break;
                    case 'phone':
                        $columns[$index] = $user ? (string) $user->getPhone() : '';
                        break;
                    case 'fax':
                        $columns[$index] = $user ? (string) $user->getFax() : '';
                        break;
                    default:
                        $columns[$index] = '';
                }
            }

            PKPString::fputcsv($fp, array_values($columns));
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
            $user = $userDao->getUser((int) $subscription->getUserId());
            $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());

            foreach ($columns as $index => $junk) {
                switch ($index) {
                    case 'subscription_id':
                        $columns[$index] = (string) $subscription->getId();
                        break;
                    case 'status':
                        $columns[$index] = (string) $subscription->getStatusString();
                        break;
                    case 'type':
                        $columns[$index] = (string) $subscription->getSubscriptionTypeSummaryString();
                        break;
                    case 'format':
                        $columns[$index] = $subscriptionType ? __($subscriptionType->getFormatString()) : '';
                        break;
                    case 'date_start':
                        $columns[$index] = (string) $subscription->getDateStart();
                        break;
                    case 'date_end':
                        $columns[$index] = (string) $subscription->getDateEnd();
                        break;
                    case 'membership':
                        $columns[$index] = (string) $subscription->getMembership();
                        break;
                    case 'reference_number':
                        $columns[$index] = (string) $subscription->getReferenceNumber();
                        break;
                    case 'notes':
                        $columns[$index] = $this->_html2text($subscription->getNotes());
                        break;
                    case 'institution_name':
                        $columns[$index] = (string) $subscription->getInstitutionName();
                        break;
                    case 'institution_mailing_address':
                        $columns[$index] = $this->_html2text($subscription->getInstitutionMailingAddress());
                        break;
                    case 'domain':
                        $columns[$index] = (string) $subscription->getDomain();
                        break;
                    case 'ip_ranges':
                        $columns[$index] = $this->_formatIPRanges($subscription->getIPRanges());
                        break;
                    case 'contact':
                        $columns[$index] = $user ? (string) $user->getFullName() : '';
                        break;
                    case 'mailing_address':
                        $columns[$index] = $user ? $this->_html2text($user->getMailingAddress()) : '';
                        break;
                    case 'country':
                        $countryObj = $user ? $countryDao->getCountry($user->getCountry()) : null;
                        $columns[$index] = $countryObj ? (is_object($countryObj) ? (string) $countryObj->getCountry() : (string) $countryObj) : '';
                        break;
                    case 'email':
                        $columns[$index] = $user ? (string) $user->getEmail() : '';
                        break;
                    case 'phone':
                        $columns[$index] = $user ? (string) $user->getPhone() : '';
                        break;
                    case 'fax':
                        $columns[$index] = $user ? (string) $user->getFax() : '';
                        break;
                    default:
                        $columns[$index] = '';
                }
            }

            PKPString::fputcsv($fp, array_values($columns));
        }

        fclose($fp);
    }

    /**
     * Replace HTML "newline" tags (p, li, br) with line feeds. Strip all other tags.
     * @param mixed $html
     * @return string
     */
    private function _html2text($html): string {
        $html = (string) $html;
        $html = PKPString::regexp_replace('/<[\/]?p>/', chr(13) . chr(10), $html);
        $html = PKPString::regexp_replace('/<li>/', '&bull; ', $html);
        $html = PKPString::regexp_replace('/<\/li>/', chr(13) . chr(10), $html);
        $html = PKPString::regexp_replace('/<br[ ]?[\/]?>/', chr(13) . chr(10), $html);
        $html = PKPString::html2utf(strip_tags($html));
        return $html;
    }

    /**
     * Pretty format IP ranges, one per line via line feeds.
     * @param array $ipRanges
     * @return string
     */
    private function _formatIPRanges(array $ipRanges): string {
        $numRanges = count($ipRanges);
        $ipRangesString = '';

        for ($i = 0; $i < $numRanges; $i++) {
            $ipRangesString .= (string) $ipRanges[$i];
            if ($i + 1 < $numRanges) {
                $ipRangesString .= chr(13) . chr(10);
            }
        }

        return $ipRangesString;
    }

}
?>