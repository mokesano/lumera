<?php
declare(strict_types=1);

/**
 * @defgroup issue_galley_form
 */

/**
 * @file classes/issue/form/IssueGalleyForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueGalleyForm
 * @ingroup issue_galley_form
 * @see IssueGalley
 *
 * @brief Issue galley editing form.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.Form');

class IssueGalleyForm extends Form {
    /** @var int|null the ID of the issue */
    protected $_issueId = null;

    /** @var IssueGalley|null current galley */
    protected $_galley = null;

    /**
     * Constructor.
     * @param int $issueId
     * @param int|null $galleyId (optional)
     */
    public function __construct($issueId, $galleyId = null) {
        parent::__construct('editor/issues/issueGalleyForm.tpl');
        
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $this->setIssueId($issueId);

        if (isset($galleyId) && !empty($galleyId)) {
            $galleyDao = DAORegistry::getDAO('IssueGalleyDAO');
            $galley = $galleyDao->getGalley($galleyId, $issueId);
            $this->setGalley($galley);
        }

        //
        // Validation checks for this form
        //

        // Ensure a label is provided
        $this->addCheck(
            new FormValidator(
                $this,
                'label',
                'required',
                'editor.issues.galleyLabelRequired'
            )
        );

        // Ensure a locale is provided and valid
        // [WIZDAM] Replaced create_function with closure
        $this->addCheck(
            new FormValidator(
                $this,
                'galleyLocale',
                'required',
                'editor.issues.galleyLocaleRequired'
            ),
            function($galleyLocale, $availableLocales) {
                return in_array($galleyLocale, $availableLocales);
            },
            array_keys($journal->getSupportedLocaleNames())
        );

        // Ensure form was POSTed
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function IssueGalleyForm($issueId, $galleyId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($issueId, $galleyId);
    }

    /**
     * Get the issue ID.
     * @return int|null
     */
    public function getIssueId() {
        return $this->_issueId;
    }

    /**
     * Set the issue ID.
     * @param int $issueId
     */
    public function setIssueId($issueId) {
        $this->_issueId = (int) $issueId;
    }

    /**
     * Get issue galley.
     * @return IssueGalley|null
     */
    public function getGalley() {
        return $this->_galley;
    }

    /**
     * Set issue galley.
     * @param IssueGalley $galley
     */
    public function setGalley($galley) {
        $this->_galley = $galley;
    }

    /**
     * Get the galley ID.
     * @return int|null
     */
    public function getGalleyId() {
        $galley = $this->getGalley();
        if ($galley) {
            return $galley->getId();
        } else {
            return null;
        }
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign('issueId', $this->getIssueId());
        $templateMgr->assign('galleyId', $this->getGalleyId());
        $templateMgr->assign('supportedLocales', $journal->getSupportedLocaleNames());
        $templateMgr->assign('enablePublicGalleyId', $journal->getSetting('enablePublicGalleyId'));

        $galley = $this->getGalley();
        if ($galley) {
            // [WIZDAM] Use assign instead of assign_by_ref
            $templateMgr->assign('galley', $galley);
        }

        parent::display($request, $template);
    }

    /**
     * Validate the form
     * @return bool
     */
    public function validate($callHooks = true) {
        // [WIZDAM] Singleton Fallback
        $request = Application::get()->getRequest();
        
        // Check if public galley ID is already being used
        $journal = $request->getJournal();
        $journalDao = DAORegistry::getDAO('JournalDAO'); /* @var $journalDao JournalDAO */

        $publicGalleyId = $this->getData('publicGalleyId');
        if ($publicGalleyId && $journalDao->anyPubIdExists($journal->getId(), 'publisher-id', $publicGalleyId, ASSOC_TYPE_ISSUE_GALLEY, $this->getGalleyId())) {
            $this->addError('publicGalleyId', __('editor.publicIdentificationExists', ['publicIdentifier' => $publicGalleyId]));
            $this->addErrorField('publicGalleyId');
        }

        return parent::validate();
    }

    /**
     * Initialize form data from current galley (if applicable).
     * @param array $galley
     * @return void
     */
    public function initData() {
        $galley = $this->getGalley();

        if ($galley) {
            $this->_data = [
                'label' => $galley->getLabel(),
                'publicGalleyId' => $galley->getPubId('publisher-id'),
                'galleyLocale' => $galley->getLocale()
            ];
        } else {
            $this->_data = [];
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @param array $galley
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'label',
                'publicGalleyId',
                'galleyLocale'
            ]
        );
    }

    /**
     * Save changes to the galley.
     * @param string|null $fileName
     * @return int|null the galley ID
     */
    public function execute($fileName = null) {
        import('classes.file.IssueFileManager');
        $issueFileManager = new IssueFileManager($this->getIssueId());
        $galleyDao = DAORegistry::getDAO('IssueGalleyDAO');

        $fileName = isset($fileName) ? $fileName : 'galleyFile';
        
        // [WIZDAM] Singleton Fallback
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        $galley = $this->getGalley();

        // Update an existing galley
        if ($galley) {
            if ($issueFileManager->uploadedFileExists($fileName)) {
                // Galley has a file, delete it before uploading new one
                if ($galley->getFileId()) {
                    $issueFileManager->deleteFile($galley->getFileId());
                }
                // Upload new file
                $fileId = $issueFileManager->uploadPublicFile($fileName);
                $galley->setFileId($fileId);
            }

            $galley->setLabel($this->getData('label'));
            if ($journal->getSetting('enablePublicGalleyId')) {
                $galley->setStoredPubId('publisher-id', $this->getData('publicGalleyId'));
            }
            $galley->setLocale($this->getData('galleyLocale'));

            // Update galley in the db
            $galleyDao->updateGalley($galley);

        } else {
            // Create a new galley
            // Upload galley file
            if ($issueFileManager->uploadedFileExists($fileName)) {
                $fileType = $issueFileManager->getUploadedFileType($fileName);
                $fileId = $issueFileManager->uploadPublicFile($fileName);
            } else {
                // No galley file uploaded
                $fileId = 0;
            }

            $galley = new IssueGalley();
            $galley->setIssueId($this->getIssueId());
            $galley->setFileId($fileId);

            if ($this->getData('label') == null) {
                // Generate initial label based on file type
                $enablePublicGalleyId = $journal->getSetting('enablePublicGalleyId');
                
                // [WIZDAM] Explicit null checks for fileType to avoid strstr errors
                if (isset($fileType) && $fileType) {
                    if(strstr($fileType, 'pdf')) {
                        $galley->setLabel('PDF');
                        if ($enablePublicGalleyId) $galley->setStoredPubId('publisher-id', 'pdf');
                    } else if (strstr($fileType, 'postscript')) {
                        $galley->setLabel('PostScript');
                        if ($enablePublicGalleyId) $galley->setStoredPubId('publisher-id', 'ps');
                    } else if (strstr($fileType, 'xml')) {
                        $galley->setLabel('XML');
                        if ($enablePublicGalleyId) $galley->setStoredPubId('publisher-id', 'xml');
                    }
                }

                if ($galley->getLabel() == null) {
                    $galley->setLabel(__('common.untitled'));
                }

            } else {
                $galley->setLabel($this->getData('label'));
            }
            $galley->setLocale($this->getData('galleyLocale'));

            if ($enablePublicGalleyId) {
                // Ensure the assigned public id doesn't already exist
                $journalDao = DAORegistry::getDAO('JournalDAO'); /* @var $journalDao JournalDAO */
                $publicGalleyId = $galley->getPubId('publisher-id');
                $suffix = '';
                $i = 1;
                while ($journalDao->anyPubIdExists($journal->getId(), 'publisher-id', $publicGalleyId . $suffix)) {
                    $suffix = '_'.$i++;
                }

                $galley->setStoredPubId('publisher-id', $publicGalleyId . $suffix);
            }

            // Insert new galley into the db
            $galleyDao->insertGalley($galley);
            $this->setGalley($galley);
        }

        return $this->getGalleyId();
    }
}

?>