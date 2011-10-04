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
 * FIXME output code belongs into view
 *
 * @package LimeSurvey
 * @author Shubham Sachdeva
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class Installer extends CI_Controller {
    /**
     * dbTasks object used in installation.
     * @var LS_Installer_DbTasks
     */
    private $dbTasks;

    /**
     * Installer::__construct()
     * Constructor
     * @return
     */
    function __construct()
    {
        parent::__construct();
        self::_checkInstallation();
        require_once(APPPATH.'libraries/LS/LS.php');
        $this->dbTasks = new LS_Installer_DbTasks;
        $lang = $this->session->userdata('installerLang');
        if (!$lang)
        {
            $lang = "en";
        }
        $this->load->library('Limesurvey_lang',array($lang));
    }

    /**
     * Installer::index()
     *
     * @return
     */
    function index()
    {
        // redirect to license screen
        redirect(site_url('installer/install/welcome'));
    }

    /**
     * Installer::_checkInstallation()
     *
     * Based on existance of 'sample_installer_file.txt' file, check if installation should
     * proceed further or not.
     * @return
     */
    function _checkInstallation()
    {
        if (!file_exists($this->config->item('rootdir').'/tmp/sample_installer_file.txt'))
        {
            show_error('Installation has been done already.', 500, 'Installer disabled.');
            exit();
        }
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
            return sprintf('<img src="%sinstaller/images/tick-%s.png" alt="Check" />', base_url(), $label[$result]);
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

        // database file
        if (!check_FileWriteable($this->config->item('rootdir').'/application/config/database.php', $data, 'database', 'derror') )
            $bProceed = false;

        // autoload file
        if (!check_FileWriteable($this->config->item('rootdir').'/application/config/autoload.php', $data, 'autoload', 'derror') )
            $bProceed = false;

        // tmp directory check
        if (!check_DirectoryWriteable($this->config->item('rootdir').'/tmp/', $data, 'tmpdir', 'terror') )
            $bProceed = false;

        // templates directory check
        if (!check_DirectoryWriteable($this->config->item('rootdir').'/templates/', $data, 'templatedir', 'tperror') )
            $bProceed = false;

        //upload directory check
        if (!check_DirectoryWriteable($this->config->item('rootdir').'/upload/', $data, 'uploaddir', 'uerror') )
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
     * welcome and language selection install step
     */
    private function stepWelcome()
    {
        $clang = $this->limesurvey_lang;
        // $aData array contain all the information required by view.
        $aData['title']=$clang->gT('Welcome');
        $aData['descp']=$clang->gT('Welcome to the LimeSurvey installation wizard. This wizard will guide you through the installation, database setup and initial configuration of LimeSurvey.');
        $aData['classesForStep']=array('on','off','off','off','off','off');
        $aData['progressValue']=0; // TODO

        if ($lang = $this->input->post('installerLang', false))
        {
            $this->session->set_userdata('installerLang', $lang);
            redirect(site_url('installer/install/license'));
        }

        $this->load->helper('surveytranslator');

        $this->load->view('installer/welcome_view',$aData);
    }

    /**
     * display license
     */
    private function stepLicense()
    {
        $clang = $this->limesurvey_lang;
        // $aData array contain all the information required by view.
        $aData['title']=$clang->gT('License');
        $aData['descp']=$clang->gT('GNU General Public License:');
        $aData['classesForStep']=array('off','on','off','off','off','off');
        $aData['progressValue']=0;
        
        if ($_SERVER['REQUEST_METHOD']=='POST')
        {
            redirect(site_url('installer/install/0'));
        }

        $this->load->view('installer/license_view',$aData);
    }

    /**
     * check a few writing permissions and optional settings
     */
    private function stepPreInstallationCheck()
    {
        $clang = $this->limesurvey_lang;
        //usual data required by view
        $aData['title']=$clang->gT('Pre-installation check');
        $aData['descp']=$clang->gT('Pre-installation check for LimeSurvey ').$this->config->item('versionnumber');
        $aData['classesForStep']=array('off','off','on','off','off','off');
        $aData['progressValue']=20;
        $aData['phpVersion'] = phpversion();
        // variable storing next button link.initially null
        $aData['next']='';

        $bProceed = $this->_check_requirements($aData);

        // after all check, if flag value is true, show next button and sabe step2 status.
        if ($bProceed)
        {
            $aData['next']=TRUE;
            $aStatusdata = array(
                'step2'  => 'TRUE'
            );
            $this->session->set_userdata($aStatusdata);
        }

        $this->load->view('installer/precheck_view',$aData);
    }

    /**
     * Configure database screen
     */
    private function stepDatabaseConfiguration()
    {
        $clang = $this->limesurvey_lang;
        // usual data required by view
        $aData['title']=$clang->gT('Database configuration');
        $aData['descp']=$clang->gT('Please enter the database settings you want to use for LimeSurvey:');
        $aData['classesForStep']=array('off','off','off','on','off','off');
        $aData['progressValue']=40;
        // errorConnection store text to be displayed if connection with DB fail
        $aData['errorConnection']="";

        //load form validation library and helpers necessary.
        $this->load->helper('form');
        $this->load->library('form_validation');

        //setting form validation rules.
        $this->form_validation->set_rules('dbtype', 'Database Type', 'required');
        $this->form_validation->set_rules('dblocation', 'Database Location', 'required');
        $this->form_validation->set_rules('dbname', 'Database Name', 'required');
        $this->form_validation->set_rules('dbuser', 'Database User', 'required');
        $this->form_validation->set_rules('dbconfirmpwd', 'Confirm Password', 'matches[dbpwd]');
        $this->form_validation->set_rules('dbprefix', 'Database Prefix', 'not required');

        //setting custom error message for confirm password field
        $this->form_validation->set_message('matches',$clang->gT('Passwords do not match!'));
        //setting error delimiters so that errors can be displayed in red!
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');

        //run validation, if it fails, load the view again else proceed to next step.
        if ($this->form_validation->run() == FALSE)
        {
            $this->load->view('installer/dbconfig_view',$aData);
        }
        else
        {
            //to establish connection withh different DB.
            require_once(APPPATH.'third_party/adodb/adodb.inc.php');
            //lets assume
            $_POST = $this->input->post();

            $sAdodbType=$_POST['dbtype'];
            if ($sAdodbType=='postgre')
            {
                $sAdodbType='postgres';
            }
            $connect=ADONewConnection($sAdodbType);

            if (strpos($_POST['dblocation'],':')!==false)
            {
                list($sDatabasePort, $sDatabaseLocation)
                    = explode(':', $_POST['dblocation'], 2);
            }
            else
            {
                $sDatabasePort='default';
                $sDatabaseLocation=$_POST['dblocation'];
            }

            $sADODBHost = $sDatabaseLocation;
            $dbtype = $_POST['dbtype'];
            //check connection
            switch ($dbtype)
            {
                case 'postgre':
                case 'mysqli':
                case 'mysql':
                    if ($sDatabasePort != 'default')
                    {
                        $sADODBHost = $sDatabaseLocation.':'.$sDatabasePort;
                    }
                break;
                case 'mssql':
                    if ($sDatabasePort != 'default')
                    {
                        $sADODBHost = $sDatabaseLocation.','.$sDatabasePort;
                    }
                break;
                default:
                    throw new Exception(sprintf('Unknown database type "%s".', $dbtype));
            }

            $bDBExists = false;
            $bDBConnectionWorks = false;
            // Now try connecting to the database
            if (@$connect->Connect($sADODBHost, $_POST['dbuser'], $_POST['dbpwd'], $_POST['dbname']))
            {
                $bDBExists = true;
                $bDBConnectionWorks = true;
            }
            else
            {
                // If that doesn't work try connection without database-name
                $connect->database = '';
                $bDBConnectionWorks = (bool) @$connect->Connect($dbhost, $_POST['dbuser'], $_POST['dbpwd']);
            }

            //if connection with database fail
            if (!$bDBConnectionWorks)
            {
                $aData['errorConnection'] = '<b>'.$clang->gT('Try again! Connection with database failed.').'</b>';
                $this->load->view('installer/dbconfig_view',$aData);
            }
            else
            {
                //saving the form data
                $aStatusdata = array(
                    'dbname' => $this->input->post('dbname'),
                    'dbtype' => $this->input->post('dbtype'),
                    'dblocation' => $this->input->post('dblocation'),
                    'dbpwd' => $this->input->post('dbpwd'),
                    'dbuser' => $this->input->post('dbuser'),
                    'dbprefix' => $this->input->post('dbprefix')
                    );
                $this->session->set_userdata($aStatusdata);

                 //check if table exists or not
                $sTestTablename = 'surveys';
                $bTablesDoNotExist=false;

                $aTableList = $connect->MetaTables();

                if ($aTableList==false)
                {
                    $bTablesDoNotExist = true;
                }
                else
                {
                    $bProceed=false;

                    foreach ($aTableList as $sTable)
                    {
                        if (self::_db_quote_id($sTable,$this->input->post('dbtype')) == self::_db_table_name($sTestTablename,$_POST['dbprefix'],$_POST['dbtype']))
                        {
                            $bProceed=true;
                            break;
                        }
                    }
                    if ($bProceed)
                    {
                        $bTablesDoNotExist = false;
                    } else {
                        $bTablesDoNotExist = true;
                    }
                }

                // AdoDB seems to be defaulting to ADODB_FETCH_NUM and we want to be sure that the right default mode is set
                $connect->SetFetchMode(ADODB_FETCH_ASSOC);

                $dbexistsbutempty=($bDBExists && $bTablesDoNotExist);

                //store them in session
                $this->session->set_userdata(array('databaseexist' => $bDBExists, 'tablesexist' => !$bTablesDoNotExist));

                // If database is up to date, redirect to Optional Configuration screen.
                if ($bDBExists && !$bTablesDoNotExist)
                {

                    $aStatusdata = array(
                    'optconfig_message' => sprintf('<b>%s</b>', $clang->gT('The database you specified is up to date.')),
                    'step3'  => TRUE
                    );
                    $this->session->set_userdata($aStatusdata);
                    redirect(site_url("installer/loadOptView"));
                }

                if ($_POST['dbtype']=='mysql' || $_POST['dbtype']=='mysqli') {
                if ($this->config->item('debug')>1) {
                    @$connect->Execute("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI'");
                }//for development - use mysql in the strictest mode  //Checked)
                    $infoarray=$connect->ServerInfo();
                    if (version_compare ($infoarray['version'],'4.1','<'))
                    {
                        safe_die ("<br />Error: You need at least MySQL version 4.1 to run LimeSurvey. Your version:".$infoarray['version']);
                    }
                    @$connect->Execute("SET CHARACTER SET 'utf8'");  //Checked
                    @$connect->Execute("SET NAMES 'utf8'");  //Checked
                }

                // Setting dateformat for mssql driver. It seems if you don't do that the in- and output format could be different
                if ($_POST['dbtype']=='odbc_mssql' || $_POST['dbtype']=='odbtp' || $_POST['dbtype']=='mssql_n' || $_POST['dbtype']=='mssqlnative') {
                    @$connect->Execute('SET DATEFORMAT ymd;');     //Checked
                    @$connect->Execute('SET QUOTED_IDENTIFIER ON;');     //Checked
                }

                //$aData array won't work here. changing the name
                $values['title']=$clang->gT('Database settings');
                $values['descp']=$clang->gT('Database settings');
                $values['classesForStep']=array('off','off','off','off','on','off');
                $values['progressValue']=60;

                //it store text content
                $values['adminoutputText']='';
                //it store the form code to be displayed
                $values['adminoutputForm']='';

                //if DB exist, check if its empty or up to date. if not, tell user LS can create it.
                if (!$bDBExists)
                {
                    $aStatusdata = array(
                    'databaseDontExist'  => 'TRUE'
                    );
                    $this->session->set_userdata($aStatusdata);

                    $values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td align='center'>\n"
                    ."<strong>".$clang->gT("Database doesn't exist!")."</strong><br /><br />\n"
                    .$clang->gT("The database you specified does not exist:")."<br /><br />\n<strong>".$this->input->post('dbname')."</strong><br /><br />\n"
                    .$clang->gT("LimeSurvey can attempt to create this database for you.")."<br /><br />\n";

                    $values['adminoutputForm']="<form action='".site_url("installer/createdb")."' method='post'><input type='submit' value='"
                    .$clang->gT("Create Database")."' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' /></form>";


                }
                elseif ($dbexistsbutempty ) //&& !(returnglobal('createdbstep2')==$clang->gT("Populate Database")))
                {
                    $aStatusdata = array(
                        'populatedatabase'  => 'TRUE'
                    );
                    $this->session->set_userdata($aStatusdata);

                    $connect->database = $this->input->post('dbname');
                    $connect->Execute("USE DATABASE `".$this->input->post('dbname')."`");
                    $values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td colspan='2' align='center'><br />\n"
                    ."<font class='successtitle'><strong>\n"
                    .sprintf($clang->gT('A database named "%s" already exists.'),$this->input->post('dbname'))."</strong></font><br /><br />\n"
                    .$clang->gT("Do you want to populate that database now by creating the necessary tables?")."<br /><br />";

                    $values['adminoutputForm']= "<form method='post' action='".base_url()."index.php/installer/populatedb/'>"
                    ."<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='".$clang->gT("Populate Database")."' />"
                    ."</form>";
                }
                elseif (!$dbexistsbutempty)
                {
                    //DB EXISTS, CHECK FOR APPROPRIATE UPGRADES
                    $connect->database = $this->input->post('dbname');
                    $connect->Execute("USE DATABASE `$databasename`");
                    $output=CheckForDBUpgrades();
                    if ($output== '') {$values['adminoutput'].='<br />'.$clang->gT('LimeSurvey Database is up to date. No action needed');}
                    else {$values['adminoutput'].=$output;}
                    $values['adminoutput'].="<br />Please ".anchor("admin","log in.");
                }
                $this->load->view('installer/dbsettings_view', $values);
            }
        }
    }

    /**
     * Optional settings screen
     */
    private function stepOptionalConfiguration()
    {
        $this->_writeDatabaseFile();
        $this->_writeAutoloadfile();

        $clang = $this->limesurvey_lang;

        // confirmation message to be displayed
        $aData['confirmation']=$this->session->userdata('optconfig_message');
        $aData['title']=$clang->gT("Optional settings");
        $aData['descp']=$clang->gT("Optional settings to give you a head start");
        $aData['classesForStep']=array('off','off','off','off','off','on');
        $aData['progressValue']=80;

        $this->session->set_userdata('optional', TRUE);

        $this->load->helper('surveytranslator'); // FIXME for what?
        $this->load->view('installer/optconfig_view', $aData);
    }

    /**
     * Installer::install()
     * Install Limeurvey database and default settings.
     * @param integer $step
     * @return
     */
    function install($step=0)
    {
        $clang = $this->limesurvey_lang;

        switch($step)
        {
            case 'welcome':
                $this->stepWelcome();
                break;

            case 'license':
                $this->stepLicense();
                break;

            case 0:
                $this->stepPreInstallationCheck();
                break;

            case 1:
                //check if user has completed step2
                if (!$this->session->userdata('step2'))
                {
                    redirect(site_url('installer/install/0'));
                }
                $this->stepDatabaseConfiguration();
                break;

            case 2:
                //check status whether user is done populating DB,
                if( !$this->session->userdata('step3'))
                {
                    redirect(site_url('installer/install/1'));
                }
                $message = sprintf($clang->gT("Database %s has been successfully populated."), sprintf('<b>%s<b>', $this->session->userdata('dbname')));
                $this->session->set_userdata('optconfig_message', $message);
                $this->stepOptionalConfiguration();
                break;
        }
    }


    // this function does the processing of optional view form.
    /**
     * Installer::optional()
     * Processes optional configuration screen data
     * @return
     */
    function optional()
    {

        if (!$this->session->userdata('optional'))
        {
            redirect(site_url('installer/install/license'));
        }

        $clang = $this->limesurvey_lang;

        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');

        //check if passwords match , input class take care of any xss filetring or sql injection
        $adminLoginPwd = $this->input->post('adminLoginPwd');
        $confirmPwd = $this->input->post('confirmPwd');
        if (!empty($adminLoginPwd) && $adminLoginPwd == $confirmPwd)
        {

            $defaultuser = $this->input->post('adminLoginName');
            $defaultpass = $this->input->post('adminLoginPwd');
            $siteadminname = $this->input->post('adminName');
            $siteadminemail = $this->input->post('adminEmail');
            $sitename = $this->input->post('siteName');
            $defaultlang = $this->input->post('surveylang');


            //if any of the field was left blank, replace it with default.
            //FIXME doing the following leads to a problem. this action could make use of the
            //      form helper like the database step (@see install())
            //      problem: two empty passwords are accepted.
            if ($defaultuser == '') $defaultuser  = "admin";
            if ($defaultpass == '') $defaultpass  = "password";
            if ($siteadminname == '') $siteadminname  = "Your Name";
            if ($siteadminemail == '') $siteadminemail  = "your-email@example.net";
            if ($sitename == '') $sitename  = "LimeSurvey";
            if ($defaultlang == '') $defaultlang  = "en";


            $dbname = $this->session->userdata('dbname');
            $sAdodbType = $this->session->userdata('dbtype');
            if ($sAdodbType=='postgre')
            {
                $sAdodbType='postgres';
            }
            $connect=ADONewConnection($sAdodbType);


                //checking DB Connection
                if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
                {
                    $aData['errorConnection'] ='<b>'.$clang->gT('Try again! Connection with database failed.').'</b><br/><b>'.$clang->gT('Reason').'</b>:'.$connect->ErrorMsg().'<br/>';
                    $aData['title']=$clang->gT("Database configuration");
                    $aData['descp']=$clang->gT("Please enter the database settings you want to use for LimeSurvey:");
                    $aData['classesForStep']=array('off','off','off','on','off','off');
                    $aData['progressValue']=40;
                    $this->load->view('dbconfig_view',$aData);
                }
                else
                {
                $this->load->library('admin/sha256','sha256');
                $password_hash = $this->sha256->hashing($defaultpass);
                /**
                $insertdata = array(
                              'users_name' => $defaultuser,
                              'password' => $defaultpass,
                              'full_name' => $siteadminname,
                              'parent_id' => 0,
                              'lang' => $defaultlang,
                              'email' => $siteadminemail,
                              'create_survey' => 1,
                              'create_user' => 1,
                              'delete_user' => 1,
                              'superadmin' => 1,
                              'configurator' => 1,
                              'manage_template' => 1,
                              'manage_label' => 1
                );
                $this->db->insert('users', $insertdata);
                */
                    //finding dbtype and inserting new data
                    $dbtype = $this->session->userdata('dbtype');
                    switch ($dbtype){
                        case 'mysql':
                        case 'mysqli':
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'users` (`users_name`, `password`, `full_name`, `parent_id`, `lang` ,`email`, `create_survey`, `create_user` , `delete_user` , `superadmin` , `configurator` , `manage_template` , `manage_label`) VALUES (\''.$defaultuser.'\', \''.$password_hash.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            // Default global settings
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global` (`stg_name`,`stg_value`) VALUES (\'sitename\', \''.$sitename.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global` (`stg_name`,`stg_value`) VALUES (\'siteadminname\', \''.$siteadminname.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global` (`stg_name`,`stg_value`) VALUES (\'siteadminemail\', \''.$siteadminemail.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global` (`stg_name`,`stg_value`) VALUES (\'siteadminbounce\', \''.$siteadminemail.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global` (`stg_name`,`stg_value`) VALUES (\'defaultlang\', \''.$defaultlang.'\')');
                            break;
                        case 'mssql':
                        case 'odbc':
                            $connect->Execute('INSERT INTO ['.$this->session->userdata("dbprefix").'users] ([users_name], [password], [full_name], [parent_id], [lang] ,[email], [create_survey], [create_user] , [delete_user] , [superadmin] , [configurator] , [manage_template] , [manage_label]) VALUES (\''.$defaultuser.'\', \''.$password_hash.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            // Default global settings
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'sitename\', \''.$sitename.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'siteadminname\', \''.$siteadminname.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'siteadminemail\', \''.$siteadminemail.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'siteadminbounce\', \''.$siteadminemail.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'defaultlang\', \''.$defaultlang.'\')');
                            break;
                        case 'postgres':
                            $connect->Execute('INSERT INTO '.$this->session->userdata("dbprefix").'users (users_name, "password", full_name, parent_id, lang ,email, create_survey, create_user , delete_user , superadmin , configurator , manage_template , manage_label, htmleditormode) VALUES (\''.$defaultuser.'\', \''.$password_hash.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1,\'default\')');
                            // Default global settings
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global (stg_name,stg_value) VALUES (\'sitename\', \''.$sitename.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global (stg_name,stg_value) VALUES (\'siteadminname\', \''.$siteadminname.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'siteadminemail\', \''.$siteadminemail.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'siteadminbounce\', \''.$siteadminemail.'\')');
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'settings_global] ([stg_name],[stg_value]) VALUES (\'defaultlang\', \''.$defaultlang.'\')');
                            break;
                        default:
                            throw new Exception(sprintf('Unkown database type "%s".', $dbtype));
                    }
                    // only continue if we're error free otherwise setup is broken.
                    if ($error_number = $connect->ErrorNo())
                    {
                        throw new Exception(sprintf('Could not create admin user (%d): %s.', $error_number, $connect->ErrorMsg()));
                    }

                    // if successfully data is inserted, notify user to login and redirect to proper link
                    // code to force user to rename/delete installation directory will be in Admin_Controller
                    //header( "refresh:5;url=".site_url('admin'));
                    //echo 'You\'ll be redirected in about 5 secs. If not, click '.anchor('admin',"here").'.';

                    $this->session->set_userdata('deletedirectories' , TRUE);
                    $newdata = array();
                    //DELETE SAMPLE INSTALLER FILE. If we can't, notify user of the same.
                    $installer_file = $this->config->item('rootdir').'/tmp/sample_installer_file.txt';
                    if (is_writable($installer_file))
                    {
                        rename($installer_file, $installer_file.'.removed');
                        //show_error("Script of installation (\"".APPPATH . "controllers/installer.php\") is present. Remove/Rename it to proceed further.");
                        //exit();
                    }
                    else
                    {
                        $newdata['error'] = TRUE;
                    }

                    $newdata['title']=$clang->gT("Success!");
                    $newdata['descp']=$clang->gT("LimeSurvey has been installed successfully.");
                    $newdata['classesForStep']=array('off','off','off','off','off','off');
                    $newdata['progressValue']=100;
                    $newdata['user']=$defaultuser;
                    $newdata['pwd']=$defaultpass;

                    $this->load->view('installer/success_view',$newdata);

                 }

        }
        else
        {
            // if passwords don't match, redirect to proper link.
            $this->session->set_userdata('optconfig_message', sprintf('<b>%s</b>', $clang->gT("Passwords don't match.")));
            redirect(site_url('installer/loadOptView'));

        }
    }

    /**
     * Loads optional configuration screen.
     * @return
     */
    function loadOptView()
    {
        if (!$this->session->userdata('step3'))
        {
            redirect(site_url('installer/install/license'));
        }

        $this->_writeDatabaseFile();
        $this->_writeAutoloadfile();

        $clang = $this->limesurvey_lang;

        // confirmation message to be displayed
        $aData['confirmation']=$this->session->userdata('optconfig_message');
        $aData['title']=$clang->gT("Optional settings");
        $aData['descp']=$clang->gT("Optional settings to give you a head start");
        $aData['classesForStep']=array('off','off','off','off','off','on');
        $aData['progressValue']=80;

        $this->session->set_userdata('optional', TRUE);

        $this->load->helper('surveytranslator'); // FIXME for what?
        $this->load->view('installer/optconfig_view', $aData);

    }

    //function used to create different DB systems.
    /**
     * Installer::createdb()
     * Create database.
     * @return
     */
    function createdb()
    {
        // check status . to be called only when database don't exist else rdirect to proper link.
        $status=$this->session->userdata('databaseDontExist');
        if(!$status) {
            redirect(site_url('installer/install/license'));
        }

        $clang = $this->limesurvey_lang;

        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');
        $dbname = $this->session->userdata('dbname');
        $sAdodbType = $this->session->userdata('dbtype');
        $this->dbTasks->validateDatabaseType($sAdodbType);
        if ($sAdodbType=='postgre')
        {
            $sAdodbType='postgres';
        }

        $connect=ADONewConnection($sAdodbType);

        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd')))
        {
            $aData['errorConnection'] ='<b>'.$clang->gT('Try again! Connection with database failed.').'</b><br/><b>'.$clang->gT('Reason').'</b>:'.$connect->ErrorMsg().'<br/>';
            $aData['title']=$clang->gT("Database configuration");
            $aData['descp']=$clang->gT("Please enter the database settings you want to use for LimeSurvey:");
            $aData['classesForStep']=array('off','off','off','on','off','off');
            $aData['progressValue']=40;
            $this->load->view('dbconfig_view',$aData);
        }
        else
        {

            $values['adminoutputForm']='';
            switch ($this->session->userdata('dbtype'))
            {
                case 'mysqli':
                case 'mysql': $createDb=$connect->Execute("CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
                break;
                case 'mssql':
                case 'odbc':  $createDb=$connect->Execute("CREATE DATABASE [$dbname];");
                break;
                default: $createDb=$connect->Execute("CREATE DATABASE $dbname");
            }
            //$this->load->dbforge();
            if ($createDb) //Database has been successfully created
            {
                $connect->database = $dbname;
                $connect->Execute("USE DATABASE `$dbname`");
                $aStatusdata = array(
                    'populatedatabase'  => 'TRUE',
                    'databaseexist' => TRUE
                );
                $this->session->set_userdata($aStatusdata);
                $values['adminoutputText']="<tr bgcolor='#efefef'><td colspan='2' align='center'> <br />"
                    ."<strong><font class='successtitle'>\n"
                    .$clang->gT("Database has been created.")."</font></strong><br /><br />\n"
                    .$clang->gT("Please continue with populating the database.")."<br /><br />\n";
                $values['adminoutputForm'] = "<form method='post' action='".site_url('installer/populatedb')."'>"
                    ."<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='".$clang->gT("Populate Database")."' /></form>";

                $this->session->unset_userdata('databaseDontExist');
            }
            else
            {
                $aData['errorConnection'] ='<b>'.$clang->gT('Try again! Connection with database failed.').'</b>';
                $aData['title']=$clang->gT("Database configuration");
                $aData['descp']=$clang->gT("Please enter the database settings you want to use for LimeSurvey:");
                $aData['classesForStep']=array('off','off','off','on','off','off');
                $aData['progressValue']=40;
                $this->load->view('installer/dbconfig_view',$aData);

            }

            $values['title']=$clang->gT("Database settings");
            $values['descp']=$clang->gT("Database settings");
            $values['classesForStep']=array('off','off','off','off','on','off');
            $values['progressValue']=60;
            $this->load->view('installer/dbsettings_view',$values);
        }
    }

    //function used to populate database
    /**
     * Installer::populatedb()
     * Function to populate the database.
     * @return
     */
    function populatedb()
    {
        if (!$this->session->userdata('populatedatabase'))
        {
            redirect(site_url('installer/install/license'));
        }

        $clang = $this->limesurvey_lang;

        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');
        $dbname = $this->session->userdata('dbname');
        $sAdodbType = $this->session->userdata('dbtype');

        $this->dbTasks->validateDatabaseType($sAdodbType);

        $connect=ADONewConnection($sAdodbType);

        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
        {
            $aData['errorConnection'] ='<b>'.$clang->gT('Try again! Connection with database failed.').'</b><br/><b>'.$clang->gT("Reason").'</b>:'.$connect->ErrorMsg().'<br/>';
            $aData['title']=$clang->gT("Database configuration");
            $aData['descp']=$clang->gT("Please enter the database settings you want to use for LimeSurvey:");
            $aData['classesForStep']=array('off','off','off','on','off','off');
            $aData['progressValue']=40;
            $this->load->view('dbconfig_view',$aData);
        }
        else
        {
            $dbtype = $this->session->userdata('dbtype');
            switch ($dbtype)
            {
                case 'mysql':
                case 'mysqli':
                    $connect->Execute("ALTER DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
                    $sql_file = 'mysql';
                    break;
                case 'mssql':
                case 'odbc':
                    $sql_file = 'mssql';
                    break;
                case 'postgre':
                    if ($connect->pgVersion == '9')
                    {
                        $connect->execute("ALTER DATABASE {$dbname} SET bytea_output='escape';");
                    }
                    $sql_file = 'postgre';
                    break;
                default:
                    throw new Exception(sprintf('Unkown database type "%s".', $dbtype));
            }

            $sErrors = self::_modify_database($this->config->item('rootdir').'/installer/sql/create-'.$sql_file.'.sql');
            if ($sErrors=='')
            {
                //$data1['adminoutput'] = '';
                //$data1['adminoutput'] .= sprintf("Database `%s` has been successfully populated.",$dbname)."</font></strong></font><br /><br />\n";
                //$data1['adminoutput'] .= "<input type='submit' value='Main Admin Screen' onclick=''>";
                $this->session->set_userdata(array('tablesexist' => TRUE));
                $aStatusdata = array(
                            //'step2'  => 'TRUE',
                            'step3'  => TRUE,
                            'confirmation'=> sprintf($clang->gT("The %s database has been successfully populated."),$this->session->userdata('dbname'))
                            );

                $this->session->unset_userdata('populatedatabase');
                $this->session->set_userdata($aStatusdata);
                redirect(site_url('installer/install/2'));

            }
            else
            {
                $aStatusdata = array(
                            //'step2'  => 'TRUE',
                            'step3'  => TRUE,
                            'confirmation'=>$clang->gT('Database was populated but there were errors:').'<p>'.$sErrors
                            );
                $this->session->set_userdata(array('tablesexist' => TRUE));
                $this->session->set_userdata($aStatusdata);
                $this->session->unset_userdata('populatedatabase');
                redirect(site_url('installer/install/2'));
            }

        }


    }

    //function that ultimately populate database.
    /**
     * Installer::_modify_database()
     * Function that actually modify the database. Read $sqlfile and execute it.
     * @param string $sqlfile
     * @param string $sqlstring
     * @return  Empty string if everything was okay - otherwise the error messages
     */
    function _modify_database($sqlfile='', $sqlstring='')
    {
        $clang = $this->limesurvey_lang;
        $dbprefix = $this->session->userdata('dbprefix');
        $defaultuser = "admin";
        $defaultpass = "password";
        $siteadminemail = "your-email@example.net";
        $siteadminname = "Your Name";
        $defaultlang = "en";
        $databasetabletype = "myISAM";

        //checking DB Connection
        $db_config = array_flip(array('dbname', 'dblocation', 'dbuser', 'dbpwd', 'dbtype'));
        foreach($db_config as $key => &$value)
            $value = $this->session->userdata($key);
        unset($value);

        $connected = $this->dbTasks->testConnection($db_config);
        $connect = $this->dbTasks->getConnection();

        if (false == $connected)
        {
            $aData['errorConnection'] = sprintf('<b>'.$clang->gT('Try again! Connection with database failed.').'</b><br/><b>'.$clang->gT("Reason").'</b>:%s<br/>', $this->dbTasks->getConnection()->ErrorMsg());
            $aData['title'] = $clang->gT("Database configuration");
            $aData['descp'] = $clang->gT("Please enter the database settings you want to use for LimeSurvey:");
            $aData['classesForStep'] = array('off','off','off','on','off','off');
            $aData['progressValue'] = 40;
            $this->load->view('dbconfig_view', $aData);
        }
        else
        {
            $this->load->library('admin/sha256','sha256');
            $defaultpass=$this->sha256->hashing($defaultpass);
            $modifyoutput='';

            if (!empty($sqlfile)) {
                if (!is_readable($sqlfile)) {
                    echo '<p>'.$clang->gT('Tried to modify database, but "'. $sqlfile .'" doesn\'t exist!').'</p>';
                    return false;
                } else {
                    $lines = file($sqlfile);
                }
            } else {
                $sqlstring = trim($sqlstring);
                if ($sqlstring{strlen($sqlstring)-1} != ";") {
                $sqlstring .= ";"; // add it in if it's not there.
                }
                $lines[] = $sqlstring;
            }

            $command = '';


            foreach ($lines as $line) {
                $line = rtrim($line);
                $length = strlen($line);

                if ($length and $line[0] <> '#' and substr($line,0,2) <> '--') {
                    if (substr($line, $length-1, 1) == ';') {
                        $line = substr($line, 0, $length-1);   // strip ;
                        $command .= $line;
                        $command = str_replace('prefix_', $dbprefix, $command); // Table prefixes
                        $command = str_replace('$defaultuser', $defaultuser, $command);
                        $command = str_replace('$defaultpass', $defaultpass, $command);
                        $command = str_replace('$siteadminname', $siteadminname, $command);
                        $command = str_replace('$siteadminemail', $siteadminemail, $command);
                        $command = str_replace('$defaultlang', $defaultlang, $command);
                        $command = str_replace('$sessionname', 'ls'.self::_getRandomID().self::_getRandomID().self::_getRandomID(), $command);
                        $command = str_replace('$databasetabletype', $databasetabletype, $command);

                        if (! $connect->execute($command)) {  //Checked //FIXME check for errorNo() != 0 instead.
                            $command=htmlspecialchars($command);
                            $modifyoutput .="<br />"."Executing: ".$command."<font color='#FF0000'> Failed! Reason: ".$connect->ErrorMsg()."</font>";
                        }
                        else
                        {
                            $command=htmlspecialchars($command);
                        }
                        $command = '';
                    } else {
                        $command .= $line;
                    }
                }
            }

            return $modifyoutput;
        }

    }

    /**
     * Installer::_writeDatabaseFile()
     * Function to write given database settings in APPPATH.'application/config/database.php'
     * @return
     */
    function _writeDatabaseFile()
    {
        $clang = $this->limesurvey_lang;
        //write database.php if database exists and has been populated.
        if ($this->session->userdata('databaseexist') && $this->session->userdata('tablesexist'))
        {
            //write variables in database.php
            $this->load->helper('file');

            $dblocation = $this->session->userdata('dblocation');
            list($sDatabaseLocation, $sDatabasePort) = LS_Installer_DbTasks::getHostParts($dblocation);

            $dbvalues = array(
                        'hostname' => $sDatabaseLocation,
                        'username' => $this->session->userdata('dbuser'),
                        'password' => $this->session->userdata('dbpwd'),
                        'database' => $this->session->userdata('dbname'),
                        'dbdriver' => $this->session->userdata('dbtype'),
                        'dbprefix' => $this->session->userdata('dbprefix'),
                        'pconnect' => 'FALSE',
                        'db_debug' => 'TRUE',
                        'cache_on' => 'FALSE',
                        'cachedir' => '',
                        'char_set' => 'utf8',
                        'dbcollat' => 'utf8_unicode_ci',
                        'swap_pre' => '',
                        'autoinit' => 'TRUE',
                        'stricton' => 'FALSE',
                        'databasetabletype' => 'myISAM'
            );
            if (isset($sDatabasePort))
            {
                $dbvalues['port']=$sDatabasePort;
            }

            $dbdata = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); " ."\n"
                    ."/*"."\n"
                    ."| -------------------------------------------------------------------"."\n"
                    ."| DATABASE CONNECTIVITY SETTINGS"."\n"
                    ."| ------------------------------------------------------------------- "."\n"
                    ."| This file will contain the settings needed to access your database."."\n"
                    ."|"."\n"
                    ."| For complete instructions please consult the 'Database Connection' "."\n"
                    ."| page of the User Guide. "."\n"
                    ."|"."\n"
                    ."| ------------------------------------------------------------------- "."\n"
                    ."| EXPLANATION OF VARIABLES "."\n"
                    ."| ------------------------------------------------------------------- "."\n"
                    ."|"."\n"
                    ."|	['hostname'] The hostname of your database server. "."\n"
                    ."|	['username'] The username used to connect to the database "."\n"
                    ."|	['password'] The password used to connect to the database "."\n"
                    ."|	['database'] The name of the database you want to connect to "."\n"
                    ."|	['dbdriver'] The database type. ie: mysql.  Currently supported: "."\n"
                    ."|				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8 "."\n"
                    ."|	['dbprefix'] You can add an optional prefix, which will be added "."\n"
                    ."|				 to the table name when using the  Active Record class "."\n"
                    ."|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection "."\n"
                    ."|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed. "."\n"
                    ."|	['cache_on'] TRUE/FALSE - Enables/disables query caching "."\n"
                    ."|	['cachedir'] The path to the folder where cache files should be stored "."\n"
                    ."|	['char_set'] The character set used in communicating with the database "."\n"
                    ."|	['dbcollat'] The character collation used in communicating with the database "."\n"
                    ."|	['swap_pre'] A default table prefix that should be swapped with the dbprefix "."\n"
                    ."|	['autoinit'] Whether or not to automatically initialize the database. "."\n"
                    ."|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections "."\n"
                    ."|							- good for ensuring strict SQL while developing "."\n"
                    ."|"."\n"
                    .'| The $active_group'." variable lets you choose which connection group to "."\n"
                    ."| make active.  By default there is only one group (the 'default' group). "."\n"
                    ."|"."\n"
                    .'| The $active_record'." variables lets you determine whether or not to load "."\n"
                    ."| the active record class "."\n"
                    ."*/ "."\n"
                    .""."\n"
                    .'$active_group = \'default\'; '."\n"
                    .'$active_record = TRUE; '."\n"
                    .""."\n" ;
            foreach ($dbvalues as $key=>$value)
            {
                if ($value == 'FALSE' || $value == 'TRUE')
                {
                    $dbdata .= '$db[\'default\'][\'' . $key . '\'] = '. $value.';'."\n" ;
                }
                else
                {
                    $dbdata .= '$db[\'default\'][\'' . $key . '\'] = \''. $value.'\';'."\n" ;
                }

            }
            $dbdata .= '$config[\'dbdriver\'] = $db[\'default\'][\'dbdriver\']; ' . "\n" . "\n"
                   . "/* End of file database.php */ ". "\n"
                    . "/* Location: ./application/config/database.php */ ";

            if (is_writable(APPPATH . 'config/database.php'))
            {
                write_file(APPPATH . 'config/database.php', $dbdata);
            }
            else
            {
                header('refresh:5;url='.site_url("installer/install/0"));
                echo "<b>".$clang->gT("Directory is not writable")."</b><br/>";
                echo $clang->gT('You will be redirected in about 5 secs. If not, click ').anchor("installer/install/0",$clang->gT("here")).'.';
            }
        }
    }

    /**
     * Installer::_writeAutoloadfile()
     * Function to make database library get autoloaded in APPPATH.'application/config/autoload.php'
     * @return
     */
    function _writeAutoloadfile()
    {
        if ($this->session->userdata('databaseexist') && $this->session->userdata('tablesexist'))
        {

            $this->load->helper('file');

            $string = read_file(APPPATH . 'config/autoload.php');

            $string = str_replace('$autoload[\'libraries\'] = array();','$autoload[\'libraries\'] = array(\'database\');', $string);
            write_file(APPPATH . 'config/autoload.php', $string);
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
     * Quote values to be inserted later on.
     *
     * @param string $id
     * @param string $databasetype
     * @return
     */
    function _db_quote_id($id, $databasetype)
    {
        $this->dbTasks->validateDatabaseType($databasetype);

        // WE DONT HAVE nor USE other thing that alfanumeric characters in the field names
        //  $quote = $connect->nameQuote;
        //  return $quote.str_replace($quote,$quote.$quote,$id).$quote;

        switch ($databasetype)
        {
            case "mysqli" :
            case "mysql" :
                return "`".$id."`";
                break;
            case "mssql" :
            case "odbc" :
                return "[".$id."]";
                break;
            case "postgres":
                return "\"".$id."\"";
                break;
            default:
                return "`".$id."`";
        }
    }

    /**
     * Installer::_db_table_name()
     * Function to return complete database table name.
     * @param string $name
     * @param string $dbprefix
     * @param string $databasetype
     * @return
     */
    function _db_table_name($name, $dbprefix, $databasetype)
    {
        $this->dbTasks->validateDatabaseType($databasetype);

        return self::_db_quote_id($dbprefix.$name, $databasetype);
    }


}
