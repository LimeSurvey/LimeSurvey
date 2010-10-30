<?php

// file heading here

// check if script was run directly
include_once("login_check.php");
//include_once("database.php");

//BEGIN Sanitizing POSTed data
if (!isset($subaction)) {$subaction=returnglobal('subaction');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($limit)) {$limit=(int)returnglobal('limit');}
if ($limit==0) $limit=50;
if (!isset($start)) {$start=(int)returnglobal('start');}
if (!isset($searchstring)) {$searchstring=returnglobal('searchstring');}
if (!isset($question)) {$question=returnglobal('question');}

// initialize output
$extendedconditionsoutput = "";

// reloading after deletion
if ($subaction == 'delete') {
    $_SESSION['metaHeader']="<meta http-equiv=\"refresh\" content=\"1;URL={$scriptname}?action=extendedconditions&amp;subaction=browse&amp;sid='$surveyid'&amp;gid='$gid'&amp;qid='$qid'&amp;start=$start&amp;limit=$limit&amp;order=$order\" />";
}

// MAKE SURE THAT THERE IS A SID
if (!isset($surveyid) || !$surveyid)
{
    $extendedconditionsoutput .= "\t<div class='messagebox'><div class='header'>"
    .$clang->gT("Extended conditions editor")."</div>\n"
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
    $extendedconditionsoutput .= "\t<div class='messagebox'>\n<div class='header'>\n"
    .$clang->gT("Extended conditions editor")."</div>\n"
    ."\t<br /><div class='warningheader'>".$clang->gT("Error")."</div>"
    ."<br />".$clang->gT("The survey you selected does not exist")
    ."<br /><br />\n\t<input type='submit' value='"
    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />"
    ."</div>\n";
    return;
}
else // A survey DOES exist
{
    $extendedconditionsoutput .= "\t<div class='menubar'>"
    ."<div class='menubar-title'>"
    ."<strong>".$clang->gT("Extended conditions editor")." </strong> (".htmlspecialchars($thissurvey['surveyls_title']).")</div>\n";
}

// CHECK TO SEE IF THE EXTENDED CONDITION TABLE EXISTS
if (!tableExists('extendedconditions'))
{
    $extendedconditionsoutput .= "\t<div class='messagebox'><div class='header'>"
    .$clang->gT("Extended conditions editor")."</div>\n"
    ."\t<br /><div class='warningheader'>".$clang->gT("Error")."</div>"
    ."<br />".$clang->gT("There is no extended condition table")."<br /><br />"
    ."<input type='submit' value='"
    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\" /><br />\n"
    ."</div>\n";
    return;
}
// TODO create table if doesn't exist

// MENU BAR
$extendedconditionsoutput .= "\t<div class='menubar-main'>\n"
. "<div class='menubar-left'>\n"
. "<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\" "
. "title='".$clang->gTview("Return to survey administration")."'>"
. "<img name='HomeButton' src='$imageurl/home.png' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
. "<img src='$imageurl/blank.gif' alt='' width='11' />\n"
. "<img src='$imageurl/seperator.gif' alt='' />\n"
. "<img src='$imageurl/blank.gif' alt='' width='11' />\n"
. "<a href=\"#\" onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browse', '_top')\" "
. "title='".$clang->gTview("Display conditions")."' >"
. "<img name='BrowseButton' src='$imageurl/conditions_copy.png' alt='".$clang->gT("Display extended conditions")."' /></a>\n"
. "<a href=\"#\" onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=addnew', '_top')\" "
. "title='".$clang->gTview("Add new condition")."' >"
. "<img name='AddNewButton' src='$imageurl/conditions_add.png' alt='".$clang->gT("Add new condition")."' /></a>\n"
. "\t</div></div></div>\n";

