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

    $js_admin_includes[]='scripts/labels.js';
    if (isset($_POST['sortorder'])) {$postsortorder=sanitize_int($_POST['sortorder']);}

    if (!isset($action)) {$action=returnglobal('action');}
    if (!isset($lid)) {$lid=returnglobal('lid');}

    //DO DATABASE UPDATESTUFF
    if ($action == "updateset") {updateset($lid);}
    if ($action == "insertlabelset") {$lid=insertlabelset();}
    if (($action == "modlabelsetanswers")||($action == "ajaxmodlabelsetanswers")) {modlabelsetanswers($lid);}
    if ($action == "deletelabelset") {if (deletelabelset($lid)) {$lid=0;}}
    if ($action == "importlabels")
    {
        include("importlabel.php");
    }
    if ($action == "importlabelresources")
    {
        include("import_resources_zip.php");
        if (isset($importlabelresourcesoutput)) {$labelsoutput.= $importlabelresourcesoutput;}
        return;
    }


    $labelsoutput= "<div class='menubar'>\n"
    ."\t\t<div class='menubar-title ui-widget-header'>\n"
    ."\t\t<strong>".$clang->gT("Label Sets Administration")."</strong>\n"
    ."\t\t</div>\n"
    ."\t<div class='menubar-main'>\n"
    ."\t<div class='menubar-left'>\n"
    ."\t<a href='$scriptname' title=\"".$clang->gTview("Return to survey administration")."\" >"
    ."<img name='Administration' src='$imageurl/home.png' align='left' alt='".$clang->gT("Return to survey administration")."' /></a>"
    ."\t<img src='$imageurl/blank.gif' width='11' height='20' align='left' alt='' />\n"
    ."\t<img src='$imageurl/seperator.gif' align='left' alt='' />\n"
    ."\t<img src='$imageurl/blank.gif' width='76' align='left' height='20' alt='' />\n"
    ."\t<img src='$imageurl/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
    ."\t<a href='admin.php?action=labels&amp;subaction=exportmulti' title=\"".$clang->gTview("Export Label Set")."\" >"
    ."<img src='$imageurl/dumplabelmulti.png' alt='".$clang->gT("Export multiple label sets")."' align='left' /></a>"
    ."\t</div>\n"
    ."\t<div class='menubar-right'>\n"
    ."\t<img src='$imageurl/blank.gif' width='5' height='20' alt='' />\n"
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
    ." title=\"".$clang->gTview("Create or import new label set(s)")."\">"
    ."<img src='$imageurl/add.png' name='AddLabel' alt='". $clang->gT("Create or import new label set(s)")."' /></a>\n"
    ."\t<img src='$imageurl/seperator.gif'  alt='' />\n"
    ."\t<img src='$imageurl/blank.gif' width='5' height='20' alt='' />\n"
    //Logout button
    ."<a href=\"#\" onclick=\"window.open('$scriptname?action=logout', '_top')\""
    ." title=\"".$clang->gTview("Logout")."\" >"
    ."<img src='$imageurl/logout.png' name='Logout' alt='".$clang->gT("Logout")."' /></a>"
    //Show help
    ."<a href=\"#\" onclick=\"showhelp('show')\" title=\"".$clang->gTview("Show Help")."\">"
    ."<img src='$imageurl/showhelp.png' name='ShowHelp' "
    ."alt='". $clang->gT("Show Help")."' /></a>"
    ."\t\t</div>\n"
    ."\t</div>\n"
    ."</div>\n";

    if (isset($importlabeloutput)) {
        $labelsoutput.= $importlabeloutput;
        return;
    }


    if ($subaction == "exportmulti")
    {

           $labelsoutput.="<script type='text/javascript'>\n"
            ."<!--\n"
            ."var strSelectLabelset='".$clang->gT('You have to select at least one label set.','js')."';\n"
            ."//-->\n"
            ."</script>\n";

        $labelsoutput .="<div class='header ui-widget-header'>".$clang->gT('Export multiple label sets')."</div>"
        ."<form method='post' id='exportlabelset' class='form30' action='admin.php'><ul>"
        ."<li><label for='labelsets'>".$clang->gT('Please choose the label sets you want to export:')."<br />".$clang->gT('(Select multiple label sets by using the Ctrl key)')."</label>"
        ."<select id='labelsets' multiple='multiple' name='lids[]' size='20'>\n";
        $labelsets=getlabelsets();
        if (count($labelsets)>0)
        {
            foreach ($labelsets as $lb)
            {
                $labelsoutput.="<option value='{$lb[0]}'>{$lb[0]}: {$lb[1]}</option>\n";
            }
        }

        $labelsoutput.= "\t</select></li>\n"
        ."</ul><p><input type='submit' id='btnDumpLabelSets' value='".$clang->gT('Export selected label sets')."' />"
        ."<input type='hidden' name='action' value='dumplabel' />"
        ."</form>";


    }


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
        ."<input type='image' src='$imageurl/close.gif' align='right' "
        ."onclick=\"window.open('admin.php?action=labels&amp;lid=$lid', '_top')\" />\n";
        if ($action == "newlabelset") {$labelsoutput.= $clang->gT("Create or import new label set(s)"); $langids=$_SESSION['adminlang']; $tabitem=$clang->gT("Create New Label Set");}
        else {$labelsoutput.= $clang->gT("Edit Label Set"); $tabitem=$clang->gT("Edit label set");}
        $langidsarray=explode(" ",trim($langids)); //Make an array of it
        $labelsoutput.= "\n\t</div>\n";

        if (isset($row['lid'])) { $panecookie=$row['lid'];} else  {$panecookie='new';}

        $tab_title[0] = $tabitem;
        $tab_content[0] = "<form method='post' class='form30' id='labelsetform' action='admin.php' onsubmit=\"return isEmpty(document.getElementById('label_name'), '".$clang->gT("Error: You have to enter a name for this label set.","js")."')\">\n";

        $tab_content[0].= "<ul'>\n"
        ."<li><label for='languageids'>".$clang->gT("Set name:")."</label>\n"
        ."\t<input type='hidden' name='languageids' id='languageids' value='$langids' />"
        ."\t<input type='text' id='label_name' name='label_name' maxlength='100' size='50' value='";
        if (isset($lbname)) {$tab_content[0].= $lbname;}
        $tab_content[0].= "' />\n"
        ."</li>\n"
        // Additional languages listbox
        . "\t<li><label>".$clang->gT("Languages:")."</label>\n"
        . "<table><tr><td align='left'><select multiple='multiple' style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>";
        foreach ($langidsarray as $langid)
        {
            $tab_content[0].=  "\t<option id='".$langid."' value='".$langid."'";
            $tab_content[0].= ">".getLanguageNameFromCode($langid,false)."</option>\n";
        }

        //  Add/Remove Buttons
        $tab_content[0].= "</select></td>"
        . "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(1,'".$clang->gT("You cannot remove this item since you need at least one language in a labelset.", "js")."')\" id=\"RemoveBtn\"  /></td>\n"

        // Available languages listbox
        . "<td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>";
        foreach (getLanguageData() as  $langkey=>$langname)
        {
            if (in_array($langkey,$langidsarray)==false)  // base languag must not be shown here
            {
                $tab_content[0].= "\t<option id='".$langkey."' value='".$langkey."'";
                $tab_content[0].= ">".$langname['description']."</option>\n";
            }
        }

        $tab_content[0].= "\t</select></td>"
        ." </tr></table></li></ul>\n"
        ."<p><input type='submit' value='";
        if ($action == "newlabelset") {$tab_content[0].= $clang->gT("Save");}
        else {$tab_content[0].= $clang->gT("Update");}
       $tab_content[0].= "' />\n"
        ."<input type='hidden' name='action' value='";
        if ($action == "newlabelset") {$tab_content[0].= "insertlabelset";}
        else {$tab_content[0].= "updateset";}
        $tab_content[0].= "' />\n";

        if ($action == "editlabelset") {
            $tab_content[0].= "<input type='hidden' name='lid' value='$lblid' />\n";
        }

        $tab_content[0].= "</form>\n";


        if ($action == "newlabelset"){
            $tab_title[1] = $clang->gT("Import label set(s)");
            $tab_content[1] = "<form enctype='multipart/form-data' id='importlabels' name='importlabels' action='admin.php' method='post'>\n"
            ."<div class='header ui-widget-header'>\n"
            .$clang->gT("Import label set(s)")."\n"
            ."</div><ul>\n"
            ."<li><label for='the_file'>"
            .$clang->gT("Select label set file (*.lsl,*.csv):")."</label>\n"
            ."<input id='the_file' name='the_file' type='file' size='35' />"
            ."</li>\n"
            ."<li><label for='checkforduplicates'>"
            .$clang->gT("Don't import if label set already exists:")."</label>\n"
            ."<input name='checkforduplicates' id='checkforduplicates' type='checkbox' checked='checked' />\n"
            ."</li>"
            ."<li><label for='translinksfields'>"
            .$clang->gT("Convert resources links?")."</label>\n"
            ."<input name='translinksfields' id='translinksfields' type='checkbox' checked='checked' />\n"
            ."</li></ul>\n"
            ."<p><input type='submit' value='".$clang->gT("Import label set(s)")."' />\n"
            ."<input type='hidden' name='action' value='importlabels' />\n"
            ."</form></div>\n";
        }

        $labelsoutput .= "<div id='tabs'><ul>";
        foreach($tab_title as $i=>$eachtitle){
            $labelsoutput .= "<li><a href='#neweditlblset$i'>$eachtitle</a></li>";
        }
        $labelsoutput .= "</ul>";
        foreach($tab_content as $i=>$eachcontent){
            $labelsoutput .= "<div id='neweditlblset$i'>$eachcontent</div>";
        }
        $labelsoutput .= "</div>";

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
            ."<div class='menubar-title ui-widget-header'>\n"
            ."\t<strong>".$clang->gT("Label Set").":</strong> {$row['label_name']}\n"
            ."</div>\n"
            ."<div class='menubar-main'>\n"
            ."\t<div class='menubar-left'>\n"
            ."\t<img src='$imageurl/blank.gif' width='40' height='20' border='0' hspace='0' align='left' alt='' />\n"
            ."\t<img src='$imageurl/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
            ."\t<a href='admin.php?action=editlabelset&amp;lid=$lid' title=\"".$clang->gTview("Edit label set")."\" >" .
			"<img name='EditLabelsetButton' src='$imageurl/edit.png' alt='".$clang->gT("Edit label set")."' align='left'  /></a>"
			."\t<a href='#' title='".$clang->gTview("Delete label set")."' onclick=\"if (confirm('".$clang->gT("Do you really want to delete this label set?","js")."')) {".get2post("admin.php?action=deletelabelset&amp;lid=$lid")."}\" >"
			."<img src='$imageurl/delete.png' border='0' alt='".$clang->gT("Delete label set")."' align='left' /></a>\n"
			."\t<img src='$imageurl/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
			."\t<a href='admin.php?action=dumplabel&amp;lid=$lid' title=\"".$clang->gTview("Export this label set")."\" >" .
					"<img src='$imageurl/dumplabel.png' alt='".$clang->gT("Export this label set")."' align='left' /></a>"
					."\t</div>\n"
					."\t<div class='menubar-right'>\n"
					."\t<input type='image' src='$imageurl/close.gif' title='".$clang->gT("Close Window")."'"
					."onclick=\"window.open('admin.php?action=labels', '_top')\" />\n"
					."\t</div>\n"
					."\t</div>\n"
					."\t</div>\n";
					$labelsoutput .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
        }


        //LABEL ANSWERS  - SHOW THE MASK FOR EDITING THE LABELS

		$js_admin_includes[]='scripts/updateset.js';

        $qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
        $rslabelset = db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
        $rwlabelset=$rslabelset->FetchRow();
        $lslanguages=explode(" ", trim($rwlabelset['languages']));

        $labelsoutput.= PrepareEditorScript();

        $maxquery = "SELECT max(sortorder) as maxsortorder FROM ".db_table_name('labels')." WHERE lid=$lid and language='{$lslanguages[0]}'";
        $maxresult = db_execute_assoc($maxquery) or safe_die($connect->ErrorMsg());
        $msorow=$maxresult->FetchRow();
        $maxsortorder=$msorow['maxsortorder']+1;

        // labels table
        $labelsoutput.= "\t<div class='header ui-widget-header'>".$clang->gT("Labels")."\t</div>\n";
        $labelsoutput.= "<form method='post' id='mainform' action='admin.php' onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate label codes.",'js')."','".$clang->gT("Error: 'other' is a reserved keyword.",'js')."');\">\n"
        ."<input type='hidden' name='sortorder' value='{$row['sortorder']}' />\n"
        ."<input type='hidden' name='lid' value='$lid' />\n"
        ."<input type='hidden' name='action' value='modlabelsetanswers' />\n";
        $first=true;
        $sortorderids=''; $codeids='';
        $i = 0;
        foreach ($lslanguages as $lslanguage)
        {
            $position=0;
            $query = "SELECT * FROM ".db_table_name('labels')." WHERE lid=$lid and language='$lslanguage' ORDER BY sortorder, code";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
            $labelcount = $result->RecordCount();
            $tab_title[$i] = getLanguageNameFromCode($lslanguage,false);

            $tab_content[$i] = "
                <input type='hidden' class='lslanguage' value='{$lslanguage}'>
                <table class='answertable' align='center'>
                    <thead align='center'>
                        <tr>";

            if ($first)
                $tab_content[$i] .= "<th>&nbsp;</th>";

            $tab_content[$i] .= "<th class='settingcaption'>{$clang->gT("Code")}</th>
                            <th class='settingcaption'>{$clang->gT("Assessment value")}</th>
                            <th class='settingcaption'>{$clang->gT("Title")}</th>";

            if ($first)
                $tab_content[$i] .= "<th class='settingcaption'>{$clang->gT("Action")}</th>";

            $tab_content[$i] .= "</tr>
                    </thead>
                    <tbody align='center'>
            ";

            $alternate=false;
            while ($row=$result->FetchRow())
            {
                $sortorderids=$sortorderids.' '.$row['language'].'_'.$row['sortorder'];
                if ($first) {$codeids=$codeids.' '.$row['sortorder'];}

                $tab_content[$i].= "<tr style='white-space: nowrap;' name='{$row['sortorder']}'";

                if ($alternate==true)
                    $tab_content[$i].=' class = "highlight" ';
                else
                    $alternate=true;

                $tab_content[$i] .= ">";
                if (!$first)
                    $tab_content[$i].= "<td>{$row['code']}</td><td>{$row['assessment_value']}</td>";
                else
                    $tab_content[$i].= "
                        <td><img src='$imageurl/handle.png' /></td>
                        <td>
                            <input type='hidden' class='hiddencode' value='{$row['code']}' />
                            <input type='text'  class='codeval'id='code_{$row['sortorder']}' name='code_{$row['sortorder']}' maxlength='5'
                                size='6' value='{$row['code']}'/>
                        </td>

                        <td>
                            <input type='text' class='assessmentval' id='assessmentvalue_{$row['sortorder']}' style='text-align: right;' name='assessmentvalue_{$row['sortorder']}' maxlength='5' size='6' value='{$row['assessment_value']}' />
                        </td>
                    ";

                $tab_content[$i].= "
                     <td>
                        <input type='text' name='title_{$row['language']}_{$row['sortorder']}' maxlength='3000' size='80' value=\"".html_escape($row['title'])."\" />"
                        .getEditor("editlabel", "title_{$row['language']}_{$row['sortorder']}", "[".$clang->gT("Label:", "js")."](".$row['language'].")",'','','',$action)
                    ."</td>";

                 if ($first)
                     $tab_content[$i] .= "
                     <td style='text-align:center;'>
                     <img src='$imageurl/addanswer.png' class='btnaddanswer' /><img src='$imageurl/deleteanswer.png' class='btndelanswer' />
                     </td>
                     </tr>";

                $position++;
            }

            $tab_content[$i] .= "</tbody></table>";

            $tab_content[$i] .= "<button class='btnquickadd' id='btnquickadd' type='button'>".$clang->gT('Quick add...')."</button>";

            $tab_content[$i].= "<p><input type='submit' name='method' value='".$clang->gT("Save Changes")."'  id='saveallbtn_$lslanguage' /></p>";


            $first=false;

            $i++;
        }

        $labelsoutput .= "<div id='tabs'><ul>";
        foreach($tab_title as $i=>$eachtitle){
            $labelsoutput .= "<li><a href='#neweditlblset$i'>$eachtitle</a></li>";
        }
        $labelsoutput .= "<li><a href='#up_resmgmt'>".$clang->gT("Uploaded Resources Management")."</a></li>";
        $labelsoutput .= "</ul>";

        foreach($tab_content as $i=>$eachcontent){
            $labelsoutput .= "<div id='neweditlblset$i'>$eachcontent</div>";
        }
        $labelsoutput .="</form>";


        $disabledIfNoResources = '';
        if (hasResources($lid,'label') === false)
        {
            $disabledIfNoResources = " disabled='disabled'";
        }

        // TAB for resources management
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
        if (!function_exists("zip_open"))
        {
            $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
        }

        $labelsoutput.="<div id='up_resmgmt'><div>\t<form class='form30' enctype='multipart/form-data' id='importlabelresources' name='importlabelresources' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
        . "\t<input type='hidden' name='lid' value='$lid' />\n"
        . "\t<input type='hidden' name='action' value='importlabelresources' />\n"
        . "\t<ul style='list-style-type:none; text-align:center'>\n"
        . "\t\t<li><label>&nbsp;</label>\n"
        . "\t\t<input type='button' $disabledIfNoResources onclick='window.open(\"$sCKEditorURL/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php?\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\"  /></li>\n"
        . "\t\t<li><label>&nbsp;</label>\n"
        . "\t\t<input type='button' $disabledIfNoResources onclick='window.open(\"$scriptname?action=exportlabelresources&amp;lid={$lid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\"  /></li>\n"
        . "\t\t<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
        . "\t\t<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
        . "\t\t<li><label>&nbsp;</label>\n"
        . "\t\t<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
        . "\t\t</ul></form></div></div>\n";

        $labelsoutput .= "</div>";

        $labelsoutput .= "<div id='quickadd' name='{$clang->gT('Quick add')}'style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your labels:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or space.')."' rows='30' style='width:570px;'></textarea>
                      <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   </div> ";


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

    // Update the label set itself
    $query = "UPDATE ".db_table_name('labelsets')." SET label_name={$postlabel_name}, languages={$postlanguageids} WHERE lid=$lid";
    if (!$result = $connect->Execute($query))
    {
        $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Update of Label Set failed","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
    }
}


