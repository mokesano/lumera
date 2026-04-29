<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customBlockManager/CustomBlockManagerPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomBlockManagerPlugin
 * @ingroup GenericPlugin
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class CustomBlockManagerPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CustomBlockManagerPlugin() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomBlockManagerPlugin(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.customBlockManager.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.customBlockManager.description');
    }

    /**
     * Called as a plugin is registered to the registry.
     * @param $category string
     * @param $path string
     * @return boolean
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
            if ( $this->getEnabled() ) {
                HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory'));
            }
            return true;
        }
        return false;
    }

    /**
     * Callback to load custom block plugins when the 'blocks' category is loaded.
     * @param $hookName string
     * @param $args array [category, &plugins]
     * @return boolean
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];
        
        if ($category === 'blocks') {
            $this->import('CustomBlockPlugin');
            $journal = Request::getJournal();
            
            if ($journal) {
                // Gunakan strtolower agar sesuai DB
                $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
                $blocks = $pluginSettingsDao->getSetting($journal->getId(), strtolower($this->getName()), 'blocks');
                
                if (is_array($blocks)) {
                    $i = 0;
                    foreach ($blocks as $block) {
                        $blockPlugin = new CustomBlockPlugin($block, $this->getName());
                        $seq = $blockPlugin->getSeq();
                        $pluginPath = $blockPlugin->getPluginPath();

                        // Initialize array key if not exists
                        if (!isset($args[1][$seq])) {
                            $args[1][$seq] = array();
                        }
                        
                        $uniqueKey = $pluginPath . $i;
                        // Assign plugin to registry (passed by ref in args)
                        $args[1][$seq][$uniqueKey] = $blockPlugin;
                        $i++;
                        unset($blockPlugin);
                    }
                }
            }
        }
        return false;
    }

    /**
     * Extend the management verbs for this plugin.
     * @param $verbs array
     * @param $request Request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            $verbs[] = array('settings', __('plugins.generic.customBlockManager.settings'));
        }
        return parent::getManagementVerbs($verbs, $request);
    }

    /**
     * Perform management functions for this plugin.
     * @param $verb string
     * @param $args array
     * @param $message string
     * @param $messageParams array
     * @param $request Request
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if ($verb === 'settings') {
            $this->import('CustomBlockPlugin');
            $journal = Request::getJournal();
            $templateMgr = TemplateManager::getManager();
            
            $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
            
            $pageCrumbs = array(
                array(Request::url(null, 'user'), 'navigation.user'),
                array(Request::url(null, 'manager'), 'user.role.manager'),
                array(Request::url(null, 'manager', 'plugins'), 'manager.plugins', true)
            );
            $templateMgr->assign('pageHierarchy', $pageCrumbs);

            $this->import('SettingsForm'); 
            // FIX: Pastikan pakai $journal->getId()
            $form = new SettingsForm($this, $journal->getId());

            if (Request::getUserVar('save')) {
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    $form->display(); 
                    return true;
                } else {
                    $form->display();
                    return true;
                }
            } elseif (Request::getUserVar('addBlock')) {
                $form->readInputData();
                $blocks = $form->getData('blocks');
                if (!is_array($blocks)) $blocks = array();
                array_push($blocks, '');
                $form->setData('blocks', $blocks);
                $form->display();
                return true;
            } elseif (Request::getUserVar('delBlock')) {
                $form->readInputData();
                $delBlock = Request::getUserVar('delBlock');
                if (count($delBlock) == 1) {
                    list($delBlock) = array_keys($delBlock);
                    $delBlock = (int) $delBlock;
                    $blocks = $form->getData('blocks');
                    if (isset($blocks[$delBlock])) {
                         $deletedBlocks = explode(':', $form->getData('deletedBlocks'));
                         array_push($deletedBlocks, $blocks[$delBlock]);
                         $form->setData('deletedBlocks', join(':', $deletedBlocks));
                    }
                    array_splice($blocks, $delBlock, 1);
                    $form->setData('blocks', $blocks);
                }
                $form->display();
                return true;
            } else {
                $form->initData();
                $form->display();
                return true;
            }
        }
        return parent::manage($verb, $args, $message, $messageParams, $request);
    }
}

?>