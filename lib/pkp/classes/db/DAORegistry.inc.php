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
 */

import('lib.pkp.classes.db.DAO');

class DAORegistry {

    /**
     * @var array Static list of instantiated DAOs 
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
     * @param mixed $name string The name of the DAO to register
     * @param mixed $dao object The DAO object to be registered
     * @return object The registered DAO
     */
    public static function registerDAO($name, $dao) {
        $returner = isset(self::$daos[$name]) ? self::$daos[$name] : null;
        self::$daos[$name] = $dao;
        return $returner;
    }

    /**
     * Retrieve a reference to the specified DAO.
     * @param mixed $name string the class name of the requested DAO
     * @param $dbconn ADONewConnection optional
     * @return DAO
     */
    public static function getDAO($name, $dbconn = null) {
        // 1. Cek apakah DAO sudah ada di memori (Singleton Pattern)
        if (isset(self::$daos[$name])) {
            return self::$daos[$name];
        }

        // 2. Jika Class belum didefinisikan, coba import
        if (!class_exists($name)) {
            $application = PKPApplication::getApplication();
            $className = $application->getQualifiedDAOName($name);
            
            if ($className) {
                import($className);
            }
        }

        // 3. Instansiasi
        if (class_exists($name)) {
            $instance = new $name($dbconn);
            if (!($instance instanceof DAO) && !is_a($instance, 'XMLDAO')) {
                 fatalError('DAORegistry: Class "' . $name . '" is not a valid DAO.');
            }

            if ($dbconn != null) {
                $instance->setDataSource($dbconn);
            }

            self::$daos[$name] = $instance;

            return $instance;
        }

        // 4. Fatal Error jika class tidak ditemukan sama sekali
        fatalError('Unrecognized DAO ' . $name . '! Please ensure the file is imported.');
        return null;
    }
}
?>