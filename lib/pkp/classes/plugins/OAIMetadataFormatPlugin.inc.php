<?php
declare(strict_types=1);

/**
 * @file lib/pkp/classes/plugins/OAIMetadataFormatPlugin.inc.php
 *
 * @class OAIMetadataFormatPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for OAI Metadata format plugins
 */

import('classes.plugins.Plugin');
import('lib.pkp.classes.oai.OAIStruct');

class OAIMetadataFormatPlugin extends Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Called as a plugin is registered to the registry
	 * @see PluginRegistry::register()
	 * @param $category String Name of category plugin was registered to
	 * @param $path string
	 * @return boolean True if plugin initialized successfully
	 */
	public function register(string $category, string $path): bool {
		if (parent::register($category, $path)) {
			$this->addLocaleData();
			HookRegistry::register('OAI::metadataFormats', array($this, 'callback_formatRequest'));
			return true;
		}
		return false;
	}

	/**
	 * Get the name of this plugin. Must be overridden by subclasses.
	 * @see Plugin::getName()
	 * @return string
	 */
	public function getName(): string {
		assert(false);
	}

	/**
	 * Get the display name for this plugin.
	 * @see Plugin::getDisplayName()
     * @return string
	 */
	public function getDisplayName(): string {
		assert(false);
	}

	/**
	 * Get a description of this plugin.
	 * @see Plugin::getDescription()
     * @return string
	 */
	public function getDescription(): string {
		assert(false);
	}

	/**
	 * Get the metadata prefix for this plugin's format.
	 * @see OAIStruct::metadataPrefix
     * @return string
	 */
	public function getMetadataPrefix() {
		assert(false);
	}

	/**
	 * Get the OAI schema URL
	 * @see OAIStruct::schema
	 * @return string
	 */
	public function getSchema() {
		return '';
	}

	/**
	 * Get the OAI namespace URL
	 * @see OAIStruct::metadataNamespace
	 * @return string
	 */
	public function getNamespace() {
		return '';
	}

	/**
	 * Get name of class that formats metadata
	 * @see OAIStruct::metadataPrefix
	 * @return string
	 */
	public function getFormatClass() {
		assert(false);
	}

	/**
	 * Callback invoked from OAI::metadataFormats hook.
	 * @see HookRegistry::register()
	 * @param $hookName string
	 * @param $args array
	 * @return boolean false to allow further hooks
	 */
	public function callback_formatRequest($hookName, $args) {
		$namesOnly = $args[0];
		$identifier = $args[1];
		$formats = $args[2];

		if ($namesOnly) {
			$formats = array_merge($formats, array($this->getMetadataPrefix()));
		} else {
			$formatClass = $this->getFormatClass();
			$formats = array_merge(
				$formats,
				array(
					$this->getMetadataPrefix() =>
					new $formatClass(
						$this->getMetadataPrefix(),
						$this->getSchema(),
						$this->getNamespace()
					)
				)
			);
		}

		$args[2] = $formats;
		return false;
	}
}

?>