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
$cmd_install=true;
if (isset($argv[1]) && ($argv[1]=='install'|| $argv[1]=='upgrade') && isset($argv[2]) && isset($argv[3]))    
{
    require_once($argv[2]);
    require_once($argv[3]);
}
else
{
    require_once(dirname(__FILE__).'/../../config-defaults.php');
    require_once(dirname(__FILE__).'/../../common.php');
}
// SET THE LANGUAGE???? -> DEFAULT SET TO EN FOR NOW
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang("en");
ob_implicit_flush(true);

if (isset($argv[1]) && $argv[1]=='install')
{
	print("trying to create and populate $databasename on $databaselocation:$databaseport ($databasetype) \n");
	
	if (!$database_exists) //Database named in config.php does not exist
	{
		
		if($connect->Execute("CREATE DATABASE $databasename;"))
		{
			print("\nDatabase $databasename on $databasetype CREATED \n");
		}
		else
		{
			print("\nDatabase $databasename on $databasetype COULD NOT BE CREATED \n");
			print("\n".$connect->ErrorMsg());
			return 1;
		}

	}
	else
	{
		if ($databasetype=='mysql') {$connect->Execute("ALTER DATABASE `$databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");} //Set the collation also for manually created DBs
		
		print("\nDatabase $databasename on $databasetype EXISTS, not created \n");
		
	}
	
	// Connect to the database we created a sec ago or to the existing db. 
	if(!$connect->Connect($databaselocation,$databaseuser,$databasepass,$databasename))
	{
		print("\n".$connect->ErrorMsg());
		return 1;
	}
		
	require_once($homedir."/classes/core/sha256.php");
	
	$success = 0;  // Let's be optimistic
	
	$sqlfile = dirname(__FILE__).'/create-'.$databasetype.'.sql' ;
	
	if (!empty($sqlfile)) {
		if (!is_readable($sqlfile)) {
			$success = false;
			print "\nTried to populate database, but '". $sqlfile ."' doesn't exist!\n";
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
	
	$connect->SetFetchMode(ADODB_FETCH_NUM);
	foreach ($lines as $line) {
		$line = rtrim($line);
		$length = strlen($line);

		if ($length and $line[0] <> '#' and substr($line,0,2) <> '--') {
			if (substr($line, $length-1, 1) == ';') {
  				$line = substr($line, 0, $length-1);   // strip ;
				$command .= $line;
				$command = str_replace('prefix_', $dbprefix, $command); // Table prefixes
				$command = str_replace('$defaultuser', $defaultuser, $command); // variables By Moses
				$command = str_replace('$defaultpass', SHA256::hash($defaultpass), $command); // variables By Moses
				$command = str_replace('$siteadminname', $siteadminname, $command);
				$command = str_replace('$siteadminemail', $siteadminemail, $command); // variables By Moses
				$command = str_replace('$defaultlang', $defaultlang, $command); // variables By Moses
				$command = str_replace('$sessionname', 'ls'.getRandomID().getRandomID().getRandomID().getRandomID(), $command); // variables By Moses
				$command = str_replace('$databasetabletype', $databasetabletype, $command);


					
					
					if(!$connect->Execute($command,false))
					{
						print ("\n".$clang->gT("Executing").".....".$command."...".$clang->gT('Failed! Reason:')."\n".$connect->ErrorMsg()."\n\n");
						$success=1;
					}
					
		

				$command = '';
			} else {
				$command .= $line;
			}
		}
	}
	$connect->SetFetchMode(ADODB_FETCH_ASSOC);
	if($success == 0)
	{
		print("Database $databasename on $databasetype POPULATED");
		print("\n\neverything went fine");
		return $success;
	}
	else
	{
		print("\n\nSomething is strange");
		print("\nplease check you Database and Settings");
		return $success;
	}
	
	
    // if (modify_database(dirname(__FILE__).'\create-'.$databasetype.'.sql'))
    // {
    	
    	// print("\nDatabase $databasename on $databasetype POPULATED \n");
    	// return 0;
    // } else {

    	// print("Could not populate $databasename on $databasetype\n");
    	// return 1;
    // }
}

elseif (isset($argv[1]) && $argv[1]=='upgrade')

{
   
    include ('upgrade-'.$databasetype.'.php');
    $tables = $connect->MetaTables();
    
    $usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='DBVersion'";
    $usresult = db_execute_assoc($usquery);
    $usrow = $usresult->FetchRow();
    if (intval($usrow['stg_value'])<$dbversionnumber)
    {
    	print("Upgrading db to $dbversionnumber\n");
            db_upgrade(intval($usrow['stg_value']));
    } else {
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