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
 */
class InstallerConfigForm extends CFormModel
{
    const ENGINE_TYPE_MYISAM = 'MyISAM';
    const ENGINE_TYPE_INNODB = 'InnoDB';

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
        $this->supported_db_types = $this->db_names;

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
            'dbtype' => Yii::t('app','Database type'),
            'dblocation' => Yii::t('app','Database location'),
            'dbname' => Yii::t('app','Database name'),
            'dbuser' => Yii::t('app','Database user'),
            'dbpwd' => Yii::t('app','Database password'),
            'dbprefix' => Yii::t('app','Table prefix'),
            'dbengine' => Yii::t('app','MySQL databse engine type'),
        );
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
}
