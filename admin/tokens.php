<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 */


# TOKENS FILE
include_once("login_check.php");
 
if ($enableLdap)
{
    require_once(dirname(__FILE__).'/../config-ldap.php');
}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($limit)) {$limit=(int)returnglobal('limit');}
if ($limit==0) $limit=50;
if (!isset($start)) {$start=(int)returnglobal('start');}
if (!isset($searchstring)) {$searchstring=returnglobal('searchstring');}
if (!isset($tokenid)) {$tokenid=returnglobal('tid');}
if (!isset($tokenids)) {$tokenids=returnglobal('tids');}
if (!isset($gtokenid)) {$gtokenid=returnglobal('gtid');}
if (!isset($gtokenids)) {$gtokenids=returnglobal('gtids');}
if (!isset($starttokenid)) {$starttokenid=sanitize_int(returnglobal('last_tid'));}
if (!isset($usertoken)) {$usertoken=returnglobal('usertoken');}

if(isset($tokenids)) {
    $tokenidsarray=explode("|", substr($tokenids, 1)); //Make the tokenids string into an array, and exclude the first character
    unset($tokenids);
    foreach($tokenidsarray as $tokenitem) {
        if($tokenitem != "") $tokenids[]=sanitize_int($tokenitem);
    }
}
if(isset($gtokenids)) {
    $gtokenidsarray=explode("|", substr($gtokenids, 1)); //Make the gtids string into an array, and exclude the first character
    unset($gtokenids);
    foreach($gtokenidsarray as $gtokenitem) {
        if($gtokenitem != "") $gtokenids[]=sanitize_int($gtokenitem);
    }
}

include_once("login_check.php");
include_once("database.php");
$js_admin_includes[]='scripts/tokens.js';
$dateformatdetails=getDateFormatData($_SESSION['dateformat']);
$thissurvey=getSurveyInfo($surveyid);

//$invitationBody = "Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}";
//$reminderBody = "Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}";
//$confirmationBody = "Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}";
//$registrationBody = "Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.");

if ($subaction == "import" || $subaction == "upload" )  // THis array only needs to be defined for these two functions
{
    $js_admin_includes[]='scripts/tokens.js';

    $encodingsarray = array("armscii8"=>$clang->gT("ARMSCII-8 Armenian")
    ,"ascii"=>$clang->gT("US ASCII")
    ,"auto"=>$clang->gT("Automatic")
    ,"big5"=>$clang->gT("Big5 Traditional Chinese")
    ,"binary"=>$clang->gT("Binary pseudo charset")
    ,"cp1250"=>$clang->gT("Windows Central European")
    ,"cp1251"=>$clang->gT("Windows Cyrillic")
    ,"cp1256"=>$clang->gT("Windows Arabic")
    ,"cp1257"=>$clang->gT("Windows Baltic")
    ,"cp850"=>$clang->gT("DOS West European")
    ,"cp852"=>$clang->gT("DOS Central European")
    ,"cp866"=>$clang->gT("DOS Russian")
    ,"cp932"=>$clang->gT("SJIS for Windows Japanese")
    ,"dec8"=>$clang->gT("DEC West European")
    ,"eucjpms"=>$clang->gT("UJIS for Windows Japanese")
    ,"euckr"=>$clang->gT("EUC-KR Korean")
    ,"gb2312"=>$clang->gT("GB2312 Simplified Chinese")
    ,"gbk"=>$clang->gT("GBK Simplified Chinese")
    ,"geostd8"=>$clang->gT("GEOSTD8 Georgian")
    ,"greek"=>$clang->gT("ISO 8859-7 Greek")
    ,"hebrew"=>$clang->gT("ISO 8859-8 Hebrew")
    ,"hp8"=>$clang->gT("HP West European")
    ,"keybcs2"=>$clang->gT("DOS Kamenicky Czech-Slovak")
    ,"koi8r"=>$clang->gT("KOI8-R Relcom Russian")
    ,"koi8u"=>$clang->gT("KOI8-U Ukrainian")
    ,"latin1"=>$clang->gT("cp1252 West European")
    ,"latin2"=>$clang->gT("ISO 8859-2 Central European")
    ,"latin5"=>$clang->gT("ISO 8859-9 Turkish")
    ,"latin7"=>$clang->gT("ISO 8859-13 Baltic")
    ,"macce"=>$clang->gT("Mac Central European")
    ,"macroman"=>$clang->gT("Mac West European")
    ,"sjis"=>$clang->gT("Shift-JIS Japanese")
    ,"swe7"=>$clang->gT("7bit Swedish")
    ,"tis620"=>$clang->gT("TIS620 Thai")
    ,"ucs2"=>$clang->gT("UCS-2 Unicode")
    ,"ujis"=>$clang->gT("EUC-JP Japanese")
    ,"utf8"=>$clang->gT("UTF-8 Unicode"));
    if (isset($_POST['csvcharset']) && $_POST['csvcharset'])  //sanitize charset - if encoding is not found sanitize to 'auto'
    {
        $uploadcharset=$_POST['csvcharset'];
        if (!array_key_exists($uploadcharset,$encodingsarray)) {$uploadcharset='auto';}
        $filterduplicatetoken=(isset($_POST['filterduplicatetoken']) && $_POST['filterduplicatetoken']=='on');
        $filterblankemail=(isset($_POST['filterblankemail']) && $_POST['filterblankemail']=='on');
    }

}
if ($subaction == "importldap" || $subaction == "uploadldap" )
{
    $filterduplicatetoken=(isset($_POST['filterduplicatetoken']) && $_POST['filterduplicatetoken']=='on');
    $filterblankemail=(isset($_POST['filterblankemail']) && $_POST['filterblankemail']=='on');
}
$tokenoutput = "";

if ($subaction == "export" && ( bHasSurveyPermission($surveyid, 'tokens', 'export')) )//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
{
    header("Content-Disposition: attachment; filename=tokens_".$surveyid.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");



    $bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid").' where 1=1';
    if (trim($_POST['filteremail'])!='')
    {
        if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $connect->databaseType == 'mssqlnative')
        {
            $bquery .= ' and CAST(email as varchar) like '.db_quoteall('%'.$_POST['filteremail'].'%', true);
        }
        else
        {
            $bquery .= ' and email like '.db_quoteall('%'.$_POST['filteremail'].'%', true);
        }
    }
    if ($_POST['tokenstatus']==1)
    {
        $bquery .= " and completed<>'N'";
    }
    if ($_POST['tokenstatus']==2)
    {
        $bquery .= " and completed='N'";
        if ($thissurvey['private']=='N')
        {
            $bquery .=" and token not in (select token from ".db_table_name("survey_$surveyid")." group by token)";
        }
    }
    if ($_POST['tokenstatus']==3 && $thissurvey['private']=='N')
    {
        $bquery .= " and completed='N' and token in (select token from ".db_table_name("survey_$surveyid")." group by token)";
    }

    if ($_POST['invitationstatus']==1)
    {
        $bquery .= " and sent<>'N'";
    }
    if ($_POST['invitationstatus']==2)
    {
        $bquery .= " and sent='N'";
    }

    if ($_POST['reminderstatus']==1)
    {
        $bquery .= " and remindersent<>'N'";
    }
    if ($_POST['reminderstatus']==2)
    {
        $bquery .= " and remindersent='N'";
    }

    if ($_POST['tokenlanguage']!='')
    {
        $bquery .= " and language=".db_quoteall($_POST['tokenlanguage']);
    }
    $bquery .= " ORDER BY tid";

    $bresult = db_execute_assoc($bquery) or die ("$bquery<br />".htmlspecialchars($connect->ErrorMsg()));
    $bfieldcount=$bresult->FieldCount();
    // Export UTF8 WITH BOM
    $tokenoutput = chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'));
    $tokenoutput .= "tid,firstname,lastname,email,emailstatus,token,language,validfrom,validuntil,invited,reminded,remindercount,completed,usesleft";
    $attrfieldnames = GetAttributeFieldnames($surveyid);
    $attrfielddescr = GetTokenFieldsAndNames($surveyid, true);
    foreach ($attrfieldnames as $attr_name)
    {
        $tokenoutput .=", $attr_name";
        if (isset($attrfielddescr[$attr_name]))
        $tokenoutput .=" <".str_replace(","," ",$attrfielddescr[$attr_name]).">";
    }
    $tokenoutput .="\n";
    while ($brow = $bresult->FetchRow())
    {

        if (trim($brow['validfrom']!=''))
        {
            $datetimeobj = new Date_Time_Converter($brow['validfrom'] , "Y-m-d H:i:s");
            $brow['validfrom']=$datetimeobj->convert('Y-m-d H:i');
        }
        if (trim($brow['validuntil']!=''))
        {
            $datetimeobj = new Date_Time_Converter($brow['validuntil'] , "Y-m-d H:i:s");
            $brow['validuntil']=$datetimeobj->convert('Y-m-d H:i');
        }

        $tokenoutput .= '"'.trim($brow['tid']).'",';
        $tokenoutput .= '"'.trim($brow['firstname']).'",';
        $tokenoutput .= '"'.trim($brow['lastname']).'",';
        $tokenoutput .= '"'.trim($brow['email']).'",';
        $tokenoutput .= '"'.trim($brow['emailstatus']).'",';
        $tokenoutput .= '"'.trim($brow['token']).'",';
        $tokenoutput .= '"'.trim($brow['language']).'",';
        $tokenoutput .= '"'.trim($brow['validfrom']).'",';
        $tokenoutput .= '"'.trim($brow['validuntil']).'",';
        $tokenoutput .= '"'.trim($brow['sent']).'",';
        $tokenoutput .= '"'.trim($brow['remindersent']).'",';
        $tokenoutput .= '"'.trim($brow['remindercount']).'",';
        $tokenoutput .= '"'.trim($brow['completed']).'",';
        $tokenoutput .= '"'.trim($brow['usesleft']).'",';
        foreach ($attrfieldnames as $attr_name)
        {
            $tokenoutput .='"'.trim($brow[$attr_name]).'",';
        }
        $tokenoutput = substr($tokenoutput,0,-1); // remove last comma
        $tokenoutput .= "\n";
    }
    echo $tokenoutput;
    exit;
}


if ($subaction == "delete" && bHasSurveyPermission($surveyid, 'tokens','delete'))
{
    $_SESSION['metaHeader']="<meta http-equiv=\"refresh\" content=\"1;URL={$scriptname}?action=tokens&amp;subaction=browse&amp;sid=".returnglobal('sid')."&amp;start=$start&amp;limit=$limit&amp;order=$order\" />";
}

if ($subaction == "deletegroup" && bHasSurveyPermission($surveyid, 'tokens','delete'))       
{
    $_SESSION['metaHeader']="<meta http-equiv=\"refresh\" content=\"1;URL={$scriptname}?action=tokens&amp;subaction=browsegroup&amp;sid=".returnglobal('sid')."&amp;start=$start&amp;limit=$limit&amp;order=$order\" />";
}

$tokenoutput .= "<script type='text/javascript'>\n"
."<!--\n"
. "function fillin(tofield, fromfield)\n"
. "\t{\n"
. "if (confirm(\"".$clang->gT("This will replace the existing text. Continue?","js")."\")) {\n"
. "if (document.getElementById(tofield).readOnly == false)\n"
. "{\n"
. "    $('#'+tofield).val($('#'+fromfield).val());\n"
. "}\n"
. "updateFCKeditor(tofield,$('#'+fromfield).val());\n"
. "}\n"
. "\t}\n"
."-->\n"
."</script>\n";

// MAKE SURE THAT THERE IS A SID
if (!isset($surveyid) || !$surveyid )
{
    $tokenoutput .= "\t<div class='messagebox'><div class='header'>"
    .$clang->gT("Token control")."</div>\n"
    ."\t<br /><div class='warningheader'>".$clang->gT("Error")."</div>"
    ."<br />".$clang->gT("You have not selected a survey")."<br /><br />"
    ."<input type='submit' value='"
    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
    ."</div>\n";
    return;
}
// MAKE SURE THAT THE SURVEY EXISTS
$thissurvey=getSurveyInfo($surveyid);
if ($thissurvey===false)
{

    $tokenoutput .= "\t<div class='messagebox'>\n<div class='header'>\n"
    .$clang->gT("Token control")."</div>\n"
    ."\t<br /><div class='warningheader'>".$clang->gT("Error")."</div>"
    ."<br />".$clang->gT("The survey you selected does not exist")
    ."<br /><br />\n\t<input type='submit' value='"
    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />"
    ."</div>\n";
    return;
}
else        // A survey DOES exist
{if($subaction != 'bounceprocessing')
{

   $tokenoutput .= "\t<div class='menubar'>"
    ."<div class='menubar-title'>"
    ."<strong>".$clang->gT("Token control")." </strong> ".htmlspecialchars($thissurvey['surveyls_title'])."</div>\n";
    $surveyprivate = $thissurvey['private'];
}
}

// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
$tokenexists=tableExists('tokens_'.$surveyid);
if (!$tokenexists) //If no tokens table exists
{
    if (isset($_POST['createtable']) && $_POST['createtable']=="Y" && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
    {
        $createtokentable=
		"tid int I NOTNULL AUTO PRIMARY,\n "
		. "firstname C(40),\n "
		. "lastname C(40),\n ";
		//MSSQL needs special treatment because of some strangeness in ADODB
        if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n' || $connect->databaseType == 'mssqlnative')
		{
		    $createtokentable.= "email C(320),\n "
		    ."emailstatus C(300) DEFAULT 'OK',\n ";
		}
		else
		{
		    $createtokentable.= "email X(320),\n "
		    ."emailstatus X(300) DEFAULT 'OK',\n ";
		}

		$createtokentable.= "token C(36) ,\n "
		. "language C(25) ,\n "
		. "sent C(17) DEFAULT 'N',\n "
		. "remindersent C(17) DEFAULT 'N',\n "
		. "remindercount int I DEFAULT 0,\n "
		. "completed C(17) DEFAULT 'N',\n "
		. "usesleft I DEFAULT 1,\n"
		. "validfrom T ,\n "
		. "validuntil T ,\n "
		. "mpid I ";


		$tabname = "{$dbprefix}tokens_{$surveyid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
		$taboptarray = array('mysql' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
                             'mysqli' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		$dict = NewDataDictionary($connect);
		$sqlarray = $dict->CreateTableSQL($tabname, $createtokentable, $taboptarray);
		$execresult=$dict->ExecuteSQLArray($sqlarray, false);

		if ($execresult==0 || $execresult==1)
		{

		    $tokenoutput .= "\t</div><div class='messagebox'>\n"
		    ."<font size='1'><strong><center>".$clang->gT("Token table could not be created.")."</center></strong></font>\n"
		    .$clang->gT("Error").": \n<font color='red'>" . $connect->ErrorMsg() . "</font>\n"
		    ."<pre>".htmlspecialchars(implode(" ",$sqlarray))."</pre>\n"
		    ."<br />"
		    ."<input type='submit' value='"
		    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n"
		    ."</div>\n"
		    ."</div>\n";

		} else {
		    $createtokentableindex = $dict->CreateIndexSQL("{$tabname}_idx", $tabname, array('token'));
		    $dict->ExecuteSQLArray($createtokentableindex, false) or safe_die ("Failed to create token table index<br />$createtokentableindex<br /><br />".$connect->ErrorMsg());
		    if ($connect->databaseType == 'mysql' || $connect->databaseType == 'mysqli')
		    {
		        $query = 'CREATE INDEX idx_'.$tabname.'_efl ON '.$tabname.' ( email(120), firstname, lastname )';
		        $result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
		    }


		    $tokenoutput .= "\t</div><p>\n"
		    .$clang->gT("A token table has been created for this survey.")." (\"".$dbprefix."tokens_$surveyid\")<br /><br />\n"
		    ."<input type='submit' value='"
		    .$clang->gT("Continue")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n";
		}
		return;
    }
    elseif (returnglobal('restoretable') == "Y" && returnglobal('oldtable') && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
    {
        $query = db_rename_table(returnglobal('oldtable') , db_table_name_nq("tokens_$surveyid"));
        $result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
		
        $tokenoutput .= "\t</div><div class='messagebox'>\n"
        ."<div class='header'>".$clang->gT("Import old tokens")."</div>"
        ."<br />".$clang->gT("A token table has been created for this survey and the old tokens were imported.")." (\"".$dbprefix."tokens_$surveyid\")<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Continue")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n"
        ."</div>\n";
        return;
    }
    else
    {
        $query=db_select_tables_like("{$dbprefix}old_tokens_".$surveyid."_%");
        $result=db_execute_num($query) or safe_die("Couldn't get old table list<br />".$query."<br />".$connect->ErrorMsg());
        $tcount=$result->RecordCount();
        if ($tcount > 0)
        {
            while($rows=$result->FetchRow())
            {
                $oldlist[]=$rows[0];
            }
        }
        $tokenoutput .= "\t</div><div class='messagebox'>\n"
        ."<div class='warningheader'>".$clang->gT("Warning")."</div>\n"
        ."<br /><strong>".$clang->gT("Tokens have not been initialised for this survey.")."</strong><br /><br />\n";
        if (bHasSurveyPermission($surveyid, 'surveyactivation','update'))
        {
            $tokenoutput .= "".$clang->gT("If you initialise tokens for this survey then this survey will only be accessible to users who provide a token either smanually or by URL.")
            ."<br /><br />\n";

            $thissurvey=getSurveyInfo($surveyid);

            if ($thissurvey['private'] == 'Y')
            {
                $tokenoutput .= "".$clang->gT("Note: If you turn on the -Anonymous answers- option for this survey then LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.")
                ."<br /><br />\n";
            }

            $tokenoutput .= $clang->gT("Do you want to create a token table for this survey?");
            $tokenoutput .= "<br /><br />\n";
            $tokenoutput .= "<input type='submit' value='"
            .$clang->gT("Initialise tokens")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;createtable=Y")."\" />\n";
            $tokenoutput .= "<input type='submit' value='"
            .$clang->gT("No, thanks.")."' onclick=\"window.open('{$scriptname}?sid=$surveyid', '_top')\" /></div>\n";
        }
        else
        {
            $tokenoutput .= $clang->gT("You don't have the permission to activate tokens.");
            $tokenoutput .= "<input type='submit' value='"
            .$clang->gT("Back to main menu")."' onclick=\"window.open('{$scriptname}?sid=$surveyid', '_top')\" /></div>\n";

        }
        // Do not offer old postgres token tables for restore since these are having an issue with missing index
        if ($tcount>0 && $databasetype!='postgres' && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
        {
            $tokenoutput .= "<br /><div class='header'>".$clang->gT("Restore options")."</div>\n"
            ."<div class='messagebox'>\n"
            ."<form method='post' action='$scriptname?action=tokens'>\n"
            .$clang->gT("The following old token tables could be restored:")."<br /><br />\n"
            ."<select size='4' name='oldtable' style='width:250px;'>\n";
            foreach($oldlist as $ol)
            {
                $tokenoutput .= "<option>".$ol."</option>\n";
            }
            $tokenoutput .= "</select><br /><br />\n"
            ."<input type='submit' value='".$clang->gT("Restore")."' />\n"
            ."<input type='hidden' name='restoretable' value='Y' />\n"
            ."<input type='hidden' name='sid' value='$surveyid' />\n"
            ."</form></div>\n";
        }

        return;
    }
}


#Lookup the names of the attributes
/*$query = "SELECT attribute1, attribute2 FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
 $result = db_execute_assoc($query) or safe_die("Couldn't execute query: <br />$query<br />".$connect->ErrorMsg());
 $row = $result->FetchRow();
 if ($row["attribute1"]) {$attr1_name = $row["attribute1"];} else {$attr1_name=$clang->gT("Attribute 1");}
 if ($row["attribute2"]) {$attr2_name = $row["attribute2"];} else {$attr2_name=$clang->gT("Attribute 2");}*/

// IF WE MADE IT THIS FAR, THEN THERE IS A TOKENS TABLE, SO LETS DEVELOP THE MENU ITEMS
if($subaction != 'bounceprocessing')
{

$tokenoutput .= "\t<div class='menubar-main'>\n"
."<div class='menubar-left'>\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" "
."title='".$clang->gTview("Return to survey administration")."'>"
."<img name='HomeButton' src='$imageurl/home.png' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
."<img src='$imageurl/blank.gif' alt='' width='11' />\n"
."<img src='$imageurl/seperator.gif' alt='' />\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" title='".$clang->gTview("Show token summary")."' >"
."<img name='SummaryButton' src='$imageurl/summary.png' alt='".$clang->gT("Show token summary")."' /></a>\n"
."<img src='$imageurl/seperator.gif' alt='' />\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse', '_top')\" "
."title='".$clang->gTview("Display tokens")."' >"
."<img name='ViewAllButton' src='$imageurl/document.png' alt='".$clang->gT("Display tokens")."' /></a>\n";
if (bHasSurveyPermission($surveyid, 'tokens','create'))
{
    $tokenoutput .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnew', '_top')\""
    ."title='".$clang->gTview("Add new token entry")."' >"
    ."<img name='AddNewButton' src='$imageurl/add.png' title='' alt='".$clang->gT("Add new token entry")."' /></a>\n";
}
if (bHasSurveyPermission($surveyid, 'tokens','update'))
{
    $tokenoutput .= "<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=managetokenattributes', '_top')\" "
    ."title='".$clang->gTview("Manage additional attribute fields")."'>"
    ."<img name='ManageAttributesButton' src='$imageurl/token_manage.png' title='' alt='".$clang->gT("Manage additional attribute fields")."' /></a>\n";
}
if (bHasSurveyPermission($surveyid, 'tokens','import'))
{
    $tokenoutput .= "<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=import', '_top')\" "
    ."title='".$clang->gTview("Import tokens from CSV file")."'> "
    ."<img name='ImportButton' src='$imageurl/importcsv.png' title='' alt='".$clang->gT("Import tokens from CSV file")."' /></a>"
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=importldap', '_top')\" "
    ."title='".$clang->gTview("Import tokens from LDAP query")."'> <img name='ImportLdapButton' src='$imageurl/importldap.png' alt='".$clang->gT("Import tokens from LDAP query")."' /></a>";
}

if (bHasSurveyPermission($surveyid, 'tokens','export'))
{
    $tokenoutput .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=exportdialog', '_top')\" "
    ."title='".$clang->gTview("Export tokens to CSV file")."'>".
	"<img name='ExportButton' src='$imageurl/exportcsv.png' alt='".$clang->gT("Export tokens to CSV file")."' /></a>\n";
}
if (bHasSurveyPermission($surveyid, 'tokens','update'))
{
    $tokenoutput .= "<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=emailsettings', '_top')\" "
    ."title='".$clang->gTview("Edit email templates")."'>"
    ."<img name='EmailSettingsButton' src='$imageurl/emailsettings.png' alt='".$clang->gT("Edit email templates")."' /></a>\n"
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=email', '_top')\" "
    ."title='".$clang->gTview("Send email invitation")."'>"
    ."<img name='InviteButton' src='$imageurl/invite.png' alt='".$clang->gT("Send email invitation")."' /></a>\n"
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=remind', '_top')\" "
    ."title='".$clang->gTview("Send email reminder")."'>"
    ."<img name='RemindButton' src='$imageurl/remind.png' alt='".$clang->gT("Send email reminder")."' /></a>\n"

    ."<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify")."\" "
    ." title='".$clang->gTview("Generate tokens")."'>"
    ."<img name='TokenifyButton' src='$imageurl/tokenify.png' alt='".$clang->gT("Generate tokens")."' /></a>\n"
    ."<img src='$imageurl/seperator.gif' alt='' />\n";
}
if (bHasSurveyPermission($surveyid, 'surveyactivation','update'))
{
    $tokenoutput .="<a href=\"#\" onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=kill")."\" "
    ."title='".$clang->gTview("Drop tokens table")."' >"
    ."<img name='DeleteTokensButton' src='$imageurl/delete.png' alt='".$clang->gT("Drop tokens table")."' /></a>\n"
    ."<img src='$imageurl/seperator.gif' alt='' />\n";
}
if (bHasSurveyPermission($surveyid, 'tokens','update'))
{
    $tokenoutput .="<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=bouncesettings', '_top')\" "
    ."title='".$clang->gTview("Bounce processing settings")."' >"
    ."<img name='BounceSettings' src='$imageurl/bounce_settings.png' alt='".$clang->gT("Bounce Settings")."' /></a>\n";
}
if (!tableExists('grouptokens_'.$surveyid) || !tableExists('usedtokens_'.$surveyid))
{
	$tokenoutput .= "<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=usegrouptokens")."\" "
	."title='".$clang->gTview("Use group tokens")."' >"
	."<img name='UseGroupTokensButton' src='$imageurl/tokens.png' alt='".$clang->gT("Use group tokens")."' /></a>\n";
}

if (tableExists('grouptokens_'.$surveyid) && tableExists('usedtokens_'.$surveyid))
{
	$tokenoutput .= "<img src='$imageurl/seperator.gif' alt='' />\n"
	."<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup', '_top')\" "
	."title='".$clang->gTview("Display group tokens")."' >"
	."<img name='ViewGroupTokensButton' src='$imageurl/grouptokens.png' alt='".$clang->gT("Display group tokens")."' /></a>\n";

	if (bHasSurveyPermission($surveyid,'tokens','update'))
	{
		$tokenoutput .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnewgroup', '_top')\""
		."title='".$clang->gTview("Add new group token entry")."' >"
		."<img name='AddNewGroupTokenButton' src='$imageurl/addgrouptoken.png' title='' alt='".$clang->gT("Add new group token entry")."' /></a>\n"
    ."<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenifygroup")."\" "
    ." title='".$clang->gTview("Generate group tokens")."'>"
    ."<img name='TokenifyGroupButton' src='$imageurl/tokenifygroup.png' alt='".$clang->gT("G
    enerate group tokens")."' /></a>\n"
    ."<img src='$imageurl/seperator.gif' alt='' />\n"
    ."<a href=\"#\" onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=killgroup")."\" "
    ."title='".$clang->gTview("Drop group tokens table")."' >"
    ."<img name='DeleteGroupTokensButton' src='$imageurl/deletegroup.png' alt='".$clang->gT("Drop group tokens table")."' /></a>\n";
	}
}

