<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/CheckoutSessionDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class CheckoutSessionDAO
 * 
 * @brief DAO tabel checkout_sessions -- terpisah total dari
 * queued_payments (legacy) dan cart_items (CartService), supaya migrasi
 * data lama tidak pernah lagi berisiko menyentuh sesi checkout WIZDAM aktif.
 */

import('lib.pkp.classes.db.DAO');
import('lib.wizdam.classes.checkout.CheckoutSession');

class CheckoutSessionDAO extends DAO {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get from row CheckoutSession
     * @param array $row
     */
    private function _fromRow(array $row): CheckoutSession {
        $session = new CheckoutSession((float) $row['amount'], (string) $row['currency_code'], (int) $row['user_id'], (int) $row['assoc_id']);
        $session->setSessionId((int) $row['session_id']);
        $session->setJournalId((int) $row['journal_id']);
        $session->setType($row['type']);
        $payload = @json_decode((string) $row['payload'], true);
        $session->setCheckoutPayload(is_array($payload) ? $payload : []);
        return $session;
    }

    /**
     * Get session Id
     * @param int $sessionId
     */
    public function getSession(int $sessionId): ?CheckoutSession {
        $result = $this->retrieve('SELECT * FROM checkout_sessions WHERE session_id = ?', [$sessionId]);
        if ($result && $result->RecordCount() > 0) {
            $session = $this->_fromRow($result->GetRowAssoc(false));
            $result->Close();
            return $session;
        }
        if ($result) $result->Close();
        return null;
    }

    /**
     * Insert cart
     * @param CheckoutSession $session
     */
    public function insertSession(CheckoutSession $session): int {
        $now = Core::getCurrentDate();
        $this->update(
            sprintf(
                'INSERT INTO checkout_sessions (journal_id, user_id, assoc_id, type, amount, currency_code, payload, date_created, date_modified) VALUES (?, ?, ?, ?, ?, ?, ?, %s, %s)',
                $this->datetimeToDB($now), $this->datetimeToDB($now)
            ),
            [
                $session->getJournalId(), $session->getUserId(), $session->getAssocId(), $session->getType(),
                $session->getAmount(), $session->getCurrencyCode(), json_encode($session->getCheckoutPayload())
            ]
        );
        $sessionId = (int) $this->getInsertId();
        $session->setSessionId($sessionId);
        return $sessionId;
    }

    /**
     * Update cart
     * @param int $sessionId
     * @param CheckoutSession $session
     */
    public function updateSession(int $sessionId, CheckoutSession $session): bool {
        return (bool) $this->update(
            sprintf(
                'UPDATE checkout_sessions SET journal_id = ?, user_id = ?, assoc_id = ?, type = ?, amount = ?, currency_code = ?, payload = ?, date_modified = %s WHERE session_id = ?',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            [
                $session->getJournalId(), $session->getUserId(), $session->getAssocId(), $session->getType(),
                $session->getAmount(), $session->getCurrencyCode(), json_encode($session->getCheckoutPayload()), $sessionId
            ]
        );
    }

    /**
     * Delete cart
     * @param int $sessionId
     */
    public function deleteSession(int $sessionId): bool {
        return (bool) $this->update('DELETE FROM checkout_sessions WHERE session_id = ?', [$sessionId]);
    }
    
}
?>