<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ReviewObjectMetadata.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectMetadata
 * @ingroup plugins_generic_objectsForReview
 * @see ReviewObjectMetadataDAO
 *
 * @brief Basic class describing a review object metadata.
 */

define('REVIEW_OBJECT_METADATA_TYPE_SMALL_TEXT_FIELD',    0x000001);
define('REVIEW_OBJECT_METADATA_TYPE_TEXT_FIELD',          0x000002);
define('REVIEW_OBJECT_METADATA_TYPE_TEXTAREA',            0x000003);
define('REVIEW_OBJECT_METADATA_TYPE_CHECKBOXES',          0x000004);
define('REVIEW_OBJECT_METADATA_TYPE_RADIO_BUTTONS',       0x000005);
define('REVIEW_OBJECT_METADATA_TYPE_DROP_DOWN_BOX',       0x000006);
define('REVIEW_OBJECT_METADATA_TYPE_ROLE_DROP_DOWN_BOX',  0x000007);
define('REVIEW_OBJECT_METADATA_TYPE_LANG_DROP_DOWN_BOX',  0x000008);
define('REVIEW_OBJECT_METADATA_TYPE_COVERPAGE',           0x000009);

define('REVIEW_OBJECT_METADATA_KEY_TITLE',        'title');
define('REVIEW_OBJECT_METADATA_KEY_ROLE',         'role');
define('REVIEW_OBJECT_METADATA_KEY_DATE',         'date');
define('REVIEW_OBJECT_METADATA_KEY_LANG',         'language');
define('REVIEW_OBJECT_METADATA_KEY_SHORTTITLE',   'shortTitle');
define('REVIEW_OBJECT_METADATA_KEY_URL',          'url');
define('REVIEW_OBJECT_METADATA_KEY_RIGHTS',       'rights');
define('REVIEW_OBJECT_METADATA_KEY_ABSTRACT',     'abstract');
define('REVIEW_OBJECT_METADATA_KEY_COPY',         'copy');
define('REVIEW_OBJECT_METADATA_KEY_COVERPAGE',    'coverPage');

class ReviewObjectMetadata extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewObjectMetadata() {
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
     * Get localized name.
     * @return string|null
     */
    public function getLocalizedName() {
        $name = $this->getLocalizedData('name');
        return $name !== null ? (string) $name : null;
    }

    /**
     * Get localized list of possible options.
     * @return array|null
     */
    public function getLocalizedPossibleOptions() {
        $options = $this->getLocalizedData('possibleOptions');
        return is_array($options) ? $options : null;
    }

    //
    // Get/set methods
    //

