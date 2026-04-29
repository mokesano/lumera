<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/medra/classes/MedraWebservice.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MedraWebservice
 * @ingroup plugins_importexport_medra_classes
 *
 * @brief A wrapper for the mEDRA web service 2.0.
 *
 * NB: We do not use PHP's SoapClient because it is not PHP4 compatible and
 * it doesn't support multipart SOAP messages.
 */

import('lib.pkp.classes.xml.XMLNode');

define('MEDRA_WS_ENDPOINT_DEV', 'https://medra.dev.cineca.it/servlet/ws/medraWS');
define('MEDRA_WS_ENDPOINT', 'https://www.medra.org/servlet/ws/medraWS');
define('MEDRA_WS_RESPONSE_OK', 200);

class MedraWebservice {

    /** @var string HTTP authentication credentials. */
    protected $_auth;

    /** @var string The mEDRA web service endpoint. */
    protected $_endpoint;

    /**
     * Constructor
     * @param string $endpoint The mEDRA web service endpoint.
     * @param string $login
     * @param string $password
     */
    public function __construct(string $endpoint, string $login, string $password) {
        $this->_endpoint = $endpoint;
        $this->_auth = "$login:$password";
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MedraWebservice() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Public Web Service Actions
    //
    /**
     * mEDRA upload operation.
     * @param string $xml
     * @return bool|string
     */
    public function upload(string $xml) {
        $attachmentId = $this->_getContentId('metadata');
        $attachment = [$attachmentId => $xml];
        $arg = "<med:contentID href=\"$attachmentId\" />";
        return $this->_doRequest('upload', $arg, $attachment);
    }

    /**
     * mEDRA viewMetadata operation
     * @param string $doi
     * @return bool|string
     */
    public function viewMetadata(string $doi) {
        $doi = $this->_escapeXmlEntities($doi);
        $arg = "<med:doi>$doi</med:doi>";
        return $this->_doRequest('viewMetadata', $arg);
    }

    //
    // Internal helper methods.
    //
    /**
     * Do the actual web service request.
     * @param string $action
     * @param string $arg
     * @param array|null $attachment
     * @return bool|string True for success, an error message otherwise.
     */
    protected function _doRequest(string $action, string $arg, ?array $attachment = null) {
        // Build the multipart SOAP message from scratch.
        $soapMessage =
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" ' .
                    'xmlns:med="http://www.medra.org">' .
                '<SOAP-ENV:Header/>' .
                '<SOAP-ENV:Body>' .
                    "<med:$action>$arg</med:$action>" .
                '</SOAP-ENV:Body>' .
            '</SOAP-ENV:Envelope>';

        $soapMessageId = $this->_getContentId($action);
        if ($attachment) {
            assert(count($attachment) == 1);
            $request =
                "--MIME_boundary\r\n" .
                $this->_getMimePart($soapMessageId, $soapMessage) .
                "--MIME_boundary\r\n" .
                $this->_getMimePart((string) key($attachment), current($attachment)) .
                "--MIME_boundary--\r\n";
            $contentType = 'multipart/related; type="text/xml"; boundary="MIME_boundary"';
        } else {
            $request = $soapMessage;
            $contentType = 'text/xml';
        }

        // Prepare HTTP session.
        $curlCh = curl_init();
        if ($httpProxyHost = Config::getVar('proxy', 'http_host')) {
            curl_setopt($curlCh, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            if ($username = Config::getVar('proxy', 'username')) {
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlCh, CURLOPT_POST, true);

        // Set up basic authentication.
        curl_setopt($curlCh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curlCh, CURLOPT_USERPWD, $this->_auth);

        // Set up SSL.
        curl_setopt($curlCh, CURLOPT_SSL_VERIFYPEER, false);

        // Make SOAP request.
        curl_setopt($curlCh, CURLOPT_URL, $this->_endpoint);
        $extraHeaders = [
            'SOAPAction: "' . $action . '"',
            'Content-Type: ' . $contentType,
            'UserAgent: OJS-mEDRA'
        ];
        curl_setopt($curlCh, CURLOPT_HTTPHEADER, $extraHeaders);
        curl_setopt($curlCh, CURLOPT_POSTFIELDS, $request);

        $result = true;
        $response = curl_exec($curlCh);
        $status = curl_getinfo($curlCh, CURLINFO_HTTP_CODE);

        // We do not localize our error messages as they are all
        // fatal errors anyway and must be analyzed by technical staff.
        if ($response === false) {
            $result = 'OJS-mEDRA: Expected string response.';
        }

        if ($result === true && $status != MEDRA_WS_RESPONSE_OK) {
            $result = 'OJS-mEDRA: Expected ' . MEDRA_WS_RESPONSE_OK . ' response code, got ' . $status . ' instead.';
        }

        curl_close($curlCh);

        // Check SOAP response by simple string manipulation rather
        // than instantiating a DOM.
        if (is_string($response)) {
            $matches = [];
            PKPString::regexp_match_get('#<faultstring>([^<]*)</faultstring>#', $response, $matches);
            if (empty($matches)) {
                if ($attachment) {
                    assert(PKPString::regexp_match('#<returnCode>success</returnCode>#', $response));
                } else {
                    $parts = explode("\r\n\r\n", $response);
                    $result = array_pop($parts);
                    $result = PKPString::regexp_replace('/>[^>]*$/', '>', $result);
                }
            } else {
                $result = 'mEDRA: ' . $status . ' - ' . $matches[1];
            }
        } else {
            $result = 'OJS-mEDRA: Expected string response.';
        }

        return $result;
    }

    /**
     * Create a mime part with the given content.
     * @param string $contentId
     * @param string $content
     * @return string
     */
    protected function _getMimePart(string $contentId, string $content): string {
        return
            "Content-Type: text/xml; charset=utf-8\r\n" .
            "Content-ID: <{$contentId}>\r\n" .
            "\r\n" .
            $content . "\r\n";
    }

    /**
     * Create a globally unique MIME content ID.
     * @param string $prefix
     * @return string
     */
    protected function _getContentId(string $prefix): string {
        return $prefix . md5(uniqid()) . '@medra.org';
    }

    /**
     * Escape XML entities.
     * @param string $string
     * @return string
     */
    protected function _escapeXmlEntities(string $string): string {
        return XMLNode::xmlentities($string);
    }
}

?>