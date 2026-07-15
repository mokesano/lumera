<?php
declare(strict_types=1);

/**
 * @file classes/core/DataObject.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObject
 * @ingroup core
 * @see Core
 *
 * @brief Any class with an associated DAO should extend this class.
 * 
 */

class DataObject {

    /** @var array Array of object data */
    protected $_data = array();

    /** @var bool whether this objects loads meta-data adapters from the database */
    protected $_hasLoadableAdapters = false;

    /** @var array an array of meta-data extraction adapters (one per supported schema) */
    protected $_metadataExtractionAdapters = array();

    /** @var bool whether extraction adapters have already been loaded from the database */
    protected $_extractionAdaptersLoaded = false;

    /** @var array an array of meta-data injection adapters (one per supported schema) */
    protected $_metadataInjectionAdapters = array();

    /** @var bool whether injection adapters have already been loaded from the database */
    protected $_injectionAdaptersLoaded = false;

    /**
     * Constructor
     */
    public function __construct($callHooks = true) {
        // Hooks logic can be added here if needed in the future
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataObject($callHooks = true) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'(). Please refactor to use parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct($callHooks);
    }

    //
    // Getters and Setters
    //

    /**
     * Get a piece of data for this object, localized to the current
     * locale if possible.
     * 
     * @param string $key
     * @return mixed
     */
    public function getLocalizedData($key) {
        $localePrecedence = AppLocale::getLocalePrecedence();
        
        // Cari locale yang cocok dari precedence
        foreach ($localePrecedence as $locale) {
            $value = $this->getData($key, $locale);
            if (!empty($value)) return $value;
        }

        // Fallback: Ambil data pertama yang tersedia.
        $data = $this->getData($key, null);
        
        if (is_string($data)) {
            return $data;
        } 
        
        if (is_array($data) && !empty($data)) {
            // Mengambil elemen pertama dari array PHP 8.x (tanpa merusak pointer aslinya)
            return reset($data) ?: null; 
        }

        return null;
    }

    /**
     * Get the value of a data variable.
     * 
     * @param string $key
     * @param string|null $locale (optional)
     * @return mixed
     */
    public function getData($key, $locale = null) {
        // 1. Jika locale spesifik diminta (misal: 'id_ID')
        if ($locale !== null) {
            return $this->_data[$key][$locale] ?? null;
        }

        // 2. Locale saat ini adalah NULL.
        // Apakah argumen kedua (null) dikirim secara eksplisit?
        // - TRUE: $this->getData('abstract', null) -> Meminta array multibahasa
        // - FALSE: $this->getData('status') -> Meminta scalar value biasa
        $isExplicitRequestForLocalizedArray = func_num_args() === 2;

        // 3. Jika data sudah ada di memori, kembalikan apa adanya
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }

        // 4. [THE ROOT FIX]
        // Jika data tidak ada di database, TETAPI caller secara eksplisit 
        // meminta format multibahasa, kita paksa kembalikan empty array [].
        // Ini mencegah crash "Trying to access array offset on value of type null" di Smarty.
        if ($isExplicitRequestForLocalizedArray) {
            return [];
        }

