<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *	Files Purpose: lots of common functions
*/

/**
 * Class InstallerConfigForm
 * @property array $dbEngines the MySQL database engines as [value=>'label']
 * @property boolean $isMysql whether the db type is mysql or mysqli
 * @property boolean $isMSSql whether the db type is one of MS Sql types
 */
class InstallerConfigForm extends LSCFormModel
{
    const ENGINE_TYPE_MYISAM = 'MYISAM';
    const ENGINE_TYPE_INNODB = 'INNODB';

    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_MYSQLI = 'mysqli';
    const DB_TYPE_SQLSRV = 'sqlsrv';
    const DB_TYPE_MSSQL = 'mssql';
    const DB_TYPE_DBLIB = 'dblib';
    const DB_TYPE_PGSQL = 'pgsql';

    // Database
    /** @var string $dbtype */
    public $dbtype;
    /** @var string $dblocation */
    public $dblocation = 'localhost';
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
    /** @var array $supported_db_types */
    public $supported_db_types = array();
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


    /**
     * InstallerConfigForm constructor.
     * @param string $scenario
     */
    public function __construct($scenario = 'database')
    {
        $drivers = array();
        if (extension_loaded('pdo')) {
            $drivers = CDbConnection::getAvailableDrivers();
        }
        foreach ($drivers as $driver) {
            if (isset($this->db_names[$driver])) {
                $this->supported_db_types[$driver] = $this->db_names[$driver];
            }
        }

        if (isset($this->supported_db_types[self::DB_TYPE_MYSQL])) {
            if (getenv('DBENGINE')) {
                $this->dbengine = getenv('DBENGINE');
            } else {
                $this->dbengine = self::ENGINE_TYPE_MYISAM;
            }
        }
        asort($this->supported_db_types);

        parent::__construct();

        // Default is database
        $this->setScenario($scenario);
    }


    /** @inheritdoc */
    public function rules()
    {
        return array(
            // Database
            array('dbname', 'match', 'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/'), // Check that database name is a single word with options underscores not starting with a number
            array('dbtype, dblocation, dbname, dbuser', 'required', 'on' => 'database'),
            array('dbpwd, dbprefix', 'safe', 'on' => 'database'),
            array('dbtype', 'in', 'range' => array_keys($this->supported_db_types), 'on' => 'database'),
            array('dbengine', 'validateDBEngine', 'on' => 'database'),
            array('dbengine', 'in', 'range' => array_keys(self::getDbEngines()), 'on' => 'database'),
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
            'dbengine' => gT('MySQL databse engine type'),
        );
    }

