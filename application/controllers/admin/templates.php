<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey (tm)
* Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*
*/

/**
* templates
*
* @package LimeSurvey
* @author
* @copyright 2011
* @version $Id: templates.php 11189 2011-10-17 21:18:55Z tpartner $
* @access public
*/
class templates extends Survey_Common_Action {

    /**
     * Routes to correct sub-action
     *
     * @access public
     * @param string $sa
     */
	public function run($sa)
	{
		Yii::app()->loadHelper('replacements');
		Yii::app()->loadHelper('expressions.em_manager');
		if ($sa == 'fileredirect')
			$this->route('fileredirect', array('templatename', 'screenname', 'editfile'));
		elseif ($sa == 'screenredirect')
			$this->route('screenredirect', array('editfile', 'templatename', 'screenname'));
		elseif ($sa == 'view')
			$this->route('view', array('editfile', 'screenname', 'templatename'));
		elseif ($sa == 'upload')
			$this->route('upload', array());
		elseif ($sa == 'delete')
			$this->route('delete', array('templatename'));
		elseif ($sa == 'templatezip')
			$this->route('templatezip', array('templatename'));
		else
			$this->route($sa, array());

	}

	/**
	 * Exports a template
	 *
	 * @access public
	 * @param string $templatename
	 * @return void
	 */
	public function templatezip($templatename)
	{
		Yii::import('application.libraries.admin.Phpzip', true);
		$z = new PHPZip();
		$templatedir = sGetTemplatePath($templatename).DIRECTORY_SEPARATOR;
		$tempdir = Yii::app()->getConfig('tempdir');

		$zipfile = "$tempdir/$templatename.zip";
		$z->Zip($templatedir, $zipfile);
		if (is_file($zipfile))
		{
			//Send the file for download!
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

			header("Content-Type: application/force-download");
			header( "Content-Disposition: attachment; filename=$templatename.zip" );
			header( "Content-Description: File Transfer");
			@readfile($zipfile);

			//Delete the temporary file
			unlink($zipfile);
		}
	}

    /**
    * templates::upload()
    * function responsible to import a template archive.
    * @return void
    */
    function upload()
    {
        $clang = $this->controller->lang;

    	$this->controller->_getAdminHeader();
        $this->controller->_js_admin_includes(Yii::app()->baseUrl.'/scripts/admin/templates.js');

        self::_initialise('default','welcome', 'startpage.pstpl',FALSE);
        $lid = returnglobal('lid');
        $action = returnglobal('action');

        if ($action == 'templateupload')
        {
            $basedestdir = Yii::app()->getConfig('publicdir')."/upload/surveys";
            $importtemplateoutput = "<div class='header ui-widget-header'>".$clang->gT("Import template")."</div>\n";
            $importtemplateoutput .= "<div class='messagebox ui-corner-all'>";

            if (Yii::app()->getConfig('demoMode'))
            {
                $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importtemplateoutput .= sprintf ($clang->gT("Demo mode: Uploading templates is disabled."),$basedestdir)."<br/><br/>\n";
                $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view')."', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
                $importtemplateoutput .= "</div>\n";
                show_error($importtemplateoutput);
                return;
            }

            //require("classes/phpzip/phpzip.inc.php");
            Yii::import('application.libraries.admin.Phpzip', true);
            //$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];
            $zipfile=$_FILES['the_file']['tmp_name'];
        	$z = new PHPZip(); //new PHPZip();
            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir=self::_tempdir(Yii::app()->getConfig('tempdir'));
            $basedestdir = Yii::app()->getConfig('usertemplaterootdir');
            $newdir=str_replace('.','',self::_strip_ext(sanitize_paranoid_string($_FILES['the_file']['name'])));
            $destdir=$basedestdir.'/'.$newdir.'/';

            if (!is_writeable($basedestdir))
            {
                $importtemplateoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importtemplateoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br/><br/>\n";
                $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view')."', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
                $importtemplateoutput .= "</div>\n";
                show_error($importtemplateoutput);
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
                $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view')."', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
                $importtemplateoutput .= "</div>\n";
                die($importtemplateoutput);
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
                    $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view')."', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
                    $importtemplateoutput .= "</div>\n";
                    show_error($importtemplateoutput);
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
                            if  (!(stripos(','.Yii::app()->getConfig('allowedresourcesuploads').',',','.$extfile.',') === false))
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
                    $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view')."', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
                    $importtemplateoutput .= "</div>\n";
                    show_error($importtemplateoutput);
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
                $importtemplateoutput .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view')."', '_top')\" value=\"".$clang->gT("Template Editor")."\"/>\n";
                $importtemplateoutput .= "</div>\n";
                die($importtemplateoutput);
                return;
            }
            $importtemplateoutput .= "<input type='submit' value='".$clang->gT("Open imported template")."' onclick=\"window.open('".$this->controller->createUrl('admin/templates/sa/view/editfile/startpage.pstpl/screenname/welcome/templatename/'.$newdir)."', '_top')\"/>\n";
            $importtemplateoutput .= "</div>\n";

            $idata['display'] = $importtemplateoutput;
            $this->controller->render('/survey_view',$idata);

        }
        else
        {

            $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) { this.form.submit();}'";
            if (!function_exists("zip_open"))
            {
                $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
            }
            $templatesoutput= "<div class='header ui-widget-header'>".$clang->gT("Uploaded template file") ."</div>\n";


