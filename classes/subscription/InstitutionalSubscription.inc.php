<?php

/**
 * @defgroup subscription
 */
 
/**
 * @file classes/subscription/InstitutionalSubscription.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InstitutionalSubscription
 * @ingroup subscription 
 * @see InstitutionalSubscriptionDAO
 *
 * @brief Basic class describing an institutional subscription.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('classes.subscription.Subscription');

define('SUBSCRIPTION_IP_RANGE_RANGE', '-');
define('SUBSCRIPTION_IP_RANGE_WILDCARD', '*');


class InstitutionalSubscription extends Subscription {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InstitutionalSubscription() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor InstitutionalSubscription(). Please refactor to use __construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the institution name of the institutionalSubscription.
     * @return string 
     */
    public function getInstitutionName() {
        return $this->getData('institutionName');
    }

    /**
     * Set the institution name of the institutionalSubscription.
     * @param $institutionName string
     */
    public function setInstitutionName($institutionName) {
        return $this->setData('institutionName', $institutionName);
    }

    /**
     * Get the mailing address of the institutionalSubscription.
     * @return string 
     */
    public function getInstitutionMailingAddress() {
        return $this->getData('mailingAddress');
    }

    /**
     * Set the mailing address of the institutionalSubscription.
     * @param $mailingAddress string
     */
    public function setInstitutionMailingAddress($mailingAddress) {
        return $this->setData('mailingAddress', $mailingAddress);
    }

    /**
     * Get institutionalSubscription domain string.
     * @return string
     */
    public function getDomain() {
        return $this->getData('domain');
    }

    /**
     * Set institutionalSubscription domain string.
     * @param $domain string
     */
    public function setDomain($domain) {
        return $this->setData('domain', $domain);
    }

    /**
     * Get institutionalSubscription ip ranges.
     * @return array 
     */
    public function getIPRanges() {
        return $this->getData('ipRanges');
    }

    /**
     * Get institutionalSubscription ip ranges string.
     * @return string
     */
    public function getIPRangesString() {
        $ipRanges = $this->getData('ipRanges');
        $numRanges = count($ipRanges);
        $ipRangesString = '';

        for($i=0; $i<$numRanges; $i++) {
            $ipRangesString .= $ipRanges[$i];
            // [MODERNISASI] Gunakan double quote "\n" agar terdeteksi sebagai newline
            if ( $i+1 < $numRanges) $ipRangesString .= "\n";
        }

        return $ipRangesString;
    }

    /**
     * Set institutionalSubscription ip ranges.
     * @param ipRanges array 
     */
    public function setIPRanges($ipRanges) {
        return $this->setData('ipRanges', $ipRanges);
    }

    /**
     * Check whether subscription is valid (parent signature compatibility)
     */
    public function isValid($check = SUBSCRIPTION_DATE_BOTH, $checkDate = null) {
        $request = Application::getRequest();
        $domain = $request->getServerHost();
        $IP = $request->getRemoteAddr();
        return $this->isValidInstitutional($domain, $IP, $check, $checkDate);
    }
    
    /**
     * Check whether subscription is valid for specific institution
     */
    public function isValidInstitutional($domain, $IP, $check = SUBSCRIPTION_DATE_BOTH, $checkDate = null) {
        $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        return $subscriptionDao->isValidInstitutionalSubscription($domain, $IP, $this->getData('journalId'), $check, $checkDate);
    }

}

?>