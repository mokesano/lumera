<?php
declare(strict_types=1);

/**
 * @file tools/poToCountries.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class poToCountries
 * @ingroup tools
 *
 * @brief CLI tool to convert a .PO file for ISO3166 into the countries.xml format
 * supported by the PKP suite.
 * [WIZDAM EDITION] Modernized Localization Tool.
 */

require(__DIR__ . '/bootstrap.inc.php');

// [WIZDAM] Ensure the binary exists, otherwise this tool is unusable.
define('PO_TO_CSV_TOOL', '/usr/bin/po2csv');
if (!is_executable(PO_TO_CSV_TOOL)) {
    fwrite(STDERR, "Error: Required binary " . PO_TO_CSV_TOOL . " not found or not executable.\n");
    exit(1);
}

class poToCountries extends CommandLineTool {
    /** @var string */
    protected string $locale = '';

    /** @var string */
    protected string $translationFile = '';

    /**
     * Constructor
     * @param array $argv
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        // Parent constructor removed $this->argv[0] (script name)
        $this->locale = $this->argv[0] ?? '';
        $this->translationFile = $this->argv[1] ?? '';

        if (
            !preg_match('/^[a-z]{2}_[A-Z]{2}$/', $this->locale) ||
            empty($this->translationFile) ||
            !file_exists($this->translationFile)
        ) {
            $this->usage();
            exit(1);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function poToCountries($argv = []) {
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
        echo "Script to convert PO file to Wizdam's ISO3166 XML format\n"
            . "Usage: {$this->scriptName} locale /path/to/translation.po\n";
    }

    /**
     * Execute the conversion and XML generation.
     */
    public function execute(): void {
        // Read the translated file as a map from English => Whatever
        $cmd = PO_TO_CSV_TOOL . ' ' . escapeshellarg($this->translationFile);
        $ih = popen($cmd, 'r');
        
        if ($ih === false) {
            fwrite(STDERR, 'Error: Unable to read ' . $this->translationFile . ' using ' . PO_TO_CSV_TOOL . "\n");
            exit(1);
        }

        $translationMap = [];
        while (($row = fgetcsv($ih)) !== false) {
            if (count($row) != 3) continue;
            // list($comment, $english, $translation) = $row; // PHP 7.1+ array destructuring
            $english = $row[1];
            $translation = $row[2];
            $translationMap[$english] = $translation;
        }
        pclose($ih);

        // Get the English map from the DAO
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries(); // Returns array code => English name

        // Generate a map of code => translation
        $outputMap = [];
        foreach ((array) $countries as $code => $english) {
            if (!isset($translationMap[$english])) {
                echo "WARNING: Unknown country \"$english\"! Using English as default.\n";
                $outputMap[$code] = $english;
            } else {
                $outputMap[$code] = $translationMap[$english];
                // Unset to find unused translations, though not strictly necessary for core logic
                // unset($translationMap[$english]);
            }
        }

        // Use the map to convert the country list to the new locale
        $ofn = 'registry/locale/' . $this->locale . '/countries.xml';
        
        // [WIZDAM SAFETY] Ensure directory exists and open file
        $dir = dirname($ofn);
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            fwrite(STDERR, "Error: Unable to create directory for $ofn.\n");
            exit(1);
        }

        $oh = fopen($ofn, 'w');
        if ($oh === false) {
            fwrite(STDERR, "Error: Unable to open $ofn for writing.\n");
            exit(1);
        }
        
        // [WIZDAM] Using HEREDOC for clean XML output
        $xmlHeader = <<<XML
<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE countries [
    <!ELEMENT countries (country+)>
    <!ELEMENT country EMPTY>
        <!ATTLIST country
            code CDATA #REQUIRED
            name CDATA #REQUIRED>
]>

<countries>

XML;
        fwrite($oh, $xmlHeader);

        foreach ($outputMap as $code => $translation) {
            // [WIZDAM] Ensure translation is safe for XML attribute
            $safeTranslation = htmlspecialchars($translation, ENT_XML1, 'UTF-8');
            fwrite($oh, "    <country name=\"$safeTranslation\" code=\"$code\"/>\n");
        }

        fwrite($oh, "</countries>");
        fclose($oh);
        
        printf("Success: Wrote %s entries to %s\n", count($outputMap), $ofn);
    }
}

// [WIZDAM] Safe instantiation
$tool = new poToCountries($argv ?? []);
$tool->execute();

?>