<?php
declare(strict_types=1);

/**
 * @file tools/deleteSubmissions.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class deleteSubmissions
 * @ingroup tools
 *
 * @brief CLI tool to delete submissions
 * [WIZDAM EDITION] Modernized CLI Tool.
 */

require(__DIR__ . '/bootstrap.inc.php');

import('classes.file.ArticleFileManager');

class SubmissionDeletionTool extends CommandLineTool {

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        if (count($this->argv) < 1) {
            $this->usage();
            exit(1);
        }

        $this->parameters = $this->argv;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionDeletionTool() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Print command usage information.
     */
    public function usage(): void {
        echo "Permanently removes submission(s) and associated information.  USE WITH CARE.\n"
            . "Usage: {$this->scriptName} submission_id [...]\n";
    }

    /**
     * Delete submission data and associated files
     */
    public function execute(): void {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        foreach($this->parameters as $articleId) {
            // [WIZDAM FIX] Removed legacy reference (&)
            $article = $articleDao->getArticle($articleId);

            if ($article) {
                // remove files first, to prevent orphans
                $articleFileManager = new ArticleFileManager($articleId);

                // Note: Accessing public property filesDir directly (Legacy behavior preserved)
                if (!file_exists($articleFileManager->filesDir)) {
                    printf("Warning: no files found for submission %s.\n", $articleId);
                } else {
                    if (!is_writable($articleFileManager->filesDir)) {
                        printf("Error: Skipping submission %s. Can't delete files in %s\n", $articleId, $articleFileManager->filesDir);
                        continue;
                    } else {
                        $articleFileManager->deleteArticleTree();
                    }
                }

                $articleDao->deleteArticleById($articleId);
                continue;
            }
            printf("Error: Skipping %s. Unknown submission.\n", $articleId);
        }
    }
}

// [WIZDAM] Safe instantiation
$tool = new SubmissionDeletionTool($argv ?? []);
$tool->execute();