        // 5. Fallback untuk field biasa (non-localized) yang belum diset
        return null;
    }

    /**
     * Set the value of a new or existing data variable.
     * 
     * NB: Passing in null as a value will unset the
     * data variable if it already existed.
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $locale (optional)
     */
    public function setData($key, $value, $locale = null) {
        if ($locale === null) {
            // Non-localized value or setting all locales at once.
            if ($value === null) {
                if (isset($this->_data[$key])) unset($this->_data[$key]);
            } else {
                $this->_data[$key] = $value;
            }
        } else {
            // (Un-)set a single localized value.
            if ($value === null) {
                if (isset($this->_data[$key])) {
                    if (is_array($this->_data[$key]) && isset($this->_data[$key][$locale])) {
                        unset($this->_data[$key][$locale]);
                    }
                    // Was this the last entry?
                    if (empty($this->_data[$key])) {
                        unset($this->_data[$key]);
                    }
                }
            } else {
                $this->_data[$key][$locale] = $value;
            }
        }
    }

    /**
     * Check whether a value exists for a given data variable.
     * 
     * @param string $key
     * @param string|null $locale (optional)
     * @return bool
     */
    public function hasData($key, $locale = null) {
        if ($locale === null) {
            return isset($this->_data[$key]);
        } else {
            return isset($this->_data[$key]) && is_array($this->_data[$key]) && isset($this->_data[$key][$locale]);
        }
    }

    /**
     * Return an array with all data variables.
     * 
     * @return array
     */
    public function getAllData() {
        return $this->_data;
    }

    /**
     * Set all data variables at once.
     * 
     * @param array $data
     */
    public function setAllData($data) {
        $this->_data = $data;
    }

    /**
     * Get ID of object.
     * 
     * @return int|null
     */
    public function getId() {
        return $this->getData('id');
    }

    /**
     * Set ID of object.
     * 
     * @param int $id
     */
    public function setId($id) {
        $this->setData('id', $id);
    }

    //
    // Public helper methods
    //

    /**
     * Upcast this data object to the target object.
     * 
     * @param DataObject $targetObject
     * @return DataObject
     */
    public function upcastTo($targetObject) {
        assert($targetObject instanceof $this);

        // Copy data from the source to the target.
        $targetObject->setAllData($this->getAllData());

        return $targetObject;
    }

    //
    // MetadataProvider interface implementation
    //

    /**
     * Set whether the object has loadable meta-data adapters.
     * 
     * @param bool $hasLoadableAdapters
     */
    public function setHasLoadableAdapters($hasLoadableAdapters) {
        $this->_hasLoadableAdapters = $hasLoadableAdapters;
    }

    /**
     * Get whether the object has loadable meta-data adapters.
     * 
     * @return bool
     */
    public function getHasLoadableAdapters() {
        return $this->_hasLoadableAdapters;
    }

    /**
     * Add a meta-data adapter.
     * 
     * @param MetadataDataObjectAdapter $metadataAdapter
     */
    public function addSupportedMetadataAdapter($metadataAdapter) {
        $metadataSchemaName = $metadataAdapter->getMetadataSchemaName();
        assert(!empty($metadataSchemaName));

        // Is this a meta-data extractor?
        $inputType = $metadataAdapter->getInputType();
        if ($inputType->checkType($this)) {
            if (!isset($this->_metadataExtractionAdapters[$metadataSchemaName])) {
                $this->_metadataExtractionAdapters[$metadataSchemaName] = $metadataAdapter;
            }
        }

        // Is this a meta-data injector?
        $outputType = $metadataAdapter->getOutputType();
        if ($outputType->checkType($this)) {
            if (!isset($this->_metadataInjectionAdapters[$metadataSchemaName])) {
                $this->_metadataInjectionAdapters[$metadataSchemaName] = $metadataAdapter;
            }
        }
    }

    /**
     * Remove all adapters for the given meta-data schema.
     * 
     * @param string $metadataSchemaName
     * @return bool
     */
    public function removeSupportedMetadataAdapter($metadataSchemaName) {
        $result = false;
        if (isset($this->_metadataExtractionAdapters[$metadataSchemaName])) {
            unset($this->_metadataExtractionAdapters[$metadataSchemaName]);
            $result = true;
        }
        if (isset($this->_metadataInjectionAdapters[$metadataSchemaName])) {
            unset($this->_metadataInjectionAdapters[$metadataSchemaName]);
            $result = true;
        }
        return $result;
    }

    /**
     * Get all meta-data extraction adapters.
     * 
     * @return array
     */
    public function getSupportedExtractionAdapters() {
        if ($this->getHasLoadableAdapters() && !$this->_extractionAdaptersLoaded) {
            /** @var FilterDAO $filterDao */
            $filterDao = DAORegistry::getDAO('FilterDAO');
            $loadedAdapters = $filterDao->getObjectsByTypeDescription('class::%', 'metadata::%', $this);
            foreach ($loadedAdapters as $loadedAdapter) {
                $this->addSupportedMetadataAdapter($loadedAdapter);
            }
            $this->_extractionAdaptersLoaded = true;
        }

        return $this->_metadataExtractionAdapters;
    }

    /**
     * Get all meta-data injection adapters.
     * 
     * @return array
     */
    public function getSupportedInjectionAdapters() {
        if ($this->getHasLoadableAdapters() && !$this->_injectionAdaptersLoaded) {
            /** @var FilterDAO $filterDao */
            $filterDao = DAORegistry::getDAO('FilterDAO');
            $loadedAdapters = $filterDao->getObjectsByTypeDescription('metadata::%', 'class::%', $this, false);
            foreach ($loadedAdapters as $loadedAdapter) {
                $this->addSupportedMetadataAdapter($loadedAdapter);
            }
            $this->_injectionAdaptersLoaded = true;
        }

        return $this->_metadataInjectionAdapters;
    }

    /**
     * Returns all supported meta-data schemas.
     * 
     * @return array
     */
    public function getSupportedMetadataSchemas() {
        $supportedMetadataSchemas = array();
        $extractionAdapters = $this->getSupportedExtractionAdapters();
        foreach ($extractionAdapters as $metadataAdapter) {
            $supportedMetadataSchemas[] = $metadataAdapter->getMetadataSchema();
        }
        return $supportedMetadataSchemas;
    }

    /**
     * Retrieve the names of meta-data properties.
     * 
     * @param bool $translated
     * @return array
     */
    public function getMetadataFieldNames($translated = true) {
        $metadataFieldNames = array();
        $extractionAdapters = $this->getSupportedExtractionAdapters();
        foreach ($extractionAdapters as $metadataSchemaName => $metadataAdapter) {
            $metadataFieldNames = array_merge($metadataFieldNames,
                    $metadataAdapter->getDataObjectMetadataFieldNames($translated));
        }
        return array_unique($metadataFieldNames);
    }

    /**
     * Retrieve the names of meta-data properties that need to be persisted.
     * 
     * @param bool $translated
     * @return array
     */
    public function getSetMetadataFieldNames($translated = true) {
        $metadataFieldNameCandidates = $this->getMetadataFieldNames($translated);
        $metadataFieldNames = array();
        foreach ($metadataFieldNameCandidates as $metadataFieldNameCandidate) {
            if ($this->hasData($metadataFieldNameCandidate)) {
                $metadataFieldNames[] = $metadataFieldNameCandidate;
            }
        }
        return $metadataFieldNames;
    }

    /**
     * Retrieve the names of translated meta-data properties.
     * 
     * @return array
     */
    public function getLocaleMetadataFieldNames() {
        return $this->getMetadataFieldNames(true);
    }

    /**
     * Retrieve the names of additional meta-data properties.
     * 
     * @return array
     */
    public function getAdditionalMetadataFieldNames() {
        return $this->getMetadataFieldNames(false);
    }

    /**
     * Inject a meta-data description into this data object.
     * 
     * @param MetadataDescription $metadataDescription
     * @return mixed DataObject|null
     */
    public function injectMetadata($metadataDescription) {
        $dataObject = null;
        $metadataSchemaName = $metadataDescription->getMetadataSchemaName();
        $injectionAdapters = $this->getSupportedInjectionAdapters();
        if (isset($injectionAdapters[$metadataSchemaName])) {
            $metadataAdapter = $injectionAdapters[$metadataSchemaName];
            $metadataAdapter->setTargetDataObject($this);
            $dataObject = $metadataAdapter->execute($metadataDescription);
        }
        return $dataObject;
    }

    /**
     * Extract a meta-data description from this data object.
     * 
     * @param MetadataSchema $metadataSchema
     * @return MetadataDescription|null
     */
    public function extractMetadata($metadataSchema) {
        $metadataDescription = null;
        $metadataSchemaName = $metadataSchema->getClassName();
        $extractionAdapters = $this->getSupportedExtractionAdapters();
        if (isset($extractionAdapters[$metadataSchemaName])) {
            $metadataAdapter = $extractionAdapters[$metadataSchemaName];
            $metadataDescription = $metadataAdapter->execute($this);
        }
        return $metadataDescription;
    }

}
?>