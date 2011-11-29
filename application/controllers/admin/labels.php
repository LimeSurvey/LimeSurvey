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
  * labels
  *
  * @package LimeSurvey
  * @author
  * @copyright 2011
  * @version $Id: labels.php 11246 2011-10-23 20:46:05Z c_schmitz $
  * @access public
  */
 class labels extends Survey_Common_Action {

 	protected $yii;

    /**
     * routes to the correct subdir
     *
     * @access public
     * @param string $sa
     * @return void
     */
 	public function run($sa)
 	{
 		$this->yii = Yii::app();

 		if ($sa == 'newlabelset' || $sa == 'editlabelset' || $sa == 'index')
 			$this->route('index', array('sa', 'lid' ));
 		elseif ($sa == 'import')
 			$this->route('import', array());
 		elseif ($sa == 'importlabelresources')
 			$this->route('importlabelresources', array());
 		elseif ($sa == 'exportmulti')
 			$this->route('exportmulti', array());
 		elseif ($sa == 'process')
 			$this->route('process', array());
 		elseif ($sa == 'view')
 			$this->route('view', array('lid'));
 	}

    /**
     * labels::importlabelresources()
     * Function responsible to import label resources from a '.zip' file.
     * @return
     */
    function importlabelresources()
    {
        $clang = $this->controller->lang;
        $action = returnglobal('action');
        $lid = returnglobal('lid');
        $this->controller->_getAdminHeader();
        $this->_labelsetbar();
        if ($action == "importlabelresources" && $lid)
        {
            $importlabelresourcesoutput = "<div class='header ui-widget-header'>".$clang->gT("Import Label Set")."</div>\n";
            $importlabelresourcesoutput .= "<div class='messagebox ui-corner-all'>";

            if (Yii::app()->getConfig('demoMode'))
            {
                $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importlabelresourcesoutput .= sprintf ($clang->gT("Demo mode only: Uploading files is disabled in this system."),$basedestdir)."<br /><br />\n";
                $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".$this->createUrl('admin/labels/sa/view/lid/'.$lid)."', '_top')\" />\n";
                $importlabelresourcesoutput .= "</div>\n";
                show_error($importlabelresourcesoutput);
                return;
            }

            //require("classes/phpzip/phpzip.inc.php");
            Yii::import('application.libraries.admin.Phpzip');
            //$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];
            $zipfile=$_FILES['the_file']['tmp_name'];
            $z = new Phpzip(); // PHPZip();
            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir=self::_tempdir(Yii::app()->getConfig('tempdir'));
            $basedestdir = Yii::app()->getConfig('publicdir')."/upload/labels";
            $destdir=$basedestdir."/$lid/";

            if (!is_writeable($basedestdir))
            {
                $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importlabelresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
                $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".$this->createUrl('admin/labels/sa/view/lid/'.$lid)."', '_top')\" />\n";
                $importlabelresourcesoutput .= "</div>\n";
                show_error($importlabelresourcesoutput);
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
                    $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".$this->createUrl('admin/labels/sa/view/lid/'.$lid)."', '_top')\" />\n";
                    $importlabelresourcesoutput .= "</div>\n";
                    show_error($importlabelresourcesoutput);
                    return;
                }

                // now read tempdir and copy authorized files only
                $dh = opendir($extractdir);
            	$aErrorFilesInfo = array();
            	$aImportedFilesInfo = array();
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
                //Delete temporary folder
                rmdir($extractdir);

                // display summary
                $okfiles = 0;
                $errfiles= 0;
                $ErrorListHeader = "";
                $ImportListHeader = "";
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
                    $importlabelresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP archives.")."<br /><br />\n";
                    $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".$this->createUrl('admin/labels/sa/view/lid/'.$lid)."', '_top')\" />\n";
                    $importlabelresourcesoutput .= "</div>\n";
                    show_error($importlabelresourcesoutput);
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
                $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".$this->controller->createUrl('admin/labels/sa/view/lid/'.$lid)."', '_top')\" />\n";
                $importlabelresourcesoutput .= "</div>\n";
                die($importlabelresourcesoutput);
                return;
            }
            $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->controller->createUrl('admin/labels/sa/view/lid/'.$lid)."', '_top')\">\n";
            $importlabelresourcesoutput .= "</div>\n";

            $data['display'] = $importlabelresourcesoutput;
            $this->controller->render('/survey_view',$data);
        }

        $this->controller->_loadEndScripts();


        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));


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
     * labels::import()
     * Function to import a label set
     * @return
     */
    function import()
    {
        $clang = $this->controller->lang;
        $action = returnglobal('action');
        $this->controller->_getAdminHeader();
        $this->_labelsetbar();
        if ($action == 'importlabels')
        {
            Yii::app()->loadHelper('admin/import');
            $importlabeloutput = "<div class='header ui-widget-header'>".$clang->gT("Import Label Set")."</div>\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = !empty($aPathInfo['extension']) ? $aPathInfo['extension'] : '';
            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $importlabeloutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
                $importlabeloutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),Yii::app()->getConfig('tempdir'))."<br /><br />\n";
                $importlabeloutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".$this->controller->createUrl('admin')."', '_top')\"><br /><br />\n";
            	die($importlabeloutput);
                return;
            }

            $importlabeloutput .= "<div class='messagebox ui-corner-all'><div class='successheader'>".$clang->gT("Success")."</div><br />\n";
            $importlabeloutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
            $importlabeloutput .= $clang->gT("Reading file..")."<br /><br />\n";
            $options['checkforduplicates']='off';
            if (isset($_POST['checkforduplicates']))
            {
                $options['checkforduplicates']=$_POST['checkforduplicates'];
            }

            if (strtolower($sExtension)=='csv')
            {
                $aImportResults=CSVImportLabelset($sFullFilepath, $options);
            }
            elseif (strtolower($sExtension)=='lsl')
            {
                $aImportResults=XMLImportLabelsets($sFullFilepath, $options);
            }
            else
            {
                $importlabeloutput .= "<br />\n<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
                $importlabeloutput .= "<strong><u>".$clang->gT("Label set import summary")."</u></strong><br />\n";
                $importlabeloutput .= $clang->gT("Uploaded label set file needs to have an .lsl extension.")."<br /><br />\n";
                $importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('".$this->controller->createUrl('admin/labels/sa/view')."', '_top')\" />\n";
                $importlabeloutput .= "</div><br />\n";
            }
            unlink($sFullFilepath);

            if (isset($aImportResults))
            {
                if (count($aImportResults['warnings'])>0)
                {
                    $importlabeloutput .= "<br />\n<div class='warningheader'>".$clang->gT("Warnings")."</div><ul>\n";
                    foreach ($aImportResults['warnings'] as $warning)
                    {
                        $importlabeloutput .= '<li>'.$warning.'</li>';
                    }
                    $importlabeloutput .= "</ul>\n";
                }

                $importlabeloutput .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br />\n";
                $importlabeloutput .= "<strong><u>".$clang->gT("Label set import summary")."</u></strong><br />\n";
                $importlabeloutput .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
                $importlabeloutput .= "\t<li>".$clang->gT("Labels").": {$aImportResults['labels']}</li></ul>\n";
                $importlabeloutput .= "<p><strong>".$clang->gT("Import of label set(s) is completed.")."</strong><br /><br />\n";
                $importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('".$this->controller->createUrl('admin/labels/sa/view')."', '_top')\" />\n";
                $importlabeloutput .= "</div><br />\n";
            }

            $data['display'] = $importlabeloutput;
            $this->controller->render('/survey_view',$data);

        }
        $this->controller->_loadEndScripts();


        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    /**
     * labels::index()
     * Function to load new/edit labelset screen.
     * @param mixed $action
     * @param integer $lid
     * @return
     */
    function index($action,$lid=0)
    {
		$lid = sanitize_int($lid);

        Yii::app()->loadHelper('surveytranslator');
        $clang = $this->controller->lang;

        $this->controller->_getAdminHeader();
        $this->_labelsetbar($lid);

        if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
        {
            if ($action == "editlabelset")
            {
                $query = "SELECT label_name, {{labelsets}}.lid, languages FROM {{labelsets}} WHERE lid=".$lid;
                $result=Yii::app()->db->createCommand($query)->query();
                foreach ($result->readAll() as $row) {$lbname=$row['label_name']; $lblid=$row['lid']; $langids=$row['languages'];}
                $data['lbname'] = $lbname;
                $data['lblid'] = $lblid;
            }

            $data['clang'] = $clang;
            $data['action'] = $action;
            $data['lid'] = $lid;



            if ($action == "newlabelset") {$langids=Yii::app()->session['adminlang']; $tabitem=$clang->gT("Create New Label Set");}
            else { $tabitem=$clang->gT("Edit label set");}
            $langidsarray=explode(" ",trim($langids)); //Make an array of it

            if (isset($row['lid'])) { $panecookie=$row['lid'];} else  {$panecookie='new';}

            $data['langids'] = $langids;
            $data['langidsarray'] = $langidsarray;
            $data['panecookie'] = $panecookie;
            $data['tabitem'] = $tabitem;

            $this->controller->render('/admin/labels/editlabel_view',$data);

        }

        $this->controller->_loadEndScripts();


        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));

    }

 	/**
 	 * labels::view()
 	 * Function to view a labelset.
 	 * @param bool $lid
 	 * @return
 	 */
 	function view($lid=false)
 	{
 		// Escapes the id variable
 		if($lid!=false) $lid = sanitize_int($lid);
 		// Gets the current language
 		$clang = $this->controller->lang;
 		$action = 'labels';

 		// Loads admin header
 		$this->controller->_getAdminHeader();
 		// Includes some javascript files
 		$this->controller->_js_admin_includes($this->yii->baseUrl.'/scripts/admin/labels.js');
 		$this->controller->_js_admin_includes($this->yii->baseUrl.'/scripts/admin/updateset.js');

 		// Checks if user have the sufficient rights to manage the labels
 		if($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1 || $this->yii->session['USER_RIGHT_MANAGE_LABEL'] == 1)
 		{
 			// Sets the menubar
 			self::_labelsetbar($lid);

 			// Inititalize labalset model
 			$labelset = new Labelset;
 			//Set condition and params for the query
 			$condn = 'lid=:lid';
 			$params = array(':lid' => $lid);

 			// Get a result containing labelset with the specified id
 			$result = $labelset->find($condn, $params); //($query) ;)

 			// If there is label id in the variable $lid and there are labelset records in the database
 			$labelset_exists = Labelset::model()->exists($condn, $params);
 			if ($lid && $labelset_exists)
 			{
 				// Now recieve all labelset information and display it

 				$data['lid'] = $lid;
 				$data['clang'] = $clang;
 				$data['row'] = $result;

 				// Display a specific labelbar menu
 				$this->controller->render("/admin/labels/labelbar_view",$data);

 				$rwlabelset = $result; //db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
 				// Make languages array from the current row
 				$lslanguages = explode(" ", trim( $result['languages']));

 				$this->yii->loadHelper("admin/htmleditor");

 				PrepareEditorScript(true, $this->controller);

 				$label = new Label;
 				$connection = $this->yii->db;
 				$maxquery = "SELECT max(sortorder) as maxsortorder, sortorder FROM ".$label->tableName()." WHERE lid=$lid and language='{$lslanguages[0]}'";
 				$command = $connection->createCommand($maxquery);
 				$maxresult = $command->queryAll();
 				$msorow = $maxresult[0];
 				$maxsortorder=$msorow['maxsortorder']+1;
 				$labelsoutput = "\n<script type=\"text/javascript\">\n<!--\n var ci_path = '".$this->yii->getConfig('imageurl')."'; //-->\n</script>\n";
 				// labels table
 				$labelsoutput .= "\t<div class='header ui-widget-header'>".$clang->gT("Labels")."\t</div>\n";
 				$labelsoutput.= "<form method='post' id='mainform' action='".$this->controller->createUrl('admin/labels/sa/process')."' onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate label codes.",'js')."','".$clang->gT("Error: 'other' is a reserved keyword.",'js')."');\">\n"
 				."<input type='hidden' name='sortorder' value='{$msorow['sortorder']}' />\n"
 				."<input type='hidden' name='lid' value='$lid' />\n"
 				."<input type='hidden' name= 'action' value='modlabelsetanswers' />\n";
 				$first=true;
 				$sortorderids=''; $codeids='';
 				$i = 0;
 				$this->yii->loadHelper("surveytranslator");
 				foreach ($lslanguages as $lslanguage)
 				{
 					$position=0;
 					$query = "SELECT * FROM ".$label->tableName()." WHERE lid=$lid and language='$lslanguage' ORDER BY sortorder, code";
 					$query_count = "SELECT count(*) FROM ".$label->tableName()." WHERE lid=$lid and language='$lslanguage' ORDER BY sortorder, code";
 					$result = $connection->createCommand($query); // or safe_die($connect->ErrorMsg());
 					$labelcount = $connection->createCommand($query_count)->queryScalar();
 					$tab_title[$i] = getLanguageNameFromCode($lslanguage,false);

 					$tab_content[$i] = "
 					    <input type='hidden' class='lslanguage' value='{$lslanguage}'>
 					    <table class='answertable' align='center'>
 					        <thead align='center'>
 					            <tr>";

 					if ($first)
 						$tab_content[$i] .= "<th>&nbsp;</th>";

 					$tab_content[$i] .= "<th>{$clang->gT("Code")}</th>
 					                <th>{$clang->gT("Assessment value")}</th>
 					                <th>{$clang->gT("Title")}</th>";

 					if ($first)
 						$tab_content[$i] .= "<th>{$clang->gT("Action")}</th>";

 					$tab_content[$i] .= "</tr>
 					        </thead>
 					        <tbody align='center'>
 					";

 					$alternate=false;
 					foreach ($result->queryAll() as $row)
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
 							    <td><img src='".$this->yii->getConfig('imageurl')."/handle.png' /></td>
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
 							<img src='".$this->yii->getConfig('imageurl')."/addanswer.png' class='btnaddanswer' /><img src='".$this->yii->getConfig('imageurl')."/deleteanswer.png' class='btndelanswer' />
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
 				$ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) { this.form.submit();}'";
 				if (!function_exists("zip_open"))
 				{
 					$ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
 				}

 				$labelsoutput.="<div id='up_resmgmt'><div>\t<form class='form30' enctype='multipart/form-data' id='importlabelresources' name='importlabelresources' action='".$this->controller->createUrl('admin/labels/sa/importlabelresources')."' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
 				. "\t<input type='hidden' name='lid' value='$lid' />\n"
 				. "\t<input type='hidden' name='action' value='importlabelresources' />\n"
 				. "\t<ul style='list-style-type:none; text-align:center'>\n"
 				. "\t\t<li><label>&nbsp;</label>\n"
 				. "\t\t<input type='button' $disabledIfNoResources onclick='window.open(\"".$this->yii->getConfig('sCKEditorURL')."/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php?\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\"  /></li>\n"
 				. "\t\t<li><label>&nbsp;</label>\n"
 				. "\t\t<input type='button' $disabledIfNoResources onclick='window.open(\"scriptname?action=exportlabelresources&amp;lid={$lid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\"  /></li>\n"
 				. "\t\t<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
 				. "\t\t<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
 				. "\t\t<li><label>&nbsp;</label>\n"
 				. "\t\t<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
 				. "\t\t</ul></form></div></div>\n";

 				$labelsoutput .= "</div>";

 				$labelsoutput .= "<div id='quickadd' name='{$clang->gT('Quick add')}'style='display:none;'><div style='float:left;'>
 				              <label for='quickadd'>".$clang->gT('Enter your labels:')."</label>
 				              <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or space.')."' rows='30' cols='100' style='width:570px;'></textarea>
 				              <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                              <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                              <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                           </div> ";

 				$displaydata['display'] = $labelsoutput;
 				//$data['display'] = $editsurvey;
 				$this->controller->render('/survey_view', $displaydata);


 			}



 		}

 		$this->controller->_loadEndScripts();

 		$this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

 	}

    /**
     * labels::process()
     * Process labels form data depending on $action.
     * @return
     */
    function process()
    {
        if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
        {
            if (isset($_POST['sortorder'])) {$postsortorder=sanitize_int($_POST['sortorder']);}

            if (isset($_POST['method']) && get_magic_quotes_gpc())
            {
                $_POST['method']  = stripslashes($_POST['method']);
            }

            $action = returnglobal('action');
            Yii::app()->loadHelper('admin/label');
            $lid = returnglobal('lid');

            //DO DATABASE UPDATESTUFF
            if ($action == "updateset") {updateset($lid);}
            if ($action == "insertlabelset") {$lid=insertlabelset();}
            if (($action == "modlabelsetanswers")||($action == "ajaxmodlabelsetanswers")) {modlabelsetanswers($lid);}
            if ($action == "deletelabelset") {if (deletelabelset($lid)) {$lid=0;}}

            if ($lid)
                $this->controller->redirect($this->controller->createUrl("admin/labels/sa/view/lid/".$lid));
            else
                $this->controller->redirect($this->controller->createUrl("admin/labels/sa/view"));
        }
    }

	/**
	 * labels::exportmulti()
	 *
	 * @return
	 */
	function exportmulti()
	{
		$this->controller->_getAdminHeader();
		$this->_labelsetbar(0);
        $this->controller->_js_admin_includes(Yii::app()->baseUrl.'scripts/admin/labels.js');
		$data['clang'] = $this->controller->lang;
        $data['labelsets'] = getlabelsets();
		$this->controller->render('/admin/labels/exportmulti_view', $data);
		$this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
	}

    /**
     * labels::_labelsetbar()
     * Load labelset menu.
     * @param integer $lid
     * @return
     */
    function _labelsetbar($lid=0)
    {
    	$lid = (int) $lid;
        $data['clang'] = $this->controller->lang;
        $data['lid'] = $lid;
        $data['labelsets'] = getlabelsets();
        $this->controller->render("/admin/labels/labelsetsbar_view",$data);
    }
 }