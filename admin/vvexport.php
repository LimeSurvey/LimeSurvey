<?php
/*
    #############################################################
    # >>> PHPSurveyor                                           #
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
//Exports all responses to a survey in special "Verified Voting" format.
require_once("config.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($action)) {$action = returnglobal('action');}

if (!$action == "export")
    {
    echo $htmlheader;
    echo "<br /><form method='post' action='vvexport.php?sid=$surveyid'>
    	<table align='center' class='outlinetable'>
        <tr><th colspan='2'>"._VV_EXPORTFILE."</th></tr>
        <tr>
         <td align='right'>"._EXPORTSURVEY.":</td>
         <td><input type='text' $slstyle size=4 value='$surveyid' name='sid' readonly></td>
        </tr>
        <tr>
         <td align='right'>
          Mode:
         </td>
         <td>
          <select name='method' $slstyle>
           <option value='deactivate'>"._VV_EXPORTDEACTIVATE."</option>
           <option value='none' selected>"._VV_EXPORTONLY."</option>
          </select>
         </td>
        </tr>
        <tr>
         <td>&nbsp;
         </td>
         <td>
          <input type='submit' value='"._EXPORTRESULTS."' $btstyle onClick='return confirm(\""._VV_RUSURE."\")'>&nbsp;
          <input type='hidden' name='action' value='export'>
         </td>
        </tr>
        <tr><td colspan='2' align='center'>[<a href='$scriptname?sid=$surveyid'>"._B_ADMIN_BT."</a>]</td></tr>
        </table>
        </form>";        
    }
elseif (isset($surveyid) && $surveyid)
    {
    //Export is happening
    header("Content-Disposition: attachment; filename=vvexport_$surveyid.xls");
    header("Content-type: application/vnd.ms-excel");
    Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    $s="\t";

    $fieldmap=createFieldMap($surveyid, "full");
    $surveytable = "{$dbprefix}survey_$surveyid";

    loadPublicLangFile($surveyid);

    $fldlist = mysql_list_fields($databasename, $surveytable);
    $columns = mysql_num_fields($fldlist);
    for ($i = 0; $i < $columns; $i++)
        {
        $fieldnames[] = mysql_field_name($fldlist, $i);
        }


    //Create the human friendly first line
    $firstline="";
    $secondline="";
    foreach ($fieldnames as $field)
        {
        $fielddata=arraySearchByKey($field, $fieldmap, "fieldname", 1);
        //echo "<pre>";print_r($fielddata);echo "</pre>";
        if (count($fielddata) < 1) {$firstline.=$field;}
        else
            //{$firstline.=str_replace("\n", " ", str_replace("\t", "   ", strip_tags($fielddata['question'])));}
            {$firstline.=preg_replace('/\s+/',' ',strip_tags($fielddata['question']));}
        $firstline .= $s;
        $secondline .= $field.$s;
        }
    echo $firstline."\n";
    echo $secondline."\n";
    $query = "SELECT * FROM $surveytable";
    $result = mysql_query($query) or die("Error:<br />$query<br />".mysql_error());

    while ($row=mysql_fetch_array($result))
        {
        foreach ($fieldnames as $field)
            {
            $value=trim($row[$field]);
            // sunscreen for the value. necessary for the beach.
            // careful about the order of these arrays:
            // lbrace has to be substituted *first*
            $value=str_replace(array("{",
                                     "\n",
                                     "\r",
                                     "\t"),
                               array("{lbrace}",
                                     "{newline}",
                                     "{cr}",
                                     "{tab}"),
                               $value);
            // one last tweak: excel likes to quote values when it
            // exports as tab-delimited (esp if value contains a comma,
            // oddly enough).  So we're going to encode a leading quote,
            // if it occurs, so that we can tell the difference between
            // strings that "really are" quoted, and those that excel quotes
            // for us.
            $value=preg_replace('/^"/','{quote}',$value);
            // yay!  that nasty sun won't hurt us now!
            $sun[]=$value;
            }
        $beach=implode($s, $sun);
        echo $beach;
        unset($sun);
        echo "\n";
        }

    //echo "<pre>$firstline</pre>";
    //echo "<pre>$secondline</pre>";
    //echo "<pre>"; print_r($fieldnames); echo "</pre>";
    //echo "<pre>"; print_r($fieldmap); echo "</pre>";

    //Now lets finalised according to the "method"
    if (!isset($method)) {$method=returnglobal('method');}
    switch($method)
        {
        case "deactivate": //Deactivate the survey
            $date = date('YmdHi'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day
            $result = mysql_list_tables($databasename);
            while ($row = mysql_fetch_row($result))
                {
                $tablelist[]=$row[0];
                }
            if (in_array("{$dbprefix}tokens_{$_GET['sid']}", $tablelist))
                {
                $toldtable="{$dbprefix}tokens_{$_GET['sid']}";
                $tnewtable="{$dbprefix}old_tokens_{$_GET['sid']}_{$date}";
                $tdeactivatequery = "RENAME TABLE $toldtable TO $tnewtable";
                $tdeactivateresult = mysql_query($tdeactivatequery) or die ("\n\n"._ERROR."Couldn't deactivate tokens table because:<br />".mysql_error()."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>"._GO_ADMIN."</a>");
                }
            $oldtable="{$dbprefix}survey_{$_GET['sid']}";
            $newtable="{$dbprefix}old_{$_GET['sid']}_{$date}";

            //Update the auto_increment value from the table before renaming
            $query = "SELECT id FROM $oldtable ORDER BY id desc LIMIT 1";
            $result = mysql_query($query) or die("Couldn't get latest id from table<br />$query<br />".mysql_error());
            while ($row=mysql_fetch_array($result))
                {
                $new_autonumber_start=$row['id']+1;
                }
            $query = "UPDATE {$dbprefix}surveys SET autonumber_start=$new_autonumber_start WHERE sid=$surveyid";
            $result = mysql_query($query); //Note this won't kill the script if it fails

            //Rename survey responses table
            $deactivatequery = "RENAME TABLE $oldtable TO $newtable";
            $deactivateresult = mysql_query($deactivatequery) or die ("\n\n"._ERROR."Couldn't deactivate because:<BR>".mysql_error()."<BR><BR><a href='$scriptname?sid={$_GET['sid']}'>Admin</a>");
            break;
        case "delete": //Delete the rows
            break;
        default:

        } // switch
    }

?>