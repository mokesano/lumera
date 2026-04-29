<?php
declare(strict_types=1);

/**
 * @class PublisherIdPlugin
 * [WIZDAM] Plugin khusus untuk mengelola Publisher ID (Custom Identifier)
 * Modernized for ScholarWizdam (PHP 7.4/8.x Compatibility)
 */

import('classes.plugins.PubIdPlugin');

class PublisherIdPlugin extends PubIdPlugin {
    /**
     * Daftarkan plugin dan pasang "jembatan" (Hook) ke Core
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        // Meneruskan parameter ke parent dengan kepastian tipe data
        if (parent::register($category, $path, $mainContextId)) {
            
            // Pengecekan status aktif plugin berdasarkan konteks jurnal
            if ($this->getEnabled((int) $mainContextId)) {
                
                // Injeksi field ke IssueDAO secara dinamis
                HookRegistry::register(
                    'IssueDAO::getAdditionalFieldNames', 
                    [$this, 'addAdditionalFieldNames']
                );
            }
            return true;
        }
        return false;
    }

    /**
     * Menyuntikkan nama field ke dalam daftar field tambahan DAO
     * @param string $hookName
     * @param array $args [0] => &additionalFields
     * @return bool
     */
    public function addAdditionalFieldNames(string $hookName, array $args): bool {
        $additionalFields =& $args[0];
        // Memastikan tipe ID selalu string untuk menghindari TypeError di core
        $additionalFields[] = 'pub-id::' . (string) $this->getPubIdType();
        return false; 
    }

    // --- Implementasi Fungsi Wajib PubIdPlugin ---

    /**
     * Mendapatkan tipe Publisher ID
     * @return string
     */
    public function getPubIdType(): string { 
        return 'publisher-id'; 
    }

    /**
     * Mendapatkan tipe tampilan Publisher ID
     * @return string
     */
    public function getPubIdDisplayType(): string { 
        return 'Publisher ID'; 
    }

    /**
     * Mendapatkan nama lengkap Publisher ID
     * @return string
     */
    public function getPubIdFullName(): string { 
        return 'Custom Publisher Identifier'; 
    }

    /**
     * Mendapatkan nama tampilan plugin
     * @return string
     */
    public function getDisplayName(): string { 
        return 'Wizdam Publisher ID Plugin'; 
    }

    /**
     * Mendapatkan deskripsi plugin
     * @return string
     */
    public function getDescription(): string { 
        return 'Mengelola identitas khusus penerbit di level volume dan issue untuk kebutuhan pengarsipan dan ekspor.'; 
    }
}
?>