<?php
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

namespace ls\controllers;
use \Yii;
use PreCheck, InstallerConfigForm, User;
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
class InstallerController extends \CController {
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
    * @return bool
    */
    
    public $progress = 0;
    
    public $stepTitle = '';
  
    /**
    * welcome and language selection install step
    */
    public function actionIndex()
    {
        App()->session->destroy();
        
        if (App()->request->isPostRequest) {
            App()->setLanguage(App()->request->getPost('installerLang'));
            $this->redirect(['installer/license']);
            die('no');
        }
        App()->loadHelper('surveytranslator');

        $this->stepTitle = gT('Welcome');
        $aData['descp'] = gT('Welcome to the LimeSurvey installation wizard. This wizard will guide you through the installation, database setup and initial configuration of LimeSurvey.');
        $this->progress = 10;

        if (isset(Yii::app()->session['installerLang']))
        {
            $sCurrentLanguage=Yii::app()->session['installerLang'];
        }
        else
            $sCurrentLanguage='en';

        foreach(getLanguageData(true, $sCurrentLanguage) as $sKey => $aLanguageInfo)
        {
            $aLanguages[htmlspecialchars($sKey)] = sprintf('%s - %s', $aLanguageInfo['nativedescription'], $aLanguageInfo['description']);
        }
        $aData['languages']=$aLanguages;
        $this->render('welcome',$aData);
    }

    /**
    * Display license
    */
    public function actionLicense()
    {
        $this->stepTitle = gT('License');
      $this->progress = 15;

        if (App()->request->isPostRequest) {
            $this->redirect(array('installer/precheck'));
        }
        /** 
         * Load PreCheck model here to allow it to check session stuff.
         */
        new PreCheck();
        $this->render('license');
    }
    /**
    * check a few writing permissions and optional settings
    */
    public function  actionPreCheck()
    {
        $preCheck = new PreCheck('required');
        if ($preCheck->validate() && App()->request->isPostRequest) {
            $this->redirect(['installer/config']);
        }
        $this->stepTitle = gT('Pre-installation check');
        $this->progress = 20;
        
        
        $this->render('precheck', ['preCheck' => $preCheck]);
    }

    /**
    * Configure database screen
    */
    public function actionConfig()
    {
        $this->stepTitle = gT('Database configuration');
        $aData['descp'] = gT('Please enter the database settings you want to use for LimeSurvey:');
        $this->progress = 40;
        $aData['model'] = $oModel = new InstallerConfigForm;
        
        if(Yii::app()->request->getPost('InstallerConfigForm') != null) {
            $oModel->attributes = Yii::app()->request->getPost('InstallerConfigForm');
            if ($oModel->validate() // All is good
                    || ($oModel->validate(['dsn']) && $oModel->createDatabase()) // Database was created, now all is good.
                ) {
                Yii::app()->setComponent('db', [
                    'connectionString' => $oModel->dsn,
                    'username' => $oModel->dbuser,
                    'password' => $oModel->dbpwd,
                    'tablePrefix' => $oModel->dbprefix
                ]);
                Yii::app()->db->active = true;
                /**
                 * Save configuration.
                 */
                if (!$this->writeConfigFile($oModel)) {
                    throw new CHttpException(500, "Failed to write config file.");
                }
                /*
                 * Check if database is filled by checking a subset of the tables.
                 */
                
                $tables = array_flip(Yii::app()->db->schema->tableNames);
                $requiredTables = [
                    'answers',
                    'quota',
                    'surveys',
                    'users'
                ];
                $isEmpty = true;
                foreach($requiredTables as $table) {
                    $isEmpty = $isEmpty  && !isset($tables["{$oModel->dbprefix}$table"]);
                }
                
                /**
                 * isEmpty will be true if no tables exist using the user specified prefix.
                 */
                if ($isEmpty) {
                    $this->populateDatabase();
                } 
                $this->redirect(['installer/optional']);
            } 
        } 
        $this->render('config', $aData);
    }

