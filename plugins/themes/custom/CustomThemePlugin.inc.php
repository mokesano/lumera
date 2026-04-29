<?php
declare(strict_types=1);

/**
 * @file plugins/themes/custom/CustomThemePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomThemePlugin
 * @ingroup plugins_themes_uncommon
 *
 * @brief "Custom" theme plugin
 */

import('classes.plugins.ThemePlugin');

class CustomThemePlugin extends ThemePlugin
{
    /**
     * Register the plugin, if enabled; note that this plugin
     * runs under both Journal and Site contexts.
     *
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool
    {
        if (parent::register($category, $path, $mainContextId)) {
            $this->addLocaleData();
            return true;
        }
        return false;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string
     */
    public function getName(): string
    {
        return 'CustomThemePlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string
    {
        return __('plugins.theme.custom.name');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string
    {
        return __('plugins.theme.custom.description');
    }

    /**
     * Get the filename of this plugin's stylesheet.
     * @return string
     */
    public function getStylesheetFilename(): string
    {
        return 'custom.css';
    }

    /**
     * Get the file path to this plugin's stylesheet.
     * @return string
     */
    public function getStylesheetPath(): string
    {
        $journal = Request::getJournal();
        if ($journal && $this->getSetting($journal->getId(), 'customThemePerJournal')) {
            import('classes.file.PublicFileManager');
            $fileManager = new PublicFileManager();
            return $fileManager->getJournalFilesPath($journal->getId());
        }

        return $this->getPluginPath();
    }

    /**
     * Get the available management verbs.
     * @param array $verbs
     * @param Request $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array
    {
        return [['settings', __('plugins.theme.custom.settings')]];
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items
     * to append.
     * @param bool $isSubclass Whether called from a subclass
     * @return void
     */
    public function setBreadcrumbs(bool $isSubclass = false): void
    {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                Request::url(null, 'user'),
                'navigation.user'
            ],
            [
                Request::url(null, 'manager'),
                'user.role.manager'
            ]
        ];

        $pageCrumbs[] = [
            Request::url(null, 'manager', 'plugins'),
            'manager.plugins'
        ];

        $pageCrumbs[] = [
            Request::url(null, 'manager', 'plugins', 'themes'),
            'plugins.categories.themes'
        ];

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     *
     * @param array $params
     * @param object $smarty reference
     */
    public function smartyPluginUrl(array $params, $smarty): string
    {
        $path = [$this->getCategory(), $this->getName()];
        
        if (isset($params['path']) && is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [$params['id']]);
            unset($params['id']);
        }

        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Manage the theme.
     *
     * @param string $verb management action
     * @param array $args
     */
    public function manage(string $verb, array $args, string $message = null, $messageParams = null, $pluginModalContent = null): bool
    {
        if ($verb !== 'settings') {
            return false;
        }

        $journal = Request::getJournal();
        $journalId = ($journal ? $journal->getId() : CONTEXT_ID_NONE);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        $templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);

        $this->import('CustomThemeSettingsForm');
        
        // Strict typing in Form constructor requires int
        $form = new CustomThemeSettingsForm($this, (int) $journalId);

        if (Request::getUserVar('save')) {
            $form->readInputData();
            if ($form->validate()) {
                $form->execute();
                Request::redirect(null, 'manager', 'plugin', ['themes', 'CustomThemePlugin', 'settings']);
            } else {
                $this->setBreadcrumbs(true);
                $form->display();
            }
        } else {
            $this->setBreadcrumbs(true);
            $form->initData();
            $form->display();
        }

        return true;
    }

    /**
     * Activate the theme.
     *
     * @param object $templateMgr reference
     * @return void
     */
    public function activate($templateMgr)
    {
        // Overrides parent::activate because path needs to be changed.
        $stylesheetFilename = $this->getStylesheetFilename();
        if ($stylesheetFilename !== null && $stylesheetFilename !== '') {
            $path = Request::getBaseUrl() . '/' . $this->getStylesheetPath() . '/' . $stylesheetFilename;
            $templateMgr->addStyleSheet($path);
        }
    }
}
?>