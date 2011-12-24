<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey (tm)
* Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* @author Shubham Sachdeva
*/

/**
* Installer
*
* @todo Output code belongs into view
* @todo Make it write the config.php file
*
* @package LimeSurvey
* @author Shubham Sachdeva
* @copyright 2011
* @version $Id$
* @access public
*/
class InstallerController extends CController {

	/**
	 * clang
	 */
	public $lang = null;

    /**
    * Database connection
    * @var CDbConnection
    */
    private $connection;

    /**
    * Sha256
    * @var Sha256
    */
    private $sha256;

	/**
	 * Checks for action specific authorization and then executes an action
	 *
	 * @access public
	 * @param string $action
	 * @return bool
	 */
	public function run($action = 'index')
	{
        self::_checkInstallation();
        self::_sessioncontrol();

		switch ($action) {

			case 'welcome':
				$this->stepWelcome();
				break;

			case 'license':
				$this->stepLicense();
				break;

			case 'viewlicense':
				$this->stepViewLicense();
				break;

			case 'precheck':
				$this->stepPreInstallationCheck();
				break;

			case 'database':
				$this->stepDatabaseConfiguration();
				break;

			case 'createdb':
				$this->stepCreateDb();
				break;

			case 'populatedb':
				$this->stepPopulateDb();
				break;

			case 'optional':
				$this->stepOptionalConfiguration();
				break;

			case 'index' :
			default :
				$this->redirect($this->createUrl('installer/welcome'));
				break;

		}
	}

    /**
    * Installer::_checkInstallation()
    *
    * Based on existance of 'sample_installer_file.txt' file, check if
    * installation should proceed further or not.
    * @return
    */
    function _checkInstallation()
    {
        if (file_exists(APPPATH . 'config/config.php'))
        {
			throw new CHttpException(500, 'Installation has been done already. Installer disabled.');
            exit();
        }
    }

	/**
	 * Load and set session vars
	 *
	 * @access protected
	 * @return void
	 */
	protected function _sessioncontrol()
	{
		if (!empty(Yii::app()->session['installerLang']))
			Yii::app()->session['installerLang'] = 'en';

		Yii::import('application.libraries.Limesurvey_lang');
		$this->lang = new Limesurvey_lang(array('langcode' => Yii::app()->session['installerLang']));
		Yii::app()->setLang($this->lang);
	}

    /**
    * welcome and language selection install step
    */
    private function stepWelcome()
    {
        $aData['clang'] = $clang = $this->lang;
        // $aData array contain all the information required by view.
        $aData['title'] = $clang->gT('Welcome');
        $aData['descp'] = $clang->gT('Welcome to the LimeSurvey installation wizard. This wizard will guide you through the installation, database setup and initial configuration of LimeSurvey.');
        $aData['classesForStep'] = array('on','off','off','off','off','off');
        $aData['progressValue'] = 0;

        if (!empty($_POST['installerLang']))
        {
            Yii::app()->session['installerLang'] = $_POST['installerLang'];
            $this->redirect($this->createUrl('installer/license'));
        }

        $this->loadHelper('surveytranslator');
        $this->render('/installer/welcome_view',$aData);
    }

    /**
    * Display license
    */
    private function stepLicense()
    {
        $aData['clang'] = $clang = $this->lang;
        // $aData array contain all the information required by view.
        $aData['title'] = $clang->gT('License');
        $aData['descp'] = $clang->gT('GNU General Public License:');
        $aData['classesForStep'] = array('off','on','off','off','off','off');
        $aData['progressValue']=0;

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
        {
            $this->redirect($this->createUrl('installer/precheck'));
        }

        $this->render('/installer/license_view',$aData);
    }

    /**
    * display the license file as IIS for example
    * does not display it via the server.
    */
    public function stepViewLicense()
    {
        $filename = dirname(BASEPATH) . '/COPYING';
        header('Content-Type: text/plain;');
        readfile($filename);
        exit;
    }

    /**
    * check a few writing permissions and optional settings
    */
    private function stepPreInstallationCheck()
    {
        $aData['clang'] = $clang = $this->lang;
        //usual data required by view
        $aData['title'] = $clang->gT('Pre-installation check');
        $aData['descp'] = $clang->gT('Pre-installation check for LimeSurvey ').Yii::app()->getConfig('versionnumber');
        $aData['classesForStep'] = array('off','off','on','off','off','off');
        $aData['progressValue'] = 20;
        $aData['phpVersion'] = phpversion();
        // variable storing next button link.initially null
        $aData['next'] = '';

        $bProceed = $this->_check_requirements($aData);

        // after all check, if flag value is true, show next button and sabe step2 status.
        if ($bProceed)
        {
            $aData['next'] = true;
            Yii::app()->session['step2'] = true;
        }

        $this->render('/installer/precheck_view',$aData);
    }

