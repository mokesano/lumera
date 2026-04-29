<?php
declare(strict_types=1);

/**
 * @file tools/CopyAccessLogFileTool.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyAccessLogFileTool
 * @ingroup tools
 *
 * @brief CLI tool to copy apache log files while filtering entries
 * related only to the current instalation.
 * [WIZDAM EDITION] Modernized CLI Tool.
 */

require(dirname(__DIR__) . '/tools/bootstrap.inc.php');

// Bring in the file loader folder constants.
import('lib.pkp.classes.task.FileLoader');

class CopyAccessLogFileTool extends CommandLineTool {

    /** @var string */
    protected $_usageStatsDir = '';

    /** @var string */
    protected $_tmpDir = '';

    /** @var array */
    protected $_usageStatsFiles = [];

    /** @var string */
    protected $_journalPaths = '';

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        AppLocale::requireComponents(LOCALE_COMPONENT_OJS_ADMIN, LOCALE_COMPONENT_PKP_ADMIN);

        if (count($this->argv) < 1 || count($this->argv) > 2)  {
            $this->usage();
            exit(1);
        }

        /** @var UsageStatsPlugin $plugin */
        $plugin = PluginRegistry::getPlugin('generic', 'usagestatsplugin');

        $this->_usageStatsDir = $plugin->getFilesPath();
        $this->_tmpDir = $this->_usageStatsDir . DIRECTORY_SEPARATOR . 'tmp';

        // Get a list of files currently inside the usage stats dir.
        $fileLoaderDirs = [
            FILE_LOADER_PATH_STAGING, 
            FILE_LOADER_PATH_PROCESSING,
            FILE_LOADER_PATH_ARCHIVE, 
            FILE_LOADER_PATH_REJECT
        ];

        $usageStatsFiles = [];
        foreach ($fileLoaderDirs as $dir) {
            $dirFiles = glob($this->_usageStatsDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '*');
            if (is_array($dirFiles) && count($dirFiles) > 0) {
                foreach ($dirFiles as $file) {
                    if (!is_file($file)) continue;
                    $fileBasename = pathinfo($file, PATHINFO_BASENAME);

                    if (pathinfo($file, PATHINFO_EXTENSION) == 'gz') {
                        // Always save the filename without compression extension.
                        $fileBasename = substr($fileBasename, 0, -3);
                    }
                    $usageStatsFiles[] = $fileBasename;
                }
            }
        }

        $this->_usageStatsFiles = $usageStatsFiles;

        // Get a list of journal paths.
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalFactory = $journalDao->getJournals();
        $journalPaths = [];
        
        while ($journal = $journalFactory->next()) {
            /* @var Journal $journal */
            $journalPaths[] = escapeshellarg($journal->getPath());
        }
        $this->_journalPaths = implode('/|/', $journalPaths);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CopyAccessLogFileTool($argv = []) {
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
        echo "\n" . __('admin.copyAccessLogFileTool.usage', ['scriptName' => $this->scriptName]) . "\n\n";
    }

    /**
     * Process apache log files, copying and filtering them
     * to the usage stats stage directory. Can work with both
     * a specific file or a directory.
     */
    public function execute(): void {
        $fileMgr = new FileManager();
        // $filesDir unused variable removed
        $filePath = current($this->argv);
        // $usageStatsDir unused variable removed
        $tmpDir = $this->_tmpDir;

        if ($fileMgr->fileExists($tmpDir, 'dir')) {
            $fileMgr->rmtree($tmpDir);
        }

        if (!$fileMgr->mkdir($tmpDir)) {
            printf(__('admin.copyAccessLogFileTool.error.creatingFolder', ['tmpDir' => $tmpDir]) . "\n");
            exit(1);
        }

        if ($fileMgr->fileExists($filePath, 'dir')) {
            // Directory.
            $filesToCopy = glob($filePath . DIRECTORY_SEPARATOR . '*');
            foreach ($filesToCopy as $file) {
                // If a base filename is given as a parameter, check it.
                if (count($this->argv) == 2) {
                    $baseFilename = $this->argv[1];
                    if (strpos(pathinfo($file, PATHINFO_BASENAME), $baseFilename) !== 0) {
                        continue;
                    }
                }

                $this->_copyFile($file);
            }
        } else {
            if ($fileMgr->fileExists($filePath)) {
                // File.
                $this->_copyFile($filePath);
            } else {
                // Can't access.
                printf(__('admin.copyAccessLogFileTool.error.acessingFile', ['filePath' => $filePath]) . "\n");
            }
        }

        $fileMgr->rmtree($tmpDir);
    }


