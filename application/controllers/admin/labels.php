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
  * @version $Id$
  * @access public
  */
 class labels extends Admin_Controller {

    /**
     * labels::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * labels::importlabelresources()
     * Function responsible to import label resources from a '.zip' file.
     * @return
     */
    function importlabelresources()
    {
        $clang = $this->limesurvey_lang;
        $action = $this->input->post('action');
        $lid = $this->input->post('lid');
        self::_getAdminHeader();
        self::_labelsetbar();
        if ($action == "importlabelresources" && $lid)
        {
            $importlabelresourcesoutput = "<div class='header ui-widget-header'>".$clang->gT("Import Label Set")."</div>\n";
            $importlabelresourcesoutput .= "<div class='messagebox ui-corner-all'>";

            if ($demoModeOnly === true)
            {
                $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importlabelresourcesoutput .= sprintf ($clang->gT("Demo mode only: Uploading files is disabled in this system."),$basedestdir)."<br /><br />\n";
                $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin/labels/view/'.$lid)."', '_top')\" />\n";
                $importlabelresourcesoutput .= "</div>\n";
                show_error($importlabelresourcesoutput);
                return;
            }

            //require("classes/phpzip/phpzip.inc.php");
            $this->load->library('admin/Phpzip');
            //$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];
            $zipfile=$_FILES['the_file']['tmp_name'];
            $z = $this->phpzip; // PHPZip();
            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir=self::_tempdir($this->config->item('tempdir'));
            $basedestdir = $this->config->item('publicdir')."/upload/labels";
            $destdir=$basedestdir."/$lid/";

            if (!is_writeable($basedestdir))
            {
                $importlabelresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importlabelresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
                $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin/labels/view/'.$lid)."', '_top')\" />\n";
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
                    $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin/labels/view/'.$lid)."', '_top')\" />\n";
                    $importlabelresourcesoutput .= "</div>\n";
                    show_error($importlabelresourcesoutput);
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
                    $importlabelresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP archives.")."<br /><br />\n";
                    $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin/labels/view/'.$lid)."', '_top')\" />\n";
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
                $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin/labels/view/'.$lid)."', '_top')\" />\n";
                $importlabelresourcesoutput .= "</div>\n";
                show_error($importlabelresourcesoutput);
                return;
            }
            $importlabelresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/labels/view/'.$lid)."', '_top')\">\n";
            $importlabelresourcesoutput .= "</div>\n";

            $data['display'] = $importlabeloutput;
            $this->load->view('survey_view',$data);
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


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
        $clang = $this->limesurvey_lang;
        $action = $this->input->post('action');
        self::_getAdminHeader();
        self::_labelsetbar();
        if ($action == 'importlabels')
        {
            $_POST = $this->input->post();
            $this->load->helper('admin/import');
            $importlabeloutput = "<div class='header ui-widget-header'>".$clang->gT("Import Label Set")."</div>\n";

            $sFullFilepath = $this->config->item('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = $aPathInfo['extension'];

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $importlabeloutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
                $importlabeloutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
                $importlabeloutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin')."', '_top')\"><br /><br />\n";
                show_error($importlabeloutput);
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
                $importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('".site_url('admin/labels/view')."', '_top')\" />\n";
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
                $importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('".site_url('admin/labels/view')."', '_top')\" />\n";
                $importlabeloutput .= "</div><br />\n";
            }


            $importlabeloutput .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br />\n";
            $importlabeloutput .= "<strong><u>".$clang->gT("Label set import summary")."</u></strong><br />\n";
            $importlabeloutput .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
            $importlabeloutput .= "\t<li>".$clang->gT("Labels").": {$aImportResults['labels']}</li></ul>\n";
            $importlabeloutput .= "<p><strong>".$clang->gT("Import of label set(s) is completed.")."</strong><br /><br />\n";
            $importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('".site_url('admin/labels/view')."', '_top')\" />\n";
            $importlabeloutput .= "</div><br />\n";

            $data['display'] = $importlabeloutput;
            $this->load->view('survey_view',$data);

        }
        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
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

        $this->load->helper('database');
        $this->load->helper('surveytranslator');
        $clang = $this->limesurvey_lang;


        self::_getAdminHeader();
        self::_labelsetbar($lid);

        if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_LABEL') == 1)
        {
            if ($action == "editlabelset")
            {
                $query = "SELECT label_name, ".$this->db->dbprefix."labelsets.lid, languages FROM ".$this->db->dbprefix."labelsets WHERE lid=".$lid;
                $result=db_execute_assoc($query);
                foreach ($result->result_array() as $row) {$lbname=$row['label_name']; $lblid=$row['lid']; $langids=$row['languages'];}
                $data['lbname'] = $lbname;
                $data['lblid'] = $lblid;
            }

            $data['clang'] = $clang;
            $data['action'] = $action;
            $data['lid'] = $lid;


            /**$labelsoutput.="<div class='header header_statistics'>\n"
            ."<input type='image' src='$imageurl/close.gif' align='right' "
            ."onclick=\"window.open('admin.php?action=labels&amp;lid=$lid', '_top')\" />\n"; */
            if ($action == "newlabelset") {$langids=$this->session->userdata('adminlang'); $tabitem=$clang->gT("Create New Label Set");}
            else { $tabitem=$clang->gT("Edit label set");}
            $langidsarray=explode(" ",trim($langids)); //Make an array of it
            //$labelsoutput.= "\n\t</div>\n";

            if (isset($row['lid'])) { $panecookie=$row['lid'];} else  {$panecookie='new';}

            $data['langids'] = $langids;
            $data['langidsarray'] = $langidsarray;
            $data['panecookie'] = $panecookie;
            $data['tabitem'] = $tabitem;
            /**$tab_title[0] = $tabitem;
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

            */
            $this->load->view('admin/labels/editlabel_view',$data);

        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * labels::view()
     * Function to view a labelset.
     * @param bool $lid
     * @return
     */
    function view($lid=false)
    {
        $clang = $this->limesurvey_lang;
        $action = 'labels';
        self::_getAdminHeader();
        self::_js_admin_includes(base_url().'scripts/admin/labels.js');
        self::_js_admin_includes(base_url().'scripts/admin/updateset.js');

        if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_LABEL') == 1)
        {

            self::_labelsetbar($lid);

            $this->load->model('labelsets_model');
            $condn = array('lid' => $lid);

            //$query = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
            $result = $this->labelsets_model->getAllRecords($condn); //($query);)

            if ($lid && $result->num_rows()>0)
            {
                //NOW GET THE ANSWERS AND DISPLAY THEM


                if ($result->num_rows()>0)
                {

                    $data['lid'] = $lid;
                    $data['clang'] = $clang;
                    $data['row'] = $result->row_array();
                    $this->load->view("admin/labels/labelbar_view",$data);
                }
                /**
                foreach ($result->result_array() as $row)
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
                */

                //LABEL ANSWERS  - SHOW THE MASK FOR EDITING THE LABELS

        		//$js_admin_includes[]='scripts/updateset.js';

                //$qulabelset = "SELECT * FROM ".db_table_name('labelsets')." WHERE lid=$lid";
                $rslabelset = $this->labelsets_model->getAllRecords($condn); //db_execute_assoc($qulabelset) or safe_die($connect->ErrorMsg());
                $rwlabelset=$rslabelset->row_array();
                $lslanguages=explode(" ", trim($rwlabelset['languages']));

                $this->load->helper("admin/htmleditor");

                PrepareEditorScript(true);

                $maxquery = "SELECT max(sortorder) as maxsortorder, sortorder FROM ".$this->db->dbprefix."labels WHERE lid=$lid and language='{$lslanguages[0]}'";
                $maxresult = db_execute_assoc($maxquery); // or safe_die($connect->ErrorMsg());
                $msorow=$maxresult->row_array();
                $maxsortorder=$msorow['maxsortorder']+1;
                $labelsoutput = "\n<script type=\"text/javascript\">\n<!--\n var ci_path = '".$this->config->item('imageurl')."'; //-->\n</script>\n";
                // labels table
                $labelsoutput .= "\t<div class='header ui-widget-header'>".$clang->gT("Labels")."\t</div>\n";
                $labelsoutput.= "<form method='post' id='mainform' action='".site_url('admin/labels/process')."' onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate label codes.",'js')."','".$clang->gT("Error: 'other' is a reserved keyword.",'js')."');\">\n"
                ."<input type='hidden' name='sortorder' value='{$msorow['sortorder']}' />\n"
                ."<input type='hidden' name='lid' value='$lid' />\n"
                ."<input type='hidden' name='action' value='modlabelsetanswers' />\n";
                $first=true;
                $sortorderids=''; $codeids='';
                $i = 0;
                $this->load->helper("surveytranslator");
                foreach ($lslanguages as $lslanguage)
                {

                    $position=0;
                    $query = "SELECT * FROM ".$this->db->dbprefix."labels WHERE lid=$lid and language='$lslanguage' ORDER BY sortorder, code";
                    $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg());
                    $labelcount = $result->num_rows();
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
                    foreach ($result->result_array() as $row)
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
                                <td><img src='".$this->config->item('imageurl')."/handle.png' /></td>
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
                             <img src='".$this->config->item('imageurl')."/addanswer.png' class='btnaddanswer' /><img src='".$this->config->item('imageurl')."/deleteanswer.png' class='btndelanswer' />
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

                $labelsoutput.="<div id='up_resmgmt'><div>\t<form class='form30' enctype='multipart/form-data' id='importlabelresources' name='importlabelresources' action='".site_url('admin/labels/importlabelresources')."' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
                . "\t<input type='hidden' name='lid' value='$lid' />\n"
                . "\t<input type='hidden' name='action' value='importlabelresources' />\n"
                . "\t<ul style='list-style-type:none; text-align:center'>\n"
                . "\t\t<li><label>&nbsp;</label>\n"
                . "\t\t<input type='button' $disabledIfNoResources onclick='window.open(\"".$this->config->item('sCKEditorURL')."/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php?\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\"  /></li>\n"
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
                              <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or space.')."' rows='30' style='width:570px;'></textarea>
                              <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                              <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                              <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                           </div> ";

                $displaydata['display'] = $labelsoutput;
                //$data['display'] = $editsurvey;
                $this->load->view('survey_view',$displaydata);


            }



        }


        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * labels::process()
     * Process labels form data depending on $action.
     * @return
     */
    function process()
    {
        if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_LABEL') == 1)
        {
            $_POST = $this->input->post();
            if (isset($_POST['sortorder'])) {$postsortorder=sanitize_int($_POST['sortorder']);}

            if (isset($_POST['method']) && get_magic_quotes_gpc())
            {
                $_POST['method']  = stripslashes($_POST['method']);
            }

            $action = $this->input->post('action');
            $this->load->helper('admin/labels');
            $lid = $this->input->post('lid');

            //DO DATABASE UPDATESTUFF
            if ($action == "updateset") {updateset($lid);}
            if ($action == "insertlabelset") {$lid=insertlabelset();}
            if (($action == "modlabelsetanswers")||($action == "ajaxmodlabelsetanswers")) {modlabelsetanswers($lid);}
            if ($action == "deletelabelset") {if (deletelabelset($lid)) {$lid=0;}}

            if ($lid)
            {
                redirect("admin/labels/view/".$lid);
            }
            else
            {
                redirect("admin/labels/view");
            }



        }
    }

	/**
	 * labels::exportmulti()
	 *
	 * @return
	 */
	function exportmulti()
	{
		self::_getAdminHeader();
		self::_labelsetbar(0);
        self::_js_admin_includes(base_url().'scripts/admin/labels.js');
		$data['clang'] = $this->limesurvey_lang;
        $data['labelsets'] = getlabelsets();
		$this->load->view('admin/labels/exportmulti_view', $data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	}

    /**
     * labels::_labelsetbar()
     * Load labelset menu.
     * @param integer $lid
     * @return
     */
    function _labelsetbar($lid=0)
    {
        $data['clang'] = $this->limesurvey_lang;
        $data['lid'] = $lid;
        $data['labelsets'] = getlabelsets();
        $this->load->view("admin/labels/labelsetsbar_view",$data);

    }


 }