$tokenoutput .="</div><div class='menubar-right'><a href=\"#\" onclick=\"showhelp('show')\" "
." title='".$clang->gTview("Show help")."'>"
."<img src='$imageurl/showhelp.png' align='right' alt='".$clang->gT("Show help")."' /></a>\n";


$tokenoutput .= "\t</div></div></div>\n";
}
// SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
$tksq = "SELECT count(tid) FROM ".db_table_name("tokens_$surveyid");
$tksr = db_execute_num($tksq);
$tkr = $tksr->FetchRow();
$tkcount = $tkr[0];

// GIVE SOME INFORMATION ABOUT THE TOKENS
if ($subaction=='')
{
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Token summary")."</div>\n"
    ."<br /><table align='center' class='statisticssummary'>\n"
    ."\t<tr>\n"
    ."<th>\n"
    .$clang->gT("Total records in this token table")."</th><td> $tkcount</td></tr><tr>\n";



    $tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE token IS NULL OR token=''";
    $tksr = db_execute_num($tksq);
    while ($tkr = $tksr->FetchRow())
    {$tokenoutput .= "<th>".$clang->gT("Total with no unique Token")."</th><td> $tkr[0] / $tkcount</td></tr><tr>\n";}

    $tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE (sent!='N' and sent<>'')";
    $tksr = db_execute_num($tksq);
    while ($tkr = $tksr->FetchRow())
    {$tokenoutput .= "<th>".$clang->gT("Total invitations sent")."</th><td> $tkr[0] / $tkcount</td></tr><tr>\n";}

    $tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE (completed!='N' and completed<>'')";
    $tksr = db_execute_num($tksq) or safe_die ("Couldn't execute token selection query<br />$abquery<br />".$connect->ErrorMsg());
    while ($tkr = $tksr->FetchRow())
    {$tokenoutput .= "<th>".$clang->gT("Total surveys completed")."</th><td> $tkr[0] / $tkcount\n";}
    $tokenoutput .= "</td>\n"
    ."\t</tr>\n"
    ."</table><br />\n";
    
    // Group token information
    if (tableExists('grouptokens_'.$surveyid) && tableExists('usedtokens_'.$surveyid))
    {
		$gtksq = "SELECT count(gtid) FROM ".db_table_name("grouptokens_$surveyid");
		$gtksr = db_execute_num($gtksq);
		$gtkr = $gtksr->FetchRow();
		$gtkcount = $gtkr[0];
		
		$tokenoutput .= "\t<div class='header'>".$clang->gT("Group token summary")."</div>\n"
		."<br /><table align='center' class='statisticssummary'>\n"
		."\t<tr>\n"
		."<th>\n"
		.$clang->gT("Total records in group token table")."</th><td> $gtkcount</td></tr><tr>\n";
		
		$gtksq = "SELECT count(*) FROM ".db_table_name("grouptokens_$surveyid")." WHERE token IS NULL OR token=''";
		$gtksr = db_execute_num($gtksq);
		while ($gtkr = $gtksr->FetchRow())
		{$tokenoutput .= "<th>".$clang->gT("Total with no unique Token")."</th><td> $gtkr[0] / $gtkcount</td></tr><tr>\n";}
		
		$gtksq = "SELECT sum(completedsurveys) FROM ".db_table_name("grouptokens_$surveyid")."";
		$gtksr = db_execute_num($gtksq);
		while ($gtkr = $gtksr->FetchRow())
		{$tokenoutput .= "<th>".$clang->gT("Total surveys completed")."</th><td> $gtkr[0]</td></tr>\n";}
		
		$tokenoutput .= "</table><br />\n";
}
}

#############################################################################################
// NOW FOR VARIOUS ACTIONS:

if(isset($surveyid) && getEmailFormat($surveyid) == 'html')
{
    $ishtml=true;
}
else
{
    $ishtml=false;
}

if ($subaction == "exportdialog" && bHasSurveyPermission($surveyid, 'tokens','export') )//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
{
    $langquery = "SELECT language FROM ".db_table_name("tokens_$surveyid")." group by language";
    $langresult = db_execute_assoc($langquery);


    $tokenoutput .= "\t<div class='header'>".$clang->gT("Token export options")."</div>\n";
    $tokenoutput .= "<form id='exportdialog' name='exportdialog' action='$scriptname' method='post'>\n"
    ."<ul><li><label for='tokenstatus'>".$clang->gT('Token status:')."</label><select id='tokenstatus' name='tokenstatus' >"
    ."<option selected='selected' value='0'>".$clang->gT('All tokens')."</option>"
    ."<option value='1'>".$clang->gT('Completed')."</option>"
    ."<option value='2'>".$clang->gT('Not started')."</option>";
    if ($thissurvey['private']=='N')
    {
        $tokenoutput.="<option value='3'>".$clang->gT('Started but not yet completed')."</option>";
    }
    $tokenoutput.="</select></li>"
    ."<li><label for='invitationstatus'>".$clang->gT('Invitation status:')."</label><select id='invitationstatus' name='invitationstatus' >"
    ."<option selected='selected' value='0'>".$clang->gT('All')."</option>"
    ."<option value='1'>".$clang->gT('Invited')."</option>"
    ."<option value='2'>".$clang->gT('Not invited')."</option>";
    $tokenoutput.="</select></li>"
    ."<li><label for='reminderstatus'>".$clang->gT('Reminder status:')."</label><select id='reminderstatus' name='reminderstatus' >"
    ."<option selected='selected' value='0'>".$clang->gT('All')."</option>"
    ."<option value='1'>".$clang->gT('Reminder(s) sent')."</option>"
    ."<option value='2'>".$clang->gT('No reminder(s) sent')."</option>";
    $tokenoutput.="</select></li>"
    ."<li><label for='tokenlanguage' >".$clang->gT('Filter by language')."</label><select id='tokenlanguage' name='tokenlanguage' >"
    ."<option selected='selected' value=''>".$clang->gT('All')."</option>";
    while ($lrow = $langresult->FetchRow())
    {
        $tokenoutput.="<option value='{$lrow['language']}'>".getLanguageNameFromCode($lrow['language'])."</option>";
    }

    $tokenoutput.="</select> </li>"
    ."<li><label for='filteremail' >".$clang->gT('Filter by email address')."</label><input type='text' id='filteremail' name='filteremail' /></li>"
    ."<li>&nbsp;</li>"
    //   ."<li><label for='tokendeleteexported' >".$clang->gT('Delete exported tokens')."</label><input type='checkbox' id='tokendeleteexported' name='tokendeleteexported' /> </li>"
    ."</ul>"
    ."<p><input type='submit' name='submit' value='".$clang->gT('Export tokens')."' />"
    ."<input type='hidden' name='action' id='action' value='tokens' />"
    ."<input type='hidden' name='sid' id='sid' value='$surveyid' />"
    ."<input type='hidden' name='subaction' id='subaction' value='export' />"
    ."</p></form>";

}

$tokenoutput .= "<script language='javascript' type='text/javascript'>"
	         ."surveyid = '$surveyid'"
                 ."</script>";

if($subaction=="surveysettingsave")
{
	global $connect;
	@$fieldvalue = array("bounceprocessing"=>$_POST['bounceprocessing'],
	"bounce_email"=>$_POST['bounce_email'],
	);
		
						if(@$_POST['bounceprocessing']=='L')
						{
							$fieldvalue['bounceaccountencryption']=$_POST['bounceaccountencryption'];
							$fieldvalue['bounceaccountuser']=$_POST['bounceaccountuser'];
							$fieldvalue['bounceaccountpass']=$_POST['bounceaccountpass'];
							$fieldvalue['bounceaccounttype']=$_POST['bounceaccounttype'];
							$fieldvalue['bounceaccounthost']=$_POST['bounceaccounthost'];
						}
						
	$connect->AutoExecute("{$dbprefix}surveys", $fieldvalue,
                        2,"sid=$surveyid");
    $tokenoutput .= "<div class='header'>".$clang->gT("Bounce settings")."</div>\n"
    ."<div class='messagebox'>"
    ."\t<div class='successheader'>".$clang->gT("Bounce settings have been saved.")."</div>\n"
    ."</div>";
                        
}

if ($subaction=='bouncesettings'){           
    
$settings=getSurveyInfo($surveyid);
$tokenoutput .= "\t<div class='header'>".$clang->gT("Bounce settings")."</div>\n";
$tokenoutput .= "<div id='bouncesettings'>\n"
			."<form id='bouncesettings' name='bouncesettings' action='$scriptname?action=tokens&sid=$surveyid&subaction=surveysettingsave' method='post'>"
	        ."\t\n<br><li><label for='bounce_email'>".$clang->gT('Survey bounce email:')."</label>\n"
            ."\t\t<input type='text' size='50' id='bounce_email' name='bounce_email' value=\"".htmlspecialchars(getGlobalSetting('siteadminbounce'))."\" /></li>\n"
			."\t<li><label for='bounceprocessing'>".$clang->gT("Bounce Settings to be used")."</label>\n"
	        ."\t\t<select id='bounceprocessing' name='bounceprocessing'>\n"
    	    ."\t\t\t<option value='N'";
            if ($settings['bounceprocessing']=='N') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("No settings to be used")."</option>\n"
            . "\t\t\t<option value='L'";
            if ($settings['bounceprocessing']=='L') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("Use Local Settings")."</option>\n"
            . "\t\t\t<option value='G'";
            if ($settings['bounceprocessing']=='G') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("Use Global Settings")."</option>\n"
            ."\t\t</select></li>\n"
            
            . "\t<li><label for='bounceaccounttype'>".$clang->gT("Bounce account type:")."</label>\n"
	        . "\t\t<select id='bounceaccounttype' name='bounceaccounttype'>\n"
    	    . "\t\t\t<option value='Off'";
            if ($settings['bounceaccounttype']=='Off') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t\t<option value='IMAP'";
            if ($settings['bounceaccounttype']=='IMAP') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("IMAP")."</option>\n"
            . "\t\t\t<option value='POP'";
            if ($settings['bounceaccounttype']=='POP') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("POP")."</option>\n"
            ."\t\t</select></li>\n"
            
            . "\t<li><label for='bounceaccounthost'>".$clang->gT("Bounce account host:")."</label>\n"
            . "\t\t<input type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value=\"".$settings['bounceaccounthost']."\" />\n"."<font size='1'>".$clang->gT("Enter your hostname and port, e.g.: imap.gmail.com:995")."</font>\n"
            . "\t<li><label for='bounceaccountuser'>".$clang->gT("Bounce account user:")."</label>\n"
            . "\t\t<input type='text' size='50' id='bounceaccountuser' name='bounceaccountuser' value=\"".$settings['bounceaccountuser']."\" /></li>\n"
            . "\t<li><label for='bounceaccountpass'>".$clang->gT("Bounce account password:")."</label>\n"
            . "\t\t<input type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value=\"".$settings['bounceaccountpass']."\"/></li>\n";
			$tokenoutput.= "\t<li><label for='bounceencryption'>".$clang->gT("Bounce account encryption type")."</label>\n"
			. "\t\t<select id='bounceaccountencryption' name='bounceaccountencryption'>\n"
			. "\t\t\t<option value='O'";
            if ($settings['bounceaccountencryption']=='Off') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t\t<option value='SSL'";
            if ($settings['bounceaccountencryption']=='SSL') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("SSL")."</option>\n"
            . "\t\t\t<option value='TLS'";
            if ($settings['bounceaccountencryption']=='TLS') {$tokenoutput .= " selected='selected'";}
            $tokenoutput .= ">".$clang->gT("TLS")."</option>\n"
            ."\t\t</select></li>\n<br></div>"."</form>";
            $tokenoutput .= "\t<p><input type='button' onclick='bouncesettings.submit()' class='standardbtn' value='".$clang->gT("Save settings")."' /><br /></p>\n";
            
          }
if ($subaction == "emailsettings")
{
    $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($grplangs,$baselang);

    $tokenoutput .= PrepareEditorScript();
    $tokenoutput .="<tr><td align='center'>"
    . "<div class='header'>\n"
    . "".$clang->gT("Edit email settings")."</div>\n"
    . "<form class='form30' name='frmemailsettings' action='$scriptname' method='post'>\n"
    . '<div class="tab-pane" id="tab-pane-emailsettings-'.$surveyid.'">';
    $surveyinfo=getSurveyInfo($surveyid);
    foreach ($grplangs as $grouplang)
    {
        // this one is created to get the right default texts fo each language
        $bplang = new limesurvey_lang($grouplang);
        $esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
        $esresult = db_execute_assoc($esquery);
        $esrow = $esresult->FetchRow();
        $tokenoutput .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['surveyls_language'],false);
        if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid)) {$tokenoutput .= '('.$clang->gT("Base language").')';}
        $tokenoutput .= '</h2>';
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Invitation email subject:")."</span>\n"
        . "<span class='settingentry'><input type='text' size='80' name='email_invite_subj_".$esrow['surveyls_language']."' id='email_invite_subj_{$grouplang}' value=\"{$esrow['surveyls_email_invite_subj']}\" />\n"
        . "<input type='hidden' name='email_invite_subj_default_".$esrow['surveyls_language']."' id='email_invite_subj_default_{$grouplang}' value='".$bplang->gT("Invitation to participate in survey")."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_invite_subj_{$grouplang}\",\"email_invite_subj_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Invitation email:")."</span>\n"
        . "<span class='settingentry'><textarea cols='80' rows='10' name='email_invite_".$esrow['surveyls_language']."' id='email_invite_{$grouplang}'>{$esrow['surveyls_email_invite']}</textarea>\n"
        . getEditor("email-inv","email_invite_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_invite_default_".$esrow['surveyls_language']."' id='email_invite_default_{$grouplang}' value='".conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}"),$ishtml)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_invite_{$grouplang}\",\"email_invite_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Email reminder subject:")."</span>\n"
        . "<span class='settingentry'><input type='text' size='80' name='email_remind_subj_".$esrow['surveyls_language']."' id='email_remind_subj_{$grouplang}' value=\"{$esrow['surveyls_email_remind_subj']}\" />\n"
        . "<input type='hidden' name='email_remind_subj_default_".$esrow['surveyls_language']."' id='email_remind_subj_default_{$grouplang}' value='".$bplang->gT("Reminder to participate in survey")."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_remind_subj_{$grouplang}\",\"email_remind_subj_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Email reminder:")."</span>\n"
        . "<span class='settingentry'><textarea cols='80' rows='10' name='email_remind_".$esrow['surveyls_language']."' id='email_remind_{$grouplang}'>{$esrow['surveyls_email_remind']}</textarea>\n"
        . getEditor("email-rem","email_remind_{$grouplang}", "[".$clang->gT("Email reminder:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_remind_default_".$esrow['surveyls_language']."' id='email_remind_default_{$grouplang}' value='".conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}"),$ishtml)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_remind_{$grouplang}\",\"email_remind_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Confirmation email subject:")."</span>\n"
        . "<span class='settingentry'><input type='text' size='80' name='email_confirm_subj_".$esrow['surveyls_language']."' id='email_confirm_subj_{$grouplang}' value=\"{$esrow['surveyls_email_confirm_subj']}\" />\n"
        . "<input type='hidden' name='email_confirm_subj_default_".$esrow['surveyls_language']."' id='email_confirm_subj_default_{$grouplang}' value='".$bplang->gT("Confirmation of completed survey")."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_confirm_subj_{$grouplang}\",\"email_confirm_subj_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Confirmation email:")."</span>\n"
        . "<span class='settingentry'><textarea cols='80' rows='10' name='email_confirm_".$esrow['surveyls_language']."' id='email_confirm_{$grouplang}'>{$esrow['surveyls_email_confirm']}</textarea>\n"
        . getEditor("email-conf","email_confirm_{$grouplang}", "[".$clang->gT("Confirmation email", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_confirm_default_".$esrow['surveyls_language']."' id='email_confirm_default_{$grouplang}' value='".conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}"),$ishtml)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_confirm_{$grouplang}\",\"email_confirm_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Public registration email subject:")."</span>\n"
        . "<span class='settingentry'><input type='text' size='80' name='email_register_subj_".$esrow['surveyls_language']."' id='email_register_subj_{$grouplang}' value=\"{$esrow['surveyls_email_register_subj']}\" />\n"
        . "<input type='hidden' name='email_register_subj_default_".$esrow['surveyls_language']."' id='email_register_subj_default_{$grouplang}' value='".$bplang->gT("Survey registration confirmation")."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript:  fillin(\"email_register_subj_{$grouplang}\",\"email_register_subj_default_{$grouplang}\")' />\n"
        . "\t</span></div>\n";
        $tokenoutput .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Public registration email:")."</span>\n"
        . "<span class='settingentry'><textarea cols='80' rows='10' name='email_register_{$grouplang}' id='email_register_{$grouplang}'>{$esrow['surveyls_email_register']}</textarea>\n"
        . getEditor("email-reg","email_register_{$grouplang}", "[".$clang->gT("Public registration email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_register_default_".$esrow['surveyls_language']."' id='email_register_default_{$grouplang}' value='".conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}."),$ishtml)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript:  fillin(\"email_register_{$grouplang}\",\"email_register_default_{$grouplang}\")' />\n"
        . "\t</span><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /></div>\n</div>";
    }
    $tokenoutput .= '</div>';
    $tokenoutput .= "\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
    . "\t<input type='hidden' name='action' value='tokens' />\n"
    . "\t<input type='hidden' name='subaction' value='updateemailsettings' />\n"
    . "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
    . "\t<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
    . "\t</p>\n"
    . "</form></td></tr></table>\n";
}

