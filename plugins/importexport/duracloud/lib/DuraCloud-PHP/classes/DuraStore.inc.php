<?php
declare(strict_types=1);

/**
 * @file classes/DuraStore.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraStore
 * @ingroup duracloud_classes
 *
 * @brief DuraStore client implementation
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, Reference Cleanup)
 */

//
// DuraStore standard metadata element names
//
define('DURACLOUD_SPACE_ACCESS', 'space-access');
define('DURACLOUD_SPACE_ACCESS_OPEN', 'OPEN');
define('DURACLOUD_SPACE_ACCESS_CLOSED', 'CLOSED');
define('DURACLOUD_SPACE_COUNT', 'space-count');
define('DURACLOUD_SPACE_CREATED', 'space-created');

// Default store
define('DURACLOUD_DEFAULT_STORE', null);

// DuraCloud metadata prefix
define('DURACLOUD_METADATA_PREFIX', 'x-dura-meta-');

class DuraStore extends DuraCloudComponent {
    
    /**
     * Constructor
     * @param DuraCloudConnection $dcc
     */
    public function __construct($dcc) {
        parent::__construct($dcc, 'durastore');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraStore() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Store management
    //

    /**
     * Get a list of stores.
     * @return array|bool List of store IDs or false on failure
     */
    public function getStores() {
        // Get the stores list
        $dcc = $this->getConnection();
        $xml = $dcc->get($this->getPrefix() . 'stores');
        if (!$xml) return false;

        // Parse the result
        $parser = new DuraCloudXMLParser();
        if (!$parser->parse($xml)) return false;

        $returner = [];
        $storageProviderAccounts = $parser->getResults();
        
        // Validation check usually handled by assert, added safety here
        if (!isset($storageProviderAccounts['name']) || $storageProviderAccounts['name'] !== 'storageProviderAccounts') {
             $parser->destroy();
             return false;
        }

        foreach ((array) ($storageProviderAccounts['children'] ?? []) as $i => $storageAcct) {
            // assert($storageAcct['name'] === 'storageAcct');
            if (($storageAcct['name'] ?? '') !== 'storageAcct') continue;

            foreach (($storageAcct['children'] ?? []) as $c) {
                // assert(in_array($c['name'], ['id', 'storageProviderType']));
                if (!isset($returner[$i])) {
                    $isPrimary = isset($storageAcct['attributes']['isPrimary']) && $storageAcct['attributes']['isPrimary'] == 'true';
                    $returner[$i] = [
                        'primary' => $isPrimary
                    ];
                }
                if (isset($c['name'])) {
                    $returner[$i][$c['name']] = $c['content'] ?? null;
                }
            }
        }

        $parser->destroy();
        return $returner;
    }


    //
    // Space management
    //

    /**
     * Get a list of spaces.
     * @param int|null $storeId optional ID of store
     * @return array|bool List of space IDs or false
     */
    public function getSpaces($storeId = DURACLOUD_DEFAULT_STORE) {
        // Get the spaces list
        $dcc = $this->getConnection();
        $xml = $dcc->get(
            $this->getPrefix() . 'spaces',
            $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeID' => $storeId] : []
        );

        if (!$xml) return false;
        
        // Parse the result
        $parser = new DuraCloudXMLParser();
        if (!$parser->parse($xml)) return false;

        $returner = [];
        $spaces = $parser->getResults();
        
        // assert($spaces['name'] === 'spaces');
        if (isset($spaces['children']) && is_array($spaces['children'])) {
            foreach ($spaces['children'] as $c) {
                // assert($c['name'] === 'space');
                if (isset($c['attributes']['id'])) {
                    $returner[] = $c['attributes']['id'];
                }
            }
        }

        $parser->destroy();

        return $returner;
    }

    /**
     * Get a list of a space's contents.
     * @param string $spaceId
     * @param array $metadata Reference to variable that will receive metadata
     * @param int|null $storeId optional
     * @param string|null $prefix optional
     * @param int|null $maxResults optional
     * @param string|null $marker optional
     * @return array|bool List of space IDs or false
     */
    public function getSpace($spaceId, &$metadata, $storeId = DURACLOUD_DEFAULT_STORE, $prefix = null, $maxResults = null, $marker = null) {
        // Get the space contents list
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;
        if ($prefix !== null) $params['prefix'] = $prefix;
        if ($maxResults !== null) $params['maxResults'] = (int) $maxResults;
        if ($marker !== null) $params['marker'] = $marker;
        
        if (!$dcc->get(
            $this->getPrefix() . urlencode($spaceId),
            $params
        )) return false;
        
        $xml = $dcc->getData();
        $headers = $dcc->getHeaders();

        // Parse the result headers to return as metadata
        $metadata = $this->_filterMetadata($headers);

        // Parse the result XML
        $parser = new DuraCloudXMLParser();
        if (!$parser->parse($xml)) return false;

        $returner = [];
        $space = $parser->getResults();
        
        // assert($space['name'] === 'space');
        foreach ((array) ($space['children'] ?? []) as $c) {
            // assert($c['name'] === 'item');
            if (isset($c['content'])) {
                $returner[] = $c['content'];
            }
        }

        $parser->destroy();

        return $returner;
    }

    /**
     * Get a list of a space's metadata.
     * @param string $spaceId
     * @param int|null $storeId optional ID of store
     * @return array|bool List of space metadata
     */
    public function getSpaceMetadata($spaceId, $storeId = DURACLOUD_DEFAULT_STORE) {
        // Get the space metadata list
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;
        
        if (!$dcc->head(
            $this->getPrefix() . urlencode($spaceId),
            $params
        )) return false;
        
        $headers = $dcc->getHeaders();

        // Parse the result headers to return as metadata
        $metadata = $this->_filterMetadata($headers);

        return $metadata;
    }

    /**
     * Create a space.
     * @param string $spaceId
     * @param array $metadata optional
     * @param int|null $storeId optional
     * @return string|bool Location of the new space iff success; false otherwise
     */
    public function createSpace($spaceId, $metadata = [], $storeId = DURACLOUD_DEFAULT_STORE) {
        // Create a new space
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;

        if (!$dcc->put(
            $this->getPrefix() . urlencode($spaceId),
            null, 0, // No file
            $params,
            $this->_addMetadataPrefix($metadata)
        )) return false;
        
        $headers = $dcc->getHeaders();

        if (isset($headers['Location'])) return $headers['Location'];

        return false;
    }

    /**
     * Set a space's metadata.
     * @param string $spaceId
     * @param array $metadata optional
     * @param int|null $storeId optional
     * @return bool success
     */
    public function setSpaceMetadata($spaceId, $metadata, $storeId = DURACLOUD_DEFAULT_STORE) {
        // Update space metadata
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;

        $data = $dcc->post(
            $this->getPrefix() . urlencode($spaceId),
            $params,
            $this->_addMetadataPrefix($metadata)
        );

        return ($data == "Space $spaceId updated successfully");
    }

    /**
     * Delete a space.
     * @param string $spaceId
     * @param int|null $storeId optional
     * @return bool success
     */
    public function deleteSpace($spaceId, $storeId = DURACLOUD_DEFAULT_STORE) {
        // Delete a space
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;

        $data = $dcc->delete(
            $this->getPrefix() . urlencode($spaceId),
            $params
        );

        return ($data == "Space $spaceId deleted successfully");
    }


    //
    // Content management
    //

    /**
     * Store content.
     * @param string $spaceId
     * @param string $contentId
     * @param DuraCloudContent $content
     * @param int|null $storeId optional
     * @return string|bool Location of the new content iff success; false otherwise
     */
    public function storeContent($spaceId, $contentId, $content, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;

        $descriptor = $content->getDescriptor();

        $headers = $this->_addMetadataPrefix($descriptor->getMetadata());
        $headers['Content-Type'] = $descriptor->getContentType();
        if (($md5 = $descriptor->getMD5()) != '') $headers['Content-MD5'] = $md5;

        if (!$dcc->put(
            $this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId),
            $content->getResource(), $content->getSize(),
            $params,
            $headers
        )) return false;
        
        $headers = $dcc->getHeaders();

        if (isset($headers['Location'])) return $headers['Location'];

        return false;
    }

