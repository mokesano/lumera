<?php
declare(strict_types=1);

/**
 * @file plugins/themes/custom/CustomThemeSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomThemeSettingsForm
 * @ingroup plugins_generic_customTheme
 *
 * @brief Form for journal managers to modify custom theme plugin settings
 */

import('lib.pkp.classes.form.Form');
import('classes.file.PublicFileManager');

class CustomThemeSettingsForm extends Form
{
    public int $journalId;
    public object $plugin;

    /**
     * Constructor
     *
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct(object $plugin, int $journalId)
    {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * Display the form
     */
    public function display($request = null, $template = null)
    {
        $templateMgr = TemplateManager::getManager();
        
        // Add JS and CSS
        $additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');
        $additionalHeadData .= '<script type="text/javascript" src="' . Request::getBaseUrl() . '/plugins/themes/custom/picker.js"></script>' . "\n";
        $templateMgr->addStyleSheet(Request::getBaseUrl() . '/plugins/themes/custom/picker.css');
        $templateMgr->assign('additionalHeadData', $additionalHeadData);

        // Determine Stylesheet Location
        $stylesheetFilePluginLocation = $this->plugin->getPluginPath() . '/' . $this->plugin->getStylesheetFilename();
        $isPerJournal = (bool) $this->plugin->getSetting($this->journalId, 'customThemePerJournal');
        $canUsePluginPath = $this->canUsePluginPath();
        $stylesheetFileLocation = $stylesheetFilePluginLocation;

        if (!$canUsePluginPath || $isPerJournal) {
            if (!$canUsePluginPath) {
                $templateMgr->assign('disablePluginPath', true);
                $templateMgr->assign('stylesheetFilePluginLocation', $stylesheetFilePluginLocation);
            }
            
            $fileManager = new PublicFileManager();
            $stylesheetFileLocation = $fileManager->getJournalFilesPath($this->journalId) . '/' . $this->plugin->getStylesheetFilename();
        }

        $templateMgr->assign('canSave', $this->isWritable($stylesheetFileLocation));
        $templateMgr->assign('stylesheetFileLocation', $stylesheetFileLocation);

        return parent::display($request, $template);
    }

    /**
     * Initialize form data.
     */
    public function initData(): void
    {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->_data = [
            'customThemeHeaderColour'     => $plugin->getSetting($journalId, 'customThemeHeaderColour'),
            'customThemeLinkColour'       => $plugin->getSetting($journalId, 'customThemeLinkColour'),
            'customThemeBackgroundColour' => $plugin->getSetting($journalId, 'customThemeBackgroundColour'),
            'customThemeForegroundColour' => $plugin->getSetting($journalId, 'customThemeForegroundColour'),
            'customThemePerJournal'       => $plugin->getSetting($journalId, 'customThemePerJournal'),
        ];
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData(): void
    {
        $this->readUserVars([
            'customThemeHeaderColour',
            'customThemeLinkColour',
            'customThemeBackgroundColour',
            'customThemeForegroundColour',
            'customThemePerJournal'
        ]);
    }

    /**
     * Save settings.
     */
    public function execute(...$functionArgs)
    {
        $plugin = $this->plugin;
        $journalId = $this->journalId;
        $css = '';

        // Header and footer colours
        $customThemeHeaderColour = $this->getData('customThemeHeaderColour');
        $plugin->updateSetting($journalId, 'customThemeHeaderColour', $customThemeHeaderColour, 'string');
        $css .= "#header {background-color: $customThemeHeaderColour;}\n";
        $css .= "#footer {background-color: $customThemeHeaderColour;}\n";
        $css .= "table.listing tr.fastTracked {background-color: $customThemeHeaderColour;}\n";

        // Link colours
        $customThemeLinkColour = $this->getData('customThemeLinkColour');
        $plugin->updateSetting($journalId, 'customThemeLinkColour', $customThemeLinkColour, 'string');
        $css .= "a {color: $customThemeLinkColour;}\n";
        $css .= "a:link {color: $customThemeLinkColour;}\n";
        $css .= "a:active {color: $customThemeLinkColour;}\n";
        $css .= "a:visited {color: $customThemeLinkColour;}\n";
        $css .= "a:hover {color: $customThemeLinkColour;}\n";
        $css .= "input.defaultButton {color: $customThemeLinkColour;}\n";

        // Background colours
        $customThemeBackgroundColour = $this->getData('customThemeBackgroundColour');
        $plugin->updateSetting($journalId, 'customThemeBackgroundColour', $customThemeBackgroundColour, 'string');
        $css .= "body {background-color: $customThemeBackgroundColour;}\n";
        $css .= "input.defaultButton {background-color: $customThemeBackgroundColour;}\n";

        // Foreground colours
        $customThemeForegroundColour = $this->getData('customThemeForegroundColour');
        $plugin->updateSetting($journalId, 'customThemeForegroundColour', $customThemeForegroundColour, 'string');
        $css .= "body {color: $customThemeForegroundColour;}\n";
        $css .= "input.defaultButton {color: $customThemeForegroundColour;}\n";

        // Handle File Writing
        $fileManager = new PublicFileManager();
        $customThemePerJournal = (bool) $this->getData('customThemePerJournal');

        if (!$customThemePerJournal && !$this->canUsePluginPath()) {
            $customThemePerJournal = true;
        }

        $plugin->updateSetting($journalId, 'customThemePerJournal', $customThemePerJournal, 'bool');

        if ($customThemePerJournal) {
            $fileManager->writeJournalFile($journalId, $this->plugin->getStylesheetFilename(), $css);
        } else {
            // Using __DIR__ instead of dirname(__FILE__)
            $fileManager->writeFile(__DIR__ . '/' . $this->plugin->getStylesheetFilename(), $css);
        }
    }

    /**
     * Evaluate whether the plugin path is writable and available for use
     */
    protected function canUsePluginPath(): bool
    {
        return is_writable($this->plugin->getPluginPath() . '/' . $this->plugin->getStylesheetFilename());
    }

    /**
     * Evaluate whether a path is writable
     * Check if the filename provided (or the parent directory, if the filename does not exist) can be written
     */
    protected function isWritable(string $filename): bool
    {
        if (is_writable($filename)) {
            return true;
        } elseif (!file_exists($filename) && is_writable(dirname($filename))) {
            return true;
        }
        return false;
    }
}
?>