/**
* Deletes a label set alog with its labels
*
* @param mixed $lid Label ID
* @return boolean Returns always true
*/
function deletelabelset($lid)
{
    global $connect;
    $query = "DELETE FROM ".db_table_name('labels')." WHERE lid=$lid";
    $result = $connect->Execute($query);
    $query = "DELETE FROM ".db_table_name('labelsets')." WHERE lid=$lid";
    $result = $connect->Execute($query);
    return true;
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
        safe_die("Inserting the label set failed:<br />".$query."<br />".$connect->ErrorMsg());
    }
    else
    {
        return $connect->Insert_ID(db_table_name_nq('labelsets'),"lid");
    }

}

function modlabelsetanswers($lid)
{

    global $dbprefix, $connect, $clang, $labelsoutput, $databasetype, $filterxsshtml,$postsortorder;
    $ajax = false;
    if ($_POST['ajax'] == "1"){
        $ajax = true;
    }
    if (!isset($_POST['method'])) {
        $_POST['method'] = $clang->gT("Save");
    }

    $data = json_decode(stripslashes($_POST['dataToSend']));

    if ($ajax){
        $lid = insertlabelset();
    }


    if (count(array_unique($data->{'codelist'})) == count($data->{'codelist'}))
    {
        if ($filterxsshtml)
        {
            require_once("../classes/inputfilter/class.inputfilter_clean.php");
            $myFilter = new InputFilter('','',1,1,1);
        }

        $query = "DELETE FROM ".db_table_name('labels')."  WHERE `lid` = '$lid'";

        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());

        foreach($data->{'codelist'} as $index=>$codeid){

            $codeObj = $data->$codeid;


            $actualcode = db_quoteall($codeObj->{'code'},true);
            $codeid = db_quoteall($codeid,true);

            $assessmentvalue = (int)($codeObj->{'assessmentvalue'});

            foreach($data->{'langs'} as $lang){

                $strTemp = 'text_'.$lang;
                $title = $codeObj->$strTemp;

                if ($filterxsshtml)
                    $title=$myFilter->process($title);
                else
                    $title = html_entity_decode($title, ENT_QUOTES, "UTF-8");


                // Fix bug with FCKEditor saving strange BR types
                $title =fix_FCKeditor_text($title);
                $title = db_quoteall($title,true);


                $sort_order = db_quoteall($index);
                $lang = db_quoteall($lang);

                $query = "INSERT INTO ".db_table_name('labels')." (`lid`,`code`,`title`,`sortorder`, `assessment_value`, `language`)
                    VALUES('$lid',$actualcode,$title,$sort_order,$assessmentvalue,$lang)";

                $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
            }

        }

    $_SESSION['flashmessage']=$clang->gT("Labels sucessfully updated");

    }
    else
    {
        $labelsoutput.= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Can't update labels because you are using duplicated codes","js")."\")\n //-->\n</script>\n";
    }

    if ($ajax){ die(); }

}

/**
* Function rewrites the sortorder for a label set
*
* @param mixed $lid Label set ID
*/
function fixorder($lid) {
    global $dbprefix, $connect, $labelsoutput;
    $qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
    $rslabelset = db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
    $rwlabelset=$rslabelset->FetchRow();
    $lslanguages=explode(" ", trim($rwlabelset['languages']));
    foreach ($lslanguages as $lslanguage)
    {
        $query = "SELECT lid, code, title, sortorder FROM ".db_table_name('labels')." WHERE lid=? and language=? ORDER BY sortorder, code";
        $result = db_execute_num($query, array($lid,$lslanguage)) or safe_die("Can't read labels table: $query // (lid=$lid, language=$lslanguage) ".$connect->ErrorMsg());
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
