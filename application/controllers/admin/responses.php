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
 */

/**
 * Responses Controller
 *
 * This controller performs browse actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class responses extends Survey_Common_Action
{

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('surveytranslator');
    }

    private function _getData($params)
    {
        if (is_numeric($params))
        {
            $iSurveyId = $params;
        }
        elseif (is_array($params))
        {
            extract($params);
        }
        $aData = array();
        // Set the variables in an array
        $aData['surveyid'] = $aData['iSurveyId'] = (int) $iSurveyId;
        if (!empty($iId))
        {
            $aData['iId'] = (int) $iId;
        }
        $aData['clang'] = $clang = $this->getController()->lang;
        $aData['imageurl'] = Yii::app()->getConfig('imageurl');
        $aData['action'] = Yii::app()->request->getParam('action');
        $aData['all']=Yii::app()->request->getParam('all');
        $thissurvey=getSurveyInfo($iSurveyId);
        if(!$thissurvey)// Already done in Survey_Common_Action
        {
            Yii::app()->session['flashmessage'] = $clang->gT("Invalid survey ID");
            $this->getController()->redirect(array("admin/index"));
        }
        elseif($thissurvey['active'] != 'Y')
        {
            Yii::app()->session['flashmessage'] = $clang->gT("This survey has not been activated. There are no results to browse.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

        $aData['surveyinfo'] = $thissurvey;

        if (Yii::app()->request->getParam('browselang'))
        {
            $aData['language'] = Yii::app()->request->getParam('browselang');
            $aData['languagelist'] = $languagelist = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            $aData['languagelist'][] = Survey::model()->findByPk($iSurveyId)->language;
            if (!in_array($aData['language'], $languagelist))
            {
                $aData['language'] = $thissurvey['language'];
            }
        }
        else
        {
            $aData['language'] = $thissurvey['language'];
        }

        $aData['qulanguage'] = Survey::model()->findByPk($iSurveyId)->language;

        $aData['surveyoptions'] = '';
        $aData['browseoutput']  = '';

        return $aData;
    }

    public function view($iSurveyID, $iId, $sBrowseLang = '')
    {
        if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','read'))
        {
            $aData = $this->_getData(array('iId' => $iId, 'iSurveyId' => $iSurveyID, 'browselang' => $sBrowseLang));
            $oBrowseLanguage = new Limesurvey_lang($aData['language']);

            extract($aData);
            $clang = Yii::app()->lang;
            $aViewUrls = array();

            $fncount = 0;
            $fieldmap = createFieldMap($iSurveyID, 'full', false, false, $aData['language']);

            //add token to top of list if survey is not private
            if ($aData['surveyinfo']['anonymized'] == "N" && tableExists('tokens_' . $iSurveyID) && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read'))
            {
                $fnames[] = array("token", $clang->gT("Token ID"), 'code'=>'token');
                $fnames[] = array("firstname", $clang->gT("First name"), 'code'=>'firstname');// or token:firstname ?
                $fnames[] = array("lastname", $clang->gT("Last name"), 'code'=>'lastname');
                $fnames[] = array("email", $clang->gT("Email"), 'code'=>'email');
            }
            $fnames[] = array("submitdate", $clang->gT("Submission date"), $clang->gT("Completed"), "0", 'D','code'=>'submitdate');
            $fnames[] = array("completed", $clang->gT("Completed"), "0");

            foreach ($fieldmap as $field)
            {
                if ($field['fieldname'] == 'lastpage' || $field['fieldname'] == 'submitdate')
                    continue;
                if ($field['type'] == 'interview_time')
                    continue;
                if ($field['type'] == 'page_time')
                    continue;
                if ($field['type'] == 'answer_time')
                    continue;

                //$question = $field['question'];
                $question = viewHelper::getFieldText($field);

                if ($field['type'] != "|")
                {
                    $fnames[] = array($field['fieldname'], viewHelper::getFieldText($field),'code'=>viewHelper::getFieldCode($field,array('LEMcompat'=>true)));
                }
                elseif ($field['aid'] !== 'filecount')
                {
                    $qidattributes = getQuestionAttributeValues($field['qid']);

                    for ($i = 0; $i < $qidattributes['max_num_of_files']; $i++)
                    {
                        $filenum=sprintf($clang->gT("File %s"),$i + 1);
                        if ($qidattributes['show_title'] == 1)
                            $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".$clang->gT('Title').")",'code'=>viewHelper::getFieldCode($field).'(title)', "type" => "|", "metadata" => "title", "index" => $i);

                        if ($qidattributes['show_comment'] == 1)
                            $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".$clang->gT('Comment').")",'code'=>viewHelper::getFieldCode($field).'(comment)', "type" => "|", "metadata" => "comment", "index" => $i);

                        $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".$clang->gT('File name').")",'code'=>viewHelper::getFieldCode($field).'(name)', "type" => "|", "metadata" => "name", "index" => $i);
                        $fnames[] = array($field['fieldname'], "{$filenum} - {$question} (".$clang->gT('File size').")",'code'=>viewHelper::getFieldCode($field).'(size)', "type" => "|", "metadata" => "size", "index" => $i);

                        //$fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
                    }
                }
                else
                {
                    $fnames[] = array($field['fieldname'], $clang->gT("File count"));
                }
            }

            $nfncount = count($fnames) - 1;
            if ($iId < 1)
            {
                $iId = 1;
            }

            $exist = SurveyDynamic::model($iSurveyID)->exist($iId);
            $next = SurveyDynamic::model($iSurveyID)->next($iId,true);
            $previous = SurveyDynamic::model($iSurveyID)->previous($iId,true);
            $aData['exist'] = $exist;
            $aData['next'] = $next;
            $aData['previous'] = $previous;
            $aData['id'] = $iId;

            $aViewUrls[] = 'browseidheader_view';
            if($exist)
            {
                $oPurifier=new CHtmlPurifier();
                //SHOW INDIVIDUAL RECORD
                $oCriteria = new CDbCriteria();
                if ($aData['surveyinfo']['anonymized'] == 'N' && tableExists("{{tokens_$iSurveyID}}}") && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read'))
                {
                    $oCriteria = SurveyDynamic::model($iSurveyID)->addTokenCriteria($oCriteria);
                }
                // If admin ask an specific response, then show it
                // Don't add incompleteAnsFilterState
    #            if (incompleteAnsFilterState() == 'incomplete')
    #                $oCriteria->addCondition('submitdate = ' . mktime(0, 0, 0, 1, 1, 1980) . ' OR submitdate IS NULL');
    #            elseif (incompleteAnsFilterState() == 'complete')
    #                $oCriteria->addCondition('submitdate >= ' . mktime(0, 0, 0, 1, 1, 1980));
                $oCriteria->addCondition("id = {$iId}");
                $iIdresult = SurveyDynamic::model($iSurveyID)->findAllAsArray($oCriteria);
                foreach ($iIdresult as $iIdrow)
                {
                    $iId = $iIdrow['id'];
                    $rlanguage = $iIdrow['startlanguage'];
                }
                $next = SurveyDynamic::model($iSurveyID)->next($iId);
                $previous = SurveyDynamic::model($iSurveyID)->previous($iId);

                if (isset($rlanguage))
                {
                    $aData['rlanguage'] = $rlanguage;
                }
                foreach ($iIdresult as $iIdrow)
                {
                    $highlight = false;
                    for ($i = 0; $i < $nfncount + 1; $i++)
                    {
                        if ($fnames[$i][0] != 'completed' && is_null($iIdrow[$fnames[$i][0]]))
                        {
                            continue;   // irrelevant, so don't show
                        }
                        $inserthighlight = '';
                        if ($highlight)
                            $inserthighlight = "class='highlight'";

                        if ($fnames[$i][0] == 'completed')
                        {
                            if ($iIdrow['submitdate'] == NULL || $iIdrow['submitdate'] == "N")
                            {
                                $answervalue = "N";
                            }
                            else
                            {
                                $answervalue = "Y";
                            }
                        }
                        else
                        {
                            if (isset($fnames[$i]['type']) && $fnames[$i]['type'] == "|")
                            {
                                $index = $fnames[$i]['index'];
                                $metadata = $fnames[$i]['metadata'];
                                $phparray = json_decode_ls($iIdrow[$fnames[$i][0]]);

                                if (isset($phparray[$index]))
                                {
                                    if ($metadata === "size")
                                        $answervalue = rawurldecode(((int) ($phparray[$index][$metadata])) . " KB");
                                    else if ($metadata === "name")
                                        $answervalue = CHtml::link($oPurifier->purify(rawurldecode($phparray[$index][$metadata])), $this->getController()->createUrl("/admin/responses/sa/browse/fieldname/{$fnames[$i][0]}/id/{$iId}/surveyid/{$iSurveyID}",array('downloadindividualfile'=>$phparray[$index][$metadata])));
                                    else
                                        $answervalue = rawurldecode($phparray[$index][$metadata]);
                                }
                                else
                                    $answervalue = "";
                            }
                            else
                            {
                                $answervalue = htmlspecialchars(strip_tags(stripJavaScript(getExtendedAnswer($iSurveyID, $fnames[$i][0], $iIdrow[$fnames[$i][0]], $oBrowseLanguage))), ENT_QUOTES);
                            }
                        }
                        $aData['answervalue'] = $answervalue;
                        $aData['inserthighlight'] = $inserthighlight;
                        $aData['fnames'] = $fnames;
                        $aData['i'] = $i;
                        $aViewUrls['browseidrow_view'][] = $aData;
                    }
                }
            }
            else
            {
                Yii::app()->session['flashmessage'] = $clang->gT("This response ID is invalid.");
            }

            $aViewUrls[] = 'browseidfooter_view';

            $this->_renderWrappedTemplate('',$aViewUrls, $aData);
        }
        else
        {
            $clang = $this->getController()->lang;
            $aData['surveyid'] = $iSurveyID;
            App()->getClientScript()->registerPackage('jquery-superfish');
            $message['title']= $clang->gT('Access denied!');
            $message['message']= $clang->gT('You do not have sufficient rights to access this page.');
            $message['class']= "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
    }

    public function index($iSurveyID)
    {
        $aData = $this->_getData($iSurveyID);
        extract($aData);
        $aViewUrls = array();
        $oBrowseLanguage = new Limesurvey_lang($aData['language']);

        /**
         * fnames is used as informational array
         * it containts
         *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
         */
        if (Yii::app()->request->getPost('sql'))
        {
            $aViewUrls[] = 'browseallfiltered_view';
        }

            $clang = $aData['clang'];
            $aData['num_total_answers'] = SurveyDynamic::model($iSurveyID)->count();
            $aData['num_completed_answers'] = SurveyDynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
            if (tableExists('{{tokens_' . $iSurveyID . '}}') && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read'))
            {
                $aData['with_token']= Yii::app()->db->schema->getTable('{{tokens_' . $iSurveyID . '}}');
				$aData['tokeninfo'] = Token::model($iSurveyID)->summary();
            }

            $aViewUrls[] = 'browseindex_view';
            $this->_renderWrappedTemplate('',$aViewUrls, $aData);
    }


    function browse($iSurveyID)
    {
        $aData = $this->_getData($iSurveyID);
        extract($aData);
        $aViewUrls = array();
        $oBrowseLanguage = new Limesurvey_lang($aData['language']);

        $tokenRequest = Yii::app()->request->getParam('token', null);

        //Delete Individual answer using inrow delete buttons/links - checked
        if (Yii::app()->request->getPost('deleteanswer') && Yii::app()->request->getPost('deleteanswer') != '' && Yii::app()->request->getPost('deleteanswer') != 'marked')
        {
            if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','delete'))
            {
                $iResponseID = (int) Yii::app()->request->getPost('deleteanswer'); // sanitize the value
                Response::model($iSurveyID)->findByPk($iResponseID)->delete(true);
                // delete timings if savetimings is set
                if($aData['surveyinfo']['savetimings'] == "Y"){
                    SurveyTimingDynamic::model($iSurveyID)->deleteByPk($iResponseID);
                }
                Yii::app()->session['flashmessage'] = sprintf(gT("Response ID %s was successfully deleted."),$iResponseID);
            }
            else
            {
                Yii::app()->session['flashmessage'] = gT("Access denied!",'js');
            }
        }
        // Marked responses -> deal with the whole batch of marked responses
        if (Yii::app()->request->getPost('markedresponses') && count(Yii::app()->request->getPost('markedresponses')) > 0)
        {
            // Delete the marked responses - checked
            if (Yii::app()->request->getPost('deleteanswer') && Yii::app()->request->getPost('deleteanswer') === 'marked')
            {
                if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','delete'))
                {
                    foreach (Response::model($iSurveyID)->findAllByPk(Yii::app()->request->getPost('markedresponses')) as $response)
                    {
                        $response->deleteFiles();
                        // delete timings if savetimings is set
                        /**
                         * @todo Move this to the Response model.
                         */
                        if($aData['surveyinfo']['savetimings'] == "Y"){
                            SurveyTimingDynamic::model($iSurveyID)->deleteByPk($iResponseID);
                        }
                    }

                    Response::model($iSurveyID)->deleteByPk(Yii::app()->request->getPost('markedresponses'));


                    Yii::app()->session['flashmessage'] = sprintf(ngT("%s response was successfully deleted.","%s responses were successfully deleted.",count(Yii::app()->request->getPost('markedresponses'))),count(Yii::app()->request->getPost('markedresponses')),'js');
                }
                else
                {
                    Yii::app()->session['flashmessage'] = $clang->gT("Access denied!",'js');
                }
            }
            // Download all files for all marked responses  - checked
            elseif (Yii::app()->request->getPost('downloadfile') && Yii::app()->request->getPost('downloadfile') === 'marked')
            {
                if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','read'))
                {
                    // Now, zip all the files in the filelist
                    $zipfilename = "Responses_for_survey_{$iSurveyID}.zip";
                    $this->_zipFiles($iSurveyID, Yii::app()->request->getPost('markedresponses'), $zipfilename);
                }
            }
        }
        // Download all files for this entry - checked
        elseif (Yii::app()->request->getPost('downloadfile') && Yii::app()->request->getPost('downloadfile') != '' && Yii::app()->request->getPost('downloadfile') !== true)
        {
            if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','read'))
            {
                // Now, zip all the files in the filelist
                $zipfilename = "Files_for_responses_" . Yii::app()->request->getPost('downloadfile') . ".zip";
                $this->_zipFiles($iSurveyID, Yii::app()->request->getPost('downloadfile'), $zipfilename);
            }
        }
        elseif (Yii::app()->request->getParam('downloadindividualfile') != '')
        {
            if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','read'))
            {
                $iId = (int) Yii::app()->request->getParam('id');
                $downloadindividualfile = Yii::app()->request->getParam('downloadindividualfile');
                $fieldname = Yii::app()->request->getParam('fieldname');

                $oRow = SurveyDynamic::model($iSurveyID)->findByAttributes(array('id' => $iId));
                $phparray = json_decode_ls($oRow->$fieldname);

                for ($i = 0; $i < count($phparray); $i++)
                {
                    if (rawurldecode($phparray[$i]['name']) == rawurldecode($downloadindividualfile))
                    {
                        $file = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files/" . $phparray[$i]['filename'];

                        if (file_exists($file))
                        {
                            @ob_clean();
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename="' . rawurldecode($phparray[$i]['name']) . '"');
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($file));
                            readfile($file);
                            exit;
                        }
                        break;
                    }
                }
            }
        }

        /**
         * fnames is used as informational array
         * it containts
         *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
         */
        if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','read'))
        {
            if (Yii::app()->request->getPost('sql'))
            {
                $aViewUrls[] = 'browseallfiltered_view';
            }
            //add token to top of list if survey is not private
            if ($aData['surveyinfo']['anonymized'] == "N" && tableExists('tokens_' . $iSurveyID) ) //add token to top of list if survey is not private
            {
                if(Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read'))
                {
                    $fnames[] = array("token", $clang->gT("Token ID"), 'code'=>'token');
                    $fnames[] = array("firstname", $clang->gT("First name"), 'code'=>'firstname');// or token:firstname ?
                    $fnames[] = array("lastname", $clang->gT("Last name"), 'code'=>'lastname');
                    $fnames[] = array("email", $clang->gT("Email"), 'code'=>'email');
                }
            }

            $fnames[] = array("submitdate", $clang->gT("Completed"), $clang->gT("Completed"), "0", 'D');
            $fields = createFieldMap($iSurveyID, 'full', false, false, $aData['language']);

            foreach ($fields as $fielddetails)
            {
                if ($fielddetails['fieldname'] == 'lastpage' || $fielddetails['fieldname'] == 'submitdate')
                    continue;

                $question = $fielddetails['question'];
                if ($fielddetails['type'] != "|")
                {
                    if ($fielddetails['fieldname'] == 'lastpage' || $fielddetails['fieldname'] == 'submitdate' || $fielddetails['fieldname'] == 'token')
                        continue;

                    // no headers for time data
                    if ($fielddetails['type'] == 'interview_time')
                        continue;
                    if ($fielddetails['type'] == 'page_time')
                        continue;
                    if ($fielddetails['type'] == 'answer_time')
                        continue;
                    $fnames[] = array($fielddetails['fieldname'], viewHelper::getFieldText($fielddetails),'code'=>viewHelper::getFieldCode($fielddetails,array('LEMcompat'=>true)));
                }
                elseif ($fielddetails['aid'] !== 'filecount')
                {
                    $qidattributes = getQuestionAttributeValues($fielddetails['qid']);
                    for ($i = 0; $i < $qidattributes['max_num_of_files']; $i++)
                    {
                        $filenum=sprintf($clang->gT("File %s"),$i + 1);
                        if ($qidattributes['show_title'] == 1)
                            $fnames[] = array($fielddetails['fieldname'], "{$filenum} - {$question} (".$clang->gT('Title').")",'code'=>viewHelper::getFieldCode($fielddetails).'(title)', "type" => "|", "metadata" => "title", "index" => $i);
                        if ($qidattributes['show_comment'] == 1)
                            $fnames[] = array($fielddetails['fieldname'], "{$filenum} - {$question} (".$clang->gT('Comment').")",'code'=>viewHelper::getFieldCode($fielddetails).'(comment)', "type" => "|", "metadata" => "comment", "index" => $i);

                        $fnames[] = array($fielddetails['fieldname'], "{$filenum} - {$question} (".$clang->gT('File name').")",'code'=>viewHelper::getFieldCode($fielddetails).'(name)', "type" => "|", "metadata" => "name", "index" => $i);
                        $fnames[] = array($fielddetails['fieldname'], "{$filenum} - {$question} (".$clang->gT('File size').")",'code'=>viewHelper::getFieldCode($fielddetails).'(size)', "type" => "|", "metadata" => "size", "index" => $i);

                        //$fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
                    }
                }
                else
                {
                    $fnames[] = array($fielddetails['fieldname'], $clang->gT("File count"), 'code'=>viewHelper::getFieldCode($fielddetails));
                }
            }

            $fncount = count($fnames);

            $start = (int)Yii::app()->request->getParam('start', 0);
            $limit = (int)Yii::app()->request->getParam('limit', 50);
            $order =  Yii::app()->request->getParam('order', 'asc');
            if(!$limit){$limit=50;}
            $oCriteria = new CDbCriteria;
            //Create the query
            if ($aData['surveyinfo']['anonymized'] == "N" && tableExists("{{tokens_{$iSurveyID}}}") && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read'))
            {
                $oCriteria = SurveyDynamic::model($iSurveyID)->addTokenCriteria($oCriteria);
            }

            if (incompleteAnsFilterState() == "incomplete")
            {
                $oCriteria->addCondition("submitdate IS NULL");
            }
            elseif (incompleteAnsFilterState() == "complete")
            {
                $oCriteria->addCondition("submitdate IS NOT NULL");
            }

            $dtcount = SurveyDynamic::model($iSurveyID)->count($oCriteria);// or die("Couldn't get response data<br />");

            if ($limit > $dtcount)
            {
                $limit = $dtcount;
            }

            //NOW LETS SHOW THE DATA
            if (Yii::app()->request->getPost('sql') && stripcslashes(Yii::app()->request->getPost('sql')) !== "" && Yii::app()->request->getPost('sql') != "NULL")
                $oCriteria->addCondition(stripcslashes(Yii::app()->request->getPost('sql')));

            if (!is_null($tokenRequest)) {
                $oCriteria->addCondition('t.token = ' . Yii::app()->db->quoteValue($tokenRequest));
            }

            $oCriteria->order = 'id ' . ($order == 'desc' ? 'desc' : 'asc');
            $oCriteria->offset = $start;
            $oCriteria->limit = $limit;

            $dtresult = SurveyDynamic::model($iSurveyID)->findAllAsArray($oCriteria);

            $dtcount2 = count($dtresult);
            $cells = $fncount + 1;
            // Fix start if order is desc, only if actual start is 0
            if($order == 'desc' && $start==0)
            {
                $start=$dtcount-count($dtresult);
            }

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

            $aData['dtcount2'] = $dtcount2;
            $aData['sCompletionStateValue']=incompleteAnsFilterState();

            $aData['start'] = $start;
            $aData['limit'] = $limit;
            $aData['last'] = $last;
            $aData['next'] = $next;
            $aData['end'] = $end;
            $aData['fncount'] = $fncount;
            $aData['fnames'] = $fnames;
            $aData['bHasFileUploadQuestion'] = hasFileUploadQuestion($iSurveyID);

            $aViewUrls[] = 'browseallheader_view';

            $bgcc = 'even';
            foreach ($dtresult as $dtrow)
            {
                    if ($bgcc == "even")
                    {
                        $bgcc = "odd";
                    }
                    else
                    {
                        $bgcc = "even";
                    }
                $aData['dtrow'] = $dtrow;
                $aData['bgcc'] = $bgcc;
                $aData['oBrowseLanguage']=$oBrowseLanguage;
                $aViewUrls['browseallrow_view'][] = $aData;
            }

            $aViewUrls[] = 'browseallfooter_view';
            $this->_renderWrappedTemplate('',$aViewUrls, $aData);
        }
        else
        {
            $clang = $this->getController()->lang;
            $aData['surveyid'] = $iSurveyID;
            App()->getClientScript()->registerPackage('jquery-superfish');
            $message['title']= $clang->gT('Access denied!');
            $message['message']= $clang->gT('You do not have sufficient rights to access this page.');
            $message['class']= "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
    }

    public function time($iSurveyID)
    {
        $aData = $this->_getData(array('iSurveyId' => $iSurveyID));
        extract($aData);
        $aViewUrls = array();

        if ($aData['surveyinfo']['savetimings'] != "Y")
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

        $fields = createTimingsFieldMap($iSurveyID, 'full',true,false,$aData['language']);

        $clang = $aData['clang'];
        foreach ($fields as $fielddetails)
        {
            // headers for answer id and time data
            if ($fielddetails['type'] == 'id')
                $fnames[] = array($fielddetails['fieldname'], $fielddetails['question']);
            if ($fielddetails['type'] == 'interview_time')
                $fnames[] = array($fielddetails['fieldname'], $clang->gT('Total time'));
            if ($fielddetails['type'] == 'page_time')
                $fnames[] = array($fielddetails['fieldname'], $clang->gT('Group') . ": " . $fielddetails['group_name']);
            if ($fielddetails['type'] == 'answer_time')
                $fnames[] = array($fielddetails['fieldname'], $clang->gT('Question') . ": " . $fielddetails['title']);
        }
        $fncount = count($fnames);

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
        $dtcount2 = count($dtresult);
        $cells = $fncount + 1;

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
        $aViewUrls[] = 'browsetimeheader_view';

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
            $aViewUrls['browsetimerow_view'][] = $aData;
        }

        //interview Time statistics
        $aData['statistics'] = SurveyTimingDynamic::model($iSurveyId)->statistics();
        $aData['num_total_answers'] = SurveyDynamic::model($iSurveyID)->count();
        $aData['num_completed_answers'] = SurveyDynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
        $aViewUrls[] = 'browsetimefooter_view';
        $this->_renderWrappedTemplate('',$aViewUrls, $aData);
    }

    /**
     * Supply an array with the responseIds and all files will be added to the zip
     * and it will be be spit out on success
     *
     * @param array $responseIds
     * @param string $zipfilename
     * @param string $language
     * @return ZipArchive
     */
    private function _zipFiles($iSurveyID, $responseIds, $zipfilename)
    {
        /**
         * @todo Move this to model.
         */
        Yii::app()->loadLibrary('admin/pclzip');

        $tmpdir = Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR."surveys". DIRECTORY_SEPARATOR . $iSurveyID . DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR;

        $filelist = array();
        $responses = Response::model($iSurveyID)->findAllByPk($responseIds);
        $filecount = 0;
        foreach ($responses as $response)
        {
            foreach ($response->getFiles() as $file)
            {
                $filecount++;
                /*
                 * Now add the file to the archive, prefix files with responseid_index to keep them
                 * unique. This way we can have 234_1_image1.gif, 234_2_image1.gif as it could be
                 * files from a different source with the same name.
                 */
                 if (file_exists($tmpdir . basename($file['filename'])))
                 {
                    $filelist[] = array(PCLZIP_ATT_FILE_NAME => $tmpdir . basename($file['filename']),
                        PCLZIP_ATT_FILE_NEW_FULL_NAME => sprintf("%05s_%02s_%s", $response->id, $filecount, rawurldecode($file['name'])));
                 }
            }
        }

        if (count($filelist) > 0)
        {
            $zip = new PclZip($tmpdir . $zipfilename);
            if ($zip->create($filelist) === 0)
            {
                //Oops something has gone wrong!
            }

            if (file_exists($tmpdir . '/' . $zipfilename))
            {
                @ob_clean();
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($zipfilename));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tmpdir . "/" . $zipfilename));
                readfile($tmpdir . '/' . $zipfilename);
                unlink($tmpdir . '/' . $zipfilename);
                exit;
            }
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction='', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'browse.js');

        $aData['display']['menu_bars'] = false;
        $aData['display']['menu_bars']['browse'] = Yii::app()->lang->gT('Browse responses'); // browse is independent of the above

        parent::_renderWrappedTemplate('responses', $aViewUrls, $aData);
    }

}
