<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/quickSubmit/QuickSubmitPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class QuickSubmitPlugin
 * @ingroup plugins_importexport_quickSubmit
 *
 * @brief Quick Submit one-page submission plugin
 */

import('classes.plugins.ImportExportPlugin');

class QuickSubmitPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function QuickSubmitPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path to plugin
     * @return bool True iff plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'QuickSubmitPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.quickSubmit.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.quickSubmit.description');
    }

    /**
     * Display the plugin.
     * @param array $args
     * @param object $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        AppLocale::requireComponents(LOCALE_COMPONENT_OJS_AUTHOR, LOCALE_COMPONENT_OJS_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION);
        $this->setBreadcrumbs();

        if (array_shift($args) == 'saveSubmit') {
            $this->saveSubmit($args, $request);
        } else {
            $this->import('QuickSubmitForm');
            $form = new QuickSubmitForm($this, $request);
            if ($form->isLocaleResubmit()) {
                $form->readInputData();
            } else {
                $form->initData();
            }
            $form->display();
        }
    }

    /**
     * Save the submitted form
     * @param array $args
     * @param object $request
     */
    public function saveSubmit($args, $request): void {
        $templateMgr = TemplateManager::getManager();

        $this->import('QuickSubmitForm');
        $form = new QuickSubmitForm($this, $request);
        $form->readInputData();
        $formLocale = $form->getFormLocale();

        $editData = false;

        if ($request->getUserVar('addAuthor')) {
            $editData = true;
            $authors = $form->getData('authors');
            $authors[] = [];
            $form->setData('authors', $authors);
        } elseif (($delAuthor = $request->getUserVar('delAuthor')) && count($delAuthor) == 1) {
            $editData = true;
            $delAuthorKeys = array_keys($delAuthor);
            $delAuthorIndex = (int) array_shift($delAuthorKeys);
            
            $authors = $form->getData('authors');
            if (isset($authors[$delAuthorIndex]['authorId']) && !empty($authors[$delAuthorIndex]['authorId'])) {
                $deletedAuthors = explode(':', $form->getData('deletedAuthors'));
                $deletedAuthors[] = $authors[$delAuthorIndex]['authorId'];
                $form->setData('deletedAuthors', implode(':', $deletedAuthors));
            }
            array_splice($authors, $delAuthorIndex, 1);
            $form->setData('authors', $authors);

            if ($form->getData('primaryContact') == $delAuthorIndex) {
                $form->setData('primaryContact', 0);
            }
        } elseif ($request->getUserVar('moveAuthor')) {
            $editData = true;
            $moveAuthorDir = $request->getUserVar('moveAuthorDir');
            $moveAuthorDir = $moveAuthorDir == 'u' ? 'u' : 'd';
            $moveAuthorIndex = (int) $request->getUserVar('moveAuthorIndex');
            $authors = $form->getData('authors');

            if (!(($moveAuthorDir == 'u' && $moveAuthorIndex <= 0) || ($moveAuthorDir == 'd' && $moveAuthorIndex >= count($authors) - 1))) {
                $tmpAuthor = $authors[$moveAuthorIndex];
                $primaryContact = $form->getData('primaryContact');
                if ($moveAuthorDir == 'u') {
                    $authors[$moveAuthorIndex] = $authors[$moveAuthorIndex - 1];
                    $authors[$moveAuthorIndex - 1] = $tmpAuthor;
                    if ($primaryContact == $moveAuthorIndex) {
                        $form->setData('primaryContact', $moveAuthorIndex - 1);
                    } elseif ($primaryContact == ($moveAuthorIndex - 1)) {
                        $form->setData('primaryContact', $moveAuthorIndex);
                    }
                } else {
                    $authors[$moveAuthorIndex] = $authors[$moveAuthorIndex + 1];
                    $authors[$moveAuthorIndex + 1] = $tmpAuthor;
                    if ($primaryContact == $moveAuthorIndex) {
                        $form->setData('primaryContact', $moveAuthorIndex + 1);
                    } elseif ($primaryContact == ($moveAuthorIndex + 1)) {
                        $form->setData('primaryContact', $moveAuthorIndex);
                    }
                }
            }
            $form->setData('authors', $authors);
        } elseif ($request->getUserVar('uploadSubmissionFile')) {
            $editData = true;
            $tempFileId = $form->getData('tempFileId');
            $tempFileId[$formLocale] = $form->uploadSubmissionFile('submissionFile');
            $form->setData('tempFileId', $tempFileId);
        }

        if ($request->getUserVar('createAnother') && $form->validate()) {
            $form->execute();
            $request->redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
        } elseif (!$editData && $form->validate()) {
            $form->execute();
            $templateMgr->display($this->getTemplatePath() . 'submitSuccess.tpl');
        } else {
            $form->display();
        }
    }

    /**
     * Extend the {url ...} for smarty to support this plugin.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyPluginUrl($params, $smarty): string {
        $path = ['plugin', $this->getName()];
        if (is_array($params['path'])) {
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
}

?>