<?php
declare(strict_types=1);

/**
 * @defgroup captcha
 */

/**
 * @file classes/captcha/Captcha.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Captcha
 * @ingroup captcha
 * @see CaptchaDAO, CaptchaManager
 *
 * @brief Class for Captcha verifiers.
 *
 */


class Captcha extends DataObject {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Captcha() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * get captcha id
     * @return int
     */
    public function getCaptchaId(): int {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return (int) $this->getId();
    }

    /**
     * set captcha id
     * @param int $captchaId
     */
    public function setCaptchaId($captchaId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($captchaId);
    }

    /**
     * get session id
     * @return string
     */
    public function getSessionId(): string {
        return (string) $this->getData('sessionId');
    }

    /**
     * set session id
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId) {
        return $this->setData('sessionId', $sessionId);
    }

    /**
     * get value
     * @return string
     */
    public function getValue(): string {
        return (string) $this->getData('value');
    }

    /**
     * set value
     * @param string $value
     */
    public function setValue(string $value) {
        return $this->setData('value', $value);
    }

    /**
     * get poster name
     * @return string
     */
    public function getPosterName(): string {
        return (string) $this->getData('posterName');
    }

    /**
     * set date created
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        return $this->setData('dateCreated', $dateCreated);
    }

    /**
     * get date created
     * @return string|null
     */
    public function getDateCreated() {
        return $this->getData('dateCreated');
    }
}

?>