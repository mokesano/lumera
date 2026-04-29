<?php
declare(strict_types=1);

/**
 * @file classes/cliTool/UpgradeTool.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class upgradeTool
 * @ingroup tools
 *
 * @brief CLI tool for upgrading OJS.
 * [WIZDAM EDITION] Modernized CLI Upgrade Tool.
 */


define('RUNNING_UPGRADE', 1);

import('classes.install.Upgrade');
import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionCheck');

class UpgradeTool extends CommandLineTool {

    /** @var string command to execute (check|upgrade|patch|download) */
    protected string $command = '';

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        $supportedCommands = ['check', 'latest', 'upgrade', 'patch', 'download'];
        
        // Command should be the first element after the script name (which is removed in parent)
        $this->command = $this->argv[0] ?? '';

        if (!in_array($this->command, $supportedCommands)) {
            $this->usage();
            exit(1);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function upgradeTool($argv = []) {
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
        echo "Upgrade tool\n"
            . "Usage: {$this->scriptName} command\n"
            . "Supported commands:\n"
            . "    check               perform version check\n"
            . "    latest              display latest version info\n"
            . "    upgrade             execute upgrade script\n"
            . "    patch               download and apply patch for latest version\n"
            . "    download [package|patch]  download latest version (does not unpack/install)\n";
    }

    /**
     * Parse and execute the specified command.
     * [WIZDAM FIX] Replaced dynamic call ($this->$command()) with explicit switch statement for security.
     */
    public function execute(): void {
        switch ($this->command) {
            case 'check':
                $this->check();
                break;
            case 'latest':
                $this->latest();
                break;
            case 'upgrade':
                $this->upgrade();
                break;
            case 'patch':
                $this->patch();
                break;
            case 'download':
                $this->download();
                break;
            default:
                // Should be unreachable due to constructor validation
                $this->usage();
                exit(1);
        }
    }

    /**
     * Perform version check against latest available version.
     */
    protected function check(): void {
        $this->checkVersion(VersionCheck::getLatestVersion());
    }

    /**
     * Print information about the latest available version.
     */
    protected function latest(): void {
        $this->checkVersion(VersionCheck::getLatestVersion(), true);
    }

    /**
     * Run upgrade script.
     */
    protected function upgrade(): void {
        $installer = new Upgrade([]);
        $installer->setLogger($this);

        if ($installer->execute()) {
            $notes = $installer->getNotes();
            if (count($notes) > 0) {
                printf("\nRelease Notes\n");
                printf("----------------------------------------\n");
                foreach ($notes as $note) {
                    printf("%s\n\n", $note);
                }
            }

            $newVersion = $installer->getNewVersion();
            printf("Successfully upgraded to version %s\n", $newVersion->getVersionString());

        } else {
            printf("ERROR: Upgrade failed: %s\n", $installer->getErrorString());
        }
    }

    /**
     * Apply patch to update code to latest version.
     */
    protected function patch(): void {
        $versionInfo = VersionCheck::getLatestVersion();
        $check = $this->checkVersion($versionInfo);

        if ($check < 0) {
            $patch = VersionCheck::getPatch($versionInfo);
            if (!isset($patch)) {
                printf("No applicable patch available\n");
                return;
            }

            $outFile = $versionInfo['application'] . '-' . $versionInfo['release'] . '.patch';
            printf("Download patch: %s\n", $patch);
            printf("Patch will be saved to: %s\n", $outFile);

            if (!$this->promptContinue()) {
                exit(0);
            }

            // [WIZDAM] Safe File Handling
            $out = fopen($outFile, 'wb');
            if (!$out) {
                printf("Failed to open %s for writing\n", $outFile);
                exit(1);
            }

            $in = @gzopen($patch, 'r');
            if (!$in) {
                printf("Failed to open %s for reading (GZ)\n", $patch);
                fclose($out);
                exit(1);
            }

            printf('Downloading patch...');

            while(($data = gzread($in, 4096)) !== false && $data !== '') {
                printf('.');
                fwrite($out, $data);
            }

            printf("done\n");

            gzclose($in);
            fclose($out);

            $command = 'patch -p1 < ' . escapeshellarg($outFile);
            printf("Apply patch: %s\n", $command);

            if (!$this->promptContinue()) {
                exit(0);
            }

            system($command, $ret);
            if ($ret == 0) {
                printf("Successfully applied patch for version %s\n", $versionInfo['release']);
            } else {
                printf("ERROR: Failed to apply patch\n");
            }
        }
    }

    /**
     * Download latest package/patch.
     */
    protected function download(): void {
        $versionInfo = VersionCheck::getLatestVersion();
        if (!$versionInfo) {
            // [WIZDAM] Modern Singleton Access
            $application = Application::get();
            printf("Failed to load version info from %s\n", $application->getVersionDescriptorUrl());
            exit(1);
        }

        $type = ($this->argv[1] ?? '') === 'patch' ? 'patch' : 'package';
        $download = $type === 'package' ? $versionInfo['package'] : VersionCheck::getPatch($versionInfo);

        if (!isset($download)) {
            printf("No applicable download available\n");
            return;
        }
        $outFile = basename($download);

        printf("Download %s: %s\n", $type, $download);
        printf("File will be saved to: %s\n", $outFile);

        if (!$this->promptContinue()) {
            exit(0);
        }

        // [WIZDAM] Safe File Handling
        $out = fopen($outFile, 'wb');
        if (!$out) {
            printf("Failed to open %s for writing\n", $outFile);
            exit(1);
        }

        $in = @fopen($download, 'rb');
        if (!$in) {
            printf("Failed to open %s for reading\n", $download);
            fclose($out);
            exit(1);
        }

        printf('Downloading file...');

        while(($data = fread($in, 4096)) !== false && $data !== '') {
            printf('.');
            fwrite($out, $data);
        }

        printf("done\n");

        fclose($in);
        fclose($out);
    }

    /**
     * Perform version check.
     * @param array|false $versionInfo latest version info
     * @param bool $displayInfo just display info, don't perform check
     * @return int Comparison result (-1: current < latest, 0: current == latest, 1: current > latest)
     */
    protected function checkVersion($versionInfo, bool $displayInfo = false): int {
        if (!$versionInfo) {
            // [WIZDAM] Modern Singleton Access
            $application = Application::get();
            printf("Failed to load version info from %s\n", $application->getVersionDescriptorUrl());
            exit(1);
        }

        $dbVersion = VersionCheck::getCurrentDBVersion();
        $codeVersion = VersionCheck::getCurrentCodeVersion();
        $latestVersion = $versionInfo['version'];

        printf("Code version:      %s\n", $codeVersion->getVersionString());
        printf("Database version:  %s\n", $dbVersion->getVersionString());
        printf("Latest version:    %s\n", $latestVersion->getVersionString());

        $compare1 = $codeVersion->compare($latestVersion); // Code vs Latest
        $compare2 = $dbVersion->compare($codeVersion);    // DB vs Code

        if (!$displayInfo) {
            if ($compare2 < 0) {
                printf("Database version is older than code version\n");
                printf("Run \"{$this->scriptName} upgrade\" to update\n");
                exit(0);

            } else if($compare2 > 0) {
                printf("Database version is newer than code version! (Potentially dangerous)\n");
                exit(1);

            } else if ($compare1 == 0) {
                printf("Your system is up-to-date\n");

            } else if($compare1 < 0) {
                printf("A newer version is available:\n");
                $displayInfo = true; // Fall through to display info

            } else {
                printf("Current version is newer than latest! (Possible pre-release/custom build)\n");
                exit(1);
            }
        }

        if ($displayInfo) {
            $patch = VersionCheck::getPatch($versionInfo, $codeVersion);
            printf("          tag:     %s\n", $versionInfo['tag']);
            printf("          date:    %s\n", $versionInfo['date']);
            printf("          info:    %s\n", $versionInfo['info']);
            printf("          package: %s\n", $versionInfo['package']);
            printf("          patch:   %s\n", $patch ?? 'N/A');
        }

        return $compare1;
    }

    /**
     * Prompt user for yes/no input (default no).
     * @param string $prompt
     * @return bool
     */
    protected function promptContinue(string $prompt = "Continue?"): bool {
        printf("%s [y/N] ", $prompt);
        // Read up to 255 bytes from STDIN
        $continue = fread(STDIN, 255); 
        
        if ($continue === false) {
            return false;
        }

        return (strtolower(substr(trim($continue), 0, 1)) == 'y');
    }

    /**
     * Log install message to stdout.
     * @param mixed $message
     */
    public function log($message): void {
        printf("[%s]\n", (string)$message);
    }
}