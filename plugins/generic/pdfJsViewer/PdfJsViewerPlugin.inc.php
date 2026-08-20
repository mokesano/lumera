<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pdfJsViewer/PdfJsViewerPlugin.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * @class PdfJsViewerPlugin
 *
 * @brief This plugin enables embedding of the pdf.js viewer for PDF display.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PdfJsViewerPlugin extends GenericPlugin {

    /**
     * [WIZDAM] Prioritas registrasi lebih tinggi (nilai lebih rendah)
     * dibanding default (0). PluginRegistry::loadCategory() memanggil
     * ksort($plugins) berdasarkan getSeq() SEBELUM mendaftarkan hook tiap
     * plugin -- tanpa override ini, urutan "siapa lebih dulu mengklaim
     * target 'article/pdfViewer.tpl' di hook TemplateManager::include"
     * ditentukan readdir() filesystem, yang TIDAK DIJAMIN alfabetis/
     * konsisten lintas server.
     *
     * pdf.js SELF-HOSTED (vendor lokal, tidak bergantung layanan pihak
     * ketiga mana pun) -- dijadikan pemenang deterministik dibanding
     * GoogleViewerPlugin (bergantung layanan docs.google.com/viewer yang
     * bisa gagal karena proteksi bot Cloudflare, rate-limit, atau
     * keandalan layanan itu sendiri di luar kendali kita) setiap kali
     * KEDUA plugin sama-sama aktif. Tidak perlu plugin ini tahu apa pun
     * soal GoogleViewerPlugin secara langsung -- murni lewat mekanisme
     * seq resmi PluginRegistry.
     * @return int
     */
    public function getSeq(): int {
        return -1;
    }

    /**
     * Register the plugin.
     * @param string $category
     * @param string $path
     * @return bool
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
     * @param string $hookName
     * @param array $args
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
                    break; // Jika return true; halaman pdf gagal tampil
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
            // Reference needed for string modification ($template path)
            $template =& $args[1];

            switch ($template) {
                case 'issue/issueGalley.tpl':
                    $templatePath = $this->getTemplatePath();
                    $templateMgr->assign('pluginTemplatePath', $templatePath);
                    $templateMgr->assign('pluginUrl', Request::getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath());
                    $template = $templatePath . 'issueGalley.tpl';
                    break; // Jika return true; halaman pdf gagal tampil
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