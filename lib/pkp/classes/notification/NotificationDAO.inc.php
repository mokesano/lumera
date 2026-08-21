<?php
declare(strict_types=1);

/**
 * @file classes/notification/NotificationDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationDAO
 * @ingroup notification
 * @see Notification
 *
 * @brief Operations for retrieving and modifying Notification objects.
 *
 * [WIZDAM BUGFIX -- KEAMANAN] getByUserId(), getByAssoc(), dan
 * getNotificationCount() sebelumnya bisa dipanggil dengan userId=0
 * (dipakai NotificationHandler untuk pengunjung TANPA LOGIN, atau
 * UNSUBSCRIBED_USER_NOTIFICATION=0 di AnnouncementForm.inc.php untuk
 * artefak internal mailing-list) dan TETAP menjalankan query yang bisa
 * mencocokkan baris user_id=0 sungguhan -- mengekspos informasi yang
 * seharusnya privat (mis. lewat /notification) ke SIAPA PUN tanpa
 * perlu autentikasi sama sekali.
 *
 * Diperbaiki di lapisan DAO (bukan di satu Handler saja) supaya
 * melindungi SEMUA pemanggil ketiga method ini di seluruh aplikasi.
 * "AND user_id > 0" ditambahkan langsung ke SQL: untuk userId positif
 * sungguhan kondisi ini selalu benar (tidak mengubah perilaku sama
 * sekali), untuk userId=0/negatif kondisi ini menjamin nol baris
 * cocok, tanpa percabangan kode tambahan.
 */

import('classes.notification.Notification');

class NotificationDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::NotificationDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve Notification by notification id
     * @param mixed $notificationId
     * @return Notification|null
     */
    public function getById($notificationId) {
        $result = $this->retrieve(
            'SELECT * FROM notifications WHERE notification_id = ?',
            [(int) $notificationId]
        );

        $notification = null;
        if (!$result->EOF) {
            $notification = $this->_returnNotificationFromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $notification;
    }

    /**
     * Retrieve Notifications by user id
     * Note that this method will not return fully-fledged notification objects. Use
     * NotificationManager::getNotificationsForUser() to get notifications with URL, and contents
     * @param mixed $userId
     * @param mixed $level
     * @param mixed $type
     * @param mixed $contextId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getByUserId($userId, $level = NOTIFICATION_LEVEL_NORMAL, $type = null, $contextId = null, $rangeInfo = null) {
        $params = [(int) $userId, (int) $level];
        // [WIZDAM BUGFIX -- KEAMANAN] "AND user_id > 0" -- lihat dokblok
        // kelas di atas.
        $sql = 'SELECT * FROM notifications WHERE user_id = ? AND user_id > 0 AND level = ?';
        
        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = (int) $type;
        }
        if ($contextId !== null) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }
        // [WIZDAM BUGFIX] "date_created" presisinya cuma sampai DETIK
        // (Core::getCurrentDate() -> date('Y-m-d H:i:s'), tanpa
        // milidetik). Notifikasi yang dibuat berturut-turut dalam satu
        // foreach/while (mis. menotifikasi beberapa editor sekaligus
        // saat artikel disubmit -- lihat SubmitHandler.inc.php,
        // Action.inc.php) sangat mungkin punya date_created IDENTIK.
        // Tanpa penentu urutan kedua, MySQL/MariaDB TIDAK MENJAMIN
        // urutan relatif antar baris yang nilainya sama -- bisa tampak
        // tidak kronologis. notification_id (AUTO_INCREMENT, mencerminkan
        // urutan INSERT persis) dipakai sebagai penentu urutan kedua
        // yang deterministik.
        $sql .= ' ORDER BY date_created DESC, notification_id DESC';

        $result = $this->retrieveRange($sql, $params, $rangeInfo);
        return new DAOResultFactory($result, $this, '_returnNotificationFromRow');
    }

    /**
     * Retrieve Notifications by assoc.
     * Note that this method will not return fully-fledged notification objects. Use
     * NotificationManager::getNotificationsForUser() to get notifications with URL, and contents
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $userId
     * @param mixed $type
     * @param mixed $contextId
     * @return DAOResultFactory
     */
    public function getByAssoc($assocType, $assocId, $userId = null, $type = null, $contextId = null) {
        $params = [(int) $assocType, (int) $assocId];
        $sql = 'SELECT * FROM notifications WHERE assoc_type = ? AND assoc_id = ?';
        
        if ($userId !== null) {
            // [WIZDAM BUGFIX -- KEAMANAN] "AND user_id > 0" ditambahkan
            // HANYA di cabang ini (saat userId eksplisit diberikan) --
            // userId===null (maksud "semua pengguna", dipakai untuk tipe
            // notifikasi yang memang publik-ke-semua-pengguna terdaftar)
            // TIDAK disentuh, tetap berperilaku sama seperti sebelumnya.
            $sql .= ' AND user_id = ? AND user_id > 0';
            $params[] = (int) $userId;
        }
        if ($contextId !== null) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }
        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = (int) $type;
        }
        // [WIZDAM BUGFIX] Sama seperti getByUserId() di atas -- lihat
        // dokblok di sana untuk penjelasan lengkap.
        $sql .= ' ORDER BY date_created DESC, notification_id DESC';

        $result = $this->retrieveRange($sql, $params);
        return new DAOResultFactory($result, $this, '_returnNotificationFromRow');
    }

    /**
     * Retrieve Notifications by notification id
     * @param mixed $notificationId
     * @param string|null $dateRead
     * @return string
     */
    public function setDateRead($notificationId, $dateRead = null, $userId = null) {
        $dateRead = $dateRead ?? Core::getCurrentDate();

        // [WIZDAM BUGFIX -- KEAMANAN] Sebelumnya method ini hanya
        // memfilter "WHERE notification_id = ?" TANPA verifikasi
        // kepemilikan -- berbeda dari deleteById() yang sudah benar
        // memakai "AND user_id = ?" saat $userId diberikan. Sekarang
        // konsisten: kalau $userId diberikan, notifikasi milik pengguna
        // LAIN tidak bisa ditandai lewat panggilan ini.
        $params = [$this->datetimeToDB($dateRead), (int) $notificationId];
        $sql = 'UPDATE notifications SET date_read = ? WHERE notification_id = ?';
        if ($userId !== null) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }

        $this->update($sql, $params);

        return $dateRead;
    }

    /**
     * Instantiate and return a new data object.
     * @return Notification
     */
    public function newDataObject() {
        return new Notification();
    }

    /**
     * Creates and returns an notification object from a row
     * @param array $row
     * @return Notification
     */
    public function _returnNotificationFromRow($row) {
        $notification = $this->newDataObject();
        $notification->setId((int) $row['notification_id']);
        $notification->setUserId((int) $row['user_id']);
        $notification->setLevel((int) $row['level']);
        $notification->setDateCreated($this->datetimeFromDB($row['date_created']));
        $notification->setDateRead($this->datetimeFromDB($row['date_read']));
        $notification->setContextId((int) $row['context_id']);
        $notification->setType((int) $row['type']);
        $notification->setAssocType((int) $row['assoc_type']);
        $notification->setAssocId((int) $row['assoc_id']);

        HookRegistry::dispatch('NotificationDAO::_returnNotificationFromRow', [$notification, &$row]);

        return $notification;
    }

    /**
     * Inserts a new notification into notifications table
     * @param Notification $notification
     * @return int Notification Id
     */
    public function insertObject($notification) {
        // [WIZDAM BUGFIX -- AKAR MASALAH date_created '0000-00-00
        // 00:00:00'] SEBELUMNYA memakai $this->datetimeToDB(...) --
        // yang mendelegasikan ke ADOdb DBTimeStamp(). Method itu
        // MEMBUNGKUS string tanggal dengan tanda kutip literal SEBAGAI
        // BAGIAN dari nilai return-nya sendiri (lihat
        // lib/pkp/lib/adodb/adodb.inc.php:
        // "if ($this->isoDates && strlen($ts) !== 14) return \"'$ts'\";"
        // -- Core::getCurrentDate() berformat "Y-m-d H:i:s", 19 karakter,
        // bukan 14, jadi baris ini SELALU terpicu untuk kolom ini).
        //
        // DBTimeStamp() dirancang untuk disisipkan LANGSUNG ke TEKS SQL
        // (pola ADOdb lama, "..VALUES (" . $db->DBTimeStamp($d) . ")"),
        // BUKAN dipakai sebagai nilai PARAMETER TERIKAT (?) seperti di
        // sini. Query ini query BERPARAMETER ($this->update($sql,
        // $params) dengan placeholder ?) -- nilai yang SUDAH DIBUNGKUS
        // KUTIP itu diteruskan APA ADANYA sebagai parameter, membuat
        // MySQL menerima string tanggal CACAT (kutip literal ikut jadi
        // bagian nilai) -- gagal parse, diam-diam diganti
        // '0000-00-00 00:00:00' (perilaku default MySQL mode non-strict
        // untuk tanggal tidak valid, TANPA error yang terlihat sama
        // sekali -- dibuktikan lewat diagnostik langsung: nilai PHP-nya
        // benar, tapi yang tersimpan di database tetap kosong).
        //
        // Dikonfirmasi lewat datetimeFromDB() (pembacaan baliknya) yang
        // mengharapkan format "Y-m-d H:i:s" MENTAH -- persis yang
        // dihasilkan Core::getCurrentDate() -- sehingga TIDAK PERLU
        // dibungkus datetimeToDB() sama sekali untuk konteks parameter
        // terikat seperti ini. Nilai mentah dipakai langsung.
        $this->update(
            'INSERT INTO notifications (user_id, level, date_created, context_id, type, assoc_type, assoc_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $notification->getUserId(),
                (int) $notification->getLevel(),
                Core::getCurrentDate(),
                (int) $notification->getContextId(),
                (int) $notification->getType(),
                (int) $notification->getAssocType(),
                (int) $notification->getAssocId()
            ]
        );
        $notification->setId($this->getInsertNotificationId());

        return $notification->getId();
    }

    /**
     * Inserts or update a notification into notifications table.
     * @param Notification $notification
     * @return void
     */
    public function build($notification) {
        $this->update(
            'DELETE FROM notifications WHERE context_id = ? AND level = ? AND type = ? AND user_id = ? AND assoc_type = ? AND assoc_id = ?',
            [
                (int) $notification->getContextId(),
                (int) $notification->getLevel(),
                (int) $notification->getType(),
                (int) $notification->getUserId(),
                (int) $notification->getAssocType(),
                (int) $notification->getAssocId()
            ]
        );
        $this->insertObject($notification);
    }

    /**
     * Delete Notification by notification id
     * @param mixed $notificationId
     * @param mixed $userId
     * @return bool
     */
    public function deleteById($notificationId, $userId = null) {
        $params = [(int) $notificationId];
        $sql = 'DELETE FROM notifications WHERE notification_id = ?';
        
        if ($userId !== null) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }
        
        $this->update($sql, $params);
        
        if ($this->getAffectedRows()) {
            /** @var NotificationSettingsDAO $notificationSettingsDao */
            $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
            $notificationSettingsDao->deleteSettingsByNotificationId($notificationId);
            return true;
        }
        return false;
    }

    /**
     * Delete Notification
     * @param Notification $notification
     * @return bool
     */
    public function deleteObject($notification) {
        return $this->deleteById($notification->getId());
    }

    /**
     * Delete notification(s) by association
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $userId
     * @param mixed $type
     * @param mixed $contextId
     * @return void
     */
    public function deleteByAssoc($assocType, $assocId, $userId = null, $type = null, $contextId = null) {
        $notificationsFactory = $this->getByAssoc($assocType, $assocId, $userId, $type, $contextId);
        while ($notification = $notificationsFactory->next()) {
            $this->deleteObject($notification);
        }
    }

    /**
     * Get the ID of the last inserted notification
     * @return int
     */
    public function getInsertNotificationId() {
        return $this->getInsertId('notifications', 'notification_id');
    }

    /**
     * Get the number of unread messages for a user
     * @param mixed $userId
     * @param mixed $contextId
     * @param mixed $level
     * @param bool $read
     * @return int
     */
    public function getNotificationCount($userId, $contextId = null, $level = NOTIFICATION_LEVEL_NORMAL, $read = true) {
        $params = [(int) $userId, (int) $level];
        // [WIZDAM BUGFIX -- KEAMANAN] "AND user_id > 0" -- lihat dokblok
        // kelas di atas.
        $sql = 'SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND user_id > 0 AND date_read IS' . ($read ? ' NOT' : '') . ' NULL AND level = ?';
        
        if ($contextId !== null) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve($sql, $params);
        
        $returner = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (int) $row['count'];
        }
        $result->Close();

        return $returner;
    }

    /**
     * Transfer the notifications for a user.
     * @param mixed $oldUserId
     * @param mixed $newUserId
     * @return void
     */
    public function transferNotifications($oldUserId, $newUserId) {
        $this->update(
            'UPDATE notifications SET user_id = ? WHERE user_id = ?',
            [(int) $newUserId, (int) $oldUserId]
        );
    }

}
?>