<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/feature/OrderItemsFeature.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OrderItemsFeature
 * @ingroup controllers_grid_feature
 *
 * @brief Base class for grid widgets ordering functionality.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.feature.GridFeature');

class OrderItemsFeature extends GridFeature {

    /** @var bool */
    protected bool $_overrideRowTemplate;

    /**
     * Constructor.
     * @param bool $overrideRowTemplate
     */
    public function __construct(bool $overrideRowTemplate) {
        parent::__construct('orderItems');
        $this->setOverrideRowTemplate($overrideRowTemplate);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OrderItemsFeature(bool $overrideRowTemplate) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($overrideRowTemplate);
    }


    //
    // Getters and setters.
    //
    
    /**
     * Set override row template flag.
     * @param bool $overrideRowTemplate
     */
    public function setOverrideRowTemplate(bool $overrideRowTemplate): void {
        $this->_overrideRowTemplate = $overrideRowTemplate;
    }

    /**
     * Get override row template flag.
     * @param mixed $gridRow GridRow
     * @return bool
     */
    public function getOverrideRowTemplate($gridRow): bool {
        // Make sure we don't return the override row template
        // flag to objects that are not instances of GridRow class.
        // [WIZDAM] Strict check retained: this specifically targets the base GridRow class,
        // likely to avoid overriding templates on custom subclasses.
        if (is_object($gridRow) && get_class($gridRow) === 'GridRow') {
            return $this->_overrideRowTemplate;
        } else {
            return false;
        }
    }


    //
    // Extended methods from GridFeature.
    //
    
    /**
     * @see GridFeature::setOptions()
     */
    public function setOptions($request, $grid): void {
        parent::setOptions($request, $grid);

        $router = $request->getRouter();
        $this->addOptions([
            'saveItemsSequenceUrl' => $router->url($request, null, null, 'saveSequence', null, $grid->getRequestArgs())
        ]);
    }


    //
    // Hooks implementation.
    //
    
    /**
     * @see GridFeature::getInitializedRowInstance()
     */
    public function getInitializedRowInstance($args) {
        $row = $args['row'];
        if ($row) {
            $this->addRowOrderAction($row);
        }
    }

    /**
     * @see GridFeature::gridInitialize()
     */
    public function gridInitialize($args) {
        $grid = $args['grid'];

        if ($this->isOrderActionNecessary()) {
            import('lib.pkp.classes.linkAction.request.NullAction');
            $grid->addAction(
                new LinkAction(
                    'orderItems',
                    new NullAction(),
                    __('grid.action.order'),
                    'order_items'
                )
            );
        }
    }

    /**
     * @see GridFeature::fetchUIElements()
     */
    public function fetchUIElements($grid): array {
        if ($this->isOrderActionNecessary()) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('gridId', $grid->getId());
            return ['orderFinishControls' => $templateMgr->fetch('controllers/grid/feature/gridOrderFinishControls.tpl')];
        }
        return [];
    }


    //
    // Protected methods.
    //
    
    /**
     * Add grid row order action.
     * @param GridRow $row
     */
    public function addRowOrderAction($row): void {
        if ($this->getOverrideRowTemplate($row)) {
            $row->setTemplate('controllers/grid/gridRow.tpl');
        }

        import('lib.pkp.classes.linkAction.request.NullAction');
        $row->addAction(
            new LinkAction(
                'moveItem',
                new NullAction(),
                '',
                'order_items'
            ), GRID_ACTION_POSITION_ROW_LEFT
        );
    }

    //
    // Protected template methods.
    //
    /**
     * Return if this feature will use
     * a grid level order action. Default is
     * true, override it if needed.
     * @return bool
     */
    public function isOrderActionNecessary(): bool {
        return true;
    }
}

?>