<?php
/*
    #############################################################
    # >>> PHP Surveyor                                          #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA
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
require_once("config.php");

if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
    {
    require_once ("$jpgraphdir/jpgraph.php"); 
    require_once ("$jpgraphdir/jpgraph_pie.php");
    require_once ("$jpgraphdir/jpgraph_pie3d.php");
    require_once ("$jpgraphdir/jpgraph_bar.php");
    //$currentuser is created as prefix for jpgraph files
    if (isset($_SERVER['REDIRECT_REMOTE_USER']))
        {
        $currentuser=$_SERVER['REDIRECT_REMOTE_USER'];
        }
    elseif (session_id())
        {
        $currentuser=substr(session_id(), 0, 15);
        }
    else
        {
        $currentuser="standard";
        }
    }

if (isset($_POST['summary']) && !is_array($_POST['summary'])) {
    $_POST['summary'] = explode("|", $_POST['summary']);
}   

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

sendcacheheaders();

$slstyle2 = "style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080; width: 150'";

echo $htmlheader;

if (!$surveyid)
    {
    //need to have a survey id
    echo "<center>You have not selected a survey!</center>";
    exit;
    }

//Delete any stats files from the temp directory that aren't from today.
deleteNotPattern($tempdir, "STATS_*.png","STATS_".date("d")."*.png");

//Get the menubar
$surveyoptions=browsemenubar();

echo "\t<script type='text/javascript'>
      <!--
       function hide(element) {
        document.getElementById(element).style.display='none';
       }
       function show(element) {
        document.getElementById(element).style.display='';
       }
      //-->
      </script>\n";

echo "<table><tr><td></td></tr></table>\n"
    ."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
    ."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._STATISTICS."</strong></font></td></tr>\n";
echo $surveyoptions;
echo "</table>\n"
    ."<table ><tr><td></td></tr></table>\n"
    ."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1'"
    ." cellspacing='0'>\n"
    ."<tr><td align='center' bgcolor='#555555' height='22'>"
    ."<input type='image' src='$imagefiles/plus.gif' align='right' onClick='show(\"filtersettings\"); hide(\"sqlbuilder\")'><input type='image' src='$imagefiles/minus.gif' align='right' onClick='hide(\"filtersettings\")'>"
    ."<font size='2' face='verdana' color='orange'><strong>"._ST_FILTERSETTINGS."</strong></font>"
    ."</td></tr>\n"
    ."<form method='post' name='formbuilder' action='statistics.php'>\n";

//Select public language file
$query = "SELECT language, datestamp FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result = mysql_query($query) or die("Error selecting language: <br />".$query."<br />".mysql_error());
while ($row=mysql_fetch_array($result)) {$surveylanguage = $row['language']; $datestamp=$row['datestamp'];}
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename); 

// 1: Get list of questions from survey
$query = "SELECT {$dbprefix}questions.*, group_name\n"
        ."FROM {$dbprefix}questions, {$dbprefix}groups\n"
        ."WHERE {$dbprefix}groups.gid={$dbprefix}questions.gid\n"
        ."AND {$dbprefix}questions.sid=$surveyid";
$result = mysql_query($query) or die("Couldn't do it!<br />$query<br />".mysql_error());
while($row=mysql_fetch_assoc($result)){$rows[]=$row;} // while
//SORT IN NATURAL ORDER!
usort($rows, 'CompareGroupThenTitle');
foreach ($rows as $row) 
    {
    $filters[]=array($row['qid'], 
                     $row['gid'], 
                     $row['type'], 
                     $row['title'], 
                     $row['group_name'], 
                     strip_tags($row['question']), 
                     $row['lid']);
    }

// SHOW ID FIELD

echo "\t\t<tr><td align='center'>
       <table cellspacing='0' cellpadding='0' width='100%' id='filtersettings'><tr><td>
        <table align='center'><tr>\n";
$myfield = "id";
$myfield2=$myfield."G";
$myfield3=$myfield."L";
$myfield4=$myfield."=";
echo "<td align='center'>$setfont<strong>id</strong><br />";
echo "\t\t\t\t\t<font size='1'>"._ST_NOGREATERTHAN.":<br />\n"
        ."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield2' value='";
    if (isset($_POST[$myfield2])){echo $_POST[$myfield2];}
    echo "'><br />\n"
        ."\t\t\t\t\t"._ST_NOLESSTHAN.":<br />\n"
        ."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield3' value='";
    if (isset($_POST[$myfield3])) {echo $_POST[$myfield3];}
    echo "'><br />\n";
    echo "\t\t\t\t\t=<br />
            <input type='text' $slstyle2 name='$myfield4' value='";
    if (isset($_POST[$myfield4])) {echo $_POST[$myfield4];}
    echo "'><br /></td>\n";
    $allfields[]=$myfield2;
    $allfields[]=$myfield3;
    $allfields[]=$myfield4;

if ($datestamp == "Y") {
    $myfield = "datestamp";
    $myfield2 = "datestampG";
    $myfield3 = "datestampL";
    $myfield2="$myfield";
    $myfield3="$myfield2=";
    $myfield4="$myfield2<"; $myfield5="$myfield2>";
    echo "<td width='40'></td>";
    echo "\t\t\t\t<td align='center' valign='top'>$setfont<strong>datestamp</strong>"
        ."<br />\n"
        ."\t\t\t\t\t<font size='1'>"._ST_DATEEQUALS.":<br />\n"
        ."\t\t\t\t\t<input name='$myfield3' type='text' value='";
    if (isset($_POST[$myfield3])) {echo $_POST[$myfield3];}
    echo "' ".substr($slstyle2, 0, -13) ."; width:80'><br />\n"
        ."\t\t\t\t\t&nbsp;&nbsp;"._ST_ORBETWEEN.":<br />\n"
        ."\t\t\t\t\t<input name='$myfield4' value='";
    if (isset($_POST[$myfield4])) {echo $_POST[$myfield4];}
    echo "' type='text' ".substr($slstyle2, 0, -13) 
        ."; width:65'> "._AND." <input  name='$myfield5' value='";
    if (isset($_POST[$myfield5])) {echo $_POST[$myfield5];}
    echo "' type='text' ".substr($slstyle2, 0, -13) 
        ."; width:65'>\n";
    $allfields[]=$myfield2;
    $allfields[]=$myfield3;
    $allfields[]=$myfield4;
    $allfields[]=$myfield5;
}
echo "</td></tr></table>";

// 2: Get answers for each question
if (!isset($currentgroup)) {$currentgroup="";}
foreach ($filters as $flt)
    {
    if ($flt[1] != $currentgroup) 
        {   //If the groupname has changed, start a new row
        if ($currentgroup)
            {
            //if we've already drawn a table for a group, and we're changing - close off table
            echo "\n\t\t\t\t</td></tr>\n\t\t\t</table>\n";
            }
        echo "\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n"
            ."\t\t<font size='1' face='verdana'><strong>$flt[4]</strong> ("._GROUP." $flt[1])</font></td></tr>\n\t\t"
            ."<tr><td align='center'>\n"
            ."\t\t\t<table align='center'><tr>\n";
        $counter=0;
        }
    if (isset($counter) && $counter == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>"; $counter=0;}
    $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";
    $niceqtext = str_replace("\"", "`", $flt[5]);
    $niceqtext = str_replace("'", "`", $niceqtext);
    $niceqtext = str_replace("\r", "", $niceqtext);
    $niceqtext = str_replace("\n", "", $niceqtext);
    //headings
    if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && $flt[2] != "F" && $flt[2] != "H" && $flt[2] != "T" && $flt[2] != "U" && $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" && $flt[2] != "X" && $flt[2] != "W" && $flt[2] != "Z") //Have to make an exception for these types!
        {
        echo "\t\t\t\t<td align='center'>"
            ."$setfont<strong>$flt[3]&nbsp;"; //Heading (Question No)
        if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R") {$myfield = "M$myfield";}
        if ($flt[2] == "N") {$myfield = "N$myfield";}
        echo "<input type='checkbox' name='summary[]' value='$myfield'";
        if (isset($_POST['summary']) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE)) 
            {echo " CHECKED";}
        echo ">&nbsp;"
            ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])."\" onClick=\"alert('"._QUESTION.": ".$niceqtext."')\"></strong>"
            ."<br />\n";
        if ($flt[2] != "N") {echo "\t\t\t\t<select name='";}
        if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R") {echo "M";}
        if ($flt[2] != "N") {echo "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple $slstyle2>\n";}
        $allfields[]=$myfield;
        }
    echo "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
    switch ($flt[2])
        {
        case "Q":
            //DO NUSSINK
            break;
        case "T": // Long free text
        case "U": // Huge free text
            $myfield2="T$myfield";
            echo "\t\t\t\t<td align='center' valign='top'>"
                ."$setfont<strong>$flt[3]</strong>";
            echo "<input type='checkbox' name='summary[]' value='$myfield2'";
            if (isset($_POST['summary']) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE)) 
                {echo " CHECKED";}
            echo ">&nbsp;"
                ."&nbsp;<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                .str_replace("\"", "`", $flt[5])." \" "
                ."onClick=\"alert('"._QUESTION.": ".$niceqtext." "
                ."')\">"
                ."<br />\n"
                ."\t\t\t\t\t<font size='1'>"._ST_RESPONECONT.":</font><br />\n"
                ."\t\t\t\t\t<textarea $slstyle2 name='$myfield2' rows='3'>";
            if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
            echo "</textarea>";
            $allfields[]=$myfield2;
            break;
        case "S": // Short free text
            $myfield2="T$myfield";
            echo "\t\t\t\t<td align='center' valign='top'>"
                ."$setfont<strong>$flt[3]</strong>";
            echo "<input type='checkbox' name='summary[]' value='$myfield2'";
            if (isset($_POST['summary']) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE)) 
                {echo " CHECKED";}
            echo ">&nbsp;"
                ."&nbsp;<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                .str_replace("\"", "`", $flt[5])
                ." [$flt[1]]\" onClick=\"alert('"._QUESTION.": ".$niceqtext." "
                ."')\">"
                ."<br />\n"
                ."\t\t\t\t\t<font size='1'>"._ST_RESPONECONT.":</font><br />\n"
                ."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield2' value='";
            if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
            echo "'>";
            $allfields[]=$myfield2;
            break;
        case "N": // Numerical
            $myfield2="{$myfield}G";
            $myfield3="{$myfield}L";
            echo "\t\t\t\t\t<font size='1'>"._ST_NOGREATERTHAN.":<br />\n"
                ."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield2' value='";
            if (isset($_POST[$myfield2])){echo $_POST[$myfield2];}
            echo "'><br />\n"
                ."\t\t\t\t\t"._ST_NOLESSTHAN.":<br />\n"
                ."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield3' value='";
            if (isset($_POST[$myfield3])) {echo $_POST[$myfield3];}
            echo "'><br />\n";
            $allfields[]=$myfield2;
            $allfields[]=$myfield3;
            break;
        case "D": // Date
            $myfield2="D$myfield";
            $myfield3="$myfield2=";
            $myfield4="$myfield2<"; $myfield5="$myfield2>";
            echo "\t\t\t\t<td align='center' valign='top'>$setfont<strong>$flt[3]</strong>"
                ."&nbsp;<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                .str_replace("\"", "`", $flt[5])
                ." \" onClick=\"alert('"._QUESTION.": ".$niceqtext." "
                ."')\">"
                ."<br />\n"
                ."\t\t\t\t\t<font size='1'>"._ST_DATEEQUALS.":<br />\n"
                ."\t\t\t\t\t<input name='$myfield3' type='text' value='";
            if (isset($_POST[$myfield3])) {echo $_POST[$myfield3];}
            echo "' ".substr($slstyle2, 0, -13) ."; width:80'><br />\n"
                ."\t\t\t\t\t&nbsp;&nbsp;"._ST_ORBETWEEN.":<br />\n"
                ."\t\t\t\t\t<input name='$myfield4' value='";
            if (isset($_POST[$myfield4])) {echo $_POST[$myfield4];}
            echo "' type='text' ".substr($slstyle2, 0, -13) 
                ."; width:65'> "._AND." <input  name='$myfield5' value='";
            if (isset($_POST[$myfield5])) {echo $_POST[$myfield5];}
            echo "' type='text' ".substr($slstyle2, 0, -13) 
                ."; width:65'>\n";
            break;
        case "5": // 5 point choice
            for ($i=1; $i<=5; $i++)
                {
                echo "\t\t\t\t\t<option value='$i'";
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield])) 
                    {echo " selected";}
                echo ">$i</option>\n";
                }
            break;
        case "G": // Gender
            echo "\t\t\t\t\t<option value='F'";
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {echo " selected";}
            echo ">"._FEMALE."</option>\n";
            echo "\t\t\t\t\t<option value='M'";
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {echo " selected";}
            echo ">"._MALE."</option>\n";
            break;
        case "Y": // Yes\No
            echo "\t\t\t\t\t<option value='Y'";
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {echo " selected";}
            echo ">"._YES."</option>\n"
                ."\t\t\t\t\t<option value='N'";
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {echo " selected";}
            echo ">"._NO."</option>\n";
            break;
        // ARRAYS
        case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
            $counter2=0;
            while ($row=mysql_fetch_row($result))
                {
                $myfield2 = $myfield."$row[0]";
                echo "<!-- $myfield2 -- ";
                if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
                echo " -->\n";
                if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
                    ."<input type='checkbox' name='summary[]' value='$myfield2'";
                if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {echo " CHECKED";}
                echo ">&nbsp;"
                    ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                    .str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('"._QUESTION.": "
                    .$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
                    ."<br />\n"
                    ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
                for ($i=1; $i<=5; $i++)
                    {
                    echo "\t\t\t\t\t<option value='$i'";
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
                    if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected";}
                    echo ">$i</option>\n";
                    }
                echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                $allfields[]=$myfield2;
                }
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;
        case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
            $counter2=0;
            while ($row=mysql_fetch_row($result))
                {
                $myfield2 = $myfield . "$row[0]";
                echo "<!-- $myfield2 -- ";
                if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
                echo " -->\n";
                if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                
                echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"; //heading
                echo "<input type='checkbox' name='summary[]' value='$myfield2'";
                if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {echo " CHECKED";}
                echo ">&nbsp;"
                    ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                    .str_replace("\"", "`", $flt[5])
                    ." [$row[1]]\" onClick=\"alert('"._QUESTION.": ".$niceqtext." "
                    .str_replace("'", "`", $row[1])
                    ."')\">"
                    ."<br />\n"
                    ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
                for ($i=1; $i<=10; $i++)
                    {
                    echo "\t\t\t\t\t<option value='$i'";
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
                    if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected";}
                    echo ">$i</option>\n";
                    }
                echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                $allfields[]=$myfield2;
                }
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;
        case "C": // ARRAY OF YES\No\Uncertain QUESTIONS
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0][]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
            $counter2=0;
            while ($row=mysql_fetch_row($result))
                {
                $myfield2 = $myfield . "$row[0]";
                echo "<!-- $myfield2 -- ";
                if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
                echo " -->\n";
                if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
                    ."<input type='checkbox' name='summary[]' value='$myfield2'";
                if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) 
                    {echo " CHECKED";}
                echo ">&nbsp;"
                    ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                    .str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('"._QUESTION.": ".$niceqtext." "
                    .str_replace("'", "`", $row[1])."')\">"
                    ."<br />\n"
                    ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n"
                    ."\t\t\t\t\t<option value='Y'";
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {echo " selected";}
                echo ">"._YES."</option>\n"
                    ."\t\t\t\t\t<option value='U'";
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {echo " selected";}
                echo ">"._UNCERTAIN."</option>\n"
                    ."\t\t\t\t\t<option value='N'";
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {echo " selected";}
                echo ">"._NO."</option>\n"
                    ."\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                $allfields[]=$myfield2;
                }
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;
        case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0][]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
            $counter2=0;
            while ($row=mysql_fetch_row($result))
                {
                $myfield2 = $myfield . "$row[0]";
                echo "<!-- $myfield2 -- ";
                if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
                echo " -->\n";
                if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
                    ."<input type='checkbox' name='summary[]' value='$myfield2'";
                if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {echo " CHECKED";}
                echo ">&nbsp;"
                    ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                    .str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('"._QUESTION
                    .": ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
                    ."<br />\n"
                    ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n"
                    ."\t\t\t\t\t<option value='I'";
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {echo " selected";}
                echo ">"._INCREASE."</option>\n"
                    ."\t\t\t\t\t<option value='S'";
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {echo " selected";}
                echo ">"._SAME."</option>\n"
                    ."\t\t\t\t\t<option value='D'";
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {echo " selected";}
                echo ">"._DECREASE."</option>\n"
                    ."\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                $allfields[]=$myfield2;
                }
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;
        case "F": // ARRAY OF Flexible QUESTIONS
        case "H": // ARRAY OF Flexible Questions (By Column)
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0][]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
            $counter2=0;
            while ($row=mysql_fetch_row($result))
                {
                $myfield2 = $myfield . "$row[0]";
                echo "<!-- $myfield2 -- ";
                if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
                echo " -->\n";
                if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
                    ."<input type='checkbox' name='summary[]' value='$myfield2'";
                if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {echo " CHECKED";}
                echo ">&nbsp;"
                    ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                    .str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('"._QUESTION
                    .": ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
                    ."<br />\n";
                $fquery = "SELECT * FROM labels WHERE lid={$flt[6]} ORDER BY sortorder, code";
                //echo $fquery;
                $fresult = mysql_query($fquery);
                echo "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
                while ($frow = mysql_fetch_array($fresult))
                    {
                    echo "\t\t\t\t\t<option value='{$frow['code']}'";
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected";}
                    echo ">{$frow['title']}</option>\n";
                    }
                echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                $allfields[]=$myfield2;
                }
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;
        case "R": //RANKING
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
            $count = mysql_num_rows($result);
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
                {
                $answers[]=array($row['code'], $row['answer']);
                }
            $counter2=0;
            for ($i=1; $i<=$count; $i++)
                {
                if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter=0;}
                $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                $myfield3 = $myfield . $i;
                echo "<!-- $myfield2 -- ";
                if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
                echo " -->\n"
                    ."\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($i)"
                    ."<input type='checkbox' name='summary[]' value='$myfield2'";
                if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary']) !== FALSE) {echo " CHECKED";}
                echo ">&nbsp;"
                    ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\""
                    .str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('"._QUESTION
                    .": ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
                    ."<br />\n"
                    ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple $slstyle2>\n";
                foreach ($answers as $ans)
                    {
                    echo "\t\t\t\t\t<option value='$ans[0]'";
                    if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {echo " selected";}
                    echo ">$ans[1]</option>\n";
                    }
                echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                $allfields[]=$myfield2;
                }
            echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            //Link to rankwinner script - awaiting completion
//          echo "\t\t\t\t</tr>\n\t\t\t\t<tr bgcolor='#DDDDDD'>\n"
//              ."<td colspan=$count align=center>$setfont"
//              ."<input $btstyle type='button' value='Show Rank Summary' onClick=\"window.open('rankwinner.php?sid=$surveyid&qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
//              ."</td></tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            unset($answers);
            break;
        case "X": //This is a boilerplate question and it has no business in this script
            break;
        case "W":
        case "Z":
            echo "\t\t\t\t<td align='center'>"
                ."$setfont<strong>$flt[3]&nbsp;"; //Heading (Question No)
            echo "<input type='checkbox' name='summary[]' value='$myfield'";
            if (isset($_POST['summary']) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE)) 
                {echo " CHECKED";}
            echo ">&nbsp;"
                ."<img src='$imagefiles/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])."\" onClick=\"alert('"._QUESTION.": ".$niceqtext."')\"></strong>"
                ."<br />\n";
            echo "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple $slstyle2>\n";
            $allfields[]=$myfield;
            $query = "SELECT code, title FROM {$dbprefix}labels WHERE lid={$flt[6]} ORDER BY sortorder, title";
            $result = mysql_query($query) or die("Couldn't get answers!<br />$query<br />".mysql_error());
            while($row=mysql_fetch_row($result))
                {
                echo "\t\t\t\t\t\t<option value='$row[0]'";
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {echo " selected";}
                echo ">$row[1]</option>\n";
                } // while
            echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
            break;
        default:
            $query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
            $result = mysql_query($query) or die("Couldn't get answers!<br />$query<br />".mysql_error());
            while ($row=mysql_fetch_row($result))
                {
                echo "\t\t\t\t\t\t<option value='$row[0]'";
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {echo " selected";}
                echo ">$row[1]</option>\n";
                }
            break;
        }
    if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && $flt[2] != "T" && $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" && $flt[2] != "W" && $flt[2] !="Z") //Have to make an exception for these types!
        {
        echo "\n\t\t\t\t</td>\n";
        }
    $currentgroup=$flt[1];
    if (!isset($counter)) {$counter=0;}
    $counter++;
    }
echo "\n\t\t\t\t</td></tr>\n";
if (isset($allfields))
    {
    $allfield=implode("|", $allfields);
    }

echo "\t\t\t</table>\n"
    ."\t\t</td></tr>\n"
    ."\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n"
    ."\t\t<font size='1' face='verdana'>&nbsp;</font></td></tr>\n"
    ."\t\t\t\t<tr><td align='center'>$setfont<input type='radio' id='viewsummaryall' name='summary' value='$allfield'"
    ."><label for='viewsummaryall'>"._ST_VIEWALL."</label></td></tr>\n"
    ."\t\t<tr><td align='center' bgcolor='#CCCCCC'>\n\t\t\t<br />\n"
    ."\t\t\t<input $btstyle type='submit' value='"._ST_SHOWRESULTS."'>\n"
    ."\t\t\t<input $btstyle type='button' value='"._ST_CLEAR."' onClick=\"window.open('statistics.php?sid=$surveyid', '_top')\">\n"
    ."\t\t<br />&nbsp;\n\t\t</td></tr>\n"
    ."\t<input type='hidden' name='sid' value='$surveyid'>\n"
    ."\t<input type='hidden' name='display' value='stats'>\n"
    ."\t</form>\n"
    ."</table>\n"
    ."</td></tr></table>";

$fieldmap = createFieldMap($surveyid, "full");
$selectlist = "";
foreach ($fieldmap as $field)
    {
    $selectlist .= "<option value='".$field['fieldname']."'>"
                .$field['title'].": ".$field['question']."</option>\n";
    //create a select list of all the possible answers to this question
    switch($field['type'])
        {
        case "S":
        case "T":
        case "U":
        case "N":
            //text type - don't do anything
            break;
        case "G":
            $thisselect="<div id='{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
            $thisselect .= "<option value='F'>[F] "._FEMALE."</option>\n";
            $thisselect .= "<option value='Y'>[M] "._MALE."</option>\n";
            $thisselect .= "</select></div>\n";
            $answerselects[]=$thisselect;
            $asnames[]=$field['fieldname'];
            break;
        case "Y":
            $thisselect="<div id='{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
            $thisselect .= "<option value='Y'>[Y] "._YES."</option>\n";
            $thisselect .= "<option value='N'>[N] "._NO."</option>\n";
            $thisselect .= "</select></div>\n";
            $answerselects[]=$thisselect;
            $asnames[]=$field['fieldname'];
            break;
        case "M":
            //multiple choise - yes or nothing
            $thisselect="<div id='{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
            $thisselect .= "<option value='Y'>[Y] "._YES."</option>\n";
            $thisselect .= "</select></div>\n";
            $answerselects[]=$thisselect;
            $asnames[]=$field['fieldname'];
            break;
        case "L":
            //list - show possible answers
            $query = "SELECT * FROM {$dbprefix}answers WHERE qid={$field['qid']}";
            $result = mysql_query($query);
            $thisselect="<div id='{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
            while($row = mysql_fetch_array($result))
                {
                $thisselect .= "<option value='".$row['code']."'>[".$row['code']."] ".$row['answer']."</option>\n";
                } // while
            $thisselect .= "</select></div>\n";
            $answerselects[]=$thisselect;
            $asnames[]=$field['fieldname'];
            break;
        } // switch
    }

echo "</table>\n"
    ."<table ><tr><td></td></tr></table>\n"
    ."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1'"
    ." cellspacing='0'>\n"
    ."<tr><td align='center' bgcolor='#555555' height='22'>"
    ."<input type='image' src='$imagefiles/plus.gif' align='right' hspace='0' border='0' onClick='show(\"sqlbuilder\"); hide(\"filtersettings\")'><input type='image' src='$imagefiles/minus.gif' align='right' border='0' onClick='hide(\"sqlbuilder\")' hspace='0'>"
    ."<font size='2' face='verdana' color='orange'><strong>SQL Builder</strong>"
    ."</td></tr>\n"
    ."<form method='post' name='sqlbuilder'>\n";

echo "      <tr><td>
      <script type='text/javascript'>
      <!--
       function displayvalue(value) {
         document.getElementById('fieldnametext').value=value;\n";
foreach ($asnames as $as)
    {
    echo "      document.getElementById('$as').style.display='none';\n";
    }
echo "       document.getElementById(value).style.display='';
       }
      //-->
      </script>
      <table width='100%' align='center' cellspacing='0' cellpadding='0' style='display:none' id='sqlbuilder'>
       <tr>
        <td align='center'>Questions:<br />
         <select size='5' style='width: 800; font-size: 8.5px' onClick='displayvalue(this.value)' onChange='displayvalue(this.value)'>$selectlist</select>
        </td>
       </tr>
       <tr>
        <td align='center'><br />
         Field Name:<br /><input type='text' id='fieldnametext' size='50'>
        </td>
       </tr>
       <tr>
        <td align='center'>Answers:<br />\n";
foreach ($answerselects as $as) {echo "$as\n";}
if (!isset($_POST['sql'])) 
    {
    $_POST['sql']="SELECT *\nFROM survey_$surveyid\n";
    }
echo "      </td>
       </tr>
       <tr>
        <td align='center'><br />
         "._SQL.":<br />
         <textarea name='sql' cols='60' rows='10'>".$_POST['sql']."</textarea>
        </td>
       </tr>
       <tr>
        <td align='center'>
         <input type='submit' value='Query'>
        </td>
       </tr>
      </table>
      <input type='hidden' name='display' value=\"Hi\">
      </td></tr></form>
      </table>";
//echo "<pre>";print_r($fieldmap);echo "</pre>";
// DISPLAY RESULTS
if (isset($_POST['display']) && $_POST['display'])
    {
    echo "<script type='text/javascript'>
    <!-- 
     hide('sqlbuilder'); 
     hide('filtersettings'); 
    //-->
    </script>\n";
    // 1: Get list of questions with answers chosen
    for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;} // creates array of post variable names
    foreach ($postvars as $pv) 
        {
        if (in_array($pv, $allfields)) //Only do this if there is actually a value for the $pv
            {
            $firstletter=substr($pv,0,1);
            if ($pv != "sid" && $pv != "display" && $firstletter != "M" && $firstletter != "T" && $firstletter != "D" && $firstletter != "N" && $pv != "summary" && substr($pv, 0, 2) != "id" && substr($pv, 0, 9) != "datestamp") //pull out just the fieldnames
                {
                $thisquestion = "`$pv` IN (";
                foreach ($_POST[$pv] as $condition)
                    {
                    $thisquestion .= "'$condition', ";
                    }
                $thisquestion = substr($thisquestion, 0, -2)
                              . ")";
                $selects[]=$thisquestion;
                }
            elseif (substr($pv, 0, 1) == "M")
                {
                list($lsid, $lgid, $lqid) = explode("X", $pv);
                $aquery="SELECT code FROM {$dbprefix}answers WHERE qid=$lqid ORDER BY sortorder, answer";
                $aresult=mysql_query($aquery) or die ("Couldn't get answers<br />$aquery<br />".mysql_error());
                while ($arow=mysql_fetch_row($aresult)) // go through every possible answer
                    {
                    if (in_array($arow[0], $_POST[$pv])) // only add condition if answer has been chosen
                        {
                        $mselects[]="`".substr($pv, 1, strlen($pv))."$arow[0]` = 'Y'";
                        }
                    }
                if ($mselects) 
                    {
                    $thismulti=implode(" OR ", $mselects);
                    $selects[]="($thismulti)";
                    }
                }
            elseif (substr($pv, 0, 1) == "N")
                {
                if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
                    {
                    $selects[]="`".substr($pv, 1, -1)."` > '".$_POST[$pv]."'";
                    }
                if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
                    {
                    $selects[]="`".substr($pv, 1, -1)."` < '".$_POST[$pv]."'";
                    }
                }
            elseif (substr($pv, 0, 2) == "id")
                {
                if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
                    {
                    $selects[]="`".substr($pv, 0, -1)."` > '".$_POST[$pv]."'";
                    }
                if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
                    {
                    $selects[]="`".substr($pv, 0, -1)."` < '".$_POST[$pv]."'";
                    }
                if (substr($pv, strlen($pv)-1, 1) == "=" && $_POST[$pv] != "")
                    {
                    $selects[]="`".substr($pv, 0, -1)."` = '".$_POST[$pv]."'";
                    }
                }
            elseif (substr($pv, 0, 1) == "T" && $_POST[$pv] != "")
                {
                $selects[]="`".substr($pv, 1, strlen($pv))."` like '%".$_POST[$pv]."%'";
                }
            elseif (substr($pv, 0, 1) == "D" && $_POST[$pv] != "")
                {
                if (substr($pv, -1, 1) == "=")
                    {
                    $selects[] = "`".substr($pv, 1, strlen($pv)-2)."` = '".$_POST[$pv]."'";
                    }
                else
                    {
                    if (substr($pv, -1, 1) == "<")
                        {
                        $selects[]= "`".substr($pv, 1, strlen($pv)-2) . "` > '".$_POST[$pv]."'";
                        }
                    if (substr($pv, -1, 1) == ">")
                        {
                        $selects[]= "`".substr($pv, 1, strlen($pv)-2) . "` < '".$_POST[$pv]."'";
                        }
                    }
                }
            elseif (substr($pv, 0, 9) == "datestamp")
                {
                if (substr($pv, -1, 1) == "=" && !empty($_POST[$pv]))
                    {
                    $selects[] = "`datestamp` = '".$_POST[$pv]."'";
                    }
                else
                    {
                    if (substr($pv, -1, 1) == "<" && !empty($_POST[$pv]))
                        {
                        $selects[]= "`datestamp` > '".$_POST[$pv]."'";
                        }
                    if (substr($pv, -1, 1) == ">" && !empty($_POST[$pv]))
                        {
                        $selects[]= "`datestamp` < '".$_POST[$pv]."'";
                        }
                    }
                }
            } else {
             echo "<!-- $pv DOES NOT EXIST IN ARRAY -->";
            }
        }
    // 2: Do SQL query
    $query = "SELECT count(*) FROM {$dbprefix}survey_$surveyid";
    $result = mysql_query($query) or die ("Couldn't get total<br />$query<br />".mysql_error());
    while ($row=mysql_fetch_row($result)) {$total=$row[0];}
    if (isset($selects) && $selects) 
        {
        $query .= " WHERE ";
        $query .= implode(" AND ", $selects);
        }
    elseif (!empty($_POST['sql']) && !isset($_POST['id=']))
        {
        $newsql=substr($_POST['sql'], strpos($_POST['sql'], "WHERE")+5, strlen($_POST['sql']));
        //$query = $_POST['sql'];
        $query .= " WHERE ".$newsql;
        }
    $result=mysql_query($query) or die("Couldn't get results<br />$query<br />".mysql_error());
    while ($row=mysql_fetch_row($result)) {$results=$row[0];}
    
    // 3: Present results including option to view those rows
    echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' "
        ."cellpadding='2' cellspacing='0' >\n"
        ."\t<tr><td colspan='2' align='center'><strong>$setfont<font color='orange'>"
        ._ST_RESULTS."</strong></td></tr>\n"
        ."\t<tr><td colspan='2' align='center' bgcolor='#666666'>"
        ."$setfont<font color='#EEEEEE'>"
        ."<strong>"._ST_RECORDSRETURNED.": $results </strong><br />\n\t\t"
        ._ST_TOTALRECORDS.": $total<br />\n";
    if ($total)
        {
        $percent=sprintf("%01.2f", ($results/$total)*100);
        echo _ST_PERCENTAGE
            .": $percent%<br />";
        }
    echo "\n\t\t<br />\n"
        ."\t\t<font size='1'><strong>"._SQL.":</strong> $query\n"
        ."\t</td></tr>\n";
    if (isset ($selects) && $selects) {$sql=implode(" AND ", $selects);}
    elseif (!empty($newsql)) {$sql = $newsql;}
    if (!isset($sql) || !$sql) {$sql="NULL";}
    if ($results > 0)
        {
        echo "\t<tr>"
            ."\t\t<form action='browse.php' method='post' target='_blank'><td align='right' width='50%'>\n"
            ."\t\t<input type='submit' value='Browse' $btstyle>\n"
            ."\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
            ."\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n"
            ."\t\t\t<input type='hidden' name='action' value='all'>\n"
            ."\t\t</td></form>\n"
            ."\t\t<form action='export.php' method='post' target='_blank'><td width='50%'>\n"
            ."\t\t<input type='submit' value='Export' $btstyle>\n"
            ."\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
            ."\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n";
            //Add the fieldnames
            if (isset($_POST['summary']) && $_POST['summary'])
                {
                foreach($_POST['summary'] as $viewfields) 
                    {
                    switch(substr($viewfields, 0, 1))
                        {
                        case "N":
                        case "T":
                            $field = substr($viewfields, 1, strlen($viewfields)-1);
                            echo "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
                            break;
                        case "M":
                            list($lsid, $lgid, $lqid) = explode("X", substr($viewfields, 1, strlen($viewfields)-1));
                            $aquery="SELECT code FROM {$dbprefix}answers WHERE qid=$lqid ORDER BY sortorder, answer";
                            $aresult=mysql_query($aquery) or die ("Couldn't get answers<br />$aquery<br />".mysql_error());
                            while ($arow=mysql_fetch_row($aresult)) // go through every possible answer
                                {
                                $field = substr($viewfields, 1, strlen($viewfields)-1).$arow[0];
                                echo "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
                                }
                            $aquery = "SELECT other FROM {$dbprefix}questions WHERE qid=$lqid";
                            $aresult = mysql_query($aquery);
                            while($arow = mysql_fetch_row($aresult)){
                                if ($arow[0] == "Y") {
                                    //echo $arow[0];
                                    $field = substr($viewfields, 1, strlen($viewfields)-1)."other";
                                    echo "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
                                }
                            } // while
                            break;
                        default:
                            $field = $viewfields;
                            echo "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
                            break;
                        }
                    }
                }
        echo "\t\t</td></form>\n\t</tr>\n";
        }
    echo "</table>\n";
    }

//Show Summary results
if (isset($_POST['summary']) && $_POST['summary'])
    {
    if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
        {
        //Delete any old temp image files
        deletePattern($tempdir, "STATS_".date("d")."X".$currentuser."X".$surveyid."X"."*.png");
        }
    $runthrough=returnglobal('summary');

    //START Chop up fieldname and find matching questions
    $lq = "SELECT DISTINCT qid FROM {$dbprefix}questions WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
    $lr = mysql_query($lq);
    $legitqs[] = "DUMMY ENTRY";
    while ($lw = mysql_fetch_array($lr))
        {
        $legitqids[] = $lw['qid']; //this creates an array of question id's'
        }
    //Finished collecting legitqids
    foreach ($runthrough as $rt)
        {
        // 1. Get answers for question ##############################################################
        if (substr($rt, 0, 1) == "M") //MULTIPLE OPTION, THEREFORE MULTIPLE FIELDS.
            {
            list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
            $nquery = "SELECT title, type, question, lid, other FROM {$dbprefix}questions WHERE qid='$qqid'";
            $nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
            while ($nrow=mysql_fetch_row($nresult)) 
                {
                $qtitle=$nrow[0]; 
                $qtype=$nrow[1];
                $qquestion=strip_tags($nrow[2]); 
                $qlid=$nrow[3];
                $qother=$nrow[4];
                }
            
            //1. Get list of answers
            $query="SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qqid' ORDER BY sortorder, answer";
            $result=mysql_query($query) or die("Couldn't get list of answers for multitype<br />$query<br />".mysql_error());
            while ($row=mysql_fetch_row($result))
                {
                $mfield=substr($rt, 1, strlen($rt))."$row[0]";
                $alist[]=array("$row[0]", "$row[1]", $mfield);
                }
            if ($qother == "Y")
                {
                $mfield=substr($rt, 1, strlen($rt))."other";
                $alist[]=array(_OTHER, _OTHER, $mfield);
                }
            }
        elseif (substr($rt, 0, 1) == "T" || substr($rt, 0, 1) == "S") //Short and long text
            {
            list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
            $nquery = "SELECT title, type, question, other FROM {$dbprefix}questions WHERE qid='$qqid'";
            $nresult = mysql_query($nquery) or die("Couldn't get text question<br />$nquery<br />".mysql_error());
            while ($nrow=mysql_fetch_row($nresult))
                {
                $qtitle=$nrow[0]; $qtype=$nrow[1];
                $qquestion=strip_tags($nrow[2]);
                }
            $mfield=substr($rt, 1, strlen($rt));
            $alist[]=array("Answers", _AL_ANSWER, $mfield);
            $alist[]=array("NoAnswer", _NOANSWER, $mfield);
            }
        elseif (substr($rt, 0, 1) == "R") //RANKING OPTION THEREFORE CONFUSING
            {
            $lengthofnumeral=substr($rt, strpos($rt, "-")+1, 1);
            list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strpos($rt, "-")-($lengthofnumeral+1)), 3); 
            $nquery = "SELECT title, type, question FROM {$dbprefix}questions WHERE qid='$qqid'";
            $nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
            while ($nrow=mysql_fetch_row($nresult)) 
                {
                $qtitle=$nrow[0]. " [".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]"; 
                $qtype=$nrow[1]; 
                $qquestion=strip_tags($nrow[2]). "["._RANK." ".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
                }
            $query="SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qqid' ORDER BY sortorder, answer";
            $result=mysql_query($query) or die("Couldn't get list of answers for multitype<br />$query<br />".mysql_error());
            while ($row=mysql_fetch_row($result))
                {
                $mfield=substr($rt, 1, strpos($rt, "-")-1);
                $alist[]=array("$row[0]", "$row[1]", $mfield);
                }
            }
        elseif (substr($rt, 0, 1) == "N") //NUMERICAL TYPE
            {
            if (substr($rt, -1) == "G" || substr($rt, -1) == "L" || substr($rt, -1) == "=") 
                {
                //DO NUSSINK
                }
            else 
                {
                list($qsid, $qgid, $qqid) = explode("X", $rt, 3);
                $nquery = "SELECT title, type, question, qid, lid FROM {$dbprefix}questions WHERE qid='$qqid'";
                $nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
                while ($nrow=mysql_fetch_row($nresult)) {$qtitle=$nrow[0]; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]); $qiqid=$nrow[3]; $qlid=$nrow[4];}
                echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' >\n"
                    ."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='orange'>"._ST_FIELDSUMMARY." $qtitle:</strong>"
                    ."</td></tr>\n"
                    ."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='#EEEEEE'>$qquestion</strong></font></font></td></tr>\n"
                    ."\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><strong>"
                    ._ST_CALCULATION."</strong></font></td>\n"
                    ."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><strong>"
                    ._ST_RESULT."</strong></font></td>\n"
                    ."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><strong></strong></font></td>\n"
                    ."\t</tr>\n";
                $fieldname=substr($rt, 1, strlen($rt));
                $query = "SELECT STDDEV(`$fieldname`) as stdev";
                $query .= ", SUM(`$fieldname`*1) as sum";
                $query .= ", AVG(`$fieldname`*1) as average";
                $query .= ", MIN(`$fieldname`*1) as minimum";
                $query .= ", MAX(`$fieldname`*1) as maximum";
                $query .= " FROM {$dbprefix}survey_$surveyid WHERE `$fieldname` IS NOT NULL AND `$fieldname` != ' '";
                if ($sql != "NULL") {$query .= " AND $sql";}
                $result=mysql_query($query) or die("Couldn't do maths testing<br />$query<br />".mysql_error());
                while ($row=mysql_fetch_array($result))
                    {
                    $showem[]=array(_ST_SUM, $row['sum']);
                    $showem[]=array(_ST_STDEV, $row['stdev']);
                    $showem[]=array(_ST_AVERAGE, $row['average']);
                    $showem[]=array(_ST_MIN, $row['minimum']);
                    $maximum=$row['maximum']; //we're going to put this after the quartiles for neatness
                    $minimum=$row['minimum'];
                    }
                
                //CALCULATE QUARTILES
                $query ="SELECT `$fieldname` FROM {$dbprefix}survey_$surveyid WHERE `$fieldname` IS NOT null AND `$fieldname` != ' '";
                if ($sql != "NULL") {$query .= " AND $sql";}
                $result=mysql_query($query) or die("Disaster during median calculation<br />$query<br />".mysql_error());
                $querystarter="SELECT `$fieldname` FROM {$dbprefix}survey_$surveyid WHERE `$fieldname` IS NOT null AND `$fieldname` != ' '";
                if ($sql != "NULL") {$querystarter .= " AND $sql";}
                $medcount=mysql_num_rows($result);
                
                //1ST QUARTILE (Q1)
                $q1=(1/4)*($medcount+1);
                $q1b=(int)((1/4)*($medcount+1));
                $q1c=$q1b-1;
                $q1diff=$q1-$q1b;
                $total=0;
                if ($q1c<1) {$q1c=1;$lastnumber=0;}  // fix if there are too few values to evaluate.
                if ($q1 != $q1b)
                    {
                    //ODD NUMBER
                    $query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q1c, 2";
                    $result=mysql_query($query) or die("1st Quartile query failed<br />".mysql_error());
                    while ($row=mysql_fetch_array($result)) 
                        {
                        if ($total == 0)    {$total=$total-$row[$fieldname];}
                        else                {$total=$total+$row[$fieldname];}
                        $lastnumber=$row[$fieldname];
                        }
                    $q1total=$lastnumber-(1-($total*$q1diff));
                    if ($q1total < $minimum) {$q1total=$minimum;}
                    $showem[]=array(_ST_Q1, $q1total);
                    }
                else
                    {
                    //EVEN NUMBER
                    $query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q1c, 1";
                    $result=mysql_query($query) or die ("1st Quartile query failed<br />".mysql_error());
                    while ($row=mysql_fetch_array($result)) {$showem[]=array("1st Quartile (Q1)", $row[$fieldname]);}
                    }
                $total=0;
                //MEDIAN (Q2)
                $median=(1/2)*($medcount+1);
                $medianb=(int)((1/2)*($medcount+1));
                $medianc=$medianb-1;
                $mediandiff=$median-$medianb;
                if ($median != (int)((($medcount+1)/2)-1)) 
                    {
                    //remainder
                    $query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $medianc, 2";
                    $result=mysql_query($query) or die("What a complete mess<br />".mysql_error());
                    while ($row=mysql_fetch_array($result)) {$total=$total+$row[$fieldname];}
                    $showem[]=array(_ST_Q2, $total/2);
                    }
                else
                    {
                    //EVEN NUMBER
                    $query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $medianc, 1";
                    $result=mysql_query($query) or die("What a complete mess<br />".mysql_error());
                    while ($row=mysql_fetch_array($result)) {$showem[]=array("Median Value", $row[$fieldname]);}
                    }
                $total=0;
                //3RD QUARTILE (Q3)
                $q3=(3/4)*($medcount+1);
                $q3b=(int)((3/4)*($medcount+1));
                $q3c=$q3b-1;
                $q3diff=$q3-$q3b;
                if ($q3 != $q3b)
                    {
                    $query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q3c, 2";
                    $result = mysql_query($query) or die("3rd Quartile query failed<br />".mysql_error());
                    $lastnumber='';
                    while ($row=mysql_fetch_array($result)) 
                        {
                        if ($total == 0)    {$total=$total-$row[$fieldname];}
                        else                {$total=$total+$row[$fieldname];}
                        if (!$lastnumber) {$lastnumber=$row[$fieldname];}
                        }
                    $q3total=$lastnumber+($total*$q3diff);
                    if ($q3total < $maximum) {$q1total=$maximum;}
                    $showem[]=array(_ST_Q3, $q3total);
                    }
                else
                    {
                    $query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q3c, 1";
                    $result = mysql_query($query) or die("3rd Quartile even query failed<br />".mysql_error());
                    while ($row=mysql_fetch_array($result)) {$showem[]=array("3rd Quartile (Q3)", $row[$fieldname]);}
                    }
                $total=0;
                $showem[]=array(_ST_MAX, $maximum);
                foreach ($showem as $shw)
                    {
                    echo "\t<tr>\n"
                        ."\t\t<td align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$shw[0]</font></font></td>\n"
                        ."\t\t<td align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$shw[1]</td>\n"
                        ."\t\t<td bgcolor='#666666'></td>\n"
                        ."\t</tr>\n";
                    }
                echo "\t<tr>\n"
                    ."\t\t<td colspan='3' align='center' bgcolor='#EEEEEE'>\n"
                    ."\t\t\t$setfont<font size='1'>"._ST_NULLIGNORED."<br />\n"
                    ."\t\t\t"._ST_QUARTMETHOD
                    ."</font></font>\n"
                    ."\t\t</td>\n"
                    ."\t</tr>\n";
                unset($showem);
                }
            }
        elseif (substr($rt, 0, 2) == "id" || substr($rt, 0, 9) == "datestamp")
            {
            }
        else // NICE SIMPLE SINGLE OPTION ANSWERS
            {
            $fieldmap=createFieldMap($surveyid);
            $fielddata=arraySearchByKey($rt, $fieldmap, "fieldname", 1);
            $qsid=$fielddata['sid'];
            $qgid=$fielddata['gid'];
            $qqid=$fielddata['qid'];
            $qanswer=$fielddata['aid'];
            $rqid=$qqid;
            $nquery = "SELECT title, type, question, qid, lid, other FROM {$dbprefix}questions WHERE qid=$rqid";
            $nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
            while ($nrow=mysql_fetch_row($nresult)) 
                {
                $qtitle=$nrow[0]; 
                $qtype=$nrow[1]; 
                $qquestion=strip_tags($nrow[2]); 
                $qiqid=$nrow[3]; 
                $qlid=$nrow[4];
                $qother=$nrow[5];
                }
            $alist[]=array("", _NOANSWER);
            switch($qtype)
                {
                case "A": //Array of 5 point choices
                    $qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
                    $qresult=mysql_query($qquery) or die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".mysql_error());
                    while ($qrow=mysql_fetch_row($qresult))
                        {
                        for ($i=1; $i<=5; $i++)
                            {
                            $alist[]=array("$i", "$i");
                            }
                        $atext=$qrow[1];
                        }
                    $qquestion .= "<br />\n[".$atext."]";
                    $qtitle .= "($qanswer)";
                    break;
                case "B": //Array of 10 point choices
                    $qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
                    $qresult=mysql_query($qquery) or die ("Couldn't get answer details (Array 10p Q)<br />$qquery<br />".mysql_error());
                    while ($qrow=mysql_fetch_row($qresult))
                        {
                        for ($i=1; $i<=10; $i++)
                            {
                            $alist[]=array("$i", "$i");
                            }
                        $atext=$qrow[1];
                        }
                    $qquestion .= "<br />\n[".$atext."]";
                    $qtitle .= "($qanswer)";
                    break;
                case "C": //Array of Yes/No/Uncertain
                    $qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
                    $qresult=mysql_query($qquery) or die ("Couldn't get answer details<br />$qquery<br />".mysql_error());
                    while ($qrow=mysql_fetch_row($qresult))
                        {
                        $alist[]=array("Y", _YES);
                        $alist[]=array("N", _NO);
                        $alist[]=array("U", _UNCERTAIN);
                        $atext=$qrow[1];
                        }
                    $qquestion .= "<br />\n[".$atext."]";
                    $qtitle .= "($qanswer)";
                    break;
                case "E": //Array of Yes/No/Uncertain
                    $qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
                    $qresult=mysql_query($qquery) or die ("Couldn't get answer details<br />$qquery<br />".mysql_error());
                    while ($qrow=mysql_fetch_row($qresult))
                        {
                        $alist[]=array("I", _INCREASE);
                        $alist[]=array("S", _SAME);
                        $alist[]=array("D", _DECREASE);
                        $atext=$qrow[1];
                        }
                    $qquestion .= "<br />\n[".$atext."]";
                    $qtitle .= "($qanswer)";
                    break;
                case "F": //Array of Flexible
                case "H": //Array of Flexible by Column
                    $qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
                    $qresult=mysql_query($qquery) or die ("Couldn't get answer details<br />$qquery<br />".mysql_error());
                    while ($qrow=mysql_fetch_row($qresult))
                        {
                        $fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$qlid ORDER BY sortorder, code";
                        $fresult = mysql_query($fquery);
                        while ($frow=mysql_fetch_array($fresult))
                            {
                            $alist[]=array($frow['code'], $frow['title']);
                            }
                        $atext=$qrow[1];
                        }
                    $qquestion .= "<br />\n[".$atext."]";
                    $qtitle .= "($qanswer)";
                    break;
                case "G": //Gender
                    $alist[]=array("F", _FEMALE);
                    $alist[]=array("M", _MALE);
                    break;
                case "Y": //Yes\No
                    $alist[]=array("Y", _YES);
                    $alist[]=array("N", _NO);
                    break;
                case "5": //5 Point
                    for ($i=1; $i<=5; $i++)
                        {
                        $alist[]=array("$i", "$i");
                        }
                    break;
                case "W":
                case "Z":
                    $fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$qlid ORDER BY sortorder, code";
                    $fresult = mysql_query($fquery);
                    while ($frow=mysql_fetch_array($fresult))
                        {
                        $alist[]=array($frow['code'], $frow['title']);
                        }
                    break;
                default:
                    $qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qqid' ORDER BY sortorder, answer";
                    $qresult = mysql_query($qquery) or die ("Couldn't get answers list<br />$qquery<br />".mysql_error());
                    while ($qrow=mysql_fetch_row($qresult))
                        {
                        $alist[]=array("$qrow[0]", "$qrow[1]");
                        }
                    if (($qtype == "L" || $qtype == "!") && $qother == "Y") 
                        {
                        $alist[]=array("-oth-", _OTHER);
                        }
                }
            }
    
        //foreach ($alist as $al) {echo "$al[0] - $al[1]<br />";} //debugging line
        //foreach ($fvalues as $fv) {echo "$fv | ";} //debugging line
        
        //2. Collect and Display results #######################################################################
        if (isset($alist) && $alist) //Make sure there really is an answerlist, and if so:
            {
            echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' >\n"
                ."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='orange'>"
                ._ST_FIELDSUMMARY." $qtitle:</strong>"
                ."</td></tr>\n"
                ."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='#EEEEEE'>"
                ."$qquestion</strong></font></font></td></tr>\n"
                ."\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont"
                ."<font color='#EEEEEE'><strong>"._AL_ANSWER."</strong></font></td>\n"
                ."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont"
                ."<font color='#EEEEEE'><strong>"._COUNT."</strong></font></td>\n"
                ."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont"
                ."<font color='#EEEEEE'><strong>"._PERCENTAGE."</strong></font></td>\n"
                ."\t</tr>\n";
            foreach ($alist as $al)
                {
                if (isset($al[2]) && $al[2]) //picks out alist that come from the multiple list above
                    {
                    if ($al[1] == _OTHER)
                        {
                        $query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$surveyid WHERE `$al[2]` != ''";
                        }
                    elseif ($qtype == "T" || $qtype == "S")
                        {
                        if($al[0]=="Answers")
                            {
                            $query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$surveyid WHERE `$al[2]` != ''";
                            }
                        elseif($al[0]=="NoAnswer")
                            {
                            $query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$surveyid WHERE `$al[2]` IS NULL OR `$al[2]` = ''";
                            }
                        }
                    else
                        {
                        $query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$surveyid WHERE `$al[2]` =";
                        if (substr($rt, 0, 1) == "R")
                            {
                            $query .= " '$al[0]'";
                            }
                        else
                            {
                            $query .= " 'Y'";
                            }
                        }
                    }
                else
                    {
                    $query = "SELECT count(`$rt`) FROM {$dbprefix}survey_$surveyid WHERE `$rt` = '$al[0]'";
                    }
                if ($sql != "NULL") {$query .= " AND $sql";}
                $result=mysql_query($query) or die ("Couldn't do count of values<br />$query<br />".mysql_error());
                echo "\n<!-- ($sql): $query -->\n\n";
                while ($row=mysql_fetch_row($result))
                    {
                    if ($al[0] == "") 
                        {$fname=_NOANSWER;} 
                    elseif ($al[0] == _OTHER || $al[0] == "Answers")
                        {$fname="$al[1] <input $btstyle type='submit' value='"._BROWSE."' onclick=\"window.open('listcolumn.php?sid=$surveyid&column=$al[2]&sql=".urlencode($sql)."', 'results', 'width=300, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\">";}
                    elseif ($qtype == "S" || $qtype == "T")
                        {
                        if ($al[0] == "Answer")
                            {
                            $fname= "$al[1] <input $btstyle type='submit' value='"
                                  . _BROWSE."' onclick=\"window.open('listcolumn.php?sid=$surveyid&column=$al[2]&sql="
                                  . urlencode($sql)."', 'results', 'width=300, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\">";
                            }
                        elseif ($al[0] == "NoAnswer")
                            {
                            $fname= "$al[1]";
                            }
                        }
                    else
                        {$fname="$al[1] ($al[0])";}
                    echo "\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont"
                        ."<font color='#EEEEEE'>$fname\n"
                        ."\t\t</td>\n"
                        ."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$row[0]";
                    if ($results > 0) {$vp=sprintf("%01.2f", ($row[0]/$results)*100)."%";} else {$vp="N/A";}
                    echo "\t\t</td><td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$vp"
                        ."\t\t</td></tr>\n";
                    if ($results > 0) {$gdata[] = ($row[0]/$results)*100;
                                       } else {$gdata[] = 0;}
                    $grawdata[]=$row[0];
                    $label=strip_tags($fname);
                    $justcode[]=$al[0];
                    $lbl[] = wordwrap($label, 20, "\n");
                    }
                }
                
                if ($usejpgraph == 1 && array_sum($gdata)>0) //JPGRAPH CODING ORIGINALLY SUBMITTED BY Pieterjan Heyse
                    {
//                  echo "<pre>";
//                  echo "GDATA:\n";
//                  print_r($gdata);
//                  echo "GRAWDATA\n";
//                  print_r($grawdata);
//                  echo "LABEL\n";
//                  print_r($label);
//                  echo "JUSTCODE\n";
//                  print_r($justcode);
//                  echo "LBL\n";
//                  print_r($lbl);
//                  echo "</pre>";
                    //First, lets delete any earlier graphs from the tmp directory
                    //$gdata and $lbl are arrays built at the end of the last section
                    //that contain the values, and labels for the data we are about
                    //to send to jpgraph.
                    if ($qtype == "M" || $qtype == "P") { //Bar Graph
                        $graph = new Graph(640,320,'png');
                        $graph->SetScale("textint"); 
                        $graph->img->SetMargin(50,50,50,50);
                        $graph->xaxis->SetTickLabels($justcode);
                        $graph->xaxis->SetFont(constant($jpgraphfont), FS_NORMAL, 8);
                        $graph->xaxis->SetColor("silver");
                        $graph->xaxis->title->Set(_AL_CODE);
                        $graph->xaxis->title->SetFont(constant($jpgraphfont), FS_BOLD, 9);
                        $graph->xaxis->title->SetColor("silver");
                        $graph->yaxis->SetFont(constant($jpgraphfont), FS_NORMAL, 8);
                        $graph->yaxis->SetColor("silver");
                        $graph->yaxis->title->Set(_COUNT." / $results");
                        $graph->yaxis->title->SetFont(constant($jpgraphfont), FS_BOLD, 9);
                        $graph->yaxis->title->SetColor("silver");
                        //$graph->Set90AndMargin();
                    } else { //Pie Charts
                        $totallines=countLines($lbl);
                        if ($totallines>26) {
                            $gheight=320+(6.7*($totallines-26));
                            $fontsize=7;
                            $legendtop=0.01;
                            $setcentrey=0.5/(($gheight/320));
                        } else {
                            $gheight=320;
                            $fontsize=8;
                            $legendtop=0.07;
                            $setcentrey=0.5;
                        }
                        $graph = new PieGraph(640,$gheight,'png');
                        $graph->legend->SetFont(constant($jpgraphfont), FS_NORMAL, $fontsize);
                        $graph->legend->SetPos(0.015, $legendtop, 'right', 'top');
                        $graph->legend->SetFillColor("silver");
                        
                    }
                    $graph->title->SetColor("#EEEEEE");
                    $graph->SetMarginColor("#666666");
                    $graph->img->SetAntiAliasing();
                    // Set A title for the plot
                    //$graph->title->Set($qquestion);
                    $graph->title->SetFont(constant($jpgraphfont),FS_BOLD,13); 
                    // Create pie plot
                    if ($qtype == "M" || $qtype == "P") { //Bar Graph
                        $p1 = new BarPlot($grawdata);
                        $p1->SetWidth(0.8);
                        $p1->SetValuePos("center");
                        $p1->SetFillColor("orange");
                        if (!in_array(0, $grawdata)) { //don't show shadows if any of the values are 0 - jpgraph bug
                            $p1->SetShadow();
                        }
                        $p1->value->Show();
                        $p1->value->SetFont(constant($jpgraphfont),FS_NORMAL,8);
                        $p1->value->SetColor("#555555");
                    } else { //Pie Chart
                        $p1 = new PiePlot3d($gdata);
//                        echo "<pre>";print_r($lbl);echo "</pre>";
//                        echo "<pre>";print_r($gdata);echo "</pre>";
						$p1->SetTheme("earth");
                        $p1->SetLegends($lbl);
                        $p1->SetSize(200);
                        $p1->SetCenter(0.375,$setcentrey);
                        $p1->value->SetColor("orange");
                        $p1->value->SetFont(constant($jpgraphfont),FS_NORMAL,10);
                        // Set how many pixels each slice should explode
                        //$p1->Explode(array(0,15,15,25,15));
                    }
                    
                    if (!isset($ci)) {$ci=0;}
                    $ci++;
                    $graph->Add($p1);
                    $gfilename="STATS_".date("d")."X".$currentuser."X".$surveyid."X".$ci.date("His").".png";
                    $graph->Stroke($tempdir."/".$gfilename);
                    echo "<tr><td colspan='3' style=\"text-align:center\"><img src=\"$tempurl/".$gfilename."\" border='1'></td></tr>";
                    
                    ////// PIE ALL DONE
                    }
            }
        echo "</table>\n";
        unset($gdata);
        unset($grawdata);
        unset($lbl);
        unset($justcode);
        unset ($alist);
        }
    }
echo "<br />&nbsp;";
echo htmlfooter("$langdir/instructions.html#statistics", "Using PHPSurveyors Statistics Function");

function deletePattern($dir, $pattern = "")
   {
   $deleted = false;
   $pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
   if (substr($dir,-1) != "/") $dir.= "/";
   if (is_dir($dir))
       {    
       $d = opendir($dir);
       while ($file = readdir($d))
        {
        if (is_file($dir.$file) && ereg("^".$pattern."$", $file))
            {
            if (unlink($dir.$file))    
                {
                $deleted[] = $file;
                }
            }
       }
       closedir($d);
       return $deleted;
       }
   else return 0; 
   }
  
function deleteNotPattern($dir, $matchpattern, $pattern = "")
   {
   $deleted = false;
   $pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
   $matchpattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($matchpattern));
   if (substr($dir,-1) != "/") $dir.= "/";
   if (is_dir($dir))
       {    
       $d = opendir($dir);
       while ($file = readdir($d))
        {
        if (is_file($dir.$file) && ereg("^".$matchpattern."$", $file) && !ereg("^".$pattern."$", $file))
            {
            if (unlink($dir.$file))    
                {
                $deleted[] = $file;
                }
            }
       }
       closedir($d);
       return $deleted;
       }
   else return 0; 
   }

function countLines($array)
    {
    //$totalelements=count($array);
    $totalnewlines=0;
    foreach ($array as $ar)
        {
        $totalnewlines=$totalnewlines+substr_count($ar, "\n")+1;
        }
    $totallines=$totalnewlines+count($array);
    return $totallines;
    }

?>