            $templatesoutput.= "\t<form enctype='multipart/form-data' id='importtemplate' name='importtemplate' action='".$this->controller->createUrl('admin/templates/sa/upload')."' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "\t<input type='hidden' name='lid' value='$lid' />\n"
            . "\t<input type='hidden' name='action' value='templateupload' />\n"
            . "\t<ul>\n"
            . "<li><label for='the_file'>".$clang->gT("Select template ZIP file:")."</label>\n"
            . "<input id='the_file' name='the_file' type=\"file\" size=\"50\" /></li>\n"
            . "<li><label>&nbsp;</label><input type='button' value='".$clang->gT("Import template ZIP archive")."' $ZIPimportAction /></li>\n"
            . "\t</ul></form>\n";
            $data['display'] = $templatesoutput;
            $this->controller->render('/survey_view',$data);
        }


        $this->controller->_loadEndScripts();


        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));


    }

    //---------------------
    // Comes from http://fr2.php.net/tempnam
    function _tempdir($dir, $prefix='', $mode=0700)
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
    function _strip_ext($name)
    {
        $ext = strrchr($name, '.');
        if($ext !== false)
        {
            $name = substr($name, 0, -strlen($ext));
        }
        return $name;
    }


    /**
    * templates::view()
    * Load default view screen of template controller.
    * @param string $editfile
    * @param string $screenname
    * @param string $templatename
    * @return
    */
    function view($editfile='startpage.pstpl', $screenname='welcome', $templatename='default')
    {
        $this->controller->_js_admin_includes(Yii::app()->baseUrl.'/scripts/admin/templates.js');
        $this->controller->_css_admin_includes(Yii::app()->baseUrl.'/scripts/admin/codemirror_ui/lib/CodeMirror-2.0/lib/codemirror.css');
        $this->controller->_css_admin_includes(Yii::app()->baseUrl.'/scripts/admin/codemirror_ui/lib/CodeMirror-2.0/mode/javascript/javascript.css');
        $this->controller->_css_admin_includes(Yii::app()->baseUrl.'/scripts/admin/codemirror_ui/css/codemirror-ui.css');

        $this->controller->_getAdminHeader();
        self::_initialise($templatename, $screenname, $editfile);

        $this->controller->_loadEndScripts();

        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));

        if ($screenname != 'welcome') {Yii::app()->session['step'] = 1;} else {unset(Yii::app()->session['step']);} //This helps handle the load/save buttons)


    }
    //temporary solution to the bug that crashes LS!
    /**
    * templates::screenredirect()
    * Function that modify order of arguments and pass to main viewing function i.e. view()
    * @param string $editfile
    * @param string $templatename
    * @param string $screenname
    * @return
    */
    function screenredirect($editfile='startpage.pstpl', $templatename='default', $screenname='welcome')
    {
        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/".$editfile."/screenname/".$screenname."/templatename/".$templatename));
    }
    //temporary solution to th bug that crashes LS!
    /**
    * templates::fileredirect()
    * Function that modify order of arguments and pass to main viewing function i.e. view()
    * @param string $templatename
    * @param string $screenname
    * @param string $editfile
    * @return
    */
    function fileredirect($templatename='default', $screenname='welcome', $editfile='startpage.pstpl')
    {
        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/".$editfile."/screenname/".$screenname."/templatename/".$templatename));
    }

    /**
    * templates::templatefiledelete()
    * Function responsible to delete a template file.
    * @return
    */
    function templatefiledelete()
    {
        if (returnglobal('action') == "templatefiledelete") {
            $the_full_file_path = Yii::app()->getConfig('usertemplaterootdir')."/".$_POST['templatename']."/".returnglobal('otherfile'); //This is where the temp file is
            unlink($the_full_file_path);
            $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/".returnglobal('editfile')."/screenname/".returnglobal('screenname')."/templatename/".returnglobal('templatename')));
        }

    }

    /**
    * templates::templaterename()
    * Function responsible to rename a template(folder).
    * @return
    */
    function templaterename()
    {

        if (returnglobal('action') == "templaterename" && returnglobal('newname') && returnglobal('copydir'))
        {
            $newdirname=Yii::app()->getConfig('usertemplaterootdir')."/".returnglobal('newname');
            $olddirname=Yii::app()->getConfig('usertemplaterootdir')."/".returnglobal('copydir');
            if(isStandardTemplate(returnglobal('newname')))
            {
                echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Template could not be renamed to `%s`.","js"), returnglobal('newname'))." ".$clang->gT("This name is reserved for standard template.","js")."\");\n//-->\n</script>";
            }
            elseif (rename($olddirname, $newdirname)==false)
            {
                echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Directory could not be renamed to `%s`.","js"), returnglobal('newname'))." ".$clang->gT("Maybe you don't have permission.","js")."\");\n//-->\n</script>";
            }
            else
            {
                //$templates[returnglobal('newname')]=$newdirname;
                $templatename=returnglobal('newname');
                self::view("startpage.pstpl","welcome",$templatename);
            }
        }
    }

    /**
    * templates::templatecopy()
    * Function responsible to copy a template.
    * @return
    */
    function templatecopy()
    {
        $clang = $this->controller->lang;
        if (returnglobal('action') == "templatecopy" && returnglobal('newname') && returnglobal('copydir')) {
            //Copies all the files from one template directory to a new one
            //This is a security issue because it is allowing copying from get variables...
            Yii::app()->loadHelper('admin/template');
            $newdirname=Yii::app()->getConfig('usertemplaterootdir')."/".returnglobal('newname');
            $copydirname=sGetTemplatePath(returnglobal('copydir'));
            $mkdirresult=mkdir_p($newdirname);
            if ($mkdirresult == 1) {
                $copyfiles=getListOfFiles($copydirname);
                foreach ($copyfiles as $file) {
                    $copyfile=$copydirname."/".$file;
                    $newfile=$newdirname."/".$file;
                    if (!copy($copyfile, $newfile)) {
                        echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Failed to copy %s to new template directory.","js"), $file)."\");\n//-->\n</script>";
                    }
                }
                //$templates[$newname]=$newdirname;
                $templatename=returnglobal('newname');
                self::view("startpage.pstpl","welcome",$templatename);
            } elseif($mkdirresult == 2) {
                echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Directory with the name `%s` already exists - choose another name","js"), returnglobal('newname'))."\");\n//-->\n</script>";
            } else {
                echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Unable to create directory `%s`.","js"), returnglobal('newname'))." ".$clang->gT("Please check the directory permissions.","js")."\");\n//-->\n</script>";
            }
        }

    }

    /**
    * templates::delete()
    * Function responsible to delete a template.
    * @param mixed $templatename
    * @return
    */
    function delete($templatename)
    {
        Yii::app()->loadHelper("admin/template");
        if (is_template_editable($templatename)==true)
        {
            $clang = $this->controller->lang;
            if (rmdirr(Yii::app()->getConfig('usertemplaterootdir')."/".$templatename)==true)
            {
                $condn = array('template' => $templatename);
            	$survey = Survey::model()->findAllByAttributes($condn);
            	foreach ($survey as $s)
            	{
            		$s->template = Yii::app()->getConfig('defaulttemplate');
            		$s->save();
            	}

                /**
                $templatequery = "UPDATE {$dbprefix}surveys set template='$defaulttemplate' where template='$templatename'\n";
                $connect->Execute($templatequery) or safe_die ("Couldn't update surveys with default template!<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked

                $templatequery = "UPDATE {$dbprefix}surveys set template='$defaulttemplate' where template='$templatename'\n";
                $connect->Execute($templatequery) or safe_die ("Couldn't update surveys with default template!<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked
                */
                $templatequery = "delete from {{templates_rights}} where folder='$templatename'\n";
                Yii::app()->db->createCommand($templatequery)->execute();//$connect->Execute($templatequery) or safe_die ("Couldn't update template_rights<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked)

                $templatequery = "delete from {{templates}} where folder='$templatename'\n";
                Yii::app()->db->createCommand($templatequery)->execute();//$connect->Execute($templatequery) or safe_die ("Couldn't update templates<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked

                Yii::app()->session['flashmessage'] = sprintf($clang->gT("Template '%s' was successfully deleted."),$templatename);
                //unset($templates[$templatename]);
                //$templatename = Yii::app()->getConfig('defaulttemplate');
            }
            else
            {
                Yii::app()->session['flashmessage'] = sprintf($clang->gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."),$templatename);
            }
        }
        // redirect with default templatename, editfile and screenname
        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view"));
    }

    /**
    * templates::templatesavechanges()
    * Function responsible to save the changes made in CodemMirror editor.
    * @return
    */
    function templatesavechanges()
    {

        if (returnglobal('changes')) {
            $changedtext=returnglobal('changes');
            $changedtext=str_replace ('<?','',$changedtext);
            if(get_magic_quotes_gpc())
            {
                $changedtext = stripslashes($changedtext);
            }
        }

        if (returnglobal('changes_cp')) {
            $changedtext=returnglobal('changes_cp');
            $changedtext=str_replace ('<?','',$changedtext);
            if(get_magic_quotes_gpc())
            {
                $changedtext = stripslashes($changedtext);
            }
        }

        $action = returnglobal('action');
        $editfile = returnglobal('editfile');
        $templatename = returnglobal('templatename');
        $screenname = returnglobal('screenname');
        $files = self::_initfiles($templatename);
        $cssfiles = self::_initcssfiles();

        if ($action=="templatesavechanges" && $changedtext) {

            Yii::app()->loadHelper('admin/template');
            $changedtext=str_replace("\r\n", "\n", $changedtext);

            if ($editfile) {
                // Check if someone tries to submit a file other than one of the allowed filenames
                if (multiarray_search($files,'name',$editfile)===false && multiarray_search($cssfiles,'name',$editfile)===false) {show_error('Invalid template filename');}  // Die you sneaky bastard!
                $savefilename=Yii::app()->getConfig('usertemplaterootdir')."/".$templatename."/".$editfile;
                if (is_writable($savefilename)) {
                    if (!$handle = fopen($savefilename, 'w')) {
                        echo "Could not open file ($savefilename)";
                        exit;
                    }
                    if (!fwrite($handle, $changedtext)) {
                        echo "Cannot write to file ($savefilename)";
                        exit;
                    }
                    fclose($handle);
                } else {
                    show_error( "The file $savefilename is not writable");

                }
            }
        }

        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/".$editfile."/screenname/".$screenname."/templatename/".$templatename));
    }



    /**
    * templates::_templatebar()
    * Load menu bar related to a template.
    * @param mixed $screenname
    * @param mixed $editfile
    * @param mixed $screens
    * @param mixed $tempdir
    * @param mixed $templatename
    * @return
    */
    function _templatebar($screenname,$editfile,$screens,$tempdir,$templatename)
    {
        $data['clang'] = $this->controller->lang;
        $data['screenname'] = $screenname;
        $data['editfile'] = $editfile;
        $data['screens'] = $screens;
        $data['tempdir'] = $tempdir;
        $data['templatename'] = $templatename;
        $data['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->controller->render("/admin/templates/templatebar_view",$data);

    }

    /**
    * templates::_templatesummary()
    * Load CodeMirror editor and various files information.
    * @param mixed $templatename
    * @param mixed $screenname
    * @param mixed $editfile
    * @param mixed $templates
    * @param mixed $files
    * @param mixed $cssfiles
    * @param mixed $otherfiles
    * @param mixed $myoutput
    * @return
    */
    function _templatesummary($templatename,$screenname,$editfile,$templates,$files,$cssfiles,$otherfiles,$myoutput)
    {

        $tempdir = Yii::app()->getConfig("tempdir");
        $tempurl = Yii::app()->getConfig("tempurl");

        Yii::app()->loadHelper("admin/template");
        $data = array();
        $time=date("ymdHis");
        // prepare textarea class for optional javascript
        $templateclasseditormode='full'; // default
        if (Yii::app()->session['templateeditormode']=='none'){$templateclasseditormode='none';}
        $data['templateclasseditormode'] = $templateclasseditormode;

        // The following lines are forcing the browser to refresh the templates on each save
        @$fnew=fopen("$tempdir/template_temp_$time.html", "w+");
        $data['time'] = $time;

        if(!$fnew)
        {
            $data['filenotwritten'] =  true;
        }
        else
        {
            @fwrite ($fnew, getHeader());
            foreach ($cssfiles as $cssfile)
            {
                $myoutput=str_replace($cssfile['name'],$cssfile['name']."?t=$time",$myoutput);
            }

            foreach($myoutput as $line) {
                @fwrite($fnew, $line);
            }
            @fclose($fnew);
        }


        $data['clang'] = $this->controller->lang;
        $data['screenname'] = $screenname;
        $data['editfile'] = $editfile;

        $data['tempdir'] = $tempdir;
        $data['templatename'] = $templatename;
        $data['templates'] = $templates;
        $data['files'] = $files;
        $data['cssfiles'] = $cssfiles;
        $data['otherfiles'] = $otherfiles;
        $data['tempurl'] = $tempurl;
        $data['time']= $time;

        $this->controller->render("/admin/templates/templatesummary_view",$data);

    }

    /**
    * templates::_initfiles()
    * Function that initialises file data.
    * @param mixed $templatename
    * @return
    */
    function _initfiles($templatename)
    {
        $files[]=array('name'=>'assessment.pstpl');
        $files[]=array('name'=>'clearall.pstpl');
        $files[]=array('name'=>'completed.pstpl');
        $files[]=array('name'=>'endgroup.pstpl');
        $files[]=array('name'=>'endpage.pstpl');
        $files[]=array('name'=>'groupdescription.pstpl');
        $files[]=array('name'=>'load.pstpl');
        $files[]=array('name'=>'navigator.pstpl');
        $files[]=array('name'=>'printanswers.pstpl');
        $files[]=array('name'=>'privacy.pstpl');
        $files[]=array('name'=>'question.pstpl');
        $files[]=array('name'=>'register.pstpl');
        $files[]=array('name'=>'save.pstpl');
        $files[]=array('name'=>'surveylist.pstpl');
        $files[]=array('name'=>'startgroup.pstpl');
        $files[]=array('name'=>'startpage.pstpl');
        $files[]=array('name'=>'survey.pstpl');
        $files[]=array('name'=>'welcome.pstpl');
        $files[]=array('name'=>'print_survey.pstpl');
        $files[]=array('name'=>'print_group.pstpl');
        $files[]=array('name'=>'print_question.pstpl');

        if(is_file(Yii::app()->getConfig('usertemplaterootdir').'/'.$templatename.'/question_start.pstpl'))
        {
            $files[]=array('name'=>'question_start.pstpl');
        }

        return $files;

    }

    /**
    * templates::_initcssfiles()
    * Function that initialises cssfile data.
    * @return
    */
    function _initcssfiles()
    {
        $cssfiles[]=array('name'=>'template.css');
        $cssfiles[]=array('name'=>'template-rtl.css');
        $cssfiles[]=array('name'=>'ie_fix_6.css');
        $cssfiles[]=array('name'=>'ie_fix_7.css');
        $cssfiles[]=array('name'=>'ie_fix_8.css');
		$cssfiles[]=array('name'=>'jquery-ui-custom.css');
        $cssfiles[]=array('name'=>'print_template.css');
        $cssfiles[]=array('name'=>'template.js');

        return $cssfiles;
    }

    /**
    * templates::_initialise()
    * Function that initialises all data and call other functions to load default view.
    * @param mixed $templatename
    * @param mixed $screenname
    * @param mixed $editfile
    * @param bool $showsummary
    * @return
    */
    function _initialise($templatename, $screenname, $editfile,$showsummary=TRUE)
    {
        global $siteadminname, $siteadminemail;
        $clang = $this->controller->lang;
        Yii::app()->loadHelper('admin/template');


        //Standard Template Subfiles
        //Only these files may be edited or saved
        /**
        $files[]=array('name'=>'assessment.pstpl');
        $files[]=array('name'=>'clearall.pstpl');
        $files[]=array('name'=>'completed.pstpl');
        $files[]=array('name'=>'endgroup.pstpl');
        $files[]=array('name'=>'endpage.pstpl');
        $files[]=array('name'=>'groupdescription.pstpl');
        $files[]=array('name'=>'load.pstpl');
        $files[]=array('name'=>'navigator.pstpl');
        $files[]=array('name'=>'printanswers.pstpl');
        $files[]=array('name'=>'privacy.pstpl');
        $files[]=array('name'=>'question.pstpl');
        $files[]=array('name'=>'register.pstpl');
        $files[]=array('name'=>'save.pstpl');
        $files[]=array('name'=>'surveylist.pstpl');
        $files[]=array('name'=>'startgroup.pstpl');
        $files[]=array('name'=>'startpage.pstpl');
        $files[]=array('name'=>'survey.pstpl');
        $files[]=array('name'=>'welcome.pstpl');
        $files[]=array('name'=>'print_survey.pstpl');
        $files[]=array('name'=>'print_group.pstpl');
        $files[]=array('name'=>'print_question.pstpl');
        */
        $files = self::_initfiles($templatename);



        //Standard CSS Files
        //These files may be edited or saved
        /**
        $cssfiles[]=array('name'=>'template.css');
        $cssfiles[]=array('name'=>'template-rtl.css');
        $cssfiles[]=array('name'=>'ie_fix_6.css');
        $cssfiles[]=array('name'=>'ie_fix_7.css');
        $cssfiles[]=array('name'=>'ie_fix_8.css');
        $cssfiles[]=array('name'=>'print_template.css');
        $cssfiles[]=array('name'=>'template.js');
        */
        $cssfiles = self::_initcssfiles();

        //Standard Support Files
        //These files may be edited or saved
        $supportfiles[]=array('name'=>'print_img_radio.png');
        $supportfiles[]=array('name'=>'print_img_checkbox.png');

        //Standard screens
        //Only these may be viewed

        $screens[]=array('name'=>$clang->gT('Survey List Page'),'id'=>'surveylist');
        $screens[]=array('name'=>$clang->gT('Welcome Page'),'id'=>'welcome');
        $screens[]=array('name'=>$clang->gT('Question Page'),'id'=>'question');
        $screens[]=array('name'=>$clang->gT('Completed Page'),'id'=>'completed');
        $screens[]=array('name'=>$clang->gT('Clear All Page'),'id'=>'clearall');
        $screens[]=array('name'=>$clang->gT('Register Page'),'id'=>'register');
        $screens[]=array('name'=>$clang->gT('Load Page'),'id'=>'load');
        $screens[]=array('name'=>$clang->gT('Save Page'),'id'=>'save');
        $screens[]=array('name'=>$clang->gT('Print answers page'),'id'=>'printanswers');
        $screens[]=array('name'=>$clang->gT('Printable survey page'),'id'=>'printablesurvey');

        //Page display blocks
        $SurveyList=array('startpage.pstpl',
        'surveylist.pstpl',
        'endpage.pstpl'
        );
        $Welcome=array('startpage.pstpl',
        'welcome.pstpl',
        'privacy.pstpl',
        'navigator.pstpl',
        'endpage.pstpl'
        );
        $Question=array('startpage.pstpl',
        'survey.pstpl',
        'startgroup.pstpl',
        'groupdescription.pstpl',
        'question.pstpl',
        'endgroup.pstpl',
        'navigator.pstpl',
        'endpage.pstpl'
        );
        $CompletedTemplate=array(
        'startpage.pstpl',
        'assessment.pstpl',
        'completed.pstpl',
        'endpage.pstpl'
        );
        $Clearall=array('startpage.pstpl',
        'clearall.pstpl',
        'endpage.pstpl'
        );
        $Register=array('startpage.pstpl',
        'survey.pstpl',
        'register.pstpl',
        'endpage.pstpl'
        );
        $Save=array('startpage.pstpl',
        'save.pstpl',
        'endpage.pstpl'
        );
        $Load=array('startpage.pstpl',
        'load.pstpl',
        'endpage.pstpl'
        );
        $printtemplate=array('startpage.pstpl',
        'printanswers.pstpl',
        'endpage.pstpl'
        );
        $printablesurveytemplate=array('print_survey.pstpl',
        'print_group.pstpl',
        'print_question.pstpl'
        );





        $file_version="LimeSurvey template editor ".Yii::app()->getConfig('versionnumber');
        Yii::app()->session['s_lang'] = Yii::app()->session['adminlang'];

        $templatename = sanitize_paranoid_string($templatename);
        //if (!isset($templatedir)) {$templatedir = sanitize_paranoid_string(returnglobal('templatedir'));}
        //$editfile = sanitize_filename($editfile);
        $screenname=auto_unescape($screenname);

        // Checks if screen name is in the list of allowed screen names
        if ( multiarray_search($screens,'id',$screenname)===false) {die('Invalid screen name');}  // Die you sneaky bastard! haha :P


        if (!isset($action)) {$action=sanitize_paranoid_string(returnglobal('action'));}
        if (!isset($subaction)) {$subaction=sanitize_paranoid_string(returnglobal('subaction'));}
        //if (!isset($otherfile)) {$otherfile = sanitize_filename(returnglobal('otherfile'));}
        if (!isset($newname)) {$newname = sanitize_paranoid_string(returnglobal('newname'));}
        if (!isset($copydir)) {$copydir = sanitize_paranoid_string(returnglobal('copydir'));}

        if(is_file(Yii::app()->getConfig('usertemplaterootdir').'/'.$templatename.'/question_start.pstpl'))
        {
            $files[]=array('name'=>'question_start.pstpl');
            $Question[]='question_start.pstpl';
        }

        $availableeditorlanguages=array('bg','cs','de','dk','en','eo','es','fi','fr','hr','it','ja','mk','nl','pl','pt','ru','sk','zh');
        $extension = substr(strrchr($editfile, "."), 1);
        if ($extension=='css' || $extension=='js') {$highlighter=$extension;} else {$highlighter='html';};
        if(in_array(Yii::app()->session['adminlang'],$availableeditorlanguages)) {$codelanguage=Yii::app()->session['adminlang'];}
        else  {$codelanguage='en';}



        $templates=gettemplatelist();
        if (!isset($templates[$templatename]))
        {
            $templatename = Yii::app()->getConfig('defaulttemplate');
        }

        $normalfiles=array("DUMMYENTRY", ".", "..", "preview.png");
        foreach ($files as $fl) {
            $normalfiles[]=$fl["name"];
        }
        foreach ($cssfiles as $fl) {
            $normalfiles[]=$fl["name"];
        }


        // Set this so common.php doesn't throw notices about undefined variables
        $thissurvey['active']='N';

        // ===========================   FAKE DATA FOR TEMPLATES
        $thissurvey['name']=$clang->gT("Template Sample");
        $thissurvey['description']=$clang->gT('This is a sample survey description. It could be quite long.').'<br /><br />'.$clang->gT("But this one isn't.");
        $thissurvey['welcome']=$clang->gT('Welcome to this sample survey').'<br />'.$clang->gT('You should have a great time doing this').'<br />';
        $thissurvey['allowsave']="Y";
        $thissurvey['active']="Y";
        $thissurvey['tokenanswerspersistence']="Y";
        $thissurvey['templatedir']=$templatename;
        $thissurvey['format']="G";
        $thissurvey['surveyls_url']="http://www.limesurvey.org/";
        $thissurvey['surveyls_urldescription']=$clang->gT("Some URL description");
        $thissurvey['usecaptcha']="A";
        $percentcomplete=makegraph(6, 10);
        $groupname=$clang->gT("Group 1: The first lot of questions");
        $groupdescription=$clang->gT("This group description is fairly vacuous, but quite important.");
        $navigator="<input class=\"submit\" type=\"submit\" value=\"".$clang->gT('Next')."&gt;&gt;\" name=\"move\" />\n";
        if ($screenname != 'welcome') {$navigator = "<input class=\"submit\" type=\"submit\" value=\"&lt;&lt;".$clang->gT('Previous')."\" name=\"move\" />\n".$navigator;}
        $help=$clang->gT("This is some help text.");
        $totalquestions="10";
        $surveyformat="Format";
        $completed = "<br /><span class='success'>".$clang->gT("Thank you!")."</span><br /><br />"
        .$clang->gT("Your survey responses have been recorded.")."<br /><br />\n";
        $notanswered="5";
        $privacy="";
        $surveyid="1295";
        $token=1234567;
        $assessments="<table align='center'><tr><th>".$clang->gT("Assessment heading")."</th></tr><tr><td align='center'>".$clang->gT("Assessment details")."<br />".$clang->gT("Note that this assessment section will only show if assessment rules have been set and assessment mode is activated.")."</td></tr></table>";
        $printoutput="<span class='printouttitle'><strong>".$clang->gT("Survey name (ID)")."</strong> Test survey (46962)</span><br />
        <table class='printouttable' >
        <tr><th>".$clang->gT("Question")."</th><th>".$clang->gT("Your answer")."</th></tr>
        <tr>
        <td>id</td>
        <td>12</td>
        </tr>
        <tr>
        <td>Date Submitted</td>

        <td>1980-01-01 00:00:00</td>
        </tr>
        <tr>
        <td>This is a sample question text. The user was asked to enter a date.</td>
        <td>2007-11-06</td>
        </tr>
        <tr>
        <td>This is another sample question text - asking for number. </td>
        <td>666</td>
        </tr>
        <tr>
        <td>This is one last sample question text - asking for some free text. </td>
        <td>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</td>
        </tr>
        </table>";

        $addbr=false;

        $templatedir=sGetTemplatePath($templatename);
        $templateurl=sGetTemplateURL($templatename);

        // save these variables in an array
        $data['thissurvey'] = $thissurvey;
        $data['percentcomplete'] = $percentcomplete;
        $data['groupname'] = $groupname;
        $data['groupdescription'] = $groupdescription;
        $data['navigator'] = $navigator;
        $data['help'] = $help;
        $data['surveyformat'] = $surveyformat;
        $data['totalquestions'] = $totalquestions;
        $data['completed'] = $completed;
        $data['notanswered'] = $notanswered;
        $data['privacy'] = $privacy;
        $data['surveyid'] = $surveyid;
        $data['token'] = $token;
        $data['assessments'] = $assessments;
        $data['printoutput'] = $printoutput;
        $data['templatedir'] = $templatedir;
        $data['templateurl'] = $templateurl;
        $data['templatename'] = $templatename;
        $data['screenname'] = $screenname;
        $data['editfile'] = $editfile;






        $myoutput[]="";
        switch($screenname) {
            case 'surveylist':
                unset($files);

                $list[]="<li class='surveytitle'><a href='#'>Survey Number 1</a></li>\n";
                $list[]="<li class='surveytitle'><a href='#'>Survey Number 2</a></li>\n";

                $surveylist=array(
                "nosid"=>$clang->gT("You have not provided a survey identification number"),
                "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail),
                "listheading"=>$clang->gT("The following surveys are available:"),
                "list"=>implode("\n",$list),
                );
                $data['surveylist'] = $surveylist;

                $myoutput[]="";
                foreach ($SurveyList as $qs) {
                    $files[]=array("name"=>$qs);
                    $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/$qs",$data));

                }
                break;

            case 'question':
                unset($files);
                foreach ($Question as $qs) {
                    $files[]=array("name"=>$qs);
                }
                $myoutput[]="<meta http-equiv=\"expires\" content=\"Wed, 26 Feb 1997 08:21:57 GMT\" />\n";
                $myoutput[]="<meta http-equiv=\"Last-Modified\" content=\"".gmdate('D, d M Y H:i:s'). " GMT\" />\n";
                $myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"no-store, no-cache, must-revalidate\" />\n";
                $myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"post-check=0, pre-check=0, false\" />\n";
                $myoutput[]="<meta http-equiv=\"Pragma\" content=\"no-cache\" />\n";
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/startpage.pstpl",$data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/survey.pstpl",$data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/startgroup.pstpl",$data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/groupdescription.pstpl",$data));

                $question = array(
                'all' => 'How many roads must a man walk down?'
                ,'text' => 'How many roads must a man walk down?'
                ,'code' => '1a'
                ,'help' => 'helpful text'
                ,'mandatory' => ''
                ,'man_message' => ''
                ,'valid_message' => ''
                ,'file_valid_message' => ''
                ,'essentials' => 'id="question1"'
                ,'class' => 'list-radio'
                ,'man_class' => ''
                ,'input_error_class' => ''
                ,'number' => '1'
                );
                $data['question'] = $question;

                $answer="<ul><li><input type='radio' class='radio' name='1' value='1' id='radio1' /><label class='answertext' for='radio1'>One</label></li><li><input type='radio' class='radio' name='1' value='2' id='radio2' /><label class='answertext' for='radio2'>Two</label></li><li><input type='radio' class='radio' name='1' value='3' id='radio3' /><label class='answertext' for='radio3'>Three</label></li></ul>\n";
                $data['answer'] = $answer;
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/question.pstpl",$data));

                //	$question='<span class="asterisk">*</span>'.$clang->gT("Please explain something in detail:");
                $answer="<textarea class='textarea' rows='5' cols='40'>Some text in this answer</textarea>";
                $data['answer'] = $answer;
                $question = array(
                'all' => '<span class="asterisk">*</span>'.$clang->gT("Please explain something in detail:")
                ,'text' => $clang->gT('Please explain something in detail:')
                ,'code' => '2a'
                ,'help' => ''
                ,'mandatory' => $clang->gT('*')
                ,'man_message' => ''
                ,'valid_message' => ''
                ,'file_valid_message' => ''
                ,'essentials' => 'id="question2"'
                ,'class' => 'text-long'
                ,'man_class' => 'mandatory'
                ,'input_error_class' => ''
                ,'number' => '2'
                );
                $data['question'] = $question;
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/question.pstpl",$data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/endgroup.pstpl",$data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/navigator.pstpl",$data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/endpage.pstpl",$data));
                break;

            case 'welcome':

                unset($files);

                $myoutput[]="";
                foreach ($Welcome as $qs) {
                    $files[]=array("name"=>$qs);
                    $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/$qs",$data));
                    //exit();

                }
                break;

            case 'register':
                unset($files);
                foreach($Register as $qs) {
                    $files[]=array("name"=>$qs);
                }
                $myoutput[] =  templatereplace("$templatedir/startpage.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/survey.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/register.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/endpage.pstpl",array(),$data);

                /**foreach(file("$templatedir/startpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/survey.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/register.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/endpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                */
                $myoutput[]= "\n";
                break;

            case 'save':
                unset($files);
                foreach($Save as $qs) {
                    $files[]=array("name"=>$qs);
                }

                $myoutput[] =  templatereplace("$templatedir/startpage.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/save.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/endpage.pstpl",array(),$data);
                /**
                foreach(file("$templatedir/startpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/save.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/endpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                */

                $myoutput[]= "\n";
                break;

            case 'load':
                unset($files);
                foreach($Load as $qs) {
                    $files[]=array("name"=>$qs);
                }
                $myoutput[] =  templatereplace("$templatedir/startpage.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/load.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/endpage.pstpl",array(),$data);

                /**
                foreach(file("$templatedir/startpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/load.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/endpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                } */
                $myoutput[]= "\n";
                break;

            case 'clearall':
                unset($files);
                foreach ($Clearall as $qs) {
                    $files[]=array("name"=>$qs);
                }
                $myoutput[] =  templatereplace("$templatedir/startpage.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/clear.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/endpage.pstpl",array(),$data);

                /**
                foreach(file("$templatedir/startpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/clearall.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/endpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                } */
                $myoutput[]= "\n";
                break;

            case 'completed':
                unset($files);
                $myoutput[]="";
                foreach ($CompletedTemplate as $qs) {
                    $files[]=array("name"=>$qs);
                    $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename)."/$qs",$data));
                }
                break;

            case 'printablesurvey':
                unset($files);
                foreach ($printablesurveytemplate as $qs) {
                    $files[]=array("name"=>$qs);
                }

                $questionoutput=array();
                foreach(file("$templatedir/print_question.pstpl") as $op)
                { // echo '<pre>line '.__LINE__.'$op = '.htmlspecialchars(print_r($op)).'</pre>';
                    $questionoutput[]=templatereplace($op, array(
                    'QUESTION_NUMBER'=>'1',
                    'QUESTION_CODE'=>'Q1',
                    'QUESTION_MANDATORY' => $clang->gT('*'),
                    'QUESTION_SCENARIO' => 'Only answer this if certain conditions are met.',    // if there are conditions on a question, list the conditions.
                    'QUESTION_CLASS' => ' mandatory list-radio',
                    'QUESTION_TYPE_HELP' => $clang->gT('Please choose *only one* of the following:'),
                    'QUESTION_MAN_MESSAGE' => '',        // (not sure if this is used) mandatory error
                    'QUESTION_VALID_MESSAGE' => '',        // (not sure if this is used) validation error
                    'QUESTION_FILE_VALID_MESSAGE' => '',        // (not sure if this is used) file validation error
                    'QUESTION_TEXT'=>'This is a sample question text. The user was asked to pick an entry.',
                    'QUESTIONHELP'=>'This is some help text for this question.',
                    'ANSWER'=>'<ul>
                    <li>
                    <img src="'.$templateurl.'/print_img_radio.png" alt="First choice" class="input-radio" height="14" width="14">First choice
                    </li>
                    <li>
                    <img src="'.$templateurl.'/print_img_radio.png" alt="Second choice" class="input-radio" height="14" width="14">Second choice
                    </li>
                    <li>
                    <img src="'.$templateurl.'/print_img_radio.png" alt="Third choice" class="input-radio" height="14" width="14">Third choice
                    </li>
                    </ul>'
                    ),$data);
                }
                $groupoutput=array();
                $groupoutput[] = templatereplace("$templatedir/print_group.pstpl",array('QUESTIONS'=>implode(' ',$questionoutput)),$data);
                /**
                foreach(file("$templatedir/print_group.pstpl") as $op)
                {
                $groupoutput[]=templatereplace($op, array('QUESTIONS'=>implode(' ',$questionoutput)),$data);
                }
                */
                $myoutput[] =  templatereplace("$templatedir/print_survey.pstpl",array('GROUPS'=>implode(' ',$groupoutput),
                'FAX_TO' => $clang->gT("Please fax your completed survey to:")." 000-000-000",
                'SUBMIT_TEXT'=> $clang->gT("Submit your survey."),
                'HEADELEMENTS'=>getPrintableHeader(),
                'SUBMIT_BY' => sprintf($clang->gT("Please submit by %s"), date('d.m.y')),
                'THANKS'=>$clang->gT('Thank you for completing this survey.'),
                'END'=>$clang->gT('This is the survey end message.')
                ),$data);
                /**
                foreach(file("$templatedir/print_survey.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op, array('GROUPS'=>implode(' ',$groupoutput),
                'FAX_TO' => $clang->gT("Please fax your completed survey to:")." 000-000-000",
                'SUBMIT_TEXT'=> $clang->gT("Submit your survey."),
                'HEADELEMENTS'=>getPrintableHeader(),
                'SUBMIT_BY' => sprintf($clang->gT("Please submit by %s"), date('d.m.y')),
                'THANKS'=>$clang->gT('Thank you for completing this survey.'),
                'END'=>$clang->gT('This is the survey end message.')
                ),$data);
                }
                */
                break;

            case 'printanswers':
                unset($files);
                foreach ($printtemplate as $qs)
                {
                    $files[]=array("name"=>$qs);
                }

                $myoutput[] =  templatereplace("$templatedir/startpage.pstpl",array(),$data);
                $myoutput[] =  templatereplace("$templatedir/printanswers.pstpl",array('ANSWERTABLE'=>$printoutput),$data);
                $myoutput[] =  templatereplace("$templatedir/endpage.pstpl",array(),$data);

                /**
                foreach(file("$templatedir/startpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                }
                foreach(file("$templatedir/printanswers.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array('ANSWERTABLE'=>$printoutput),$data);
                }
                foreach(file("$templatedir/endpage.pstpl") as $op)
                {
                $myoutput[]=templatereplace($op,array(),$data);
                } */
                $myoutput[]= "\n";
                break;
        }
        //$myoutput[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".Yii::app()->getConfig('standardtemplaterooturl')."/default/template.css\" />";
        $myoutput[]="</html>";

        if (is_array($files)) {
            $match=0;
            foreach ($files as $f) {
                if ($editfile == $f["name"]) {
                    $match=1;
                }
            }
            foreach ($cssfiles as $f) {
                if ($editfile == $f["name"]) {
                    $match=1;
                }
            }
            if ($match == 0) {
                if (count($files) > 0) {
                    $editfile=$files[0]["name"];
                } else {
                    $editfile="";
                }
            }
        }

        //Get list of 'otherfiles'
        $otherfiles=array();
        if ($handle = opendir($templatedir)) {
            while(false !== ($file = readdir($handle))) {
                if (!array_search($file, $normalfiles)) {
                    if (!is_dir($templatedir.DIRECTORY_SEPARATOR.$file)) {
                        $otherfiles[]=array("name"=>$file);
                    }
                }
            } // while
            closedir($handle);
        }


        $data['clang'] = $this->controller->lang;
        $data['codelanguage'] = $codelanguage;
        $data['highlighter'] = $highlighter;
        //$data['allowedtemplateuploads'] = Yii::app()->getConfig('allowedtemplateuploads');
        $data['screens'] = $screens;
        $data['templatename'] = $templatename;
        $data['templates'] = $templates;
        $data['editfile'] = $editfile;
        $data['screenname'] = $screenname;
        $data['tempdir'] = Yii::app()->getConfig('tempdir');
        $data['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->controller->render("/admin/templates/templateeditorbar_view",$data);

        if ($showsummary)
            self::_templatesummary($templatename,$screenname,$editfile,$templates,$files,$cssfiles,$otherfiles,$myoutput);
    }

}
