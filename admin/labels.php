<?php
/*
#############################################################
# >>> LimeSurvey                                           #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA                  #
# > Date:    19 April 2003                                  #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#    Copyright (C) 2003  Jason Cleeland                     #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#                                                           #
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

// ToDo: Prevent users from creating/savin labels with the same code in the same label set
include_once("login_check.php");

if (get_magic_quotes_gpc())
$_POST  = array_map('stripslashes', $_POST);

if($_SESSION['USER_RIGHT_MANAGE_LABEL'] == 1)
	{


	if (isset($_POST['sortorder'])) {$_POST['sortorder']=sanitize_int($_POST['sortorder']);}

	if (!isset($action)) {$action=returnglobal('action');}
	if (!isset($lid)) {$lid=returnglobal('lid');}
	$labelsoutput= include2var('./scripts/addremove.js');
	
	//DO DATABASE UPDATESTUFF 
	if ($action == "updateset") {updateset($lid);}
	if ($action == "insertlabelset") {$lid=insertlabelset();}
	if ($action == "modlabelsetanswers") {modlabelsetanswers($lid);}
	if ($action == "deletelabelset") {if (deletelabelset($lid)) {$lid=0;}}
	if ($action == "importlabels")
	{
		include("importlabel.php");
        if (isset($importlabeloutput)) {echo $importlabeloutput;}
        exit;
	}
	

	$labelsoutput.= "<table width='100%' border='0' bgcolor='#DDDDDD'>\n"
                    . "\t<tr>\n"
                    . "\t\t<td>\n"
                    . "\t\t\t<table class='menubar'>\n"
                    . "\t\t\t<tr >\n"
                    . "\t\t\t\t<td colspan='2' height='8'>\n"
                    . "\t\t\t\t<strong>"
	.$clang->gT("Label Sets Administration")."</strong></td></tr>\n"
	."<tr >\n"
	."\t<td>\n"
	."\t<a href='$scriptname' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Return to Survey Administration", "js")."');return false\">" .
			"<img name='Administration' src='$imagefiles/home.png' title='' alt='' align='left'  /></a>"
	."\t<img src='$imagefiles/blank.gif' width='11' height='20' border='0' hspace='0' align='left' alt='' />\n"
	."\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
	."\t</td>\n"
	."\t<td align='right' width='620'>\n"
	."<a href=\"#\" onclick=\"showhelp('show')\"" 
	."onmouseout=\"hideTooltip()\"" 
	."onmouseover=\"showTooltip(event,'".$clang->gT("Show Help", "js")."');return false\">" 
	."<img src='$imagefiles/showhelp.png' name='ShowHelp' title=''" 
	."alt='". $clang->gT("Show Help")."' align='right'  /></a>"	
	."\t<img src='$imagefiles/blank.gif' width='42' height='20' align='right' hspace='0' border='0'  alt='' />\n"
	."\t<img src='$imagefiles/seperator.gif' align='right' hspace='0' border='0' alt='' />\n"
	."<a href=\"#\" onclick=\"window.open('admin.php?action=newlabelset', '_top')\"" 
	."onmouseout=\"hideTooltip()\"" 
	."onmouseover=\"showTooltip(event,'".$clang->gT("Add New Label Set", "js")."');return false\">"
	."<img src='$imagefiles/add.png' align='right' name='AddLabel' title='' alt='". $clang->gT("Add new label set")."' /></a>\n"	 
	."\t<font class='boxcaption'>".$clang->gT("Labelsets").": </font>"
	."\t<select class='listboxsurveys' "
	."onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
	$labelsets=getlabelsets();
	if (count($labelsets)>0)
	{
		foreach ($labelsets as $lb)
		{
			$labelsoutput.="<option value='admin.php?action=labels&amp;lid={$lb[0]}'";
			if ($lb[0] == $lid) {$labelsoutput.= " selected='selected'";}
			$labelsoutput.= ">{$lb[1]}</option>\n";
		}
	}
	$labelsoutput.= "<option value=''";
	if (!isset($lid) || $lid<1) {$labelsoutput.= " selected='selected'";}
	$labelsoutput.= ">".$clang->gT("Please Choose...")."</option>\n";
	
	$labelsoutput.= "\t</select>\n"
	."\t</td>\n"
	."</tr>\n"
	."\t</table>\n";


	if ($action!='labels' || isset($lid))  {$labelsoutput.="<table ><tr><td></td></tr></table>\n";}
    	
	//NEW SET
	if ($action == "newlabelset" || $action == "editlabelset")
	{
		if ($action == "editlabelset")
		{
         	$query = "SELECT label_name,".db_table_name('labelsets').".lid, languages FROM ".db_table_name('labelsets')." WHERE lid=".$lid;
			$result=db_execute_assoc($query);
			while ($row=$result->FetchRow()) {$lbname=$row['label_name']; $lblid=$row['lid']; $langids=$row['languages'];}
		}
		$labelsoutput.= "<form style='margin-bottom:0;' method='post' action='admin.php' onsubmit=\"return isEmpty(document.getElementById('label_name'), '".$clang->gT("Error: You have to enter a name for this label set.","js")."')\">\n"
		."<table width='100%' class='table2columns'>\n"
		."\t<tr>\n"
		."<td colspan='4' align='center' class='settingcaption'><strong>\n"
		."<input type='image' src='$imagefiles/close.gif' align='right' "
		."onclick=\"window.open('admin.php?action=labels&amp;lid=$lid', '_top')\" />\n";
		if ($action == "newlabelset") {$labelsoutput.= $clang->gT("Create New Label Set"); $langids="en";}
		else {$labelsoutput.= $clang->gT("Edit Label Set");}
		$langidsarray=explode(" ",trim($langids)); //Make an array of it
		$labelsoutput.= "</strong></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."<td align='right' width='25%'>\n"
		."\t<strong>".$clang->gT("Set Name").":</strong>"
		."</td>\n"
		."<td>\n"
		."\t<input type='hidden' name='languageids' id='languageids' value='$langids' />"
		."\t<input type='text' id='label_name' name='label_name' value='";
		if (isset($lbname)) {$labelsoutput.= $lbname;}
		$labelsoutput.= "' />\n"
		."</td>\n"
		."\t</tr>\n"
		// Additional languages listbox
    	. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Languages").":</font></td>\n"
		. "<td><select multiple='multiple' style='min-width:250px;' size='5' id='additional_languages' name='additional_languages'>";
		foreach ($langidsarray as $langid) 
			{
					$labelsoutput.=  "\t<option id='".$langid."' value='".$langid."'";
					$labelsoutput.= ">".getLanguageNameFromCode($langid)."</option>\n";
			}

			//  Add/Remove Buttons
			$labelsoutput.= "</select></td>"
			. "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(1,'".$clang->gT("You cannot remove this items since you need at least one language in a labelset.", "js")."')\" id=\"RemoveBtn\"  /></td>\n"

			// Available languages listbox
			. "<td align='left' width='45%'><select size='5' id='available_languages' name='available_languages'>";
			foreach (getLanguageData() as  $langkey=>$langname)
			{
				if (in_array($langkey,$langidsarray)==false)  // base languag must not be shown here
				{
					$labelsoutput.= "\t<option id='".$langkey."' value='".$langkey."'";
					$labelsoutput.= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
				}
			}

		$labelsoutput.= "\t</select></td></tr><tr>\n"
		."<td></td><td></td>\n"
		."<td>\n"
    	."<br /><input type='submit' value='";
		if ($action == "newlabelset") {$labelsoutput.= $clang->gT("Add");}
		  else {$labelsoutput.= $clang->gT("Update");}
		$labelsoutput.= "' />\n"
		."<input type='hidden' name='action' value='";
		if ($action == "newlabelset") {$labelsoutput.= "insertlabelset";}
		else {$labelsoutput.= "updateset";}
		$labelsoutput.= "' />\n";
		
        if ($action == "editlabelset") 
        {
            $labelsoutput.= "<input type='hidden' name='lid' value='$lblid' />\n";
        }
		
        $labelsoutput.= "</td>\n"
		."\t</tr>\n";
		$labelsoutput.= "</table></form>\n";
		if ($action == "newlabelset")
		{
			$labelsoutput.= "<form enctype='multipart/form-data' name='importlabels' action='admin.php' method='post'>\n"
			."<table width='100%' class='table2columns'>\n"
			."\t<tr><td colspan='2'>\n"
			."<strong>".$clang->gT("OR")."</strong>\n"
			."\t</td></tr>\n"
			."\t<tr bgcolor='black'>\n"
			."<td colspan='2' align='center' class='settingcaption'><strong>\n"
			.$clang->gT("Import Label Set")."\n"
			."</strong></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."<td align='right'><strong>"
			.$clang->gT("Select CSV File:")."</strong></td>\n"
			."<td><input name=\"the_file\" type=\"file\" size=\"35\" />"
			."</td></tr>\n"
			."\t<tr><td></td><td><input type='submit' value='".$clang->gT("Import Label Set")."' />\n"
			."\t<input type='hidden' name='action' value='importlabels' /></td>\n"
			."\t</tr></table></form>\n";
		}
	}
	//SET SELECTED
	if (isset($lid) && ($action != "editlabelset") && $lid)
	{
		//CHECK TO SEE IF ANY ACTIVE SURVEYS ARE USING THIS LABELSET (Don't let it be changed if this is the case)
		$query = "SELECT ".db_table_name('surveys_languagesettings').".surveyls_title FROM ".db_table_name('questions').", ".db_table_name('surveys')." , ".db_table_name('surveys_languagesettings')." WHERE ".db_table_name('questions').".sid=".db_table_name('surveys').".sid AND ".db_table_name('surveys').".sid=".db_table_name('surveys_languagesettings').".surveyls_survey_id AND ".db_table_name('questions').".lid=$lid AND ".db_table_name('surveys').".active='Y'";
		$result = db_execute_assoc($query);
		$activeuse=$result->RecordCount();
		while ($row=$result->FetchRow()) {$activesurveys[]=$row['surveyls_title'];}
		//NOW ALSO COUNT UP HOW MANY QUESTIONS ARE USING THIS LABELSET, TO GIVE WARNING ABOUT CHANGES
		//$query = "SELECT * FROM ".db_table_name('questions')." WHERE type IN ('F','H','Z','W') AND lid='$lid' GROUP BY qid";
		//NOTE: OK, we're back to "what the hell is Tom up to?". SQL Server complains if the selected columns aren't either aggregated
		// part of the GROUP BY clause. This should work for both databases.
		$query = "SELECT qid, sid, gid FROM ".db_table_name('questions')." WHERE type IN ('F','H','Z','W') AND lid='$lid' GROUP BY qid, sid, gid";		
		$result = db_execute_assoc($query);
		$totaluse=$result->RecordCount();
		while($row=$result->FetchRow())
		{
			$qidarray[]=array("url"=>"$scriptname?sid=".$row['sid']."&amp;gid=".$row['gid']."&amp;qid=".$row['qid'], "title"=>"QID: ".$row['qid']);
		}
		//NOW GET THE ANSWERS AND DISPLAY THEM
		$query = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
		$result = db_execute_assoc($query);
		while ($row=$result->FetchRow())
		{
			$labelsoutput.= "\t<table class='menubar'>\n"
			."<tr><td height='4' colspan='2'>"
			."<strong>".$clang->gT("Label Set").":</strong> {$row['label_name']}</td></tr>\n"
			."<tr>\n"
			."\t<td>\n"
			."\t<input type='image' src='$imagefiles/close.gif' title='"
			.$clang->gT("Close Window")."' align='right' "
			."onclick=\"window.open('admin.php?action=labels', '_top')\" />\n"
			."\t<img src='$imagefiles/blank.gif' width='50' height='20' border='0' hspace='0' align='left' alt='' />\n"
			."\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
			."\t<a href='admin.php?action=editlabelset&amp;lid=$lid' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Edit label set", "js")."');return false\">" .
			"<img name='EditLabelsetButton' src='$imagefiles/edit.png' alt='' align='left'  /></a>" 
			."\t<a href='admin.php?action=deletelabelset&amp;lid=$lid' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Delete label set", "js")."');return false\">"
			."<img src='$imagefiles/delete.png' border='0' alt='' title='' align='left' onclick=\"return confirm('".$clang->gT("Are you sure?","js")."')\" /></a>\n"
			."\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
			."\t<a href='dumplabel.php?lid=$lid' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Export Label Set", "js")."');return false\">" .
					"<img src='$imagefiles/exportcsv.png' alt='".$clang->gT("Export Label Set")."' title='' align='left' /></a>" 
			."\t</td>\n"
			."</tr>\n"
			."\t</table>\n";
		}


		//LABEL ANSWERS  - SHOW THE MASK FOR EDITING THE LABELS 


		$qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
		$rslabelset = db_execute_assoc($qulabelset) or die($connect->ErrorMsg());
		$rwlabelset=$rslabelset->FetchRow();
		$lslanguages=explode(" ", trim($rwlabelset['languages'])); 
		
		$labelsoutput.= "\t<table class='menubar'>\n"
		."<tr>\n"
		."\t<td colspan='4'><strong>\n"
		.$clang->gT("Labels")
		."\t</strong></td>\n"
		."</tr>\n"
        ."\t<tr><td colspan='4'>\n"        ."<form method='post' action='admin.php'>\n"
	    ."<input type='hidden' name='sortorder' value='{$row['sortorder']}' />\n"
		."<input type='hidden' name='lid' value='$lid' />\n"
		."<input type='hidden' name='action' value='modlabelsetanswers' />\n";
        $labelsoutput.= "<div class='tab-pane' id='tab-pane-1'>";    
        $first=true;
        $sortorderids=''; $codeids='';
		foreach ($lslanguages as $lslanguage)
		{
     		$position=0;
    		$query = "SELECT * FROM ".db_table_name('labels')." WHERE lid=$lid and language='$lslanguage' ORDER BY sortorder, code";
    		$result = db_execute_assoc($query) or die($connect->ErrorMsg());
    		$labelcount = $result->RecordCount();
            $labelsoutput.= "<div class='tab-page'>"
                ."<h2 class='tab'>".getLanguageNameFromCode($lslanguage)."</h2>"
                ."\t<table width='100%' class='table2columns'>\n"
                ."<thead align='center'>"
        		."<tr>\n"
        		."\t<td width='25%' align='right' class='settingcaption'><strong>\n"
        		.$clang->gT("Code")
        		."\t</strong></td>\n"
        		."\t<td width='35%' class='settingcaption'><strong>\n"
        		.$clang->gT("Title")
        		."\t</strong></td>\n"
        		."\t<td width='25%' class='settingcaption'><strong>\n"
        		.$clang->gT("Action")
        		."\t</strong></td>\n"
        		."\t<td width='15%' align='center' class='settingcaption'><strong>\n"
        		.$clang->gT("Order")
        		."\t</strong></td>\n"
        		."</tr></thead>"
                ."<tbody align='center'>";
    		while ($row=$result->FetchRow())
    		{
                $sortorderids=$sortorderids.' '.$row['language'].'_'.$row['sortorder'];
    			if ($first) {$codeids=$codeids.' '.$row['sortorder'];}                 
    			$labelsoutput.= "<tr><td width='25%' align='right'>\n";

    			if ($activeuse > 0)
    			{
    				$labelsoutput.= "\t{$row['code']}"
    				."<input type='hidden' name='code_{$row['sortorder']}' value=\"{$row['code']}\" />\n";
    			}
    			elseif (!$first)
    			{   
                    $labelsoutput.= "\t{$row['code']}";
                }
    			else
    			{
    				$labelsoutput.= "\t<input type='text' name='code_{$row['sortorder']}' maxlength='5' size='10' value=\"{$row['code']}\" onkeypress=\"return catchenter(event,'saveallbtn');\" />\n"; // TIBO
    			}
    			
    			$labelsoutput.= "\t</td>\n"
    			."\t<td width='35%'>\n"
    			."\t<input type='text' name='title_{$row['language']}_{$row['sortorder']}' maxlength='100' size='80' value=\"".html_escape($row['title'])."\" onkeypress=\"return catchenter(event,'saveallbtn');\"/>\n"
    			."\t</td>\n"
    			."\t<td width='25%'>\n";
    			if ($activeuse == 0)
    			{
    				$labelsoutput.= "\t<input type='submit' name='method' value='".$clang->gT("Del")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
    			}
    			$labelsoutput.= "\t</td>\n"
    			."\t<td>\n";
    			if ($position > 0)
    			{
    				$labelsoutput.= "\t<input type='submit' name='method' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
    			};
    			if ($position < $labelcount-1)
    			{
    				// Fill the sortorder hiddenfield so we now what field is moved down
                    $labelsoutput.= "\t<input type='submit' name='method' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
    			}
    			$labelsoutput.= "\t</td></tr>\n";
    			$position++;
    		}
    	    if ($labelcount>0)  
            {                       
                $labelsoutput.= "\t<tr><td colspan='4'><center><input type='submit' name='method' value='".$clang->gT("Save All")."'  id='saveallbtn' />"
                ."</center></td></tr>\n";
            }

    		$position=sprintf("%05d", $position);
    		if ($activeuse == 0 && $first)
    		{   $labelsoutput.= "<tr><td><br /></td></tr><tr><td width='25%' align='right'>"
  			    ."<strong>".$clang->gT("New label").":</strong> <input type='text' maxlength='5' name='insertcode' size='10' id='addnewlabelcode' onkeypress=\"return catchenter(event,'addnewlabelbtn');\" />\n"
    			."\t</td>\n"
    			."\t<td width='35%'>\n"
    			."\t<input type='text' maxlength='100' name='inserttitle' size='80' onkeypress=\"return catchenter(event,'addnewlabelbtn');\"/>\n"
    			."\t</td>\n"
    			."\t<td width='25%'>\n"
    			."\t<input type='submit' name='method' value='".$clang->gT("Add new label")."' id='addnewlabelbtn' />\n"
    			."\t</td>\n"
    			."\t<td>\n"
                ."<script type='text/javascript'>\n"
    			."<!--\n"
    			."document.getElementById('addnewlabelcode').focus();\n"
    			."function catchenter(evt, btnid)\n"
    			."{\n"
    			."\tvar mykey = window.event ? evt.keyCode : evt.which;\n"
    			."\tif (mykey == 13)\n"
    			."\t{\n"
    			."\t\tvar mybtn = document.getElementById(btnid);\n"
    			."\t\tmybtn.click();\n"
    			."\t\treturn false;\n"
    			."\t}\n"
    			."\treturn true;\n"
    			."}\n"
    			."//-->\n"
    			."</script>\n"
    			."\t</td>\n"
    			."</tr>\n";
    	
    		}
			elseif ($activeuse == 0  && !$first)
			{
    			$labelsoutput.= "<tr>\n"
    			."\t<td colspan='4' align='center'>\n"
    			."<font color='green' size='1'><i><strong>"
    			.$clang->gT("Warning")."</strong>: ".$clang->gT("Inserting New labels must be done on the first language folder.")."</i></font>\n"
    			."\t</td>\n"
    			."</tr>\n";
			}
    		else
    		{
    			$labelsoutput .= "<tr>\n"
    			."\t<td colspan='4' align='center'>\n"
    			."<font color='red' size='1'><i><strong>"
    			.$clang->gT("Warning")."</strong>: ".$clang->gT("You cannot change codes, add or delete entries in this label set because it is being used by an active survey.")."</i></strong></font><br />\n";
    			if ($totaluse > 0)
    			{
    				foreach ($qidarray as $qd) {$labelsoutput.= "[<a href='".$qd['url']."'>".$qd['title']."</a>] ";}
    			}
    			$labelsoutput .= "\t</td>\n"
    			."</tr>\n";
    		}
        $first=false;
    	$labelsoutput.="</tbody></table>\n";

	    $labelsoutput.=("</div>");
        }	
	$labelsoutput.= "<input type='hidden' name='sortorderids' value='$sortorderids' />\n";
	$labelsoutput.= "<input type='hidden' name='codeids' value='$codeids' />\n";
	$labelsoutput.= "</div>"
	
    	."</form>";
	
	// Here starts the Fix Sort order form
    $labelsoutput.= "</td></tr><tr><td colspan='4'>"
        ."<form style='margin-bottom:0;' action='admin.php?action=labels' method='post'>"
		."<table width='100%' style='border: solid; border-width: 0px; border-color: #555555' cellspacing='0'><tbody align='center'>\n"
		."\t<tr><td width='80%'></td>"
		."<td></td><td><input type='submit' name='method' value='"
		.$clang->gT("Fix Sort")."' /></td>\n"
		."</tr></tbody></table>"
		."\t<input type='hidden' name='lid' value='$lid' />\n"
		."\t<input type='hidden' name='action' value='modlabelsetanswers' />\n"
		."</form></td></tr>\n";
		if ($totaluse > 0 && $activeuse == 0) //If there are surveys using this labelset, but none are active warn about modifying
		{
			$labelsoutput.= "<tr>\n"
			."\t<td colspan='4' align='center'>\n"
			."<font color='red' size='1'><i><strong>"
			.$clang->gT("Warning")."</strong>: ".$clang->gT("Some surveys currently use this label set. Modifying the codes, adding or deleting entries to this label set may produce undesired results in other surveys.")."</i><br />";
			foreach ($qidarray as $qd) {$labelsoutput.= "[<a href='".$qd['url']."'>".$qd['title']."</a>] ";}
			$labelsoutput.= "</strong></font>\n"
			."\t</td>\n"
			."</tr>\n";
		}
		$labelsoutput.= "\t</table>\n";
	}
		$labelsoutput.="</td></tr></table>";	
	}
else
	{
	$action = "labels";
	include("access_denied.php");
	include("admin.php");	
	}
	
//************************FUNCTIONS********************************
function updateset($lid)
{
	global $dbprefix, $connect, $labelsoutput; 
	// Get added and deleted languagesid arrays
	$newlanidarray=explode(" ",trim($_POST['languageids']));

	$_POST['languageids'] = db_quoteall($_POST['languageids'],true);
	$_POST['label_name'] = db_quoteall($_POST['label_name'],true);
	$oldlangidsarray=array();
	$query = "SELECT languages FROM ".db_table_name('labelsets')." WHERE lid=".$lid;
	$result=db_execute_assoc($query);
	if ($result)
	{
		while ($row=$result->FetchRow()) {$oldlangids=$row['languages'];}
		$oldlangidsarray=explode(" ",trim($oldlangids));
	}
	$addlangidsarray=array_diff($newlanidarray,$oldlangidsarray);
	$dellangidsarray=array_diff($oldlangidsarray,$newlanidarray);

	// If new languages are added, create labels' codes and sortorder for the new languages	
	$query = "SELECT code,sortorder FROM ".db_table_name('labels')." WHERE lid=".$lid." GROUP BY code,sortorder";
	$result=db_execute_assoc($query);
	if ($result) { while ($row=$result->FetchRow()) {$oldcodesarray[$row['code']]=$row['sortorder'];} }
	$sqlvalues='';
	if (isset($oldcodesarray) && count($oldcodesarray) > 0 )
	{
		foreach ($addlangidsarray as $addedlangid)
		{
			foreach ($oldcodesarray as $oldcode => $oldsortorder)
			{
				$sqlvalues .= ", ($lid, '$oldcode', '$oldsortorder', '$addedlangid')";
			}
		}
	}	
	if ($sqlvalues)
	{
		$query = "INSERT INTO ".db_table_name('labels')." (lid,code,sortorder,language) VALUES ".trim($sqlvalues,',');
		$result=db_execute_assoc($query);
		if (!$result)
		{
			$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to Copy already defined labels to added languages","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
		}
	}

	// If languages are removed, delete labels for these languages
	$sqlwherelang='';
	foreach ($dellangidsarray as $dellangid)
	{
		$sqlwherelang .= " OR language='".$dellangid."'";
	}
	if ($sqlwherelang)
	{
		$query = "DELETE FROM ".db_table_name('labels')." WHERE lid=$lid AND (".trim($sqlwherelang, ' OR').")";
		$result=db_execute_assoc($query);
		if (!$result)
		{
			$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete labels for removed languages","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
		}
	}

	// Update the labelset itself
	$query = "UPDATE ".db_table_name('labelsets')." SET label_name={$_POST['label_name']}, languages={$_POST['languageids']} WHERE lid=$lid";
	if (!$result = $connect->Execute($query))
	{
		$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Update of Label Set failed","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
	}
}



function deletelabelset($lid)
// language proof
{
	global $dbprefix, $connect, $clang, $labelsoutput;
	//CHECK THAT THERE ARE NO QUESTIONS THAT RELY ON THIS LID
	$query = "SELECT qid FROM ".db_table_name('questions')." WHERE type IN ('F','H','W','Z') AND lid=$lid";
	$result = $connect->Execute($query) or die("Error");
	$count = $result->RecordCount();
	if ($count > 0)
	{
		$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Couldn't Delete Label Set - There are questions that rely on this. You must delete these questions first.","js")."\")\n //-->\n</script>\n";
		return false;
	}
	else //There are no dependencies. We can delete this safely
	{
		$query = "DELETE FROM ".db_table_name('labels')." WHERE lid=$lid";
		$result = $connect->Execute($query);
		$query = "DELETE FROM ".db_table_name('labelsets')." WHERE lid=$lid";
		$result = $connect->Execute($query);
		return true;
	}
}



function insertlabelset()
{
	global $dbprefix, $connect, $clang, $labelsoutput;
//	$labelsoutput.= $_POST['languageids'];  For debug purposes
	$_POST['label_name'] = db_quoteall($_POST['label_name'],true);
	$_POST['languageids'] = db_quoteall($_POST['languageids'],true);
	$query = "INSERT INTO ".db_table_name('labelsets')." (label_name,languages) VALUES ({$_POST['label_name']},{$_POST['languageids']})";
	if (!$result = $connect->Execute($query))
	{
		$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Update of Label Set failed","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
	}
	else
	{
		return $connect->Insert_ID();
	}
}



function modlabelsetanswers($lid)
{
	global $dbprefix, $connect, $clang, $labelsoutput;
	
	$qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid='$lid'";
	$rslabelset = db_execute_assoc($qulabelset) or die($connect->ErrorMsg());
	$rwlabelset=$rslabelset->FetchRow();
	$lslanguages=explode(" ", trim($rwlabelset['languages'])); 
	
	if (!isset($_POST['method'])) {
		$_POST['method'] = $clang->gT("Save");
	}
	switch($_POST['method'])
	{
		case $clang->gT("Add new label", "unescaped"):
		if (isset($_POST['insertcode']) && $_POST['insertcode']!='')
		{
			// check that the code doesn't exist yet
   			$query = "SELECT code FROM ".db_table_name('labels')." WHERE lid='$lid' AND code='".$_POST['insertcode']."'";
			$result = $connect->Execute($query);
			$codeoccurences=$result->RecordCount();
			if ($codeoccurences == 0)
			{
	   			$query = "select max(sortorder) as maxorder from ".db_table_name('labels')." where lid='$lid'";
				$result = $connect->Execute($query);
				$newsortorder=sprintf("%05d", $result->fields['maxorder']+1);
	
				$_POST['insertcode'] = db_quoteall($_POST['insertcode'],true);
	   			$_POST['inserttitle'] = db_quoteall($_POST['inserttitle'],true);
	  			foreach ($lslanguages as $lslanguage)
				{
	    				$query = "INSERT INTO ".db_table_name('labels')." (lid, code, title, sortorder,language) VALUES ($lid, {$_POST['insertcode']}, {$_POST['inserttitle']}, '$newsortorder','$lslanguage')";
					if (!$result = $connect->Execute($query))
	    				{
	    					$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to insert label", "js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
	    				}
				}
			}
			else
			{
	    			$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("This label code is already used in this labelset. Please choose another code or rename the existing one.", "js")."\")\n //-->\n</script>\n";
			}
		}
		break;

		// Save all labels with one button
		case $clang->gT("Save All", "unescaped"):
		//Determine autoids by evaluating the hidden field		
		$sortorderids=explode(' ', trim($_POST['sortorderids']));
		$codeids=explode(' ', trim($_POST['codeids']));
		$count=0; 

		// Quote each code_codeid first
		foreach ($codeids as $codeid)
		{
			$_POST['code_'.$codeid] = db_quoteall($_POST['code_'.$codeid],true);
			// Get the code values to check for duplicates
			$codevalues[] = $_POST['code_'.$codeid];
		}

		// Check that there is no code duplicate
		if (count(array_unique($codevalues)) == count($codevalues))
		{
	         	foreach ($sortorderids as $sortorderid)
	        	{
	        		$langid=substr($sortorderid,0,strrpos($sortorderid,'_')); 
	        		$orderid=substr($sortorderid,strrpos($sortorderid,'_')+1,20);
				    $_POST['title_'.$sortorderid] = db_quoteall($_POST['title_'.$sortorderid],true);
	                $query = "UPDATE ".db_table_name('labels')." SET code=".$_POST['code_'.$codeids[$count]].", title={$_POST['title_'.$sortorderid]} WHERE lid=$lid AND sortorder=$orderid AND language='$langid'";
	        		if (!$result = $connect->Execute($query)) 
	        		// if update didn't work we assume the label does not exist and insert it
	        		{
	                    $query = "insert into ".db_table_name('labels')." SET code=".$_POST['code_'.$codeids[$count]].", title={$_POST['title_'.$sortorderid]}, lid=$lid , sortorder=$orderid , language='$langid'";
	            		if (!$result = $connect->Execute($query))
	            		{
	            			$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update label","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
	        			}
	    			}
	    			$count++;
	    			if ($count>count($codeids)-1) {$count=0;}
			}
		}
		else
		{
	            $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Can't update labels because you are using duplicated codes","js")."\")\n //-->\n</script>\n";
		}

		break;

        // Pressing the Up button
		case $clang->gT("Up", "unescaped"):
        $newsortorder=$_POST['sortorder']-1;
		$oldsortorder=$_POST['sortorder'];
		$cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=-1 WHERE lid=$lid AND sortorder=$newsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=$newsortorder WHERE lid=$lid AND sortorder=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('labels')." SET sortorder='$oldsortorder' WHERE lid=$lid AND sortorder=-1";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;

        // Pressing the Down button
		case $clang->gT("Dn", "unescaped"):
		$newsortorder=$_POST['sortorder']+1;
		$oldsortorder=$_POST['sortorder'];
		$cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=-1 WHERE lid=$lid AND sortorder='$newsortorder'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('labels')." SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=$oldsortorder WHERE lid=$lid AND sortorder=-1";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;
		
		// Delete Button
		case $clang->gT("Del", "unescaped"):
		$query = "DELETE FROM ".db_table_name('labels')." WHERE lid=$lid AND sortorder='{$_POST['sortorder']}'";
		if (!$result = $connect->Execute($query))
		{
			$labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete label","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
		}
		break;
		
		// Fix Sortorder button
		case $clang->gT("Fix Sort", "unescaped"):
		fixorder($lid);
		break;
	}
}


function fixorder($lid) //Function rewrites the sortorder for a group of answers
{
	global $dbprefix, $connect, $labelsoutput;
	$qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
	$rslabelset = db_execute_assoc($qulabelset) or die($connect->ErrorMsg());
	$rwlabelset=$rslabelset->FetchRow();
	$lslanguages=explode(" ", trim($rwlabelset['languages'])); 
	foreach ($lslanguages as $lslanguage)
	{
    	$query = "SELECT lid, code, title FROM ".db_table_name('labels')." WHERE lid=? and language='$lslanguage' ORDER BY sortorder, code";
    	$result = db_execute_num($query, array($lid));
    	$position=0;
    	while ($row=$result->FetchRow())
    	{
    		$position=sprintf("%05d", $position);
    		$query2="UPDATE ".db_table_name('labels')." SET sortorder='$position' WHERE lid=? AND code=? AND title=? AND language='$lslanguage' ";
    		$result2=$connect->Execute($query2, array ($row[0], $row[1], $row[2])) or die ("Couldn't update sortorder<br />$query2<br />".$connect->ErrorMsg());
    		$position++;
    	}
    }	
}




?>
