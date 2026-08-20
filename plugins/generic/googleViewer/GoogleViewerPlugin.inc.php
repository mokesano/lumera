<?php
declare(strict_types=1);

/**
 * @file plugins/generic/googleViewer/GoogleViewerPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GoogleViewerPlugin
 *
 * @brief This plugin enables embedding of the google document viewer for PDF display.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class GoogleViewerPlugin extends GenericPlugin {

    /**
     * Register the plugin.
     * @param string $category
     * @param string $path
     * @return boolean
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
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.googleViewer.name');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.googleViewer.description');
    }

    /**
     * Hook callback function for TemplateManager::include
     * @param string $hookName
     * @param array $args
     */
    public function _includeCallback($hookName, $args) {
        if ($this->getEnabled()) {
            $templateMgr = $args[0];
            $params =& $args[1]; // [NOTE] Reference (&) restored to ensure modification affects the caller

            if (!isset($params['smarty_include_tpl_file'])) return false;

            switch ($params['smarty_include_tpl_file']) {
                case 'article/pdfViewer.tpl':
                    $params['smarty_include_tpl_file'] = $this->getTemplatePath() . 'index.tpl';
                    break;
            }
            return false;
        }
    }

    /**
     * Hook callback function for TemplateManager::display
     * @param string $hookName
     * @param array $args
     */
    public function _displayCallback($hookName, $args) {
        if ($this->getEnabled()) {
            $templateMgr = $args[0];
            $template =& $args[1]; // [NOTE] Reference (&) restored to ensure modification affects the caller

            switch ($template) {
                case 'issue/issueGalley.tpl':
                    $template = $this->getTemplatePath() . 'issueGalley.tpl';
                    break;
            }
            return false;
        }
    }

    /**
     * [WIZDAM] Cegah plugin ini diaktifkan selama PdfJsViewerPlugin
     * sudah aktif -- dicegah di SUMBERNYA (aksi enable itu sendiri
     * ditolak), bukan cuma diselesaikan belakangan lewat prioritas
     * eksekusi hook.
     *
     * Pesan hasil dikirim lewat NotificationManager, BUKAN mekanisme
     * $message/$messageParams form biasa -- konvensi fork ini: semua
     * pesan hasil aksi (apa pun jenisnya) WAJIB tampil sebagai
     * notifikasi lewat NotificationManager, bukan error form biasa.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param object|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if ($verb === 'enable') {
            $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

            /** @var PdfJsViewerPlugin|null $pdfJsPlugin */
            $pdfJsPlugin = PluginRegistry::getPlugin('generic', 'pdfjsviewerplugin');
            if ($pdfJsPlugin && $pdfJsPlugin->getEnabled($request)) {
                $user = $request->getUser();
                if ($user) {
                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $notificationManager->createTrivialNotification(
                        (int) $user->getId(),
                        NOTIFICATION_TYPE_ERROR,
                        ['contents' => __('plugins.generic.googleViewer.pdfJsAlreadyEnabled')]
                    );
                }
                return false;
            }
        }
        return parent::manage($verb, $args, $message, $messageParams, $request);
    }

}
?>