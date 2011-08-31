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
     * Installer::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
        parent::__construct();
        self::_checkInstallation();
        //need to write unique encryption key before we can use session data.
        self::_writeConfigfile();
	}

	/**
	 * Installer::index()
	 *
	 * @return
	 */
	function index()
    {
        // redirect to license screen
        redirect(site_url('installer/install/license'));
    }

    /**
     * Installer::_checkInstallation()
     * check if installation should proceed further or not. (Based on existance of 'sample_installer_file.txt' file.)
     * @return
     */
    function _checkInstallation()
    {
        if (!file_exists($this->config->item('rootdir').'/tmp/sample_installer_file.txt'))
        {
            show_error('Installation has been done already.');
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
         * check if directory exists and is writeable, returns via parameters by reference
         *
         * @param string $directory to check
         * @param string $data to manipulate
         * @param string $base key for data manipulation
         * @param string $keyError key for error data
         * @return bool result of check (that it is writeable which implies existance)
         */
        function check_PathWriteable($directory, &$data, $base, $keyError)
        {
            $result = false;
            $data[$base.'Present'] = 'Not Found';
            $data[$base.'Writable'] = '';
            if (is_dir($directory))
            {
                $data[$base.'Present'] = 'Found';
                if (is_writable($directory))
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

        //  version check
        if (version_compare(PHP_VERSION, '5.1.6', '<'))
            $bProceed = !$data['verror'] = true;

        // mbstring library check
        if (!check_PHPFunction('mb_convert_encoding', $data['mbstringPresent']))
            $bProceed = false;

        // ** directory permissions checking **

        // root directory file check
        if (!check_PathWriteable($this->config->item('rootdir'), $data, 'directory', 'derror') )
            $bProceed = false;

        // tmp directory check
        if (!check_PathWriteable($this->config->item('rootdir').'/tmp/', $data, 'tmpdir', 'terror') )
            $bProceed = false;

        // templates directory check
        if (!check_PathWriteable($this->config->item('rootdir').'/templates/', $data, 'templatedir', 'tperror') )
            $bProceed = false;

        //upload directory check
        if (!check_PathWriteable($this->config->item('rootdir').'/upload/', $data, 'uploaddir', 'uerror') )
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
     * Installer::install()
     * Install Limeurvey database and default settings.
     * @param integer $step
     * @return
     */
    function install($step=0)
    {

        switch($step){
            case 'license' :
            {

                // $aData array contain all the information required by view.
                $aData['title']='License';
                $aData['descp']='GNU General Public License:';
                $aData['classesForStep']=array('on','off','off','off','off');
                $aData['progressValue']=0;

                $this->load->view('installer/license_view',$aData);
                break;
            }
            // time to check a few writing permissions and optional settings.
            case 0:
            {
                //usual data required by view
                $aData['title']='Pre-installation check';
                $aData['descp']='Pre-installation check for LimeSurvey '.$this->config->item('versionnumber');
                $aData['classesForStep']=array('off','on','off','off','off');
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
                break;
            }
            // Configure database screen
            case 1:
            {
                //check if user has completed step2
                $status=$this->session->userdata('step2');
                if(!$status) {
                    redirect(site_url('installer/install/license'));
                }

                //usual data required by view
                $aData['title']="Database configuration";
                $aData['descp']="Connection settings:";
                $aData['classesForStep']=array("off","off","on","off","off");
                $aData['progressValue']=40;
                // errorConnection store text to be displayed if connection with DB fail
                $aData['errorConnection']="";

                //load form validation library and helpers necessary.
                $this->load->helper('form');
                $this->load->library('form_validation');


                //setting form validation rules.
                $this->form_validation->set_rules('dbType','Database Type','required');
                $this->form_validation->set_rules('dbLocation','Database Location','required');
                $this->form_validation->set_rules('dbName','Database Name','required');
                $this->form_validation->set_rules('dbUser','Database User','required');
                //$this->form_validation->set_rules('dbPwd','Password','required|matches[dbConfirmPwd]');
                $this->form_validation->set_rules('dbConfirmPwd','Confirm Password','matches[dbPwd]');
                $this->form_validation->set_rules('dbPrefix','Database Prefix','not required');

                //setting custom error message for confirm password field
                $this->form_validation->set_message('matches','Passwords do not match!');
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
                    $sDatabasePort='default';
                    $sAdodbType=$_POST['dbType'];
                    if ($sAdodbType=='postgre')
                    {
                        $sAdodbType='postgres';
                    }
                    $connect=ADONewConnection($sAdodbType);
                    if (strpos($_POST['dbLocation'],':')!==false)
                    {
                        $sDatabasePort=substr($_POST['dbLocation'],strpos($_POST['dbLocation'],':')+1);
                        $sDatabaseLocation=substr($_POST['dbLocation'],0,strpos($_POST['dbLocation'],':')-1);
                    }
                    else
                    {
                        $sDatabaseLocation=$_POST['dbLocation'];
                    }
                    $sADODBHost=$sDatabaseLocation;
                    //check connection
                    switch ($_POST['dbType'])
                    {
                        case 'postgre':
                        case 'mysqli':
                        case 'mysql': if ($sDatabasePort!='default') {$sADODBHost= $sDatabaseLocation.':'.$sDatabasePort;}
                        break;
                        case 'mssql': if ($sDatabasePort!='default') {$sADODBHost= $sDatabaseLocation.','.$sDatabasePort;}
                        break;
                        default: die("Unknown database type");
                    }

                    $bDBExists = false;
                    $bDBConnectionWorks = false;
                    // Now try connecting to the database
                    if (@$connect->Connect($sADODBHost, $_POST['dbUser'], $_POST['dbPwd'], $_POST['dbName']))
                    {
                        $bDBExists = true;
                        $bDBConnectionWorks = true;
                    }
                    else {
                        // If that doesn't work try connection without database-name
                        $connect->database = '';

                        if (!@$connect->Connect($dbhost, $_POST['dbUser'], $_POST['dbPwd']))
                        {
                            $bDBConnectionWorks=false;
                        }
                        else{
                            $bDBConnectionWorks=true;
                        }
                    }

                    //if connection with database fail
                    if (!$bDBConnectionWorks)
                    {
                        $aData['errorConnection'] ='<b>Try again! Connection with database failed.</b>';
                        $this->load->view('installer/dbconfig_view',$aData);
                    }
                    else
                    {

                        //saving the form data
                        $aStatusdata = array(
                            'dbname' => $this->input->post('dbName'),
                            'databasetype' => $this->input->post('dbType'),
                            'dblocation' => $this->input->post('dbLocation'),
                            'dbpwd' => $this->input->post('dbPwd'),
                            'dbuser' => $this->input->post('dbUser'),
                            'dbprefix' => $this->input->post('dbPrefix')
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
                                if (self::_db_quote_id($sTable,$this->input->post('dbType')) == self::_db_table_name($sTestTablename,$_POST['dbPrefix'],$_POST['dbType']))
                                {

                                    $bProceed=true;
                                    break;
                                }
                            }
                            if ($bProceed)
                            $bTablesDoNotExist = false;
                            else
                            $bTablesDoNotExist = true;
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
                            'optconfig_message' => 'The database you specified is up to date.',
                            'step3'  => TRUE
                            );
                            $this->session->set_userdata($aStatusdata);
                            redirect(site_url("installer/loadOptView"));
                        }



                        if ($_POST['dbType']=='mysql' || $_POST['dbType']=='mysqli') {
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
                        if ($_POST['dbType']=='odbc_mssql' || $_POST['dbType']=='odbtp' || $_POST['dbType']=='mssql_n' || $_POST['dbType']=='mssqlnative') {
                            @$connect->Execute('SET DATEFORMAT ymd;');     //Checked
                            @$connect->Execute('SET QUOTED_IDENTIFIER ON;');     //Checked
                        }

                        //$aData array won't work here. changing the name
                        $values['title']="Database settings";
                        $values['descp']="Database settings";
                        $values['classesForStep']=array("off","off","off","on","off");
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
                            ."<strong>"."Database doesn't exist!"."</strong><br /><br />\n"
                            ."The database you specified does not exist:<br /><br />\n<strong>".$this->input->post('dbName')."</strong><br /><br />\n"
                            ."LimeSurvey can attempt to create this database for you."."<br /><br />\n";

                            $values['adminoutputForm']="<form action='".site_url("installer/createdb")."' method='post'><input type='submit' value='"
                            ."Create Database"."' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' /></form>";


                        }
                        elseif ($dbexistsbutempty ) //&& !(returnglobal('createdbstep2')==$clang->gT("Populate Database")))
                        {
                            $aStatusdata = array(
                            'populatedatabase'  => 'TRUE'

                            );
                            $this->session->set_userdata($aStatusdata);

                            $connect->database = $this->input->post('dbName');
                            $connect->Execute("USE DATABASE `".$this->input->post('dbName')."`");
                            $values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td colspan='2' align='center'><br />\n"
                            ."<font class='successtitle'><strong>\n"
                            .sprintf('A database named "%s" already exists.',$this->input->post('dbName'))."</strong></font><br /><br />\n"
                            ."Do you want to populate that database now by creating the necessary tables?<br /><br />";

                            $values['adminoutputForm']= "<form method='post' action='".base_url()."index.php/installer/populatedb/'>"
                            ."<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='Populate Database' />"
                            ."</form>";
                        }
                        elseif (!$dbexistsbutempty)
                        {
                            //DB EXISTS, CHECK FOR APPROPRIATE UPGRADES
                            $connect->database = $this->input->post('dbName');
                            $connect->Execute("USE DATABASE `$databasename`");
                            $output=CheckForDBUpgrades();
                            if ($output== '') {$values['adminoutput'].='<br />LimeSurvey Database is up to date. No action needed';}
                            else {$values['adminoutput'].=$output;}
                            $values['adminoutput'].="<br />Please ".anchor("admin","log in.");

                        }



                        $this->load->view('installer/dbsettings_view',$values);
                    }
                }

                break;
            }
            // Optional settings screen
            case 2:
            {
                //check status whether user is done populating DB,
                $status1=$this->session->userdata('step3');
                $status2=$this->session->userdata('databaseDontExist');
                if(!$status1) {

                    redirect(site_url('installer/install/license'));
                }

                self::_writeDatabaseFile();
                self::_writeAutoloadfile();
                //self::_writeConfigfile();
                // confirmation message to be displayed
                $aData['confirmation']= sprintf("Database <b>%s</b> has been successfully populated.",$this->session->userdata('dbname'));
                $aData['title']="Optional settings";
                $aData['descp']="Optional settings to give you a head start";
                $aData['classesForStep']=array("off","off","off","off","on");
                $aData['progressValue']=80;

                $aStatusdata = array(
                            'optional'  => 'TRUE'

                );
                $this->load->helper('surveytranslator');
                $this->session->set_userdata($aStatusdata);
                $this->load->view('installer/optconfig_view',$aData);
                break;
            }

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

        $status=$this->session->userdata('optional');
        if(!$status) {
            redirect(site_url('installer/install/license'));
        }

        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');

        //check if passwords match , input class take care of any xss filetring or sql injection
        if ($this->input->post('adminLoginPwd') == $this->input->post('confirmPwd'))
        {

            $defaultuser = $this->input->post('adminLoginName');
            $defaultpass = $this->input->post('adminLoginPwd');
            $siteadminname = $this->input->post('siteName');
            $defaultlang = $this->input->post('surveylang');
            $siteadminemail = $this->input->post('adminEmail');

            //if any of the field was left blank, replace it with default.
            //FIXME doing the following leads to a problem. this action could make use of the
            //      form helper like the database step (@see install())
            //      problem: two empty passwords are accepted.
            if ($defaultuser=='')
            $defaultuser  = "admin";
            if ($defaultpass=='')
            $defaultpass  = "password";
            if ($siteadminname=='')
            $siteadminname  = "Your Name";
            if ($defaultlang=='')
            $defaultlang  = "en";
            if ($siteadminemail=='')
            $siteadminemail  = "your-email@example.net";

            $adminpwd = $defaultpass;


            $dbname = $this->session->userdata('dbname');
            $sAdodbType = $this->session->userdata('databasetype');
            if ($sAdodbType=='postgre')
            {
                $sAdodbType='postgres';
            }
            $connect=ADONewConnection($sAdodbType);

            //require_once($rootdir."/classes/core/sha256.php");
            //require_once($rootdir."/classes/core/surveytranslator.php");
            //require_once($rootdir."/classes/core/sanitize.php");

                //checking DB Connection
                if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
                {
                    $aData['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
                    $aData['title']="Database configuration";
                    $aData['descp']="Connection settings:";
                    $aData['classesForStep']=array("off","off","on","off","off");
                    $aData['progressValue']=40;
                    $this->load->view('dbconfig_view',$aData);
                }
                else
                {
                $this->load->library('admin/sha256','sha256');
                $defaultpass=$this->sha256->hashing($defaultpass);
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
                    $databasetype=$this->session->userdata('databasetype');
                    switch ($databasetype){
                        case 'mysql':
                        case 'mysqli':
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'users` (`users_name`, `password`, `full_name`, `parent_id`, `lang` ,`email`, `create_survey`, `create_user` , `delete_user` , `superadmin` , `configurator` , `manage_template` , `manage_label`) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            break;
                        case 'mssql':
                        case 'odbc':
                            $connect->Execute('INSERT INTO ['.$this->session->userdata("dbprefix").'users] ([users_name], [password], [full_name], [parent_id], [lang] ,[email], [create_survey], [create_user] , [delete_user] , [superadmin] , [configurator] , [manage_template] , [manage_label]) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            break;
                        case 'postgres':
                            $connect->Execute('INSERT INTO '.$this->session->userdata("dbprefix").'users (users_name, "password", full_name, parent_id, lang ,email, create_survey, create_user , delete_user , superadmin , configurator , manage_template , manage_label, htmleditormode) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1,\'default\')');
                            break;
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

                    $newdata['title']="Success!";
                    $newdata['descp']="LimeSurvey has been installed successfully.";
                    $newdata['classesForStep']=array("off","off","off","off","off");
                    $newdata['progressValue']=100;
                    $newdata['user']=$defaultuser;
                    $newdata['pwd']=$adminpwd;

                    $this->load->view('installer/success_view',$newdata);

                 }

        }
        else
        {
            // if passwords don't match, redirect to proper link.
            $aStatusdata = array(
                            //'step2'  => 'TRUE',
                            'optconfig_message' => 'Password don\'t match.'
                            );
            $this->session->set_userdata($aStatusdata);
            redirect(site_url('installer/loadOptView'));

        }




    }
    // this function loads optconfig_view with proper confirmation message.
    /**
     * Installer::loadOptView()
     * Loads optional configuration screen.
     * @return
     */
    function loadOptView()
    {
        $status1=$this->session->userdata('step3');
        if(!$status1) {

            redirect(site_url('installer/install/license'));
        }

        self::_writeDatabaseFile();
        self::_writeAutoloadfile();

        $this->load->helper('surveytranslator');
        $aData['confirmation']="<b>".$this->session->userdata('optconfig_message')."</b><br/>";
        $aData['title']="Optional settings";
        $aData['descp']="Optional settings to give you a head start";
        $aData['classesForStep']=array("off","off","off","off","on");
        $aData['progressValue']=80;

        $aStatusdata = array(
            'optional'  => TRUE

        );
        $this->session->set_userdata($aStatusdata);
        $this->load->view('installer/optconfig_view',$aData);

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

        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');
        $dbname = $this->session->userdata('dbname');
        $sAdodbType = $this->session->userdata('databasetype');
        if ($sAdodbType=='postgre')
        {
            $sAdodbType='postgres';
        }

        $connect=ADONewConnection($sAdodbType);

        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd')))
        {
            $aData['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
            $aData['title']="Database configuration";
            $aData['descp']="Connection settings:";
            $aData['classesForStep']=array("off","off","on","off","off");
            $aData['progressValue']=40;
            $this->load->view('dbconfig_view',$aData);
        }
        else
        {

            $values['adminoutputForm']='';
            switch ($this->session->userdata('databasetype'))
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
                                'populatedatabase'  => 'TRUE'

                                );
                $this->session->set_userdata($aStatusdata);
                $this->session->set_userdata(array('databaseexist' => TRUE));
                $values['adminoutputText']="<tr bgcolor='#efefef'><td colspan='2' align='center'> <br />"
                ."<strong><font class='successtitle'>\n"
                ."Database has been created.</font></strong><br /><br />\n"
                ."Please click below to populate the database<br /><br />\n"
                ."<form method='post' action='".site_url('installer/populatedb')."'>"
                ."<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='Populate Database' /></form>";

                $this->session->unset_userdata('databaseDontExist');
            }
            else
            {
                $aData['errorConnection'] ='<b>Try again! Connection with database failed.</b>';
                $aData['title']="Database configuration";
                $aData['descp']="Connection settings:";
                $aData['classesForStep']=array("off","off","on","off","off");
                $aData['progressValue']=40;
                $this->load->view('installer/dbconfig_view',$aData);

            }

            $values['title']="Database settings";
            $values['descp']="Database settings";
            $values['classesForStep']=array("off","off","off","on","off");
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
        $status1=$this->session->userdata('populatedatabase');

        if(!$status1) {
            redirect(site_url('installer/install/license'));
        }

        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');
        $dbname = $this->session->userdata('dbname');

        $sAdodbType=$this->session->userdata('databasetype');
        if ($sAdodbType=='postgre')
        {
            $sAdodbType='postgres';
        }

        $connect=ADONewConnection($sAdodbType);


        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
        {
            $aData['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
            $aData['title']="Database configuration";
            $aData['descp']="Connection settings:";
            $aData['classesForStep']=array("off","off","on","off","off");
            $aData['progressValue']=40;
            $this->load->view('dbconfig_view',$aData);
        }
        else
        {

            $createdbtype=$this->session->userdata('databasetype');

            if ($createdbtype=='mysql' || $createdbtype=='mysqli') {
                $connect->Execute("ALTER DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
                $createdbtype='mysql';
            }
            if ($createdbtype=='mssql' || $createdbtype=='odbc') $createdbtype='mssql';
            if($createdbtype=='postgre' && $connect->pgVersion=='9')
            {
                $connect->execute("ALTER DATABASE {$dbname} SET bytea_output='escape';");
            }
            $sErrors=self::_modify_database($this->config->item('rootdir').'/installer/sql/create-'.$createdbtype.'.sql');
            if ($sErrors=='')
            {
                //$data1['adminoutput'] = '';
                //$data1['adminoutput'] .= sprintf("Database `%s` has been successfully populated.",$dbname)."</font></strong></font><br /><br />\n";
                //$data1['adminoutput'] .= "<input type='submit' value='Main Admin Screen' onclick=''>";
                $this->session->set_userdata(array('tablesexist' => TRUE));
                $aStatusdata = array(
                            //'step2'  => 'TRUE',
                            'step3'  => TRUE,
                            'confirmation'=> sprintf("Database <b>%s</b> has been successfully populated.",$this->session->userdata('dbname'))
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
                            'confirmation'=>'Database was populated but there were errors:<p>'.$sErrors
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
        $dbprefix=$this->session->userdata('dbprefix');
        $defaultuser="admin";
        $defaultpass="password";
        $siteadminemail="your-email@example.net";
        $siteadminname="Your Name";
        $defaultlang="en";
        //global $codeString;


        //global $modifyoutput;
        $databasetabletype="myISAM";


        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');
        $dbname = $this->session->userdata('dbname');
        $sAdodbType=$this->session->userdata('databasetype');
        if ($sAdodbType=='postgre')
        {
            $sAdodbType='postgres';
        }
        $connect=ADONewConnection($sAdodbType);


        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
        {
            $aData['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
            $aData['title']="Database configuration";
            $aData['descp']="Connection settings:";
            $aData['classesForStep']=array("off","off","on","off","off");
            $aData['progressValue']=40;
            $this->load->view('dbconfig_view',$aData);
        }
        else
        {
            //require_once($rootdir."/classes/core/sha256.php");
            //require_once($rootdir."/classes/core/surveytranslator.php");
            //require_once($rootdir."/classes/core/sanitize.php");
            $this->load->library('admin/sha256','sha256');
            $defaultpass=$this->sha256->hashing($defaultpass);
            $modifyoutput='';
            /**
            $this->load->dbforge();


            $this->dbforge->add_field("qid int(11) NOT NULL DEFAULT '0'");
            $this->dbforge->add_field("code varchar(5) NOT NULL default ''");
            $this->dbforge->add_field("answer text NOT NULL");
            $this->dbforge->add_field("assessment_value int(11) NOT NULL default '0'");
            $this->dbforge->add_field("sortorder int(11) NOT NULL");
            $this->dbforge->add_field("language varchar(20) default 'en'");
            $this->dbforge->add_field("scale_id tinyint NOT NULL default '0'");
            $this->dbforge->add_field("assessment_value int(11) NOT NULL default '0'");

            */


            if (!empty($sqlfile)) {
                if (!is_readable($sqlfile)) {
                    $success = false;
                    echo '<p>Tried to modify database, but "'. $sqlfile .'" doesn\'t exist!</p>';
                    return $success;
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

                        if (! $connect->execute($command)) {  //Checked
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
        //write database.php if database exists and has been populated.
                if ($this->session->userdata('databaseexist') && $this->session->userdata('tablesexist'))
                {
                    //write variables in database.php
                            $this->load->helper('file');

                            if (strpos($this->session->userdata('dblocation'),':')!==false)
                            {
                                $sDatabasePort=substr($_POST['dbLocation'],strpos($this->session->userdata('dblocation'),':')+1);
                                $sDatabaseLocation=substr($_POST['dbLocation'],0,strpos($this->session->userdata('dblocation'),':')-1);
                            }
                            else
                            {
                                $sDatabaseLocation=$this->session->userdata('dblocation');
                            }


                            $dbvalues = array(
                                        'hostname' => $sDatabaseLocation,
                                        'username' => $this->session->userdata('dbuser'),
                                        'password' => $this->session->userdata('dbpwd'),
                                        'database' => $this->session->userdata('dbname'),
                                        'dbdriver' => $this->session->userdata('databasetype'),
                                        'dbprefix' => $this->session->userdata('dbprefix'),
                                        'pconnect' => 'FALSE',
                                        'db_debug' => 'TRUE',
                                        'cache_on' => 'FALSE',
                                        'cachedir' => '',
                                        'char_set' => 'utf8',
                                        'dbcollat' => 'utf8_general_ci',
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
                                    ." | -------------------------------------------------------------------"."\n"
                                    ." | DATABASE CONNECTIVITY SETTINGS"."\n"
                                    ." | ------------------------------------------------------------------- "."\n"
                                    ." | This file will contain the settings needed to access your database."."\n"
                                    ." | "."\n"
                                    ." | For complete instructions please consult the 'Database Connection' "."\n"
                                    ." | page of the User Guide. "."\n"
                                    ." | "."\n"
                                    ." | ------------------------------------------------------------------- "."\n"
                                    ." | EXPLANATION OF VARIABLES "."\n"
                                    ." | ------------------------------------------------------------------- "."\n"
                                    ." | "."\n"
                                    ." |	['hostname'] The hostname of your database server. "."\n"
                                    ." |	['username'] The username used to connect to the database "."\n"
                                    ." |	['password'] The password used to connect to the database "."\n"
                                    ." |	['database'] The name of the database you want to connect to "."\n"
                                    ." |	['dbdriver'] The database type. ie: mysql.  Currently supported: "."\n"
                                    ." 				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8 "."\n"
                                    ." |	['dbprefix'] You can add an optional prefix, which will be added "."\n"
                                    ." |				 to the table name when using the  Active Record class "."\n"
                                    ." |	['pconnect'] TRUE/FALSE - Whether to use a persistent connection "."\n"
                                    ." |	['db_debug'] TRUE/FALSE - Whether database errors should be displayed. "."\n"
                                    ." |	['cache_on'] TRUE/FALSE - Enables/disables query caching "."\n"
                                    ." |	['cachedir'] The path to the folder where cache files should be stored "."\n"
                                    ." |	['char_set'] The character set used in communicating with the database "."\n"
                                    ." |	['dbcollat'] The character collation used in communicating with the database "."\n"
                                    ." |	['swap_pre'] A default table prefix that should be swapped with the dbprefix "."\n"
                                    ." |	['autoinit'] Whether or not to automatically initialize the database. "."\n"
                                    ." |	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections "."\n"
                                    ." |							- good for ensuring strict SQL while developing "."\n"
                                    ." | "."\n"
                                    .' | The $active_group'." variable lets you choose which connection group to "."\n"
                                    ." | make active.  By default there is only one group (the 'default' group). "."\n"
                                    ." | "."\n"
                                    .' | The $active_record'." variables lets you determine whether or not to load "."\n"
                                    ." | the active record class "."\n"
                                    ." */ "."\n"
                                    ." "."\n"
                                    .'$active_group = \'default\'; '."\n"
                                    .'$active_record = TRUE; '."\n"
                                    ." "."\n" ;
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
                                echo "<b>directory not writable</b><br/>";
                                echo 'You\'ll be redirected in about 5 secs. If not, click '.anchor("installer/install/0","here").'.';
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
     * Installer::_writeConfigfile()
     * Function to write session encryption key in APPPATH.'application/config/config.php'
     * @return
     */
    function _writeConfigfile()
    {
        if ($this->session->userdata('databaseexist') && $this->session->userdata('tablesexist'))
        {

            $this->load->helper('file');

            $string = read_file(APPPATH . 'config/config.php');
            $this->load->helper('string');
            $string = str_replace('$config[\'encryption_key\'] = \'encryption_key\';','$config[\'encryption_key\'] = \''.random_string("unique").'\';', $string);
            write_file(APPPATH . 'config/config.php', $string);
        }

    }



    /**
     * Installer::_getRandomID()
     * Create a random survey ID - based on code from Ken Lyle
     * @return
     */
    function _getRandomID()
    {        // Create a random survey ID - based on code from Ken Lyle
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
     * Installer::_db_quote_id()
     * Function to quote values to be inserted later on.
     * @param mixed $id
     * @param mixed $databasetype
     * @return
     */
    function _db_quote_id($id,$databasetype)
    {

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
            case "postgre":
                return "\"".$id."\"";
                break;
            default:
                return "`".$id."`";
        }
    }

    /**
     * Installer::_db_table_name()
     * Function to return complete database table name.
     * @param mixed $name
     * @param mixed $dbprefix
     * @param mixed $databasetype
     * @return
     */
    function _db_table_name($name,$dbprefix,$databasetype)
    {
        return self::_db_quote_id($dbprefix.$name,$databasetype);
    }


}