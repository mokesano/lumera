<?php

/**
 * @file classes/subscription/SubscriptionType.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionType
 * @ingroup subscription 
 * @see SubscriptionTypeDAO
 *
 * @brief Basic class describing a subscription type.
 * * MODERNIZED FOR WIZDAM FORK
 */

/**
 * Subscription type formats
 */
define('SUBSCRIPTION_TYPE_FORMAT_ONLINE',        0x01); 
define('SUBSCRIPTION_TYPE_FORMAT_PRINT',        0x10);
define('SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE',    0x11);


class SubscriptionType extends DataObject {
    //
    // Get/set methods
    //

    /**
     * Get the ID of the subscription type.
     * @return int
     */
    public function getTypeId() {
        return $this->getData('typeId');
    }

    /**
     * Set the ID of the subscription type.
     * @param $typeId int
     */
    public function setTypeId($typeId) {
        return $this->setData('typeId', $typeId);
    }

    /**
     * Get the journal ID of the subscription type.
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journalId');
    }

    /**
     * Set the journal ID of the subscription type.
     * @param $journalId int
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', $journalId);
    }

    /**
     * Get the localized subscription type name
     * @return string
     */
    public function getSubscriptionTypeName() {
        return $this->getLocalizedData('name');
    }

    /**
     * Get subscription type name.
     * @param $locale string
     * @return string
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set subscription type name.
     * @param $name string
     * @param $locale string
     */
    public function setName($name, $locale) {
        return $this->setData('name', $name, $locale);
    }

    /**
     * Get the localized subscription type description
     * @return string
     */
    public function getSubscriptionTypeDescription() {
        return $this->getLocalizedData('description');
    }

    /**
     * Get subscription type description.
     * @param $locale string
     * @return string
     */
    public function getDescription($locale) {
        return $this->getData('description', $locale);
    }

    /**
     * Set subscription type description.
     * @param $description string
     * @param $locale string
     */
    public function setDescription($description, $locale) {
        return $this->setData('description', $description, $locale);
    }

    /**
     * Get subscription type cost.
     * @return float 
     */
    public function getCost() {
        return $this->getData('cost');
    }

    /**
     * Set subscription type cost.
     * @param $cost float
     */
    public function setCost($cost) {
        return $this->setData('cost', $cost);
    }

    /**
     * Get subscription type currency code.
     * @return string
     */
    public function getCurrencyCodeAlpha() {
        return $this->getData('currencyCodeAlpha');
    }

    /**
     * Set subscription type currency code.
     * @param $currencyCodeAlpha string
     */
    public function setCurrencyCodeAlpha($currencyCodeAlpha) {
        return $this->setData('currencyCodeAlpha', $currencyCodeAlpha);
    }

    /**
     * Get subscription type currency string.
     * @return int
     */
    public function getCurrencyString() {
        $currencyDao = DAORegistry::getDAO('CurrencyDAO');
        $currency = $currencyDao->getCurrencyByAlphaCode($this->getData('currencyCodeAlpha'));

        if ($currency != null) {
            return $currency->getName();
        } else {
            return 'subscriptionTypes.currency';
        }
    }

    /**
     * Get subscription type currency abbreviated string.
     * @return int
     */
    public function getCurrencyStringShort() {
        $currencyDao = DAORegistry::getDAO('CurrencyDAO');
        $currency = $currencyDao->getCurrencyByAlphaCode($this->getData('currencyCodeAlpha'));

        if ($currency != null) {
            return $currency->getCodeAlpha();
        } else {
            return 'subscriptionTypes.currency';
        }
    }

    /**
     * Get subscription type nonExpiring.
     * @return int
     */
    public function getNonExpiring() {
        return $this->getData('nonExpiring');
    }

    /**
     * Set subscription type nonExpiring.
     * @param $nonExpiring int
     */
    public function setNonExpiring($nonExpiring) {
        return $this->setData('nonExpiring', $nonExpiring);
    }

    /**
     * Get subscription type duration.
     * @return int
     */
    public function getDuration() {
        return $this->getData('duration');
    }

    /**
     * Set subscription type duration.
     * @param $duration int
     */
    public function setDuration($duration) {
        return $this->setData('duration', $duration);
    }

    /**
     * Get subscription type duration in years and months.
     * @param $locale string
     * @return string
     */
    public function getDurationYearsMonths($locale = null) {
        if ($this->getData('nonExpiring')) {
            return __('subscriptionTypes.nonExpiring', null, $locale);
        }

        $years = (int)floor($this->getData('duration')/12);
        $months = (int)fmod($this->getData('duration'), 12);
        $yearsMonths = '';

        if ($years == 1) {
            $yearsMonths = '1 ' . __('subscriptionTypes.year', null, $locale);
        } elseif ($years > 1) {
            $yearsMonths = $years . ' ' . __('subscriptionTypes.years', null, $locale);
        }

        if ($months == 1) {
            $yearsMonths .= $yearsMonths == ''  ? '1 ' : ' 1 ';
            $yearsMonths .= __('subscriptionTypes.month', null, $locale);
        } elseif ($months > 1){
            $yearsMonths .= $yearsMonths == ''  ? $months . ' ' : ' ' . $months . ' ';
            $yearsMonths .= __('subscriptionTypes.months', null, $locale);
        }

        return $yearsMonths;
    }

    /**
     * Get subscription type format.
     * @return int
     */
    public function getFormat() {
        return $this->getData('format');
    }

    /**
     * Set subscription type format.
     * @param $format int
     */
    public function setFormat($format) {
        return $this->setData('format', $format);
    }

    /**
     * Get subscription type format locale key.
     * @return int
     */
    public function getFormatString() {
        switch ($this->getData('format')) {
            case SUBSCRIPTION_TYPE_FORMAT_ONLINE:
                return 'subscriptionTypes.format.online';
            case SUBSCRIPTION_TYPE_FORMAT_PRINT:
                return 'subscriptionTypes.format.print';
            case SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE:
                return 'subscriptionTypes.format.printOnline';
            default:
                return 'subscriptionTypes.format';
        }
    }

    /**
     * Check if this subscription type is for an institution.
     * @return boolean
     */
    public function getInstitutional() {
        return $this->getData('institutional');
    }

    /**
     * Set whether or not this subscription type is for an institution.
     * @param $institutional boolean
     */
    public function setInstitutional($institutional) {
        return $this->setData('institutional', $institutional);
    }

    /**
     * Check if this subscription type requires a membership.
     * @return boolean
     */
    public function getMembership() {
        return $this->getData('membership');
    }

    /**
     * Set whether or not this subscription type requires a membership.
     * @param $membership boolean
     */
    public function setMembership($membership) {
        return $this->setData('membership', $membership);
    }

    /**
     * Check if this subscription type should be publicly visible.
     * @return boolean
     */
    public function getDisablePublicDisplay() {
        return $this->getData('disable_public_display');
    }

    /**
     * Set whether or not this subscription type should be publicly visible.
     * @param $disablePublicDisplay boolean
     */
    public function setDisablePublicDisplay($disablePublicDisplay) {
        return $this->setData('disable_public_display', $disablePublicDisplay);
    }

    /**
     * Get subscription type display sequence.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set subscription type display sequence.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get subscription type summary in the form: TypeName - Duration - Cost (CurrencyShort).
     * @return string
     */
    public function getSummaryString() {
        return $this->getSubscriptionTypeName() . ' - ' . $this->getDurationYearsMonths() . ' - ' . sprintf('%.2f', $this->getCost()) . ' ' . $this->getCurrencyStringShort();
    }
}

?>