<?PHP

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    if ($oldversion < 109) {
      modify_database("","ALTER TABLE `prefix_answers` ADD `language` varchar(20) default 'en'");
      modify_database("","ALTER TABLE `prefix_questions` ADD `language` varchar(20) default 'en'");
      modify_database("","ALTER TABLE `prefix_groups` ADD `language` varchar(20) default 'en'");
      modify_database("","ALTER TABLE `prefix_labels` ADD `language` varchar(20) default 'en'");
      modify_database("","UPDATE  `prefix_settings_global` SET stg_value='109' where stg_name ='DBVersion'");
    }

    if ($oldversion < 110) {
      modify_database("","ALTER TABLE `prefix_surveys` ADD `additional_languages` varchar(255)");
      modify_database("","DROP TABLE IF EXISTS `prefix_surveys_languagesettings`;");
      modify_database("","CREATE TABLE `prefix_surveys_languagesettings` (
                        `surveyls_survey_id` INT UNSIGNED NOT NULL DEFAULT 0,
                        `surveyls_language` VARCHAR(45) NULL DEFAULT 'en',
                        `surveyls_title` VARCHAR(200) NOT NULL,
                        `surveyls_description` TEXT NULL,
                        `surveyls_welcometext` TEXT NULL,
                        `surveyls_urldescription` VARCHAR(255) NULL,
                        `surveyls_email_invite_subj` VARCHAR(255) NULL,
                        `surveyls_email_invite` TEXT NULL,
                        `surveyls_email_remind_subj` VARCHAR(255) NULL,
                        `surveyls_email_remind` TEXT NULL,
                        `surveyls_email_register_subj` VARCHAR(255) NULL,
                        `surveyls_email_register` TEXT NULL,
                        `surveyls_email_confirm_subj` VARCHAR(255) NULL,
                        `surveyls_email_confirm` TEXT NULL,
                        PRIMARY KEY (`surveyls_survey_id`, `surveyls_language`)
                            )
                    TYPE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");  
      modify_database("","ALTER TABLE `prefix_groups` DROP PRIMARY KEY");     
      modify_database("","ALTER TABLE `prefix_groups` ADD PRIMARY KEY (`gid`,`language`)");     
      modify_database("","ALTER TABLE `prefix_questions` DROP PRIMARY KEY");     
      modify_database("","ALTER TABLE `prefix_questions` ADD PRIMARY KEY (`qid`,`language`)");     
      modify_database("","ALTER TABLE `prefix_answers` DROP PRIMARY KEY");     
      modify_database("","ALTER TABLE `prefix_answers` ADD PRIMARY KEY (`qid`,` code`,`language`)");     

      modify_database("","ALTER TABLE `prefix_labelsets` ADD `languages` varchar(200) default 'en'");
      modify_database("","ALTER TABLE `prefix_labels` MODIFY `sortorder` int(11) NOT NULL");
      modify_database("","ALTER TABLE `prefix_answers` MODIFY `sortorder` int(11) NOT NULL");
                    
      modify_database("","UPDATE  `prefix_settings_global` SET stg_value='110' where stg_name ='DBVersion'");
    }

    
    return true;
}











// This is the old MySQL Check field function
// It is only run if there is no settings table

