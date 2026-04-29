<?php
declare(strict_types=1);

/**
 * @file classes/db/DAORegistry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DAORegistry
 * @ingroup db
 * @see DAO
 *
 * @brief Maintains a static list of DAO objects so each DAO is instantiated only once.
 * MODERNIZED FOR PHP 7.4+
 */

import('lib.pkp.classes.db.DAO');

class DAORegistry {

    /** * @var array Static list of instantiated DAOs 
     * Menggantikan Registry::get('daos')
     */
    protected static $daos = array();

    /**
     * Get the current list of registered DAOs.
     * @return array
     */
    public static function getDAOs() {
        return self::$daos;
    }

    /**
     * Register a new DAO with the system.
     * @param $name string The name of the DAO to register
     * @param $dao object The DAO object to be registered
     * @return object The registered DAO
     */
    public static function registerDAO($name, $dao) {
        // [MODERNISASI] Hapus & pada parameter dan return
        // Cek apakah sudah ada sebelumnya (optional logic, sesuai aslinya)
        $returner = isset(self::$daos[$name]) ? self::$daos[$name] : null;
        
        self::$daos[$name] = $dao;
        return $returner;
    }

    /**
     * Retrieve a reference to the specified DAO.
     * @param $name string the class name of the requested DAO
     * @param $dbconn ADONewConnection optional
     * @return DAO
     */
    public static function getDAO($name, $dbconn = null) {
        // 1. Cek apakah DAO sudah ada di memori (Singleton Pattern)
        if (isset(self::$daos[$name])) {
            return self::$daos[$name];
        }

        // 2. Jika Class belum didefinisikan, coba import
        // OJS 2 biasanya mewajibkan import() sebelum getDAO, tapi kita beri safety net.
        if (!class_exists($name)) {
            // Coba cari path dari PKPApplication jika tersedia (Backward Compatibility)
            // Namun jika Anda memodernisasi full, lebih baik explicit import di file pemanggil.
            // Blok ini mencoba meniru logika lama tanpa terlalu bergantung pada global functions.
            $application = PKPApplication::getApplication();
            $className = $application->getQualifiedDAOName($name);
            
            if ($className) {
                // Import berdasarkan hasil mapping aplikasi
                // Format $className biasanya 'classes.journal.JournalDAO'
                import($className);
            }
        }

        // 3. Instansiasi
        if (class_exists($name)) {
            // [MODERNISASI] Gunakan native 'new' operator
            // instanatiate() dihapus karena overhead tidak perlu.
            $instance = new $name($dbconn);

            // Validasi tipe (menggantikan array('DAO', 'XMLDAO') di fungsi instantiate lama)
            if (!($instance instanceof DAO) && !is_a($instance, 'XMLDAO')) {
                 fatalError('DAORegistry: Class "' . $name . '" is not a valid DAO.');
            }

            // [MODERNISASI] Setup DataSource jika diberikan
            // Perhatikan: Constructor DAO baru Anda sudah menangani $dbconn,
            // tapi jika DAO sudah di-instantiate tanpa dbconn, kita set di sini.
            if ($dbconn != null) {
                $instance->setDataSource($dbconn);
            }

            // Simpan ke static array
            self::$daos[$name] = $instance;

            return $instance;
        }

        // 4. Fatal Error jika class tidak ditemukan sama sekali
        fatalError('Unrecognized DAO ' . $name . '! Please ensure the file is imported.');
        return null;
    }
}

?>