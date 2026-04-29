<?php
declare(strict_types=1);

/**
 * @file plugins/generic/piwik/PiwikPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PiwikPlugin
 * @ingroup plugins_generic_piwik
 *
 * @brief Piwik plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PiwikPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	public function register(string $category, string $path): bool {
		$success = parent::register($category, $path);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Insert Piwik page tag to common footer
			HookRegistry::register('Templates::Common::Footer::PageFooter', array($this, 'insertFooter'));

			// Insert Piwik page tag to article footer
			HookRegistry::register('Templates::Article::Footer::PageFooter', array($this, 'insertFooter'));

			// Insert Piwik page tag to article interstitial footer
			HookRegistry::register('Templates::Article::Interstitial::PageFooter', array($this, 'insertFooter'));

			// Insert Piwik page tag to article pdf interstitial footer
			HookRegistry::register('Templates::Article::PdfInterstitial::PageFooter', array($this, 'insertFooter'));

			// Insert Piwik page tag to reading tools footer
			HookRegistry::register('Templates::Rt::Footer::PageFooter', array($this, 'insertFooter'));

			// Insert Piwik page tag to help footer
			HookRegistry::register('Templates::Help::Footer::PageFooter', array($this, 'insertFooter'));
		}
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category, and should be suitable for part of a filename
	 * (ie short, no spaces, and no dependencies on cases being unique).
	 * @return String name of plugin
	 */
	public function getName(): string {
		return 'PiwikPlugin';
	}

    /**
     * Get display name
     * @return string
     */
	public function getDisplayName(): string {
		return __('plugins.generic.piwik.displayName');
	}

    /**
     * Get description
     * @return string
     */
	public function getDescription(): string {
		return __('plugins.generic.piwik.description');
	}

	/**
	 * Extend the {url ...} smarty to support this plugin.
	 */
	public function smartyPluginUrl(array $params, $smarty): string {
		$path = array($this->getCategory(), $this->getName());
		if (is_array($params['path'])) {
			$params['path'] = array_merge($path, $params['path']);
		} elseif (!empty($params['path'])) {
			$params['path'] = array_merge($path, array($params['path']));
		} else {
			$params['path'] = $path;
		}

		if (!empty($params['id'])) {
			$params['path'] = array_merge($params['path'], array($params['id']));
			unset($params['id']);
		}
		return $smarty->smartyUrl($params, $smarty);
	}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items
	 * to append.
	 * @param $subclass boolean
	 */
	public function setBreadcrumbs($isSubclass = false) {
		$templateMgr = TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			)
		);
		if ($isSubclass) $pageCrumbs[] = array(
			Request::url(null, 'manager', 'plugins'),
			'manager.plugins'
		);

		$templateMgr->assign('pageHierarchy', $pageCrumbs);
	}

	/**
	 * Display verbs for the management interface.
	 */
	public function getManagementVerbs(array $verbs = [], $request = null): array {
	    
	   	// 1. Seragamkan Definisi (tambah $request) 
	    $verbs = array(); // Logika plugin ini 'mengganti'
	    
	    // 2. Seragamkan Pengecekan Konteks (pakai $request)
	    if ($this->getEnabled($request)) { 
           $verbs[] = array(
               'disable',
               __('manager.plugins.disable')
           );
           $verbs[] = array(
               'settings',
               __('plugins.generic.piwik.manager.settings')
           );
        } else {
           $verbs[] = array(
               'enable',
               __('manager.plugins.enable')
           );
        }
        
        // 3. Kembalikan daftar
        return $verbs;
    }

	/**
	 * Determine whether or not this plugin is enabled.
	 */
	public function getEnabled($request = NULL): bool {
		$journal = Request::getJournal();
		if (!$journal) return false;
		return $this->getSetting($journal->getId(), 'enabled');
	}

	/**
	 * Set the enabled/disabled state of this plugin
	 */
	public function setEnabled(bool $enabled, $request = NULL): bool { 
	    return parent::setEnabled($enabled, $request); 
    }

	/**
	 * Insert Piwik page tag to footer
	 */
	public function insertFooter($hookName, $params) {
		if ($this->getEnabled()) {
			$smarty = $params[1];
			$output = $params[2];
			$journal = Request::getJournal();
			$journalId = $journal->getId();
			$journalPath = $journal->getPath();
			$piwikSiteId = $this->getSetting($journalId, 'piwikSiteId');
			$piwikUrl = $this->getSetting($journalId, 'piwikUrl');
			if (!empty($piwikSiteId) && !empty($piwikUrl)) {
				$output .= 	'<!-- Piwik -->'.
						'<script type="text/javascript">'.
						'var pkBaseURL = "'.$piwikUrl.'/";'.
						'document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));'.
						'</script><script type="text/javascript">'.
						'try {'.
						'var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", '.$piwikSiteId.');'.
						'piwikTracker.setDocumentTitle("'.$journalPath.'");'.
						'piwikTracker.trackPageView();'.
						'piwikTracker.enableLinkTracking();'.
						'} catch( err ) {}'.
						'</script><noscript><p><img src="'.$piwikUrl.'/piwik.php?idsite='.$piwikSiteId.'" style="border:0" alt="" /></p></noscript>'.
						'<!-- End Piwik Tag -->';
			}
		}
		return false;
	}

	/**
	 * Perform management functions
	 */
	public function manage(string $verb, array $args, string $message = null, array $messageParams = null, $request = null): bool {
		$templateMgr = TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		$journal = Request::getJournal();
		$returner = true;

		switch ($verb) {
			case 'enable':
				$this->setEnabled(true);
				$returner = false;
				break;
			case 'disable':
				$this->setEnabled(false);
				$returner = false;
				break;
			case 'settings':
				if ($this->getEnabled()) {
					$this->import('PiwikSettingsForm');
					$form = new PiwikSettingsForm($this, $journal->getJournalId());
					if (Request::getUserVar('save')) {
						$form->readInputData();
						if ($form->validate()) {
							$form->execute();
							$request->redirect(null, 'manager', 'plugins', $this->getCategory());
						} else {
							$this->setBreadCrumbs(true);
							$form->display();
						}
					} else {
						$this->setBreadCrumbs(true);
						$form->initData();
						$form->display();
					}
				} else {
					$request->redirect(null, 'manager');
				}
				break;
			default:
				$request->redirect(null, 'manager');
		}
		return $returner;
	}
}
?>