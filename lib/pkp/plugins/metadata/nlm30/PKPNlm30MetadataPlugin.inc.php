<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_nlm30
 */

/**
 * @file plugins/metadata/nlm30/PKPNlm30MetadataPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPNlm30MetadataPlugin
 * @ingroup plugins_metadata_nlm30
 *
 * @brief Abstract base class for NLM 3.0 metadata plugins
 */


import('lib.pkp.classes.plugins.MetadataPlugin');

class PKPNlm30MetadataPlugin extends MetadataPlugin {

	/**
	 * Constructor
	 */
    function __construct() {
        parent::__construct();
    }


	//
	// Override protected template methods from PKPPlugin
	//
	
	/**
	 * @see PKPPlugin::getName()
	 */
	function getName(): string {
		return 'Nlm30MetadataPlugin';
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName(): string {
		return __('plugins.metadata.nlm30.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription(): string {
		return __('plugins.metadata.nlm30.description');
	}
}

?>