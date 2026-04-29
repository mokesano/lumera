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
 * @brief Basic class describing a controlled vocab.
 */

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
            // [CCTV] Menggunakan get_class($this) agar log mencatat NAMA CLASS ANAK yang memanggil
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
     * @return int
     */
    public function getControlledVocabId() {
        return $this->getData('controlledVocabId');
    }

    /**
     * Set the ID of the controlled vocab.
     * @param $controlledVocabId int
     */
    public function setControlledVocabId($controlledVocabId) {
        return $this->setData('controlledVocabId', $controlledVocabId);
    }

    /**
     * Get sequence number.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence number.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get the localized name.
     * @return string
     */
    public function getLocalizedName() {
        return $this->getLocalizedData('name');
    }

    /**
     * Get the name of the controlled vocabulary entry.
     * @param $locale string
     * @return string
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set the name of the controlled vocabulary entry.
     * @param $name string
     * @param $locale string
     */
    public function setName($name, $locale) {
        return $this->setData('name', $name, $locale);
    }
}

?>