    /**
     * Get content.
     * [WIZDAM FIX] Removed reference return (&)
     * @param string $spaceId
     * @param string $contentId
     * @param int|null $storeId optional ID of store
     * @return DuraCloudContent|bool
     */
    public function getContent($spaceId, $contentId, $storeId = DURACLOUD_DEFAULT_STORE) {
        // Prepare descriptor and content
        $descriptor = new DuraCloudContentDescriptor();
        $content = new DuraCloudFileContent($descriptor);
        $fp = tmpfile();
        $content->setResource($fp);

        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;
        
        if (!$dcc->getFile(
            $this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId),
            $fp,
            $params
        )) {
            return false;
        }
        
        $headers = $dcc->getHeaders();
        if (isset($headers['Content-Type'])) $descriptor->setContentType($headers['Content-Type']);
        if (isset($headers['Content-MD5'])) $descriptor->setMD5($headers['Content-MD5']);

        // Parse the result headers to return as metadata
        $descriptor->setMetadata($this->_filterMetadata($headers));

        return $content;
    }

    /**
     * Get content metadata.
     * @param string $spaceId
     * @param string $contentId
     * @param int|null $storeId optional ID of store
     * @return DuraCloudContentDescriptor|bool false iff failure
     */
    public function getContentMetadata($spaceId, $contentId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;
        
        if (!$dcc->head(
            $this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId),
            $params
        )) return false;
        
        $headers = $dcc->getHeaders();

        // Parse the result headers to return as metadata
        $descriptor = new DuraCloudContentDescriptor($this->_filterMetadata($headers));
        if (isset($headers['Content-MD5'])) $descriptor->setMD5($headers['Content-MD5']);
        if (isset($headers['Content-Type'])) $descriptor->setContentType($headers['Content-Type']);

        return $descriptor;
    }

    /**
     * Set content metadata.
     * @param string $spaceId
     * @param string $contentId
     * @param DuraCloudContentDescriptor $descriptor
     * @param int|null $storeId optional
     * @return bool success
     */
    public function setContentMetadata($spaceId, $contentId, $descriptor, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;

        $headers = $this->_addMetadataPrefix($descriptor->getMetadata());
        if (($contentType = $descriptor->getContentType()) != '') $headers['Content-Type'] = $contentType;
        if (($md5 = $descriptor->getMD5()) != '') $headers['Content-MD5'] = $md5;

        $data = $dcc->post(
            $this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId),
            $params,
            $headers
        );

        return ($data == "Content $contentId updated successfully");
    }

    /**
     * Delete content.
     * @param string $spaceId
     * @param string $contentId
     * @param int|null $storeId optional
     * @return bool success
     */
    public function deleteContent($spaceId, $contentId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = [];
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;

        $data = $dcc->delete(
            $this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId),
            $params
        );

        return ($data == "Content $contentId deleted successfully");
    }


    //
    // For internal use only
    //

    /**
     * Used internally by getSpace and getSpaceMetadata to filter extaneous HTTP headers
     * out of the metadata set and return only the DuraCloud-specific content.
     * @param array $headers
     * @return array
     */
    protected function _filterMetadata($headers) {
        $metadata = [];
        foreach ($headers as $key => $value) {
            if (strpos($key, DURACLOUD_METADATA_PREFIX) === 0) {
                $metadata[substr($key, strlen(DURACLOUD_METADATA_PREFIX))] = $value;
            }
        }

        return $metadata;
    }

    /**
     * Used internally by createSpace and setSpaceMetadata to add the DuraCloud metadata
     * prefix to a set of metadata, returning the result.
     * @param array $metadata
     * @return array
     */
    protected function _addMetadataPrefix($metadata) {
        $headers = [];
        foreach ($metadata as $name => $value) {
            $headers[DURACLOUD_METADATA_PREFIX . $name] = $value;
        }
        return $headers;
    }
}
?>