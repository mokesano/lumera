<?php
declare(strict_types=1);

/**
 * @file classes/oai/OAIStruct.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class OAIConfig
 * @ingroup oai
 * @see OAI
 *
 * @brief Data structures associated with the OAI request handler.
 */

define('OAIRECORD_STATUS_DELETED', 0);
define('OAIRECORD_STATUS_ALIVE', 1);

/**
 * @brief OAI repository configuration.
 */
class OAIConfig {

    /** @var string URL to the OAI front-end */
    public $baseUrl = '';

    /** @var string identifier of the repository */
    public $repositoryId = 'oai';

    /** @var string record datestamp granularity */
    // Must be either 'YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ'
    public $granularity = 'YYYY-MM-DDThh:mm:ssZ';

    /** @var int TTL of resumption tokens */
    public $tokenLifetime = 86400;

    /** @var int maximum identifiers returned per request */
    public $maxIdentifiers = 500;

    /** @var int maximum records returned per request */
    public $maxRecords;

    /** @var int maximum sets returned per request */
    // Must be set to zero if sets not supported by repository
    public $maxSets = 50;

    /**
     * Constructor.
     * @param string $baseUrl
     * @param string $repositoryId
     */
    public function __construct($baseUrl, $repositoryId) {
        $this->baseUrl = $baseUrl;
        $this->repositoryId = $repositoryId;

        $this->maxRecords = Config::getVar('oai', 'oai_max_records');
        if (!$this->maxRecords) $this->maxRecords = 100;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $baseUrl
     * @param string $repositoryId
     */
    public function OAIConfig($baseUrl, $repositoryId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}

/**
 * @brief OAI repository information.
 */
class OAIRepository {

    /** @var string name of the repository */
    public $repositoryName;

    /** @var string administrative contact email */
    public $adminEmail;

    /** @var int earliest *nix timestamp in the repository */
    public $earliestDatestamp;

    /** @var string delimiter in identifier */
    public $delimiter = ':';

    /** @var string example identifier */
    public $sampleIdentifier;

    /** @var string toolkit/software title (e.g. Open Journal Systems) */
    public $toolkitTitle;

    /** @var string toolkit/software version */
    public $toolkitVersion;

    /** @var string toolkit/software URL */
    public $toolkitURL;
}


/**
 * @brief OAI resumption token.
 * Used to resume a record retrieval at the last-retrieved offset.
 */
class OAIResumptionToken {

    /** @var string unique token ID */
    public $id;

    /** @var int record offset */
    public $offset;

    /** @var array request parameters */
    public $params;

    /** @var int expiration timestamp */
    public $expire;

    /**
     * Constructor.
     * @param string $id
     * @param int $offset
     * @param mixed $params
     * @param int $expire
     */
    public function __construct($id, $offset, $params, $expire) {
        $this->id = $id;
        $this->offset = $offset;
        $this->params = $params;
        $this->expire = $expire;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $id
     * @param int $offset
     * @param mixed $params
     * @param int $expire
     */
    public function OAIResumptionToken($id, $offset, $params, $expire) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}


/**
 * @brief OAI metadata format.
 * Used to generated metadata XML according to a specified schema.
 */
class OAIMetadataFormat {

    /** @var string metadata prefix */
    public $prefix;

    /** @var string XML schema */
    public $schema;

    /** @var string XML namespace */
    public $namespace;

    /**
     * Constructor.
     * @param string $prefix
     * @param string $schema
     * @param string $namespace
     */
    public function __construct($prefix, $schema, $namespace) {
        $this->prefix = $prefix;
        $this->schema = $schema;
        $this->namespace = $namespace;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $prefix
     * @param string $schema
     * @param string $namespace
     */
    public function OAIMetadataFormat($prefix, $schema, $namespace) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get localized data
     * @param mixed $data
     * @param mixed $locale
     */
    public function getLocalizedData($data, $locale) {
        foreach ($data as $element) {
            if (isset($data[$locale])) return $data[$locale];
        }
        return '';
    }

    /**
     * Retrieve XML-formatted metadata for the specified record.
     * @param $record OAIRecord
     * @param $format string OAI metadata prefix
     * @return string
     */
    public function toXml($record, $format = null) {
        return '';
    }

    /**
     * Recursively strip HTML from a (multidimensional) array.
     * @param mixed $values array
     * @return array the cleansed array
     */
    public function stripAssocArray($values) {
        // Asumsi: Fungsi global stripAssocArray tersedia di PKP library
        return stripAssocArray($values);
    }
}


/**
 * @brief OAI set.
 * Identifies a set of related records.
 */
class OAISet {

    /** @var string unique set specifier */
    public $spec;

    /** @var string set name */
    public $name;

    /** @var string set description */
    public $description;

    /**
     * Constructor.
     * @param string $spec
     * @param string $name
     * @param string $description
     */
    public function __construct($spec, $name, $description) {
        $this->spec = $spec;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $spec
     * @param string $name
     * @param string $description
     */
    public function OAISet($spec, $name, $description) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}


/**
 * @brief OAI identifier.
 */
class OAIIdentifier {
    /** @var string unique OAI record identifier */
    public $identifier;

    /** @var int last-modified *nix timestamp */
    public $datestamp;

    /** @var array sets this record belongs to */
    public $sets;

    /** @var string if this record is deleted */
    public $status;

    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIIdentifier() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}


/**
 * @brief OAI record.
 * Describes metadata for a single record in the repository.
 */
class OAIRecord extends OAIIdentifier {

    /** @var array $data */
    public $data;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->data = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIRecord() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Set data
     * @param string $name
     * @param mixed $value
     */
    public function setData($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * Get data
     * @param string $name
     */
    public function getData($name) {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

}
?>