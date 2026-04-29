<?php
declare(strict_types=1);

/**
 * @file classes/issue/IssuePubIdService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssuePubIdService
 * @ingroup issue
 *
 * @brief Service class to handle Public IDs for Issues, decoupling the Model from PluginRegistry.
 */

class IssuePubIdService {

    /**
     * Get the public ID of the issue by querying active plugins.
     * @param $issue Issue
     * @param $pubIdType string
     * @return string|null
     */
    public static function getPubId($issue, $pubIdType) {
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        if (is_array($pubIdPlugins)) {
            foreach ($pubIdPlugins as $pubIdPlugin) {
                if ($pubIdPlugin->getPubIdType() == $pubIdType) {
                    return $pubIdPlugin->getPubId($issue);
                }
            }
        }
        return null;
    }
}
?>