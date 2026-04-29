<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridDataProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridDataProvider
 * @ingroup classes_controllers_grid
 *
 * @brief Provide access to grid data.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

class GridDataProvider {
    /** 
     * @var array 
     * [WIZDAM] Renamed from $_authorizedContext and typed
     */
    protected array $authorizedContext = [];

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridDataProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }


    //
    // Getters and Setters
    //
    
    /**
     * Set the authorized context once it is established.
     * @param array $authorizedContext
     */
    public function setAuthorizedContext($authorizedContext) {
        $this->authorizedContext = $authorizedContext;
    }

    /**
     * Retrieve an object from the authorized context
     * @param int $assocType
     * @return mixed will return null if the context for the given assoc type does not exist.
     */
    public function getAuthorizedContextObject($assocType) {
        // [WIZDAM] Simplified using Null Coalescing Operator
        return $this->authorizedContext[$assocType] ?? null;
    }

    /**
     * Check whether an object already exists in the authorized context.
     * @param int $assocType
     * @return bool
     */
    public function hasAuthorizedContextObject($assocType): bool {
        return isset($this->authorizedContext[$assocType]);
    }


    //
    // Template methods to be implemented by subclasses
    //
    
    /**
     * Get the authorization policy.
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     * @return PolicySet|null
     */
    public function getAuthorizationPolicy($request, $args, $roleAssignments) {
        assert(false);
        return null;
    }

    /**
     * Get an array with all request parameters
     * necessary to uniquely identify the data
     * selection of this data provider.
     * @return array
     */
    public function getRequestArgs(): array {
        assert(false);
        return [];
    }

    /**
     * Retrieve the data to load into the grid.
     * @return array
     */
    public function loadData(): array {
        assert(false);
        return [];
    }
}

?>