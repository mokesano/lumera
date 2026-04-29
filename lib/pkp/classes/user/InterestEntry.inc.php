<?php
declare(strict_types=1);

/**
 * @file classes/user/InterestEntry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InterestEntry
 * @ingroup user
 * @see InterestDAO
 *
 * @brief Basic class describing a reviewer interest
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class InterestEntry extends ControlledVocabEntry {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InterestEntry() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::InterestEntry(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the interest
     * @return string
     */
    public function getInterest() {
        return $this->getData('interest');
    }

    /**
     * Set the interest text
     * @param $interest string
     */
    public function setInterest($interest) {
        $this->setData('interest', $interest);
    }
}

?>