<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
    #                                                           #
    # This program is free software; you can redistribute       #
    # it and/or modify it under the terms of the GNU General    #
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
    #                                                           #
    # This program is distributed in the hope that it will be   #
    # useful, but WITHOUT ANY WARRANTY; without even the        #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A      #
    # PARTICULAR PURPOSE.  See the GNU General Public License   #
    # for more details.                                         #
    #                                                           #
    # You should have received a copy of the GNU General        #
    # Public License along with this program; if not, write to  #
    # the Free Software Foundation, Inc., 59 Temple Place -     #
    # Suite 330, Boston, MA  02111-1307, USA.                   #
    #############################################################   
*/
//THE TABLE STRUCTURE, TABLE BY TABLE AND FIELD BY FIELD
require_once(dirname(__FILE__).'/../config.php');

sendcacheheaders();

//TABLES THAT SHOULD EXIST
$alltables=array("{$dbprefix}surveys", 
                 "{$dbprefix}groups", 
                 "{$dbprefix}questions", 
                 "{$dbprefix}answers", 
                 "{$dbprefix}conditions", 
                 "{$dbprefix}users", 
                 "{$dbprefix}labelsets", 
                 "{$dbprefix}labels",
                 "{$dbprefix}saved",
                 "{$dbprefix}saved_control",
                 "{$dbprefix}question_attributes",
                 "{$dbprefix}assessments");

//KEYS
$keyinfo[]=array("{$dbprefix}surveys", "sid");
$keyinfo[]=array("{$dbprefix}groups", "gid");
$keyinfo[]=array("{$dbprefix}questions", "qid");
$keyinfo[]=array("{$dbprefix}conditions", "cid");
$keyinfo[]=array("{$dbprefix}labelsets", "lid");
$keyinfo[]=array("{$dbprefix}saved", "saved_id");
$keyinfo[]=array("{$dbprefix}saved_control", "scid");
$keyinfo[]=array("{$dbprefix}question_attributes", "qaid");
$keyinfo[]=array("{$dbprefix}assessments", "id");

//FIELDS THAT SHOULD EXIST
$allfields[]=array("{$dbprefix}labelsets", "lid", "lid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}labelsets", "label_name", "label_name varchar(100) NOT NULL default ''");

$allfields[]=array("{$dbprefix}labels", "lid", "lid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}labels", "code", "code varchar(5) NOT NULL default ''");
$allfields[]=array("{$dbprefix}labels", "title", "title varchar(100) NOT NULL default ''");
$allfields[]=array("{$dbprefix}labels", "sortorder", "sortorder varchar(5) NULL");

$allfields[]=array("{$dbprefix}users", "user", "user varchar(20) NOT NULL default ''");
$allfields[]=array("{$dbprefix}users", "password", "password varchar(20) NOT NULL default ''");
$allfields[]=array("{$dbprefix}users", "security", "security varchar(10) NOT NULL default ''");

$allfields[]=array("{$dbprefix}answers", "qid", "qid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}answers", "code", "code varchar(5) NOT NULL default ''");
$allfields[]=array("{$dbprefix}answers", "answer", "answer text NOT NULL");
$allfields[]=array("{$dbprefix}answers", "default_value", "`default_value` char(1) NOT NULL default 'N'");
$allfields[]=array("{$dbprefix}answers", "sortorder", "sortorder varchar(5) NULL");

$allfields[]=array("{$dbprefix}conditions", "cid", "cid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}conditions", "qid", "qid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}conditions", "cqid", "cqid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}conditions", "cfieldname", "cfieldname varchar(50) NOT NULL default ''");
$allfields[]=array("{$dbprefix}conditions", "method", "method char(2) NOT NULL default ''");
$allfields[]=array("{$dbprefix}conditions", "value", "value varchar(5) NOT NULL default ''");

$allfields[]=array("{$dbprefix}groups", "gid", "gid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}groups", "sid", "sid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}groups", "group_name", "group_name varchar(100) NOT NULL default ''");
$allfields[]=array("{$dbprefix}groups", "description", "description text");

$allfields[]=array("{$dbprefix}questions", "qid", "qid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}questions", "sid", "sid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}questions", "gid", "gid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}questions", "type", "type char(1) NOT NULL default 'T'");
$allfields[]=array("{$dbprefix}questions", "title", "title varchar(20) NOT NULL default ''");
$allfields[]=array("{$dbprefix}questions", "question", "question text NOT NULL");
$allfields[]=array("{$dbprefix}questions", "preg", "preg text");
$allfields[]=array("{$dbprefix}questions", "help", "help text");
$allfields[]=array("{$dbprefix}questions", "other", "other char(1) NOT NULL default 'N'");
$allfields[]=array("{$dbprefix}questions", "mandatory", "mandatory char(1) default NULL");
$allfields[]=array("{$dbprefix}questions", "lid", "lid int(11) NOT NULL default '0'");