function mysqlcheckfields()
{
global $dbprefix, $connect, $langdir, $allfields, $dbversionnumber;
//TABLES THAT SHOULD EXIST
$alltables=array("{$dbprefix}surveys",
                 "{$dbprefix}groups",
                 "{$dbprefix}questions",
                 "{$dbprefix}answers",
                 "{$dbprefix}conditions",
                 "{$dbprefix}users",
                 "{$dbprefix}labelsets",
                 "{$dbprefix}labels",
                 "{$dbprefix}settings_global",
                 "{$dbprefix}saved_control",
                 "{$dbprefix}question_attributes",
                 "{$dbprefix}assessments",
                 "{$dbprefix}surveys_rights",
                 "{$dbprefix}user_groups",
                 "{$dbprefix}user_in_groups"); //New table for rights);

//KEYS
$keyinfo[]=array("{$dbprefix}surveys", "sid");
$keyinfo[]=array("{$dbprefix}groups", "gid");
$keyinfo[]=array("{$dbprefix}questions", "qid");
$keyinfo[]=array("{$dbprefix}conditions", "cid");
$keyinfo[]=array("{$dbprefix}labelsets", "lid");
$keyinfo[]=array("{$dbprefix}settings_global", "stg_name");
$keyinfo[]=array("{$dbprefix}saved_control", "scid");
$keyinfo[]=array("{$dbprefix}question_attributes", "qaid");
$keyinfo[]=array("{$dbprefix}assessments", "id");
$keyinfo[]=array("{$dbprefix}users", "uid"); //users now has Key
$keyinfo[]=array("{$dbprefix}surveys_rights", "sid, uid"); //survey_rights now has Key
$keyinfo[]=array("{$dbprefix}user_groups", "ugid");

//FIELDS THAT SHOULD EXIST
$allfields[]=array("{$dbprefix}labelsets", "lid", "lid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}labelsets", "label_name", "label_name varchar(100) NOT NULL default ''");

$allfields[]=array("{$dbprefix}labels", "lid", "lid int(11) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}labels", "code", "code varchar(5) NOT NULL default ''");
$allfields[]=array("{$dbprefix}labels", "title", "title varchar(100) NOT NULL default ''");
$allfields[]=array("{$dbprefix}labels", "sortorder", "sortorder varchar(5) NULL");

$allfields[]=array("{$dbprefix}users", "uid", "uid int(10) unsigned NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}users", "user", "user varchar(20) NOT NULL default '' UNIQUE");
$allfields[]=array("{$dbprefix}users", "password", "password BLOB NOT NULL");
$allfields[]=array("{$dbprefix}users", "full_name", "full_name VARCHAR(50) NOT NULL");
$allfields[]=array("{$dbprefix}users", "parent_id", "parent_id int(10) unsigned NOT NULL");
$allfields[]=array("{$dbprefix}users", "lang", "lang varchar(20)");
$allfields[]=array("{$dbprefix}users", "email", "email varchar(50) NOT NULL UNIQUE");
$allfields[]=array("{$dbprefix}users", "create_survey","create_survey tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}users", "create_user","create_user tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}users", "delete_user","delete_user tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}users", "move_user","move_user tinyint(1) NOT NULL default '0'");//GIVE NEW PARENT USER
$allfields[]=array("{$dbprefix}users", "configurator","configurator tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}users", "manage_template","manage_template tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}users", "manage_label","manage_label tinyint(1) NOT NULL default '0'");

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
$allfields[]=array("{$dbprefix}groups", "group_code", "group_code varchar(50) NULL");
$allfields[]=array("{$dbprefix}groups", "group_order", "group_order int(11) NOT NULL default '0'");
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
$allfields[]=array("{$dbprefix}questions", "question_order", "question_order int(11) NOT NULL default '0'");

$allfields[]=array("{$dbprefix}surveys", "sid", "sid int(11) NOT NULL");
$allfields[]=array("{$dbprefix}surveys", "creator_id", "uid int(11) NOT NULL");
$allfields[]=array("{$dbprefix}surveys", "surveyls_title", "surveyls_title varchar(200) NOT NULL default ''");
$allfields[]=array("{$dbprefix}surveys", "description", "description text");
$allfields[]=array("{$dbprefix}surveys", "datecreated", "datecreated date");
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
$allfields[]=array("{$dbprefix}surveys", "refurl", "refurl char(1) default 'N'");
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



$allfields[]=array("{$dbprefix}saved_control", "scid", "scid int(11) NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}saved_control", "sid", "sid int(11) NOT NULL default '0'");
// --> START NEW FEATURE - SAVE
$allfields[]=array("{$dbprefix}saved_control", "srid", "srid int(11) NOT NULL default '0'");
// --> END NEW FEATURE - SAVE
$allfields[]=array("{$dbprefix}saved_control", "identifier", "identifier text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "access_code", "access_code text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "email", "email varchar(200)");
$allfields[]=array("{$dbprefix}saved_control", "ip", "ip text NOT NULL");
$allfields[]=array("{$dbprefix}saved_control", "refurl", "refurl text");
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

$allfields[]=array("{$dbprefix}settings_global", "stg_name", "stg_name varchar(50) NOT NULL default ''");
$allfields[]=array("{$dbprefix}settings_global", "stg_value", "stg_value varchar(255) NOT NULL default ''");
//Check new tables for rights
$allfields[]=array("{$dbprefix}surveys_rights", "sid", "sid int(10) unsigned NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "uid", "uid int(10) unsigned NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "edit_survey_property", "CHANGE_SURVEY_PROPERTY tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "define_questions", "DEFINE_QUESTIONS tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "browse_response", "LOOK_STATISTICS tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "export", "EXPORT tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "delete_survey", "DELETE_SURVEY tinyint(1) NOT NULL default '0'");
$allfields[]=array("{$dbprefix}surveys_rights", "activate_survey", "DO_SURVEY tinyint(1) NOT NULL default '0'");


$allfields[]=array("{$dbprefix}user_groups", "ugid", "ugid int(10) unsigned NOT NULL auto_increment");
$allfields[]=array("{$dbprefix}user_groups", "name", "name varchar(20) NOT NULL UNIQUE");
$allfields[]=array("{$dbprefix}user_groups", "description", "description TEXT NOT NULL default ''");
$allfields[]=array("{$dbprefix}user_groups", "creator_id", "creator_id int(10) unsigned NOT NULL");

$allfields[]=array("{$dbprefix}user_in_groups", "gid","gid int(10) unsigned NOT NULL");
$allfields[]=array("{$dbprefix}user_in_groups", "uid","uid int(10) unsigned NOT NULL");

echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._("Check Database Fields")."</strong></font></td></tr>\n";
echo "\t<tr bgcolor='#CCCCCC'><td>\n";

echo "<strong>"._("Checking to ensure all tables exist").":</strong><br /><font size='1'>\n";

if (!isset($databasetabletype)) {$databasetabletype="MyISAM";}

$tablelist = $connect->MetaTables();
if (!isset($tablelist) || !is_array($tablelist))
    {
    $tablelist[]="empty";
    }
foreach ($alltables as $at)
    {
    echo "<strong>-></strong>"._("Checking")." <strong>$at</strong>..<br />";
    if (!in_array($at, $tablelist))
        {
        //Create table
        $ctquery="CREATE TABLE ".db_quote_id($at)." (\n";
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
        $ctresult=$connect->Execute($ctquery) or die ("Couldn't create $at table<br />$ctquery<br />".htmlspecialchars($connect->ErrorMsg()));
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>"._("Table Created")."! ($at)</font><br />\n";
        }
    else
        {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='green'>"._("OK")."</font><br />\n";
        }
    //echo "<br />\n";
    }
echo "<br /></font>\n";


echo "<strong>"._("Checking to ensure all fields exist").":</strong><br /><font size='1'>\n";

//GET LIST OF TABLES
$tablenames = $connect->MetaTables();

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
    
    // INIT TABLES
echo "$setfont<strong>"._("Checking to ensure all tables initialized:")."</strong><br /><font size='1'>\n";

//INIT USER TABLE
$query = "SELECT uid, create_survey, create_user, delete_user, move_user, configurator, manage_template, manage_label FROM {$dbprefix}users";
$result = $connect->Execute($query) or die("Initialization check failed.<br />$query<br />".($connect->ErrorMsg()));
echo "<strong>-></strong>"._("Checking")." <strong>{$dbprefix}users</strong>..<br />";
if($row = $result->FetchRow()) {

	if(($row[1] + $row[2] + $row[3] + $row[4] + $row[5] + $row[6] + $row[7]) != 7) {
		$query = "UPDATE {$dbprefix}users SET manage_label = 1, manage_template = 1, create_survey = 1, create_user = 1, delete_user = 1, move_user = 1, configurator = 1 WHERE uid = ".$row[0];
		mysql_query($query) or die("Fixing of {$dbprefix}users with rights of $defaultuser failed.<br />$query<br />".($connect->ErrorMsg()));
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>"._("Table_fixed")."</font><br />\n";
	}
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='green'>"._("OK")."</font><br />\n";
} else {
	$query = "INSERT INTO {$dbprefix}users VALUES(NULL, '$defaultuser', ENCODE('$defaultpass', '$codeString'), 0, '$defaultlang', '$siteadminemail',1,1,1,1,1,1,1)";
	mysql_query($query) or die("Initialization of {$dbprefix}users with admin user failed.<br />$query<br />".($connect->ErrorMsg()));
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>"._("User Table Initialized")."</font><br />\n";
}

convertquestionorder();
convertgrouporder();
modify_database('','insert '.db_table_name("settings_global").' values("DBVersion","108")');

echo "</font></font></font></td></tr>\n";
echo "<tr><td align='center' bgcolor='#CCCCCC'>\n";
echo "<input type='submit' value='"._("Main Admin Screen")."' onClick=\"window.open('admin.php', '_top')\">\n";

echo "</td></tr></table>\n";
echo "<br />\n";
echo getAdminFooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");
}