    /**
    * Configure database screen
    */
    private function stepDatabaseConfiguration()
    {
        $this->loadHelper('surveytranslator');

        $aData['clang'] = $clang = $this->lang;
        // usual data required by view
        $aData['title'] = $clang->gT('Database configuration');
        $aData['descp'] = $clang->gT('Please enter the database settings you want to use for LimeSurvey:');
        $aData['classesForStep'] = array('off','off','off','on','off','off');
        $aData['progressValue'] = 40;
		$aData['model'] = $model = new InstallerConfigForm;

		if(isset($_POST['InstallerConfigForm']))
		{
			$model->attributes = $_POST['InstallerConfigForm'];

			//run validation, if it fails, load the view again else proceed to next step.
			if($model->validate()) {
				$sDatabaseType = $model->dbtype;
				$sDatabaseName = $model->dbname;
				$sDatabaseUser = $model->dbuser;
				$sDatabasePwd = $model->dbpwd;
				$sDatabasePrefix = $model->dbprefix;
				$sDatabaseLocation = $model->dblocation;
				$sDatabasePort = '';
				if (strpos($sDatabaseLocation, ':')!==false)
				{
					list($sDatabasePort, $sDatabaseLocation) = explode(':', $sDatabaseLocation, 2);
				}
				$sDatabasePort = self::_getDbPort($sDatabaseType, $sDatabasePort);

				$bDBExists = false;
				$bDBConnectionWorks = false;
				$aDbConfig = compact('sDatabaseType', 'sDatabaseName', 'sDatabaseUser', 'sDatabasePwd', 'sDatabasePrefix', 'sDatabaseLocation', 'sDatabasePort');

				if (self::_dbConnect($aDbConfig, array())) {
					$bDBExists = true;
					$bDBConnectionWorks = true;
				} else {
					$aDbConfig['sDatabaseName'] = '';
					if (self::_dbConnect($aDbConfig, array())) {
						$bDBConnectionWorks = true;
					} else {
						$model->addError('dblocation', $clang->gT('Try again! Connection with database failed.'));
					}
				}

				//if connection with database fail
				if ($bDBConnectionWorks)
				{
					//saving the form data
					foreach(array('dbname', 'dbtype', 'dbpwd', 'dbuser', 'dbprefix') as $sStatusKey) {
						Yii::app()->session[$sStatusKey] = $model->$sStatusKey;
					}
					Yii::app()->session['dbport'] = $sDatabasePort;
					Yii::app()->session['dblocation'] = $sDatabaseLocation;

					//check if table exists or not
					$sTestTablename = 'surveys';
					$bTablesDoNotExist = false;

					// Check if the surveys table exists or not
					if ($bDBExists == true) {
						try {
							$this->connection->createCommand()->select()->from('{{surveys}}')->queryAll();
							$bTablesDoNotExist = false;
						} catch(Exception $e) {
							$bTablesDoNotExist = true;
						}
					}

					$dbexistsbutempty = ($bDBExists && $bTablesDoNotExist);

					//store them in session
					Yii::app()->session['databaseexist'] = $bDBExists;
					Yii::app()->session['tablesexist'] = !$bTablesDoNotExist;

					// If database is up to date, redirect to administration screen.
					if ($bDBExists && !$bTablesDoNotExist)
					{
						Yii::app()->session['optconfig_message'] = sprintf('<b>%s</b>', $clang->gT('The database you specified is up to date.'));
						Yii::app()->session['step3'] = true;

						//$this->redirect($this->createUrl("installer/loadOptView"));
						header("refresh:5;url=".$this->createUrl("/admin"));
						echo sprintf( $clang->gT('The database you specified is up to date. You\'ll be redirected in 5 seconds. If not, click <a href="%s">here</a>.', 'unescaped'), $this->createUrl("/admin"));
						exit();
					}

					if (in_array($model->dbtype, array('mysql', 'mysqli'))) {
						//for development - use mysql in the strictest mode  //Checked)
						if (Yii::app()->getConfig('debug')>1) {
							$this->connection->createCommand("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI'")->execute();
						}
						$versioninfo = $this->connection->getServerVersion();
						if (version_compare($versioninfo,'4.1','<'))
						{
							die("<br />Error: You need at least MySQL version 4.1 to run LimeSurvey. Your version:".$versioninfo);
						}
						@$this->connection->createCommand("SET CHARACTER SET 'utf8'")->execute();  //Checked
						@$this->connection->createCommand("SET NAMES 'utf8'")->execute();  //Checked
					}

					// Setting dateformat for mssql driver. It seems if you don't do that the in- and output format could be different
					if (in_array($model->dbtype, array('odbc_mssql', 'odbtp', 'mssql_n', 'mssqlnative'))) {
						@$this->connection->createCommand('SET DATEFORMAT ymd;')->execute();     //Checked
						@$this->connection->createCommand('SET QUOTED_IDENTIFIER ON;')->execute();     //Checked
					}

					//$aData array won't work here. changing the name
					$values['title'] = $clang->gT('Database settings');
					$values['descp'] = $clang->gT('Database settings');
					$values['classesForStep'] = array('off','off','off','off','on','off');
					$values['progressValue'] = 60;

					//it store text content
					$values['adminoutputText'] = '';
					//it store the form code to be displayed
					$values['adminoutputForm'] = '';

					//if DB exist, check if its empty or up to date. if not, tell user LS can create it.
					if (!$bDBExists)
					{
						Yii::app()->session['databaseDontExist'] = true;

						$values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td align='center'>\n"
						."<strong>".$clang->gT("Database doesn't exist!")."</strong><br /><br />\n"
						.$clang->gT("The database you specified does not exist:")."<br /><br />\n<strong>".$model->dbname."</strong><br /><br />\n"
						.$clang->gT("LimeSurvey can attempt to create this database for you.")."<br /><br />\n";

						$values['adminoutputForm'] = "<form action='".$this->createUrl("installer/createdb")."' method='post'><input type='submit' value='"
						.$clang->gT("Create database")."' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' /></form>";
					}
					elseif ($dbexistsbutempty) //&& !(returnglobal('createdbstep2')==$clang->gT("Populate database")))
					{
						Yii::app()->session['populatedatabase'] = true;

						//$this->connection->database = $model->dbname;
//						//$this->connection->createCommand("USE DATABASE `".$model->dbname."`")->execute();
						$values['adminoutputText'].= sprintf($clang->gT('A database named "%s" already exists.'),$model->dbname)."<br /><br />\n"
						.$clang->gT("Do you want to populate that database now by creating the necessary tables?")."<br /><br />";

						$values['adminoutputForm']= "<form method='post' action='".$this->createUrl("installer/populatedb")."'>"
						."<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='".$clang->gT("Populate database")."' />"
						."</form>";
					}
					elseif (!$dbexistsbutempty)
					{
						//DB EXISTS, CHECK FOR APPROPRIATE UPGRADES
						//$this->connection->database = $model->dbname;
						//$this->connection->createCommand("USE DATABASE `$databasename`")->execute();
						/* @todo Implement Upgrade */
						//$output=CheckForDBUpgrades();
						if ($output== '') {$values['adminoutput'].='<br />'.$clang->gT('LimeSurvey database is up to date. No action needed');}
						else {$values['adminoutput'].=$output;}
						$values['adminoutput'].= "<br />" . sprintf($clang->gT('Please <a href="%s">log in</a>.', 'unescaped'), $this->createUrl("/admin"));
					}
					$values['clang'] = $clang;
					$this->render('/installer/dbsettings_view', $values);
				} else {
					$this->render('/installer/dbconfig_view', $aData);
				}
			} else {
				$this->render('/installer/dbconfig_view', $aData);
			}
        } else {
			$this->render('/installer/dbconfig_view', $aData);
		}
    }

