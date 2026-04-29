<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/medra/MedraExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MedraExportPlugin
 * @ingroup plugins_importexport_medra
 *
 * @brief mEDRA Onix for DOI (O4DOI) export/registration plugin.
 */

if (!class_exists('DOIExportPlugin')) { // Bug #7848
    import('plugins.importexport.medra.classes.DOIExportPlugin');
}

// O4DOI schemas.
define('O4DOI_ISSUE_AS_WORK', 0x01);
define('O4DOI_ISSUE_AS_MANIFESTATION', 0x02);
define('O4DOI_ARTICLE_AS_WORK', 0x03);
define('O4DOI_ARTICLE_AS_MANIFESTATION', 0x04);

class MedraExportPlugin extends DOIExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MedraExportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Implement template methods from ImportExportPlugin
    //
    /**
     * Get the name of this plugin. The name must be unique within
     * the category of plugins it belongs to.
     * @see ImportExportPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'MedraExportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see ImportExportPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.medra.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see ImportExportPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.medra.description');
    }


    //
    // Implement template methods from DOIExportPlugin
    //
    /**
     * Get the plugin ID.
     * @see DOIExportPlugin::getPluginId()
     * @return string
     */
    public function getPluginId(): string {
        return 'medra';
    }

    /**
     * Get the class name of the settings form.
     * @see DOIExportPlugin::getSettingsFormClassName()
     * @return string
     */
    public function getSettingsFormClassName(): string {
        return 'MedraSettingsForm';
    }

    /**
     * Generate export files for the given objects.
     * @see DOIExportPlugin::generateExportFiles()
     * @param object $request
     * @param mixed $exportType
     * @param array $objects
     * @param string $targetPath
     * @param object $journal
     * @param array|null $errors
     * @return array|false
     */
    public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
        assert(count($objects) >= 1);

        // Identify the O4DOI schema to export.
        $exportIssuesAs = $this->getSetting($journal->getId(), 'exportIssuesAs');
        $schema = $this->_identifyO4DOISchema($exportType, $journal, $exportIssuesAs);
        assert(!is_null($schema));

        // Create the XML DOM and document.
        $this->import('classes.O4DOIExportDom');
        $dom = new O4DOIExportDom($request, $this, $schema, $journal, $this->getCache(), $exportIssuesAs);
        $doc = $dom->generate($objects);
        if ($doc === false) {
            $errors = $dom->getErrors();
            return false;
        }

        // Write the result to the target file.
        $exportFileName = $this->getTargetFileName($targetPath, $exportType);
        file_put_contents($exportFileName, XMLCustomWriter::getXML($doc));
        
        // Remove reference (&) in array value as objects are passed by reference by default
        $generatedFiles = [$exportFileName => $objects];
        return $generatedFiles;
    }

    /**
     * Register DOIs with the DOI registration agency.
     * @see DOIExportPlugin::registerDoi()
     * @param object $request
     * @param object $journal
     * @param array $objects
     * @param string $file
     * @return bool|array
     */
    public function registerDoi($request, $journal, $objects, $file) {
        // Use a different endpoint for testing and
        // production.
        $this->import('classes.MedraWebservice');
        $endpoint = ($this->isTestMode($request) ? MEDRA_WS_ENDPOINT_DEV : MEDRA_WS_ENDPOINT);

        // Get credentials.
        $username = $this->getSetting($journal->getId(), 'username');
        $password = $this->getSetting($journal->getId(), 'password');

        // Retrieve the XML.
        assert(is_readable($file));
        $xml = file_get_contents($file);
        assert($xml !== false && !empty($xml));

        // Instantiate the mEDRA web service wrapper.
        $ws = new MedraWebservice($endpoint, $username, $password);

        // Register the XML with mEDRA.
        $result = $ws->upload($xml);

        if ($result === true) {
            // Mark all objects as registered.
            foreach($objects as $object) {
                $this->markRegistered($request, $object, MEDRA_WS_TESTPREFIX);
            }
        } else {
            // Handle errors.
            if (is_string($result)) {
                $result = [
                    ['plugins.importexport.common.register.error.mdsError', $result]
                ];
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Callback to add scheduled tasks.
     * @see AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args) {
        $taskFilesPath =& $args[0];
        $taskFilesPath[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'scheduledTasks.xml';
        return false;
    }

    //
    // Private helper methods
    //
    /**
     * Determine the O4DOI export schema.
     *
     * @param int $exportType One of the DOI_EXPORT_* constants.
     * @param object $journal Journal
     * @param int|null $exportIssuesAs Whether issues are exported as work
     * or as manifestation. One of the O4DOI_* schema constants.
     *
     * @return int|null One of the O4DOI_* schema constants.
     */
    private function _identifyO4DOISchema($exportType, $journal, $exportIssuesAs) {
        switch ($exportType) {
            case DOI_EXPORT_ISSUES:
                assert($exportIssuesAs == O4DOI_ISSUE_AS_WORK || $exportIssuesAs == O4DOI_ISSUE_AS_MANIFESTATION);
                return (int) $exportIssuesAs;

            case DOI_EXPORT_ARTICLES:
                return O4DOI_ARTICLE_AS_WORK;

            case DOI_EXPORT_GALLEYS:
                return O4DOI_ARTICLE_AS_MANIFESTATION;
        }

        return null;
    }
}

?>