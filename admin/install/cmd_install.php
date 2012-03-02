<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Nicolas Barcet
 * All rights reserved.
 * License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: index.php 4729 2008-05-31 21:32:02Z nijaba $
 */
 
//set up a new installation or update the existing system?
if (isset($argv[1]) && ($argv[1]=='install'|| $argv[1]=='upgrade') && isset($argv[2]) && isset($argv[3]))
{
    require_once($argv[2]);
    require_once($argv[3]);
}
//get the required files
else
{
    require_once(dirname(__FILE__).'/../../config-defaults.php');
    $cmd_install=true;
    require_once(dirname(__FILE__).'/../../common.php');
}
// SET THE LANGUAGE???? -> DEFAULT SET TO EN FOR NOW
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang("en");
ob_implicit_flush(true);

//NEW INSTALLATION
if (isset($argv[1]) && $argv[1]=='install')
{
	//output
    print("trying to create and populate $databasename on $databaselocation:$databaseport ($databasetype) \n");

	//Database named in config.php does not exist
    if (!$database_exists) 
    {
		//create database because it doesn't exist yet
        if($connect->Execute("CREATE DATABASE $databasename;"))
        {
			//output
            print("\nDatabase $databasename on $databasetype CREATED \n");
        }
		//error: database could not be created
        else
        {
            print("\nDatabase $databasename on $databasetype COULD NOT BE CREATED \n");
            print("\n".$connect->ErrorMsg());
            return 1;
        }

    }
	//DB already exists
    else
    {
		//MySQL and MySQLi DBs only
        if ($databasetype=='mysql' || $databasetype=='mysqli') 
		{
			//Set the collation also for manually created DBs
			$connect->Execute("ALTER DATABASE `$databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
		} 

        print("\nDatabase $databasename on $databasetype EXISTS, not created \n");

    }

    //Connect to the database we created a sec ago or to the existing db.
	//Show error message if the connection fails
    if(!$connect->Connect($databaselocation,$databaseuser,$databasepass,$databasename))
    {
        print("\n".$connect->ErrorMsg());
        return 1;
    }

	//include this file to hash the initial password entered at config.php
    require_once($homedir."/classes/core/sha256.php");

	//Let's be optimistic
    $success = 0;  

	//check which DB type we have to deal with
    $createdbtype=$databasetype;
	
	//set DB type
    if ($createdbtype=='mssql_n' || $createdbtype=='odbc_mssql' || $createdbtype=='odbtp') $createdbtype='mssql';
	
	//set DB type
	if ($createdbtype=='mssqlnative') $createdbtype == 'mssqlnative';
    
	//this is the SQL file holding the installation details to set up the Limesurvey DB
    $sqlfile = dirname(__FILE__).'/create-'.$createdbtype.'.sql' ;

	//check if file is not empty
    if (!empty($sqlfile)) 
	{
		//check if file is readable
        if (!is_readable($sqlfile)) 
		{
			//can't read from file, set error flag
            $success = false;
			
			//output error message
            print "\nTried to populate database, but '". $sqlfile ."' doesn't exist!\n";
            
			//return error flag
            return $success;
        } 
		//everything is fine -> read in the file
		else 
		{
            $lines = file($sqlfile);
        }
    } 
	else 
	{
        $sqlstring = trim($sqlstring);
		
		//check if ";" exists
        if ($sqlstring{strlen($sqlstring)-1} != ";") 
		{
			//add ";" if it's not there.
            $sqlstring .= ";"; 
        }
		//assign the data
        $lines[] = $sqlstring;
    }

    $command = '';

	//set connection mode
    $connect->SetFetchMode(ADODB_FETCH_NUM);
	
	//check each statement if the SQL installation file
    foreach ($lines as $line) 
	{
		//special data treatment
        $line = rtrim($line);
		
		//get length of statement
        $length = strlen($line);

		//only read those lines that are (1) not empty, (2) don't start with "#" or (3) "--"
        if ($length and $line[0] <> '#' and substr($line,0,2) <> '--') 
		{
			//check if trailing ";" exists
            if (substr($line, $length-1, 1) == ';')			
			{
				//get the matching substring
                $line = substr($line, 0, $length-1);
				
				//put together the SQL command
                $command .= $line;
                $command = str_replace('prefix_', $dbprefix, $command); // Table prefixes
                $command = str_replace('$defaultuser', $defaultuser, $command); // variables By Moses
                $command = str_replace('$defaultpass', SHA256::hashing($defaultpass), $command); // variables By Moses
                $command = str_replace('$siteadminname', $siteadminname, $command);
                $command = str_replace('$siteadminemail', $siteadminemail, $command); // variables By Moses
                $command = str_replace('$defaultlang', $defaultlang, $command); // variables By Moses
                $command = str_replace('$sessionname', 'ls'.sRandomChars(20,'123456789'), $command);
                $command = str_replace('$databasetabletype', $databasetabletype, $command);

				//error message if certain statement failed
                if(!$connect->Execute($command,false))
                {
                    print ("\n".$clang->gT("Executing").".....".$command."...".$clang->gT('Failed! Reason:')."\n".$connect->ErrorMsg()."\n\n");
                    
					//set error flag
                    $success=1;
                }
				
				//empty command variable
                $command = '';
            } 
			//add further data to command statement
			else 
			{
                $command .= $line;
            }
        }
    }
	
	//connect to DB
    $connect->SetFetchMode(ADODB_FETCH_ASSOC);
	
	//no error flag set -> tell user that all wnt well
    if($success == 0)
    {
        print("Database $databasename on $databasetype POPULATED");
        print("\n\neverything went fine");
        return $success;
    }
	//there have been errors, damn!
    else
    {
        print("\n\nSomething is strange");
        print("\nplease check you Database and Settings");
        return $success;
    }
}

//UPDATE existing system
elseif (isset($argv[1]) && $argv[1]=='upgrade')
{
	//Db type
    $upgradedbtype=$databasetype;
	
	//treatment for different DB types
    if ($upgradedbtype=='mssql_n' || $upgradedbtype=='odbc_mssql' || $upgradedbtype=='odbtp') $upgradedbtype='mssql';
    if ($upgradedbtype=='mssqnlative') $upgradedbtype='mssqlnative';
    
	//we need thise files to run the update
    include ('upgrade-all.php');
    include ('upgrade-'.$upgradedbtype.'.php');

	//get current DB version
    $usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='DBVersion'";
    $usresult = db_execute_assoc($usquery);
    $usrow = $usresult->FetchRow();
    
	//if user doesn't run the latest DB version, show a message and run update
    if (intval($usrow['stg_value'])<$dbversionnumber)
    {
        print("Upgrading db to $dbversionnumber\n");
        db_upgrade_all(intval($usrow['stg_value']));
        db_upgrade(intval($usrow['stg_value']));
    } 
	//latest DB version is already in use
	else 
	{
        print("Already at db version $dbversionnumber\n");
    }
    return 0;
}
else
{
    print("LimeSurvey Commandline Install\n");
    print("Usage: cmd_install <option> < <<path1>> <<path2>> >\n");
    print("<option> - 'install' or 'upgrade' \n");
    print("<<path1>> - Full path including filename to a custom config-defaults.php\n");
    print("<<path2>> - Full path including filename to a custom common.php\n");
    print("Paths are optional. If used both paths must be set.\n");
    return 2;
}
?>