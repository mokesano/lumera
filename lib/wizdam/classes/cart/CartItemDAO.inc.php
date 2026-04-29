<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/cart/CartItemDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class CartItemDAO
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
     * @param int $userId ID pengguna
     * @param string $itemType Tipe item (misalnya 'article', 'issue')
     * @param int $itemReferenceId ID referensi item (misalnya article_id, issue_id)
     * @return array|null Mengembalikan array dengan 'cart_item_id' dan 'quantity' jika item ditemukan, atau null jika tidak ditemukan.
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
     * @param int $cartItemId ID item keranjang
     * @param int $newQuantity Kuantitas baru
     * @return bool Mengembalikan true jika pembaruan berhasil, false jika gagal.
     */
    public function updateQuantity(int $cartItemId, int $newQuantity): bool {
        return (bool) $this->update(
            'UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?',
            [$newQuantity, $cartItemId]
        );
    }

    /**
     * Masukkan item baru ke dalam keranjang.
     * @param int $userId ID pengguna
     * @param string $itemType Tipe item (misalnya 'article', 'issue')
     * @param int $itemReferenceId ID referensi item (misalnya article_id, issue_id)
     * @param string $itemTitle Judul item
     * @param float $unitPrice Harga per unit
     * @param int $quantity Kuantitas
     * @return bool Mengembalikan true jika penyisipan berhasil, false jika gagal.
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
     * @param int $userId ID pengguna
     * @return array Mengembalikan array item keranjang, setiap item adalah array asosiatif dengan kolom dari tabel cart_items.
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
     * @param int $userId ID pengguna
     * @param int $cartItemId ID item keranjang
     * @return bool Mengembalikan true jika penghapusan berhasil, false jika gagal.
     */
    public function deleteItem(int $userId, int $cartItemId): bool {
        return (bool) $this->update(
            'DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?',
            [$cartItemId, $userId]
        );
    }

    /**
     * Hapus semua item dari keranjang untuk pengguna tertentu.
     * @param int $userId ID pengguna
     * @return bool Mengembalikan true jika penghapusan berhasil, false jika gagal.
     */
    public function deleteItemsByUserId(int $userId): bool {
        return (bool) $this->update(
            'DELETE FROM cart_items WHERE user_id = ?',
            [$userId]
        );
    }
}
?>