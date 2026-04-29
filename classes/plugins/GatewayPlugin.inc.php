<?php
declare(strict_types=1);

/**
 * @file classes/plugins/GatewayPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GatewayPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for gateway plugins
 */

import('classes.plugins.Plugin');

class GatewayPlugin extends Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GatewayPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        assert(false); // Should always be overridden
        return 'GatewayPlugin';
    }

    /**
     * Get the display name of this plugin. This name is displayed on the
     * Journal Manager's plugin management page, for example.
     * @return string
     */
    public function getDisplayName(): string {
        // This name should never be displayed because child classes
        // will override this method.
        return 'Abstract Gateway Plugin';
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return 'This is the GatewayPlugin base class. Its functions can be overridden by subclasses to provide import/export functionality for various formats.';
    }

    /**
     * Display verbs for the management interface.
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = [];
        if ($this->getEnabled()) {
            $verbs[] = [
                'disable',
                __('manager.plugins.disable')
            ];
        } else {
            $verbs[] = [
                'enable',
                __('manager.plugins.enable')
            ];
        }
        return $verbs;
    }

    /**
     * Determine whether or not this plugin is enabled.
     * @return bool
     */
    public function getEnabled(): bool {
        $journal = Request::getJournal();
        if (!$journal) return false;
        return (bool) $this->getSetting($journal->getId(), 'enabled');
    }

    /**
     * Set the enabled/disabled state of this plugin
     * @param bool $enabled
     * @return bool
     */
    public function setEnabled(bool $enabled, $request = NULL): bool {
        $journal = Request::getJournal();
        if ($journal) {
            $this->updateSetting(
                $journal->getId(),
                'enabled',
                $enabled ? true : false
            );
            return true;
        }
        return false;
    }

    /**
     * Perform management functions
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param PKPRequest|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message = null, $messageParams = null, $request = null): bool {
        $templateManager = TemplateManager::getManager();
        $templateManager->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        switch ($verb) {
            case 'enable': $this->setEnabled(true); break;
            case 'disable': $this->setEnabled(false); break;
        }
        return false;
    }

    /**
     * Handle fetch requests for this plugin.
     * @param array $args
     * @param object $request
     * @return bool
     */
    public function fetch($args, $request) {
        // Subclasses should override this function.
        return false;
    }
}

?>