    /**
     * Get review object type ID.
     * @return int|null
     */
    public function getReviewObjectTypeId() {
        $id = $this->getData('reviewObjectTypeId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set review object type ID.
     * @param int|null $reviewObjectTypeId
     */
    public function setReviewObjectTypeId($reviewObjectTypeId) {
        $this->setData('reviewObjectTypeId', $reviewObjectTypeId !== null ? (int) $reviewObjectTypeId : null);
    }

    /**
     * Get sequence.
     * @return float|null
     */
    public function getSequence() {
        $seq = $this->getData('sequence');
        return $seq !== null ? (float) $seq : null;
    }

    /**
     * Set sequence.
     * @param float|null $sequence
     */
    public function setSequence($sequence) {
        $this->setData('sequence', $sequence !== null ? (float) $sequence : null);
    }

    /**
     * Get type.
     * @return int|null
     */
    public function getMetadataType() {
        $type = $this->getData('metadataType');
        return $type !== null ? (int) $type : null;
    }

    /**
     * Set type.
     * @param int|null $metadataType
     */
    public function setMetadataType($metadataType) {
        $this->setData('metadataType', $metadataType !== null ? (int) $metadataType : null);
    }

    /**
     * Get required flag.
     * @return int|null
     */
    public function getRequired() {
        $req = $this->getData('required');
        return $req !== null ? (int) $req : null;
    }

    /**
     * Set required flag.
     * @param int|null $required
     */
    public function setRequired($required) {
        $this->setData('required', $required !== null ? (int) $required : null);
    }

    /**
     * Get display flag.
     * @return int|null
     */
    public function getDisplay() {
        $disp = $this->getData('display');
        return $disp !== null ? (int) $disp : null;
    }

    /**
     * Set display flag.
     * @param int|null $display
     */
    public function setDisplay($display) {
        $this->setData('display', $display !== null ? (int) $display : null);
    }

    /**
     * Get key.
     * @return string|null
     */
    public function getKey() {
        $key = $this->getData('key');
        return $key !== null ? (string) $key : null;
    }

    /**
     * Set key.
     * @param string|null $key
     */
    public function setKey($key) {
        $this->setData('key', $key !== null ? (string) $key : null);
    }

    /**
     * Get name.
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale) {
        $name = $this->getData('name', $locale);
        return $name !== null ? (string) $name : null;
    }

    /**
     * Set name.
     * @param string|null $name
     * @param string|null $locale
     */
    public function setName($name, $locale) {
        $this->setData('name', $name !== null ? (string) $name : null, $locale);
    }

    /**
     * Get possible options.
     * @param string|null $locale
     * @return array|null
     */
    public function getPossibleOptions($locale) {
        $options = $this->getData('possibleOptions', $locale);
        return is_array($options) ? $options : null;
    }

    /**
     * Set possible options.
     * @param array|null $possibleOptions
     * @param string|null $locale
     */
    public function setPossibleOptions($possibleOptions, $locale) {
        $this->setData('possibleOptions', is_array($possibleOptions) ? $possibleOptions : null, $locale);
    }

    /**
     * Get an associative array matching metadata type codes with locale strings.
     * (Includes default '' => "Choose One" string.)
     * @return array
     */
    public function getMetadataFormTypeOptions() {
        static $metadataTypeOptions = [
            '' => 'plugins.generic.objectsForReview.editor.objectMetadata.type.chooseType',
            REVIEW_OBJECT_METADATA_TYPE_SMALL_TEXT_FIELD => 'plugins.generic.objectsForReview.editor.objectMetadata.type.smalltextfield',
            REVIEW_OBJECT_METADATA_TYPE_TEXT_FIELD => 'plugins.generic.objectsForReview.editor.objectMetadata.type.textfield',
            REVIEW_OBJECT_METADATA_TYPE_TEXTAREA => 'plugins.generic.objectsForReview.editor.objectMetadata.type.textarea',
            REVIEW_OBJECT_METADATA_TYPE_CHECKBOXES => 'plugins.generic.objectsForReview.editor.objectMetadata.type.checkboxes',
            REVIEW_OBJECT_METADATA_TYPE_RADIO_BUTTONS => 'plugins.generic.objectsForReview.editor.objectMetadata.type.radiobuttons',
            REVIEW_OBJECT_METADATA_TYPE_DROP_DOWN_BOX => 'plugins.generic.objectsForReview.editor.objectMetadata.type.dropdownbox'
        ];
        return $metadataTypeOptions;
    }

    /**
     * Get metadata DTD types.
     * @return array
     */
    public function getMetadataDTDTypes() {
        static $metadataDTDTypes = [
            'smallTextField' => REVIEW_OBJECT_METADATA_TYPE_SMALL_TEXT_FIELD,
            'singleLineTextBox' => REVIEW_OBJECT_METADATA_TYPE_TEXT_FIELD,
            'extendedTextBox' => REVIEW_OBJECT_METADATA_TYPE_TEXTAREA,
            'checkBoxes' => REVIEW_OBJECT_METADATA_TYPE_CHECKBOXES,
            'radioButtons' => REVIEW_OBJECT_METADATA_TYPE_RADIO_BUTTONS,
            'dropDownBox' => REVIEW_OBJECT_METADATA_TYPE_DROP_DOWN_BOX,
            'roleDropDownBox' => REVIEW_OBJECT_METADATA_TYPE_ROLE_DROP_DOWN_BOX,
            'languageDropDownBox' => REVIEW_OBJECT_METADATA_TYPE_LANG_DROP_DOWN_BOX,
            'coverPage' => REVIEW_OBJECT_METADATA_TYPE_COVERPAGE
        ];
        return $metadataDTDTypes;
    }

    /**
     * Get array of all multiple options metadata types.
     * @return array
     */
    public function getMultipleOptionsTypes() {
        static $multipleOptionsTypes = [
            REVIEW_OBJECT_METADATA_TYPE_CHECKBOXES,
            REVIEW_OBJECT_METADATA_TYPE_RADIO_BUTTONS,
            REVIEW_OBJECT_METADATA_TYPE_DROP_DOWN_BOX,
            REVIEW_OBJECT_METADATA_TYPE_ROLE_DROP_DOWN_BOX
        ];
        return $multipleOptionsTypes;
    }

    /**
     * Get array of all common metadata keys.
     * @return array
     */
    public function getCommonMetadataKeys() {
        static $commonMetadataKeys = [
            REVIEW_OBJECT_METADATA_KEY_TITLE,
            REVIEW_OBJECT_METADATA_KEY_ROLE,
            REVIEW_OBJECT_METADATA_KEY_DATE,
            REVIEW_OBJECT_METADATA_KEY_LANG,
            REVIEW_OBJECT_METADATA_KEY_SHORTTITLE,
            REVIEW_OBJECT_METADATA_KEY_URL,
            REVIEW_OBJECT_METADATA_KEY_RIGHTS,
            REVIEW_OBJECT_METADATA_KEY_ABSTRACT,
            REVIEW_OBJECT_METADATA_KEY_COPY,
            REVIEW_OBJECT_METADATA_KEY_COVERPAGE
        ];
        return $commonMetadataKeys;
    }

    /**
     * Check if this is a common metadata.
     * @return bool
     */
    public function isCommon() {
        return in_array($this->getKey(), $this->getCommonMetadataKeys(), true);
    }

    /**
     * Check if there is a key for this metadata,
     * i.e. if the metadata is predefined.
     * @return bool
     */
    public function keyExists() {
        $key = $this->getKey();
        return $key !== null && $key !== '';
    }

    /**
     * Get localised content of the possible option.
     * @param int $order
     * @return string|null
     */
    public function getLocalizedPossibleOptionContent($order) {
        $possibleOptions = $this->getLocalizedData('possibleOptions');
        if (is_array($possibleOptions)) {
            foreach ($possibleOptions as $option) {
                if (is_array($option) && isset($option['order']) && (int) $option['order'] === (int) $order) {
                    return (string) ($option['content'] ?? '');
                }
            }
        }
        return null;
    }

}
?>