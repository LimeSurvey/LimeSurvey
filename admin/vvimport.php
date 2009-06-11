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

include_once("login_check.php");
if (!isset($noid)) {$noid=returnglobal('noid');}
if (!isset($insertstyle)) {$insertstyle=returnglobal('insert');}
if (!isset($finalized)) {$finalized=returnglobal('finalized');}

$encodingsarray = array("armscii8"=>$clang->gT("ARMSCII-8 Armenian")
		,"ascii"=>$clang->gT("US ASCII")
		,"auto"=>$clang->gT("Automatic")
		,"big5"=>$clang->gT("Big5 Traditional Chinese")
		,"binary"=>$clang->gT("Binary pseudo charset")
		,"cp1250"=>$clang->gT("Windows Central European")
		,"cp1251"=>$clang->gT("Windows Cyrillic")
		,"cp1256"=>$clang->gT("Windows Arabic")
		,"cp1257"=>$clang->gT("Windows Baltic")
		,"cp850"=>$clang->gT("DOS West European")
		,"cp852"=>$clang->gT("DOS Central European")
		,"cp866"=>$clang->gT("DOS Russian")
		,"cp932"=>$clang->gT("SJIS for Windows Japanese")
		,"dec8"=>$clang->gT("DEC West European")
		,"eucjpms"=>$clang->gT("UJIS for Windows Japanese")
		,"euckr"=>$clang->gT("EUC-KR Korean")
		,"gb2312"=>$clang->gT("GB2312 Simplified Chinese")
		,"gbk"=>$clang->gT("GBK Simplified Chinese")
		,"geostd8"=>$clang->gT("GEOSTD8 Georgian")
		,"greek"=>$clang->gT("ISO 8859-7 Greek")
		,"hebrew"=>$clang->gT("ISO 8859-8 Hebrew")
		,"hp8"=>$clang->gT("HP West European")
		,"keybcs2"=>$clang->gT("DOS Kamenicky Czech-Slovak")
		,"koi8r"=>$clang->gT("KOI8-R Relcom Russian")
		,"koi8u"=>$clang->gT("KOI8-U Ukrainian")
		,"latin1"=>$clang->gT("cp1252 West European")
		,"latin2"=>$clang->gT("ISO 8859-2 Central European")
		,"latin5"=>$clang->gT("ISO 8859-9 Turkish")
		,"latin7"=>$clang->gT("ISO 8859-13 Baltic")
		,"macce"=>$clang->gT("Mac Central European")
		,"macroman"=>$clang->gT("Mac West European")
		,"sjis"=>$clang->gT("Shift-JIS Japanese")
		,"swe7"=>$clang->gT("7bit Swedish")
		,"tis620"=>$clang->gT("TIS620 Thai")
		,"ucs2"=>$clang->gT("UCS-2 Unicode")
		,"ujis"=>$clang->gT("EUC-JP Japanese")
		,"utf8"=>$clang->gT("UTF-8 Unicode"));
if (isset($_POST['vvcharset']) && $_POST['vvcharset'])  //sanitize charset - if encoding is not found sanitize to 'utf8' which is the default for vvexport
{
	$uploadcharset=$_POST['vvcharset'];
	if (!array_key_exists($uploadcharset,$encodingsarray)) {$uploadcharset='utf8';}
}
   					   
