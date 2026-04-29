<?php
declare(strict_types=1);

/**
 * @file plugins/generic/translator/TranslatorPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TranslatorPlugin
 * @ingroup plugins_generic_translator
 *
 * @brief This plugin helps with translation maintenance.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class TranslatorPlugin extends GenericPlugin {
    
    /**
     * Register the plugin
     * @copydoc Plugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register($category, $path, $mainContextId = null): bool {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled()) {
                $this->addHelpData();
                // [WIZDAM FIX] Modern HookRegistry syntax
                HookRegistry::register('LoadHandler', [$this, 'handleRequest']);
            }
            return true;
        }
        return false;
    }

    /**
     * Hook registry function that is called when the translation handler is requested.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function handleRequest($hookName, $args) {
        $page = $args[0];
        $op = $args[1];
        $sourceFile = $args[2];

        if ($page === 'translate') {
            $this->import('TranslatorHandler');
            Registry::set('plugin', $this);
            define('HANDLER_CLASS', 'TranslatorHandler');
            return true;
        }

        return false;
    }

    /**
     * Get the display name of this plugin
     * @copydoc Plugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.translator.name');
    }

    /**
     * Get the description of this plugin
     * @copydoc Plugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.translator.description');
    }

    /**
     * Indicate that this is a site plugin
     * @copydoc Plugin::isSitePlugin()
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * Get management verbs
     * @copydoc Plugin::getManagementVerbs()
     * @param array $verbs
     * @param Request|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled()) {
            $verbs[] = ['translate', __('plugins.generic.translator.translate')];
        }
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin
     * @param string $verb
     * @param array $args
     * @param string $message Result status message
     * @param array $messageParams Parameters for the message key
     * @param Request $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) {
            return false;
        }

        switch ($verb) {
            case 'translate':
                Request::redirect(null, 'translate');
                return false;
            default:
                // Unknown management verb
                return false;
        }
    }
}

?>