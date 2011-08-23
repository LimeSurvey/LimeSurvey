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
 * $Id$
 */

 /**
  * survey
  *
  * @package LimeSurvey
  * @author
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class survey extends Survey_Common_Controller {

    /**
     * survey::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * survey::importsurveyresources()
     * Function responsible to import survey reasources.
     * @return
     */
    function importsurveyresources()
    {
        $clang = $this->limesurvey_lang;
        $action = $this->input->post('action');
        $surveyid = $this->input->post('sid');
        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,$action);

        if ($action == "importsurveyresources" && $surveyid)
        {
            $importsurveyresourcesoutput = "<div class='header ui-widget-header'>".$clang->gT("Import survey resources")."</div>\n";
            $importsurveyresourcesoutput .= "<div class='messagebox ui-corner-all'>";

            if ($demoModeOnly === true)
            {
                $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importsurveyresourcesoutput .= $clang->gT("Demo Mode Only: Uploading file is disabled in this system.")."<br /><br />\n";
                $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                $importsurveyresourcesoutput .= "</div>\n";
                show_error($importsurveyresourcesoutput);
                return;
            }

            //require("classes/phpzip/phpzip.inc.php");
            $this->load->library('admin/Phpzip');
            $zipfile=$_FILES['the_file']['tmp_name'];
            $z = $this->phpzip; //new PHPZip();

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir=self::_tempdir($this->config->item('tempdir'));
            $basedestdir = $this->config->item('publicdir')."/upload/surveys";
            $destdir=$basedestdir."/$surveyid/";

            if (!is_writeable($basedestdir))
            {
                $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importsurveyresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
                $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                $importsurveyresourcesoutput .= "</div>\n";
                show_error($importsurveyresourcesoutput);
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
                    $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                    $importsurveyresourcesoutput .= "</div>\n";
                    show_error($importsurveyresourcesoutput);
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
                    $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                    $importsurveyresourcesoutput .= $clang->gT("This ZIP archive contains no valid Resources files. Import failed.")."<br /><br />\n";
                    $importsurveyresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP archives.")."<br /><br />\n";
                    $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                    $importsurveyresourcesoutput .= "</div>\n";
                    show_error($importsurveyresourcesoutput);
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
                $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                $importsurveyresourcesoutput .= "</div>\n";
                show_error($importsurveyresourcesoutput);
                return;
            }
            $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".site_url('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
            $importsurveyresourcesoutput .= "</div>\n";

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
	 * survey::view()
	 * Load complete view of survey properties and actions specified by $surveyid
	 * @param mixed $surveyid
	 * @param mixed $gid
	 * @param mixed $qid
	 * @return
	 */
	function view($surveyid,$gid=null,$qid=null)
	{
	    // show till question menubar.
        if (!is_null($qid))
        {
            $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
   			$this->config->set_item("css_admin_includes", $css_admin_includes);

   			self::_getAdminHeader();
   			self::_showadminmenu($surveyid);
   			self::_surveybar($surveyid,$gid);
            self::_surveysummary($surveyid,"viewquestion");
   			self::_questiongroupbar($surveyid,$gid,$qid,"viewquestion");

            self::_questionbar($surveyid,$gid,$qid,"viewquestion");
            self::_loadEndScripts();


   			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

        }
        else
        {
            //show till questiongroup menu bar.
            if (!is_null($gid))
            {
                $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
       			$this->config->set_item("css_admin_includes", $css_admin_includes);

       			self::_getAdminHeader();
       			self::_showadminmenu($surveyid);
       			self::_surveybar($surveyid,$gid);
                self::_surveysummary($surveyid,"viewgroup");
       			self::_questiongroupbar($surveyid,$gid,$qid,"viewgroup");

                self::_loadEndScripts();


       			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

            }
            else
            {
                //show till survey menu bar.
                if(bHasSurveyPermission($surveyid,'survey','read'))
        	    {
                    $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        			$this->config->set_item("css_admin_includes", $css_admin_includes);

        			self::_getAdminHeader();
        			self::_showadminmenu($surveyid);
        			self::_surveybar($surveyid);
        			self::_surveysummary($surveyid);
                    self::_loadEndScripts();


        			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

        		}

            }


        }




	}

    /**
     * survey::deactivate()
     * Function responsible to deactivate a survey.
     * @param mixed $surveyid
     * @return
     */
    function deactivate($surveyid)
    {

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);

	   self::_getAdminHeader();
	   self::_showadminmenu($surveyid);

        //$postsid=returnglobal('sid');
        if ($this->input->post('sid'))
        {
            $postsid = $this->input->post('sid');
        }
        else
        {
            $postsid = $surveyid;
        }
        $clang = $this->limesurvey_lang;
        $date = date('YmdHis'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day
        $_POST = $this->input->post();
        if (!isset($_POST['ok']) || !$_POST['ok'])
        {
            /**$deactivateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
            $deactivateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Deactivate Survey")." ($surveyid)</div>\n";
            $deactivateoutput .= "\t<div class='warningheader'>\n";
            $deactivateoutput .= $clang->gT("Warning")."<br />".$clang->gT("READ THIS CAREFULLY BEFORE PROCEEDING");
            $deactivateoutput .= "</div>\n";
            $deactivateoutput .= "\t".$clang->gT("In an active survey, a table is created to store all the data-entry records.")."\n";
            $deactivateoutput .= "\t<p>".$clang->gT("When you deactivate a survey all the data entered in the original table will be moved elsewhere, and when you activate the survey again, the table will be empty. You will not be able to access this data using LimeSurvey any more.")."</p>\n";
            $deactivateoutput .= "\t<p>".$clang->gT("Deactivated survey data can only be accessed by system administrators using a Database data access tool like phpmyadmin. If your survey uses tokens, this table will also be renamed and will only be accessible by system administrators.")."</p>\n";
            $deactivateoutput .= "\t<p>".$clang->gT("Your responses table will be renamed to:")." {$dbprefix}old_{$_GET['sid']}_{$date}</p>\n";
            $deactivateoutput .= "\t<p>".$clang->gT("Also you should export your responses before deactivating.")."</p>\n";
            $deactivateoutput .= "\t<input type='submit' value='".$clang->gT("Deactivate Survey")."' onclick=\"".get2post("$scriptname?action=deactivate&amp;ok=Y&amp;sid={$_GET['sid']}")."\" />\n";
            $deactivateoutput .= "</div><br />\n"; */

            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['date'] = $date;
            $data['dbprefix'] = $this->db->dbprefix;
            $data['step1'] = true;
            self::_surveybar($surveyid);
            $this->load->view('admin/Survey/deactivateSurvey_view',$data);
        }

        else
        {
            $this->load->helper('database');
            //See if there is a tokens table for this survey
            if (tableExists("tokens_{$postsid}"))
            {
                $toldtable=$this->db->dbprefix."tokens_{$postsid}";
                $tnewtable=$this->db->dbprefix."old_tokens_{$postsid}_{$date}";
                //$tdeactivatequery = db_rename_table(db_table_name_nq($toldtable) ,db_table_name_nq($tnewtable));
                $tdeactivateresult = db_rename_table($toldtable ,$tnewtable) or die ("Couldn't deactivate tokens table because:<br /><br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");

                if ($this->db->dbdriver=='postgres')
                {
                    // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
                    //$deactivatequery = db_rename_table(db_table_name_nq($toldtable).'_tid_seq',db_table_name_nq($tnewtable).'_tid_seq');
                    $deactivateresult = db_rename_table($toldtable.'_tid_seq',$tnewtable.'_tid_seq') or die ("Could not rename the old sequence for this token table.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
                    $setsequence="ALTER TABLE ".$this->db->dbprefix.$tnewtable." ALTER COLUMN tid SET DEFAULT nextval('".$this->db->dbprefix.$tnewtable."_tid_seq'::regclass);";
                    $deactivateresult = db_execute_assosc($setsequence) or die ("Could not alter the field 'tid' to point to the new sequence name for this token table. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
                    $setidx="ALTER INDEX ".$this->db->dbprefix.$toldtable."_idx RENAME TO ".$this->db->dbprefix.$tnewtable."_idx;";
                    $deactivateresult = db_execute_assosc($setidx) or die ("Could not alter the index for this token table. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");

                }
                $data['tnewtable'] = $tnewtable;
                $data['toldtable'] = $toldtable;
            }

            // IF there are any records in the saved_control table related to this survey, they have to be deleted
            $query = "DELETE FROM ".$this->db->dbprefix."saved_control WHERE sid={$postsid}";
            //$result = $connect->Execute($query);
            $result = db_execute_assoc($query);
            $oldtable=$this->db->dbprefix."survey_{$postsid}";
            $newtable=$this->db->dbprefix."old_survey_{$postsid}_{$date}";

            //Update the auto_increment value from the table before renaming
            $new_autonumber_start=0;
            $query = "SELECT id FROM $oldtable ORDER BY id desc";
            $result = db_select_limit_assoc($query, 1,0, false, false);
            if ($result)
            {
                foreach ($result->result_array() as $row)
                {
                    if (strlen($row['id']) > 12) //Handle very large autonumbers (like those using IP prefixes)
                    {
                        $part1=substr($row['id'], 0, 12);
                        $part2len=strlen($row['id'])-12;
                        $part2=sprintf("%0{$part2len}d", substr($row['id'], 12, strlen($row['id'])-12)+1);
                        $new_autonumber_start="{$part1}{$part2}";
                    }
                    else
                    {
                        $new_autonumber_start=$row['id']+1;
                    }
                }
            }
            /**
            $query = "UPDATE ".$this->db->dbprefix."surveys SET autonumber_start=$new_autonumber_start WHERE sid=$postsid";
            //echo "hello".$new_autonumber_start."---".$postsid;
            $result = db_execute_assosc($query); //Note this won't die if it fails - that's deliberate.
            */
            $condn = array('sid' => $surveyid);
            $insertdata = array('autonumber_start' => $new_autonumber_start);
            $this->load->model('surveys_model');
            $this->surveys_model->updateSurvey($insertdata,$condn);

            //$deactivatequery = db_rename_table($oldtable,$newtable);
            $deactivateresult = db_rename_table($oldtable,$newtable) or die ("Couldn't make backup of the survey table. Please try again. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");

            if ($this->db->dbdriver=='postgres')
            {
                // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
                //$deactivatequery = db_rename_table($oldtable.'_id_seq',$newtable.'_id_seq');
                $deactivateresult = db_rename_table($oldtable.'_id_seq',$newtable.'_id_seq') or die ("Couldn't make backup of the survey table. Please try again. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
                $setsequence="ALTER TABLE $newtable ALTER COLUMN id SET DEFAULT nextval('{$newtable}_id_seq'::regclass);";
                $deactivateresult = db_execute_assosc($setsequence) or die ("Couldn't make backup of the survey table. Please try again. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
            }

            //            $dict = NewDataDictionary($connect);
            //            $dropindexquery=$dict->DropIndexSQL(db_table_name_nq($oldtable).'_idx');
            //            $connect->Execute($dropindexquery[0]);


            $insertdata = array('active' => 'N');
            //$this->load->model('surveys_model');

            /**
            $deactivatequery = "UPDATE ".$this->db->dbprefix."surveys SET active='N' WHERE sid=$surveyid"; */
            $deactivateresult = $this->surveys_model->updateSurvey($insertdata,$condn) or die ("Couldn't deactivate because updating of surveys table failed!<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>Admin</a>");

            /**
            $deactivateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
            $deactivateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Deactivate Survey")." ($surveyid)</div>\n";
            $deactivateoutput .= "\t<div class='successheader'>".$clang->gT("Survey Has Been Deactivated")."\n";
            $deactivateoutput .= "</div>\n";
            $deactivateoutput .= "\t<p>\n";
            $deactivateoutput .= "\t".$clang->gT("The responses table has been renamed to: ")." $newtable.\n";
            $deactivateoutput .= "\t".$clang->gT("The responses to this survey are no longer available using LimeSurvey.")."\n";
            $deactivateoutput .= "\t<p>".$clang->gT("You should note the name of this table in case you need to access this information later.")."</p>\n";
            if (isset($toldtable) && $toldtable)
            {
                $deactivateoutput .= "\t".$clang->gT("The tokens table associated with this survey has been renamed to: ")." $tnewtable.\n";
            }
            $deactivateoutput .= "\t<p>".$clang->gT("Note: If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure. See the LimeSurvey documentation for further details")."</p>";
            $deactivateoutput .= "</div><br/>&nbsp;\n";
            */



            $pquery = "SELECT savetimings FROM ".$this->db->dbprefix."surveys WHERE sid={$postsid}";
            $presult=db_execute_assoc($pquery);
            $prow=$presult->row_array(); //fetch savetimings value
            if ($prow['savetimings'] == "Y")
            {
        		$oldtable=$this->db->dbprefix."survey_{$postsid}_timings";
        		$newtable=$this->db->dbprefix."old_survey_{$postsid}_timings_{$date}";

        		//$deactivatequery = db_rename_table($oldtable,$newtable);
        		$deactivateresult2 = db_rename_table($oldtable,$newtable) or die ("Couldn't make backup of the survey timings table. Please try again.<br /><br />Survey was deactivated.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
        		$deactivateresult=($deactivateresult && $deactivateresult2);
            }

            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['newtable'] = $newtable;
            self::_surveybar($surveyid);
            $this->load->view('admin/Survey/deactivateSurvey_view',$data);

        }

        self::_loadEndScripts();


	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
     * survey::activate()
     * Function responsible to activate survey.
     * @param mixed $surveyid
     * @return
     */
    function activate($surveyid)
    {

       $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
	   $this->config->set_item("css_admin_includes", $css_admin_includes);

	   self::_getAdminHeader();
	   self::_showadminmenu($surveyid);
	   self::_surveybar($surveyid);




        //$postsid=returnglobal('sid');
        if ($this->input->post('sid'))
        {
            $postsid = $this->input->post('sid');
        }
        else
        {
            $postsid = $surveyid;
        }

        $qtypes=getqtypelist('','array');
        $this->load->helper("admin/activate");
        $_POST = $this->input->post();


        if (!isset($_POST['ok']) || !$_POST['ok'])
        {
            if (isset($_GET['fixnumbering']) && $_GET['fixnumbering'])
            {
                fixNumbering($_GET['fixnumbering']);
            }

            // Check consistency for groups and questions
            $failedgroupcheck = checkGroup($postsid);
            $failedcheck = checkQuestions($postsid, $surveyid, $qtypes);

            $data['clang'] = $this->limesurvey_lang;
            $data['surveyid'] =  $surveyid;
            $data['$failedcheck'] = $failedcheck;
            $data['failedgroupcheck'] = $failedgroupcheck;


            $this->load->view("admin/Survey/activateSurvey_view",$data);
            //IF ANY OF THE CHECKS FAILED, PRESENT THIS SCREEN
            /**if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck))
            {
                $activateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
                $activateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
                $activateoutput .= "<div class='warningheader'>\n".$clang->gT("Error")."<br />\n";
                $activateoutput .= $clang->gT("Survey does not pass consistency check")."</div>\n";
                $activateoutput .= "<p>\n";
                $activateoutput .= "<strong>".$clang->gT("The following problems have been found:")."</strong><br />\n";
                $activateoutput .= "<ul>\n";
                if (isset($failedcheck) && $failedcheck)
                {
                    foreach ($failedcheck as $fc)
                    {
                        $activateoutput .= "<li> Question qid-{$fc[0]} (\"<a href='$scriptname?sid=$surveyid&amp;gid=$fc[3]&amp;qid=$fc[0]'>{$fc[1]}</a>\"){$fc[2]}</li>\n";
                    }
                }
                if (isset($failedgroupcheck) && $failedgroupcheck)
                {
                    foreach ($failedgroupcheck as $fg)
                    {
                        $activateoutput .= "\t\t\t\t<li> Group gid-{$fg[0]} (\"<a href='$scriptname?sid=$surveyid&amp;gid=$fg[0]'>{$fg[1]}</a>\"){$fg[2]}</li>\n";
                    }
                }
                $activateoutput .= "</ul>\n";
                $activateoutput .= $clang->gT("The survey cannot be activated until these problems have been resolved.")."\n";
                $activateoutput .= "</div><br />&nbsp;\n";

                return;
            }

            $activateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
            $activateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
            $activateoutput .= "<div class='warningheader'>\n";
            $activateoutput .= $clang->gT("Warning")."<br />\n";
            $activateoutput .= $clang->gT("READ THIS CAREFULLY BEFORE PROCEEDING")."\n";
            $activateoutput .= "\t</div>\n";
            $activateoutput .= $clang->gT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing.")."<br /><br />\n";
            $activateoutput .= $clang->gT("Once a survey is activated you can no longer:")."<ul><li>".$clang->gT("Add or delete groups")."</li><li>".$clang->gT("Add or delete questions")."</li><li>".$clang->gT("Add or delete subquestions or change their codes")."</li></ul>\n";
            $activateoutput .= $clang->gT("However you can still:")."<ul><li>".$clang->gT("Edit your questions code/title/text and advanced options")."</li><li>".$clang->gT("Edit your group names or descriptions")."</li><li>".$clang->gT("Add, remove or edit answer options")."</li><li>".$clang->gT("Change survey name or description")."</li></ul>\n";
            $activateoutput .= $clang->gT("Once data has been entered into this survey, if you want to add or remove groups or questions, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table.")."<br /><br />\n";
            $activateoutput .= "\t<input type='submit' value=\"".$clang->gT("Activate Survey")."\" onclick=\"".get2post("$scriptname?action=activate&amp;ok=Y&amp;sid={$_GET['sid']}")."\" />\n";
            $activateoutput .= "</div><br />&nbsp;\n";
            */

        }
        else
        {
            $activateoutput = activateSurvey($postsid,$surveyid);
            $displaydata['display'] = $activateoutput;
            //$data['display'] = $editsurvey;
            $this->load->view('survey_view',$displaydata);
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }


    /**
     * survey::listsurveys()
     * Function that load list of surveys and it's few quick properties.
     * @return
     */
    function listsurveys()
    {
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $this->load->helper('surveytranslator');

        //self::_js_admin_includes(base_url().'scripts/admin/surveysettings.js');

        self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
        self::_js_admin_includes(base_url().'scripts/admin/listsurvey.js');

        self::_getAdminHeader();
        self::_showadminmenu(false);;


        $query = " SELECT a.*, c.*, u.users_name FROM ".$this->db->dbprefix."surveys as a "
        ." INNER JOIN ".$this->db->dbprefix."surveys_languagesettings as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) AND surveyls_survey_id=a.sid AND surveyls_language=a.language "
        ." INNER JOIN ".$this->db->dbprefix."users as u ON (u.uid=a.owner_id) ";

        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
        {
            $query .= "WHERE a.sid in (select sid from ".$this->db->dbprefix."survey_permissions WHERE uid=".$this->session->userdata('loginID')." AND permission='survey' AND read_p=1) ";
        }

        $query .= " ORDER BY surveyls_title";
        $this->load->helper('database');
        $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg()); //Checked

        if($result->num_rows() > 0) {

           /**$listsurveys= "<br /><table class='listsurveys'><thead>
                      <tr>
                        <th colspan='7'>&nbsp;</th>
                        <th colspan='3'>".$clang->gT("Responses")."</th>
                        <th colspan='2'>&nbsp;</th>
                      </tr>
    				  <tr>
    				    <th>".$clang->gT("Status")."</th>
                        <th>".$clang->gT("SID")."</th>
    				    <th>".$clang->gT("Survey")."</th>
    				    <th>".$clang->gT("Date created")."</th>
    				    <th>".$clang->gT("Owner") ."</th>
    				    <th>".$clang->gT("Access")."</th>
    				    <th>".$clang->gT("Anonymized responses")."</th>
    				    <th>".$clang->gT("Full")."</th>
                        <th>".$clang->gT("Partial")."</th>
                        <th>".$clang->gT("Total")."</th>
                        <th>".$clang->gT("Tokens available")."</th>
                        <th>".$clang->gT("Response rate")."</th>
    				  </tr></thead>
    				  <tfoot><tr class='header ui-widget-header'>
    		<td colspan=\"12\">&nbsp;</td>".
    		"</tr></tfoot>
    		<tbody>"; */
            $gbc = "evenrow";
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $listsurveys = "";
            $first_time = true;
            foreach ($result->result_array() as $rows)
            {

                if($rows['anonymized']=="Y")
                {
                    $privacy=$clang->gT("Yes") ;
                }
                else
                {
                    $privacy =$clang->gT("No") ;
                }


                if (tableExists('tokens_'.$rows['sid']))
                {
                    $visibility = $clang->gT("Closed");
                }
                else
                {
                    $visibility = $clang->gT("Open");
                }

                if($rows['active']=="Y")
                {

                    if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $status=$clang->gT("Expired") ;
                    }
                    elseif ($rows['startdate']!='' && $rows['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $status=$clang->gT("Not yet active") ;
                    }
                    else {
                        $status=$clang->gT("Active") ;
                    }

                    // Complete Survey Responses - added by DLR
                    $gnquery = "SELECT COUNT(id) AS countofid FROM ".$this->db->dbprefix."survey_".$rows['sid']." WHERE submitdate IS NULL";
                    $gnresult = db_execute_assoc($gnquery); //Checked)

                    foreach ($gnresult->result_array() as $gnrow)
                    {
                        $partial_responses=$gnrow['countofid'];
                    }
                    $gnquery = "SELECT COUNT(id) AS countofid FROM ".$this->db->dbprefix."survey_".$rows['sid'];
                    $gnresult = db_execute_assoc($gnquery); //Checked
                    foreach ($gnresult->result_array() as $gnrow)
                    {
                        $responses=$gnrow['countofid'];
                    }

                }
                else $status = $clang->gT("Inactive") ;

                if ($first_time) // can't use same instance of Date_Time_Converter library every time!
                {
                    $this->load->library('Date_Time_Converter',array($rows['datecreated'], "Y-m-d H:i:s"));
                    $datetimeobj = $this->date_time_converter ; // new Date_Time_Converter($rows['datecreated'] , "Y-m-d H:i:s");
                    $first_time = false;

                }
                else
                {
                    // no need to load the library again, just make a new instance!
                    $datetimeobj = new Date_Time_Converter(array($rows['datecreated'], "Y-m-d H:i:s"));

                }


                $datecreated=$datetimeobj->convert($dateformatdetails['phpdate']);

                if (in_array($rows['owner_id'],getuserlist('onlyuidarray')))
                {
                    $ownername=$rows['users_name'] ;
                }
                else
                {
                    $ownername="---";
                }

                $questionsCount = 0;
                $questionsCountQuery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid={$rows['sid']} AND language='".$rows['language']."'"; //Getting a count of questions for this survey
                $questionsCountResult = db_execute_assoc($questionsCountQuery); //($connect->Execute($questionsCountQuery); //Checked)
                $questionsCount = $questionsCountResult->num_rows();

                $listsurveys.="<tr>";

                if ($rows['active']=="Y")
                {
                    if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $listsurveys .= "<td><img src='".$this->config->item('imageurl')."/expired.png' "
                        . "alt='".$clang->gT("This survey is active but expired.")."' /></td>";
                    }
                    else
                    {
                        if (bHasSurveyPermission($rows['sid'],'surveyactivation','update'))
                        {
                            $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('".$this->config->item('scriptname')."?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
                            . " title=\"".$clang->gTview("This survey is active - click here to deactivate this survey.")."\" >"
                            . "<img src='".$this->config->item('imageurl')."/active.png' alt='".$clang->gT("This survey is active - click here to deactivate this survey.")."' /></a></td>\n";
                        } else
                        {
                            $listsurveys .= "<td><img src='".$this->config->item('imageurl')."/active.png' "
                            . "alt='".$clang->gT("This survey is currently active.")."' /></td>\n";
                        }
                    }
                } else {
                    if ( $questionsCount > 0 && bHasSurveyPermission($rows['sid'],'surveyactivation','update') )
                    {
                        $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('".$this->config->item('scriptname')."?action=activate&amp;sid={$rows['sid']}', '_top')\""
                        . " title=\"".$clang->gTview("This survey is currently not active - click here to activate this survey.")."\" >"
                        . "<img src='".$this->config->item('imageurl')."/inactive.png' title='' alt='".$clang->gT("This survey is currently not active - click here to activate this survey.")."' /></a></td>\n" ;
                    } else
                    {
                        $listsurveys .= "<td><img src='".$this->config->item('imageurl')."/inactive.png'"
                        . " title='".$clang->gT("This survey is currently not active.")."' alt='".$clang->gT("This survey is currently not active.")."' />"
                        . "</td>\n";
                    }
                }
                $link = site_url("admin/survey/view/".$rows['sid']);
                $listsurveys.="<td align='center'><a href='".$link."'>{$rows['sid']}</a></td>";
                $listsurveys.="<td align='left'><a href='".$link."'>{$rows['surveyls_title']}</a></td>".
    					    "<td>".$datecreated."</td>".
    					    "<td>".$ownername." (<a href='#' class='ownername_edit' id='ownername_edit_{$rows['sid']}'>Edit</a>)</td>".
    					    "<td>".$visibility."</td>" .
    					    "<td>".$privacy."</td>";

                if ($rows['active']=="Y")
                {
                    $complete = $responses - $partial_responses;
                    $listsurveys .= "<td>".$complete."</td>";
                    $listsurveys .= "<td>".$partial_responses."</td>";
                    $listsurveys .= "<td>".$responses."</td>";
                }else{
                    $listsurveys .= "<td>&nbsp;</td>";
                    $listsurveys .= "<td>&nbsp;</td>";
                    $listsurveys .= "<td>&nbsp;</td>";
                }

                if ($rows['active']=="Y" && tableExists("tokens_".$rows['sid']))
    		    {
    		    	//get the number of tokens for each survey
    		    	$tokencountquery = "SELECT COUNT(tid) AS countoftid FROM ".$this->db->dbprefix."tokens_".$rows['sid'];
                                $tokencountresult = db_execute_assoc($tokencountquery); //Checked)
                                foreach ($tokencountresult->result_array() as $tokenrow)
                                {
                                    $tokencount = $tokenrow['countoftid'];
                                }

    		    	//get the number of COMLETED tokens for each survey
    		    	$tokencompletedquery = "SELECT COUNT(tid) AS countoftid FROM ".$this->db->dbprefix."tokens_".$rows['sid']." WHERE completed!='N'";
                                $tokencompletedresult = db_execute_assoc($tokencompletedquery); //Checked
                                foreach ($tokencompletedresult->result_array() as $tokencompletedrow)
                                {
                                    $tokencompleted = $tokencompletedrow['countoftid'];
                                }

                                //calculate percentage

                                //prevent division by zero problems
                                if($tokencompleted != 0 && $tokencount != 0)
                                {
                                $tokenpercentage = round(($tokencompleted / $tokencount) * 100, 1);
                                }
                                else
                                {
                                $tokenpercentage = 0;
                                }

                                $listsurveys .= "<td>".$tokencount."</td>";
                                $listsurveys .= "<td>".$tokenpercentage."%</td>";
    		    }
    		    else
    		    {
    				$listsurveys .= "<td>&nbsp;</td>";
    				$listsurveys .= "<td>&nbsp;</td>";
    		    }

    		    $listsurveys .= "</tr>" ;
            }

    		$listsurveys.="</tbody>";
    		$listsurveys.="</table><br />" ;
            $data['clang'] = $clang;
            $this->load->view('admin/Survey/listSurveys_view',$data);


        }
        else
        {
            $listsurveys ="<p><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
            //$this->load->view('survey_view',$displaydata);
        }

        $displaydata['display'] = $listsurveys;
        //$data['display'] = $editsurvey;
        $this->load->view('survey_view',$displaydata);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


    }

    /**
     * survey::delete()
     * Function responsible to delete a survey.
     * @return
     */
    function delete()
    {
        $this->load->helper("database");
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
        $data['surveyid'] = $surveyid = $this->input->post('sid');
        if ($this->input->post('deleteok'))
        {
            self::_showadminmenu(false);
        }
        else{
            self::_showadminmenu($surveyid);
        }
        if (!$this->input->post('deleteok')) self::_surveybar($this->input->post('sid'));

        if(bHasSurveyPermission($surveyid,'survey','delete'))
        {
            $data['deleteok'] = $deleteok = $this->input->post('deleteok');
            $data['clang'] = $this->limesurvey_lang;

            $data['link'] = site_url("admin/survey/delete");
            if (!(!isset($deleteok) || !$deleteok))
            {
                $this->load->dbforge();
                if (tableExists("survey_$surveyid"))  //delete the survey_$surveyid table
                {
                    //$dsquery = $dict->DropTableSQL("{$dbprefix}survey_$surveyid");
                    //$dict->ExecuteSQLArray($sqlarray); $dict->ExecuteSQLArray($dsquery)
                    $dsresult = $this->dbforge->drop_table('survey_'.$surveyid) or safe_die ("Couldn't drop table survey_".$surveyid." in survey.php");
                }

            	if (tableExists("survey_{$surveyid}_timings"))  //delete the survey_$surveyid_timings table
                {
                    //$dsquery = $dict->DropTableSQL("{$dbprefix}survey_{$surveyid}_timings");
                    //$dict->ExecuteSQLArray($sqlarraytimings);
                    $dsresult = $this->dbforge->drop_table('survey_'.$surveyid.'_timings') or safe_die ("Couldn't drop table survey_".$surveyid."_timings in survey.php");
                }

                if (tableExists("tokens_$surveyid")) //delete the tokens_$surveyid table
                {
                    //$dsquery = $dict->DropTableSQL("{$dbprefix}tokens_$surveyid");
                    $dsresult = $this->dbforge->drop_table('tokens_'.$surveyid) or safe_die ("Couldn't drop table token_".$surveyid." in survey.php");
                }

                $dsquery = "SELECT qid FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid";
                $dsresult = db_execute_assoc($dsquery) or safe_die ("Couldn't find matching survey to delete<br />$dsquery<br />");
                foreach ($dsresult->result_array() as $dsrow)
                {
                    //$asdel = "DELETE FROM {$dbprefix}answers WHERE qid={$dsrow['qid']}";
                    //$asres = $connect->Execute($asdel);
                    $this->db->delete('answers', array('qid' => $dsrow['qid']));
                    $this->db->delete('conditions', array('qid' => $dsrow['qid']));
                    $this->db->delete('question_attributes', array('qid' => $dsrow['qid']));
                    /**
                    $cddel = "DELETE FROM {$dbprefix}conditions WHERE qid={$dsrow['qid']}";
                    $cdres = $connect->Execute($cddel) or safe_die ("Delete conditions failed<br />$cddel<br />".$connect->ErrorMsg());
                    $qadel = "DELETE FROM {$dbprefix}question_attributes WHERE qid={$dsrow['qid']}";
                    $qares = $connect->Execute($qadel); */
                }

                //$qdel = "DELETE FROM {$dbprefix}questions WHERE sid=$surveyid";
                //$qres = $connect->Execute($qdel);
                $this->db->delete('questions', array('sid' => $surveyid));

                //$scdel = "DELETE FROM {$dbprefix}assessments WHERE sid=$surveyid";
                //$scres = $connect->Execute($scdel);
                $this->db->delete('assessments', array('sid' => $surveyid));

                //$gdel = "DELETE FROM {$dbprefix}groups WHERE sid=$surveyid";
                //$gres = $connect->Execute($gdel);
                $this->db->delete('groups', array('sid' => $surveyid));

                //$slsdel = "DELETE FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=$surveyid";
                //$slsres = $connect->Execute($slsdel);
                $this->db->delete('surveys_languagesettings', array('surveyls_survey_id' => $surveyid));

                //$srdel = "DELETE FROM {$dbprefix}survey_permissions WHERE sid=$surveyid";
                //$srres = $connect->Execute($srdel);
                $this->db->delete('survey_permissions', array('sid' => $surveyid));

                //$srdel = "DELETE FROM {$dbprefix}saved_control WHERE sid=$surveyid";
                //$srres = $connect->Execute($srdel);
                $this->db->delete('saved_control', array('sid' => $surveyid));

                //$sdel = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
                //$sres = $connect->Execute($sdel);
                $this->db->delete('surveys', array('sid' => $surveyid));

                $sdel = "DELETE ".$this->db->dbprefix."quota_languagesettings FROM ".$this->db->dbprefix."quota_languagesettings, ".$this->db->dbprefix."quota WHERE ".$this->db->dbprefix."quota_languagesettings.quotals_quota_id=".$this->db->dbprefix."quota.id and sid=$surveyid";
                $sres = db_execute_assoc($sdel);
                //$sres = $connect->Execute($sdel);
                //$this->db->delete('assessments', array('sid' => $surveyid));

                //$sdel = "DELETE FROM {$dbprefix}quota WHERE sid=$surveyid";
                //$sres = $connect->Execute($sdel);
                $this->db->delete('quota', array('sid' => $surveyid));

                //$sdel = "DELETE FROM {$dbprefix}quota_members WHERE sid=$surveyid;";
                //$sres = $connect->Execute($sdel);
                $this->db->delete('quota_members', array('sid' => $surveyid));
                rmdirr($this->config->item("uploaddir").'/surveys/'.$surveyid);

            }
            $this->load->view('admin/Survey/deleteSurvey_view',$data);
        }
        else {
            //include('access_denied.php');
            $finaldata['display'] = access_denied("editsurvey",$surveyid);
            $this->load->view('survey_view',$finaldata);
        }
        self::_loadEndScripts();

        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
     * survey::editlocalsettings()
     * Load editing of local settings of a survey screen.
     * @param mixed $surveyid
     * @return
     */
    function editlocalsettings($surveyid)
    {
        $clang = $this->limesurvey_lang;

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_js_admin_includes(base_url().'scripts/admin/surveysettings.js');
        self::_getAdminHeader();
  		self::_showadminmenu($surveyid);;
        self::_surveybar($surveyid);
        if(bHasSurveyPermission($surveyid,'surveylocale','read'))
        {

            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($grplangs,$baselang);

            $editsurvey = PrepareEditorScript();


            $editsurvey .="<div class='header ui-widget-header'>".$clang->gT("Edit survey text elements")."</div>\n";
            $editsurvey .= "<form id='addnewsurvey' class='form30' name='addnewsurvey' action='".site_url("admin/database/index/updatesurveylocalesettings")."' method='post'>\n"
            . '<div id="tabs">';
            $i = 0;
            foreach ($grplangs as $grouplang)
            {
                // this one is created to get the right default texts fo each language
                $this->load->library('Limesurvey_lang',array($grouplang));
                $this->load->helper('database');
                $this->load->helper('surveytranslator');
                $bplang = $this->limesurvey_lang;//new limesurvey_lang($grouplang);
                $esquery = "SELECT * FROM ".$this->db->dbprefix."surveys_languagesettings WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
                $esresult = db_execute_assoc($esquery); //Checked
                $esrow = $esresult->row_array();

                $tab_title[$i] = getLanguageNameFromCode($esrow['surveyls_language'],false);

                if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid))
                    $tab_title[$i]  .= '('.$clang->gT("Base Language").')';

                $esrow = array_map('htmlspecialchars', $esrow);
                $data['clang'] = $clang;
                $data['esrow'] = $esrow;
                $data['surveyid'] = $surveyid;
                $data['action'] = "editsurveylocalesettings";

                $tab_content[$i] = $this->load->view('admin/Survey/editLocalSettings_view',$data,true);

                /**
                $tab_content[$i] = "<ul>\n"
                . "<li><label for=''>".$clang->gT("Survey title").":</label>\n"
                . "<input type='text' size='80' name='short_title_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_title']}\" /></li>\n"
                . "<li><label for=''>".$clang->gT("Description:")."</label>\n"
                . "<textarea cols='80' rows='15' name='description_".$esrow['surveyls_language']."'>{$esrow['surveyls_description']}</textarea>\n"
                . getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action)
                . "</li>\n"
                . "<li><label for=''>".$clang->gT("Welcome message:")."</label>\n"
                . "<textarea cols='80' rows='15' name='welcome_".$esrow['surveyls_language']."'>{$esrow['surveyls_welcometext']}</textarea>\n"
                . getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action)
                . "</li>\n"
                . "<li><label for=''>".$clang->gT("End message:")."</label>\n"
                . "<textarea cols='80' rows='15' name='endtext_".$esrow['surveyls_language']."'>{$esrow['surveyls_endtext']}</textarea>\n"
                . getEditor("survey-endtext","endtext_".$esrow['surveyls_language'], "[".$clang->gT("End message:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action)
                . "</li>\n"
                . "<li><label for=''>".$clang->gT("End URL:")."</label>\n"
                . "<input type='text' size='80' name='url_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_url']}\" />\n"
                . "</li>"
                . "<li><label for=''>".$clang->gT("URL description:")."</label>\n"
                . "<input type='text' size='80' name='urldescrip_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_urldescription']}\" />\n"
                . "</li>"
                . "<li><label for=''>".$clang->gT("Date format:")."</label>\n"
                . "<select size='1' name='dateformat_".$esrow['surveyls_language']."'>\n";
                foreach (getDateFormatData() as $index=>$dateformatdata)
                {
                    $tab_content[$i].= "<option value='{$index}'";
                    if ($esrow['surveyls_dateformat']==$index) {
                       $tab_content[$i].=" selected='selected'";
                    }
                    $tab_content[$i].= ">".$dateformatdata['dateformat'].'</option>';
                }
                $tab_content[$i].= "</select></li>"
                . "<li><label for=''>".$clang->gT("Decimal Point Format:")."</label>\n";
                $tab_content[$i].="<select size='1' name='numberformat_".$esrow['surveyls_language']."'>\n";
                foreach (getRadixPointData() as $index=>$radixptdata)
                {
                    $tab_content[$i].= "<option value='{$index}'";
                    if ($esrow['surveyls_numberformat']==$index) {
                       $tab_content[$i].=" selected='selected'";
                    }
                    $tab_content[$i].= ">".$radixptdata['desc'].'</option>';
                }
                $tab_content[$i].= "</select></li></ul>"; */
                $i++;
            }

            $editsurvey .= "<ul>";
            foreach($tab_title as $i=>$eachtitle){
                $editsurvey .= "<li style='clear:none'><a href='#edittxtele$i'>$eachtitle</a></li>";
            }
            $editsurvey .= "</ul>";
            foreach ($tab_content as $i=>$eachcontent){
                $editsurvey .= "<div id='edittxtele$i'>$eachcontent</div>";
            }
            $editsurvey .= "</div>";
            if(bHasSurveyPermission($surveyid,'surveylocale','update'))
            {
                $editsurvey .= "<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
                . "<input type='hidden' name='action' value='updatesurveylocalesettings' />\n"
                . "<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
                . "<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
                . "</p>\n"
                . "</form>\n";
            }






            //echo $editsurvey;
            $finaldata['display'] = $editsurvey;
            $this->load->view('survey_view',$finaldata);
            //self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

        }
        else
        {
            //include("access_denied.php");
            $finaldata['display'] = access_denied("editsurvey",$surveyid);
            $this->load->view('survey_view',$finaldata);

        }
        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * survey::copy()
     * Function responsible to import/copy a survey based on $action.
     * @return
     */
    function copy()
    {
        $importsurvey = "";
        $action = $this->input->post('action');
        $surveyid = $this->input->post('sid');

        if ($action == "importsurvey" || $action == "copysurvey")
        {
            if ( $this->input->post('copysurveytranslinksfields') == "on"  || $this->input->post('translinksfields') == "on")
            {
                $sTransLinks = true;
            }
            $clang = $this->limesurvey_lang;

            // Start the HTML
            if ($action == 'importsurvey')
            {
                $importsurvey .= "<div class='header ui-widget-header'>".$clang->gT("Import survey")."</div>\n";
                $importingfrom = "http";
            }
            elseif($action == 'copysurvey')
            {
                $importsurvey .= "<div class='header ui-widget-header'>".$clang->gT("Copy survey")."</div>\n";
                $copyfunction= true;
            }
            // Start traitment and messagebox
            $importsurvey .= "<div class='messagebox ui-corner-all'>\n";
            $importerror=false; // Put a var for continue

            if ($action == 'importsurvey')
            {

                $the_full_file_path = $this->config->item('tempdir') . "/" . $_FILES['the_file']['name'];
                if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
                {
                    $importsurvey .= "<div class='errorheader'>".$clang->gT("Error")."</div>\n";
                    $importsurvey .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$this->config->item('tempdir'))."<br /><br />\n";
                    $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\"><br /><br />\n";
                    $importerror=true;
                }
                else
                {
                    $importsurvey .= "<div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n";
                    $importsurvey .= $clang->gT("File upload succeeded.")."<br /><br />\n";
                    $importsurvey .= $clang->gT("Reading file..")."<br />\n";
                    $sFullFilepath=$the_full_file_path;
                    $aPathInfo = pathinfo($sFullFilepath);
                    if (isset($aPathInfo['extension']))
                    {
                        $sExtension = $aPathInfo['extension'];
                    }
                    else
                    {
                        $sExtension = "";
                    }

                }

                if (!$importerror && (strtolower($sExtension)!='csv' && strtolower($sExtension)!='lss'))
                {
                    $importsurvey .= "<div class='errorheader'>".$clang->gT("Error")."</div>\n";
                    $importsurvey .= $clang->gT("Import failed. You specified an invalid file type.")."\n";
                    $importerror=true;
                }
            }
            elseif ($action == 'copysurvey')
            {
                $surveyid = sanitize_int($this->input->post('copysurveylist'));
                $exclude = array();

                if (get_magic_quotes_gpc()) {$sNewSurveyName = stripslashes($this->input->post('copysurveyname'));}
                else{
                    $sNewSurveyName=$this->input->post('copysurveyname');
                }
                /**
                require_once("../classes/inputfilter/class.inputfilter_clean.php");
                $myFilter = new InputFilter('','',1,1,1);
                if ($filterxsshtml)
                {
                    $sNewSurveyName = $myFilter->process($sNewSurveyName);
                } else {
                    $sNewSurveyName = html_entity_decode($sNewSurveyName, ENT_QUOTES, "UTF-8");
                }
                */
                if ($this->input->post('copysurveyexcludequotas') == "on")
                {
                    $exclude['quotas'] = true;
                }
                if ($this->input->post('copysurveyexcludeanswers') == "on")
                {
                    $exclude['answers'] = true;
                }
                if ($this->input->post('copysurveyresetconditions') == "on")
                {
                    $exclude['conditions'] = true;
                }
                //include("export_structure_xml.php");

                if (!$surveyid)
                {
                    self::_getAdminHeader();
                    echo ""
                    ."<br />\n"
                    ."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
                    ."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
                    .$clang->gT("Export Survey")."</strong></td></tr>\n"
                    ."\t<tr><td align='center'>\n"
                    ."<br /><strong><font color='red'>"
                    .$clang->gT("Error")."</font></strong><br />\n"
                    .$clang->gT("No SID has been provided. Cannot dump survey")."<br />\n"
                    ."<br /><input type='submit' value='"
                    .$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n"
                    ."\t</td></tr>\n"
                    ."</table>\n"
                    ."</body></html>\n";
                    exit;
                }


                $this->load->helper('export_helper');


                if (!isset($copyfunction))
                {
                    $fn = "limesurvey_survey_$surveyid.lss";
                    header("Content-Type: text/xml");
                    header("Content-Disposition: attachment; filename=$fn");
                    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Pragma: public");                          // HTTP/1.0
                    echo getXMLData();
                    exit;
                }

                $copysurveydata = survey_getXMLData($surveyid,$exclude);


            }

            // Now, we have the survey : start importing
            //require_once('import_functions.php');
            $this->load->helper('admin/import');
            $_POST = $this->input->post();
            if ($action == 'importsurvey' && !$importerror)
            {

                if (isset($sExtension) && strtolower($sExtension)=='csv')
                {
                    $aImportResults=CSVImportSurvey($sFullFilepath);
                }
                elseif (isset($sExtension) && strtolower($sExtension)=='lss')
                {
                    $aImportResults=XMLImportSurvey($sFullFilepath,null,null, null,(isset($_POST['translinksfields'])));
                }
                else
                {
                    $importerror = true;
                }


            }
            elseif ($action == 'copysurvey' && !$importerror)
            {
                $aImportResults=XMLImportSurvey('',$copysurveydata,$sNewSurveyName);
            }
            else
            {
                $importerror=true;
            }

            if (isset($aImportResults['error']) && $aImportResults['error']!=false)
            {
                $link = site_url('admin');
                $importsurvey .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
                $importsurvey .= $aImportResults['error']."<br /><br />\n";
                $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$link', '_top')\" />\n";
                $importerror = true;
            }

            if (!$importerror)
            {
                $importsurvey .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br /><br />\n";
                if ($action == 'importsurvey')
                {
                    $importsurvey .= "<strong>".$clang->gT("Survey copy summary")."</strong><br />\n";
                }
                elseif($action == 'copysurvey')
                {
                    $importsurvey .= "<strong>".$clang->gT("Survey import summary")."</strong><br />\n";
                }

                $importsurvey .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Surveys").": {$aImportResults['surveys']}</li>\n";
                $importsurvey .= "\t<li>".$clang->gT("Languages").": {$aImportResults['languages']}</li>\n";
                $importsurvey .= "\t<li>".$clang->gT("Question groups").": {$aImportResults['groups']}</li>\n";
                $importsurvey .= "\t<li>".$clang->gT("Questions").": {$aImportResults['questions']}</li>\n";
                $importsurvey .= "\t<li>".$clang->gT("Answers").": {$aImportResults['answers']}</li>\n";
                if (isset($aImportResults['subquestions']))
                {
                    $importsurvey .= "\t<li>".$clang->gT("Subquestions").": {$aImportResults['subquestions']}</li>\n";
                }
                if (isset($aImportResults['defaultvalues']))
                {
                    $importsurvey .= "\t<li>".$clang->gT("Default answers").": {$aImportResults['defaultvalues']}</li>\n";
                }
                if (isset($aImportResults['conditions']))
                {
                    $importsurvey .= "\t<li>".$clang->gT("Conditions").": {$aImportResults['conditions']}</li>\n";
                }
                if (isset($aImportResults['labelsets']))
                {
                    $importsurvey .= "\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
                }
                if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
                {
                    $importsurvey .= "\t<li>".$clang->gT("Not imported label sets").": {$aImportResults['deniedcountls']} ".$clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)")."</li>\n";
                }
                $importsurvey .= "\t<li>".$clang->gT("Question attributes").": {$aImportResults['question_attributes']}</li>\n";
                $importsurvey .= "\t<li>".$clang->gT("Assessments").": {$aImportResults['assessments']}</li>\n";
                $importsurvey .= "\t<li>".$clang->gT("Quotas").": {$aImportResults['quota']} ({$aImportResults['quotamembers']} ".$clang->gT("quota members")." ".$clang->gT("and")." {$aImportResults['quotals']} ".$clang->gT("quota language settings").")</li>\n</ul><br />\n";

                if (count($aImportResults['importwarnings'])>0)
                {
                    $importsurvey .= "<div class='warningheader'>".$clang->gT("Warnings").":</div><ul style=\"text-align:left;\">";
                    foreach ($aImportResults['importwarnings'] as $warning)
                    {
                        $importsurvey .='<li>'.$warning.'</li>';
                    }
                    $importsurvey .= "</ul><br />\n";
                }
                $link = site_url('admin/survey/view/'.$aImportResults['newsid']);
                if ($action == 'importsurvey')
                {
                    $importsurvey .= "<strong>".$clang->gT("Import of Survey is completed.")."</strong><br />\n"
                    . "<a href='$link'>".$clang->gT("Go to survey")."</a><br />\n";
                }
                elseif($action == 'copysurvey')
                {
                    $importsurvey .= "<strong>".$clang->gT("Copy of survey is completed.")."</strong><br />\n"
                    . "<a href='$link'>".$clang->gT("Go to survey")."</a><br />\n";
                }

                if ($action == 'importsurvey')
                {
                    unlink($sFullFilepath);
                }

            }
            // end of traitment an close message box
            $importsurvey .= "</div><br />\n";
        }
        self::_getAdminHeader();
        self::_showadminmenu();;
        //self::_surveybar($surveyid);

        $data['display'] = $importsurvey;
        $this->load->view('survey_view',$data);

        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * survey::index()
     * Load edit/new survey screen.
     * @param mixed $action
     * @param mixed $surveyid
     * @return
     */
    function index($action,$surveyid=null)
    {
        //global $surveyid;

        if (!isset($action))
        {
            redirect("admin");
        }

        self::_js_admin_includes(base_url().'scripts/admin/surveysettings.js');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
		self::_showadminmenu($surveyid);;
        if (!is_null($surveyid))
        self::_surveybar($surveyid);

        if(!bHasSurveyPermission($surveyid,'surveysettings','read') && !bHasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
        {
            //include("access_denied.php");
        }
        $this->load->helper('surveytranslator');
        $clang = $this->limesurvey_lang;

        $esrow = array();
        $editsurvey = '';
        if ($action == "newsurvey") {
            $esrow = self::_fetchSurveyInfo('newsurvey');
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $this->load->helper('admin/htmleditor');
            $editsurvey = PrepareEditorScript();

            $editsurvey .= "";
            $editsurvey .="<script type=\"text/javascript\">
                        standardtemplaterooturl='".$this->config->item('standardtemplaterooturl')."';
                        templaterooturl='".$this->config->item('usertemplaterooturl')."'; \n";
            $editsurvey .= "</script>\n";

        // header
        $editsurvey .= "<div class='header ui-widget-header'>" . $clang->gT("Create, import, or copy survey") . "</div>\n";
        } elseif ($action == "editsurveysettings") {
            $esrow = self::_fetchSurveyInfo('editsurvey',$surveyid);
            // header
            $editsurvey .= "<div class='header ui-widget-header'>".$clang->gT("Edit survey settings")."</div>\n";
        }
        if ($action == "newsurvey") {
            $editsurvey .= self::_generalTabNewSurvey();
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= self::_generalTabEditSurvey($surveyid,$esrow);
        }

        $editsurvey .= self::_tabPresentationNavigation($esrow);
        $editsurvey .= self::_tabPublicationAccess($esrow);
        $editsurvey .= self::_tabNotificationDataManagement($esrow);
        $editsurvey .= self::_tabTokens($esrow);

        if ($action == "newsurvey") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='insertsurvey' />\n";
            //$this->session->set_userdata(array('action' => 'insertsurvey'));
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='updatesurveysettings' />\n"
            . "<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
            . "<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
            . "<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n";
        }
        $editsurvey .= "</form>";
        if ($action == "newsurvey") {
            $editsurvey .= self::_tabImport($surveyid);
            $editsurvey .= self::_tabCopy($surveyid);
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= self::_tabResourceManagement($surveyid);
        }


        // End TAB pane
        $editsurvey .= "</div>\n";


        if ($action == "newsurvey") {
            $cond = "if (isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "'))";
            $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
        } elseif ($action == "editsurveysettings") {
            $cond = "if (UpdateLanguageIDs(mylangs,'" . $clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js") . "'))";
            if (bHasSurveyPermission($surveyid,'surveysettings','update'))
            {
                $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
            }
            if (bHasSurveyPermission($surveyid,'surveylocale','read'))
            {
                $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('surveysettingsaction').value = 'updatesurveysettingsandeditlocalesettings'; document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save & edit survey text elements") . " >></button></p>\n";
            }
        }

        //echo $editsurvey;
        $data['display'] = $editsurvey;
        $this->load->view('survey_view',$data);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }


    /**
     * survey::_fetchSurveyInfo()
     * Load survey information based on $action.
     * @param mixed $action
     * @param mixed $surveyid
     * @return
     */
    function _fetchSurveyInfo($action,$surveyid=null)
    {
        if ($action == 'newsurvey')
        {
            $esrow = array();
            $esrow['active']                   = 'N';
            $esrow['allowjumps']               = 'N';
            $esrow['format']                   = 'G'; //Group-by-group mode
            $esrow['template']                 = $this->config->item('defaulttemplate');
            $esrow['allowsave']                = 'Y';
            $esrow['allowprev']                = 'N';
            $esrow['nokeyboard']               = 'N';
            $esrow['printanswers']             = 'N';
            $esrow['publicstatistics']         = 'N';
            $esrow['publicgraphs']             = 'N';
            $esrow['public']                   = 'Y';
            $esrow['autoredirect']             = 'N';
            $esrow['tokenlength']              = 15;
            $esrow['allowregister']            = 'N';
            $esrow['usecookie']                = 'N';
            $esrow['usecaptcha']               = 'D';
            $esrow['htmlemail']                = 'Y';
            $esrow['emailnotificationto']      = '';
            $esrow['anonymized']               = 'N';
            $esrow['datestamp']                = 'N';
            $esrow['ipaddr']                   = 'N';
            $esrow['refurl']                   = 'N';
            $esrow['tokenanswerspersistence']  = 'N';
            $esrow['alloweditaftercompletion'] = 'N';
            $esrow['assesments']               = 'N';
            $esrow['startdate']                = '';
            $esrow['savetimings']              = 'N';
            $esrow['expires']                  = '';
            $esrow['showqnumcode']             = 'X';
            $esrow['showwelcome']              = 'Y';
            $esrow['emailresponseto']          = '';
            $esrow['assessments']              = 'N';
        } elseif ($action == 'editsurvey') {
            $condition = array('sid' => $surveyid);
            $this->load->model('surveys_model');
            //$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
            $esresult = $this->surveys_model->getAllRecords($condition); //($esquery); //Checked)
            if ($esrow = $esresult->row_array()) {
                $esrow = array_map('htmlspecialchars', $esrow);
            }

        }

        return $esrow;
    }

    /**
     * survey::_generalTabNewSurvey()
     * Load "General" tab of new survey screen.
     * @return
     */
    function _generalTabNewSurvey()
    {
        global $siteadminname,$siteadminemail;
        $clang = $this->limesurvey_lang;

        $condition = array('users_name' => $this->session->userdata('user'));
        $fieldstoselect = array('full_name', 'email');
        $this->load->model('users_model');
        //Use the current user details for the default administrator name and email for this survey
        //$query = "SELECT full_name, email FROM " . db_table_name('users') . " WHERE users_name = " . db_quoteall($_SESSION['user']);
        $result = $this->users_model->getSomeRecords($fieldstoselect,$condition); //($query) or safe_die($connect->ErrorMsg());)
        $owner = $result->row_array();
        //Degrade gracefully to $siteadmin details if anything is missing.
        if (empty($owner['full_name']))
            $owner['full_name'] = $siteadminname;
        if (empty($owner['email']))
            $owner['email'] = $siteadminemail;
        //Bounce setting by default to global if it set globally
        $this->load->helper('globalsettings');
        if (getGlobalSetting('bounceaccounttype')!='off'){
            $owner['bounce_email']         = getGlobalSetting('siteadminbounce');
        } else {
            $owner['bounce_email']        = $owner['email'];
        }

        /**
        $editsurvey .= "<span class='annotation'> " . $clang->gT("*This setting cannot be changed later!") . "</span></li>\n";
        $action = "newsurvey";
        $editsurvey .= ""
                    . "<li><label for='surveyls_title'><span class='annotationasterisk'>*</span>" . $clang->gT("Title") . ":</label>\n"
                    . "<input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='description'>" . $clang->gT("Description:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='description' name='description'></textarea>"
                    . getEditor("survey-desc", "description", "[" . $clang->gT("Description:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='welcome'>" . $clang->gT("Welcome message:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>"
                    . getEditor("survey-welc", "welcome", "[" . $clang->gT("Welcome message:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='endtext'>" . $clang->gT("End message:") . "</label>\n"
                    . "<textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>"
                    . getEditor("survey-endtext", "endtext", "[" . $clang->gT("End message:", "js") . "]", '', '', '', $action)
                    . "</li>\n";

            // End URL
            $editsurvey .= "<li><label for='url'>" . $clang->gT("End URL:") . "</label>\n"
                    . "<input type='text' size='50' id='url' name='url' value='http://";
            $editsurvey .= "' /></li>\n";

            // URL description
            $editsurvey.= "<li><label for='urldescrip'>" . $clang->gT("URL description:") . "</label>\n"
                    . "<input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='";
            $editsurvey .= "' /></li>\n"

                    //Default date format
                    . "<li><label for='dateformat'>" . $clang->gT("Date format:") . "</label>\n"
                    . "<select size='1' id='dateformat' name='dateformat'>\n";
            foreach (getDateFormatData () as $index => $dateformatdata) {
                $editsurvey.= "<option value='{$index}'";
                $editsurvey.= ">" . $dateformatdata['dateformat'] . '</option>';
            }
            $editsurvey.= "</select></li>"

                    . "<li><label for='admin'>" . $clang->gT("Administrator:") . "</label>\n"
                    . "<input type='text' size='50' id='admin' name='admin' value='" . $owner['full_name'] . "' /></li>\n"
                    . "<li><label for='adminemail'>" . $clang->gT("Admin Email:") . "</label>\n"
                    . "<input type='text' size='50' id='adminemail' name='adminemail' value='" . $owner['email'] . "' /></li>\n"
                    . "<li><label for='bounce_email'>" . $clang->gT("Bounce Email:") . "</label>\n"
                    . "<input type='text' size='50' id='bounce_email' name='bounce_email' value='" . $owner['bounce_email'] . "' /></li>\n"
                    . "<li><label for='faxto'>" . $clang->gT("Fax to:") . "</label>\n"
                    . "<input type='text' size='50' id='faxto' name='faxto' /></li>\n";

            $editsurvey.= "</ul>";


            // End General TAB
            $editsurvey .= "</div>\n";
            return $editsurvey;
            */
            $data['action'] = "newsurvey";
            $data['clang'] = $clang;
            $data['owner'] = $owner;
            return $this->load->view('admin/Survey/superview/superGeneralNewSurvey_view',$data, true);
    }

    /**
     * survey::_generalTabEditSurvey()
     * Load "General" tab of edit survey screen.
     * @param mixed $surveyid
     * @param mixed $esrow
     * @return
     */
    function _generalTabEditSurvey($surveyid,$esrow)
    {
        global $siteadminname,$siteadminemail;
        $clang = $this->limesurvey_lang;
        $data['action'] = "editsurveysettings";
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        $data['surveyid'] = $surveyid;
        return $this->load->view('admin/Survey/superview/superGeneralEditSurvey_view',$data, true);
    }

    /**
     * survey::_tabPresentationNavigation()
     * Load "Presentation & navigation" tab.
     * @param mixed $esrow
     * @return
     */
    function _tabPresentationNavigation($esrow)
    {
        $clang = $this->limesurvey_lang;
        global $showXquestions,$showgroupinfo,$showqnumcode;

        /**
        // Presentation and navigation TAB
        $editsurvey = "<div id='presentation'><ul>\n";

        //Format
        $editsurvey .= "<li><label for='format'>".$clang->gT("Format:")."</label>\n"
        . "<select id='format' name='format'>\n"
        . "<option value='S'";
        if ($esrow['format'] == "S" || !$esrow['format']) {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
            . "<option value='G'";
        if ($esrow['format'] == "G") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
            . "<option value='A'";
        if ($esrow['format'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("All in one")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //TEMPLATES
            $editsurvey .= "<li><label for='template'>".$clang->gT("Template:")."</label>\n"
            . "<select id='template' name='template'>\n";
        foreach (array_keys(gettemplatelist()) as $tname) {

            if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1 || hasTemplateManageRights($this->session->userdata("loginID"), $tname) == 1) {
                    $editsurvey .= "<option value='$tname'";
                if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {
                    $editsurvey .= " selected='selected'";
                } elseif (!$esrow['template'] && $tname == "default") {
                    $editsurvey .= " selected='selected'";
                }
                    $editsurvey .= ">$tname</option>\n";
                }
            }
            $editsurvey .= "</select>\n"
            . "</li>\n";

            $editsurvey .= "<li><label for='preview'>".$clang->gT("Template Preview:")."</label>\n"
            . "<img alt='".$clang->gT("Template preview image")."' id='preview' src='".sGetTemplateURL($esrow['template'])."/preview.png' />\n"
            . "</li>\n" ;

        //SHOW WELCOMESCRN
        $editsurvey .= "<li><label for='showwelcome'>" . $clang->gT("Show welcome screen?") . "</label>\n"
                . "<select id='showwelcome' name='showwelcome'>\n"
                . "<option value='Y'";
        if (!$esrow['showwelcome'] || $esrow['showwelcome'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("Yes") . "</option>\n"
                . "<option value='N'";
        if ($esrow['showwelcome'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("No") . "</option>\n"
                . "</select></li>\n";

        //Navigation Delay
        if (!isset($esrow['navigationdelay']))
        {
            $esrow['navigationdelay']=0;
        }
        $editsurvey .= "<li><label for='navigationdelay'>".$clang->gT("Navigation delay (seconds):")."</label>\n"
        . "<input type='text' value=\"{$esrow['navigationdelay']}\" name='navigationdelay' id='navigationdelay' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";

        //Show Prev Button
        $editsurvey .= "<li><label for='allowprev'>".$clang->gT("Show [<< Prev] button")."</label>\n"
        . "<select id='allowprev' name='allowprev'>\n"
        . "<option value='Y'";
        if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

        //Show Question Index
        $editsurvey .= "<li><label for='allowjumps'>".$clang->gT("Show question index / allow jumping")."</label>\n"
                . "<select id='allowjumps' name='allowjumps'>\n"
                . "<option value='Y'";
        if (!isset($esrow['allowjumps']) || !$esrow['allowjumps'] || $esrow['allowjumps'] == "Y") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
        if (isset($esrow['allowjumps']) && $esrow['allowjumps'] == "N") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

        //No Keyboard
        $editsurvey .= "<li><label for='nokeyboard'>".$clang->gT("Keyboard-less operation")."</label>\n"
                . "<select id='nokeyboard' name='nokeyboard'>\n"
                . "<option value='Y'";
        if (!isset($esrow['nokeyboard']) || !$esrow['nokeyboard'] || $esrow['nokeyboard'] == "Y") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
        if (isset($esrow['nokeyboard']) && $esrow['nokeyboard'] == "N") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

	//Show Progress
	$editsurvey .= "<li><label for='showprogress'>".$clang->gT("Show progress bar")."</label>\n"
                . "<select id='showprogress' name='showprogress'>\n"
                . "<option value='Y'";
	if (!isset($esrow['showprogress']) || !$esrow['showprogress'] || $esrow['showprogress'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
	if (isset($esrow['showprogress']) && $esrow['showprogress'] == "N") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

            //Result printing
            $editsurvey .= "<li><label for='printanswers'>".$clang->gT("Participants may print answers?")."</label>\n"
            . "<select id='printanswers' name='printanswers'>\n"
            . "<option value='Y'";
        if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicstatistics'>".$clang->gT("Public statistics?")."</label>\n"
            . "<select id='publicstatistics' name='publicstatistics'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicgraphs'>".$clang->gT("Show graphs in public statistics?")."</label>\n"
            . "<select id='publicgraphs' name='publicgraphs'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";


            // End URL block
            $editsurvey .= "<li><label for='autoredirect'>".$clang->gT("Automatically load URL when survey complete?")."</label>\n"
            . "<select id='autoredirect' name='autoredirect'>";
            $editsurvey .= "<option value='Y'";
        if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n";
            $editsurvey .= "<option value='N'";
        if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>";

            // Show {THEREAREXQUESTIONS} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t".'<input type="hidden" name="showXquestions" id="" value="';
	    $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="';
	    $show_dis_post = "\" size=\"70\" />\n\t</li>\n";
        switch ($showXquestions) {
		case 'show':
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('Yes (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'hide':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('No (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showxq = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['showXquestions'])) {
		    	$set_showxq = $esrow['showXquestions'];
			$sel_showxq[$set_showxq] = ' selected="selected"';
		    }
                if (empty($sel_showxq['Y']) && empty($sel_showxq['N'])) {
		    	$sel_showxq['Y'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t"
		    . "<select id=\"showXquestions\" name=\"showXquestions\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showxq['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showxq['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showxq,$set_showxq);
		    break;
	    };

            // Show {GROUPNAME} and/or {GROUPDESCRIPTION} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showgroupinfo\">".$clang->gT('Show group name and/or group description')."</label>\n\t\t".'<input type="hidden" name="showgroupinfo" id="showgroupinfo" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="';
        switch ($showgroupinfo) {
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'name':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show group name only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'description':
		    $editsurvey .= $show_dis_pre.'D'.$show_dis_mid.$clang->gT('Show group description only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showgroupinfo'])) {
		    	$set_showgri = $esrow['showgroupinfo'];
                $sel_showgri[$set_showgri] = ' selected="selected"';
		    }
                if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X'])) {
		    	$sel_showgri['C'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showgroupinfo\">".$clang->gT('Show group name and/or group description')."</label>\n\t\t"
		    . "<select id=\"showgroupinfo\" name=\"showgroupinfo\">\n\t\t\t"
		    . '<option value="B"'.$sel_showgri['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showgri['N'].'>'.$clang->gT('Show group name only')."</option>\n\t\t\t"
		    . '<option value="D"'.$sel_showgri['D'].'>'.$clang->gT('Show group description only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showgri['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showgri,$set_showgri);
		    break;
	    };

            // Show {QUESTION_CODE} and/or {QUESTION_NUMBER} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showqnumcode\">".$clang->gT('Show question number and/or code')."</label>\n\t\t".'<input type="hidden" name="showqnumcode" id="showqnumcode" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="';
        switch ($showqnumcode) {
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'number':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show question number only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'code':
		    $editsurvey .= $show_dis_pre.'C'.$show_dis_mid.$clang->gT('Show question code only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showqnc = array( 'B' => '' , 'C' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showqnumcode'])) {
		    	$set_showqnc = $esrow['showqnumcode'];
			$sel_showqnc[$set_showqnc] = ' selected="selected"';
		    }
                if (empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N']) && empty($sel_showqnc['X'])) {
		    	$sel_showqnc['X'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showqnumcode\">".$clang->gT('Show question number and/or code')."</label>\n\t\t"
		    . "<select id=\"showqnumcode\" name=\"showqnumcode\">\n\t\t\t"
		    . '<option value="B"'.$sel_showqnc['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showqnc['N'].'>'.$clang->gT('Show question number only')."</option>\n\t\t\t"
		    . '<option value="C"'.$sel_showqnc['C'].'>'.$clang->gT('Show question code only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showqnc['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showqnc,$set_showqnc);
		    break;
	    };

            // Show "No Answer" block
	    $shownoanswer = isset($shownoanswer)?$shownoanswer:'Y';
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_shownoanswer\">".$clang->gT('Show "No answer"')."</label>\n\t\t".'<input type="hidden" name="shownoanswer" id="shownoanswer" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="';
        switch ($shownoanswer) {
	    	case 0:
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Off (Forced by the system administrator)').$show_dis_post;
		    break;
	        case 2:
		    $sel_showno = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['shownoanswer'])) {
		    	$set_showno = $esrow['shownoanswer'];
			$sel_showno[$set_showno] = ' selected="selected"';
		    };
                if (empty($sel_showno)) {
		    	$sel_showno['Y'] = ' selected="selected"';
		    };
	    	    $editsurvey .= "\n\t<li>\n\t\t<label for=\"shownoanswer\">".$clang->gT('Show "No answer"')."</label>\n\t\t"
		    . "<select id=\"shownoanswer\" name=\"shownoanswer\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showno['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showno['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    break;
		default:
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('On (Forced by the system administrator)').$show_dis_post;
		    break;
	    };

            // End Presention and navigation TAB
            $editsurvey .= "</ul></div>\n";

        */
        if (!isset($esrow['navigationdelay']))
        {
            $esrow['navigationdelay']=0;
        }

        $this->load->helper('globalsettings');

        $shownoanswer = getGlobalSetting('shownoanswer')?getGlobalSetting('shownoanswer'):'Y';

        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        //$data['surveyid'] = $surveyid;
        $data['shownoanswer'] = $shownoanswer;
        $data['showXquestions'] = $showXquestions;
        $data['showgroupinfo'] = $showgroupinfo;
        $data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superPresentation_view',$data, true);

    }

    /**
     * survey::_tabPublicationAccess()
     * Load "Publication * access control" tab.
     * @param mixed $esrow
     * @return
     */
    function _tabPublicationAccess($esrow)
    {
        $clang = $this->limesurvey_lang;
        /**
        $editsurvey = "<div id='publication'><ul>\n";

             //Public Surveys
            $editsurvey .= "<li><label for='public'>".$clang->gT("List survey publicly:")."</label>\n"
            . "<select id='public' name='public'>\n"
            . "<option value='Y'";
        if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Self registration
            $editsurvey .= "<li><label for='allowregister'>".$clang->gT("Allow public registration?")."</label>\n"
            . "<select id='allowregister' name='allowregister'>\n"
            . "<option value='Y'";
        if ($esrow['allowregister'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowregister'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";
            */
            // Start date
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $startdate='';
        if (trim($esrow['startdate']) != '') {
                $items = array($esrow['startdate'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
                $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            /**
            $editsurvey .= "<li><label for='startdate'>".$clang->gT("Start date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value=\"{$startdate}\" /></li>\n";
            */
            // Expiration date
            $expires='';
        if (trim($esrow['expires']) != '') {
                $items = array($esrow['expires'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
                $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            /**
            $editsurvey .="<li><label for='expires'>".$clang->gT("Expiry date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='expires' size='20' name='expires' value=\"{$expires}\" /></li>\n";

            //COOKIES
            $editsurvey .= "<li><label for=''>".$clang->gT("Set cookie to prevent repeated participation?")."</label>\n"
            . "<select name='usecookie'>\n"
            . "<option value='Y'";
        if ($esrow['usecookie'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecookie'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Use Captcha
            $editsurvey .= "<li><label for=''>".$clang->gT("Use CAPTCHA for").":</label>\n"
            . "<select name='usecaptcha'>\n"
            . "<option value='A'";
        if ($esrow['usecaptcha'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='B'";
        if ($esrow['usecaptcha'] == "B") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='C'";
        if ($esrow['usecaptcha'] == "C") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='D'";
        if ($esrow['usecaptcha'] == "D") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='X'";

        if ($esrow['usecaptcha'] == "X") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
            . "<option value='R'";
        if ($esrow['usecaptcha'] == "R") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='S'";
        if ($esrow['usecaptcha'] == "S") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecaptcha'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ---------</option>\n"
            . "</select>\n</li>\n";

            // Email format
            $editsurvey .= "<li><label for=''>".$clang->gT("Use HTML format for token emails?")."</label>\n"
            . "<select name='htmlemail' onchange=\"alert('".$clang->gT("If you switch email mode, you'll have to review your email templates to fit the new format","js")."');\">\n"
            . "<option value='Y'";
        if ($esrow['htmlemail'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['htmlemail'] == "N") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // End Publication and access control TAB
            $editsurvey .= "</ul></div>\n";
        return $editsurvey;

        */
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        //$data['surveyid'] = $surveyid;
        $data['startdate'] = $startdate;
        $data['expires'] = $expires;
        //$data['showgroupinfo'] = $showgroupinfo;
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superPublication_view',$data, true);

    }

    /**
     * survey::_tabNotificationDataManagement()
     * Load "Notification & data management" tab.
     * @param mixed $esrow
     * @return
     */
    function _tabNotificationDataManagement($esrow)
    {
        $clang = $this->limesurvey_lang;

        /**
        // Notification and Data management TAB
            $editsurvey = "<div id='notification'><ul>\n";


            //NOTIFICATION
            $editsurvey .= "<li><label for='emailnotificationto'>".$clang->gT("Send basic admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailnotificationto']}\" id='emailnotificationto' name='emailnotificationto' />\n"
            . "</li>\n";

            //EMAIL SURVEY RESPONSES TO
            $editsurvey .= "<li><label for='emailresponseto'>".$clang->gT("Send detailed admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailresponseto']}\" id='emailresponseto' name='emailresponseto' />\n"
            . "</li>\n";

            //ANONYMOUS
            $editsurvey .= "<li><label for=''>".$clang->gT("Anonymized responses?")."\n";
            // warning message if anonymous + tokens used
            $editsurvey .= "\n"
            . "<script type=\"text/javascript\"><!-- \n"
            . "function alertPrivacy()\n"
            . "{\n"
            . "if (document.getElementById('tokenanswerspersistence').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("You can't use Anonymized responses when Token-based answers persistence is enabled.","js")."');\n"
            . "document.getElementById('anonymized').value = 'N';\n"
            . "}\n"
            . "else if (document.getElementById('anonymized').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
            . "}\n"
            . "}"
            . "//--></script></label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['anonymized'] == "N") {
                $editsurvey .= " " . $clang->gT("This survey is NOT anonymous.");
            } else {
                $editsurvey .= $clang->gT("Answers to this survey are anonymized.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='anonymized' value=\"{$esrow['anonymized']}\" />\n";
        } else {
                $editsurvey .= "<select id='anonymized' name='anonymized' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['anonymized'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['anonymized'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // date stamp
            $editsurvey .= "<li><label for='datestamp'>".$clang->gT("Date Stamp?")."</label>\n";
        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not be date stamped.");
            } else {
                $editsurvey .= $clang->gT("Responses will be date stamped.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" />\n";
        } else {
                $editsurvey .= "<select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['datestamp'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // Ip Addr
            $editsurvey .= "<li><label for=''>".$clang->gT("Save IP Address?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have the IP address logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have the IP address logged");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n";
        } else {
                $editsurvey .= "<select name='ipaddr'>\n"
                . "<option value='Y'";
            if ($esrow['ipaddr'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }

            $editsurvey .= "</li>\n";

            // begin REF URL Block
            $editsurvey .= "<li><label for=''>".$clang->gT("Save referrer URL?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have their referring URL logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have their referring URL logged.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n";
        } else {
                $editsurvey .= "<select name='refurl'>\n"
                . "<option value='Y'";
            if ($esrow['refurl'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";
            // BENBUN - END REF URL Block

            // Enable assessments
            $editsurvey .= "<li><label for=''>".$clang->gT("Enable assessment mode?")."</label>\n"
            . "<select id='assessments' name='assessments'>\n"
            . "<option value='Y'";
        if ($esrow['assessments'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['assessments'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

            // Allow editing answers after completion
            $editsurvey .= "<li><label for=''>".$clang->gT("Allow editing answers after completion?")."</label>\n"
            . "<select id='alloweditaftercompletion' name='alloweditaftercompletion' onchange=\"javascript: if (document.getElementById('private').value == 'Y') {alert('".$clang->gT("This option can't be set if Anonymous answers are used","js")."'); this.value='N';}\">\n"
            . "<option value='Y'";
            if ($esrow['alloweditaftercompletion'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['alloweditaftercompletion'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

        // Save timings
        $editsurvey .= "<li><label for='savetimings'>".$clang->gT("Save timings?")."</label>\n";
        if ($esrow['active']=="Y")
        {
            $editsurvey .= "\n";
            if ($esrow['savetimings'] != "Y") {$editsurvey .= " ".$clang->gT("Timings will not be saved.");}
            else {$editsurvey .= $clang->gT("Timings will be saved.");}
            $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
            . "</font>\n";
            $editsurvey .= "<input type='hidden' name='savetimings' value='".$esrow['savetimings']."' />\n";
		}
		else
        {
			$editsurvey .= "<select id='savetimings' name='savetimings'>\n"
			. "<option value='Y'";
			if (!isset($esrow['savetimings']) || !$esrow['savetimings'] || $esrow['savetimings'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if (isset($esrow['savetimings']) && $esrow['savetimings'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select>\n"
			. "</li>\n";
		}
        //ALLOW SAVES
        $editsurvey .= "<li><label for='allowsave'>".$clang->gT("Participant may save and resume later?")."</label>\n"
        . "<select id='allowsave' name='allowsave'>\n"
        . "<option value='Y'";
        if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowsave'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";


        // End Notification and Data management TAB
        $editsurvey .= "</ul></div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;

        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superNotification_view',$data, true);

    }

    /**
     * survey::_tabTokens()
     * Load "Tokens" tab.
     * @param mixed $esrow
     * @return
     */
    function _tabTokens($esrow)
    {
        $clang = $this->limesurvey_lang;
        // Tokens TAB
        /**
        $editsurvey = "<div id='tokens'><ul>\n";
        // Token answers persistence
        $editsurvey .= "<li><label for=''>".$clang->gT("Enable token-based response persistence?")."</label>\n"
        . "<select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange=\"javascript: if (document.getElementById('anonymized').value == 'Y') {alert('".$clang->gT("This option can't be set if the `Anonymized responses` option is active.","js")."'); this.value='N';}\">\n"
        . "<option value='Y'";
        if ($esrow['tokenanswerspersistence'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N'";
        if ($esrow['tokenanswerspersistence'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Set token length
        $editsurvey .= "<li><label for='tokenlength'>".$clang->gT("Set token length to:")."</label>\n"
        . "<input type='text' value=\"{$esrow['tokenlength']}\" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";

        // End Tokens TAB
        $editsurvey .= "</ul></div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;

        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superTokens_view',$data, true);
    }

    /**
     * survey::_tabImport()
     * Load "Import" tab.
     * @param mixed $surveyid
     * @return
     */
    function _tabImport($surveyid)
    {
        $clang = $this->limesurvey_lang;
        /**
        // Import TAB
        $editsurvey = "<div id='import'>\n";

        // Import survey
        $editsurvey .= "<form enctype='multipart/form-data' class='form30' id='importsurvey' name='importsurvey' action='../database/index/insertsurvey' method='post' onsubmit='return validatefilename(this,\"" . $clang->gT('Please select a file to import!', 'js') . "\");'>\n"
                    . "<ul>\n"
                    . "<li><label for='the_file'>" . $clang->gT("Select survey structure file (*.lss, *.csv):") . "</label>\n"
                    . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
                    . "<li><label for='translinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked='checked'/></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Import survey") . "' />\n"
                    . "<input type='hidden' name='action' value='importsurvey' /></p></form>\n";

        // End Import TAB
        $editsurvey .= "</div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        $data['surveyid'] = $surveyid;
        //$data['esrow'] = $esrow;

        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superImport_view',$data, true);
    }

    /**
     * survey::_tabCopy()
     * Load "Copy" tab.
     * @param mixed $surveyid
     * @return
     */
    function _tabCopy($surveyid)
    {
        $clang = $this->limesurvey_lang;
        /**
        // Copy survey TAB
        $editsurvey = "<div id='copy'>\n";

        // Copy survey
        $editsurvey .= "<form class='form30' action='../database/index/insertsurvey' id='copysurveyform' method='post'>\n"
                    . "<ul>\n"
                    . "<li><label for='copysurveylist'><span class='annotationasterisk'>*</span>" . $clang->gT("Select survey to copy:") . "</label>\n"
                    . "<select id='copysurveylist' name='copysurveylist'>\n"
                    . getsurveylist(false, true) . "</select> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveyname'><span class='annotationasterisk'>*</span>" . $clang->gT("New survey title:") . "</label>\n"
                    . "<input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' />"
                    . "<span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveytranslinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='copysurveytranslinksfields' name=\"copysurveytranslinksfields\" type=\"checkbox\" checked='checked'/></li>\n"
                    . "<li><label for='copysurveyexcludequotas'>" . $clang->gT("Exclude quotas?") . "</label>\n"
                    . "<input id='copysurveyexcludequotas' name=\"copysurveyexcludequotas\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyexcludeanswers'>" . $clang->gT("Exclude answers?") . "</label>\n"
                    . "<input id='copysurveyexcludeanswers' name=\"copysurveyexcludeanswers\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyresetconditions'>" . $clang->gT("Reset conditions?") . "</label>\n"
                    . "<input id='copysurveyresetconditions' name=\"copysurveyresetconditions\" type=\"checkbox\" /></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Copy survey") . "' />\n"
                    . "<input type='hidden' name='action' value='copysurvey' /></p></form>\n";

        // End Copy survey TAB
        $editsurvey .= "</div>\n";

        return $editsurvey;
        */

        $data['clang'] = $clang;
        $data['surveyid'] = $surveyid;
        //$data['esrow'] = $esrow;

        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superCopy_view',$data, true);
    }


    /**
     * survey::_tabResourceManagement()
     * Load "Resources" tab.
     * @param mixed $surveyid
     * @return
     */
    function _tabResourceManagement($surveyid)
    {
        $clang = $this->limesurvey_lang;
        global $sCKEditorURL;
        // TAB Uploaded Resources Management
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
        if (!function_exists("zip_open")) {
            $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($surveyid, 'survey') === false) {
            $disabledIfNoResources = " disabled='disabled'";
        }
        /**
        // functionality not ported
        $editsurvey = "<div id='resources'>\n"
            . "<form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='../database/index/insertsurvey' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "<input type='hidden' name='action' value='importsurveyresources' />\n"
            . "<ul>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$sCKEditorURL/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$scriptname?action=exportsurvresources&amp;sid={$surveyid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources /></li>\n"
            . "<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
            . "<input id='the_file' name='the_file' type='file' size='50' /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
            . "</ul></form>\n";

        // End TAB Uploaded Resources Management
        $editsurvey .= "</div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        //$data['esrow'] = $esrow;
        $data['ZIPimportAction'] = $ZIPimportAction;
        $data['disabledIfNoResources'] = $disabledIfNoResources;
        $dqata['sCKEditorURL'] = $sCKEditorURL;
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/Survey/superview/superResourceManagement_view',$data, true);

    }

 }