if ($subaction != "upload")
{
	asort($encodingsarray);               
	$charsetsout='';
	foreach  ($encodingsarray as $charset=>$title)
	{
		$charsetsout.="<option value='$charset' ";
		if ($charset=='utf8') {$charsetsout.=" selected ='selected'";}
		$charsetsout.=">$title ($charset)</option>";
	}

	//Make sure that the survey is active
	$tablelist = $connect->MetaTables();
	if (in_array("{$dbprefix}survey_$surveyid", $tablelist))
	{

		$vvoutput= "<br />
		<form enctype='multipart/form-data' method='post' action='admin.php?sid=$surveyid'>
		<table class='form2columns' style='width:95%;' align='center'>		
		<tr><th colspan=2>".$clang->gT("Import a VV survey file")."</th></tr>
		<tr><td>".$clang->gT("File:")."</td><td><input type='file' size=50 name='the_file'></td></tr>
		<tr><td>".$clang->gT("Survey ID:")."</td><td><input type='text' size=10 name='sid' value='$surveyid' readonly></td></tr>
		<tr><td>".$clang->gT("Exclude record IDs?")."</td><td><input type='checkbox' name='noid' value='noid' checked=checked onchange='form.insertmethod.disabled=this.checked;' ></td></tr>
        <!-- this next item should only appear if noid is not checked -->
		<tr><td>".$clang->gT("When an imported record matches an existing record ID:")."</td><td><select id='insertmethod' name='insert' disabled='disabled'>
        <option value='ignore' selected='selected'>".$clang->gT("Report and skip the new record.")."</option>
        <option value='renumber'>".$clang->gT("Renumber the new record.")."</option>
        <option value='replace'>".$clang->gT("Replace the existing record.")."</option>
        </select></td></tr>
		<tr><td>".$clang->gT("Import as not finalized answers?")."</td><td><input type='checkbox' name='finalized' value='notfinalized' ></td></tr>
		<tr><td>".$clang->gT("Character set of the file:")."</td><td><select id='vvcharset' name='vvcharset'>
		$charsetsout
		</select></td></tr>
		<tr</td><td><td><input type='submit' value='".$clang->gT("Import")."'>
		<input type='hidden' name='action' value='vvimport' />
		<input type='hidden' name='subaction' value='upload' />
		</td></tr>
        <tr></td><td><td>[<a href='$scriptname?action=browse&amp;sid=$surveyid'>".$clang->gT("Return to Survey Administration")."</a>]</td></tr>
		</table>
		</form><br />";
	}
	else
	{
		$vvoutput .= "<br /><table class='outlinetable' align='center'>
		<tr><th colspan=2>Import a VV survey file</th></tr>
		<tr><td colspan='2' align='center'>
		<strong>".$clang->gT("Cannot import the VVExport file.")."</strong><br /><br />
		".("This survey is not active. You must activate the survey before attempting to import a VVexport file.")."<br /><br />
		[<a href='$scriptname?sid=4'>".$clang->gT("Return to Survey Administration")."</a>]
		</td></tr>
		</table>";		
	}


}
else
{
	$vvoutput = "<br /><table class='outlinetable' align='center'>
		<tr><th>Upload</th></tr>
		<tr><td align='center'>";
	$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

	if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
	{
		$vvoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
		$vvoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
		$vvoutput .= "<input type='submit' value='".$clang->gT("Back to Response Import")."' onclick=\"window.open('$scriptname?action=vvimport&sid=$surveyid', '_top')\">\n";
		$vvoutput .= "</font></td></tr></table><br />&nbsp;\n";
		return;
	}
	// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

	$vvoutput .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
	$vvoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
	$vvoutput .= $clang->gT("Reading file..")."<br />\n";
	$handle = fopen($the_full_file_path, "r");
	while (!feof($handle))
	{
		$buffer = fgets($handle); //To allow for very long lines 
		$bigarray[] = @mb_convert_encoding($buffer,"UTF-8",$uploadcharset);
	}
	fclose($handle);

	$surveytable = "{$dbprefix}survey_$surveyid";

	unlink($the_full_file_path); //delete the uploaded file
	unset($bigarray[0]); //delete the first line

	$fieldnames=explode("\t", trim($bigarray[1]));
	
	$fieldcount=count($fieldnames)-1;
	while (trim($fieldnames[$fieldcount]) == "") // get rid of blank entries
	{
		unset($fieldnames[$fieldcount]);
		$fieldcount--;
	}

	$realfieldnames = array_values($connect->MetaColumnNames($surveytable, true));
	if ($noid == "noid") {unset($realfieldnames[0]);}
	if ($finalized == "notfinalized") {unset($realfieldnames[1]);}
	unset($bigarray[1]); //delete the second line

	//	$vvoutput .= "<tr><td valign='top'><strong>Import Fields:<pre>"; print_r($fieldnames); $vvoutput .= "</pre></td>";
	//	$vvoutput .= "<td valign='top'><strong>Actual Fields:<pre>"; print_r($realfieldnames); $vvoutput .= '</pre></td></tr>';

	//See if any fields in the import file don't exist in the active survey
	$missing = array_diff($fieldnames, $realfieldnames);
	if (is_array($missing) && count($missing) > 0)
	{
		foreach ($missing as $key=>$val)
		{
			$donotimport[]=$key;
			unset($fieldnames[$key]);
		}
	}
	if ($finalized == "notfinalized")
	{
		$donotimport[]=1;
		unset($fieldnames[1]);
	}
	$importcount=0;
	$recordcount=0;
	foreach($bigarray as $row)
	{
		if (trim($row) != "")
		{
			$recordcount++;
			$fieldvalues=explode("\t", str_replace("\n", "", $row), $fieldcount+1);
			// Excel likes to quote fields sometimes. =(
			$fieldvalues=preg_replace('/^"(.*)"$/s','\1',$fieldvalues);
			// careful about the order of these arrays:
			// lbrace has to be substituted *last*
			$fieldvalues=str_replace(array("{newline}",
			"{cr}",
			"{tab}",
			"{quote}",
			"{lbrace}"),
			array("\n",
			"\r",
			"\t",
			"\"",
			"{"),
			$fieldvalues);
			if (isset($donotimport)) //remove any fields which no longer exist
			{
				foreach ($donotimport as $not)
				{
					unset($fieldvalues[$not]);
				}
			}
			// sometimes columns with nothing in them get omitted by excel
			while (count($fieldnames) > count($fieldvalues))
			{
				$fieldvalues[]="";
			}
			// sometimes columns with nothing in them get added by excel
			while (count($fieldnames) < count($fieldvalues) &&
			trim($fieldvalues[count($fieldvalues)-1])=="")
			{
				unset($fieldvalues[count($fieldvalues)-1]);
			}
			// make this safe for DB (*after* we undo first excel's
			// and then our escaping).
			$fieldvalues=array_map('db_quote',$fieldvalues);
			$fieldnames=array_map('db_quote_id',$fieldnames);
            
            $fielddata=array_combine($fieldnames,$fieldvalues);
            
            if ($fielddata[db_quote_id('submitdate')]=='NULL') unset ($fielddata[db_quote_id('submitdate')]); 
            
            $recordexists=false;     
            if (isset($fielddata['[id]']))
            {
                $result = $connect->Execute("select id from $surveytable where id=".$fielddata[db_quote_id('id')]);     
                $recordexists=$result->RecordCount()>0;
                if ($recordexists)  // record with same id exists
                {
                    if ($insertstyle=="ignore") 
                    {
                        $vvoutput .=sprintf($clang->gT("Record ID %d was skipped because of duplicate ID."), $fielddata[db_quote_id('id')]).'<br/>';
                        continue;
                    }
                    if ($insertstyle=="replace")
                    {
                        $result = $connect->Execute("delete from $surveytable where id=".$fielddata['id']);
                        $recordexists=false;     
                    } 
                }
            }
			if ($insertstyle=="renumber")
			{
                unset($fielddata['id']);
            }
            if (isset($fielddata['id']))
            {
               if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.$surveytable." ON");}   //Checked
            }
			    // try again, without the 'id' field.

			    $insert = "INSERT INTO $surveytable\n";
			    $insert .= "(".implode(", ", array_keys($fielddata)).")\n";
			    $insert .= "VALUES\n";
			    $insert .= "('".implode("', '", array_values($fielddata))."')\n";
			    $result = $connect->Execute($insert);

            if (isset($fielddata['id']))
            {
               if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.$surveytable." OFF");}   //Checked
            }
                
                
			if (!$result)
			{
				$vvoutput .= "<table align='center' class='outlintable'>\n$insert"
                            ."<tr><td>".sprintf($clang->gT("Import Failed on Record %d because [%s]"), $recordcount, htmlspecialchars(utf8_encode($connect->ErrorMsg())))
                            ."</td>\n</tr></table>\n";
			}
			else
			{
				$importcount++;
			}
            

		}
	}

	if ($noid == "noid" || $insertstyle == "renumber")
	{
		$vvoutput .= "<br /><i><strong><font color='red'>".$clang->gT("Important Note:")."<br />".$clang->gT("Do NOT refresh this page, as this will import the file again and produce duplicates")."</font></strong></i><br /><br />";
	}
	$vvoutput .= $clang->gT("Total records imported:")." ".$importcount."<br /><br />";
	$vvoutput .= "[<a href='admin.php?action=browse&amp;sid=$surveyid'>".$clang->gT("Browse Responses")."</a>]";
	$vvoutput .= "</td></tr></table><br />&nbsp;";
}
?>
