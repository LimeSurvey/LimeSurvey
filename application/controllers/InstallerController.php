<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
*
* @package LimeSurvey
* @author Shubham Sachdeva
* @copyright 2011
* @access public
*/
class InstallerController extends CController
{

    /**
     * @var CDbConnection
     */
    public $connection;

    /**
     * clang
     */
    public $lang = null;

    public $layout = 'installer';

    /**
     * Checks for action specific authorization and then executes an action
     *
     * @access public
     * @param string $action
     * @return boolean|null
     */
    public function run($action = 'index')
    {
        $this->_checkInstallation();
        $this->_sessioncontrol();
        Yii::import('application.helpers.common_helper', true);

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
                $this->redirect(array('installer/welcome'));
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
    private function _checkInstallation()
    {
        if (file_exists(APPPATH.'config/config.php')) {
            throw new CHttpException(500, 'Installation has been done already. Installer disabled.');
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
        if (empty(Yii::app()->session['installerLang'])) {
                    Yii::app()->session['installerLang'] = 'en';
        }
        Yii::app()->setLanguage(Yii::app()->session['installerLang']);
    }

    /**
     * welcome and language selection install step
     */
    private function stepWelcome()
    {
        if (!is_null(Yii::app()->request->getPost('installerLang'))) {
            Yii::app()->session['installerLang'] = Yii::app()->request->getPost('installerLang');
            $this->redirect(array('installer/license'));
        }
        $this->loadHelper('surveytranslator');
        Yii::app()->session->remove('configFileWritten');
        $aData = [];
        $aData['title'] = gT('Welcome');
        $aData['descp'] = gT('Welcome to the LimeSurvey installation wizard. This wizard will guide you through the installation, database setup and initial configuration of LimeSurvey.');
        $aData['classesForStep'] = array('on', 'off', 'off', 'off', 'off', 'off');
        $aData['progressValue'] = 10;

        if (isset(Yii::app()->session['installerLang'])) {
            $sCurrentLanguage = Yii::app()->session['installerLang'];
        } else {
            $sCurrentLanguage = 'en';
        }
        $aLanguages = [];
        foreach (getLanguageData(true, $sCurrentLanguage) as $sKey => $aLanguageInfo) {
            $aLanguages[htmlspecialchars($sKey)] = sprintf('%s - %s', $aLanguageInfo['nativedescription'], $aLanguageInfo['description']);
        }
        $aData['languages'] = $aLanguages;
        $this->render('/installer/welcome_view', $aData);
    }

    /**
     * Display license
     */
    private function stepLicense()
    {
        // $aData array contain all the information required by view.
        $aData = [];
        $aData['title'] = gT('License');
        $aData['descp'] = gT('GNU General Public License:');
        $aData['classesForStep'] = array('off', 'on', 'off', 'off', 'off', 'off');
        $aData['progressValue'] = 15;

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $this->redirect(array('installer/precheck'));
        }
        Yii::app()->session['saveCheck'] = 'save'; // Checked in next step

        $this->render('/installer/license_view', $aData);
    }

    /**
     * display the license file as IIS for example
     * does not display it via the server.
     */
    public function stepViewLicense()
    {
        header('Content-Type: text/plain; charset=UTF-8');
        readfile(dirname((string) BASEPATH).'/docs/license.txt');
        exit;
    }

    /**
     * check a few writing permissions and optional settings
     */
    private function stepPreInstallationCheck()
    {
        $oModel = new InstallerConfigForm();
        //usual data required by view
        $aData = [];
        $aData['title'] = gT('Pre-installation check');
        $aData['descp'] = gT('Pre-installation check for LimeSurvey ').Yii::app()->getConfig('versionnumber');
        $aData['classesForStep'] = array('off', 'off', 'on', 'off', 'off', 'off');
        $aData['progressValue'] = 20;
        $aData['phpVersion'] = phpversion();
        // variable storing next button link.initially null
        $aData['next'] = '';
        $aData['dbtypes'] = $oModel->supported_db_types;

        $bProceed = $this->_check_requirements($aData);
        $aData['dbtypes'] = $oModel->supported_db_types;

        if (count($aData['dbtypes']) == 0) {
            $bProceed = false;
        }
        // after all check, if flag value is true, show next button and sabe step2 status.
        if ($bProceed) {
            $aData['next'] = true;
            Yii::app()->session['step2'] = true;
        }

        $this->render('/installer/precheck_view', $aData);
    }

    /**
     * Configure database screen
     */
    private function stepDatabaseConfiguration()
    {
        $this->loadHelper('surveytranslator');

        // usual data required by view
        $aData = [];
        $aData['title'] = gT('Database configuration');
        $aData['descp'] = gT('Please enter the database settings you want to use for LimeSurvey:');
        $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
        $aData['progressValue'] = 40;
        $aData['model'] = $oModel = new InstallerConfigForm;
        if (!empty(Yii::app()->session['populateerror'])) {
            if(is_string(Yii::app()->session['populateerror'])) {
                $oModel->addError('dblocation', Yii::app()->session['populateerror']);
            } else {
                foreach(Yii::app()->session['populateerror'] as $error) {
                    $oModel->addError('dblocation', $error);
                }
            }
            //~ $oModel->addError('dbpwd', '');
            //~ $oModel->addError('dbuser', '');
            unset(Yii::app()->session['populateerror']);
        }

        if (!is_null(Yii::app()->request->getPost('InstallerConfigForm'))) {
            $oModel->setAttributes(Yii::app()->request->getPost('InstallerConfigForm'), false);

            //run validation, if it fails, load the view again else proceed to next step.
            if ($oModel->validate()) {
                $sDatabaseType = $oModel->dbtype;
                $sDatabaseName = $oModel->dbname;
                $sDatabaseUser = $oModel->dbuser;
                $sDatabasePwd = $oModel->dbpwd;
                $sDatabasePrefix = $oModel->dbprefix;
                $sDatabaseLocation = $oModel->dblocation;
                $sDatabasePort = '';
                if (strpos($sDatabaseLocation, ':') !== false) {
                    list($sDatabaseLocation, $sDatabasePort) = explode(':', $sDatabaseLocation, 2);
                } else {
                    $sDatabasePort = $this->_getDbPort($sDatabaseType, $sDatabasePort);
                }
                $bDBConnectionWorks = false;
                $aDbConfig = compact('sDatabaseType', 'sDatabaseName', 'sDatabaseUser', 'sDatabasePwd', 'sDatabasePrefix', 'sDatabaseLocation', 'sDatabasePort');
                $bDBExists = $this->dbTest($aDbConfig, $aData);
                if ($this->_dbConnect($aDbConfig, $aData)) {
                    $bDBConnectionWorks = true;
                } else {
                    $oModel->addError('dblocation', gT('Connection with database failed. Please check database location, user name and password and try again.'));
                    $oModel->addError('dbpwd', '');
                    $oModel->addError('dbuser', '');
                }

                //if connection with database fail
                if ($bDBConnectionWorks) {
                    //saving the form data
                    foreach (array('dbname', 'dbtype', 'dbpwd', 'dbuser', 'dbprefix') as $sStatusKey) {
                        Yii::app()->session[$sStatusKey] = $oModel->$sStatusKey;
                    }
                    Yii::app()->session['dbport'] = $sDatabasePort;
                    Yii::app()->session['dblocation'] = $sDatabaseLocation;

                    //check if table exists or not
                    $bTablesDoNotExist = false;

                    // Check if the surveys table exists or not
                    if ($bDBExists === true) {
                        try {
                            // We do the following check because DBLIB does not throw an exception on a missing table
                            if ($this->connection->createCommand()->select()->from('{{users}}')->query()->rowCount == 0) {
                                $bTablesDoNotExist = true;
                            }
                        } catch (Exception $e) {
                            $bTablesDoNotExist = true;
                        }
                    }

                    $bDBExistsButEmpty = ($bDBExists && $bTablesDoNotExist);

                    //store them in session
                    Yii::app()->session['databaseexist'] = $bDBExists;
                    Yii::app()->session['tablesexist'] = !$bTablesDoNotExist;

                    // If database is up to date, redirect to administration screen.
                    if ($bDBExists && !$bTablesDoNotExist) {
                        Yii::app()->session['optconfig_message'] = sprintf('<b>%s</b>', gT('The database you specified does already exist.'));
                        Yii::app()->session['step3'] = true;

                        //Write config file as we no longer redirect to optional view
                        $this->_writeConfigFile();

                        header("refresh:5;url=".$this->createUrl("/admin"));
                        $aData['noticeMessage'] = gT('The database exists and contains LimeSurvey tables.');
                        $aData['text'] = sprintf(gT("You'll be redirected to the database update or (if your database is already up to date) to the administration login in 5 seconds. If not, please click %shere%s."), "<a href='".$this->createUrl("/admin")."'>", "</a>");
                        $this->render('/installer/redirectmessage_view', $aData);
                        exit();
                    }

                    if (in_array($oModel->dbtype, array('mysql', 'mysqli'))) {
                        //for development - use mysql in the strictest mode  
                        if (Yii::app()->getConfig('debug') > 1) {
                            $this->connection->createCommand("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI'")->execute();
                        }
                        $sMySQLVersion = $this->connection->getServerVersion();
                        if (version_compare($sMySQLVersion, '4.1', '<')) {
                            die("<br />Error: You need at least MySQL version 4.1 to run LimeSurvey. Your version: ".$sMySQLVersion);
                        }
                            /** @scrutinizer ignore-unhandled */ @$this->connection->createCommand("SET CHARACTER SET 'utf8mb4'")->execute(); 
                            /** @scrutinizer ignore-unhandled */ @$this->connection->createCommand("SET NAMES 'utf8mb4'")->execute();
                    }

                    //$aData array won't work here. changing the name
                    $aValues = [];
                    $aValues['title'] = gT('Database settings');
                    $aValues['descp'] = gT('Database settings');
                    $aValues['classesForStep'] = array('off', 'off', 'off', 'off', 'on', 'off');
                    $aValues['progressValue'] = 60;

                    //it store text content
                    $aValues['adminoutputText'] = '';
                    //it store the form code to be displayed
                    $aValues['adminoutputForm'] = '';

                    //if DB exist, check if its empty or up to date. if not, tell user LS can create it.
                    if (!$bDBExists) {
                        Yii::app()->session['databaseDontExist'] = true;

                        $aValues['dbname'] = $oModel->dbname;

                        // The database doesn't exist, etc. TODO: renderPartial should be done in the view, really.
                        $aValues['adminoutputText'] = $this->renderPartial('/installer/nodatabase_view', $aValues, true);

                        $aValues['next'] = array(
                            'action' => 'installer/createdb',
                            'label' => gT('Create database'),
                            'name' => '',
                        );
                    } elseif ($bDBExistsButEmpty) {
                        Yii::app()->session['populatedatabase'] = true;

                        //$this->connection->database = $model->dbname;
                        //                        //$this->connection->createCommand("USE DATABASE `".$model->dbname."`")->execute();
                        $aValues['adminoutputText'] .= sprintf(gT('A database named "%s" already exists.'), $oModel->dbname)."<br /><br />\n"
                        .gT("Do you want to populate that database now by creating the necessary tables?")."<br /><br />";

                        $aValues['next'] = array(
                            'action' => 'installer/populatedb',
                            'label' => gT("Populate database", 'unescaped'),
                            'name' => 'createdbstep2',
                        );
                    } elseif (!$bDBExistsButEmpty) {
                        $aValues['adminoutput'] .= "<br />".sprintf(gT('Please <a href="%s">log in</a>.', 'unescaped'), $this->createUrl("/admin"));
                    }
                    $this->render('/installer/dbsettings_view', $aValues);
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
    public function stepCreateDb()
    {
        // check status. to be called only when database don't exist else redirect to proper link.
        if (!Yii::app()->session['databaseDontExist']) {
            $this->redirect(array('installer/welcome'));
        }
        $aData = [];
        $aData['model'] = new InstallerConfigForm;
        $aData['title'] = gT("Database configuration");
        $aData['descp'] = gT("Please enter the database settings you want to use for LimeSurvey:");
        $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
        $aData['progressValue'] = 40;

        $aDbConfig = $this->_getDatabaseConfig();
        extract($aDbConfig);
        // unset database name for connection, since we want to create it and it doesn't already exists
        $aDbConfig['sDatabaseName'] = '';
        $this->_dbConnect($aDbConfig, $aData);

        $aData['adminoutputForm'] = '';
        // Yii doesn't have a method to create a database
        $bCreateDB = true; // We are thinking positive
        switch ($sDatabaseType) {
            case 'mysqli':
            case 'mysql':
            try {
                $this->connection->createCommand("CREATE DATABASE `$sDatabaseName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")->execute();
            } catch (Exception $e) {
                $bCreateDB = false;
            }
            break;
            case 'dblib':
            case 'mssql':
            case 'odbc':
            try {
                $this->connection->createCommand("CREATE DATABASE [$sDatabaseName];")->execute();
            } catch (Exception $e) {
                $bCreateDB = false;
            }
            break;
            case 'pgsql':
            try {
                $this->connection->createCommand("CREATE DATABASE \"$sDatabaseName\" ENCODING 'UTF8'")->execute();
            } catch (Exception $e) {
                $bCreateDB = false;
            }
            break;
            default:
            try {
                $this->connection->createCommand("CREATE DATABASE $sDatabaseName")->execute();
            } catch (Exception $e) {
                $bCreateDB = false;
            }
            break;
        }

        //$this->load->dbforge();
        if ($bCreateDB) {
//Database has been successfully created
            $sDsn = $this->_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, $sDatabaseName, $sDatabaseUser, $sDatabasePwd);
            $this->connection = new DbConnection($sDsn, $sDatabaseUser, $sDatabasePwd);

            Yii::app()->session['populatedatabase'] = true;
            Yii::app()->session['databaseexist'] = true;
            unset(Yii::app()->session['databaseDontExist']);

            $aData['adminoutputText'] = "<tr bgcolor='#efefef'><td colspan='2' align='center'>"
            ."<div class='alert alert-success''><strong>\n"
            .gT("Database has been created.")."</strong></div>\n"
            .gT("Please continue with populating the database.")."<br /><br />\n";
            $aData['next'] = array(
                'action' => 'installer/populatedb',
                'label' => gT("Populate database"),
                'name' => 'createdbstep2',
            );
        } else {
            $this->loadHelper('surveytranslator');
            $oModel = new InstallerConfigForm;
            $oModel->dbtype = $aDbConfig['sDatabaseType'];
            $oModel->dblocation = $aDbConfig['sDatabaseLocation'];
            $oModel->dbuser = $aDbConfig['sDatabaseUser'];
            //$oModel->dbpwd$aDbConfig['sDatabasePwd']; Don't set password for security issue
            $oModel->dbprefix = $aDbConfig['sDatabasePrefix'];
            $oModel->addError('dbname', gT('Try again! Creation of database failed.'));

            $aData['title'] = gT('Database configuration');
            $aData['descp'] = gT('Please enter the database settings you want to use for LimeSurvey:');
            $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
            $aData['progressValue'] = 40;
            $aData['model'] = $oModel;

            $this->render('/installer/dbconfig_view', $aData);
        }

        $aData['title'] = gT("Database settings");
        $aData['descp'] = gT("Database settings");
        $aData['classesForStep'] = array('off', 'off', 'off', 'off', 'on', 'off');
        $aData['progressValue'] = 60;
        $this->render('/installer/dbsettings_view', $aData);
    }

    /**
     * Installer::stepPopulateDb()
     * Function to populate the database.
     * @return
     */
    public function stepPopulateDb()
    {
        if (!Yii::app()->session['populatedatabase']) {
            $this->redirect(array('installer/welcome'));
        }

        $aData = [];
        $aData['model'] = $model = new InstallerConfigForm;
        $aData['title'] = gT("Database configuration");
        $aData['descp'] = gT("Please enter the database settings you want to use for LimeSurvey:");
        $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
        $aData['progressValue'] = 40;

        $aDbConfig = $this->_getDatabaseConfig();
        extract($aDbConfig);
        $this->_dbConnect($aDbConfig, $aData);

        if (!in_array($sDatabaseType, ['mysqli', 'mysql', 'dblib', 'sqlsrv', 'mssql', 'pgsql'])) {
            throw new Exception(sprintf('Unknown database type "%s".', $sDatabaseType));
        }
  
        //checking DB Connection
        $aErrors = $this->_setup_tables(dirname(APPPATH).'/installer/create-database.php');
        if ($aErrors === false) {
            $model->addError('dblocation', gT('Try again! Connection with database failed. Reason: ').implode(', ', $aErrors));
            $this->render('/installer/dbconfig_view', $aData);
        } elseif ($aErrors === true) {
            //$data1['adminoutput'] = '';
            //$data1['adminoutput'] .= sprintf("Database `%s` has been successfully populated.",$dbname)."</font></strong></font><br /><br />\n";
            //$data1['adminoutput'] .= "<input type='submit' value='Main Admin Screen' onclick=''>";
            $sConfirmation = sprintf(gT("Database %s has been successfully populated."), sprintf('<b>%s</b>', Yii::app()->session['dbname']));
        } elseif (is_array($aErrors) && count($aErrors) > 0) {
            Yii::app()->session['populateerror'] = $aErrors;
            $this->redirect(array('installer/database'));
        } else {
            throw new UnexpectedValueException('_setup_tables is expected to return true, false or an array of strings');
        }

        Yii::app()->session['tablesexist'] = true;
        Yii::app()->session['step3'] = true;
        Yii::app()->session['optconfig_message'] = $sConfirmation;
        unset(Yii::app()->session['populatedatabase']);

        $this->redirect(array('installer/optional'));
    }

    /**
     * Optional settings screen
     */
    private function stepOptionalConfiguration()
    {
        $aData = [];
        $aData['confirmation'] = Yii::app()->session['optconfig_message'];
        $aData['title'] = gT("Administrator settings");
        $aData['descp'] = gT("Further settings for application administrator");
        $aData['classesForStep'] = array('off', 'off', 'off', 'off', 'off', 'on');
        $aData['progressValue'] = 80;
        $this->loadHelper('surveytranslator');
        $aData['model'] = $model = new InstallerConfigForm('optional');
        // Backup the default, needed only for $sDefaultAdminPassword
        $sDefaultAdminPassword = $model->adminLoginPwd;
        if (!is_null(Yii::app()->request->getPost('InstallerConfigForm'))) {
            $model->setAttributes(Yii::app()->request->getPost('InstallerConfigForm'), false);

            //run validation, if it fails, load the view again else proceed to next step.
            if ($model->validate()) {
                $sAdminUserName = $model->adminLoginName;
                $sAdminPassword = $model->adminLoginPwd;
                $sAdminRealName = $model->adminName;
                $sSiteName = $model->siteName;
                $sSiteLanguage = $model->surveylang;
                $sAdminEmail = $model->adminEmail;

                $aData['title'] = gT("Database configuration");
                $aData['descp'] = gT("Please enter the database settings you want to use for LimeSurvey:");
                $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
                $aData['progressValue'] = 40;

                // Flush query cache because Yii does not handle properly the new DB prefix
                if (method_exists(Yii::app()->cache, 'flush')) {
                    Yii::app()->cache->flush();
                }

                $aDbConfigArray = $this->_getDatabaseConfigArray();
                $aDbConfigArray['class'] = '\CDbConnection';
                \Yii::app()->setComponent('db', $aDbConfigArray, false);



                $db = \Yii::app()->getDb();
                $db->setActive(true);
                $this->connection = $db;

                //checking DB Connection
                if ($this->connection->getActive() == true) {
                    try {

                        if (User::model()->count() > 0) {
                            safeDie('Fatal error: Already an admin user in the system.');
                        }

                        // Save user
                        $user = new User;
                        // Fix UserID to 1 for MySQL even if installed in master-master configuration scenario
                        if (in_array($this->connection->getDriverName(), array('mysql', 'mysqli'))) {
                            $user->uid = 1;
                        }
                        $user->users_name = $sAdminUserName;
                        $user->setPassword($sAdminPassword);
                        $user->full_name = $sAdminRealName;
                        $user->parent_id = 0;
                        $user->lang = $sSiteLanguage;
                        $user->email = $sAdminEmail;
                        $user->save();

                        // Save permissions
                        $permission = new Permission;
                        $permission->entity_id = 0;
                        $permission->entity = 'global';
                        $permission->uid = $user->uid;
                        $permission->permission = 'superadmin';
                        $permission->read_p = 1;
                        $permission->save();

                        // Save  global settings
                        $this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'SessionName', 'stg_value' => $this->_getRandomString()));
                        $this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'sitename', 'stg_value' => $sSiteName));
                        $this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminname', 'stg_value' => $sAdminRealName));
                        $this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminemail', 'stg_value' => $sAdminEmail));
                        $this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminbounce', 'stg_value' => $sAdminEmail));
                        $this->connection->createCommand()->insert("{{settings_global}}", array('stg_name' => 'defaultlang', 'stg_value' => $sSiteLanguage));

                        // only continue if we're error free otherwise setup is broken.
                        Yii::app()->session['deletedirectories'] = true;

                        $aData['title'] = gT("Success!");
                        $aData['descp'] = gT("LimeSurvey has been installed successfully.");
                        $aData['classesForStep'] = array('off', 'off', 'off', 'off', 'off', 'off');
                        $aData['progressValue'] = 100;
                        $aData['user'] = $sAdminUserName;
                        if ($sDefaultAdminPassword == $sAdminPassword) {
                            $aData['pwd'] = $sAdminPassword;
                        } else {
                            $aData['pwd'] = gT("The password you have chosen at the optional settings step.");
                        }

                        $this->_writeConfigFile();

                        $this->render('/installer/success_view', $aData);

                        return;

                    } catch (Exception $e) {
                        throw new Exception(sprintf('Could not add administrator settings: %s.', $e));
                    }

                }
            } else {
                unset($aData['confirmation']);
            }
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
        Yii::import('application.helpers.'.$helper.'_helper', true);
    }

    /**
     * Loads a library
     *
     * @access public
     * @return void
     */
    public function loadLibrary($library)
    {
        Yii::import('application.libraries.'.$library, true);
    }

    /**
     * check image HTML template
     *
     * @param bool $result
     * @return string Span with check if $result is true; otherwise a span with warning
     */
    public function check_HTML_image($result)
    {
        if ($result) {
            return "<span class='fa fa-check text-success' alt='right'></span>";
        } else {
            return "<span class='fa fa-exclamation-triangle text-danger' alt='wrong'></span>";
        }
    }

    /**
     * @param string $sDirectory
     */
    public function is_writable_recursive($sDirectory)
    {
        $sFolder = opendir($sDirectory);
        if ($sFolder === false) {
            return false; // Dir does not exist
        }
        while ($sFile = readdir($sFolder)) {
            if ($sFile != '.' && $sFile != '..' &&
                (!is_writable($sDirectory."/".$sFile) ||
                (is_dir($sDirectory."/".$sFile) && !$this->is_writable_recursive($sDirectory."/".$sFile)))) {
                closedir($sFolder);
                return false;
            }
        }
        closedir($sFolder);
        return true;
    }

    /**
     * check for a specific PHPFunction, return HTML image
     *
     * @param string $sFunctionName
     * @param string $sImage return
     * @return bool result
     */
    public function checkPHPFunction($sFunctionName, &$sImage)
    {
        $bExists = function_exists($sFunctionName);
        $sImage = $this->check_HTML_image($bExists);
        return $bExists;
    }

    /**
     * check if file or directory exists and is writeable, returns via parameters by reference
     *
     * @param string $path file or directory to check
     * @param int $type 0:undefined (invalid), 1:file, 2:directory
     * @param string $base key for data manipulation
     * @param string $keyError key for error data
     * @param string $aData
     * @return bool result of check (that it is writeable which implies existance)
     */
    public function checkPathWriteable($path, $type, &$aData, $base, $keyError, $bRecursive = false)
    {
        $bResult = false;
        $aData[$base.'Present'] = 'Not Found';
        $aData[$base.'Writable'] = '';
        switch ($type) {
            case 1:
                $exists = is_file($path);
                break;
            case 2:
                $exists = is_dir($path);
                break;
            default:
                throw new Exception('Invalid type given.');
        }
        if ($exists) {
            $aData[$base.'Present'] = 'Found';
            if ((!$bRecursive && is_writable($path)) || ($bRecursive && $this->is_writable_recursive($path))) {
                $aData[$base.'Writable'] = 'Writable';
                $bResult = true;
            } else {
                $aData[$base.'Writable'] = 'Unwritable';
            }
        }
        $bResult || $aData[$keyError] = true;

        return $bResult;
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
    public function checkFileWriteable($file, &$data, $base, $keyError)
    {
        return $this->checkPathWriteable($file, 1, $data, $base, $keyError);
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
    public function checkDirectoryWriteable($directory, &$data, $base, $keyError, $bRecursive = false)
    {
        return $this->checkPathWriteable($directory, 2, $data, $base, $keyError, $bRecursive);
    }

    /**
     * check requirements
     *
     * @return bool requirements met
     */
    private function _check_requirements(&$aData)
    {
        // proceed variable check if all requirements are true. If any of them is false, proceed is set false.
        $bProceed = true; //lets be optimistic!


        //  version check
        if (version_compare(PHP_VERSION, '5.5.9', '<')) {
                    $bProceed = !$aData['verror'] = true;
        }

        if (convertPHPSizeToBytes(ini_get('memory_limit')) / 1024 / 1024 < 128 && ini_get('memory_limit') != -1) {
                    $bProceed = !$aData['bMemoryError'] = true;
        }


        // mbstring library check
        if (!$this->checkPHPFunction('mb_convert_encoding', $aData['mbstringPresent'])) {
                    $bProceed = false;
        }

        // zlib library check    
        if (!$this->checkPHPFunction('zlib_get_coding_type', $aData['zlibPresent'])) {
            $bProceed = false;
        }

        // JSON library check
        if (!$this->checkPHPFunction('json_encode', $aData['bJSONPresent'])) {
                    $bProceed = false;
        }

        // ** file and directory permissions checking **

        // config directory
        if (!$this->checkDirectoryWriteable(Yii::app()->getConfig('rootdir').'/application/config', $aData, 'config', 'derror')) {
            $bProceed = false;
        }

        // templates directory check
        if (!$this->checkDirectoryWriteable(Yii::app()->getConfig('tempdir').'/', $aData, 'tmpdir', 'tperror', true)) {
            $bProceed = false;
        }

        //upload directory check
        if (!$this->checkDirectoryWriteable(Yii::app()->getConfig('uploaddir').'/', $aData, 'uploaddir', 'uerror', true)) {
            $bProceed = false;
        }

        // Session writable check
        $session = Yii::app()->session; /* @var $session CHttpSession */
        $sessionWritable = ($session->get('saveCheck', null) === 'save');
        $aData['sessionWritable'] = $sessionWritable;
        $aData['sessionWritableImg'] = $this->check_HTML_image($sessionWritable);
        if (!$sessionWritable) {
            // For recheck, try to set the value again
            $session['saveCheck'] = 'save';
            $bProceed = false;
        }

        // ** optional settings check **

        // gd library check
        if (function_exists('gd_info')) {
            $aData['gdPresent'] = $this->check_HTML_image(array_key_exists('FreeType Support', gd_info()));
        } else {
            $aData['gdPresent'] = $this->check_HTML_image(false);
        }
        // ldap library check
        $this->checkPHPFunction('ldap_connect', $aData['ldapPresent']);

        // php zip library check
        $this->checkPHPFunction('zip_open', $aData['zipPresent']);

        // zlib php library check
        $this->checkPHPFunction('zlib_get_coding_type', $aData['zlibPresent']);

        // imap php library check
        $this->checkPHPFunction('imap_open', $aData['bIMAPPresent']);

        // Silently check some default PHP extensions
        $this->checkDefaultExtensions();

        return $bProceed;
    }

    /**
     * Installer::_setup_tables()
     * Function that actually modify the database.
     * @param string $sFileName
     * @param array $aDbConfig
     * @return string[]|true True if everything was okay, otherwise array of error messages.
     */
    public function _setup_tables($sFileName, $aDbConfig = array())
    {
        $aDbConfig = empty($aDbConfig) ? $this->_getDatabaseConfig() : $aDbConfig;
        extract($aDbConfig);
        try {
            switch ($sDatabaseType) {
                case 'mysql':
                case 'mysqli':
                $this->connection->createCommand("ALTER DATABASE ".$this->connection->quoteTableName($sDatabaseName)." DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")->execute();
                break;
            }
        } catch (Exception $e) {
            return array($e->getMessage());
        }
        require_once($sFileName);
        try {
            createDatabase($this->connection);
        } catch (Exception $e) {
            return array($e->getMessage());
        }
        return true;
    }

    /**
     * Executes an SQL file
     *
     * @param string $sFileName
     * @param string $sDatabasePrefix
     */
    public function _executeSQLFile($sFileName, $sDatabasePrefix)
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

            if ($iLineLength && $sLine[0] != '#' && substr($sLine, 0, 2) != '--') {
                if (substr($sLine, $iLineLength - 1, 1) == ';') {
                    $sCommand .= $sLine;
                    $sCommand = str_replace('prefix_', $sDatabasePrefix, $sCommand); // Table prefixes

                    try {
                        $this->connection->createCommand($sCommand)->execute();
                    } catch (Exception $e) {
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
     * Function to write given database settings in APPPATH.'config/config.php'
     */
    private function _writeConfigFile()
    {

        //write config.php if database exists and has been populated.
        if (Yii::app()->session['databaseexist'] && Yii::app()->session['tablesexist']) {

            extract($this->_getDatabaseConfig());
            $sDsn = $this->_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, $sDatabaseName, $sDatabaseUser, $sDatabasePwd);

            // mod_rewrite existence check
            // Section commented out until a better method of knowing whether the mod_rewrite actually
            // works is found. In the meantime, it is better to set $showScriptName to 'true' so it
            // works on all installations, and allow users to change it manually later.
            //if ((function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) || strtolower(getenv('HTTP_MOD_REWRITE')) == 'on')
            //{
            //    $showScriptName = 'false';
            //}
            //else
            //{
            $sShowScriptName = 'true';
            //}
            if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false || (ini_get('security.limit_extensions') && ini_get('security.limit_extensions') != '')) {
                $sURLFormat = 'path';
            } else {
// Apache
                $sURLFormat = 'get'; // Fall back to get if an Apache server cannot be determined reliably
            }
            $sCharset = 'utf8';
            if (in_array($sDatabaseType, array('mysql', 'mysqli'))) {
                $sCharset = 'utf8mb4';
            }

            if ($sDatabaseType) {
                            $sConfig = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');"."\n"
                ."/*"."\n"
                ."| -------------------------------------------------------------------"."\n"
                ."| DATABASE CONNECTIVITY SETTINGS"."\n"
                ."| -------------------------------------------------------------------"."\n"
                ."| This file will contain the settings needed to access your database."."\n"
                ."|"."\n"
                ."| For complete instructions please consult the 'Database Connection'"."\n"
                ."| page of the User Guide."."\n"
                ."|"."\n"
                ."| -------------------------------------------------------------------"."\n"
                ."| EXPLANATION OF VARIABLES"."\n"
                ."| -------------------------------------------------------------------"."\n"
                ."|"."\n"
                ."|    'connectionString' Hostname, database, port and database type for "."\n"
                ."|     the connection. Driver example: mysql. Currently supported:"."\n"
                ."|                 mysql, pgsql, mssql, sqlite, oci"."\n"
                ."|    'username' The username used to connect to the database"."\n"
                ."|    'password' The password used to connect to the database"."\n"
                ."|    'tablePrefix' You can add an optional prefix, which will be added"."\n"
                ."|                 to the table name when using the Active Record class"."\n"
                ."|"."\n"
                ."*/"."\n"
                . "return array("."\n"
                /*
                ."\t"     . "'basePath' => dirname(dirname(__FILE__))," . "\n"
                ."\t"     . "'runtimePath' => dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'runtime'," . "\n"
                ."\t"     . "'name' => 'LimeSurvey',"                   . "\n"
                ."\t"     . "'defaultController' => 'survey',"          . "\n"
                ."\t"     . ""                                          . "\n"

                ."\t"     . "'import' => array("                        . "\n"
                ."\t\t"   . "'application.core.*',"                     . "\n"
                ."\t\t"   . "'application.models.*',"                   . "\n"
                ."\t\t"   . "'application.controllers.*',"              . "\n"
                ."\t\t"   . "'application.modules.*',"                  . "\n"
                ."\t"     . "),"                                        . "\n"
                ."\t"     . ""                                          . "\n"
                */
                ."\t"."'components' => array("."\n"
                ."\t\t"."'db' => array("."\n"
                ."\t\t\t"."'connectionString' => '$sDsn',"."\n";
            }
            if ($sDatabaseType != 'sqlsrv' && $sDatabaseType != 'dblib') {
                $sConfig .= "\t\t\t"."'emulatePrepare' => true,"."\n";

            }
            $sConfig .= "\t\t\t"."'username' => '".addcslashes($sDatabaseUser, "'")."',"."\n"
            ."\t\t\t"."'password' => '".addcslashes($sDatabasePwd, "'")."',"."\n"
            ."\t\t\t"."'charset' => '{$sCharset}',"."\n"
            ."\t\t\t"."'tablePrefix' => '{$sDatabasePrefix}',"."\n";

            if (in_array($sDatabaseType, array('mssql', 'sqlsrv', 'dblib'))) {
                $sConfig .= "\t\t\t"."'initSQLs'=>array('SET DATEFORMAT ymd;','SET QUOTED_IDENTIFIER ON;'),"."\n";
            }

            $sConfig .= "\t\t"."),"."\n"
            ."\t\t".""."\n"

            ."\t\t"." 'session' => array ("."\n"
            ."\t\t\t"."'sessionName'=>'LS-".$this->_getRandomString(16)."'"."\n"
            ."\t\t\t"."// Uncomment the following lines if you need table-based sessions."."\n"
            ."\t\t\t"."// Note: Table-based sessions are currently not supported on MSSQL server."."\n"
            ."\t\t\t"."// 'class' => 'application.core.web.DbHttpSession',"."\n"
            ."\t\t\t"."// 'connectionID' => 'db',"."\n"
            ."\t\t\t"."// 'sessionTableName' => '{{sessions}}',"."\n"
            ."\t\t"." ),"."\n"
            ."\t\t".""."\n"

            /** @todo Uncomment after implementing the error controller */
            /*
            ."\t\t"   . "'errorHandler' => array("                  . "\n"
            ."\t\t\t" . "'errorAction' => 'error',"                 . "\n"
            ."\t\t"   . "),"                                        . "\n"
            ."\t\t"   . ""                                          . "\n"
            */

            ."\t\t"."'urlManager' => array("."\n"
            ."\t\t\t"."'urlFormat' => '{$sURLFormat}',"."\n"
            ."\t\t\t"."'rules' => array("."\n"
            ."\t\t\t\t"."// You can add your own rules here"."\n"
            ."\t\t\t"."),"."\n"
            ."\t\t\t"."'showScriptName' => {$sShowScriptName},"."\n"
            ."\t\t"."),"."\n"
            ."\t".""."\n"

            ."\t"."),"."\n"
            ."\t"."// For security issue : it's better to set runtimePath out of web access"."\n"
            ."\t"."// Directory must be readable and writable by the webuser"."\n"
            ."\t"."// 'runtimePath'=>'/var/limesurvey/runtime/'"."\n"
            ."\t"."// Use the following config variable to set modified optional settings copied from config-defaults.php"."\n"
            ."\t"."'config'=>array("."\n"
            ."\t"."// debug: Set this to 1 if you are looking for errors. If you still get no errors after enabling this"."\n"
            ."\t"."// then please check your error-logs - either in your hosting provider admin panel or in some /logs directory"."\n"
            ."\t"."// on your webspace."."\n"
            ."\t"."// LimeSurvey developers: Set this to 2 to additionally display STRICT PHP error messages and get full access to standard templates"."\n"
            ."\t\t"."'debug'=>0,"."\n"
            ."\t\t"."'debugsql'=>0, // Set this to 1 to enanble sql logging, only active when debug = 2"."\n"
            ."\t\t"."// Update default LimeSurvey config here"."\n"
            ."\t".")"."\n"
            . ");"."\n"
            . "/* End of file config.php */"."\n"
            . "/* Location: ./application/config/config.php */";

            if (is_writable(APPPATH.'config')) {
                file_put_contents(APPPATH.'config/config.php', $sConfig);
                Yii::app()->session['configFileWritten'] = true;
                $oUrlManager = Yii::app()->getComponent('urlManager');
                /* @var $oUrlManager CUrlManager */
                $oUrlManager->setUrlFormat($sURLFormat);
            } else {
                header('refresh:5;url='.$this->createUrl("installer/welcome"));
                echo "<b>".gT("Configuration directory is not writable")."</b><br/>";
                printf(gT('You will be redirected in about 5 secs. If not, click <a href="%s">here</a>.', 'unescaped'), $this->createUrl('installer/welcome'));
                Yii::app()->end();
            }
        }
    }

    /**
     * Create a random ASCII string
     *
     * @return string
     */
    private function _getRandomString($iTotalChar=64)
    {
        $sResult = '';
        for ($i = 0; $i < $iTotalChar; $i++) {
            // Range 65-90 means A-Z, uppercase. Lowercase is betweeen 97-122.
            // @see http://www.asciitable.com/
            $sResult .= chr(rand(65, 90));
        }
        return $sResult;
    }

    /**
     * Get the dsn for the database connection
     *
     * @param string $sDatabaseType
     * @param string $sDatabasePort
     * @return string
     */
    private function _getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, $sDatabaseName, $sDatabaseUser, $sDatabasePwd)
    {
        switch ($sDatabaseType) {
            case 'mysql':
            case 'mysqli':
                // MySQL allow unix_socket for database location, then test if $sDatabaseLocation start with "/"
                if (substr($sDatabaseLocation, 0, 1) == "/") {
                    $sDSN = "mysql:unix_socket={$sDatabaseLocation};dbname={$sDatabaseName};";
                } else {
                    $sDSN = "mysql:host={$sDatabaseLocation};port={$sDatabasePort};dbname={$sDatabaseName};";
                }
                break;
            case 'pgsql':
                if (empty($sDatabasePwd)) {
                    // If there's no password, we need to write password=""; instead of password=;,
                    // or PostgreSQL's libpq will consider the DSN string part after "password="
                    // (including the ";" and the potential dbname) as part of the password definition.
                    $sDatabasePwd = '""';
                }
                $sDSN = "pgsql:host={$sDatabaseLocation};port={$sDatabasePort};user={$sDatabaseUser};password={$sDatabasePwd};";
                if ($sDatabaseName != '') {
                    $sDSN .= "dbname={$sDatabaseName};";
                }
                break;

            case 'dblib' :
                $sDSN = $sDatabaseType.":host={$sDatabaseLocation};dbname={$sDatabaseName}";
                break;
            case 'mssql' :
            case 'sqlsrv':
                if ($sDatabasePort != '') {$sDatabaseLocation = $sDatabaseLocation.','.$sDatabasePort; }
                $sDSN = $sDatabaseType.":Server={$sDatabaseLocation};Database={$sDatabaseName}";
                break;
            default:
                throw new Exception(sprintf('Unknown database type "%s".', $sDatabaseType));
        }
        return $sDSN;
    }

    /**
     * Get the default port if database port is not set
     *
     * @param string $sDatabaseType
     * @param string $sDatabasePort
     * @return string
     */
    private function _getDbPort($sDatabaseType, $sDatabasePort = '')
    {
        if (is_numeric($sDatabasePort)) {
                return $sDatabasePort;
        }

        switch ($sDatabaseType) {
            case 'mysql':
            case 'mysqli':
                $sDatabasePort = '3306';
                break;
            case 'pgsql':
                $sDatabasePort = '5432';
                break;
            case 'dblib' :
            case 'mssql' :
            case 'sqlsrv':
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
    private function _getDatabaseConfig()
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
     * Use with \Yii::app()->setComponent() to set connection at runtime.
     * @return array
     */
    private function _getDatabaseConfigArray()
    {
        $sDatabaseType = Yii::app()->session['dbtype'];
        $sDatabasePort = Yii::app()->session['dbport'];
        $sDatabaseName = Yii::app()->session['dbname'];
        $sDatabaseUser = Yii::app()->session['dbuser'];
        $sDatabasePwd = Yii::app()->session['dbpwd'];
        $sDatabasePrefix = Yii::app()->session['dbprefix'];
        $sDatabaseLocation = Yii::app()->session['dblocation'];

        $sCharset = 'utf8';
        if (in_array($sDatabaseType, array('mysql', 'mysqli'))) {
            $sCharset = 'utf8mb4';
        }

        $sDsn = self::_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, $sDatabaseName, $sDatabaseUser, $sDatabasePwd);

        if ($sDatabaseType != 'sqlsrv' && $sDatabaseType != 'dblib') {
            $emulatePrepare = true;
        } else {
            $emulatePrepare = null;
        }

        $db = array(
            'connectionString' => $sDsn,
            'emulatePrepare' => $emulatePrepare,
            'username' => $sDatabaseUser,
            'password' => $sDatabasePwd,
            'charset' => $sCharset,
            'tablePrefix' => $sDatabasePrefix
        );

        return $db;
    }

    /**
     * Connect to the database
     * @param array $aDbConfig : The config to be tested
     * @param array $aData
     * @return bool
     */
    private function _dbConnect($aDbConfig = array(), $aData = array())
    {
        $aDbConfig = empty($aDbConfig) ? $this->_getDatabaseConfig() : $aDbConfig;
        extract($aDbConfig);
        $sDatabaseName = empty($sDatabaseName) ? '' : $sDatabaseName;
        $sDatabasePort = empty($sDatabasePort) ? '' : $sDatabasePort;

        $sDsn = $this->_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, $sDatabaseName, $sDatabaseUser, $sDatabasePwd);
        if (!$this->dbTest($aDbConfig, $aData)) {
            // Remove sDatabaseName from the connexion is not exist
            $sDsn = $this->_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, "", $sDatabaseUser, $sDatabasePwd);
        }
        try {
            $this->connection = new DbConnection($sDsn, $sDatabaseUser, $sDatabasePwd);
            if ($sDatabaseType != 'sqlsrv' && $sDatabaseType != 'dblib') {
                $this->connection->emulatePrepare = true;
            }
            if (in_array($sDatabaseType, array('mssql', 'sqlsrv', 'dblib'))) {
                $this->connection->initSQLs=array('SET DATEFORMAT ymd;', 'SET QUOTED_IDENTIFIER ON;');
            }
            $this->connection->active = true;
            $this->connection->tablePrefix = $sDatabasePrefix;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * Trye a connexion to the DB and add error in model if exist
     * @param array $aDbConfig : The config to be tested
     * @param array $aData
     * @return bool if connection is done
     */
    private function dbTest($aDbConfig = array(), $aData = array())
    {
        $aDbConfig = empty($aDbConfig) ? $this->_getDatabaseConfig() : $aDbConfig;
        extract($aDbConfig);
        $sDatabaseName = empty($sDatabaseName) ? '' : $sDatabaseName;
        $sDatabasePort = empty($sDatabasePort) ? '' : $sDatabasePort;
        $sDsn = $this->_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, $sDatabaseName, $sDatabaseUser, $sDatabasePwd);
        try {
            $testPdo = new PDO($sDsn, $sDatabaseUser, $sDatabasePwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (Exception $e) {
            if ($sDatabaseType == 'mysql' && $e->getCode() == 1049) {
                return false;
            }
            if ($sDatabaseType == 'pgsql' && $e->getCode() == 7) {
                return false;
            }
            /* @todo : find the good error code for dblib and sqlsrv in exactly same situation : user can read the DB but DB don't exist*/
            /* using same behaviuor than before : test without dbname : db creation can break */
            $sDsn = $this->_getDsn($sDatabaseType, $sDatabaseLocation, $sDatabasePort, "", $sDatabaseUser, $sDatabasePwd);
            try {
                new PDO($sDsn, $sDatabaseUser, $sDatabasePwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            } catch (Exception $e) {
                /* With pgsql : sending "" to tablename => tablename==username , we are unsure if this must be OK */
                /* But Yii sedn exception in same condition : the show this error */
                if (!empty($aData['model'])) {
                    $aData['model']->addError('dblocation', gT('Try again! Connection with database failed.'));
                    $aData['model']->addError('dblocation', gT('Reason:').' '.$e->getMessage());
                    $this->render('/installer/dbconfig_view', $aData);
                    Yii::app()->end();
                } else {
                    // Use same exception than Yii ? unclear
                    throw new DbConnection('CDbConnection failed to open the DB connection.', (int) $e->getCode(), $e->errorInfo);
                }
            }
            return false;
        }
        $sMinimumMySQLVersion = '5.5.3';
        if ($sDatabaseType == 'mysql' && version_compare($testPdo->getAttribute(constant("PDO::ATTR_SERVER_VERSION")), $sMinimumMySQLVersion) == -1) {
            if (!empty($aData['model'])) {
                $aData['model']->addError('dblocation', sprintf(gT('The database does not meet the minimum MySQL/MariaDB server version requirement for LimeSurvey (%s). Found version: %s'), $sMinimumMySQLVersion, $testPdo->getAttribute(constant("PDO::ATTR_SERVER_VERSION"))));
                $this->render('/installer/dbconfig_view', $aData);
                Yii::app()->end();
            }
        }
        return true;
    }

    /**
     * Contains a number of extensions that can be expected
     * to be installed by default, but maybe not on BSD systems etc.
     * Check them silently and die if they are missing.
     * @return void
     */
    private function checkDefaultExtensions()
    {
        $extensions = array(
            'simplexml',
            'filter',
            'ctype',
            'session',
            'hash'
        );

        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                safeDie('You\'re missing default PHP extension '.$extension);
            }
        }

    }
}