// Save the updated email settings
if ($subaction == "updateemailsettings" && bHasSurveyPermission($surveyid, 'tokens','update')
)
{
    $_POST  = array_map('db_quote', $_POST);
    $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
    $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
    foreach ($languagelist as $langname)
    {
        if ($langname)
        {
            $usquery = "UPDATE ".db_table_name('surveys_languagesettings')." \n"
            . "SET surveyls_email_invite_subj='".$_POST['email_invite_subj_'.$langname]."',\n"
            . "surveyls_email_invite='".$_POST['email_invite_'.$langname]."', surveyls_email_remind_subj='".$_POST['email_remind_subj_'.$langname]."',\n"
            . "surveyls_email_remind='".$_POST['email_remind_'.$langname]."', surveyls_email_register_subj='".$_POST['email_register_subj_'.$langname]."',\n"
            . "surveyls_email_register='".$_POST['email_register_'.$langname]."', surveyls_email_confirm_subj='".$_POST['email_confirm_subj_'.$langname]."',\n"
            . "surveyls_email_confirm='".$_POST['email_confirm_'.$langname]."'\n"
            . "WHERE surveyls_survey_id=".$surveyid." and surveyls_language='".$langname."'";
            $usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br />".$connect->ErrorMsg());
        }
    }
    $tokenoutput .= "<div class='header'>".$clang->gT("Edit email settings")."</div>\n"
    ."<div class='messagebox'>"
    ."\t<div class='successheader'>".$clang->gT("Token email settings have been saved.")."</div>\n"
    ."</div>";
}

if ($subaction == "deleteall" && bHasSurveyPermission($surveyid, 'tokens', 'delete')){
    $query="DELETE FROM ".db_table_name("tokens_$surveyid");
    $result=$connect->Execute($query) or safe_die ("Couldn't update sent field<br />$query<br />".$connect->ErrorMsg());
    $tokenoutput .= "<div class='header'>".$clang->gT("Delete all token entries")."</div>\n"
    ."<div class='messagebox'><div class='successheader'>".$clang->gT("All token entries have been deleted.")."</div></div><br />\n";
    $subaction="";
}

if ($subaction == "clearinvites" && bHasSurveyPermission($surveyid, 'tokens', 'update'))
{
    $query="UPDATE ".db_table_name("tokens_$surveyid")." SET sent='N', remindersent='N', remindercount=0";
    $result=$connect->Execute($query) or safe_die ("Couldn't update sent field<br />$query<br />".$connect->ErrorMsg());
    $tokenoutput .= "<div class='header'>".$clang->gT("Reset token invitation status")."</div>\n"
    ."<div class='messagebox'><div class='successheader'>".$clang->gT("All token entries have been set to 'Not invited'.")."</div></div><br />\n";
    $subaction="";
}

if ($subaction == "cleartokens" && bHasSurveyPermission($surveyid, 'tokens', 'update'))    
{
    $query="UPDATE ".db_table_name("tokens_$surveyid")." SET token=''";
    $result=$connect->Execute($query) or safe_die("Couldn't reset the tokens field<br />$query<br />".$connect->ErrorMsg());
    $tokenoutput .= "<div class='header'>".$clang->gT("Remove unique token numbers")."</div>\n"
    ."<div class='messagebox'><div class='successheader'>".$clang->gT("All unique token numbers have been removed.")."</div></div><br />\n";
    $subaction="";
}


if (!$subaction && (bHasSurveyPermission($surveyid, 'tokens', 'update') || bHasSurveyPermission($surveyid, 'tokens', 'delete')))
{
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Token database administration options")."</div>\n"
    ."<div style='width:30%; margin:0 auto;'>";

    if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
    {
        $tokenoutput .="<ul><li><a href='#' onclick=\"if( confirm('"
        .$clang->gT("Are you really sure you want to reset all invitation records to NO?","js")."')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=clearinvites")."}\">".$clang->gT("Set all entries to 'No invitation sent'.")."</a></li>\n"
        ."<li><a href='#' onclick=\"if ( confirm('"
        .$clang->gT("Are you sure you want to delete all unique token strings?","js")."')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=cleartokens")."}\">".$clang->gT("Delete all unique token strings")."</a></li>\n";
    }
    if (bHasSurveyPermission($surveyid, 'tokens', 'delete'))
    {
        
        $tokenoutput .="<li><a href='#' onclick=\" if (confirm('"
        .$clang->gT("Are you really sure you want to delete ALL token entries?","js")."')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=deleteall")."}\">".$clang->gT("Delete all token entries")."</a></li>\n";
    }
    $tokenoutput .= "</ul></div>\n";
}

if ($subaction == "usegrouptokens" && bHasSurveyPermission($surveyid,'tokens','update'))
{
	if (isset($_POST['creategrouptable']) && $_POST['creategrouptable']=="Y"
	// make sure there are no group token tables
	 && !tableExists('grouptokens_'.$surveyid) && !tableExists('usedtokens_'.$surveyid))
	{
		$creategrouptokentable=
	    "gtid int I NOTNULL AUTO PRIMARY,\n "
	    . "name C(40),\n "
	    . "description C(200),\n"
	    . "token C(36) ,\n "
	    . "language C(25) ,\n "
	    . "completedsurveys I DEFAULT 0,\n "
	    . "validfrom T ,\n "
	    . "validuntil T ";

	    $tabname = "{$dbprefix}grouptokens_{$surveyid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
	    $taboptarray = array('mysql' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
						     'mysqli' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
	    $dict = NewDataDictionary($connect);
	    $sqlarray = $dict->CreateTableSQL($tabname, $creategrouptokentable, $taboptarray);
	    $execresult=$dict->ExecuteSQLArray($sqlarray, false);

	    if ($execresult==0 || $execresult==1)
	    {

		    $tokenoutput .= "\t</div><div class='messagebox'>\n"
		    ."<font size='1'><strong><center>".$clang->gT("Group token table could not be created.")."</center></strong></font>\n"
		    .$clang->gT("Error").": \n<font color='red'>" . $connect->ErrorMsg() . "</font>\n"
		    ."<pre>".htmlspecialchars(implode(" ",$sqlarray))."</pre>\n"
		    ."<br />"
		    ."<input type='submit' value='"
		    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n"
		    ."</div>\n"
		    ."</div>\n";

	    }
	    else
	    {
		    $tokenoutput .= "\t<br><p>\n"
		    ."".$clang->gT("A group token table has been created for this survey.")." (\"".$dbprefix."grouptokens_$surveyid\")<br /><br />\n";
		    
		    // create table of pairs of used tokens (association table token-grouptoken)
		    $createusedtokens=
		    "id I NOTNULL PRIMARY,\n "
		    . "tid I NOTNULL,\n"
		    . "gtid I NOTNULL";

		    $tabname = "{$dbprefix}usedtokens_{$surveyid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
		    $taboptarray = array('mysql' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
							     'mysqli' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		    $dict = NewDataDictionary($connect);
		    $sqlarray = $dict->CreateTableSQL($tabname, $createusedtokens, $taboptarray);
		    $execresult = $dict->ExecuteSQLArray($sqlarray, false);
							    
		    if ($execresult==0 || $execresult==1)
		    {
			    $tokenoutput .= "\t</div><div class='messagebox'>\n"
			    ."<font size='1'><strong><center>".$clang->gT("Used token table could not be created.")."</center></strong></font>\n"
			    .$clang->gT("Error").": \n<font color='red'>" . $connect->ErrorMsg() . "</font>\n"
			    ."<pre>".htmlspecialchars(implode(" ",$sqlarray))."</pre>\n"
			    ."<br />"
			    ."<input type='submit' value='"
			    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n"
			    ."</div>\n"
			    ."</div>\n";
		    }
		    else
		    {
			    // set unique index on fields 'tid' and 'gtid'
			    $sqlarray = $dict->CreateIndexSQL("unique_{$tabname}", $tabname, array('tid', 'gtid'), array('unique'));
			    $execresult = $dict->ExecuteSQLArray($sqlarray, false);
			    
			    $tokenoutput .= "\t<br><p>\n"
			    ."".$clang->gT("A used tokens table has been created for this survey.")." (\"".$tabname."\")<br /><br /><br />\n"
			    ."<input type='submit' value='"
			    .$clang->gT("Continue")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n";
		    }
	    }
	}
	elseif (returnglobal('restoregrouptable') == "Y" && returnglobal('oldtable'))
    {
        $query = db_rename_table(returnglobal('oldtable') , db_table_name_nq("grouptokens_$surveyid"));
        $result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
		
		$oldusedtable = str_replace('grouptokens', 'usedtokens', returnglobal('oldtable'));
		$query = db_rename_table($oldusedtable , db_table_name_nq("usedtokens_$surveyid"));
		$result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
		
		$tokenoutput .= "\t<div class='messagebox'>\n"
        ."<div class='header'>".$clang->gT("Import old group tokens")."</div>"
        ."<br />".$clang->gT("A group token table has been created for this survey and the old group tokens were imported.")." (\"".$dbprefix."grouptokens_$surveyid\")<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Continue")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n"
        ."</div>\n";
        return;
    }
	else
    {
        $query=db_select_tables_like("{$dbprefix}old_grouptokens_".$surveyid."_%");
        $result=db_execute_num($query) or safe_die("Couldn't get old table list<br />".$query."<br />".$connect->ErrorMsg());
        $tcount=$result->RecordCount();
        if ($tcount > 0)
        {
            while($rows=$result->FetchRow())
            {
                $oldlist[]=$rows[0];
            }
        }
        $tokenoutput .= "\t<div class='messagebox'>\n"
        ."<div class='warningheader'>".$clang->gT("Warning")."</div>\n"
        ."<br /><strong>".$clang->gT("Group tokens have not been initialised for this survey.")."</strong><br /><br />\n"
		.$clang->gT("If you initialise group tokens for this survey then this survey will only be accessible to users who provide a group token either manually or by URL.")
        ."<br /><br />\n"
		.$clang->gT("Do you want to create a group token table for this survey?")."<br /><br />\n"
		."<input type='submit' value='".$clang->gT("Initialise group tokens")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=usegrouptokens&amp;creategrouptable=Y")."\" />\n"
        ."<input type='submit' value='".$clang->gT("No, thanks.")."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid=$surveyid', '_top')\" /></div>\n";
		
        // Do not offer old postgres token tables for restore since these are having an issue with missing index
        if ($tcount>0 && $databasetype!='postgres')
        {
            $tokenoutput .= "<br /><div class='header'>".$clang->gT("Restore options")."</div>\n"
            ."<div class='messagebox'>\n"
            ."<form method='post' action='$scriptname?action=tokens&amp;subaction=usegrouptokens'>\n"
            .$clang->gT("The following old group token tables could be restored:")."<br /><br />\n"
            ."<select size='4' name='oldtable' style='width:250px;'>\n";
            foreach($oldlist as $ol)
            {
                $tokenoutput .= "<option>".$ol."</option>\n";
            }
            $tokenoutput .= "</select><br /><br />\n"
            ."<input type='submit' value='".$clang->gT("Restore")."' />\n"
            ."<input type='hidden' name='restoregrouptable' value='Y' />\n"
            ."<input type='hidden' name='sid' value='$surveyid' />\n"
            ."</form></div>\n";
        }
        return;
    }
}

if ($subaction == "browse" || $subaction == "search")
{
    if (!isset($limit)) {$limit = 100;}
    if (!isset($start)) {$start = 0;}

    if ($limit > $tkcount) {$limit=$tkcount;}
    $next=$start+$limit;
    $last=$start-$limit;
    $end=$tkcount-$limit;
    if ($end < 0) {$end=0;}
    if ($last <0) {$last=0;}
    if ($next >= $tkcount) {$next=$tkcount-$limit;}
    if ($end < 0) {$end=0;}
    $baselanguage = GetBaseLanguageFromSurveyID($surveyid);

    //ALLOW SELECTION OF NUMBER OF RECORDS SHOWN
if($subaction != 'bounceprocessing')
{
	$tokenoutput .="\t<div class='menubar'><div class='menubar-title'><span style='font-weight:bold;'>"
    .$clang->gT("Data view control")."</span></div>\n"
	."<div class='menubar-main'>\n";
    $tokenoutput .="<div class='menubar-left'>\n";
    if (bHasSurveyPermission($surveyid,'tokens','update'))
    {
        if($thissurvey['bounceprocessing']=='N')
        {
            $tokenoutput .="<image src='$imageurl/bounce_disabled.png'  alt='".$clang->gT("You have selected not to use any bounce settings")."'>";
        }
        else
        {
            $tokenoutput .="<image src='$imageurl/bounce.png' id='bounceprocessing' alt='".$clang->gT("Bounce Processing")."'>";
        }
        $tokenoutput .= "<img src='$imageurl/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
    }
    
    $tokenoutput .= "<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=0&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'"
    ." title='".$clang->gTview("Show start...")."'>"
    ."<img name='DBeginButton' align='left' src='$imageurl/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
    ."<a href='$scriptname?action=toknens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	" title='".$clang->gTview("Show previous...")."'>" 
	."<img name='DBackButton' align='left' src='$imageurl/databack.png' alt='".$clang->gT("Show previous...")."' /></a>\n"
	."<img src='$imageurl/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left' />\n"
	."<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	"title='".$clang->gTview("Show next...")."'>" .
	"<img name='DForwardButton' align='left' src='$imageurl/dataforward.png' alt='".$clang->gT("Show next...")."' /></a>\n"
	."<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	"title='".$clang->gTview("Show last...")."'>".
	"<img name='DEndButton' align='left'  src='$imageurl/dataend.png' alt='".$clang->gT("Show last...")."' /></a>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' hspace='0' align='left' /></form>\n";
}
$tokenoutput .="\t<form id='tokensearch' method='post' action='$scriptname?action=tokens'>\n"
	."<input type='text' name='searchstring' value='$searchstring' />\n"
	."<input type='submit' value='".$clang->gT("Search")."' />\n"
	."\t<input type='hidden' name='order' value='$order' />\n"
	."\t<input type='hidden' name='subaction' value='search' />\n"
	."\t<input type='hidden' name='sid' value='$surveyid' />\n"
	."\t</form>\n"
	."<form id='tokenrange' action='{$scriptname}'>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' />\n"
	."<font size='1' face='verdana'>"
	."&nbsp;<label for='limit'>".$clang->gT("Records displayed:")."</label> <input type='text' size='4' value='$limit' id='limit' name='limit' />"
	."&nbsp;&nbsp;<label for='start'>".$clang->gT("Starting from:")."</label> <input type='text' size='4' value='$start'  id='start' name='start' />"
	."&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
	."</font>\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='tokens' />\n"
	."<input type='hidden' name='subaction' value='browse' />\n"
	."<input type='hidden' name='order' value='$order' />\n"
	."<input type='hidden' name='searchstring' value='$searchstring' />\n"
	."</form>\n";
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
	if ($searchstring)
	{
	    $bquery .= " WHERE firstname LIKE '%$searchstring%' "
	    . "OR lastname LIKE '%$searchstring%' "
	    . "OR email LIKE '%$searchstring%' "
	    . "OR emailstatus LIKE '%$searchstring%' "
	    . "OR token LIKE '%$searchstring%'";
	}
	if (!isset($order) || !$order) {$bquery .= " ORDER BY tid";}
	else {$bquery .= " ORDER BY $order"; }

	$bresult = db_select_limit_assoc($bquery, $limit, $start) or safe_die ($clang->gT("Error").": $bquery<br />".$connect->ErrorMsg());
	$bgc="";

	$tokenoutput .= "</div></div></div>\n";

	$tokenoutput .= "<table class='browsetokens' id='browsetokens' cellpadding='1' cellspacing='1'>\n";
	//COLUMN HEADINGS
	$tokenoutput .= "\t<tr>\n"
	."<th><input type='checkbox' id='tokencheckboxtoggle'></th>\n"   //Checkbox
	."<th align='left' >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=tid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	."ID' alt='"
	.$clang->gT("Sort by: ")
	."ID' border='0' align='left' hspace='0' /></a>"."ID</th>\n" // ID
	."<th align='left'  >".$clang->gT("Actions")."</th>\n"  //Actions
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=firstname&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("First name")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("First name")
	."' border='0' align='left' /></a>".$clang->gT("First name")."</th>\n"
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=lastname&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Last name")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Last name")
	."' border='0' align='left' /></a>".$clang->gT("Last name")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=email&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Email address")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Email address")
	."' border='0' align='left' /></a>".$clang->gT("Email address")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=token&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Token")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Token")
	."' border='0' align='left' /></a>".$clang->gT("Token")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=language&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Language")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Language")
	."' border='0' align='left' /></a>".$clang->gT("Language")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=sent%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Invite sent?")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Invite sent?")
	."' border='0' align='left' /></a>".$clang->gT("Invite sent?")."</th>\n"
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=remindersent%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder sent?")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder sent?")
	."' border='0' align='left' /></a><span>".$clang->gT("Reminder sent?")."</span></th>\n"
	."<th align='left'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=remindercount%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder count")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder count")
	."' border='0' align='left' /></a><span>".$clang->gT("Reminder count")."</span></th>\n"
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=completed%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Completed?")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Completed?")
	."' border='0' align='left' /></a>".$clang->gT("Completed?")."</th>\n"
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=usesleft%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Uses left")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Uses left")
	."' border='0' align='left' /></a><span>".$clang->gT("Uses left")."</span></th>\n"
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=validfrom%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid from")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid from")
	."' border='0' align='left' /></a>".$clang->gT("Valid from")."</th>\n"
	."<th align='left'  >"


	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=validuntil%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid until")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid until")
	."' border='0' align='left' /></a>".$clang->gT("Valid until")."</th>\n";

	$attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
	foreach ($attrfieldnames as $attr_name=>$attr_translation)
	{
	    $tokenoutput .= "<th align='left' >"
	    ."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=$attr_name&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	    ."<img src='$imageurl/downarrow.png' alt='' title='"
	    .$clang->gT("Sort by: ").$attr_translation."' border='0' align='left' /></a>".$attr_translation."</th>\n";
	}
	$tokenoutput .="\t</tr>\n";

	$tokenfieldorder=array('tid',
                           'firstname',
                           'lastname',
                           'email',
                           'emailstatus',
                           'token',
                           'language',
                           'sent',
                           'remindersent',
                           'remindercount',
                           'completed',
                           'usesleft',
                           'validfrom',
                           'validuntil');
	foreach ($attrfieldnames as $attr_name=>$attr_translation)
	{
	    $tokenfieldorder[]=$attr_name;
	}

	while ($brow = $bresult->FetchRow())
	{
	    $brow['token'] = trim($brow['token']);
	    if (trim($brow['validfrom'])!=''){
	        $datetimeobj = new Date_Time_Converter($brow['validfrom'] , "Y-m-d H:i:s");
	        $brow['validfrom']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };
	    if (trim($brow['validuntil'])!=''){
	        $datetimeobj = new Date_Time_Converter($brow['validuntil'] , "Y-m-d H:i:s");
	        $brow['validuntil']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };

	    if ($bgc == "evenrow") {$bgc = "oddrow";} else {$bgc = "evenrow";}
	    $tokenoutput .= "\t<tr class='$bgc'>\n";

	    $tokenoutput .= "<td><input type='checkbox' name='".$brow['tid']."'></td>\n";

	    foreach ($tokenfieldorder as $tokenfieldname)
	    {

	        if ($tokenfieldname =='email' && $brow['emailstatus'] != 'OK')
	        {
	            if ($brow['emailstatus']!='OptOut')
	            {
	                $tokenoutput .= "<td>"
	                ."<a href=\"#\" class='invalidemail' title='".$clang->gT('Invalid email address:').htmlspecialchars($brow['emailstatus'])."' >"
	                ."$brow[$tokenfieldname]</a></td>\n";
	            }
	            else
	            {
	                $tokenoutput .= "<td>"
	                ."<a href=\"#\" class='optoutemail' title='".$clang->gT('This participant opted out of this survey.')."' >"
	                ."$brow[$tokenfieldname]</a></td>\n";
	            }
	        }
	        elseif ($tokenfieldname != 'emailstatus')
	        {
	            if  ($tokenfieldname=='tid')
	            {
	                $tokenoutput.="<td><span style='font-weight:bold'>".$brow[$tokenfieldname]."</span></td>";
	            }
	            else
	            {
	                $tokenoutput .= "<td>$brow[$tokenfieldname]</td>\n";
	            }
	        }
	        if ($tokenfieldname=='tid')
	        {
	            $tokenoutput .= "<td align='left' style='white-space:nowrap;'>\n";
	            if (bHasSurveyPermission($surveyid, 'tokens','update'))
	            {
	                if (($brow['completed'] == "N" || $brow['completed'] == "") &&$brow['token'])
	                {
	                    $toklang = ($brow['language'] == '') ? $baselanguage : $brow['language'];
	                    $tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/do_16.png' title='"
	                    .$clang->gT("Do Survey")
	                    ."' alt='"
	                    .$clang->gT("Do Survey")
	                    ."' onclick=\"window.open('{$publicurl}/index.php?sid={$surveyid}&amp;lang={$toklang}&amp;token=".trim($brow['token'])."', '_blank')\" />\n";
	                }
	                else
	                {
	                    $tokenoutput .= "<img src='{$imageurl}/blank.gif' height='16' width='16'/>";
	                }
	                $tokenoutput .="<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_edit.png' title='"
	                .$clang->gT("Edit token entry")
	                ."' alt='"
	                .$clang->gT("Edit token entry")
	                ."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=edit&amp;tid=".$brow['tid']."&amp;start={$start}&amp;limit={$limit}&amp;order={$order}', '_top')\" /> ";
	            }
                if (bHasSurveyPermission($surveyid, 'tokens','delete'))
                {
                    $tokenoutput .="<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_delete.png' title='"
                    .$clang->gT("Delete token entry")
                    ."' alt='"
                    .$clang->gT("Delete token entry")
                    ."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")." (".$brow['tid'].")')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=delete&amp;tid=".$brow['tid']."&amp;limit=$limit&amp;start=$start&amp;order=$order")."}\"  />";
                }
                
	            if ($brow['completed'] != "N" && $brow['completed']!="" && $surveyprivate == "N"  && $thissurvey['active']=='Y')
	            {
	                // Get response Id
	                $query="SELECT id FROM ".db_table_name('survey_'.$surveyid)." WHERE token='{$brow['token']}' ORDER BY id desc";
	                $result=db_execute_num($query) or safe_die ("<br />Could not find token!<br />\n" .$connect->ErrorMsg());
	                list($id) = $result->FetchRow();

	                // UPDATE button to the tokens display in the MPID Actions column
	                if  ($id)
	                {
	                    $tokenoutput .= "<input type='image' src='{$imageurl}/token_viewanswer.png' style='height: 16; width: 16px;' onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$id', '_top')\" type='submit'  title='"
	                    .$clang->gT("View/Update last response")
	                    ."' alt='"
	                    .$clang->gT("View/Update last response")
	                    ."' />\n";
	                }
	            }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] == "N" && trim($brow['email'])!='' && bHasSurveyPermission($surveyid, 'tokens','update'))
	            {
	                $tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_invite.png' title='"
	                .$clang->gT("Send invitation email to this entry")
	                ."' alt='"
	                .$clang->gT("Send invitation email to this entry")
	                ."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=email&amp;tid=".$brow['tid']."', '_top')\" />";
	            }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] != "N" && trim($brow['email'])!='')  // reminder button
	            {
	                $tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_remind.png' title='"
	                .$clang->gT("Send reminder email to this entry")
	                ."' alt='"
	                .$clang->gT("Send reminder email to this entry")
	                ."' onclick=\"window.open('{$scriptname}?sid={$surveyid}&amp;action=tokens&amp;subaction=remind&amp;tid={$brow['tid']}', '_top')\" />";
	            }
				// link to used group tokens
				if (tableExists('grouptokens_'.$surveyid) && tableExists('usedtokens_'.$surveyid))
				{
					$tokenoutput .= "<input type='image' src='$imageurl/tokens.png' style='height: 16; width: 16px;' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;usertoken={$brow['tid']}','_top')\" type='submit' title='"
					.$clang->gT("View used groups")
					."' alt='"
					.$clang->gT("View used groups")
					."' />\n";
				}
	            $tokenoutput .= "\n</td>\n";
	        }
	    }
	    $tokenoutput .= "\t</tr>\n";
	}

	// Multiple item actions
	if ($bresult->rowCount() > 0) {
	    $tokenoutput .= "<tr class='{$bgc}'>\n"
	    . "<td align='left' style='text-align: left' colspan='".(count($tokenfieldorder)+1)."'>";
        
        if (bHasSurveyPermission($surveyid, 'tokens','delete'))
        {
            $tokenoutput .= "<img src='{$imageurl}/blank.gif' height='16' width='16'/>"
            . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_delete.png' title='"
            .$clang->gT("Delete the selected entries")
            ."' alt='"
            .$clang->gT("Delete the selected entries")
            ."' onclick=\"if (confirm('"
            .$clang->gT("Are you sure you want to delete the selected entries?","js")
            ."')) {".get2post("{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tids=document.getElementById('tokenboxeschecked').value&amp;limit={$limit}&amp;start={$start}&amp;order={$order}")."}\"  />";
            
        }
        
        if (bHasSurveyPermission($surveyid, 'tokens','delete'))         
        {
            $tokenoutput .= "&nbsp;"
            . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_invite.png' title='"
            .$clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)")
            ."' alt='"
            .$clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)")
            ."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=email&amp;tids='+document.getElementById('tokenboxeschecked').value, '_top')\" />"
            . "&nbsp;"
            . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_remind.png' title='"
            .$clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)")
            ."' alt='"
            .$clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)")
            ."' onclick=\"window.open('{$scriptname}/?sid={$surveyid}&amp;action=tokens&amp;subaction=remind&amp;tids='+document.getElementById('tokenboxeschecked').value, '_top')\" />";
        }
	    $tokenoutput .= "</td>\n"
	    . "</tr>\n";
	    $tokenoutput .= "<input type='hidden' id='tokenboxeschecked' value='' onChange='alert(this.value)'>\n";
	}
	//End multiple item actions

	$tokenoutput .= "</table>\n<br />\n";
}