    //
    // Private helper methods.
    //
    /**
     * Copy the passed file, filtering entries
     * related to this installation.
     * @param string $filePath
     */
    protected function _copyFile(string $filePath): void {
        $usageStatsFiles = $this->_usageStatsFiles;
        $usageStatsDir = $this->_usageStatsDir;
        $tmpDir = $this->_tmpDir;
        $fileName = pathinfo($filePath, PATHINFO_BASENAME);
        $fileMgr = new FileManager();

        $isCompressed = false;
        $uncompressedFileName = $fileName;
        if (pathinfo($filePath, PATHINFO_EXTENSION) == 'gz') {
            $isCompressed = true;
            $uncompressedFileName = substr($fileName, 0, -3);
        }

        if (in_array($uncompressedFileName, $usageStatsFiles)) {
            printf(__('admin.copyAccessLogFileTool.warning.fileAlreadyExists', ['filePath' => $filePath]) . "\n");
            return;
        }

        $tmpFilePath = $tmpDir . DIRECTORY_SEPARATOR . $fileName;

        // Copy the file to a temporary directory.
        if (!$fileMgr->copyFile($filePath, $tmpFilePath)) {
            printf(__('admin.copyAccessLogFileTool.error.copyingFile', ['filePath' => $filePath, 'tmpFilePath' => $tmpFilePath]) . "\n");
            exit(1);
        }

        // Uncompress it, if needed.
        if ($isCompressed) {
            $fileMgr = new FileManager();
            $errorMsg = null;
            if (!$fileMgr->decompressFile($filePath, $errorMsg)) {
                printf($errorMsg . "\n");
                exit(1);
            }
            $tmpFilePath = substr($tmpFilePath, 0, -3);
        }

        // Filter only entries that contains journal paths.
        $egrepPath = Config::getVar('cli', 'egrep');
        $destinationPath = $usageStatsDir . DIRECTORY_SEPARATOR .
        FILE_LOADER_PATH_STAGING . DIRECTORY_SEPARATOR .
        pathinfo($tmpFilePath, PATHINFO_BASENAME);
        
        if (!is_executable($egrepPath)) {
            printf(__('admin.error.executingUtil', ['utilPath' => $egrepPath, 'utilVar' => 'egrep']) . "\n");
            exit(1);
        }
        
        $egrepPathEscaped = escapeshellarg($egrepPath);
        $output = [];
        $returnValue = 0;
        
        // Each journal path is already escaped, see the constructor.
        // We use exec to run the grep command.
        exec($egrepPathEscaped . " -i '" . $this->_journalPaths . "' " . escapeshellarg($tmpFilePath) . " > " . escapeshellarg($destinationPath), $output, $returnValue);
        
        if ($returnValue > 1) {
            printf(__('admin.error.executingUtil', ['utilPath' => $egrepPath, 'utilVar' => 'egrep']) . "\n");
            exit(1);
        }

        if (!$fileMgr->deleteFile($tmpFilePath)) {
            printf(__('admin.copyAccessLogFileTool.error.deletingFile', ['tmpFilePath' => $tmpFilePath]) . "\n");
            exit(1);
        }

        printf(__('admin.copyAccessLogFileTool.success', ['filePath' => $filePath, 'destinationPath' => $destinationPath]) . "\n");
    }
}

$tool = new CopyAccessLogFileTool(isset($argv) ? $argv : []);
$tool->execute();