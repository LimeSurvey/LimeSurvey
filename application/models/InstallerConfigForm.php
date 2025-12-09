<?php

/*
   * LimeSurvey
   * Copyright (C) 2013-2026 The LimeSurvey Project Team
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *  Files Purpose: lots of common functions
*/

/**
 * Class InstallerConfigForm
 * @property array $dbEngines the MySQL database engines as [value=>'label']
 * @property boolean $isMysql whether the db type is mysql or mysqli
 * @property boolean $isMSSql whether the db type is one of MS Sql types
 * @property float|integer $memoryLimit
 * @property boolean $hasMinimumRequirements
 * @property boolean $isConfigDirWriteable
 * @property boolean $isUploadDirWriteable
 * @property boolean $isTmpDirWriteable
 * @property string[] $supportedDbTypes
 */
class InstallerConfigForm extends CFormModel
{
    public const ENGINE_TYPE_MYISAM = 'MYISAM';
    public const ENGINE_TYPE_INNODB = 'INNODB';

    public const DB_TYPE_MYSQL = 'mysql';
    public const DB_TYPE_MYSQLI = 'mysqli';
    public const DB_TYPE_SQLSRV = 'sqlsrv';
    public const DB_TYPE_MSSQL = 'mssql';
    public const DB_TYPE_DBLIB = 'dblib';
    public const DB_TYPE_PGSQL = 'pgsql';
    public const DB_TYPE_ODBC = 'odbc';

    public const MINIMUM_MEMORY_LIMIT = 128;
    public const MINIMUM_PHP_VERSION = '8.1.29';

    // Database
    /** @var string $dbtype */
    public $dbtype;
    /** @var string $dblocation */
    public $dblocation = 'localhost';
    /** @var string $dbport */
    public $dbport;
    /** @var  string $dbname */
    public $dbname;

    /** @var  boolean $useDbName */
    public $useDbName = true;

    /** @var  string $dbuser */
    public $dbuser;
    /** @var string $dbpwd  */
    public $dbpwd;
    /** @var string $dbprefix */
    public $dbprefix = 'lime_';
    /** @var string $dbengine Database Engine type if DB type is MySQL */
    public $dbengine;

    /** @var array $db_names */
    public $db_names = array(
        self::DB_TYPE_MYSQL => 'MySQL',
        self::DB_TYPE_MYSQLI => 'MySQL (newer driver)',
        self::DB_TYPE_SQLSRV => 'Microsoft SQL Server (sqlsrv)',
        self::DB_TYPE_MSSQL => 'Microsoft SQL Server (mssql)',
        self::DB_TYPE_DBLIB => 'Microsoft SQL Server (dblib)',
        self::DB_TYPE_PGSQL => 'PostgreSQL',
    );


    // Optional
    /** @var string $adminLoginPwd */
    public $adminLoginPwd = 'password';
    /** @var string $confirmPwd */
    public $confirmPwd = 'password';
    /** @var string $adminLoginName */
    public $adminLoginName = 'admin';
    /** @var string $adminName */
    public $adminName = 'Administrator';
    /** @var string $adminEmail */
    public $adminEmail = 'your-email@example.net';
    /** @var string $siteName */
    public $siteName = 'LimeSurvey';
    /** @var string $surveylang */
    public $surveylang = 'en';

    /** @var DbConnection */
    public $db;

    /** @var bool $tablesExist */
    public $tablesExist = false;

    /** @var bool dbExists */
    public $dbExists = false;

    /** @var bool */
    public $isPhpMbStringPresent = false;

    /** @var bool */
    public $isPhpFileInfoPresent = false;

    /** @var bool */
    public $isPhpZlibPresent = false;

    /** @var bool */
    public $isPhpJsonPresent = false;

    /** @var bool */
    public $isMemoryLimitOK = false;

    /** @var bool */
    public $isPhpGdPresent = false;

    /** @var bool */
    public $phpGdHasJpegSupport = false;

    /** @var bool */
    public $phpGdHasFreeTypeSupport = false;

    /** @var bool */
    public $isPhpLdapPresent = false;

    /** @var bool */
    public $isPhpZipPresent = false;

    /** @var bool */
    public $isPhpImapPresent = false;

    /** @var bool */
    public $isPhpVersionOK = false;

    /** @var bool */
    public $isSodiumPresent = false;

    /** @var bool */
    public $isCollatorPresent = false;