if ($subaction == "browsegroup" || $subaction == "searchgroup")
{
	// count group tokens
	$gtksq = "SELECT count(gtid) FROM ".db_table_name("grouptokens_$surveyid");
	$gtksr = db_execute_num($gtksq);
	$gtkr = $gtksr->FetchRow();
	$gtkcount = $gtkr[0];

    if (!isset($limit)) {$limit = 100;}
    if (!isset($start)) {$start = 0;}

    if ($limit > $gtkcount) {$limit=$gtkcount;}
    $next=$start+$limit;
    $last=$start-$limit;
    $end=$gtkcount-$limit;
    if ($end < 0) {$end=0;}
    if ($last <0) {$last=0;}
    if ($next >= $gtkcount) {$next=$gtkcount-$limit;}
    if ($end < 0) {$end=0;}
    $baselanguage = GetBaseLanguageFromSurveyID($surveyid);

    //ALLOW SELECTION OF NUMBER OF RECORDS SHOWN
    $tokenoutput .= "\t<div class='menubar'><div class='menubar-title'><span style='font-weight:bold;'>"
    .$clang->gT("Data view control")."</span></div>\n"
    ."<div class='menubar-main'>\n"
    ."<div class='menubar-left'>\n"
    ."<img src='$imageurl/blank.gif' alt='' width='31' height='20' border='0' hspace='0' align='left' />\n"
    ."<img src='$imageurl/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
    ."<a href='$scriptname?action=tokens&amp;subaction=browsegroup&amp;sid=$surveyid&amp;start=0&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'"
    ." title='".$clang->gTview("Show start...")."'>"
    ."<img name='DBeginButton' align='left' src='$imageurl/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
    ."<a href='$scriptname?action=tokens&amp;subaction=browsegroup&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	" title='".$clang->gTview("Show previous...")."'>" .
	"<img name='DBackButton' align='left' src='$imageurl/databack.png' alt='".$clang->gT("Show previous...")."' /></a>\n"
	."<img src='$imageurl/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left' />\n"
	."<a href='$scriptname?action=tokens&amp;subaction=browsegroup&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	"title='".$clang->gTview("Show next...")."'>" .
	"<img name='DForwardButton' align='left' src='$imageurl/dataforward.png' alt='".$clang->gT("Show next...")."' /></a>\n"
	."<a href='$scriptname?action=tokens&amp;subaction=browsegroup&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	"title='".$clang->gTview("Show last...")."'>".
	"<img name='DEndButton' align='left'  src='$imageurl/dataend.png' alt='".$clang->gT("Show last...")."' /></a>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
	."\t<form id='tokensearch' method='post' action='$scriptname?action=tokens'>\n"
	."<input type='text' name='searchstring' value='$searchstring' />\n"
	."<input type='submit' value='".$clang->gT("Search")."' />\n"
	."\t<input type='hidden' name='order' value='$order' />\n"
	."\t<input type='hidden' name='subaction' value='searchgroup' />\n"
	."\t<input type='hidden' name='sid' value='$surveyid' />\n"
	."\t</form>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' />\n"
	."<form id='tokenrange' action='{$scriptname}'>\n"
	."<font size='1' face='verdana'>"
	."&nbsp;<label for='limit'>".$clang->gT("Records displayed:")."</label> <input type='text' size='4' value='$limit' id='limit' name='limit' />"
	."&nbsp;&nbsp;<label for='start'>".$clang->gT("Starting from:")."</label> <input type='text' size='4' value='$start'  id='start' name='start' />"
	."&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' />\n"
	."&nbsp;<label for='usertoken'>".$clang->gT("User:")."</label>";
	$selectall = "selected='selected'";
	if (!$usertoken) {
		$selectall = "";
	}
	$tokenoutput .= "&nbsp;&nbsp;\n"
	."<select name='usertoken' onchange='javascript:document.getElementById(\"limit\").value=\"\";submit();'>\n"
	."\t<option value='' $selectall>".$clang->gT("All users")."</option>\n";
	
	$gtquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
	$gtresult = db_execute_assoc($gtquery) or safe_die("Couldn't get tokens:<br />$gtquery<br />".$connect->ErrorMsg());
	while ($gtrow = $gtresult->FetchRow())
	{
		$tokenoutput .= "\t<option value='{$gtrow['tid']}'";
		if ($usertoken == $gtrow['tid'])
			$tokenoutput .= "selected='selected'";
		$tokenoutput .= ">{$gtrow['firstname']} {$gtrow['lastname']} ({$gtrow['email']})</option>\n";
	}
	$tokenoutput .= "</select>\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='tokens' />\n"
	."<input type='hidden' name='subaction' value='browsegroup' />\n"
	."<input type='hidden' name='order' value='$order' />\n"
	."<input type='hidden' name='searchstring' value='$searchstring' />\n"
	."</font>\n</form>\n";
	
	if ($usertoken) {
		$bquery = "SELECT * FROM ".db_table_name("grouptokens_$surveyid")." NATURAL JOIN ".db_table_name("usedtokens_$surveyid");
	}
	else {
		$bquery = "SELECT * FROM ".db_table_name("grouptokens_$surveyid");
	}
	if ($searchstring)
	{
	    $bquery .= " WHERE (name LIKE '%$searchstring%' "
	    . "OR description LIKE '%$searchstring%' "
	    . "OR token LIKE '%$searchstring%')";
		if ($usertoken) {
			$bquery .= " AND tid='$usertoken'";
		}
	}
	elseif ($usertoken) {
		$bquery .= " WHERE tid='$usertoken'";
	}
	if (!isset($order) || !$order) {$bquery .= " ORDER BY gtid";}
	else {$bquery .= " ORDER BY $order"; }

	$bresult = db_select_limit_assoc($bquery, $limit, $start) or safe_die ($clang->gT("Error").": $bquery<br />".$connect->ErrorMsg());
	$bgc="";

	$tokenoutput .= "</div></div></div>\n";

	$tokenoutput .= "<table class='browsetokens' id='browsetokens' cellpadding='1' cellspacing='1'>\n";
	//COLUMN HEADINGS
	$tokenoutput .= "\t<tr>\n"
	."<th><input type='checkbox' id='tokencheckboxtoggle'></th>\n"   //Checkbox
	."<th align='left' >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=gtid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	."ID' alt='"
	.$clang->gT("Sort by: ")
	."ID' border='0' align='left' hspace='0' /></a>"."ID</th>\n" // ID
	."<th align='left'  >".$clang->gT("Actions")."</th>\n"  //Actions
	."<th align='left'  >"
	
	//name
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=name&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Name")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Name")
	."' border='0' align='left' /></a>".$clang->gT("Name")."</th>\n"
	
	//description
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=description&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Description")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Description")
	."' border='0' align='left' /></a>".$clang->gT("Description")."</th>\n"
	
	//token
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=token&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Token")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Token")
	."' border='0' align='left' /></a>".$clang->gT("Token")."</th>\n"

	//language
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=language&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Language")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Language")
	."' border='0' align='left' /></a>".$clang->gT("Language")."</th>\n"

	//completedsurveys
	."<th align='left'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=completedsurveys%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Completed surveys")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Completed surveys")
	."' border='0' align='left' /></a><span>".$clang->gT("Completed surveys")."</span></th>\n"
	
	//validfrom
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=validfrom%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid from")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid from")
	."' border='0' align='left' /></a>".$clang->gT("Valid from")."</th>\n"
	
	//validuntil
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup&amp;order=validuntil%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;usertoken=$usertoken'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid until")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid until")
	."' border='0' align='left' /></a>".$clang->gT("Valid until")."</th>\n";

	$tokenoutput .="\t</tr>\n";


	$tokenfieldorder=array('gtid',
                           'name',
                           'description',
                           'token',
                           'language',
                           'completedsurveys',
                           'validfrom',
                           'validuntil');

	while ($brow = $bresult->FetchRow())
	{
	    $brow['token'] = trim($brow['token']);
	    if (trim($brow['validfrom'])!=''){
	        $datetimeobj = new Date_Time_Converter($brow['validfrom'] , "Y-m-d H:i:s");
	        $brow['validfrom']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };
	    if (trim($brow['validuntil'])!=''){
	        $datetimeobj = new Date_Time_Converter($brow['validuntil'] , "Y-m-d H:i:s");
	        $brow['validuntil']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };

	    if ($bgc == "evenrow") {$bgc = "oddrow";} else {$bgc = "evenrow";}
	    $tokenoutput .= "\t<tr class='$bgc'>\n";

	    $tokenoutput .= "<td><input type='checkbox' name='".$brow['gtid']."'></td>\n";

	    foreach ($tokenfieldorder as $tokenfieldname)
	    {	        
			if  ($tokenfieldname=='gtid')
			{
				$tokenoutput.="<td><span style='font-weight:bold'>".$brow[$tokenfieldname]."</span></td>";
			}
			else
			{
				$tokenoutput .= "<td>$brow[$tokenfieldname]</td>\n";
			}
			
	        if ($tokenfieldname=='gtid')
	        {
	            $tokenoutput .= "<td align='left' style='white-space:nowrap;'>\n";
	            if (bHasSurveyPermission($surveyid,'tokens','update'))
	            {
	                $tokenoutput .="<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/token_edit.png' title='"
	                .$clang->gT("Edit token entry")
	                ."' alt='"
	                .$clang->gT("Edit token entry")
	                ."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=editgroup&amp;gtid=".$brow['gtid']."&amp;start=$start&amp;limit=$limit&amp;order=$order', '_top')\" />"
	                ."<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/token_delete.png' title='"
	                .$clang->gT("Delete token entry")
	                ."' alt='"
	                .$clang->gT("Delete token entry")
	                ."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")." (".$brow['gtid'].")')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=deletegroup&amp;gtid=".$brow['gtid']."&amp;limit=$limit&amp;start=$start&amp;order=$order")."}\"  />";
	            }
	            // here maybe add viewing responses connected to token
	            $tokenoutput .= "\n</td>\n";
	        }
	    }
	    $tokenoutput .= "\t</tr>\n";
	}

	// Multiple item actions - NOT WORKING
	if ($bresult->rowCount() > 0) {
	    $tokenoutput .= "<tr class='$bgc'>\n"
	    . "<td align='left' style='text-align: left' colspan='".(count($tokenfieldorder)+2)."'>"
	    . "<img src='$imageurl/blank.gif' height='16' width='16'/>"
	    . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/token_delete.png' title='"
	    .$clang->gT("Delete the selected entries")
	    ."' alt='"
	    .$clang->gT("Delete the selected entries")
	    ."' onclick=\"if (confirm('"
	    .$clang->gT("Are you sure you want to delete the selected entries?","js")
	    ."')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=deletegroup&amp;gtids=document.getElementById('grouptokenboxeschecked').value&amp;limit=$limit&amp;start=$start&amp;order=$order")."}\"  />"
	    . "</tr>\n";
	    $tokenoutput .= "<input type='hidden' id='grouptokenboxeschecked' value='' onChange='alert(this.value)'>\n";
	}
	//End multiple item actions

	$tokenoutput .= "</table>\n<br />\n";
}

if ($subaction == "kill" && bHasSurveyPermission($surveyid, 'surveyactivation', 'update'))
{
    $date = date('YmdHis');
    $tokenoutput .= "<div class='header'>".$clang->gT("Delete Tokens Table")."</div>\n"
    ."<div class='messagebox'>\n";
    // ToDo: Just delete it if there is no token in the table
    if (!isset($_POST['ok']) || !$_POST['ok'])
    {
        $tokenoutput .= "<div class='warningheader'>".$clang->gT("Warning")."</div><br />\n"
        .$clang->gT("If you delete this table tokens will no longer be required to access this survey.")."<br />".$clang->gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.")."<br />\n"
        ."( \"old_tokens_{$surveyid}_$date\" )<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Delete Tokens")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=kill&amp;ok=surething")."\" />\n"
        ."<input type='submit' value='"
        .$clang->gT("Cancel")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n";
    }
    elseif (isset($_POST['ok']) && $_POST['ok'] == "surething")
    {
        $oldtable = "tokens_$surveyid";
        $newtable = "old_tokens_{$surveyid}_$date";
        $deactivatequery = db_rename_table( db_table_name_nq($oldtable), db_table_name_nq($newtable));

        if ($databasetype=='postgres')
        {
            // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
            $oldTableJur = db_table_name_nq($oldtable);
            $deactivatequery = db_rename_table(db_table_name_nq($oldtable),db_table_name_nq($newtable).'_tid_seq');
            $deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$oldtable. " / oldtableJur : ". $oldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
            $setsequence="ALTER TABLE ".db_table_name_nq($newtable)."_tid_seq ALTER COLUMN tid SET DEFAULT nextval('".db_table_name_nq($newtable)."_tid_seq'::regclass);";
            $deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
            $setidx="ALTER INDEX ".db_table_name_nq($oldtable)."_idx RENAME TO ".db_table_name_nq($newtable)."_idx;";
            $deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
        } else {
            $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
        }
		
		if (tableExists('grouptokens_'.$surveyid) && tableExists('usedtokens_'.$surveyid))
		{
			// deactivate grouptokens table
			$oldtable = "grouptokens_$surveyid";
			$newtable = "old_grouptokens_{$surveyid}_$date";
			$deactivatequery = db_rename_table( db_table_name_nq($oldtable), db_table_name_nq($newtable));

			if ($databasetype=='postgres')
			{
				// If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
				$oldTableJur = db_table_name_nq($oldtable);
				$deactivatequery = db_rename_table(db_table_name_nq($oldtable),db_table_name_nq($newtable).'_gtid_seq');
				$deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$oldtable. " / oldtableJur : ". $oldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setsequence="ALTER TABLE ".db_table_name_nq($newtable)."_gtid_seq ALTER COLUMN gtid SET DEFAULT nextval('".db_table_name_nq($newtable)."_gtid_seq'::regclass);";
				$deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setidx="ALTER INDEX ".db_table_name_nq($oldtable)."_idx RENAME TO ".db_table_name_nq($newtable)."_idx;";
				$deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
			}
			else
			{
				$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
			}
			
			// deactivate usedtokens table
			$oldtable = "usedtokens_$surveyid";
			$newtable = "old_usedtokens_{$surveyid}_$date";
			$deactivatequery = db_rename_table( db_table_name_nq($oldtable), db_table_name_nq($newtable));

			if ($databasetype=='postgres')
			{
				// If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
				$oldTableJur = db_table_name_nq($oldtable);
				$deactivatequery = db_rename_table(db_table_name_nq($oldtable),db_table_name_nq($newtable).'_id_seq');
				$deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$oldtable. " / oldtableJur : ". $oldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setsequence="ALTER TABLE ".db_table_name_nq($newtable)."_id_seq ALTER COLUMN gtid SET DEFAULT nextval('".db_table_name_nq($newtable)."_gtid_seq'::regclass);";
				$deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setidx="ALTER INDEX ".db_table_name_nq($oldtable)."_idx RENAME TO ".db_table_name_nq($newtable)."_idx;";
				$deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
			}
			else
			{
				$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
			}
		}
		
        $tokenoutput .= '<br />'.$clang->gT("The tokens table has now been removed and tokens are no longer required to access this survey.")."<br /> ".$clang->gT("A backup of this table has been made and can be accessed by your system administrator.")."<br />\n"
        ."(\"{$dbprefix}old_tokens_{$surveyid}_$date\")"."<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?sid={$surveyid}', '_top')\" />\n";
    }
    $tokenoutput .= "</div>\n";
}


if ($subaction == "killgroup" && bHasSurveyPermission($surveyid,'surveyactivation','update'))
{
    $date = date('YmdHis');
    $tokenoutput .= "<div class='header'>".$clang->gT("Delete Group Tokens Table")."</div>\n"
    ."<div class='messagebox'>\n";
    // ToDo: Just delete it if there is no token in the table
    if (!isset($_POST['ok']) || !$_POST['ok'])
    {
        $tokenoutput .= "<div class='warningheader'>".$clang->gT("Warning")."</div><br />\n"
        .$clang->gT("If you delete this table, group tokens will no longer be required to access this survey.")."<br />".$clang->gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.")."<br />\n"
        ."( \"old_grouptokens_{$surveyid}_$date\" )<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Delete Group Tokens")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=killgroup&amp;ok=surething")."\" />\n"
        ."<input type='submit' value='"
        .$clang->gT("Cancel")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n";
    }
    elseif (isset($_POST['ok']) && $_POST['ok'] == "surething")
	{
		// deactivate grouptokens table
		$oldtable = "grouptokens_$surveyid";
		$newtable = "old_grouptokens_{$surveyid}_$date";
		$deactivatequery = db_rename_table( db_table_name_nq($oldtable), db_table_name_nq($newtable));

		if ($databasetype=='postgres')
		{
				// If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
				$oldTableJur = db_table_name_nq($oldtable);
				$deactivatequery = db_rename_table(db_table_name_nq($oldtable),db_table_name_nq($newtable).'_gtid_seq');
				$deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$oldtable. " / oldtableJur : ". $oldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setsequence="ALTER TABLE ".db_table_name_nq($newtable)."_gtid_seq ALTER COLUMN gtid SET DEFAULT nextval('".db_table_name_nq($newtable)."_gtid_seq'::regclass);";
				$deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setidx="ALTER INDEX ".db_table_name_nq($oldtable)."_idx RENAME TO ".db_table_name_nq($newtable)."_idx;";
				$deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
		}
		else
		{
				$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
		}
		
		// deactivate usedtokens table
		$oldtable = "usedtokens_$surveyid";
		$newtable = "old_usedtokens_{$surveyid}_$date";
		$deactivatequery = db_rename_table( db_table_name_nq($oldtable), db_table_name_nq($newtable));

		if ($databasetype=='postgres')
		{
				// If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
				$oldTableJur = db_table_name_nq($oldtable);
				$deactivatequery = db_rename_table(db_table_name_nq($oldtable),db_table_name_nq($newtable).'_id_seq');
				$deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$oldtable. " / oldtableJur : ". $oldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setsequence="ALTER TABLE ".db_table_name_nq($newtable)."_id_seq ALTER COLUMN gtid SET DEFAULT nextval('".db_table_name_nq($newtable)."_gtid_seq'::regclass);";
				$deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
				$setidx="ALTER INDEX ".db_table_name_nq($oldtable)."_idx RENAME TO ".db_table_name_nq($newtable)."_idx;";
				$deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
		}
		else
		{
				$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
		}
				
        $tokenoutput .= '<br />'.$clang->gT("The group tokens table has now been removed and group tokens are no longer required to access this survey.")."<br /> ".$clang->gT("A backup of this table has been made and can be accessed by your system administrator.")."<br />\n"
        ."(\"{$dbprefix}old_grouptokens_{$surveyid}_$date\")"."<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Return")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid={$surveyid}', '_top')\" />\n";
    }
    $tokenoutput .= "</div>\n";
}


