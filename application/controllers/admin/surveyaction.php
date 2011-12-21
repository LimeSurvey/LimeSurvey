<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
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
class SurveyAction extends Survey_Common_Action {

	private $yii;
	private $controller;

    /**
	* Base function
	*
	* This functions receives the form data from listsurveys and executes
	* according mass actions, like survey deletions, etc.
    * Only superadmins are allowed to do this!
	*
    * @access public
    * @return void
    */
   public function run($sa = '', $surveyid = 0)
   {
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
        {
            die();
        }

		$this->yii = Yii::app();
		$this->controller = $this->getController();

   		if (empty($sa)) $sa = null;

		if ($sa == 'view')
			$this->route('view', array('surveyid', 'gid', 'qid'));
		elseif ($sa == 'newsurvey' || isset($_GET['newsurvey']))
			$this->route('newsurvey', array());
		elseif ($sa == 'view' || isset($_GET['view']))
			$this->view($_GET['view']);
		elseif ($sa == 'insert' || isset($_GET['insert']))
			$this->route('insert', array());
		elseif ($sa == 'importsurveyresources')
			$this->route('importsurveyresources', array());
		elseif ($sa == 'copy' || isset($_GET['copy']))
			$this->route('copy', array());
        elseif ($sa == 'activate')
            $this->route('activate', array('surveyid'));
		elseif ($sa == 'listsurveys')
			$this->route('listsurveys', array());
		elseif ($sa == 'ajaxgetusers')
			$this->route('ajaxgetusers', array());
		elseif ($sa == 'ajaxowneredit')
			$this->route('ajaxowneredit', array('newowner', 'surveyid'));
		elseif ($sa == 'deactivate')
			$this->route('deactivate', array('surveyid'));
		elseif ($sa == 'confirmdelete' || $sa == 'delete')
			$this->route('delete', array('surveyid', 'sa'));
		elseif ($sa == 'editlocalsettings' || isset($_GET['editlocalsettings']))
			$this->route('editlocalsettings', array('surveyid'));
		elseif ($sa == 'editsurveysettings')
			$this->route('editsurveysettings', array('surveyid'));
		elseif ($sa == 'getUrlParamsJSON')
			$this->route('getUrlParamsJSON', array('surveyid'));
		elseif ($sa == 'expire')
			$this->route('expire', array('surveyid'));
   		return;

		/* @todo Implement this */
		$clang = $this->getController()->lang;
		$actioncount = 0;
		$iSurveyId = 0;
		$message = $clang->gT('You did not choose any surveys.');
		if (Survey::model()->findByPk($iSurveyID) === null)
			return;
		switch ($sa){
			case 'expire':
				if ($this->_expireSurvey($iSurveyID)) $actioncount++;;
				$message=$clang->gT('%s survey(s) were successfully expired.');
				break;
			case 'archive':
				Yii::app()->session['surveyid'] = $iSurveyID;
				redirect('admin/export/sa/surveyarchives/surveyid/'.$iSurveyId);
				break;
		}
        $this->getController()->render('/admin/survey/listSurveys_view', array('clang' => $clang));
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
        Yii::app()->clientScript->registerScriptFile('/scripts/admin/surveysettings.js');
        Yii::app()->clientScript->registerScriptFile('/scripts/jquery/jqGrid/js/i18n/grid.locale-en.js');
        Yii::app()->clientScript->registerScriptFile('/scripts/jquery/jqGrid/js/jquery.jqGrid.min.js');
        Yii::app()->clientScript->registerCSSFile('/styles/admin/default/superfish.css');
        Yii::app()->clientScript->registerScriptFile('/scripts/jquery/jqGrid/css/ui.jqgrid.css');
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu();;
        Yii::app()->loadHelper('surveytranslator');
        $clang = Yii::app()->lang;

        $esrow = $this->_fetchSurveyInfo('newsurvey');
        $dateformatdetails=getDateFormatData(Yii::app()->session['dateformat']);
        Yii::app()->loadHelper('admin/htmleditor');

        $data = $this->_generalTabNewSurvey();
        $data['esrow']=$esrow;
        $data = array_merge($data,$this->_tabPresentationNavigation($esrow));
        $data = array_merge($data,$this->_tabPublicationAccess($esrow));
        $data = array_merge($data,$this->_tabNotificationDataManagement($esrow));
        $data = array_merge($data,$this->_tabTokens($esrow));
		$arrayed_data['data']=$data;
        $this->getController()->render('/admin/survey/newSurvey_view',$arrayed_data);
        $this->getController()->_loadEndScripts();
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
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

		$dbprefix = Yii::app()->db->tablePrefix;

        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'admin/surveysettings.js');
        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'jquery/jqGrid/js/i18n/grid.locale-en.js');
        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'jquery/jqGrid/js/jquery.jqGrid.min.js');
        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'jquery/jquery.json.min.js');
        $this->controller->_css_admin_includes(Yii::app()->getConfig('styleurl')."admin/default/superfish.css");
        $this->controller->_css_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css");
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu($surveyid);;
        self::_surveybar($surveyid);

        //Yii::app()->loadHelper('text');
        Yii::app()->loadHelper('surveytranslator');
        $clang = $this->getController()->lang;

        $esrow = array();
        $editsurvey = '';
        $esrow = self::_fetchSurveyInfo('editsurvey',$surveyid);
		//$esrow = $esrow[0]->tableSchema->columns;
        $data['esrow']=$esrow;

        $data = array_merge($data,$this->_generalTabEditSurvey($surveyid,$esrow));
        $data = array_merge($data,$this->_tabPresentationNavigation($esrow));
        $data = array_merge($data,$this->_tabPublicationAccess($esrow));
        $data = array_merge($data,$this->_tabNotificationDataManagement($esrow));
        $data = array_merge($data,$this->_tabTokens($esrow));
        $data = array_merge($data,$this->_tabPanelIntegration($esrow));
        $data = array_merge($data,$this->_tabResourceManagement($surveyid));

        //echo $editsurvey;
        //$this->load->model('questions_model');
        $oResult=Questions::model()->getQuestionsWithSubQuestions($surveyid,$esrow['language'],"({$dbprefix}questions.type = 'T'  OR  {$dbprefix}questions.type = 'Q'  OR  {$dbprefix}questions.type = 'T' OR {$dbprefix}questions.type = 'S')");
        $data['questions']=$oResult;
        $data['display'] = $editsurvey;
        $data['action'] = "editsurveysettings";
		$data['data'] = $data;
        $this->controller->render('/admin/survey/editSurvey_view',$data);
        $this->controller->_loadEndScripts();
        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }


    /**
    * survey::importsurveyresources()
    * Function responsible to import survey resources.
    * @return
    */
    function importsurveyresources()
    {
        $clang = $this->getController()->lang;
        $action = $_POST['action'];
        $surveyid = $_POST['sid'];
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,$action);

        if ($action == "importsurveyresources" && $surveyid)
        {
            $importsurveyresourcesoutput = "<div class='header ui-widget-header'>".$clang->gT("Import survey resources")."</div>\n";
            $importsurveyresourcesoutput .= "<div class='messagebox ui-corner-all'>";

            if (Yii::app()->getConfig('demoMode'))
            {
                $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importsurveyresourcesoutput .= $clang->gT("Demo Mode Only: Uploading file is disabled in this system.")."<br /><br />\n";
                $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->getController()->createUrl('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                $importsurveyresourcesoutput .= "</div>\n";
                echo($importsurveyresourcesoutput);
                return;
            }

            //require("classes/phpzip/phpzip.inc.php");
            $this->yii->loadLibrary('admin/Phpzip');
            $zipfile=$_FILES['the_file']['tmp_name'];
            $z = new PHPZip();

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir=self::_tempdir(Yii::app()->getConfig('tempdir'));
            $basedestdir = Yii::app()->getConfig('publicdir')."/upload/surveys";
            $destdir=$basedestdir."/$surveyid/";

            if (!is_writeable($basedestdir))
            {
                $importsurveyresourcesoutput .= "<div class=\"warningheader\">".$clang->gT("Error")."</div><br />\n";
                $importsurveyresourcesoutput .= sprintf ($clang->gT("Incorrect permissions in your %s folder."),$basedestdir)."<br /><br />\n";
                $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->getController()->createUrl('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                $importsurveyresourcesoutput .= "</div>\n";
                echo($importsurveyresourcesoutput);
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
                    $importsurveyresourcesoutput .= $clang->gT("This file is not a valid ZIP file $zipfile. Import failed.")."<br /><br />\n";
                    $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->getController()->createUrl('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                    $importsurveyresourcesoutput .= "</div>\n";
                    echo($importsurveyresourcesoutput);
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
                    $importsurveyresourcesoutput .= $clang->gT("This ZIP $zipfile contains no valid Resources files. Import failed.")."<br /><br />\n";
                    $importsurveyresourcesoutput .= $clang->gT("Remember that we do not support subdirectories in ZIP $zipfile's.")."<br /><br />\n";
                    $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->getController()->createUrl('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                    $importsurveyresourcesoutput .= "</div>\n";
                    echo($importsurveyresourcesoutput);
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
                $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->getController()->createUrl('admin/survey/editsurveysettings/'.$surveyid)."', '_top')\" />\n";
                $importsurveyresourcesoutput .= "</div>\n";
                show_error($importsurveyresourcesoutput);
                return;
            }
            $importsurveyresourcesoutput .= "<input type='submit' value='".$clang->gT("Back")."' onclick=\"window.open('".$this->getController()->createUrl('admin/survey/sa/editsurveysettings/surveyid/'.$surveyid)."', '_top')\" />\n";
            $importsurveyresourcesoutput .= "</div>\n";

            $data['display'] = $importsurveyresourcesoutput;
            $this->getController()->render('/survey_view',$data);
        }

        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

    }

    //---------------------
    // Comes from http://fr2.php.net/tempnam
    function _tempdir($dir, $prefix='', $mode=0700)
    {
        if (substr($dir, -1) != PATH_SEPARATOR) $dir .= PATH_SEPARATOR;

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

        Yii::app()->clientscript->registerScriptFile(Yii::app()->getConfig('generalscripts').'admin/surveysettings.js');
        Yii::app()->clientscript->registerScriptFile(Yii::app()->getConfig('generalscripts').'jquery/jqGrid/js/i18n/grid.locale-en.js');
        Yii::app()->clientscript->registerScriptFile(Yii::app()->getConfig('generalscripts').'jquery/jqGrid/js/jquery.jqGrid.min.js');
        Yii::app()->clientscript->registerScriptFile(Yii::app()->getConfig('generalscripts').'jquery/jquery.json.min.js');
        Yii::app()->clientscript->registerScriptFile(Yii::app()->getConfig('styleurl')."admin/default/superfish.css");
        Yii::app()->clientscript->registerScriptFile(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css");
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);;
        $this->getController()->_surveybar($surveyid);

        $data['errors'] = LimeExpressionManager::GetSyntaxErrors();

        Yii::app()->render('admin/survey/showSyntaxErrors_view',$data);
        $this->getController()->_loadEndScripts();
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
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

        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'admin/surveysettings.js');
        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'jquery/jqGrid/js/i18n/grid.locale-en.js');
        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'jquery/jqGrid/js/jquery.jqGrid.min.js');
        $this->controller->_js_admin_includes(Yii::app()->getConfig('generalscripts').'jquery/jquery.json.min.js');
        $this->controller->_css_admin_includes(Yii::app()->getConfig('styleurl')."admin/default/superfish.css");
        $this->controller->_css_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css");
        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid);

        LimeExpressionManager::ResetSyntaxErrorLog();

        $this->load->view('admin/survey/resetSyntaxErrorLog_view');
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
    * surveyaction::view()
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
            $css_admin_includes[] = $this->yii->getConfig('styleurl')."admin/default/superfish.css";
            $this->yii->setConfig("css_admin_includes", $css_admin_includes);

            $this->controller->_getAdminHeader();
            $this->controller->_showadminmenu($surveyid);
            self::_surveybar($surveyid,$gid);
            self::_surveysummary($surveyid,"viewquestion");
            self::_questiongroupbar($surveyid,$gid,$qid,"viewquestion");

            self::_questionbar($surveyid,$gid,$qid,"viewquestion");
            $this->controller->_loadEndScripts();

            $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));

        }
        else
        {
            //show till questiongroup menu bar.
            if (!is_null($gid))
            {
                $css_admin_includes[] = $this->yii->getConfig('styleurl')."admin/default/superfish.css";
                $this->yii->setConfig("css_admin_includes", $css_admin_includes);

                $this->controller->_getAdminHeader();
                $this->controller->_showadminmenu($surveyid);
                self::_surveybar($surveyid,$gid);
                self::_surveysummary($surveyid,"viewgroup");
                self::_questiongroupbar($surveyid,$gid,$qid,"viewgroup");

                $this->controller->_loadEndScripts();

                $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

            }
            else
            {
                //show till survey menu bar.
                if(bHasSurveyPermission($surveyid,'survey','read'))
                {
                    $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
                    $this->yii->setConfig("css_admin_includes", $css_admin_includes);

                    $this->controller->_getAdminHeader();
                    $this->controller->_showadminmenu($surveyid);
                    self::_surveybar($surveyid);
                    self::_surveysummary($surveyid);
                    $this->controller->_loadEndScripts();


                    $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));
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
    function deactivate($surveyid = null)
    {
        $surveyid = sanitize_int($surveyid);
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);

        //$postsid=returnglobal('sid');
        if (!empty($_POST['sid']))
        {
            $postsid = $_POST['sid'];
        }
        else
        {
            $postsid = $surveyid;
        }
        $clang = $this->getController()->lang;
        $date = date('YmdHis'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day

        if (!isset($_POST['ok']) || !$_POST['ok'])
        {
            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['date'] = $date;
            $data['dbprefix'] = Yii::app()->db->tablePrefix;
            $data['step1'] = true;
            $this->_surveybar($surveyid);
            $this->getController()->render('/admin/survey/deactivateSurvey_view',$data);
        }

        else
        {
            //See if there is a tokens table for this survey
            if (Yii::app()->db->schema->getTable("{{tokens_{$postsid}}}"))
            {
                if (Yii::app()->db->getDriverName() == 'postgre')
                {
	                $deactivateresult = Yii::app()->db->createCommand()->renameTable($toldtable.'_tid_seq',$tnewtable.'_tid_seq');
	                $setsequence="ALTER TABLE {{{$tnewtable}}} ALTER COLUMN tid SET DEFAULT nextval('{{{$tnewtable}}}_tid_seq'::regclass);";
	                $deactivateresult = Yii::app()->db->createCommand($setsequence)->query();
	                $setidx="ALTER INDEX {{{$toldtable}}}_idx RENAME TO {{{$tnewtable}}}_idx;";
	                $deactivateresult = Yii::app()->db->createCommand($setidx)->query();
                }
                $toldtable="{{tokens_{$postsid}}}";
                $tnewtable="{{old_tokens_{$postsid}_{$date}}}";
                $tdeactivateresult = Yii::app()->db->createCommand()->renameTable($toldtable, $tnewtable);
                $data['tnewtable'] = $tnewtable;
                $data['toldtable'] = $toldtable;
            }

            // IF there are any records in the saved_control table related to this survey, they have to be deleted
            $query = "DELETE FROM {{saved_control}} WHERE sid={$postsid}";
            $result = Yii::app()->db->createCommand($query)->query();
            $oldtable="{{survey_{$postsid}}}";
            $newtable="{{old_survey_{$postsid}_{$date}}}";

            //Update the auto_increment value from the table before renaming
            $new_autonumber_start=0;
            $query = "SELECT id FROM $oldtable ORDER BY id desc LIMIT 1";
            $result = Yii::app()->db->createCommand($query)->query();
            if ($result->getRowCount() > 0)
            {
                foreach ($result->readAll() as $row)
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

        	$survey = Survey::model()->findByAttributes($condn);
        	$survey->autonumber_start = $new_autonumber_start;
        	$survey->save();
            if (Yii::app()->db->getDrivername()=='postgre')
            {
	            $deactivateresult = Yii::app()->db->createCommand()->renameTable($oldtable.'_id_seq',$newtable.'_id_seq');
                $setsequence="ALTER TABLE $newtable ALTER COLUMN id SET DEFAULT nextval('{$newtable}_id_seq'::regclass);";
	            $deactivateresult = Yii::app()->db->createCommand($setsequence)->execute();
            }

            $deactivateresult = Yii::app()->db->createCommand()->renameTable($oldtable, $newtable);

            $insertdata = array('active' => 'N');
        	$survey->active = 'N';
        	$survey->save();

            $pquery = "SELECT savetimings FROM {{surveys}} WHERE sid={$postsid}";
            $presult=Yii::app()->db->createCommand($pquery)->query();
            $prow=$presult->read(); //fetch savetimings value
            if ($prow['savetimings'] == "Y")
            {
                $oldtable="{{survey_{$postsid}_timings}}";
                $newtable="{{old_survey_{$postsid}_timings_{$date}}}";

                $deactivateresult2 = Yii::app()->db->createCommand()->renameTable($oldtable, $newtable);
                $deactivateresult=($deactivateresult && $deactivateresult2);
            }

            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['newtable'] = $newtable;
            $this->_surveybar($surveyid);
            $this->getController()->render('/admin/survey/deactivateSurvey_view',$data);

        }

        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
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

        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->getConfig("css_admin_includes", $css_admin_includes);
        $data['aSurveysettings'] = getSurveyInfo($iSurveyID);
        if (!isset($data['aSurveysettings']['active']) || $data['aSurveysettings']['active']=='Y') die(); // Die if this is not possible

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($iSurveyID);
        //$this->getController()->_surveybar($iSurveyID);



        $qtypes=getqtypelist('','array');
        Yii::app()->loadHelper("admin/activate");
        //$_POST = $this->input->post();


        if (!isset($_POST['ok']) || !$_POST['ok'])
        {
            if (isset($_GET['fixnumbering']) && $_GET['fixnumbering'])
            {
                fixNumbering($_GET['fixnumbering']);
            }

            // Check consistency for groups and questions
            $failedgroupcheck = checkGroup($iSurveyID);
            $failedcheck = checkQuestions($iSurveyID, $iSurveyID, $qtypes);

            $data['clang'] = $this->getController()->lang;
            $data['surveyid'] =  $iSurveyID;
            $data['$failedcheck'] = $failedcheck;
            $data['failedgroupcheck'] = $failedgroupcheck;
            $data['aSurveysettings'] = getSurveyInfo($iSurveyID);

            $this->getController()->render("/admin/survey/activateSurvey_view",$data);
            //IF ANY OF THE CHECKS FAILED, PRESENT THIS SCREEN


        }
        else
        {
            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyID));
            if(!is_null($survey))
            {
                $survey->anonymized = $_POST['anonymized'];
                $survey->datestamp = $_POST['datestamp'];
                $survey->ipaddr = $_POST['ipaddr'];
                $survey->refurl = $_POST['refurl'];
                $survey->savetimings = $_POST['savetimings'];
                $survey->save();
            }

            $activateoutput = activateSurvey($iSurveyID);
            $displaydata['display'] = $activateoutput;
            $this->getController()->render('/survey_view',$displaydata);
        }

         $this->getController()->_loadEndScripts();


         $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

    }


    /**
    * survey::ajaxgetusers()
    * This get the userlist in surveylist screen.
    * @return void
    */
    function ajaxgetusers()
    {
        header('Content-type: application/json');

        $query = "SELECT users_name, uid FROM {{users}}";

        $result =Yii::app()->db->createCommand($query)->query();

        $aUsers = array();
        if($result->getRowCount() > 0) {
            foreach($result->readAll() as $rows)
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

        //if (isset($_REQUEST['newowner'])) {$intNewOwner=sanitize_int($_REQUEST['newowner']);}
        //if (isset($_REQUEST['survey_id'])) {$intSurveyId=sanitize_int($_REQUEST['survey_id']);}
        $intNewOwner = sanitize_int($newowner);
        $intSurveyId = sanitize_int($surveyid);
        $owner_id = Yii::app()->session['loginID'];

        header('Content-type: application/json');

        $query = "UPDATE {{surveys}} SET owner_id = $intNewOwner WHERE sid=$intSurveyId";
        if (bHasGlobalPermission("USER_RIGHT_SUPERADMIN"))
            $query .="";
        else
            $query .=" AND owner_id=$owner_id";

        $result = Yii::app()->db->createCommand($query)->execute();

        $query = "SELECT b.users_name FROM {{surveys}} as a"
        ." INNER JOIN  {{users}} as b ON a.owner_id = b.uid   WHERE sid=$intSurveyId AND owner_id=$intNewOwner;";
        $result = Yii::app()->db->createCommand($query)->query();
        $intRecordCount = $result->readAll();

        $aUsers = array(
        'record_count' => $intRecordCount,
        );

        if($result->getRowCount() > 0) {
            foreach($result->readAll() as $rows)
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
		$clang = Yii::app()->lang;
		Yii::app()->loadHelper('surveytranslator');


		$this->getController()->_js_admin_includes(Yii::app()->baseUrl.'/scripts/jquery/jquery.tablesorter.min.js');
		$this->getController()->_js_admin_includes(Yii::app()->baseUrl.'/scripts/admin/listsurvey.js');

		$query = " SELECT a.*, c.*, u.users_name FROM {{surveys}} as a "
			." INNER JOIN {{surveys_languagesettings}} as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) AND surveyls_survey_id=a.sid AND surveyls_language=a.language "
			." INNER JOIN {{users}} as u ON (u.uid=a.owner_id) ";

		if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
		{
			$query .= "WHERE a.sid in (select sid from {{survey_permissions}} WHERE uid=".Yii::app()->session['loginID']." AND permission='survey' AND read_p=1) ";
			$data['issuperadmin']=false;
		}
		else
		{
			$data['issuperadmin']=true;
		}
		$query .= " ORDER BY surveyls_title";
		$result = Yii::app()->db->createCommand($query)->query();

		if($result->getRowCount() > 0) {

			$dateformatdetails=getDateFormatData(Yii::app()->session['dateformat']);
			$listsurveys = "";
			$first_time = true;
			foreach ($result->readAll() as $rows)
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


				if (Yii::app()->db->schema->getTable('{{tokens_'.$rows['sid'] . '}}'))
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
					if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')))
					{
						$aSurveyEntry['statusText']=$clang->gT("Expired") ;
						$aSurveyEntry['status']='expired' ;
					}
					elseif ($rows['startdate']!='' && $rows['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')))
					{
						$aSurveyEntry['statusText']=$clang->gT("Not yet active") ;
						$aSurveyEntry['status']='notyetactive' ;
					}
					else {
						$aSurveyEntry['statusText']=$clang->gT("Active") ;
						$aSurveyEntry['status']='active' ;
					}

					// Complete Survey Responses - added by DLR
					$gnquery = "SELECT COUNT(id) AS countofid FROM {{survey_".$rows['sid']."}} WHERE submitdate IS NULL";
					$gnresult = Yii::app()->db->createCommand($gnquery)->query(); //Checked)

					foreach ($gnresult->readAll() as $gnrow)
					{
						$aSurveyEntry['partial_responses']=$gnrow['countofid'];
					}
					$gnquery = "SELECT COUNT(id) AS countofid FROM {{survey_".$rows['sid'] . "}}";;
					$gnresult = Yii::app()->db->createCommand($gnquery)->query(); //Checked
					foreach ($gnresult->readAll() as $gnrow)
					{
						$aSurveyEntry['responses']=$gnrow['countofid'];
					}

				}
				else
				{
					$aSurveyEntry['statusText'] = $clang->gT("Inactive") ;
					$aSurveyEntry['status']='inactive' ;
				}

				Yii::import('application.libraries.Date_Time_Converter', true);
				$datetimeobj = new Date_Time_Converter(array($rows['datecreated'], "Y-m-d H:i:s"));

				$aSurveyEntry['datecreated']=$datetimeobj->convert($dateformatdetails['phpdate']);

				if (in_array($rows['owner_id'],getuserlist('onlyuidarray')))
				{
					$aSurveyEntry['ownername']=$rows['users_name'] ;
				}
				else
				{
					$aSurveyEntry['ownername']="---";
				}

				$questionsCountQuery = "SELECT qid FROM {{questions}} WHERE sid={$rows['sid']} AND language='".$rows['language']."'"; //Getting a count of questions for this survey
				$questionsCountResult = Yii::app()->db->createCommand($questionsCountQuery)->query(); //($connect->Execute($questionsCountQuery); //Checked)
				$aSurveyEntry['questioncount'] = $questionsCountResult->getRowCount();


				$aSurveyEntry['viewurl'] = $this->getController()->createUrl("/admin/survey/sa/view/surveyid/".$rows['sid']);
				$aSurveyEntry['iSurveyID']=$rows['sid'];
				$aSurveyEntry['sSurveyTitle']=$rows['surveyls_title'];


				if ($rows['active']=="Y" && Yii::app()->db->schema->getTable("{{tokens_".$rows['sid'] . "}}"))
				{
					//get the number of tokens for each survey
					$tokencountquery = "SELECT COUNT(tid) AS countoftid FROM {{tokens_".$rows['sid'] . "}}";
					$tokencountresult = Yii::app()->db->createCommand($tokencountquery)->query(); //Checked)
					foreach ($tokencountresult->readAll() as $tokenrow)
					{
						$aSurveyEntry['tokencount'] = $tokenrow['countoftid'];
					}

					//get the number of COMLETED tokens for each survey
					$tokencompletedquery = "SELECT COUNT(tid) AS countoftid FROM {{tokens_".$rows['sid']."}} WHERE completed!='N'";
					$tokencompletedresult = Yii::app()->db->createCommand($tokencompletedquery)->query(); //Checked
					foreach ($tokencompletedresult->readAll() as $tokencompletedrow)
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
			$data['clang'] = $clang;
			$listsurveys ="<p><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
		}

		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu(false);;
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$this->getController()->render('/admin/survey/listSurveys_view',$data);
		$this->getController()->_loadEndScripts();
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
	}

    /**
    * survey::delete()
    * Function responsible to delete a survey.
    * @return
    */
    function delete($surveyid, $sa = 'confirmdelete')
    {
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
        $this->getController()->_getAdminHeader();
        $data['surveyid'] = $iSurveyId = $surveyid;
        if ($sa == 'confirmdelete')
        {
            $this->getController()->_showadminmenu($iSurveyId);
            self::_surveybar($iSurveyId);
        }

        if(bHasSurveyPermission($iSurveyId,'survey','delete'))
        {
            $data['clang'] = $this->getController()->lang;

            if ($sa == 'delete')
            {
				$data['issuperadmin'] = Yii::app()->session['USER_RIGHT_SUPERADMIN'] == true;
                self::_deleteSurvey($iSurveyId);
                $this->getController()->_showadminmenu(false);
            } elseif ($sa == 'confirmdelete') {
				$this->getController()->render('/admin/survey/deleteSurvey_view',$data);
			}
        }
        else {
            //include('access_denied.php');
            $finaldata['display'] = access_denied("editsurvey",$iSurveyId);
            $this->getController()->render('survey_view',$finaldata);
        }

        $this->getController()->_loadEndScripts();
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $data['clang']->gT("LimeSurvey online manual"));
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

        $clang = $this->controller->lang;

        $css_admin_includes[] = $this->yii->getConfig('styleurl')."/admin/default/superfish.css";
        $this->yii->setConfig("css_admin_includes", $css_admin_includes);
        $this->controller->_js_admin_includes($this->controller->createUrl('scripts/admin/surveysettings.js'));
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu($surveyid);;
        self::_surveybar($surveyid);

        if(bHasSurveyPermission($surveyid,'surveylocale','read'))
        {
        	$editsurvey = '';
            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($grplangs,$baselang);

            $this->yii->loadHelper("admin/htmleditor");

			PrepareEditorScript(true, $this->controller);

            $editsurvey .="<div class='header ui-widget-header'>".$clang->gT("Edit survey text elements")."</div>\n";
            $editsurvey .= "<form id='addnewsurvey' class='form30' name='addnewsurvey' action='".$this->controller->createUrl("admin/database/index/updatesurveylocalesettings")."' method='post'>\n"
            . '<div id="tabs">';
            $i = 0;
            foreach ($grplangs as $grouplang)
            {
                // this one is created to get the right default texts fo each language
                //$this->yii->loadLibrary('lang',array($grouplang));
                $this->yii->loadHelper('database');
                $this->yii->loadHelper('surveytranslator');
                $bplang = $this->controller->lang;//new lang($grouplang);
                $esquery = "SELECT * FROM ".$this->yii->db->tablePrefix."surveys_languagesettings WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
                $esresult = db_execute_assoc($esquery); //Checked
                $esrow = $esresult->read();

                $tab_title[$i] = getLanguageNameFromCode($esrow['surveyls_language'],false);

                if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid))
                    $tab_title[$i]  .= '('.$clang->gT("Base Language").')';

                $esrow = array_map('htmlspecialchars', $esrow);
                $data['clang'] = $clang;
                $data['esrow'] = $esrow;
                $data['surveyid'] = $surveyid;
                $data['action'] = "editsurveylocalesettings";

                $tab_content[$i] = $this->controller->render('/admin/survey/editLocalSettings_view',$data,true);


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
            $this->controller->render('/survey_view',$finaldata);
            //self::_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

        }
        else
        {
            //include("access_denied.php");
            $finaldata['display'] = access_denied("editsurvey",$surveyid);
            $this->controller->render('/survey_view',$finaldata);

        }
        $this->controller->_loadEndScripts();


        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

    }

    /**
    * survey::copy()
    * Function responsible to import/copy a survey based on $action.
    * @return
    */
    function copy()
    {
        $importsurvey = "";
        $action = $_POST['action'];
        @$surveyid = $_POST['sid'];

        if ($action == "importsurvey" || $action == "copysurvey")
        {
            if ( @$_POST['copysurveytranslinksfields'] == "on"  || @$_POST['translinksfields'] == "on")
            {
                $sTransLinks = true;
            }
            $clang = $this->getController()->lang;

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

                $the_full_file_path = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
                if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
                {
                    $aData['sErrorMessage'] = sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),Yii::app()->getConfig('tempdir'));
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
                $surveyid = sanitize_int($_POST['copysurveylist']);
                $exclude = array();

                if (get_magic_quotes_gpc()) {$sNewSurveyName = stripslashes($_POST['copysurveyname']);}
                else{
                    $sNewSurveyName=$_POST['copysurveyname'];
                }

                if (@$_POST['copysurveyexcludequotas'] == "on")
                {
                    $exclude['quotas'] = true;
                }
                if (@$_POST['copysurveyexcludeanswers'] == "on")
                {
                    $exclude['answers'] = true;
                }
                if (@$_POST['copysurveyresetconditions'] == "on")
                {
                    $exclude['conditions'] = true;
                }

                if (!$surveyid)
                {
                    $aData['sErrorMessage'] = $clang->gT("No survey ID has been provided. Cannot copy survey");
                    $aData['bFailed']=true;
                }

                Yii::app()->loadHelper('export');
                $copysurveydata = survey_getXMLData($surveyid,$exclude);
            }

            // Now, we have the survey : start importing
            //require_once('import_functions.php');
            Yii::app()->loadHelper('admin/import');
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
                    if ($this->pclzip->extract(PCLZIP_OPT_PATH, Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR, PCLZIP_OPT_BY_EREG, '/(lss|lsr|lsi|lst)$/')== 0) {
                        unset($this->pclzip);
                    }
                    // Step 1 - import the LSS file and activate the survey
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lss')
                        {
                            //Import the LSS file
                            $aImportResults=XMLImportSurvey(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],null, null, null,true);
                            // Activate the survey
                            Yii::app()->loadHelper("admin/activate");
                            $activateoutput = activateSurvey($aImportResults['newsid']);
                            unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                    // Step 2 - import the responses file
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lsr')
                        {
                            //Import the LSS file
                            $aResponseImportResults=XMLImportResponses(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],$aImportResults['newsid'],$aImportResults['FieldReMap']);
                            $aImportResults=array_merge($aResponseImportResults,$aImportResults);
                            unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                    // Step 3 - import the tokens file - if exists
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lst')
                        {
                            Yii::app()->loadHelper("admin/token");
                            if (createTokenTable($aImportResults['newsid'])) $aTokenCreateResults = array('tokentablecreated'=>true);
                            $aImportResults=array_merge($aTokenCreateResults,$aImportResults);
                            $aTokenImportResults = XMLImportTokens(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],$aImportResults['newsid']);
                            $aImportResults=array_merge($aTokenImportResults,$aImportResults);
                            unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                    // Step 4 - import the timings file - if exists
                    foreach ($aFiles as $aFile)
                    {
                        if (pathinfo($aFile['filename'], PATHINFO_EXTENSION)=='lsi' && tableExists("survey_{$aImportResults['newsid']}_timings"))
                        {
                            $aTimingsImportResults = XMLImportTimings(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'],$aImportResults['newsid'],$aImportResults['FieldReMap']);
                            $aImportResults=array_merge($aTimingsImportResults,$aImportResults);
                            unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                            break;
                        }
                    }
                }
                else
                {
                    $importerror = true;
                }
            }
            elseif ($action == 'copysurvey' && empty($importerror) || !$importerror)
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
            $aData['sLink']= $this->getController()->createUrl('admin/survey/view/'.$aImportResults['newsid']);
            $aData['aImportResults']=$aImportResults;
			$aData['clang']=$this->getController()->lang;
        }

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu();;
        $this->getController()->render('/admin/survey/importSurvey_view',$aData);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));

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
            $esrow['template']                 = Yii::app()->getConfig('defaulttemplate');
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
            //$this->load->model('surveys_model');
			$dbprefix = $this->yii->db->tablePrefix;
			$this->yii->loadHelper('database');
            $esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
            $esresult = db_execute_assoc($esquery)->read();//Survey::model()->getAllRecords($condition); //($esquery); //Checked)
            if ($esresult) {
                $esrow = $esresult;
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
        $clang = $this->getController()->lang;

        $condition = array('users_name' => Yii::app()->session['user']);
        $fieldstoselect = array('full_name', 'email');
        //Use the current user details for the default administrator name and email for this survey
        $owner = User::model()->getSomeRecords($fieldstoselect,$condition); //($query) or safe_die($connect->ErrorMsg());)
        //Degrade gracefully to $siteadmin details if anything is missing.
        if (empty($owner['full_name']))
            $owner['full_name'] = $siteadminname;
        if (empty($owner['email']))
            $owner['email'] = $siteadminemail;
        //Bounce setting by default to global if it set globally
        Yii::app()->loadHelper('globalsettings');
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
        $clang = $this->getController()->lang;
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
        $clang = $this->getController()->lang;
        global $showXquestions,$showgroupinfo,$showqnumcode;

        Yii::app()->loadHelper('globalsettings');

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
        $clang = $this->getController()->lang;
        $dateformatdetails=getDateFormatData(Yii::app()->session['dateformat']);
        $startdate='';
        if ($esrow['startdate']) {
            $items = array($esrow["startdate"] ,"Y-m-d H:i:s"); // $dateformatdetails['phpdate']
            $this->yii->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter($items) ; //new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
            $startdate=$datetimeobj->convert("d.m.Y H:i");//$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }

        $expires='';
        if ($esrow['expires']) {
            $items = array($esrow['expires'] ,"Y-m-d H:i:s");

            $this->yii->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter($items) ; //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
            $expires=$datetimeobj->convert("d.m.Y H:i");
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
        $clang = $this->getController()->lang;

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
        $clang = $this->getController()->lang;

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
        $clang = $this->getController()->lang;
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
        $clang = $this->getController()->lang;
        Yii::app()->session['flashmessage'] = $clang->gT("The survey was successfully expired by setting an expiration date in the survey settings.");
        $this->_expireSurvey($iSurveyID);
        $dExpirationdate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
        $dExpirationdate=date_shift($dExpirationdate, "Y-m-d H:i:s", '-1 day');
        Survey::model()->updateSurvey(array('expires'=>$dExpirationdate),
        'sid= \''.$iSurveyID.'\'');
        $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$iSurveyID));

    }

    /**
    * Expires a survey
    *
    * @param mixed $iSurveyID The survey ID
    * @return False if not successful
    */
    function _expireSurvey($iSurveyID)
    {
        $dExpirationdate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
        $dExpirationdate=date_shift($dExpirationdate, "Y-m-d H:i:s", '-1 day');
        return Survey::model()->updateSurvey(array('expires'=>$dExpirationdate), 'sid=\''.$iSurveyID.'\'');
    }



    function getUrlParamsJSON($iSurveyID)
    {
        $iSurveyID = (int)$iSurveyID;
        $data = new Object();
		$this->yii->loadHelper('database');
		$oResult = db_execute_assoc("select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {$this->yii->db->tablePrefix}survey_url_parameters up
                            left join {$this->yii->db->tablePrefix}questions q on q.qid=up.targetqid
                            left join {$this->yii->db->tablePrefix}questions sq on q.qid=up.targetqid
                            where up.sid={$iSurveyID}");
        $i=0;

        foreach ($oResult->readAll() as $oRow)
        {
            $data->rows[$i]['id']=$oRow['id'];
            $oRow['title']= $oRow['title'].': '.ellipsize(FlattenText($oRow['question'],false,true),43,.70);

            if ($oRow['sqquestion']!='')
            {
                echo (' - '.ellipsize(FlattenText($oRow['sqquestion'],false,true),30,.75));
            }
            unset($oRow['sqquestion']);
            unset($oRow['sqtitle']);
            unset($oRow['question']);

            $data->rows[$i]['cell']=array_values($oRow);
            $i++;
        }

        $data->page = 1;
        $data->records = $oResult->getRowCount();
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
		Survey::model()->deleteByPk($iSurveyID);
        rmdirr(Yii::app()->getConfig("uploaddir").'/surveys/'.$iSurveyID);
    }

    /**
    * Saves the new survey after the creation screen is submitted
    *
    * @param $iSurveyID  The survey id to be used for the new survey. If already taken a new random one will be used.
    */
    function insert($iSurveyID=null)
    {
        if (Yii::app()->session['USER_RIGHT_CREATE_SURVEY'])
        {
            $clang = $this->getController()->lang;
            // Check if survey title was set
            if (!$_POST['surveyls_title'])
            {
                Yii::app()->session['flashmessage']=$clang->gT("Survey could not be created because it did not have a title");
                redirect($this->getController()->createUrl('admin'));
                return;
            }

            // Check if template may be used
            $sTemplate = $_POST['template'];
            if(!$sTemplate || (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1 && Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights(Yii::app()->session['loginID'], $_POST['template'])))
            {
                $sTemplate = "default";
            }

            Yii::app()->loadHelper("surveytranslator");


            // If start date supplied convert it to the right format
            $aDateFormatData=getDateFormatData($_SESSION['dateformat']);
            $sStartDate = $_POST['startdate'];
            if (trim($sStartDate)!='')
            {
                $this->load->library('Date_Time_Converter',array($sStartDate , $aDateFormatData['phpdate'].' H:i:s'));
                $sStartDate=$this->date_time_converter->convert("Y-m-d H:i:s");
            }

            // If expiry date supplied convert it to the right format
            $sExpiryDate = $_POST['expires'];
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
            'owner_id'=>Yii::app()->session['loginID'],
            'admin'=>$_POST['admin'],
            'active'=>'N',
            'adminemail'=>$_POST['adminemail'],
            'bounce_email'=>$_POST['bounce_email'],
            'anonymized'=>$_POST['anonymized'],
            'faxto'=>$_POST['faxto'],
            'format'=>$_POST['format'],
            'savetimings'=>$_POST['savetimings'],
            'language'=>$_POST['language'],
            'datestamp'=>$_POST['datestamp'],
            'ipaddr'=>$_POST['ipaddr'],
            'refurl'=>$_POST['refurl'],
            'usecookie'=>$_POST['usecookie'],
            'emailnotificationto'=>$_POST['emailnotificationto'],
            'allowregister'=>$_POST['allowregister'],
            'allowsave'=>$_POST['allowsave'],
            'navigationdelay'=>$_POST['navigationdelay'],
            'autoredirect'=>$_POST['autoredirect'],
            'showXquestions'=>$_POST['showXquestions'],
            'showgroupinfo'=>$_POST['showgroupinfo'],
            'showqnumcode'=>$_POST['showqnumcode'],
            'shownoanswer'=>$_POST['shownoanswer'],
            'showwelcome'=>$_POST['showwelcome'],
            'allowprev'=>$_POST['allowprev'],
            'allowjumps'=>$_POST['allowjumps'],
            'nokeyboard'=>$_POST['nokeyboard'],
            'showprogress'=>$_POST['showprogress'],
            'printanswers'=>$_POST['printanswers'],
            'listpublic'=>$_POST['public'],
            'htmlemail'=>$_POST['htmlemail'],
            'sendconfirmation'=>$_POST['sendconfirmation'],
            'tokenanswerspersistence'=>$_POST['tokenanswerspersistence'],
            'alloweditaftercompletion'=>$_POST['alloweditaftercompletion'],
            'usecaptcha'=>$_POST['usecaptcha'],
            'publicstatistics'=>$_POST['publicstatistics'],
            'publicgraphs'=>$_POST['publicgraphs'],
            'assessments'=>$_POST['assessments'],
            'emailresponseto'=>$_POST['emailresponseto'],
            'tokenlength'=>$_POST['tokenlength']
            );
            if (!is_null($iSurveyID))
            {
                $aInsertData['wishSID']=$iSurveyID;
            }

            $iNewSurveyid=Survey::model()->insertNewSurvey($aInsertData);
            if (!$iNewSurveyid) die('Survey could not be created.');

            // Prepare locale data for surveys_language_settings table
            $sTitle = $_POST['surveyls_title'];
            $sDescription = $_POST['description'];
            $sWelcome = $_POST['welcome'];
            $sURLDescription = $_POST['urldescrip'];
            if (Yii::app()->getConfig('filterxsshtml'))
            {
				//$p = new CHtmlPurifier();
				//$p->options = array('URI.AllowedSchemes'=>array('http' => true,  'https' => true));
                //$sTitle=$p->purify($sTitle);
                //$sDescription=$p->purify($sDescription);
                //$sWelcome=$p->purify($sWelcome);
                //$sURLDescription=$p->purify($sURLDescription);
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
            $oLanguage = new Limesurvey_lang(array($_POST['language']));
            $aDefaultTexts=aTemplateDefaultTexts($oLanguage,'unescaped');
            unset($oLanguage);

            if ($_POST['htmlemail'] && $_POST['htmlemail'] == "Y")
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
            'surveyls_language'=>$_POST['language'],
            'surveyls_urldescription'=>$_POST['urldescrip'],
            'surveyls_endtext'=>$_POST['endtext'],
            'surveyls_url'=>$_POST['url'],
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
            'surveyls_dateformat'=>(int) $_POST['dateformat'],
            'surveyls_numberformat'=>(int) $_POST['numberformat']
            );
			$langsettings = new Surveys_languagesettings;
            $langsettings->insertNewSurvey($aInsertData);
            Yii::app()->session['flashmessage']=$this->getController()->lang->gT("Survey was successfully added.");

            // Update survey permissions
            Survey_permissions::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'],$iNewSurveyid);
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$iNewSurveyid));
        }

    }
}
