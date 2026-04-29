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
 * [WIZDAM EDITION] Modernized Search Index Tool.
 */

require(__DIR__ . '/bootstrap.inc.php');

import('classes.search.ArticleSearchIndex');

class rebuildSearchIndex extends CommandLineTool {

    /**
     * Constructor. (Implicitly inherits parent's __construct)
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
     * [WIZDAM NOTE] Using void return type for clarity.
     */
    public function usage(): void {
        echo "Script to rebuild article search index\n"
            . "Usage: {$this->scriptName} [journal_path]\n";
    }

    /**
     * Rebuild the search index for all articles in all journals.
     * @return void
     * [WIZDAM NOTE] Using die(1) for CLI failures instead of exit(1).
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
                // [WIZDAM] Using die(1) for CLI failures
                die (__('search.cli.rebuildIndex.unknownJournal', ['journalPath' => $journalPath]). "\n");
            }
        }

        // Register a router hook so that we can construct
        // useful URLs to journal content.
        // [WIZDAM FIX] Using clean array callable syntax.
        HookRegistry::register('Request::getBaseUrl', [$this, 'callbackBaseUrl']);

        // Let the search implementation re-build the index.
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->rebuildIndex(true, $journal);
    }

    /**
     * Callback to patch the base URL which will be required
     * when constructing galley/supp file download URLs.
     * [WIZDAM CRITICAL NOTE] The second parameter MUST be passed by reference 
     * because the legacy hook expects to modify $params[0] directly.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function callbackBaseUrl(string $hookName, array &$params): bool {
        // $baseUrl must be assigned by reference (&$params[0]) in the legacy method
        // But since PHP 5, object parameters are passed by identifier. We rely on the
        // array parameter itself being passed by reference in the signature (&$params)
        // to correctly modify the first element $params[0].
        
        $params[0] = Config::getVar('general', 'base_url');
        return true;
    }
}

// [WIZDAM] Safe instantiation
$tool = new rebuildSearchIndex($argv ?? []);
$tool->execute();

?>