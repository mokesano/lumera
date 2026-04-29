<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/feature/GridFeature.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridFeature
 * @ingroup controllers_grid_feature
 *
 * @brief Base grid feature class. A feature is a type of plugin specific
 * to the grid widgets. It provides several hooks to allow injection of
 * additional grid functionality. This class implements template methods
 * to be extendeded by subclasses.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

class GridFeature {

    /** @var string Feature id */
    protected string $_id;

    /** @var array Feature options */
    protected array $_options = [];

    /**
     * Constructor.
     * @param string $id Feature id.
     */
    public function __construct(string $id) {
        $this->setId($id);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridFeature(string $id) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($id);
    }

    //
    // Getters and setters.
    //
    /**
     * Get feature id.
     * @return string
     */
    public function getId(): string {
        return $this->_id;
    }

    /**
     * Set feature id.
     * @param string $id
     */
    public function setId(string $id): void {
        $this->_id = $id;
    }

    /**
     * Get feature js class options.
     * @return array
     */
    public function getOptions(): array {
        return $this->_options;
    }

    /**
     * Add feature js class options.
     * @param array $options $optionId => $optionValue
     */
    public function addOptions(array $options): void {
        $this->_options = array_merge($this->getOptions(), $options);
    }


    //
    // Protected methods to be used or extended by subclasses.
    //
    /**
     * Set feature js class options. Extend this method to
     * define more feature js class options.
     * @param Request $request
     * @param GridHandler $grid
     */
    public function setOptions($request, $grid): void {
        $renderedElements = $this->fetchUIElements($grid);
        if ($renderedElements) {
            foreach ($renderedElements as $id => $markup) {
                $this->addOptions([$id => $markup]);
            }
        }
    }

    /**
     * Fetch any user interface elements that
     * this feature needs to add its functionality
     * into the grid.
     * @param GridHandler $grid The grid that this feature is attached to.
     * @return array It is expected that the array returns data in this format: $elementId => $elementMarkup
     */
    public function fetchUIElements($grid): array {
        return [];
    }

    /**
     * Return the java script feature class.
     * @return string|null
     */
    public function getJSClass(): ?string {
        return null;
    }


    //
    // Public hooks to be implemented in subclasses.
    //
    /**
     * Hook called every time grid initialize a row object.
     * @param array $args Contains the initialized referenced row object in 'row' array index.
     * @return mixed
     */
    public function getInitializedRowInstance($args) {
        return null;
    }

    /**
     * Hook called on grid category row initialization.
     * @param array $args 'request' => Request, 'grid' => CategoryGridHandler, 'row' => GridCategoryRow
     * @return mixed
     */
    public function getInitializedCategoryRowInstance($args) {
        return null;
    }

    /**
     * Hook called on grid's initialization.
     * @param array $args Contains the grid handler referenced object in 'grid' array index.
     * @return mixed
     */
    public function gridInitialize($args) {
        return null;
    }

    /**
     * Hook called on grid fetching.
     * @param array $args 'grid' => GridHandler, 'request' => Request
     */
    public function fetchGrid($args): void {
        $grid = $args['grid'];
        $request = $args['request'];

        $this->setOptions($request, $grid);
    }

    /**
     * Hook called when save grid items sequence is requested.
     * @param array $args 'request' => PKPRequest, 'grid' => GridHandler
     * @return mixed
     */
    public function saveSequence($args) {
        return null;
    }
}

?>