<?php
namespace ls\models\installer;

use DbConnection;
use Yii;
use PDO;
use PDOException;

class InstallerConfigForm extends \CFormModel
{
	// Database
	public $dbtype;
    public $dblocation = 'localhost';
    public $dbname;
	public $dbuser;
	public $dbpwd;
	public $dbprefix = 'lime_';

	public $supported_db_types = [];
	public $db_names = [
		'mysql' => 'MySQL',
		'mysqli' => 'MySQL (newer driver)',
        'sqlsrv' => 'Microsoft SQL Server (sqlsrv)',
		'mssql' => 'Microsoft SQL Server (mssql)',
		'dblib' => 'Microsoft SQL Server (dblib)',
		'pgsql' => 'PostgreSQL',
    ];

	// Optional
	public $adminLoginPwd = 'password';
	public $confirmPwd = 'password';
	public $adminLoginName = 'admin';
	public $adminName = 'Administrator';
	public $adminEmail = 'your-email@example.net';
	public $siteName = 'LimeSurvey';
	public $surveylang = 'en';

    /**
     * @var DbConnection
     */
    protected $_dbConnection;
	public function __construct($scenario = 'database') {
        $drivers= [];
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

        return [
			// Database
            ['dbtype, dblocation, dbname, dbuser', 'required', 'on' => 'database'],
			['dbpwd, dbprefix', 'safe', 'on' => 'database'],
			['dbtype', 'in', 'range' => array_keys($this->supported_db_types), 'on' => 'database'],
			//Optional
			['adminLoginName, adminName, siteName, confirmPwd', 'safe', 'on' => 'optional'],
			['adminEmail', 'email', 'on' => 'optional'],
			['surveylang', 'in', 'range' => array_keys(\ls\helpers\SurveyTranslator::getLanguageData(true, Yii::app()->session['installerLang'])), 'on' => 'optional'],
            ['adminLoginPwd', 'compare', 'compareAttribute' => 'confirmPwd', 'message' => gT('Passwords do not match!'), 'strict' => true, 'on' => 'optional'],
            
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
            $this->addError($attribute, "Dsn used: {$this->getDsn()}");
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
                $parts['host'] = $this->host;
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

        $result = $this->getDbConnection()->schema->createDatabase($this->dbname);
        if (!$result) {
            $this->addError('dbname', gT('Database does not exist and could not be created.'));
        }
        return $result;
    }

    public function isDatabaseEmpty()
    {
        /*
         * Check if database is filled by checking a subset of the tables.
         */

        $tables = array_flip($this->getDbConnection()->schema->tableNames);
        $requiredTables = [
            'answers',
            'quota',
            'surveys',
            'users'
        ];
        $result = true;
        foreach($requiredTables as $table) {
            $result = $result  && !isset($tables["{$this->dbprefix}$table"]);
        }
        return $result;

    }

    public function getDbConnection() {
        if (!isset($this->_dbConnection)) {
            $this->_dbConnection = new DbConnection($this->getDsn(), $this->dbuser, $this->dbpwd);
            $this->_dbConnection->tablePrefix = $this->dbprefix;

        }

        return $this->_dbConnection;
    }

    /**
     * Function to populate the database.
     * @return
     */
    public function populateDatabase()
    {
        $db = $this->getDbConnection();
        /* @todo Use Yii as it supports various db types and would better handle this process */
        switch ($db->driverName)
        {
            case 'mysqli':
            case 'mysql':
                $sql_file = 'mysql';
                break;
            case 'dblib':
            case 'sqlsrv':
            case 'mssql':
                $sql_file = 'mssql';
                break;
            case 'pgsql':
                $sql_file = 'pgsql';
                break;
            default:
                throw new Exception(sprintf('Unknown database type "%s".', $db->driverName));
        }
        $db->executeFile(Yii::getPathOfAlias('application') . "/installer/create-$sql_file.sql", $db->tablePrefix);
    }
}

