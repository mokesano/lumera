<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/PublisherProfileService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class PublisherProfileService
 *
 * @brief Sumber TUNGGAL identitas resmi Penerbit (nama, motto, tagline,
 * legalitas, alamat, logo, warna brand) -- dibaca dari site_settings yang
 * diisi via AboutSiteForm. Dipakai oleh Invoice, LoA, Sertifikat, dan
 * halaman root Penerbit, supaya tidak ada lagi hardcode berulang.
 */

class PublisherProfileService {

    /**
     * @return array ['name','motto','tagline','legalEntity','address',
     *   'logoUrl','colorPrimary','colorSecondary']
     */
    public function getProfile(): array {
        $request = Application::get()->getRequest();
        $site = $request->getSite();

        if (!$site) {
            return $this->_defaults();
        }

        $logoUrl = '';
        $logoSetting = $site->getSetting('publisherLogo');
        if (is_array($logoSetting) && !empty($logoSetting['uploadName'])) {
            $publicDir = (string) Config::getVar('files', 'public_files_dir');
            $logoUrl = $request->getBaseUrl() . '/' . $publicDir . '/site/' . (string) $logoSetting['uploadName'];
        }

        return [
            'name' => (string) ($site->getSetting('publisherName') ?: 'Publisher'),
            'motto' => (string) $site->getLocalizedSetting('publisherMotto'),
            'tagline' => (string) $site->getLocalizedSetting('publisherTagline'),
            'legalEntity' => (string) $site->getSetting('publisherLegalEntity'),
            'address' => (string) $site->getSetting('publisherAddress'),
            'logoUrl' => $logoUrl,
            'colorPrimary' => (string) ($site->getSetting('publisherColorPrimary') ?: '#1a4f8b'),
            'colorSecondary' => (string) ($site->getSetting('publisherColorSecondary') ?: '#28a745'),
        ];
    }

    /**
     * @return array Fallback aman kalau Site belum ter-load sama sekali.
     */
    private function _defaults(): array {
        return [
            'name' => 'Publisher', 'motto' => '', 'tagline' => '',
            'legalEntity' => '', 'address' => '', 'logoUrl' => '',
            'colorPrimary' => '#1a4f8b', 'colorSecondary' => '#28a745',
        ];
    }

}
?>