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
* $Id: survey.php 11349 2011-11-09 21:49:00Z tpartner $
*/

/**
* survey
*
* @package LimeSurvey
* @author  The LimeSurvey Project team
* @copyright 2011
* @version $Id: survey.php 11349 2011-11-09 21:49:00Z tpartner $
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

            if ($this->config->item('demoMode'))
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
                    $importsurveyresourcesoutput .= $clang->gT("This file is not a valid ZIP file $CI. Import failed.")."<br /><br />\n";
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
                    $importsurveyresourcesoutput .= $clang->gT("This ZIP $CI contains no valid Resources files. Import failed.")."<br /><br />\n";
                    $importsurveyresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP $CIs.")."<br /><br />\n";
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

    function showsyntaxerrors($surveyid)
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

        $data['errors'] = LimeExpressionManager::GetSyntaxErrors();

        $this->load->view('admin/survey/showSyntaxErrors_view',$data);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    function resetsyntaxerrorlog($surveyid)
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

        LimeExpressionManager::ResetSyntaxErrorLog();

        $this->load->view('admin/survey/resetSyntaxErrorLog_view');
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
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
        $surveyid = sanitize_int($surveyid);
        if(isset($gid)) $gid = sanitize_int($gid);
        if(isset($qid)) $qid = sanitize_int($qid);

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
        $surveyid = sanitize_int($surveyid);
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
            $activateoutput = activateSurvey($iSurveyID);
            $displaydata['display'] = $activateoutput;
            $this->load->view('survey_view',$displaydata);
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }


    /**
    * survey::ajaxgetusers()
    * This get the userlist in surveylist screen.
    * @return void
    */
    function ajaxgetusers()
    {
        header('Content-type: application/json');
        $this->load->helper('database');

        $query = "SELECT users_name, uid FROM ".$this->db->dbprefix."users";

        $result = db_execute_assoc($query) or show_error("Couldn't execute $query");

        $aUsers = array();
        if($result->num_rows() > 0) {
            foreach($result->result_array() as $rows)
                $aUsers[] = array($rows['uid'], $rows['users_name']);
        }

        $ajaxoutput = json_encode($aUsers) . "\n";
        echo $ajaxoutput;
    }

    /**
    * survey::ajaxowneredit()
    * This function change the owner of a survey.
    * @param mixed $newowner
    * @param mixed $surveyid
    * @return void
    */
    function ajaxowneredit($newowner,$surveyid)
    {
        header('Content-type: application/json');
        $this->load->helper('database');
        //if (isset($_REQUEST['newowner'])) {$intNewOwner=sanitize_int($_REQUEST['newowner']);}
        //if (isset($_REQUEST['survey_id'])) {$intSurveyId=sanitize_int($_REQUEST['survey_id']);}
        $intNewOwner = sanitize_int($newowner);
        $intSurveyId = sanitize_int($surveyid);
        $owner_id = $this->session->userdata('loginID');

        header('Content-type: application/json');

        $query = "UPDATE ".$this->db->dbprefix."surveys SET owner_id = $intNewOwner WHERE sid=$intSurveyId";
        if (bHasGlobalPermission("USER_RIGHT_SUPERADMIN"))
            $query .="";
        else
            $query .=" AND owner_id=$owner_id";

        $result = db_execute_assoc($query) or show_error("Couldn't execute $query");

        $query = "SELECT b.users_name FROM ".$this->db->dbprefix."surveys as a"
        ." INNER JOIN  ".$this->db->dbprefix."users as b ON a.owner_id = b.uid   WHERE sid=$intSurveyId AND owner_id=$intNewOwner;";
        $result = db_execute_assoc($query) or show_error("Couldn't execute $query");
        $intRecordCount = $result->num_rows();

        $aUsers = array(
        'record_count' => $intRecordCount,
        );

        if($result->num_rows() > 0) {
            foreach($result->result_array() as $rows)
                $aUsers['newowner'] = $rows['users_name'];
        }
        $ajaxoutput = json_encode($aUsers) . "\n";
        echo $ajaxoutput;


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



        $query = " SELECT a.*, c.*, u.users_name FROM ".$this->db->dbprefix."surveys as a "
        ." INNER JOIN ".$this->db->dbprefix."surveys_languagesettings as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) AND surveyls_survey_id=a.sid AND surveyls_language=a.language "
        ." INNER JOIN ".$this->db->dbprefix."users as u ON (u.uid=a.owner_id) ";

        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
        {
            $query .= "WHERE a.sid in (select sid from ".$this->db->dbprefix."survey_permissions WHERE uid=".$this->session->userdata('loginID')." AND permission='survey' AND read_p=1) ";
            $data['issuperadmin']=false;
        }
        else
        {
            $data['issuperadmin']=true;
        }
        $query .= " ORDER BY surveyls_title";
        $result = db_execute_assoc($query);

        if($result->num_rows() > 0) {

            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $listsurveys = "";
            $first_time = true;
            foreach ($result->result_array() as $rows)
            {
                $aSurveyEntry['dbactive']=($rows['active']=="Y");
                $aSurveyEntry['surveyid']=$rows['sid'];
                $aSurveyEntry['mayupdate']=bHasSurveyPermission($rows['sid'],'surveyactivation','update');


                if($rows['anonymized']=="Y")
                {
                    $aSurveyEntry['privacy']=$clang->gT("Yes") ;
                }
                else
                {
                    $aSurveyEntry['privacy']=$clang->gT("No") ;
                }


                if (tableExists('tokens_'.$rows['sid']))
                {
                    $aSurveyEntry['visibility'] = $clang->gT("Closed");
                }
                else
                {
                    $aSurveyEntry['visibility'] = $clang->gT("Open");
                }

                if($rows['active']=="Y")
                {
                    $aSurveyEntry['bActive']=true;
                    if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $aSurveyEntry['statusText']=$clang->gT("Expired") ;
                        $aSurveyEntry['status']='expired' ;
                    }
                    elseif ($rows['startdate']!='' && $rows['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $aSurveyEntry['statusText']=$clang->gT("Not yet active") ;
                        $aSurveyEntry['status']='notyetactive' ;
                    }
                    else {
                        $aSurveyEntry['statusText']=$clang->gT("Active") ;
                        $aSurveyEntry['status']='active' ;
                    }

                    // Complete Survey Responses - added by DLR
                    $gnquery = "SELECT COUNT(id) AS countofid FROM ".$this->db->dbprefix."survey_".$rows['sid']." WHERE submitdate IS NULL";
                    $gnresult = db_execute_assoc($gnquery); //Checked)

                    foreach ($gnresult->result_array() as $gnrow)
                    {
                        $aSurveyEntry['partial_responses']=$gnrow['countofid'];
                    }
                    $gnquery = "SELECT COUNT(id) AS countofid FROM ".$this->db->dbprefix."survey_".$rows['sid'];
                    $gnresult = db_execute_assoc($gnquery); //Checked
                    foreach ($gnresult->result_array() as $gnrow)
                    {
                        $aSurveyEntry['responses']=$gnrow['countofid'];
                    }

                }
                else
                {
                    $aSurveyEntry['statusText'] = $clang->gT("Inactive") ;
                    $aSurveyEntry['status']='inactive' ;
                }

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


                $aSurveyEntry['datecreated']=$datetimeobj->convert($dateformatdetails['phpdate']);

                if (in_array($rows['owner_id'],getuserlist('onlyuidarray')))
                {
                    $aSurveyEntry['ownername']=$rows['users_name'] ;
                }
                else
                {
                    $aSurveyEntry['ownername']="---";
                }

                $questionsCountQuery = "SELECT qid FROM ".$this->db->dbprefix."questions WHERE sid={$rows['sid']} AND language='".$rows['language']."'"; //Getting a count of questions for this survey
                $questionsCountResult = db_execute_assoc($questionsCountQuery); //($connect->Execute($questionsCountQuery); //Checked)
                $aSurveyEntry['questioncount'] = $questionsCountResult->num_rows();


                $aSurveyEntry['viewurl'] = site_url("admin/survey/view/".$rows['sid']);
                $aSurveyEntry['iSurveyID']=$rows['sid'];
                $aSurveyEntry['sSurveyTitle']=$rows['surveyls_title'];


                if ($rows['active']=="Y" && tableExists("tokens_".$rows['sid']))
                {
                    //get the number of tokens for each survey
                    $tokencountquery = "SELECT COUNT(tid) AS countoftid FROM ".$this->db->dbprefix."tokens_".$rows['sid'];
                    $tokencountresult = db_execute_assoc($tokencountquery); //Checked)
                    foreach ($tokencountresult->result_array() as $tokenrow)
                    {
                        $aSurveyEntry['tokencount'] = $tokenrow['countoftid'];
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
                    if($tokencompleted != 0 && $aSurveyEntry['tokencount'] != 0)
                    {
                        $aSurveyEntry['tokenpercentage'] = round(($tokencompleted / $aSurveyEntry['tokencount']) * 100, 1);
                    }
                    else
                    {
                        $aSurveyEntry['tokenpercentage'] = 0;
                    }

                }
                else
                {
                    $aSurveyEntry['tokenpercentage'] = '&nbsp;';
                    $aSurveyEntry['tokencount'] = '&nbsp;';
                }

                $listsurveys .= "</tr>" ;
                $data['aSurveyEntries'][]=$aSurveyEntry;
            }

            $listsurveys.="</tbody>";
            $listsurveys.="</table><br />" ;
            $data['clang'] = $clang;

        }
        else
        {
            $data['sSurveyEntries']=array();
            $listsurveys ="<p><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
        }

        self::_getAdminHeader();
        self::_showadminmenu(false);;
        $data['imageurl']=$this->config->item('imageurl');
        $this->load->view('admin/survey/listSurveys_view',$data);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * This functions receives the form data from listsurveys and executes accroding mass actions, like survey deletions, etc.
    * Only superadmins are allowed to do this!
    */

    function surveyactions()
    {
        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
        {
            die();
        }
        $aSurveyIDs=$this->input->post('surveyids');
        $sSurveysAction=$this->input->post('surveysaction');
        $actioncount=0; $message=$this->limesurvey_lang->gT('You did not choose any surveys.');
        if (is_array($aSurveyIDs))
            foreach($aSurveyIDs as $iSurveyID)
            {
                $iSurveyID= (int)$iSurveyID;
                switch ($sSurveysAction){
                    case 'delete': $this->_deleteSurvey($iSurveyID);
                        $message=$this->limesurvey_lang->gT('%s survey(s) were successfully deleted.');
                        $actioncount++;
                        break;
                    case 'expire': if ($this->_expireSurvey($iSurveyID)) $actioncount++;;
                        $message=$this->limesurvey_lang->gT('%s survey(s) were successfully expired.');
                        break;
                    case 'archive': $this->session->set_flashdata('sids', $aSurveyIDs);
                        redirect('admin/export/surveyarchives');
                        break;
                }
        }
        $this->session->set_userdata('flashmessage',sprintf($message, $actioncount));
        redirect('admin/survey/listsurveys');
    }



    /**
    * survey::delete()
    * Function responsible to delete a survey.
    * @return
    */
    function delete()
    {
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
        $data['surveyid'] = $surveyid = (int) $this->input->post('sid');
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
                self::_deleteSurvey($surveyid);
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
        $surveyid = sanitize_int($surveyid);

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
                $aData['sHeader']=$clang->gT("Import survey data");
                $aData['sSummaryHeader']=$clang->gT("Survey structure import summary");
                $importingfrom = "http";
            }
            elseif($action == 'copysurvey')
            {
                $aData['sHeader']=$clang->gT("Copy survey");
                $aData['sSummaryHeader']=$clang->gT("Survey copy summary");
            }
            // Start traitment and messagebox
            $aData['bFailed']=false; // Put a var for continue

            if ($action == 'importsurvey')
            {

                $the_full_file_path = $this->config->item('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
                if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
                {
                    $aData['sErrorMessage'] = sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$this->config->item('tempdir'));
                    $aData['bFailed']=true;
                }
                else
                {
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

                if (!$aData['bFailed'] && (strtolower($sExtension)!='csv' && strtolower($sExtension)!='lss' && strtolower($sExtension)!='zip'))
                {
                    $aData['sErrorMessage'] = $clang->gT("Import failed. You specified an invalid file type.");
                    $aData['bFailed']=true;
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
                    $aData['sErrorMessage'] = $clang->gT("No survey ID has been provided. Cannot copy survey");
                    $aData['bFailed']=true;
                }

                $this->load->helper('export_helper');
                $copysurveydata = survey_getXMLData($surveyid,$exclude);
            }

            // Now, we have the survey : start importing
            //require_once('import_functions.php');
            $this->load->helper('admin/import');
            $_POST = $this->input->post();
            if ($action == 'importsurvey' && !$aData['bFailed'])
            {

                if (isset($sExtension) && strtolower($sExtension)=='csv')
                {
                    $aImportResults=CSVImportSurvey($sFullFilepath,null,(isset($_POST['translinksfields'])));
                }
                elseif (isset($sExtension) && strtolower($sExtension)=='lss')
                {
                    $aImportResults=XMLImportSurvey($sFullFilepath,null,null, null,(isset($_POST['translinksfields'])));
                }
                elseif (isset($sExtension) && strtolower($sExtension)=='zip')  // Import a survey archive
                {
                    $this->load->library("admin/pclzip/pclzip",array('p_zipname' => $sFullFilepath));
                    $aFiles=$this->pclzip->listContent();
                    if ($this->pclzip->extract(PCLZIP_OPT_PATH, $this->config->item('tempdir').DIRECTORY_SEPARATOR, PCLZIP_OPT_BY_EREG, '/(lss|lsr|lsi|lst)$/')== 0) {
                        unset($this->pclzip);
                    }
                    // Step 1 - import the LSS file and activate the survey
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lss')
                        {
                            //Import the LSS file
                            $aImportResults=XMLImportSurvey($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],null, null, null,true);
                            // Activate the survey
                            $this->load->helper("admin/activate");
                            $activateoutput = activateSurvey($aImportResults['newsid']);
                            unlink($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                    // Step 2 - import the responses file
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lsr')
                        {
                            //Import the LSS file
                            $aResponseImportResults=XMLImportResponses($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],$aImportResults['newsid'],$aImportResults['FieldReMap']);
                            $aImportResults=array_merge($aResponseImportResults,$aImportResults);
                            unlink($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                    // Step 3 - import the tokens file - if exists
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lst')
                        {
                            $this->load->helper("admin/token");
                            if (createTokenTable($aImportResults['newsid'])) $aTokenCreateResults = array('tokentablecreated'=>true);
                            $aImportResults=array_merge($aTokenCreateResults,$aImportResults);
                            $aTokenImportResults = XMLImportTokens($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],$aImportResults['newsid']);
                            $aImportResults=array_merge($aTokenImportResults,$aImportResults);
                            unlink($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                    // Step 4 - import the timings file - if exists
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lsi' && tableExists("survey_{$aImportResults['newsid']}_timings"))
                        {
                            $aTimingsImportResults = XMLImportTimings($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],$aImportResults['newsid'],$aImportResults['FieldReMap']);
                            $aImportResults=array_merge($aTimingsImportResults,$aImportResults);
                            unlink($this->config->item('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
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
            if ($action == 'importsurvey')
            {
                unlink($sFullFilepath);
            }

            $aData['action']= $action;
            $aData['sLink']= site_url('admin/survey/view/'.$aImportResults['newsid']);
            $aData['aImportResults']=$aImportResults;
        }

        self::_getAdminHeader();
        self::_showadminmenu();;
        $this->load->view('admin/survey/importSurvey_view',$aData);
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
        if(isset($surveyid)) $surveyid = sanitize_int($surveyid);
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
            $esrow['sendconfirmation']         = 'Y';
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
        $iSurveyID = (int)$iSurveyID;
        if(!bHasSurveyPermission($iSurveyID,'surveysettings','update'))
        {
            die();
        }
        $clang = $this->limesurvey_lang;
        $this->session->set_userdata('flashmessage',$clang->gT("The survey was successfully expired by setting an expiration date in the survey settings."));
        _expireSurvey($iSurveyID);
        $this->load->model('surveys_model');
        $dExpirationdate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $this->config->item('timeadjust'));
        $dExpirationdate=date_shift($dExpirationdate, "Y-m-d H:i:s", '-1 day');
        $this->surveys_model->updateSurvey(array('expires'=>$dExpirationdate),
        array('sid'=>$iSurveyID));
        redirect('admin/survey/view/'.$iSurveyID);

    }

    /**
    * Expires a survey
    *
    * @param mixed $iSurveyID The survey ID
    * @return False if not successful
    */
    function _expireSurvey($iSurveyID)
    {
        $this->load->model('surveys_model');
        $dExpirationdate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $this->config->item('timeadjust'));
        $dExpirationdate=date_shift($dExpirationdate, "Y-m-d H:i:s", '-1 day');
        return $this->surveys_model->updateSurvey(array('expires'=>$dExpirationdate), array('sid'=>$iSurveyID));
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
            $oRow['title']= $oRow['title'].': '.ellipsize(FlattenText($oRow['question'],false,true),43,.70);
            if ($oRow['sqquestion']!='')
            {
                echo ' - '.ellipsize(FlattenText($oRow['sqquestion'],false,true),30,.75);
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

        echo ls_json_encode($data);
    }

    /**
    * This private function deletes a survey
    * Important: If you change this function also change the remotecontrol XMLRPC function
    *
    * @param mixed $iSurveyID  The survey ID to delete
    */
    function _deleteSurvey($iSurveyID)
    {
        $this->load->model('surveys_model');
        $this->surveys_model->deleteSurvey($iSurveyID);
        rmdirr($this->config->item("uploaddir").'/surveys/'.$iSurveyID);
    }

    /**
    * Saves the new survey after the creation screen is submitted
    *
    * @param $iSurveyID  The survey id to be used for the new survey. If already taken a new random one will be used.
    */
    function insert($iSurveyID=null)
    {
        if ($this->session->userdata('USER_RIGHT_CREATE_SURVEY'))
        {
            // Check if survey title was set
            if (!$this->input->post('surveyls_title'))
            {
                $this->session->set_userdata('flashmessage',$clang->gT("Survey could not be created because it did not have a title"));
                redirect(site_url('admin'));
                return;
            }

            // Check if template may be used
            $sTemplate = $this->input->post('template');
            if(!$sTemplate || ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') != 1 && !hasTemplateManageRights($this->session->userdata('loginID'), $this->input->post('template'))))
            {
                $sTemplate = "default";
            }

            $this->load->helper("surveytranslator");


            // If start date supplied convert it to the right format
            $aDateFormatData=getDateFormatData($_SESSION['dateformat']);
            $sStartDate = $this->input->post('startdate');
            if (trim($sStartDate)!='')
            {
                $this->load->library('Date_Time_Converter',array($sStartDate , $aDateFormatData['phpdate'].' H:i:s'));
                $sStartDate=$this->date_time_converter->convert("Y-m-d H:i:s");
            }

            // If expiry date supplied convert it to the right format
            $sExpiryDate = $this->input->post('expires');
            if (trim($sExpiryDate)!='')
            {
                $this->load->library('Date_Time_Converter',array($sExpiryDate , $aDateFormatData['phpdate'].' H:i:s'));
                $sExpiryDate=$this->date_time_converter->convert("Y-m-d H:i:s");
            }

            // Insert base settings into surveys table
            $aInsertData=array(
            'expires'=>$sExpiryDate,
            'startdate'=>$sStartDate,
            'template'=>$sTemplate,
            'owner_id'=>$this->session->userdata('loginID'),
            'admin'=>$this->input->post('admin'),
            'active'=>'N',
            'adminemail'=>$this->input->post('adminemail'),
            'bounce_email'=>$this->input->post('bounce_email'),
            'anonymized'=>$this->input->post('anonymized'),
            'faxto'=>$this->input->post('faxto'),
            'format'=>$this->input->post('format'),
            'savetimings'=>$this->input->post('savetimings'),
            'language'=>$this->input->post('language'),
            'datestamp'=>$this->input->post('datestamp'),
            'ipaddr'=>$this->input->post('ipaddr'),
            'refurl'=>$this->input->post('refurl'),
            'usecookie'=>$this->input->post('usecookie'),
            'emailnotificationto'=>$this->input->post('emailnotificationto'),
            'allowregister'=>$this->input->post('allowregister'),
            'allowsave'=>$this->input->post('allowsave'),
            'navigationdelay'=>$this->input->post('navigationdelay'),
            'autoredirect'=>$this->input->post('autoredirect'),
            'showXquestions'=>$this->input->post('showXquestions'),
            'showgroupinfo'=>$this->input->post('showgroupinfo'),
            'showqnumcode'=>$this->input->post('showqnumcode'),
            'shownoanswer'=>$this->input->post('shownoanswer'),
            'showwelcome'=>$this->input->post('showwelcome'),
            'allowprev'=>$this->input->post('allowprev'),
            'allowjumps'=>$this->input->post('allowjumps'),
            'nokeyboard'=>$this->input->post('nokeyboard'),
            'showprogress'=>$this->input->post('showprogress'),
            'printanswers'=>$this->input->post('printanswers'),
            'listpublic'=>$this->input->post('public'),
            'htmlemail'=>$this->input->post('htmlemail'),
            'sendconfirmation'=>$this->input->post('sendconfirmation'),
            'tokenanswerspersistence'=>$this->input->post('tokenanswerspersistence'),
            'alloweditaftercompletion'=>$this->input->post('alloweditaftercompletion'),
            'usecaptcha'=>$this->input->post('usecaptcha'),
            'publicstatistics'=>$this->input->post('publicstatistics'),
            'publicgraphs'=>$this->input->post('publicgraphs'),
            'assessments'=>$this->input->post('assessments'),
            'emailresponseto'=>$this->input->post('emailresponseto'),
            'tokenlength'=>$this->input->post('tokenlength')
            );
            if (!is_null($iSurveyID))
            {
                $aInsertData['wishSID']=$iSurveyID;
            }

            $this->load->model('surveys_model');
            $iNewSurveyid=$this->surveys_model->insertNewSurvey($aInsertData);
            if (!$iNewSurveyid) die('Survey could not be created.');

            // Prepare locale data for surveys_language_settings table
            $sTitle = $this->input->post('surveyls_title');
            $sDescription = $this->input->post('description');
            $sWelcome = $this->input->post('welcome');
            $sURLDescription = $this->input->post('urldescrip');
            if ($this->config->item('filterxsshtml'))
            {
                $sTitle=$this->security->xss_clean($sTitle);
                $sDescription=$this->security->xss_clean($sDescription);
                $sWelcome=$this->security->xss_clean($sWelcome);
                $sURLDescription=$this->security->xss_clean($sURLDescription);
            }
            $sTitle = html_entity_decode($sTitle, ENT_QUOTES, "UTF-8");
            $sDescription = html_entity_decode($sDescription, ENT_QUOTES, "UTF-8");
            $sWelcome = html_entity_decode($sWelcome, ENT_QUOTES, "UTF-8");
            $sURLDescription = html_entity_decode($sURLDescription, ENT_QUOTES, "UTF-8");

            // Fix bug with FCKEditor saving strange BR types
            $sTitle=fix_FCKeditor_text($sTitle);
            $sDescription=fix_FCKeditor_text($sDescription);
            $sWelcome=fix_FCKeditor_text($sWelcome);

            // Load default email templates for the chosen language
            $oLanguage = new limesurvey_lang(array($this->input->post('language')));
            $aDefaultTexts=aTemplateDefaultTexts($oLanguage,'unescaped');
            unset($oLanguage);

            if ($this->input->post('htmlemail') && $this->input->post('htmlemail') == "Y")
            {
                $bIsHTMLEmail = true;
                $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$bIsHTMLEmail,'unescaped');
            }
            else
            {
                $bIsHTMLEmail = false;
            }

            // Insert base language into surveys_language_settings table

            $aInsertData=array( 'surveyls_survey_id'=>$iNewSurveyid,
            'surveyls_title'=>$sTitle,
            'surveyls_description'=>$sDescription,
            'surveyls_welcometext'=>$sWelcome,
            'surveyls_language'=>$this->input->post('language'),
            'surveyls_urldescription'=>$this->input->post('urldescrip'),
            'surveyls_endtext'=>$this->input->post('endtext'),
            'surveyls_url'=>$this->input->post('url'),
            'surveyls_email_invite_subj'=>$aDefaultTexts['invitation_subject'],
            'surveyls_email_invite'=>conditional_nl2br($aDefaultTexts['invitation'],$bIsHTMLEmail,'unescaped'),
            'surveyls_email_remind_subj'=>$aDefaultTexts['reminder_subject'],
            'surveyls_email_remind'=>conditional_nl2br($aDefaultTexts['reminder'],$bIsHTMLEmail,'unescaped'),
            'surveyls_email_confirm_subj'=>$aDefaultTexts['confirmation_subject'],
            'surveyls_email_confirm'=>conditional_nl2br($aDefaultTexts['confirmation'],$bIsHTMLEmail,'unescaped'),
            'surveyls_email_register_subj'=>$aDefaultTexts['registration_subject'],
            'surveyls_email_register'=>conditional_nl2br($aDefaultTexts['registration'],$bIsHTMLEmail,'unescaped'),
            'email_admin_notification_subj'=>$aDefaultTexts['admin_notification_subject'],
            'email_admin_notification'=>conditional_nl2br($aDefaultTexts['admin_notification'],$bIsHTMLEmail,'unescaped'),
            'email_admin_responses_subj'=>$aDefaultTexts['admin_detailed_notification_subject'],
            'email_admin_responses'=>$aDefaultTexts['admin_detailed_notification'],
            'surveyls_dateformat'=>(int) $this->input->post('dateformat'),
            'surveyls_numberformat'=>(int) $this->input->post('numberformat')
            );

            $this->load->model('surveys_languagesettings_model');
            $this->surveys_languagesettings_model->insertNewSurvey($aInsertData);
            $this->session->set_userdata('flashmessage',$this->limesurvey_lang->gT("Survey was successfully added."));

            // Update survey permissions
            $this->load->model('survey_permissions_model');
            $this->survey_permissions_model->giveAllSurveyPermissions($this->session->userdata('loginID'),$iNewSurveyid);
            redirect(site_url('admin/survey/view/'.$iNewSurveyid));
        }

    }
}
