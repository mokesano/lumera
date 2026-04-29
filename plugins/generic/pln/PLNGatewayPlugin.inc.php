<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/PLNGatewayPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PLNGatewayPlugin
 * @ingroup plugins_generic_pln
 *
 * @brief Gateway component of web feed plugin
 *
 */

import('classes.plugins.GatewayPlugin');
import('lib.pkp.classes.site.VersionCheck');
import('lib.pkp.classes.db.DBResultRange');
import('lib.pkp.classes.core.ArrayItemIterator');

define('PLN_PLUGIN_PING_ARTICLE_COUNT', 12);

// Archive/Tar.php may not be installed, so supress possible error.
@include_once('Archive/Tar.php');

class PLNGatewayPlugin extends GatewayPlugin {
    
	/** @var $parentPluginName string Name of parent plugin */
	var $parentPluginName;

	/**
	 * Constructor.
	 */
	public function __construct($parentPluginName) {
		parent::__construct();
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * Hide this plugin from the management interface (it's subsidiary)
	 */
	public function getHideManagement(): bool {
		return true;
	}

	/**
     * Get the name of this plugin.
     * @copydoc Plugin::getName
     */
	public function getName(): string {
		return 'PLNGatewayPlugin';
	}

    /**
     * Get the display name of this plugin.
     * @copydoc Plugin::getDisplayName
     */
	public function getDisplayName(): string {
		return __('plugins.generic.plngateway.displayName');
	}

    /**
     * Get a description of the plugin.
     * @copydoc Plugin::getDescription
     */
	public function getDescription(): string {
		return __('plugins.generic.plngateway.description');
	}

	/**
	 * Get the PLN plugin
	 * @return object
	 */
	public function getPLNPlugin() {
		$plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 */
	public function getPluginPath(): string {
		$plugin = $this->getPLNPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	public function getTemplatePath(): string {
		$plugin = $this->getPLNPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * Get whether or not this plugin is enabled. (Should always return true, 
	 * as the parent plugin will take care of loading this one when needed)
	 * @return boolean
	 */
	public function getEnabled(): bool {
		$plugin = $this->getPLNPlugin();
		return $plugin->getEnabled(); // Should always be true anyway if this is loaded
	}

	/**
	 * Get the management verbs for this plugin (override to none so that 
	 * the parent plugin can handle this)
	 * @return array
	 */
	public function getManagementVerbs(array $verbs = [], $request = null): array {
        return array();
	}
        
	/**
	 * Handle fetch requests for this plugin.
	 */
	public function fetch($args, $request) {
		$plugin = $this->getPLNPlugin();
		$templateMgr = TemplateManager::getManager();

		$journal = Request::getJournal();
		$templateMgr->assign_by_ref('journal', $journal);

		$pluginVersionFile = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'version.xml';
		$pluginVersion = VersionCheck::parseVersionXml($pluginVersionFile);
		$templateMgr->assign_by_ref('pluginVersion', $pluginVersion);

		$terms = array();
		$termsAccepted = $plugin->termsAgreed($journal->getId());
		if ($termsAccepted) {
			$templateMgr->assign('termsAccepted', 'yes');
			$terms = unserialize($plugin->getSetting($journal->getId(), 'terms_of_use'));
			$termsAgreement = unserialize($plugin->getSetting($journal->getId(), 'terms_of_use_agreement'));
		} else {
			$templateMgr->assign('termsAccepted', 'no');
		}
		
		$application = PKPApplication::getApplication();
		$products = $application->getEnabledProducts('plugins.generic');
		$curlVersion = 'not installed';
		if(function_exists('curl_version')) {
			$versionArray = curl_version();
			$curlVersion = $versionArray['version'];
		}		
		$prerequisites = array(
			'phpVersion' => PHP_VERSION,
			'curlVersion' => $curlVersion,
			'zipInstalled' => class_exists('ZipArchive') ? 'yes' : 'no',
			'tarInstalled' => class_exists('Archive_Tar') ? 'yes' : 'no',
			'acron' => isset($products['acron']) ? 'yes' : 'no',
			'tasks' => Config::getVar('scheduled_tasks', false) ? 'yes' : 'no',
		);
		$templateMgr->assign_by_ref('prerequisites', $prerequisites);

		$termKeys = array_keys($terms);
		$termsDisplay = array();
		foreach ($termKeys as $key) {
			$termsDisplay[] = array(
				'key' => $key,
				'term' => $terms[$key]['term'],
				'updated' => $terms[$key]['updated'],
				'accepted' => $termsAgreement[$key]
			);
		}
		$templateMgr->assign('termsDisplay', new ArrayItemIterator($termsDisplay));

		$versionDao = DAORegistry::getDAO('VersionDAO');
		$ojsVersion = $versionDao->getCurrentVersion();
		$templateMgr->assign('ojsVersion', $ojsVersion->getVersionString());

		$publishedArticlesDAO = DAORegistry::getDAO('PublishedArticleDAO');
		$range = new DBResultRange(PLN_PLUGIN_PING_ARTICLE_COUNT);
		$publishedArticles = $publishedArticlesDAO->getPublishedArticlesByJournalId($journal->getId(), $range, true);
		$templateMgr->assign_by_ref('articles', $publishedArticles);
		$templateMgr->assign_by_ref('pln_network', $plugin->getSetting($journal->getId(), 'pln_network'));

		$templateMgr->display($this->getTemplatePath() . DIRECTORY_SEPARATOR . 'ping.tpl', 'text/xml');

		return true;
	}
}
?>