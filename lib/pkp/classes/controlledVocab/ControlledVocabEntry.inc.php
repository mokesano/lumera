<?php
declare(strict_types=1);

/**
 * @file classes/controlledVocab/ControlledVocabEntry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ControlledVocabEntry
 * @ingroup controlled_vocabs
 * @see ControlledVocabEntryDAO
 *
 * @brief Basic class describing a controlled vocab entry.
 */

import('lib.pkp.classes.core.DataObject');

class ControlledVocabEntry extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ControlledVocabEntry() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ControlledVocabEntry(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the ID of the controlled vocab.
     * @return int|null
     */
    public function getControlledVocabId() {
        return $this->getData('controlledVocabId');
    }

    /**
     * Set the ID of the controlled vocab.
     * @param int $controlledVocabId
     */
    public function setControlledVocabId($controlledVocabId) {
        return $this->setData('controlledVocabId', (int) $controlledVocabId);
    }

    /**
     * Get sequence number.
     * @return float|null
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence number.
     * @param float $sequence
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', (float) $sequence);
    }

    /**
     * Get the localized name.
     * @return string|null
     */
    public function getLocalizedName() {
        return $this->getLocalizedData('name');
    }

    /**
     * Get the name of the controlled vocabulary entry.
     * @param string $locale
     * @return string|null
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set the name of the controlled vocabulary entry.
     * @param string $name
     * @param string $locale
     */
    public function setName($name, $locale) {
        return $this->setData('name', (string) $name, $locale);
    }
    
}
?>