    /** @var bool */
    public $isConfigPresent = false;


    /**
     * InstallerConfigForm constructor.
     * @param string $scenario
     */
    public function __construct($scenario = 'database')
    {
        parent::__construct($scenario);
        $this->setInitialEngine();
        $this->checkStatus();
    }


    /** @inheritdoc */
    public function rules()
    {
        return array(
            // Database
            array('dbname', 'match', 'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/'), // Check that database name is a single word with options underscores not starting with a number
            array('dbtype, dblocation, dbname, dbuser', 'required', 'on' => 'database'),
            array('dbpwd, dbprefix', 'safe', 'on' => 'database'),
            array('dbtype', 'in', 'range' => array_keys($this->supportedDbTypes), 'on' => 'database'),
            array('dbengine', 'validateDBEngine', 'on' => 'database'),
            array('dbengine', 'in', 'range' => array_keys($this->dbEngines), 'on' => 'database'),
            //Optional
            array('adminLoginName, adminLoginPwd, confirmPwd, adminEmail', 'required', 'on' => 'optional', 'message' => gT('Either admin login name, password or email is empty')),
            array('adminLoginName, adminName, siteName, confirmPwd', 'safe', 'on' => 'optional'),
            array('adminEmail', 'email', 'on' => 'optional'),
            array('surveylang', 'in', 'range' => array_keys(getLanguageData(true, Yii::app()->session['installerLang'])), 'on' => 'optional'),
            array('adminLoginPwd', 'compare', 'compareAttribute' => 'confirmPwd', 'message' => gT('Passwords do not match!'), 'strict' => true, 'on' => 'optional'),
        );
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return array(
            'dbtype' => gT('Database type'),
            'dblocation' => gT('Database location'),
            'dbname' => gT('Database name'),
            'dbuser' => gT('Database user'),
            'dbpwd' => gT('Database password'),
            'dbprefix' => gT('Table prefix'),
            'dbengine' => gT('MySQL database engine type'),
        );
    }

    public function attributeHints()
    {
        return [
            'dbtype' => gT("The type of your database management system"),
            'dblocation' => gT('Set this to the IP/net location of your database server. In most cases "localhost" will work. You can force Unix socket with socket path.') . ' ' . gT('If your database is using a custom port, attach it using a colon. Example: db.host.com:5431'),
            'dbname' => gT("If the database does not yet exist it will be created (make sure your database user has the necessary permissions). In contrast, if there are existing LimeSurvey tables in that database they will be upgraded automatically after installation."),
            'dbuser' => gT('Your database server user name. In most cases "root" will work.'),
            'dbpwd' => gT("Your database server password."),
            'dbprefix' => gT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.'),
        ];
    }

    public function validate($attributes = null, $clearErrors = true)
    {
        $this->dbConnect();
        return parent::validate($attributes, $clearErrors);
    }

    private function checkStatus()
    {
        $this->isPhpMbStringPresent = extension_loaded('mbstring');
        $this->isPhpFileInfoPresent = extension_loaded('fileinfo');
        $this->isPhpZlibPresent =  extension_loaded('zlib');
        $this->isPhpGdPresent =  extension_loaded('gd');
        $this->isPhpJsonPresent = function_exists('json_encode');
        $this->isMemoryLimitOK = $this->checkMemoryLimit();
        $this->isPhpLdapPresent = extension_loaded('ldap');
        $this->isPhpImapPresent = extension_loaded('imap');
        $this->isPhpZipPresent = extension_loaded('zip');
        $this->isSodiumPresent = function_exists('sodium_crypto_sign_open');
        $this->isCollatorPresent = class_exists('Collator');

        if (function_exists('gd_info')) {
            $gdInfo = gd_info();
            $this->phpGdHasJpegSupport = !empty($gdInfo['JPEG Support']);
            $this->phpGdHasFreeTypeSupport = !empty($gdInfo['FreeType Support']);
            $this->isPhpGdPresent = true;
        }
        $this->isPhpVersionOK = version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=');
    }

