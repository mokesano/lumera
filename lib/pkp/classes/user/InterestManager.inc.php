<?php
declare(strict_types=1);

/**
 * @file classes/user/InterestManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InterestManager
 * @ingroup user
 * @see InterestDAO
 * 
 * @brief Handle user interest functions.
 */

class InterestManager {
    
    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function InterestManager() {
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
     * Get all interests for all users in the system.
     * @param string|null $filter
     * @return array
     */
    public function getAllInterests($filter = null) {
        /** @var InterestDAO $interestDao */
        $interestDao = DAORegistry::getDAO('InterestDAO');
        $interests = $interestDao->getAllInterests($filter);

        $interestReturner = [];
        if ($interests !== null) {
            while ($interest = $interests->next()) {
                if (method_exists($interest, 'getInterest')) {
                    $interestReturner[] = (string) $interest->getInterest();
                } elseif (method_exists($interest, 'getName')) {
                    $interestReturner[] = (string) $interest->getName(AppLocale::getLocale());
                }
            }
        }

        return $interestReturner;
    }

    /**
     * Get user reviewing interests. (Cached in memory for batch fetches.)
     * @param object $user PKPUser
     * @return array
     */
    public function getInterestsForUser($user) {
        static $interestsCache = [];
        $interests = [];

        /** @var InterestDAO $interestDao */
        $interestDao = DAORegistry::getDAO('InterestDAO');
        /** @var InterestEntryDAO $interestEntryDao */
        $interestEntryDao = DAORegistry::getDAO('InterestEntryDAO');
        
        $controlledVocab = $interestDao->build();
        $userId = (int) $user->getId();
        $vocabId = (int) $controlledVocab->getId();

        $userInterestIds = $interestDao->getUserInterestIds($userId);
        if (is_array($userInterestIds)) {
            foreach ($userInterestIds as $interestEntryId) {
                $entryId = (int) $interestEntryId;
                if (!isset($interestsCache[$entryId])) {
                    $interestsCache[$entryId] = $interestEntryDao->getById($entryId, $vocabId);
                }
                
                if (isset($interestsCache[$entryId]) && $interestsCache[$entryId] !== null) {
                    $entry = $interestsCache[$entryId];
                    if (method_exists($entry, 'getInterest')) {
                        $interests[] = (string) $entry->getInterest();
                    } elseif (method_exists($entry, 'getName')) {
                        $interests[] = (string) $entry->getName(AppLocale::getLocale());
                    }
                }
            }
        }

        return $interests;
    }

    /**
     * Returns a comma-separated string of a user's interests.
     * @param object $user PKPUser
     * @return string
     */
    public function getInterestsString($user) {
        $interests = $this->getInterestsForUser($user);
        return is_array($interests) ? implode(', ', $interests) : '';
    }

    /**
     * Set a user's interests.
     * @param object $user PKPUser
     * @param mixed $interests
     * @return void
     */
    public function setInterestsForUser($user, $interests) {
        /** @var InterestDAO $interestDao */
        $interestDao = DAORegistry::getDAO('InterestDAO');
        
        if (is_array($interests)) {
            $parsedInterests = $interests;
        } elseif (empty($interests)) {
            $parsedInterests = null;
        } else {
            $parsedInterests = explode(',', (string) $interests);
        }
        
        $interestDao->setUserInterests($parsedInterests, (int) $user->getId());
    }

}
?>