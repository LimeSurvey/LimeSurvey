<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
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

require_once("config.php");

if (!isset($action)) {$action=returnglobal('action');}
if (!isset($lid)) {$lid=returnglobal('lid');}

sendcacheheaders();

//DO DATABASE UPDATESTUFF
if ($action == "updateset") {updateset($lid);}
if ($action == "insertset") {$lid=insertset();}
if ($action == "modanswers") {modanswers($lid);}
if ($action == "delset") {if (delset($lid)) {$lid=0;}}

echo $htmlheader;

if ($action == "importlabels")
    {
    include("importlabel.php");
    exit;
    }

echo "<script type='text/javascript'>\n"
    ."<!--\n"
    ."\tfunction showhelp(action)\n"
    ."\t\t{\n"
    ."\t\tvar name='help';\n"
    ."\t\tif (action == \"hide\")\n"
    ."\t\t\t{\n"
    ."\t\t\tdocument.getElementById(name).style.display='none';\n"
    ."\t\t\t}\n"
    ."\t\telse if (action == \"show\")\n"
    ."\t\t\t{\n"
    ."\t\t\tdocument.getElementById(name).style.display='';\n"
    ."\t\t\t}\n"
    ."\t\t}\n"
    ."-->\n"
    ."</script>\n";
echo "<table width='100%' border='0' cellpadding='0' cellspacing='0' >\n"
    ."\t<tr>\n"
    ."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n"
    ."\t\t\t<table cellspacing='1'><tr><td></td></tr></table>\n"
    ."\t\t\t<table width='99%' align='center' style='border: 1px solid #555555' "
    ."cellpadding='1' cellspacing='0'>\n"
    ."\t\t\t\t<tr bgcolor='#555555'><td height='4' colspan='2'>"
    ."<font size='1' face='verdana' color='white'><strong>"
    ._LABELCONTROL."</strong></font></td></tr>\n"
    ."\t\t\t\t<tr bgcolor='#999999'>\n"
    ."\t\t\t\t\t<td>\n"
    ."\t\t\t\t\t<input type='image' src='$imagefiles/home.gif' title='"
    ._B_ADMIN_BT."' alt='"._B_ADMIN_BT."' align='left' "
    ."onClick=\"window.open('$scriptname', '_top')\">\n"
    ."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='11' height='20' border='0' hspace='0' align='left' alt=''>\n"
    ."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
    ."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='60' height='20' border='0' hspace='0' align='left' alt=''>\n"
    ."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
    ."\t\t\t\t\t</td>\n"
    ."\t\t\t\t\t<td align='right' width='620'>\n"
    ."\t\t\t\t\t<input type='image' src='$imagefiles/showhelp.gif' title='"
    ._A_HELP_BT."' alt='"._A_HELP_BT."' align='right'  "
    ."onClick=\"showhelp('show')\">\n"
    ."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='42' height='20' align='right' hspace='0' border='0'  alt=''>\n"
    ."\t\t\t\t\t<img src='$imagefiles/seperator.gif' align='right' hspace='0' border='0' alt=''>\n"
    ."\t\t\t\t\t<input type='image' src='$imagefiles/add.gif' align='right' title='"
    ._L_ADDSET_BT."' alt='"._L_ADDSET_BT."' onClick=\"window.open('labels.php?action=newset', '_top')\">\n"
    ."\t\t\t\t\t$setfont<font size='1'><strong>"
    ._LABELSETS.":</strong> "
    ."\t\t\t\t\t<select style='font-size: 9; font-family: verdana; font-color: #333333; background: SILVER; width: 160' "
    ."onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
$labelsets=getlabelsets();
if (count($labelsets)>0)
    {
    foreach ($labelsets as $lb)
        {
        echo "\t\t\t\t\t\t<option value='?lid={$lb[0]}'";
        if ($lb[0] == $lid) {echo " selected";}
        echo ">{$lb[1]}</option>\n";
        }
    }
echo "\t\t\t\t\t\t<option value=''";
if (!isset($lid) || $lid<1) {echo " selected";}
echo ">"._AD_CHOOSE."</option>\n";

