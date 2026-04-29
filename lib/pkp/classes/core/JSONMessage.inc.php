<?php
declare(strict_types=1);

/**
 * @file classes/core/JSONMessage.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JSONMessage
 * @ingroup core
 *
 * @brief Class to represent a JSON (Javascript Object Notation) message.
 *
 */

class JSONMessage {
    /** @var string The status of an event (e.g. false if form validation fails). */
    public $_status;

    /** @var Mixed The message to be delivered back to the calling script. */
    public $_content;

    /** @var string ID for DOM element that will be replaced. */
    public $_elementId;

    /** @var array A JS event generated on the server side. */
    public $_event;

    /** @var array Set of additional attributes for special cases. */
    public $_additionalAttributes;

    /** @var boolean An internal variable used for unit testing only. */
    public $_simulatePhp4 = false;

    /**
     * Constructor.
     * @param $status boolean The status of an event (e.g. false if form validation fails).
     * @param $content Mixed The message to be delivered back to the calling script.
     * @param $elementId string The DOM element to be replaced.
     * @param $additionalAttributes array Additional data to be returned.
     */
    public function __construct($status = true, $content = '', $elementId = '0', $additionalAttributes = null) {
        // Set internal state.
        $this->setStatus($status);
        $this->setContent($content);
        $this->setElementId($elementId);
        if (isset($additionalAttributes)) {
            $this->setAdditionalAttributes($additionalAttributes);
        }
    }

    /**
     * Backward compatibility shim for PHP 4 constructor.
     */
    public function JSONMessage($status = true, $content = '', $elementId = '0', $additionalAttributes = null) {
        trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::JSONMessage(). Please refactor to use parent::__construct().", E_USER_DEPRECATED);
        self::__construct($status, $content, $elementId, $additionalAttributes);
    }

    /**
     * Get the status string
     * @return string
     */
    public function getStatus () {
        return $this->_status;
    }

    /**
     * Set the status string
     * @param $status string
     */
    public function setStatus($status) {
        assert(is_bool($status));
        $this->_status = $status;
    }

    /**
     * Get the content string
     * @return mixed
     */
    public function getContent() {
        return $this->_content;
    }

    /**
     * Set the content data
     * @param $content mixed
     */
    public function setContent($content) {
        $this->_content = $content;
    }

    /**
     * Get the elementId string
     * @return string
     */
    public function getElementId() {
        return $this->_elementId;
    }

    /**
     * Set the elementId string
     * @param $elementId string
     */
    public function setElementId($elementId) {
        // In strict mode or PHP 8, make sure we aren't passing null before assertion if possible,
        // though the constructor defaults to '0'.
        assert(is_string($elementId) || is_numeric($elementId));
        $this->_elementId = $elementId;
    }

    /**
     * Set the event to trigger with this JSON message
     * @param $eventName string
     * @param $eventData string
     */
    public function setEvent($eventName, $eventData = null) {
        assert(is_string($eventName));

        // Construct the even as an associative array.
        $event = array('name' => $eventName);
        if(!is_null($eventData)) $event['data'] = $eventData;

        $this->_event = $event;
    }

    /**
     * Get the event to trigger with this JSON message
     * @return array
     */
    public function getEvent() {
        return $this->_event;
    }

    /**
     * Get the additionalAttributes array
     * @return array
     */
    public function getAdditionalAttributes() {
        return $this->_additionalAttributes;
    }

    /**
     * Set the additionalAttributes array
     * @param $additionalAttributes array
     */
    public function setAdditionalAttributes($additionalAttributes) {
        assert(is_array($additionalAttributes));
        $this->_additionalAttributes = $additionalAttributes;
    }

    /**
     * Set to simulate a PHP4 environment.
     * This is for internal use in unit tests only.
     * @param $simulatePhp4 boolean
     */
    public function setSimulatePhp4($simulatePhp4) {
        assert(is_bool($simulatePhp4));
        $this->_simulatePhp4 = $simulatePhp4;
    }

    /**
     * Construct a JSON string to use for AJAX communication
     * @return string
     */
    public function getString() {
        // Construct an associative array that contains all information we require.
        $jsonObject = array(
            'status' => $this->getStatus(),
            'content' => $this->getContent(),
            'elementId' => $this->getElementId()
        );
        if(is_array($this->getAdditionalAttributes())) {
            foreach($this->getAdditionalAttributes() as $key => $value) {
                $jsonObject[$key] = $value;
            }
        }
        if(is_array($this->getEvent())) {
            $jsonObject['event'] = $this->getEvent();
        }

        // Encode the object.
        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();
        return $jsonManager->encode($jsonObject);
    }
}

?>