    /**
    * Function to populate the database.
    * @return
    */
    protected function populateDatabase()
    {
       /* @todo Use Yii as it supports various db types and would better handle this process */
        switch (Yii::app()->db->driverName)
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
                throw new Exception(sprintf('Unknown database type "%s".', $sDatabaseType));
        }
        Yii::app()->db->executeFile(Yii::getPathOfAlias('application') . "/../installer/sql/create-$sql_file.sql", Yii::app()->db->tablePrefix);
    }

    /**
    * Optional settings screen
    */
    public function actionOptional()
    {
        Yii::app()->cache->flush();
        // Check if password was not changed.
        $aData['confirmation'] = Yii::app()->session['optconfig_message'];
        $this->stepTitle = gT("Optional settings");
        $aData['descp'] = gT("Optional settings to give you a head start");
        $this->progress = 80;
        $aData['model'] = $model = new InstallerConfigForm('optional');
        // Backup the default, needed only for $sDefaultAdminPassword
        $sDefaultAdminUserName = $model->adminLoginName;
        $sDefaultAdminPassword = $model->adminLoginPwd;
        $sDefaultAdminRealName = $model->adminName;
        $sDefaultSiteName = $model->siteName;
        $sDefaultSiteLanguage = $model->surveylang;
        $sDefaultAdminEmail = $model->adminEmail;
        if(!is_null(Yii::app()->request->getPost('InstallerConfigForm')))
        {
            $model->attributes = Yii::app()->request->getPost('InstallerConfigForm');
            
            //run validation, if it fails, load the view again else proceed to next step.
            if($model->validate()) {
                if (User::model()->count() == 0) {
                    $sAdminUserName = $model->adminLoginName;
                    $sAdminPassword = $model->adminLoginPwd;
                    $sAdminRealName = $model->adminName;
                    $sSiteName = $model->siteName;
                    $sSiteLanguage = $model->surveylang;
                    $sAdminEmail = $model->adminEmail;

                    $sPasswordHash=hash('sha256', $sAdminPassword);
                    try {
                        // Save user
                        $user=new User;
                        $user->users_name=$sAdminUserName;
                        $user->password=$sPasswordHash;
                        $user->full_name=$sAdminRealName;
                        $user->parent_id=0;
                        $user->lang=$sSiteLanguage;
                        $user->email=$sAdminEmail;
                        $user->save();
                        // Save permissions
                        $permission=new \Permission;
                        $permission->entity_id=0;
                        $permission->entity='global';
                        $permission->uid=$user->uid;
                        $permission->permission='superadmin';
                        $permission->read_p=1;
                        $permission->save();
                        // Save  global settings
                        $db = App()->db;
                        $db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'SessionName', 'stg_value' => App()->securityManager->generateRandomString(64)));
                        $db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'sitename', 'stg_value' => $sSiteName));
                        $db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminname', 'stg_value' => $sAdminRealName));
                        $db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminemail', 'stg_value' => $sAdminEmail));
                        $db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'siteadminbounce', 'stg_value' => $sAdminEmail));
                        $db->createCommand()->insert("{{settings_global}}", array('stg_name' => 'defaultlang', 'stg_value' => $sSiteLanguage));
                        // only continue if we're error free otherwise setup is broken.
                    } catch (Exception $e) {
                        throw new Exception(sprintf('Could not add optional settings: %s.', $e));
                    }

                    $aData['user'] = $sAdminUserName;
                    if($sDefaultAdminPassword==$sAdminPassword){
                        $aData['pwd'] = $sAdminPassword;
                    }else{
                        $aData['pwd'] = gT("The password you have chosen at the optional settings step.");
                    }
                } else {
                    // User already existed.
                    $aData['user'] = gT("A user already existed in the database.");
                    $aData['pwd'] = gT("A user already existed in the database.");
                    
                }
                $this->stepTitle = gT("Setup finished!");
                $this->progress = 100;
                $this->render('success', $aData);
                return;
            } else {
                // if passwords don't match, redirect to proper link.
                Yii::app()->session['optconfig_message'] = sprintf('<b>%s</b>', gT("Passwords don't match."));
                $this->redirect(array('installer/optional'));
            }
        }

        $this->render('optional', $aData);
    }
  
    /**
     * Function to write given database settings in APPPATH.'config/config.php'
     */
    private function writeConfigFile(InstallerConfigForm $config)
    {
        // Settings array.
//        $settings = [
//            'components' => [
//                'db' => [
//                    'connectionString' => 'DSN',
//                    'username' => 'username',
//                    'password' => 'pass',
//                    'tablePrefix' => 'prefix',
//                    'emulatePrepare' => true
//                ],
//                'urlManager' => [
//                    'urlFormat' => 'get',
//                    'showScriptName' => true
//                ]
//            ],
//            'config' => [
//                'debug' => 0
//            ]
//        ];
//        echo '<pre>';
//        echo "&lt?php\n";
//        var_export($settings); die();
        //write config.php if database exists and has been populated.
        $sShowScriptName = 'true';
        //}
        if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false || (ini_get('security.limit_extensions') && ini_get('security.limit_extensions')!=''))
        {
            $sURLFormat='path';
        }
        else // Apache
        {
            $sURLFormat='get'; // Fall back to get if an Apache server cannot be determined reliably
        }
        App()->urlManager->setUrlFormat($sURLFormat);
        $sConfig = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');" . "\n"
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
        ."|    'connectionString' Hostname, database, port and database type for " ."\n"
        ."|     the connection. Driver example: mysql. Currently supported:"       ."\n"
        ."|                 mysql, pgsql, mssql, sqlite, oci"                      ."\n"
        ."|    'username' The username used to connect to the database"            ."\n"
        ."|    'password' The password used to connect to the database"            ."\n"
        ."|    'tablePrefix' You can add an optional prefix, which will be added"  ."\n"
        ."|                 to the table name when using the Active Record class"  ."\n"
        ."|"                                                                    ."\n"
        ."*/"                                                                   ."\n"
        . "return array("                             . "\n"
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
        ."\t"     . "'components' => array("                    . "\n"
        ."\t\t"   . "'db' => array("                            . "\n"
        ."\t\t\t" . "'connectionString' => '{$config->dsn}',"            . "\n";
        if ($config->dbtype!='sqlsrv' && $config->dbtype!='dblib' )
        {
            $sConfig .="\t\t\t" . "'emulatePrepare' => true,"    . "\n";

        }
        $sConfig .="\t\t\t" . "'username' => '".addcslashes ($config->dbuser,"'")."',"  . "\n"
        ."\t\t\t" . "'password' => '".addcslashes ($config->dbpwd,"'")."',"            . "\n"
        ."\t\t\t" . "'charset' => 'utf8',"                      . "\n"
        ."\t\t\t" . "'tablePrefix' => '{$config->dbprefix}',"      . "\n";

        if (in_array($config->dbtype, array('mssql', 'sqlsrv', 'dblib'))) {
            $sConfig .="\t\t\t" ."'initSQLs'=>array('SET DATEFORMAT ymd;','SET QUOTED_IDENTIFIER ON;'),"    . "\n";
        }

        $sConfig .="\t\t" . "),"                                          . "\n"
        ."\t\t"   . ""                                          . "\n"

        ."\t\t"   . "// Uncomment the following line if you need table-based sessions". "\n"
        ."\t\t"   . "// 'session' => array ("                      . "\n"
        ."\t\t\t" . "// 'class' => 'system.web.CDbHttpSession',"   . "\n"
        ."\t\t\t" . "// 'connectionID' => 'db',"                   . "\n"
        ."\t\t\t" . "// 'sessionTableName' => '{{sessions}}',"     . "\n"
        ."\t\t"   . "// ),"                                        . "\n"
        ."\t\t"   . ""                                          . "\n"

        /** @todo Uncomment after implementing the error controller */
        /*
        ."\t\t"   . "'errorHandler' => array("                  . "\n"
        ."\t\t\t" . "'errorAction' => 'error',"                 . "\n"
        ."\t\t"   . "),"                                        . "\n"
        ."\t\t"   . ""                                          . "\n"
        */

        ."\t\t"   . "'urlManager' => array("                    . "\n"
        ."\t\t\t" . "'urlFormat' => '{$sURLFormat}',"           . "\n"
        ."\t\t\t" . "'rules' => require('routes.php'),"         . "\n"
        ."\t\t\t" . "'showScriptName' => $sShowScriptName,"      . "\n"
        ."\t\t"   . "),"                                        . "\n"
        ."\t"     . ""                                          . "\n"

        ."\t"     . "),"                                        . "\n"
        ."\t"     . "// Use the following config variable to set modified optional settings copied from config-defaults.php". "\n"
        ."\t"     . "'config'=>array("                          . "\n"
        ."\t"     . "// debug: Set this to 1 if you are looking for errors. If you still get no errors after enabling this". "\n"
        ."\t"     . "// then please check your error-logs - either in your hosting provider admin panel or in some /logs directory". "\n"
        ."\t"     . "// on your webspace.". "\n"
        ."\t"     . "// LimeSurvey developers: Set this to 2 to additionally display STRICT PHP error messages and get full access to standard templates". "\n"
        ."\t\t"   . "'debug'=>0,"                                . "\n"
        ."\t\t"   . "'debugsql'=>0 // Set this to 1 to enanble sql logging, only active when debug = 2" . "\n"
        ."\t"     . ")"                                         . "\n"
        . ");"                                        . "\n"
        . "/* End of file config.php */"              . "\n"
        . "/* Location: ./application/config/config.php */";

        return strlen($sConfig) <= file_put_contents(\Yii::app()->basePath . '/config/config.php', $sConfig);
    }
    
    public function filters()
    {
        return array_merge(parent::filters(), ['accessControl']);
    }
    
public function accessRules()
    {
        $rules = [
            ['allow', 
                'actions' => ['index', 'license', 'precheck', 'config'],
                'expression' => function() { return !file_exists(__DIR__ . '/../config/config.php'); }
            ], 
            ['allow', 
                'actions' => ['optional'], 
                'expression' => function() { return User::model()->count() == 0; }
            ],
            ['deny'],
        ];
        // Note the order; rules are numerically indexed and we want to
        // parents rules to be executed only if ours dont apply.
        return array_merge($rules, parent::accessRules());
    }
}
