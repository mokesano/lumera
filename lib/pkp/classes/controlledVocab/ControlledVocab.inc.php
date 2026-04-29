<?php
declare(strict_types=1);

/**
 * @defgroup controlled_vocab
 */

/**
 * @file classes/controlledVocab/ControlledVocab.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ControlledVocab
 * @ingroup controlled_vocab
 * @see ControlledVocabDAO
 *
 * @brief Basic class describing an controlled vocab.
 */

class ControlledVocab extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ControlledVocab() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Menggunakan get_class($this) agar log mencatat NAMA CLASS ANAK yang memanggil
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ControlledVocab(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * get assoc id
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * set assoc id
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get associated type.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set associated type.
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get symbolic name.
     * @return string
     */
    public function getSymbolic() {
        return $this->getData('symbolic');
    }

    /**
     * Set symbolic name.
     * @param $symbolic string
     */
    public function setSymbolic($symbolic) {
        return $this->setData('symbolic', $symbolic);
    }

    /**
     * Get a list of controlled vocabulary options.
     * @param $settingName string optional
     * @return array $controlledVocabEntryId => name
     */
    public function enumerate($settingName = 'name') {
        // [MODERNISASI] Hapus tanda & pada assignment object
        $controlledVocabDao = DAORegistry::getDAO('ControlledVocabDAO');
        return $controlledVocabDao->enumerate($this->getId(), $settingName);
    }
}

?>