if ($subaction == "email" && bHasSurveyPermission($surveyid, 'tokens','update'))
{
    if (getEmailFormat($surveyid) == 'html')
    {
        $ishtml=true;
    }
    else
    {
        $ishtml=false;
    }

    $tokenoutput .= PrepareEditorScript();
    $tokenoutput .= "\t<div class='header'>"
    .$clang->gT("Send email invitations")."</div>\n"
    ."\t<div><br/>\n";
    if (!isset($_POST['ok']) || !$_POST['ok'])
    {
        if ($thissurvey['active']!='Y')
        {
            $tokenoutput .="<div class='messagebox'><div class='warningheader'>".$clang->gT('Warning!')."</div>".$clang->gT("This survey is not yet activated and so your participants won't be able to fill out the survey.")."</div>";
        }
        $tokenoutput .= "<form id='sendinvitation' class='form30' method='post' action='$scriptname?action=tokens&amp;sid=$surveyid'>";

        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($surveylangs,$baselang);
        $tokenoutput .= "<div class='tab-pane' id='tab-pane-send-$surveyid'>";
        foreach ($surveylangs as $language)
        {
            //GET SURVEY DETAILS
            $thissurvey=getSurveyInfo($surveyid,$language);
            if (!$thissurvey['email_invite'])
            {
                if ($ishtml===true)
                {
                    $thissurvey['email_invite']=html_escape(str_replace("\n", "<br />", $clang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped')."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",'unescaped')));
                }
                else
                {
                    $thissurvey['email_invite']=str_replace("\n", "\r\n", $clang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}"));
                }
            }
            if (!$thissurvey['email_invite_subj'])
            {
                $thissurvey['email_invite_subj']=$clang->gT("Invitation to participate in survey");
            }
            $fieldsarray["{ADMINNAME}"]= $thissurvey['adminname'];
            $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
            $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
            $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
            $fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];
            $fieldsarray["{EXPIRY-DMY}"]=date("d-m-Y",strtotime($thissurvey["expiry"]));
            $fieldsarray["{EXPIRY-MDY}"]=date("m-d-Y",strtotime($thissurvey["expiry"]));

            $subject=Replacefields($thissurvey['email_invite_subj'], $fieldsarray);
            $textarea=Replacefields($thissurvey['email_invite'], $fieldsarray);
            if ($ishtml!==true){$textarea=str_replace(array('<x>','</x>'),array(''),$textarea);}
            $tokenoutput .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($language,false);
            if ($language==$baselang)
            {
                $tokenoutput .= "(".$clang->gT("Base Language").")";
            }
            $tokenoutput .= "</h2>\n";

            $tokenoutput .= "\t<ul>\n"
            ."<li><label for='from_$language'>".$clang->gT("From").":</label>\n"
            ."<input type='text' size='50' id='from_$language' name='from_$language' value=\"{$thissurvey['adminname']} <{$thissurvey['adminemail']}>\" /></li>\n"

            ."<li><label for='subject_$language'>".$clang->gT("Subject").":</label>\n"
            ."<input type='text' size='83' id='subject_$language' name='subject_$language' value=\"$subject\" /></li>\n"

            ."<li><label for='message_$language'>".$clang->gT("Message").":</label>\n"
            ."<textarea name='message_$language' id='message_$language' rows='20' cols='80'>\n"
            .$textarea
            ."</textarea>\n"
            . getEditor("email-inv","message_$language","[".$clang->gT("Invitation Email:", "js")."](".$language.")",$surveyid,'','',$action)
            ."</li>\n"
            ."\t</ul></div>\n";
        }
        $tokenoutput .= "</div>";
        /*
         if (isset($tokenid))
         {
         $tokenoutput .= "<li><label>"
         .$clang->gT("Sending to Token ID").":</label>".$tokenid
         ."</li>";
         }
         if (isset($tokenids) && count($tokenids) > 0)
         {
         $tokenoutput .= "<li><label>"
         .$clang->gT("Sending to Token IDs").":</label>".implode(", ", $tokenids)
         ."</li>";
         } else {
         $tokenoutput .= "<li><label>"
         .$clang->gT("Sending to:")."</label>"
         .$clang->gT("All tokens who have not yet been sent an invitation")
         ."</li>";
         }
         */
        $tokenoutput .="\t<p>\n"
        ."\t<label for='bypassbademails'>".$clang->gT("Bypass token with failing email addresses").":</label><select id='bypassbademails' name='bypassbademails'>\n"
        ."<option value='Y'>".$clang->gT("Yes")."</option>"
        ."<option value='N'>".$clang->gT("No")."</option>"
        ."\t</select></p><p>\n"
        ."\t<input type='submit' value='".$clang->gT("Send Invitations")."' />\n"
        ."\t<input type='hidden' name='ok' value='absolutely' />\n"
        ."\t<input type='hidden' name='sid' value='{$_GET['sid']}' />\n"
        ."\t<input type='hidden' name='subaction' value='email' />\n";
        if (isset($tokenid)) {$tokenoutput .= "\t<input type='hidden' name='tid' value='$tokenid' />\n";}
        if (isset($tokenids)) {$tokenoutput .= "\n<input type='hidden' name='tids' value='|".implode("|", $tokenids)."' />\n";}
        $tokenoutput .= "</form>\n";
    }
    else
    {
        $tokenoutput .= "<div class='messagebox'>\n"
        ."\t<div class='header'>\n";
        $tokenoutput .= $clang->gT("Sending invitations...");
        $tokenoutput .= "\n\t</div>\n";
        if (isset($tokenid)) {$tokenoutput .= " (".$clang->gT("Sending to Token ID").":&nbsp;{$tokenid})";}
        if (isset($tokenids)) {$tokenoutput .= " (".$clang->gT("Sending to Token IDs").":&nbsp;".implode(", ", $tokenids).")";}
        $tokenoutput .= "<br />\n";

        if (isset($_POST['bypassbademails']) && $_POST['bypassbademails'] == 'Y')
        {
            $SQLemailstatuscondition = " AND emailstatus = 'OK'";
        }
        else
        {
            $SQLemailstatuscondition = " AND emailstatus <> 'OptOut'";
        }

        $ctquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";

        if (isset($tokenid)) {$ctquery .= " AND tid='{$tokenid}'";}
        if (isset($tokenids)) {$ctquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
        $tokenoutput .= "<!-- ctquery: $ctquery -->\n";
        $ctresult = $connect->Execute($ctquery) or safe_die("Database error!<br />\n" . $connect->ErrorMsg());
        $ctcount = $ctresult->RecordCount();
        $ctfieldcount = $ctresult->FieldCount();

        $emquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";

        if (isset($tokenid)) {$emquery .= " and tid='{$tokenid}'";}
        if (isset($tokenids)) {$emquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
        $tokenoutput .= "\n\n<!-- emquery: $emquery -->\n\n";
        $emresult = db_select_limit_assoc($emquery,$maxemails) or safe_die ("Couldn't do query.<br />\n$emquery<br />\n".$connect->ErrorMsg());
        $emcount = $emresult->RecordCount();

        $tokenoutput .= "<ul>\n";
         
        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($surveylangs,$baselanguage);

        foreach ($surveylangs as $language)
        {
            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);
            if ($ishtml) $_POST['message_'.$language] = html_entity_decode($_POST['message_'.$language], ENT_QUOTES, $emailcharset);
        }


        $attributes=GetTokenFieldsAndNames($surveyid);
        if ($emcount > 0)
        {
            while ($emrow = $emresult->FetchRow())
            {
                unset($fieldsarray);
                $to = $emrow['email'];
                $fieldsarray["{EMAIL}"]=$emrow['email'];
                $fieldsarray["{FIRSTNAME}"]=$emrow['firstname'];
                $fieldsarray["{LASTNAME}"]=$emrow['lastname'];
                $fieldsarray["{TOKEN}"]=$emrow['token'];
                $fieldsarray["{LANGUAGE}"]=$emrow['language'];

                foreach ($attributes as $attributefield=>$attributedescription)
                {
                    $fieldsarray['{'.strtoupper($attributefield).'}']=$emrow[$attributefield];
                }

                $emrow['language']=trim($emrow['language']);
                if ($emrow['language']=='') {$emrow['language']=$baselanguage;} //if language is not given use default
                $found = array_search($emrow['language'], $surveylangs);
                if ($found==false) {$emrow['language']=$baselanguage;}

                $from = $_POST['from_'.$emrow['language']];


                if ($ishtml === false)
                {
                    $fieldsarray["{OPTOUTURL}"]="$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";

                    if ( $modrewrite )
                    {
                        $fieldsarray["{SURVEYURL}"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
                    }
                    else
                    {
                        $fieldsarray["{SURVEYURL}"]="$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
                    }
                }
                else
                {
                    $fieldsarray["{OPTOUTURL}"]="<a href='$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
                    if ( $modrewrite )
                    {
                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}'>".htmlspecialchars("$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}")."</a>";
                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
                    }
                    else
                    {
                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/index.php?lang=".trim($emrow['language'])."&amp;sid=$surveyid&amp;token={$emrow['token']}";
                    }
                }
		$customheaders = array( '1' => "X-surveyid: ".$surveyid,
					'2' => "X-tokenid: ".$fieldsarray["{TOKEN}"]);

                $modsubject=Replacefields($_POST['subject_'.$emrow['language']], $fieldsarray);
                $modmessage=Replacefields($_POST['message_'.$emrow['language']], $fieldsarray);
		
                if (trim($emrow['validfrom'])!='' && convertDateTimeFormat($emrow['validfrom'],'Y-m-d H:i:s','U')*1>date('U')*1)
                {
                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.")."<br />", $fieldsarray);
                }
                elseif (trim($emrow['validuntil'])!='' && convertDateTimeFormat($emrow['validuntil'],'Y-m-d H:i:s','U')*1<date('U')*1)
                {
                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.")."<br />", $fieldsarray);
                }
                elseif (SendEmailMessage($modmessage, $modsubject, $to , $from, $sitename, $ishtml, getBounceEmail($surveyid),null,$customheaders))
                {
                    // Put date into sent
                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
                    $udequery = "UPDATE ".db_table_name("tokens_{$surveyid}")."\n"
                    ."SET sent='$today' WHERE tid={$emrow['tid']}";
                    //
                    $uderesult = $connect->Execute($udequery) or safe_die ("Could not update tokens<br />$udequery<br />".$connect->ErrorMsg());
                    $tokenoutput .= $clang->gT("Invitation sent to:")." {$emrow['firstname']} {$emrow['lastname']} ($to)<br />\n";
                    if ($emailsmtpdebug==2)
                    {
                        $tokenoutput .=$maildebug;
                    }
                }
                else
                {
                    $tokenoutput .= '<li>'.ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:")." ".$maildebug."<br />", $fieldsarray).'</li>';
                    if ($debug>0)
                    {
                        $tokenoutput .= "<pre>Subject : $modsubject<br /><br />".htmlspecialchars($maildebugbody)."</pre>";
                    }
                }
            }
            if ($ctcount > $emcount)
            {
                $lefttosend = $ctcount-$maxemails;
                $tokenoutput .= "</ul>\n"
                ."<div class='warningheader'>".$clang->gT("Warning")."</div><br />\n"
                ."<form method='post' action='$scriptname?action=tokens&amp;sid=$surveyid'>"
                .$clang->gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.")."<br /><br />\n";
                $tokenoutput .= str_replace("{EMAILCOUNT}", "$lefttosend", $clang->gT("There are {EMAILCOUNT} emails still to be sent."));
                $tokenoutput .= "<br /><br />\n";
                $tokenoutput .= "<input type='submit' value='".$clang->gT("Continue")."' />\n"
                ."<input type='hidden' name='ok' value=\"absolutely\" />\n"
                ."<input type='hidden' name='subaction' value=\"email\" />\n"
                ."<input type='hidden' name='action' value=\"tokens\" />\n"
                ."<input type='hidden' name='bypassbademails' value=\"".$_POST['bypassbademails']."\" />\n"
                ."<input type='hidden' name='sid' value=\"{$surveyid}\" />\n";
                foreach ($surveylangs as $language)
                {
                    $message = html_escape($_POST['message_'.$language]);
                    $subject = html_escape($_POST['subject_'.$language]);
                    $tokenoutput .="<input type='hidden' name='from_$language' value=\"".$_POST['from_'.$language]."\" />\n"
                    ."<input type='hidden' name='subject_$language' value=\"".$_POST['subject_'.$language]."\" />\n"
                    ."<input type='hidden' name='message_$language' value=\"$message\" />\n";
                }
                $tokenoutput .="</form>\n";
            }
        }
        else
        {
            $tokenoutput .= "<div class='warningheader'>".$clang->gT("Warning")."</div>\n".$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
            ."<br/>&nbsp;<ul><li>".$clang->gT("having a valid email address")."</li>"
            ."<li>".$clang->gT("not having been sent an invitation already")."</li>"
            ."<li>".$clang->gT("having already completed the survey")."</li>"
            ."<li>".$clang->gT("having a token")."</li></ul>";
        }
    }
    $tokenoutput .= "</div>\n</div>\n";
}

if ($subaction == "remind" && bHasSurveyPermission($surveyid, 'tokens','update'))
{
    $tokenoutput .= PrepareEditorScript();
    $tokenoutput .= "\t<div class='header'>"
    .$clang->gT("Send email reminder")."</div><br />\n";
    if (!isset($_POST['ok']) || !$_POST['ok'])
    {
        if ($thissurvey['active']!='Y')
        {
            $tokenoutput .="<div class='messagebox'><div class='warningheader'>".$clang->gT('Warning!')."</div>".$clang->gT("This survey is not yet activated and so your participants won't be able to fill out the survey.")."</div>";
        }
        //GET SURVEY DETAILS
        $tokenoutput .= "<form method='post' class='form30' id='sendreminder' action='$scriptname?action=tokens'>";
        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($surveylangs,$baselang);

        $tokenoutput .= "<div class='tab-pane' id='tab-pane-send-$surveyid'>";
        foreach ($surveylangs as $language)
        {
            //GET SURVEY DETAILS
            $thissurvey=getSurveyInfo($surveyid,$language);
            if (!$thissurvey['email_remind']) {$thissurvey['email_remind']=str_replace("\n", "\r\n", $clang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}"));}
            $tokenoutput .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($language,false);
            if ($language==$baselang)
            {
                $tokenoutput .= "(".$clang->gT("Base Language").")";
            }
            $tokenoutput .= "</h2><ul>\n"
            ."<li><label for='from_$language' >".$clang->gT("From").":</label>\n"
            ."<input type='text' size='50' name='from_$language' id='from_$language' value=\"{$thissurvey['adminname']} <{$thissurvey['adminemail']}>\" /></li>\n"

            ."<li><label for='subject_$language' >".$clang->gT("Subject").":</label>\n";

            $fieldsarray["{ADMINNAME}"]= $thissurvey['adminname'];
            $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
            $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
            $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
            $fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];
            $fieldsarray["{EXPIRY-DMY}"]=date("d-m-Y",strtotime($thissurvey["expiry"]));
            $fieldsarray["{EXPIRY-MDY}"]=date("m-d-Y",strtotime($thissurvey["expiry"]));

            $subject=Replacefields($thissurvey['email_remind_subj'], $fieldsarray);
            $textarea=Replacefields($thissurvey['email_remind'], $fieldsarray);
            if ($ishtml!==true){$textarea=str_replace(array('<x>','</x>'),array(''),$textarea);}

            $tokenoutput .= "<input type='text' size='83' id='subject_$language' name='subject_$language' value=\"$subject\" /></li>\n";

            $tokenoutput .= "\t<li>\n"
            ."<label for='message_$language'>".$clang->gT("Message").":</label>\n"
            ."<textarea name='message_$language' id='message_$language' rows='20' cols='80' >\n";

            $tokenoutput .= $textarea;

            $tokenoutput .= "</textarea>\n"
            . getEditor("email-rem","message_$language","[".$clang->gT("Reminder Email:", "js")."](".$language.")",$surveyid,'','',$action)
            ."</li>\n"
            ."</ul></div>";
        }

        $tokenoutput .= "</div><ul>\n";

        if (isset($tokenids)) {
            $tokenoutput .= "\t<li>\n"
            . "<label>".$clang->gT("Send reminder to token ID(s):")."</label>\n"
            . implode(", ", $tokenids)."</li>\n";
        } elseif (!isset($tokenid)) {
            $tokenoutput .= "<li><label>"
            .$clang->gT("Sending to:")."</label>"
            .$clang->gT("All token entries to whom a reminder email would apply")
            ."</li>";
            $tokenoutput .= "\t<li>\n"
            ."<label for='last_tid'>".$clang->gT("Start at Token ID:")."</label>\n"
            ."<input type='text' size='5' id='last_tid' name='last_tid' />\n"
            ."\t</li>\n";
        } elseif (isset($tokenid)) {
            $tokenoutput .= "\t<li>\n"
            ."<label>".$clang->gT("Send reminder to token ID(s):")."</label>\n"
            ."{$tokenid}</li>\n";
        }
        $tokenoutput .="<li><label for='bypassbademails'>\n"
        .$clang->gT("Bypass token with failing email addresses").":</label>\n"
        ."<select id='bypassbademails' name='bypassbademails'>\n"
        ."\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
        ."\t<option value='N'>".$clang->gT("No")."</option>\n"
        ."</select></li>\n"
        . "<li><label for='minreminderdelay'>\n"
        . $clang->gT("Min days between reminders").":</label>\n"
        ."<input type='text' value='' name='minreminderdelay' id='minreminderdelay' /></li>\n"
            
        . "<li><label for='maxremindercount'>\n"
        . $clang->gT("Max reminders").":</label>\n"
        . "<input type='text' value='' name='maxremindercount' id='maxremindercount' /></li>\n"
        . "</ul><p>\n"
        ."<input type='submit' value='".$clang->gT("Send Reminders")."' />\n"
        ."\t<input type='hidden' name='ok' value='absolutely' />\n"
        ."\t<input type='hidden' name='sid' value='{$_GET['sid']}' />\n"
        ."\t<input type='hidden' name='subaction' value='remind' />\n";
        if (isset($tokenid)) {$tokenoutput .= "\t<input type='hidden' name='tid' value='{$tokenid}' />\n";}
        if (isset($tokenids)) {$tokenoutput .= "\n<input type='hidden' name='tids' value='|".implode("|", $tokenids)."' />\n";}
        $tokenoutput .= "</form>\n";
    }
    else
    {

        $tokenoutput .= "<div class='messagebox'>\n"
        . "<div class='header'>";
        $tokenoutput .= $clang->gT("Sending Reminders")
        ."</div><br />\n";

        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($surveylangs,$baselanguage);

        foreach ($surveylangs as $language)
        {
            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);

        }

        if (isset($starttokenid)) {$tokenoutput .= " (".$clang->gT("From Token ID").":&nbsp;{$starttokenid})";}
        if (isset($tokenid)) {$tokenoutput .= " (".$clang->gT("Sending to Token ID").":&nbsp;{$tokenid})";}
        if (isset($tokenids)) {$tokenoutput .= " (".$clang->gT("Sending to Token IDs").":&nbsp;".implode("|", $tokenids).")";}

        if (isset($_POST['bypassbademails']) && $_POST['bypassbademails'] == 'Y')
        {
            $SQLemailstatuscondition = " AND emailstatus = 'OK'";
        }
        else
        {
            $SQLemailstatuscondition = "";
        }

        if (isset($_POST['maxremindercount']) &&
        $_POST['maxremindercount'] != '' &&
        intval($_POST['maxremindercount']) != 0)
        {
            $SQLremindercountcondition = " AND remindercount < ".intval($_POST['maxremindercount']);
        }
        else
        {
            $SQLremindercountcondition = "";
        }

        if (isset($_POST['minreminderdelay']) &&
        $_POST['minreminderdelay'] != '' &&
        intval($_POST['minreminderdelay']) != 0)
        {
            // $_POST['minreminderdelay'] in days (86400 seconds per day)
            $compareddate = date_shift(
            date("Y-m-d H:i:s",time() - 86400 * intval($_POST['minreminderdelay'])),
					"Y-m-d H:i",
            $timeadjust);
            $SQLreminderdelaycondition = " AND ( "
            . " (remindersent = 'N' AND sent < '".$compareddate."') "
            . " OR "
            . " (remindersent < '".$compareddate."'))";
        }
        else
        {
            $SQLreminderdelaycondition = "";
        }

        $ctquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE (completed ='N' or completed ='') AND sent<>'' AND sent<>'N' AND token <>'' AND email <> '' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";

        if (isset($starttokenid)) {$ctquery .= " AND tid > '{$starttokenid}'";}
        if (isset($tokenid) && $tokenid) {$ctquery .= " AND tid = '{$tokenid}'";}
        if (isset($tokenids)) {$ctquery .= " AND tid IN (".implode(", ", $tokenids).")";}
        $tokenoutput .= "<!-- ctquery: $ctquery -->\n";
        $ctresult = $connect->Execute($ctquery) or safe_die ("Database error!<br />\n" . $connect->ErrorMsg());
        $ctcount = $ctresult->RecordCount();
        $ctfieldcount = $ctresult->FieldCount();
        $emquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE (completed = 'N' or completed = '') AND sent <> 'N' and sent <>'' AND token <>'' AND EMAIL <>'' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";

        if (isset($starttokenid)) {$emquery .= " AND tid > '{$starttokenid}'";}
        if (isset($tokenid) && $tokenid) {$emquery .= " AND tid = '{$tokenid}'";}
        if (isset($tokenids)) {$emquery .= " AND tid IN (".implode(", ", $tokenids).")";}
        $emquery .= " ORDER BY tid ";
        $emresult = db_select_limit_assoc($emquery, $maxemails) or safe_die ("Couldn't do query.<br />$emquery<br />".$connect->ErrorMsg());
        $emcount = $emresult->RecordCount();


        $attributes=GetTokenFieldsAndNames($surveyid);
        if ($emcount > 0)
        {
            $tokenoutput .= "<table width='450' align='center' >\n"
            ."\t<tr>\n"
            ."<td><font size='1'>\n";
            while ($emrow = $emresult->FetchRow())
            {
                unset($fieldsarray);
                $to = $emrow['email'];
                $fieldsarray["{EMAIL}"]=$emrow['email'];
                $fieldsarray["{FIRSTNAME}"]=$emrow['firstname'];
                $fieldsarray["{LASTNAME}"]=$emrow['lastname'];
                $fieldsarray["{TOKEN}"]=$emrow['token'];
                $fieldsarray["{LANGUAGE}"]=$emrow['language'];

                foreach ($attributes as $attributefield=>$attributedescription)
                {
                    $fieldsarray['{'.strtoupper($attributefield).'}']=$emrow[$attributefield];
                }

                $emrow['language']=trim($emrow['language']);
                if ($emrow['language']=='') {$emrow['language']=$baselanguage;} //if language is not give use default
                $found = array_search($emrow['language'], $surveylangs);
                if ($found==false) {$emrow['language']=$baselanguage;}

                $from = $_POST['from_'.$emrow['language']];

                if (getEmailFormat($surveyid) == 'html')
                {
                    $ishtml=true;
                }
                else
                {
                    $ishtml=false;
                }

                if ($ishtml == false)
                {
                    $fieldsarray["{OPTOUTURL}"]="$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
                    if ( $modrewrite )
                    {
                        $fieldsarray["{SURVEYURL}"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
                    }
                    else
                    {
                        $fieldsarray["{SURVEYURL}"]="$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
                    }
                }
                else
                {
                    $fieldsarray["{OPTOUTURL}"]="<a href='$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
                    if ( $modrewrite )
                    {
                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}'>".htmlspecialchars("$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}")."</a>";
                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
                    }
                    else
                    {
                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/index.php?lang=".trim($emrow['language'])."&amp;sid=$surveyid&amp;token={$emrow['token']}";
                        $_POST['message_'.$emrow['language']] = html_entity_decode($_POST['message_'.$emrow['language']], ENT_QUOTES, $emailcharset);
                    }
                }

                $msgsubject=Replacefields($_POST['subject_'.$emrow['language']], $fieldsarray);
                $sendmessage=Replacefields($_POST['message_'.$emrow['language']], $fieldsarray);
$customheaders = array( '1' => "X-surveyid: ".$surveyid,
					'2' => "X-tokenid: ".$tokenid);

                if (trim($emrow['validfrom'])!='' && convertDateTimeFormat($emrow['validfrom'],'Y-m-d H:i:s','U')*1>date('U')*1)
                {
                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.")."<br />", $fieldsarray);
                }
                elseif (trim($emrow['validuntil'])!='' && convertDateTimeFormat($emrow['validuntil'],'Y-m-d H:i:s','U')*1<date('U')*1)
                {
                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.")."<br />", $fieldsarray);
                }
                elseif (SendEmailMessage($sendmessage, $msgsubject, $to, $from, $sitename,$ishtml,getBounceEmail($surveyid)))
                {

                    // Put date into remindersent
                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
                    $udequery = "UPDATE ".db_table_name("tokens_{$surveyid}")."\n"
                    ."SET remindersent='$today',remindercount = remindercount+1  WHERE tid={$emrow['tid']}";
                    //
                    $uderesult = $connect->Execute($udequery) or safe_die ("Could not update tokens<br />$udequery<br />".$connect->ErrorMsg());
                    //orig: $tokenoutput .= "({$emrow['tid']})[".$clang->gT("Reminder sent to:")." {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
                    $tokenoutput .= "({$emrow['tid']}) [".$clang->gT("Reminder sent to:")." {$emrow['firstname']} {$emrow['lastname']} ($to)]<br />\n";
                }
                else
                {
                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:")." ".$maildebug."<br />", $fieldsarray);
                    if ($debug>0)
                    {
                        $tokenoutput .= "<pre>Subject : $msgsubject<br /><br />".htmlspecialchars($maildebugbody)."<br /></pre>";
                    }

                }
                $lasttid = $emrow['tid'];
            }
            if ($ctcount > $emcount)
            {
                $lefttosend = $ctcount-$maxemails;
                $tokenoutput .= "</td>\n"
                ."\t</tr>\n"
                ."\t<tr><form method='post' action='$scriptname?action=tokens&amp;sid=$surveyid'>"
                ."<td align='center'>\n"
                ."<strong>".$clang->gT("Warning")."</strong><br /><br />\n"
                .$clang->gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.")."<br /><br />\n"
                .str_replace("{EMAILCOUNT}", $lefttosend, $clang->gT("There are {EMAILCOUNT} emails still to be sent."))
                ."<br />\n"
                ."<input type='submit' value='".$clang->gT("Continue")."' />\n"
                ."</td>\n"
                ."\t<input type='hidden' name='ok' value=\"absolutely\" />\n"
                ."\t<input type='hidden' name='subaction' value=\"remind\" />\n"
                ."\t<input type='hidden' name='action' value=\"tokens\" />\n"
                ."\t<input type='hidden' name='bypassbademails' value=\"".$_POST['bypassbademails']."\" />\n"
                ."\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n";
                //Include values for constraints minreminderdelay and maxremindercount if they exist
                if (isset($_POST['minreminderdelay']) &&
                $_POST['minreminderdelay'] != '' &&
                intval($_POST['minreminderdelay']) != 0)
                {
                    $tokenoutput .= "\t<input type='hidden' name='minreminderdelay' value=\"".$_POST['minreminderdelay']."\" />\n";
                }
                if (isset($_POST['maxremindercount']) &&
                $_POST['maxremindercount'] != '' &&
                intval($_POST['maxremindercount']) != 0)
                {
                    $tokenoutput .= "\t<input type='hidden' name='maxremindercount' value=\"".$_POST['maxremindercount']."\" />\n";
                }
                //
                foreach ($surveylangs as $language)
                {
                    $message = html_escape($_POST['message_'.$language]);
                    $tokenoutput .="<input type='hidden' name='from_$language' value=\"".$_POST['from_'.$language]."\" />\n"
                    ."<input type='hidden' name='subject_$language' value=\"".$_POST['subject_'.$language]."\" />\n"
                    ."<input type='hidden' name='message_$language' value=\"$message\" />\n";
                }
                $tokenoutput.="\t<input type='hidden' name='last_tid' value=\"$lasttid\" />\n"
                ."\t</form>\n";
            }
            $tokenoutput .= "\t</tr>\n"
            ."</table>\n";
        }
        else
        {
            $tokenoutput .= "<div class='warningheader'>".$clang->gT("Warning")."</div>\n"
            .$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")."\n"
            ."<br/>&nbsp;<ul><li>".$clang->gT("having a valid email address")."</li>"
            ."<li>".$clang->gT("not having been sent an invitation already")."</li>"
            ."<li>".$clang->gT("but not having already completed the survey")."</li>"
            ."</ul><br />\n";
        }
        $tokenoutput .= "</div>\n";
    }
}

if ($subaction == "tokenify" && bHasSurveyPermission($surveyid, 'tokens', 'update'))
{
    $tokenoutput .= "<div class='header'>".$clang->gT("Create tokens")."</div>\n";
    $tokenoutput .= "<div class='messagebox'>\n";
    if (!isset($_POST['ok']) || !$_POST['ok'])
    {
        $tokenoutput .= "".$clang->gT("Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?")."<br /><br />\n"
        ."<input type='submit' value='"
        //		.$clang->gT("Yes")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify&amp;ok=Y', '_top')\" />\n"
        .$clang->gT("Yes")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify&amp;ok=Y")."\" />\n"
        ."<input type='submit' value='"
        .$clang->gT("No")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n"
        ."<br />\n";
    }
    else
    {
        //get token length from survey settings
        $tlquery = "SELECT tokenlength FROM ".db_table_name("surveys")." WHERE sid=$surveyid";
        $tlresult = db_execute_assoc($tlquery);
        while ($tlrow = $tlresult->FetchRow())
        {
            $tokenlength = $tlrow['tokenlength'];
        }

        //if tokenlength is not set or there are other problems use the default value (15)
        if(!isset($tokenlength) || $tokenlength == '')
        {
            $tokenlength = 15;
        }

        // select all existing tokens
        $ntquery = "SELECT token FROM ".db_table_name("tokens_$surveyid")." group by token";
        $ntresult = db_execute_assoc($ntquery);
        while ($tkrow = $ntresult->FetchRow())
        {
            $existingtokens[$tkrow['token']]=null;
        }
        $newtokencount = 0;
        $tkquery = "SELECT tid FROM ".db_table_name("tokens_$surveyid")." WHERE token IS NULL OR token=''";
        $tkresult = db_execute_assoc($tkquery) or safe_die ("Mucked up!<br />$tkquery<br />".$connect->ErrorMsg());
        while ($tkrow = $tkresult->FetchRow())
        {
            $isvalidtoken = false;
            while ($isvalidtoken == false)
            {
                $newtoken = randomkey($tokenlength);
                if (!isset($existingtokens[$newtoken])) {
                    $isvalidtoken = true;
                    $existingtokens[$newtoken]=null;
                }
            }
            $itquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET token='$newtoken' WHERE tid={$tkrow['tid']}";
            $itresult = $connect->Execute($itquery);
            $newtokencount++;
        }
        $message=str_replace("{TOKENCOUNT}", $newtokencount, $clang->gT("{TOKENCOUNT} tokens have been created"));
        $tokenoutput .= "<div class='successheader'>$message</div>\n";
    }
    $tokenoutput .= "</div>\n";
}

if ($subaction == "tokenifygroup" && bHasSurveyPermission($surveyid,'tokens','update'))
{
    $tokenoutput .= "<div class='header'>".$clang->gT("Create group tokens")."</div>\n";
    $tokenoutput .= "<div class='messagebox'>\n";
    if (!isset($_POST['ok']) || !$_POST['ok'])
    {
        $tokenoutput .= "".$clang->gT("Clicking yes will generate tokens for all those in this group token list that have not been issued one. Is this OK?")."<br /><br />\n"
        ."<input type='submit' value='"
        .$clang->gT("Yes")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenifygroup&amp;ok=Y")."\" />\n"
        ."<input type='submit' value='"
        .$clang->gT("No")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n"
        ."<br />\n";
    }
    else
    {
        //get token length from survey settings
        $tlquery = "SELECT tokenlength FROM ".db_table_name("surveys")." WHERE sid=$surveyid";
        $tlresult = db_execute_assoc($tlquery);
        while ($tlrow = $tlresult->FetchRow())
        {
            $tokenlength = $tlrow['tokenlength'];
        }

        //if tokenlength is not set or there are other problems use the default value (15)
        if(!isset($tokenlength) || $tokenlength == '')
        {
            $tokenlength = 15;
        }

        // select all existing tokens
        $ntquery = "SELECT token FROM ".db_table_name("grouptokens_$surveyid")." group by token";
        $ntresult = db_execute_assoc($ntquery);
        while ($tkrow = $ntresult->FetchRow())
        {
            $existingtokens[$tkrow['token']]=null;
        }
        $newtokencount = 0;
        $tkquery = "SELECT gtid FROM ".db_table_name("grouptokens_$surveyid")." WHERE token IS NULL OR token=''";
        $tkresult = db_execute_assoc($tkquery) or safe_die ("Mucked up!<br />$tkquery<br />".$connect->ErrorMsg());
        while ($tkrow = $tkresult->FetchRow())
        {
            $isvalidtoken = false;
            while ($isvalidtoken == false)
            {
                $newtoken = randomkey($tokenlength);
                if (!isset($existingtokens[$newtoken])) {
                    $isvalidtoken = true;
                    $existingtokens[$newtoken]=null;
                }
            }
            $itquery = "UPDATE ".db_table_name("grouptokens_$surveyid")." SET token='$newtoken' WHERE gtid={$tkrow['gtid']}";
            $itresult = $connect->Execute($itquery);
            $newtokencount++;
        }
        $message=str_replace("{TOKENCOUNT}", $newtokencount, $clang->gT("{TOKENCOUNT} tokens have been created"));
        $tokenoutput .= "<div class='successheader'>$message</div>\n";
    }
    $tokenoutput .= "</div>\n";
}

if ($subaction == "delete" && bHasSurveyPermission($surveyid, 'tokens','delete'))
{
    $tokenoutput .= "<div class='messagebox'>\n"
    ."\t<div class='header'>"
    .$clang->gT("Delete")
    ."\t</div>\n"
    ."\t<p><br /><strong>";
    if(isset($tokenids) && count($tokenids)>0) {
        if(implode(", ", $tokenids) != "") {
            $dlquery = "DELETE FROM ".db_table_name("tokens_$surveyid")." WHERE tid IN (".implode(", ", $tokenids).")";
            $dlresult = $connect->Execute($dlquery) or safe_die ("Couldn't delete record {$tokenid}<br />".$connect->ErrorMsg()."\n\n$dlquery");
            $tokenoutput .= $clang->gT("Marked tokens have been deleted.");
        } else {
            $tokenoutput .= $clang->gT("No tokens were selected for deletion");
        }
    } elseif (isset($tokenid)) {
        $dlquery = "DELETE FROM ".db_table_name("tokens_$surveyid")." WHERE tid={$tokenid}";
        $dlresult = $connect->Execute($dlquery) or safe_die ("Couldn't delete record {$tokenid}<br />".$connect->ErrorMsg());
        $tokenoutput .= $clang->gT("Token has been deleted.");
    }
    $tokenoutput .= "</strong><br /><font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i><br /><br /></font>\n"
    ."</p>\n</div>\n";
}

if ($subaction == "deletegroup" && bHasSurveyPermission($surveyid,'tokens','delete'))
{
    $tokenoutput .= "<div class='messagebox'>\n"
    ."\t<div class='header'>"
    .$clang->gT("Delete")
    ."\t</div>\n"
    ."\t<p><br /><strong>";
    if(isset($gtokenids) && count($gtokenids)>0) {
        if(implode(", ", $gtokenids) != "") {
            $dlquery = "DELETE FROM ".db_table_name("grouptokens_$surveyid")." WHERE gtid IN (".implode(", ", $gtokenids).")";
            $dlresult = $connect->Execute($dlquery) or safe_die ("Couldn't delete record {$gtokenid}<br />".$connect->ErrorMsg()."\n\n$dlquery");
            $tokenoutput .= $clang->gT("Marked tokens have been deleted.");
        } else {
            $tokenoutput .= $clang->gT("No tokens were selected for deletion");
        }
    } elseif (isset($gtokenid)) {
        $dlquery = "DELETE FROM ".db_table_name("grouptokens_$surveyid")." WHERE gtid={$gtokenid}";
        $dlresult = $connect->Execute($dlquery) or safe_die ("Couldn't delete record {$gtokenid}<br />".$connect->ErrorMsg());
        $tokenoutput .= $clang->gT("Token has been deleted.");
    }
    $tokenoutput .= "</strong><br /><font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i><br /><br /></font>\n"
    ."</p>\n</div>\n";
}

if ($subaction == "managetokenattributes" && bHasSurveyPermission($surveyid, 'tokens', 'update'))
{
    $tokenoutput .= "<div class='header'>".$clang->gT("Manage token attribute fields")."</div>\n";
    $tokenfields=GetTokenFieldsAndNames($surveyid,true);
    $nrofattributes=0;
    $tokenoutput.='<form action="'.$scriptname.'" method="post">'
    ."<table class='listsurveys'><tr><th>".$clang->gT("Attribute field")."</th><th>".$clang->gT("Field description")."</th><th>".$clang->gT("Example data")."</th></tr>";

    $exampledataquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
    $exampledata = db_select_limit_assoc($exampledataquery,1) or safe_die ("Could not get example data!<br />$exampledataquery<br />".$connect->ErrorMsg());
    $examplerow = $exampledata->FetchRow();


    foreach ($tokenfields as $tokenfield=>$tokendescription)
    {
        $nrofattributes++;
        $tokenoutput.="<tr><td>$tokenfield</td><td><input type='text' name='description_$tokenfield' value='".htmlspecialchars($tokendescription,ENT_QUOTES,'UTF-8')."' /></td><td>";
        if ($examplerow!==false)
        {
            $tokenoutput.=htmlspecialchars($examplerow[$tokenfield]);
        }
        else
        {
            $tokenoutput.=$clang->gT('<no data>');
        }
        $tokenoutput.="</td></tr>";
    }
    $tokenoutput.="</table><p>"
    .'<input type="submit" value="'.$clang->gT('Save').'" />'
    ."<input type='hidden' name='action' value='tokens' />\n"
    ."<input type='hidden' name='subaction' value='updatetokenattributedescriptions' />\n"
    ."<input type='hidden' name='sid' value=\"{$surveyid}\" /></p>\n"
    .'</form><br /><br />';

    $tokenoutput .= "<div class='header'>".$clang->gT("Add token attributes")."</div><p>\n";

    $tokenoutput .=sprintf($clang->gT('There are %s user attribute fields in this token table'),$nrofattributes).'</p>'
    .'<form id="addattribute" action="'.$scriptname.'" method="post">'
    .'<p>'
    .'<label for="addnumber">'.$clang->gT('Number of attribute fields to add:').'</label>'
    .'<input type="text" id="addnumber" name="addnumber" size="3" maxlength="3" value="1" />'
    .'</p>'
    .'<p>'
    .'<input type="submit" value="'.$clang->gT('Add fields').'" />'
    ."<input type='hidden' name='action' value='tokens' />"
    ."<input type='hidden' name='subaction' value='updatetokenattributes' />"
    ."<input type='hidden' name='sid' value=\"{$surveyid}\" />"
    ."</p>"
    .'</form>'
    .'<br /><br />';
}

if ($subaction == "updatetokenattributedescriptions" && bHasSurveyPermission($surveyid, 'tokens', 'update'))  
{
    // find out the existing token attribute fieldnames
    $tokenattributefieldnames=GetAttributeFieldNames($surveyid);
    $fieldcontents='';
    foreach ($tokenattributefieldnames as $fieldname)
    {
        $fieldcontents.=$fieldname.'='.strip_tags($_POST['description_'.$fieldname])."\n";
    }
    $updatequery = "update ".db_table_name('surveys').' set attributedescriptions='.db_quoteall($fieldcontents,true)." where sid=$surveyid";
    $execresult=db_execute_assoc($updatequery);

    $tokenoutput .= "\t<div class='header'>".$clang->gT("Update token attribute descriptions")."</div>\n"
    ."\t<div class='messagebox'>";
    if ($execresult===false)
    {
        $tokenoutput.="\t\t<div class='warningheader'>".$clang->gT("Updating token attribute descriptions failed:")."".htmlspecialchars($connect->ErrorMsg())."</div>"
        ."\t\t<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=managetokenattributes', '_top')\" />\n";
    }
    else
    {
        $tokenoutput.="\t\t<div class='successheader'>".$clang->gT("Token attribute descriptions were successfully updated.")."</div>"
        ."\t\t<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=managetokenattributes', '_top')\" />\n";
    }
    $tokenoutput .= "\t</div>";
}
$tokenoutput .= "<div id ='dialog-modal'></div>";
if ($subaction == "updatetokenattributes" && bHasSurveyPermission($surveyid, 'tokens', 'update'))
{
    $number2add=sanitize_int($_POST['addnumber'],1,100);
    // find out the existing token attribute fieldnames
    $tokenfieldnames = array_values($connect->MetaColumnNames("{$dbprefix}tokens_$surveyid", true));
    $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
    $i=1;
    for ($b=0;$b<$number2add;$b++)
    {
        while (in_array('attribute_'.$i,$tokenattributefieldnames)!==false) {
            $i++;
        }
        $tokenattributefieldnames[]='attribute_'.$i;
        $fields[]=array('attribute_'.$i,'C','255');
    }
    $dict = NewDataDictionary($connect);
    $sqlarray = $dict->ChangeTableSQL("{$dbprefix}tokens_$surveyid", $fields);
    $execresult=$dict->ExecuteSQLArray($sqlarray, false);

    $tokenoutput .= "\t<div class='header'>".$clang->gT("Update token attributes")."</div>\n"
    ."\t<div class='messagebox'>";
    if ($execresult==0)
    {
        $tokenoutput.="\t\t<div class='warningheader'>".$clang->gT("Adding attribute fields failed:")."".htmlspecialchars($connect->ErrorMsg())."</div>"
        ."\t\t<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=managetokenattributes', '_top')\" />\n";
    }
    else
    {
        $tokenoutput.="\t\t<div class='successheader'>".sprintf($clang->gT("%s field(s) were successfully added."),$number2add)."</div>"
        ."\t\t<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=managetokenattributes', '_top')\" />\n";
    }
    $tokenoutput .= "\t</div>";
}


if (($subaction == "edit" &&  bHasSurveyPermission($surveyid, 'tokens','update')) || 
    ($subaction == "addnew" && bHasSurveyPermission($surveyid, 'tokens','create')))
{
    if ($subaction == "edit")
    {
        $edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE tid={$tokenid}";
        $edresult = db_execute_assoc($edquery);
        $edfieldcount = $edresult->FieldCount();
        while($edrow = $edresult->FetchRow())
        {
            //Create variables with the same names as the database column names and fill in the value
            foreach ($edrow as $Key=>$Value) {$$Key = $Value;}
        }
    }
    if ($subaction != "edit")
    {
        $edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
        $edresult = db_select_limit_assoc($edquery, 1);
        $edfieldcount = $edresult->FieldCount();
    }

    $tokenoutput .= "<div class='header'>";
    if ($subaction == "edit")
    {
        $tokenoutput .=$clang->gT("Edit token entry");
    }
    else
    {
        $tokenoutput .=$clang->gT("Add token entry");
    }

    $tokenoutput .="</div>"
    ."<form id='edittoken' class='form30' method='post' action='$scriptname?action=tokens'>\n"
    ."<ul>\n"
    ."\t<li><label>ID:</label>\n";
    if ($subaction == "edit")
    {$tokenoutput .=$tokenid;} else {$tokenoutput .=$clang->gT("Auto");}
    $tokenoutput .= "</li>\n"
    ."<li><label for='firstname'>".$clang->gT("First Name").":</label>\n"
    ."<input type='text' size='30' id='firstname' name='firstname' value=\"";
    if (isset($firstname)) {$tokenoutput .= $firstname;}
    $tokenoutput .= "\" /></li>\n"
    ."<li><label for='lastname'>".$clang->gT("Last Name").":</label>\n"
    ."<input type='text' size='30'  id='lastname' name='lastname' value=\"";
    if (isset($lastname)) {$tokenoutput .= $lastname;}
    $tokenoutput .= "\" /></li>\n"
    ."\t<li><label for='email'>".$clang->gT("Email").":</label>\n"
    ."\t<input type='text' maxlength='320' size='50' id='email' name='email' value=\"";
    if (isset($email)) {$tokenoutput .= $email;}
    $tokenoutput .= "\" /></li>\n"
    ."<li><label for='emailstatus'>".$clang->gT("Email Status").":</label>\n"
    ."<input type='text' maxlength='320' size='50' id='emailstatus' name='emailstatus' value=\"";
    if (isset($emailstatus)) {
        $tokenoutput .= $emailstatus;
    }
    else {
        $tokenoutput .= "OK";
    }
    $tokenoutput .= "\" /></li>\n"
    ."<li><label for='token'>".$clang->gT("Token").":</label>\n"
    ."<input type='text' size='20' name='token' id='token' value=\"";
    if (isset($token)) {$tokenoutput .= $token;}
    $tokenoutput .= "\" />\n";
    if ($subaction == "addnew")
    {
        $tokenoutput .= "<font size='1' color='red'>".$clang->gT("You can leave this blank, and automatically generate tokens using 'Generate Tokens'")."</font>\n";
    }
    $tokenoutput .= "\t</li>\n"
    ."<li><label for='language'>".$clang->gT("Language").":</label>\n";
    if (isset($language)) {$tokenoutput .= languageDropdownClean($surveyid,$language);}
    else {
        $tokenoutput .= languageDropdownClean($surveyid,GetBaseLanguageFromSurveyID($surveyid));
    }
    $tokenoutput .= "</li>\n"

    ."\t<li><label for='sent'>".$clang->gT("Invite sent?")."</label>\n"
    ."\t<input type='text' size='20' id='sent' name='sent' value=\"";
    if (isset($sent)) {$tokenoutput .= $sent;}	else {$tokenoutput .= "N";}
    $tokenoutput .= "\" /></li>\n"

    ."\t<li><label for='remindersent'>".$clang->gT("Reminder sent?")."</label>\n"
    ."\t<input type='text' size='20' id='remindersent' name='remindersent' value=\"";
    if (isset($remindersent)) {$tokenoutput .= $remindersent;}    else {$tokenoutput .= "N";}
    $tokenoutput .= "\" /></li>\n";

    if ($subaction == "edit")
    {
        $tokenoutput.="\t<li><label for='remindercount'>".$clang->gT("Reminder count:")."</label>\n"
        ."\t<input type='text' size='6' id='remindercount' name='remindercount' value=\"";
        $tokenoutput .= $remindercount;
        $tokenoutput .= "\" /></li>\n";
    }

    $tokenoutput.="\t<li><label for='completed'>".$clang->gT("Completed?")."</label>\n"
    ."\t<input type='text' size='20' id='completed' name='completed' value=\"";
    if (isset($completed)) {$tokenoutput .= $completed;} else {$tokenoutput .= "N";}
    $tokenoutput .= "\" /></li>\n"

    ."\t<li><label for='usesleft'>".$clang->gT("Uses left:")."</label>\n"
    ."\t<input type='text' size='20' id='usesleft' name='usesleft' value=\"";
    if (isset($usesleft)) {$tokenoutput .= $usesleft;} else {$tokenoutput .= "1";}
    $tokenoutput .= "\" /></li>\n"

    ."\t<li><label for='validfrom'>".$clang->gT("Valid from").":</label>\n"
    ."\t<input type='text' class='popupdatetime' size='20' id='validfrom' name='validfrom' value=\"";
    if (isset($validfrom)){
        $datetimeobj = new Date_Time_Converter($validfrom , "Y-m-d H:i:s");
        $tokenoutput .=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }
    $tokenoutput .= "\" />\n <label for='validuntil'>".$clang->gT('until')
    ."\t</label><input type='text' size='20' id='validuntil' name='validuntil' class='popupdatetime' value=\"";
    if (isset($validuntil)){
        $datetimeobj = new Date_Time_Converter($validuntil , "Y-m-d H:i:s");
        $tokenoutput .=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }
    $tokenoutput .= "\" /> <span class='annotation'>".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'].' '.$clang->gT('hh:mm')).'</span>'
    ."</li>\n";

    // now the attribute fieds
    $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
    foreach ($attrfieldnames as $attr_name=>$attr_description)
    {
        $tokenoutput .= "<li>"
        ."<label for='$attr_name'>".$attr_description.":</label>\n"
        ."\t<input type='text' size='55' id='$attr_name' name='$attr_name' value='";
        if (isset($$attr_name)) { $tokenoutput .=htmlspecialchars($$attr_name,ENT_QUOTES,'UTF-8');}
        $tokenoutput.="' /></li>";
    }

    $tokenoutput .="\t</ul><p>";
    switch($subaction)
    {
        case "edit":
            $tokenoutput .= "<input type='submit' value='".$clang->gT("Update token entry")."' />\n"
            ."<input type='hidden' name='subaction' value='updatetoken' />\n"
            ."<input type='hidden' name='tid' value='{$tokenid}' />\n";
            break;
        case "addnew":
            $tokenoutput .= "<input type='submit' value='".$clang->gT("Add token entry")."' />\n"
            ."<input type='hidden' name='subaction' value='inserttoken' />\n";
            break;
    }
    $tokenoutput .= "<input type='hidden' name='sid' value='$surveyid' /></p>\n"
    ."</form>\n";
}

if (($subaction == "editgroup" || $subaction == "addnewgroup") && bHasSurveyPermission($surveyid,'tokens','add'))
{
    if ($subaction == "editgroup")
    {
        $edquery = "SELECT * FROM ".db_table_name("grouptokens_$surveyid")." WHERE gtid={$gtokenid}";
        $edresult = db_execute_assoc($edquery);
        $edfieldcount = $edresult->FieldCount();
        while($edrow = $edresult->FetchRow())
        {
            //Create variables with the same names as the database column names and fill in the value
            foreach ($edrow as $Key=>$Value) {$$Key = $Value;}
        }
    }
    if ($subaction != "editgroup")
    {
        $edquery = "SELECT * FROM ".db_table_name("grouptokens_$surveyid");
        $edresult = db_select_limit_assoc($edquery, 1);
        $edfieldcount = $edresult->FieldCount();
    }

    $tokenoutput .= "<div class='header'>";
    if ($subaction == "editgroup")
    {
        $tokenoutput .=$clang->gT("Edit group token entry");
    }
    else
    {
        $tokenoutput .=$clang->gT("Add group token entry");
    }

    $tokenoutput .="</div>"
    ."<form id='editgrouptoken' class='form30' method='post' action='$scriptname?action=tokens'>\n"
    ."<ul>\n"
    ."\t<li><label>ID:</label>\n";
    if ($subaction == "editgroup")
    {$tokenoutput .= $gtokenid;} else {$tokenoutput .=$clang->gT("Auto");}
    $tokenoutput .= "</li>\n"
    ."<li><label for='name'>".$clang->gT("Name").":</label>\n"
    ."<input type='text' size='30' id='name' name='name' value=\"";
    if (isset($name)) {$tokenoutput .= $name;}
    $tokenoutput .= "\" /></li>\n"
    ."<li><label for='description'>".$clang->gT("Description").":</label>\n"
    ."<input type='text' size='30'  id='description' name='description' value=\"";
    if (isset($description)) {$tokenoutput .= $description;}
    $tokenoutput .= "\" /></li>\n"
    ."<li><label for='token'>".$clang->gT("Token").":</label>\n"
    ."<input type='text' size='20' name='token' id='token' value=\"";
    if (isset($token)) {$tokenoutput .= $token;}
    $tokenoutput .= "\" />\n";
    if ($subaction == "addnewgroup")
    {
        $tokenoutput .= "<font size='1' color='red'>".$clang->gT("You can leave this blank, and automatically generate tokens using 'Generate group tokens'")."</font>\n";
    }
    $tokenoutput .= "\t</li>\n"
    ."<li><label for='language'>".$clang->gT("Language").":</label>\n";
    if (isset($language)) {$tokenoutput .= languageDropdownClean($surveyid,$language);}
    else {
        $tokenoutput .= languageDropdownClean($surveyid,GetBaseLanguageFromSurveyID($surveyid));
    }
    $tokenoutput .= "</li>\n"

	// Editing the 'completedsurveys' value by hand is not intended, but let's leave it.
    ."\t<li><label for='completedsurveys'>".$clang->gT("Completed surveys").":</label>\n"
    ."\t<input type='text' size='20' id='completedsurveys' name='completedsurveys' value=\"";
    if (isset($completedsurveys)) {$tokenoutput .= $completedsurveys;} else {$tokenoutput .= "0";}
    $tokenoutput .= "\" /></li>\n"

    ."\t<li><label for='validfrom'>".$clang->gT("Valid from").":</label>\n"
    ."\t<input type='text' class='popupdatetime' size='20' id='validfrom' name='validfrom' value=\"";
    if (isset($validfrom)){
        $datetimeobj = new Date_Time_Converter($validfrom , "Y-m-d H:i:s");
        $tokenoutput .=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }
    $tokenoutput .= "\" />\n <label for='validuntil'>".$clang->gT('until')
    ."\t</label><input type='text' size='20' id='validuntil' name='validuntil' class='popupdatetime' value=\"";
    if (isset($validuntil)){
        $datetimeobj = new Date_Time_Converter($validuntil , "Y-m-d H:i:s");
        $tokenoutput .=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }
    $tokenoutput .= "\" /> <span class='annotation'>".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'].' '.$clang->gT('hh:mm')).'</span>'
    ."</li>\n";

    $tokenoutput .="\t</ul><p>";
    switch($subaction)
    {
        case "editgroup":
            $tokenoutput .= "<input type='submit' value='".$clang->gT("Update group token entry")."' />\n"
            ."<input type='hidden' name='subaction' value='updategrouptoken' />\n"
            ."<input type='hidden' name='gtid' value='{$gtokenid}' />\n";
            break;
        case "addnewgroup":
            $tokenoutput .= "<input type='submit' value='".$clang->gT("Add group token entry")."' />\n"
            ."<input type='hidden' name='subaction' value='insertgrouptoken' />\n";
            break;
    }
    $tokenoutput .= "<input type='hidden' name='sid' value='$surveyid' /></p>\n"
    ."</form>\n";
}

if ($subaction == "updatetoken" && bHasSurveyPermission($surveyid, 'tokens','update'))
{
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Edit token entry")."</div>\n"
    ."\t<div class='messagebox'>\n";
    if (trim($_POST['validfrom'])=='') {
        $_POST['validfrom']=null;
    }
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validfrom'] =$datetimeobj->convert('Y-m-d H:i:s');
    }
    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validuntil'] =$datetimeobj->convert('Y-m-d H:i:s');
    }
    $data = array();
    $data[] = $_POST['firstname'];
    $data[] = $_POST['lastname'];
    $data[] = sanitize_email($_POST['email']);
    $data[] = $_POST['emailstatus'];
    $santitizedtoken=sanitize_token($_POST['token']);
    $data[] = $santitizedtoken;
    $data[] = sanitize_languagecode($_POST['language']);
    $data[] = $_POST['sent'];
    $data[] = $_POST['completed'];
    $data[] = $_POST['usesleft'];
    //    $db->DBTimeStamp("$year-$month-$day $hr:$min:$secs");
    $data[] = $_POST['validfrom'];
    $data[] = $_POST['validuntil'];
    $data[] = $_POST['remindersent'];
    $data[] = intval($_POST['remindercount']);

    $udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where tid<>{$tokenid} and token<>'' and token='{$santitizedtoken}'") or safe_die ("Update record {$tokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());
    if ($udresult->RecordCount()==0)
    {
        $udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where tid={$tokenid} and email='".sanitize_email($_POST['email'])."'") or safe_die ("Update record {$tokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());


        // Using adodb Execute with blinding method so auto-dbquote is done
        $udquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET firstname=?, "
        . "lastname=?, email=?, emailstatus=?, "
        . "token=?, language=?, sent=?, completed=?, usesleft=?, validfrom=?, validuntil=?, remindersent=?, remindercount=?";
        $attrfieldnames=GetAttributeFieldnames($surveyid);
        foreach ($attrfieldnames as $attr_name)
        {
            $udquery.= ", $attr_name=?";
            $data[].=$_POST[$attr_name];
        }

        $udquery .= " WHERE tid={$tokenid}";
        $udresult = $connect->Execute($udquery, $data) or safe_die ("Update record {$tokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());
        $tokenoutput .=  "\t\t<div class='successheader'>".$clang->gT("Success")."</div>\n"
        ."\t\t<br />".$clang->gT("The token entry was successfully updated.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Display Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse', '_top')\" />\n";
    }
    else
    {
        $tokenoutput .=  "\t\t<div class='warningheader'>".$clang->gT("Failed")."</div>\n"
        ."\t\t<br />".$clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Show this token entry")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=edit&amp;tid={$tokenid}', '_top')\" />\n";
    }
    $tokenoutput .= "\t</div>";
}

if ($subaction == "updategrouptoken" && bHasSurveyPermission($surveyid,'tokens','update'))
{
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Edit group token entry")."</div>\n"
    ."\t<div class='messagebox'>\n";
    if (trim($_POST['validfrom'])=='') {
        $_POST['validfrom']=null;
    }
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validfrom'] =$datetimeobj->convert('Y-m-d H:i:s');
    }
    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validuntil'] =$datetimeobj->convert('Y-m-d H:i:s');
    }
    $data = array();
    $data[] = $_POST['name'];
    $data[] = $_POST['description'];
    $santitizedtoken=sanitize_token($_POST['token']);
    $data[] = $santitizedtoken;
    $data[] = sanitize_languagecode($_POST['language']);
    $data[] = $_POST['completedsurveys'];
    $data[] = $_POST['validfrom'];
    $data[] = $_POST['validuntil'];

    $udresult = $connect->Execute("Select * from ".db_table_name("grouptokens_$surveyid")." where gtid<>{$gtokenid} and token<>'' and token='{$santitizedtoken}'") or safe_die ("Update record {$gtokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());
    if ($udresult->RecordCount()==0)
    {
        // Using adodb Execute with blinding method so auto-dbquote is done
        $udquery = "UPDATE ".db_table_name("grouptokens_$surveyid")." SET name=?, "
        . "description=?, token=?, language=?, completedsurveys=?, validfrom=?, validuntil=?"
        . " WHERE gtid={$gtokenid}";
        $udresult = $connect->Execute($udquery, $data) or safe_die ("Update record {$gtokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());
        $tokenoutput .=  "\t\t<div class='successheader'>".$clang->gT("Success")."</div>\n"
        ."\t\t<br />".$clang->gT("The group token entry was successfully updated.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Display Group Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup', '_top')\" />\n";
    }
    else
    {
        $tokenoutput .=  "\t\t<div class='warningheader'>".$clang->gT("Failed")."</div>\n"
        ."\t\t<br />".$clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Show this token entry")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=editgroup&amp;gtid={$gtokenid}', '_top')\" />\n";
    }
    $tokenoutput .= "\t</div>";
}

if ($subaction == "inserttoken" && (bHasSurveyPermission($surveyid, 'tokens','create')))
{
    //Fix up dates and match to database format
    if (trim($_POST['validfrom'])=='') {
        $_POST['validfrom']=null;
    }
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validfrom'] =$datetimeobj->convert('Y-m-d H:i:s');
    }
    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validuntil'] =$datetimeobj->convert('Y-m-d H:i:s');
    }

    $santitizedtoken=sanitize_token($_POST['token']);
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Add token entry")."</div>\n"
    ."\t<div class='messagebox'>\n";
    $data = array('firstname' => $_POST['firstname'],
	'lastname' => $_POST['lastname'],
	'email' => sanitize_email($_POST['email']),
	'emailstatus' => $_POST['emailstatus'],
	'token' => $santitizedtoken,
	'language' => sanitize_languagecode($_POST['language']),
    'sent' => $_POST['sent'],
	'remindersent' => $_POST['remindersent'],
	'completed' => $_POST['completed'],
	'usesleft' => $_POST['usesleft'],
	'validfrom' => $_POST['validfrom'],
	'validuntil' => $_POST['validuntil']);
    // add attributes
    $attrfieldnames=GetAttributeFieldnames($surveyid);
    foreach ($attrfieldnames as $attr_name)
    {
        $data[$attr_name]=$_POST[$attr_name];
    }
    $tblInsert=db_table_name('tokens_'.$surveyid);
    $udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where  token<>'' and token='{$santitizedtoken}'");
    if ($udresult->RecordCount()==0)
    {
        // AutoExecute
        $inresult = $connect->AutoExecute($tblInsert, $data, 'INSERT') or safe_die ("Add new record failed:<br />\n$inquery<br />\n".$connect->ErrorMsg());
        $tokenoutput .= "\t\t<div class='successheader'>".$clang->gT("Success")."</div>\n"
        ."\t\t<br />".$clang->gT("New token was added.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Display Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse', '_top')\" /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Add another token entry")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnew', '_top')\" /><br />\n";
    }
    else
    {
        $tokenoutput .=  "\t\t<div class='warningheader'>".$clang->gT("Failed")."</div>\n"
        ."\t\t<br />".$clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Display Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse', '_top')\" /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Add new token entry")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnew', '_top')\" /><br />\n";
    }
    $tokenoutput .= "\t</div>";
}

