<?php

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
*	$Id$
*/

/**
* Survey Common Action
*
* This controller contains common functions for survey related views.
*
* @package		LimeSurvey
* @subpackage	Backend
* @author		Shitiz Garg
*/
class Survey_Common_Action extends CAction
{
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        // Make sure viewHelper can be autoloaded
        Yii::import('application.helpers.viewHelper');
    }
    
    /**
    * Override runWithParams() implementation in CAction to help us parse
    * requests with subactions.
    *
    * @param array $params URL Parameters
    */
    public function runWithParams($params)
    {
        // Default method that would be called if the subaction and run() do not exist
        $sDefault = 'index';

        // Check for a subaction
        if (empty($params['sa']))
        {
            $sSubAction = $sDefault; // default
        }
        else
        {
            $sSubAction = $params['sa'];
        }

        // Check if the class has the method
        $oClass = new ReflectionClass($this);
        if (!$oClass->hasMethod($sSubAction))
        {
            // If it doesn't, revert to default Yii method, that is run() which should reroute us somewhere else
            $sSubAction = 'run';
        }

        // Populate the params. eg. surveyid -> iSurveyId
        $params = $this->_addPseudoParams($params);

        if (!empty($params['iSurveyId']))
        {
            if(!Survey::model()->findByPk($params['iSurveyId']))
            {
                $this->getController()->error('Invalid survey id');
            }
            elseif (!hasSurveyPermission($params['iSurveyId'], 'survey', 'read'))
            {
                $this->getController()->error('No permission');
            }
            else
            {
                LimeExpressionManager::SetSurveyId($params['iSurveyId']); // must be called early - it clears internal cache if a new survey is being used
            }
        }

        // Check if the method is public and of the action class, not its parents
        // ReflectionClass gets us the methods of the class and parent class
        // If the above method existence check passed, it might not be neceessary that it is of the action class
        $oMethod  = new ReflectionMethod($this, $sSubAction);

        // Get the action classes from the admin controller as the urls necessarily do not equal the class names. Eg. survey -> surveyaction
        $aActions = Yii::app()->getController()->getActionClasses();
        if(empty($aActions[$this->getId()]) || strtolower($oMethod->getDeclaringClass()->name) != $aActions[$this->getId()] || !$oMethod->isPublic())
        {
            // Either action doesn't exist in our whitelist, or the method class doesn't equal the action class or the method isn't public
            // So let us get the last possible default method, ie. index
            $oMethod = new ReflectionMethod($this, $sDefault);
        }

        // We're all good to go, let's execute it
        // runWithParamsInternal would automatically get the parameters of the method and populate them as required with the params
        return parent::runWithParamsInternal($this, $oMethod, $params);
    }

    /**
    * Some functions have different parameters, which are just an alias of the
    * usual parameters we're getting in the url. This function just populates
    * those variables so that we don't end up in an error.
    *
    * This is also used while rendering wrapped template
    * {@link Survey_Common_Action::_renderWrappedTemplate()}
    *
    * @param array $params Parameters to parse and populate
    * @return array Populated parameters
    */
    private function _addPseudoParams($params)
    {
        // Return if params isn't an array
        if (empty($params) || !is_array($params))
        {
            return $params;
        }

        $pseudos = array(
        'id' => 'iId',
        'gid' => 'iGroupId',
        'qid' => 'iQuestionId',
        'sid' => array('iSurveyId', 'iSurveyID'),
        'surveyid' => array('iSurveyId', 'iSurveyID'),
        'srid' => 'iSurveyResponseId',
        'scid' => 'iSavedControlId',
        'uid' => 'iUserId',
        'ugid' => 'iUserGroupId',
        'fieldname' => 'sFieldName',
        'fieldtext' => 'sFieldText',
        'action' => 'sAction',
        'lang' => 'sLanguage',
        'browselang' => 'sBrowseLang',
        'tokenids' => 'aTokenIds',
        'tokenid' => 'iTokenId',
        'subaction' => 'sSubAction',
        );

        // Foreach pseudo, take the key, if it exists,
        // Populate the values (taken as an array) as keys in params
        // with that key's value in the params
        // (only if that place is empty)
        foreach ($pseudos as $key => $pseudo)
        {
            if (!empty($params[$key]))
            {
                $pseudo = (array) $pseudo;

                foreach ($pseudo as $pseud)
                {
                    if (empty($params[$pseud]))
                    {
                        $params[$pseud] = $params[$key];
                    }
                }
            }
        }

        // Finally return the populated array
        return $params;
    }

    /**
    * Action classes require them to have a run method. We reroute it to index
    * if called.
    */
    public function run()
    {
        $this->index();
    }

    /**
    * Routes the action into correct subaction
    *
    * @access protected
    * @param string $sa
    * @param array $get_vars
    * @return void
    */
    protected function route($sa, array $get_vars)
    {
        $func_args = array();
        foreach ($get_vars as $k => $var)
            $func_args[$k] = Yii::app()->request->getQuery($var);

        return call_user_func_array(array($this, $sa), $func_args);
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * Addition of parameters should be avoided if they can be added to $aData
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array())
    {
        // Gather the data
        $aData['clang'] = $clang = Yii::app()->lang;
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');

        $aData = $this->_addPseudoParams($aData);
        $aViewUrls = (array) $aViewUrls;
        $sViewPath = '/admin/';

        if (!empty($sAction))
        {
            $sViewPath .= $sAction . '/';
        }



        // Header
        if(!isset($aData['display']['header']) || $aData['display']['header'] !== false)
        {
            // Send HTTP header
            header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
            Yii::app()->getController()->_getAdminHeader();
        }


        // Menu bars
        if (!isset($aData['display']['menu_bars']) || ($aData['display']['menu_bars'] !== false && (!is_array($aData['display']['menu_bars']) || !in_array('browse', array_keys($aData['display']['menu_bars'])))))
        {
            Yii::app()->getController()->_showadminmenu(!empty($aData['surveyid']) ? $aData['surveyid'] : null);

            if (!empty($aData['surveyid']))
            {

                LimeExpressionManager::StartProcessingPage(false, Yii::app()->baseUrl,true);  // so can click on syntax highlighting to edit questions

                $this->_surveybar($aData['surveyid'], !empty($aData['gid']) ? $aData['gid'] : null);

                if (isset($aData['display']['menu_bars']['surveysummary']))
                {

                    if ((empty($aData['display']['menu_bars']['surveysummary']) || !is_string($aData['display']['menu_bars']['surveysummary'])) && !empty($aData['gid']))
                    {
                        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
                    }
                    $this->_surveysummary($aData['surveyid'], !empty($aData['display']['menu_bars']['surveysummary']) ? $aData['display']['menu_bars']['surveysummary'] : null, !empty($aData['gid']) ? $aData['gid'] : null);
                }

                if (!empty($aData['gid']))
                {
                    if (empty($aData['display']['menu_bars']['gid_action']) && !empty($aData['qid']))
                    {
                        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
                    }

                    $this->_questiongroupbar($aData['surveyid'], $aData['gid'], !empty($aData['qid']) ? $aData['qid'] : null, !empty($aData['display']['menu_bars']['gid_action']) ? $aData['display']['menu_bars']['gid_action'] : null);

                    if (!empty($aData['qid']))
                    {
                        $this->_questionbar($aData['surveyid'], $aData['gid'], $aData['qid'], !empty($aData['display']['menu_bars']['qid_action']) ? $aData['display']['menu_bars']['qid_action'] : null);
                    }
                }

                LimeExpressionManager::FinishProcessingPage();

            }
        }

        if (!empty($aData['display']['menu_bars']['browse']) && !empty($aData['surveyid']))
        {
            $this->_browsemenubar($aData['surveyid'], $aData['display']['menu_bars']['browse']);
        }

        if (!empty($aData['display']['menu_bars']['user_group']))
        {
            $this->_userGroupBar(!empty($aData['ugid']) ? $aData['ugid'] : 0);
        }

        // Load views
        foreach ($aViewUrls as $sViewKey => $viewUrl)
        {
            if (empty($sViewKey) || !in_array($sViewKey, array('message', 'output')))
            {
                if (is_numeric($sViewKey))
                {
                    Yii::app()->getController()->render($sViewPath . $viewUrl, $aData);
                }
                elseif (is_array($viewUrl))
                {
                    foreach ($viewUrl as $aSubData)
                    {
                        $aSubData = array_merge($aData, $aSubData);
                        Yii::app()->getController()->render($sViewPath . $sViewKey, $aSubData);
                    }
                }
            }
            else
            {
                switch ($sViewKey)
                {
                    // Message
                    case 'message' :
                        if (empty($viewUrl['class']))
                        {
                            Yii::app()->getController()->_showMessageBox($viewUrl['title'], $viewUrl['message']);
                        }
                        else
                        {
                            Yii::app()->getController()->_showMessageBox($viewUrl['title'], $viewUrl['message'], $viewUrl['class']);
                        }
                        break;

                        // Output
                    case 'output' :
                        echo $viewUrl;
                        break;
                }
            }
        }

        // Footer
        if(!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false)
            Yii::app()->getController()->_loadEndScripts();

        if(!isset($aData['display']['footer']) || $aData['display']['footer'] !== false)
            Yii::app()->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
    }

    /**
    * Shows admin menu for question
    * @param int Survey id
    * @param int Group id
    * @param int Question id
    * @param string action
    */
    function _questionbar($iSurveyID, $gid, $qid, $action = null)
    {
        $clang = $this->getController()->lang;


        $baselang = Survey::model()->findByPk($iSurveyID)->language;

        //Show Question Details
        //Count answer-options for this question
        $qrr = Answers::model()->findAllByAttributes(array('qid' => $qid, 'language' => $baselang));

        $aData['qct'] = $qct = count($qrr);

        //Count sub-questions for this question
        $sqrq = Questions::model()->findAllByAttributes(array('parent_qid' => $qid, 'language' => $baselang));
        $aData['sqct'] = $sqct = count($sqrq);

        $qrrow = Questions::model()->findByAttributes(array('qid' => $qid, 'gid' => $gid, 'sid' => $iSurveyID, 'language' => $baselang));

        $questionsummary = "<div class='menubar'>\n";

        // Check if other questions in the Survey are dependent upon this question
        $condarray = getQuestDepsForConditions($iSurveyID, "all", "all", $qid, "by-targqid", "outsidegroup");

        $sumresult1 = Survey::model()->findByPk($iSurveyID);
        if (is_null($sumresult1))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("Invalid survey ID");
            $this->getController()->redirect($this->getController()->createUrl("admin/index"));
        } //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;

        $surveyinfo = array_map('flattenText', $surveyinfo);
        $aData['activated'] = $surveyinfo['active'];

        $qrrow = $qrrow->attributes;
        if (hasSurveyPermission($iSurveyID, 'surveycontent', 'read'))
        {
            if (count(Survey::model()->findByPk($iSurveyID)->additionalLanguages) != 0)
            {
                Yii::app()->loadHelper('surveytranslator');
                $tmp_survlangs = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
                $baselang = Survey::model()->findByPk($iSurveyID)->language;
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);
                $aData['tmp_survlangs'] = $tmp_survlangs;
            }
        }
        $aData['qtypes'] = $qtypes = getQuestionTypeList('', 'array');
        if ($action == 'editansweroptions' || $action == "editsubquestions" || $action == "editquestion" || $action == "editdefaultvalues" || $action =="editdefaultvalues" || $action == "copyquestion")
        {
            $qshowstyle = "style='display: none'";
        }
        else
        {
            $qshowstyle = "";
        }
        $aData['qshowstyle'] = $qshowstyle;
        $aData['action'] = $action;
        $aData['surveyid'] = $iSurveyID;
        $aData['qid'] = $qid;
        $aData['gid'] = $gid;
        $aData['clang'] = $clang;
        $aData['qrrow'] = $qrrow;
        $aData['baselang'] = $baselang;
        $aAttributesWithValues = Questions::model()->getAdvancedSettingsWithValues($qid, $qrrow['type'], $iSurveyID, $baselang);
        $DisplayArray = array();
        foreach ($aAttributesWithValues as $aAttribute)
        {
            if (($aAttribute['i18n'] == false && isset($aAttribute['value']) && $aAttribute['value'] != $aAttribute['default']) || ($aAttribute['i18n'] == true && isset($aAttribute['value'][$baselang]) && $aAttribute['value'][$baselang] != $aAttribute['default']))
            {
                if ($aAttribute['inputtype'] == 'singleselect')
                {
                    $aAttribute['value'] = $aAttribute['options'][$aAttribute['value']];
                }
                /*
                if ($aAttribute['name']=='relevance')
                {
                $sRelevance = $aAttribute['value'];
                if ($sRelevance !== '' && $sRelevance !== '1' && $sRelevance !== '0')
                {
                LimeExpressionManager::ProcessString("{" . $sRelevance . "}");    // tests Relevance equation so can pretty-print it
                $aAttribute['value']= LimeExpressionManager::GetLastPrettyPrintExpression();
                }
                }
                */
                $DisplayArray[] = $aAttribute;
            }
        }
        $aData['advancedsettings'] = $DisplayArray;
        $aData['condarray'] = $condarray;
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
        $questionsummary .= $this->getController()->render('/admin/survey/Question/questionbar_view', $aData, true);
        $finaldata['display'] = $questionsummary;

        $this->getController()->render('/survey_view', $finaldata);
    }

    /**
    * Shows admin menu for question groups
    * @param int Survey id
    * @param int Group id
    */
    function _questiongroupbar($iSurveyID, $gid, $qid=null, $action = null)
    {
        $clang = $this->getController()->lang;
        $baselang = Survey::model()->findByPk($iSurveyID)->language;

        Yii::app()->loadHelper('replacements');
        // TODO: check that surveyid and thus baselang are always set here
        $sumresult4 = Questions::model()->findAllByAttributes(array('sid' => $iSurveyID, 'gid' => $gid, 'language' => $baselang));
        $sumcount4 = count($sumresult4);

        $grpresult = Groups::model()->findAllByAttributes(array('gid' => $gid, 'language' => $baselang));

        // Check if other questions/groups are dependent upon this group
        $condarray = getGroupDepsForConditions($iSurveyID, "all", $gid, "by-targgid");

        $groupsummary = "<div class='menubar'>\n"
        . "<div class='menubar-title ui-widget-header'>\n";

        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$iSurveyID"; //Getting data for this survey
        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->findByPk($iSurveyID); //$sumquery1, 1) ; //Checked //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings[0]->attributes);
        $surveyinfo = array_map('flattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $aData['activated'] = $activated = $surveyinfo['active'];

        foreach ($grpresult as $grow)
        {
            $grow = $grow->attributes;

            $grow = array_map('flattenText', $grow);
            $aData = array();
            $aData['activated'] = $activated;
            $aData['qid'] = $qid;
            $aData['QidPrev'] = $QidPrev = getQidPrevious($iSurveyID, $gid, $qid);
            $aData['QidNext'] = $QidNext = getQidNext($iSurveyID, $gid, $qid);

            if ($action == 'editgroup' || $action == 'addquestion' || $action == 'viewquestion' || $action == "editdefaultvalues")
            {
                $gshowstyle = "style='display: none'";
            }
            else
            {
                $gshowstyle = "";
            }

            $aData['gshowstyle'] = $gshowstyle;
            $aData['surveyid'] = $iSurveyID;
            $aData['gid'] = $gid;
            $aData['grow'] = $grow;
            $aData['clang'] = $clang;
            $aData['condarray'] = $condarray;
            $aData['sumcount4'] = $sumcount4;
            $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
            $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');

            $groupsummary .= $this->getController()->render('/admin/survey/QuestionGroups/questiongroupbar_view', $aData, true);
        }
        $groupsummary .= "\n</table>\n";

        $finaldata['display'] = $groupsummary;
        $this->getController()->render('/survey_view', $finaldata);
    }

    /**
    * Shows admin menu for surveys
    * @param int Survey id
    */
    function _surveybar($iSurveyID, $gid=null)
    {
        $clang = $this->getController()->lang;
        $baselang = Survey::model()->findByPk($iSurveyID)->language;
        $condition = array('sid' => $iSurveyID, 'language' => $baselang);

        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->find('sid = :surveyid', array(':surveyid' => $iSurveyID)); //$sumquery1, 1) ; //Checked
        if (is_null($sumresult1))
        {
            Yii::app()->session['flashmessage'] = $clang->gT("Invalid survey ID");
            $this->getController()->redirect($this->getController()->createUrl("admin/index"));
        } //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings[0]->attributes);
        $surveyinfo = array_map('flattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = ($surveyinfo['active'] == 'Y');

        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/jquery.coookie.js';
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/superfish.js';
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/hoverIntent.js';
        $js_admin_includes[] = Yii::app()->getConfig('adminscripts') . 'surveytoolbar.js';
        $this->getController()->_js_admin_includes($js_admin_includes);

        //Parse data to send to view
        $aData['clang'] = $clang;
        $aData['surveyinfo'] = $surveyinfo;
        $aData['surveyid'] = $iSurveyID;

        // ACTIVATE SURVEY BUTTON
        $aData['activated'] = $activated;

        $condition = array('sid' => $iSurveyID, 'parent_qid' => 0, 'language' => $baselang);

        //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$iSurveyID} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = Questions::model()->findAllByAttributes($condition); //Checked
        $sumcount3 = count($sumresult3);

        $aData['canactivate'] = $sumcount3 > 0 && hasSurveyPermission($iSurveyID, 'surveyactivation', 'update');
        $aData['candeactivate'] = hasSurveyPermission($iSurveyID, 'surveyactivation', 'update');
        $aData['expired'] = $surveyinfo['expires'] != '' && ($surveyinfo['expires'] < dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));
        $aData['notstarted'] = ($surveyinfo['startdate'] != '') && ($surveyinfo['startdate'] > dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));

        // Start of suckerfish menu
        // TEST BUTTON
        if (!$activated)
        {
            $aData['icontext'] = $clang->gT("Test this survey");
        }
        else
        {
            $aData['icontext'] = $clang->gT("Execute this survey");
        }

        $aData['baselang'] = Survey::model()->findByPk($iSurveyID)->language;

        $tmp_survlangs = Survey::model()->findByPk($iSurveyID)->getAdditionalLanguages();
        $aData['onelanguage']=(count($tmp_survlangs)==0);
        $aData['additionallanguages'] = $tmp_survlangs;
        $tmp_survlangs[] = $aData['baselang'];
        rsort($tmp_survlangs);
        $aData['languagelist'] = $tmp_survlangs;

        $aData['hasadditionallanguages'] = (count($aData['additionallanguages']) > 0);

        // EDIT SURVEY TEXT ELEMENTS BUTTON
        $aData['surveylocale'] = hasSurveyPermission($iSurveyID, 'surveylocale', 'read');
        // EDIT SURVEY SETTINGS BUTTON
        $aData['surveysettings'] = hasSurveyPermission($iSurveyID, 'surveysettings', 'read');
        // Survey permission item
        $aData['surveysecurity'] = hasSurveyPermission($iSurveyID, 'surveysecurity', 'read');
        // CHANGE QUESTION GROUP ORDER BUTTON
        $aData['surveycontent'] = hasSurveyPermission($iSurveyID, 'surveycontent', 'read');
        $aData['groupsum'] = (getGroupSum($iSurveyID, $surveyinfo['language']) > 1);
        // SET SURVEY QUOTAS BUTTON
        $aData['quotas'] = hasSurveyPermission($iSurveyID, 'quotas', 'read');
        // Assessment menu item
        $aData['assessments'] = hasSurveyPermission($iSurveyID, 'assessments', 'read');
        // EDIT SURVEY TEXT ELEMENTS BUTTON
        // End if survey properties
        // Tools menu item
        // Delete survey item
        $aData['surveydelete'] = hasSurveyPermission($iSurveyID, 'survey', 'delete');
        // Translate survey item
        $aData['surveytranslate'] = hasSurveyPermission($iSurveyID, 'translations', 'read');
        // RESET SURVEY LOGIC BUTTON
        //$sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')." as q WHERE c.qid = q.qid AND q.sid=$iSurveyID"; //Getting a count of conditions for this survey
        // TMSW Conditions->Relevance:  How is conditionscount used?  Should Relevance do the same?

        $iConditionCount = Conditions::model()->with(Array('questions'=>array('condition'=>'sid ='.$iSurveyID)))->count();

        $aData['surveycontent'] = hasSurveyPermission($iSurveyID, 'surveycontent', 'update');
        $aData['conditionscount'] = ($iConditionCount > 0);
        // Eport menu item
        $aData['surveyexport'] = hasSurveyPermission($iSurveyID, 'surveycontent', 'export');
        // PRINTABLE VERSION OF SURVEY BUTTON
        // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON
        //browse responses menu item
        $aData['respstatsread'] = hasSurveyPermission($iSurveyID, 'responses', 'read') || hasSurveyPermission($iSurveyID, 'statistics', 'read') || hasSurveyPermission($iSurveyID, 'responses', 'export');
        // Data entry screen menu item
        $aData['responsescreate'] = hasSurveyPermission($iSurveyID, 'responses', 'create');
        $aData['responsesread'] = hasSurveyPermission($iSurveyID, 'responses', 'read');
        // TOKEN MANAGEMENT BUTTON
        $bTokenExists = tableExists('{{tokens_' . $iSurveyID . '}}');
        if(!$bTokenExists)
            $aData['tokenmanagement'] = hasSurveyPermission($iSurveyID, 'surveysettings', 'update') || hasSurveyPermission($iSurveyID, 'tokens', 'create');
        else
            $aData['tokenmanagement'] = hasSurveyPermission($iSurveyID, 'surveysettings', 'update') || hasSurveyPermission($iSurveyID, 'tokens', 'create') || hasSurveyPermission($iSurveyID, 'tokens', 'read') || hasSurveyPermission($iSurveyID, 'tokens', 'export') || hasSurveyPermission($iSurveyID, 'tokens', 'import'); // and export / import ?

        $aData['gid'] = $gid; // = $this->input->post('gid');

        if (hasSurveyPermission($iSurveyID, 'surveycontent', 'read'))
        {
            $aData['permission'] = true;
        }
        else
        {
            $aData['gid'] = $gid = null;
            $qid = null;
            $aData['permission'] = false;
        }

        if (getGroupListLang($gid, $baselang, $iSurveyID))
        {
            $aData['groups'] = getGroupListLang($gid, $baselang, $iSurveyID);
        }
        else
        {
            $aData['groups'] = "<option>" . $clang->gT("None") . "</option>";
        }

        $aData['GidPrev'] = $GidPrev = getGidPrevious($iSurveyID, $gid);

        $aData['GidNext'] = $GidNext = getGidNext($iSurveyID, $gid);
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');

        $this->getController()->render("/admin/survey/surveybar_view", $aData);
    }

    /**
    * Show survey summary
    * @param int Survey id
    * @param string Action to be performed
    */
    function _surveysummary($iSurveyID, $action=null, $gid=null)
    {
        $clang = $this->getController()->lang;
        //$surveyinfo = array_map('flattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $surveyinfo=getSurveyInfo($iSurveyID);
        $baselang = $surveyinfo['language'];
        $activated = $surveyinfo['active'];

        $condition = array('sid' => $iSurveyID, 'parent_qid' => 0, 'language' => $baselang);

        $sumresult3 = Questions::model()->findAllByAttributes($condition); //Checked
        $sumcount3 = count($sumresult3);

        $condition = array('sid' => $iSurveyID, 'language' => $baselang);

        //$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid={$iSurveyID} AND language='".$baselang."'"; //Getting a count of groups for this survey
        $sumresult2 = Groups::model()->findAllByAttributes($condition); //Checked
        $sumcount2 = count($sumresult2);

        //SURVEY SUMMARY

        $aAdditionalLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
        $surveysummary2 = "";
        if ($surveyinfo['anonymized'] != "N")
        {
            $surveysummary2 .= $clang->gT("Responses to this survey are anonymized.") . "<br />";
        }
        else
        {
            $surveysummary2 .= $clang->gT("Responses to this survey are NOT anonymized.") . "<br />";
        }
        if ($surveyinfo['format'] == "S")
        {
            $surveysummary2 .= $clang->gT("It is presented question by question.") . "<br />";
        }
        elseif ($surveyinfo['format'] == "G")
        {
            $surveysummary2 .= $clang->gT("It is presented group by group.") . "<br />";
        }
        else
        {
            $surveysummary2 .= $clang->gT("It is presented on one single page.") . "<br />";
        }
        if ($surveyinfo['allowjumps'] == "Y")
        {
            if ($surveyinfo['format'] == 'A')
            {
                $surveysummary2 .= $clang->gT("No question index will be shown with this format.") . "<br />";
            }
            else
            {
                $surveysummary2 .= $clang->gT("A question index will be shown; participants will be able to jump between viewed questions.") . "<br />";
            }
        }
        if ($surveyinfo['datestamp'] == "Y")
        {
            $surveysummary2 .= $clang->gT("Responses will be date stamped.") . "<br />";
        }
        if ($surveyinfo['ipaddr'] == "Y")
        {
            $surveysummary2 .= $clang->gT("IP Addresses will be logged") . "<br />";
        }
        if ($surveyinfo['refurl'] == "Y")
        {
            $surveysummary2 .= $clang->gT("Referrer URL will be saved.") . "<br />";
        }
        if ($surveyinfo['usecookie'] == "Y")
        {
            $surveysummary2 .= $clang->gT("It uses cookies for access control.") . "<br />";
        }
        if ($surveyinfo['allowregister'] == "Y")
        {
            $surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey") . "<br />";
        }
        if ($surveyinfo['allowsave'] == "Y" && $surveyinfo['tokenanswerspersistence'] == 'N')
        {
            $surveysummary2 .= $clang->gT("Participants can save partially finished surveys") . "<br />\n";
        }
        if ($surveyinfo['emailnotificationto'] != '')
        {
            $surveysummary2 .= $clang->gT("Basic email notification is sent to:") . " {$surveyinfo['emailnotificationto']}<br />\n";
        }
        if ($surveyinfo['emailresponseto'] != '')
        {
            $surveysummary2 .= $clang->gT("Detailed email notification with response data is sent to:") . " {$surveyinfo['emailresponseto']}<br />\n";
        }

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if (trim($surveyinfo['startdate']) != '')
        {
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($surveyinfo['startdate'], 'Y-m-d H:i:s');
            $aData['startdate'] = $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
        }
        else
        {
            $aData['startdate'] = "-";
        }

        if (trim($surveyinfo['expires']) != '')
        {
            //$constructoritems = array($surveyinfo['expires'] , "Y-m-d H:i:s");
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($surveyinfo['expires'], 'Y-m-d H:i:s');
            //$datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
            $aData['expdate'] = $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
        }
        else
        {
            $aData['expdate'] = "-";
        }

        if (!$surveyinfo['language'])
        {
            $aData['language'] = getLanguageNameFromCode($currentadminlang, false);
        }
        else
        {
            $aData['language'] = getLanguageNameFromCode($surveyinfo['language'], false);
        }

        // get the rowspan of the Additionnal languages row
        // is at least 1 even if no additionnal language is present
        $additionnalLanguagesCount = count($aAdditionalLanguages);
        $first = true;
        $aData['additionnalLanguages'] = "";
        if ($additionnalLanguagesCount == 0)
        {
            $aData['additionnalLanguages'] .= "<td>-</td>\n";
        }
        else
        {
            foreach ($aAdditionalLanguages as $langname)
            {
                if ($langname)
                {
                    if (!$first)
                    {
                        $aData['additionnalLanguages'].= "<tr><td>&nbsp;</td>";
                    }
                    $first = false;
                    $aData['additionnalLanguages'] .= "<td>" . getLanguageNameFromCode($langname, false) . "</td></tr>\n";
                }
            }
        }
        if ($first)
            $aData['additionnalLanguages'] .= "</tr>";

        if ($surveyinfo['surveyls_urldescription'] == "")
        {
            $surveyinfo['surveyls_urldescription'] = htmlspecialchars($surveyinfo['surveyls_url']);
        }

        if ($surveyinfo['surveyls_url'] != "")
        {
            $aData['endurl'] = " <a target='_blank' href=\"" . htmlspecialchars($surveyinfo['surveyls_url']) . "\" title=\"" . htmlspecialchars($surveyinfo['surveyls_url']) . "\">".flattenText($surveyinfo['surveyls_urldescription'])."</a>";
        }
        else
        {
            $aData['endurl'] = "-";
        }

        $aData['sumcount3'] = $sumcount3;
        $aData['sumcount2'] = $sumcount2;

        if ($activated == "N")
        {
            $aData['activatedlang'] = $clang->gT("No");
        }
        else
        {
            $aData['activatedlang'] = $clang->gT("Yes");
        }

        $aData['activated'] = $activated;
        if ($activated == "Y")
        {
            $aData['surveydb'] = Yii::app()->db->tablePrefix . "survey_" . $iSurveyID;
        }
        $aData['warnings'] = "";
        if ($activated == "N" && $sumcount3 == 0)
        {
            $aData['warnings'] = $clang->gT("Survey cannot be activated yet.") . "<br />\n";
            if ($sumcount2 == 0 && hasSurveyPermission($iSurveyID, 'surveycontent', 'create'))
            {
                $aData['warnings'] .= "<span class='statusentryhighlight'>[" . $clang->gT("You need to add question groups") . "]</span><br />";
            }
            if ($sumcount3 == 0 && hasSurveyPermission($iSurveyID, 'surveycontent', 'create'))
            {
                $aData['warnings'] .= "<span class='statusentryhighlight'>[" . $clang->gT("You need to add questions") . "]</span><br />";
            }
        }
        $aData['hints'] = $surveysummary2;

        //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
        //        $aData['tableusage'] = getDBTableUsage($iSurveyID);
        // ToDo: Table usage is calculated on every menu display which is too slow with bug surveys.
        // Needs to be moved to a database field and only updated if there are question/subquestions added/removed (it's currently also not functional due to the port)
        //

        $aData['tableusage'] = false;
        if ($gid || ($action !== true && in_array($action, array('deactivate', 'activate', 'surveysecurity', 'editdefaultvalues', 'editemailtemplates',
        'surveyrights', 'addsurveysecurity', 'addusergroupsurveysecurity',
        'setsurveysecurity', 'setusergroupsurveysecurity', 'delsurveysecurity',
        'editsurveysettings', 'editsurveylocalesettings', 'updatesurveysettingsandeditlocalesettings', 'addgroup', 'importgroup',
        'ordergroups', 'deletesurvey', 'resetsurveylogic',
        'importsurveyresources', 'translate', 'emailtemplates',
        'exportstructure', 'quotas', 'copysurvey', 'viewgroup', 'viewquestion'))))
        {
            $showstyle = "style='display: none'";
        }
        else
        {
            $showstyle = "";
        }

        $aData['showstyle'] = $showstyle;
        $aData['aAdditionalLanguages'] = $aAdditionalLanguages;
        $aData['clang'] = $clang;
        $aData['surveyinfo'] = $surveyinfo;

        $this->getController()->render("/admin/survey/surveySummary_view", $aData);
    }

    /**
    * Browse Menu Bar
    */
    function _browsemenubar($iSurveyID, $title='')
    {
        //BROWSE MENU BAR
        $aData['title'] = $title;
        $aData['thissurvey'] = getSurveyInfo($iSurveyID);
        $aData['sImageURL'] = Yii::app()->getConfig("adminimageurl");
        $aData['clang'] = Yii::app()->lang;
        $aData['surveyid'] = $iSurveyID;
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/superfish.js';
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/hoverIntent.js';
        $this->getController()->_js_admin_includes($js_admin_includes);
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");
        

        $tmp_survlangs = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
        $baselang = Survey::model()->findByPk($iSurveyID)->language;
        $tmp_survlangs[] = $baselang;
        rsort($tmp_survlangs);
        $aData['tmp_survlangs'] = $tmp_survlangs;

        $this->getController()->render("/admin/responses/browsemenubar_view", $aData);
    }
    /**
    * Load menu bar of user group controller.
    * @param int $ugid
    * @return void
    */
    function _userGroupBar($ugid = 0)
    {
        $data['clang'] = Yii::app()->lang;
        Yii::app()->loadHelper('database');

        if (!empty($ugid)) {
            $sQuery = "SELECT gp.* FROM {{user_groups}} AS gp, {{user_in_groups}} AS gu WHERE gp.ugid=gu.ugid AND gp.ugid = {$ugid}";
            if (!hasGlobalPermission('USER_RIGHT_SUPERADMIN'))
            {
                $sQuery .=" AND gu.uid = ".Yii::app()->session['loginID'];
            }
            
            $grpresult = Yii::app()->db->createCommand($sQuery)->queryRow();  //Checked

            if ($grpresult) {
                $grpresultcount=1;
                $grow = array_map('htmlspecialchars', $grpresult);
            }
            else
            {
                $grpresultcount=0;
                $grow = false;
            }

            $data['grow'] = $grow;
            $data['grpresultcount'] = $grpresultcount;

        }

        $data['ugid'] = $ugid;
        $data['imageurl'] = Yii::app()->getConfig("adminimageurl"); // Don't came from rendertemplate ?
        $this->getController()->render('/admin/usergroup/usergroupbar_view', $data);
    }

    protected function _filterImportedResources($extractdir, $destdir)
    {
        $clang = $this->getController()->lang;
        $aErrorFilesInfo = array();
        $aImportedFilesInfo = array();

        if (!is_dir($extractdir))
            return array(array(), array());

        if (!is_dir($destdir))
            mkdir($destdir);

        $dh = opendir($extractdir);

        while ($direntry = readdir($dh))
        {
            if ($direntry != "." && $direntry != "..")
            {
                if (is_file($extractdir . "/" . $direntry))
                {
                    // is  a file
                    $extfile = substr(strrchr($direntry, '.'), 1);
                    if (!(stripos(',' . Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false))
                    {
                        // Extension allowed
                        if (!copy($extractdir . "/" . $direntry, $destdir . "/" . $direntry))
                        {
                            $aErrorFilesInfo[] = Array(
                            "filename" => $direntry,
                            "status" => $clang->gT("Copy failed")
                            );
                        }
                        else
                        {
                            $aImportedFilesInfo[] = Array(
                            "filename" => $direntry,
                            "status" => $clang->gT("OK")
                            );
                        }
                    }
                    else
                    {
                        // Extension forbidden
                        $aErrorFilesInfo[] = Array(
                        "filename" => $direntry,
                        "status" => $clang->gT("Forbidden Extension")
                        );
                    }
                    unlink($extractdir . "/" . $direntry);
                }
            }
        }

        return array($aImportedFilesInfo, $aErrorFilesInfo);
    }

    /**
    * Creates a temporary directory
    *
    * @access protected
    * @param string $dir
    * @param string $prefix
    * @param int $mode
    * @return string
    */
    protected function _tempdir($dir, $prefix='', $mode=0700)
    {
        if (substr($dir, -1) != DIRECTORY_SEPARATOR)
            $dir .= DIRECTORY_SEPARATOR;

        do
        {
            $path = $dir . $prefix . mt_rand(0, 9999999);
        }
        while (!mkdir($path, $mode));

        return $path;
    }

}
