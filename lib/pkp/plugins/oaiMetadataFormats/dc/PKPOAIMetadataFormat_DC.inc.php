<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadata/dc/PKPOAIMetadataFormat_DC.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPOAIMetadataFormat_DC
 * @see OAI
 *
 * @brief OAI metadata format class -- Dublin Core.
 * [WIZDAM NATIVE] PHP 7.4+ Strict. Schema-based Metadata Extraction.
 */

class PKPOAIMetadataFormat_DC extends OAIMetadataFormat {
    
    /**
     * @see OAIMetadataFormat#toXML
     * Signature dibuat kompatibel dengan OAIMetadataFormat::toXml($record, $format = NULL)
     * @param DataObject $dataObject
     * @param string|null $format
     * @return string
     */
    public function toXml($dataObject, $format = null): string {
        
        if ($dataObject instanceof OAIRecord) {
            $dataObject = $dataObject->getData('article');
        }

        // Jika setelah diekstrak masih bukan DataObject (misal null), stop.
        if (!$dataObject instanceof DataObject) {
            return '';
        }
        
        import('plugins.metadata.dc11.schema.Dc11Schema');
        
        // [MODERNISASI] Hapus =& (reference assignment) yang usang untuk object
        $dcDescription = $dataObject->extractMetadata(new Dc11Schema());

        $response = "<oai_dc:dc\n" .
            "\txmlns:oai_dc=\"http://www.openarchives.org/OAI/2.0/oai_dc/\"\n" .
            "\txmlns:dc=\"http://purl.org/dc/elements/1.1/\"\n" .
            "\txmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n" .
            "\txsi:schemaLocation=\"http://www.openarchives.org/OAI/2.0/oai_dc/\n" .
            "\thttp://www.openarchives.org/OAI/2.0/oai_dc.xsd\">\n";

        foreach ($dcDescription->getProperties() as $propertyName => $property) { /* @var $property MetadataProperty */
            if ($dcDescription->hasStatement($propertyName)) {
                if ($property->getTranslated()) {
                    $values = $dcDescription->getStatementTranslations($propertyName);
                } else {
                    $values = $dcDescription->getStatement($propertyName);
                }
                // Cast property name to string to ensure strict typing compliance
                $response .= $this->formatElement((string) $propertyName, $values, $property->getTranslated());
            }
        }

        $response .= "</oai_dc:dc>\n";

        return $response;
    }

    /**
     * Format XML for single DC element.
     * @param string $propertyName
     * @param mixed $values (string|array)
     * @param bool $multilingual
     * @return string
     */
    public function formatElement(string $propertyName, $values, bool $multilingual = false): string {
        if (!is_array($values)) {
            $values = [$values];
        }

        // Translate the property name to XML syntax.
        $openingElement = str_replace(['[@', ']'], [' ', ''], $propertyName);
        
        // [WIZDAM] Native regex replace is preferred, but sticking to PKPString if strict UTF8 handling is needed
        $closingElement = PKPString::regexp_replace('/\[@.*/', '', $propertyName);

        // Create the actual XML entry.
        $response = '';
        foreach ($values as $key => $value) {
            if ($multilingual) {
                $key = str_replace('_', '-', (string) $key);
                
                // [STRICT] Ensure value is array for multilingual fields
                if (!is_array($value)) {
                    continue; 
                }
                
                foreach ($value as $subValue) {
                    if ($key === METADATA_DESCRIPTION_UNKNOWN_LOCALE) {
                        $response .= "\t<$openingElement>" . OAIUtils::prepOutput($subValue) . "</$closingElement>\n";
                    } else {
                        $response .= "\t<$openingElement xml:lang=\"$key\">" . OAIUtils::prepOutput($subValue) . "</$closingElement>\n";
                    }
                }
            } else {
                // [STRICT] Ensure scalar value
                if (is_array($value)) {
                     // Fallback mechanism or skip if structure is unexpected
                     continue;
                }
                $response .= "\t<$openingElement>" . OAIUtils::prepOutput($value) . "</$closingElement>\n";
            }
        }
        return $response;
    }
}
?>