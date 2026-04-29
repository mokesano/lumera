<?php
declare(strict_types=1);

/**
 * @file classes/cliTool/InstallTool.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class installTool
 * @ingroup tools
 *
 * @brief CLI tool for installing a PKP app.
 * [WIZDAM EDITION] Modernized CLI Installer.
 */

import('classes.install.Install');
import('classes.install.form.InstallForm');
import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionCheck');

class InstallTool extends CommandLineTool {

    /** @var array installation parameters */
    protected array $params = [];

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InstallTool() {
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
        echo "Install tool\n"
            . "Usage: {$this->scriptName}\n";
    }

    /**
     * Execute the script.
     */
    public function execute(): void {
        if ($this->readParams()) {
            $this->install();
        }
    }

    /**
     * Perform installation.
     */
    public function install(): void {
        $installer = new Install($this->params);
        $installer->setLogger($this);

        if ($installer->execute()) {
            if (count($installer->getNotes()) > 0) {
                printf("\nRelease Notes\n");
                printf("----------------------------------------\n");
                foreach ($installer->getNotes() as $note) {
                    printf("%s\n\n", $note);
                }
            }

            if (!$installer->wroteConfig()) {
                    printf("\nNew config.inc.php:\n");
                    printf("----------------------------------------\n");
                    echo $installer->getConfigContents();
                    printf("----------------------------------------\n");
            }

            $newVersion = $installer->getNewVersion();
            printf("Successfully installed version %s\n", $newVersion->getVersionString());

        } else {
            printf("ERROR: Installation failed: %s\n", $installer->getErrorString());
        }
    }

    /**
     * Read installation parameters from stdin.
     * @return bool
     */
    protected function readParams(): bool {
        $installForm = new InstallForm();

        // Locale Settings
        $this->printTitle('installer.localeSettings');
        $this->readParamOptions('locale', 'locale.primary', $installForm->supportedLocales, 'en_US');
        $this->readParamOptions('additionalLocales', 'installer.additionalLocales', $installForm->supportedLocales, '', true);
        $this->readParamOptions('clientCharset', 'installer.clientCharset', $installForm->supportedClientCharsets, 'utf-8');
        $this->readParamOptions('connectionCharset', 'installer.connectionCharset', $installForm->supportedConnectionCharsets, '');
        $this->readParamOptions('databaseCharset', 'installer.databaseCharset', $installForm->supportedDatabaseCharsets, '');

        // File Settings
        $this->printTitle('installer.fileSettings');
        $this->readParam('filesDir', 'installer.filesDir');

        // Security Settings
        $this->printTitle('installer.securitySettings');
        $this->readParamOptions('encryption', 'installer.encryption', $installForm->supportedEncryptionAlgorithms, 'md5');

        // Administrator Account
        $this->printTitle('installer.administratorAccount');
        $this->readParam('adminUsername', 'user.username');
        
        // [WIZDAM] Password masking (Best Effort)
        @`/bin/stty -echo`;
        do {
            $this->readParam('adminPassword', 'user.password');
            printf("\n");
            $this->readParam('adminPassword2', 'user.repeatPassword');
            printf("\n");
        } while ($this->params['adminPassword'] != $this->params['adminPassword2']);
        @`/bin/stty echo`;
        
        $this->readParam('adminEmail', 'user.email');

        // Database Settings
        $this->printTitle('installer.databaseSettings');
        $this->readParamOptions('databaseDriver', 'installer.databaseDriver', $installForm->checkDBDrivers());
        $this->readParam('databaseHost', 'installer.databaseHost', '');
        $this->readParam('databaseUsername', 'installer.databaseUsername', '');
        $this->readParam('databasePassword', 'installer.databasePassword', '');
        $this->readParam('databaseName', 'installer.databaseName');
        $this->readParamBoolean('createDatabase', 'installer.createDatabase', 'Y');

        // Miscellaneous Settings
        $this->printTitle('installer.miscSettings');
        $this->readParam('oaiRepositoryId', 'installer.oaiRepositoryId');

        $this->readParamBoolean('enableBeacon', 'installer.beacon.enable', 'Y');

        printf("\n*** ");
        
        // [WIZDAM FIX] Explicitly return true so execute() proceeds
        return true;
    }

