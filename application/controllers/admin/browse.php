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
 * Browse Controller
 *
 * This controller performs browse actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class browse extends Survey_Common_Action
{

    public function run($sa = 'all')
    {
        // Load helpers
        Yii::app()->loadHelper('surveytranslator');

        // Route
        switch ($sa)
        {
            case 'view' :
            case 'time' :
                $this->route($sa, array('surveyid', 'id', 'browselang'));
                break;
            case 'all' :
            default :
                $this->route('all', array('surveyid'));
                break;
        }
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
        if (!empty($iSurveyId))
        {
            $aData['iId'] = (int) $iSurveyId;
        }
        $aData['clang'] = $clang = $this->getController()->lang;
        $aData['imageurl'] = Yii::app()->getConfig('imageurl');
        $aData['action'] = CHttpRequest::getParam('action');

        $oCriteria = new CDbCriteria;
        $oCriteria->select = 'sid, active';
        $oCriteria->join = 'INNER JOIN {{surveys_languagesettings}} as b on (b.surveyls_survey_id=sid and b.surveyls_language=language)';
        $oCriteria->condition = 'sid=:survey';
        $oCriteria->params = array('survey' => $iSurveyId);
        $actresult = Survey::model()->findAll($oCriteria);

        if (count($actresult) > 0)
        {
            foreach ($actresult as $actrow)
            {
                if ($actrow['active'] == 'N') //SURVEY IS NOT ACTIVE YET
                {
                    $aErrorData['sHeading'] = $clang->gT('Browse responses');
                    $aErrorData['sMessage'] = $clang->gT('This survey has not been activated. There are no results to browse.');
                    $this->getController()->render("/error_view", $aErrorData);
                }
            }
        }
        //SURVEY MATCHING $iSurveyId DOESN'T EXIST
        else
        {
            $aErrorData['sHeading'] = $clang->gT('Browse responses');
            $aErrorData['sMessage'] = $clang->gT('There is no matching survey.');
            $this->getController()->render("/error_view", $aErrorData);
        }

        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

        $aData['surveyinfo'] = getSurveyInfo($iSurveyId);

        if (isset($browselang) && $browselang != '')
        {
            $_SESSION['browselang'] = $browselang;
            $aData['language'] = $_SESSION['browselang'];
        }
        elseif (isset($_SESSION['browselang']))
        {
            $aData['language'] = $_SESSION['browselang'];
            $aData['languagelist'] = $languagelist = GetAdditionalLanguagesFromSurveyID($iSurveyId);
            $aData['languagelist'][] = GetBaseLanguageFromSurveyID($iSurveyId);
            if (!in_array($aData['language'], $languagelist))
            {
                $aData['language'] = GetBaseLanguageFromSurveyID($iSurveyId);
            }
        }
        else
        {
            $aData['language'] = GetBaseLanguageFromSurveyID($iSurveyId);
        }

        $aData['qulanguage'] = GetBaseLanguageFromSurveyID($iSurveyId);

        $aData['surveyoptions'] = '';
        $aData['browseoutput']  = '';

        return $aData;
    }

    /**
     * Pre Quota
     *
     * @access publlic
     * @param int $iSurveyId
     * @return void
     */
    private function _displayHeader($iSurveyId)
    {
        $clang = $this->getController()->lang;

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'browse.js');

        $this->getController()->_getAdminHeader();
        self::_browsemenubar($iSurveyId, $clang->gT('Browse responses'));
    }

    public function view($iSurveyId, $iId, $sBrowseLang = '')
    {
        $aData = $this->_getData(array('iId' => $iId, 'iSurveyId' => $iSurveyId, 'browselang' => $sBrowseLang));
        extract($aData);
        $this->_displayHeader($iSurveyId);
        $clang = Yii::app()->lang;

        $fncount = 0;
        $fieldmap = createFieldMap($iSurveyId, 'full', false, false, $aData['language']);

        //add token to top of list if survey is not private
        if ($aData['surveyinfo']['anonymized'] == "N" && tableExists('tokens_' . $iSurveyId))
        {
            $fnames[] = array("token", "Token", $clang->gT("Token ID"), 0);
            $fnames[] = array("firstname", "First name", $clang->gT("First name"), 0);
            $fnames[] = array("lastname", "Last name", $clang->gT("Last name"), 0);
            $fnames[] = array("email", "Email", $clang->gT("Email"), 0);
        }
        $fnames[] = array("submitdate", $clang->gT("Submission date"), $clang->gT("Completed"), "0", 'D');
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

            $question = $field['question'];
            if ($field['type'] != "|")
            {
                if (isset($field['subquestion']) && $field['subquestion'] != '')
                    $question .=' (' . $field['subquestion'] . ')';
                if (isset($field['subquestion1']) && isset($field['subquestion2']))
                    $question .=' (' . $field['subquestion1'] . ':' . $field['subquestion2'] . ')';
                if (isset($field['scale_id']))
                    $question .='[' . $field['scale'] . ']';
                $fnames[] = array($field['fieldname'], $question);
            }
            else
            {
                if ($field['aid'] !== 'filecount')
                {
                    $qidattributes = getQuestionAttributeValues($field['qid']);

                    for ($i = 0; $i < $qidattributes['max_files']; $i++)
                    {
                        if ($qidattributes['show_title'] == 1)
                            $fnames[] = array($field['fieldname'], "File " . ($i + 1) . " - " . $field['question'] . " (Title)", "type" => "|", "metadata" => "title", "index" => $i);

                        if ($qidattributes['show_comment'] == 1)
                            $fnames[] = array($field['fieldname'], "File " . ($i + 1) . " - " . $field['question'] . " (Comment)", "type" => "|", "metadata" => "comment", "index" => $i);

                        $fnames[] = array($field['fieldname'], "File " . ($i + 1) . " - " . $field['question'] . " (File name)", "type" => "|", "metadata" => "name", "index" => $i);
                        $fnames[] = array($field['fieldname'], "File " . ($i + 1) . " - " . $field['question'] . " (File size)", "type" => "|", "metadata" => "size", "index" => $i);
                        //$fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
                    }
                }
                else
                    $fnames[] = array($field['fieldname'], "File count");
            }
        }

        $nfncount = count($fnames) - 1;
        //SHOW INDIVIDUAL RECORD
        $oCriteria = new CDbCriteria();
        if ($aData['surveyinfo']['anonymized'] == 'N' && tableExists('tokens_' . $iSurveyId))
            $oCriteria->join = "LEFT JOIN 'tokens_{$iSurveyId}' ON surveys_{$iSurveyId}.token = tokens_{$iSurveyId}.token";
        if (incompleteAnsFilterstate() == 'inc')
            $oCriteria->addCondition('submitdate = ' . mktime(0, 0, 0, 1, 1, 1980) . ' OR submitdate IS NULL');
        elseif (incompleteAnsFilterstate() == 'filter')
            $oCriteria->addCondition('submitdate >= ' . mktime(0, 0, 0, 1, 1, 1980));
        if ($iId < 1)
        {
            $iId = 1;
        }
        if (CHttpRequest::getPost('sql'))
        {
            if (get_magic_quotes_gpc())
            {
                $oCriteria->addCondition(stripslashes(CHttpRequest::getPost('sql')));
            }
            else
            {
                $oCriteria->addCondition(CHttpRequest::getPost('sql'));
            }
        }
        else
        {
            $oCriteria->addCondition("id = {$iId}");
        }
        $iIdresult = Survey_dynamic::model($iSurveyId)->findAll($oCriteria) or die("Couldn't get entry");
        foreach ($iIdresult as $iIdrow)
        {
            $iId = $iIdrow['id'];
            $rlanguage = $iIdrow['startlanguage'];
        }
        $next = $iId + 1;
        $last = $iId - 1;

        $aData['id'] = $iId;
        if (isset($rlanguage))
        {
            $aData['rlanguage'] = $rlanguage;
        }
        $aData['next'] = $next;
        $aData['last'] = $last;

        $this->getController()->render('/admin/browse/browseidheader_view', $aData);

        foreach ($iIdresult as $iIdrow)
        {
            $highlight = false;
            for ($i = 0; $i < $nfncount + 1; $i++)
            {
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
                        $phparray = json_decode($iIdrow[$fnames[$i][0]], true);
                        if (isset($phparray[$index]))
                        {
                            if ($metadata === "size")
                                $answervalue = rawurldecode(((int) ($phparray[$index][$metadata])) . " KB");
                            else if ($metadata === "name")
                                $answervalue = CHtml::link(rawurldecode($phparray[$index][$metadata]), $this->createUrl("/admin/browse/sa/all/downloadindividualfile/{$phparray[$index][$metadata]}/fieldname/{$fnames[$i][0]}/id/{$iId}/surveyid/{$iSurveyId}"));
                            else
                                $answervalue = rawurldecode($phparray[$index][$metadata]);
                        }
                        else
                            $answervalue = "";
                    }
                    else
                    {
                        $answervalue = htmlspecialchars(strip_tags(strip_javascript(getextendedanswer($iSurveyId, "browse", $fnames[$i][0], $iIdrow[$fnames[$i][0]], ''))), ENT_QUOTES);
                    }
                }
                $aData['answervalue'] = $answervalue;
                $aData['inserthighlight'] = $inserthighlight;
                $aData['fnames'] = $fnames;
                $aData['i'] = $i;
                $this->getController()->render('/admin/browse/browseidrow_view', $aData);
            }
        }

        $this->getController()->render('/admin/browse/browseidfooter_view', $aData);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    public function all($iSurveyId)
    {
        $aData = $this->_getData($iSurveyId);
        extract($aData);
        $this->_displayHeader($iSurveyId);

        /**
         * fnames is used as informational array
         * it containts
         *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
         */
        if (CHttpRequest::getPost('sql'))
        {
            $this->getController()->render("/admin/browse/browseallfiltered_view");
        }

        //Delete Individual answer using inrow delete buttons/links - checked
        if (CHttpRequest::getPost('deleteanswer') && CHttpRequest::getPost('deleteanswer') != '' && CHttpRequest::getPost('deleteanswer') != 'marked' && bHasSurveyPermission($iSurveyId, 'responses', 'delete'))
        {
            $_POST['deleteanswer'] = (int) CHttpRequest::getPost('deleteanswer'); // sanitize the value
            // delete the files as well if its a fuqt

            $fieldmap = createFieldMap($iSurveyId);
            $fuqtquestions = array();
            // find all fuqt questions
            foreach ($fieldmap as $field)
            {
                if ($field['type'] == "|" && strpos($field['fieldname'], "_filecount") == 0)
                    $fuqtquestions[] = $field['fieldname'];
            }

            if (!empty($fuqtquestions))
            {
                // find all responses (filenames) to the fuqt questions
                $responses = Survey_dynamic::model($iSurveyId)->findAllByAttributes(array('id' => CHttpRequest::getPost('deleteanswer')));

                foreach ($responses as $json)
                {
                    foreach ($fuqtquestions as $fieldname)
                    {
                        $phparray = json_decode($json[$fieldname]);
                        foreach ($phparray as $metadata)
                        {
                            $path = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyId . "/files/";
                            unlink($path . $metadata->filename); // delete the file
                        }
                    }
                }
            }

            // delete the row
            Survey_dynamic::model($iSurveyId)->deleteAllByAttributes(array('id' => mysql_real_escape_string(CHttpRequest::getPost('deleteanswer'))));
        }
        // Marked responses -> deal with the whole batch of marked responses
        if (CHttpRequest::getPost('markedresponses') && count(CHttpRequest::getPost('markedresponses')) > 0 && bHasSurveyPermission($iSurveyId, 'responses', 'delete'))
        {
            // Delete the marked responses - checked
            if (CHttpRequest::getPost('deleteanswer') && CHttpRequest::getPost('deleteanswer') === 'marked')
            {
                $fieldmap = createFieldMap($iSurveyId);
                $fuqtquestions = array();
                // find all fuqt questions
                foreach ($fieldmap as $field)
                {
                    if ($field['type'] == "|" && strpos($field['fieldname'], "_filecount") == 0)
                        $fuqtquestions[] = $field['fieldname'];
                }

                foreach (CHttpRequest::getPost('markedresponses') as $iResponseID)
                {
                    $iResponseID = (int) $iResponseID; // sanitize the value

                    if (!empty($fuqtquestions))
                    {
                        // find all responses (filenames) to the fuqt questions
                        $responses = Survey_dynamic::model($iSurveyId)->findAllByAttributes(array('id' => $iResponseID));

                        foreach ($responses as $json)
                        {
                            foreach ($fuqtquestions as $fieldname)
                            {
                                $phparray = json_decode($json[$fieldname]);
                                foreach ($phparray as $metadata)
                                {
                                    $path = $this->getController()->getConfig('uploaddir') . "/surveys/{$iSurveyId}/files/";
                                    unlink($path . $metadata->filename); // delete the file
                                }
                            }
                        }
                    }

                    Survey_dynamic::model($iSurveyId)->deleteAllByAttributes(array('id' => $iResponseID));
                }
            }
            // Download all files for all marked responses  - checked
            else if (CHttpRequest::getPost('downloadfile') && CHttpRequest::getPost('downloadfile') === 'marked')
            {
                // Now, zip all the files in the filelist
                $zipfilename = "Responses_for_survey_{$iSurveyId}.zip";
                $this->_zipFiles(CHttpRequest::getPost('markedresponses'), $zipfilename);
            }
        }
        // Download all files for this entry - checked
        else if (CHttpRequest::getPost('downloadfile') && CHttpRequest::getPost('downloadfile') != '' && CHttpRequest::getPost('downloadfile') !== true)
        {
            // Now, zip all the files in the filelist
            $zipfilename = "LS_Responses_for_" . CHttpRequest::getPost('downloadfile') . ".zip";
            $this->_zipFiles(CHttpRequest::getPost('downloadfile'), $zipfilename);
        }
        else if (CHttpRequest::getPost('downloadindividualfile') != '')
        {
            $iId = (int) CHttpRequest::getPost('id');
            $downloadindividualfile = CHttpRequest::getPost('downloadindividualfile');
            $fieldname = CHttpRequest::getPost('fieldname');

            $row = Survey_dynamic::model($iSurveyId)->findByAttributes(array('id' => $iId));
            $phparray = json_decode(reset($row));

            for ($i = 0; $i < count($phparray); $i++)
            {
                if ($phparray[$i]->name == $downloadindividualfile)
                {
                    $file = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyId . "/files/" . $phparray[$i]->filename;

                    if (file_exists($file))
                    {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . rawurldecode($phparray[$i]->name) . '"');
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($file));
                        ob_clean();
                        flush();
                        readfile($file);
                        exit;
                    }
                    break;
                }
            }
        }

        $clang = $aData['clang'];
        //add token to top of list if survey is not private
        if ($aData['surveyinfo']['anonymized'] == "N" && tableExists('tokens_' . $iSurveyId)) //add token to top of list if survey is not private
        {
            $fnames[] = array("token", "Token", $clang->gT("Token ID"), 0);
            $fnames[] = array("firstname", "First name", $clang->gT("First name"), 0);
            $fnames[] = array("lastname", "Last name", $clang->gT("Last name"), 0);
            $fnames[] = array("email", "Email", $clang->gT("Email"), 0);
        }

        $fnames[] = array("submitdate", $clang->gT("Completed"), $clang->gT("Completed"), "0", 'D');
        $fields = createFieldMap($iSurveyId, 'full', false, false, $aData['language']);

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
                if (isset($fielddetails['subquestion']) && $fielddetails['subquestion'] != '')
                    $question .=' (' . $fielddetails['subquestion'] . ')';
                if (isset($fielddetails['subquestion1']) && isset($fielddetails['subquestion2']))
                    $question .=' (' . $fielddetails['subquestion1'] . ':' . $fielddetails['subquestion2'] . ')';
                if (isset($fielddetails['scale_id']))
                    $question .='[' . $fielddetails['scale'] . ']';
                $fnames[] = array($fielddetails['fieldname'], $question);
            }
            else
            {
                if ($fielddetails['aid'] !== 'filecount')
                {
                    $qidattributes = getQuestionAttributeValues($fielddetails['qid']);

                    for ($i = 0; $i < $qidattributes['max_files']; $i++)
                    {
                        if ($qidattributes['show_title'] == 1)
                            $fnames[] = array($fielddetails['fieldname'], "File " . ($i + 1) . " - " . $fielddetails['question'] . "(Title)", "type" => "|", "metadata" => "title", "index" => $i);

                        if ($qidattributes['show_comment'] == 1)
                            $fnames[] = array($fielddetails['fieldname'], "File " . ($i + 1) . " - " . $fielddetails['question'] . "(Comment)", "type" => "|", "metadata" => "comment", "index" => $i);

                        $fnames[] = array($fielddetails['fieldname'], "File " . ($i + 1) . " - " . $fielddetails['question'] . "(File name)", "type" => "|", "metadata" => "name", "index" => $i);
                        $fnames[] = array($fielddetails['fieldname'], "File " . ($i + 1) . " - " . $fielddetails['question'] . "(File size)", "type" => "|", "metadata" => "size", "index" => $i);
                        //$fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
                    }
                }
                else
                    $fnames[] = array($fielddetails['fieldname'], "File count");
            }
        }

        $fncount = count($fnames);

        $start = CHttpRequest::getParam('start', 0);
        $limit = CHttpRequest::getParam('limit', 100);

        $oCriteria = new CDbCriteria;
        //Create the query
        if ($aData['surveyinfo']['anonymized'] == "N" && tableExists("{{tokens_{$iSurveyId}}}"))
        {
            $oCriteria->join = "LEFT JOIN {{tokens_{$iSurveyId}}} ON {{survey_{$iSurveyId}}}.token = {{tokens_{$iSurveyId}}}.token";
        }

        if (incompleteAnsFilterstate() == "inc")
        {
            $oCriteria->addCondition("`submitdate` IS NULL");
        }
        elseif (incompleteAnsFilterstate() == "filter")
        {
            $oCriteria->addCondition("`submitdate` IS NOT NULL");
        }

        $dtcount = Survey_dynamic::model($iSurveyId)->count($oCriteria) or die("Couldn't get response data<br />");

        if ($limit > $dtcount)
        {
            $limit = $dtcount;
        }

        //NOW LETS SHOW THE DATA
        if (CHttpRequest::getPost('sql') && stripcslashes(CHttpRequest::getPost('sql')) !== "" && CHttpRequest::getPost('sql') != "NULL")
            $oCriteria->addCondition(stripcslashes(CHttpRequest::getPost('sql')));

        $oCriteria->order = 'id ' . (CHttpRequest::getParam('order') == 'desc' ? 'desc' : 'asc');
        $oCriteria->offset = $start;
        $oCriteria->limit = $limit;

        $dtresult = Survey_dynamic::model($iSurveyId)->findAll($oCriteria);
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

        $aData['dtcount2'] = $dtcount2;
        $aData['start'] = $start;
        $aData['limit'] = $limit;
        $aData['last'] = $last;
        $aData['next'] = $next;
        $aData['end'] = $end;
        $aData['fncount'] = $fncount;
        $aData['fnames'] = $fnames;

        $this->getController()->render("/admin/browse/browseallheader_view", $aData);

        $bgcc = 'odd';
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
            $this->getController()->render("/admin/browse/browseallrow_view", $aData);
        }

        $this->getController()->render("/admin/browse/browseallfooter_view", $aData);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    public function time($iSurveyId)
    {
        $aData = $this->_getData(array('iSurveyId' => $iSurveyId));
        extract($aData);
        $this->_displayHeader($iSurveyId);

        if ($aData['surveyinfo']['savetimings'] != "Y")
            die();

        if (CHttpRequest::getPost('deleteanswer') != '')
        {
            Survey_dynamic::model($iSurveyId)->deleteByAttributes(array('id' => (int) CHttpRequest::getPost('deleteanswer'))) or die("Could not delete response");
        }

        if (CHttpRequest::getPost('markedresponses') && count(CHttpRequest::getPost('markedresponses')) > 0)
        {
            foreach (CHttpRequest::getPost('markedresponses') as $iResponseID)
            {
                Survey_dynamic::model($iSurveyId)->deleteByAttributes(array('id' => (int) $iResponseID)) or die("Could not delete response");
            }
        }

        $fields = createTimingsFieldMap($iSurveyId, 'full');

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

        $start = CHttpRequest::getParam('start', 0);
        $limit = CHttpRequest::getParam('limit', 50);

        //LETS COUNT THE DATA
        $oCriteria = new CdbCritera();
        $oCriteria->select = 'tid';
        $oCriteria->join = "INNER JOIN {{survey_{$iSurveyId}}} ON {{survey_{$iSurveyId}_timings}}.id={{survey_{$iSurveyId}}}.id";
        $oCriteria->condition = 'submitdate IS NOT NULL';
        $dtcount = Survey_timings::model($iSurveyId)->count($oCriteria) or die("Couldn't get response data");

        if ($limit > $dtcount)
        {
            $limit = $dtcount;
        }

        //NOW LETS SHOW THE DATA
        $oCriteria = new CdbCritera();
        $oCriteria->join = "INNER JOIN {{survey_{$iSurveyId}}} ON {{survey_{$iSurveyId}_timings}}.id = {{survey_{$iSurveyId}}}.id";
        $oCriteria->condition = 'submitdate IS NOT NULL';
        $oCriteria->order = "{{survey_{$iSurveyId}}}.id";
        $oCriteria->limit = $limit;
        $oCriteria->offset = $start;
        if ($aData['order'] == "desc")
        {
            $oCriteria->order .= " DESC";
        }

        $dtresult = Survey_timings::model($iSurveyId)->findAll($oCriteria) or die("Couldn't get surveys");
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

        $selectshow = '';
        $selectinc = '';
        $selecthide = '';

        if (incompleteAnsFilterstate() == "inc")
        {
            $selectinc = " selected='selected'";
        }
        elseif (incompleteAnsFilterstate() == "filter")
        {
            $selecthide = " selected='selected'";
        }
        else
        {
            $selectshow = " selected='selected'";
        }

        $this->getController()->render("/admin/browse/browsetimeheader_view", $aData);

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

            for ($i = 0; $i < $fncount; $i++)
            {
                $browsedatafield = htmlspecialchars($dtrow[$fnames[$i][0]]);

                // seconds -> minutes & seconds
                if (strtolower(substr($fnames[$i][0], -4)) == "time")
                {
                    $minutes = (int) ($browsedatafield / 60);
                    $seconds = $browsedatafield % 60;
                    $browsedatafield = '';
                    if ($minutes > 0)
                        $browsedatafield .= "$minutes min ";
                    $browsedatafield .= "$seconds s";
                }
            }

            $aData['browsedatafield'] = $browsedatafield;
            $aData['bgcc'] = $bgcc;
            $aData['dtrow'] = $dtrow;
            $this->getController()->render("/admin/browse/browsetimerow_view", $aData);
        }

        //interview Time statistics
        $count = false;
        //$survstats=substr($surveytableNq);
        $oCriteria = new CDbCriteria;
        $oCriteria->select = 'AVG(interviewtime) AS avg, COUNT(id)';
        $oCriteria->join = "{{survey_{$iSurveyId}}} AS surv ON id = surv.id";
        $oCriteria->condition = 'surv.submitdate IS NOT NULL';
        $oCriteria->order = 'interviewtime';
        $queryAvg = Survey_timings::model($iSurveyId)->find($oCriteria);

        $oCriteria->select = 'interviewtime';
        $queryAll = Survey_timings::model($iSurveyId)->findAll($oCriteria);

        if ($aData['result'] = $row = $queryAvg)
        {
            $aData['avgmin'] = (int) ($row['avg'] / 60);
            $aData['avgsec'] = $row['avg'] % 60;
            $aData['count'] = $row['count'];
        }

        if ($count && $result = $queryAll)
        {

            $middleval = floor(($count - 1) / 2);
            $i = 0;
            if ($count % 2)
            {
                foreach ($result as $row)
                {
                    if ($i == $middleval)
                    {
                        $median = $row['interviewtime'];
                        break;
                    }
                    $i++;
                }
            }
            else
            {
                foreach ($result as $row)
                {
                    if ($i == $middleval)
                    {
                        $nextrow = next($result);
                        $median = ($row['interviewtime'] + $nextrow['interviewtime']) / 2;
                        break;
                    }
                    $i++;
                }
            }
            $aData['allmin'] = (int) ($median / 60);
            $aData['allsec'] = $median % 60;
        }

        $this->getController()->render('/admin/browse/browsetimefooter_view', $aData);
        $aData['num_total_answers'] = Survey_dynamic::model($iSurveyId)->count();
        $aData['num_completed_answers'] = Survey_dynamic::model($iSurveyId)->count('submitdate IS NOT NULL');
        $this->getController()->render('/admin/browse/browseindex_view', $aData);
    }

    /**
     * Supply an array with the responseIds and all files will be added to the zip
     * and it will be be spit out on success
     *
     * @param array $responseIds
     * @return ZipArchive
     */
    private function _zipFiles($responseIds, $zipfilename)
    {
        global $iSurveyId, $surveytable;

        $tmpdir = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyId . "/files/";

        $filelist = array();
        $fieldmap = createFieldMap($iSurveyId, 'full');

        foreach ($fieldmap as $field)
        {
            if ($field['type'] == "|" && $field['aid'] !== 'filecount')
            {
                $filequestion[] = $field['fieldname'];
            }
        }

        foreach ((array) $responseIds as $responseId)
        {
            $responseId = (int) $responseId; // sanitize the value

            $filearray = Survey_dynamic::model($iSurveyId)->findAllByAttributes(array('id' => $responseId)) or die('Could not download response');
            $metadata = array();
            $filecount = 0;
            foreach ($filearray as $metadata)
            {
                foreach ($metadata as $aData)
                {
                    $phparray = json_decode($aData, true);
                    if (is_array($phparray))
                    {
                        foreach ($phparray as $file)
                        {
                            $filecount++;
                            $file['responseid'] = $responseId;
                            $file['name'] = rawurldecode($file['name']);
                            $file['index'] = $filecount;
                            /*
                             * Now add the file to the archive, prefix files with responseid_index to keep them
                             * unique. This way we can have 234_1_image1.gif, 234_2_image1.gif as it could be
                             * files from a different source with the same name.
                             */
                            $filelist[] = array(PCLZIP_ATT_FILE_NAME => $tmpdir . $file['filename'],
                                PCLZIP_ATT_FILE_NEW_FULL_NAME => sprintf("%05s_%02s_%s", $file['responseid'], $file['index'], $file['name']));
                        }
                    }
                }
            }
        }

        if (count($filelist) > 0)
        {
            // TODO: to extend the yii app function loadLibrary to meet the app requirements
            Yii::app()->loadLibrary('admin/pclzip/pclzip'/* ,array('p_zipname' => $tempdir.$zipfilename) */);
            $zip = new PclZip($tmpdir . $zipfilename);
            if ($zip->create($filelist) === 0)
            {
                //Oops something has gone wrong!
            }

            if (file_exists($tmpdir . '/' . $zipfilename))
            {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($zipfilename));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tmpdir . "/" . $zipfilename));
                ob_clean();
                flush();
                readfile($tmpdir . '/' . $zipfilename);
                unlink($tmpdir . '/' . $zipfilename);
                exit;
            }
        }
    }

}