    public function attributeHints() {
        return [
            'dbtype' => gT("The type of your database management system"),
            'dblocation' => gT('Set this to the IP/net location of your database server. In most cases "localhost" will work. You can force Unix socket with complete socket path.').' '.gT('If your database is using a custom port attach it using a colon. Example: db.host.com:5431'),
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

    public function checkStatus() {


    }

    public function validateDBEngine($attribute,$params)
    {
        if($this->isMysql
            && ($this->dbengine === null or !in_array($this->dbengine,array_keys(self::getDbEngines()))) ){
            $this->addError($attribute, Yii::t('app','The database engine type must be set for MySQL'));
        }

        if ($this->isMysql && $this->dbengine === self::ENGINE_TYPE_INNODB) {
            if (!$this->isInnoDbLargeFilePrefixEnabled()) {
                $this->addError($attribute, Yii::t('app','You need to enable large_file_prefix setting in your database configuration in order to use InooDb engine for LimeSurvey!'));
            }
            if (!$this->isInnoDbBarracudaFileFormat()) {
                $this->addError($attribute, Yii::t('app','Your database configuration needs to have innodb_file_format and innodb_file_format_max set to use the Barracuda format in order to use InooDb engine for LimeSurvey!'));
            }
        }
    }

    public function isInnoDbLargeFilePrefixEnabled(){
        return $this->getMySqlConfigValue('innodb_large_prefix') == '1';
    }

    private function getMySqlConfigValue($itemName) {
        $item = "@@".$itemName;
        try {
            $query = "SELECT ".$item.";";
            $result = $this->db->createCommand($query)->queryRow();
            return isset($result[$item]) ? $result[$item] : null;
        } catch (\Exception $e) {
            // ignore
        }
        return null;
    }

    private function isInnoDbBarracudaFileFormat(){
        $check1 = $this->getMySqlConfigValue('innodb_file_format') == 'Barracuda';
        $check2 = $this->getMySqlConfigValue('innodb_file_format_max') == 'Barracuda';
        return $check1 && $check2;
    }

    /**
     * @return array
     */
    public function getDbEngines(){
        return [
            self::ENGINE_TYPE_MYISAM => Yii::t('app','MyISAM'),
            self::ENGINE_TYPE_INNODB => Yii::t('app','InnoDB'),
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
        }
        try {
            $this->db = new DbConnection($sDsn, $this->dbuser, $this->dbpwd);
            if ($this->dbtype != self::DB_TYPE_SQLSRV && $this->dbtype != self::DB_TYPE_DBLIB) {
                $this->db->emulatePrepare = true;
            }
            $this->db->active = true;
            $this->db->tablePrefix = $this->dbprefix;
            $this->setMySQLDefaultEngine($this->dbengine);

        } catch (\Exception $e) {
            $this->addError('dblocation', gT('Try again! Connection with database failed.'));
            $this->addError('dblocation', gT('Reason:').' '.$e->getMessage());
        }
        if ($this->useDbName){
            $this->dbExists = true;
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


    private function setMySQLDefaultEngine($dbEngine){
        if(!empty($this->db) && $this->db->driverName === self::DB_TYPE_MYSQL){
            $this->connection
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
    public function getDsn($dbName = null)
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
                $sDSN = $this->dbtype.":host={$this->dblocation};dbname={$this->dbname}";
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
    private function getMysqlDsn(){

        $port = $this->getDbPort();
        if (!$this->useDbName) {
            $dbName = '';
        } else {
            $dbName = $this->dbname;
        }

        // MySQL allow unix_socket for database location, then test if $sDatabaseLocation start with "/"
        if (substr($this->dblocation, 0, 1) == "/") {
            $sDSN = "mysql:unix_socket={$this->dblocation};dbname={$dbName};";
        } else {
            $sDSN = "mysql:host={$this->dblocation};port={$port};dbname={$dbName};";
        }
        return $sDSN;
    }


    /**
     * @return string
     */
    private function getPgsqlDsn() {
        $port = $this->getDbPort();
        if (!$this->useDbName) {
            $dbName = '';
        } else {
            $dbName = $this->dbname;
        }
        if (empty($this->dbpwd)) {
            // If there's no password, we need to write password=""; instead of password=;,
            // or PostgreSQL's libpq will consider the DSN string part after "password="
            // (including the ";" and the potential dbname) as part of the password definition.
            $this->dbpwd = '""';
        }
        $sDSN = "pgsql:host={$this->dblocation};port={$port};user={$this->dbuser};password={$this->dbpwd};";
        if ($this->dbname != '') {
            $sDSN .= "dbname={$dbName};";
        }
        return $sDSN;
    }

    /**
     * @return string
     */
    private function getMssqlDsn(){
        $port = $this->getDbPort();
        if (!$this->useDbName) {
            $dbName = '';
        } else {
            $dbName = $this->dbname;
        }
        if ($port != '') {
            $sDatabaseLocation = $this->dblocation.','.$port;
        }
        $sDSN = $this->dbtype.":Server={$sDatabaseLocation};Database={$dbName}";
        return $sDSN;
    }

    /**
     * Get the default port if database port is not set
     * @return string
     */
    public function getDbPort()
    {
        $sDatabasePort = '';
        if (strpos($this->dblocation, ':') !== false) {
            list($sDatabaseLocation, $sDatabasePort) = explode(':', $this->dblocation, 2);
            if (is_numeric($sDatabasePort)) {
                return $sDatabasePort;
            }
        }

        switch ($this->dbtype) {
            case self::DB_TYPE_MYSQL:
            case self::DB_TYPE_MYSQLI:
                $sDatabasePort = '3306';
                break;
            case self::DB_TYPE_PGSQL:
                $sDatabasePort = '5432';
                break;
            case self::DB_TYPE_DBLIB:
            case self::DB_TYPE_MSSQL:
            case self::DB_TYPE_SQLSRV:
            default:
                $sDatabasePort = '';
        }

        return $sDatabasePort;
    }

    /**
     * @return bool
     */
    public function getIsMysql(){
        return in_array($this->dbtype,[self::DB_TYPE_MYSQL,self::DB_TYPE_MYSQLI]);
    }

    /**
     * @return bool
     */
    public function getIsMSSql(){
        return in_array($this->dbtype,[self::DB_TYPE_MSSQL, self::DB_TYPE_DBLIB, self::DB_TYPE_SQLSRV]);
    }

    /**
     * @return bool
     */
    public function createDatabase() {
        $bCreateDB = true; // We are thinking positive
        switch ($this->dbtype) {
            case self::DB_TYPE_MYSQL:
            case self::DB_TYPE_MYSQLI:
                try {
                    $this->db->createCommand("CREATE DATABASE `{$this->dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")->execute();
                } catch (Exception $e) {
                    $bCreateDB = false;
                }
                break;
            case 'dblib':
            case 'mssql':
            case 'odbc':
                try {
                    $this->db->createCommand("CREATE DATABASE [{$this->dbname}];")->execute();
                } catch (Exception $e) {
                    $bCreateDB = false;
                }
                break;
            case self::DB_TYPE_PGSQL:
                try {
                    $this->db->createCommand("CREATE DATABASE \"{$this->dbname}\" ENCODING 'UTF8'")->execute();
                } catch (Exception $e) {
                    $bCreateDB = false;
                }
                break;
            default:
                try {
                    $this->db->createCommand("CREATE DATABASE {$this->dbname}")->execute();
                } catch (Exception $e) {
                    $bCreateDB = false;
                }
                break;
        }
        return $bCreateDB;

    }

    /**
     * Function that actually modify the database.
     * @param string $sFileName
     * @return string|boolean True if everything was okay, otherwise error message.
     */
    public function setupTables($sFileName)
    {
        try {
            switch ($this->dbtype) {
                case self::DB_TYPE_MYSQL:
                case self::DB_TYPE_MYSQLI:
                    $this->db->createCommand("ALTER DATABASE ".$this->db->quoteTableName($this->dbname)." DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
                        ->execute();
                    break;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        require_once($sFileName);
        createDatabase($this->connection);
    }


}