if ($subaction == "insertgrouptoken" && bHasSurveyPermission($surveyid,'tokens','create'))
{
    //Fix up dates and match to database format
    if (trim($_POST['validfrom'])=='') {
        $_POST['validfrom']=null;
    }
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validfrom'] =$datetimeobj->convert('Y-m-d H:i:s');
    }
    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
    else
    {
        $datetimeobj = new Date_Time_Converter(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i');
        $_POST['validuntil'] =$datetimeobj->convert('Y-m-d H:i:s');
    }

    $santitizedtoken=sanitize_token($_POST['token']);
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Add group token entry")."</div>\n"
    ."\t<div class='messagebox'>\n";
    $data = array('name' => $_POST['name'],
	'description' => $_POST['description'],
	'token' => $santitizedtoken,
	'language' => sanitize_languagecode($_POST['language']),
	'completedsurveys' => $_POST['completedsurveys'],
	'validfrom' => $_POST['validfrom'],
	'validuntil' => $_POST['validuntil']);
    $tblInsert=db_table_name('grouptokens_'.$surveyid);
    $udresult = $connect->Execute("Select * from ".db_table_name("grouptokens_$surveyid")." where  token<>'' and token='{$santitizedtoken}'");
    if ($udresult->RecordCount()==0)
    {
        // AutoExecute
        $inresult = $connect->AutoExecute($tblInsert, $data, 'INSERT') or safe_die ("Add new record failed:<br />\n$inquery<br />\n".$connect->ErrorMsg());
        $tokenoutput .= "\t\t<div class='successheader'>".$clang->gT("Success")."</div>\n"
        ."\t\t<br />".$clang->gT("New group token was added.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Display Group Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup', '_top')\" /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Add another group token entry")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnewgroup', '_top')\" /><br />\n";
    }
    else
    {
        $tokenoutput .=  "\t\t<div class='warningheader'>".$clang->gT("Failed")."</div>\n"
        ."\t\t<br />".$clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.")."<br /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Display Group Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browsegroup', '_top')\" /><br />\n"
        ."\t\t<input type='button' value='".$clang->gT("Add new group token entry")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnewgroup', '_top')\" /><br />\n";
    }
    $tokenoutput .= "\t</div>";
}

