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
            show_error("Installation has been done already.");
            exit();
        }

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
            case "license" :
            {

                // $data array contain all the information required by view.
                $data['title']="License";
                $data['descp']="GNU General Public License:";
                $data['classesForStep']=array("on","off","off","off","off");
                $data['progressValue']=0;

                $this->load->view('installer/license_view',$data);
                break;
            }
            // time to check few writing permissions and optional settings.
            case 0:
            {
                //check user checked the checkbox. if yes,save the status
                if ($this->input->post('accept'))
                {

                    $statusdata = array(
                        'license'  => 'TRUE'
                    );
                    $this->session->set_userdata($statusdata);
                }
                //if the staus is false, redirect to license view
                $status=$this->session->userdata('license');
                if(!$status) {
                redirect(site_url('installer/install/license'));
                }
                //usual data required by view
                $data['title']="Pre-installation check";
                $data['descp']="Pre-installation check for LimeSurvey ".$this->config->item('versionnumber');
                $data['classesForStep']=array("off","on","off","off","off");
                $data['progressValue']=20;
                $data['phpVersion'] = phpversion();
                // variable storing next button link.initially null
                $data['next']="";

                //proceed variable check if all requirements are true. If any of them is false, proceed is set false.
                $proceed = true; //lets be optimistic!

                //  version check
                $ver = explode( '.', PHP_VERSION );
                $ver_num = $ver[0] . $ver[1] . $ver[2];

                if ($ver_num < 500)
                {
                    $proceed=false;
                }


                //mbstring library check
                if ( function_exists('mb_convert_encoding') )
                $data['mbstringPresent'] = "<img src=\"".base_url()."installer/images/tick-right.gif\" />";
                else
                {
                    $data['mbstringPresent'] = "<img src=\"".base_url()."installer/images/tick-wrong.png\" />";
                    $proceed=false;
                }

                //directory permissions checking
                // root directory file check

                if (file_exists($this->config->item('rootdir')))
                {
                    $data['directoryPresent'] = "Found";
                    //echo octal_permissions(fileperms($rootdir)) ;
                    //if( octal_permissions(fileperms($rootdir))>=666 )
                    if (is_writable($this->config->item('rootdir')))
                    $data['directoryWritable'] = "Writable";
                    else
                    {
                        $data['directoryWritable'] = "Unwritable";
                        $data['derror'] = true;
                        $proceed=false;
                    }

                }
                else
                {
                    $data['directoryPresent'] = "Not Found";
                    $data['directoryWritable'] = "";
                    $data['derror'] = true;
                    $proceed=false;
                }
                // tmp directory check

                if (file_exists($this->config->item('rootdir').'/tmp/'))
                {
                    $data['tmpdirPresent'] = "Found";
                    //echo octal_permissions(fileperms($rootdir."/tmp/"));
                    //if( octal_permissions(fileperms($rootdir."/tmp/"))>=666 )
                    if (is_writable($this->config->item('rootdir').'/tmp/'))
                    $data['tmpdirWritable'] = "Writable";
                    else
                    {
                        $data['tmpdirWritable'] = "Unwritable";
                        $data['terror'] = true;
                        $proceed=false;
                    }

                }
                else
                {
                    $data['tmpdirPresent'] = "Not Found";
                    $data['tmpdirWritable'] = "";
                    $data['terror'] = true;
                    $proceed=false;
                }
                // templates directory check

                if (file_exists($this->config->item('rootdir').'/templates/'))
                {
                    $data['templatedirPresent'] = "Found";
                    //echo octal_permissions(fileperms($rootdir."/template/"));
                    //if( octal_permissions(fileperms($rootdir."/template/"))>=666 )
                    if (is_writable($this->config->item('rootdir').'/templates/'))
                    $data['templatedirWritable'] = "Writable";
                    else
                    {
                        $data['templatedirWritable'] = "Unwritable";
                        $data['tperror'] = true;
                        $proceed=false;
                    }

                }
                else
                {
                    $data['templatedirPresent'] = "Not Found";
                    $data['templatedirWritable'] = "";
                    $data['tperror'] = true;
                    $proceed=false;
                }
                //upload directory check

                if (file_exists($this->config->item('rootdir').'/upload/'))
                {
                    $data['uploaddirPresent'] = "Found";
                    //echo octal_permissions(fileperms($rootdir."/upload/"));
                    //if( octal_permissions(fileperms($rootdir."/upload/"))>=666 )
                    if (is_writable($this->config->item('rootdir').'/upload/'))
                    $data['uploaddirWritable'] = "Writable";
                    else
                    {
                        $data['uploaddirWritable'] = "Unwritable";
                        $data['uerror'] = true;
                        $proceed=false;
                    }

                }
                else
                {
                    $data['uploaddirPresent'] = "Not Found";
                    $data['uploaddirWritable'] = "";
                    $data['uerror'] = true;
                    $proceed=false;
                }

                //optional settings check
                //gd library check
                $gdArray = gd_info();

                if ( $gdArray["FreeType Support"] )
                $data['gdPresent'] = "<img src=\"".base_url()."installer/images/tick-right.gif\" />";
                else
                $data['gdPresent'] = "<img src=\"".base_url()."installer/images/tick-wrong.png\" />";
                //ldap library check
                if ( function_exists('ldap_connect') )
                $data['ldapPresent'] = "<img src=\"".base_url()."installer/images/tick-right.gif\" />";
                else
                $data['ldapPresent'] = "<img src=\"".base_url()."installer/images/tick-wrong.png\" />";
                //php zip library check
                if ( function_exists('zip_open') )
                $data['zipPresent'] = "<img src=\"".base_url()."installer/images/tick-right.gif\" />";
                else
                $data['zipPresent'] = "<img src=\"".base_url()."installer/images/tick-wrong.png\" />";
                //zlib php library check
                if ( function_exists('zlib_get_coding_type') )
                $data['zlibPresent'] = "<img src=\"".base_url()."installer/images/tick-right.gif\" />";
                else
                $data['zlibPresent'] = "<img src=\"".base_url()."installer/images/tick-wrong.png\" />";








                //after all check, if flag value is true, show next button and sabe step2 status.
                if ($proceed)
                {
                    $data['next']=TRUE;
                    $statusdata = array(
                        'step2'  => 'TRUE'
                    );
                    $this->session->set_userdata($statusdata);
                }

                $this->load->view('installer/precheck_view',$data);
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
                $data['title']="Database configuration";
                $data['descp']="Connection settings:";
                $data['classesForStep']=array("off","off","on","off","off");
                $data['progressValue']=40;
                // errorConnection store text to be displayed if connection with DB fail
                $data['errorConnection']="";

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
                    $this->load->view('installer/dbconfig_view',$data);
                }
                else
                {

                    //to establish connection withh different DB.
                    require_once(APPPATH.'third_party/adodb/adodb.inc.php');
                    //lets assume


                    //write variables in database.php
                    //$this->load->helper('file');
                    /**
                    $dbvalues = array(
                                'hostname' => $this->input->post('dbLocation'),
                                'username' => $this->input->post('dbUser'),
                                'password' => $this->input->post('dbPwd'),
                                'database' => $this->input->post('dbName'),
                                'dbdriver' => $this->input->post('dbType'),
                                'dbprefix' => $this->input->post('dbPrefix'),
                                'pconnect' => 'TRUE',
                                'db_debug' => 'TRUE',
                                'cache_on' => 'FALSE',
                                'cachedir' => '',
                                'char_set' => 'utf8',
                                'dbcollat' => 'utf8_general_ci',
                                'swap_pre' => '',
                                'autoinit' => 'TRUE',
                                'stricton' => 'FALSE',
                                'port' => 'default',
                                'databasetabletype' => 'myISAM'

                    );

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
                            ." | the active record class "."\n" */
                          //  ." */ "."\n"
                           /** ." "."\n"
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
                    $dbdata .= '$config[\'dbdriver\'] = $db[\'default\'][\'dbdriver\']; ' . "\n" . "\n" */
                    //       . "/* End of file database.php */ ". "\n"
                    //        . "/* Location: ./application/config/database.php */ ";
                    /**
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
                    */
                    $_POST = $this->input->post();
                    $databaseport='default';
                    $databasepersistent=true;
                    $dbhost='';
                    $connect=ADONewConnection($_POST['dbType']);

                    //check connection
                    /**
                    $dsn = $this->input->post('dbType').'://'.$_POST['dbUser'].":".$_POST['dbPwd'].'@'.$_POST['dbLocation'].'/'.$_POST['dbName']; //.'?char_set=utf8&dbcollat=utf8_general_ci&cache_on=true&cachedir=\'\'';
                    //'dbdriver://username:password@hostname/database?char_set=utf8&dbcollat=utf8_general_ci&cache_on=true&cachedir=/path/to/cache';
echo "hello<br/>".$dsn;
                    var_dump($this->load->database($dsn));
                    exit(); */
                    switch ($_POST['dbType'])
                    {
                        case "postgres":
                        case "mysqli":
                        case "mysql": if ($databaseport!="default") {$dbhost= $_POST['dbLocation'].":$databaseport";}
                        else {$dbhost=$_POST['dbLocation'];}
                        break;
                        case "mssql_n":
                        case "mssqlnative":
                        case "mssql": if ($databaseport!="default") {$dbhost= $_POST['dbLocation'].",$databaseport";}
                        else {$dbhost=$_POST['dbLocation'];}
                        break;
                        case "odbc": $dbhost="Driver={SQL Server};Server=".$_POST['dbLocation'].";Database=".$_POST['dbName'];
                        break;

                        default: safe_die("Unknown database type");
                    }
                    /**
                    if (!$connection_db)
                    {
                        //usual data required by view
                        $newdata['title']="Database configuration";
                        $newdata['descp']="Connection settings:";
                        $newdata['classesForStep']=array("off","off","on","off","off");
                        $newdata['progressValue']=40;
                        $newdata['errorConnection'] ='<b>Try again! Connection with database failed.</b>';
                        $this->load->view('installer/dbconfig_view',$newdata);

                    }
                    else
                    {

                    }

                    $database_exists =false;
                    $_POST = $this->input->post();
                    switch ($this->input->post('dbType'))
                    {
                        case "postgres": $database_exists = @pg_connect("host=".$_POST['dbLocation']." port=$databaseport dbname=".$_POST['dbName']);
                        break;
                        case "mysqli":
                        case "mysql": if ($databaseport!="default") {$dbhost= $_POST['dbLocation'].":$databaseport";}
                        else {$dbhost=$_POST['dbLocation'];}
                        $link = @mysql_connect($dbhost, $_POST['dbUser'], $_POST['dbPwd']);
                        $database_exists = mysql_select_db($_POST['dbName'],$link);
                        break;
                        case "mssql_n":
                        case "mssqlnative":
                        case "mssql": if ($databaseport!="default") {$dbhost= $_POST['dbLocation'].",$databaseport";}
                        else {$dbhost=$_POST['dbLocation'];}
                        $link = @mssql_connect($dbhost, $_POST['dbUser'], $_POST['dbPwd']);
                        $database_exists = mssql_select_db($_POST['dbName'], $link);
                        break;
                        case "odbc_mssql": $dbhost="Driver={SQL Server};Server=".$_POST['dbLocation'].";Database=".$_POST['dbName'];
                        $database_exists = @odbc_connect($dbhost, $_POST['dbUser'], $_POST['dbPwd']);
                        break;

                        default: safe_die("Unknown database type");
                    }

                    //$this->load->dbutil();
                    //$database_exists = $this->dbutil->database_exists($this->input->post('dbName'));

                    if (!$database_exists)
                    {

                            $statusdata = array(
                            'databaseDontExist'  => 'TRUE'
                            );
                            $this->session->set_userdata($statusdata);

                            $values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td align='center'>\n"
                            ."<strong>"."Database doesn't exist!"."</strong><br /><br />\n"
                            ."The database you specified does not exist."."<br />\n"
                            ."LimeSurvey can attempt to create this database for you."."<br /><br />\n"
                            ."Your selected database name is:"."<strong>".$this->input->post('dbName')."</strong><br />\n"
                            ."</center>\n" ;

                            $values['adminoutputForm']="<form action='".base_url()."index.php/installer/createdb/' method='post'><input type='submit' value='"
                            ."Create Database"."' /></form>";
                    }
                    */


                    $database_exists =false;
                    $connection_db = false;
                    // Now try connecting to the database
                    if ($databasepersistent==true)
                    {
                        if (@$connect->PConnect($dbhost, $_POST['dbUser'], $_POST['dbPwd'], $_POST['dbName']))
                        {
                            $database_exists = TRUE;
                            $connection_db=true;
                        }
                        else {
                            // If that doesnt work try connection without database-name
                            $connect->database = '';
                            if (!@$connect->PConnect($dbhost, $_POST['dbUser'], $_POST['dbPwd']))
                            {
                                $connection_db=false;

                            }
                            else{
                                $connection_db=true;
                            }

                        }
                    }
                    else
                    {
                        if (@$connect->Connect($dbhost, $_POST['dbUser'], $_POST['dbPwd'], $_POST['dbName']))
                        {
                            $database_exists = TRUE;
                            $connection_db=true;

                        }
                        else {
                            // If that doesnt work try connection without database-name
                            $connect->database = '';

                            if (!@$connect->Connect($dbhost, $_POST['dbUser'], $_POST['dbPwd']))
                            {
                                $connection_db=false;
                            }
                            else{
                                $connection_db=true;
                            }
                        }
                    }




                    //if connection with database fail
                    if (!$connection_db)
                    {
                        $data['errorConnection'] ='<b>Try again! Connection with database failed.</b>';
                        $this->load->view('installer/dbconfig_view',$data);
                    }
                    else
                    {

                        //saving the form data
                        $statusdata = array(
                            //'step2'  => 'TRUE',

                            'dbname' => $this->input->post('dbName'),
                            'databasetype' => $this->input->post('dbType'),
                            'dblocation' => $dbhost,
                            'dbpwd' => $this->input->post('dbPwd'),
                            'dbuser' => $this->input->post('dbUser'),
                            'dbprefix' => $this->input->post('dbPrefix')
                            );
                        $this->session->set_userdata($statusdata);

                         //check if table exists or not

                        //static $tablelist;
                        $tablename = 'surveys';
                        $tablesdontexist=false; //!$this->db->table_exists($tablename); //false;

                        if (!isset($tablelist)) $tablelist = $connect->MetaTables();

                        if ($tablelist==false)
                        {
                            $tablesdontexist = true;

                        }
                        else
                        {
                            $proceed=false;

                            foreach ($tablelist as $tbl)
                            {
                                if (self::_db_quote_id($tbl,$this->input->post('dbType')) == self::_db_table_name($tablename,$_POST['dbPrefix'],$_POST['dbType']))
                                {

                                    $proceed=true;
                                    break;
                                }
                            }
                            if ($proceed)
                            $tablesdontexist = false;
                            else
                            $tablesdontexist = true;
                        }


                        // AdoDB seems to be defaulting to ADODB_FETCH_NUM and we want to be sure that the right default mode is set

                        $connect->SetFetchMode(ADODB_FETCH_ASSOC);

                        $dbexistsbutempty=($database_exists && $tablesdontexist);

                        //store them in session
                        $this->session->set_userdata(array('databaseexist' => $database_exists, 'tablesexist' => !$tablesdontexist));

                        // If database is up to date, redirect to Optional Configuration screen.
                        if ($database_exists && !$tablesdontexist)
                        {

                            $statusdata = array(
                            'optconfig_message' => 'The database you specified is up to date.',
                            'step3'  => TRUE
                            );
                            $this->session->set_userdata($statusdata);
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

                        /**
                        //writing config.php
                        //checking just in case
                        if (is_writable($rootdir))
                        {
                            $data = '<?php $databasetype = \''.$_POST['dbType'].'\'; $databaselocation = \''.$_POST['dbLocation'].'\'; $databasename = \''.$_POST['dbName'].'\'; $databaseuser = \''.$_POST['dbUser'].'\'; $databasepass = \''.$_POST['dbPwd'].'\'; $dbprefix = \''.$_POST['dbPrefix'].'\'; $rootdir = dirname(__FILE__); $rooturl = "http://{$_SERVER[\'HTTP_HOST\']}/limesurvey" ; $defaultuser = \'admin\' ; $defaultpass = \'password\' ; $debug = 0; ?>';
                            write_file('../config.php',$data,"w+");
                        }
                        else
                        {
                            header( "refresh:5;url=$rootdir/installation/index.php/installer/install/0" );
                            echo "<b>directory not writable</b><br/>";
                            echo 'You\'ll be redirected in about 5 secs. If not, click <a href="$rootdir/installation/index.php/installer/install/0">here</a>.';
                        }
                        */
                        //$data array won't work here. changing the name
                        $values['title']="Database settings";
                        $values['descp']="Database settings";
                        $values['classesForStep']=array("off","off","off","on","off");
                        $values['progressValue']=60;

                        //it store text content
                        $values['adminoutputText']='';
                        //it store the form code to be displayed
                        $values['adminoutputForm']='';



                        //if DB exist, check if its empty or up to date. if not, tell user LS can create it.
                        if (!$database_exists)
                        {
                            $statusdata = array(
                            'databaseDontExist'  => 'TRUE'
                            );
                            $this->session->set_userdata($statusdata);

                            $values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td align='center'>\n"
                            ."<strong>"."Database doesn't exist!"."</strong><br /><br />\n"
                            ."The database you specified does not exist."."<br />\n"
                            ."LimeSurvey can attempt to create this database for you."."<br /><br />\n"
                            ."Your selected database name is:"."<strong>".$this->input->post('dbName')."</strong><br />\n"
                            ."</center>\n" ;

                            $values['adminoutputForm']="<form action='".site_url("installer/createdb")."' method='post'><input type='submit' value='"
                            ."Create Database"."' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' /></form>";


                        }
                        elseif ($dbexistsbutempty ) //&& !(returnglobal('createdbstep2')==$clang->gT("Populate Database")))
                        {
                            $statusdata = array(
                            'populatedatabase'  => 'TRUE'

                            );
                            $this->session->set_userdata($statusdata);

                            $connect->database = $this->input->post('dbName');
                            $connect->Execute("USE DATABASE `".$this->input->post('dbName')."`");
                            $values['adminoutputText'].= "\t<tr bgcolor='#efefef'><td colspan='2' align='center'>\n";
                            $values['adminoutputText'].= "<br /><strong><font class='successtitle'>\n";
                            $values['adminoutputText'].= sprintf('A database named "%s" already exists.',$this->input->post('dbName'))."</font></strong></font><br /><br />\n";
                            $values['adminoutputText'].= "Do you want to populate that database now by creating the necessary tables?<br /><br />";
                            $values['adminoutputForm'].= "<form method='post' action='".base_url()."index.php/installer/populatedb/'>";
                            $values['adminoutputForm'].= "<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='Populate Database'></form>";
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
                $data['confirmation']= sprintf("Database <b>%s</b> has been successfully populated.",$this->session->userdata('dbname'));
                $data['title']="Optional settings";
                $data['descp']="Optional settings to give you a head start";
                $data['classesForStep']=array("off","off","off","off","on");
                $data['progressValue']=80;

                $statusdata = array(
                            'optional'  => 'TRUE'

                );
                $this->load->helper('surveytranslator');
                $this->session->set_userdata($statusdata);
                $this->load->view('installer/optconfig_view',$data);
                break;
            }

        }

    }

    /**
     * Installer::deletefiles()
     * delete installer files at end of installation.
     * @return
     */
    function deletefiles()
    {
        $status=$this->session->userdata('deletedirectories');
        if(!$status) {
            redirect(site_url('installer/install/license'));
        }
        $this->load->helper('file');
        $text = '';
        /**
        if (is_writable($this->config->item('rootdir').'/installer'))
        {
            delete_files($this->config->item('rootdir').'/installer/', TRUE);
            //show_error("Installation Directory(\"".$this->config->item('rootdir')."/installer\") is present. Remove/Rename it to proceed further.");
            //exit();
        }
        else
        {
            $text = "Couldn't delete Installation Directory(".$this->config->item('rootdir')."/installer) ";
        }

        if (is_writable(APPPATH . 'controllers/installer.php'))
        {
            delete_files(APPPATH . 'controllers/installer.php');
            //show_error("Script of installation (\"".APPPATH . "controllers/installer.php\") is present. Remove/Rename it to proceed further.");
            //exit();
        }
        else
        {
            if ($text != '')
            {
                $text .= ", script of installation(".APPPATH . "controllers/installer.php) ";
            }
            else
            {
                $text = "Couldn't delete script of installation(".APPPATH . "controllers/installer.php) ";
            }
        }

        if (is_writable(APPPATH . 'views/installer'))
        {
            delete_files(APPPATH . 'views/installer', TRUE);
            //show_error("Script of installation (\"".APPPATH . "controllers/installer.php\") is present. Remove/Rename it to proceed further.");
            //exit();
        }
        else
        {
            if ($text != '')
            {
                $text .= "and installer views(".APPPATH . "views/installer) .";
            }
            else
            {
                $text = "Couldn't delete installer views(".APPPATH . "views/installer) .";
            }
        }
        */

        header('refresh:5;url='.site_url("installer/install/0"));
        /**
        if ($text != '')
        {
           echo "<b>".$text."</b><br/>You can remove them manually later on. <br/>";
        }
        */

        echo 'You\'ll be redirected in about 5 secs. If not, click '.anchor("admin","here").'.';

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
            $databasetype = $this->session->userdata('databasetype');
            $connect=ADONewConnection($databasetype);

            //require_once($rootdir."/classes/core/sha256.php");
            //require_once($rootdir."/classes/core/surveytranslator.php");
            //require_once($rootdir."/classes/core/sanitize.php");

                //checking DB Connection
                if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
                {
                    $data['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
                    $data['title']="Database configuration";
                    $data['descp']="Connection settings:";
                    $data['classesForStep']=array("off","off","on","off","off");
                    $data['progressValue']=40;
                    $this->load->view('dbconfig_view',$data);
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
                    $createdbtype=$databasetype;
                    if ($databasetype=='mysql' || $databasetype=='mysqli') {
                    $createdbtype='mysql';
                    }
                    if ($createdbtype=='mssql' || $createdbtype=='odbc' || $createdbtype=='odbtp') $createdbtype='mssql';
                    if($createdbtype=='mssqlnative') $createdbtype='mssqlnative';
                    //$defaultpass=SHA256::hashing($defaultpass);

                    switch ($createdbtype) {

                        case "mysql":
                        {
                            $connect->Execute('INSERT INTO `'.$this->session->userdata("dbprefix").'users` (`users_name`, `password`, `full_name`, `parent_id`, `lang` ,`email`, `create_survey`, `create_user` , `delete_user` , `superadmin` , `configurator` , `manage_template` , `manage_label`) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            break;
                        }
                        case "mssqlnative":
                        {
                            $connect->Execute('INSERT INTO ['.$this->session->userdata("dbprefix").'users] ([users_name], [password], [full_name], [parent_id], [lang] ,[email], [create_survey], [create_user] , [delete_user] , [superadmin] , [configurator] , [manage_template] , [manage_label]) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            break;
                        }
                        case "mssql":
                        {
                            $connect->Execute('INSERT INTO ['.$this->session->userdata("dbprefix").'users] ([users_name], [password], [full_name], [parent_id], [lang] ,[email], [create_survey], [create_user] , [delete_user] , [superadmin] , [configurator] , [manage_template] , [manage_label]) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1)');
                            break;
                        }
                        case "postgres":
                        {
                            $connect->Execute('INSERT INTO '.$this->session->userdata("dbprefix").'users (users_name, "password", full_name, parent_id, lang ,email, create_survey, create_user , delete_user , superadmin , configurator , manage_template , manage_label, htmleditormode) VALUES (\''.$defaultuser.'\', \''.$defaultpass.'\', \''.$siteadminname.'\', 0, \''.$defaultlang.'\', \''.$siteadminemail.'\', 1,1,1,1,1,1,1,\'default\')');
                            break;
                        }

                    }

                    // if successfully data is inserted, notify user to login and redirect to proper link
                    // code to force user to rename/delete installation directory will be in Admin_Controller
                    //header( "refresh:5;url=".site_url('admin'));
                    //echo 'You\'ll be redirected in about 5 secs. If not, click '.anchor('admin',"here").'.';

                    $this->session->set_userdata('deletedirectories' , TRUE);
                    $newdata = array();
                    //DELETE SAMPLE INSTALLER FILE. If we can't, notify user of the same.
                    if (is_writable($this->config->item('rootdir').'/tmp/sample_installer-file.txt'))
                    {
                        $this->load->helper('file');
                        delete_files($this->config->item('rootdir').'/tmp/sample_installer_file.txt',TRUE);
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
            $statusdata = array(
                            //'step2'  => 'TRUE',
                            'optconfig_message' => 'Password don\'t match.'
                            );
            $this->session->set_userdata($statusdata);
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
        $data['confirmation']="<b>".$this->session->userdata('optconfig_message')."</b><br/>";
        $data['title']="Optional settings";
        $data['descp']="Optional settings to give you a head start";
        $data['classesForStep']=array("off","off","off","off","on");
        $data['progressValue']=80;

        $statusdata = array(
            'optional'  => TRUE

        );
        $this->session->set_userdata($statusdata);
        $this->load->view('installer/optconfig_view',$data);

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
        $databasetype = $this->session->userdata('databasetype');
        $connect=ADONewConnection($databasetype);

        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd')))
        {
            $data['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
            $data['title']="Database configuration";
            $data['descp']="Connection settings:";
            $data['classesForStep']=array("off","off","on","off","off");
            $data['progressValue']=40;
            $this->load->view('dbconfig_view',$data);
        }
        else
        {

            $values['adminoutputForm']='';
            switch ($databasetype)
            {
                case 'mysqli':
                case 'mysql': $createDb=$connect->Execute("CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
                break;
                case 'mssql':
                case 'odbc':
                case 'mssqlnative':
                case 'odbtp': $createDb=$connect->Execute("CREATE DATABASE [$dbname];");
                break;
                default: $createDb=$connect->Execute("CREATE DATABASE $dbname");
            }
            //$this->load->dbforge();
            if ($createDb) //Database has been successfully created
            {
                $connect->database = $dbname;
                $connect->Execute("USE DATABASE `$dbname`");
                $statusdata = array(
                                'populatedatabase'  => 'TRUE'

                                );
                $this->session->set_userdata($statusdata);
                $this->session->set_userdata(array('databaseexist' => TRUE));
                $values['adminoutputText']="<tr bgcolor='#efefef'><td colspan='2' align='center'> <br />";
                $values['adminoutputText'] .= "<strong><font class='successtitle'>\n";
                $values['adminoutputText'] .= "Database has been created.</strong></font><br /><br />\n";
                $values['adminoutputText'] .= "Please click below to populate the database<br /><br />\n";
                $values['adminoutputForm'] .= "<form method='post' action='".site_url('installer/populatedb')."'>";
                $values['adminoutputForm'] .= "<input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type='submit' name='createdbstep2' value='Populate Database' ></form>";

                $this->session->unset_userdata('databaseDontExist');
            }
            else
            {
                $data['errorConnection'] ='<b>Try again! Connection with database failed.</b>';
                $data['title']="Database configuration";
                $data['descp']="Connection settings:";
                $data['classesForStep']=array("off","off","on","off","off");
                $data['progressValue']=40;
                $this->load->view('installer/dbconfig_view',$data);

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
        $databasetype = $this->session->userdata('databasetype');

        $connect=ADONewConnection($databasetype);


        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
        {
            $data['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
            $data['title']="Database configuration";
            $data['descp']="Connection settings:";
            $data['classesForStep']=array("off","off","on","off","off");
            $data['progressValue']=40;
            $this->load->view('dbconfig_view',$data);
        }
        else
        {

            $createdbtype=$databasetype;

            if ($databasetype=='mysql' || $databasetype=='mysqli') {
                $connect->Execute("ALTER DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
                $createdbtype='mysql';
            }
            if ($createdbtype=='mssql' || $createdbtype=='odbc' || $createdbtype=='odbtp') $createdbtype='mssql';
            if($createdbtype=='postgres' && $connect->pgVersion=='9')
            {
                $connect->execute("ALTER DATABASE {$dbname} SET bytea_output='escape';");
            }
            if($createdbtype=='mssqlnative') $createdbtype='mssqlnative';

            if (self::_modify_database($this->config->item('rootdir').'/installer/sql/create-'.$createdbtype.'.sql'))
            {
                //$data1['adminoutput'] = '';
                //$data1['adminoutput'] .= sprintf("Database `%s` has been successfully populated.",$dbname)."</font></strong></font><br /><br />\n";
                //$data1['adminoutput'] .= "<input type='submit' value='Main Admin Screen' onclick=''>";
                $this->session->set_userdata(array('tablesexist' => TRUE));
                $statusdata = array(
                            //'step2'  => 'TRUE',
                            'step3'  => TRUE
                            );

                $this->session->unset_userdata('populatedatabase');
                $this->session->set_userdata($statusdata);
                redirect(site_url('installer/install/2'));

            }
            else
            {
                $values['adminoutput'] = "Error";
                $values['title']="Database settings";
                $values['descp']="Database settings";
                $values['classesForStep']=array("off","off","off","on","off");
                $values['progressValue']=60;
                $this->load->view('installer/dbsettings_view',$values);
            }

        }


    }

    //function that ultimately populate database.
    /**
     * Installer::_modify_database()
     * Function that actually modify the database. Read $sqlfile and execute it.
     * @param string $sqlfile
     * @param string $sqlstring
     * @return
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
        $databasetype = $this->session->userdata('databasetype');
        $connect=ADONewConnection($databasetype);


        //checking DB Connection
        if (!$connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname))
        {
            $data['errorConnection'] ='<b>Try again! Connection with database failed.</b><br/><b>Reason</b>:'.$connect->ErrorMsg().'<br/>';
            $data['title']="Database configuration";
            $data['descp']="Connection settings:";
            $data['classesForStep']=array("off","off","on","off","off");
            $data['progressValue']=40;
            $this->load->view('dbconfig_view',$data);
        }
        else
        {
            //require_once($rootdir."/classes/core/sha256.php");
            //require_once($rootdir."/classes/core/surveytranslator.php");
            //require_once($rootdir."/classes/core/sanitize.php");
            $this->load->library('admin/sha256','sha256');
            $defaultpass=$this->sha256->hashing($defaultpass);
            $success = true;  // Let's be optimistic
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

                        if (! self::_db_execute_num($command)) {  //Checked
                            $command=htmlspecialchars($command);
                            $modifyoutput .="<br />"."Executing.....".$command."<font color='#FF0000'>..."."Failed! Reason: ".$connect->ErrorMsg()."</font>";
                            $success = false;
                        }
                        else
                        {
                            $command=htmlspecialchars($command);
                            $modifyoutput .=". ";
                        }

                        $command = '';
                    } else {
                        $command .= $line;
                    }
                }
            }

            return $success;
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

                            $dbvalues = array(
                                        'hostname' => $this->session->userdata('dblocation'),
                                        'username' => $this->session->userdata('dbuser'),
                                        'password' => $this->session->userdata('dbpwd'),
                                        'database' => $this->session->userdata('dbname'),
                                        'dbdriver' => $this->session->userdata('databasetype'),
                                        'dbprefix' => $this->session->userdata('dbprefix'),
                                        'pconnect' => 'TRUE',
                                        'db_debug' => 'TRUE',
                                        'cache_on' => 'FALSE',
                                        'cachedir' => '',
                                        'char_set' => 'utf8',
                                        'dbcollat' => 'utf8_general_ci',
                                        'swap_pre' => '',
                                        'autoinit' => 'TRUE',
                                        'stricton' => 'FALSE',
                                        'port' => 'default',
                                        'databasetabletype' => 'myISAM'

                            );

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

            $string = str_replace('$autoload[\'libraries\'] = array(\'session\');','$autoload[\'libraries\'] = array(\'database\', \'session\');', $string);
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
     * Installer::_db_execute_num()
     * Function to execute database queries and return normal array(not assosciative one!).
     * @param mixed $sql
     * @param bool $inputarr
     * @return
     */
    function _db_execute_num($sql,$inputarr=false)
    {
        //include(dirname(__FILE__).'/../../../config-sample.php');
        require_once(APPPATH.'third_party/adodb/adodb.inc.php');
        $dbname = $this->session->userdata('dbname');
        $databasetype = $this->session->userdata('databasetype');
        $connect=ADONewConnection($databasetype);;
        $connect->Connect($this->session->userdata('dblocation'), $this->session->userdata('dbuser'), $this->session->userdata('dbpwd'),$dbname);

        $connect->SetFetchMode(ADODB_FETCH_NUM);
        $dataset=$connect->Execute($sql,$inputarr);  //Checked
        return $dataset;
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
            case "mssql_n" :
            case "mssql" :
            case "mssqlnative" :
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