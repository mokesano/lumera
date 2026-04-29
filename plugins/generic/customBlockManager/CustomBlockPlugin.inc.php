<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customBlockManager/CustomBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomBlockPlugin
 * @ingroup plugins_generic_customBlockManager
 *
 * @brief Plugin to handle individual custom blocks.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class CustomBlockPlugin extends BlockPlugin {
    
    /** @var string Name of the block (from database) */
    public $blockName;

    /** @var string Name of the parent manager plugin */
    public $parentPluginName;

    /**
     * Constructor
     * @param string|null $blockName
     * @param string|null $parentPluginName
     */
    public function __construct($blockName = null, $parentPluginName = null) {
        parent::__construct();
        if ($blockName) $this->blockName = $blockName;
        if ($parentPluginName) $this->parentPluginName = $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility Constructor
     * @param string|null $blockName
     * @param string|null $parentPluginName
     */
    public function CustomBlockPlugin($blockName = null, $parentPluginName = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomBlockPlugin(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($blockName, $parentPluginName);
    }

    /**
     * Get the management plugin object.
     * @return object|null The parent CustomBlockManagerPlugin instance.
     */
    public function getManagerPlugin() {
        if (!$this->parentPluginName) return null;
        return PluginRegistry::getPlugin('generic', $this->parentPluginName);
    }

    /**
     * Get the current version of the plugin.
     * @return false Custom blocks don't have individual versions.
     */
    public function getCurrentVersion() { 
        return false; 
    }

    /**
     * Get the name of this plugin (the block name).
     * Used for database storage key and routing.
     * @return string
     */
    public function getName(): string { 
        return $this->blockName;
    }

    /**
     * Get the path to the plugin directory.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getManagerPlugin();
        return $plugin ? $plugin->getPluginPath() : '';
    }

    /**
     * Get the path to the template directory.
     * @return string
     */
    public function getTemplatePath(): string {
        $plugin = $this->getManagerPlugin();
        return $plugin ? $plugin->getTemplatePath() : '';
    }

    /**
     * Determine if the block is enabled.
     * Logic: Data Exists = Enabled. Only returns FALSE if Database explicitly says '0'.
     * @param PKPRequest|null $request
     * @return boolean
     */
    public function getEnabled($request = NULL): bool {
        if (!Config::getVar('general', 'installed')) return true;

        $journal = ($request && is_a($request, 'PKPRequest')) ? $request->getJournal() : Request::getJournal();
        $journalId = $journal ? $journal->getId() : 0;

        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        $setting = $pluginSettingsDao->getSetting($journalId, $this->getName(), 'enabled');

        if ($setting === '0') {
            return false;
        }
        return true;
    }

    /**
     * Set the enabled state of the block.
     * @param boolean $enabled
     * @param PKPRequest|null $request
     */
    public function setEnabled(bool $enabled, $request = NULL): bool {
        $journal = ($request && is_a($request, 'PKPRequest')) ? $request->getJournal() : Request::getJournal();
        $journalId = $journal ? $journal->getId() : 0;
        
        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        $pluginSettingsDao->updateSetting($journalId, $this->getName(), 'enabled', $enabled ? '1' : '0', 'string');
        
        return true;
    }

    /**
     * Get the management verbs for this plugin.
     * @param array $verbs
     * @param PKPRequest|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            $verbs[] = array('disable', __('manager.plugins.disable'));
            $verbs[] = array('edit', __('plugins.generic.customBlock.edit'));
        } else {
            $verbs[] = array('enable', __('manager.plugins.enable'));
        }
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin.
     * Handles 'edit' and 'save' actions.
     * 
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array $messageParams
     * @param PKPRequest|null $request
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        // Handle enable/disable langsung
        if ($verb === 'enable') {
            $this->setEnabled(true, $request);
            return false;
        }
        if ($verb === 'disable') {
            $this->setEnabled(false, $request);
            return false;
        }
    
        // Handle edit/save sendiri TANPA delegasi ke parent
        $journal = Request::getJournal();
        $this->import('CustomBlockEditForm');
        $form = new CustomBlockEditForm($this, $journal->getId());
    
        if ($verb === 'edit') {
            if ($form->isLocaleResubmit()) {
                $form->readInputData();
                $form->addTinyMCE();
            } else {
                $form->initData();
            }
            $form->display();
            return true;
        }
        
        if ($verb === 'save') {
            if (Request::getUserVar('cancel')) {
                Request::redirect(null, 'manager', 'plugins');
                return true;
            }
            $form->readInputData();
            if ($form->validate()) {
                $form->save();
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign(array(
                    'currentUrl' => Request::url(null, null, null, array($this->getCategory(), $this->getName(), 'edit')),
                    'pageTitleTranslated' => $this->getDisplayName(),
                    'message' => 'plugins.generic.customBlock.saved',
                    'backLink' => Request::url(null, 'manager', 'plugins'),
                    'backLinkLabel' => 'common.continue'
                ));
                $templateMgr->display('common/message.tpl');
                return true;
            } else {
                $form->display();
                return true;
            }
        }
    
        // Untuk verb lain yang tidak dikenali, baru delegasi ke parent
        return parent::manage($verb, $args, $message, $messageParams, $request);
    }

    /**
     * Get the contents of the block.
     * @param TemplateManager $templateMgr
     * @param PKPRequest|null $request
     * @return string
     */
    public function getContents($templateMgr, $request = NULL) {
        $journal = ($request && is_a($request, 'PKPRequest')) ? $request->getJournal() : Request::getJournal();
        if (!$journal) return '';

        $managerPlugin = $this->getManagerPlugin();
        if (!$managerPlugin) return '';

        $blocks = $managerPlugin->getSetting($journal->getId(), 'blocks');
        $blockContent = $managerPlugin->getSetting($journal->getId(), 'blockContent');
        $locale = AppLocale::getLocale();
        $content = '';
        
        if (is_array($blocks) && is_array($blockContent)) {
             // Find index by block name
             $index = array_search($this->getName(), $blocks);
             // Fallback: try decoding name if encoded
             if ($index === false) {
                 $index = array_search(urldecode($this->getName()), $blocks);
             }
             
             if ($index !== false && isset($blockContent[$index][$locale])) {
                 $content = $blockContent[$index][$locale];
             }
        }

        // Generate safe ID for HTML
        $cleanBlockId = 'customblock-' . preg_replace('/\W+/', '-', $this->blockName);
        $templateMgr->assign('customBlockId', $cleanBlockId);
        $templateMgr->assign('customBlockContent', $content);
        
        return $templateMgr->fetch($this->getTemplatePath() . 'block.tpl');
    }
    
    /**
     * Get the block context.
     * @return int
     */
    public function getBlockContext() {
        if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_RIGHT_SIDEBAR;
        return parent::getBlockContext();
    }

    /**
     * Get the sequence of the block.
     * @return int
     */
    public function getSeq(): int {
        if (!Config::getVar('general', 'installed')) return 1;
        return parent::getSeq();
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        // Return only the block name as the title, cleaner for UI
        return $this->blockName;
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.customBlock.description');
    }
}
?>