echo "\t\t\t\t\t</select></font></font>\n"
    ."\t\t\t\t\t</td>\n"
    ."\t\t\t\t</tr>\n"
    ."\t\t\t</table>\n"
    ."\t\t<table ><tr><td></td></tr></table>\n";

//NEW SET
if ($action == "newset" || $action == "editset")
    {
    if ($action == "editset")
        {
        $query = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
        $result=mysql_query($query);
        while ($row=mysql_fetch_array($result)) {$lbname=$row['label_name']; $lblid=$row['lid'];}
        }
    echo "\t\t<table width='100%' bgcolor='#DDDDDD'>\n"
        ."\t\t\t<tr bgcolor='black'>\n"
        ."\t\t\t\t<td colspan='2' align='center'>$setfont<font color='white'><strong>\n"
        ."\t\t\t\t<input type='image' src='$imagefiles/close.gif' align='right' "
        ."onClick=\"window.open('labels.php?lid=$lid', '_top')\">\n";
    if ($action == "newset") {echo _LB_NEWSET;}
    else {echo _LB_EDITSET;}
    echo "\t\t\t\t</font></font></strong></td>\n"
        ."\t\t\t</tr>\n"
        ."\t\t<form method='post' action='labels.php'>\n"
        ."\t\t\t<tr>\n"
        ."\t\t\t\t<td align='right' width='15%'>\n"
        ."\t\t\t\t\t$setfont<strong>"._LL_NAME.":</strong></font>"
        ."\t\t\t\t</td>\n"
        ."\t\t\t\t<td>\n"
        ."\t\t\t\t\t<input type='text' $slstyle name='label_name' value='";
    if (isset($lbname)) {echo $lbname;} 
    echo "'>\n"
        ."\t\t\t\t</td>\n"
        ."\t\t\t</tr>\n"
        ."\t\t\t<tr>\n"
        ."\t\t\t\t<td></td>\n"
        ."\t\t\t\t<td>\n"
        ."\t\t\t\t<input $btstyle type='submit' value='";
    if ($action == "newset") {echo _ADD;}
    else {echo _UPDATE;}
    echo "'>\n"
        ."\t\t\t\t</td>\n"
        ."\t\t\t</tr>\n"
        ."\t\t<input type='hidden' name='action' value='";
    if ($action == "newset") {echo "insertset";}
    else {echo "updateset";}
    echo "'>\n";
    if ($action == "editset") {echo "\t\t<input type='hidden' name='lid' value='$lblid'>\n";}
    echo "\t\t</form>\n";
    if ($action == "newset")
        {
        echo "\t\t\t<tr><td colspan='2' align='center'>\n"
            ."\t\t\t\t$setfont<strong>OR</strong></font>\n"
            ."\t\t\t</td></tr>\n"
            ."\t\t\t<tr bgcolor='black'>\n"
            ."\t\t\t\t<td colspan='2' align='center'>$setfont<font color='white'><strong>\n"
            ."\t\t\t\t"._IMPORTLABEL."\n"
            ."\t\t\t\t</font></font></strong></td>\n"
            ."\t\t\t</tr>\n"
            ."\t\t\t<tr>\n"
            ."\t\t\t<form enctype='multipart/form-data' name='importlabels' action='labels.php' "
            ."method='post'>\n"
            ."\t\t\t\t<td align='right'>$setfont<strong>"
            ._SL_SELSQL."</strong></font></td>\n"
            ."\t\t<td><input $btstyle name=\"the_file\" type=\"file\" size=\"35\">"
            ."</td></tr>\n"
            ."\t<tr><td></td><td><input type='submit' $btstyle value='"._IMPORTLABEL."'></TD>\n"
            ."\t<input type='hidden' name='action' value='importlabels'>\n"
            ."\t</tr></form>\n";
        
        }
    echo "\t\t</table>\n";
    }
