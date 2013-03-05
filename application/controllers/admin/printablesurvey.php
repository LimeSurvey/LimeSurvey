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
 *    $Id$
 */

/**
 * Printable Survey Controller
 *
 * This controller shows a printable survey.
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class printablesurvey extends Survey_Common_Action
{

    /**
     * Show printable survey
     */
    function index($surveyid, $lang = null)
    {
        $surveyid = sanitize_int($surveyid);
        if(!hasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $clang = $this->getController()->lang;
            $aData['surveyid'] = $surveyid;
            $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");
            $message['title']= $clang->gT('Access denied!');
            $message['message']= $clang->gT('You do not have sufficient rights to access this page.');
            $message['class']= "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
        else
        {
            // PRESENT SURVEY DATAENTRY SCREEN
            // Set the language of the survey, either from GET parameter of session var
            if (isset($lang))
            {
                $lang = preg_replace("/[^a-zA-Z0-9-]/", "", $lang);
                if ($lang) $surveyprintlang = $lang;
            } else
            {
                $surveyprintlang=getBaseLanguageFromSurveyID((int) $surveyid);
            }
            $_POST['surveyprintlang']=$surveyprintlang;

            // Setting the selected language for printout
            $clang = new limesurvey_lang($surveyprintlang);

            $desrow = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=:language','params'=>array(':language'=>$surveyprintlang))))->findByAttributes(array('sid' => $surveyid));

            if (is_null($desrow))
                $this->getController()->error('Invalid survey ID');

            $desrow = array_merge($desrow->attributes, $desrow->languagesettings[0]->attributes);

            //echo '<pre>'.print_r($desrow,true).'</pre>';
            $template = $desrow['template'];
            $welcome = $desrow['surveyls_welcometext'];
            $end = $desrow['surveyls_endtext'];
            $surveyname = $desrow['surveyls_title'];
            $surveydesc = $desrow['surveyls_description'];
            $surveyactive = $desrow['active'];
            $surveytable = "{{survey_".$desrow['sid']."}}";
            $surveyexpirydate = $desrow['expires'];
            $surveyfaxto = $desrow['faxto'];
            $dateformattype = $desrow['surveyls_dateformat'];

            Yii::app()->loadHelper('surveytranslator');
            
            if (!is_null($surveyexpirydate))
            {
                $dformat=getDateFormatData($dateformattype);
                $dformat=$dformat['phpdate'];

                $expirytimestamp = strtotime($surveyexpirydate);
                $expirytimeofday_h = date('H',$expirytimestamp);
                $expirytimeofday_m = date('i',$expirytimestamp);

                $surveyexpirydate = date($dformat,$expirytimestamp);

                if(!empty($expirytimeofday_h) || !empty($expirytimeofday_m))
                {
                    $surveyexpirydate .= ' &ndash; '.$expirytimeofday_h.':'.$expirytimeofday_m;
                };            
                sprintf($clang->gT("Please submit by %s"), $surveyexpirydate);
            }
            else
            {
                $surveyexpirydate='';
            }

            if(is_file(Yii::app()->getConfig('usertemplaterootdir').DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'print_survey.pstpl'))
            {
                define('PRINT_TEMPLATE_DIR' , Yii::app()->getConfig('usertemplaterootdir').DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR , true);
                define('PRINT_TEMPLATE_URL' , Yii::app()->getConfig('usertemplaterooturl').'/'.$template.'/' , true);
            }
            elseif(is_file(Yii::app()->getConfig('usertemplaterootdir').'/'.$template.'/print_survey.pstpl'))
            {
                define('PRINT_TEMPLATE_DIR' , Yii::app()->getConfig('standardtemplaterootdir').DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR , true);
                define('PRINT_TEMPLATE_URL' , Yii::app()->getConfig('standardtemplaterooturl').'/'.$template.'/' , true);
            }
            else
            {
                define('PRINT_TEMPLATE_DIR' , Yii::app()->getConfig('standardtemplaterootdir').DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR , true);
                define('PRINT_TEMPLATE_URL' , Yii::app()->getConfig('standardtemplaterooturl').'/default/' , true);
            }

            LimeExpressionManager::StartSurvey($surveyid, 'survey',NULL,false,LEM_PRETTY_PRINT_ALL_SYNTAX);
            $moveResult = LimeExpressionManager::NavigateForwards();

            $condition = "sid = '{$surveyid}' AND language = '{$surveyprintlang}'";
            $degresult = Groups::model()->getAllGroups($condition, array('group_order'));  //xiao,

            if (!isset($surveyfaxto) || !$surveyfaxto and isset($surveyfaxnumber))
            {
                $surveyfaxto=$surveyfaxnumber; //Use system fax number if none is set in survey.
            }


            $headelements = getPrintableHeader();

            //if $showsgqacode is enabled at config.php show table name for reference
            $showsgqacode = Yii::app()->getConfig("showsgqacode");
            if(isset($showsgqacode) && $showsgqacode == true)
            {
                $surveyname =  $surveyname."<br />[".$clang->gT('Database')." ".$clang->gT('table').": $surveytable]";
            }
            else
            {
                $surveyname = $surveyname;
            }

            $survey_output = array(
            'SITENAME' => Yii::app()->getConfig("sitename")
            ,'SURVEYNAME' => $surveyname
            ,'SURVEYDESCRIPTION' => $surveydesc
            ,'WELCOME' => $welcome
            ,'END' => $end
            ,'THEREAREXQUESTIONS' => 0
            ,'SUBMIT_TEXT' => $clang->gT("Submit Your Survey.")
            ,'SUBMIT_BY' => $surveyexpirydate
            ,'THANKS' => $clang->gT("Thank you for completing this survey.")
            ,'HEADELEMENTS' => $headelements
            ,'TEMPLATEURL' => PRINT_TEMPLATE_URL
            ,'FAXTO' => $surveyfaxto
            ,'PRIVACY' => ''
            ,'GROUPS' => ''
            );

            $survey_output['FAX_TO'] ='';
            if(!empty($surveyfaxto) && $surveyfaxto != '000-00000000') //If no fax number exists, don't display faxing information!
            {
                $survey_output['FAX_TO'] = $clang->gT("Please fax your completed survey to:")." $surveyfaxto";
            }

            /**
             * Output arrays:
             *    $survey_output  =       final vaiables for whole survey
             *        $survey_output['SITENAME'] =
             *        $survey_output['SURVEYNAME'] =
             *        $survey_output['SURVEY_DESCRIPTION'] =
             *        $survey_output['WELCOME'] =
             *        $survey_output['THEREAREXQUESTIONS'] =
             *        $survey_output['PDF_FORM'] =
             *        $survey_output['HEADELEMENTS'] =
             *        $survey_output['TEMPLATEURL'] =
             *        $survey_output['SUBMIT_TEXT'] =
             *        $survey_output['SUBMIT_BY'] =
             *        $survey_output['THANKS'] =
             *        $survey_output['FAX_TO'] =
             *        $survey_output['SURVEY'] =     contains an array of all the group arrays
             *
             *    $groups[]       =       an array of all the groups output
             *        $group['GROUPNAME'] =
             *        $group['GROUPDESCRIPTION'] =
             *        $group['QUESTIONS'] =     templated formatted content if $question is appended to this at the end of processing each question.
             *        $group['ODD_EVEN'] =     class to differentiate alternate groups
             *        $group['SCENARIO'] =
             *
             *    $questions[]    =       contains an array of all the questions within a group
             *        $question['QUESTION_CODE'] =         content of the question code field
             *        $question['QUESTION_TEXT'] =         content of the question field
             *        $question['QUESTION_SCENARIO'] =         if there are conditions on a question, list the conditions.
             *        $question['QUESTION_MANDATORY'] =     translated 'mandatory' identifier
             *        $question['QUESTION_CLASS'] =         classes to be added to wrapping question div
             *        $question['QUESTION_TYPE_HELP'] =         instructions on how to complete the question
             *        $question['QUESTION_MAN_MESSAGE'] =     (not sure if this is used) mandatory error
             *        $question['QUESTION_VALID_MESSAGE'] =     (not sure if this is used) validation error
             *        $question['ANSWER'] =                contains formatted HTML answer
             *        $question['QUESTIONHELP'] =         content of the question help field.
             *
             */

            $total_questions = 0;
            $mapquestionsNumbers=Array();
            $answertext = '';   // otherwise can throw an error on line 1617

            // =========================================================
            // START doin the business:
            foreach ($degresult->readAll() as $degrow)
            {
                // ---------------------------------------------------
                // START doing groups
                $deqresult=Questions::model()->getQuestions($surveyid, $degrow['gid'], $surveyprintlang, 0, '"I"');
                $deqrows = array(); //Create an empty array in case FetchRow does not return any rows
                foreach ($deqresult->readAll() as $deqrow) {$deqrows[] = $deqrow;} // Get table output into array

                // Perform a case insensitive natural sort on group name then question title of a multidimensional array
                usort($deqrows, 'groupOrderThenQuestionOrder');

                if ($degrow['description'])
                {
                    $group_desc = $degrow['description'];
                }
                else
                {
                    $group_desc = '';
                }

                $group = array(
                    'GROUPNAME' => $degrow['group_name']
                    ,'GROUPDESCRIPTION' => $group_desc
                    ,'QUESTIONS' => '' // templated formatted content if $question is appended to this at the end of processing each question.
                );

                // A group can have only hidden questions. In that case you don't want to see the group's header/description either.
                $bGroupHasVisibleQuestions = false;
                $gid = $degrow['gid'];
                //Alternate bgcolor for different groups
                if (!isset($group['ODD_EVEN']) || $group['ODD_EVEN'] == ' g-row-even')
                {
                    $group['ODD_EVEN'] = ' g-row-odd';}
                else
                {
                    $group['ODD_EVEN'] = ' g-row-even';
                }

                //Loop through questions
                foreach ($deqrows as $deqrow)
                {
                    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                    // START doing questions

                    $qidattributes=getQuestionAttributeValues($deqrow['qid'],$deqrow['type']);
                    if ($qidattributes['hidden'] == 1 && $deqrow['type'] != '*')
                    {
                        continue;
                    }
                    $bGroupHasVisibleQuestions = true;

                    //GET ANY CONDITIONS THAT APPLY TO THIS QUESTION

                    $printablesurveyoutput = '';
                    $sExplanation = ''; //reset conditions explanation
                    $s=0;
                    // TMSW Conditions->Relevance:  show relevance instead of this whole section to create $explanation


                    $scenarioresult=Conditions::model()->getScenarios($deqrow['qid']);
                    $scenarioresult = $scenarioresult->readAll();
                    //Loop through distinct scenarios, thus grouping them together.
                    foreach ($scenarioresult as $scenariorow)
                    {
                        if( $s == 0 && count($scenarioresult) > 1)
                        {
                            $sExplanation .= '<p class="scenario">'." -------- Scenario {$scenariorow['scenario']} --------</p>\n\n";
                        }
                        if($s > 0)
                        {
                            $sExplanation .= '<p class="scenario">'.' -------- '.$clang->gT("or")." Scenario {$scenariorow['scenario']} --------</p>\n\n";
                        }

                        $x=0;

                        $conditions1="qid={$deqrow['qid']} AND scenario={$scenariorow['scenario']}";
                        $distinctresult=Conditions::model()->getSomeConditions(array('cqid','method', 'cfieldname', 'value'), $conditions1, array('cqid'),array('cqid', 'method','cfieldname','value'));

                        //Loop through each condition for a particular scenario.
                        foreach ($distinctresult->readAll() as $distinctrow)
                        {
                            $condition = "qid = '{$distinctrow['cqid']}' AND parent_qid = 0 AND language = '{$surveyprintlang}'";
                            $subresult=Questions::model()->find($condition);

                            if($x > 0)
                            {
                                $sExplanation .= ' <em class="scenario-and-seperator">'.$clang->gT('and').'</em> ';
                            }
                            if(trim($distinctrow['method'])=='') //If there is no method chosen assume "equals"
                            {
                                $distinctrow['method']='==';
                            }

                            if($distinctrow['cqid']){ // cqid != 0  ==> previous answer match
                                if($distinctrow['method']=='==')
                                {
                                    $sExplanation .= $clang->gT("Answer was")." ";
                                }
                                elseif($distinctrow['method']=='!=')
                                {
                                    $sExplanation .= $clang->gT("Answer was NOT")." ";
                                }
                                elseif($distinctrow['method']=='<')
                                {
                                    $sExplanation .= $clang->gT("Answer was less than")." ";
                                }
                                elseif($distinctrow['method']=='<=')
                                {
                                    $sExplanation .= $clang->gT("Answer was less than or equal to")." ";
                                }
                                elseif($distinctrow['method']=='>=')
                                {
                                    $sExplanation .= $clang->gT("Answer was greater than or equal to")." ";
                                }
                                elseif($distinctrow['method']=='>')
                                {
                                    $sExplanation .= $clang->gT("Answer was greater than")." ";
                                }
                                elseif($distinctrow['method']=='RX')
                                {
                                    $sExplanation .= $clang->gT("Answer matched (regexp)")." ";
                                }
                                else
                                {
                                    $sExplanation .= $clang->gT("Answer was")." ";
                                }
                                if($distinctrow['value'] == '') {
                                    $sExplanation .= ' '.$clang->gT("Not selected").' ';
                                }
                                //If question type is numerical or multi-numerical, show the actual value - otherwise, don't.
                                if($subresult['type'] == 'N' || $subresult['type'] == 'K') {
                                    $sExplanation .= ' '.$distinctrow['value']. ' ';
                                }
                            }
                            if(!$distinctrow['cqid']) { // cqid == 0  ==> token attribute match
                                $tokenData = getTokenFieldsAndNames($surveyid);
                                preg_match('/^{TOKEN:([^}]*)}$/',$distinctrow['cfieldname'],$extractedTokenAttr);
                                $sExplanation .= "Your ".$tokenData[strtolower($extractedTokenAttr[1])]['description']." ";
                                if($distinctrow['method']=='==')
                                {
                                    $sExplanation .= $clang->gT("is")." ";
                                }
                                elseif($distinctrow['method']=='!=')
                                {
                                    $sExplanation .= $clang->gT("is NOT")." ";
                                }
                                elseif($distinctrow['method']=='<')
                                {
                                    $sExplanation .= $clang->gT("is less than")." ";
                                }
                                elseif($distinctrow['method']=='<=')
                                {
                                    $sExplanation .= $clang->gT("is less than or equal to")." ";
                                }
                                elseif($distinctrow['method']=='>=')
                                {
                                    $sExplanation .= $clang->gT("is greater than or equal to")." ";
                                }
                                elseif($distinctrow['method']=='>')
                                {
                                    $sExplanation .= $clang->gT("is greater than")." ";
                                }
                                elseif($distinctrow['method']=='RX')
                                {
                                    $sExplanation .= $clang->gT("is matched (regexp)")." ";
                                }
                                else
                                {
                                    $sExplanation .= $clang->gT("is")." ";
                                }
                                $answer_section = ' '.$distinctrow['value'].' ';
                            }

                            $conresult=Conditions::model()->getConditionsQuestions($distinctrow['cqid'],$deqrow['qid'],$scenariorow['scenario'],$surveyprintlang);

                            $conditions=array();
                            foreach ($conresult->readAll() as $conrow)
                            {

                                $postans="";
                                $value=$conrow['value'];
                                switch($conrow['type'])
                                {
                                    case "Y":
                                    switch ($conrow['value'])
                                    {
                                        case "Y": $conditions[]=$clang->gT("Yes"); break;
                                        case "N": $conditions[]=$clang->gT("No"); break;
                                    }
                                    break;
                                    case "G":
                                    switch($conrow['value'])
                                    {
                                        case "M": $conditions[]=$clang->gT("Male"); break;
                                        case "F": $conditions[]=$clang->gT("Female"); break;
                                    } // switch
                                    break;
                                    case "A":
                                    case "B":
                                    case ":":
                                    case ";":
                                        $conditions[]=$conrow['value'];
                                        break;
                                    case "C":
                                    switch($conrow['value'])
                                    {
                                        case "Y": $conditions[]=$clang->gT("Yes"); break;
                                        case "U": $conditions[]=$clang->gT("Uncertain"); break;
                                        case "N": $conditions[]=$clang->gT("No"); break;
                                    } // switch
                                    break;
                                    case "E":
                                    switch($conrow['value'])
                                    {
                                        case "I": $conditions[]=$clang->gT("Increase"); break;
                                        case "D": $conditions[]=$clang->gT("Decrease"); break;
                                        case "S": $conditions[]=$clang->gT("Same"); break;
                                    }
                                    case "1":
                                        $labelIndex=preg_match("/^[^#]+#([01]{1})$/",$conrow['cfieldname']);
                                        if ($labelIndex == 0)
                                        { // TIBO

                                            $condition="qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND scale_id=0 AND language='{$surveyprintlang}'";
                                            $fresult=Answers::model()->getAllRecords($condition);

                                            foreach($fresult->readAll() as $frow)
                                            {
                                                $postans=$frow['answer'];
                                                $conditions[]=$frow['answer'];
                                            } // while
                                        }
                                        elseif ($labelIndex == 1)
                                        {

                                            $condition="qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND scale_id=1 AND language='{$surveyprintlang}'";
                                            $fresult=Answers::model()->getAllRecords($condition);
                                            foreach($fresult->readAll() as $frow)
                                            {
                                                $postans=$frow['answer'];
                                                $conditions[]=$frow['answer'];
                                            } // while
                                        }
                                        break;
                                    case "L":
                                    case "!":
                                    case "O":
                                    case "R":
                                        $condition="qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
                                        $ansresult=Answers::model()->findAll($condition);

                                        foreach ($ansresult as $ansrow)
                                        {
                                            $conditions[]=$ansrow['answer'];
                                        }
                                        if($conrow['value'] == "-oth-") {
                                            $conditions[]=$clang->gT("Other");
                                        }
                                        $conditions = array_unique($conditions);
                                        break;
                                    case "M":
                                    case "P":
                                        $condition=" parent_qid='{$conrow['cqid']}' AND title='{$conrow['value']}' AND language='{$surveyprintlang}'";
                                        $ansresult=Questions::model()->findAll($condition);
                                        foreach ($ansresult as $ansrow)
                                        {
                                            $conditions[]=$ansrow['question'];
                                        }
                                        $conditions = array_unique($conditions);
                                        break;
                                    case "N":
                                        $conditions[]=$value;
                                        break;
                                    case "F":
                                    case "H":
                                    default:
                                        $value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));

                                        $condition=" qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";

                                        $fresult=Answers::model()->getAllRecords($condition);
                                        foreach ($fresult->readAll() as $frow)
                                        {
                                            $postans=$frow['answer'];
                                            $conditions[]=$frow['answer'];
                                        } // while
                                        break;
                                } // switch

                                // Now let's complete the answer text with the answer_section
                                $answer_section="";
                                switch($conrow['type'])
                                {
                                    case "A":
                                    case "B":
                                    case "C":
                                    case "E":
                                    case "F":
                                    case "H":
                                    case "K":
                                        $thiscquestion=$fieldmap[$conrow['cfieldname']];
                                        $condition="parent_qid='{$conrow['cqid']}' AND title='{$thiscquestion['aid']}' AND language='{$surveyprintlang}'";
                                        $ansresult= Questions::model()->findAll($condition);

                                        foreach ($ansresult as $ansrow)
                                        {
                                            $answer_section=" (".$ansrow['question'].")";
                                        }
                                        break;

                                    case "1": // dual: (Label 1), (Label 2)
                                        $labelIndex=substr($conrow['cfieldname'],-1);
                                        $thiscquestion=$fieldmap[$conrow['cfieldname']];
                                        $condition="parent_qid='{$conrow['cqid']}' AND title='{$thiscquestion['aid']}' AND language='{$surveyprintlang}'";
                                        $ansresult= Questions::model()->findAll($condition);
                                        $cqidattributes = getQuestionAttributeValues($conrow['cqid'], $conrow['type']);
                                        if ($labelIndex == 0)
                                        {
                                            if (trim($cqidattributes['dualscale_headerA']) != '') {
                                                $header = $clang->gT($cqidattributes['dualscale_headerA']);
                                            } else {
                                                $header = '1';
                                            }
                                        }
                                        elseif ($labelIndex == 1)
                                        {
                                            if (trim($cqidattributes['dualscale_headerB']) != '') {
                                                $header = $clang->gT($cqidattributes['dualscale_headerB']);
                                            } else {
                                                $header = '2';
                                            }
                                        }
                                        foreach ($ansresult->readAll() as $ansrow)
                                        {
                                            $answer_section=" (".$ansrow['question']." ".sprintf($clang->gT("Label %s"),$header).")";
                                        }
                                        break;
                                    case ":":
                                    case ";": //multi flexi: ( answer [label] )
                                        $thiscquestion=$fieldmap[$conrow['cfieldname']];
                                        $condition="parent_qid='{$conrow['cqid']}' AND title='{$thiscquestion['aid']}' AND language='{$surveyprintlang}'";
                                        $ansresult= Questions::model()->findAll($condition);
                                        foreach ($ansresult as $ansrow)
                                        {

                                            $condition = "qid = '{$conrow['cqid']}' AND code = '{$conrow['value']}' AND language= '{$surveyprintlang}'";
                                            $fresult= Answers::model()->findAll($condition);
                                            foreach ($fresult as $frow)
                                            {
                                                //$conditions[]=$frow['title'];
                                                $answer_section=" (".$ansrow['question']."[".$frow['answer']."])";
                                            } // while
                                        }
                                        break;
                                    case "R": // (Rank 1), (Rank 2)... TIBO
                                        $thiscquestion=$fieldmap[$conrow['cfieldname']];
                                        $rankid=$thiscquestion['aid'];
                                        $answer_section=" (".$clang->gT("RANK")." $rankid)";
                                        break;
                                    default: // nothing to add
                                        break;
                                }
                            }

                            if (count($conditions) > 1)
                            {
                                $sExplanation .=  "'".implode("' <em class='scenario-or-seperator'>".$clang->gT("or")."</em> '", $conditions)."'";
                            }
                            elseif (count($conditions) == 1)
                            {
                                $sExplanation .= "'".$conditions[0]."'";
                            }
                            unset($conditions);
                            // Following line commented out because answer_section  was lost, but is required for some question types
                            //$explanation .= " ".$clang->gT("to question")." '".$mapquestionsNumbers[$distinctrow['cqid']]."' $answer_section ";
                            if($distinctrow['cqid']){
                                $sExplanation .= " <span class='scenario-at-seperator'>".$clang->gT("at question")."</span> '".$mapquestionsNumbers[$distinctrow['cqid']]." [".$subresult['title']."]' (".strip_tags($subresult['question'])."$answer_section)" ;
                            }
                            else{
                                $sExplanation .= " ".$distinctrow['value'] ;
                            }
                            //$distinctrow
                            $x++;
                        }
                        $s++;
                    }

                        $qinfo = LimeExpressionManager::GetQuestionStatus($deqrow['qid']);
                        $relevance = trim($qinfo['info']['relevance']);
                        $sEquation = $qinfo['relEqn'];

                        if (trim($relevance) != '' && trim($relevance) != '1')
                        {
                            if (isset($qidattributes['printable_help'][$surveyprintlang]) && $qidattributes['printable_help'][$surveyprintlang]!='')
                            {
                                $sExplanation=$qidattributes['printable_help'][$surveyprintlang];
                            }
                            elseif ($sExplanation=='') // There is only a relevance equation without conditions
                            {
                                $sExplanation=$sEquation;
                                $sEquation='&nbsp;'; // No need to show it twice
                            }
                            $sExplanation = "<b>".$clang->gT('Only answer this question if the following conditions are met:')."</b><br/> ".$sExplanation;
                            if (Yii::app()->getConfig('showrelevance')) 
                            {
                                $sExplanation.="<span class='printable_equation'><br>".$sEquation."</span>";
                            }
                        }
                        else
                        {
                            $sExplanation = '';
                        }

                        ++$total_questions;

                        //TIBO map question qid to their q number
                        $mapquestionsNumbers[$deqrow['qid']]=$total_questions;
                        //END OF GETTING CONDITIONS

                        $qid = $deqrow['qid'];
                        $fieldname = "$surveyid"."X"."$gid"."X"."$qid";

                        if(isset($showsgqacode) && $showsgqacode == true)
                        {
                            $deqrow['question'] = $deqrow['question']."<br />".$clang->gT("ID:")." $fieldname <br />".
                                                  $clang->gT("Question code:")." ".$deqrow['title'];
                        }

                        $question = array(
                         'QUESTION_NUMBER' => $total_questions    // content of the question code field
                        ,'QUESTION_CODE' => $deqrow['title']
                        ,'QUESTION_TEXT' => preg_replace('/(?:<br ?\/?>|<\/(?:p|h[1-6])>)$/is' , '' , $deqrow['question'])    // content of the question field
                        ,'QUESTION_SCENARIO' => $sExplanation    // if there are conditions on a question, list the conditions.
                        ,'QUESTION_MANDATORY' => ''        // translated 'mandatory' identifier
                        ,'QUESTION_ID' => $deqrow['qid']    // id to be added to wrapping question div
                        ,'QUESTION_CLASS' => getQuestionClass( $deqrow['type'])    // classes to be added to wrapping question div
                        ,'QUESTION_TYPE_HELP' => $qinfo['validTip']   // ''        // instructions on how to complete the question // prettyValidTip is too verbose; assuming printable surveys will use static values
                        ,'QUESTION_MAN_MESSAGE' => ''        // (not sure if this is used) mandatory error
                        ,'QUESTION_VALID_MESSAGE' => ''        // (not sure if this is used) validation error
                        ,'QUESTION_FILE_VALID_MESSAGE' => ''// (not sure if this is used) file validation error
                        ,'QUESTIONHELP' => ''            // content of the question help field.
                        ,'ANSWER' => ''                // contains formatted HTML answer
                        );

                        if($question['QUESTION_TYPE_HELP'] != "") {
                            $question['QUESTION_TYPE_HELP'] .= "<br />\n";
                        }

                        if ($deqrow['mandatory'] == 'Y')
                        {
                            $question['QUESTION_MANDATORY'] = $clang->gT('*');
                            $question['QUESTION_CLASS'] .= ' mandatory';
                        }


                        //DIFFERENT TYPES OF DATA FIELD HERE
                        if ($deqrow['help'])
                        {
                            $question['QUESTIONHELP'] = $deqrow['help'];
                        }


                        if (!empty($qidattributes['page_break']))
                        {
                            $question['QUESTION_CLASS'] .=' breakbefore ';
                        }


                        if (isset($qidattributes['maximum_chars']) && $qidattributes['maximum_chars']!='') {
                            $question['QUESTION_CLASS'] ="max-chars-{$qidattributes['maximum_chars']} ".$question['QUESTION_CLASS'];
                        }

                        switch($deqrow['type'])
                        {
                            // ==================================================================
                            case "5":    //5 POINT CHOICE
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT('Please choose *only one* of the following:');
                                $question['ANSWER'] .= "\n\t<ul>\n";
                                for ($i=1; $i<=5; $i++)
                                {
                                    $question['ANSWER'] .="\t\t<li>\n\t\t\t".self::_input_type_image('radio',$i)."\n\t\t\t$i ".self::_addsgqacode("($i)")."\n\t\t</li>\n";
                                }
                                $question['ANSWER'] .="\t</ul>\n";

                                break;

                                // ==================================================================
                            case "D":  //DATE
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT('Please enter a date:');
                                $question['ANSWER'] .= "\t".self::_input_type_image('text',$question['QUESTION_TYPE_HELP'],30,1);
                                break;

                                // ==================================================================
                            case "G":  //GENDER
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose *only one* of the following:");

                                $question['ANSWER'] .= "\n\t<ul>\n";
                                $question['ANSWER'] .= "\t\t<li>\n\t\t\t".self::_input_type_image('radio',$clang->gT("Female"))."\n\t\t\t".$clang->gT("Female")." ".self::_addsgqacode("(F)")."\n\t\t</li>\n";
                                $question['ANSWER'] .= "\t\t<li>\n\t\t\t".self::_input_type_image('radio',$clang->gT("Male"))."\n\t\t\t".$clang->gT("Male")." ".self::_addsgqacode("(M)")."\n\t\t</li>\n";
                                $question['ANSWER'] .= "\t</ul>\n";
                                break;

                                // ==================================================================
                            case "L": //LIST drop-down/radio-button list

                                // ==================================================================
                            case "!": //List - dropdown
                                if (isset($qidattributes['display_columns']) && trim($qidattributes['display_columns'])!='')
                                {
                                    $dcols=$qidattributes['display_columns'];
                                }
                                else
                                {
                                    $dcols=0;
                                }
                                if (isset($qidattributes['category_separator']) && trim($qidattributes['category_separator'])!='') {
                                    $optCategorySeparator = $qidattributes['category_separator'];
                                }
                                else
                                {
                                    unset($optCategorySeparator);
                                }

                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose *only one* of the following:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $dearesult=Answers::model()->getAllRecords(" qid='{$deqrow['qid']}' AND language='{$surveyprintlang}' ", array('sortorder','answer'));
                                $dearesult=$dearesult->readAll();
                                $deacount=count($dearesult);
                                if ($deqrow['other'] == "Y") {$deacount++;}

                                $wrapper = setupColumns(0, $deacount);

                                $question['ANSWER'] = $wrapper['whole-start'];

                                $rowcounter = 0;
                                $colcounter = 1;

                                foreach ($dearesult as $dearow)
                                {
                                    if (isset($optCategorySeparator))
                                    {
                                        list ($category, $answer) = explode($optCategorySeparator,$dearow['answer']);
                                        if ($category != '')
                                        {
                                            $dearow['answer'] = "($category) $answer ".self::_addsgqacode("(".$dearow['code'].")");
                                        }
                                        else
                                        {
                                            $dearow['answer'] = $answer.self::_addsgqacode(" (".$dearow['code'].")");
                                        }
                                        $question['ANSWER'] .= "\t".$wrapper['item-start']."\t\t".self::_input_type_image('radio' , $dearow['answer'])."\n\t\t\t".$dearow['answer']."\n".$wrapper['item-end'];
                                    }
                                    else
                                    {
                                        $question['ANSWER'] .= "\t".$wrapper['item-start']."\t\t".self::_input_type_image('radio' , $dearow['answer'])."\n\t\t\t".$dearow['answer'].self::_addsgqacode(" (".$dearow['code'].")")."\n".$wrapper['item-end'];
                                    }
                                    ++$rowcounter;
                                    if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
                                    {
                                        if($colcounter == $wrapper['cols'] - 1)
                                        {
                                            $question['ANSWER'] .= $wrapper['col-devide-last'];
                                        }
                                        else
                                        {
                                            $question['ANSWER']  .= $wrapper['col-devide'];
                                        }
                                        $rowcounter = 0;
                                        ++$colcounter;
                                    }
                                }
                                if ($deqrow['other'] == 'Y')
                                {
                                    if(trim($qidattributes["other_replace_text"][$surveyprintlang])=='')
                                    {$qidattributes["other_replace_text"][$surveyprintlang]="Other";}
                                    //                    $printablesurveyoutput .="\t".$wrapper['item-start']."\t\t".self::_input_type_image('radio' , $clang->gT("Other"))."\n\t\t\t".$clang->gT("Other")."\n\t\t\t<input type='text' size='30' readonly='readonly' />\n".$wrapper['item-end'];
                                    $question['ANSWER']  .= $wrapper['item-start-other'].self::_input_type_image('radio',$clang->gT($qidattributes["other_replace_text"][$surveyprintlang])).' '.$clang->gT($qidattributes["other_replace_text"][$surveyprintlang]).self::_addsgqacode(" (-oth-)")."\n\t\t\t".self::_input_type_image('other').self::_addsgqacode(" (".$deqrow['sid']."X".$deqrow['gid']."X".$deqrow['qid']."other)")."\n".$wrapper['item-end'];
                                }
                                $question['ANSWER'] .= $wrapper['whole-end'];
                                //Let's break the presentation into columns.
                                break;

                                // ==================================================================
                            case "O":  //LIST WITH COMMENT
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose *only one* of the following:");
                                $dearesult=Answers::model()->getAllRecords(" qid='{$deqrow['qid']}' AND language='{$surveyprintlang}'", array('sortorder', 'answer') );

                                $question['ANSWER'] = "\t<ul>\n";
                                foreach ($dearesult->readAll() as $dearow)
                                {
                                    $question['ANSWER'] .= "\t\t<li>\n\t\t\t".self::_input_type_image('radio',$dearow['answer'])."\n\t\t\t".$dearow['answer'].self::_addsgqacode(" (".$dearow['code'].")")."\n\t\t</li>\n";
                                }
                                $question['ANSWER'] .= "\t</ul>\n";

                                $question['ANSWER'] .= "\t<p class=\"comment\">\n\t\t".$clang->gT("Make a comment on your choice here:")."\n";
                                $question['ANSWER'] .= "\t\t".self::_input_type_image('textarea',$clang->gT("Make a comment on your choice here:"),50,8).self::_addsgqacode(" (".$deqrow['sid']."X".$deqrow['gid']."X".$deqrow['qid']."comment)")."\n\t</p>\n";
                                break;

                                // ==================================================================
                            case "R":  //RANKING Type Question
                                $rearesult=Answers::model()->getAllRecords(" qid='{$deqrow['qid']}' AND language='{$surveyprintlang}'", array('sortorder', 'answer'));
                                $rearesult = $rearesult->readAll();
                                $reacount = count($rearesult);
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please number each box in order of preference from 1 to")." $reacount";
                                $question['QUESTION_TYPE_HELP'] .= self::_min_max_answers_help($qidattributes, $surveyprintlang, $surveyid);
                                $question['ANSWER'] = "\n<ul>\n";
                                foreach ($rearesult as $rearow)
                                {
                                    $question['ANSWER'] .="\t<li>\n\t".self::_input_type_image('rank','',4,1)."\n\t\t&nbsp;".$rearow['answer'].self::_addsgqacode(" (".$fieldname.$rearow['code'].")")."\n\t</li>\n";
                                }
                                $question['ANSWER'] .= "\n</ul>\n";
                                break;

                                // ==================================================================
                            case "M":  //Multiple choice (Quite tricky really!)

                                if (trim($qidattributes['display_columns'])!='')
                                {
                                    $dcols=$qidattributes['display_columns'];
                                }
                                else
                                {
                                    $dcols=0;
                                }
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose *all* that apply:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $mearesult = Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}' AND language='{$surveyprintlang}' ", array('question_order'));
                                $mearesult = $mearesult->readAll();
                                $meacount = count($mearesult);
                                if ($deqrow['other'] == 'Y') {$meacount++;}

                                $wrapper = setupColumns($dcols, $meacount);
                                $question['ANSWER'] = $wrapper['whole-start'];

                                $rowcounter = 0;
                                $colcounter = 1;

                                foreach ($mearesult as $mearow)
                                {
                                    $question['ANSWER'] .= $wrapper['item-start'].self::_input_type_image('checkbox',$mearow['question'])."\n\t\t".$mearow['question'].self::_addsgqacode(" (".$fieldname.$mearow['title'].") ").$wrapper['item-end'];
                                    ++$rowcounter;
                                    if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
                                    {
                                        if($colcounter == $wrapper['cols'] - 1)
                                        {
                                            $question['ANSWER'] .= $wrapper['col-devide-last'];
                                        }
                                        else
                                        {
                                            $question['ANSWER'] .= $wrapper['col-devide'];
                                        }
                                        $rowcounter = 0;
                                        ++$colcounter;
                                    }
                                }
                                if ($deqrow['other'] == "Y")
                                {
                                    if (trim($qidattributes['other_replace_text'][$surveyprintlang])=='')
                                    {
                                        $qidattributes["other_replace_text"][$surveyprintlang]="Other";
                                    }
                                    if(!isset($mearow['answer'])) $mearow['answer']="";
                                    $question['ANSWER'] .= $wrapper['item-start-other'].self::_input_type_image('checkbox',$mearow['answer']).$clang->gT($qidattributes["other_replace_text"][$surveyprintlang]).":\n\t\t".self::_input_type_image('other').self::_addsgqacode(" (".$fieldname."other) ").$wrapper['item-end'];
                                }
                                $question['ANSWER'] .= $wrapper['whole-end'];
                                //                }
                                break;

                                 // ==================================================================
                            case "P":  //Multiple choice with comments
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose all that apply and provide a comment:");

                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);
                                $mearesult=Questions::model()->getAllRecords("parent_qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}'", array('question_order'));
                                //                $printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose all that apply and provide a comment:")."</u><br />\n";
                                $j=0;
                                $longest_string = 0;
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $longest_string = longestString($mearow['question'] , $longest_string );
                                    $question['ANSWER'] .= "\t<li><span>\n\t\t".self::_input_type_image('checkbox',$mearow['question']).$mearow['question'].self::_addsgqacode(" (".$fieldname.$mearow['title'].") ")."</span>\n\t\t".self::_input_type_image('text','comment box',60).self::_addsgqacode(" (".$fieldname.$mearow['title']."comment) ")."\n\t</li>\n";
                                    $j++;
                                }
                                if ($deqrow['other'] == "Y")
                                {
                                    $question['ANSWER'] .= "\t<li class=\"other\">\n\t\t<div class=\"other-replacetext\">".$clang->gT('Other:').self::_input_type_image('other','',1)."</div>".self::_input_type_image('othercomment','comment box',50).self::_addsgqacode(" (".$fieldname."other) ")."\n\t</li>\n";
                                    $j++;
                                }

                                $question['ANSWER'] = "\n<ul>\n".$question['ANSWER']."</ul>\n";
                                break;


                                // ==================================================================
                            case "Q":  //MULTIPLE SHORT TEXT
                                $width=60;

                                // ==================================================================
                            case "K":  //MULTIPLE NUMERICAL
                                $question['QUESTION_TYPE_HELP'] = "";
                                $width=(isset($width))?$width:16;

    //                            if (!empty($qidattributes['equals_num_value']))
    //                            {
    //                                $question['QUESTION_TYPE_HELP'] .= "* ".sprintf($clang->gT('Total of all entries must equal %d'),$qidattributes['equals_num_value'])."<br />\n";
    //                            }
    //                            if (!empty($qidattributes['max_num_value']))
    //                            {
    //                                $question['QUESTION_TYPE_HELP'] .= sprintf($clang->gT('Total of all entries must not exceed %d'), $qidattributes['max_num_value'])."<br />\n";
    //                            }
    //                            if (!empty($qidattributes['min_num_value']))
    //                            {
    //                                $question['QUESTION_TYPE_HELP'] .= sprintf($clang->gT('Total of all entries must be at least %s'),$qidattributes['min_num_value'])."<br />\n";
    //                            }

                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please write your answer(s) here:");


                                $longest_string = 0;
                                $mearesult=Questions::model()->getAllRecords("parent_qid='{$deqrow['qid']}' AND language='{$surveyprintlang}'", array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $longest_string = longestString($mearow['question'] , $longest_string );
                                    if (isset($qidattributes['slider_layout']) && $qidattributes['slider_layout']==1)
                                    {
                                      $mearow['question']=explode(':',$mearow['question']);
                                      $mearow['question']=$mearow['question'][0];
                                    }
                                    $question['ANSWER'] .=  "\t<li>\n\t\t<span>".$mearow['question']."</span>\n\t\t".self::_input_type_image('text',$mearow['question'],$width).self::_addsgqacode(" (".$fieldname.$mearow['title'].") ")."\n\t</li>\n";
                                }
                                $question['ANSWER'] =  "\n<ul>\n".$question['ANSWER']."</ul>\n";
                                break;


                                // ==================================================================
                            case "S":  //SHORT TEXT
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please write your answer here:");
                                $question['ANSWER'] = self::_input_type_image('text',$question['QUESTION_TYPE_HELP'], 50);
                                break;


                                // ==================================================================
                            case "T":  //LONG TEXT
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please write your answer here:");
                                $question['ANSWER'] = self::_input_type_image('textarea',$question['QUESTION_TYPE_HELP'], '100%' , 8);
                                break;


                                // ==================================================================
                            case "U":  //HUGE TEXT
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please write your answer here:");
                                $question['ANSWER'] = self::_input_type_image('textarea',$question['QUESTION_TYPE_HELP'], '100%' , 30);
                                break;


                                // ==================================================================
                            case "N":  //NUMERICAL
                                $prefix="";
                                $suffix="";
                                if($qidattributes['prefix'][$surveyprintlang] != "") {
                                    $prefix=$qidattributes['prefix'][$surveyprintlang];
                                }
                                if($qidattributes['suffix'][$surveyprintlang] != "") {
                                    $suffix=$qidattributes['suffix'][$surveyprintlang];
                                }
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please write your answer here:");
                                $question['ANSWER'] = "<ul>\n\t<li>\n\t\t<span>$prefix</span>\n\t\t".self::_input_type_image('text',$question['QUESTION_TYPE_HELP'],20)."\n\t\t<span>$suffix</span>\n\t\t</li>\n\t</ul>";
                                break;

                                // ==================================================================
                            case "Y":  //YES/NO
                                  $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose *only one* of the following:");
                                $question['ANSWER'] = "\n<ul>\n\t<li>\n\t\t".self::_input_type_image('radio',$clang->gT('Yes'))."\n\t\t".$clang->gT('Yes').self::_addsgqacode(" (Y)")."\n\t</li>\n";
                                $question['ANSWER'] .= "\n\t<li>\n\t\t".self::_input_type_image('radio',$clang->gT('No'))."\n\t\t".$clang->gT('No').self::_addsgqacode(" (N)")."\n\t</li>\n</ul>\n";
                                break;


                                // ==================================================================
                            case "A":  //ARRAY (5 POINT CHOICE)
                                $condition = "parent_qid = '{$deqrow['qid']}'  AND language= '{$surveyprintlang}'";
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] = "
            <table>
                <thead>
                    <tr>
                        <td>&nbsp;</td>
                        <th style='font-family:Arial,helvetica,sans-serif;font-weight:normal;'>1&nbsp;&nbsp;&nbsp;&nbsp;".self::_addsgqacode(" (1)")."</th>
                        <th style='font-family:Arial,helvetica,sans-serif;font-weight:normal;'>2&nbsp;&nbsp;&nbsp;&nbsp;".self::_addsgqacode(" (2)")."</th>
                        <th style='font-family:Arial,helvetica,sans-serif;font-weight:normal;'>3&nbsp;&nbsp;&nbsp;&nbsp;".self::_addsgqacode(" (3)")."</th>
                        <th style='font-family:Arial,helvetica,sans-serif;font-weight:normal;'>4&nbsp;&nbsp;&nbsp;&nbsp;".self::_addsgqacode(" (4)")."</th>
                        <th style='font-family:Arial,helvetica,sans-serif;font-weight:normal;'>5".self::_addsgqacode(" (5)")."</th>
                    </tr>
                </thead>
                <tbody>";

                                $j=0;
                                $rowclass = 'array1';
                                $mearesult= Questions::model()->getAllRecords( $condition, array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $rowclass = alternation($rowclass,'row');

                                    //semantic differential question type?
                                    if (strpos($mearow['question'],'|'))
                                    {
                                        $answertext = substr($mearow['question'],0, strpos($mearow['question'],'|')).self::_addsgqacode(" (".$fieldname.$mearow['title'].")")." ";
                                    }
                                    else
                                    {
                                        $answertext=$mearow['question'].self::_addsgqacode(" (".$fieldname.$mearow['title'].")");
                                    }
                                    $question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";

                                    for ($i=1; $i<=5; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$i)."</td>\n";
                                    }

                                    $answertext .= $mearow['question'];

                                    //semantic differential question type?
                                    if (strpos($mearow['question'],'|'))
                                    {
                                        $answertext2 = substr($mearow['question'],strpos($mearow['question'],'|')+1);
                                        $question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext2</td>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                    $j++;
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case "B":  //ARRAY (10 POINT CHOICE)

                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
                                for ($i=1; $i<=10; $i++)
                                {
                                    $question['ANSWER'] .= "\t\t\t<th>$i".self::_addsgqacode(" ($i)")."</th>\n";
                                }
                                $question['ANSWER'] .= "\t</thead>\n\n\t<tbody>\n";
                                $j=0;
                                $rowclass = 'array1';
                                $mearesult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}' AND language='{$surveyprintlang}' ", array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {

                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n\t\t\t<th class=\"answertext\">{$mearow['question']}".self::_addsgqacode(" (".$fieldname.$mearow['title'].")")."</th>\n";
                                    $rowclass = alternation($rowclass,'row');

                                    for ($i=1; $i<=10; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$i)."</td>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                    $j++;
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case "C":  //ARRAY (YES/UNCERTAIN/NO)

                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] = '
            <table>
                <thead>
                    <tr>
                        <td>&nbsp;</td>
                        <th>'.$clang->gT("Yes").self::_addsgqacode(" (Y)").'</th>
                        <th>'.$clang->gT("Uncertain").self::_addsgqacode(" (U)").'</th>
                        <th>'.$clang->gT("No").self::_addsgqacode(" (N)").'</th>
                    </tr>
                </thead>
                <tbody>
            ';
                                $j=0;

                                $rowclass = 'array1';

                                $mearesult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}' ", array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $question['ANSWER'] .= "\t\t\t<th class=\"answertext\">{$mearow['question']}".self::_addsgqacode(" (".$fieldname.$mearow['title'].")")."</th>\n";
                                    $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$clang->gT("Yes"))."</td>\n";
                                    $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$clang->gT("Uncertain"))."</td>\n";
                                    $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$clang->gT("No"))."</td>\n";
                                    $question['ANSWER'] .= "\t\t</tr>\n";

                                    $j++;
                                    $rowclass = alternation($rowclass,'row');
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                            case "E":  //ARRAY (Increase/Same/Decrease)
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] = '
            <table>
                <thead>
                    <tr>
                        <td>&nbsp;</td>
                        <th>'.$clang->gT("Increase").self::_addsgqacode(" (I)").'</th>
                        <th>'.$clang->gT("Same").self::_addsgqacode(" (S)").'</th>
                        <th>'.$clang->gT("Decrease").self::_addsgqacode(" (D)").'</th>
                    </tr>
                </thead>
                <tbody>
            ';
                                $j=0;
                                $rowclass = 'array1';

                                $mearesult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}' ", array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $question['ANSWER'] .= "\t\t\t<th class=\"answertext\">{$mearow['question']}".self::_addsgqacode(" (".$fieldname.$mearow['title'].")")."</th>\n";
                                    $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$clang->gT("Increase"))."</td>\n";
                                    $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$clang->gT("Same"))."</td>\n";
                                    $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio',$clang->gT("Decrease"))."</td>\n";
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                    $j++;
                                    $rowclass = alternation($rowclass,'row');
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case ":": //ARRAY (Multi Flexible) (Numbers)
                                $headstyle="style='padding-left: 20px; padding-right: 7px'";
                                if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) =='') {
                                    $maxvalue=$qidattributes['multiflexible_max'];
                                    $minvalue=1;
                                }
                                if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) =='') {
                                    $minvalue=$qidattributes['multiflexible_min'];
                                    $maxvalue=$qidattributes['multiflexible_min'] + 10;
                                }
                                if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) =='') {
                                    $minvalue=1;
                                    $maxvalue=10;
                                }
                                if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !='') {
                                    if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                                        $minvalue=$qidattributes['multiflexible_min'];
                                        $maxvalue=$qidattributes['multiflexible_max'];
                                    }
                                }

                                if (trim($qidattributes['multiflexible_step'])!='') {
                                    $stepvalue=$qidattributes['multiflexible_step'];
                                }
                                else
                                {
                                    $stepvalue=1;
                                }
                                if ($qidattributes['multiflexible_checkbox']!=0) {
                                    $checkboxlayout=true;
                                } else {
                                    $checkboxlayout=false;
                                }

                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
                                $fresult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}' and scale_id=1 AND language='{$surveyprintlang}' ", array('question_order'));
                                $fresult = $fresult->readAll();
                                $fcount = count($fresult);
                                $fwidth = "120";
                                $i=0;
                                //array to temporary store X axis question codes
                                $xaxisarray = array();
                                foreach ($fresult as $frow)

                                {
                                    $question['ANSWER'] .= "\t\t\t<th>{$frow['question']}</th>\n";
                                    $i++;

                                    //add current question code
                                    $xaxisarray[$i] = $frow['title'];
                                }
                                $question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
                                $a=1; //Counter for pdfoutput
                                $rowclass = 'array1';

                                $mearesult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}' and scale_id=0 AND language='{$surveyprintlang}' ", array('question_order'));
                                $result = $mearesult->readAll();
                                foreach ($result as $frow)
                                {
                                    $question['ANSWER'] .= "\t<tr class=\"$rowclass\">\n";
                                    $rowclass = alternation($rowclass,'row');

                                    $answertext=$frow['question'];
                                    if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
                                    $question['ANSWER'] .= "\t\t\t\t\t<th class=\"answertext\">$answertext</th>\n";
                                    //$printablesurveyoutput .="\t\t\t\t\t<td>";
                                    for ($i=1; $i<=$fcount; $i++)
                                    {

                                        $question['ANSWER'] .= "\t\t\t<td>\n";
                                        if ($checkboxlayout === false)
                                        {
                                            $question['ANSWER'] .= "\t\t\t\t".self::_input_type_image('text','',4).self::_addsgqacode(" (".$fieldname.$frow['title']."_".$xaxisarray[$i].") ")."\n";
                                        }
                                        else
                                        {
                                            $question['ANSWER'] .= "\t\t\t\t".self::_input_type_image('checkbox').self::_addsgqacode(" (".$fieldname.$frow['title']."_".$xaxisarray[$i].") ")."\n";
                                        }
                                        $question['ANSWER'] .= "\t\t\t</td>\n";
                                    }
                                    $answertext=$frow['question'];
                                    if (strpos($answertext,'|'))
                                    {
                                        $answertext=substr($answertext,strpos($answertext,'|')+1);
                                        $question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                    $a++;
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case ";": //ARRAY (Multi Flexible) (text)
                                $headstyle="style='padding-left: 20px; padding-right: 7px'";
                                $mearesult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}' AND scale_id=0 AND language='{$surveyprintlang}' ", array('question_order'));
                                $mearesult=$mearesult->readAll();

                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
                                $fresult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}'  AND scale_id=1 AND language='{$surveyprintlang}' ", array('question_order'));
                                $fresult = $fresult->readAll();
                                $fcount = count($fresult);
                                $fwidth = "120";
                                $i=0;
                                //array to temporary store X axis question codes
                                $xaxisarray = array();
                                foreach ($fresult as $frow)
                                {
                                    $question['ANSWER'] .= "\t\t\t<th>{$frow['question']}</th>\n";
                                    $i++;

                                    //add current question code
                                    $xaxisarray[$i] = $frow['title'];
                                }
                                $question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n<tbody>\n";
                                $a=1;
                                $rowclass = 'array1';

                                foreach ($mearesult as $mearow)
                                {
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $rowclass = alternation($rowclass,'row');
                                    $answertext=$mearow['question'];
                                    if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
                                    $question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";

                                    for ($i=1; $i<=$fcount; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>\n";
                                        $question['ANSWER'] .= "\t\t\t\t".self::_input_type_image('text','',23).self::_addsgqacode(" (".$fieldname.$mearow['title']."_".$xaxisarray[$i].") ")."\n";
                                        $question['ANSWER'] .= "\t\t\t</td>\n";
                                    }
                                    $answertext=$mearow['question'];
                                    if (strpos($answertext,'|'))
                                    {
                                        $answertext=substr($answertext,strpos($answertext,'|')+1);
                                        $question['ANSWER'] .= "\t\t\t\t<th class=\"answertextright\">$answertext</th>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                    $a++;
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case "F": //ARRAY (Flexible Labels)
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $fresult=Answers::model()->getAllRecords(" scale_id=0 AND qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}'", array('sortorder','code'));
                                $fresult = $fresult->readAll(); 
                                $fcount = count($fresult);
                                $fwidth = "120";
                                $i=1;
                                $column_headings = array();
                                foreach ($fresult as $frow)
                                {
                                    $column_headings[] = $frow['answer'].self::_addsgqacode(" (".$frow['code'].")");
                                }
                                if (trim($qidattributes['answer_width'])!='')
                                {
                                    $iAnswerWidth=100-$qidattributes['answer_width'];
                                }
                                else
                                {
                                    $iAnswerWidth=80;
                                }
                                if (count($column_headings)>0)
                                {
                                    $col_width = round($iAnswerWidth / count($column_headings));

                                }
                                else
                                {
                                    $heading='';
                                }
                                $question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n";
                                $question['ANSWER'] .= "\t\t\t<td>&nbsp;</td>\n";
                                foreach($column_headings as $heading)
                                {
                                    $question['ANSWER'] .= "\t\t\t<th style=\"width:$col_width%;\">$heading</th>\n";
                                }
                                $i++;
                                $question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
                                $counter = 1;
                                $rowclass = 'array1';

                                $mearesult=Questions::model()->getAllRecords(" parent_qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}' ", array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $rowclass = alternation($rowclass,'row');
                                    if (trim($answertext)=='') $answertext='&nbsp;';

                                    //semantic differential question type?
                                    if (strpos($mearow['question'],'|'))
                                    {
                                        $answertext = substr($mearow['question'],0, strpos($mearow['question'],'|')).self::_addsgqacode(" (".$fieldname.$mearow['title'].")")." ";
                                    }
                                    else
                                    {
                                        $answertext=$mearow['question'].self::_addsgqacode(" (".$fieldname.$mearow['title'].")");
                                    }

                                    if (trim($qidattributes['answer_width'])!='')
                                    {
                                        $sInsertStyle=' style="width:'.$qidattributes['answer_width'].'%" ';
                                    }
                                    else
                                    {
                                        $sInsertStyle='';
                                    }
                                    $question['ANSWER'] .= "\t\t\t<th $sInsertStyle class=\"answertext\">$answertext</th>\n";

                                    for ($i=1; $i<=$fcount; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio')."</td>\n";

                                    }
                                    $counter++;

                                    $answertext=$mearow['question'];

                                    //semantic differential question type?
                                    if (strpos($mearow['question'],'|'))
                                    {
                                        $answertext2=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                                        $question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext2</th>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case "1": //ARRAY (Flexible Labels) multi scale

                                $leftheader= $qidattributes['dualscale_headerA'][$surveyprintlang];
                                $rightheader= $qidattributes['dualscale_headerB'][$surveyprintlang];

                                $headstyle = 'style="padding-left: 20px; padding-right: 7px"';
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['QUESTION_TYPE_HELP'] .= self::_array_filter_help($qidattributes, $surveyprintlang, $surveyid);

                                $question['ANSWER'] .= "\n<table>\n\t<thead>\n";


                                $condition = "qid= '{$deqrow['qid']}'  AND language= '{$surveyprintlang}' AND scale_id=0";
                                $fresult= Answers::model()->getAllRecords( $condition, array('sortorder', 'code'));
                                $fresult = $fresult->readAll();
                                $fcount = count($fresult);

                                $fwidth = "120";
                                $l1=0;
                                $printablesurveyoutput2 = "\t\t\t<td>&nbsp;</td>\n";
                                $myheader2 = '';
                                foreach ($fresult as $frow)
                                {
                                    $printablesurveyoutput2 .="\t\t\t<th>{$frow['answer']}".self::_addsgqacode(" (".$frow['code'].")")."</th>\n";
                                    $myheader2 .= "<td></td>";
                                    $l1++;
                                }
                                // second scale
                                $printablesurveyoutput2 .="\t\t\t<td>&nbsp;</td>\n";
                                //$fquery1 = "SELECT * FROM {{answers}} WHERE qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}' AND scale_id=1 ORDER BY sortorder, code";
                               // $fresult1 = Yii::app()->db->createCommand($fquery1)->query();
                                $fresult1 = Answers::model()->getAllRecords(" qid='{$deqrow['qid']}'  AND language='{$surveyprintlang}' AND scale_id=1 ", array('sortorder','code'));
                                $fresult1 = $fresult1->readAll();
                                $fcount1 = count ($fresult1);
                                $fwidth = "120";
                                $l2=0;

                                //array to temporary store second scale question codes
                                $scale2array = array();
                                foreach ($fresult1 as $frow1)
                                {
                                    $printablesurveyoutput2 .="\t\t\t<th>{$frow1['answer']}".self::_addsgqacode(" (".$frow1['code'].")")."</th>\n";

                                    //add current question code
                                    $scale2array[$l2] = $frow1['code'];

                                    $l2++;
                                }
                                // build header if needed
                                if ($leftheader != '' || $rightheader !='')
                                {
                                    $myheader = "\t\t\t<td>&nbsp;</td>";
                                    $myheader .= "\t\t\t<th colspan=\"".$l1."\">$leftheader</th>\n";

                                    if ($rightheader !='')
                                    {
                                        // $myheader .= "\t\t\t\t\t" .$myheader2;
                                        $myheader .= "\t\t\t<td>&nbsp;</td>";
                                        $myheader .= "\t\t\t<th colspan=\"".$l2."\">$rightheader</td>\n";
                                    }

                                    $myheader .= "\t\t\t\t</tr>\n";
                                }
                                else
                                {
                                    $myheader = '';
                                }
                                $question['ANSWER'] .= $myheader . "\t\t</tr>\n\n\t\t<tr>\n";
                                $question['ANSWER'] .= $printablesurveyoutput2;
                                $question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";

                                $rowclass = 'array1';

                                //counter for each subquestion
                                $sqcounter = 0;
                                $mearesult=Questions::model()->getAllRecords(" parent_qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ", array('question_order'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $rowclass = alternation($rowclass,'row');
                                    $answertext=$mearow['question'].self::_addsgqacode(" (".$fieldname.$mearow['title']."#0) / (".$fieldname.$mearow['title']."#1)");
                                    if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
                                    $question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";
                                    for ($i=1; $i<=$fcount; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio')."</td>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t\t<td>&nbsp;</td>\n";
                                    for ($i=1; $i<=$fcount1; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio')."</td>\n";
                                    }

                                    $answertext=$mearow['question'];
                                    if (strpos($answertext,'|'))
                                    {
                                        $answertext=substr($answertext,strpos($answertext,'|')+1);
                                        $question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";
                                    }
                                    $question['ANSWER'] .= "\t\t</tr>\n";

                                    //increase subquestion counter
                                    $sqcounter++;
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";
                                break;

                                // ==================================================================
                            case "H": //ARRAY (Flexible Labels) by Column
                                //$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
                                $headstyle="style='padding-left: 20px; padding-right: 7px'";

                                $condition = "parent_qid= '{$deqrow['qid']}'  AND language= '{$surveyprintlang}'";
                                $fresult= Questions::model()->getAllRecords( $condition, array('question_order', 'title'));
                                $fresult = $fresult->readAll();
                                $question['QUESTION_TYPE_HELP'] .= $clang->gT("Please choose the appropriate response for each item:");
                                $question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";

                                $fcount = count($fresult);
                                $fwidth = "120";
                                $i=0;
                                foreach ($fresult as $frow)
                                {
                                    $question['ANSWER'] .= "\t\t\t<th>{$frow['question']}".self::_addsgqacode(" (".$fieldname.$frow['title'].")")."</th>\n";
                                    $i++;
                                }
                                $question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
                                $a=1;
                                $rowclass = 'array1';

                                $mearesult=Answers::model()->getAllRecords(" qid='{$deqrow['qid']}' AND scale_id=0 AND language='{$surveyprintlang}' ", array('sortorder','code'));
                                foreach ($mearesult->readAll() as $mearow)
                                {
                                    //$_POST['type']=$type;
                                    $question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
                                    $rowclass = alternation($rowclass,'row');
                                    $question['ANSWER'] .= "\t\t\t<th class=\"answertext\">{$mearow['answer']}".self::_addsgqacode(" (".$mearow['code'].")")."</th>\n";
                                    //$printablesurveyoutput .="\t\t\t\t\t<td>";
                                    for ($i=1; $i<=$fcount; $i++)
                                    {
                                        $question['ANSWER'] .= "\t\t\t<td>".self::_input_type_image('radio')."</td>\n";
                                    }
                                    //$printablesurveyoutput .="\t\t\t\t\t</tr></table></td>\n";
                                    $question['ANSWER'] .= "\t\t</tr>\n";
                                    $a++;
                                }
                                $question['ANSWER'] .= "\t</tbody>\n</table>\n";

                                break;
                            case "|":   // File Upload
                                $question['QUESTION_TYPE_HELP'] .= "Kindly attach the aforementioned documents along with the survey";
                                break;
                                // === END SWITCH ===================================================
                        }

                        $question['QUESTION_TYPE_HELP'] = self::_star_replace($question['QUESTION_TYPE_HELP']);
                        $group['QUESTIONS'] .= self::_populate_template( 'question' , $question);

                    }
                    if ($bGroupHasVisibleQuestions)
                    {
                        $survey_output['GROUPS'] .= self::_populate_template( 'group' , $group );
                    }
            }

            $survey_output['THEREAREXQUESTIONS'] =  str_replace( '{NUMBEROFQUESTIONS}' , $total_questions , $clang->gT('There are {NUMBEROFQUESTIONS} questions in this survey'));
            
            // START recursive tag stripping.
            // PHP 5.1.0 introduced the count parameter for preg_replace() and thus allows this procedure to run with only one regular expression.
            // Previous version of PHP needs two regular expressions to do the same thing and thus will run a bit slower.
            $server_is_newer = version_compare(PHP_VERSION , '5.1.0' , '>');
            $rounds = 0;
            while($rounds < 1)
            {
                $replace_count = 0;
                if($server_is_newer) // Server version of PHP is at least 5.1.0 or newer
                {
                    $survey_output['GROUPS'] = preg_replace(
                    array(
                                             '/<td>(?:&nbsp;|&#160;| )?<\/td>/isU'
                                             ,'/<th[^>]*>(?:&nbsp;|&#160;| )?<\/th>/isU'
                                             ,'/<([^ >]+)[^>]*>(?:&nbsp;|&#160;|\r\n|\n\r|\n|\r|\t| )*<\/\1>/isU'
                                             )
                                             ,array(
                                             '[[EMPTY-TABLE-CELL]]'
                                             ,'[[EMPTY-TABLE-CELL-HEADER]]'
                                             ,''
                                             )
                                             ,$survey_output['GROUPS']
                                             ,-1
                                             ,$replace_count
                                             );
                }
                else // Server version of PHP is older than 5.1.0
                {
                    $survey_output['GROUPS'] = preg_replace(
                    array(
                                             '/<td>(?:&nbsp;|&#160;| )?<\/td>/isU'
                                             ,'/<th[^>]*>(?:&nbsp;|&#160;| )?<\/th>/isU'
                                             ,'/<([^ >]+)[^>]*>(?:&nbsp;|&#160;|\r\n|\n\r|\n|\r|\t| )*<\/\1>/isU'
                                             )
                                             ,array(
                                             '[[EMPTY-TABLE-CELL]]'
                                             ,'[[EMPTY-TABLE-CELL-HEADER]]'
                                             ,''
                                             )
                                             ,$survey_output['GROUPS']
                                             );
                                             $replace_count = preg_match(
                                     '/<([^ >]+)[^>]*>(?:&nbsp;|&#160;|\r\n|\n\r|\n|\r|\t| )*<\/\1>/isU'
                                     , $survey_output['GROUPS']
                                     );
                }

                if($replace_count == 0)
                {
                    ++$rounds;
                    $survey_output['GROUPS'] = preg_replace(
                    array(
                                     '/\[\[EMPTY-TABLE-CELL\]\]/'
                                     ,'/\[\[EMPTY-TABLE-CELL-HEADER\]\]/'
                                     ,'/\n(?:\t*\n)+/'
                                     )
                                     ,array(
                                     '<td>&nbsp;</td>'
                                     ,'<th>&nbsp;</th>'
                                     ,"\n"
                                     )
                                     ,$survey_output['GROUPS']
                                     );

                }
            }

            $survey_output['GROUPS'] = preg_replace( '/(<div[^>]*>){NOTEMPTY}(<\/div>)/' , '\1&nbsp;\2' , $survey_output['GROUPS']);

            // END recursive empty tag stripping.

            echo self::_populate_template( 'survey' , $survey_output );
            
        }// End print
    }

    /**
     * A poor mans templating system.
     *
     *     $template    template filename (path is privided by config.php)
     *     $input        a key => value array containg all the stuff to be put into the template
     *     $line         for debugging purposes only.
     *
     * Returns a formatted string containing template with
     * keywords replaced by variables.
     *
     * How:
     */
    private function _populate_template( $template , $input  , $line = '')
    {
        $full_path = PRINT_TEMPLATE_DIR.'print_'.$template.'.pstpl';
        $full_constant = 'TEMPLATE'.$template.'.pstpl';
        if(!defined($full_constant))
        {
            if(is_file($full_path))
            {
                define( $full_constant , file_get_contents($full_path));

                $template_content = constant($full_constant);
                $test_empty = trim($template_content);
                if(empty($test_empty))
                {
                    return "<!--\n\t$full_path\n\tThe template was empty so is useless.\n-->";
                }
            }
            else
            {
                define($full_constant , '');
                return "<!--\n\t$full_path is not a propper file or is missing.\n-->";
            }
        }
        else
        {
            $template_content = constant($full_constant);
            $test_empty = trim($template_content);
            if(empty($test_empty))
            {
                return "<!--\n\t$full_path\n\tThe template was empty so is useless.\n-->";
            }
        }

        if(is_array($input))
        {
            foreach($input as $key => $value)
            {
                $find[] = '{'.$key.'}';
                $replace[] = $value;
            }
            return str_replace( $find , $replace , $template_content );
        }
        else
        {
            if(Yii::app()->getConfig('debug') > 0)
            {
                if(!empty($line))
                {
                    $line =  'LINE '.$line.': ';
                }
                return '<!-- '.$line.'There was nothing to put into the template -->'."\n";
            }
        }
    }

    private function _min_max_answers_help($qidattributes, $surveyprintlang, $surveyid) {
        $clang = $this->getController()->lang;
        $output = "";
        if(!empty($qidattributes['min_answers'])) {
            $output .= "\n<p class='extrahelp'>".sprintf($clang->gT("Please choose at least %s items."), $qidattributes['min_answers'])."</p>\n";
        }
        if(!empty($qidattributes['max_answers'])) {
            $output .= "\n<p class='extrahelp'>".sprintf($clang->gT("Please choose no more than %s items."),$qidattributes['max_answers'])."</p>\n";
        }
        return $output;
    }


    private function _input_type_image( $type , $title = '' , $x = 40 , $y = 1 , $line = '' )
    {
        if($type == 'other' or $type == 'othercomment')
        {
            $x = 1;
        }
        $tail = substr($x , -1 , 1);
        switch($tail)
        {
            case '%':
            case 'm':
            case 'x':    $x_ = $x;
            break;
            default:    $x_ = $x / 2;
        }

        if($y < 2)
        {
            $y_ = 2;
        }
        else
        {
            $y_ = $y * 2;
        }

        if(!empty($title))
        {
            $div_title = ' title="'.htmlspecialchars($title).'"';
        }
        else
        {
            $div_title = '';
        }
        switch($type)
        {
            case 'textarea':
            case 'text':    $style = ' style="width:'.$x_.'em; height:'.$y_.'em;"';
            break;
            default:    $style = '';
        }

        switch($type)
        {
            case 'radio':
            case 'checkbox':if(!defined('IMAGE_'.$type.'_SIZE'))
            {
                $image_dimensions = getimagesize(PRINT_TEMPLATE_DIR.'print_img_'.$type.'.png');
                // define('IMAGE_'.$type.'_SIZE' , ' width="'.$image_dimensions[0].'" height="'.$image_dimensions[1].'"');
                define('IMAGE_'.$type.'_SIZE' , ' width="14" height="14"');
            }
            $output = '<img src="'.PRINT_TEMPLATE_URL.'print_img_'.$type.'.png"'.constant('IMAGE_'.$type.'_SIZE').' alt="'.htmlspecialchars($title).'" class="input-'.$type.'" />';
            break;

            case 'rank':
            case 'other':
            case 'othercomment':
            case 'text':
            case 'textarea':$output = '<div class="input-'.$type.'"'.$style.$div_title.'>{NOTEMPTY}</div>';
            break;

            default:    $output = '';
        }
        return $output;
    }

    private function _star_replace($input)
    {
        return preg_replace(
                 '/\*(.*)\*/U'
                 ,'<strong>\1</strong>'
                 ,$input
                 );
    }

    private function _array_filter_help($qidattributes, $surveyprintlang, $surveyid) 
    {
        $clang = $this->getController()->lang;
        $output = "";
        if(!empty($qidattributes['array_filter']))
        {
            $newquestiontext = Questions::model()->findByAttributes(array('title' => $qidattributes['array_filter'], 'language' => $surveyprintlang, 'sid' => $surveyid))->getAttribute('question');
            $output .= "\n<p class='extrahelp'>
                ".sprintf($clang->gT("Only answer this question for the items you selected in question %s ('%s')"),$qidattributes['array_filter'], flattenText(breakToNewline($newquestiontext['question'])))."
            </p>\n";
        }
        if(!empty($qidattributes['array_filter_exclude']))
        {
            $newquestiontext = Questions::model()->findByAttributes(array('title' => $qidattributes['array_filter_exclude'], 'language' => $surveyprintlang, 'sid' => $surveyid))->getAttribute('question');

            $output .= "\n    <p class='extrahelp'>
                ".sprintf($clang->gT("Only answer this question for the items you did not select in question %s ('%s')"),$qidattributes['array_filter_exclude'], breakToNewline($newquestiontext['question']))."
            </p>\n";
        }
        return $output;
    }

    /*
     * $code: Text string containing the reference (column heading) for the current (sub-) question
     *
     * Checks if the $showsgqacode setting is enabled at config and adds references to the column headings
     * to the output so it can be used as a code book for customized SQL queries when analysing data.
     *
     * return: adds the text string to the overview
     */
    private function _addsgqacode($code)
    {
        $showsgqacode = Yii::app()->getConfig('showsgqacode');
        if(isset($showsgqacode) && $showsgqacode == true)
        {
            return $code;
        }
    }

}
