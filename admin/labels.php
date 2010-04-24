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

include_once("login_check.php");  //Login Check dies also if the script is started directly

// ToDo: Prevent users from creating/savin labels with the same code in the same label set

// Do not stripslashes on POSted fields because labels.php uses db_quoteall($str, $ispostvariable) that checks for magic_quotes_gpc
// However We need to stripslashes from $_POST['method'] compared to
// unescaped strings in switch case
//if (get_magic_quotes_gpc())
//$_POST  = array_map('stripslashes', $_POST);
if (isset($_POST['method']) && get_magic_quotes_gpc())
{
    $_POST['method']  = stripslashes($_POST['method']);
}

$labelsoutput='';
if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_LABEL'] == 1)
{


    if (isset($_POST['sortorder'])) {$postsortorder=sanitize_int($_POST['sortorder']);}

    if (!isset($action)) {$action=returnglobal('action');}
    if (!isset($lid)) {$lid=returnglobal('lid');}
    if (!isset($lid1)) {$lid1=returnglobal('lid1');}

    //DO DATABASE UPDATESTUFF
    if ($action == "updateset") {updateset($lid);}
    if ($action == "insertlabelset") {$lid=insertlabelset();}
    if ($action == "modlabelsetanswers") {modlabelsetanswers($lid);}
    if ($action == "deletelabelset") {if (deletelabelset($lid)) {$lid=0;}}
    if ($action == "importlabels")
    {
        include("importlabel.php");
        if (isset($importlabeloutput)) {$labelsoutput.= $importlabeloutput;}
        return;
    }
    if ($action == "importlabelresources")
    {
        include("import_resources_zip.php");
        if (isset($importlabelresourcesoutput)) {$labelsoutput.= $importlabelresourcesoutput;}
        return;
    }


    $labelsoutput= "<div class='menubar'>\n"
    ."\t\t<div class='menubar-title'>\n"
    ."\t\t<strong>".$clang->gT("Label Sets Administration")."</strong>\n"
    ."\t\t</div>\n"
    ."\t<div class='menubar-main'>\n"
    ."\t<div class='menubar-left'>\n"
    ."\t<a href='$scriptname' title=\"".$clang->gTview("Return to survey administration")."\" >"
    ."<img name='Administration' src='$imagefiles/home.png' alt='".$clang->gT("Return to survey administration")."' /></a>"
    ."\t<img src='$imagefiles/blank.gif' width='11' height='20' alt='' />\n"
    ."\t<img src='$imagefiles/seperator.gif' alt='' />\n"
    ."\t</div>\n"
    ."\t<div class='menubar-right'>\n"
    ."\t<img src='$imagefiles/blank.gif' width='5' height='20' alt='' />\n"
    ."\t<font class='boxcaption'>".$clang->gT("Labelsets").": </font>"
    ."\t<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
    $labelsoutput.= "<option value=''";
    if (!isset($lid) || $lid<1) {$labelsoutput.= " selected='selected'";}
    $labelsoutput.= ">".$clang->gT("Please Choose...")."</option>\n";
    $labelsets=getlabelsets();
    if (count($labelsets)>0)
    {
        foreach ($labelsets as $lb)
        {
            $labelsoutput.="<option value='admin.php?action=labels&amp;lid={$lb[0]}'";
            if ($lb[0] == $lid) {$labelsoutput.= " selected='selected'";}
            $labelsoutput.= ">{$lb[0]}: {$lb[1]}</option>\n";
        }
    }

    $labelsoutput.= "\t</select>\n"
    ."<a href=\"#\" onclick=\"window.open('admin.php?action=newlabelset', '_top')\""
    ." title=\"".$clang->gTview("Add new label set")."\">"
    ."<img src='$imagefiles/add.png' name='AddLabel' alt='". $clang->gT("Add new label set")."' /></a>\n"
    ."\t<img src='$imagefiles/seperator.gif'  alt='' />\n"
    ."\t<img src='$imagefiles/blank.gif' width='5' height='20' alt='' />\n"
    //Logout button
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=logout', '_top')\""
    ." title=\"".$clang->gTview("Logout")."\" >"
    ."<img src='$imagefiles/logout.png' name='Logout' alt='".$clang->gT("Logout")."' /></a>"
    //Show help
    ."<a href=\"#\" onclick=\"showhelp('show')\" title=\"".$clang->gTview("Show Help")."\">"
    ."<img src='$imagefiles/showhelp.png' name='ShowHelp' "
    ."alt='". $clang->gT("Show Help")."' /></a>"
    ."\t\t</div>\n"
    ."\t</div>\n"
    ."</div>\n";
     
    //NEW SET
    if ($action == "newlabelset" || $action == "editlabelset")
    {
        if ($action == "editlabelset")
        {
            $query = "SELECT label_name,".db_table_name('labelsets').".lid, languages FROM ".db_table_name('labelsets')." WHERE lid=".$lid;
            $result=db_execute_assoc($query);
            while ($row=$result->FetchRow()) {$lbname=$row['label_name']; $lblid=$row['lid']; $langids=$row['languages'];}
        }
        $labelsoutput.="<div class='header header_statistics'>\n"
        ."<input type='image' src='$imagefiles/close.gif' align='right' "
        ."onclick=\"window.open('admin.php?action=labels&amp;lid=$lid', '_top')\" />\n";
        if ($action == "newlabelset") {$labelsoutput.= $clang->gT("Create or Import New Label Set"); $langids=$_SESSION['adminlang']; $tabitem=$clang->gT("Create New Label Set");}
        else {$labelsoutput.= $clang->gT("Edit Label Set"); $tabitem=$clang->gT("Edit Label Set");}
        $langidsarray=explode(" ",trim($langids)); //Make an array of it
        $labelsoutput.= "\n\t</div>\n";

        if (isset($row['lid'])) { $panecookie=$row['lid'];} else  {$panecookie='new';}
        $labelsoutput.= "<div class='tab-pane' id='tab-pane-labelset-{$panecookie}'>\n";
        $labelsoutput.= "<div class='tab-page'> <h2 class='tab'>".$tabitem."</h2>\n";
        $labelsoutput.= "<form method='post' class='form30' id='labelsetform' action='admin.php' onsubmit=\"return isEmpty(document.getElementById('label_name'), '".$clang->gT("Error: You have to enter a name for this label set.","js")."')\">\n";

        $labelsoutput.= "<ul'>\n"
        ."<li><label for='languageids'>".$clang->gT("Set name:")."</label>\n"
        ."\t<input type='hidden' name='languageids' id='languageids' value='$langids' />"
        ."\t<input type='text' id='label_name' name='label_name' value='";
        if (isset($lbname)) {$labelsoutput.= $lbname;}
        $labelsoutput.= "' />\n"
        ."</li>\n"
        // Additional languages listbox
        . "\t<li><label>".$clang->gT("Languages:")."</label>\n"
        . "<table><tr><td align='left'><select multiple='multiple' style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>";
        foreach ($langidsarray as $langid)
        {
            $labelsoutput.=  "\t<option id='".$langid."' value='".$langid."'";
            $labelsoutput.= ">".getLanguageNameFromCode($langid,false)."</option>\n";
        }

        //  Add/Remove Buttons
        $labelsoutput.= "</select></td>"
        . "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(1,'".$clang->gT("You cannot remove this item since you need at least one language in a labelset.", "js")."')\" id=\"RemoveBtn\"  /></td>\n"

        // Available languages listbox
        . "<td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>";
        foreach (getLanguageData() as  $langkey=>$langname)
        {
            if (in_array($langkey,$langidsarray)==false)  // base languag must not be shown here
            {
                $labelsoutput.= "\t<option id='".$langkey."' value='".$langkey."'";
                $labelsoutput.= ">".$langname['description']."</option>\n";
            }
        }

        $labelsoutput.= "\t</select></td>"
        ." </tr></table></li></ul>\n"
        ."<p><input type='submit' value='";
        if ($action == "newlabelset") {$labelsoutput.= $clang->gT("Save label set");}
        else {$labelsoutput.= $clang->gT("Update");}
        $labelsoutput.= "' />\n"
        ."<input type='hidden' name='action' value='";
        if ($action == "newlabelset") {$labelsoutput.= "insertlabelset";}
        else {$labelsoutput.= "updateset";}
        $labelsoutput.= "' />\n";

        if ($action == "editlabelset") {
            $labelsoutput.= "<input type='hidden' name='lid' value='$lblid' />\n";
        }

        $labelsoutput.= "</form></div>\n";

        if ($action == "newlabelset"){
            $labelsoutput.= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Import Label Set")."</h2>\n"
            ."<form enctype='multipart/form-data' id='importlabels' name='importlabels' action='admin.php' method='post'>\n"
            ."<div class='header'>\n"
            .$clang->gT("Import Label Set")."\n"
            ."</div><ul>\n"
            ."<li><label for='the_file'>"
            .$clang->gT("Select CSV File:")."</label>\n"
            ."<input id='the_file' name='the_file' type='file' size='35' />"
            ."</li>\n"
            ."<li><label for='checkforduplicates'>"
            .$clang->gT("Check for duplicates?")."</label>\n"
            ."<input name='checkforduplicates' id='checkforduplicates' type='checkbox' checked='checked' />\n"
            ."</li>"
            ."<li><label for='translinksfields'>"
            .$clang->gT("Convert resources links?")."</label>\n"
            ."<input name='translinksfields' id='translinksfields' type='checkbox' checked='checked' />\n"
            ."</li></ul>\n"
            ."<p><input type='submit' value='".$clang->gT("Import Label Set")."' />\n"
            ."<input type='hidden' name='action' value='importlabels' />\n"
            ."</form></div>\n";
        }
        $labelsoutput.= "</div>\n";
    }
    //SET SELECTED
    if (isset($lid) && ($action != "editlabelset") && $lid)
    {
        //NOW GET THE ANSWERS AND DISPLAY THEM
        $query = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
        $result = db_execute_assoc($query);
        while ($row=$result->FetchRow())
        {
            $labelsoutput.= "<div class='menubar'>\n"
            ."<div class='menubar-title'>\n"
            ."\t<strong>".$clang->gT("Label Set").":</strong> {$row['label_name']}\n"
            ."</div>\n"
            ."<div class='menubar-main'>\n"
            ."\t<div class='menubar-left'>\n"
            ."\t<img src='$imagefiles/blank.gif' width='60' height='20' border='0' hspace='0' align='left' alt='' />\n"
            ."\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
            ."\t<a href='admin.php?action=editlabelset&amp;lid=$lid' title=\"".$clang->gTview("Edit label set")."\" >" .
			"<img name='EditLabelsetButton' src='$imagefiles/edit.png' alt='".$clang->gT("Edit label set")."' align='left'  /></a>" 
			."\t<a href='#' title='".$clang->gTview("Delete label set")."' onclick=\"if (confirm('".$clang->gT("Do you really want to delete this label set?","js")."')) {".get2post("admin.php?action=deletelabelset&amp;lid=$lid")."}\" >"
			."<img src='$imagefiles/delete.png' border='0' alt='".$clang->gT("Delete label set")."' align='left' /></a>\n"
			."\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
			."\t<a href='admin.php?action=dumplabel&amp;lid=$lid' title=\"".$clang->gTview("Export Label Set")."\" >" .
					"<img src='$imagefiles/exportcsv.png' alt='".$clang->gT("Export Label Set")."' align='left' /></a>" 
					."\t</div>\n"
					."\t<div class='menubar-right'>\n"
					."\t<input type='image' src='$imagefiles/close.gif' title='".$clang->gT("Close Window")."'"
					."onclick=\"window.open('admin.php?action=labels', '_top')\" />\n"
					."\t</div>\n"
					."\t</div>\n"
					."\t</div>\n";
					$labelsoutput .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
        }


        //LABEL ANSWERS  - SHOW THE MASK FOR EDITING THE LABELS


        $qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
        $rslabelset = db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
        $rwlabelset=$rslabelset->FetchRow();
        $lslanguages=explode(" ", trim($rwlabelset['languages']));

        $labelsoutput.= PrepareEditorScript("editlabel");

        $maxquery = "SELECT max(sortorder) as maxsortorder FROM ".db_table_name('labels')." WHERE lid=$lid and language='{$lslanguages[0]}'";
        $maxresult = db_execute_assoc($maxquery) or safe_die($connect->ErrorMsg());
        $msorow=$maxresult->FetchRow();
        $maxsortorder=$msorow['maxsortorder']+1;

        $labelsoutput.= "\t<div class='header'>".$clang->gT("Labels")."\t</div>\n";
        $labelsoutput.= "<div class='tab-pane' id='tab-pane-labels-{$lid}'>"
        ."<form method='post' action='admin.php' onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate label codes.",'js')."','".$clang->gT("Error: 'other' is a reserved keyword.",'js')."');\">\n"
        ."<input type='hidden' name='sortorder' value='{$row['sortorder']}' />\n"
        ."<input type='hidden' name='lid' value='$lid' />\n"
        ."<input type='hidden' name='action' value='modlabelsetanswers' />\n";
        $first=true;
        $sortorderids=''; $codeids='';
        foreach ($lslanguages as $lslanguage)
        {
            $position=0;
            $query = "SELECT * FROM ".db_table_name('labels')." WHERE lid=$lid and language='$lslanguage' ORDER BY sortorder, code";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
            $labelcount = $result->RecordCount();
            $labelsoutput.= "<div class='tab-page'>"
            ."<h2 class='tab'>".getLanguageNameFromCode($lslanguage)."</h2>"
            ."\t<table class='answertable' align='center'>\n"
            ."<thead align='center'>"
            ."<tr>\n"
            ."\t<th align='right' class='settingcaption'>\n"
            .$clang->gT("Code")
            ."\t</th>\n";
            $labelsoutput.="<th align='right' class='settingcaption'>".$clang->gT("Assessment value").'</th>';
            $labelsoutput.="\t<th class='settingcaption'>\n"
            .$clang->gT("Title")
            ."\t</th>\n"
            ."\t<th align='center' class='settingcaption'>\n"
            .$clang->gT("Action")
            ."\t</th>\n"
            ."\t<th align='center' class='settingcaption'>\n"
            .$clang->gT("Order")
            ."\t</th>\n"
            ."</tr></thead>"
            ."<tbody align='center'>";
            $alternate=false;
            while ($row=$result->FetchRow())
            {
                $sortorderids=$sortorderids.' '.$row['language'].'_'.$row['sortorder'];
                if ($first) {$codeids=$codeids.' '.$row['sortorder'];}
                $labelsoutput.= "<tr style='white-space: nowrap;' ";
                if ($alternate==true)
                {
                    $labelsoutput.=' class="highlight" ';
                    $alternate=false;
                }
                else
                {
                    $alternate=true;
                }
                $labelsoutput.="><td align='right'>\n";

                if (!$first)
                {
                    $labelsoutput.= "\t{$row['code']}";
                }
                else
                {
                    $labelsoutput.= "\t<input type='hidden' name='oldcode_{$row['sortorder']}' value=\"{$row['code']}\" />\n";
                    $labelsoutput.= "\t<input type='text' id='code_{$row['sortorder']}' name='code_{$row['sortorder']}' maxlength='5' size='6' value=\"{$row['code']}\" onkeypress=\"if(event.keyCode!=13) {return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyz');}; return catchenter(event,'saveallbtn_$lslanguage');\" />\n";
                }
                 



                $labelsoutput.= "\t</td>\n"
                ."\t<td style='text-align:center;'>\n";
                if ($first)
                {
                    $labelsoutput.= "\t<input type='text' id='assessmentvalue_{$row['sortorder']}' style='text-align: right;' name='assessmentvalue_{$row['sortorder']}' maxlength='5' size='6' value=\"{$row['assessment_value']}\" "
                    ." onkeypress=\"if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$lslanguage').click(); return false;} return goodchars(event,'1234567890-')\" />";
                }
                else
                {
                    $labelsoutput.= $row['assessment_value'];

                }
                $labelsoutput.= "\t</td>\n"
                ."\t<td>\n"
                ."\t<input type='text' name='title_{$row['language']}_{$row['sortorder']}' maxlength='3000' size='80' value=\"".html_escape($row['title'])."\" onkeypress=\"return catchenter(event,'saveallbtn_$lslanguage');\"/>\n"
                . getEditor("editlabel", "title_{$row['language']}_{$row['sortorder']}", "[".$clang->gT("Label:", "js")."](".$row['language'].")",'','','',$action)
                ."\t</td>\n"
                ."\t<td style='text-align:center;'>\n";
                $labelsoutput.= "\t<input type='submit' name='method' value='".$clang->gT("Del")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
                 
                $labelsoutput.= "\t</td>\n"
                ."\t<td>\n";
                if ($position > 0)
                {
                    $labelsoutput.= "\t<input type='submit' name='method' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
                };
                if ($position < $labelcount-1)
                {
                    // Fill the sortorder hiddenfield so we know what field is moved down
                    $labelsoutput.= "\t<input type='submit' name='method' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
                }
                $labelsoutput.= "\t</td></tr>\n";
                $position++;
            }
            if ($labelcount>0)
            {
                $labelsoutput.= "\t<tr><td colspan='5'><center><input type='submit' name='method' value='".$clang->gT("Save Changes")."'  id='saveallbtn_$lslanguage' />"
                ."</center></td></tr>\n";
            }

            $position=sprintf("%05d", $position);
            if (!isset($_SESSION['nextlabelcode'])) $_SESSION['nextlabelcode']='';
            if ($first)
            {   $labelsoutput.= "<tr><td><br /></td></tr><tr><td align='right'>"
            ."<strong>".$clang->gT("New label").":</strong> <input type='text' maxlength='5' name='insertcode' size='6' value='".$_SESSION['nextlabelcode']."' id='code_$maxsortorder' onkeypress=\"if(event.keyCode!=13) {return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyz');}; return catchenter(event,'addnewlabelbtn');\" />\n"
            ."\t</td>\n"
            ."<td style='text-align:center;'>"
            ."<input style='text-align:right;' type='text' maxlength='5' name='insertassessmentvalue' size='6' id='insertassessmentvalue' value='0' "
            ."onkeypress=\"if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('addnewlabelbtn').click(); return false;} return goodchars(event,'1234567890-')\" />"
            ."\t</td>\n"
            ."\t<td>\n"
            ."\t<input type='text' maxlength='3000' name='inserttitle' size='80' onkeypress=\"return catchenter(event,'addnewlabelbtn');\"/>\n"
            . getEditor("addlabel", "inserttitle", "[".$clang->gT("Label:", "js")."](".$lslanguage.")",'','','',$action)
            ."\t</td>\n"
            ."\t<td colspan='2'>\n"
            ."\t<input type='submit' name='method' value='".$clang->gT("Add new label")."' id='addnewlabelbtn' />\n"
            ."<script type='text/javascript'>\n"
            ."<!--\n"
            ."document.getElementById('code_$maxsortorder').focus();\n"
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
            else
            {
                $labelsoutput.= "<tr>\n"
                ."\t<td colspan='4' align='center'>\n"
                ."<span style='font-color:green; font-size:8px; font-weight:bold; font-style: italic;'>"
                .$clang->gT("Note: Inserting new labels must be done on the first language tab.")."</span>\n"
                ."\t</td>\n"
                ."</tr>\n";
            }

            unset($_SESSION['nextlabelcode']);
            $first=false;
            $labelsoutput.="</tbody></table>\n";

            $labelsoutput.=("</div>");
        }
        // Let's close the form for First Languages TABs
        $labelsoutput.= "<input type='hidden' name='sortorderids' value='$sortorderids' />\n";
        $labelsoutput.= "<input type='hidden' name='codeids' value='$codeids' />\n";

        $labelsoutput.= "</form>"; // End First TABs form

        // TAB for resources management
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
        if (!function_exists("zip_open"))
        {
            $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($lid,'label') === false)
        {
            $disabledIfNoResources = " disabled='disabled'";
        }

        $labelsoutput.= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Uploaded Resources Management")."</h2>\n"
        . "\t<form class='form30' enctype='multipart/form-data' id='importlabelresources' name='importlabelresources' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
        . "\t<input type='hidden' name='lid' value='$lid' />\n"
        . "\t<input type='hidden' name='action' value='importlabelresources' />\n"
        . "\t<ul>\n"
        . "\t\t<li><label>&nbsp;</label>\n"
        . "\t\t<input type='button' onclick='window.open(\"$fckeditordir/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php?\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources /></li>\n"
        . "\t\t<li><label>&nbsp;</label>\n"
        . "\t\t<input type='button' onclick='window.open(\"$scriptname?action=exportlabelresources&amp;lid={$lid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources /></li>\n"
        . "\t\t<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
        . "\t\t<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
        . "\t\t<li><label>&nbsp;</label>\n"
        . "\t\t<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
        . "\t\t</ul></form>\n";

        // End TAB Uploaded Resources Management
        $labelsoutput.= "</div>";

        $labelsoutput.= "</div>"; // End Tab pane


    }
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
    global $dbprefix, $connect, $labelsoutput, $databasetype;
    // Get added and deleted languagesid arrays

    if (isset($_POST['languageids']))
    {
        $postlanguageids=sanitize_languagecodeS($_POST['languageids']);
    }

    if (isset($_POST['label_name']))
    {
        $postlabel_name=sanitize_labelname($_POST['label_name']);
    }

    $newlanidarray=explode(" ",trim($postlanguageids));

    $postlanguageids = db_quoteall($postlanguageids,true);
    $postlabel_name = db_quoteall($postlabel_name,true);
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
    $query = "SELECT code,sortorder,assessment_value FROM ".db_table_name('labels')." WHERE lid=".$lid." GROUP BY code,sortorder,assessment_value";
    $result=db_execute_assoc($query);
    if ($result) { while ($row=$result->FetchRow()) {$oldcodesarray[$row['code']]=array('sortorder'=>$row['sortorder'],'assessment_value'=>$row['assessment_value']);} }
    if (isset($oldcodesarray) && count($oldcodesarray) > 0 )
    {
        foreach ($addlangidsarray as $addedlangid)
        {
            foreach ($oldcodesarray as $oldcode => $olddata)
            {
                $sqlvalues[]= " ($lid, '$oldcode', '{$olddata['sortorder']}', '$addedlangid', '{$olddata['assessment_value']}' )";
            }
        }
    }
    if (isset($sqlvalues))
    {
        db_switchIDInsert('labels',true);
        foreach ($sqlvalues as $sqlline)
        {
            $query = "INSERT INTO ".db_table_name('labels')." (lid,code,sortorder,language,assessment_value) VALUES ".($sqlline);
            $result=db_execute_assoc($query);
            if (!$result)
            {
                $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to Copy already defined labels to added languages","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
            }
        }
        db_switchIDInsert('labels',false);
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
    $query = "UPDATE ".db_table_name('labelsets')." SET label_name={$postlabel_name}, languages={$postlanguageids} WHERE lid=$lid";
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
    $result = $connect->Execute($query) or safe_die("Error");
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

    if (isset($_POST['languageids']))
    {
        $postlanguageids=sanitize_languagecodeS($_POST['languageids']);
    }

    if (isset($_POST['label_name']))
    {
        $postlabel_name=sanitize_labelname($_POST['label_name']);
    }

    $postlabel_name = db_quoteall($postlabel_name,true);
    $postlanguageids = db_quoteall($postlanguageids,true);

    $query = "INSERT INTO ".db_table_name('labelsets')." (label_name,languages) VALUES ({$postlabel_name},{$postlanguageids})";
    if (!$result = $connect->Execute($query))
    {
        $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Insert of Label Set failed","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
    }
    else
    {
        return $connect->Insert_ID(db_table_name_nq('labelsets'),"lid");
    }

}



function modlabelsetanswers($lid)
{
    global $dbprefix, $connect, $clang, $labelsoutput, $databasetype, $filterxsshtml,$postsortorder;

    $qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid='$lid'";
    $rslabelset = db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
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
                $_SESSION['nextlabelcode']=getNextCode($_POST['insertcode']);
                $_POST['insertcode'] = db_quoteall($_POST['insertcode'],true);
                // check that the code doesn't exist yet
                $query = "SELECT code FROM ".db_table_name('labels')." WHERE lid='$lid' AND code=".$_POST['insertcode'];
                $result = $connect->Execute($query);
                $codeoccurences=$result->RecordCount();
                if ($codeoccurences == 0)
                {
                    $query = "select max(sortorder) as maxorder from ".db_table_name('labels')." where lid='$lid'";
                    $result = $connect->Execute($query);
                    $newsortorder=sprintf("%05d", $result->fields['maxorder']+1);
                    if ($filterxsshtml)
                    {
                        require_once("../classes/inputfilter/class.inputfilter_clean.php");
                        $myFilter = new InputFilter('','',1,1,1);
                        $_POST['inserttitle']=$myFilter->process($_POST['inserttitle']);
                    }
                    else
                    {
                        $_POST['inserttitle'] = html_entity_decode($_POST['inserttitle'], ENT_QUOTES, "UTF-8");
                    }

                    // Fix bug with FCKEditor saving strange BR types
                    $_POST['inserttitle']=fix_FCKeditor_text($_POST['inserttitle']);
                     
                    $_POST['inserttitle'] = db_quoteall($_POST['inserttitle'],true);
                    $_POST['insertassessmentvalue']=(int)$_POST['insertassessmentvalue'];
                    foreach ($lslanguages as $lslanguage)
                    {
                        db_switchIDInsert('labels',true);
                        $query = "INSERT INTO ".db_table_name('labels')." (lid, code, title, sortorder,language, assessment_value) VALUES ($lid, {$_POST['insertcode']}, {$_POST['inserttitle']}, '$newsortorder','$lslanguage',{$_POST['insertassessmentvalue']})";
                        if (!$result = $connect->Execute($query))
                        {
                            $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to insert label", "js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                        }
                        db_switchIDInsert('labels',false);
                    }
                }
                else
                {
                    $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("This label code is already used in this labelset. Please choose another code or rename the existing one.", "js")."\")\n //-->\n</script>\n";
                }
            }
            break;

            // Save all labels with one button
        case $clang->gT("Save Changes", "unescaped"):
            //Determine autoids by evaluating the hidden field
            $sortorderids=explode(' ', trim($_POST['sortorderids']));
            $codeids=explode(' ', trim($_POST['codeids']));
            $count=0;

            // Quote each code_codeid first
            foreach ($codeids as $codeid)
            {
                $_POST['code_'.$codeid] = db_quoteall($_POST['code_'.$codeid],true);
                if (isset($_POST['oldcode_'.$codeid])) $_POST['oldcode_'.$codeid] = db_quoteall($_POST['oldcode_'.$codeid],true);
                // Get the code values to check for duplicates
                $codevalues[] = $_POST['code_'.$codeid];
            }

            // Check that there is no code duplicate
            if (count(array_unique($codevalues)) == count($codevalues))
            {
                if ($filterxsshtml)
                {
                    require_once("../classes/inputfilter/class.inputfilter_clean.php");
                    $myFilter = new InputFilter('','',1,1,1);
                }

                foreach ($sortorderids as $sortorderid)
                {
                    $langid=substr($sortorderid,0,strrpos($sortorderid,'_'));
                    $orderid=substr($sortorderid,strrpos($sortorderid,'_')+1,20);
                    if ($filterxsshtml)
                    {
                        $_POST['title_'.$sortorderid]=$myFilter->process($_POST['title_'.$sortorderid]);
                    }
                    else
                    {
                        $_POST['title_'.$sortorderid] = html_entity_decode($_POST['title_'.$sortorderid], ENT_QUOTES, "UTF-8");
                    }


                    // Fix bug with FCKEditor saving strange BR types
                    $_POST['title_'.$sortorderid]=fix_FCKeditor_text($_POST['title_'.$sortorderid]);
                    $_POST['title_'.$sortorderid] = db_quoteall($_POST['title_'.$sortorderid],true);

                    $query = "UPDATE ".db_table_name('labels')." SET code=".$_POST['code_'.$codeids[$count]].", title={$_POST['title_'.$sortorderid]}, assessment_value={$_POST['assessmentvalue_'.$codeids[$count]]} WHERE lid=$lid AND sortorder=$orderid AND language='$langid'";

                    if (!$result = $connect->Execute($query))
                    // if update didn't work we assume the label does not exist and insert it
                    {

                        $query = "insert into ".db_table_name('labels')." (code,title,lid,sortorder,language) VALUES (".$_POST['code_'.$codeids[$count]].", {$_POST['title_'.$sortorderid]}, $lid , $orderid , '$langid')";
                        if (!$result = $connect->Execute($query))
                        {
                            $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update label","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                        }
                    }

                    $count++;
                    if ($count>count($codeids)-1) {$count=0;}
                }
                fixorder($lid);
            }
            else
            {
                $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Can't update labels because you are using duplicated codes","js")."\")\n //-->\n</script>\n";
            }

            break;

            // Pressing the Up button
        case $clang->gT("Up", "unescaped"):
            $newsortorder=$postsortorder-1;
            $oldsortorder=$postsortorder;
            $cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=-1 WHERE lid=$lid AND sortorder=$newsortorder";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            $cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=$newsortorder WHERE lid=$lid AND sortorder=$oldsortorder";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            $cdquery = "UPDATE ".db_table_name('labels')." SET sortorder='$oldsortorder' WHERE lid=$lid AND sortorder=-1";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            break;

            // Pressing the Down button
        case $clang->gT("Dn", "unescaped"):
            $newsortorder=$postsortorder+1;
            $oldsortorder=$postsortorder;
            $cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=-1 WHERE lid=$lid AND sortorder='$newsortorder'";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            $cdquery = "UPDATE ".db_table_name('labels')." SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder=$oldsortorder";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            $cdquery = "UPDATE ".db_table_name('labels')." SET sortorder=$oldsortorder WHERE lid=$lid AND sortorder=-1";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            break;

            // Delete Button
        case $clang->gT("Del", "unescaped"):
            $query = "DELETE FROM ".db_table_name('labels')." WHERE lid=$lid AND sortorder='{$postsortorder}'";
            if (!$result = $connect->Execute($query))
            {
                $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete label","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
            }
            fixorder($lid);
            break;
    }
}


function fixorder($lid) //Function rewrites the sortorder for a group of answers
{
    global $dbprefix, $connect, $labelsoutput;
    $qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
    $rslabelset = db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
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
            $result2=$connect->Execute($query2, array ($row[0], $row[1], $row[2])) or safe_die ("Couldn't update sortorder<br />$query2<br />".$connect->ErrorMsg());
            $position++;
        }
    }
}




?>
