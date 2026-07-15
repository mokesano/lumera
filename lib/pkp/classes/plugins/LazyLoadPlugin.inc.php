<?php
declare(strict_types=1);

/**
 * @file classes/plugins/CachedPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CachedPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for plugins that optionally.
 */

import('classes.plugins.Plugin');

class LazyLoadPlugin extends Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
	 * Override public methods from PKPPlugin.
     * @see PKPPlugin::register()
	 * @param string $category
	 * @param string $path
	 * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if ($success) {
            $this->addLocaleData();
        }
        return $success;
    }

    /**
     * Override protected methods from PKPPlugin
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        // Lazy load enabled plug-ins always use the plugin's class name
        // as plug-in name. Legacy plug-ins will override this method so
        // this implementation is backwards compatible.
        // NB: strtolower is required for PHP4 compatibility.
        return strtolower_codesafe(get_class($this));
    }

    /*
     * Protected methods required to support lazy load.
     */
     
    /**
     * Determine whether or not this plugin is currently enabled.
	 * @see PKPPlugin::getContextSpecificSetting()
	 * @param string $request
     * @return bool
     */
    public function getEnabled($request = null): bool {
        // Cegah fatal error: 
        // Jika Aplikasi belum diinisialisasi (null) saat tugas latar belakang, tidak mungkin ada konteks. 
        // Kembalikan false secara aman tanpa mengakses PKPPlugin.
        if (!PKPApplication::getApplication()) {
            return false;
        }
        
        return (bool) $this->getContextSpecificSetting($this->getSettingMainContext($request), 'enabled');
    }

    /**
     * Set whether or not this plugin is currently enabled.
	 * @see PKPPlugin::updateContextSpecificSetting()
	 * @param bool $enabled
	 * @param mixed $request
	 * @return bool
     */
    public function setEnabled(bool $enabled, $request = null): bool {
        $this->updateContextSpecificSetting($this->getSettingMainContext($request), 'enabled', $enabled, 'bool');
        return true;
    }

}
?>