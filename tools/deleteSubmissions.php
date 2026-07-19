<?php
declare(strict_types=1);

/**
 * @file tools/deleteSubmissions.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDeletionTool
 * @ingroup tools
 *
 * @brief CLI tool to delete submissions
 * [LUMERA] Modernized CLI Tool with explicit property declaration.
 */

require(__DIR__ . '/bootstrap.inc.php');
import('classes.file.ArticleFileManager');

class SubmissionDeletionTool extends CommandLineTool {

    /**
     * Explicit property declaration.
     * @var array
     */
    private array $parameters;

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
        echo "Permanently removes submission(s) and associated information. USE WITH CARE.\n"
            . "Usage: {$this->scriptName} submission_id [...]\n";
    }

    /**
     * Delete submission data and associated files
     */
    public function execute(): void {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        // Menggunakan $this->parameters yang sekarang aman, terdefinisi, dan semantik
        foreach ($this->parameters as $articleId) {
            // [LUMERA] Strict type casting untuk keamanan data
            $id = (int) $articleId;
            $article = $articleDao->getArticle($id);

            if ($article) {
                $articleFileManager = new ArticleFileManager($id);

                if (!file_exists($articleFileManager->filesDir)) {
                    printf("Warning: no files found for submission %d.\n", $id);
                } else {
                    if (!is_writable($articleFileManager->filesDir)) {
                        printf("Error: Skipping submission %d. Can't delete files in %s\n", $id, $articleFileManager->filesDir);
                        continue;
                    } else {
                        $articleFileManager->deleteArticleTree();
                    }
                }

                $articleDao->deleteArticleById($id);
                printf("Success: Submission %d and its files deleted.\n", $id);
                continue;
            }
            printf("Error: Skipping %d. Unknown submission.\n", $id);
        }
    }
}

// [LUMERA] Safe instantiation
$tool = new SubmissionDeletionTool($argv ?? []);
$tool->execute();