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

if (!isset($surveyid))
{
	returnglobal('sid');
}

if (!isset($lid))
{
	returnglobal('lid');
}



if ($action == "importsurveyresources" && $surveyid) {
	if ($demoModeOnly === true)
	{
		$importsurveyresourcesoutput = "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	    $importsurveyresourcesoutput .= $clang->gT("Demo Mode Only: Uploading file is disabled in this system.")."<br /><br />\n";
		$importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\" />&nbsp;<br />&nbsp;<br />\n";
		return;
	}
	require("classes/phpzip/phpzip.inc.php");
	$zipfile=$_FILES['the_file']['tmp_name'];
	$z = new PHPZip();

	// Create temporary directory
	// If dangerous content is unzipped
	// then no one will know the path
	$extractdir=tempdir($tempdir);
	$basedestdir = $publicdir."/upload/surveys";
	$destdir=$basedestdir."/$surveyid/";

	$importsurveyresourcesoutput = "<br />\n";
	$importsurveyresourcesoutput .= "<table class='alertbox'>\n";
	$importsurveyresourcesoutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Import Survey Resources")."</strong></td></tr>\n";
	$importsurveyresourcesoutput .= "\t<tr><td align='center'>\n";

	if (!is_writeable($basedestdir))
	{
		$importsurveyresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	    $importsurveyresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
		$importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\">\n";
		$importsurveyresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
		return;
	}

	if (!is_dir($destdir))
	{
		mkdir($destdir);
	}

	$aImportedFilesInfo=null;
	$aErrorFilesInfo=null;


	if (is_file($zipfile))
	{
		$importsurveyresourcesoutput .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
		$importsurveyresourcesoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
		$importsurveyresourcesoutput .= $clang->gT("Reading file..")."<br />\n";

		if ($z->extract($extractdir,$zipfile) != 'OK')
		{
			$importsurveyresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
			$importsurveyresourcesoutput .= $clang->gT("This file is not a valid ZIP file archive. Import failed.")."<br /><br />\n";
			$importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\">\n";
			$importsurveyresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
			return;
		}

		// now read tempdir and copy authorized files only
		$dh = opendir($extractdir);
		while($direntry = readdir($dh))
		{
			if (($direntry!=".")&&($direntry!=".."))
			{
				if (is_file($extractdir."/".$direntry))
				{ // is  a file
					$extfile = substr(strrchr($direntry, '.'),1);
					if  (!(stripos(','.$allowedresourcesuploads.',',','.$extfile.',') === false))
					{ //Extension allowed
						if (!copy($extractdir."/".$direntry, $destdir.$direntry))
						{
							$aErrorFilesInfo[]=Array(
								"filename" => $direntry,
								"status" => $clang->gT("Copy failed")
							);
							unlink($extractdir."/".$direntry);
							
						}
						else
						{	
							$aImportedFilesInfo[]=Array(
								"filename" => $direntry,
								"status" => $clang->gT("OK")
							);
							unlink($extractdir."/".$direntry);
						}
					}
					
					else
					{ // Extension forbidden
						$aErrorFilesInfo[]=Array(
							"filename" => $direntry,
							"status" => $clang->gT("Error")." (".$clang->gT("Forbidden Extension").")"
						);
						unlink($extractdir."/".$direntry);
					}
				} // end if is_file
			} // end if ! . or ..
		} // end while read dir
		

		//Delete the temporary file
		unlink($zipfile);
		//Delete temporary folder
		rmdir($extractdir);

		// display summary
		$okfiles = 0;
		$errfiles= 0;
	        $ErrorListHeader .= "";
	        $ImportListHeader .= "";
		if (is_null($aErrorFilesInfo) && !is_null($aImportedFilesInfo))
		{
			$status=$clang->gT("Success");
			$color='green';
			$okfiles = count($aImportedFilesInfo);
		        $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
		}
		elseif (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
		{
			$importsurveyresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
			$importsurveyresourcesoutput .= $clang->gT("This ZIP archive contains no valid Resources files. Import failed.")."<br /><br />\n";
			$importsurveyresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP Archive.")."<br /><br />\n";
			$importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\">\n";
			$importsurveyresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
			return;
			
		}
		elseif (!is_null($aErrorFilesInfo) && !is_null($aImportedFilesInfo))
		{
			$status=$clang->gT("Partial");
			$color='orange';
			$okfiles = count($aImportedFilesInfo);
			$errfiles = count($aErrorFilesInfo);
		        $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
		        $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
		}
		else
		{
			$status=$clang->gT("Error");
			$color='red';
			$errfiles = count($aErrorFilesInfo);
		        $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
		}

        		$importsurveyresourcesoutput .= "<strong>".$clang->gT("Imported Resources for")." SID:</strong> $surveyid<br />\n";
		        $importsurveyresourcesoutput .= "<br />\n<strong><font color='$color'>".$status."</font></strong><br />\n";
		        $importsurveyresourcesoutput .= "<strong><u>".$clang->gT("Resources Import Summary")."</u></strong><br />\n";
		        $importsurveyresourcesoutput .= "".$clang->gT("Total Imported files").": $okfiles<br />\n";
		        $importsurveyresourcesoutput .= "".$clang->gT("Total Errors").": $errfiles<br />\n";
			$importsurveyresourcesoutput .= $ImportListHeader;
			foreach ($aImportedFilesInfo as $entry)
			{
		        	$importsurveyresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry["filename"]."</li>\n";
			}
		        $importsurveyresourcesoutput .= "\t</ul><br /><br />\n";
			$importsurveyresourcesoutput .= $ErrorListHeader;
			foreach ($aErrorFilesInfo as $entry)
			{
		        	$importsurveyresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry['filename']." (".$entry['status'].")</li>\n";
			}
	}
	else
	{
		$importsurveyresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	    $importsurveyresourcesoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
		$importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\">\n";
		$importsurveyresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
		return;
	}
		// Final Back not needed if files have been imported
//		$importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\">\n";
		$importsurveyresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
}



if ($action == "importlabelresources" && $lid)
{
	if ($demoModeOnly === true)
	{
		$importlabelresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
		$importlabelresourcesoutput .= sprintf ($clang->gT("Demo Mode Only: Uploading file is disabled in this system."),$basedestdir)."<br /><br />\n";
		$importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
		$importlabelresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
		return;
	}

	require("classes/phpzip/phpzip.inc.php");
	//$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];
	$zipfile=$_FILES['the_file']['tmp_name'];
	$z = new PHPZip();
	// Create temporary directory
	// If dangerous content is unzipped
	// then no one will know the path
	$extractdir=tempdir($tempdir);
	$basedestdir = $publicdir."/upload/labels";
	$destdir=$basedestdir."/$lid/";

	$importlabelresourcesoutput = "<br />\n";
	$importlabelresourcesoutput .= "<table class='alertbox'>\n";
	$importlabelresourcesoutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Import Label Set")."</strong></td></tr>\n";
	$importlabelresourcesoutput .= "\t<tr><td align='center'>\n";

	if (!is_writeable($basedestdir))
	{
		$importlabelresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	    $importlabelresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
		$importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
		$importlabelresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
		return;
	}

	if (!is_dir($destdir))
	{
		mkdir($destdir);
	}

	$aImportedFilesInfo=null;
	$aErrorFilesInfo=null;


	if (is_file($zipfile))
	{
		$importlabelresourcesoutput .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
		$importlabelresourcesoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
		$importlabelresourcesoutput .= $clang->gT("Reading file..")."<br />\n";

		if ($z->extract($extractdir,$zipfile) != 'OK')
		{
			$importlabelresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
			$importlabelresourcesoutput .= $clang->gT("This file is not a valid ZIP file archive. Import failed.")."<br /><br />\n";
			$importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
			$importlabelresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
			return;
		}

		// now read tempdir and copy authorized files only
		$dh = opendir($extractdir);
		while($direntry = readdir($dh))
		{
			if (($direntry!=".")&&($direntry!=".."))
			{
				if (is_file($extractdir."/".$direntry))
				{ // is  a file
					$extfile = substr(strrchr($direntry, '.'),1);
					if  (!(stripos(','.$allowedresourcesuploads.',',','.$extfile.',') === false))
					{ //Extension allowed
						if (!copy($extractdir."/".$direntry, $destdir.$direntry))
						{
							$aErrorFilesInfo[]=Array(
								"filename" => $direntry,
								"status" => $clang->gT("Copy failed")
							);
							unlink($extractdir."/".$direntry);
							
						}
						else
						{	
							$aImportedFilesInfo[]=Array(
								"filename" => $direntry,
								"status" => $clang->gT("OK")
							);
							unlink($extractdir."/".$direntry);
						}
					}
					
					else
					{ // Extension forbidden
						$aErrorFilesInfo[]=Array(
							"filename" => $direntry,
							"status" => $clang->gT("Error")." (".$clang->gT("Forbidden Extension").")"
						);
						unlink($extractdir."/".$direntry);
					}
				} // end if is_file
			} // end if ! . or ..
		} // end while read dir
		

		//Delete the temporary file
		unlink($zipfile);
		//Delete temporary folder
		rmdir($extractdir);

		// display summary
		$okfiles = 0;
		$errfiles= 0;
	        $ErrorListHeader .= "";
	        $ImportListHeader .= "";
		if (is_null($aErrorFilesInfo) && !is_null($aImportedFilesInfo))
		{
			$status=$clang->gT("Success");
			$color='green';
			$okfiles = count($aImportedFilesInfo);
		        $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
		}
		elseif (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
		{
			$importlabelresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
			$importlabelresourcesoutput .= $clang->gT("This ZIP archive contains no valid Resources files. Import failed.")."<br /><br />\n";
			$importlabelresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP Archive.")."<br /><br />\n";
			$importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
			$importlabelresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
			return;
			
		}
		elseif (!is_null($aErrorFilesInfo) && !is_null($aImportedFilesInfo))
		{
			$status=$clang->gT("Partial");
			$color='orange';
			$okfiles = count($aImportedFilesInfo);
			$errfiles = count($aErrorFilesInfo);
		        $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
		        $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
		}
		else
		{
			$status=$clang->gT("Error");
			$color='red';
			$errfiles = count($aErrorFilesInfo);
		        $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
		}

        		$importlabelresourcesoutput .= "<strong>".$clang->gT("Imported Resources for")." LID:</strong> $lid<br />\n";
		        $importlabelresourcesoutput .= "<br />\n<strong><font color='$color'>".$status."</font></strong><br />\n";
		        $importlabelresourcesoutput .= "<strong><u>".$clang->gT("Resources Import Summary")."</u></strong><br />\n";
		        $importlabelresourcesoutput .= "".$clang->gT("Total Imported files").": $okfiles<br />\n";
		        $importlabelresourcesoutput .= "".$clang->gT("Total Errors").": $errfiles<br />\n";
			$importlabelresourcesoutput .= $ImportListHeader;
			foreach ($aImportedFilesInfo as $entry)
			{
		        	$importlabelresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry["filename"]."</li>\n";
			}
		        $importlabelresourcesoutput .= "\t</ul><br /><br />\n";
			$importlabelresourcesoutput .= $ErrorListHeader;
			foreach ($aErrorFilesInfo as $entry)
			{
		        	$importlabelresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry['filename']." (".$entry['status'].")</li>\n";
			}
	}
	else
	{
		$importlabelresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	    $importlabelresourcesoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
		$importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
		$importlabelresourcesoutput .= "</td></tr></table><br />&nbsp;\n";
		return;
	}
			$importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
}



if ($action == "templateupload")
{
    if ($demoModeOnly === true)
    {
        $importtemplateresourcesoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("Demo mode: Uploading templates is disabled."),$basedestdir)."<br /><br />\n";
        $importtemplateoutput .= "</td></tr></table><br />&nbsp;\n";
        return;
    }

    require("classes/phpzip/phpzip.inc.php");
    //$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];
    $zipfile=$_FILES['the_file']['tmp_name'];
    $z = new PHPZip();
    // Create temporary directory
    // If dangerous content is unzipped
    // then no one will know the path
    $extractdir=tempdir($tempdir);
    $basedestdir = $templaterootdir;
    $newdir=str_replace('.','',strip_ext(sanitize_paranoid_string($_FILES['the_file']['name'])));
    $destdir=$basedestdir.'/'.$newdir.'/';

    $importtemplateoutput = "<br />\n";
    $importtemplateoutput .= "<table class='alertbox'>\n";
    $importtemplateoutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Import Template")."</strong></td></tr>\n";
    $importtemplateoutput .= "\t<tr><td align='center'>\n";

    if (!is_writeable($basedestdir))
    {
        $importtemplateoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
        $importtemplateoutput .= "</td></tr></table><br />&nbsp;\n";
        return;
    }

    if (!is_dir($destdir))
    {
        mkdir($destdir);
    }
    else
    {
        $importtemplateoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("Template '%s' does already exist."),$newdir)."<br /><br />\n";
        $importtemplateoutput .= "</td></tr></table><br />&nbsp;\n";
        return;
    }

    $aImportedFilesInfo=array();
    $aErrorFilesInfo=array();


    if (is_file($zipfile))
    {
        $importtemplateoutput .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
        $importtemplateoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
        $importtemplateoutput .= $clang->gT("Reading file..")."<br />\n";

        if ($z->extract($extractdir,$zipfile) != 'OK')
        {
            $importtemplateoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
            $importtemplateoutput .= $clang->gT("This file is not a valid ZIP file archive. Import failed.")."<br /><br />\n";
            $importtemplateoutput .= "</td></tr></table><br />&nbsp;\n";
            return;
        }

         $ErrorListHeader = "";
        $ImportListHeader = "";

        // now read tempdir and copy authorized files only
        $dh = opendir($extractdir);
        while($direntry = readdir($dh))
        {
            if (($direntry!=".")&&($direntry!=".."))
            {
                if (is_file($extractdir."/".$direntry))
                { // is  a file
                    $extfile = substr(strrchr($direntry, '.'),1);
                    if  (!(stripos(','.$allowedresourcesuploads.',',','.$extfile.',') === false))
                    { //Extension allowed
                        if (!copy($extractdir."/".$direntry, $destdir.$direntry))
                        {
                            $aErrorFilesInfo[]=Array(
                                "filename" => $direntry,
                                "status" => $clang->gT("Copy failed")
                            );
                            unlink($extractdir."/".$direntry);
                            
                        }
                        else
                        {    
                            $aImportedFilesInfo[]=Array(
                                "filename" => $direntry,
                                "status" => $clang->gT("OK")
                            );
                            unlink($extractdir."/".$direntry);
                        }
                    }
                    
                    else
                    { // Extension forbidden
                        $aErrorFilesInfo[]=Array(
                            "filename" => $direntry,
                            "status" => $clang->gT("Error")." (".$clang->gT("Forbidden Extension").")"
                        );
                        unlink($extractdir."/".$direntry);
                    }
                } // end if is_file
            } // end if ! . or ..
        } // end while read dir
        

        //Delete the temporary file
        unlink($zipfile);
        closedir($dh);
        //Delete temporary folder
        rmdir($extractdir);

        // display summary
        $okfiles = 0;
        $errfiles= 0;
        if (count($aErrorFilesInfo)==0 && count($aImportedFilesInfo)>0)
        {
            $status=$clang->gT("Success");
            $color='green';
            $okfiles = count($aImportedFilesInfo);
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        elseif (count($aErrorFilesInfo)==0 && count($aImportedFilesInfo)==0)
        {
            $importtemplateoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
            $importtemplateoutput .= $clang->gT("This ZIP archive contains no valid template files. Import failed.")."<br /><br />\n";
            $importtemplateoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP archives.")."<br /><br />\n";
            $importtemplateoutput .= "</td></tr></table><br />&nbsp;\n";
            return;
            
        }
        elseif (count($aErrorFilesInfo)>0 && count($aImportedFilesInfo)>0)
        {
            $status=$clang->gT("Partial");
            $color='orange';
            $okfiles = count($aImportedFilesInfo);
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        else
        {
            $status=$clang->gT("Error");
            $color='red';
            $errfiles = count($aErrorFilesInfo);
                $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
        }

        $importtemplateoutput .= "<strong>".$clang->gT("Imported template files for")."</strong> $lid<br />\n";
        $importtemplateoutput .= "<br />\n<strong><font color='$color'>".$status."</font></strong><br />\n";
        $importtemplateoutput .= "<strong><u>".$clang->gT("Resources Import Summary")."</u></strong><br />\n";
        $importtemplateoutput .= "".$clang->gT("Total Imported files").": $okfiles<br />\n";
        $importtemplateoutput .= "".$clang->gT("Total Errors").": $errfiles<br />\n";
        $importtemplateoutput .= $ImportListHeader;
        foreach ($aImportedFilesInfo as $entry)
        {
            $importtemplateoutput .= "\t<li>".$clang->gT("File").": ".$entry["filename"]."</li>\n";
        }
        $importtemplateoutput .= "\t</ul><br /><br />\n";
        $importtemplateoutput .= $ErrorListHeader;
        foreach ($aErrorFilesInfo as $entry)
        {
            $importtemplateoutput .= "\t<li>".$clang->gT("File").": ".$entry['filename']." (".$entry['status'].")</li>\n";
        }
    }
    else
    {
        $importtemplateoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
        $importtemplateoutput .= "</td></tr></table><br />&nbsp;\n";
        return;
    }
    $importtemplateoutput .= "<input type='submit' value='".$clang->gT("Open imported template")."' onclick=\"window.open('$scriptname?action=templates&templatename=$newdir', '_top')\">\n";
}


//---------------------
	// Comes from http://fr2.php.net/tempnam
 function tempdir($dir, $prefix='', $mode=0700)
  {
    if (substr($dir, -1) != '/') $dir .= '/';

    do
    {
      $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (!mkdir($path, $mode));

    return $path;
  }

    /**
    * Strips file extension
    * 
    * @param string $name
    * @return string
    */
    function strip_ext($name)
    {
         $ext = strrchr($name, '.');
         if($ext !== false)
         {
             $name = substr($name, 0, -strlen($ext));
         }
         return $name;
    }   
  
?>
