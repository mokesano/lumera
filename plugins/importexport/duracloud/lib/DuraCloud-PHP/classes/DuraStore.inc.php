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
 * @brief DuraStore client implementation.
 */

define('DURACLOUD_SPACE_ACCESS', 'space-access');
define('DURACLOUD_SPACE_ACCESS_OPEN', 'OPEN');
define('DURACLOUD_SPACE_ACCESS_CLOSED', 'CLOSED');
define('DURACLOUD_SPACE_COUNT', 'space-count');
define('DURACLOUD_SPACE_CREATED', 'space-created');
define('DURACLOUD_DEFAULT_STORE', null);
define('DURACLOUD_METADATA_PREFIX', 'x-dura-meta-');

class DuraStore extends DuraCloudComponent {
    
    /**
     * Constructor.
     * @param DuraCloudConnection $dcc
     */
    public function __construct($dcc) {
        parent::__construct($dcc, 'durastore');
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param DuraCloudConnection $dcc
     */
    public function DuraStore($dcc) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
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
     * @return array|false
     */
    public function getStores() {
        $dcc = $this->getConnection();
        $xml = $dcc->get($this->getPrefix() . 'stores');
        if (!$xml) {
            return false;
        }

        $parser = new DuraCloudXMLParser();
        if (!$parser->parse($xml)) {
            return false;
        }

        $returner = [];
        $storageProviderAccounts = $parser->getResults();
        
        if (!isset($storageProviderAccounts['name']) || $storageProviderAccounts['name'] !== 'storageProviderAccounts') {
             $parser->destroy();
             return false;
        }

        foreach ((array) ($storageProviderAccounts['children'] ?? []) as $i => $storageAcct) {
            if (($storageAcct['name'] ?? '') !== 'storageAcct') {
                continue;
            }

            foreach ((array) ($storageAcct['children'] ?? []) as $c) {
                if (!isset($returner[$i])) {
                    $isPrimary = isset($storageAcct['attributes']['isPrimary']) && $storageAcct['attributes']['isPrimary'] === 'true';
                    $returner[$i] = ['primary' => $isPrimary];
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
     * @param int|null $storeId
     * @return array|false
     */
    public function getSpaces($storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeID' => $storeId] : [];
        $xml = $dcc->get($this->getPrefix() . 'spaces', $params);

        if (!$xml) {
            return false;
        }
        
        $parser = new DuraCloudXMLParser();
        if (!$parser->parse($xml)) {
            return false;
        }

        $returner = [];
        $spaces = $parser->getResults();
        
        if (isset($spaces['children']) && is_array($spaces['children'])) {
            foreach ($spaces['children'] as $c) {
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
     * @param array &$metadata
     * @param int|null $storeId
     * @param string|null $prefix
     * @param int|null $maxResults
     * @param string|null $marker
     * @return array|false
     */
    public function getSpace($spaceId, &$metadata, $storeId = DURACLOUD_DEFAULT_STORE, $prefix = null, $maxResults = null, $marker = null) {
        $dcc = $this->getConnection();
        $params = [];
        
        if ($storeId !== DURACLOUD_DEFAULT_STORE) $params['storeId'] = $storeId;
        if ($prefix !== null) $params['prefix'] = $prefix;
        if ($maxResults !== null) $params['maxResults'] = (int) $maxResults;
        if ($marker !== null) $params['marker'] = $marker;
        
        if (!$dcc->get($this->getPrefix() . urlencode($spaceId), $params)) {
            return false;
        }
        
        $xml = $dcc->getData();
        $headers = $dcc->getHeaders();
        $metadata = $this->_filterMetadata($headers);

        $parser = new DuraCloudXMLParser();
        if (!$parser->parse($xml)) {
            return false;
        }

        $returner = [];
        $space = $parser->getResults();
        
        foreach ((array) ($space['children'] ?? []) as $c) {
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
     * @param int|null $storeId
     * @return array|false
     */
    public function getSpaceMetadata($spaceId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];
        
        if (!$dcc->head($this->getPrefix() . urlencode($spaceId), $params)) {
            return false;
        }
        
        $headers = $dcc->getHeaders();
        return $this->_filterMetadata($headers);
    }

    /**
     * Create a space.
     * @param string $spaceId
     * @param array $metadata
     * @param int|null $storeId
     * @return string|false
     */
    public function createSpace($spaceId, $metadata = [], $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];

        if (!$dcc->put($this->getPrefix() . urlencode($spaceId), null, 0, $params, $this->_addMetadataPrefix($metadata))) {
            return false;
        }
        
        $headers = $dcc->getHeaders();
        return $headers['Location'] ?? false;
    }

    /**
     * Set a space's metadata.
     * @param string $spaceId
     * @param array $metadata
     * @param int|null $storeId
     * @return bool
     */
    public function setSpaceMetadata($spaceId, $metadata, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];

        $data = $dcc->post($this->getPrefix() . urlencode($spaceId), $params, $this->_addMetadataPrefix($metadata));
        return $data === "Space $spaceId updated successfully";
    }

    /**
     * Delete a space.
     * @param string $spaceId
     * @param int|null $storeId
     * @return bool
     */
    public function deleteSpace($spaceId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];

        $data = $dcc->delete($this->getPrefix() . urlencode($spaceId), $params);
        return $data === "Space $spaceId deleted successfully";
    }

    //
    // Content management
    //

    /**
     * Store content.
     * @param string $spaceId
     * @param string $contentId
     * @param object $content
     * @param int|null $storeId
     * @return string|false
     */
    public function storeContent($spaceId, $contentId, $content, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];
        $descriptor = $content->getDescriptor();

        $headers = $this->_addMetadataPrefix($descriptor->getMetadata());
        $headers['Content-Type'] = $descriptor->getContentType();
        
        $md5 = $descriptor->getMD5();
        if ($md5 !== '') {
            $headers['Content-MD5'] = $md5;
        }

        $size = method_exists($content, 'getSize') ? (int) $content->getSize() : 0;

        if (!$dcc->put($this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId), $content->getResource(), $size, $params, $headers)) {
            return false;
        }
        
        $headers = $dcc->getHeaders();
        return $headers['Location'] ?? false;
    }

    /**
     * Get content.
     * @param string $spaceId
     * @param string $contentId
     * @param int|null $storeId
     * @return DuraCloudContent|false
     */
    public function getContent($spaceId, $contentId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $descriptor = new DuraCloudContentDescriptor();
        $content = new DuraCloudFileContent($descriptor);
        $fp = tmpfile();
        $content->setResource($fp);

        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];
        
        if (!$dcc->getFile($this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId), $fp, $params)) {
            return false;
        }
        
        $headers = $dcc->getHeaders();
        if (isset($headers['Content-Type'])) {
            $descriptor->setContentType($headers['Content-Type']);
        }
        if (isset($headers['Content-MD5'])) {
            $descriptor->setMD5($headers['Content-MD5']);
        }

        $descriptor->setMetadata($this->_filterMetadata($headers));
        return $content;
    }

    /**
     * Get content metadata.
     * @param string $spaceId
     * @param string $contentId
     * @param int|null $storeId
     * @return DuraCloudContentDescriptor|false
     */
    public function getContentMetadata($spaceId, $contentId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];
        
        if (!$dcc->head($this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId), $params)) {
            return false;
        }
        
        $headers = $dcc->getHeaders();
        $descriptor = new DuraCloudContentDescriptor($this->_filterMetadata($headers));
        
        if (isset($headers['Content-MD5'])) {
            $descriptor->setMD5($headers['Content-MD5']);
        }
        if (isset($headers['Content-Type'])) {
            $descriptor->setContentType($headers['Content-Type']);
        }

        return $descriptor;
    }