    /**
     * Print input section title.
     * @param string $title
     */
    protected function printTitle(string $title): void {
        printf("\n%s\n%s\n%s\n", str_repeat('-', 80), __($title), str_repeat('-', 80));
    }

    /**
     * Read a line of user input.
     * [WIZDAM] PHP 8 Safe implementation handling false return from fgets
     * @return string
     */
    protected function readInput(): string {
        $input = fgets(STDIN);
        
        if ($input === false) {
             // Handle EOF or Error
             printf("\n");
             exit(0);
        }
        
        return trim($input);
    }

    /**
     * Read a string parameter.
     * @param string $name
     * @param string $prompt
     * @param string|null $defaultValue
     */
    protected function readParam(string $name, string $prompt, ?string $defaultValue = null): void {
        do {
            if (isset($defaultValue)) {
                printf("%s (%s): ", __($prompt), $defaultValue !== '' ? $defaultValue : __('common.none'));
            } else {
                printf("%s: ", __($prompt));
            }

            $value = $this->readInput();

            if ($value === '' && isset($defaultValue)) {
                $value = $defaultValue;
            }
        } while ($value === '' && $defaultValue !== '');
        
        $this->params[$name] = $value;
    }

    /**
     * Prompt user for yes/no input.
     * @param string $name
     * @param string $prompt
     * @param string $default default value, 'Y' or 'N'
     */
    protected function readParamBoolean(string $name, string $prompt, string $default = 'N'): void {
        if ($default == 'N') {
            printf("%s [y/N] ", __($prompt));
            $value = $this->readInput();
            $this->params[$name] = (int)(strtolower(substr(trim($value), 0, 1)) == 'y');
        } else {
            printf("%s [Y/n] ", __($prompt));
            $value = $this->readInput();
            $this->params[$name] = (int)(strtolower(substr(trim($value), 0, 1)) != 'n');
        }
    }

    /**
     * Read a parameter from a set of options.
     * @param string $name
     * @param string $prompt
     * @param array $options
     * @param string|null $defaultValue
     * @param bool $allowMultiple
     */
    protected function readParamOptions(string $name, string $prompt, array $options, ?string $defaultValue = null, bool $allowMultiple = false): void {
        do {
            printf("%s\n", __($prompt));
            foreach ($options as $k => $v) {
                printf("  %-10s %s\n", '[' . $k . ']', $v);
            }
            if ($allowMultiple) {
                printf("  (%s)\n", __('installer.form.separateMultiple'));
            }
            if (isset($defaultValue)) {
                printf("%s (%s): ", __('common.select'), $defaultValue !== '' ? $defaultValue : __('common.none'));
            } else {
                printf("%s: ", __('common.select'));
            }

            $value = $this->readInput();

            if ($value === '' && isset($defaultValue)) {
                $value = $defaultValue;
            }

            $values = [];
            if ($value !== '') {
                if ($allowMultiple) {
                    $values = ($value === '' ? [] : preg_split('/\s*,\s*/', $value));
                } else {
                    $values = [$value];
                }
                foreach ($values as $k) {
                    if (!isset($options[$k])) {
                        $value = '';
                        break;
                    }
                }
            }
        } while ($value === '' && $defaultValue !== '');

        if ($allowMultiple) {
            $this->params[$name] = $values;
        } else {
            $this->params[$name] = $value;
        }
    }

    /**
     * Log install message to stdout.
     * Used by Installer via setLogger($this)
     * @param mixed $message
     */
    public function log($message): void {
        printf("[%s]\n", (string)$message);
    }

}