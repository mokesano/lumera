<?php
declare(strict_types=1);

/**
 * @file tools/rebuildSearchIndex.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class rebuildSearchIndex
 * @ingroup tools
 *
 * @brief CLI tool to rebuild the article keyword search database.
 * [LUMERA] Modernized Search Index Tool.
 */

require(__DIR__ . '/bootstrap.inc.php');

import('classes.search.ArticleSearchIndex');

class rebuildSearchIndex extends CommandLineTool {

    /**
     * Constructor.
     * @param array $argv
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function rebuildSearchIndex($argv = []) {
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
     * @return void
     */
    public function usage(): void {
        echo "Script to rebuild article search index\n"
            . "Usage: {$this->scriptName} [journal_path]\n";
    }

    /**
     * Rebuild the search index for all articles in all journals.
     * @return void
     */
    public function execute(): void {
        /** @var Journal|null $journal */
        $journal = null;
        
        // If we have an argument, this must be a journal path.
        if (count($this->argv)) {
            $journalPath = array_shift($this->argv);
            
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getJournalByPath($journalPath);
            
            if (!$journal) {
                die (__('search.cli.rebuildIndex.unknownJournal', ['journalPath' => $journalPath]). "\n");
            }
        }

        HookRegistry::register('Request::getBaseUrl', [$this, 'callbackBaseUrl']);

        // Let the search implementation re-build the index.
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->rebuildIndex(true, $journal);
    }

    /**
     * Callback to patch the base URL which will be required
     * when constructing galley/supp file download URLs.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function callbackBaseUrl(string $hookName, array &$params): bool {
        $params[0] = Config::getVar('general', 'base_url');
        return true;
    }
}

// [WIZDAM] Safe instantiation
$tool = new rebuildSearchIndex($argv ?? []);
$tool->execute();