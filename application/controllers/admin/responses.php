<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
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
 */

/**
 * Responses Controller
 *
 * This controller performs browse actions.
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class responses extends Survey_Common_Action
{

    /**
     * @var string : Default layout is bare : temporary to real layout
     */
    public $layout = 'bare';

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('surveytranslator');
    }

    /**
     * Used to get responses data for browse etc
     *
     * @param mixed $params ?
     * @return array
     * @todo Don't use extract
     */
    private function _getData($params)
    {
        if (is_numeric($params)) {
            $iSurveyId = $params;
        } elseif (is_array($params)) {
            extract($params);
        }
        $aData = array();
        // Set the variables in an array
        $aData['surveyid'] = $aData['iSurveyId'] = (int) $iSurveyId;
        if (!empty($iId)) {
            $aData['iId'] = (int) $iId;
        }
        $aData['imageurl'] = Yii::app()->getConfig('imageurl');
        $aData['action'] = Yii::app()->request->getParam('action');
        $aData['all'] = Yii::app()->request->getParam('all');
        $thissurvey = getSurveyInfo($iSurveyId);
        if (!$thissurvey) {
// Already done in Survey_Common_Action
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            $this->getController()->redirect(array("admin/index"));
        } elseif ($thissurvey['active'] != 'Y') {
            Yii::app()->session['flashmessage'] = gT("This survey has not been activated. There are no results to browse.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

        if (Yii::app()->request->getParam('browselang')) {
            $aData['language'] = Yii::app()->request->getParam('browselang');
            $aData['languagelist'] = $languagelist = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            $aData['languagelist'][] = Survey::model()->findByPk($iSurveyId)->language;
            if (!in_array($aData['language'], $languagelist)) {
                $aData['language'] = $thissurvey['language'];
            }
        } else {
            $aData['language'] = $thissurvey['language'];
        }

        $aData['qulanguage'] = Survey::model()->findByPk($iSurveyId)->language;

        $aData['surveyoptions'] = '';
        $aData['browseoutput']  = '';

        return $aData;
    }

    public function getActionParams()
    {
        return array_merge($_GET, $_POST);
    }

    public function viewbytoken($iSurveyID, $token, $sBrowseLang = '')
    {
        // Get Response ID from token
        $oResponse = SurveyDynamic::model($iSurveyID)->findByAttributes(array('token'=>$token));
        if (!$oResponse) {
            Yii::app()->setFlashMessage(gT("Sorry, this response was not found."), 'error');
            $this->getController()->redirect(array("admin/responses/sa/browse/surveyid/{$iSurveyID}"));
        } else {
            $this->getController()->redirect(array("admin/responses/sa/view/surveyid/{$iSurveyID}/id/{$oResponse->id}"));
        }

    }


    /**
     * View a single response as queXML PDF
     *
     * @param mixed $iSurveyID
     * @param mixed $iId
     * @param mixed $sBrowseLang
     */
    public function viewquexmlpdf($iSurveyID, $iId, $sBrowseLang = '')
    {
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')) {
            $aData = $this->_getData(array('iId' => $iId, 'iSurveyId' => $iSurveyID, 'browselang' => $sBrowseLang));
            $sBrowseLanguage = $aData['language'];

            Yii::import("application.libraries.admin.quexmlpdf", true);

            $quexmlpdf = new quexmlpdf();

            // Setting the selected language for printout
            App()->setLanguage($sBrowseLanguage);

            $quexmlpdf->setLanguage($sBrowseLanguage);

            set_time_limit(120);

            Yii::app()->loadHelper('export');

            $quexml = quexml_export($iSurveyID, $sBrowseLanguage, $iId);

            $quexmlpdf->create($quexmlpdf->createqueXML($quexml));

            $quexmlpdf->Output("$iSurveyID-$iId-queXML.pdf", 'D');
        } else {
            $aData = array();
            $aData['surveyid'] = $iSurveyID;
            $message = array();
            $message['title'] = gT('Access denied!');
            $message['message'] = gT('You do not have permission to access this page.');
            $message['class'] = "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
    }

    /**
     * View a single response in detail
     *
     * @param mixed $iSurveyID
     * @param mixed $iId
     * @param mixed $sBrowseLang
     */
    public function view($iSurveyID, $iId, $sBrowseLang = '')
    {
        $survey = Survey::model()->findByPk($iSurveyID);

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')) {
            $aData = $this->_getData(array('iId' => $iId, 'iSurveyId' => $iSurveyID, 'browselang' => $sBrowseLang));
            $sBrowseLanguage = $aData['language'];

            extract($aData);

            $aViewUrls = array();

            $fieldmap = createFieldMap($survey, 'full', false, false, $aData['language']);
            $bHaveToken = $survey->anonymized == "N" && tableExists('tokens_'.$iSurveyID); // Boolean : show (or not) the token
            if (!Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')) {
// If not allowed to read: remove it
                unset($fieldmap['token']);
                $bHaveToken = false;
            }
            //add token to top of list if survey is not private
            if ($bHaveToken) {
                $fnames[] = array("token", gT("Token ID"), 'code'=>'token');
                $fnames[] = array("firstname", gT("First name"), 'code'=>'firstname'); // or token:firstname ?
                $fnames[] = array("lastname", gT("Last name"), 'code'=>'lastname');
                $fnames[] = array("email", gT("Email"), 'code'=>'email');
            }
            $fnames[] = array("submitdate", gT("Submission date"), gT("Completed"), "0", 'D', 'code'=>'submitdate');
            $fnames[] = array("completed", gT("Completed"), "0");

            foreach ($fieldmap as $field) {
                if ($field['fieldname'] == 'lastpage' || $field['fieldname'] == 'submitdate') {
                                    continue;
                }
                if ($field['type'] == 'interview_time') {
                                    continue;
                }
                if ($field['type'] == 'page_time') {
                                    continue;
                }
                if ($field['type'] == 'answer_time') {
                                    continue;
                }

                //$question = $field['question'];
                $question = viewHelper::getFieldText($field);

                if ($field['type'] != Question::QT_VERTICAL_FILE_UPLOAD) {
                    $fnames[] = array($field['fieldname'], viewHelper::getFieldText($field), 'code'=>viewHelper::getFieldCode($field, array('LEMcompat'=>true)));
                } elseif ($field['aid'] !== 'filecount') {
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($field['qid']);

                    for ($i = 0; $i < $qidattributes['max_num_of_files']; $i++) {
                        $filenum = sprintf(gT("File %s"), $i + 1);
                        if ($qidattributes['show_title'] == 1) {
                                                    $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('Title').")", 'code'=>viewHelper::getFieldCode($field).'(title)', "type" => Question::QT_VERTICAL_FILE_UPLOAD, "metadata" => "title", "index" => $i);
                        }

                        if ($qidattributes['show_comment'] == 1) {
                                                    $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('Comment').")", 'code'=>viewHelper::getFieldCode($field).'(comment)', "type" => Question::QT_VERTICAL_FILE_UPLOAD, "metadata" => "comment", "index" => $i);
                        }

                        $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('File name').")", 'code'=>viewHelper::getFieldCode($field).'(name)', "type" => "|", "metadata" => "name", "index" => $i, 'qid'=>$field['qid']);
                        $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".gT('File size').")", 'code'=>viewHelper::getFieldCode($field).'(size)', "type" => "|", "metadata" => "size", "index" => $i);

                        //$fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
                    }
                } else {
                    $fnames[] = array($field['fieldname'], gT("File count"));
                }
            }

            $nfncount = count($fnames) - 1;
            if ($iId < 1) {
                $iId = 1;
            }

            $exist = SurveyDynamic::model($iSurveyID)->exist($iId);
            $next = SurveyDynamic::model($iSurveyID)->next($iId, true);
            $previous = SurveyDynamic::model($iSurveyID)->previous($iId, true);
            $aData['exist'] = $exist;
            $aData['next'] = $next;
            $aData['previous'] = $previous;
            $aData['id'] = $iId;

            $aViewUrls[] = 'browseidheader_view';
            if ($exist) {
                $oPurifier = new CHtmlPurifier();
                //SHOW INDIVIDUAL RECORD
                $oCriteria = new CDbCriteria();
                if ($bHaveToken) {
                    $oCriteria = SurveyDynamic::model($iSurveyID)->addTokenCriteria($oCriteria);
                }

                $oCriteria->addCondition("id = {$iId}");
                $iIdresult = SurveyDynamic::model($iSurveyID)->find($oCriteria);
                $aResult = array_merge($iIdresult->tokens->decrypt()->attributes, $iIdresult->decrypt()->attributes);

                $iId = $aResult['id'];
                $rlanguage = $aResult['startlanguage'];
                $aData['bHasFile'] = false;
                if (isset($rlanguage)) {
                    $aData['rlanguage'] = $rlanguage;
                }
                $highlight = false;
                for ($i = 0; $i < $nfncount + 1; $i++) {
                    if ($fnames[$i][0] != 'completed' && is_null($aResult[$fnames[$i][0]])) {
                        continue; // irrelevant, so don't show
                    }
                    $inserthighlight = '';
                    if ($highlight) {
                                                $inserthighlight = "class='highlight'";
                    }

                    if ($fnames[$i][0] == 'completed') {
                        if ($aResult['submitdate'] == null || $aResult['submitdate'] == "N") {
                            $answervalue = "N";
                        } else {
                            $answervalue = "Y";
                        }
                    } else {
                        // File upload question type.
                        if (isset($fnames[$i]['type']) && $fnames[$i]['type'] == Question::QT_VERTICAL_FILE_UPLOAD) {
                            $index = $fnames[$i]['index'];
                            $metadata = $fnames[$i]['metadata'];
                            $phparray = json_decode_ls($aResult[$fnames[$i][0]]);

                            if (isset($phparray[$index])) {
                                switch ($metadata) {
                                    case "size":
                                        $answervalue = sprintf(gT("%s KB"), intval($phparray[$index][$metadata]));
                                        break;
                                    case "name":
                                        $answervalue = CHtml::link(
                                            htmlspecialchars($oPurifier->purify(rawurldecode($phparray[$index][$metadata]))),
                                            $this->getController()->createUrl("/admin/responses", array("sa"=>"actionDownloadfile", "surveyid"=>$iSurveyID, "iResponseId"=>$iId, "iQID"=>$fnames[$i]['qid'], "iIndex"=>$index))
                                        );
                                        break;
                                    default:
                                        $answervalue = htmlspecialchars(strip_tags(stripJavaScript($phparray[$index][$metadata])));
                                }
                                $aData['bHasFile'] = true;
                            } else {
                                $answervalue = "";
                            }
                        } else {
                            $answervalue = htmlspecialchars(strip_tags(stripJavaScript(getExtendedAnswer($iSurveyID, $fnames[$i][0], $aResult[$fnames[$i][0]], $sBrowseLanguage))), ENT_QUOTES);
                        }
                    }
                    $aData['answervalue'] = $answervalue;
                    $aData['inserthighlight'] = $inserthighlight;
                    $aData['fnames'] = $fnames;
                    $aData['i'] = $i;
                    $aViewUrls['browseidrow_view'][] = $aData;
                }
            } else {
                Yii::app()->session['flashmessage'] = gT("This response ID is invalid.");
            }

            $aViewUrls[] = 'browseidfooter_view';
            $aData['sidemenu']['state'] = false;
            $aData['menu']['edition'] = true;
            $aData['menu']['view'] = true;
            $aData['menu']['close'] = true;
            // This resets the url on the close button to go to the upper view
            $aData['menu']['closeurl'] = $this->getController()->createUrl("admin/responses/sa/browse/surveyid/".$iSurveyID);

            $this->_renderWrappedTemplate('', $aViewUrls, $aData);
        } else {
            $aData = array();
            $aData['surveyid'] = $iSurveyID;
            $message = array();
            $message['title'] = gT('Access denied!');
            $message['message'] = gT('You do not have permission to access this page.');
            $message['class'] = "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
    }

    public function index($iSurveyID)
    {
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData = $this->_getData($iSurveyID);
        extract($aData);
        $aViewUrls = array();

        /**
         * fnames is used as informational array
         * it containts
         *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
         */
        if (Yii::app()->request->getPost('sql')) {
            $aViewUrls[] = 'browseallfiltered_view';
        }

        $aData['num_total_answers'] = SurveyDynamic::model($iSurveyID)->count();
        $aData['num_completed_answers'] = SurveyDynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
        if ($survey->hasTokensTable && Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')) {
            $aData['with_token'] = Yii::app()->db->schema->getTable($survey->tokensTableName);
            $aData['tokeninfo'] = Token::model($iSurveyID)->summary();
        }

        $aData['menu']['edition'] = false;

        $aViewUrls[] = 'browseindex_view';
        $this->_renderWrappedTemplate('', $aViewUrls, $aData);
    }


    /**
     * Change the value of the max characters to elipsize headers/questions in reponse grid.
     * It's called via ajax request
     */
    public function set_grid_display($displaymode)
    {
        if ($displaymode == 'extended') {
            Yii::app()->user->setState('responsesGridSwitchDisplayState', 'extended');
            Yii::app()->user->setState('defaultEllipsizeHeaderValue', 1000);
            Yii::app()->user->setState('defaultEllipsizeQuestionValue', 1000);
        } else {
            Yii::app()->user->setState('responsesGridSwitchDisplayState', 'compact');
            Yii::app()->user->setState('defaultEllipsizeHeaderValue', Yii::app()->params['defaultEllipsizeHeaderValue']);
            Yii::app()->user->setState('defaultEllipsizeQuestionValue', Yii::app()->params['defaultEllipsizeQuestionValue']);
        }
    }

    /**
     * Show responses for survey
     *
     * @param int $iSurveyId
     * @return void
     */
    public function browse($iSurveyId)
    {
        $survey = Survey::model()->findByPk($iSurveyId);
        $displaymode = Yii::app()->request->getPost('displaymode', null);

        if ($displaymode !== null) {
            $this->set_grid_display($displaymode);
        }

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read')) {
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'listresponse.js', LSYii_ClientScript::POS_BEGIN);
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'tokens.js', LSYii_ClientScript::POS_BEGIN);

            // Basic datas for the view
            $aData                      = $this->_getData($iSurveyId);
            $aData['surveyid']          = $iSurveyId;
            $aData['menu']['edition']   = false;
            $aData['sidemenu']['state'] = false;
            $aData['issuperadmin']      = Permission::model()->hasGlobalPermission('superadmin');
            $aData['hasUpload']         = hasFileUploadQuestion($iSurveyId);
            $aData['fieldmap']          = createFieldMap($survey, 'full', true, false, $aData['language']);
            $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

            ////////////////////
            // Setting the grid

            // Basic variables
            $bHaveToken                 = $survey->anonymized == "N" && tableExists('tokens_'.$iSurveyId) && Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'read'); // Boolean : show (or not) the token
            $aViewUrls                  = array('listResponses_view');
            $model                      = SurveyDynamic::model($iSurveyId);
            $model->bEncryption         = true;


            // Reset filters from stats
            if (Yii::app()->request->getParam('filters') == "reset") {
                Yii::app()->user->setState('sql_'.$iSurveyId, '');
            }

            // Page size
            if (Yii::app()->request->getParam('pageSize')) {
                Yii::app()->user->setState('pageSize', (int) Yii::app()->request->getParam('pageSize'));
            }

            // Model filters
            if (isset($_SESSION['survey_' . $iSurveyId])) {
                $sessionSurveyArray = App()->session->get('survey_' . $iSurveyId);
                $visibleColumns = isset($sessionSurveyArray['filteredColumns']) ? $sessionSurveyArray['filteredColumns'] : null;
                if (!empty($visibleColumns)){
                    $model->setAttributes($visibleColumns,false);
                }

            };
            // Using safe search on dynamic column names would be far too much complex.
            // So we pass over the safe validation and directly set attributes (second parameter of setAttributes to false).
            // see: http://www.yiiframework.com/wiki/161/understanding-safe-validation-rules/
            // see: http://www.yiiframework.com/doc/api/1.1/CModel#setAttributes-detail
            if (Yii::app()->request->getParam('SurveyDynamic')) {
                $model->setAttributes(Yii::app()->request->getParam('SurveyDynamic'), false);
            }

            // Virtual attributes filters
            // Filters on related tables need virtual filters attributes in main model (class variables)
            // Those virtual filters attributes are not set by the setAttributes, they must be set manually
            // @see: http://www.yiiframework.com/wiki/281/searching-and-sorting-by-related-model-in-cgridview/
            $aVirtualFilters = array('completed_filter', 'firstname_filter', 'lastname_filter', 'email_filter');
            foreach ($aVirtualFilters as $sFilterName) {
                $aParam = Yii::app()->request->getParam('SurveyDynamic');
                if (!empty($aParam[$sFilterName])) {
                    $model->$sFilterName = $aParam[$sFilterName];
                }
            }

            // Checks if Columns have been filtered
            $filterableColumnsExist = !empty(isset($_SESSION['survey_' . $iSurveyId]['filteredColumns']) ? $_SESSION['survey_' . $iSurveyId]['filteredColumns'] : null);
            $filteredColumns = [];
            if ($filterableColumnsExist) {
                $filteredColumns = $_SESSION['survey_' . $iSurveyId]['filteredColumns'];
            }
            $aData['filterableColumnsExist'] = $filteredColumns;
            $aData['filteredColumns'] = $filteredColumns;

            // rendering
            $aData['model']             = $model;
            $aData['bHaveToken']        = $bHaveToken;
            $aData['aDefaultColumns']   = $model->defaultColumns; // Some specific columns
            $aData['pageSize']          = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']); // Page size

            $this->_renderWrappedTemplate('responses', $aViewUrls, $aData);
        } else {
                Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
                $this->getController()->redirect(array('admin/survey', 'sa'=>'view', 'surveyid'=>$iSurveyId));
        }

    }

    /**
     * Saves the hidden columns for response browsing in the session
     *
     * @access public
     *
     * @param $surveyid
     */

    public function setFilteredColumns($surveyid)
    {
        if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')) {
            $filteredColumns = [];
            $columns = explode(',', Yii::app()->request->getPost('columns'));
            foreach ($columns as $column){
                if (!empty($column)){
                    $filteredColumns[] = $column;
                }
            }
            $_SESSION['survey_'.$surveyid]['filteredColumns'] = $filteredColumns;
        }
        $this->getController()->redirect(["admin/responses", "sa"=>"browse", "surveyid"=>$surveyid]);

    }


    /**
     * Do an actions on response
     *
     * @access public
     * @param $iSurveyId : survey id
     * @return void
     */
    public function actionResponses($iSurveyId)
    {
        $action = Yii::app()->request->getPost('oper');
        $sResponseId = Yii::app()->request->getPost('id');
        switch ($action) {
            case 'downloadzip':
                $this->actionDownloadfiles($iSurveyId, $sResponseId);
                break;
            case 'del':
                $this->actionDelete($iSurveyId, $sResponseId);
                break;
            default:
                break;
        }
    }

    /**
     * Delete response
     * @access public
     * @param $iSurveyId : survey id
     * @param $sResponseId : list of response
     * @return void
     */
    public function actionDelete($surveyid)
    {
        if(!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        if(!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }
        Yii::import('application.helpers.admin.ajax_helper', true);

        $iSurveyId = (int) $surveyid;
        $ResponseId  = (Yii::app()->request->getPost('sItems') != '') ? json_decode(Yii::app()->request->getPost('sItems')) : json_decode(Yii::app()->request->getParam('sResponseId'), true);
        if (  Yii::app()->request->getPost('modalTextArea') != '' ){
            $ResponseId = explode(',', Yii::app()->request->getPost('modalTextArea'));
            foreach($ResponseId as $key => $sResponseId){
                $ResponseId[$key] = str_replace(' ', '', $sResponseId);
            }
        }

        $aResponseId = (is_array($ResponseId)) ? $ResponseId : array($ResponseId);

        $errors = 0;
        $timingErrors = 0;

        foreach ($aResponseId as $iResponseId) {
            $beforeDataEntryDelete = new PluginEvent('beforeDataEntryDelete');
            $beforeDataEntryDelete->set('iSurveyID', $iSurveyId);
            $beforeDataEntryDelete->set('iResponseID', $iResponseId);
            App()->getPluginManager()->dispatchEvent($beforeDataEntryDelete);

            $response = Response::model($iSurveyId)->findByPk($iResponseId);
            if ($response) {
                $result = $response->delete(true);
                if (!$result) {
                    $errors++;
                } else {
                    $oSurvey = Survey::model()->findByPk($iSurveyId);
                    // TODO : add it to response->delete and response->afterDelete
                    if ($oSurvey->savetimings == "Y") {
                        $result = SurveyTimingDynamic::model($iSurveyId)->deleteByPk($iResponseId);
                        if (!$result) {
                            $timingErrors++;
                        }
                    }
                }
            } else {
                $errors++;
            }
        }

        if ($errors || $timingErrors) {
            $message = ($errors) ? ngT("A response was not deleted.|{n} responses were not deleted.",$errors) : "";
            $message.= ($timingErrors) ? ngT("A timing record was not deleted.|{n} timing records were not deleted.",$errors) : "";
            if(Yii::app()->getRequest()->isAjaxRequest) {
                ls\ajax\AjaxHelper::outputError($message);
            } else {
                Yii::app()->setFlashMessage($message,'error');
                $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$iSurveyId));
            }
        }
        if(Yii::app()->getRequest()->isAjaxRequest) {
            ls\ajax\AjaxHelper::outputSuccess(gT('Response(s) deleted.'));
        }
        Yii::app()->setFlashMessage(gT('Response(s) deleted.'),'success');
        $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$iSurveyId));
    }

    /**
     * Download individual file by response and filename
     *
     * @access public
     * @param $iSurveyId : survey id
     * @param $iResponseId : response if
     * @param $iQID : The question ID
     * @return void
     */
    public function actionDownloadfile($iSurveyId, $iResponseId, $iQID, $iIndex)
    {
        $iIndex = (int) $iIndex;
        $iResponseId = (int) $iResponseId;
        $iQID = (int) $iQID;

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read')) {
            $oResponse = Response::model($iSurveyId)->findByPk($iResponseId);
            $aQuestionFiles = $oResponse->getFiles($iQID);
            if (isset($aQuestionFiles[$iIndex])) {
                $aFile = $aQuestionFiles[$iIndex];
                $sFileRealName = Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyId."/files/".$aFile['filename'];
                if (file_exists($sFileRealName)) {
                    $mimeType = CFileHelper::getMimeType($sFileRealName, null, false);
                    if (is_null($mimeType)) {
                        $mimeType = "application/octet-stream";
                    }
                    @ob_clean();
                    header('Content-Description: File Transfer');
                    header('Content-Type: '.$mimeType);
                    header('Content-Disposition: attachment; filename="'.sanitize_filename(rawurldecode($aFile['name'])).'"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header("Cache-Control: must-revalidate, no-store, no-cache");
                    header('Content-Length: '.filesize($sFileRealName));
                    readfile($sFileRealName);
                    exit;
                }
            }
            Yii::app()->setFlashMessage(gT("Sorry, this file was not found."), 'error');
            $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$iSurveyId));
        }

    }

    /**
     * Construct a zip files from a list of response
     *
     * @access public
     * @param $iSurveyId : survey id
     * @param $sResponseId : list of response
     * @return void application/zip
     * @throws CException
     */
    public function actionDownloadfiles($iSurveyId, $sResponseId)
    {

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read')) {
            if (!$sResponseId) {
                // No response id : get all survey files
                $oCriteria = new CDbCriteria();
                $oCriteria->select = "id";
                $oSurvey = SurveyDynamic::model($iSurveyId);
                $aResponseId = $oSurvey->getCommandBuilder()
                ->createFindCommand($oSurvey->tableSchema, $oCriteria)
                ->queryColumn();
            } else {
                $aResponseId = explode(",", $sResponseId);
            }
            if (!empty($aResponseId)) {
                // Now, zip all the files in the filelist
                if (count($aResponseId) == 1) {
                    $zipfilename = "Files_for_survey_{$iSurveyId}_response_{$aResponseId[0]}.zip";
                } else {
                    $zipfilename = "Files_for_survey_{$iSurveyId}.zip";
                }
                $this->_zipFiles($iSurveyId, $aResponseId, $zipfilename);
            } else {
                // No response : redirect to browse with a alert
                Yii::app()->setFlashMessage(gT("The requested files do not exist on the server."), 'error');
                $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$iSurveyId));
            }
        }
    }

    /**
     * Delete all uploaded files for one response.
     * @return void
     */
    public function actionDeleteAttachments()
    {
        $request     = Yii::app()->request;
        $surveyid    = (int) $request->getParam('surveyid');
        $sid         = (int) $request->getParam('sid');
        $surveyId    = $sid ? $sid : $surveyid;
        $responseId  = (int) $request->getParam('sResponseId');
        $stringItems = json_decode($request->getPost('sItems'));
        // Cast all ids to int.
        $items       = array_map(
            function($id)
            {
                return (int) $id;
            },
            is_array($stringItems) ? $stringItems : array()
        );
        $responseIds = $responseId ? array($responseId) : $items;
        if(!Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        if(!$request->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }
        Yii::import('application.helpers.admin.ajax_helper', true);
        $allErrors = array();
        $allSuccess = 0;

        foreach ($responseIds as $responseId) {
            $response = Response::model($surveyId)->findByPk($responseId);
            if (!empty($response)) {
                list($success, $errors) = $response->deleteFilesAndFilename();
                if (empty($errors)) {
                    $allSuccess += $success;
                } else {
                    // Could not delete all files.
                    $allErrors = array_merge($allErrors, $errors);
                }
            } else {
                $allErrors[] = sprintf(gT('Found no response with ID %d'), $responseId);
            }
        }
        if (!empty($allErrors)) {
            $message = gT('Error: Could not delete some files: ').implode(', ', $allErrors);
            if($request->isAjaxRequest) {
                ls\ajax\AjaxHelper::outputError(
                    $message
                );
                Yii::app()->end();
            }
            Yii::app()->setFlashMessage($message,'error');
            $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$surveyId));
        }
        $message = sprintf(ngT('%d file deleted.|%d files deleted.', $allSuccess), $allSuccess);
        if($request->isAjaxRequest) {
            ls\ajax\AjaxHelper::outputSuccess($message);
            Yii::app()->end();
        }
        Yii::app()->setFlashMessage($message,'success');
        $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$surveyId));
    }

    /**
     * Time statistics for responses
     *
     * @param int $iSurveyID
     * @return void
     */
    public function time($iSurveyID)
    {
        $aData = $this->_getData(array('iSurveyId' => $iSurveyID));
        $survey = Survey::model()->findByPk($iSurveyID);

        /*
        extract($aData);
        $aViewUrls = array();

        if ($survey->savetimings != "Y")
            die();

        if (Yii::app()->request->getPost('deleteanswer') && Yii::app()->request->getPost('deleteanswer') != '' && Yii::app()->request->getPost('deleteanswer') != 'marked'
        && Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'delete'))
        {
            $iResponseID=(int) Yii::app()->request->getPost('deleteanswer');
            SurveyDynamic::model($iSurveyID)->deleteByPk($iResponseID);
            SurveyTimingDynamic::model($iSurveyID)->deleteByPk($iResponseID);
        }

        if (Yii::app()->request->getPost('markedresponses') && count(Yii::app()->request->getPost('markedresponses')) > 0)
        {
            if (Yii::app()->request->getPost('deleteanswer') && Yii::app()->request->getPost('deleteanswer') === 'marked' &&
            Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'delete'))
            {
                foreach (Yii::app()->request->getPost('markedresponses') as $iResponseID)
                {
                    $iResponseID=(int) $iResponseID;
                    SurveyDynamic::model($iSurveyID)->deleteByPk($iResponseID);
                    SurveyTimingDynamic::model($iSurveyID)->deleteByPk($iResponseID);
                }
            }
        }
        */

        $aData['columns'] = array(
            array(
                'header' => gT('ID'),
                'name' => 'id',
                'value'=> '$data->id',
                'headerHtmlOptions' => array('class' => 'hidden-xs'),
                'htmlOptions' => array('class' => 'hidden-xs')
            ),
            array(
                'header' => gT('Total time'),
                'name' => 'interviewtime',
                'value' => '$data->interviewtime'
            )
        );

        $fields = createTimingsFieldMap($iSurveyID, 'full', true, false, $aData['language']);
        foreach ($fields as $fielddetails) {
            // headers for answer id and time data
            if ($fielddetails['type'] == 'id') {
                $fnames[] = array($fielddetails['fieldname'], $fielddetails['question']);
            }

            if ($fielddetails['type'] == 'interview_time') {
                $fnames[] = array($fielddetails['fieldname'], gT('Total time'));
            }

            if ($fielddetails['type'] == 'page_time') {
                $fnames[] = array($fielddetails['fieldname'], gT('Group').": ".$fielddetails['group_name']);
                $aData['columns'][] = array(
                    'header' => gT('Group: ').$fielddetails['group_name'],
                    'name' => $fielddetails['fieldname']
                );
            }

            if ($fielddetails['type'] == 'answer_time') {
                $fnames[] = array($fielddetails['fieldname'], gT('Question').": ".$fielddetails['title']);
                $aData['columns'][] = array(
                    'header' => gT('Question: ').$fielddetails['title'],
                    'name' => $fielddetails['fieldname']
                );
            }
        }
        $fncount = count($fnames);

        /*
        //NOW LETS CREATE A TABLE WITH THOSE HEADINGS
        foreach ($fnames as $fn)
        {
            if (!isset($currentgroup))
            {
                $currentgroup = $fn[1];
                $gbc = "oddrow";
            }
            if ($currentgroup != $fn[1])
            {
                $currentgroup = $fn[1];
                if ($gbc == "oddrow")
                {
                    $gbc = "evenrow";
                }
                else
                {
                    $gbc = "oddrow";
                }
            }
        }
        $aData['fnames'] = $fnames;
        $start = Yii::app()->request->getParam('start', 0);
        $limit = Yii::app()->request->getParam('limit', 50);
        if(!$limit){$limit=50;}
        //LETS COUNT THE DATA
        $oCriteria = new CdbCriteria();
        $oCriteria->select = 'tid';
        $oCriteria->join = "INNER JOIN {{survey_{$iSurveyID}}} s ON t.id=s.id";
        $oCriteria->condition = 'submitdate IS NOT NULL';
        $dtcount = SurveyTimingDynamic::model($iSurveyID)->count($oCriteria); // or die("Couldn't get response data");

        if ($limit > $dtcount)
        {
            $limit = $dtcount;
        }

        //NOW LETS SHOW THE DATA
        $oCriteria = new CdbCriteria();
        $oCriteria->join = "INNER JOIN {{survey_{$iSurveyID}}} s ON t.id=s.id";
        $oCriteria->condition = 'submitdate IS NOT NULL';
        $oCriteria->order = "s.id " . (Yii::app()->request->getParam('order') == 'desc' ? 'desc' : 'asc');
        $oCriteria->offset = $start;
        $oCriteria->limit = $limit;

        $dtresult = SurveyTimingDynamic::model($iSurveyID)->findAllAsArray($oCriteria);

        //CONTROL MENUBAR
        $last = $start - $limit;
        $next = $start + $limit;
        $end = $dtcount - $limit;
        if ($end < 0)
        {
            $end = 0;
        }
        if ($last < 0)
        {
            $last = 0;
        }
        if ($next >= $dtcount)
        {
            $next = $dtcount - $limit;
        }
        if ($end < 0)
        {
            $end = 0;
        }

        $aData['sCompletionStateValue']=incompleteAnsFilterState();
        $aData['start'] = $start;
        $aData['limit'] = $limit;
        $aData['last'] = $last;
        $aData['next'] = $next;
        $aData['end'] = $end;
        */
        $aViewUrls[] = 'browsetimeheader_view';
        /*

        $aData['fncount'] = $fncount;
        $bgcc = 'oddrow';

        foreach ($dtresult as $dtrow)
        {
            if ($bgcc == "evenrow")
            {
                $bgcc = "oddrow";
            }
            else
            {
                $bgcc = "evenrow";
            }
            $browsedatafield=array();
            for ($i = 0; $i < $fncount; $i++)
            {
                $browsedatafield[$i] = $dtrow[$fnames[$i][0]];
                // seconds -> minutes & seconds
                if (strtolower(substr($fnames[$i][0], -4)) == "time")
                {
                    $minutes = (int) ($browsedatafield[$i] / 60);
                    $seconds = $browsedatafield[$i] % 60;
                    $browsedatafield[$i] = '';
                    if ($minutes > 0)
                        $browsedatafield[$i] .= "$minutes min ";
                    $browsedatafield[$i] .= "$seconds s";
                }
            }
            $aData['browsedatafield'] = $browsedatafield;
            $aData['bgcc'] = $bgcc;
            $aData['dtrow'] = $dtrow;
            */
            $aViewUrls['browsetimerow_view'][] = $aData;
            /*
        }
        */

        // Set number of page
        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->request->getParam('pageSize'));
        }


        //interview Time statistics
        $aData['model'] = SurveyTimingDynamic::model($iSurveyID);
        $aData['menu']['edition'] = false;

        $aData['pageSize'] = 10;
        $aData['statistics'] = SurveyTimingDynamic::model($iSurveyID)->statistics();
        $aData['num_total_answers'] = SurveyDynamic::model($iSurveyID)->count();
        $aData['num_completed_answers'] = SurveyDynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
        $aViewUrls[] = 'browsetimefooter_view';
        $this->_renderWrappedTemplate('', $aViewUrls, $aData);
    }

    /**
     * Supply an array with the responseIds and all files will be added to the zip
     * and it will be be spit out on success
     *
     * @param int $iSurveyID
     * @param array $responseIds
     * @param string $zipfilename
     * @return ZipArchive
     */
    private function _zipFiles($iSurveyID, $responseIds, $zipfilename)
    {
        /**
         * @todo Move this to model.
         */
        Yii::app()->loadLibrary('admin/pclzip');

        $tmpdir = Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR."surveys".DIRECTORY_SEPARATOR.$iSurveyID.DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR;

        $filelist = array();
        $responses = Response::model($iSurveyID)->findAllByPk($responseIds);
        $filecount = 0;
        foreach ($responses as $response) {
            foreach ($response->getFiles() as $fileInfo) {
                $filecount++;
                /*
                * Now add the file to the archive, prefix files with responseid_index to keep them
                * unique. This way we can have 234_1_image1.gif, 234_2_image1.gif as it could be
                * files from a different source with the same name.
                */
                if (file_exists($tmpdir.basename($fileInfo['filename']))) {
                    $filelist[] = array(
                        PCLZIP_ATT_FILE_NAME => $tmpdir.basename($fileInfo['filename']),
                        PCLZIP_ATT_FILE_NEW_FULL_NAME => sprintf("%05s_%02s-%s_%02s-%s", $response->id, $filecount, $fileInfo['question']['title'],$fileInfo['index'], sanitize_filename(rawurldecode($fileInfo['name'])))
                    );
                }
            }
        }

        if (count($filelist) > 0) {
            $zip = new PclZip($tmpdir.$zipfilename);
            if ($zip->create($filelist) === 0) {
                //Oops something has gone wrong!
            }

            if (file_exists($tmpdir.'/'.$zipfilename)) {
                @ob_clean();
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip, application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($zipfilename));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header("Cache-Control: must-revalidate, no-store, no-cache");
                header('Content-Length: '.filesize($tmpdir."/".$zipfilename));
                readfile($tmpdir.'/'.$zipfilename);
                unlink($tmpdir.'/'.$zipfilename);
                exit;
            }
        }
        // No files : redirect to browse with a alert
        Yii::app()->setFlashMessage(gT("Sorry, there are no files for this response."), 'error');
        $this->getController()->redirect(array("admin/responses", "sa"=>"browse", "surveyid"=>$iSurveyID));
    }



    /**
     * Responsible for setting the session variables for attribute map page redirect
     * @todo Use user session?
     * @todo Used?
     */
    public function setSession($unset = false, $sid = null)
    {
        if (!$unset) {
            unset(Yii::app()->session['responsesid']);
            Yii::app()->session['responsesid'] = Yii::app()->request->getPost('itemsid');
        } else {
            unset(Yii::app()->session['responsesid']);
            $this->getController()->redirect(array("admin/export", "sa"=>"exportresults", "surveyid"=>$sid));
        }

    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        // App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'browse.js');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl').'browse.css');

        $iSurveyId = $aData['iSurveyId'];
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $aData['display']['menu_bars'] = false;
        $aData['subaction'] = gT("Responses and statistics");
        $aData['display']['menu_bars']['browse'] = gT('Browse responses'); // browse is independent of the above
        $aData['title_bar']['title'] = gT('Browse responses').': '.$oSurvey->currentLanguageSettings->surveyls_title;
        parent::_renderWrappedTemplate('responses', $aViewUrls, $aData);
    }

}
