<?php
declare(strict_types=1);

/**
 * @file classes/plugins/CitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CitationPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for citation plugins
 */

import('classes.plugins.Plugin');

class CitationPlugin extends Plugin {
    
	/**
	 * Constructor
	 */
    public function __construct() {
        parent::__construct();
    }

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	public function getName(): string {
		assert(false); // Should always be overridden
	}

	/**
	 * Get the display name of this plugin. This name is displayed on the
	 * Journal Manager's setup page 5, for example.
	 * @return String
	 */
	public function getDisplayName(): string {
		// This name should never be displayed because child classes
		// will override this method.
		return 'Abstract Citation Plugin';
	}

	/**
	 * Get the citation format name for this plugin.
	 * @return String
	 */
	public function getCitationFormatName(): string {
		// must be implemented by sub-classes
		assert(false);
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	public function getDescription(): string {
		return 'This is the CitationPlugin base class. Its functions can be overridden by subclasses to provide citation support.';
	}

	/**
	 * Used by the cite function to embed an HTML citation in the
	 * templates/rt/captureCite.tpl template, which ships with OJS.
	 */
	public function displayCitationHook($hookName, $args) {
		$params = $args[0];
		$templateMgr = $args[1];

		echo $templateMgr->fetch($this->getTemplatePath() . '/citation.tpl');
		return true;
	}

	/**
	 * Display an HTML-formatted citation. Default implementation displays
	 * an HTML-based citation using the citation.tpl template in the plugin
	 * path.
	 * @param $article object
	 * @param $issue object
	 */
	public function displayCitation($article, $issue, $journal) {
		HookRegistry::register('Template::RT::CaptureCite', array(&$this, 'displayCitationHook'));
		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign('citationPlugin', $this);
		$templateMgr->assign('article', $article);
		$templateMgr->assign('issue', $issue);
		$templateMgr->assign('journal', $journal);
		$templateMgr->display('rt/captureCite.tpl');
	}

	/**
	 * Return an HTML-formatted citation. Default implementation displays
	 * an HTML-based citation using the citation.tpl template in the plugin
	 * path.
	 * @param $article object
	 * @param $issue object
	 */
	public function fetchCitation($article, $issue, $journal) {
		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign('citationPlugin', $this);
		$templateMgr->assign('article', $article);
		$templateMgr->assign('issue', $issue);
		$templateMgr->assign('journal', $journal);
		return $templateMgr->fetch($this->getTemplatePath() . '/citation.tpl');
	}
}

?>