function convertgrouporder() //Function rewrites the grouporder for complete db
{
	global $dbprefix, $connect;
	$tabsurvey = db_execute_assoc("SELECT sid from ".db_table_name("surveys"));
	while ($surveyrow=$tabsurvey->FetchRow())
	{
      $tabgroups = db_execute_assoc("SELECT * from ".db_table_name("groups")." where sid=".$surveyrow['sid']." order by group_name");
      $icount=0;
      while ($grouprow=$tabgroups->FetchRow())
      {
        $cd2query="UPDATE ".db_table_name('groups')." SET group_order=? WHERE gid=?";
		$cd2result=$connect->Execute($cd2query, Array($icount, $grouprow['gid'])) or die ("Couldn't update group_order<br />$cd2query<br />".htmlspecialchars($connect->ErrorMsg()));
        $icount++;
      }
	}
}


function convertquestionorder() //Function rewrites the question_order for complete db
{
	global $dbprefix, $connect;
	$tabsurvey = db_execute_assoc("SELECT gid from ".db_table_name("groups"));
	while ($surveyrow=$tabsurvey->FetchRow())
	{
      $tabgroups = db_execute_assoc("SELECT qid from ".db_table_name("questions")." where gid=".$surveyrow['gid']." order by title");
      $icount=0;
      while ($grouprow=$tabgroups->FetchRow())
      {
        $cd2query="UPDATE ".db_table_name('questions')." SET question_order=? WHERE qid=?";
		$cd2result=$connect->Execute($cd2query, Array($icount, $grouprow['qid'])) or die ("Couldn't update group_order<br />$cd2query<br />".htmlspecialchars($connect->ErrorMsg()));
        $icount++;
      }
	}
}

function checktable($tablename)
    {
    global $databasename, $allfields, $connect;
    echo "<strong>-></strong>"._("Checking")." <strong>$tablename</strong>..<br />";
    $fieldnames = array_values($connect->MetaColumnNames(db_quote_id($tablename), true));
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
                    $result = $connect->Execute($query) or die("Couldn't change name of default field to default_value.<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
                    echo "&nbsp;&nbsp;&nbsp;<font color='red'>Changed field name</font> ($af[1]) <br />\n";
                    }
                }
            if ($thisfieldexists==0)
                {
                $query="ALTER TABLE `$tablename` ADD $af[2]";
                $result=$connect->Execute($query) or die("Insert field failed.<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
                echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='red'>"._("Field Created")."</font> ($af[1]) <br />\n";
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
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color='green'>"._("OK")."</font><br />\n";
        }
    }







?>
