<?php
declare(strict_types=1);

/**
 * @defgroup issue Issue
 */

/**
 * @file classes/issue/IssueAccess.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueAccess
 * @ingroup issue
 * @see Issue
 *
 * @brief Trait for Issue Access policy and subscription control.
 */

trait IssueAccess {

    /**
     * Get issue access status (open access vs subscription).
     * @return int
     */
    public function getAccessStatus() {
        return $this->getData('accessStatus');
    }

    /**
     * Set issue access status.
     * @param $accessStatus int
     */
    public function setAccessStatus($accessStatus) {
        return $this->setData('accessStatus', $accessStatus);
    }

    /**
     * Get open access date.
     * @return string (date)
     */
    public function getOpenAccessDate() {
        return $this->getData('openAccessDate');
    }

    /**
     * Set open access date.
     * @param $openAccessDate string (date)
     */
    public function setOpenAccessDate($openAccessDate) {
        return $this->setData('openAccessDate', $openAccessDate);
    }

}
?>