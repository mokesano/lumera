<?php
declare(strict_types=1);

/**
 * @file classes/captcha/CaptchaDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CaptchaDAO
 * @ingroup captcha
 * @see Captcha
 *
 * @brief Operations for retrieving and modifying Captcha keys.
 */


import('lib.pkp.classes.captcha.Captcha');

class CaptchaDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CaptchaDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Retrieve captchas by session id
     * @param int|string $sessionId
     * @return Captcha[]
     */
    public function getCaptchasBySessionId($sessionId): array {
        $captchas = [];

        $result = $this->retrieve(
            'SELECT * FROM captchas WHERE session_id = ?',
            [(int) $sessionId]
        );

        while (!$result->EOF) {
            $captchas[] = $this->_returnCaptchaFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $captchas;
    }

    /**
     * Retrieve expired captchas
     * @param int $lifespan optional number of seconds a captcha should last
     * @return Captcha[]
     */
    public function getExpiredCaptchas(int $lifespan = 86400): array {
        $captchas = [];
        $threshold = time() - $lifespan;

        $result = $this->retrieve(
            'SELECT c.*
            FROM captchas c
                LEFT JOIN sessions s ON (s.session_id = c.session_id)
            WHERE s.session_id IS NULL OR
                c.date_created <= ' . $this->datetimeToDB($threshold)
        );

        while (!$result->EOF) {
            $captchas[] = $this->_returnCaptchaFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $captchas;
    }

    /**
     * Retrieve Captcha by captcha id
     * @param int $captchaId
     * @return Captcha|null
     */
    public function getCaptcha(int $captchaId): ?Captcha {
        $result = $this->retrieve(
            'SELECT * FROM captchas WHERE captcha_id = ?',
            [(int) $captchaId]
        );

        $captcha = null;
        if ($result->RecordCount() != 0) {
            $captcha = $this->_returnCaptchaFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $captcha;
    }

    /**
     * Instantiate and return a new data object.
     * @return Captcha
     */
    public function newDataObject(): Captcha {
        return new Captcha();
    }

    /**
     * Creates and returns a captcha object from a row
     * @param array $row
     * @return Captcha
     */
    public function _returnCaptchaFromRow(array $row): Captcha {
        $captcha = $this->newDataObject();
        $captcha->setId((int) $row['captcha_id']);
        $captcha->setSessionId((string) $row['session_id']);
        $captcha->setValue($row['value']);
        $captcha->setDateCreated($this->datetimeFromDB($row['date_created']));

        HookRegistry::dispatch('CaptchaDAO::_returnCaptchaFromRow', [&$captcha, &$row]);

        return $captcha;
    }

    /**
     * inserts a new captcha into captchas table
     * @param Captcha $captcha
     * @return int ID of new captcha
     */
    public function insertCaptcha(Captcha $captcha): int {
        $captcha->setDateCreated(Core::getCurrentDate());
        $this->update(
            sprintf('INSERT INTO captchas
                (session_id, value, date_created)
                VALUES
                (?, ?, %s)',
                $this->datetimeToDB($captcha->getDateCreated())),
            [
                (int) $captcha->getSessionId(),
                $captcha->getValue()
            ]
        );

        $captcha->setId($this->getInsertCaptchaId());
        return (int) $captcha->getId();
    }

    /**
     * Get the ID of the last inserted captcha.
     * @return int
     */
    public function getInsertCaptchaId(): int {
        return (int) $this->getInsertId('captchas', 'captcha_id');
    }

    /**
     * removes a captcha from captchas table
     * @param Captcha $captcha
     */
    public function deleteObject(Captcha $captcha): void {
        $this->update(
            'DELETE FROM captchas WHERE captcha_id = ?',
            [(int) $captcha->getId()]
        );
    }

    /**
     * [Deprecated] Wrapper for deleteObject
     * @param Captcha $captcha
     */
    public function deleteCaptcha(Captcha $captcha) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->deleteObject($captcha);
    }

    /**
     * updates a captcha
     * @param Captcha $captcha
     */
    public function updateObject(Captcha $captcha): void {
        $this->update(
            sprintf('UPDATE captchas
                SET
                    session_id = ?,
                    value = ?,
                    date_created = %s
                WHERE captcha_id = ?',
                $this->datetimeToDB($captcha->getDateCreated())),
            [
                (int) $captcha->getSessionId(),
                $captcha->getValue(),
                (int) $captcha->getId()
            ]
        );
    }

    /**
     * [Deprecated] Wrapper for updateObject
     * @param Captcha $captcha
     */
    public function updateCaptcha(Captcha $captcha) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($captcha);
    }
}

?>