// CONTENTS
if ($subaction == '' || $subaction == 'browse' || $subaction == 'search')
{
	// count extended conditions
	$ecquery = "SELECT count(*) FROM {$dbprefix}extendedconditions";
	$ecresult = db_execute_num($ecquery);
	$ecrow = $ecresult->FetchRow();
	$eccount = $ecrow[0];

    if (!isset($limit)) {$limit = 100;}
    if (!isset($start)) {$start = 0;}

    if ($limit > $eccount) {$limit=$eccount;}
    $next=$start+$limit;
    $last=$start-$limit;
    $end=$eccount-$limit;
    if ($end < 0) {$end=0;}
    if ($last <0) {$last=0;}
    if ($next >= $eccount) {$next=$eccount-$limit;}
    if ($end < 0) {$end=0;}

    //ALLOW SELECTION OF NUMBER OF RECORDS SHOWN
    $extendedconditionsoutput .= "\t<div class='menubar'><div class='menubar-title'><span style='font-weight:bold;'>"
    .$clang->gT("Data view control")."</span></div>\n"
    ."<div class='menubar-main'>\n"
    ."<div class='menubar-left'>\n"
    ."<a href='$scriptname?action=extendedconditions&amp;subaction=browse&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;start=0&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'"
    ." title='".$clang->gTview("Show start...")."'>"
    ."<img name='DBeginButton' align='left' src='$imageurl/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
    ."<a href='$scriptname?action=extendedconditions&amp;subaction=browsegroup&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;start=$last&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	" title='".$clang->gTview("Show previous...")."'>" .
	"<img name='DBackButton' align='left' src='$imageurl/databack.png' alt='".$clang->gT("Show previous...")."' /></a>\n"
	."<img src='$imageurl/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left' />\n"
	."<a href='$scriptname?action=extendedconditions&amp;subaction=browsegroup&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;start=$next&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	"title='".$clang->gTview("Show next...")."'>" .
	"<img name='DForwardButton' align='left' src='$imageurl/dataforward.png' alt='".$clang->gT("Show next...")."' /></a>\n"
	."<a href='$scriptname?action=extendedconditions&amp;subaction=browsegroup&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;start=$end&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
	"title='".$clang->gTview("Show last...")."'>".
	"<img name='DEndButton' align='left'  src='$imageurl/dataend.png' alt='".$clang->gT("Show last...")."' /></a>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
	."\t<form id='tokensearch' method='post' action='$scriptname?action=extendedconditions'>\n"
	."<input type='text' name='searchstring' value='$searchstring' />\n"
	."<input type='submit' value='".$clang->gT("Search")."' />\n"
	."\t<input type='hidden' name='order' value='$order' />\n"
	."\t<input type='hidden' name='subaction' value='search' />\n"
	."\t<input type='hidden' name='sid' value='$surveyid' />\n"
	."\t<input type='hidden' name='gid' value='$gid' />\n"
	."\t<input type='hidden' name='qid' value='$qid' />\n"
	."\t<input type='hidden' name='question' value='$question' />\n"
	."\t</form>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' />\n"
	."<form id='tokenrange' action='{$scriptname}'>\n"
	."<font size='1' face='verdana'>"
	."&nbsp;<label for='limit'>".$clang->gT("Records displayed:")."</label> <input type='text' size='4' value='$limit' id='limit' name='limit' />"
	."&nbsp;&nbsp;<label for='start'>".$clang->gT("Starting from:")."</label> <input type='text' size='4' value='$start'  id='start' name='start' />"
	."&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' />\n"
	."&nbsp;<label for='question'>".$clang->gT("Question:")."</label>";
	$selectall = "selected='selected'";
	if (!$question) {
		$selectall = "";
	}
	$extendedconditionsoutput .= "&nbsp;&nbsp;\n"
	."<select name='question' onchange='javascript:document.getElementById(\"limit\").value=\"\";submit();'>\n"
	."\t<option value='' $selectall>".$clang->gT("All questions")."</option>\n";
	
	$ecquery = "SELECT * FROM {$dbprefix}questions";
	$ecresult = db_execute_assoc($ecquery) or safe_die("Couldn't get questions:<br />$ecquery<br />".$connect->ErrorMsg());
	while ($ecrow = $ecresult->FetchRow())
	{
		$extendedconditionsoutput .= "\t<option value='{$ecrow['qid']}'";
		if ($question == $ecrow['qid'])
			$extendedconditionsoutput .= "selected='selected'";
		$extendedconditionsoutput .= ">{$ecrow['question']}</option>\n";
	}
	$extendedconditionsoutput .= "</select>\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='gid' value='$gid' />\n"
	."<input type='hidden' name='qid' value='$qid' />\n"
	."<input type='hidden' name='action' value='extendedconditions' />\n"
	."<input type='hidden' name='subaction' value='browse' />\n"
	."<input type='hidden' name='order' value='$order' />\n"
	."<input type='hidden' name='searchstring' value='$searchstring' />\n"
	."</font>\n</form>\n";
	$bquery = "SELECT * FROM {$dbprefix}extendedconditions";
	
	if ($searchstring)
	{
	    $bquery .= " WHERE condition LIKE '%$searchstring%' ";
		if ($question) {
			$bquery .= " AND qid='$question'";
		}
	}
	elseif ($question) {
		$bquery .= " WHERE qid='$question'";
	}
	if (!isset($order) || !$order) {$bquery .= " ORDER BY qid";}
	else {$bquery .= " ORDER BY $order"; }

	$bresult = db_select_limit_assoc($bquery, $limit, $start) or safe_die ($clang->gT("Error").": $bquery<br />".$connect->ErrorMsg());
    
	$bgc="";

	$extendedconditionsoutput .= "</div></div></div>\n";

	$extendedconditionsoutput .= "<table class='browsetable' id='browseconditions' cellpadding='1' cellspacing='1'>\n";
    
	// COLUMN HEADINGS
	$extendedconditionsoutput .= "\t<tr>\n"
    
    // Actions
	."<th align='left'  >".$clang->gT("Actions")."</th>\n"
	
    // question
	."<th align='left' >"
	."<a href='$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browse&amp;order=qid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;question=$question'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Question")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Question")
	."' border='0' align='left' hspace='0' /></a>".$clang->gT("Question")."</th>\n"
    
	//group
	."<th align='left'  >"
	."<a href='$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browse&amp;order=gid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;question=$question'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Group")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Group")
	."' border='0' align='left' /></a>".$clang->gT("Group")."</th>\n"
	
	//survey
	."<th align='left'  >"
	."<a href='$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browse&amp;order=sid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;question=$question'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Survey")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Survey")
	."' border='0' align='left' /></a>".$clang->gT("Survey")."</th>\n"
	
	//condition
	."<th align='left'  >"
	."<a href='$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browsegroup&amp;order=condition&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring&amp;question=$question'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Condition")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Condition")
	."' border='0' align='left' /></a>".$clang->gT("Condition")."</th>\n";

	$extendedconditionsoutput .="\t</tr>\n";

	while ($brow = $bresult->FetchRow())
	{
	    if ($bgc == "evenrow") {$bgc = "oddrow";} else {$bgc = "evenrow";}
	    $extendedconditionsoutput .= "\t<tr class='$bgc'>\n"
        
        // actions
        ."<td><input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/conditions_edit_16.png' title='"
        .$clang->gT("Edit condition")
        ."' alt='"
        .$clang->gT("Edit condition")
        ."' onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=".$brow['sid']."&amp;gid=".$brow['gid']."&amp;qid=".$brow['qid']."&amp;subaction=edit', '_top')\" />"
        ."<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/conditions_delete_16.png' title='"
        .$clang->gT("Delete condition")
        ."' alt='"
        .$clang->gT("Delete condition")
        ."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")." (".$brow['qid'].", ".$brow['gid'].", ".$brow['sid'].")')) {".get2post("$scriptname?action=extendedconditions&amp;sid=".$brow['sid']."&amp;gid=".$brow['gid']."&amp;qid=".$brow['qid']."&amp;subaction=delete")."}\"  /></td>";
        
        // question
        $query = "SELECT question FROM {$dbprefix}questions WHERE qid='{$brow['qid']}'";
        $result = db_execute_assoc($query);
        $row = $result->FetchRow();
        $extendedconditionsoutput .= "<td>{$row['question']}</td>\n";
        
        // group
        $query = "SELECT group_name FROM {$dbprefix}groups WHERE gid='{$brow['gid']}'";
        $result = db_execute_assoc($query);
        $row = $result->FetchRow();
        $extendedconditionsoutput .= "<td>{$row['group_name']}</td>\n";
        
        // survey
        $query = "SELECT surveyls_title FROM {$dbprefix}surveys LEFT JOIN {$dbprefix}surveys_languagesettings ON sid=surveyls_survey_id WHERE sid='{$brow['sid']}'";
        $result = db_execute_assoc($query);
        $row = $result->FetchRow();
        $extendedconditionsoutput .= "<td>{$row['surveyls_title']}</td>\n";
        
        // condition
        $extendedconditionsoutput .= "<td>{$brow['condition']}</td>\n";
        
	    $extendedconditionsoutput .= "\t</tr>\n";
	}
	$extendedconditionsoutput .= "</table>\n<br />\n";
}
else if ($subaction == 'addnew' || $subaction == 'edit')
{
    $extendedconditionsoutput .= "<div class='header'>";
    
    if ($subaction == 'edit')
    {
        $extendedconditionsoutput .=$clang->gT("Edit extended condition");
    }
    else
    {
        $extendedconditionsoutput .=$clang->gT("Add extended condition");
    }

    $extendedconditionsoutput .="</div>"
    ."<form id='editextendedcondition' class='form30' method='post' action='$scriptname?action=extendedconditions'>\n"
    ."<ul>\n"
    
    // question
	."<li><label for='qid'>".$clang->gT("Question:")."</label>\n"
	."<select name='qid'>\n";
	$ecquery = "SELECT * FROM {$dbprefix}questions";
	$ecresult = db_execute_assoc($ecquery) or safe_die("Couldn't get questions:<br />$ecquery<br />".$connect->ErrorMsg());
	while ($ecrow = $ecresult->FetchRow())
	{
		$extendedconditionsoutput .= "\t<option value='{$ecrow['qid']}'";
		if ($qid == $ecrow['qid'])
			$extendedconditionsoutput .= "selected='selected'";
		$extendedconditionsoutput .= ">{$ecrow['question']}</option>\n";
	}
	$extendedconditionsoutput .= "</select>\n</li>\n"
    
    // group
	."<li><label for='gid'>".$clang->gT("Group:")."</label>\n"
	."<select name='gid'>\n";
	$ecquery = "SELECT * FROM {$dbprefix}groups";
	$ecresult = db_execute_assoc($ecquery) or safe_die("Couldn't get groups:<br />$ecquery<br />".$connect->ErrorMsg());
	while ($ecrow = $ecresult->FetchRow())
	{
		$extendedconditionsoutput .= "\t<option value='{$ecrow['gid']}'";
		if ($gid == $ecrow['gid'])
			$extendedconditionsoutput .= "selected='selected'";
		$extendedconditionsoutput .= ">{$ecrow['group_name']}</option>\n";
	}
	$extendedconditionsoutput .= "</select>\n</li>\n"
    
    // survey
	."<li><label for='sid'>".$clang->gT("Survey:")."</label>\n"
	."<select name='sid'>\n";
	$ecquery = "SELECT * FROM {$dbprefix}surveys LEFT JOIN {$dbprefix}surveys_languagesettings ON sid=surveyls_survey_id";
	$ecresult = db_execute_assoc($ecquery) or safe_die("Couldn't get surveys:<br />$ecquery<br />".$connect->ErrorMsg());
	while ($ecrow = $ecresult->FetchRow())
	{
		$extendedconditionsoutput .= "\t<option value='{$ecrow['sid']}'";
		if ($surveyid == $ecrow['sid'])
			$extendedconditionsoutput .= "selected='selected'";
		$extendedconditionsoutput .= ">{$ecrow['surveyls_title']}</option>\n";
	}    
	$extendedconditionsoutput .= "</select>\n</li>\n";
    
    // condition
    $ecquery = "SELECT * FROM {$dbprefix}extendedconditions WHERE qid='$qid' AND gid='$gid' AND sid='$surveyid'";
    $ecresult = db_execute_assoc($ecquery) or safe_die("Couldn't get condition:<br />$ecquery<br />".$connect->ErrorMsg());
    $ecrow = $ecresult->FetchRow();
    $extendedconditionsoutput .= "<li><label for='condition'>".$clang->gT("Condition").":</label>\n"
    ."<textarea cols=\"40\" rows=\"5\" name='condition'>{$ecrow['condition']}</textarea></li>\n"
    ."\t</ul><p>";
    
    switch($subaction)
    {
        case 'edit':
            $extendedconditionsoutput .= "<input type='submit' value='".$clang->gT("Update condition")."' />\n"
            ."<input type='hidden' name='subaction' value='update' />\n";
            break;
        case 'addnew':
            $extendedconditionsoutput .= "<input type='submit' value='".$clang->gT("Add condition")."' />\n"
            ."<input type='hidden' name='subaction' value='insert' />\n";
            break;
    }
    
    $extendedconditionsoutput .= "</p></form>\n";
}
else if ($subaction == 'insert')
{
    $extendedconditionsoutput .= "\t<div class='header'>".$clang->gT("Add extended condition")."</div>\n"
    ."\t<div class='messagebox'>\n";
    $query = "INSERT INTO ".db_table_name('extendedconditions')." VALUES ('{$_POST['qid']}','{$_POST['gid']}','{$_POST['sid']}','{$_POST['condition']}')";
    // TODO should check if there is such condition
    $connect->Execute($query) or safe_die ("Add new record failed!<br />$query<br />".$connect->ErrorMsg());
    $extendedconditionsoutput .= "\t\t<div class='successheader'>".$clang->gT("Success")."</div>\n"
    ."\t\t<br />".$clang->gT("New condition was added.")."<br /><br />\n"
    ."\t\t<input type='button' value='".$clang->gT("Display extended conditions")."' onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browse', '_top')\" /><br />\n"
    ."\t\t<input type='button' value='".$clang->gT("Add another condition")."' onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=addnew', '_top')\" /><br />\n";
    $extendedconditionsoutput .= "\t</div>";
}
else if ($subaction == 'update')
{
    $extendedconditionsoutput .= "\t<div class='header'>".$clang->gT("Edit extended condition")."</div>\n"
    ."\t<div class='messagebox'>\n";
    $udquery = "UPDATE ".db_table_name('extendedconditions')." SET `condition`='{$_POST['condition']}'"
    . " WHERE qid='$qid' AND gid='$gid' AND sid='$surveyid'";
    $udresult = $connect->Execute($udquery) or safe_die ("Update failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());
    $extendedconditionsoutput .=  "\t\t<div class='successheader'>".$clang->gT("Success")."</div>\n"
    ."\t\t<br />".$clang->gT("The condition was successfully updated.")."<br /><br />\n"
    ."\t\t<input type='button' value='".$clang->gT("Display extended conditions")."' onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=browse', '_top')\" />\n"
    ."\t</div>";
}
else if ($subaction == 'delete')
{
    $extendedconditionsoutput .= "<div class='messagebox'>\n"
    ."\t<div class='header'>"
    .$clang->gT("Delete")
    ."\t</div>\n"
    ."\t<p><br /><strong>";
    $dlquery = "DELETE FROM {$dbprefix}extendedconditions WHERE qid='$qid' AND gid='$gid' AND sid='$surveyid'";
    $dlresult = $connect->Execute($dlquery) or safe_die ("Couldn't delete condition<br />".$connect->ErrorMsg());
    $extendedconditionsoutput .= $clang->gT("Condition has been deleted.")
    ."</strong><br /><font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i><br /><br /></font>\n"
    ."</p>\n</div>\n";
}


?>
