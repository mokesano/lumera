<?php
declare(strict_types=1);

/**
 * @file classes/plugins/BlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BlockPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for block plugins
 */

define('BLOCK_CONTEXT_LEFT_SIDEBAR',	0x00000001);
define('BLOCK_CONTEXT_RIGHT_SIDEBAR',	0x00000002);
define('BLOCK_CONTEXT_HOMEPAGE',		0x00000003);

import('lib.pkp.classes.plugins.LazyLoadPlugin');

class BlockPlugin extends LazyLoadPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function BlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /*
     * Override public methods from PKPPlugin
     */

    /**
     * Register the plugin to its associated hooks.
     * @param string $category
     * @param string $path
     * @return bool
     * @see PKPPlugin::register()
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if ($success && $this->getEnabled()) {
            $contextMap = $this->getContextMap();
            $blockContext = $this->getBlockContext();
            if (isset($contextMap[$blockContext])) {
                $hookName = $contextMap[$blockContext];
                HookRegistry::register($hookName, [&$this, 'callback']);
            }
        }
        return $success;
    }

    /*
     * Override protected methods from PKPPlugin
     */

    /**
     * Get the sequence information for this plugin.
     * NB: In the case of block plugins, higher numbers move
     * plugins down the page compared to other blocks.
     * @return int
     * @see PKPPlugin::getSeq()
     */
    public function getSeq(): int {
        // [LUMERA SAFE CAST] Use is_numeric to prevent casting errors if setting is null/uninitialized
        $seq = $this->getContextSpecificSetting($this->getSettingMainContext(), 'seq');
        return is_numeric($seq) ? (int) $seq : 0;
    }

    /*
     * Block Plugin specific methods
     */

    /**
     * Set the sequence information for this plugin.
     * NB: In the case of block plugins, higher numbers move
     * plugins down the page compared to other blocks.
     * @param mixed $seq
     * @return bool
     */
    public function setSeq($seq) {
        $seq = is_numeric($seq) ? (int) $seq : 0;
        return $this->updateContextSpecificSetting($this->getSettingMainContext(), 'seq', $seq, 'int');
    }

    /**
     * Get the block context (e.g. BLOCK_CONTEXT_...) for this block.
     * @return int|null
     */
    public function getBlockContext() {
        $context = $this->getContextSpecificSetting($this->getSettingMainContext(), 'context');
        return is_numeric($context) ? (int) $context : null;
    }

    /**
     * Set the block context (e.g. BLOCK_CONTEXT_...) for this block.
     * @param mixed $context
     * @return bool
     */
    public function setBlockContext($context) {
        $context = is_numeric($context) ? (int) $context : 0;
        return $this->updateContextSpecificSetting($this->getSettingMainContext(), 'context', $context, 'int');
    }

    /**
     * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
     * @return array
     */
    public function getSupportedContexts() {
        // Will return left and right sidebar as this is the most frequent use case.
        return [BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR];
    }

    /**
     * Get an associative array linking block context to hook name.
     * @return array
     */
    public function &getContextMap() {
        static $contextMap = [
            BLOCK_CONTEXT_LEFT_SIDEBAR => 'Templates::Common::LeftSidebar',
            BLOCK_CONTEXT_RIGHT_SIDEBAR => 'Templates::Common::RightSidebar',
        ];

        $homepageHook = $this->_getContextSpecificHomepageHook();
        if ($homepageHook !== null) {
            $contextMap[BLOCK_CONTEXT_HOMEPAGE] = $homepageHook;
        }

        // [LUMERA FIX] Safely pass static variable by reference to HookRegistry
        $refContextMap = &$contextMap;
        HookRegistry::dispatch('BlockPlugin::getContextMap', [&$this, &$refContextMap]);
        
        return $contextMap;
    }

    /**
     * Get the filename of the template block. (Default behavior may
     * be overridden through some combination of this function and the
     * getContents function.)
     * Returning null from this function results in an empty display.
     * @param object|null $request
     * @return string
     */
    public function getBlockTemplateFilename($request = null) {
        return 'block.tpl';
    }

    /**
     * Get the HTML contents for this block.
     * @param object $templateMgr
     * @param object|null $request Optional for legacy plugins
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        return '';
    }

    /**
     * Callback that renders the block.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callback($hookName, $args) {
        $params = $args[0] ?? null;
        $templateMgr = $args[1] ?? null;

        if (!$templateMgr) {
            return false;
        }

        // [LUMERA] Singleton Fallback for Request
        $request = Application::get()->getRequest();

        if (!$this->getEnabled($request)) {
            return false;
        }

        $templateMgr->assign('blockTemplate', $this->getBlockTemplateFilename($request));
        $templateMgr->assign('blockPlugin', $this);

        $template = $this->getContents($templateMgr, $request);

        if ($template !== '') {
            echo $template;
        }

        return false;
    }

    /*
     * Private helper methods
     */

    /**
     * The application specific context home page hook name.
     * @return string|null
     */
    public function _getContextSpecificHomepageHook() {
        $application = PKPApplication::getApplication();

        if ($application->getContextDepth() === 0) {
            return null;
        }

        $contextList = $application->getContextList();
        if (empty($contextList)) {
            return null;
        }
        
        return 'Templates::Index::' . array_shift($contextList);
    }

}
?>