//SET SELECTED
if (isset($lid) && ($action != "editset") && $lid)
    {
    //CHECK TO SEE IF ANY ACTIVE SURVEYS ARE USING THIS LABELSET (Don't let it be changed if this is the case)
    $query = "SELECT {$dbprefix}surveys.short_title FROM {$dbprefix}questions, {$dbprefix}surveys WHERE {$dbprefix}questions.sid={$dbprefix}surveys.sid AND {$dbprefix}questions.lid=$lid AND {$dbprefix}surveys.active='Y'";
    $result = mysql_query($query);
    $activeuse=mysql_num_rows($result);
    while ($row=mysql_fetch_array($result)) {$activesurveys[]=$row['short_title'];}
    //NOW ALSO COUNT UP HOW MANY QUESTIONS ARE USING THIS LABELSET, TO GIVE WARNING ABOUT CHANGES
    $query = "SELECT * FROM {$dbprefix}questions WHERE type IN ('F','H') AND lid=$lid";
    $result = mysql_query($query);
    $totaluse=mysql_num_rows($result);
    while($row=mysql_fetch_array($result))
        {
        $qidarray[]=array("url"=>"$scriptname?sid=".$row['sid']."&amp;gid=".$row['gid']."&amp;qid=".$row['qid'], "title"=>"QID: ".$row['qid']);
        } // while
    //NOW GET THE ANSWERS AND DISPLAY THEM
    $query = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
    $result = mysql_query($query);
    while ($row=mysql_fetch_array($result)) 
        {
        echo "\t\t\t<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
            ."\t\t\t\t<tr bgcolor='#555555'><td height='4' colspan='2'>"
            ."<font size='1' face='verdana' color='white'><strong>"
            ._LABELSET.":</strong> {$row['label_name']}</td></tr>\n"
            ."\t\t\t\t<tr bgcolor='#999999'>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
            ._CLOSEWIN."' align='right' border='0' hspace='0' "
            ."onClick=\"window.open('labels.php', '_top')\">\n"
            ."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='left'>\n"
            ."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left'>\n"
            ."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='60' height='20' border='0' hspace='0' align='left'>\n"
            ."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left'>\n"
            ."\t\t\t\t\t<input type='image' src='$imagefiles/edit.gif' title='"
            ._L_EDIT_BT."' align='left' border='0' hspace='0' "
            ."onclick=\"window.open('labels.php?action=editset&lid=$lid', '_top')\">\n"
            ."\t\t\t\t\t<a href='labels.php?action=delset&lid=$lid'>"
            ."<img src='$imagefiles/delete.gif' title='"
            ._L_DEL_BT."' align='left' border='0' hspace='0' "
            ."onClick=\"return confirm('Are you sure?')\"></a>\n"
            ."\t\t\t\t\t<input type='image' src='$imagefiles/export.gif' title='"
            ._EXPORTLABEL."' align='left' border='0' hspace='0' "
            ."onClick=\"window.open('dumplabel.php?lid=$lid', '_top')\">\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t</tr>\n"
            ."\t\t\t</table>\n";
        }
    //LABEL ANSWERS
    $query = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, code";
    $result = mysql_query($query) or die(mysql_error());
    $labelcount = mysql_num_rows($result);
    echo "\t\t<table height='1'><tr><td></td></tr></table>\n"
        ."\t\t\t<table width='99%' align='center' style='border: solid; border-width: 1px; border-color: #555555' cellspacing='0'>\n"
        ."\t\t\t\t<tr bgcolor='#555555' height='4'>\n"
        ."\t\t\t\t\t<td colspan='4'><strong><font size='1' face='verdana' color='white'>\n"
        ._LABELANS
        ."\t\t\t\t\t</strong></font></td>\n"
        ."\t\t\t\t</tr>\n"
        ."\t\t\t\t<tr bgcolor='#BBBBBB'>\n"
        ."\t\t\t\t\t<td><strong><font size='1' face='verdana'>\n"
        ._LL_CODE
        ."\t\t\t\t\t</strong></font></td>\n"
        ."\t\t\t\t\t<td><strong><font size='1' face='verdana'>\n"
        ._LL_ANSWER
        ."\t\t\t\t\t</strong></font></td>\n"
        ."\t\t\t\t\t<td><strong><font size='1' face='verdana'>\n"
        ._LL_ACTION
        ."\t\t\t\t\t</strong></font></td>\n"
        ."\t\t\t\t\t<td><strong><font size='1' face='verdana'>\n"
         ._LL_SORTORDER
        ."\t\t\t\t\t</strong></font></td>\n"
        ."\t\t\t\t</tr>\n";
    $position=0;
    while ($row=mysql_fetch_array($result))
        {
        echo "\t\t\t\t<tr>\n"
            ."\t\t\t\t<form method='post' action='labels.php'>\n"
            ."\t\t\t\t\t<td>\n";
        if ($activeuse > 0)
            {
            echo "\t\t\t\t\t$setfont{$row['code']}</font>"
                ."<input type='hidden' name='code' value=\"{$row['code']}\">\n";
            }
        else
            {
            echo "\t\t\t\t\t<input type='text' $slstyle name='code' size='5' value=\"{$row['code']}\">\n";
            }
        echo "\t\t\t\t\t</td>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t<input type='text' $slstyle name='title' size='35' value=\"{$row['title']}\">\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_SAVE."' />\n";
        if ($activeuse == 0)
            {
            echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_DEL."' />\n";
            }
        echo "\t\t\t\t\t</td>\n"
            ."\t\t\t\t\t<td>\n";
        if ($position > 0)
            {
            echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_UP."' />\n";
            }
        else {echo "<img src='$imagefiles/blank.gif' width='21' height='5' align='left'></font>";}
        if ($position < $labelcount-1)
            {
            echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_DN."' />\n";
            }
        echo "\t\t\t\t\t</td>\n"
            ."\t\t\t\t</tr>\n"
            ."\t\t\t\t<input type='hidden' name='sortorder' value='{$row['sortorder']}'>\n"
            ."\t\t\t\t<input type='hidden' name='old_title' value='{$row['title']}'>\n"
            ."\t\t\t\t<input type='hidden' name='old_code' value='{$row['code']}'>\n"
            ."\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n"
            ."\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n"
            ."\t\t\t\t</form>\n";
        $position++;
        }
    $position=sprintf("%05d", $position);
    if ($activeuse == 0)
        {
        echo "\t\t\t\t<tr>\n"
            ."\t\t\t\t<form method='post' action='labels.php'>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t<input type='text' $slstyle name='code' size='5' id='addnewlabelcode'>\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t<input type='text' $slstyle name='title' size='35'>\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_ADD."'>\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t\t<td>\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t</tr>\n";
        echo "<script type='text/javascript' language='javascript'>\n"
            . "<!--\n"
            . "document.getElementById('addnewlabelcode').focus();\n"
            . "//-->\n"
            . "</script>\n";
        }
    else
        {
        echo "\t\t\t\t<tr>\n"
            ."\t\t\t\t\t<td colspan='4' align='center'>\n"
            ."\t\t\t\t\t\t$setfont<font color='red' size='1'><i><strong>"
            ._WARNING."</strong>: "._LB_ACTIVEUSE."</i></font></font>\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t</tr>\n";
        }
    echo "\t\t\t\t<input type='hidden' name='sortorder' value='$position'>\n"
        ."\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n"
        ."\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n"
        ."\t\t\t\t</form>\n"
        ."\t\t\t\t<tr><form action='labels.php' method='post'><td colspan='2'></td>"
        ."\t\t\t\t<td></td><td align='left'><input $btstyle type='submit' name='method' value='"
        ._AL_FIXSORT."'></td>\n"
        ."\t\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n"
        ."\t\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n"
        ."\t\t\t\t</form></tr>\n";
    if ($totaluse > 0 && $activeuse == 0) //If there are surveys using this labelset, but none are active warn about modifying
        {
        echo "\t\t\t\t<tr>\n"
            ."\t\t\t\t\t<td colspan='4' align='center'>\n"
            ."\t\t\t\t\t\t$setfont<font color='red' size='1'><i><strong>"
            ._WARNING."</strong>: "._LB_TOTALUSE."</i><br />";
        foreach ($qidarray as $qd) {echo "[<a href='".$qd['url']."'>".$qd['title']."</a>] ";}
        echo "</font></font>\n"
            ."\t\t\t\t\t</td>\n"
            ."\t\t\t\t</tr>\n";
        }
    echo "\t\t\t</table>\n"
        ."\t\t\t<table height='1'><tr><td></td></tr></table>\n";
    }


