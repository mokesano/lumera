<?php
declare(strict_types=1);

/**
 * @file tools/poToCurrencies.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class poToCurrencies
 * @ingroup tools
 *
 * @brief CLI tool to convert a .PO file for ISO4217 into the currencies.xml format
 * supported by the PKP suite.
 * [WIZDAM EDITION] Modernized Currency Localization Tool.
 */

require(__DIR__ . '/bootstrap.inc.php');

// [WIZDAM] Ensure the binary exists, otherwise this tool is unusable.
define('PO_TO_CSV_TOOL', '/usr/bin/po2csv');
if (!is_executable(PO_TO_CSV_TOOL)) {
    fwrite(STDERR, "Error: Required binary " . PO_TO_CSV_TOOL . " not found or not executable.\n");
    exit(1);
}

class poToCurrencies extends CommandLineTool {
    /** @var string The target locale (e.g., 'id_ID') */
    protected string $locale = '';

    /** @var string The path to the source PO file */
    protected string $translationFile = '';

    /**
     * Constructor
     * @param array $argv
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        // $this->argv has been shifted by parent, so [0] is locale, [1] is file
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
    public function poToCurrencies($argv = []) {
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
        echo "Script to convert PO file to Wizdam's ISO4217 XML format\n"
            . "Usage: {$this->scriptName} locale /path/to/translation.po\n";
    }

    /**
     * Execute the conversion and XML generation.
     */
    public function execute(): void {
        // 1. Read the translated file as a map from English => Whatever
        $cmd = PO_TO_CSV_TOOL . ' ' . escapeshellarg($this->translationFile);
        $ih = popen($cmd, 'r');
        
        if ($ih === false) {
            fwrite(STDERR, 'Error: Unable to execute ' . $cmd . "\n");
            exit(1);
        }

        $translationMap = [];
        while (($row = fgetcsv($ih)) !== false) {
            if (count($row) != 3) continue;
            // $row[1] is English name, $row[2] is Translation
            $translationMap[$row[1]] = $row[2];
        }
        pclose($ih);

        // 2. Get the English map from the DAO
        /** @var CurrencyDAO $currencyDao */
        $currencyDao = DAORegistry::getDAO('CurrencyDAO');
        $currencies = $currencyDao->getCurrencies(); // Returns factory or array of Currency objects

        // 3. Generate a translated map of Currency objects
        $outputMap = [];
        foreach ($currencies as $currency) {
            /** @var Currency $currency */
            $english = $currency->getName();

            if (!isset($translationMap[$english])) {
                echo "WARNING: Unknown currency \"$english\"! Using English as default.\n";
            } else {
                $currency->setName($translationMap[$english]);
            }
            $outputMap[] = $currency;
        }

        // 4. Write the translated currency list to XML
        $ofn = 'locale/' . $this->locale . '/currencies.xml';
        
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

<!DOCTYPE currencies [
    <!ELEMENT currencies (currency+)>
        <!ATTLIST currencies
            locale CDATA #REQUIRED>
    <!ELEMENT currency EMPTY>
        <!ATTLIST currency
            code_alpha CDATA #REQUIRED
            code_numeric CDATA #REQUIRED
            name CDATA #REQUIRED>
]>

<currencies locale="{$this->locale}">

XML;
        fwrite($oh, $xmlHeader);

        foreach ($outputMap as $currency) {
            /** @var Currency $currency */
            // [WIZDAM] Ensure name is safe for XML attribute
            $safeName = htmlspecialchars($currency->getName(), ENT_XML1, 'UTF-8');
            
            fwrite($oh, "    <currency name=\"$safeName\" code_alpha=\"{$currency->getCodeAlpha()}\" code_numeric=\"{$currency->getCodeNumeric()}\" />\n");
        }

        fwrite($oh, "</currencies>");
        fclose($oh);
        
        printf("Success: Wrote %s currencies to %s\n", count($outputMap), $ofn);
    }
}

// [WIZDAM] Safe instantiation
$tool = new poToCurrencies($argv ?? []);
$tool->execute();

?>