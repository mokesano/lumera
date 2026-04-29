<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/marc/OAIMetadataFormatPlugin_MARC.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_MARC
 * @ingroup oai_format
 *
 * @brief MARC metadata format plugin for OAI.
 * * REFACTORED: Wizdam Edition (Fixed getEnabled fatal error)
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_MARC extends OAIMetadataFormatPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormatPlugin_MARC() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::OAIMetadataFormatPlugin_MARC(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param $category String Name of category plugin was registered to
     * @return boolean True iff plugin initialized successfully; if false,
     * the plugin will not be registered.
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        
        if ($success) {
            // Register to the OAI hook
            HookRegistry::register('OAI::metadataFormats', array($this, 'callback_metadataFormats'));
        }
        return $success;
    }

    /**
     * Hook callback to add this metadata format to the list.
     * @param $hookName string
     * @param $args array [bool $namesOnly, string $identifier, array &$formats]
     * @return boolean
     */
    public function callback_metadataFormats($hookName, $args) {
        // Unpack arguments
        $namesOnly = $args[0];
        $identifier = $args[1];
        $formats =& $args[2]; // Reference required to modify the array

        // Logic to decide if we should add this format
        $prefix = $this->getMetadataPrefix();
        $schema = $this->getSchema();
        $namespace = $this->getNamespace();
        $formatClass = $this->getFormatClass();

        // Instantiate the format class (OAIMetadataFormat_MARC)
        // We assume the file is already loaded via index.php or import
        // If strict loading is needed: 
        // $this->import($formatClass); 
        
        $formatInstance = new $formatClass($prefix, $schema, $namespace);

        if ($namesOnly) {
            $formats[] = $prefix;
        } else {
            $formats[$prefix] = $formatInstance;
        }

        return false; // Don't block other plugins
    }

    /**
     * Get the name of this plugin
     * @return string
     */
    public function getName(): string {
        return 'OAIMetadataFormatPlugin_MARC';
    }

    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.oaiMetadata.marc.displayName');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.oaiMetadata.marc.description');
    }

    /**
     * Get the class name of the metadata format
     * @return string
     */
    public function getFormatClass() {
        return 'OAIMetadataFormat_MARC';
    }

    /**
     * Get the metadata prefix
     * @return string
     */
    public function getMetadataPrefix() {
        return 'oai_marc';
    }

    /**
     * Get the schema URL
     * @return string
     */
    public function getSchema() {
        return 'http://www.openarchives.org/OAI/1.1/oai_marc.xsd';
    }

    /**
     * Get the namespace
     * @return string
     */
    public function getNamespace() {
        return 'http://www.openarchives.org/OAI/1.1/oai_marc';
    }
}

?>