    /**
    * Installer::stepCreateDb()
    * Create database.
    * @return
    */
    function stepCreateDb()
    {
        // check status. to be called only when database don't exist else rdirect to proper link.
        if(!Yii::app()->session['databaseDontExist']) {
            $this->redirect($this->createUrl('installer/welcome'));
        }

        $aData['clang'] = $clang = $this->lang;
		$aData['model'] = $model = new InstallerConfigForm;
		$aData['title'] = $clang->gT("Database configuration");
		$aData['descp'] = $clang->gT("Please enter the database settings you want to use for LimeSurvey:");
		$aData['classesForStep'] = array('off','off','off','on','off','off');
		$aData['progressValue'] = 40;

        $aDbConfig = self::_getDatabaseConfig();
		extract($aDbConfig);
		// unset database name for connection, since we want to create it and it doesn't already exists
		$aDbConfig['sDatabaseName'] = '';
		self::_dbConnect($aDbConfig, $aData);

		$aData['adminoutputForm'] = '';
		// Yii doesn't have a method to create a database
		switch ($sDatabaseType)
		{
			case 'mysqli':
			case 'mysql':
				$createDb = $this->connection->createCommand("CREATE DATABASE `$sDatabaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci")->execute();
				break;
			case 'mssql':
			case 'odbc':
				$createDb = $this->connection->createCommand("CREATE DATABASE [$sDatabaseName];")->execute();
				break;
			default:
				$createDb = $this->connection->createCommand("CREATE DATABASE $sDatabaseName")->execute();
				break;
		}

		//$this->load->dbforge();
		if ($createDb) //Database has been successfully created
		{
			$dsn = self::_getDsn($sDatabaseType, $sDatabasePort);
			$sDsn = sprintf($dsn, $sDatabaseLocation, $sDatabaseName, $sDatabasePort);
			$this->connection = new CDbConnection($sDsn, $sDatabaseUser, $sDatabasePwd);

			Yii::app()->session['populatedatabase'] = true;
			Yii::app()->session['databaseexist'] = true;
			unset(Yii::app()->session['databaseDontExist']);

			$aData['adminoutputText'] = "<tr bgcolor='#efefef'><td colspan='2' align='center'> <br />"
			."<strong><font class='successtitle'>\n"
			.$clang->gT("Database has been created.")."</font></strong><br /><br />\n"
			.$clang->gT("Please continue with populating the database.")."<br /><br />\n";
			$aData['adminoutputForm'] = "<form method='post' action='".$this->createUrl('installer/populatedb')."'>"
			."<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='".$clang->gT("Populate database")."' /></form>";
		}
		else
		{
			$model->addError('dblocation', $clang->gT('Try again! Connection with database failed.'));
			$this->render('/installer/dbconfig_view',$aData);
		}

		$aData['title'] = $clang->gT("Database settings");
		$aData['descp'] = $clang->gT("Database settings");
		$aData['classesForStep'] = array('off','off','off','off','on','off');
		$aData['progressValue'] = 60;
		$this->render('/installer/dbsettings_view',$aData);
    }

