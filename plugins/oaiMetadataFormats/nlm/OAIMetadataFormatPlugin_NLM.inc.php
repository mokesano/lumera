<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/nlm/OAIMetadataFormatPlugin_NLM.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_NLM
 * @ingroup oai_format_nlm
 * @see OAI
 *
 * @brief NLM Journal Article metadata format plugin for OAI.
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_NLM extends OAIMetadataFormatPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormatPlugin_NLM() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'OAIMetadataFormatPlugin_NLM';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.oaiMetadata.nlm.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.oaiMetadata.nlm.description');
    }

    /**
     * Get the format class name.
     * @return string
     */
    public function getFormatClass(): string {
        return 'OAIMetadataFormat_NLM';
    }

    /**
     * Get the metadata prefix.
     * @return string
     */
    public function getMetadataPrefix(): string {
        return 'nlm';
    }

    /**
     * Get the schema URL.
     * @return string
     */
    public function getSchema(): string {
        return 'http://dtd.nlm.nih.gov/publishing/2.3/xsd/journalpublishing.xsd';
    }

    /**
     * Get the namespace URL.
     * @return string
     */
    public function getNamespace(): string {
        return 'http://dtd.nlm.nih.gov/publishing/2.3';
    }
}
?>