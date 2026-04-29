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
    function __construct() {
        parent::__construct();
    }

	/**
	 * Get the name of this plugin. The name must be unique within its category.
     * @see Plugin::getName()
	 * @return String name of plugin
	 */
	function getName(): string {
		assert(false); // Should always be overridden
	}

	/**
	 * Get the display name of this plugin. This name is displayed on the
	 * Journal Manager's import/export page, for example.
     * @see Plugin::getDisplayName()
	 * @return String
	 */
	function getDisplayName(): string {
		// This name should never be displayed because child classes
		// will override this method.
		return 'Abstract Import/Export Plugin';
	}

	/**
	 * Get a description of the plugin.
     * @see Plugin::getDescription()
     * @return String
	 */
	function getDescription(): string {
		return 'This is the ImportExportPlugin base class. Its functions can be overridden by subclasses to provide import/export functionality for various formats.';
	}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @see Plugin::setBreadcrumbs()
	 * @param $crumbs Array ($url, $name, $isTranslated)
	 * @param $subclass boolean
	 */
	function setBreadcrumbs($crumbs = array(), $isSubclass = false) {
		$templateMgr = TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			),
			array (
				Request::url(null, 'manager', 'importexport'),
				'manager.importExport'
			)
		);
		if ($isSubclass) $pageCrumbs[] = array(
			Request::url(null, 'manager', 'importexport', array('plugin', $this->getName())),
			$this->getDisplayName(),
			true
		);

		$templateMgr->assign('pageHierarchy', array_merge($pageCrumbs, $crumbs));
	}

	/**
	 * Display the import/export plugin UI.
     * @see Plugin::display()
	 * @param $args array The array of arguments the user supplied.
	 * @param $request Request
	 */
	function display($args, $request) {
		$templateManager = TemplateManager::getManager();
		$templateManager->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
	}

	/**
	 * Execute import/export tasks using the command-line interface.
     * @see ImportExportPlugin::usage()
	 * @param $scriptName The name of the command-line script
	 * @param $args Parameters to the plugin
	 */
	function executeCLI($scriptName, $args) {
		$this->usage();
		// Implemented by subclasses
	}

	/**
	 * Display the command-line usage information
     * @see ImportExportPlugin::executeCLI()
     * @param $scriptName
	 */
	function usage($scriptName) {
		// Implemented by subclasses
	}

	/**
	 * Display verbs for the management interface.
     * @see Plugin::getManagementVerbs()
     * @param $verbs array The existing management verbs
     * @param $request Request
     * @return array
	 */
	function getManagementVerbs(array $verbs = [], $request = null): array {
		return array(
			array(
				'importexport',
				__('manager.importExport')
			)
		);
	}

	/**
	 * Perform management functions
     * @see Plugin::manage()
     * @param $verb string The management verb to execute
     * @param $args array The arguments to the management verb
     * @param $message string|null Optional message to display to the user
     * @param $messageParams mixed Optional parameters to the message
     * @param $request Request
     * @return boolean
	 */
	function manage(string $verb, array $args, string $message = null, $messageParams = null, $request = null): bool {
		if ($verb === 'importexport') {
			Request::redirect(null, 'manager', 'importexport', array('plugin', $this->getName()));
		}
		$templateMgr = TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		return false;
	}

	/**
	 * Extend the {url ...} smarty to support import/export plugins.
     * @param $params array The parameters to the {url ...} call
     * @param $smarty Smarty
     * @return string
     * @see SmartyUrlFunction
	 */
	function smartyPluginUrl($params, $smarty): string {
	    $path = array();
		if (!empty($params['path'])) $path = $params['path'];
		if (!is_array($path)) $path = array($params['path']);

		// Check whether our path points to a management verb.
		$managementVerbs = array();
		foreach($this->getManagementVerbs() as $managementVerb) {
			$managementVerbs[] = $managementVerb[0];
		}
		if (count($path) == 1 && in_array($path[0], $managementVerbs)) {
			// Management verbs will be routed to the plugin's manage method.
			$params['op'] = 'plugin';
			return parent::smartyPluginUrl($params, $smarty);
		} else {
			// All other paths will be routed to the plugin's display method.
			$params['path'] = array_merge(array('plugin', $this->getName()), $path);
			return $smarty->smartyUrl($params, $smarty);
		}
	}
}

?>