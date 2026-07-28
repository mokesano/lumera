<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReview.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReview
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReviewDAO
 *
 * @brief Basic class describing an object for review.
 */

class ObjectForReview extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ObjectForReview() {
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
     * Return string of person names, separated by the specified token.
     * @param bool $lastOnly
     * @param string $separator
     * @return string
     */
    public function getPersonString($lastOnly = false, $separator = ', ') {
        $str = '';
        $persons = $this->getPersons();
        foreach ($persons as $person) {
            if ($str !== '') {
                $str .= $separator;
            }
            $str .= $lastOnly ? (string) $person->getLastName() : (string) $person->getFullName();
        }
        return $str;
    }

    /**
     * Get all persons of this object for review.
     * @return array
     */
    public function getPersons() {
        /** @var ObjectForReviewPersonDAO $ofrPersonDao */
        $ofrPersonDao = DAORegistry::getDAO('ObjectForReviewPersonDAO');
        return $ofrPersonDao->getByObjectForReview((int) $this->getId());
    }

    /**
     * Get editor ID.
     * @return int|null
     */
    public function getEditorId() {
        $id = $this->getData('editorId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set editor ID.
     * @param int|null $editorId
     */
    public function setEditorId($editorId) {
        $this->setData('editorId', $editorId !== null ? (int) $editorId : null);
    }

    /**
     * Get editor assigned to the object for review.
     * @return User|null
     */
    public function getEditor() {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getById($this->getEditorId());
    }

    /**
     * Get editor's initials assigned to the object for review.
     * @return string|null
     */
    public function getEditorInitials() {
        $editor = $this->getEditor();
        if ($editor !== null) {
            $initials = $editor->getInitials();
            if (!empty($initials)) {
                return (string) $initials;
            }
            return substr((string) $editor->getFirstName(), 0, 1) . substr((string) $editor->getLastName(), 0, 1);
        }
        return null;
    }

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
     * Get review object type.
     * @return ReviewObjectType|null
     */
    public function getReviewObjectType() {
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        return $reviewObjectTypeDao->getById($this->getReviewObjectTypeId());
    }

    /**
     * Get context ID.
     * @return int|null
     */
    public function getContextId() {
        $id = $this->getData('contextId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set context ID.
     * @param int|null $contextId
     */
    public function setContextId($contextId) {
        $this->setData('contextId', $contextId !== null ? (int) $contextId : null);
    }

    /**
     * Get available status of the object for review.
     * @return int|null
     */
    public function getAvailable() {
        $available = $this->getData('available');
        return $available !== null ? (int) $available : null;
    }

    /**
     * Set available status of the object for review.
     * @param int|null $available
     */
    public function setAvailable($available) {
        $this->setData('available', $available !== null ? (int) $available : null);
    }

    /**
     * Get object for review available status locale key.
     * @return string
     */
    public function getStatusString() {
        if ((int) $this->getData('available') === 1) {
            return 'plugins.generic.objectsForReview.editor.objectForReview.status.available';
        }
        return 'plugins.generic.objectsForReview.editor.objectForReview.status.notAvailable';
    }

    /**
     * Get dateCreated.
     * @return string|null
     */
    public function getDateCreated() {
        $date = $this->getData('dateCreated');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set dateCreated.
     * @param string|null $dateCreated
     */
    public function setDateCreated($dateCreated) {
        $this->setData('dateCreated', $dateCreated !== null ? (string) $dateCreated : null);
    }

    /**
     * Get notes for the object for review.
     * @return string|null
     */
    public function getNotes() {
        $notes = $this->getData('notes');
        return $notes !== null ? (string) $notes : null;
    }

    /**
     * Set notes for the object for review.
     * @param string|null $notes
     */
    public function setNotes($notes) {
        $this->setData('notes', $notes !== null ? (string) $notes : null);
    }

    //
    // Get settings
    //

    /**
     * Get title.
     * @return mixed
     */
    public function getTitle() {
        return $this->getSettingByKey('title');
    }

    /**
     * Get cover page.
     * @return mixed
     */
    public function getCoverPage() {
        return $this->getSettingByKey('coverPage');
    }

    /**
     * Get languages.
     * @return string
     */
    public function getLanguages() {
        /** @var LanguageDAO $languageDao */
        $languageDao = DAORegistry::getDAO('LanguageDAO');
        $languageCodes = $this->getSettingByKey('language');
        $languages = [];
        
        if (is_array($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                $language = $languageDao->getLanguageByCode((string) $languageCode);
                if ($language !== null) {
                    $languages[] = (string) $language->getName();
                }
            }
        }
        return implode(';', $languages);
    }

    /**
     * Get info if there is a copy of the object for review.
     * @return mixed
     */
    public function getCopy() {
        return $this->getSettingByKey('copy');
    }

    /**
     * Retrieve array of object for review settings.
     * @return array
     */
    public function getSettings() {
        /** @var ObjectForReviewSettingsDAO $ofrSettingsDao */
        $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
        return $ofrSettingsDao->getSettings((int) $this->getId());
    }

    /**
     * Retrieve object for review setting value by review object metadata id.
     * @param int $reviewObjectMetadataId
     * @return mixed
     */
    public function getSetting($reviewObjectMetadataId) {
        /** @var ObjectForReviewSettingsDAO $ofrSettingsDao */
        $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
        $setting = $ofrSettingsDao->getSetting((int) $this->getId(), (int) $reviewObjectMetadataId);
        return $setting[(int) $reviewObjectMetadataId] ?? null;
    }

    /**
     * Update object for review setting value.
     * @param int $reviewObjectMetadataId
     * @param mixed $value
     * @param string|null $type (optional)
     * @return mixed
     */
    public function updateSetting($reviewObjectMetadataId, $value, $type = null) {
        /** @var ObjectForReviewSettingsDAO $ofrSettingsDao */
        $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
        return $ofrSettingsDao->updateSetting((int) $this->getId(), (int) $reviewObjectMetadataId, $value, $type);
    }

    /**
     * Retrieve metadata ID by key.
     * @param string $key
     * @return int|null
     */
    public function getMetadataId($key) {
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        return $reviewObjectMetadataDao->getMetadataId($this->getReviewObjectTypeId(), (string) $key);
    }

    /**
     * Retrieve object for review setting value by review object metadata key.
     * @param string $key
     * @return mixed
     */
    public function getSettingByKey($key) {
        $metadataId = $this->getMetadataId($key);
        return $this->getSetting($metadataId);
    }

}
?>