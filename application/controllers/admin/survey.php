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
* @author  The LimeSurvey Project team
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
    * This function prepares the view for a new survey
    *
    */
    function newsurvey()
    {
        if(!bHasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
        {
            die();
        }

        self::_js_admin_includes($this->config->item('generalscripts').'admin/surveysettings.js');
        self::_js_admin_includes($this->config->item('generalscripts').'jquery/jqGrid/js/i18n/grid.locale-en.js');
        self::_js_admin_includes($this->config->item('generalscripts').'jquery/jqGrid/js/jquery.jqGrid.min.js');
        self::_css_admin_includes($this->config->item('styleurl')."admin/default/superfish.css");
        self::_css_admin_includes($this->config->item('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css");
        self::_getAdminHeader();
        self::_showadminmenu();;
        $this->load->helper('surveytranslator');
        $clang = $this->limesurvey_lang;

        $esrow = self::_fetchSurveyInfo('newsurvey');
        $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
        $this->load->helper('admin/htmleditor');

        $data = self::_generalTabNewSurvey();
        $data['esrow']=$esrow;

        $data = array_merge($data,self::_tabPresentationNavigation($esrow));
        $data = array_merge($data,self::_tabPublicationAccess($esrow));
        $data = array_merge($data,self::_tabNotificationDataManagement($esrow));
        $data = array_merge($data,self::_tabTokens($esrow));

        $this->load->view('admin/survey/newSurvey_view',$data);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * This function prepares the view for editing a survey
    *
    */

    function editsurveysettings($surveyid)
    {
        $surveyid=(int)$surveyid;
        if (is_null($surveyid) || !$surveyid)
        {
            die();
        }

        if(!bHasSurveyPermission($surveyid,'surveysettings','read') && !bHasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
        {
            die();
        }

        self::_js_admin_includes($this->config->item('generalscripts').'admin/surveysettings.js');
        self::_js_admin_includes($this->config->item('generalscripts').'jquery/jqGrid/js/i18n/grid.locale-en.js');
        self::_js_admin_includes($this->config->item('generalscripts').'jquery/jqGrid/js/jquery.jqGrid.min.js');
        self::_js_admin_includes($this->config->item('generalscripts').'jquery/jquery.json.min.js');
        self::_css_admin_includes($this->config->item('styleurl')."admin/default/superfish.css");
        self::_css_admin_includes($this->config->item('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css");
        self::_getAdminHeader();
        self::_showadminmenu($surveyid);;
        self::_surveybar($surveyid);

        $this->load->helper('text');
        $this->load->helper('surveytranslator');
        $clang = $this->limesurvey_lang;

        $esrow = array();
        $editsurvey = '';
        $esrow = self::_fetchSurveyInfo('editsurvey',$surveyid);
        $data['esrow']=$esrow;

        $data = self::_generalTabEditSurvey($surveyid,$esrow);
        $data = array_merge($data,self::_tabPresentationNavigation($esrow));
        $data = array_merge($data,self::_tabPublicationAccess($esrow));
        $data = array_merge($data,self::_tabNotificationDataManagement($esrow));
        $data = array_merge($data,self::_tabTokens($esrow));
        $data = array_merge($data,self::_tabPanelIntegration($esrow));
        $data = array_merge($data,self::_tabResourceManagement($surveyid));

        //echo $editsurvey;
        $this->load->model('questions_model');
        $oResult=$this->questions_model->getQuestionsWithSubQuestions($surveyid,$esrow['language'],"({$this->db->dbprefix}questions.type = 'T'  OR  {$this->db->dbprefix}questions.type = 'Q'  OR  {$this->db->dbprefix}questions.type = 'T' OR {$this->db->dbprefix}questions.type = 'S')");
        $data['questions']=$oResult->result_array();
        //        var_dump($data['questions']);
        $data['display'] = $editsurvey;
        $data['action'] = "editsurveysettings";
        $this->load->view('admin/survey/editSurvey_view',$data);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
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
            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['date'] = $date;
            $data['dbprefix'] = $this->db->dbprefix;
            $data['step1'] = true;
            self::_surveybar($surveyid);
            $this->load->view('admin/survey/deactivateSurvey_view',$data);
        }

        else
        {
            $this->load->helper('database');
            //See if there is a tokens table for this survey
            if (tableExists("tokens_{$postsid}"))
            {
                $toldtable=$this->db->dbprefix."tokens_{$postsid}";
                $tnewtable=$this->db->dbprefix."old_tokens_{$postsid}_{$date}";
                $tdeactivateresult = db_rename_table($toldtable ,$tnewtable) or die ("Couldn't deactivate tokens table because:<br /><br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");

                if ($this->db->dbdriver=='postgre')
                {
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

            $condn = array('sid' => $surveyid);
            $insertdata = array('autonumber_start' => $new_autonumber_start);
            $this->load->model('surveys_model');
            $this->surveys_model->updateSurvey($insertdata,$condn);

            $deactivateresult = db_rename_table($oldtable,$newtable) or die ("Couldn't make backup of the survey table. Please try again. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");

            if ($this->db->dbdriver=='postgre')
            {
                $deactivateresult = db_rename_table($oldtable.'_id_seq',$newtable.'_id_seq') or die ("Couldn't make backup of the survey table. Please try again. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
                $setsequence="ALTER TABLE $newtable ALTER COLUMN id SET DEFAULT nextval('{$newtable}_id_seq'::regclass);";
                $deactivateresult = db_execute_assosc($setsequence) or die ("Couldn't make backup of the survey table. Please try again. <br /><br />Survey was not deactivated either.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
            }

            $insertdata = array('active' => 'N');
            $deactivateresult = $this->surveys_model->updateSurvey($insertdata,$condn) or die ("Couldn't deactivate because updating of surveys table failed!<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>Admin</a>");

            $pquery = "SELECT savetimings FROM ".$this->db->dbprefix."surveys WHERE sid={$postsid}";
            $presult=db_execute_assoc($pquery);
            $prow=$presult->row_array(); //fetch savetimings value
            if ($prow['savetimings'] == "Y")
            {
                $oldtable=$this->db->dbprefix."survey_{$postsid}_timings";
                $newtable=$this->db->dbprefix."old_survey_{$postsid}_timings_{$date}";

                $deactivateresult2 = db_rename_table($oldtable,$newtable) or die ("Couldn't make backup of the survey timings table. Please try again.<br /><br />Survey was deactivated.<br /><br /><a href='".site_url('admin/survey/view/'.$postsid)."'>".$clang->gT("Main Admin Screen")."</a>");
                $deactivateresult=($deactivateresult && $deactivateresult2);
            }

            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['newtable'] = $newtable;
            self::_surveybar($surveyid);
            $this->load->view('admin/survey/deactivateSurvey_view',$data);

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
    function activate($iSurveyID)
    {
        $iSurveyID=(int) $iSurveyID;

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        $data['aSurveysettings'] = getSurveyInfo($iSurveyID);
        if (!isset($data['aSurveysettings']['active']) || $data['aSurveysettings']['active']=='Y') die(); // Die if this is not possible

        self::_getAdminHeader();
        self::_showadminmenu($iSurveyID);
        self::_surveybar($iSurveyID);



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
            $failedgroupcheck = checkGroup($iSurveyID);
            $failedcheck = checkQuestions($iSurveyID, $iSurveyID, $qtypes);

            $data['clang'] = $this->limesurvey_lang;
            $data['surveyid'] =  $iSurveyID;
            $data['$failedcheck'] = $failedcheck;
            $data['failedgroupcheck'] = $failedgroupcheck;
            $data['aSurveysettings'] = getSurveyInfo($iSurveyID);

            $this->load->view("admin/survey/activateSurvey_view",$data);
            //IF ANY OF THE CHECKS FAILED, PRESENT THIS SCREEN


        }
        else
        {
            $this->load->model("surveys_model");
            $this->surveys_model->updateSurvey(array('anonymized'=>$this->input->post('anonymized'),
                                                'datestamp'=>$this->input->post('datestamp'),
                                                'ipaddr'=>$this->input->post('ipaddr'),
                                                'refurl'=>$this->input->post('refurl'),
                                                'savetimings'=>$this->input->post('savetimings')),
                                                array('sid'=>$iSurveyID));
            $activateoutput = activateSurvey($iSurveyID,$iSurveyID);
            $displaydata['display'] = $activateoutput;
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
        $result = db_execute_assoc($query);

        if($result->num_rows() > 0) {


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
                            . " title=\"".$clang->gTview("This survey is active - click here to stop this survey.")."\" >"
                            . "<img src='".$this->config->item('imageurl')."/active.png' alt='".$clang->gT("This survey is active - click here to stop this survey.")."' /></a></td>\n";
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
            $this->load->view('admin/survey/listSurveys_view',$data);

        }
        else
        {
            $listsurveys ="<p><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
        }

        $displaydata['display'] = $listsurveys;
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
        if (!$this->input->post('deleteok'))
        {
            self::_showadminmenu($surveyid);
            self::_surveybar($surveyid);
        }

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
                    $dsresult = $this->dbforge->drop_table('survey_'.$surveyid) or safe_die ("Couldn't drop table survey_".$surveyid." in survey.php");
                }

                if (tableExists("survey_{$surveyid}_timings"))  //delete the survey_$surveyid_timings table
                {
                    $dsresult = $this->dbforge->drop_table('survey_'.$surveyid.'_timings') or safe_die ("Couldn't drop table survey_".$surveyid."_timings in survey.php");
                }

                if (tableExists("tokens_$surveyid")) //delete the tokens_$surveyid table
                {
                    $dsresult = $this->dbforge->drop_table('tokens_'.$surveyid) or safe_die ("Couldn't drop table token_".$surveyid." in survey.php");
                }

                $dsquery = "SELECT qid FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid";
                $dsresult = db_execute_assoc($dsquery) or safe_die ("Couldn't find matching survey to delete<br />$dsquery<br />");
                foreach ($dsresult->result_array() as $dsrow)
                {
                    $this->db->delete('answers', array('qid' => $dsrow['qid']));
                    $this->db->delete('conditions', array('qid' => $dsrow['qid']));
                    $this->db->delete('question_attributes', array('qid' => $dsrow['qid']));

                }

                $this->db->delete('questions', array('sid' => $surveyid));
                $this->db->delete('assessments', array('sid' => $surveyid));
                $this->db->delete('groups', array('sid' => $surveyid));
                $this->db->delete('surveys_languagesettings', array('surveyls_survey_id' => $surveyid));
                $this->db->delete('survey_permissions', array('sid' => $surveyid));
                $this->db->delete('saved_control', array('sid' => $surveyid));
                $this->db->delete('surveys', array('sid' => $surveyid));
                $this->load->model('survey_url_parameters_model');
                $this->survey_url_parameters_model->deleteRecords(array('sid'=>$surveyid));

                $sdel = "DELETE ".$this->db->dbprefix."quota_languagesettings FROM ".$this->db->dbprefix."quota_languagesettings, ".$this->db->dbprefix."quota WHERE ".$this->db->dbprefix."quota_languagesettings.quotals_quota_id=".$this->db->dbprefix."quota.id and sid=$surveyid";
                $sres = db_execute_assoc($sdel);
                $this->db->delete('quota', array('sid' => $surveyid));

                $this->db->delete('quota_members', array('sid' => $surveyid));
                rmdirr($this->config->item("uploaddir").'/surveys/'.$surveyid);
                self::_showadminmenu(false);
            }
            $this->load->view('admin/survey/deleteSurvey_view',$data);
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
    * survey::editlocalesettings()
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

                $tab_content[$i] = $this->load->view('admin/survey/editLocalSettings_view',$data,true);


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
                elseif (isset($sExtension) && strtolower($sExtension)=='.zip')
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
            $esrow['navigationdelay']          = 0;
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

        $data['action'] = "newsurvey";
        $data['clang'] = $clang;
        $data['owner'] = $owner;
        return $data;
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
        return $data;
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

        $this->load->helper('globalsettings');

        $shownoanswer = getGlobalSetting('shownoanswer')?getGlobalSetting('shownoanswer'):'Y';

        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        $data['shownoanswer'] = $shownoanswer;
        $data['showXquestions'] = $showXquestions;
        $data['showgroupinfo'] = $showgroupinfo;
        $data['showqnumcode'] = $showqnumcode;
        return $data;

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
        $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
        $startdate='';
        if (trim($esrow['startdate']) != '') {
            $items = array($esrow['startdate'] , "Y-m-d H:i:s");
            $this->load->library('Date_Time_Converter',$items);
            $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
            $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }

        $expires='';
        if (trim($esrow['expires']) != '') {
            $items = array($esrow['expires'] , "Y-m-d H:i:s");
            $this->load->library('Date_Time_Converter',$items);
            $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
            $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        $data['startdate'] = $startdate;
        $data['expires'] = $expires;
        return $data;
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

        $data['clang'] = $clang;
        $data['esrow'] = $esrow;

        return $data;

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

        $data['clang'] = $clang;
        $data['esrow'] = $esrow;

        return $data;
    }

    function _tabPanelIntegration($esrow)
    {
        $data=array();
        return $data;
    }


    /**
    * survey::_tabImport()
    * Load "Import" tab.
    * @param mixed $surveyid
    * @return
    */
    function _tabImport()
    {
        $data=array();
        return $data;
    }

    /**
    * survey::_tabCopy()
    * Load "Copy" tab.
    * @param mixed $surveyid
    * @return
    */
    function _tabCopy()
    {
        $data=array();
        return $data;
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
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) { this.form.submit();}'";
        if (!function_exists("zip_open")) {
            $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($surveyid, 'survey') === false) {
            $disabledIfNoResources = " disabled='disabled'";
        }
        $data['clang'] = $clang;
        //$data['esrow'] = $esrow;
        $data['ZIPimportAction'] = $ZIPimportAction;
        $data['disabledIfNoResources'] = $disabledIfNoResources;
        $dqata['sCKEditorURL'] = $sCKEditorURL;
        //$data['showqnumcode'] = $showqnumcode;
        return $data;

    }

    function expire($iSurveyID)
    {
        $surveyid = (int)$iSurveyID;
        if(!bHasSurveyPermission($iSurveyID,'surveysettings','update'))
        {
            die();
        }
        $clang = $this->limesurvey_lang;
        $this->session->set_userdata('flashmessage',$clang->gT("The survey was successfully expired by setting an expiration date in the survey settings."));
        $this->load->model('surveys_model');
        $dExpirationdate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $this->config->item('timeadjust'));
        $dExpirationdate=date_shift($dExpirationdate, "Y-m-d H:i:s", '-1 day');
        $this->surveys_model->updateSurvey(array('expires'=>$dExpirationdate),
                                           array('sid'=>$iSurveyID));
        redirect('admin/survey/view/'.$iSurveyID);

    }


    function getUrlParamsJSON($iSurveyID)
    {
        $iSurveyID = (int)$iSurveyID;
        $this->load->model('survey_url_parameters_model');
        $this->load->helper('text');
        $oResult=$this->survey_url_parameters_model->getParametersForSurvey($iSurveyID);
        $i=0;
        foreach ($oResult->result_array() as $oRow)
        {
            $data->rows[$i]['id']=$oRow['id'];
            $oRow['title']= $oRow['title'].': '.ellipsize(FlattenText($oRow['question'],true),43,.70);
            if ($oRow['sqquestion']!='')
            {
                echo ' - '.ellipsize(FlattenText($oRow['sqquestion'],true),30,.75);
            }
            unset($oRow['sqquestion']);
            unset($oRow['sqtitle']);
            unset($oRow['question']);

            $data->rows[$i]['cell']=array_values($oRow);
            $i++;
        }

        $data->page = 1;
        $data->records = $oResult->num_rows();
        $data->total = 1;

        echo json_encode($data);
    }

}
