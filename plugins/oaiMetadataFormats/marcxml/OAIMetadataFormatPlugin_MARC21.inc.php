<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/marcxml/OAIMetadataFormatPlugin_MARC21.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_MARC21
 * @ingroup oai_format
 * @see OAI
 *
 * @brief marc21 metadata format plugin for OAI.
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_MARC21 extends OAIMetadataFormatPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormatPlugin_MARC21() {
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
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'OAIFormatPlugin_MARC21';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.OAIMetadata.marcxml.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.OAIMetadata.marcxml.description');
    }

    /**
     * Get the format class name.
     * @return string
     */
    public function getFormatClass(): string {
        return 'OAIMetadataFormat_MARC21';
    }

    /**
     * Get the metadata prefix.
     * @return string
     */
    public function getMetadataPrefix(): string {
        return 'marcxml';
    }

    /**
     * Get the schema URL.
     * @return string
     */
    public function getSchema(): string {
        return 'http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd';
    }

    /**
     * Get the namespace URL.
     * @return string
     */
    public function getNamespace(): string {
        return 'http://www.loc.gov/MARC21/slim';
    }
}

?>