    /**
    * Installer::stepPopulateDb()
    * Function to populate the database.
    * @return
    */
    function stepPopulateDb()
    {
        if (!Yii::app()->session['populatedatabase'])
        {
            $this->redirect($this->createUrl('installer/welcome'));
        }

        $aData['clang'] = $clang = $this->lang;
		$aData['model'] = $model = new InstallerConfigForm;
		$aData['title'] = $clang->gT("Database configuration");
		$aData['descp'] = $clang->gT("Please enter the database settings you want to use for LimeSurvey:");
		$aData['classesForStep'] = array('off','off','off','on','off','off');
		$aData['progressValue'] = 40;

        $aDbConfig = self::_getDatabaseConfig();
		extract($aDbConfig);
		self::_dbConnect($aDbConfig, $aData);

		/* @todo Use Yii as it supports various db types and would better handle this process */

		switch ($sDatabaseType)
		{
			case 'mysqli':
			case 'mysql':
				$sql_file = 'mysql';
				break;
			case 'odbc':
			case 'mssql':
				$sql_file = 'mssql';
				break;
			case 'pgsql':
				$sql_file = 'pgsql';
				break;
			default:
				throw new Exception(sprintf('Unkown database type "%s".', $sDatabaseType));
		}

		//checking DB Connection
		$aErrors = self::_setup_tables(dirname(APPPATH).'/installer/sql/create-'.$sql_file.'.sql');
		if ($aErrors === false)
		{
			$model->addError('dblocation', $clang->gT('Try again! Connection with database failed. Reason: ').implode(', ', $aErrors));
			$this->render('/installer/dbconfig_view', $aData);
		}
		elseif (count($aErrors)==0)
		{
			//$data1['adminoutput'] = '';
			//$data1['adminoutput'] .= sprintf("Database `%s` has been successfully populated.",$dbname)."</font></strong></font><br /><br />\n";
			//$data1['adminoutput'] .= "<input type='submit' value='Main Admin Screen' onclick=''>";
			$confirmation = sprintf($clang->gT("Database %s has been successfully populated."), sprintf('<b>%s</b>', Yii::app()->session['dbname']));
		}
		else
		{
			$confirmation = $clang->gT('Database was populated but there were errors:').'<p><ul>';
			foreach ($aErrors as $sError)
			{
				$confirmation.='<li>'.htmlspecialchars($sError).'</li>';
			}
			$confirmation.='</ul>';
		}

		Yii::app()->session['tablesexist'] = true;
		Yii::app()->session['step3'] = true;
		Yii::app()->session['optconfig_message'] = $confirmation;
		unset(Yii::app()->session['populatedatabase']);

		$this->redirect($this->createUrl('installer/optional'));
    }

    /**
    * Optional settings screen
    */
    private function stepOptionalConfiguration()
    {
        $aData['clang'] = $clang = $this->lang;
        $aData['confirmation'] = Yii::app()->session['optconfig_message'];
        $aData['title'] = $clang->gT("Optional settings");
        $aData['descp'] = $clang->gT("Optional settings to give you a head start");
        $aData['classesForStep'] = array('off','off','off','off','off','on');
        $aData['progressValue'] = 80;

        $this->loadHelper('surveytranslator');
		$aData['model'] = $model = new InstallerConfigForm('optional');

		if(isset($_POST['InstallerConfigForm']))
		{
			$model->attributes = $_POST['InstallerConfigForm'];

			//run validation, if it fails, load the view again else proceed to next step.
			if($model->validate()) {
				$adminLoginPwd = $model->adminLoginPwd;
				$confirmPwd = $model->confirmPwd;
				$defaultuser = $model->adminLoginName;
				$defaultpass = $model->adminLoginPwd;
				$siteadminname = $model->adminName;
				$siteadminbounce = $siteadminemail = $model->adminEmail;
				$sitename = $model->siteName;
				$defaultlang = $model->surveylang;

				$aData['title'] = $clang->gT("Database configuration");
				$aData['descp'] = $clang->gT("Please enter the database settings you want to use for LimeSurvey:");
				$aData['classesForStep'] = array('off','off','off','on','off','off');
				$aData['progressValue'] = 40;

				//config file is written, and we've a db in place
				$this->connection = Yii::app()->db;

				//checking DB Connection
				if ($this->connection->getActive() == true) {
					$this->loadLibrary('admin/sha256','sha256');
					$this->sha256 = new SHA256;
					$password_hash = $this->sha256->hashing($defaultpass);

					try {
						$this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'SessionName', 'stg_value' => 'ls'.self::_getRandomID().self::_getRandomID().self::_getRandomID()));

						$this->connection->createCommand()->insert('{{users}}', array('users_name' => $defaultuser, 'password' => $password_hash, 'full_name' => $siteadminname, 'parent_id' => 0, 'lang' => $defaultlang, 'email' => $siteadminemail, 'create_survey' => 1, 'create_user' => 1, 'participant_panel' => 1, 'delete_user' => 1, 'superadmin' => 1, 'configurator' => 1, 'manage_template' => 1, 'manage_label' => 1));
						foreach(array('sitename', 'siteadminname', 'siteadminemail', 'siteadminbounce', 'defaultlang') as $insert) {
							$this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => $insert, 'stg_value' => $$insert));
						}
					// only continue if we're error free otherwise setup is broken.
					} catch (Exception $e) {
						throw new Exception(sprintf('Could not add optional settings: %s.', $e));
					}