if ($subaction == "import" && bHasSurveyPermission($surveyid, 'tokens','import'))
{
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Upload CSV File")."</div>\n";
    form_csv_upload();
    $tokenoutput .= "<div class='messagebox'>\n"
    ."<div class='header'>".$clang->gT("CSV input format")."</div>\n"
    ."<p>".$clang->gT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order.").'</p><span style="font-weight:bold;">'.$clang->gT("Mandatory fields:")."</span> firstname,lastname,email<br />"
    .'<span style="font-weight:bold;">'.$clang->gT('Optional fields:')."</span> emailstatus, token, language, validfrom, validuntil, attribute_1, attribute_2, attribute_3, ... ."
    ."</div>\n";
}

if ($subaction == "importldap" && bHasSurveyPermission($surveyid, 'tokens','import'))   
{
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Upload LDAP entries")."</div>\n";
    formldap();
    $tokenoutput .= "<div class='messagebox'>\n"
    ."\t<div class='header'>".$clang->gT("Note:")."</div><br />\n"
    .$clang->gT("LDAP queries are defined by the administrator in the config-ldap.php file")."\n"
    ."</div>\n";
}

if ($subaction == "upload" && bHasSurveyPermission($surveyid, 'tokens','import'))      
{
    $attrfieldnames=GetAttributeFieldnames($surveyid);
    $duplicatelist=array();
    $invalidemaillist=array();
    $invalidformatlist=array();
    $tokenoutput .= "\t<div class='header'>".$clang->gT("Token file upload")."</div>\n"
    ."\t<div class='messagebox'>\n";
    if (!isset($tempdir))
    {
        $the_path = $homedir;
    }
    else
    {
        $the_path = $tempdir;
    }
    $the_file_name = $_FILES['the_file']['name'];
    $the_file = $_FILES['the_file']['tmp_name'];
    $the_full_file_path = $the_path."/".$the_file_name;
    if (!@move_uploaded_file($the_file, $the_full_file_path))
    {
        $errormessage="<div class='warningheader'>".$clang->gT("Error")."</div><p>".$clang->gT("Upload file not found. Check your permissions and path ({$the_full_file_path}) for the upload directory")."</p>\n";
        form_csv_upload($errormessage);
    }
    else
    {
        $tokenoutput .= "<div class='successheader'>".$clang->gT("Uploaded CSV file successfully")."</div><br />\n";
        $xz = 0; $recordcount = 0; $xv = 0;
        // This allows to read file with MAC line endings too
        @ini_set('auto_detect_line_endings', true);
        // open it and trim the ednings
        $tokenlistarray = file($the_full_file_path);
        $baselanguage=GetBaseLanguageFromSurveyID($surveyid);
        if (!isset($tokenlistarray))
        {
            $tokenoutput .= "<div class='warningheader'>".$clang->gT("Failed to open the uploaded file!")."</div><br />\n";
        }
        if (!isset($_POST['filterduplicatefields']) || (isset($_POST['filterduplicatefields']) && count($_POST['filterduplicatefields'])==0))
        {
            $filterduplicatefields=array('firstname','lastname','email');
        } else {
            $filterduplicatefields=$_POST['filterduplicatefields'];
        }
        $separator = returnglobal('separator');
        foreach ($tokenlistarray as $buffer)
        {
            $buffer=@mb_convert_encoding($buffer,"UTF-8",$uploadcharset);
            $firstname = ""; $lastname = ""; $email = ""; $emailstatus="OK"; $token = ""; $language=""; $attribute1=""; $attribute2=""; //Clear out values from the last path, in case the next line is missing a value
            if ($recordcount==0)
            {
                // Pick apart the first line
                $buffer=removeBOM($buffer);
                $allowedfieldnames=array('firstname','lastname','email','emailstatus','token','language', 'validfrom', 'validuntil');
                $allowedfieldnames=array_merge($attrfieldnames,$allowedfieldnames);
                
                switch ($separator) {
                    case 'comma':
                        $separator = ',';
                        break;
                    case 'semicolon':
                        $separator = ';';
                        break;
                    default:
                        $comma = substr_count($buffer,',');
                        $semicolon = substr_count($buffer,';');
                        if ($semicolon>$comma) $separator = ';'; else $separator = ',';
                }
                $firstline = convertCSVRowToArray($buffer,$separator,'"');
                $firstline=array_map('trim',$firstline);
                $ignoredcolumns=array();
                //now check the first line for invalid fields
                foreach ($firstline as $index=>$fieldname)
                {
                    $firstline[$index] = preg_replace("/(.*) <[^,]*>$/","$1",$fieldname);
                    $fieldname = $firstline[$index];
                    if (!in_array($fieldname,$allowedfieldnames))
                    {
                        $ignoredcolumns[]=$fieldname;
                    }
                }
                if (!in_array('firstname',$firstline) || !in_array('lastname',$firstline) || !in_array('email',$firstline))
                {
                    $tokenoutput .= "<div class='warningheader'>".$clang->gT("Error: Your uploaded file is missing one or more of the mandatory columns: 'firstname', 'lastname' or 'email'")."</div><br />";
                    $recordcount=count($tokenlistarray);
                    break;
                }

            }
            else
            {

                $line = convertCSVRowToArray($buffer,$separator,'"');

                if (count($firstline)!=count($line))
                {
                    $invalidformatlist[]=$recordcount;
                    $recordcount++;
                    continue;
                }
                $writearray=array_combine($firstline,$line);

                //kick out ignored columns
                foreach ($ignoredcolumns  as $column)
                {
                    unset($writearray[$column]);
                }
                $dupfound=false;
                $invalidemail=false;

                if ($filterduplicatetoken!=false)
                {
                    $dupquery = "SELECT tid from ".db_table_name("tokens_$surveyid")." where 1=1";
                    foreach($filterduplicatefields as $field)
                    {
                        if (isset($writearray[$field])) {
                            $dupquery.=' and '.db_quote_id($field).' = '.db_quoteall($writearray[$field]);
                        }
                    }
                    $dupresult = $connect->Execute($dupquery) or safe_die ("Invalid field in duplicate check<br />$dupquery<br /><br />".$connect->ErrorMsg());
                    if ( $dupresult->RecordCount() > 0)
                    {
                        $dupfound = true;
                        $duplicatelist[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
                    }
                }


                $writearray['email'] = trim($writearray['email']);

                //treat blank emails
                if ($filterblankemail && $writearray['email']=='')
                {
                    $invalidemail=true;
                    $invalidemaillist[]=$line[0]." ".$line[1]." ( )";
                }
                if  ($writearray['email']!='')
                {
                    $aEmailAddresses=explode(';',$writearray['email']);
                    foreach ($aEmailAddresses as $sEmailaddress)
                    {
                        if (!validate_email($sEmailaddress))
                        {
                    $invalidemail=true;
                    $invalidemaillist[]=$line[0]." ".$line[1]." (".$line[2].")";
                }
                    }
                }

                if (!isset($writearray['token'])) {
                    $writearray['token'] = '';
                } else {
                    $writearray['token']=sanitize_token($writearray['token']);
                }

                if (!$dupfound && !$invalidemail)
                {
                    if (!isset($writearray['emailstatus']) || $writearray['emailstatus']=='') $writearray['emailstatus'] = "OK";
                    if (!isset($writearray['language']) || $writearray['language'] == "") $writearray['language'] = $baselanguage;
                    if (isset($writearray['validfrom']) && trim($writearray['validfrom']=='')){ unset($writearray['validfrom']);}
                    if (isset($writearray['validuntil']) && trim($writearray['validuntil']=='')){ unset($writearray['validuntil']);}

                    // sanitize it before writing into table
                    $sanitizedArray = array_map('db_quote',array_values($writearray));

                    $iq = "INSERT INTO ".db_table_name("tokens_$surveyid")." \n"
                    . "(".implode(',',array_keys($writearray)).") \n"
                    . "VALUES ('".implode("','",$sanitizedArray)."')";
                    $ir = $connect->Execute($iq);

                    if (!$ir)
                    {
                        $duplicatelist[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
                    } else {
                        $xz++;
                    }
                }
                $xv++;
            }
            $recordcount++;
        }
        $recordcount = $recordcount-1;
        if ($xz != 0)
        {
            $tokenoutput .= "<div class='successheader'>".$clang->gT("Successfully created token entries")."</div><br />\n";
        } else {
            $tokenoutput .= "<div class='warningheader'>".$clang->gT("Failed to create token entries")."</div>\n";
        }
        $message = '<ul><li>'.sprintf($clang->gT("%s records in CSV"),$recordcount)."</li>\n";
        $message .= '<li>'.sprintf($clang->gT("%s records met minumum requirements"),$xv)."</li>\n";
        $message .= '<li>'.sprintf($clang->gT("%s records imported"),$xz)."</li></ul>\n";


        if (count($duplicatelist)>0 || count($invalidformatlist)>0 || count($invalidemaillist)>0)
        {

            $message .="<div class='warningheader'>".$clang->gT('Warnings')."</div><ul>";
            if (count($duplicatelist)>0)
            {
                $message .= '<li>'.sprintf($clang->gT("%s duplicate records removed"),count($duplicatelist));
                $message .= " [<a href='#' onclick='$(\"#duplicateslist\").toggle();'>".$clang->gT("List")."</a>]";
                $message .= "<div class='badtokenlist' id='duplicateslist' style='display: none;'><ul>";
                foreach($duplicatelist as $data) {
                    $message .= "<li>$data</li>\n";
                }
                $message .= "</ul></div>";
                $message .= "</li>\n";
            }

            if (count($invalidformatlist)>0)
            {
                $message .= '<li>'.sprintf($clang->gT("%s lines had a mismatching number of fields."),count($invalidformatlist));
                $message .= " [<a href='#' onclick='$(\"#invalidformatlist\").toggle();'>".$clang->gT("List")."</a>]";
                $message .= "<div class='badtokenlist' id='invalidformatlist' style='display: none;'><ul>";
                foreach($invalidformatlist as $data) {
                    $message .= "<li>Line $data</li>\n";
                }
            }

            if (count($invalidemaillist)>0)
            {
                $message .= '<li>'.sprintf($clang->gT("%s records with invalid email address removed"),count($invalidemaillist));
                $message .= " [<a href='#' onclick='$(\"#invalidemaillist\").toggle();'>".$clang->gT("List")."</a>]";
                $message .= "<div class='badtokenlist' id='invalidemaillist' style='display: none;'><ul>";
                foreach($invalidemaillist as $data) {
                    $message .= "<li>$data</li>\n";
                }
            }
            $message .= "</ul>";
        }
        $message .= "</div>";

        $tokenoutput .= "$message<br />\n";
        unlink($the_full_file_path);
    }
    $tokenoutput .= "</div>\n";
}

if ($subaction == "uploadldap" && bHasSurveyPermission($surveyid, 'tokens','create'))      
{
    $duplicatelist=array();
    $invalidemaillist=array();
    $tokenoutput .= "\t<tr><td colspan='2' height='4'><strong>"
    .$clang->gT("Uploading LDAP Query")."</strong></td></tr>\n"
    ."\t<tr><td align='center'>\n";
    $ldapq=$_POST['ldapQueries']; // the ldap query id

    $ldap_server_id=$ldap_queries[$ldapq]['ldapServerId'];
    $ldapserver=$ldap_server[$ldap_server_id]['server'];
    $ldapport=$ldap_server[$ldap_server_id]['port'];
    if (isset($ldap_server[$ldap_server_id]['encoding']) &&
    $ldap_server[$ldap_server_id]['encoding'] != 'utf-8' &&
    $ldap_server[$ldap_server_id]['encoding'] != 'UTF-8')
    {
        $ldapencoding=$ldap_server[$ldap_server_id]['encoding'];
    }
    else
    {
        $ldapencoding='';
    }

    // define $attrlist: list of attributes to read from users' entries
    $attrparams = array('firstname_attr','lastname_attr',
	'email_attr','token_attr', 'language');

    $aTokenAttr=GetAttributeFieldNames($surveyid);
    foreach ($aTokenAttr as $thisattrfieldname)
    {
        $attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
        $attrparams[] = "attr".$attridx;
    }

    foreach ($attrparams as $id => $attr) {
        if (array_key_exists($attr,$ldap_queries[$ldapq]) &&
        $ldap_queries[$ldapq][$attr] != '') {
            $attrlist[]=$ldap_queries[$ldapq][$attr];
        }
    }

    // Open connection to server
    $ds = ldap_getCnx($ldap_server_id);

    if ($ds) {
        // bind to server
        $resbind=ldap_bindCnx($ds, $ldap_server_id);

        if ($resbind) {
            $ResArray=array();
            $resultnum=ldap_doTokenSearch($ds, $ldapq, $ResArray);
            $xz = 0; // imported token count
            $xv = 0; // meet minim requirement count
            $xy = 0; // check for duplicates
            $duplicatecount = 0; // duplicate tokens skipped count
            $invalidemailcount = 0;

            if ($resultnum >= 1) {
                foreach ($ResArray as $responseGroupId => $responseGroup) {
                    for($j = 0;$j < $responseGroup['count']; $j++) {
                        // first let's initialize everything to ''
                        $myfirstname='';
                        $mylastname='';
                        $myemail='';
                        $mylanguage='';
                        $mytoken='';
                        $myattrArray=array();

                        // The first 3 attrs MUST exist in the ldap answer
                        // ==> send PHP notice msg to apache logs otherwise
                        $meetminirequirements=true;
                        if (isset($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]) &&
                        isset($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']])
                        )
                        {
                            // minimum requirement for ldap
                            // * at least a firstanme
                            // * at least a lastname
                            // * if filterblankemail is set (default): at least an email address
                            $myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
                            $mylastname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
                            if (isset($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]))
                            {
                                $myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
                                $myemail= sanitize_email($myemail);
                                ++$xv;
                            }
                            elseif ($filterblankemail !==true)
                            {
                                $myemail = '';
                                ++$xv;
                            }
                            else
                            {
                                $meetminirequirements=false;
                            }
                        }
                        else
                        {
                            $meetminirequirements=false;
                        }

                        // The following attrs are optionnal
                        if ( isset($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]) ) $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);

                        foreach ($aTokenAttr as $thisattrfieldname)
                        {
                            $attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
                            if ( isset($ldap_queries[$ldapq]['attr'.$attridx]) &&
                            isset($responseGroup[$j][$ldap_queries[$ldapq]['attr'.$attridx]]) ) $myattrArray[$attridx] = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr'.$attridx]]);
                        }

                        if ( isset($responseGroup[$j][$ldap_queries[$ldapq]['language']]) ) $mylanguage = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['language']]);

                        // In case Ldap Server encoding isn't UTF-8, let's translate
                        // the strings to UTF-8
                        if ($ldapencoding != '')
                        {
                            $myfirstname = @mb_convert_encoding($myfirstname,"UTF-8",$ldapencoding);
                            $mylastname = @mb_convert_encoding($mylastname,"UTF-8",$ldapencoding);
                            foreach ($aTokenAttr as $thisattrfieldname)
                            {
                                $attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
                                @mb_convert_encoding($myattrArray[$attridx],"UTF-8",$ldapencoding);
                            }

                        }

                        // Now check for duplicates or bad formatted email addresses
                        $dupfound=false;
                        $invalidemail=false;
                        if ($filterduplicatetoken)
                        {
                            $dupquery = "SELECT firstname, lastname from ".db_table_name("tokens_$surveyid")." where email=".db_quoteall($myemail)." and firstname=".db_quoteall($myfirstname)." and lastname=".db_quoteall($mylastname);
                            $dupresult = $connect->Execute($dupquery);
                            if ( $dupresult->RecordCount() > 0)
                            {
                                $dupfound = true;
                                $duplicatelist[]=$myfirstname." ".$mylastname." (".$myemail.")";
                                $xy++;

                            }
                        }
                        if ($filterblankemail && $myemail=='')
                        {
                            $invalidemail=true;
                            $invalidemaillist[]=$myfirstname." ".$mylastname." ( )";
                        }
                        elseif ($myemail!='' && !validate_email($myemail))
                        {
                            $invalidemail=true;
                            $invalidemaillist[]=$myfirstname." ".$mylastname." (".$myemail.")";
                        }

                        if ($invalidemail)
                        {
                            ++$invalidemailcount;
                        }
                        elseif ($dupfound)
                        {
                            ++$duplicatecount;
                        }
                        elseif ($meetminirequirements===true)
                        {
                            // No issue, let's import
                            $iq = "INSERT INTO ".db_table_name("tokens_$surveyid")." \n"
                            . "(firstname, lastname, email, emailstatus, token, language";

                            foreach ($aTokenAttr as $thisattrfieldname)
                            {
                                $attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
                                if (!empty($myattrArray[$attridx])) {$iq .= ", $thisattrfieldname";}
                            }
                            $iq .=") \n"
                            . "VALUES (".db_quoteall($myfirstname).", ".db_quoteall($mylastname).", ".db_quoteall($myemail).", 'OK', ".db_quoteall($mytoken).", ".db_quoteall($mylanguage)."";

                            foreach ($aTokenAttr as $thisattrfieldname)
                            {
                                $attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
                                if (!empty($myattrArray[$attridx])) {$iq .= ", ".db_quoteall($myattrArray[$attridx]).""; }// dbquote_all encloses str with quotes
                            }
                            $iq .= ")";
                            $ir = $connect->Execute($iq);
                            if (!$ir) $duplicatecount++;
                            $xz++;
                            // or safe_die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
                        }
                    } // End for each entry
                } // End foreach responseGroup
            } // End of if resnum >= 1

            if ($xz != 0)
            {
                $tokenoutput .= "<span class='successtitle'>".$clang->gT("Success")."</span><br /><br />\n";
            }
            else
            {
                $tokenoutput .= "<font color='red'>".$clang->gT("Failed")."</font><br /><br />\n";
            }
            $message = "$resultnum ".$clang->gT("Results from LDAP Query").".<br />\n";
            $message .= "$xv ".$clang->gT("Records met minumum requirements").".<br />\n";
            $message .= "$xz ".$clang->gT("Records imported").".<br />\n";
            $message .= "$xy ".$clang->gT("Duplicate records removed");
            $message .= " [<a href='#' onclick='$(\"#duplicateslist\").toggle();'>".$clang->gT("List")."</a>]";
            $message .= "<div class='badtokenlist' id='duplicateslist' style='display: none;'>";
            foreach($duplicatelist as $data) {
                $message .= "<li>$data</li>\n";
            }
            $message .= "</div>";
            $message .= "<br />\n";
            $message .= sprintf($clang->gT("%s records with invalid email address removed"),$invalidemailcount);
            $message .= " [<a href='#' onclick='$(\"#invalidemaillist\").toggle();'>".$clang->gT("List")."</a>]";
            $message .= "<div class='badtokenlist' id='invalidemaillist' style='display: none;'>";
            foreach($invalidemaillist as $data) {
                $message .= "<li>$data</li>\n";
            }
            $message .= "</div>";
            $message .= "<br />\n";
            $tokenoutput .= "<i>$message</i><br />\n";
        }
        else {
            $errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Can't bind to the LDAP directory")."</strong>\n";
            formldap($errormessage);
        }
        @ldap_close($ds);
    }
    else {
        $errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Can't connect to the LDAP directory")."</strong>\n";
        formldap($errormessage);
    }
}