//CLOSE OFF
echo "\t</td>\n"; //END OF MAIN CELL
helpscreen();
echo "</table>\n";

echo htmlfooter("$langdir/instructions.html#labels", "Using PHPSurveyor`s Labels Editor");

//************************FUNCTIONS********************************
function updateset($lid)
    {
    global $dbprefix;
    if (get_magic_quotes_gpc() == "0")
        {
        $_POST['label_name'] = addcslashes($_POST['label_name'], "'");
        }
    $query = "UPDATE {$dbprefix}labelsets SET label_name='{$_POST['label_name']}' WHERE lid=$lid";
    if (!$result = mysql_query($query))
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_UPDATESET." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
        }
    }
function delset($lid)
    {
    global $dbprefix;
    //CHECK THAT THERE ARE NO QUESTIONS THAT RELY ON THIS LID
    $query = "SELECT qid FROM {$dbprefix}questions WHERE type IN ('F','H') AND lid=$lid";
    $result = mysql_query($query) or die("Error");
    $count = mysql_num_rows($result);
    if ($count > 0)
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_DELSET."\")\n //-->\n</script>\n";
        return false;
        }
    else //There are no dependencies. We can delete this safely
        {
        $query = "DELETE FROM {$dbprefix}labels WHERE lid=$lid";
        $result = mysql_query($query);
        $query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$lid";
        $result = mysql_query($query);
        return true;
        }
    }
