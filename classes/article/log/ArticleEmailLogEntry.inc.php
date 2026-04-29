<?php
declare(strict_types=1);

/**
 * @file classes/article/log/ArticleEmailLogEntry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleEmailLogEntry
 * @ingroup article_log
 * @see ArticleEmailLogDAO
 *
 * @brief Extension to EmailLogEntry for article-specific log entries.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.log.EmailLogEntry');

// Editor events                      0x30000000
define('ARTICLE_EMAIL_EDITOR_NOTIFY_AUTHOR',             0x30000001);
define('ARTICLE_EMAIL_EDITOR_ASSIGN',                0x30000002);
define('ARTICLE_EMAIL_EDITOR_NOTIFY_AUTHOR_UNSUITABLE',        0x30000003);

// Reviewer events                      0x40000000
define('ARTICLE_EMAIL_REVIEW_NOTIFY_REVIEWER',             0x40000001);
define('ARTICLE_EMAIL_REVIEW_THANK_REVIEWER',             0x40000002);
define('ARTICLE_EMAIL_REVIEW_CANCEL',                0x40000003);
define('ARTICLE_EMAIL_REVIEW_REMIND',                0x40000004);
define('ARTICLE_EMAIL_REVIEW_CONFIRM',                0x40000005);
define('ARTICLE_EMAIL_REVIEW_DECLINE',                0x40000006);
define('ARTICLE_EMAIL_REVIEW_COMPLETE',                0x40000007);
define('ARTICLE_EMAIL_REVIEW_CONFIRM_ACK',            0x40000008);

// Copyeditor events                      0x50000000
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_COPYEDITOR',        0x50000001);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_AUTHOR',            0x50000002);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_FINAL',            0x50000003);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_COMPLETE',         0x50000004);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_AUTHOR_COMPLETE',        0x50000005);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_FINAL_COMPLETE',        0x50000006);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_ACKNOWLEDGE',         0x50000007);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_AUTHOR_ACKNOWLEDGE',      0x50000008);
define('ARTICLE_EMAIL_COPYEDIT_NOTIFY_FINAL_ACKNOWLEDGE',      0x50000009);

// Proofreader events                      0x60000000
define('ARTICLE_EMAIL_PROOFREAD_NOTIFY_AUTHOR',            0x60000001);
define('ARTICLE_EMAIL_PROOFREAD_NOTIFY_AUTHOR_COMPLETE',      0x60000002);
define('ARTICLE_EMAIL_PROOFREAD_THANK_AUTHOR',            0x60000003);
define('ARTICLE_EMAIL_PROOFREAD_NOTIFY_PROOFREADER',         0x60000004);
define('ARTICLE_EMAIL_PROOFREAD_NOTIFY_PROOFREADER_COMPLETE',    0x60000005);
define('ARTICLE_EMAIL_PROOFREAD_THANK_PROOFREADER',        0x60000006);
define('ARTICLE_EMAIL_PROOFREAD_NOTIFY_LAYOUTEDITOR',        0x60000007);
define('ARTICLE_EMAIL_PROOFREAD_NOTIFY_LAYOUTEDITOR_COMPLETE',      0x60000008);
define('ARTICLE_EMAIL_PROOFREAD_THANK_LAYOUTEDITOR',         0x60000009);

// Layout events                      0x70000000
define('ARTICLE_EMAIL_LAYOUT_NOTIFY_EDITOR',             0x70000001);
define('ARTICLE_EMAIL_LAYOUT_THANK_EDITOR',             0x70000002);
define('ARTICLE_EMAIL_LAYOUT_NOTIFY_COMPLETE',            0x70000003);

class ArticleEmailLogEntry extends EmailLogEntry {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleEmailLogEntry() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleEmailLogEntry(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}
?>