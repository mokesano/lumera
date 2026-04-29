<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/rfc1807/OAIMetadataFormatPlugin_RFC1807.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_RFC1807
 * @ingroup oai_format
 * @see OAI
 *
 * @brief rfc1807 metadata format plugin for OAI.
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_RFC1807 extends OAIMetadataFormatPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormatPlugin_RFC1807() {
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
        return 'OAIFormatPlugin_RFC1807';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.OAIMetadata.rfc1807.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.OAIMetadata.rfc1807.description');
    }

    /**
     * Get the format class name.
     * @return string
     */
    public function getFormatClass(): string {
        return 'OAIMetadataFormat_RFC1807';
    }

    /**
     * Get the metadata prefix.
     * @return string
     */
    public function getMetadataPrefix(): string {
        return 'rfc1807';
    }

    /**
     * Get the schema URL.
     * @return string
     */
    public function getSchema(): string {
        return 'http://www.openarchives.org/OAI/1.1/rfc1807.xsd';
    }

    /**
     * Get the namespace URL.
     * @return string
     */
    public function getNamespace(): string {
        return 'http://info.internet.isi.edu:80/in-notes/rfc/files/rfc1807.txt';
    }
}

?>