<?php

/*
* GititSurvey (tm)
* Copyright (C) 2011 The GititSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* GititSurvey is free software. This version may have been modified pursuant
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
*
* @package LimeSurvey
* @copyright 2019
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
     * @return void
     */
    public function run($action = 'index')
    {
        $this->checkInstallation();
        $this->sessioncontrol();
        App()->loadHelper('common');
        App()->loadHelper('surveytranslator');
        AdminTheme::getInstance();
        App()->getClientScript()->registerCssFile(App()->baseUrl . '/installer/css/main.css');
        App()->getClientScript()->registerCssFile(App()->baseUrl . '/installer/css/fonts.css');

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

            case 'index':
            default:
                $this->redirect(array('installer/welcome'));
                break;
        }
    }

    /**
     * Installer::checkInstallation()
     *
     * Based on existance of 'sample_installer_file.txt' file, check if
     * installation should proceed further or not.
     * @return void
     */
    private function checkInstallation()
    {
        if (file_exists(APPPATH . 'config/config.php')) {
            throw new CHttpException(500, 'Installation has been done already. Installer disabled.');
        }
    }

    /**
     * Load and set session vars
     *
     * @access protected
     * @return void
     */
    protected function sessioncontrol()
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
        Yii::import('application.helpers.surveytranslator_helper', true);
        if (!is_null(Yii::app()->request->getPost('installerLang'))) {
            Yii::app()->session['installerLang'] = Yii::app()->request->getPost('installerLang');
            $this->redirect(array('installer/license'));
        }
        Yii::app()->session->remove('configFileWritten');
        $aData = [];
        $aData['title'] = gT('Welcome');
        $aData['descp'] = gT('Welcome to the GititSurvey installation wizard. This wizard will guide you through the installation, database setup and initial configuration of GititSurvey.');
        $aData['classesForStep'] = array('on', 'off', 'off', 'off', 'off', 'off');
        $aData['progressValue'] = 10;

        if (isset(Yii::app()->session['installerLang'])) {
            $sCurrentLanguage = Yii::app()->session['installerLang'];
        } else {
            $sCurrentLanguage = 'en';
        }
        $aLanguages = [];
        foreach (getLanguageData(true, $sCurrentLanguage) as $sKey => $aLanguageInfo) {
            $aLanguages[htmlspecialchars((string) $sKey)] = sprintf('%s - %s', $aLanguageInfo['nativedescription'], $aLanguageInfo['description']);
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

        if (strtolower((string) $_SERVER['REQUEST_METHOD']) == 'post') {
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
        readfile(dirname((string) BASEPATH) . '/LICENSE');
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
        $aData['model'] = $oModel;
        $aData['title'] = gT('Pre-installation check');
        $aData['descp'] = gT('Pre-installation check for GititSurvey ') . Yii::app()->getConfig('versionnumber');
        $aData['classesForStep'] = array('off', 'off', 'on', 'off', 'off', 'off');
        $aData['progressValue'] = 20;
        // variable storing next button link.initially null
        $aData['next'] = '';

        // Silently check some default PHP extensions
        $this->checkDefaultExtensions();

        $bProceed = $oModel->hasMinimumRequirements;

        $sessionWritable = (Yii::app()->session->get('saveCheck', null) === 'save');
        $aData['sessionWritable'] = $sessionWritable;
        if (!$sessionWritable) {
            // For recheck, try to set the value again
            $session['saveCheck'] = 'save';
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
        Yii::import('application.helpers.surveytranslator_helper', true);

        // usual data required by view
        $aData = [];
        $aData['title'] = gT('Database configuration');
        $aData['descp'] = gT('Please enter the database settings you want to use for GititSurvey:');
        $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
        $aData['progressValue'] = 40;
        $aData['model'] = $oModel = new InstallerConfigForm();
        if (!empty(Yii::app()->session['populateerror'])) {
            if (is_string(Yii::app()->session['populateerror'])) {
                $oModel->addError('dblocation', Yii::app()->session['populateerror']);
            } else {
                foreach (Yii::app()->session['populateerror'] as $error) {
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
                //saving the form data to session
                foreach (array('dblocation', 'dbport', 'dbname', 'dbengine', 'dbtype', 'dbpwd', 'dbuser', 'dbprefix') as $sStatusKey) {
                    Yii::app()->session[$sStatusKey] = $oModel->$sStatusKey;
                }

                //check if table exists or not
                $bTablesDoNotExist = false;

                // Check if the surveys table exists or not
                if ($oModel->dbExists) {
                    try {
                        // We do the following check because DBLIB does not throw an exception on a missing table
                        if ($oModel->db->createCommand()->select()->from('{{users}}')->query()->rowCount == 0) {
                            $bTablesDoNotExist = true;
                        }
                    } catch (Exception $e) {
                        $bTablesDoNotExist = true;
                    }
                }

                $bDBExistsButEmpty = ($oModel->dbExists && $bTablesDoNotExist);

                //store them in session
                Yii::app()->session['databaseexist'] = $oModel->dbExists;
                Yii::app()->session['tablesexist'] = !$bTablesDoNotExist;

                // If database is up to date, redirect to administration screen.
                if ($oModel->dbExists && !$bTablesDoNotExist) {
                    Yii::app()->session['optconfig_message'] = sprintf('<b>%s</b>', gT('The database you specified does already exist.'));
                    Yii::app()->session['step3'] = true;

                    //Write config file as we no longer redirect to optional view
                    $this->writeConfigFile();

                    header("refresh:5;url=" . $this->createUrl("/admin"));
                    $aData['noticeMessage'] = gT('The database exists and contains GititSurvey tables.');
                    $aData['text'] = sprintf(gT("You'll be redirected to the database update or (if your database is already up to date) to the administration login in 5 seconds. If not, please click %shere%s."), "<a href='" . $this->createUrl("/admin") . "'>", "</a>");
                    $this->render('/installer/redirectmessage_view', $aData);
                    exit();
                }

                if ($oModel->isMysql) {
                    //for development - use mysql in the strictest mode
                    if (Yii::app()->getConfig('debug') > 1) {
                        $oModel->db->createCommand("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI,ONLY_FULL_GROUP_BY'")->execute();
                    }
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
                if (!$oModel->dbExists) {
                    Yii::app()->session['databaseDontExist'] = true;

                    $aValues['dbname'] = $oModel->dbname;
                    $aValues['model'] = $oModel;

                    $aValues['next'] = array(
                        'action' => 'installer/createdb',
                        'label' => gT('Create database'),
                        'name' => '',
                    );
                } elseif ($bDBExistsButEmpty) {
                    Yii::app()->session['populatedatabase'] = true;
                    $aValues['model'] = $oModel;

                    //$this->connection->database = $model->dbname;
                    //                        //$this->connection->createCommand("USE DATABASE `".$model->dbname."`")->execute();
                    $aValues['adminoutputText'] .= sprintf(gT('A database named "%s" already exists.'), $oModel->dbname) . "<br /><br />\n"
                    . gT("Do you want to populate that database now by creating the necessary tables?") . "<br /><br />";

                    $aValues['next'] = array(
                        'action' => 'installer/populatedb',
                        'label' => gT("Populate database", 'unescaped'),
                        'name' => 'createdbstep2',
                    );
                } elseif (!$bDBExistsButEmpty) {
                    $aValues['adminoutput'] .= "<br />" . sprintf(gT('Please <a href="%s">log in</a>.', 'unescaped'), $this->createUrl("/admin"));
                }
                $this->render('/installer/populatedb_view', $aValues);
                return;
            }
        }
        $this->render('/installer/dbconfig_view', $aData);
    }

    /**
     * Installer::stepCreateDb()
     * Create database.
     * @return void
     * @throws Exception
     */
    public function stepCreateDb()
    {
        Yii::import('application.helpers.surveytranslator_helper', true);
        // check status. to be called only when database don't exist else redirect to proper link.
        if (!Yii::app()->session['databaseDontExist']) {
            $this->redirect(array('installer/welcome'));
        }
        $aData = [];
        $oModel = $this->getModelFromSession();
        $oModel->dbConnect();
        $aData['model'] = $oModel;
        $aData['title'] = gT("Database configuration");
        $aData['descp'] = gT("Please enter the database settings you want to use for GititSurvey:");
        $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
        $aData['progressValue'] = 40;

        // unset database name for connection, since we want to create it and it doesn't already exists
        $aDbConfig['sDatabaseName'] = '';

        $aData['adminoutputForm'] = '';

        try {
            $oModel->createDatabase();

            Yii::app()->session['populatedatabase'] = true;
            Yii::app()->session['databaseexist'] = true;
            unset(Yii::app()->session['databaseDontExist']);
            $successAlert = $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => '<strong>' . gT("Database has been created") . '.</strong>',
                'type' => 'success',
            ], true);
            $aData['adminoutputText'] =  $successAlert . "\n"
            . gT("Please continue with populating the database.") . "<br /><br />\n";
            $aData['next'] = array(
                'action' => 'installer/populatedb',
                'label' => gT("Populate database"),
                'name' => 'createdbstep2',
            );

            $aData['title'] = gT("Database settings");
            $aData['descp'] = gT("Database settings");
            $aData['classesForStep'] = array('off', 'off', 'off', 'off', 'on', 'off');
            $aData['progressValue'] = 60;
            $this->render('/installer/populatedb_view', $aData);
        } catch (Exception $e) {
            $oModel->addError('dbname', gT('Try again! Creation of database failed.'));
            $oModel->addError('dbname', $e->getMessage());

            $aData['title'] = gT('Database configuration');
            $aData['descp'] = gT('Please enter the database settings you want to use for GititSurvey:');
            $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
            $aData['progressValue'] = 40;
            $aData['model'] = $oModel;

            $this->render('/installer/dbconfig_view', $aData);
        }
    }

    /**
     * Installer::stepPopulateDb()
     * Function to populate the database.
     * @return void
     */
    public function stepPopulateDb()
    {
        if (!Yii::app()->session['populatedatabase']) {
            $this->redirect(array('installer/welcome'));
        }

        $aData = [];
        $model = $this->getModelFromSession();
        $model->dbConnect();

        $aData['model'] = $model;
        $aData['title'] = gT("Database configuration");
        $aData['descp'] = gT("Please enter the database settings you want to use for GititSurvey:");
        $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
        $aData['progressValue'] = 40;

        //checking DB Connection
        $result = $model->setupTables();
        if ($result === true) {
            $sConfirmation = sprintf(gT("Database %s has been successfully populated."), sprintf('<b>%s</b>', Yii::app()->session['dbname']));
        } elseif (is_array($result)) {
            $errors = [];
            $errors[] = gT('There were errors when trying to populate the database:');
            foreach ($result as $error) {
                $errors[] = $error;
            }
            Yii::app()->session['populateerror'] = $errors;
            $this->redirect(array('installer/database'));
        } else {
            throw new UnexpectedValueException('setupTables is expected to return true or an array of strings');
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
        Yii::import('application.helpers.surveytranslator_helper', true);

        $aData = [];
        $aData['confirmation'] = Yii::app()->session['optconfig_message'];
        $aData['title'] = gT("Administrator settings");
        $aData['descp'] = gT("Further settings for application administrator");
        $aData['classesForStep'] = array('off', 'off', 'off', 'off', 'off', 'on');
        $aData['progressValue'] = 80;
        $aData['model'] = $model = $this->getModelFromSession('optional');
        // Backup the default, needed only for $sDefaultAdminPassword
        $sDefaultAdminPassword = $model->adminLoginPwd;
        if (!is_null(Yii::app()->request->getPost('InstallerConfigForm'))) {
            $model->setAttributes(Yii::app()->request->getPost('InstallerConfigForm'), false);

            //run validation, if it fails, load the view again else proceed to next step.
            if ($model->validate()) {
                $aData['title'] = gT("Database configuration");
                $aData['descp'] = gT("Please enter the database settings you want to use for GititSurvey:");
                $aData['classesForStep'] = array('off', 'off', 'off', 'on', 'off', 'off');
                $aData['progressValue'] = 40;

                // Flush query cache because Yii does not handle properly the new DB prefix
                if (method_exists(Yii::app()->cache, 'flush')) {
                    Yii::app()->cache->flush();
                }

                $aDbConfigArray = $this->getDatabaseConfigArray();
                $aDbConfigArray['class'] = '\CDbConnection';
                \Yii::app()->setComponent('db', $aDbConfigArray, false);


                $model->db->setActive(true);

                //checking DB Connection
                if ($model->db->getActive() == true) {
                    try {
                        if (User::model()->count() > 0) {
                            safeDie('Fatal error: Already an admin user in the system.');
                        }

                        // Save user
                        $user = new User();
                        // Fix UserID to 1 for MySQL even if installed in master-master configuration scenario
                        if ($model->isMysql) {
                            $user->uid = 1;
                        }
                        $user->users_name = $model->adminLoginName;
                        $user->setPassword($model->adminLoginPwd);
                        $user->full_name = $model->adminName;
                        $user->parent_id = 0;
                        $user->lang = $model->surveylang;
                        $user->email = $model->adminEmail;
                        $user->save();

                        // Save permissions
                        $permission = new Permission();
                        $permission->entity_id = 0;
                        $permission->entity = 'global';
                        $permission->uid = $user->uid;
                        $permission->permission = 'superadmin';
                        $permission->read_p = 1;
                        $permission->save();

                        // Save  global settings
                        $model->db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'SessionName', 'stg_value' => $this->getRandomString()));
                        $model->db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'sitename', 'stg_value' => $model->siteName));
                        $model->db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminname', 'stg_value' => $model->adminName));
                        $model->db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminemail', 'stg_value' => $model->adminEmail));
                        $model->db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminbounce', 'stg_value' => $model->adminEmail));
                        $model->db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'defaultlang', 'stg_value' => $model->surveylang));

                        // Save survey global settings
                        $model->db->createCommand()->update('{{surveys_groupsettings}}', ['admin' => $model->adminName], "gsid=0");
                        $model->db->createCommand()->update('{{surveys_groupsettings}}', ['adminemail' => $model->adminEmail], "gsid=0");

                        // only continue if we're error free otherwise setup is broken.
                        Yii::app()->session['deletedirectories'] = true;

                        $aData['title'] = gT("Success!");
                        $aData['descp'] = gT("GititSurvey has been installed successfully.");
                        $aData['classesForStep'] = array('off', 'off', 'off', 'off', 'off', 'off');
                        $aData['progressValue'] = 100;
                        $aData['user'] = $model->adminLoginName;
                        if ($sDefaultAdminPassword == $model->adminLoginPwd) {
                            $aData['pwd'] = $model->adminLoginPwd;
                        } else {
                            $aData['pwd'] = gT("The password you have chosen at the optional settings step.");
                        }

                        $this->writeConfigFile();
                        $this->clearSession();

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
        Yii::import('application.helpers.' . $helper . '_helper', true);
    }

    /**
     * Loads a library
     *
     * @access public
     * @return void
     */
    public function loadLibrary($library)
    {
        Yii::import('application.libraries.' . $library, true);
    }

    /**
     * check image HTML template
     *
     * @param bool $result
     * @return string Span with check if $result is true; otherwise a span with warning
     */
    public function chekHtmlImage($result)
    {
        if ($result) {
            return "<span class='ri-check-fill text-success' alt='right'></span>";
        } else {
            return "<span class='ri-error-warning-fill text-danger' alt='wrong'></span>";
        }
    }

    /**
     * @param string $sDirectory
     */
    public function isWritableRecursive($sDirectory)
    {
        $sFolder = opendir($sDirectory);
        if ($sFolder === false) {
            return false; // Dir does not exist
        }
        while ($sFile = readdir($sFolder)) {
            if (
                $sFile != '.' && $sFile != '..' &&
                (!is_writable($sDirectory . "/" . $sFile) ||
                (is_dir($sDirectory . "/" . $sFile) && !$this->isWritableRecursive($sDirectory . "/" . $sFile)))
            ) {
                closedir($sFolder);
                return false;
            }
        }
        closedir($sFolder);
        return true;
    }

    /**
     * Check for a specific PHP Function or class, updates HTML image
     *
     * @param string $sFunctionName Function or class name
     * @param string $sImage HTML string for related image to show
     * @return bool True if exists, otherwise false
     */
    public function checkPHPFunctionOrClass($sFunctionName, &$sImage)
    {
        $bExists = function_exists($sFunctionName) || class_exists($sFunctionName);
        $sImage = $this->chekHtmlImage($bExists);
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
        $aData[$base . 'Present'] = 'Not Found';
        $aData[$base . 'Writable'] = '';
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
            $aData[$base . 'Present'] = 'Found';
            if ((!$bRecursive && is_writable($path)) || ($bRecursive && $this->isWritableRecursive($path))) {
                $aData[$base . 'Writable'] = 'Writable';
                $bResult = true;
            } else {
                $aData[$base . 'Writable'] = 'Unwritable';
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
     * @todo Not used? Compare models/InstallerConfigForm::getHasMinimumRequirements
     */
    private function checkRequirements(&$aData)
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
        if (!$this->checkPHPFunctionOrClass('mb_convert_encoding', $aData['mbstringPresent'])) {
                    $bProceed = false;
        }

        // zlib library check
        if (!$this->checkPHPFunctionOrClass('zlib_get_coding_type', $aData['zlibPresent'])) {
            $bProceed = false;
        }

        // JSON library check
        if (!$this->checkPHPFunctionOrClass('json_encode', $aData['bJSONPresent'])) {
                    $bProceed = false;
        }

        // ** file and directory permissions checking **

        // config directory
        if (!$this->checkDirectoryWriteable(Yii::app()->getConfig('rootdir') . '/application/config', $aData, 'config', 'derror')) {
            $bProceed = false;
        }

        // templates directory check
        if (!$this->checkDirectoryWriteable(Yii::app()->getConfig('tempdir') . '/', $aData, 'tmpdir', 'tperror', true)) {
            $bProceed = false;
        }

        //upload directory check
        if (!$this->checkDirectoryWriteable(Yii::app()->getConfig('uploaddir') . '/', $aData, 'uploaddir', 'uerror', true)) {
            $bProceed = false;
        }

        // Session writable check
        $session = Yii::app()->session; /* @var $session CHttpSession */
        $sessionWritable = ($session->get('saveCheck', null) === 'save');
        $aData['sessionWritable'] = $sessionWritable;
        $aData['sessionWritableImg'] = $this->chekHtmlImage($sessionWritable);
        if (!$sessionWritable) {
            // For recheck, try to set the value again
            $session['saveCheck'] = 'save';
            $bProceed = false;
        }

        // ** optional settings check **

        // gd library check
        if (function_exists('gd_info')) {
            $aData['gdPresent'] = $this->chekHtmlImage(array_key_exists('FreeType Support', gd_info()));
        } else {
            $aData['gdPresent'] = $this->chekHtmlImage(false);
        }
        // ldap library check
        $this->checkPHPFunctionOrClass('ldap_connect', $aData['ldapPresent']);

        // php zip library check
        $this->checkPHPFunctionOrClass('ZipArchive', $aData['zipPresent']);

        // zlib php library check
        $this->checkPHPFunctionOrClass('zlib_get_coding_type', $aData['zlibPresent']);

        // imap php library check
        $this->checkPHPFunctionOrClass('imap_open', $aData['bIMAPPresent']);

        // Sodium php check
        $this->checkPHPFunctionOrClass('sodium_crypto_sign_open', $aData['sodiumPresent']);

        // Silently check some default PHP extensions
        $this->checkDefaultExtensions();

        return $bProceed;
    }

    /**
     * Executes an SQL file
     *
     * @param string $sFileName
     * @param string $sDatabasePrefix
     */
    public function executeSQLFile($sFileName, $sDatabasePrefix)
    {
        $aMessages = array();
        $sCommand = '';

        if (!is_readable($sFileName)) {
            return false;
        } else {
            $aLines = file($sFileName);
        }
        foreach ($aLines as $sLine) {
            $sLine = rtrim((string) $sLine);
            $iLineLength = strlen($sLine);

            if ($iLineLength && $sLine[0] != '#' && substr($sLine, 0, 2) != '--') {
                if (substr($sLine, $iLineLength - 1, 1) == ';') {
                    $sCommand .= $sLine;
                    $sCommand = str_replace('prefix_', $sDatabasePrefix, $sCommand); // Table prefixes

                    try {
                        $this->connection->createCommand($sCommand)->execute();
                    } catch (Exception $e) {
                        $aMessages[] = "Executing: " . $sCommand . " failed! Reason: " . $e;
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
    private function writeConfigFile()
    {
        //write config.php if database exists and has been populated.
        if (Yii::app()->session['databaseexist'] && Yii::app()->session['tablesexist']) {
            $model = $this->getModelFromSession();
            $model->dbConnect();
            $sDsn = $model->db->connectionString;

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
            if (stripos((string) $_SERVER['SERVER_SOFTWARE'], 'apache') !== false || (ini_get('security.limit_extensions') && ini_get('security.limit_extensions') != '')) {
                $sURLFormat = 'path';
            } else {
                // Apache
                $sURLFormat = 'get'; // Fall back to get if an Apache server cannot be determined reliably
            }
            $sCharset = 'utf8';
            if ($model->isMysql) {
                $sCharset = 'utf8mb4';
            }

            if ($model->dbtype) {
                            $sConfig = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');" . "\n"
                . "/*" . "\n"
                . "| -------------------------------------------------------------------" . "\n"
                . "| DATABASE CONNECTIVITY SETTINGS" . "\n"
                . "| -------------------------------------------------------------------" . "\n"
                . "| This file will contain the settings needed to access your database." . "\n"
                . "|" . "\n"
                . "| For complete instructions please consult the 'Database Connection'" . "\n"
                . "| page of the User Guide." . "\n"
                . "|" . "\n"
                . "| -------------------------------------------------------------------" . "\n"
                . "| EXPLANATION OF VARIABLES" . "\n"
                . "| -------------------------------------------------------------------" . "\n"
                . "|" . "\n"
                . "|    'connectionString' Hostname, database, port and database type for " . "\n"
                . "|     the connection. Driver example: mysql. Currently supported:" . "\n"
                . "|                 mysql, pgsql, mssql, sqlite, oci" . "\n"
                . "|    'username' The username used to connect to the database" . "\n"
                . "|    'password' The password used to connect to the database" . "\n"
                . "|    'tablePrefix' You can add an optional prefix, which will be added" . "\n"
                . "|                 to the table name when using the Active Record class" . "\n"
                . "|" . "\n"
                . "*/" . "\n"
                . "return array(" . "\n"
                /*
                ."\t"     . "'basePath' => dirname(dirname(__FILE__))," . "\n"
                ."\t"     . "'runtimePath' => dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'runtime'," . "\n"
                ."\t"     . "'name' => 'GititSurvey',"                   . "\n"
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
                . "\t" . "'components' => array(" . "\n"
                . "\t\t" . "'db' => array(" . "\n"
                . "\t\t\t" . "'connectionString' => '$sDsn'," . "\n";
            }
            if ($model->dbtype != InstallerConfigForm::DB_TYPE_SQLSRV && $model->dbtype != InstallerConfigForm::DB_TYPE_DBLIB) {
                $sConfig .= "\t\t\t" . "'emulatePrepare' => true," . "\n";
            }
            $sConfig .= "\t\t\t" . "'username' => '" . addcslashes((string) $model->dbuser, "'") . "'," . "\n"
            . "\t\t\t" . "'password' => '" . addcslashes((string) $model->dbpwd, "'") . "'," . "\n"
            . "\t\t\t" . "'charset' => '{$sCharset}'," . "\n"
            . "\t\t\t" . "'tablePrefix' => '{$model->dbprefix}'," . "\n";

            if ($model->isMSSql) {
                $sConfig .= "\t\t\t" . "'initSQLs'=>array('SET DATEFORMAT ymd;','SET QUOTED_IDENTIFIER ON;')," . "\n";
            }

            $sConfig .= "\t\t" . ")," . "\n"
            . "\t\t" . "" . "\n"

            . "\t\t" . " 'session' => array (" . "\n"
            . "\t\t\t" . "'sessionName'=>'LS-" . $this->getRandomString(16) . "'" . ",\n"
            . "\t\t\t" . "// Uncomment the following lines if you need table-based sessions." . "\n"
            . "\t\t\t" . "// Note: Table-based sessions are currently not supported on MSSQL server." . "\n"
            . "\t\t\t" . "// 'class' => 'application.core.web.DbHttpSession'," . "\n"
            . "\t\t\t" . "// 'connectionID' => 'db'," . "\n"
            . "\t\t\t" . "// 'sessionTableName' => '{{sessions}}'," . "\n"
            . "\t\t" . " )," . "\n"
            . "\t\t" . "" . "\n"

            /** @todo Uncomment after implementing the error controller */
            /*
            ."\t\t"   . "'errorHandler' => array("                  . "\n"
            ."\t\t\t" . "'errorAction' => 'error',"                 . "\n"
            ."\t\t"   . "),"                                        . "\n"
            ."\t\t"   . ""                                          . "\n"
            */

            . "\t\t" . "'urlManager' => array(" . "\n"
            . "\t\t\t" . "'urlFormat' => '{$sURLFormat}'," . "\n"
            . "\t\t\t" . "'rules' => array(" . "\n"
            . "\t\t\t\t" . "// You can add your own rules here" . "\n"
            . "\t\t\t" . ")," . "\n"
            . "\t\t\t" . "'showScriptName' => {$sShowScriptName}," . "\n"
            . "\t\t" . ")," . "\n"
            . "\t" . "" . "\n"

            . "\t\t" . "// If URLs generated while running on CLI are wrong, you need to set the baseUrl in the request component. For example:" . "\n"
            . "\t\t" . "//'request' => array(" . "\n"
            . "\t\t" . "//\t'baseUrl' => '/limesurvey'," . "\n"
            . "\t\t" . "//)," . "\n"

            . "\t" . ")," . "\n"
            . "\t" . "// For security issue : it's better to set runtimePath out of web access" . "\n"
            . "\t" . "// Directory must be readable and writable by the webuser" . "\n"
            . "\t" . "// 'runtimePath'=>'/var/limesurvey/runtime/'" . "\n"
            . "\t" . "// Use the following config variable to set modified optional settings copied from config-defaults.php" . "\n"
            . "\t" . "'config'=>array(" . "\n"
            . "\t" . "// debug: Set this to 1 if you are looking for errors. If you still get no errors after enabling this" . "\n"
            . "\t" . "// then please check your error-logs - either in your hosting provider admin panel or in some /logs directory" . "\n"
            . "\t" . "// on your webspace." . "\n"
            . "\t" . "// GititSurvey developers: Set this to 2 to additionally display STRICT PHP error messages and get full access to standard templates" . "\n"
            . "\t\t" . "'debug'=>0," . "\n"
            . "\t\t" . "'debugsql'=>0, // Set this to 1 to enanble sql logging, only active when debug = 2" . "\n"
            . "\n"
            . "\t\t" . "// If URLs generated while running on CLI are wrong, you need to uncomment the following line and set your" . "\n"
            . "\t\t" . "// public URL (the URL facing survey participants). You will also need to set the request->baseUrl in the section above." . "\n"
            . "\t\t" . "//'publicurl' => 'https://www.example.org/limesurvey'," . "\n"
            . "\n";

            if ($model->isMysql) {
                $sConfig .= "\t\t" . "// Mysql database engine (INNODB|MYISAM):" . "\n"
                . "\t\t 'mysqlEngine' => '{$model->dbengine}'\n\n,";
            }
            $sConfig .= "\t\t" . "// Update default GititSurvey config here" . "\n"
            . "\t" . ")" . "\n"
            . ");" . "\n"
            . "/* End of file config.php */" . "\n"
            . "/* Location: ./application/config/config.php */";

            if (is_writable(APPPATH . 'config')) {
                file_put_contents(APPPATH . 'config/config.php', $sConfig);
                Yii::app()->session['configFileWritten'] = true;
                $oUrlManager = Yii::app()->getComponent('urlManager');
                /* @var $oUrlManager CUrlManager */
                $oUrlManager->setUrlFormat($sURLFormat);
            } else {
                header('refresh:5;url=' . $this->createUrl("installer/welcome"));
                echo "<b>" . gT("Configuration directory is not writable") . "</b><br/>";
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
    private function getRandomString($iTotalChar = 64)
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
     * @param $scenario
     * @return InstallerConfigForm
     */
    private function getModelFromSession($scenario = null)
    {
        $model = new InstallerConfigForm($scenario);
        isset(Yii::app()->session['dbtype']) ? $model->dbtype = Yii::app()->session['dbtype'] : null;
        isset(Yii::app()->session['dbengine']) ? $model->dbengine = Yii::app()->session['dbengine'] : null;
        isset(Yii::app()->session['dbname']) ? $model->dbname = Yii::app()->session['dbname'] : null;
        isset(Yii::app()->session['dbuser']) ? $model->dbuser = Yii::app()->session['dbuser'] : null;
        isset(Yii::app()->session['dbpwd']) ? $model->dbpwd = Yii::app()->session['dbpwd'] : null;
        isset(Yii::app()->session['dblocation']) ? $model->dblocation = Yii::app()->session['dblocation'] : null;
        isset(Yii::app()->session['dbport']) ? $model->dbport = Yii::app()->session['dbport'] : null;
        isset(Yii::app()->session['dbprefix']) ? $model->dbprefix = Yii::app()->session['dbprefix'] : null;
        isset(Yii::app()->session['dbExists']) ? $model->dbExists = Yii::app()->session['databaseexist'] : null;
        return $model;
    }

    /**
     * clear the session from installation information
     */
    private function clearSession()
    {
        unset(Yii::app()->session['dbtype']);
        unset(Yii::app()->session['dbengine']);
        unset(Yii::app()->session['dbname']);
        unset(Yii::app()->session['dbuser']);
        unset(Yii::app()->session['dbpwd']);
        unset(Yii::app()->session['dblocation']);
        unset(Yii::app()->session['dbport']);
        unset(Yii::app()->session['dbprefix']);
        unset(Yii::app()->session['dbExists']);
    }

    /**
     * Use with \Yii::app()->setComponent() to set connection at runtime.
     * @return array
     */
    private function getDatabaseConfigArray()
    {
        $model = $this->getModelFromSession();

        $sCharset = 'utf8';
        if ($model->isMysql) {
            $sCharset = 'utf8mb4';
        }

        $sDsn = $model->getDsn();

        if ($model->dbtype != InstallerConfigForm::DB_TYPE_SQLSRV && $model->dbtype != InstallerConfigForm::DB_TYPE_DBLIB) {
            $emulatePrepare = true;
        } else {
            $emulatePrepare = null;
        }

        $db = array(
            'connectionString' => $sDsn,
            'emulatePrepare' => $emulatePrepare,
            'username' => $model->dbuser,
            'password' => $model->dbpwd,
            'charset' => $sCharset,
            'tablePrefix' => $model->dbprefix
        );

        return $db;
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
            'hash',
            'pdo'
        );

        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                safeDie('You\'re missing default PHP extension ' . $extension);
            }
        }
    }
}
