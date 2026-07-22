<?php
declare(strict_types=1);

/**
 * @file classes/plugins/ImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImportExportPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for import/export plugins
 */

import('classes.plugins.Plugin');

class ImportExportPlugin extends Plugin {
    
	/**
	 * Constructor
	 */
    public function __construct() {
        parent::__construct();
    }

	/**
	 * Get the name of this plugin. The name must be unique within its category.
     * @see Plugin::getName()
	 * 
	 * @return String name of plugin
	 */
	public function getName(): string {
		assert(false); // Should always be overridden
	}

	/**
	 * Get the display name of this plugin. This name is displayed on the
	 * Journal Manager's import/export page, for example.
     * @see Plugin::getDisplayName()
	 * 
	 * @return String
	 */
	public function getDisplayName(): string {
		return 'Abstract Import/Export Plugin';
	}

	/**
	 * Get a description of the plugin.
     * @see Plugin::getDescription()
	 * 
     * @return String
	 */
	public function getDescription(): string {
		return 'This is the ImportExportPlugin base class. Its functions can be overridden by subclasses to provide import/export functionality for various formats.';
	}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @see Plugin::setBreadcrumbs()
	 * 
	 * @param $crumbs Array ($url, $name, $isTranslated)
	 * @param bool $isSubclass
	 */
	public function setBreadcrumbs($crumbs = [], $isSubclass = false) {
		$templateMgr = TemplateManager::getManager();
		$pageCrumbs = [
			[
				Request::url(null, 'user'),
				'navigation.user'
			],
			[
				Request::url(null, 'manager'),
				'user.role.manager'
			],
			[
				Request::url(null, 'manager', 'importexport'),
				'manager.importExport'
			]
		];
		if ($isSubclass) $pageCrumbs[] = [
			Request::url(null, 'manager', 'importexport', ['plugin', $this->getName()]),
			$this->getDisplayName(),
			true
		];

		$templateMgr->assign('pageHierarchy', array_merge($pageCrumbs, $crumbs));
	}

	/**
	 * Display the import/export plugin UI.
     * @see Plugin::display()
	 * 
	 * @param $args array
	 * @param $request Request
	 */
	public function display($args, $request) {
		$templateManager = TemplateManager::getManager();
		$templateManager->register_function('plugin_url', [&$this, 'smartyPluginUrl']);
	}

	/**
	 * Execute import/export tasks using the command-line interface.
     * @see ImportExportPlugin::usage()
	 * 
	 * @param mixed $scriptName
	 * @param $args
	 */
	public function executeCLI($scriptName, $args) {
		$this->usage($scriptName);
		// Implemented by subclasses
	}

	/**
	 * Display the command-line usage information
     * @see ImportExportPlugin::executeCLI()
	 * 
     * @param $scriptName
	 */
	public function usage($scriptName) {
		// Implemented by subclasses
	}

	/**
	 * Display verbs for the management interface.
     * @see Plugin::getManagementVerbs()
	 * 
     * @param $verbs array
     * @param $request Request
     * @return array
	 */
	public function getManagementVerbs(array $verbs = [], $request = null): array {
		return [
			[
				'importexport',
				__('manager.importExport')
			]
		];
	}

	/**
	 * Perform management functions
     * @see Plugin::manage()
	 * 
     * @param $verb string
     * @param $args array
     * @param $message string|null
     * @param $messageParams mixed
     * @param $request Request
     * @return boolean
	 */
	public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
		if ($verb === 'importexport') {
			Request::redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
		}
		$templateMgr = TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', [&$this, 'smartyPluginUrl']);
		return false;
	}

	/**
	 * Extend the {url ...} smarty to support import/export plugins.
	 * @see SmartyUrlFunction
	 * 
     * @param array $params array
     * @param $smarty Smarty
     * @return string
	 */
	public function smartyPluginUrl($params, $smarty): string {
	    $path = array();
		if (!empty($params['path'])) $path = $params['path'];
		if (!is_array($path)) $path = array($params['path']);

		$managementVerbs = array();
		foreach($this->getManagementVerbs() as $managementVerb) {
			$managementVerbs[] = $managementVerb[0];
		}

		if (count($path) == 1 && in_array($path[0], $managementVerbs)) {
			$params['op'] = 'plugin';
			return parent::smartyPluginUrl($params, $smarty);
		} else {
			$params['path'] = array_merge(array('plugin', $this->getName()), $path);
			return $smarty->smartyUrl($params, $smarty);
		}
	}

}
?>