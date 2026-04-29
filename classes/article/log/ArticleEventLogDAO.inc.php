<?php
declare(strict_types=1);

/**
 * @file classes/article/log/ArticleEventLogDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleEventLogDAO
 * @ingroup article_log
 * @see EventLogDAO
 *
 * @brief Extension to EventLogDAO for article-specific log entries.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.log.EventLogDAO');
import('classes.article.log.ArticleEventLogEntry');

class ArticleEventLogDAO extends EventLogDAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleEventLogDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleEventLogDAO(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Return a new data object
     * @return ArticleEventLogEntry
     */
    public function newDataObject(): ArticleEventLogEntry {
        return new ArticleEventLogEntry();
    }
}
?>