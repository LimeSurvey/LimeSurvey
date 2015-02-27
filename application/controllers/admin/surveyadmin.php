<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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
*/

/**
* survey
*
* @package LimeSurvey
* @author  The LimeSurvey Project team
* @copyright 2011
* @version $Id: surveyaction.php 12301 2012-02-02 08:51:43Z c_schmitz $
* @access public
*/
class SurveyAdmin extends Survey_Common_Action
{
    /**
    * Initiates the survey action, checks for superadmin permission
    *
    * @access public
    * @param CController $controller
    * @param string $id
    * @return void
    */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);
    }

    /**
    * Loads list of surveys and it's few quick properties.
    *
    * @access public
    * @return void
    */
    public function index()
    {
        App()->getClientScript()->registerPackage('jqgrid');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "listsurvey.js");
        if (count(getSurveyList(true)) == 0)
        {
            $this->_renderWrappedTemplate('super', 'firststeps');
        } else {
            Yii::app()->loadHelper('surveytranslator');

            $aData['issuperadmin'] = false;
            if (Permission::model()->hasGlobalPermission('superadmin','read'))
            {
                $aData['issuperadmin'] = true;
            }

            $this->_renderWrappedTemplate('survey', 'listSurveys_view', $aData);
        }
    }

    public function regenquestioncodes($iSurveyID, $sSubAction )
    {
        $clang = $this->getController()->lang;
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update'))
        {
            Yii::app()->setFlashMessage($clang->gT("You do not have sufficient rights to access this page."),'error');
            $this->getController()->redirect(array('admin/survey','sa'=>'view','surveyid'=>$iSurveyID));
        }
        $oSurvey=Survey::model()->findByPk($iSurveyID);
        if ($oSurvey->active=='Y')
        {
            Yii::app()->setFlashMessage($clang->gT("You can't update question code for an active survey."),'error');
            $this->getController()->redirect(array('admin/survey','sa'=>'view','surveyid'=>$iSurveyID));
        }
        //Automatically renumbers the "question codes" so that they follow
        //a methodical numbering method
        $iQuestionNumber=1;
        $iGroupNumber=0;
        $iGroupSequence=0;
        $oQuestions=Question::model()->with('groups')->findAll(array('select'=>'t.qid,t.gid','condition'=>"t.sid=:sid and t.language=:language and parent_qid=0",'order'=>'groups.group_order, question_order','params'=>array(':sid'=>$iSurveyID,':language'=>$oSurvey->language)));
        foreach($oQuestions as $oQuestion)
        {
            if ($sSubAction == 'bygroup' && $iGroupNumber != $oQuestion->gid)
            { //If we're doing this by group, restart the numbering when the group number changes
                $iQuestionNumber=1;
                $iGroupNumber = $oQuestion->gid;
                $iGroupSequence++;
            }
            $sNewTitle=(($sSubAction == 'bygroup') ? ('G' . $iGroupSequence ) : '')."Q".str_pad($iQuestionNumber, 5, "0", STR_PAD_LEFT);
            Question::model()->updateAll(array('title'=>$sNewTitle),'qid=:qid',array(':qid'=>$oQuestion->qid));
            $iQuestionNumber++;
            $iGroupNumber=$oQuestion->gid;
        }

        Yii::app()->setFlashMessage($clang->gT("Question codes were successfully regenerated."));
        LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $iSurveyID));
    }


    /**
    * This function prepares the view for a new survey
    *
    */
    function newsurvey()
    {
        App()->getClientScript()->registerPackage('jqgrid');
        if (!Permission::model()->hasGlobalPermission('surveys','create'))
            $this->getController()->error('No permission');

        $this->_registerScriptFiles();
        Yii::app()->loadHelper('surveytranslator');

        $esrow = $this->_fetchSurveyInfo('newsurvey');
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        Yii::app()->loadHelper('admin/htmleditor');
        $aViewUrls['output'] = PrepareEditorScript(false, $this->getController());

        $aData = $this->_generalTabNewSurvey();
        $aData['esrow'] = $esrow;
        $aData = array_merge($aData, $this->_tabPresentationNavigation($esrow));
        $aData = array_merge($aData, $this->_tabPublicationAccess($esrow));
        $aData = array_merge($aData, $this->_tabNotificationDataManagement($esrow));
        $aData = array_merge($aData, $this->_tabTokens($esrow));
        $arrayed_data['data'] = $aData;
        $aViewUrls[] = 'newSurvey_view';

        $this->_renderWrappedTemplate('survey', $aViewUrls, $arrayed_data);
    }

    function fakebrowser()
    {
        $aData['clang'] = $this->getController()->lang;
        Yii::app()->getController()->renderPartial('/admin/survey/newSurveyBrowserMessage', $aData);
    }

    /**
    * This function prepares the view for editing a survey
    *
    */
    function editsurveysettings($iSurveyID)
    {
        $iSurveyID = (int) $iSurveyID;
        if (is_null($iSurveyID) || !$iSurveyID)
            $this->getController()->error('Invalid survey id');

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read') && !Permission::model()->hasGlobalPermission('surveys','read'))
            $this->getController()->error('No permission');

        $this->_registerScriptFiles();

        //Yii::app()->loadHelper('text');
        Yii::app()->loadHelper('surveytranslator');
        $clang = $this->getController()->lang;

        Yii::app()->session['FileManagerContext'] = "edit:survey:{$iSurveyID}";

        Yii::app()->loadHelper('/admin/htmleditor');
        initKcfinder();

        $esrow = array();
        $esrow = self::_fetchSurveyInfo('editsurvey', $iSurveyID);
        $aData['esrow'] = $esrow;

        $aData = array_merge($aData, $this->_generalTabEditSurvey($iSurveyID, $esrow));
        $aData = array_merge($aData, $this->_tabPresentationNavigation($esrow));
        $aData = array_merge($aData, $this->_tabPublicationAccess($esrow));
        $aData = array_merge($aData, $this->_tabNotificationDataManagement($esrow));
        $aData = array_merge($aData, $this->_tabTokens($esrow));
        $aData = array_merge($aData, $this->_tabPanelIntegration($esrow));
        $aData = array_merge($aData, $this->_tabResourceManagement($iSurveyID));

        $oResult = Question::model()->getQuestionsWithSubQuestions($iSurveyID, $esrow['language'], "({{questions}}.type = 'T'  OR  {{questions}}.type = 'Q'  OR  {{questions}}.type = 'T' OR {{questions}}.type = 'S')");

        $aData['questions'] = $oResult;
        $aData['display']['menu_bars']['surveysummary'] = "editsurveysettings";
        $tempData = $aData;
        $aData['data'] = $tempData;

        $this->_renderWrappedTemplate('survey', 'editSurvey_view', $aData);
    }

    /**
    * Function responsible to import survey resources from a '.zip' file.
    *
    * @access public
    * @return void
    */
    function importsurveyresources()
    {
        $clang = $this->getController()->lang;
        $iSurveyID = Yii::app()->request->getPost('surveyid');

        if (!empty($iSurveyID))
        {
            $aData['display']['menu_bars']['surveysummary'] = 'importsurveyresources';

            if (Yii::app()->getConfig('demoMode'))
                $this->getController()->error($clang->gT("Demo mode only: Uploading files is disabled in this system."), $this->getController()->createUrl("admin/survey/sa/view/surveyid/{$iSurveyID}"));

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir = $this->_tempdir(Yii::app()->getConfig('tempdir'));
            $zipfilename = $_FILES['the_file']['tmp_name'];
            $basedestdir = Yii::app()->getConfig('uploaddir') . "/surveys";
            $destdir = $basedestdir . "/$iSurveyID/";

            Yii::app()->loadLibrary('admin.pclzip');
            $zip = new PclZip($zipfilename);

            if (!is_writeable($basedestdir))
                $this->getController()->error(sprintf($clang->gT("Incorrect permissions in your %s folder."), $basedestdir), $this->getController()->createUrl("admin/survey/sa/view/surveyid/{$iSurveyID}"));

            if (!is_dir($destdir))
                mkdir($destdir);

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();

            if (is_file($zipfilename))
            {
                if ($zip->extract($extractdir) <= 0)
                    $this->getController()->error($clang->gT("This file is not a valid ZIP file archive. Import failed. " . $zip->errorInfo(true)), $this->getController()->createUrl("admin/survey/sa/view/surveyid/{$iSurveyID}"));

                // now read tempdir and copy authorized files only
                $folders = array('flash', 'files', 'images');
                foreach ($folders as $folder)
                {
                    list($_aImportedFilesInfo, $_aErrorFilesInfo) = $this->_filterImportedResources($extractdir . "/" . $folder, $destdir . $folder);
                    $aImportedFilesInfo = array_merge($aImportedFilesInfo, $_aImportedFilesInfo);
                    $aErrorFilesInfo = array_merge($aErrorFilesInfo, $_aErrorFilesInfo);
                }

                // Deletes the temp directory
                rmdirr($extractdir);

                // Delete the temporary file
                unlink($zipfilename);

                if (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
                    $this->getController()->error($clang->gT("This ZIP archive contains no valid Resources files. Import failed."), $this->getController()->createUrl("admin/survey/sa/view/surveyid/{$iSurveyID}"));
            }
            else
                $this->getController()->error(sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir), $this->getController()->createUrl("admin/survey/sa/view/surveyid/{$iSurveyID}"));

            $aData = array(
            'aErrorFilesInfo' => $aErrorFilesInfo,
            'aImportedFilesInfo' => $aImportedFilesInfo,
            'surveyid' => $iSurveyID
            );

            $this->_renderWrappedTemplate('survey', 'importSurveyResources_view', $aData);
        }
    }

    /**
    * Load complete view of survey properties and actions specified by $iSurveyID
    *
    * @access public
    * @param mixed $iSurveyID
    * @param mixed $gid
    * @param mixed $qid
    * @return void
    */
    public function view($iSurveyID, $gid = null, $qid = null)
    {
        $iSurveyID = sanitize_int($iSurveyID);
        if (isset($gid))
            $gid = sanitize_int($gid);
        if (isset($qid))
            $qid = sanitize_int($qid);

        // Reinit LEMlang and LEMsid: ensure LEMlang are set to default lang, surveyid are set to this survey id
        // Ensure Last GetLastPrettyPrintExpression get info from this sid and default lang
        LimeExpressionManager::SetEMLanguage(Survey::model()->findByPk($iSurveyID)->language);
        LimeExpressionManager::SetSurveyId($iSurveyID);
        LimeExpressionManager::StartProcessingPage(false,true);

        $aData['surveyid'] = $iSurveyID;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['display']['menu_bars']['surveysummary'] = true;

        $this->_renderWrappedTemplate('survey', array(), $aData);
    }

    /**
    * Function responsible to deactivate a survey.
    *
    * @access public
    * @param int $iSurveyID
    * @return void
    */
    public function deactivate($iSurveyID = null)
    {
        $iSurveyID = Yii::app()->request->getPost('sid', $iSurveyID);
        $iSurveyID = sanitize_int($iSurveyID);
        $clang = $this->getController()->lang;
        $date = date('YmdHis'); //'His' adds 24hours+minutes to name to allow multiple deactiviations in a day

        if (empty($_POST['ok']))
        {
            if (!tableExists('survey_'.$iSurveyID))
            {
                $_SESSION['flashmessage'] = $clang->gT("Error: Response table does not exist. Survey cannot be deactivated.");
                $this->getController()->redirect($this->getController()->createUrl("admin/survey/sa/view/surveyid/{$iSurveyID}"));
            }
            $aData['surveyid'] = $iSurveyID;
            $aData['date'] = $date;
            $aData['dbprefix'] = Yii::app()->db->tablePrefix;
            $aData['step1'] = true;
        }
        else
        {
            //See if there is a tokens table for this survey
            if (tableExists("{{tokens_{$iSurveyID}}}"))
            {
                $toldtable = Yii::app()->db->tablePrefix."tokens_{$iSurveyID}";
                $tnewtable = Yii::app()->db->tablePrefix."old_tokens_{$iSurveyID}_{$date}";
                if (Yii::app()->db->getDriverName() == 'pgsql')
                {
                    $tidDefault = Yii::app()->db->createCommand("SELECT pg_attrdef.adsrc FROM pg_attribute JOIN pg_class ON (pg_attribute.attrelid=pg_class.oid) JOIN pg_attrdef ON(pg_attribute.attrelid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum) WHERE pg_class.relname='$toldtable' and pg_attribute.attname='tid'")->queryScalar();
                    if(preg_match("/nextval\('(tokens_\d+_tid_seq\d*)'::regclass\)/", $tidDefault, $matches)){
                        $oldSeq = $matches[1];
                        $deactivateresult = Yii::app()->db->createCommand()->renameTable($oldSeq, $tnewtable . '_tid_seq');
                        $setsequence = "ALTER TABLE ".Yii::app()->db->quoteTableName($toldtable)." ALTER COLUMN tid SET DEFAULT nextval('{$tnewtable}_tid_seq'::regclass);";
                        $deactivateresult = Yii::app()->db->createCommand($setsequence)->query();
                    }
                }

                $tdeactivateresult = Yii::app()->db->createCommand()->renameTable($toldtable, $tnewtable);

                $aData['tnewtable'] = $tnewtable;
                $aData['toldtable'] = $toldtable;
            }

            //Remove any survey_links to the CPDB
            SurveyLink::model()->deleteLinksBySurvey($iSurveyID);


            // IF there are any records in the saved_control table related to this survey, they have to be deleted
            $result = SavedControl::model()->deleteSomeRecords(array('sid' => $iSurveyID)); //Yii::app()->db->createCommand($query)->query();
            $sOldSurveyTableName = Yii::app()->db->tablePrefix."survey_{$iSurveyID}";
            $sNewSurveyTableName = Yii::app()->db->tablePrefix."old_survey_{$iSurveyID}_{$date}";
            $aData['sNewSurveyTableName']=$sNewSurveyTableName;
            //Update the autonumber_start in the survey properties
            $new_autonumber_start = 0;
            $query = "SELECT id FROM ".Yii::app()->db->quoteTableName($sOldSurveyTableName)." ORDER BY id desc";
            $sLastID = Yii::app()->db->createCommand($query)->limit(1)->queryScalar();
            $new_autonumber_start = $sLastID + 1;
            $insertdata = array('autonumber_start' => $new_autonumber_start);
            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyID));
            $survey->autonumber_start = $new_autonumber_start;
            $survey->save();
            if (Yii::app()->db->getDriverName() == 'pgsql')
            {
                $idDefault = Yii::app()->db->createCommand("SELECT pg_attrdef.adsrc FROM pg_attribute JOIN pg_class ON (pg_attribute.attrelid=pg_class.oid) JOIN pg_attrdef ON(pg_attribute.attrelid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum) WHERE pg_class.relname='$sOldSurveyTableName' and pg_attribute.attname='id'")->queryScalar();
                if(preg_match("/nextval\('(survey_\d+_id_seq\d*)'::regclass\)/", $idDefault, $matches)){
                    $oldSeq = $matches[1];
                    $deactivateresult = Yii::app()->db->createCommand()->renameTable($oldSeq, $sNewSurveyTableName . '_id_seq');
                    $setsequence = "ALTER TABLE ".Yii::app()->db->quoteTableName($sOldSurveyTableName)." ALTER COLUMN id SET DEFAULT nextval('{{{$sNewSurveyTableName}}}_id_seq'::regclass);";
                    $deactivateresult = Yii::app()->db->createCommand($setsequence)->query();
                }
            }

            $deactivateresult = Yii::app()->db->createCommand()->renameTable($sOldSurveyTableName, $sNewSurveyTableName);

            $insertdata = array('active' => 'N');
            $survey->active = 'N';
            $survey->save();

            $prow = Survey::model()->find('sid = :sid', array(':sid' => $iSurveyID));
            if ($prow->savetimings == "Y")
            {
                $sOldTimingsTableName = Yii::app()->db->tablePrefix."survey_{$iSurveyID}_timings";
                $sNewTimingsTableName = Yii::app()->db->tablePrefix."old_survey_{$iSurveyID}_timings_{$date}";

                $deactivateresult2 = Yii::app()->db->createCommand()->renameTable($sOldTimingsTableName, $sNewTimingsTableName);
                $deactivateresult = ($deactivateresult && $deactivateresult2);
                $aData['sNewTimingsTableName'] = $sNewTimingsTableName;
            }

            $aData['surveyid'] = $iSurveyID;
            Yii::app()->db->schema->refresh();
        }

        $this->_renderWrappedTemplate('survey', 'deactivateSurvey_view', $aData);
    }

    /**
    * Function responsible to activate survey.
    *
    * @access public
    * @param int $iSurveyID
    * @return void
    */
    public function activate($iSurveyID)
    {
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update')) die();
        $clang = Yii::app()->lang;

        $iSurveyID = (int) $iSurveyID;

        $aData = array();
        $aData['aSurveysettings'] = getSurveyInfo($iSurveyID);
        $aData['surveyid'] = $iSurveyID;

        // Die if this is not possible
        if (!isset($aData['aSurveysettings']['active']) || $aData['aSurveysettings']['active'] == 'Y')
            $this->getController()->error('Survey not active');

        $qtypes = getQuestionTypeList('', 'array');
        Yii::app()->loadHelper("admin/activate");

        if (empty($_POST['ok']))
        {
            if (isset($_GET['fixnumbering']) && $_GET['fixnumbering'])
            {
                fixNumbering($_GET['fixnumbering'], $iSurveyID);
            }

            // Check consistency for groups and questions
            $failedgroupcheck = checkGroup($iSurveyID);
            $failedcheck = checkQuestions($iSurveyID, $iSurveyID, $qtypes);

            $aData['failedcheck'] = $failedcheck;
            $aData['failedgroupcheck'] = $failedgroupcheck;
            $aData['aSurveysettings'] = getSurveyInfo($iSurveyID);

            $this->_renderWrappedTemplate('survey', 'activateSurvey_view', $aData);
        }
        else
        {
            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyID));
            if (!is_null($survey))
            {
                $survey->anonymized = Yii::app()->request->getPost('anonymized');
                $survey->datestamp = Yii::app()->request->getPost('datestamp');
                $survey->ipaddr = Yii::app()->request->getPost('ipaddr');
                $survey->refurl = Yii::app()->request->getPost('refurl');
                $survey->savetimings = Yii::app()->request->getPost('savetimings');
                $survey->save();
                Survey::model()->resetCache();  // Make sure the saved values will be picked up
            }

            $aResult=activateSurvey($iSurveyID);
            if (isset($aResult['error']))
            {
                $aViewUrls['output']= "<br />\n<div class='messagebox ui-corner-all'>\n" .
                "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($iSurveyID)</div>\n";
                if ($aResult['error']=='surveytablecreation')
                {
                    $aViewUrls['output'].="<div class='warningheader'>".$clang->gT("Survey table could not be created.")."</div>\n";
                }
                else
                {
                    $aViewUrls['output'].="<div class='warningheader'>".$clang->gT("Timings table could not be created.")."</div>\n";
                }
                $aViewUrls['output'].="<p>" .
                $clang->gT("Database error!!")."\n <font color='red'>" ."</font>\n" .
                "<pre>".var_export ($aResult['error'],true)."</pre>\n
                <a href='".Yii::app()->getController()->createUrl("admin/survey/sa/view/surveyid/".$iSurveyID)."'>".$clang->gT("Main Admin Screen")."</a>\n</div>" ;
            }
            else
            {
                $aViewUrls['output']= "<br />\n<div class='messagebox ui-corner-all'>\n"
                ."<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ({$iSurveyID})</div>\n"
                ."<div class='successheader'>".$clang->gT("Survey has been activated. Results table has been successfully created.")."</div><br /><br />\n";

                if (isset($aResult['warning']))
                {
                    $aViewUrls['output'] .= "<div class='warningheader'>"
                    .$clang->gT("The required directory for saving the uploaded files couldn't be created. Please check file premissions on the /upload/surveys directory.")
                    ."</div>";
                }

                if ($survey->allowregister=='Y')
                {
                    $aViewUrls['output'] .= $clang->gT("This survey allows public registration. A token table must also be created.")."<br /><br />\n"
                    ."<input type='submit' value='".$clang->gT("Initialise tokens")."' onclick=\"".convertGETtoPOST(Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/".$iSurveyID))."\" />\n";
                }
                else
                {
                    $aViewUrls['output'] .= $clang->gT("This survey is now active, and responses can be recorded.")."<br /><br />\n"
                    ."<strong>".$clang->gT("Open-access mode").":</strong> ".$clang->gT("No invitation code is needed to complete the survey.")."<br />".$clang->gT("You can switch to the closed-access mode by initialising a token table with the button below.")."<br /><br />\n"
                    ."<input type='submit' value='".$clang->gT("Switch to closed-access mode")."' onclick=\"".convertGETtoPOST(Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/".$iSurveyID))."\" />\n"
                    ."<input type='submit' value='".$clang->gT("No, thanks.")."' onclick=\"".convertGETtoPOST(Yii::app()->getController()->createUrl("admin/survey/sa/view/surveyid/".$iSurveyID))."\" />\n";
                }
                $aViewUrls['output'] .= "</div><br />&nbsp;\n";
            }


            $this->_renderWrappedTemplate('survey', $aViewUrls, $aData);
        }
    }

    /**
    * This get the userlist in surveylist screen.
    *
    * @access public
    * @return void
    */
    public function ajaxgetusers()
    {
        header('Content-type: application/json');
        $result = getUserList();
        $aUsers = array();
        if (count($result) > 0)
        {
            foreach ($result as $rows)
                $aUsers[] = array($rows['uid'], $rows['user']);
        }
        $ajaxoutput = ls_json_encode($aUsers) . "\n";
        echo $ajaxoutput;
    }

    /**
    * This function change the owner of a survey.
    *
    * @access public
    * @param int $newowner
    * @param int $iSurveyID
    * @return void
    */
    public function ajaxowneredit()
    {
        header('Content-type: application/json');
        $intNewOwner = sanitize_int(Yii::app()->request->getPost("newowner"));
        $intSurveyId = sanitize_int(Yii::app()->request->getPost("surveyid"));
        $owner_id = Yii::app()->session['loginID'];
        $query_condition = 'sid=:sid';
        $params[':sid']=$intSurveyId;
        if (!Permission::model()->hasGlobalPermission('superadmin','create'))
        {
            $query_condition .= ' AND owner_id=:uid';
            $params[':uid']=$owner_id;
        }

        $result = Survey::model()->updateAll(array('owner_id'=>$intNewOwner), $query_condition, $params);

        $result = Survey::model()->with('owner')->findAllByAttributes(array('sid' => $intSurveyId, 'owner_id' => $intNewOwner));

        $intRecordCount = count($result);

        $aUsers = array(
            'record_count' => $intRecordCount,
        );

        foreach ($result as $row)
            $aUsers['newowner'] = $row->owner->users_name;

        $ajaxoutput = ls_json_encode($aUsers) . "\n";

        echo $ajaxoutput;
    }

    /**
    * Returns surveys in json format
    *
    * @access public
    * @return void
    */
    public function getSurveys_json()
    {
        $this->getController()->loadHelper('surveytranslator');
        $clang = $this->getController()->lang;
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        $oSurvey = new Survey;
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
            $oSurvey->permission(Yii::app()->user->getId());

        $aSurveys = $oSurvey->with(array('languagesettings'=>array('condition'=>'surveyls_language=language'), 'owner'))->findAll();
        $aSurveyEntries = new stdClass();
        $aSurveyEntries->page = 1;
        foreach ($aSurveys as $rows)
        {
            if (!isset($rows->owner->attributes)) $aOwner=array('users_name'=>$clang->gT('(None)')); else $aOwner=$rows->owner->attributes;
            $rows = array_merge($rows->attributes, $rows->defaultlanguage->attributes, $aOwner);
            $aSurveyEntry = array();
            // Set status
            if ($rows['active'] == "Y" && $rows['expires'] != '' && $rows['expires'] < dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')))
            {
                $aSurveyEntry[] = '<!--a--><img src="' . Yii::app()->getConfig('adminimageurl') . 'expired.png" alt="' . $clang->gT("This survey is active but expired.") . '" />';
            }
            elseif ($rows['active'] == "Y" && $rows['startdate'] != '' && $rows['startdate'] > dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')))
            {
                $aSurveyEntry[] = '<!--b--><img src="' . Yii::app()->getConfig('adminimageurl') . 'notyetstarted.png" alt="' . $clang->gT("This survey is active but has a start date.") . '" />';
            }
            elseif ($rows['active'] == "Y")
            {
                if (Permission::model()->hasSurveyPermission($rows['sid'], 'surveyactivation', 'update'))
                {
                    $aSurveyEntry[] = '<!--c--><a href="' . $this->getController()->createUrl('admin/survey/sa/deactivate/surveyid/' . $rows['sid']) . '"><img src="' . Yii::app()->getConfig('adminimageurl') . 'active.png" alt="' . $clang->gT("This survey is active - click here to stop this survey.") . '"/></a>';
                }
                else
                {
                    $aSurveyEntry[] = '<!--d--><img src="' . Yii::app()->getConfig('adminimageurl') . 'active.png" alt="' . $clang->gT("This survey is currently active.") . '" />';
                }
            }
            else
            {
                $condition = "sid={$rows['sid']} AND language='" . $rows['language'] . "'";
                $questionsCountResult = Question::model()->count($condition);

                if ($questionsCountResult>0 && Permission::model()->hasSurveyPermission($rows['sid'], 'surveyactivation', 'update'))
                {
                    $aSurveyEntry[] = '<!--e--><a href="' . $this->getController()->createUrl('admin/survey/sa/activate/surveyid/' . $rows['sid']) . '"><img src="' . Yii::app()->getConfig('adminimageurl') . 'inactive.png" title="" alt="' . $clang->gT("This survey is currently not active - click here to activate this survey.") . '" /></a>';
                }
                else
                {
                    $aSurveyEntry[] = '<!--f--><img src="' . Yii::app()->getConfig('adminimageurl') . 'inactive.png" title="' . $clang->gT("This survey is currently not active.") . '" alt="' . $clang->gT("This survey is currently not active.") . '" />';
                }
            }

            //Set SID
            $aSurveyEntry[] = $rows['sid'];
            '<a href="' . $this->getController()->createUrl("/admin/survey/sa/view/surveyid/" . $rows['sid']) . '">' . $rows['sid'] . '</a>';

            //Set Title
            $aSurveyEntry[] = '<a href="' . $this->getController()->createUrl("/admin/survey/sa/view/surveyid/" . $rows['sid']) . '">' . CHtml::encode($rows['surveyls_title'])  . '</a>';

            //Set Date
            Yii::import('application.libraries.Date_Time_Converter', true);
            $datetimeobj = new Date_Time_Converter($rows['datecreated'], "Y-m-d H:i:s");
            $aSurveyEntry[] = '<!--' . $rows['datecreated'] . '-->' . $datetimeobj->convert($dateformatdetails['phpdate']);

            //Set Owner
            if(Permission::model()->hasGlobalPermission('superadmin','read') || Yii::app()->session['loginID']==$rows['owner_id'])
            {
                $aSurveyEntry[] = $rows['users_name'] . ' (<a class="ownername_edit" translate_to="' . $clang->gT('Edit') . '" id="ownername_edit_' . $rows['sid'] . '">'. $clang->gT('Edit') .'</a>)';
            }
            else
            {
                $aSurveyEntry[] = $rows['users_name'];
            }
            //Set Access
            if (tableExists('tokens_' . $rows['sid'] ))
            {
                $aSurveyEntry[] = $clang->gT("Closed");
            }
            else
            {
                $aSurveyEntry[] = $clang->gT("Open");
            }

            //Set Anonymous
            if ($rows['anonymized'] == "Y")
            {
                $aSurveyEntry[] = $clang->gT("Yes");
            }
            else
            {
                $aSurveyEntry[] = $clang->gT("No");
            }

            //Set Responses
            if ($rows['active'] == "Y")
            {
                $cntResult = SurveyDynamic::countAllAndPartial($rows['sid']);
                $all = $cntResult['cntall'];
                $partial = $cntResult['cntpartial'];

                $aSurveyEntry[] = $all - $partial;
                $aSurveyEntry[] = $partial;
                $aSurveyEntry[] = $all;


                $aSurveyEntry['viewurl'] = $this->getController()->createUrl("/admin/survey/sa/view/surveyid/" . $rows['sid']);
                if (tableExists('tokens_' . $rows['sid'] ))
                {
                    $summary = Token::model($rows['sid'])->summary();
                    $tokens = $summary['count'];
                    $tokenscompleted = $summary['completed'];

                    $aSurveyEntry[] = $tokens;
                    $aSurveyEntry[] = ($tokens == 0) ? 0 : round($tokenscompleted / $tokens * 100, 1);
                }
                else
                {
                    $aSurveyEntry[] = $aSurveyEntry[] = '';
                }
            }
            else
            {
                $aSurveyEntry[] = $aSurveyEntry[] = $aSurveyEntry[] = $aSurveyEntry[] = $aSurveyEntry[] = '';
            }
            $aSurveyEntries->rows[] = array('id' => $rows['sid'], 'cell' => $aSurveyEntry);
        }
        header('Content-type: application/json');
        echo ls_json_encode($aSurveyEntries);
    }

    /**
    * Function responsible to delete a survey.
    *
    * @access public
    * @param int $iSurveyID
    * @param string $sa
    * @return void
    */
    public function delete($iSurveyID)
    {
        $aData = $aViewUrls = array();
        $aData['surveyid'] = $iSurveyID = (int) $iSurveyID;
        $clang = $this->getController()->lang;

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete'))
        {
            if (Yii::app()->request->getPost("delete") == 'yes')
            {
                $aData['issuperadmin'] = Permission::model()->hasGlobalPermission('superadmin','read');
                $this->_deleteSurvey($iSurveyID);
                Yii::app()->session['flashmessage'] = $clang->gT("Survey deleted.");
                $this->getController()->redirect(array("admin/index"));
            }
            else
            {
                $aViewUrls[] = 'deleteSurvey_view';
            }
        }
        else
            $this->getController()->error('Access denied');

        $this->_renderWrappedTemplate('survey', $aViewUrls, $aData);
    }

    /**
    * Takes the edit call from the detailed survey view, which either deletes the survey information
    */
    function editSurvey_json()
    {
        $operation = Yii::app()->request->getPost('oper');
        $iSurveyIDs = Yii::app()->request->getPost('id');
        if ($operation == 'del') // If operation is delete , it will delete, otherwise edit it
        {
            foreach(explode(',',$iSurveyIDs) as $iSurveyID)
            {
                if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete'))
                {
                    $this->_deleteSurvey($iSurveyID);
                }
            }
        }
    }
    /**
    * Load editing of local settings of a survey screen.
    *
    * @access public
    * @param int $iSurveyID
    * @return void
    */
    public function editlocalsettings($iSurveyID)
    {
        $clang = $this->getController()->lang;
        $aData['surveyid'] = $iSurveyID = sanitize_int($iSurveyID);
        $aViewUrls = array();

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read'))
        {
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'update'))
            {
                Yii::app()->session['FileManagerContext'] = "edit:survey:{$iSurveyID}";
            }
            $grplangs = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $baselang = Survey::model()->findByPk($iSurveyID)->language;
            array_unshift($grplangs, $baselang);

            Yii::app()->loadHelper("admin/htmleditor");

            $aViewUrls['output'] = PrepareEditorScript(false, $this->getController());

            foreach ($grplangs as $sLang)
            {
                // this one is created to get the right default texts fo each language
                Yii::app()->loadHelper('database');
                Yii::app()->loadHelper('surveytranslator');
                $bplang = $this->getController()->lang; //new lang($grouplang);

                $esrow = SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLang))->getAttributes();

                $aTabTitles[$sLang] = getLanguageNameFromCode($esrow['surveyls_language'], false);

                if ($esrow['surveyls_language'] == Survey::model()->findByPk($iSurveyID)->language)
                    $aTabTitles[$sLang] .= '(' . $clang->gT("Base language") . ')';

                $esrow = array_map('htmlspecialchars', $esrow);
                $aData['esrow'] = $esrow;
                $aData['action'] = "editsurveylocalesettings";
                $aData['clang'] = $clang;

                $aTabContents[$sLang] = $this->getController()->renderPartial('/admin/survey/editLocalSettings_view', $aData, true);
            }


            $aData['has_permissions'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'update');
            $aData['surveyls_language'] = $esrow["surveyls_language"];
            $aData['aTabContents'] = $aTabContents;
            $aData['aTabTitles'] = $aTabTitles;

            $aViewUrls[] = 'editLocalSettings_main_view';
        }
        else
            $this->getController()->error('Access denied');

        $this->_renderWrappedTemplate('survey', $aViewUrls, $aData);
    }

    /**
    * Function responsible to import/copy a survey based on $action.
    *
    * @access public
    * @return void
    */
    public function copy()
    {
        $importsurvey = "";
        $action = Yii::app()->request->getParam('action');
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('sid'));

        if ($action == "importsurvey" || $action == "copysurvey")
        {
            $clang = $this->getController()->lang;

            // Start the HTML
            if ($action == 'importsurvey')
            {
                $aData['sHeader'] = $clang->gT("Import survey data");
                $aData['sSummaryHeader'] = $clang->gT("Survey structure import summary");
                $importingfrom = "http";
                $aPathInfo = pathinfo($_FILES['the_file']['name']);
                if (isset($aPathInfo['extension']))
                {
                    $sExtension = $aPathInfo['extension'];
                }
                else
                {
                    $sExtension = "";
                }

            }
            elseif ($action == 'copysurvey')
            {
                $aData['sHeader'] = $clang->gT("Copy survey");
                $aData['sSummaryHeader'] = $clang->gT("Survey copy summary");
            }
            // Start traitment and messagebox
            $aData['bFailed'] = false; // Put a var for continue

            if ($action == 'importsurvey')
            {

                $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20).'.'.$sExtension;
                if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
                {
                    $aData['sErrorMessage'] = sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), Yii::app()->getConfig('tempdir'));
                    $aData['bFailed'] = true;
                }
                if (!$aData['bFailed'] && (strtolower($sExtension) != 'csv' && strtolower($sExtension) != 'lss' && strtolower($sExtension) != 'txt' && strtolower($sExtension) != 'lsa'))
                {
                    $aData['sErrorMessage'] = sprintf($clang->gT("Import failed. You specified an invalid file type '%s'."), $sExtension);
                    $aData['bFailed'] = true;
                }
            }
            elseif ($action == 'copysurvey')
            {
                $iSurveyID = sanitize_int(Yii::app()->request->getParam('copysurveylist'));
                $aExcludes = array();

                $sNewSurveyName = Yii::app()->request->getPost('copysurveyname');

                if (Yii::app()->request->getPost('copysurveyexcludequotas') == "on")
                {
                    $aExcludes['quotas'] = true;
                }
                if (Yii::app()->request->getPost('copysurveyexcludepermissions') == "on")
                {
                    $aExcludes['permissions'] = true;
                }
                if (Yii::app()->request->getPost('copysurveyexcludeanswers') == "on")
                {
                    $aExcludes['answers'] = true;
                }
                if (Yii::app()->request->getPost('copysurveyresetconditions') == "on")
                {
                    $aExcludes['conditions'] = true;
                }
                if (Yii::app()->request->getPost('copysurveyresetstartenddate') == "on")
                {
                    $aExcludes['dates'] = true;
                }
                if (!$iSurveyID)
                {
                    $aData['sErrorMessage'] = $clang->gT("No survey ID has been provided. Cannot copy survey");
                    $aData['bFailed'] = true;
                }
                elseif(!Survey::model()->findByPk($iSurveyID))
                {
                    $aData['sErrorMessage'] = $clang->gT("Invalid survey ID");
                    $aData['bFailed'] = true;
                }
                elseif (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export') && !Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export'))
                {
                    $aData['sErrorMessage'] = $clang->gT("You don't have sufficient permissions.");
                    $aData['bFailed'] = true;
                }
                else
                {
                    Yii::app()->loadHelper('export');
                    $copysurveydata = surveyGetXMLData($iSurveyID, $aExcludes);
                }
            }

            // Now, we have the survey : start importing
            Yii::app()->loadHelper('admin/import');

            if ($action == 'importsurvey' && !$aData['bFailed'])
            {
                $aImportResults=importSurveyFile($sFullFilepath,(isset($_POST['translinksfields'])));
                if (is_null($aImportResults) || !empty($aImportResults['error']) )
                {
                    $aData['sErrorMessage']=$aImportResults['error'];
                    $aData['bFailed'] = true;
                }
            }
            elseif ($action == 'copysurvey' && !$aData['bFailed'])
            {
                $aImportResults = XMLImportSurvey('', $copysurveydata, $sNewSurveyName, sanitize_int(App()->request->getParam('copysurveyid')) ,(isset($_POST['translinksfields'])));
                if (isset($aExcludes['conditions']))
                {
                    Question::model()->updateAll(array('relevance'=>'1'),'sid='.$aImportResults['newsid']);
                    QuestionGroup::model()->updateAll(array('grelevance'=>'1'),'sid='.$aImportResults['newsid']);
                }
                if (!isset($aExcludes['permissions']))
                {
                    Permission::model()->copySurveyPermissions($iSurveyID,$aImportResults['newsid']);
                }
            }
            else
            {
                $aData['bFailed'] = true;
            }
            if ($action == 'importsurvey' && isset($sFullFilepath))
            {
                unlink($sFullFilepath);
            }

            if (!$aData['bFailed'])
            {
                $aData['action'] = $action;
                $aData['sLink'] = $this->getController()->createUrl('admin/survey/sa/view/surveyid/' . $aImportResults['newsid']);
                $aData['aImportResults'] = $aImportResults;
            }
        }

        $this->_renderWrappedTemplate('survey', 'importSurvey_view', $aData);
    }

    /**
    * questiongroup::organize()
    * Load ordering of question group screen.
    * @return
    */
    public function organize($iSurveyID)
    {
        $iSurveyID = (int)$iSurveyID;

        if (!empty($_POST['orgdata']) && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update'))
        {
            $this->_reorderGroup($iSurveyID);
        }
        else
        {
            $this->_showReorderForm($iSurveyID);
        }
    }

    private function _showReorderForm($iSurveyID)
    {
        // Prepare data for the view
        $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        LimeExpressionManager::StartSurvey($iSurveyID, 'survey');
        LimeExpressionManager::StartProcessingPage(true, Yii::app()->baseUrl);

        $aGrouplist = QuestionGroup::model()->getGroups($iSurveyID);
        $initializedReplacementFields = false;

        foreach ($aGrouplist as $iGID => $aGroup)
        {
            LimeExpressionManager::StartProcessingGroup($aGroup['gid'], false, $iSurveyID);
            if (!$initializedReplacementFields) {
                templatereplace("{SITENAME}"); // Hack to ensure the EM sets values of LimeReplacementFields
                $initializedReplacementFields = true;
            }

            $oQuestionData = Question::model()->getQuestions($iSurveyID, $aGroup['gid'], $sBaseLanguage);

            $qs = array();
            $junk = array();

            foreach ($oQuestionData->readAll() as $q)
            {
                $relevance = ($q['relevance'] == '') ? 1 : $q['relevance'];
                $question = '[{' . $relevance . '}] ' . $q['question'];
                LimeExpressionManager::ProcessString($question, $q['qid']);
                $q['question'] = LimeExpressionManager::GetLastPrettyPrintExpression();
                $q['gid'] = $aGroup['gid'];
                $qs[] = $q;
            }
            $aGrouplist[$iGID]['questions'] = $qs;
            LimeExpressionManager::FinishProcessingGroup();
        }
        LimeExpressionManager::FinishProcessingPage();

        $aData['aGroupsAndQuestions'] = $aGrouplist;
        $aData['surveyid'] = $iSurveyID;

        $this->_renderWrappedTemplate('survey', 'organizeGroupsAndQuestions_view', $aData);
    }

    private function _reorderGroup($iSurveyID)
    {
        $AOrgData = array();
        parse_str($_POST['orgdata'], $AOrgData);
        $grouporder = 0;
        foreach ($AOrgData['list'] as $ID => $parent)
        {
            if ($parent == 'root' && $ID[0] == 'g') {
                QuestionGroup::model()->updateAll(array('group_order' => $grouporder), 'gid=:gid', array(':gid' => (int)substr($ID, 1)));
                $grouporder++;
            }
            elseif ($ID[0] == 'q')
            {
                if (!isset($questionorder[(int)substr($parent, 1)]))
                    $questionorder[(int)substr($parent, 1)] = 0;

                Question::model()->updateAll(array('question_order' => $questionorder[(int)substr($parent, 1)], 'gid' => (int)substr($parent, 1)), 'qid=:qid', array(':qid' => (int)substr($ID, 1)));

                Question::model()->updateAll(array('gid' => (int)substr($parent, 1)), 'parent_qid=:parent_qid', array(':parent_qid' => (int)substr($ID, 1)));

                $questionorder[(int)substr($parent, 1)]++;
            }
        }
        LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("The new question group/question order was successfully saved.");
        $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $iSurveyID));
    }

    /**
    * survey::_fetchSurveyInfo()
    * Load survey information based on $action.
    * @param mixed $action
    * @param mixed $iSurveyID
    * @return
    */
    private function _fetchSurveyInfo($action, $iSurveyID=null)
    {
        if (isset($iSurveyID))
            $iSurveyID = sanitize_int($iSurveyID);

        if ($action == 'newsurvey')
        {
            $esrow['active'] = 'N';
            $esrow['questionindex'] = 0;
            $esrow['format'] = 'G'; //Group-by-group mode
            $esrow['template'] = Yii::app()->getConfig('defaulttemplate');
            $esrow['allowsave'] = 'Y';
            $esrow['allowprev'] = 'N';
            $esrow['nokeyboard'] = 'N';
            $esrow['printanswers'] = 'N';
            $esrow['publicstatistics'] = 'N';
            $esrow['publicgraphs'] = 'N';
            $esrow['listpublic'] = 'N';
            $esrow['autoredirect'] = 'N';
            $esrow['tokenlength'] = 15;
            $esrow['allowregister'] = 'N';
            $esrow['usecookie'] = 'N';
            $esrow['usecaptcha'] = 'D';
            $esrow['htmlemail'] = 'Y';
            $esrow['sendconfirmation'] = 'Y';
            $esrow['emailnotificationto'] = '';
            $esrow['anonymized'] = 'N';
            $esrow['datestamp'] = 'N';
            $esrow['ipaddr'] = 'N';
            $esrow['refurl'] = 'N';
            $esrow['tokenanswerspersistence'] = 'N';
            $esrow['alloweditaftercompletion'] = 'N';
            $esrow['startdate'] = '';
            $esrow['savetimings'] = 'N';
            $esrow['expires'] = '';
            $esrow['showqnumcode'] = 'X';
            $esrow['showwelcome'] = 'Y';
            $esrow['emailresponseto'] = '';
            $esrow['assessments'] = 'N';
            $esrow['navigationdelay'] = 0;
            $esrow['googleanalyticsapikey']    = '';
            $esrow['googleanalyticsstyle']     = '0';
        }
        elseif ($action == 'editsurvey')
        {
            $condition = array('sid' => $iSurveyID);
            $esresult = Survey::model()->find('sid = :sid', array(':sid' => $iSurveyID));
            if ($esresult)
            {
                // Set template to default if not exist
                if (!$esresult['template'])
                {
                    $esresult['template']=Yii::app()->getConfig('defaulttemplate');
                }
                $esresult['template']=validateTemplateDir($esresult['template']);

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
    private function _generalTabNewSurvey()
    {
        $clang = $this->getController()->lang;

        //Use the current user details for the default administrator name and email for this survey
        $user=User::model()->findByPk(Yii::app()->session['loginID']);
        $owner =$user->attributes;

        //Degrade gracefully to $siteadmin details if anything is missing.
        if (empty($owner['full_name']))
            $owner['full_name'] = getGlobalSetting('siteadminname');
        if (empty($owner['email']))
            $owner['email'] = getGlobalSetting('siteadminemail');

        //Bounce setting by default to global if it set globally
        if (getGlobalSetting('bounceaccounttype') != 'off')
        {
            $owner['bounce_email'] = getGlobalSetting('siteadminbounce');
        }
        else
        {
            $owner['bounce_email'] = $owner['email'];
        }

        $aData['action'] = "newsurvey";
        $aData['clang'] = $clang;
        $aData['owner'] = $owner;
        $aLanguageDetails= getLanguageDetails(Yii::app()->session['adminlang']);
        $aData['sRadixDefault'] = $aLanguageDetails['radixpoint'];
        $aData['sDateFormatDefault'] = $aLanguageDetails['dateformat'];
        foreach (getRadixPointData() as $index=>$radixptdata){
          $aRadixPointData[$index]=$radixptdata['desc'];
        }
        $aData['aRadixPointData']=$aRadixPointData;

        foreach (getDateFormatData (0,Yii::app()->session['adminlang']) as $index => $dateformatdata)
        {
          $aDateFormatData[$index]=$dateformatdata['dateformat'];
        }
        $aData['aDateFormatData']=$aDateFormatData;

        return $aData;
    }

    /**
    * survey::_generalTabEditSurvey()
    * Load "General" tab of edit survey screen.
    * @param mixed $iSurveyID
    * @param mixed $esrow
    * @return
    */
    private function _generalTabEditSurvey($iSurveyID, $esrow)
    {
        $clang = $this->getController()->lang;
        $aData['action'] = "editsurveysettings";
        $aData['clang'] = $clang;
        $aData['esrow'] = $esrow;
        $aData['surveyid'] = $iSurveyID;

        $beforeSurveySettings = new PluginEvent('beforeSurveySettings');
        $beforeSurveySettings->set('survey', $iSurveyID);
        App()->getPluginManager()->dispatchEvent($beforeSurveySettings);
        $aData['pluginSettings'] = $beforeSurveySettings->get('surveysettings');
        return $aData;
    }

    /**
    * survey::_tabPresentationNavigation()
    * Load "Presentation & navigation" tab.
    * @param mixed $esrow
    * @return
    */
    private function _tabPresentationNavigation($esrow)
    {
        $clang = $this->getController()->lang;
        global $showxquestions, $showgroupinfo, $showqnumcode;

        Yii::app()->loadHelper('globalsettings');

        $shownoanswer = getGlobalSetting('shownoanswer') ? getGlobalSetting('shownoanswer') : 'Y';

        $aData['clang'] = $clang;
        $aData['esrow'] = $esrow;
        $aData['shownoanswer'] = $shownoanswer;
        $aData['showxquestions'] = $showxquestions;
        $aData['showgroupinfo'] = $showgroupinfo;
        $aData['showqnumcode'] = $showqnumcode;
        return $aData;
    }

    /**
    * survey::_tabPublicationAccess()
    * Load "Publication * access control" tab.
    * @param mixed $esrow
    * @return
    */
    private function _tabPublicationAccess($esrow)
    {
        $clang = $this->getController()->lang;
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        $startdate = '';
        if ($esrow['startdate'])
        {
            Yii::app()->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter($esrow["startdate"],"Y-m-d H:i:s"); //new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
            $startdate = $datetimeobj->convert("d.m.Y H:i"); //$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }

        $expires = '';
        if ($esrow['expires'])
        {
            Yii::app()->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter($esrow['expires'], "Y-m-d H:i:s"); //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
            $expires = $datetimeobj->convert("d.m.Y H:i");
        }
        $aData['clang'] = $clang;
        $aData['esrow'] = $esrow;
        $aData['startdate'] = $startdate;
        $aData['expires'] = $expires;
        return $aData;
    }

    /**
    * survey::_tabNotificationDataManagement()
    * Load "Notification & data management" tab.
    * @param mixed $esrow
    * @return
    */
    private function _tabNotificationDataManagement($esrow)
    {
        $clang = $this->getController()->lang;

        $aData['clang'] = $clang;
        $aData['esrow'] = $esrow;

        return $aData;
    }

    /**
    * survey::_tabTokens()
    * Load "Tokens" tab.
    * @param mixed $esrow
    * @return
    */
    private function _tabTokens($esrow)
    {
        $clang = $this->getController()->lang;

        $aData['clang'] = $clang;
        $aData['esrow'] = $esrow;

        return $aData;
    }

    private function _tabPanelIntegration($esrow)
    {
        $aData = array();
        return $aData;
    }

    /**
    * survey::_tabImport()
    * Load "Import" tab.
    * @param mixed $iSurveyID
    * @return
    */
    private function _tabImport()
    {
        $aData = array();
        return $aData;
    }

    /**
    * survey::_tabCopy()
    * Load "Copy" tab.
    * @param mixed $iSurveyID
    * @return
    */
    private function _tabCopy()
    {
        $aData = array();
        return $aData;
    }

    /**
    * survey::_tabResourceManagement()
    * Load "Resources" tab.
    * @param mixed $iSurveyID
    * @return
    */
    private function _tabResourceManagement($iSurveyID)
    {
        $clang = $this->getController()->lang;

        global $sCKEditorURL;

        // TAB Uploaded Resources Management
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"" . $clang->gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'";
        if (!function_exists("zip_open"))
        {
            $ZIPimportAction = " onclick='alert(\"" . $clang->gT("zip library not supported by PHP, Import ZIP Disabled", "js") . "\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($iSurveyID, 'survey') === false)
        {
            $disabledIfNoResources = " disabled='disabled'";
        }
        $aData['clang'] = $clang;
        //$aData['esrow'] = $esrow;
        $aData['ZIPimportAction'] = $ZIPimportAction;
        $aData['disabledIfNoResources'] = $disabledIfNoResources;
        $dqata['sCKEditorURL'] = $sCKEditorURL;

        return $aData;
    }

    function expire($iSurveyID)
    {
        $iSurveyID = (int) $iSurveyID;
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
        {
            die();
        }
        $clang = $this->getController()->lang;
        Yii::app()->session['flashmessage'] = $clang->gT("The survey was successfully expired by setting an expiration date in the survey settings.");
        Survey::model()->expire($iSurveyID);
        $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $iSurveyID));
    }

    function getUrlParamsJSON($iSurveyID)
    {
        $iSurveyID = (int) $iSurveyID;
        $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        $sQuery = "select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {{survey_url_parameters}} up
        left join {{questions}} q on q.qid=up.targetqid
        left join {{questions}} sq on sq.qid=up.targetsqid
        where up.sid={$iSurveyID} and q.language='{$sBaseLanguage}' and (sq.language='{$sBaseLanguage}' or sq.language is null)";
        $oResult = Yii::app()->db->createCommand($sQuery)->queryAll();
        $i = 0;
        $clang = $this->getController()->lang;
        $aData = new stdClass();
        foreach ($oResult as $oRow)
        {
            $aData->rows[$i]['id'] = $oRow['id'];
            if (!is_null($oRow['question']))
            {
                        $oRow['title'] .= ': ' . ellipsize(flattenText($oRow['question'], false, true), 43, .70);
            }
            else
            {
                        $oRow['title'] = $clang->gT('(No target question)');
            }

            if ($oRow['sqquestion'] != '')
            {
                $oRow['title'] .= (' - ' . ellipsize(flattenText($oRow['sqquestion'], false, true), 30, .75));
            }
            unset($oRow['sqquestion']);
            unset($oRow['sqtitle']);
            unset($oRow['question']);

            $aData->rows[$i]['cell'] = array_values($oRow);
            $i++;
        }

        $aData->page = 1;
        $aData->records = count($oResult);
        $aData->total = 1;

        echo ls_json_encode($aData);
    }

    /**
    * This private function deletes a survey
    * Important: If you change this function also change the remotecontrol XMLRPC function
    *
    * @param mixed $iSurveyID  The survey ID to delete
    */
    private function _deleteSurvey($iSurveyID)
    {
        Survey::model()->deleteSurvey($iSurveyID);
        rmdirr(Yii::app()->getConfig('uploaddir') . '/surveys/' . $iSurveyID);
    }

    /**
    * Executes registerScriptFile for all needed script/style files
    *
    * @param array $files
    * @return void
    */
    private function _registerScriptFiles($files = array())
    {
        if (empty($files))
        {
            $generalscripts_path = Yii::app()->getConfig('generalscripts');
            $adminscripts_path = Yii::app()->getConfig('adminscripts');
            $styleurl = Yii::app()->getConfig('styleurl');

            $js_files = array(
                $adminscripts_path . 'surveysettings.js',
            );

            $css_files = array(
            );
        }

        foreach ($js_files as $file)
        {
            App()->getClientScript()->registerScriptFile($file);


        }
        App()->getClientScript()->registerPackage('jquery-json');
        App()->getClientScript()->registerPackage('jqgrid');

        foreach ($css_files as $file)
        {
            App()->getClientScript()->registerCss($file);
        }
    }

    /**
    * Saves the new survey after the creation screen is submitted
    *
    * @param $iSurveyID  The survey id to be used for the new survey. If already taken a new random one will be used.
    */
    function insert($iSurveyID=null)
    {
        if (Permission::model()->hasGlobalPermission('surveys','create'))
        {
            // Check if survey title was set
            if (!$_POST['surveyls_title'])
            {
                Yii::app()->session['flashmessage'] = $clang->gT("Survey could not be created because it did not have a title");
                redirect($this->getController()->createUrl('admin'));
                return;
            }

            // Check if template may be used
            $sTemplate = $_POST['template'];
            if (!$sTemplate || (!Permission::model()->hasGlobalPermission('superadmin','read') && !Permission::model()->hasGlobalPermission('templates','read') && !hasTemplateManageRights(Yii::app()->session['loginID'], $_POST['template'])))
            {
                $sTemplate = "default";
            }

            Yii::app()->loadHelper("surveytranslator");


            // If start date supplied convert it to the right format
            $aDateFormatData = getDateFormatData(Yii::app()->session['dateformat']);
            $sStartDate = $_POST['startdate'];
            if (trim($sStartDate) != '')
            {
                Yii::import('application.libraries.Date_Time_Converter');
                $converter = new Date_Time_Converter($sStartDate, $aDateFormatData['phpdate'] . ' H:i:s');
                $sStartDate = $converter->convert("Y-m-d H:i:s");
            }

            // If expiry date supplied convert it to the right format
            $sExpiryDate = $_POST['expires'];
            if (trim($sExpiryDate) != '')
            {
                Yii::import('application.libraries.Date_Time_Converter');
                $converter = new Date_Time_Converter($sExpiryDate, $aDateFormatData['phpdate'] . ' H:i:s');
                $sExpiryDate = $converter->convert("Y-m-d H:i:s");
            }

            $iTokenLength=$_POST['tokenlength'];
            //token length has to be at least 5, otherwise set it to default (15)
            if($iTokenLength < 5)
            {
                $iTokenLength = 15;
            }
            if($iTokenLength > 36)
            {
                $iTokenLength = 36;
            }

            // Insert base settings into surveys table
            $aInsertData = array(
            'expires' => $sExpiryDate,
            'startdate' => $sStartDate,
            'template' => $sTemplate,
            'owner_id' => Yii::app()->session['loginID'],
            'admin' => $_POST['admin'],
            'active' => 'N',
            'anonymized' => $_POST['anonymized'],
            'faxto' => $_POST['faxto'],
            'format' => $_POST['format'],
            'savetimings' => $_POST['savetimings'],
            'language' => $_POST['language'],
            'datestamp' => $_POST['datestamp'],
            'ipaddr' => $_POST['ipaddr'],
            'refurl' => $_POST['refurl'],
            'usecookie' => $_POST['usecookie'],
            'emailnotificationto' => $_POST['emailnotificationto'],
            'allowregister' => $_POST['allowregister'],
            'allowsave' => $_POST['allowsave'],
            'navigationdelay' => $_POST['navigationdelay'],
            'autoredirect' => $_POST['autoredirect'],
            'showxquestions' => $_POST['showxquestions'],
            'showgroupinfo' => $_POST['showgroupinfo'],
            'showqnumcode' => $_POST['showqnumcode'],
            'shownoanswer' => $_POST['shownoanswer'],
            'showwelcome' => $_POST['showwelcome'],
            'allowprev' => $_POST['allowprev'],
            'questionindex' => $_POST['questionindex'],
            'nokeyboard' => $_POST['nokeyboard'],
            'showprogress' => $_POST['showprogress'],
            'printanswers' => $_POST['printanswers'],
            'listpublic' => $_POST['public'],
            'htmlemail' => $_POST['htmlemail'],
            'sendconfirmation' => $_POST['sendconfirmation'],
            'tokenanswerspersistence' => $_POST['tokenanswerspersistence'],
            'alloweditaftercompletion' => $_POST['alloweditaftercompletion'],
            'usecaptcha' => $_POST['usecaptcha'],
            'publicstatistics' => $_POST['publicstatistics'],
            'publicgraphs' => $_POST['publicgraphs'],
            'assessments' => $_POST['assessments'],
            'emailresponseto' => $_POST['emailresponseto'],
            'tokenlength' => $iTokenLength
            );

            $warning = '';
            // make sure we only update emails if they are valid
            if (Yii::app()->request->getPost('adminemail', '') == ''
                || validateEmailAddress(Yii::app()->request->getPost('adminemail'))) {
                $aInsertData['adminemail'] = Yii::app()->request->getPost('adminemail');
            } else {
                $aInsertData['adminemail'] = '';
                $warning .= $this->getController()->lang->gT("Warning! Notification email was not updated because it was not valid.").'<br/>';
            }
            if (Yii::app()->request->getPost('bounce_email', '') == ''
                || validateEmailAddress(Yii::app()->request->getPost('bounce_email'))) {
                $aInsertData['bounce_email'] = Yii::app()->request->getPost('bounce_email');
            } else {
                $aInsertData['bounce_email'] = '';
                $warning .= $this->getController()->lang->gT("Warning! Bounce email was not updated because it was not valid.").'<br/>';
            }

            if (!is_null($iSurveyID))
            {
                $aInsertData['wishSID'] = $iSurveyID;
            }

            $iNewSurveyid = Survey::model()->insertNewSurvey($aInsertData);
            if (!$iNewSurveyid)
                die('Survey could not be created.');

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
            $sTitle = fixCKeditorText($sTitle);
            $sDescription = fixCKeditorText($sDescription);
            $sWelcome = fixCKeditorText($sWelcome);


            // Insert base language into surveys_language_settings table
            $aInsertData = array('surveyls_survey_id' => $iNewSurveyid,
            'surveyls_title' => $sTitle,
            'surveyls_description' => $sDescription,
            'surveyls_welcometext' => $sWelcome,
            'surveyls_language' => $_POST['language'],
            'surveyls_urldescription' => $_POST['urldescrip'],
            'surveyls_endtext' => $_POST['endtext'],
            'surveyls_url' => $_POST['url'],
            'surveyls_dateformat' => (int) $_POST['dateformat'],
            'surveyls_numberformat' => (int) $_POST['numberformat']
            );

            $langsettings = new SurveyLanguageSetting;
            $langsettings->insertNewSurvey($aInsertData);

            Yii::app()->session['flashmessage'] = $warning.$this->getController()->lang->gT("Survey was successfully added.");

            // Update survey permissions
            Permission::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'], $iNewSurveyid);

            $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $iNewSurveyid));
        }
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'survey', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerPackage('jquery-superfish');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
