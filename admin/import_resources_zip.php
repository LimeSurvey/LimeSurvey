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
    $importsurveyresourcesoutput = "<div class='header'>".$clang->gT("Import Survey Resources")."</div>\n";
    $importsurveyresourcesoutput .= "<div class='messagebox'>";

    if ($demoModeOnly === true)
    {
        $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importsurveyresourcesoutput .= $clang->gT("Demo Mode Only: Uploading file is disabled in this system.")."<br /><br />\n";
        $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\" />\n";
        $importsurveyresourcesoutput .= "</div>\n";
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

    if (!is_writeable($basedestdir))
    {
        $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importsurveyresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
        $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\" />\n";
        $importsurveyresourcesoutput .= "</div>\n";
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
        $importsurveyresourcesoutput .= "<div class=\"successheader\">".$clang->gT("Success")."</div><br />\n";
        $importsurveyresourcesoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
        $importsurveyresourcesoutput .= $clang->gT("Reading file..")."<br /><br />\n";

        if ($z->extract($extractdir,$zipfile) != 'OK')
        {
            $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
            $importsurveyresourcesoutput .= $clang->gT("This file is not a valid ZIP file archive. Import failed.")."<br /><br />\n";
            $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\" />\n";
            $importsurveyresourcesoutput .= "</div>\n";
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
            $statusClass='successheader';
            $okfiles = count($aImportedFilesInfo);
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        elseif (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
        {
            $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
            $importsurveyresourcesoutput .= $clang->gT("This ZIP archive contains no valid Resources files. Import failed.")."<br /><br />\n";
            $importsurveyresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP Archive.")."<br /><br />\n";
            $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\" />\n";
            $importsurveyresourcesoutput .= "</div>\n";
            return;

        }
        elseif (!is_null($aErrorFilesInfo) && !is_null($aImportedFilesInfo))
        {
            $status=$clang->gT("Partial");
            $statusClass='partialheader';
            $okfiles = count($aImportedFilesInfo);
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        else
        {
            $status=$clang->gT("Error");
            $statusClass='warningheader';
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
        }

        $importsurveyresourcesoutput .= "<strong>".$clang->gT("Imported Resources for")." SID:</strong> $surveyid<br /><br />\n";
        $importsurveyresourcesoutput .= "<div class=\"".$statusClass."\">".$status."</div><br />\n";
        $importsurveyresourcesoutput .= "<strong><u>".$clang->gT("Resources Import Summary")."</u></strong><br />\n";
        $importsurveyresourcesoutput .= "".$clang->gT("Total Imported files").": $okfiles<br />\n";
        $importsurveyresourcesoutput .= "".$clang->gT("Total Errors").": $errfiles<br />\n";
        $importsurveyresourcesoutput .= $ImportListHeader;
        foreach ($aImportedFilesInfo as $entry)
        {
            $importsurveyresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry["filename"]."</li>\n";
        }
        if (!is_null($aImportedFilesInfo))
        {
            $importsurveyresourcesoutput .= "\t</ul><br />\n";
        }
        $importsurveyresourcesoutput .= $ErrorListHeader;
        foreach ($aErrorFilesInfo as $entry)
        {
            $importsurveyresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry['filename']." (".$entry['status'].")</li>\n";
        }
        if (!is_null($aErrorFilesInfo))
        {
            $importsurveyresourcesoutput .= "\t</ul><br />\n";
        }
    }
    else
    {
        $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importsurveyresourcesoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
        $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\" />\n";
        $importsurveyresourcesoutput .= "</div>\n";
        return;
    }
    $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=editsurvey&sid=$surveyid', '_top')\" />\n";
    $importsurveyresourcesoutput .= "</div>\n";
}



if ($action == "importlabelresources" && $lid)
{
    $importlabelresourcesoutput = "<div class='header'>".$clang->gT("Import Label Set")."</div>\n";
    $importlabelresourcesoutput .= "<div class='messagebox'>";

    if ($demoModeOnly === true)
    {
        $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importlabelresourcesoutput .= sprintf ($clang->gT("Demo Mode Only: Uploading file is disabled in this system."),$basedestdir)."<br /><br />\n";
        $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\" />\n";
        $importlabelresourcesoutput .= "</div>\n";
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

    if (!is_writeable($basedestdir))
    {
        $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importlabelresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
        $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\" />\n";
        $importlabelresourcesoutput .= "</div>\n";
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
        $importlabelresourcesoutput .= "<div class=\"successheader\">".$clang->gT("Success")."</div><br />\n";
        $importlabelresourcesoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
        $importlabelresourcesoutput .= $clang->gT("Reading file..")."<br /><br />\n";

        if ($z->extract($extractdir,$zipfile) != 'OK')
        {
            $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
            $importlabelresourcesoutput .= $clang->gT("This file is not a valid ZIP file archive. Import failed.")."<br /><br />\n";
            $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\" />\n";
            $importlabelresourcesoutput .= "</div>\n";
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
            $statusClass='successheader';
            $okfiles = count($aImportedFilesInfo);
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        elseif (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
        {
            $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
            $importlabelresourcesoutput .= $clang->gT("This ZIP archive contains no valid Resources files. Import failed.")."<br /><br />\n";
            $importlabelresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP Archive.")."<br /><br />\n";
            $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\" />\n";
            $importlabelresourcesoutput .= "</div>\n";
            return;
        }
        elseif (!is_null($aErrorFilesInfo) && !is_null($aImportedFilesInfo))
        {
            $status=$clang->gT("Partial");
            $statusClass='partialheader';
            $okfiles = count($aImportedFilesInfo);
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        else
        {
            $status=$clang->gT("Error");
            $statusClass='warningheader';
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
        }

        $importlabelresourcesoutput .= "<strong>".$clang->gT("Imported Resources for")." LID:</strong> $lid<br /><br />\n";
        $importlabelresourcesoutput .= "<div class=\"".$statusClass."\">".$status."</div><br />\n";
        $importlabelresourcesoutput .= "<strong><u>".$clang->gT("Resources Import Summary")."</u></strong><br />\n";
        $importlabelresourcesoutput .= "".$clang->gT("Total Imported files").": $okfiles<br />\n";
        $importlabelresourcesoutput .= "".$clang->gT("Total Errors").": $errfiles<br />\n";
        $importlabelresourcesoutput .= $ImportListHeader;
        foreach ($aImportedFilesInfo as $entry)
        {
            $importlabelresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry["filename"]."</li>\n";
        }
        if (!is_null($aImportedFilesInfo))
        {
            $importlabelresourcesoutput .= "\t</ul><br />\n";
        }
        $importlabelresourcesoutput .= $ErrorListHeader;
        foreach ($aErrorFilesInfo as $entry)
        {
            $importlabelresourcesoutput .= "\t<li>".$clang->gT("File").": ".$entry['filename']." (".$entry['status'].")</li>\n";
        }
        if (!is_null($aErrorFilesInfo))
        {
            $importlabelresourcesoutput .= "\t</ul><br />\n";
        }
    }
    else
    {
        $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importlabelresourcesoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
        $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\" />\n";
        $importlabelresourcesoutput .= "</div>\n";
        return;
    }
    $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('$scriptname?action=labels&lid=$lid', '_top')\">\n";
    $importlabelresourcesoutput .= "</div>\n";
}



if ($action == "templateupload")
{
    $importtemplateoutput = "<div class='header'>".$clang->gT("Import Template")."</div>\n";
    $importtemplateoutput .= "<div class='messagebox'>";

    if ($demoModeOnly === true)
    {
        $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("Demo mode: Uploading templates is disabled."),$basedestdir)."<br/><br/>\n";
        $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=templates', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
        $importtemplateoutput .= "</div>\n";
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

    if (!is_writeable($basedestdir))
    {
        $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br/><br/>\n";
        $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=templates', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
        $importtemplateoutput .= "</div>\n";
        return;
    }

    if (!is_dir($destdir))
    {
        mkdir($destdir);
    }
    else
    {
        $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("Template '%s' does already exist."),$newdir)."<br/><br/>\n";
        $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=templates', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
        $importtemplateoutput .= "</div>\n";
        return;
    }

    $aImportedFilesInfo=array();
    $aErrorFilesInfo=array();


    if (is_file($zipfile))
    {
        $importtemplateoutput .= "<div class=\"successheader\">".$clang->gT("Success")."</div><br />\n";
        $importtemplateoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
        $importtemplateoutput .= $clang->gT("Reading file..")."<br /><br />\n";

        if ($z->extract($extractdir,$zipfile) != 'OK')
        {
            $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
            $importtemplateoutput .= $clang->gT("This file is not a valid ZIP file archive. Import failed.")."<br/><br/>\n";
            $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=templates', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
            $importtemplateoutput .= "</div>\n";
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
            $statusClass='successheader';
            $okfiles = count($aImportedFilesInfo);
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        elseif (count($aErrorFilesInfo)==0 && count($aImportedFilesInfo)==0)
        {
            $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
            $importtemplateoutput .= $clang->gT("This ZIP archive contains no valid template files. Import failed.")."<br /><br />\n";
            $importtemplateoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP archives.")."<br/><br/>\n";
            $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=templates', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
            $importtemplateoutput .= "</div>\n";
            return;

        }
        elseif (count($aErrorFilesInfo)>0 && count($aImportedFilesInfo)>0)
        {
            $status=$clang->gT("Partial");
            $statusClass='partialheader';
            $okfiles = count($aImportedFilesInfo);
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
            $ImportListHeader .= "<br /><strong><u>".$clang->gT("Imported Files List").":</u></strong><br />\n";
        }
        else
        {
            $status=$clang->gT("Error");
            $statusClass='warningheader';
            $errfiles = count($aErrorFilesInfo);
            $ErrorListHeader .= "<br /><strong><u>".$clang->gT("Error Files List").":</u></strong><br />\n";
        }

        $importtemplateoutput .= "<strong>".$clang->gT("Imported template files for")."</strong> $lid<br /><br />\n";
        $importtemplateoutput .= "<div class=\"".$statusClass."\">".$status."</div><br />\n";
        $importtemplateoutput .= "<strong><u>".$clang->gT("Resources Import Summary")."</u></strong><br />\n";
        $importtemplateoutput .= "".$clang->gT("Total Imported files").": $okfiles<br />\n";
        $importtemplateoutput .= "".$clang->gT("Total Errors").": $errfiles<br />\n";
        $importtemplateoutput .= $ImportListHeader;
        foreach ($aImportedFilesInfo as $entry)
        {
            $importtemplateoutput .= "\t<li>".$clang->gT("File").": ".$entry["filename"]."</li>\n";
        }
        if (!is_null($aImportedFilesInfo))
        {
            $importtemplateoutput .= "\t</ul><br />\n";
        }
        $importtemplateoutput .= $ErrorListHeader;
        foreach ($aErrorFilesInfo as $entry)
        {
            $importtemplateoutput .= "\t<li>".$clang->gT("File").": ".$entry['filename']." (".$entry['status'].")</li>\n";
        }
        if (!is_null($aErrorFilesInfo))
        {
            $importtemplateoutput .= "\t</ul><br />\n";
        }
    }
    else
    {
        $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
        $importtemplateoutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$basedestdir)."<br/><br/>\n";
        $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=templates', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
        $importtemplateoutput .= "</div>\n";
        return;
    }
    $importtemplateoutput .= "<input type='submit' value='".$clang->gT("Open imported template")."' onclick=\"window.open('$scriptname?action=templates&templatename=$newdir', '_top')\"/>\n";
    $importtemplateoutput .= "</div>\n";
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
