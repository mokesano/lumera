<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pdfJsViewer/PdfJsViewerPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PdfJsViewerPlugin
 *
 * @brief This plugin enables embedding of the pdf.js viewer for PDF display
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PdfJsViewerPlugin extends GenericPlugin {

    /**
     * Register the plugin.
     * @param string $category Plugin category
     * @param string $path Plugin path
     * @return boolean true for success
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                HookRegistry::register('TemplateManager::include', [$this, '_includeCallback']);
                HookRegistry::register('TemplateManager::display', [$this, '_displayCallback']);
            }

            return true;
        }
        return false;
    }

    /**
     * Get the plugin name
     * @copydoc Plugin::getDisplayName
     */
    public function getDisplayName(): string {
        return __('plugins.generic.pdfJsViewer.name');
    }

    /**
     * Get the plugin description
     * @copydoc Plugin::getDescription
     */
    public function getDescription(): string {
        return __('plugins.generic.pdfJsViewer.description');
    }

    /**
     * Hook callback function for TemplateManager::include
     * @param string $hookName Hook name
     * @param array $args Hook arguments
     */
    public function _includeCallback($hookName, $args) {
        if ($this->getEnabled()) {
            $templateMgr = $args[0];
            // Reference needed for array modification ($params)
            $params =& $args[1];

            if (!isset($params['smarty_include_tpl_file'])) return false;

            switch ($params['smarty_include_tpl_file']) {
                case 'article/pdfViewer.tpl':
                    $templatePath = $this->getTemplatePath();
                    $templateMgr->assign('pluginTemplatePath', $templatePath);
                    $templateMgr->assign('pluginUrl', Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath());
                    $params['smarty_include_tpl_file'] = $templatePath . 'articleGalley.tpl';
                    break;
            }
            return false;
        }
    }

    /**
     * Hook callback function for TemplateManager::display
     * @param string $hookName Hook name
     * @param array $args Hook arguments
     */
    public function _displayCallback($hookName, $args) {
        if ($this->getEnabled()) {
            $templateMgr = $args[0];
            // Reference needed for string modification ($template path)
            $template =& $args[1];

            switch ($template) {
                case 'issue/issueGalley.tpl':
                    $templatePath = $this->getTemplatePath();
                    $templateMgr->assign('pluginTemplatePath', $templatePath);
                    $templateMgr->assign('pluginUrl', Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath());
                    $template = $templatePath . 'issueGalley.tpl';
                    break;
            }
            return false;
        }
    }

    /**
     * Get the template path
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }
}
?>