function form_csv_upload($error=false)
{
    global $surveyid, $tokenoutput,$scriptname, $clang, $encodingsarray;

    if ($error) {$tokenoutput .= $error . "<br /><br />\n";}
    asort($encodingsarray);
    $charsetsout='';
    foreach  ($encodingsarray as $charset=>$title)
    {
        $charsetsout.="<option value='$charset' ";
        if ($charset=='auto') {$charsetsout.=" selected ='selected'";}
        $charsetsout.=">$title ($charset)</option>";
    }
    $separator = returnglobal('separator');
    if (empty($separator) || $separator == 'auto') $selected = " selected = 'selected'"; else $selected = ''; 
    $separatorout = "<option value='auto'$selected>".$clang->gT("Auto detect")."</option>";
    if ($separator == 'comma') $selected = " selected = 'selected'"; else $selected = ''; 
    $separatorout .= "<option value='comma'$selected>".$clang->gT("Comma")."</option>";
    if ($separator == 'semicolon') $selected = " selected = 'selected'"; else $selected = ''; 
    $separatorout .= "<option value='semicolon'$selected>".$clang->gT("Semicolon")."</option>";
    $tokenoutput .= "<form id='tokenimport' enctype='multipart/form-data' action='$scriptname?action=tokens' method='post'><ul>\n"
    . "<li><label for='the_file'>".$clang->gT("Choose the CSV file to upload:")."</label><input type='file' id='the_file' name='the_file' size='35' /></li>\n"
    . "<li><label for='csvcharset'>".$clang->gT("Character set of the file:")."</label><select id='csvcharset' name='csvcharset' size='1'>$charsetsout</select></li>\n"
    . "<li><label for='separator'>".$clang->gT("Separator used:")."</label><select id='separator' name='separator' size='1'>"
    . $separatorout
    . "</select></li>\n"
    . "<li><label for='filterblankemail'>".$clang->gT("Filter blank email addresses:")."</label><input type='checkbox' id='filterblankemail' name='filterblankemail' checked='checked' /></li>\n"
    . "<li><label for='filterduplicatetoken'>".$clang->gT("Filter duplicate records:")."</label><input type='checkbox' id='filterduplicatetoken' name='filterduplicatetoken' checked='checked' /></li>"
    . "<li id='lifilterduplicatefields'><label for='filterduplicatefields[]'>".$clang->gT("Duplicates are determined by:")."</label>"
    . "<select id='filterduplicatefields[]' name='filterduplicatefields[]' multiple='multiple' size='5'>"
    . "<option selected='selected'>firstname</option>"
    . "<option selected='selected'>lastname</option>"
    . "<option selected='selected'>email</option>"
    . "<option>token</option>"
    . "<option>language</option>";
    $aTokenAttr=GetAttributeFieldNames($surveyid);
    foreach ($aTokenAttr as $thisattrfieldname)
    {
        $tokenoutput.="<option>$thisattrfieldname</option>";
    }

    $tokenoutput .= "</select> "
    . "</li></ul>\n"
    . "<p><input class='submit' type='submit' value='".$clang->gT("Upload")."' />\n"
    . "<input type='hidden' name='subaction' value='upload' />\n"
    . "<input type='hidden' name='sid' value='$surveyid' />\n"
    . "</p></form>\n\n";
} # END form

function formldap($error=false)
{
    global $surveyid, $tokenoutput, $ldap_queries, $clang, $scriptname;

    if ($error) {$tokenoutput .= $error . "<br /><br />\n";}

    if (!function_exists('ldap_connect'))
    {
        $tokenoutput .= '<p>';
        $tokenoutput .= $clang->gT('Sorry, but the LDAP module is missing in your PHP configuration.');
        $tokenoutput .= '<br />';
    }

    elseif (! isset($ldap_queries) || ! is_array($ldap_queries) || count($ldap_queries) == 0) {
        $tokenoutput .= '<br />';
        $tokenoutput .= $clang->gT('LDAP is disabled or no LDAP query defined.');
        $tokenoutput .= '<br /><br /><br />';
    }
    else {
        $tokenoutput .= "<form method='post' action='{$scriptname}?action=tokens' method='post'>";
        $tokenoutput .= '<p>';
        $tokenoutput .= $clang->gT("Select the LDAP query you want to run:")."<br />";
        $tokenoutput .= "<select name='ldapQueries' style='length=35'><br />";
        foreach ($ldap_queries as $q_number => $q) {
            $tokenoutput .= " <option value=".$q_number.">".$q['name']."</option>";
        }
        $tokenoutput .= "</select><br />";
        $tokenoutput .= '</p>';
        $tokenoutput .= "<p><label for='filterblankemail'>".$clang->gT("Filter blank email addresses:")."</label><input type='checkbox' name='filterblankemail' checked='checked' /></p>\n"
        . "<p><label for='filterduplicatetoken'>".$clang->gT("Filter duplicate records:")."</label><input type='checkbox' name='filterduplicatetoken' checked='checked' /></p>\n";
        $tokenoutput .= "<input type='hidden' name='sid' value='$surveyid' />";
        $tokenoutput .= "<input type='hidden' name='subaction' value='uploadldap' />";
        $tokenoutput .= "<p><input type='submit' name='submit' /></p>";
        $tokenoutput .= '</form></font>';
    }
}

function getLine($file)
{
    $buffer="";
    // iterate over each character in line.
    while (!feof($file))
    {
        // append the character to the buffer.
        $character = fgetc($file);
        $buffer .= $character;
        // check for end of line.
        if (($character == "\n") or ($character == "\r"))
        {
            // checks if the next character is part of the line ending, as in
            // the case of windows '\r\n' files, or not as in the case of
            // mac classic '\r', and unix/os x '\n' files.
            $character = fgetc($file);
            if ($character == "\n")
            {
                // part of line ending, append to buffer.
                $buffer .= $character;
            }
            else
            {
                // not part of line ending, roll back file pointer.
                fseek($file, -1, SEEK_CUR);
            }
            // end of line, so stop reading.
            break;
        }
    }
    // return the line buffer.
    return $buffer;
}

?>