<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class InstallerConfigForm extends CFormModel
{
	// Database
	public $dbtype;
    public $dblocation = 'localhost';
    public $dbname;
	public $dbuser;
	public $dbpwd;
	public $dbprefix = 'lime_';

	public $supported_db_types = array();
	public $db_names = array(
		'mysql' => 'MySQL',
		'mysqli' => 'MySQL (newer driver)',
        'sqlsrv' => 'Microsoft SQL Server (sqlsrv)',
		'mssql' => 'Microsoft SQL Server (mssql)',
		'dblib' => 'Microsoft SQL Server (dblib)',
		'pgsql' => 'PostgreSQL',
	);

	// Optional
	public $adminLoginPwd = 'password';
	public $confirmPwd = 'password';
	public $adminLoginName = 'admin';
	public $adminName = 'Administrator';
	public $adminEmail = 'your-email@example.net';
	public $siteName = 'LimeSurvey';
	public $surveylang = 'en';

	public function __construct($scenario = 'database') {
        $drivers=array();
        if (extension_loaded('pdo'))
        {
            $drivers = DbConnection::getAvailableDrivers();
        }
		foreach($drivers as $driver) {
			if (isset($this->db_names[$driver]))
				$this->supported_db_types[$driver] = $this->db_names[$driver];
		}

		asort($this->supported_db_types);

		parent::__construct();

		// Default is database
		$this->setScenario($scenario);
	}

    public function rules()
    {
        App()->loadHelper('surveytranslator');
        return [
			// Database
            array('dbtype, dblocation, dbname, dbuser', 'required', 'on' => 'database'),
			array('dbpwd, dbprefix', 'safe', 'on' => 'database'),
			array('dbtype', 'in', 'range' => array_keys($this->supported_db_types), 'on' => 'database'),
			//Optional
			array('adminLoginName, adminName, siteName, confirmPwd', 'safe', 'on' => 'optional'),
			array('adminEmail', 'email', 'on' => 'optional'),
			array('surveylang', 'in', 'range' => array_keys(getLanguageData(true, Yii::app()->session['installerLang'])), 'on' => 'optional'),
            array('adminLoginPwd', 'compare', 'compareAttribute' => 'confirmPwd', 'message' => gT('Passwords do not match!'), 'strict' => true, 'on' => 'optional'),
            
            ['dsn', 'validateDsn', 'on' => 'database'], // Validate connection without database.
            ['dbname', 'validateDatabase', 'on' => 'database']
        ];
    }
    
    public function validateDatabase($attribute) {
        try {
            new PDO($this->dsn, $this->dbuser, $this->dbpwd);
        } catch (PDOException $e) {
            // Connection to database failed.
            $error = gT('Database does not exist:') . $e->getMessage();
            $this->addError('dbname', $error);
        }
    }
    
    public function validateDsn($attribute) {
        try {
            $pdo = new PDO($this->getDsn(true), $this->dbuser, $this->dbpwd);
        } catch (PDOException $e) {
            // Connection to database failed.
           $error = gT('Try again! Connection with database failed. Reason: ');
           // Remove passwords.
           $error .= strtr($e->getMessage(), [$this->dbpwd => '***']);
           $this->addError($attribute, $error);
           return;
        }
        
        if (in_array($pdo->getAttribute(PDO::ATTR_DRIVER_NAME), ['mysql', 'mysqli']) && version_compare($pdo->getAttribute(PDO::ATTR_SERVER_VERSION), '4.1','<')) {
            $this->addError('dblocation', gT('You need at least MySQL version 4.1 to run LimeSurvey. Your version: ') . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
        }
    }

    /**
     * Get the dsn for the database connection
     *
     * @param string $sDatabaseType
     * @param string $sDatabasePort
     */
    public function getDsn($noDatabase = false) 
    {
        $parts = [];
        switch ($this->dbtype) {
            case 'mysql':
            case 'mysqli':
                // MySQL allow unix_socket for database location, then test if $sDatabaseLocation start with "/"
                $parts['dbname'] = $this->dbname;
                $parts['port'] = $this->port;
                
                if(substr($this->dblocation,0,1)=="/")
                    $parts['unix_socket'] = $this->dblocation;
                else
                    $parts['host'] = $this->host;
                $driver = 'mysql';
                break;
            case 'pgsql':
                $driver = 'pgsql';
                $parts['dbname'] = $this->dbname;
                $parts['port'] = $this->port;
                break;
            case 'dblib' :
                $driver = 'dblib';
                $parts['dbname'] = $this->dbname;
                $parts['host'] = $this->host;
                break;
            case 'mssql' :
            case 'sqlsrv':
                $parts['Server'] = implode(',', [
                    $this->host,
                    $this->port
                ]);
                $parts['Database'] = $this->dbname;
                break;
            default:
                throw new Exception(sprintf('Unknown database type "%s".', $this->dbtype));
        }
        $result = $driver . ":";
        if ($noDatabase) {
            unset($parts['Database']);
            unset($parts['dbname']);
        }
        foreach($parts as $key => $value) {
            if (!empty($value)) {
                $result .= "$key=$value;";
            }
        }
        return $result;
    }
	public function attributeLabels()
	{
		return [
			'dbtype' => 'Database type',
			'dblocation' => 'Database location',
			'dbname' => 'Database name',
			'dbuser' => 'Database user',
			'dbpwd' => 'Database password',
			'dbprefix' => 'Table prefix',
		];
	}
    
    public function getHost() {
        return explode(':', $this->dblocation, 2)[0];
    }
    
    public function getPort() {
        $parts = explode(':', $this->dblocation, 2);
        return count($parts) > 1 ? $parts[1] : null;
    }
    
    public function createDatabase() 
    {
        $conn = new DbConnection($this->getDsn(true), $this->dbuser, $this->dbpwd);
        $result = $conn->schema->createDatabase($this->dbname);
        if (!$result) {
            $this->addError('dbname', gT('Database does not exist and could not be created.'));
        }
        return $result;
    }
}
?>
