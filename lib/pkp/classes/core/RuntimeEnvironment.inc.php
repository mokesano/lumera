<?php
declare(strict_types=1);

/**
 * @file classes/core/RuntimeEnvironment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RuntimeEnvironment
 * @ingroup core
 *
 * @brief Class that describes a runtime environment.
 * [WIZDAM EDITION] Refactored for PHP 7.4+/8.x Strict Standards & Logic Fixes.
 */

class RuntimeEnvironment {
    /** * @var string 
     * [WIZDAM] Renamed from _phpVersionMin. Public for legacy compat.
     */
    public string $phpVersionMin;

    /** * @var string|null 
     * [WIZDAM] Renamed from _phpVersionMax.
     */
    public ?string $phpVersionMax;

    /** * @var array 
     * [WIZDAM] Renamed from _phpExtensions.
     */
    public array $phpExtensions;

    /** * @var array 
     * [WIZDAM] Renamed from _externalPrograms.
     */
    public array $externalPrograms;

    /**
     * Constructor
     * @param string $phpVersionMin
     * @param string|null $phpVersionMax
     * @param array $phpExtensions
     * @param array $externalPrograms
     */
    public function __construct(string $phpVersionMin = PHP_REQUIRED_VERSION, ?string $phpVersionMax = null, array $phpExtensions = [], array $externalPrograms = []) {
        $this->phpVersionMin = $phpVersionMin;
        $this->phpVersionMax = $phpVersionMax;
        $this->phpExtensions = $phpExtensions;
        $this->externalPrograms = $externalPrograms;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RuntimeEnvironment(
        $phpVersionMin = PHP_REQUIRED_VERSION, 
        $phpVersionMax = null, 
        $phpExtensions = [], 
        $externalPrograms = []
    ) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct((string)$phpVersionMin, $phpVersionMax, (array)$phpExtensions, (array)$externalPrograms);
    }

    //
    // Setters and Getters
    //

    /**
     * Get the min required PHP version
     * @return string
     */
    public function getPhpVersionMin(): string {
        return $this->phpVersionMin;
    }

    /**
     * Get the max required PHP version
     * @return string|null
     */
    public function getPhpVersionMax(): ?string {
        return $this->phpVersionMax;
    }

    /**
     * Get the required PHP extensions
     * @return array
     */
    public function getPhpExtensions(): array {
        return $this->phpExtensions;
    }

    /**
     * Get the required external programs
     * @return array
     */
    public function getExternalPrograms(): array {
        return $this->externalPrograms;
    }

    //
    // Public methods
    //

    /**
     * Checks whether the current runtime environment is
     * compatible with the specified parameters.
     * @return bool
     */
    public function isCompatible(): bool {
        // 1. Check Minimum PHP version
        // [WIZDAM] Replaced legacy checkPhpVersion with native version_compare
        if (version_compare(PHP_VERSION, $this->phpVersionMin, '<')) {
            error_log('Wizdam Environment: PHP version ' . PHP_VERSION . ' is less than required ' . $this->phpVersionMin);
            return false;
        }

        // 2. Check Maximum PHP version (if set)
        if ($this->phpVersionMax !== null && version_compare(PHP_VERSION, $this->phpVersionMax, '>')) {
            error_log('Wizdam Environment: PHP version ' . PHP_VERSION . ' is greater than allowed ' . $this->phpVersionMax);
            return false;
        }

        // 3. Check PHP extensions
        foreach ($this->phpExtensions as $requiredExtension) {
            if (!extension_loaded($requiredExtension)) {
                error_log('Wizdam Environment: Missing required PHP extension: ' . $requiredExtension);
                return false;
            }
        }

        // 4. Check external programs
        foreach ($this->externalPrograms as $requiredProgram) {
            $externalProgramPath = Config::getVar('cli', $requiredProgram);
            
            // Check if configured path exists
            if (empty($externalProgramPath) || !file_exists($externalProgramPath)) {
                error_log("Wizdam Environment: Configured path for '$requiredProgram' not found: " . (string)$externalProgramPath);
                return false;
            }

            // [WIZDAM BUG FIX] 
            // Original code checked '!is_executable($filename)' but $filename was undefined.
            // Corrected to check $externalProgramPath.
            if (function_exists('is_executable')) {
                if (!is_executable($externalProgramPath)) {
                    error_log("Wizdam Environment: File at '$externalProgramPath' is not executable. Check permissions.");
                    return false;
                }
            }
        }

        // Compatibility check was successful
        return true;
    }
}
?>