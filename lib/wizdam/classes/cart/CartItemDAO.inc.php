<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/cart/CartItemDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class CartItemDAO
 * 
 * @brief Data Access Object untuk operasi tabel cart_items (MyISAM).
 * Terisolasi di dalam direktori Wizdam Frontedge.
 */

import('classes.db.DAO');

class CartItemDAO extends DAO {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Periksa apakah item sudah ada di keranjang untuk pengguna tertentu.
     * @param int $userId
     * @param string $itemType
     * @param int $itemReferenceId
     * @return array|null
     */
    public function checkItemExists(int $userId, string $itemType, int $itemReferenceId): ?array {
        $result = $this->retrieve(
            'SELECT cart_item_id, quantity FROM cart_items 
             WHERE user_id = ? AND item_type = ? AND item_reference_id = ?',
            [$userId, $itemType, $itemReferenceId]
        );

        if ($result->RecordCount() > 0) {
            $row = $result->GetRowAssoc(false);
            $result->Close();
            return $row;
        }
        $result->Close();
        return null;
    }

    /**
     * Perbarui kuantitas item di keranjang.
     * @param int $cartItemId
     * @param int $newQuantity
     * @return bool
     */
    public function updateQuantity(int $cartItemId, int $newQuantity): bool {
        return (bool) $this->update(
            'UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?',
            [$newQuantity, $cartItemId]
        );
    }

    /**
     * Masukkan item baru ke dalam keranjang.
     * @param int $userId
     * @param string $itemType
     * @param int $itemReferenceId
     * @param string $itemTitle
     * @param float $unitPrice
     * @param int $quantity
     * @return bool
     */
    public function insertCartItem(int $userId, string $itemType, int $itemReferenceId, string $itemTitle, float $unitPrice, int $quantity): bool {
        return (bool) $this->update(
            'INSERT INTO cart_items 
            (user_id, item_type, item_reference_id, item_title, unit_price, quantity, date_added) 
            VALUES (?, ?, ?, ?, ?, ?, '.$this->datetimeToDB(Core::getCurrentDate()).')',
            [$userId, $itemType, $itemReferenceId, $itemTitle, $unitPrice, $quantity]
        );
    }

    /**
     * Ambil semua item keranjang untuk pengguna tertentu, diurutkan berdasarkan tanggal penambahan.
     * @param int $userId
     * @return array
     */
    public function getItemsByUserId(int $userId): array {
        $result = $this->retrieve(
            'SELECT * FROM cart_items WHERE user_id = ? ORDER BY date_added ASC',
            [$userId]
        );

        $cartItems = [];
        while (!$result->EOF) {
            $cartItems[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        $result->Close();

        return $cartItems;
    }

    /**
     * Hapus item dari keranjang berdasarkan ID item keranjang dan ID pengguna.
     * @param int $userId
     * @param int $cartItemId
     * @return bool
     */
    public function deleteItem(int $userId, int $cartItemId): bool {
        return (bool) $this->update(
            'DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?',
            [$cartItemId, $userId]
        );
    }

    /**
     * Hapus semua item dari keranjang untuk pengguna tertentu.
     * @param int $userId
     * @return bool
     */
    public function deleteItemsByUserId(int $userId): bool {
        return (bool) $this->update(
            'DELETE FROM cart_items WHERE user_id = ?',
            [$userId]
        );
    }
    
}
?>