					Yii::app()->session['deletedirectories'] = true;

					$aData['title'] = $clang->gT("Success!");
					$aData['descp'] = $clang->gT("LimeSurvey has been installed successfully.");
					$aData['classesForStep'] = array('off','off','off','off','off','off');
					$aData['progressValue'] = 100;
					$aData['user'] = $defaultuser;
					$aData['pwd'] = $defaultpass;

					$this->render('/installer/success_view', $aData);
					exit();
				}
			} else {
				// if passwords don't match, redirect to proper link.
				Yii::app()->session['optconfig_message'] = sprintf('<b>%s</b>', $clang->gT("Passwords don't match."));
				$this->redirect($this->createUrl('installer/optional'));
			}
        } elseif(empty(Yii::app()->session['configFileWritten'])) {
			$this->_writeConfigFile();
		}

        $this->render('/installer/optconfig_view', $aData);
    }

	/**
	 * Loads a helper
	 *
	 * @access public
	 * @param string $helper
	 * @return void
	 */
	public function loadHelper($helper)
	{
		Yii::import('application.helpers.' . $helper . '_helper', true);
	}

	/**
	 * Loads a library
	 *
	 * @access public
	 * @param string $helper
	 * @return void
	 */
	public function loadLibrary($library)
	{
		Yii::import('application.libraries.'.$library, true);
	}

    /**
    * check requirements
    *
    * @param array $data return theme variables
    * @return bool requirements met
    */
    private function _check_requirements(&$data)
    {
        // proceed variable check if all requirements are true. If any of them is false, proceed is set false.
        $bProceed = true; //lets be optimistic!

        /**
        * check image HTML template
        *
        * @param bool $result
        */
        function check_HTML_image($result)
        {
            $label = array('wrong', 'right');
            return sprintf('<img src="%s/installer/images/tick-%s.png" alt="Found" />', Yii::app()->baseUrl, $label[$result]);
        }

        /**
        * check for a specific PHPFunction, return HTML image
        *
        * @param string $function
        * @param string $image return
        * @return bool result
        */
        function check_PHPFunction($function, &$image)
        {
            $result = function_exists($function);
            $image = check_HTML_image($result);
            return $result;
        }

        /**
        * check if file or directory exists and is writeable, returns via parameters by reference
        *
        * @param string $path file or directory to check
        * @param int $type 0:undefined (invalid), 1:file, 2:directory
        * @param string $data to manipulate
        * @param string $base key for data manipulation
        * @param string $keyError key for error data
        * @return bool result of check (that it is writeable which implies existance)
        */
        function check_PathWriteable($path, $type, &$data, $base, $keyError)
        {
            $result = false;
            $data[$base.'Present'] = 'Not Found';
            $data[$base.'Writable'] = '';
            switch($type) {
                case 1:
                    $exists = is_file($path);
                    break;
                case 2:
                    $exists = is_dir($path);
                    break;
                default:
                    throw new Exception('Invalid type given.');
            }
            if ($exists)
            {
                $data[$base.'Present'] = 'Found';
                if (is_writable($path))
                {
                    $data[$base.'Writable'] = 'Writable';
                    $result = true;
                }
                else
                {
                    $data[$base.'Writable'] = 'Unwritable';
                }
            }
            $result || $data[$keyError] = true;

            return $result;
        }

        /**
        * check if file exists and is writeable, returns via parameters by reference
        *
        * @param string $file to check
        * @param string $data to manipulate
        * @param string $base key for data manipulation
        * @param string $keyError key for error data
        * @return bool result of check (that it is writeable which implies existance)
        */
        function check_FileWriteable($file, &$data, $base, $keyError)
        {
            return check_PathWriteable($file, 1, $data, $base, $keyError);
        }

        /**
        * check if directory exists and is writeable, returns via parameters by reference
        *
        * @param string $directory to check
        * @param string $data to manipulate
        * @param string $base key for data manipulation
        * @param string $keyError key for error data
        * @return bool result of check (that it is writeable which implies existance)
        */
        function check_DirectoryWriteable($directory, &$data, $base, $keyError)
        {
            return check_PathWriteable($directory, 2, $data, $base, $keyError);
        }

        //  version check
        if (version_compare(PHP_VERSION, '5.1.6', '<'))
            $bProceed = !$data['verror'] = true;

        // mbstring library check
        if (!check_PHPFunction('mb_convert_encoding', $data['mbstringPresent']))
            $bProceed = false;

        // ** file and directory permissions checking **

        // config directory
        if (!check_DirectoryWriteable(Yii::app()->getConfig('rootdir').'/application/config', $data, 'config', 'derror') )
            $bProceed = false;

        // templates directory check
        if (!check_DirectoryWriteable(Yii::app()->getConfig('rootdir').'/templates/', $data, 'templatedir', 'tperror') )
            $bProceed = false;

        //upload directory check
        if (!check_DirectoryWriteable(Yii::app()->getConfig('rootdir').'/upload/', $data, 'uploaddir', 'uerror') )
            $bProceed = false;

        // ** optional settings check **

        // gd library check
        $data['gdPresent'] = check_HTML_image(array_key_exists('FreeType Support', gd_info()));

        // ldap library check
        check_PHPFunction('ldap_connect', $data['ldapPresent']);

        // php zip library check
        check_PHPFunction('zip_open', $data['zipPresent']);

        // zlib php library check
        check_PHPFunction('zlib_get_coding_type', $data['zlibPresent']);

        return $bProceed;
    }

    /**
    * Installer::_setup_tables()
    * Function that actually modify the database. Read $sqlfile and execute it.
    * @param string $sqlfile
    * @return  Empty string if everything was okay - otherwise the error messages
    */
    function _setup_tables($sFileName, $aDbConfig = array(), $sDatabasePrefix = '')
    {
        extract(empty($aDbConfig) ? self::_getDatabaseConfig() : $aDbConfig);

        switch ($sDatabaseType) {
            case 'mysql':
            case 'mysqli':
                $this->connection->createCommand("ALTER DATABASE `{$sDatabaseName}` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;")->execute();
                break;
            case 'pgsql':
                if ($this->connection->getClientVersion() == '9') {
                    $this->connection->createCommand("ALTER DATABASE {$sDatabaseName} SET bytea_output='escape';")->execute();
                }
                break;
        }

        return $this->_executeSQLFile($sFileName, $sDatabasePrefix);
    }

    /**
    * Executes an SQL file using ADODB
    *
	* @param string $sFileName
    * @param string $sDatabasePrefix
    */
    function _executeSQLFile($sFileName, $sDatabasePrefix)
    {
        $aMessages = array();
        $sCommand = '';

        if (!is_readable($sFileName)) {
            return false;
        } else {
            $aLines = file($sFileName);
        }
        foreach ($aLines as $sLine) {
            $sLine = rtrim($sLine);
            $iLineLength = strlen($sLine);

            if ($iLineLength && $sLine[0] != '#' && substr($sLine,0,2) != '--') {
                if (substr($sLine, $iLineLength-1, 1) == ';') {
                    $line = substr($sLine, 0, $iLineLength-1);
                    $sCommand .= $sLine;
                    $sCommand = str_replace('prefix_', $sDatabasePrefix, $sCommand); // Table prefixes

                    try {
						$this->connection->createCommand($sCommand)->execute();
					} catch(Exception $e) {
						$aMessages[] = "Executing: ".$sCommand." failed! Reason: ".$e;
					}

                    $sCommand = '';
                } else {
                    $sCommand .= $sLine;
                }
            }
        }
        return $aMessages;
    }

    /**
    * Installer::_writeConfigFile()
    * Function to write given database settings in APPPATH.'application/config/config.php'
    * @return
    */
    function _writeConfigFile()
    {
        $aData['clang'] = $clang = $this->lang;
        //write database.php if database exists and has been populated.
        if (Yii::app()->session['databaseexist'] && Yii::app()->session['tablesexist'])
        {
            //write variables in database.php
            $this->loadHelper('file');

			extract(self::_getDatabaseConfig());
			$sDsn = sprintf(self::_getDsn($sDatabaseType, $sDatabasePort), $sDatabaseLocation, $sDatabaseName, $sDatabasePort);

            // mod_rewrite existence check
            if ((function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) || strtolower(getenv('HTTP_MOD_REWRITE')) == 'on')
            {
                $showScriptName = "\t\t\t" . "'showScriptName' => false," . "\n";
            }
            else
            {
                $showScriptName = "\t\t\t" . "'showScriptName' => true," . "\n";
            }

            $dbdata = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');" . "\n"
            ."/*"."\n"
            ."| -------------------------------------------------------------------"."\n"
            ."| DATABASE CONNECTIVITY SETTINGS"."\n"
            ."| -------------------------------------------------------------------"."\n"
            ."| This file will contain the settings needed to access your database."."\n"
            ."|"."\n"
            ."| For complete instructions please consult the 'Database Connection'" ."\n"
            ."| page of the User Guide."."\n"
            ."|"."\n"
            ."| -------------------------------------------------------------------"."\n"
            ."| EXPLANATION OF VARIABLES"."\n"
            ."| -------------------------------------------------------------------"."\n"
            ."|"                                                                    ."\n"
            ."|	'connectionString' Hostname, database, port and database type for " ."\n"
            ."|	 the connection. Driver example: mysql. Currently supported:"       ."\n"
            ."|				 mysql, pgsql, mssql, sqlite, oci"                      ."\n"
            ."|	'username' The username used to connect to the database"            ."\n"
            ."|	'password' The password used to connect to the database"            ."\n"
            ."|	'tablePrefix' You can add an optional prefix, which will be added"  ."\n"
            ."|				 to the table name when using the Active Record class"  ."\n"
            ."|"                                                                    ."\n"
            ."*/"                                                                   ."\n"
			          . "return array("                             . "\n"
			."\t"     . "'basePath' => dirname(dirname(__FILE__))," . "\n"
			."\t"     . "'name' => 'LimeSurvey',"                   . "\n"
			."\t"     . "'defaultController' => 'survey',"          . "\n"
			."\t"     . ""                                          . "\n"

			."\t"     . "'import' => array("                        . "\n"
			."\t\t"   . "'application.core.*',"                     . "\n"
			."\t\t"   . "'application.models.*',"                   . "\n"
			."\t\t"   . "'application.controllers.*',"              . "\n"
			."\t"     . "),"                                        . "\n"
            ."\t"     . ""                                          . "\n"

			."\t"     . "'components' => array("                    . "\n"
			."\t\t"   . "'db' => array("                            . "\n"
			."\t\t\t" . "'connectionString' => '$sDsn',"            . "\n"
			."\t\t\t" . "'emulatePrepare' => true,"                 . "\n"
			."\t\t\t" . "'username' => '$sDatabaseUser',"           . "\n"
			."\t\t\t" . "'password' => '$sDatabasePwd',"            . "\n"
			."\t\t\t" . "'charset' => 'utf8',"                      . "\n"
			."\t\t\t" . "'tablePrefix' => '$sDatabasePrefix',"      . "\n"
			."\t\t" . "),"                                          . "\n"
			."\t\t"   . ""                                          . "\n"

			."\t\t"   . "'session' => array ("                      . "\n"
			."\t\t\t" . "'class' => 'system.web.CDbHttpSession',"   . "\n"
			."\t\t\t" . "'connectionID' => 'db',"                   . "\n"
			."\t\t\t" . "'sessionTableName' => '{{sessions}}',"     . "\n"
			."\t\t"   . "),"                                        . "\n"
			."\t\t"   . ""                                          . "\n"

			/** @todo Uncomment after implementing the error controller */
			/*
			."\t\t"   . "'errorHandler' => array("                  . "\n"
			."\t\t\t" . "'errorAction' => 'error',"                 . "\n"
			."\t\t"   . "),"                                        . "\n"
			."\t\t"   . ""                                          . "\n"
			*/

			."\t\t"   . "'urlManager' => array("                    . "\n"
			."\t\t\t" . "'urlFormat' => 'path',"                    . "\n"
			."\t\t\t" . "'rules' => require('routes.php'),"         . "\n"
			.           $showScriptName
			."\t\t"   . "),"                                        . "\n"
			."\t"     . ""                                          . "\n"

			."\t"     . "),"                                        . "\n"
			          . ");"                                        . "\n"
                      . "/* End of file config.php */"              . "\n"
                      . "/* Location: ./application/config/config.php */";

            if (is_writable(APPPATH . 'config')) {
                write_file(APPPATH . 'config/config.php', $dbdata);
				Yii::app()->session['configFileWritten'] = true;
            } else {
                header('refresh:5;url='.$this->createUrl("installer/welcome"));
                echo "<b>".$clang->gT("Configuration directory is not writable")."</b><br/>";
                printf($clang->gT('You will be redirected in about 5 secs. If not, click <a href="%s">here</a>.' ,'unescaped'), $this->createUrl('installer/welcome'));
                exit;
            }
        }
    }

    /**
    * Create a random survey ID
    *
    * based on code from Ken Lyle
    *
    * @return string
    */
    function _getRandomID()
    {
        // Create a random survey ID -
        // Random sid/ question ID generator...
        $totalChar = 5; // number of chars in the sid
        $salt = "123456789"; // This is the char. that is possible to use
        srand((double)microtime()*1000000); // start the random generator
        $sid=""; // set the inital variable
        for ($i=0;$i<$totalChar;$i++) // loop and create sid
            $sid = $sid . substr ($salt, rand() % strlen($salt), 1);
        return $sid;
    }

	/**
	 * Get the dsn for the database connection
	 *
	 * @param string $sDatabaseType
	 * @param string $sDatabasePort
	 */
	function _getDsn($sDatabaseType, $sDatabasePort = '')
	{
		switch ($sDatabaseType) {
			case 'mysql':
			case 'mysqli':
				$dsn = 'mysql:host=%1$s;port=%3$s;dbname=%2$s';
				break;
			case 'pgsql':
				$dsn = 'pgsql:host=%1$s;port=%3$s;dbname=%2$s';
				break;
			case 'sqlite':
				$dsn = 'sqlite:%1$s/%2$s.sq3';
			case 'sqlite2':
				$dsn = 'sqlite2:%1$s/%2$s.sq2';
				break;
			case 'mssql' :
			case 'dblib' :
			case 'sqlsrv':
			case 'sybase':
				$dsn = $sDatabaseType.':host=%1$s;dbname=%2$s';
				break;
			case 'oci':
				$dsn = 'oci:dbname=//%1$s:%3$s/%2$s';
				break;
			default:
				throw new Exception(sprintf('Unknown database type "%s".', $sDatabaseType));
		}

		return $dsn;
	}

	/**
	 * Get the default port if database port is not set
	 *
	 * @param string $sDatabaseType
	 * @param string $sDatabasePort
	 * @return string
	 */
	function _getDbPort($sDatabaseType, $sDatabasePort = '')
	{
		if (is_numeric($sDatabasePort))
			return $sDatabasePort;

		switch ($sDatabaseType) {
			case 'mysql':
			case 'mysqli':
				$sDatabasePort = '3306';
				break;
			case 'pgsql':
				$sDatabasePort = '5432';
				break;
			case 'oci':
				$sDatabasePort = '1521';
				break;
			case 'mssql' :
			case 'dblib' :
			case 'sqlsrv':
			case 'sybase':
			case 'sqlite':
			case 'sqlite2':
			default:
				$sDatabasePort = '';
		}

		return $sDatabasePort;
	}

	/**
	 * Gets the database configuration from the session
	 *
	 * @return array Database Config
	 */
	function _getDatabaseConfig()
	{
		$sDatabaseType = Yii::app()->session['dbtype'];
        $sDatabasePort = Yii::app()->session['dbport'];
        $sDatabaseName = Yii::app()->session['dbname'];
        $sDatabaseUser = Yii::app()->session['dbuser'];
        $sDatabasePwd = Yii::app()->session['dbpwd'];
        $sDatabasePrefix = Yii::app()->session['dbprefix'];
        $sDatabaseLocation = Yii::app()->session['dblocation'];

		return compact('sDatabaseLocation', 'sDatabaseName', 'sDatabasePort', 'sDatabasePrefix', 'sDatabasePwd', 'sDatabaseType', 'sDatabaseUser');
	}

	/**
	 * Connect to the database
	 *
	 * Throw an error if there's an error
	 */
	function _dbConnect($aDbConfig = array(), $aData = array())
	{
        extract(empty($aDbConfig) ? self::_getDatabaseConfig() : $aDbConfig);
		$dsn = self::_getDsn($sDatabaseType, $sDatabasePort);
		$sDatabaseName = empty($sDatabaseName) ? '' : $sDatabaseName;
		$sDatabasePort = empty($sDatabasePort) ? '' : $sDatabasePort;

		try {
			$sDsn = sprintf($dsn, $sDatabaseLocation, $sDatabaseName, $sDatabasePort);
			$this->connection = new CDbConnection($sDsn, $sDatabaseUser, $sDatabasePwd);
			$this->connection->emulatePrepare = true;
			$this->connection->active = true;
			$this->connection->tablePrefix = $sDatabasePrefix;
			return true;
		} catch(Exception $e) {
			if (!empty($aData['model']) && !empty($aData['clang'])) {
				$aData['model']->addError('dblocation', $aData['clang']->gT('Try again! Connection with database failed. Reason: ') . $e);
				$this->render('/installer/dbconfig_view', $aData);
			} else {
				return false;
			}
		}
	}

    /**
    * Function to install the LimeSurvey database from the command line
    * Call it like:
    *    php index.php installer cmd_install_db
    * from your command line
    * The function assumes that /config/database.php is already configured and the database controller
    * is added in autoload.php in the libraries array
    *
    */
    function cmd_install_db()
    {
        if (php_sapi_name() != 'cli')
        {
            die('This function can only be run from the command line');
        }

        $sDbType = $this->db->dbdriver;
        if (!in_array($sDbType, InstallerConfigForm::supported_db_types)) {
			throw new Exception(sprintf('Unkown database type "%s".', $sDbType));
        }

        $aDbConfig = array(
			'sDatabaseName' => $this->db->database,
            'sDatabaseLocation' => $this->db->hostname,
            'sDatabaseUser' => $this->db->username,
            'sDatabasePwd' => $this->db->password,
            'sDatabaseType' => $sDbType,
			'sDatabasePort' => $this->db->port,
		);

		$sDbPrefix= $this->db->dbprefix;
		self::_dbConnect($aDbConfig);
        $aErrors = self::_setup_tables(Yii::app()->getConfig('rootdir').'/installer/sql/create-'.$sFileName.'.sql',$aDbConfig,$sDbPrefix);

		foreach ($aErrors as $sError)
        {
            echo $sError.PHP_EOL;
        }

        $this->loadLibrary('admin/sha256','sha256');
		$this->sha256 = new SHA256;
        $sPasswordHash = $this->sha256->hashing(Yii::app()->getConfig('defaultpass'));

		try {
			$this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'SessionName', 'stg_value' => 'ls'.self::_getRandomID().self::_getRandomID().self::_getRandomID()));
			$this->connection->createCommand()->insert('{{users}}', array(
				'users_name'=>Yii::app()->getConfig('defaultuser'),
				'password'=>$sPasswordHash,
				'full_name'=>Yii::app()->getConfig('siteadminname'),
				'parent_id'=>0,
				'lang'=>'auto',
				'email'=>Yii::app()->getConfig('siteadminemail'),
				'create_survey'=>1,
				'participant_panel'=>1,
				'create_user'=>1,
				'delete_user'=>1,
				'superadmin'=>1,
				'configurator'=>1,
				'manage_template'=>1,
				'manage_label'=>1
			));
		} catch (Exception $e) {
			$aErrors[] = $e;
		}

        if (count($aErrors)>0) {
            echo "There were errors during the installation.\n";
			foreach ($aErrors as $sError) {
				echo "Error: " . $sError . "\n";
			}
        } else {
            echo "Installation was successful.\n";

        }
    }

}
