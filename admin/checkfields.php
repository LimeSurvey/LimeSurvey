<?php
//THE TABLE STRUCTURE, TABLE BY TABLE AND FIELD BY FIELD
include("config.php");

//TABLES THAT SHOULD EXIST
$alltables=array("surveys", "groups", "questions", "answers", "conditions", "users");


//FIELDS THAT SHOULD EXIST
$allfields[]=array("answers", "qid", "qid int(11) NOT NULL default '0'");
$allfields[]=array("answers", "code", "code varchar(5) NOT NULL default ''");
$allfields[]=array("answers", "answer", "answer text NOT NULL");
$allfields[]=array("answers", "default", "`default` char(1) NOT NULL default 'N'");
$allfields[]=array("answers", "sortorder", "sortorder varchar(5) NULL");

$allfields[]=array("conditions", "cid", "cid int(11) NOT NULL auto_increment");
$allfields[]=array("conditions", "qid", "qid int(11) NOT NULL default '0'");
$allfields[]=array("conditions", "cfieldname", "cfieldname varchar(50) NOT NULL default ''");
$allfields[]=array("conditions", "method", "method char(2) NOT NULL default ''");
$allfields[]=array("conditions", "value", "value varchar(5) NOT NULL default ''");

$allfields[]=array("groups", "gid", "gid int(11) NOT NULL auto_increment");
$allfields[]=array("groups", "sid", "sid int(11) NOT NULL default '0'");
$allfields[]=array("groups", "group_name", "group_name varchar(100) NOT NULL default ''");
$allfields[]=array("groups", "description", "description text");

$allfields[]=array("questions", "qid", "qid int(11) NOT NULL auto_increment");
$allfields[]=array("questions", "sid", "sid int(11) NOT NULL default '0'");
$allfields[]=array("questions", "gid", "gid int(11) NOT NULL default '0'");
$allfields[]=array("questions", "type", "type char(1) NOT NULL default 'T'");
$allfields[]=array("questions", "title", "title varchar(20) NOT NULL default ''");
$allfields[]=array("questions", "question", "question text NOT NULL");
$allfields[]=array("questions", "help", "help text");
$allfields[]=array("questions", "other", "other char(1) NOT NULL default 'N'");
$allfields[]=array("questions", "mandatory", "mandatory char(1) default NULL");

$allfields[]=array("surveys", "sid", "sid int(11) NOT NULL auto_increment");
$allfields[]=array("surveys", "short_title", "short_title varchar(50) NOT NULL default ''");
$allfields[]=array("surveys", "description", "description text");
$allfields[]=array("surveys", "admin", "admin varchar(20) default NULL");
$allfields[]=array("surveys", "active", "active char(1) NOT NULL default 'N'");
$allfields[]=array("surveys", "welcome", "welcome text");
$allfields[]=array("surveys", "expires", "expires date default NULL");
$allfields[]=array("surveys", "adminemail", "adminemail varchar(100) default NULL");
$allfields[]=array("surveys", "private", "private char(1) default NULL");
$allfields[]=array("surveys", "faxto", "faxto varchar(20) default NULL");
$allfields[]=array("surveys", "format", "format char(1) default NULL");
$allfields[]=array("surveys", "template", "template varchar(100) default 'default'");
$allfields[]=array("surveys", "url", "url varchar(255) default NULL");
$allfields[]=array("surveys", "urldescrip", "urldescrip varchar(255) default NULL");
$allfields[]=array("surveys", "language", "language varchar(50) default ''");
$allfields[]=array("surveys", "datestamp", "datestamp char(1) default 'N'");

echo $htmlheader;

echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._CHECKFIELDS."</b> <font color='silver'>{$s1row['short_title']}</td></tr>\n";
echo "\t<tr height='22' bgcolor='#CCCCCC'><td>\n";

echo "$setfont<b><u>"._CF_CHECKTABLES.":</u></b><br /><font size='1'>\n";

$result = mysql_list_tables($databasename);
while ($row = mysql_fetch_row($result))
	{
	$tablelist[]=$row[0];
    }
foreach ($alltables as $at)
	{
	echo "<b>-></b>"._CF_CHECKING." <b>$at</b>..<br />";
	if (!in_array($at, $tablelist))
		{
		//Create table
		$ctquery="CREATE TABLE `$at` (\n";
		foreach ($allfields as $af)
			{
			if ($af[0] == $at)
				{
				$ctquery .= $af[2]."\n";
				}
			}
		$ctquery .= ")\n";
		$ctquery .= "TYPE=MyISAM";
		$ctresult=mysql_query($ctquery) or die ("Couldn't create $at table<br />$ctquery<br />".mysql_error);
		echo _CF_TABLECREATED."! ($at)";
		}
	else
		{
		echo "&nbsp;&nbsp;&nbsp;&nbsp;"._CF_OK."<br />\n";
		}
	//echo "<br />\n";
	}
echo "<br /></font>\n";


echo "$setfont<b><u>"._CF_CHECKFIELDS.":</u></b><br /><font size='1'>\n";

//GET LIST OF TABLES
$tables = mysql_list_tables($databasename);
while ($trow = mysql_fetch_row($tables))
	{
	$tablenames[] = $trow[0];
	}

foreach ($tablenames as $tn)
	{
	if (substr($tn, 0, 3) != "old" && substr($tn, 0, 7) != "survey_" && substr($tn, 0, 3) != "tok")
		{
		checktable($tn);
		}
	}

function checktable($tablename)
	{
	global $databasename, $allfields;
	echo "<b>-></b>"._CF_CHECKING." <b>$tablename</b>..<br />";
	$fields=mysql_list_fields($databasename, $tablename);
	$numfields=mysql_num_fields($fields);
	for ($i=0; $i<$numfields; $i++)
		{
		$fieldnames[]=mysql_field_name($fields, $i);
		}
	foreach ($allfields as $af)
		{
		if ($af[0] == $tablename)
			{
			$thisfieldexists=0;
			foreach($fieldnames as $fn)
				{
				if ($af[1] == $fn)
					{
					$thisfieldexists=1;
					}
				}
			if ($thisfieldexists==0)
				{
				$query="ALTER TABLE `$tablename` ADD $af[2]";
				$result=mysql_query($query) or die("Insert field failed.<br />$query<br />".mysql_error());
				echo "&nbsp;&nbsp;&nbsp;&nbsp;"._CF_FIELDCREATED." ($af[1]) <br />\n";
				$addedfield="Y";
				}
			}
		}
	if ($addedfield != "Y")
		{
		echo "&nbsp;&nbsp;&nbsp;&nbsp;"._CF_OK."<br />\n";
		}
	}
echo "</font></td></tr>\n<tr><td align='center' bgcolor='#CCCCCC'>\n";
echo "<a href='admin.php'>"._GO_ADMIN."</a>\n";

echo "</td></tr></table>\n";
echo "<br />\n";
echo htmlfooter("instructions.html", "Using PHPSurveyors Admin Script");

?>