function insertset()
    {
    global $dbprefix;
    if (get_magic_quotes_gpc() == "0")
        {
        $_POST['label_name'] = addcslashes($_POST['label_name'], "'");
        }
    $query = "INSERT INTO {$dbprefix}labelsets (lid, label_name) VALUES ('', '{$_POST['label_name']}')";
    if (!$result = mysql_query($query))
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_UPDATESET." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
        }
    else
        {
        return mysql_insert_id();
        }
    }
function modanswers($lid)
    {
    global $dbprefix;
    if (get_magic_quotes_gpc() == "0")
        {
        if (isset($_POST['title'])) 
            {
            $_POST['title'] = addcslashes($_POST['title'], "'");
            }
        }
    if (!isset($_POST['method'])) {
        $_POST['method'] = _AL_SAVE;
    }
	switch($_POST['method'])
        {
        case _AL_ADD:
            if (isset($_POST['code']) && $_POST['code'])
                {
                $query = "INSERT INTO {$dbprefix}labels (lid, code, title, sortorder) VALUES ($lid, '{$_POST['code']}', '{$_POST['title']}', '{$_POST['sortorder']}')";
                if (!$result = mysql_query($query))
                    {
                    echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_INSERTANS." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
                    }
                }
            break;
        case _AL_SAVE:
            $query = "UPDATE {$dbprefix}labels SET code='{$_POST['code']}', title='{$_POST['title']}', sortorder='{$_POST['sortorder']}' WHERE lid=$lid AND code='{$_POST['old_code']}'";
            if (!$result = mysql_query($query))
                {
                echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_EDITANS." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
                }
            break;
        case _AL_UP:
            $newsortorder=sprintf("%05d", $_POST['sortorder']-1);
            $replacesortorder=$newsortorder;
            $newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
            $cdquery = "UPDATE {$dbprefix}labels SET sortorder='PEND' WHERE lid=$lid AND sortorder='$newsortorder'";
            $cdresult=mysql_query($cdquery) or die(mysql_error());
            $cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder='$newreplacesortorder'";
            $cdresult=mysql_query($cdquery) or die(mysql_error());
            $cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newreplacesortorder' WHERE lid=$lid AND sortorder='PEND'";
            $cdresult=mysql_query($cdquery) or die(mysql_error());
            break;
        case _AL_DN:
            $newsortorder=sprintf("%05d", $_POST['sortorder']+1);
            $replacesortorder=$newsortorder;
            $newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
            $newreplace2=sprintf("%05d", $_POST['sortorder']);
            $cdquery = "UPDATE {$dbprefix}labels SET sortorder='PEND' WHERE lid=$lid AND sortorder='$newsortorder'";
            $cdresult=mysql_query($cdquery) or die(mysql_error());
            $cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder='{$_POST['sortorder']}'";
            $cdresult=mysql_query($cdquery) or die(mysql_error());
            $cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newreplacesortorder' WHERE lid=$lid AND sortorder='PEND'";
            $cdresult=mysql_query($cdquery) or die(mysql_error());
            break;
        case _AL_DEL:
            $query = "DELETE FROM {$dbprefix}labels WHERE lid=$lid AND code='{$_POST['old_code']}'";
            if (!$result = mysql_query($query))
                {
                echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_DELANS." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
                }
            break;
        case _AL_FIXSORT:
            fixorder($lid);
            break;
        }
    }