    /**
     * Set content metadata.
     * @param string $spaceId
     * @param string $contentId
     * @param DuraCloudContentDescriptor $descriptor
     * @param int|null $storeId
     * @return bool
     */
    public function setContentMetadata($spaceId, $contentId, $descriptor, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];

        $headers = $this->_addMetadataPrefix($descriptor->getMetadata());
        $contentType = $descriptor->getContentType();
        if ($contentType !== '') {
            $headers['Content-Type'] = $contentType;
        }
        
        $md5 = $descriptor->getMD5();
        if ($md5 !== '') {
            $headers['Content-MD5'] = $md5;
        }

        $data = $dcc->post($this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId), $params, $headers);
        return $data === "Content $contentId updated successfully";
    }

    /**
     * Delete content.
     * @param string $spaceId
     * @param string $contentId
     * @param int|null $storeId
     * @return bool
     */
    public function deleteContent($spaceId, $contentId, $storeId = DURACLOUD_DEFAULT_STORE) {
        $dcc = $this->getConnection();
        $params = $storeId !== DURACLOUD_DEFAULT_STORE ? ['storeId' => $storeId] : [];

        $data = $dcc->delete($this->getPrefix() . urlencode($spaceId) . '/' . urlencode($contentId), $params);
        return $data === "Content $contentId deleted successfully";
    }

    //
    // For internal use only
    //

    /**
     * Filter extraneous HTTP headers to return only DuraCloud-specific metadata.
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
     * Add the DuraCloud metadata prefix to a set of metadata.
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