$allfields[]=array("{$dbprefix}surveys", "sid", "sid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}surveys", "short_title", "short_title varchar(200) NOT NULL default ''");
$allfields[]=array("{$dbprefix}surveys", "description", "description text");
$allfields[]=array("{$dbprefix}surveys", "admin", "admin varchar(50) default NULL");
$allfields[]=array("{$dbprefix}surveys", "active", "active char(1) NOT NULL default 'N'");
$allfields[]=array("{$dbprefix}surveys", "welcome", "welcome text");
$allfields[]=array("{$dbprefix}surveys", "useexpiry", "useexpiry char(1) NOT NULL default 'N'");
$allfields[]=array("{$dbprefix}surveys", "expires", "expires date default NULL");
$allfields[]=array("{$dbprefix}surveys", "adminemail", "adminemail varchar(100) default NULL");
$allfields[]=array("{$dbprefix}surveys", "private", "private char(1) default NULL");
$allfields[]=array("{$dbprefix}surveys", "faxto", "faxto varchar(20) default NULL");
$allfields[]=array("{$dbprefix}surveys", "format", "format char(1) default NULL");
$allfields[]=array("{$dbprefix}surveys", "template", "template varchar(100) default 'default'");
$allfields[]=array("{$dbprefix}surveys", "url", "url varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "urldescrip", "urldescrip varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "language", "language varchar(50) default ''");
$allfields[]=array("{$dbprefix}surveys", "datestamp", "datestamp char(1) default 'N'");
$allfields[]=array("{$dbprefix}surveys", "ipaddr", "ipaddr char(1) default 'N'");
$allfields[]=array("{$dbprefix}surveys", "usecookie", "usecookie char(1) default 'N'");
$allfields[]=array("{$dbprefix}surveys", "notification", "notification char(1) default '0'");
$allfields[]=array("{$dbprefix}surveys", "allowregister", "allowregister char(1) default 'N'");
$allfields[]=array("{$dbprefix}surveys", "attribute1", "attribute1 varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "attribute2", "attribute2 varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "email_invite_subj", "email_invite_subj varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "email_invite", "email_invite text");
$allfields[]=array("{$dbprefix}surveys", "email_remind_subj", "email_remind_subj varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "email_remind", "email_remind text");
$allfields[]=array("{$dbprefix}surveys", "email_register_subj", "email_register_subj varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "email_register", "email_register text");
$allfields[]=array("{$dbprefix}surveys", "email_confirm_subj", "email_confirm_subj varchar(255) default NULL");
$allfields[]=array("{$dbprefix}surveys", "email_confirm", "email_confirm text");
$allfields[]=array("{$dbprefix}surveys", "allowsave","allowsave char(1) default 'Y'");
$allfields[]=array("{$dbprefix}surveys", "autonumber_start", "autonumber_start bigint(11) default 0");
$allfields[]=array("{$dbprefix}surveys", "autoredirect", "autoredirect char(1) default 'N'");
$allfields[]=array("{$dbprefix}surveys", "allowprev","allowprev char(1) default 'Y'");

$allfields[]=array("{$dbprefix}saved", "saved_id", "saved_id int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}saved", "scid", "scid int(11) NOT NULL");
$allfields[]=array("{$dbprefix}saved", "datestamp", "datestamp datetime NOT NULL default '0000-00-00 00:00:00'");
$allfields[]=array("{$dbprefix}saved", "ipaddr", "ipaddr MEDIUMTEXT default NULL");
$allfields[]=array("{$dbprefix}saved", "fieldname", "fieldname text NOT NULL");
$allfields[]=array("{$dbprefix}saved", "value", "value text NOT NULL");

$allfields[]=array("{$dbprefix}saved_control", "scid", "scid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}saved_control", "sid", "sid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}saved_control", "identifier", "identifier text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "access_code", "access_code text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "email", "email varchar(200)");
$allfields[]=array("{$dbprefix}saved_control", "ip", "ip text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "saved_thisstep", "saved_thisstep text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "status", "status char(1) NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "saved_date", "saved_date datetime NOT NULL default '0000-00-00 00:00:00'");

$allfields[]=array("{$dbprefix}question_attributes", "qaid", "qaid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}question_attributes", "qid", "qid int(11) NOT NULL");
$allfields[]=array("{$dbprefix}question_attributes", "attribute", "attribute varchar(50)");
$allfields[]=array("{$dbprefix}question_attributes", "value", "value varchar(20)");

$allfields[]=array("{$dbprefix}assessments", "id", "id int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}assessments", "sid", "sid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}assessments", "scope", "scope varchar(5) NOT NULL default ''");
$allfields[]=array("{$dbprefix}assessments", "gid", "gid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}assessments", "name", "name text NOT NULL");
$allfields[]=array("{$dbprefix}assessments", "minimum", "minimum varchar(50) NOT NULL default ''");
$allfields[]=array("{$dbprefix}assessments", "maximum", "maximum varchar(50) NOT NULL default ''");
$allfields[]=array("{$dbprefix}assessments", "message", "message text NOT NULL");
$allfields[]=array("{$dbprefix}assessments", "link", "link text NOT NULL");

echo $htmlheader;

echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._CHECKFIELDS."</strong></font></td></tr>\n";
echo "\t<tr bgcolor='#CCCCCC'><td>\n";

echo "$setfont<strong>"._CF_CHECKTABLES.":</strong><br /><font size='1'>\n";

if (!isset($databasetabletype)) {$databasetabletype="MyISAM";}

$result = mysql_list_tables($databasename);
while ($row = mysql_fetch_row($result))
    {
    $tablelist[]=$row[0];
    }
if (!isset($tablelist) || !is_array($tablelist))
    {
    $tablelist[]="empty";
    }
foreach ($alltables as $at)
    {
    echo "<strong>-></strong>"._CF_CHECKING." <strong>$at</strong>..<br />";
    if (!in_array($at, $tablelist))
        {
        //Create table
        $ctquery="CREATE TABLE `$at` (\n";
        foreach ($allfields as $af)
            {
            if ($af[0] == $at)
                {
                $ctquery .= $af[2].",\n";
                }
            }
        foreach($keyinfo as $ki)
            {
            if ($ki[0] == $at)
                {
                $ctquery .= "PRIMARY KEY ({$ki[1]}),\n";
                }
            }
        $ctquery = substr($ctquery, 0, -2);
        $ctquery .= ")\n";
        $ctquery .= "TYPE=$databasetabletype\n";
        $ctresult=mysql_query($ctquery) or die ("Couldn't create $at table<br />$ctquery<br />".mysql_error());
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>"._CF_TABLECREATED."! ($at)</font><br />\n";
        }
    else
        {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='green'>"._CF_OK."</font><br />\n";
        }
    //echo "<br />\n";
    }
echo "<br /></font>\n";


echo "$setfont<strong>"._CF_CHECKFIELDS.":</strong><br /><font size='1'>\n";

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
        if (isset($dbprefix) && $dbprefix) {
            if(substr($tn, 0, strlen($dbprefix)) == $dbprefix) {
                checktable($tn);
            }
        } else {
            checktable($tn);
        }
        }
    }

function checktable($tablename)
    {
    global $databasename, $allfields;
    echo "<strong>-></strong>"._CF_CHECKING." <strong>$tablename</strong>..<br />";
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
                elseif ($af[1] == "default_value" && $fn == "default")
                    {
                    $thisfieldexists=1;
                    $query = "ALTER TABLE `$tablename` CHANGE `$fn` {$af[2]}";
                    $result = mysql_query($query) or die("Couldn't change name of default field to default_value.<br />$query<br />".mysql_error());
                    echo "&nbsp;&nbsp;&nbsp;<font color='red'>Changed field name</font> ($af[1]) <br />\n";
                    }
                }
            if ($thisfieldexists==0)
                {
                $query="ALTER TABLE `$tablename` ADD $af[2]";
                $result=mysql_query($query) or die("Insert field failed.<br />$query<br />".mysql_error());
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>"._CF_FIELDCREATED."</font> ($af[1]) <br />\n";
                $addedfield="Y";
                }
            else
                {
                $addedfield = "N";
                }
            }
        }
    if (isset($addedfield) && $addedfield != "Y")
        {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='green'>"._CF_OK."</font><br />\n";
        }
    }

if (isset($checkfororphans) && $checkfororphans)
    {
    $query  = "SELECT {$dbprefix}questions.qid as nullqid, {$dbprefix}answers.* "
            . "FROM {$dbprefix}answers "
            . "LEFT JOIN {$dbprefix}questions "
            . "ON {$dbprefix}answers.qid={$dbprefix}questions.qid "
            . "WHERE {$dbprefix}questions.qid IS NULL";
    $result = mysql_query($query) or die("Orphan check failed.<br />$query<br />".mysql_error());
    if ($result)
        {
        echo "<br /><strong>Orphan Database Entries</strong><br />\n";
        while ($row = mysql_fetch_array($result))
            {
            echo "$setfont ANSWER: ".$row['qid']." - ".$row['code']."<br />\n";
            }
        }
    }

echo "</font></font></font></td></tr>\n";
echo "<tr><td align='center' bgcolor='#CCCCCC'>\n";
echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";

echo "</td></tr></table>\n";
echo "<br />\n";
echo getAdminFooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");

?>