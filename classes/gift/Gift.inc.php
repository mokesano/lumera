<?php
declare(strict_types=1);

/**
 * @file classes/gift/Gift.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Gift
 * @ingroup gift
 * @see GiftDAO
 *
 * @brief Class for an OJS Gift.
 */

import('lib.pkp.classes.gift.PKPGift');

define('GIFT_TYPE_SUBSCRIPTION', 0x01);

class Gift extends PKPGift {
    
    /**
     * Constructor.
     */
    public function __construct() { // Mengganti nama konstruktor
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Gift() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Gift(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the name of the gift based on gift type.
     * @param $locale string
     * @return string
     */
    public function getGiftName($locale = null) {
        if (!isset($locale)) $locale = AppLocale::getLocale();
        switch ($this->getGiftType()){
            case GIFT_TYPE_SUBSCRIPTION:
                $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                $subscriptionType = $subscriptionTypeDao->getSubscriptionType($this->getGiftAssocId());
                if ($subscriptionType) {
                    return __('payment.type.gift', null, $locale) . ' ' . __('payment.type.gift.subscription', null, $locale) . ': ' . $subscriptionType->getName($locale) . ' - ' . $subscriptionType->getDurationYearsMonths($locale);
                } else {
                    return __('payment.type.gift', null, $locale) . ' ' . __('payment.type.gift.subscription', null, $locale);
                }
                break;
            default:
                return __('payment.type.gift', null, $locale);
        }
    }
}

?>