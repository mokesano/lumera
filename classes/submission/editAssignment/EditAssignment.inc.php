<?php
declare(strict_types=1);

/**
 * @file classes/submission/editAssignment/EditAssignment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditAssignment
 * @ingroup submission
 * @see EditAssignmentDAO
 *
 * @brief Describes edit assignment properties.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

class EditAssignment extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditAssignment() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of edit assignment.
     * @return int|null
     */
    public function getEditId() {
        return $this->getData('editId');
    }

    /**
     * Set ID of edit assignment
     * @param int $editId
     */
    public function setEditId($editId) {
        return $this->setData('editId', $editId);
    }

    /**
     * Get ID of article.
     * @return int|null
     */
    public function getArticleId() {
        return $this->getData('articleId');
    }

    /**
     * Set ID of article.
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        return $this->setData('articleId', $articleId);
    }

    /**
     * Get ID of editor.
     * @return int|null
     */
    public function getEditorId() {
        return $this->getData('editorId');
    }

    /**
     * Set ID of editor.
     * @param int $editorId
     */
    public function setEditorId($editorId) {
        return $this->setData('editorId', $editorId);
    }

    /**
     * Get flag indicating whether this section editor can review this article. (Irrelevant if this is an editor.)
     * @return bool
     */
    public function getCanReview() {
        return (bool) $this->getData('canReview');
    }

    /**
     * Set flag indicating whether this section editor can review this article. (Irrelevant if this is an editor.)
     * @param bool|int $canReview
     */
    public function setCanReview($canReview) {
        return $this->setData('canReview', $canReview);
    }

    /**
     * Get flag indicating whether this section editor can edit this article. (Irrelevant if this is an editor.)
     * @return bool
     */
    public function getCanEdit() {
        return (bool) $this->getData('canEdit');
    }

    /**
     * Set flag indicating whether this section editor can edit this article. (Irrelevant if this is an editor.)
     * @param bool|int $canEdit
     */
    public function setCanEdit($canEdit) {
        return $this->setData('canEdit', $canEdit);
    }

    /**
     * Get flag indicating whether this entry is for an editor or a section editor.
     * @return bool
     */
    public function getIsEditor() {
        return (bool) $this->getData('isEditor');
    }

    /**
     * Set flag indicating whether this entry is for an editor or a section editor.
     * @param bool|int $isEditor
     */
    public function setIsEditor($isEditor) {
        return $this->setData('isEditor', $isEditor);
    }

    /**
     * Get date editor assigned.
     * @return string|null
     */
    public function getDateAssigned() {
        return $this->getData('dateAssigned');
    }

    /**
     * Set date editor assigned.
     * @param string|null $dateAssigned
     */
    public function setDateAssigned($dateAssigned) {
        return $this->setData('dateAssigned', $dateAssigned);
    }
        
    /**
     * Get date editor notified.
     * @return string|null
     */
    public function getDateNotified() {
        return $this->getData('dateNotified');
    }

    /**
     * Set date editor notified.
     * @param string|null $dateNotified
     */
    public function setDateNotified($dateNotified) {
        return $this->setData('dateNotified', $dateNotified);
    }

    /**
     * Get date editor underway.
     * @return string|null
     */
    public function getDateUnderway() {
        return $this->getData('dateUnderway');
    }

    /**
     * Set date editor underway.
     * @param string|null $dateUnderway
     */
    public function setDateUnderway($dateUnderway) {
        return $this->setData('dateUnderway', $dateUnderway);
    }

    /**
     * Get full name of editor.
     * @return string|null
     */
    public function getEditorFullName() {
        return $this->getData('editorFullName');
    }

    /**
     * Set full name of editor.
     * @param string $editorFullName
     */
    public function setEditorFullName($editorFullName) {
        return $this->setData('editorFullName', $editorFullName);
    }

    /**
     * Get first name of editor.
     * @return string|null
     */
    public function getEditorFirstName() {
        return $this->getData('editorFirstName');
    }

    /**
     * Set first name of editor.
     * @param string $editorFirstName
     */
    public function setEditorFirstName($editorFirstName) {
        return $this->setData('editorFirstName', $editorFirstName);
    }

    /**
     * Get last name of editor.
     * @return string|null
     */
    public function getEditorLastName() {
        return $this->getData('editorLastName');
    }

    /**
     * Set last name of editor.
     * @param string $editorLastName
     */
    public function setEditorLastName($editorLastName) {
        return $this->setData('editorLastName', $editorLastName);
    }

    /**
     * Get initials of editor.
     * @return string
     */
    public function getEditorInitials() {
        if ($this->getData('editorInitials')) {
            return $this->getData('editorInitials');
        } else {
            // [WIZDAM] Safety casting for substr in PHP 8
            $firstName = (string) $this->getEditorFirstName();
            $lastName = (string) $this->getEditorLastName();
            return substr($firstName, 0, 1) . substr($lastName, 0, 1);
        }
    }

    /**
     * Set initials of editor.
     * @param string $editorInitials
     */
    public function setEditorInitials($editorInitials) {
        return $this->setData('editorInitials', $editorInitials);
    }

    /**
     * Get email of editor.
     * @return string|null
     */
    public function getEditorEmail() {
        return $this->getData('editorEmail');
    }

    /**
     * Set full name of editor.
     * @param string $editorEmail
     */
    public function setEditorEmail($editorEmail) {
        return $this->setData('editorEmail', $editorEmail);
    }
}
?>