function fixorder($lid) //Function rewrites the sortorder for a group of answers
    {
    global $dbprefix;
    $query = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, code";
    $result = mysql_query($query);
    $position=0;
    while ($row=mysql_fetch_array($result))
        {
        $position=sprintf("%05d", $position);
        if (_PHPVERSION >= "4.3.0")
            {
            $title = mysql_real_escape_string($row['title']);
            }
        else
            {
            $title = mysql_escape_string($row['title']);
            }
        $query2="UPDATE {$dbprefix}labels SET sortorder='$position' WHERE lid={$row['lid']} AND code='{$row['code']}' AND title='$title'";
        $result2=mysql_query($query2) or die ("Couldn't update sortorder<br />$query2<br />".mysql_error());
        $position++;
        }
    }
    
function helpscreen()
    {
    global $homeurl, $langdir, $imagefiles;
    global $lid, $action;
    echo "\t\t<td id='help' width='150' valign='top' style='display: none' bgcolor='#CCCCCC'>\n";
    echo "\t\t\t<table width='100%'><tr><td><table width='100%' align='center' cellspacing='0'>\n";
    echo "\t\t\t\t<tr>\n";
    echo "\t\t\t\t\t<td bgcolor='#555555' height='8'>\n";
    echo "\t\t\t\t\t\t<font color='white' size='1'><strong>"._HELP."</strong></font>\n";
    echo "\t\t\t\t\t</td>\n";
    echo "\t\t\t\t</tr>\n";
    echo "\t\t\t\t<tr>\n";
    echo "\t\t\t\t\t<td align='center' bgcolor='#AAAAAA' style='border-style: solid; border-width: 1; border-color: #555555'>\n";
    echo "\t\t\t\t\t\t<img src='$imagefiles/blank.gif' width='20' hspace='0' border='0' align='left' alt=''>\n";
    echo "\t\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' align='right' onClick=\"showhelp('hide')\">\n";
    echo "\t\t\t\t\t</td>\n";
    echo "\t\t\t\t</tr>\n";
    echo "\t\t\t\t<tr>\n";
    echo "\t\t\t\t\t<td bgcolor='silver' height='100%' style='border-style: solid; border-width: 1; border-color: #333333'>\n";
    //determine which help document to show
    if (!$lid)
        {
        $helpdoc = "$langdir/labelsets.html";
        }
    elseif ($lid)
        {
        $helpdoc = "$langdir/labels.html";
        }
    echo "\t\t\t\t\t\t<iframe width='150' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n";
    echo "\t\t\t\t\t\t</iframe>\n";
    echo "\t\t\t\t\t</td>";
    echo "\t\t\t\t</tr>\n";
    echo "\t\t\t</table></td></tr></table>\n";
    echo "\t\t</td>\n";
    }
?>