    /**
     * Chek whether system meets minimum requirements
     * @return bool
     */
    public function getHasMinimumRequirements()
    {

        if (
            !$this->isMemoryLimitOK
            or !$this->isUploadDirWriteable
            or !$this->isTmpDirWriteable
            or !$this->isConfigDirWriteable
            or !$this->isPhpVersionOK
            or !$this->isPhpMbStringPresent
            or !$this->isPhpFileInfoPresent
            or !$this->isPhpZlibPresent
            or !$this->isPhpGdPresent
            or !$this->isPhpZipPresent
            or !$this->isPhpJsonPresent
        ) {
            return false;
        }

        if (count($this->supportedDbTypes) == 0) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkMemoryLimit()
    {
        if ($this->memoryLimit >= self::MINIMUM_MEMORY_LIMIT) {
            return true;
        }
        if (ini_get('memory_limit') == -1) {
            return true;
        }
        return false;
    }

    /**
     * Memory limit in MB
     * @return float|int
     */
    public function getMemoryLimit()
    {
        return convertPHPSizeToBytes(ini_get('memory_limit')) / 1024 / 1024;
    }

    public function validateDBEngine($attribute)
    {
        if (
            $this->isMysql
            && ($this->dbengine === null or !in_array($this->dbengine, array_keys($this->dbEngines)))
        ) {
            $this->addError($attribute, gT('The database engine type must be set for MySQL'));
        }

        if ($this->isMysql && $this->dbengine === self::ENGINE_TYPE_INNODB) {
            $version = $this->getMySqlConfigValue('version');
            if (is_null($version)) {
                $this->addError($attribute, gT('Could not determine the database engine version. Please check your credentials.'));
                return;
            }
            $mariadb = preg_match('/MariaDB/i', $version);
            $match = preg_match('/^\d+\.\d+\.\d+/', $version, $matchedVersion);
            if (
                !$match
                    || (!$mariadb && version_compare($matchedVersion[0], '8.0.0') < 0)
                    || ($mariadb && version_compare($matchedVersion[0], '10.2.2') < 0)
            ) {
                // Only for older db-engine
                if (!$this->isInnoDbLargeFilePrefixEnabled()) {
                    $this->addError($attribute, gT('You need to enable large_file_prefix setting in your database configuration in order to use InnoDB engine for LimeSurvey!'));
                }
                if (!$this->isInnoDbBarracudaFileFormat()) {
                    $this->addError($attribute, gT('Your database configuration needs to have innodb_file_format and innodb_file_format_max set to use the Barracuda format in order to use InnoDB engine for LimeSurvey!'));
                }
            }
        }
    }

    /**
     * Get the array of supported DB type
     * @return array
     */
    public function getSupportedDbTypes()
    {
        $result = array();
        foreach (CDbConnection::getAvailableDrivers() as $driver) {
            if (isset($this->db_names[$driver])) {
                $result[$driver] = $this->db_names[$driver];
            }
        }
        asort($result);
        return $result;
    }

    private function setInitialEngine()
    {
        if (isset($this->supportedDbTypes[self::DB_TYPE_MYSQL])) {
            if (getenv('DBENGINE')) {
                $this->dbengine = getenv('DBENGINE');
            } else {
                $this->dbengine = self::ENGINE_TYPE_MYISAM;
            }
        }
    }

    /**
     * @return bool
     */
    public function getIsConfigDirWriteable()
    {
        return is_writable(Yii::app()->getConfig('rootdir') . '/application/config');
    }

    /**
     * @return bool
     */
    public function getIsTmpDirWriteable()
    {
        return self::isWritableRecursive(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR);
    }

    /**
     * @return bool
     */
    public function getIsUploadDirWriteable()
    {
        return self::isWritableRecursive(Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR);
    }


    /**
     * @param string $sDirectory
     * @return boolean
     */
    public static function isWritableRecursive($sDirectory)
    {
        $sFolder = opendir($sDirectory);
        if ($sFolder === false) {
            return false; // Dir does not exist
        }
        while ($sFile = readdir($sFolder)) {
            if (
                $sFile != '.' && $sFile != '..' &&
                (!is_writable($sDirectory . DIRECTORY_SEPARATOR . $sFile) ||
                    (is_dir($sDirectory . DIRECTORY_SEPARATOR . $sFile) && !self::isWritableRecursive($sDirectory . DIRECTORY_SEPARATOR . $sFile)))
            ) {
                closedir($sFolder);
                return false;
            }
        }
        closedir($sFolder);
        return true;
    }

    public function isInnoDbLargeFilePrefixEnabled()
    {
        return $this->getMySqlConfigValue('innodb_large_prefix') == '1';
    }

    /**
     * @param $itemName
     * @return string
     */
    private function getMySqlConfigValue($itemName)
    {
        $item = "@@" . $itemName;
        try {
            $query = "SELECT " . $item . ";";
            $result = $this->db->createCommand($query)->queryRow();
            return $result[$item] ?? null;
        } catch (\Exception $e) {
            // ignore
        }
        return null;
    }

    /**
     * @return bool
     */
    private function isInnoDbBarracudaFileFormat()
    {
        $check1 = $this->getMySqlConfigValue('innodb_file_format') == 'Barracuda' || $this->getMySqlConfigValue('innodb_file_format') == null;
        $check2 = $this->getMySqlConfigValue('innodb_file_format_max') == 'Barracuda' || $this->getMySqlConfigValue('innodb_file_format_max') == null;
        return $check1 && $check2;
    }

    /**
     * @return array
     */
    public function getDbEngines()
    {
        return [
            self::ENGINE_TYPE_MYISAM => 'MyISAM',
            self::ENGINE_TYPE_INNODB => 'InnoDB'
        ];
    }


    /**
     * Connect to the database
     * @return bool
     */
    public function dbConnect()
    {

        $sDsn = $this->getDsn();

        if (!$this->dbTest()) {
            // the db does not exist yet
            $this->useDbName = false;
            $sDsn = $this->getDsn();
            $this->dbExists = false;
        } else {
            $this->dbExists = true;
            $this->useDbName = true;
        }

        try {
            $this->db = new DbConnection($sDsn, $this->dbuser, $this->dbpwd);
            if ($this->dbtype != self::DB_TYPE_SQLSRV && $this->dbtype != self::DB_TYPE_DBLIB) {
                $this->db->emulatePrepare = true;
            }
            if (in_array($this->dbtype, [ self::DB_TYPE_SQLSRV, self::DB_TYPE_DBLIB, self::DB_TYPE_MSSQL])) {
                $this->db->initSQLs = ['SET DATEFORMAT ymd;', 'SET QUOTED_IDENTIFIER ON;'];
            }

            $this->db->tablePrefix = $this->dbprefix;
            $this->setMySQLDefaultEngine($this->dbengine);
        } catch (\Exception $e) {
            $this->addError('dblocation', gT('Try again! Connection with database failed.'));
            $this->addError('dblocation', gT('Reason:') . ' ' . $e->getMessage());
        }
        return true;
    }

    /**
     * @return bool if connection is done
     */
    private function dbTest()
    {
        $sDsn = $this->getDsn();
        try {
            new PDO($sDsn, $this->dbuser, $this->dbpwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (Exception $e) {
            return false;
        }
        return true;
    }


    /**
     * @param string $dbEngine
     * @throws CDbException
     */
    private function setMySQLDefaultEngine($dbEngine)
    {
        if (!empty($this->db) && $this->db->driverName === self::DB_TYPE_MYSQL) {
            $this->db
                ->createCommand(new CDbExpression(sprintf('SET default_storage_engine=%s;', $dbEngine)))
                ->execute();
        }
    }

    /**
     * Get the dsn for the database connection
     *
     * @return string
     * @throws Exception
     */
    public function getDsn()
    {
        switch ($this->dbtype) {
            case self::DB_TYPE_MYSQL:
            case self::DB_TYPE_MYSQLI:
                $sDSN = $this->getMysqlDsn();
                break;
            case self::DB_TYPE_PGSQL:
                $sDSN = $this->getPgsqlDsn();
                break;
            case self::DB_TYPE_DBLIB:
                $sDSN = $this->dbtype . ":host={$this->dblocation};dbname={$this->dbname}";
                break;
            case self::DB_TYPE_MSSQL:
            case self::DB_TYPE_SQLSRV:
                $sDSN = $this->getMssqlDsn();
                break;
            default:
                throw new Exception(sprintf('Unknown database type "%s".', $this->dbtype));
        }
        return $sDSN;
    }

    /**
     * @return string
     */
    private function getMysqlDsn()
    {

        $port = $this->getDbPort();

        // MySQL allow unix_socket for database location, then test if $sDatabaseLocation start with "/"
        if (substr($this->dblocation, 0, 1) === "/") {
            $sDSN = "mysql:unix_socket={$this->dblocation};";
        } else {
            $sDSN = "mysql:host={$this->dblocation};port={$port};";
        }

        if ($this->useDbName) {
            $sDSN .= "dbname={$this->dbname};";
        }
        return $sDSN;
    }


    /**
     * @return string
     */
    private function getPgsqlDsn()
    {
        $port = $this->getDbPort();
        if (empty($this->dbpwd)) {
            // If there's no password, we need to write password=""; instead of password=;,
            // or PostgreSQL's libpq will consider the DSN string part after "password="
            // (including the ";" and the potential dbname) as part of the password definition.
            $this->dbpwd = '""';
        }
        $sDSN = "pgsql:host={$this->dblocation};port={$port};user={$this->dbuser};password={$this->dbpwd};";
        if ($this->useDbName) {
            $sDSN .= "dbname={$this->dbname};";
        }
        return $sDSN;
    }

    /**
     * @return string
     */
    private function getMssqlDsn()
    {
        $port = $this->getDbPort();
        $sDatabaseLocation = $this->dblocation;
        if ($port != '') {
            $sDatabaseLocation = $this->dblocation . ',' . $port;
        }
        $sDSN = $this->dbtype . ":Server={$sDatabaseLocation};";
        if ($this->useDbName) {
            $sDSN .= "Database={$this->dbname}";
        }
        return $sDSN;
    }

    /**
     * Get the default port if database port is not set
     * @return string
     */
    public function getDbPort()
    {
        if (strpos($this->dblocation, ':') !== false) {
            $pieces = explode(':', $this->dblocation, 2);
            if (isset($pieces[1]) && is_numeric($pieces[1])) {
                $this->dblocation = str_replace(":" . $pieces[1], "", $this->dblocation);
                $this->dbport = $pieces[1];
            }
        }
        return $this->dbport ?? $this->getDbDefaultPort();
    }

    /**
     * @return string
     */
    private function getDbDefaultPort()
    {
        $sDatabasePort = '';
        if ($this->isMysql) {
            $sDatabasePort = '3306';
        }
        if ($this->dbtype === self::DB_TYPE_PGSQL) {
            $sDatabasePort = '5432';
        }
        return $sDatabasePort;
    }

    /**
     * @return bool
     */
    public function getIsMysql()
    {
        return in_array($this->dbtype, [self::DB_TYPE_MYSQL, self::DB_TYPE_MYSQLI]);
    }

    /**
     * @return bool
     */
    public function getIsMSSql()
    {
        return in_array($this->dbtype, [self::DB_TYPE_MSSQL, self::DB_TYPE_DBLIB, self::DB_TYPE_SQLSRV]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function createDatabase()
    {
        $query = $this->createDbQuery();
        try {
            $this->db->createCommand($query)->execute();
        } catch (Exception $e) {
            throw new Exception(
                'Could not create database: ' . $query . '. Please check your credentials.'
            );
        }

        $this->useDbName = true;
        // reconnect to set database name & status
        $this->dbConnect();
    }


    /**
     * @return string
     */
    private function createDbQuery()
    {
        $query = "CREATE DATABASE {$this->dbname}";
        if ($this->isMysql) {
            $query = "CREATE DATABASE `{$this->dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        } elseif ($this->isMSSql) {
            $query = "CREATE DATABASE [{$this->dbname}];";
        }
        if ($this->dbtype === self::DB_TYPE_PGSQL) {
            $query = "CREATE DATABASE \"{$this->dbname}\" ENCODING 'UTF8'";
        }
        return $query;
    }

    /**
     * Function that actually modify the database.
     * @return string[]|boolean True if everything was okay, otherwise error message.
     */
    public function setupTables()
    {
        if (empty($this->dbname)) {
            $this->dbname = $this->getDataBaseName();
        }

        try {
            switch ($this->dbtype) {
                case self::DB_TYPE_MYSQL:
                case self::DB_TYPE_MYSQLI:
                    $this->db->createCommand("ALTER DATABASE " . $this->db->quoteTableName($this->dbname) . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
                        ->execute();
                    break;
            }
        } catch (Exception $e) {
            return array($e->getMessage());
        }
        $fileName = dirname(APPPATH) . '/installer/create-database.php';
        require_once($fileName);
        try {
            populateDatabase($this->db);
        } catch (Exception $e) {
            return array($e->getMessage());
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getDataBaseName()
    {
        if ($this->db) {
            preg_match("/dbname=([^;]*)/", (string) $this->db->connectionString, $matches);
            $databaseName = $matches[1];
            return $databaseName;
        }
        return null;
    }
}
