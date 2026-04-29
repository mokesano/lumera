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
 * @brief Handle user interest functions.
 */

class InterestManager {
    
    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InterestManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::InterestManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Get all interests for all users in the system
     * @param $filter string
     * @return array
     */
    public function getAllInterests($filter = null) {
        $interestDao = DAORegistry::getDAO('InterestDAO'); /* @var $interestDao InterestDAO */
        $interests = $interestDao->getAllInterests($filter);

        $interestReturner = array();
        while($interest = $interests->next()) {
            $interestReturner[] = $interest->getInterest();
            unset($interest);
        }

        return $interestReturner;
    }

    /**
     * Get user reviewing interests. (Cached in memory for batch fetches.)
     * @param $user PKPUser
     * @return array
     */
    public function getInterestsForUser($user) {
        static $interestsCache = array();
        $interests = array();

        $interestDao = DAORegistry::getDAO('InterestDAO');
        $interestEntryDao = DAORegistry::getDAO('InterestEntryDAO');
        
        $controlledVocab = $interestDao->build();
        foreach($interestDao->getUserInterestIds($user->getId()) as $interestEntryId) {
            if (!isset($interestsCache[$interestEntryId])) {
                $interestsCache[$interestEntryId] = $interestEntryDao->getById(
                    $interestEntryId,
                    $controlledVocab->getId()
                );
            }
            if (isset($interestsCache[$interestEntryId])) {
                $interests[] = $interestsCache[$interestEntryId]->getInterest();
            }
        }

        return $interests;
    }

    /**
     * Returns a comma separated string of a user's interests
     * @param $user PKPUser
     * @return string
     */
    public function getInterestsString($user) {
        $interests = $this->getInterestsForUser($user);

        return implode(', ', $interests);
    }

    /**
     * Set a user's interests
     * @param $user PKPUser
     * @param $interests mixed
     */
    public function setInterestsForUser($user, $interests) {
        $interestDao = DAORegistry::getDAO('InterestDAO');
        
        $interests = is_array($interests) ? $interests : (empty($interests) ? null : explode(",", $interests));
        $interestDao->setUserInterests($interests, $user->getId());
    }
}

?>