<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /*
    * LimeSurvey
    * Copyright (C) 2007-2012 The LimeSurvey Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    */

    function loadanswers()
    {
        global $surveyid;
        global $thissurvey, $thisstep;
        global $clienttoken;
        $clang = Yii::app()->lang;

        $scid=returnGlobal('scid');
        if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
        {
            $query = "SELECT * FROM {{saved_control}} INNER JOIN {$thissurvey['tablename']}
            ON {{saved_control}}.srid = {$thissurvey['tablename']}.id
            WHERE {{saved_control}}.sid=$surveyid\n";
            if (isset($scid)) //Would only come from email

            {
                $query .= "AND {{saved_control}}.scid={$scid}\n";
            }
            $query .="AND {{saved_control}}.identifier = '".autoEscape($_SESSION['survey_'.$surveyid]['holdname'])."' ";

            if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv')))
            {
                $query .="AND CAST({{saved_control}}.access_code as varchar(32))= '".md5(autoUnescape($_SESSION['survey_'.$surveyid]['holdpass']))."'\n";
            }
            else
            {
                $query .="AND {{saved_control}}.access_code = '".md5(autoUnescape($_SESSION['survey_'.$surveyid]['holdpass']))."'\n";
            }
        }
        elseif (isset($_SESSION['survey_'.$surveyid]['srid']))
        {
            $query = "SELECT * FROM {$thissurvey['tablename']}
            WHERE {$thissurvey['tablename']}.id=".$_SESSION['survey_'.$surveyid]['srid']."\n";
        }
        else
        {
            return;
        }
        $aRow = Yii::app()->db->createCommand($query)->queryRow();
        if (!$aRow)
        {
            safeDie($clang->gT("There is no matching saved survey")."<br />\n");
            return false;
        }
        else
        {
            //A match has been found. Let's load the values!
            //If this is from an email, build surveysession first
            $_SESSION['survey_'.$surveyid]['LEMtokenResume']=true;
            // Get if survey is been answered
            $submitdate=$aRow['submitdate'];
            foreach ($aRow as $column => $value)
            {
                if ($column == "token")
                {
                    $clienttoken=$value;
                    $token=$value;
                }
                elseif ($column == "saved_thisstep" && $thissurvey['alloweditaftercompletion'] != 'Y' )
                {
                    $_SESSION['survey_'.$surveyid]['step']=$value;
                    $thisstep=$value-1;
                }
                elseif ($column =='lastpage' && isset($_GET['token']))
                {
                    if(is_null($submitdate) || $submitdate=="N")
                    {
                        if ($value<1) $value=1;
                        $_SESSION['survey_'.$surveyid]['step']=$value;
                        $thisstep=$value-1;
                    }
                    else
                    {
                        $_SESSION['survey_'.$surveyid]['maxstep']=$value;
                    }
                }
                /*
                Commented this part out because otherwise startlanguage would overwrite any other language during a running survey.
                We will need a new field named 'endlanguage' to save the current language (for example for returning participants)
                /the language the survey was completed in.
                elseif ($column =='startlanguage')
                {
                $clang = SetSurveyLanguage( $surveyid, $value);
                UpdateSessionGroupList($value);  // to refresh the language strings in the group list session variable
                UpdateFieldArray();        // to refresh question titles and question text
                }*/
                elseif ($column == "scid")
                {
                    $_SESSION['survey_'.$surveyid]['scid']=$value;
                }
                elseif ($column == "srid")
                {
                    $_SESSION['survey_'.$surveyid]['srid']=$value;
                }
                elseif ($column == "datestamp")
                {
                    $_SESSION['survey_'.$surveyid]['datestamp']=$value;
                }
                if ($column == "startdate")
                {
                    $_SESSION['survey_'.$surveyid]['startdate']=$value;
                }
                else
                {
                    //Only make session variables for those in insertarray[]
                    if (in_array($column, $_SESSION['survey_'.$surveyid]['insertarray']) && isset($_SESSION['survey_'.$surveyid]['fieldmap'][$column]))
                    {
                        if (($_SESSION['survey_'.$surveyid]['fieldmap'][$column]['type'] == 'N' ||
                        $_SESSION['survey_'.$surveyid]['fieldmap'][$column]['type'] == 'K' ||
                        $_SESSION['survey_'.$surveyid]['fieldmap'][$column]['type'] == 'D') && $value == null)
                        {   // For type N,K,D NULL in DB is to be considered as NoAnswer in any case.
                            // We need to set the _SESSION[field] value to '' in order to evaluate conditions.
                            // This is especially important for the deletenonvalue feature,
                            // otherwise we would erase any answer with condition such as EQUALS-NO-ANSWER on such
                            // question types (NKD)
                            $_SESSION['survey_'.$surveyid][$column]='';
                        }
                        else
                        {
                            $_SESSION['survey_'.$surveyid][$column]=$value;
                        }
                    }  // if (in_array(
                }  // else
            } // foreach
        }
        return true;
    }

    function makegraph($currentstep, $total)
    {
        global $thissurvey;

        $clang = Yii::app()->lang;
        header_includes('lime-progress.css','css');
        $size = intval(($currentstep-1)/$total*100);

        $graph = '<script type="text/javascript">
        $(document).ready(function() {
        $("#progressbar").progressbar({
        value: '.$size.'
        });
        ;});';
        if (getLanguageRTL($clang->langcode))
        {
            $graph.='
            $(document).ready(function() {
            $("div.ui-progressbar-value").removeClass("ui-corner-left");
            $("div.ui-progressbar-value").addClass("ui-corner-right");
            });';
        }
        $graph.='
        </script>

        <div id="progress-wrapper">
        <span class="hide">'.sprintf($clang->gT('You have completed %s%% of this survey'),$size).'</span>
        <div id="progress-pre">';
        if (getLanguageRTL($clang->langcode))
        {
            $graph.='100%';
        }
        else
        {
            $graph.='0%';
        }

        $graph.='</div>
        <div id="progressbar"></div>
        <div id="progress-post">';
        if (getLanguageRTL($clang->langcode))
        {
            $graph.='0%';
        }
        else
        {
            $graph.='100%';
        }
        $graph.='</div>
        </div>';

        if ($size == 0) // Progress bar looks dumb if 0

        {
            $graph.='
            <script type="text/javascript">
            $(document).ready(function() {
            $("div.ui-progressbar-value").hide();
            });
            </script>';
        }

        return $graph;
    }

    /**
    * This function creates the language selector for a particular survey
    *
    * @param mixed $sSelectedLanguage The language in which all information is shown
    */
    function makeLanguageChangerSurvey($sSelectedLanguage)
    {
        $surveyid = Yii::app()->getConfig('surveyID');
        Yii::app()->loadHelper("surveytranslator");

        $slangs = Survey::model()->findByPk($surveyid)->getAdditionalLanguages();
        $slangs[]= GetBaseLanguageFromSurveyID($surveyid);
        $aAllLanguages=getLanguageData();
        $slangs=array_keys(array_intersect_key($aAllLanguages,array_flip($slangs))); // Sort languages by their locale name

        if (count($slangs)>1) // return a dropdow only of there are more than one lanagage
        {
            $route="/survey/index/sid/{$surveyid}";
            if (Yii::app()->request->getParam('action','none')=='previewgroup' && intval(Yii::app()->request->getParam('gid',0)))
            {
                $route.="/action/previewgroup/gid/".intval(Yii::app()->request->getParam('gid',0));
            }
            if (Yii::app()->request->getParam('token')!='')
            {
                $route.="/token/".Yii::app()->request->getParam('token');
            }
            $sHTMLCode = "<select id='languagechanger' name='languagechanger' class='languagechanger' onchange='javascript:window.location=this.value'>\n";
            foreach ($slangs as $sLanguage)
            {
                $sTargetURL=Yii::app()->getController()->createUrl($route."/lang/$sLanguage");
                $sHTMLCode .= "<option value=\"{$sTargetURL}\" ";
                if ($sLanguage==$sSelectedLanguage)
                {
                    $sHTMLCode .=" selected='selected'";
                }
                $sHTMLCode .= ">".$aAllLanguages[$sLanguage]['nativedescription']."</option>\n";

            }
            $sHTMLCode .= "</select>\n";
            return $sHTMLCode;
        }
        else
        {
            return false;
        }

    }                                                                   

    /**
    * This function creates the language selector for the public survey index page
    *
    * @param mixed $sSelectedLanguage The language in which all information is shown
    */
    function makeLanguageChanger($sSelectedLanguage)
    {
        if(count(getLanguageDataRestricted())>1)
        {
            $sHTMLCode = "<select id='languagechanger' name='languagechanger' class='languagechanger' onchange='javascript:window.location=this.value'>\n";
            foreach(getLanguageDataRestricted(true, $sSelectedLanguage) as $sLanguageID=>$aLanguageProperties)
            {
                $sLanguageUrl=Yii::app()->getController()->createUrl('survey/index',array('lang'=>$sLanguageID));
                $sHTMLCode .= "<option value='{$sLanguageUrl}'";
                if($sLanguageID == $sSelectedLanguage)
                {
                    $sHTMLCode .= " selected='selected' ";
                    $sHTMLCode .= ">{$aLanguageProperties['nativedescription']}</option>\n";
                }
                else
                {
                    $sHTMLCode .= ">".$aLanguageProperties['nativedescription'].' - '.$aLanguageProperties['description']."</option>\n";
                }
            }
            $sHTMLCode .= "</select>\n";
            return $sHTMLCode;
        }
        else
        {
            return false;
        }
    }


    // TMSW Conditions->Relevance:  this function is not needed.  Use EM to NULL fields that are irrelevant
    function checkconfield($value)
    {
        global $surveyid,$thissurvey,$qattributes;


        $fieldisdisplayed=true;
        if (!is_array($thissurvey))
        {
            $local_thissurvey=getSurveyInfo($surveyid);
        }
        else
        {
            $local_thissurvey=$thissurvey;
        }

        // we know the true fieldname $value (for instance SGQA for each checkboxes)
        // and we want to compare it to the values stored in $_SESSION['fieldarray'] which are simple fieldnames
        // ==> We first translate $value to the simple fieldname (let's call it the masterFieldName) from
        //     the $_SESSION['survey_X']['fieldnamesInfo'] translation table
        if (isset($_SESSION['survey_'.$surveyid]['fieldnamesInfo'][$value]))
        {
            $masterFieldName = $_SESSION['survey_'.$surveyid]['fieldnamesInfo'][$value];
        }
        else
        { // for token refurl, ipaddr...
            $masterFieldName = 'token';
        }
        $value_qid=0;
        $value_type='';
        $value_isconditionnal='N';

        //$value is the fieldname for the field we are checking for conditions
        foreach ($_SESSION['survey_'.$surveyid]['fieldarray'] as $sfa) //Go through each field
        {
            // record the qid and question type for future use
            if ($sfa[1]  == $masterFieldName)
            {
                $value_qid=$sfa[0];
                $value_type=$sfa[4];
                $value_isconditionnal=$sfa[7];
                break;
            }
        }

        // check if this question is conditionnal ($sfa[7]): if yes eval conditions
        if ($value_isconditionnal  == "Y" && isset($_SESSION['survey_'.$surveyid][$value]) ) //Do this if there is a condition based on this answer
        {

            $scenarioquery = "SELECT DISTINCT scenario FROM {{conditions}}"
            ." WHERE {{conditions}}.qid=$sfa[0] ORDER BY scenario";
            $scenarioresult=dbExecuteAssoc($scenarioquery);
            $matchfound=0;
            //$scenario=1;
            //while ($scenario > 0)
            $evalNextScenario = true;
            foreach($scenarioresult->readAll() as $scenariorow)
            {
                if($evalNextScenario !== true)
                    break;
                $aAllCondrows=Array();
                $cqval=Array();
                $container=Array();

                $scenario = $scenariorow['scenario'];
                $currentcfield="";
                $sConditionsQuery1 = "SELECT {{conditions}}.*, {{questions}}.type "
                . "FROM {{conditions}}, {{questions}} "
                . "WHERE {{conditions}}.cqid={{questions}}.qid "
                . "AND {{conditions}}.qid=$value_qid "
                . "AND {{conditions}}.scenario=$scenario "
                . "AND {{conditions}}.cfieldname NOT LIKE '{%' "
                . "ORDER BY {{conditions}}.qid,{{conditions}}.cfieldname";
                $oResult1=dbExecuteAssoc($sConditionsQuery1) or safeDie($query."<br />");         //Checked
                $aConditionsResult1=$oResult2->readAll();         //Checked
                $conditionsfound = count($aConditionsResult1);

                $sConditionsQuery2 = "SELECT {{conditions}}.*, '' as type "
                . "FROM {{conditions}} "
                . "WHERE "
                . " {{conditions}}.qid=$value_qid "
                . "AND {{conditions}}.scenario=$scenario "
                . "AND {{conditions}}.cfieldname LIKE '{%' "
                . "ORDER BY {{conditions}}.qid,{{conditions}}.cfieldname";
                $oResult2=dbExecuteAssoc($sConditionsQuery2) or safeDie($querytoken."<br />");
                $aConditionsResult2=$oResult2->readAll();         //Checked
                $conditionsfoundtoken = count($aConditionsResult2);
                $conditionsfound = $conditionsfound + $conditionsfoundtoken;

                foreach($aConditionsResult2 as $Condrow)
                {
                    $aAllCondrows[] = $Condrow;
                }
                foreach($aConditionsResult1 as $Condrow)
                {
                    $aAllCondrows[] = $Condrow;
                }


                foreach ($aAllCondrows as $rows)
                {
                    if (preg_match("/^\+(.*)$/",$rows['cfieldname'],$cfieldnamematch))
                    { // this condition uses a single checkbox as source
                        $rows['type'] = "+".$rows['type'];
                        $rows['cfieldname'] = $cfieldnamematch[1];
                    }

                    if($rows['type'] == "M" || $rows['type'] == "P")
                    {
                        $matchfield=$rows['cfieldname'].$rows['value'];
                        $matchmethod=$rows['method'];
                        $matchvalue="Y";
                    }
                    else
                    {
                        $matchfield=$rows['cfieldname'];
                        $matchmethod=$rows['method'];
                        $matchvalue=$rows['value'];
                    }
                    $cqval[]=array("cfieldname"=>$rows['cfieldname'],
                    "value"=>$rows['value'],
                    "type"=>$rows['type'],
                    "matchfield"=>$matchfield,
                    "matchvalue"=>$matchvalue,
                    "matchmethod"=>$matchmethod
                    );
                    if ($rows['cfieldname'] != $currentcfield)
                    {
                        $container[]=$rows['cfieldname'];
                    }
                    $currentcfield=$rows['cfieldname'];
                }
                if ($conditionsfound > 0)
                {
                    //At least one match must be found for each "$container"
                    $total=0;
                    foreach($container as $con)
                    {
                        $conditionCanBeEvaluated=true;
                        $addon=0;
                        foreach($cqval as $cqv)
                        {//Go through each condition
                            // Replace @SGQA@ condition values
                            // By corresponding value
                            if (preg_match('/^@([0-9]+X[0-9]+X[^@]+)@/',$cqv["matchvalue"], $targetconditionfieldname))
                            {
                                if (isset($_SESSION['survey_'.$surveyid][$targetconditionfieldname[1]]))
                                {
                                    $cqv["matchvalue"] = $_SESSION['survey_'.$surveyid][$targetconditionfieldname[1]];
                                }
                                else
                                {
                                    $conditionCanBeEvaluated=false;
                                }
                            }
                            // Replace {TOKEN:XXX} condition values
                            // By corresponding value
                            if ($local_thissurvey['anonymized'] == 'N' &&
                            preg_match('/^{TOKEN:([^}]*)}$/',$cqv["matchvalue"], $targetconditiontokenattr))
                            {
                                if (isset($_SESSION['survey_'.$surveyid]['token']) && in_array(strtolower($targetconditiontokenattr[1]),getTokenConditionsFieldNames($surveyid)))
                                {
                                    $cqv["matchvalue"] = getAttributeValue($surveyid,strtolower($targetconditiontokenattr[1]),$_SESSION['survey_'.$surveyid]['token']);
                                }
                                else
                                {
                                    $conditionCanBeEvaluated=false;
                                }
                            }
                            // Use == as default operator
                            if (trim($cqv['matchmethod'])=='')
                            {
                                $cqv['matchmethod']='==';
                            }
                            if($cqv['cfieldname'] == $con && $conditionCanBeEvaluated === true)
                            {
                                if (!preg_match("/^{/",$cqv['cfieldname']))
                                {
                                    if (isset($_SESSION['survey_'.$surveyid][$cqv['matchfield']]))
                                    {
                                        $comparisonLeftOperand =  $_SESSION['survey_'.$surveyid][$cqv['matchfield']];
                                    }
                                    else
                                    {
                                        $comparisonLeftOperand = null;
                                    }
                                }
                                elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$cqv['cfieldname'],$sourceconditiontokenattr))
                                {
                                    if ( isset($_SESSION['survey_'.$surveyid]['token']) &&
                                    in_array(strtolower($sourceconditiontokenattr[1]),getTokenConditionsFieldNames($surveyid)))
                                    {
                                        $comparisonLeftOperand = getAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['survey_'.$surveyid]['token']);
                                    }
                                    else
                                    {
                                        $comparisonLeftOperand = null;
                                    }

                                }
                                else
                                {
                                    $comparisonLeftOperand = null;
                                }

                                if ($cqv['matchmethod'] != "RX")
                                {
                                    if (preg_match("/^a(.*)b$/",$cqv['matchmethod'],$matchmethods))
                                    {
                                        // strings comparizon operator in PHP are the same as numerical operators
                                        $matchOperator = $matchmethods[1];
                                    }
                                    else
                                    {
                                        $matchOperator = $cqv['matchmethod'];
                                    }
                                    if (isset($comparisonLeftOperand) && !is_null($comparisonLeftOperand) && eval('if (trim($comparisonLeftOperand) '.$matchOperator.' trim($cqv["matchvalue"]) ) {return true;} else {return false;}'))
                                    {//plug successful matches into appropriate container
                                        $addon=1;
                                    }
                                }
                                elseif ( isset($comparisonLeftOperand) && !is_null($comparisonLeftOperand) && preg_match('/'.$cqv["matchvalue"].'/',$comparisonLeftOperand))
                                {
                                    $addon=1;
                                }
                            }
                        }
                        if($addon==1)
                        {
                            $total++;
                        }
                    }
                    if($total==count($container))
                    {
                        $matchfound=1;
                        $evalNextScenario=false; // Don't look for other scenario's.
                    }
                    unset($cqval);
                    unset($container);
                } else
                {
                    //Curious there is no condition for this question in this scenario
                    // this is not a normal behaviour, but I propose to defaults to a
                    // condition-matched state in this case
                    $matchfound=1;
                    $evalNextScenario=false;
                }
            } // while ($scenario)
            if($matchfound==0)
            {
                //If this is not a "moveprev" then
                // Reset the value in SESSION
                //if(isset($move) && $move != "moveprev")
                //{
                $_SESSION['survey_'.$surveyid][$value]="";
                $fieldisdisplayed=false;
                //}
            }
        }

        if ($value_qid != 0)
        { // not token masterFieldname
            $value_qa=getQuestionAttributeValues($value_qid,$value_type);
        }
        if ($fieldisdisplayed === true && isset($value_qa) && (
        (isset($value_qa['array_filter'])  && trim($value_qa['array_filter']) != '') ||
        (isset($value_qa['array_filter_exclude']) && trim($value_qa['array_filter_exclude']) != '') ))
        { // check if array_filter//array_filter_exclude have hidden the field
            $value_code = preg_replace("/$masterFieldName(.*)/","$1",$value);
            //If this question is a multi-flexible, the value_code will be both the array_filter value
            // (at the beginning) and then a labelset value after an underscore
            // ie: 2_1 for answer code=2 and labelset code=1 then 2_2 for answer_code=2 and
            // labelset code=2. So for these question types we need to split it again at the underscore!
            // 1. Find out if this is question type ":" or ";"
            if($value_type==";" || $value_type==":")
            {
                list($value_code, $value_label)=explode("_", $value_code);
            }
            if (isset($value_qa['array_filter_exclude']))
            {
                $arrayfilterXcludes_selected_codes = getArrayFilterExcludesForQuestion($value_qid);
                if ( $arrayfilterXcludes_selected_codes !== false &&
                in_array($value_code,$arrayfilterXcludes_selected_codes))
                {
                    $fieldisdisplayed=false;
                }
            }
            elseif (isset($value_qa['array_filter']))
            {
                $arrayfilter_selected_codes = getArrayFiltersForQuestion($value_qid);
                if ( $arrayfilter_selected_codes !== false &&
                !in_array($value_code,$arrayfilter_selected_codes))
                {
                    $fieldisdisplayed=false;
                }
            }
        }
        return $fieldisdisplayed;
    }

    // TMSW Conditions->Relevance:  Not needed - use EM to check mandatories
    function checkmandatorys($move, $backok=null)
    {
        global $thisstep;

        if ((isset($_POST['mandatory']) && $_POST['mandatory']) && (!isset($backok) || $backok != "Y"))
        {
            $chkmands=explode("|", $_POST['mandatory']); //These are the mandatory questions to check
            $mfns=explode("|", $_POST['mandatoryfn']); //These are the fieldnames of the mandatory questions
            $mi=0;
            foreach ($chkmands as $cm)
            {
                if (!isset($multiname) || (isset($multiname) && $multiname != "MULTI$mfns[$mi]"))  //no multiple type mandatory set, or does not match this question (set later on for first time)

                {
                    if ((isset($multiname) && $multiname) && (isset($_POST[$multiname]) && $_POST[$multiname])) //This isn't the first time (multiname exists, and is a posted variable)

                    {
                        if ($$multiname == $$multiname2 && isset($visibleanswers) && $visibleanswers > 0) //The number of questions not answered is equal to the number of questions

                        {
                            //The number of questions not answered is equal to the number of questions
                            //This section gets used if it is a multiple choice type question
                            $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                            $notanswered[]=substr($multiname, 5, strlen($multiname));
                            $$multiname=0;
                            $$multiname2=0;
                        }
                    }
                    $multiname="MULTI$mfns[$mi]";
                    $multiname2=$multiname."2"; //Make a copy, to store a second version
                    $$multiname=0;
                    $$multiname2=0;
                }
                else
                {
                    $multiname="MULTI$mfns[$mi]";
                }
                $dtcm = "tbdisp$cm";
                if (isset($_SESSION['survey_'.$surveyid][$cm]) && ($_SESSION['survey_'.$surveyid][$cm] == "0" || $_SESSION['survey_'.$surveyid][$cm]))
                {
                }
                elseif ((!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtcm]) || $_POST[$dtcm] == "on"))
                {
                    //One of the mandatory questions hasn't been asnwered
                    $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    $notanswered[]=$mfns[$mi];
                }
                else
                {
                    //One of the mandatory questions hasn't been answered
                    $$multiname++;
                }
                /* We need to have some variable to use later that indicates whether any of the
                multiple option answers were actually displayed (since it's impossible to
                answer them if they aren't). The $visibleanswers field is created here to
                record how many of the answers were actually available to be answered */
                if(!isset($visibleanswers) && (isset($_POST[$dtcm]) && $_POST[$dtcm] == "off" || isset($_POST[$dtcm])))
                {
                    $visibleanswers=0;
                }
                if(isset($_POST[$dtcm]) && $_POST[$dtcm] == "on")
                {
                    $visibleanswers++;
                }

                $$multiname2++;
                $mi++;
            }
            if ($multiname && isset($_POST[$multiname]) && $_POST[$multiname]) // Catch the last Multiple choice question in the lot

            {
                if ($$multiname == $$multiname2 && isset($visibleanswers) && $visibleanswers > 0) //so far all multiple choice options are unanswered

                {
                    //The number of questions not answered is equal to the number of questions
                    if (isset($move) && $move == "moveprev")
                    {
                        $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    }
                    if (isset($move) && $move == "movenext")
                    {
                        $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    }
                    $notanswered[]=substr($multiname, 5, strlen($multiname));
                    $$multiname="";
                    $$multiname2="";
                }
            }
        }
        if (!isset($notanswered))
        {
            return false;
        }//$notanswered=null;}
        return $notanswered;
    }

    // TMSW Conditions->Relevance:  Not needed - use EM to check mandatories
    function checkconditionalmandatorys($move, $backok=null)
    {
        global $thisstep;

        // TODO - also check whether relevance set?
        if ((isset($_POST['conmandatory']) && $_POST['conmandatory']) && (!isset($backok) || $backok != "Y")) //Mandatory conditional questions that should only be checked if the conditions for displaying that question are met

        {
            $chkcmands=explode("|", $_POST['conmandatory']);
            $cmfns=explode("|", $_POST['conmandatoryfn']);
            $mi=0;
            foreach ($chkcmands as $ccm)
            {
                if (!isset($multiname) || $multiname != "MULTI$cmfns[$mi]") //the last multipleanswerchecked is different to this one

                {
                    if (isset($multiname) && $multiname && isset($_POST[$multiname]) && $_POST[$multiname])
                    {
                        if ($$multiname == $$multiname2) //For this lot all multiple choice options are unanswered

                        {
                            //The number of questions not answered is equal to the number of questions
                            $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                            $notanswered[]=substr($multiname, 5, strlen($multiname));
                            $$multiname=0;
                            $$multiname2=0;
                        }
                    }
                    $multiname="MULTI$cmfns[$mi]";
                    $multiname2=$multiname."2"; //POSSIBLE CORRUPTION OF PROCESS - CHECK LATER
                    $$multiname=0;
                    $$multiname2=0;
                }
                else
                {
                    $multiname="MULTI$cmfns[$mi]";
                }
                $dccm="display$cmfns[$mi]";
                $dtccm = "tbdisp$ccm";
                if (isset($_SESSION['survey_'.$surveyid][$ccm]) && ($_SESSION['survey_'.$surveyid][$ccm] == "0" || $_SESSION['survey_'.$surveyid][$ccm]) && isset($_POST[$dccm]) && $_POST[$dccm] == "on") //There is an answer

                {
                    //The question has an answer, and the answer was displaying
                }
                elseif ((isset($_POST[$dccm]) && $_POST[$dccm] == "on") && (!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtccm]) || $_POST[$dtccm] == "on")) // Question and Answers is on, there is no answer, but it's a multiple

                {
                    if (isset($move) && $move == "moveprev")
                    {
                        $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    }
                    if (isset($move) && $move == "movenext")
                    {
                        $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    }
                    $notanswered[]=$cmfns[$mi];
                }
                elseif (isset($_POST[$dccm]) && $_POST[$dccm] == "on")
                {
                    //One of the conditional mandatory questions was on, but hasn't been answered
                    $$multiname++;
                }
                $$multiname2++;
                $mi++;
            }
            if (isset($multiname) && $multiname && isset($_POST[$multiname]) && $_POST[$multiname])
            {
                if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered

                {
                    //The number of questions not answered is equal to the number of questions
                    if (isset($move) && $move == "moveprev")
                    {
                        $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    }
                    if (isset($move) && $move == "movenext")
                    {
                        $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                    }
                    $notanswered[]=substr($multiname, 5, strlen($multiname));
                }
            }
        }
        if (!isset($notanswered))
        {
            return false;
        }//$notanswered=null;}
        return $notanswered;
    }

    function checkUploadedFileValidity($surveyid, $move, $backok=null)
    {
        global $thisstep;
        $clang = Yii::app()->lang;

        if (!isset($backok) || $backok != "Y")
        {
            $fieldmap = createFieldMap($surveyid,'full',false,false,$_SESSION['survey_'.$surveyid]['s_lang']);

            if (isset($_POST['fieldnames']) && $_POST['fieldnames']!="")
            {
                $fields = explode("|", $_POST['fieldnames']);

                foreach ($fields as $field)
                {
                    if ($fieldmap[$field]['type'] == "|" && !strrpos($fieldmap[$field]['fieldname'], "_filecount"))
                    {
                        $validation= getQuestionAttributeValues($fieldmap[$field]['qid']);

                        $filecount = 0;

                        $json = $_POST[$field];
                        // if name is blank, its basic, hence check
                        // else, its ajax, don't check, bypass it.

                        if ($json != "" && $json != "[]")
                        {
                            $phparray = json_decode(stripslashes($json));
                            if ($phparray[0]->size != "")
                            { // ajax
                                $filecount = count($phparray);
                            }
                            else
                            { // basic
                                for ($i = 1; $i <= $validation['max_num_of_files']; $i++)
                                {
                                    if (!isset($_FILES[$field."_file_".$i]) || $_FILES[$field."_file_".$i]['name'] == '')
                                        continue;

                                    $filecount++;

                                    $file = $_FILES[$field."_file_".$i];

                                    // File size validation
                                    if ($file['size'] > $validation['max_filesize'] * 1000)
                                    {
                                        $filenotvalidated = array();
                                        $filenotvalidated[$field."_file_".$i] = sprintf($clang->gT("Sorry, the uploaded file (%s) is larger than the allowed filesize of %s KB."), $file['size'], $validation['max_filesize']);
                                        $append = true;
                                    }

                                    // File extension validation
                                    $pathinfo = pathinfo(basename($file['name']));
                                    $ext = $pathinfo['extension'];

                                    $validExtensions = explode(",", $validation['allowed_filetypes']);
                                    if (!(in_array($ext, $validExtensions)))
                                    {
                                        if (isset($append) && $append)
                                        {
                                            $filenotvalidated[$field."_file_".$i] .= sprintf($clang->gT("Sorry, only %s extensions are allowed!"),$validation['allowed_filetypes']);
                                            unset($append);
                                        }
                                        else
                                        {
                                            $filenotvalidated = array();
                                            $filenotvalidated[$field."_file_".$i] .= sprintf($clang->gT("Sorry, only %s extensions are allowed!"),$validation['allowed_filetypes']);
                                        }
                                    }
                                }
                            }
                        }
                        else
                            $filecount = 0;

                        if (isset($validation['min_num_of_files']) && $filecount < $validation['min_num_of_files'] && LimeExpressionManager::QuestionIsRelevant($fieldmap[$field]['qid']))
                        {
                            $filenotvalidated = array();
                            $filenotvalidated[$field] = $clang->gT("The minimum number of files has not been uploaded.");
                        }
                    }
                }
            }
            if (isset($filenotvalidated))
            {
                if (isset($move) && $move == "moveprev")
                    $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                if (isset($move) && $move == "movenext")
                    $_SESSION['survey_'.$surveyid]['step'] = $thisstep;
                return $filenotvalidated;
            }
        }
        if (!isset($filenotvalidated))
            return false;
        else
            return $filenotvalidated;
    }

    function addtoarray_single($array1, $array2)
    {
        //Takes two single element arrays and adds second to end of first if value exists
        if (is_array($array2))
        {
            foreach ($array2 as $ar)
            {
                if ($ar && $ar !== null)
                {
                    $array1[]=$ar;
                }
            }
        }
        return $array1;
    }

    function remove_nulls_from_array($array)
    {
        foreach ($array as $ar)
        {
            if ($ar !== null)
            {
                $return[]=$ar;
            }
        }
        if (isset($return))
        {
            return $return;
        }
        else
        {
            return false;
        }
    }

    /**
    * Marks a tokens as completed and sends a confirmation email to the participiant.
    * If $quotaexit is set to true then the user exited the survey due to a quota
    * restriction and the according token is only marked as 'Q'
    *
    * @param mixed $quotaexit
    */
    function submittokens($quotaexit=false)
    {
        $surveyid=Yii::app()->getConfig('surveyID');
        if(isset($_SESSION['survey_'.$surveyid]['s_lang']))
        {
            $thissurvey=getSurveyInfo($surveyid,$_SESSION['survey_'.$surveyid]['s_lang']);
        }
        else
        {
            $thissurvey=getSurveyInfo($surveyid);
        }
        $clienttoken=$_SESSION['survey_'.$surveyid]['thistoken']['token'];

        $clang = Yii::app()->lang;
        $sitename = Yii::app()->getConfig("sitename");
        $emailcharset = Yii::app()->getConfig("emailcharset");
        // Shift the date due to global timeadjust setting
        $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));

        // check how many uses the token has left
        $oTokenInformation = Tokens_dynamic::model($surveyid)->findByAttributes(array('token' => $clienttoken));
        if ($oTokenInformation)
        {
                $usesleft = $oTokenInformation->usesleft;
                $participant_id = isset($oTokenInformation->participant_id) ? $oTokenInformation->participant_id : '';
        }

        if ($quotaexit==true)
        {
            $oTokenInformation->completed = 'Q';
            $oTokenInformation->usesleft = $oTokenInformation->usesleft-1;
        }
        else
        {
            if (isset($usesleft) && $usesleft<=1)
            {
                // Finish the token
                if (isTokenCompletedDatestamped($thissurvey))
                {
                    $oTokenInformation->completed = $today;
                } else {
                    $oTokenInformation->completed = 'Y';
                }
                if(!empty($participant_id))
                {
                    $slquery = Survey_links::model()->find('participant_id = :pid AND survey_id = :sid AND token_id = :tid', array(':pid'=>$participant_id, ':sid'=>$surveyid, ':tid'=>$oTokenInformation->tid));
                    
                    if (isTokenCompletedDatestamped($thissurvey))
                    {
                        $slquery->date_completed = $today;
                    } else {
                        // Update the survey_links table if necessary, to protect anonymity, use the date_created field date
                        $slquery->date_completed = $slquery->date_created;
                    }
                    $slquery->save();
                }
            }
            $oTokenInformation->usesleft = $oTokenInformation->usesleft-1;
        }
        $oTokenInformation->save();
        if ($quotaexit==false)
        {
            if ($oTokenInformation && trim(strip_tags($thissurvey['email_confirm'])) != "" && $thissurvey['sendconfirmation'] == "Y")
            {
                if($oTokenInformation->completed == "Y" || $oTokenInformation->completed == $today)
                {
                    $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
                    $to = $oTokenInformation->email;
                    $subject=$thissurvey['email_confirm_subj'];

                    $aReplacementVars=array();
                    $aReplacementVars["ADMINNAME"]=$thissurvey['admin'];
                    $aReplacementVars["ADMINEMAIL"]=$thissurvey['adminemail'];
                    $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
                    //Fill with token info, because user can have his information with anonimity control
                    $aReplacementVars["FIRSTNAME"]=$oTokenInformation->firstname;
                    $aReplacementVars["LASTNAME"]=$oTokenInformation->lastname;
                    $aReplacementVars["TOKEN"]=$clienttoken;
                    // added survey url in replacement vars
                    $surveylink = Yii::app()->createAbsoluteUrl("/survey/index/sid/{$surveyid}",array('lang'=>$_SESSION['survey_'.$surveyid]['s_lang'],'token'=>$clienttoken));
                    $aReplacementVars['SURVEYURL'] = $surveylink;
                    
                    $attrfieldnames=getAttributeFieldNames($surveyid);
                    foreach ($attrfieldnames as $attr_name)
                    {
                        $aReplacementVars[strtoupper($attr_name)]=$oTokenInformation->$attr_name;
                    }

                    $dateformatdatat=getDateFormatData($thissurvey['surveyls_dateformat']);
                    $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);
                    $redata=array('thissurvey'=>$thissurvey);
                    $subject=templatereplace($subject,$aReplacementVars,$redata);

                    $subject=html_entity_decode($subject,ENT_QUOTES,$emailcharset);

                    if (getEmailFormat($surveyid) == 'html')
                    {
                        $ishtml=true;
                    }
                    else
                    {
                        $ishtml=false;
                    }

                    $message=$thissurvey['email_confirm'];
                    //$message=ReplaceFields($message, $fieldsarray, true);
                    $message=templatereplace($message,$aReplacementVars,$redata);
                    if (!$ishtml)
                    {
                        $message=strip_tags(breakToNewline(html_entity_decode($message,ENT_QUOTES,$emailcharset)));
                    }
                    else
                    {
                        $message=html_entity_decode($message,ENT_QUOTES, $emailcharset );
                    }

                    //Only send confirmation email if there is a valid email address
                    if (validateEmailAddress($to))
                    {
                        SendEmailMessage($message, $subject, $to, $from, $sitename,$ishtml);
                    }
                }
                else
                {
                    // Leave it to send optional confirmation at closed token
                }
            }
        }
    }

    /**
    * Send a submit notification to the email address specified in the notifications tab in the survey settings
    */
    function sendSubmitNotifications($surveyid)
    {
        // @todo: Remove globals
        global $thissurvey, $maildebug, $tokensexist;
        
        if (trim($thissurvey['adminemail'])=='')
        {
            return;
        }
        
        $homeurl=Yii::app()->createAbsoluteUrl('/admin');
        $clang = Yii::app()->lang;
        $sitename = Yii::app()->getConfig("sitename");

        $debug=Yii::app()->getConfig('debug');
        $bIsHTML = ($thissurvey['htmlemail'] == 'Y');

        $aReplacementVars=array();

        if ($thissurvey['allowsave'] == "Y" && isset($_SESSION['survey_'.$surveyid]['scid']))
        {
            $aReplacementVars['RELOADURL']="".Yii::app()->getController()->createUrl("/survey/index/sid/{$surveyid}/loadall/reload/scid/".$_SESSION['survey_'.$surveyid]['scid']."/loadname/".urlencode($_SESSION['survey_'.$surveyid]['holdname'])."/loadpass/".urlencode($_SESSION['survey_'.$surveyid]['holdpass'])."/lang/".urlencode($clang->langcode));
            if ($bIsHTML)
            {
                $aReplacementVars['RELOADURL']="<a href='{$aReplacementVars['RELOADURL']}'>{$aReplacementVars['RELOADURL']}</a>";
            }
        }
        else
        {
            $aReplacementVars['RELOADURL']='';
        }

        if (!isset($_SESSION['survey_'.$surveyid]['srid']))
            $srid = null;
        else
            $srid = $_SESSION['survey_'.$surveyid]['srid'];
        $aReplacementVars['ADMINNAME'] = $thissurvey['adminname'];
        $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
        $aReplacementVars['VIEWRESPONSEURL']=Yii::app()->createAbsoluteUrl("/admin/responses/sa/view/surveyid/{$surveyid}/id/{$srid}");
        $aReplacementVars['EDITRESPONSEURL']=Yii::app()->createAbsoluteUrl("/admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$srid}");
        $aReplacementVars['STATISTICSURL']=Yii::app()->createAbsoluteUrl("/admin/statistics/sa/index/surveyid/{$surveyid}");
        if ($bIsHTML)
        {
            $aReplacementVars['VIEWRESPONSEURL']="<a href='{$aReplacementVars['VIEWRESPONSEURL']}'>{$aReplacementVars['VIEWRESPONSEURL']}</a>";
            $aReplacementVars['EDITRESPONSEURL']="<a href='{$aReplacementVars['EDITRESPONSEURL']}'>{$aReplacementVars['EDITRESPONSEURL']}</a>";
            $aReplacementVars['STATISTICSURL']="<a href='{$aReplacementVars['STATISTICSURL']}'>{$aReplacementVars['STATISTICSURL']}</a>";
        }
        $aReplacementVars['ANSWERTABLE']='';
        $aEmailResponseTo=array();
        $aEmailNotificationTo=array();
        $sResponseData="";

        if (!empty($thissurvey['emailnotificationto']))
        {
            $aRecipient=explode(";", $thissurvey['emailnotificationto']);
            {
                foreach($aRecipient as $sRecipient)
                {
                    $sRecipient=ReplaceFields($sRecipient, array('ADMINEMAIL' =>$thissurvey['adminemail'] ), true); // Only need INSERTANS, ADMINMAIL and TOKEN
                    if(validateEmailAddress($sRecipient))
                    {
                        $aEmailNotificationTo[]=$sRecipient;
                    }
                }
            }
        }

        if (!empty($thissurvey['emailresponseto']))
        {
            if (isset($_SESSION['survey_'.$surveyid]['token']) && $_SESSION['survey_'.$surveyid]['token'] != '' && tableExists('{{tokens_'.$surveyid.'}}'))
            {
                //Gather token data for tokenised surveys
                $_SESSION['survey_'.$surveyid]['thistoken']=getTokenData($surveyid, $_SESSION['survey_'.$surveyid]['token']);
            }
            // there was no token used so lets remove the token field from insertarray
            elseif ($_SESSION['survey_'.$surveyid]['insertarray'][0]=='token')
            {
                unset($_SESSION['survey_'.$surveyid]['insertarray'][0]);
            }
            //Make an array of email addresses to send to
            $aRecipient=explode(";", $thissurvey['emailresponseto']);
            {
                foreach($aRecipient as $sRecipient)
                {
                    $sRecipient=ReplaceFields($sRecipient, array('ADMINEMAIL' =>$thissurvey['adminemail'] ), true); // Only need INSERTANS, ADMINMAIL and TOKEN
                    if(validateEmailAddress($sRecipient))
                    {
                        $aEmailResponseTo[]=$sRecipient;
                    }
                }
            }

            $aFullResponseTable=getFullResponseTable($surveyid,$_SESSION['survey_'.$surveyid]['srid'],$_SESSION['survey_'.$surveyid]['s_lang']);
            $ResultTableHTML = "<table class='printouttable' >\n";
            $ResultTableText ="\n\n";
            $oldgid = 0;
            $oldqid = 0;
            foreach ($aFullResponseTable as $sFieldname=>$fname)
            {
                if (substr($sFieldname,0,4)=='gid_')
                {

                    $ResultTableHTML .= "\t<tr class='printanswersgroup'><td colspan='2'>{$fname[0]}</td></tr>\n";
                    $ResultTableText .="\n{$fname[0]}\n\n";
                }
                elseif (substr($sFieldname,0,4)=='qid_')
                {
                    $ResultTableHTML .= "\t<tr class='printanswersquestionhead'><td  colspan='2'>{$fname[0]}</td></tr>\n";
                    $ResultTableText .="\n{$fname[0]}\n";
                }
                else
                {
                    $ResultTableHTML .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]}</td><td class='printanswersanswertext'>{$fname[2]}</td></tr>";
                    $ResultTableText .="     {$fname[0]} {$fname[1]}: {$fname[2]}\n";
                }
            }

            $ResultTableHTML .= "</table>\n";
            $ResultTableText .= "\n\n";
            if ($bIsHTML)
            {
                $aReplacementVars['ANSWERTABLE']=$ResultTableHTML;
            }
            else
            {
                $aReplacementVars['ANSWERTABLE']=$ResultTableText;
            }
        }

        $sFrom = $thissurvey['adminname'].' <'.$thissurvey['adminemail'].'>';
    
        
        $redata=compact(array_keys(get_defined_vars()));
        if (count($aEmailNotificationTo)>0)
        {
            $sMessage=templatereplace($thissurvey['email_admin_notification'],$aReplacementVars,$redata,'frontend_helper[1398]',($thissurvey['anonymized'] == "Y"));
            $sSubject=templatereplace($thissurvey['email_admin_notification_subj'],$aReplacementVars,$redata,'frontend_helper[1399]',($thissurvey['anonymized'] == "Y"));
            foreach ($aEmailNotificationTo as $sRecipient)
            {
                if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, true, getBounceEmail($surveyid)))
                {
                    if ($debug>0)
                    {
                        echo '<br />Email could not be sent. Reason: '.$maildebug.'<br/>';
                    }
                }
            }
        }

        if (count($aEmailResponseTo)>0)
        {
            $sMessage=templatereplace($thissurvey['email_admin_responses'],$aReplacementVars,$redata,'frontend_helper[1414]',($thissurvey['anonymized'] == "Y"));
            $sSubject=templatereplace($thissurvey['email_admin_responses_subj'],$aReplacementVars,$redata,'frontend_helper[1415]',($thissurvey['anonymized'] == "Y"));
            foreach ($aEmailResponseTo as $sRecipient)
            {
                if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, true, getBounceEmail($surveyid)))
                {
                    if ($debug>0)
                    {
                        echo '<br />Email could not be sent. Reason: '.$maildebug.'<br/>';
                    }
                }
            }
        }


    }

    function submitfailed($errormsg='')
    {
        global $debug;
        global $thissurvey;
        global $subquery, $surveyid;

        $clang = Yii::app()->lang;

        $completed = "<br /><strong><font size='2' color='red'>"
        . $clang->gT("Did Not Save")."</strong></font><br /><br />\n\n"
        . $clang->gT("An unexpected error has occurred and your responses cannot be saved.")."<br /><br />\n";
        if ($thissurvey['adminemail'])
        {
            $completed .= $clang->gT("Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.")."<br /><br />\n";
            if ($debug>0)
            {
                $completed.='Error message: '.htmlspecialchars($errormsg).'<br />';
            }
            $email=$clang->gT("An error occurred saving a response to survey id","unescaped")." ".$thissurvey['name']." - $surveyid\n\n";
            $email .= $clang->gT("DATA TO BE ENTERED","unescaped").":\n";
            foreach ($_SESSION['survey_'.$surveyid]['insertarray'] as $value)
            {
                $email .= "$value: {$_SESSION['survey_'.$surveyid][$value]}\n";
            }
            $email .= "\n".$clang->gT("SQL CODE THAT FAILED","unescaped").":\n"
            . "$subquery\n\n"
            . $clang->gT("ERROR MESSAGE","unescaped").":\n"
            . $errormsg."\n\n";
            SendEmailMessage($email, $clang->gT("Error saving results","unescaped"), $thissurvey['adminemail'], $thissurvey['adminemail'], "LimeSurvey", false, getBounceEmail($surveyid));
            //echo "<!-- EMAIL CONTENTS:\n$email -->\n";
            //An email has been sent, so we can kill off this session.
            killSurveySession($surveyid);
        }
        else
        {
            $completed .= "<a href='javascript:location.reload()'>".$clang->gT("Try to submit again")."</a><br /><br />\n";
            $completed .= $subquery;
        }
        return $completed;
    }

    /**
    * This function builds all the required session variables when a survey is first started and
    * it loads any answer defaults from command line or from the table defaultvalues
    * It is called from the related format script (group.php, question.php, survey.php)
    * if the survey has just started.
    */
    function buildsurveysession($surveyid,$preview=false)
    {
        global $secerror, $clienttoken;
        global $tokensexist;
        //global $surveyid;
        global $templang, $move, $rooturl;

        $clang = Yii::app()->lang;

        $thissurvey = getSurveyInfo($surveyid);
        if (empty($templang))
        {
            $templang=$thissurvey['language'];
        }

        $_SESSION['survey_'.$surveyid]['templatename']=validateTemplateDir($thissurvey['template']);
        $_SESSION['survey_'.$surveyid]['templatepath']=getTemplatePath($_SESSION['survey_'.$surveyid]['templatename']).DIRECTORY_SEPARATOR;
        $sTemplatePath=$_SESSION['survey_'.$surveyid]['templatepath'];

        $loadsecurity = returnGlobal('loadsecurity');

        // NO TOKEN REQUIRED BUT CAPTCHA ENABLED FOR SURVEY ACCESS
        if ($tokensexist == 0 &&
        isCaptchaEnabled('surveyaccessscreen',$thissurvey['usecaptcha']))
        {

            // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
            if (!isset($loadsecurity) ||
            !isset($_SESSION['survey_'.$surveyid]['secanswer']) ||
            $loadsecurity != $_SESSION['survey_'.$surveyid]['secanswer'])
            {
                sendCacheHeaders();
                doHeader();
                // No or bad answer to required security question

                $redata = compact(array_keys(get_defined_vars()));
                echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[1525]');
                //echo makedropdownlist();
                echo templatereplace(file_get_contents($sTemplatePath."survey.pstpl"),array(),$redata,'frontend_helper[1527]');

                if (isset($loadsecurity))
                { // was a bad answer
                    echo "<font color='#FF0000'>".$clang->gT("The answer to the security question is incorrect.")."</font><br />";
                }

                echo "<p class='captcha'>".$clang->gT("Please confirm access to survey by answering the security question below and click continue.")."</p>"
                .CHtml::form(array("/survey/index/sid/{$surveyid}"), 'post', array('class'=>'captcha'))."
                <table align='center'>
                <tr>
                <td align='right' valign='middle'>
                <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
                <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                // In case we this is a direct Reload previous answers URL, then add hidden fields
                if (isset($_GET['loadall']) && isset($_GET['scid'])
                && isset($_GET['loadname']) && isset($_GET['loadpass']))
                {
                    echo "
                    <input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
                    <input type='hidden' name='scid' value='".returnGlobal('scid')."' id='scid' />
                    <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
                    <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                }

                echo "
                </td>
                </tr>";
                if (function_exists("ImageCreate") && isCaptchaEnabled('surveyaccessscreen', $thissurvey['usecaptcha']))
                {
                    echo "<tr>
                    <td align='center' valign='middle'><label for='captcha'>".$clang->gT("Security question:")."</label></td><td align='left' valign='middle'><table><tr><td valign='middle'><img src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.$surveyid)."' alt='captcha' /></td>
                    <td valign='middle'><input id='captcha' type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table>
                    </td>
                    </tr>";
                }
                echo "<tr><td colspan='2' align='center'><input class='submit' type='submit' value='".$clang->gT("Continue")."' /></td></tr>
                </table>
                </form>";

                echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[1567]');
                doFooter();
                exit;
            }
        }

        //BEFORE BUILDING A NEW SESSION FOR THIS SURVEY, LET'S CHECK TO MAKE SURE THE SURVEY SHOULD PROCEED!
        // TOKEN REQUIRED BUT NO TOKEN PROVIDED
        if ($tokensexist == 1 && !$clienttoken && !$preview)
        {

            if ($thissurvey['nokeyboard']=='Y')
            {
                includeKeypad();
                $kpclass = "text-keypad";
            }
            else
            {
                $kpclass = "";
            }

            // DISPLAY REGISTER-PAGE if needed
            // DISPLAY CAPTCHA if needed
            sendCacheHeaders();
            doHeader();

            $redata = compact(array_keys(get_defined_vars()));
            echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[1594]');
            //echo makedropdownlist();
            echo templatereplace(file_get_contents($sTemplatePath."survey.pstpl"),array(),$redata,'frontend_helper[1596]');
            if (isset($thissurvey) && $thissurvey['allowregister'] == "Y")
            {
                echo templatereplace(file_get_contents($sTemplatePath."register.pstpl"),array(),$redata,'frontend_helper[1599]');
            }
            else
            {
                // ->renderPartial('entertoken_view');
                if (isset($secerror)) echo "<span class='error'>".$secerror."</span><br />";
                echo '<div id="wrapper"><p id="tokenmessage">'.$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br />";
                echo $clang->gT("If you have been issued a token, please enter it in the box below and click continue.")."</p>
                <script type='text/javascript'>var focus_element='#token';</script>"
                .CHtml::form(array("/survey/index/sid/{$surveyid}"), 'post', array('id'=>'tokenform'))."
                <ul>
                <li>";?>
            <label for='token'><?php $clang->eT("Token:");?></label><input class='text <?php echo $kpclass?>' id='token' type='text' name='token' />
            <?php
            echo "<input type='hidden' name='sid' value='".$surveyid."' id='sid' />
            <input type='hidden' name='lang' value='".$templang."' id='lang' />";
            if (isset($_GET['newtest']) && $_GET['newtest'] == "Y")
            {
                echo "  <input type='hidden' name='newtest' value='Y' id='newtest' />";

            }

            // If this is a direct Reload previous answers URL, then add hidden fields
            if (isset($_GET['loadall']) && isset($_GET['scid'])
            && isset($_GET['loadname']) && isset($_GET['loadpass']))
            {
                echo "
                <input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
                <input type='hidden' name='scid' value='".returnGlobal('scid')."' id='scid' />
                <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
                <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
            }
            echo "</li>";

            if (function_exists("ImageCreate") && isCaptchaEnabled('surveyaccessscreen', $thissurvey['usecaptcha']))
            {
                echo "<li>
                <label for='captchaimage'>".$clang->gT("Security Question")."</label><img id='captchaimage' src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.$surveyid)."' alt='captcha' /><input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
                </li>";
            }
            echo "<li>
            <input class='submit' type='submit' value='".$clang->gT("Continue")."' />
            </li>
            </ul>
            </form></div>";
        }

        echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[1645]');
        doFooter();
        exit;
    }
    // TOKENS REQUIRED, A TOKEN PROVIDED
    // SURVEY WITH NO NEED TO USE CAPTCHA
    elseif ($tokensexist == 1 && $clienttoken &&
    !isCaptchaEnabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        //check if tokens actually haven't been already used
        $areTokensUsed = usedTokens(trim(strip_tags($clienttoken)),$surveyid);
        //check if token actually does exist
        // check also if it is allowed to change survey after completion
        if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
            $oTokenEntry = Tokens_dynamic::model($surveyid)->find('token=:token', array(':token'=>trim(strip_tags($clienttoken))));
        } else {
            $oTokenEntry = Tokens_dynamic::model($surveyid)->find("token=:token AND (completed = 'N' or completed='')", array(':token'=>trim(strip_tags($clienttoken))));
        }

        if (is_null($oTokenEntry) ||  ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y') )
        {
            //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

            killSurveySession($surveyid);
            sendCacheHeaders();
            doHeader();

            $redata = compact(array_keys(get_defined_vars()));
            echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[1676]');
            echo templatereplace(file_get_contents($sTemplatePath."survey.pstpl"),array(),$redata,'frontend_helper[1677]');
            echo '<div id="wrapper"><p id="tokenmessage">'.$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
            ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."<br /><br />\n"
            ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
            ." (<a href='mailto:{$thissurvey['adminemail']}'>"
            ."{$thissurvey['adminemail']}</a>)</p></div>\n";

            echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[1684]');
            doFooter();
            exit;
        }
    }
    // TOKENS REQUIRED, A TOKEN PROVIDED
    // SURVEY CAPTCHA REQUIRED
    elseif ($tokensexist == 1 && $clienttoken && isCaptchaEnabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        // IF CAPTCHA ANSWER IS CORRECT
        if (isset($loadsecurity) &&
        isset($_SESSION['survey_'.$surveyid]['secanswer']) &&
        $loadsecurity == $_SESSION['survey_'.$surveyid]['secanswer'])
        {
            //check if tokens actually haven't been already used
            $areTokensUsed = usedTokens(trim(strip_tags($clienttoken)),$surveyid);
            //check if token actually does exist
            $oTokenEntry = Tokens_dynamic::model($surveyid)->find('token=:token', array(':token'=>trim(strip_tags($clienttoken))));

            if ($thissurvey['alloweditaftercompletion'] == 'Y' )
            {
                $oTokenEntry = Tokens_dynamic::model($surveyid)->find('token=:token', array(':token'=>trim(strip_tags($clienttoken))));
            }
            else
            {
                $oTokenEntry = Tokens_dynamic::model($surveyid)->find("token=:token  AND (completed = 'N' or completed='')", array(':token'=>trim(strip_tags($clienttoken))));
           }
            if (is_null($oTokenEntry) || ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y') )
            {
                sendCacheHeaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
                echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[1719]');
                echo templatereplace(file_get_contents($sTemplatePath."survey.pstpl"),array(),$redata,'frontend_helper[1720]');
                echo "\t<div id='wrapper'>\n"
                ."\t<p id='tokenmessage'>\n"
                ."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
                ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."<br/><br />\n"
                ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
                ." (<a href='mailto:{$thissurvey['adminemail']}'>"
                ."{$thissurvey['adminemail']}</a>)\n"
                ."\t</p>\n"
                ."\t</div>\n";

                echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[1731]');
                doFooter();
                exit;
            }
        }
        // IF CAPTCHA ANSWER IS NOT CORRECT
        else if (!isset($move) || is_null($move))
            {
                unset($_SESSION['survey_'.$surveyid]['srid']);
                $gettoken = $clienttoken;
                sendCacheHeaders();
                doHeader();
                // No or bad answer to required security question
                $redata = compact(array_keys(get_defined_vars()));
                echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[1745]');
                echo templatereplace(file_get_contents($sTemplatePath."survey.pstpl"),array(),$redata,'frontend_helper[1746]');
                // If token wasn't provided and public registration
                // is enabled then show registration form
                if ( !isset($gettoken) && isset($thissurvey) && $thissurvey['allowregister'] == "Y")
                {
                    echo templatereplace(file_get_contents($sTemplatePath."register.pstpl"),array(),$redata,'frontend_helper[1751]');
                }
                else
                { // only show CAPTCHA

                    echo '<div id="wrapper"><p id="tokenmessage">';
                    if (isset($loadsecurity))
                    { // was a bad answer
                        echo "<span class='error'>".$clang->gT("The answer to the security question is incorrect.")."</span><br />";
                    }

                    echo $clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />";
                    // IF TOKEN HAS BEEN GIVEN THEN AUTOFILL IT
                    // AND HIDE ENTRY FIELD
                    if (!isset($gettoken))
                    {
                        echo $clang->gT("If you have been issued a token, please enter it in the box below and click continue.")."</p>
                        <form id='tokenform' method='get' action='".Yii::app()->getController()->createUrl("/survey/index")."'>
                        <ul>
                        <li>
                        <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
                        <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                        if (isset($_GET['loadall']) && isset($_GET['scid'])
                        && isset($_GET['loadname']) && isset($_GET['loadpass']))
                        {
                            echo "<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
                            <input type='hidden' name='scid' value='".returnGlobal('scid')."' id='scid' />
                            <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
                            <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                        }

                        echo '<label for="token">'.$clang->gT("Token")."</label><input class='text' type='text' id='token' name='token'></li>";
                }
                else
                {
                    echo $clang->gT("Please confirm the token by answering the security question below and click continue.")."</p>
                    <form id='tokenform' method='get' action='".Yii::app()->getController()->createUrl("/survey/index")."'>
                    <ul>
                    <li>
                    <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
                    <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                    if (isset($_GET['loadall']) && isset($_GET['scid'])
                    && isset($_GET['loadname']) && isset($_GET['loadpass']))
                    {
                        echo "<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
                        <input type='hidden' name='scid' value='".returnGlobal('scid')."' id='scid' />
                        <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
                        <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                    }
                    echo '<label for="token">'.$clang->gT("Token:")."</label><span id='token'>$gettoken</span>"
                    ."<input type='hidden' name='token' value='$gettoken'></li>";
                }


                if (function_exists("ImageCreate") && isCaptchaEnabled('surveyaccessscreen', $thissurvey['usecaptcha']))
                {
                    echo "<li>
                    <label for='captchaimage'>".$clang->gT("Security Question")."</label><img id='captchaimage' src='".Yii::app()->getController()->createUrl('/verification/image/sid/'.$surveyid)."' alt='captcha' /><input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
                    </li>";
                }
                echo "<li><input class='submit' type='submit' value='".$clang->gT("Continue")."' /></li>
                </ul>
                </form>
                </id>";
            }

            echo '</div>'.templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[1817]');
            doFooter();
            exit;
        }
    }

    //RESET ALL THE SESSION VARIABLES AND START AGAIN
    unset($_SESSION['survey_'.$surveyid]['grouplist']);
    unset($_SESSION['survey_'.$surveyid]['fieldarray']);
    unset($_SESSION['survey_'.$surveyid]['insertarray']);
    unset($_SESSION['survey_'.$surveyid]['thistoken']);
    unset($_SESSION['survey_'.$surveyid]['fieldnamesInfo']);
    unset($_SESSION['survey_'.$surveyid]['fieldmap-' . $surveyid . '-randMaster']);
    unset($_SESSION['survey_'.$surveyid]['groupReMap']);
    $_SESSION['survey_'.$surveyid]['fieldnamesInfo'] = Array();


    //RL: multilingual support
    if (isset($_GET['token']) && tableExists('{{tokens_'.$surveyid.'}}'))
    {

        //get language from token (if one exists)
        $tkquery2 = "SELECT * FROM {{tokens_".$surveyid."}} WHERE token='".$clienttoken."' AND (completed = 'N' or completed='')";
        //echo $tkquery2;
        $result = dbExecuteAssoc($tkquery2) or safeDie ("Couldn't get tokens<br />$tkquery<br />");    //Checked
        foreach ($result->readAll() as $rw)
        {
            $tklanguage=$rw['language'];
        }
    }
    if (returnGlobal('lang'))
    {
        $language_to_set=returnGlobal('lang');
    } elseif (isset($tklanguage))
    {
        $language_to_set=$tklanguage;
    }
    else
    {
        $language_to_set = $thissurvey['language'];
    }

    if (!isset($_SESSION['survey_'.$surveyid]['s_lang']))
    {
        SetSurveyLanguage($surveyid, $language_to_set);
    }


    UpdateGroupList($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);

    $sQuery = "SELECT count(*)\n"
    ." FROM {{groups}} INNER JOIN {{questions}} ON {{groups}}.gid = {{questions}}.gid\n"
    ." WHERE {{questions}}.sid=".$surveyid."\n"
    ." AND {{groups}}.language='".$_SESSION['survey_'.$surveyid]['s_lang']."'\n"
    ." AND {{questions}}.language='".$_SESSION['survey_'.$surveyid]['s_lang']."'\n"
    ." AND {{questions}}.parent_qid=0\n";

    $totalquestions = Yii::app()->db->createCommand($sQuery)->queryScalar();

    // Fix totalquestions by substracting Test Display questions
    $iNumberofQuestions=dbExecuteAssoc("SELECT count(*)\n"
    ." FROM {{questions}}"
    ." WHERE type in ('X','*')\n"
    ." AND sid={$surveyid}"
    ." AND language='".$_SESSION['survey_'.$surveyid]['s_lang']."'"
    ." AND parent_qid=0")->read();

    $_SESSION['survey_'.$surveyid]['totalquestions'] = $totalquestions - (int) reset($iNumberofQuestions);
	
    //2. SESSION VARIABLE: totalsteps
    //The number of "pages" that will be presented in this survey
    //The number of pages to be presented will differ depending on the survey format
    switch($thissurvey['format'])
    {
        case "A":
            $_SESSION['survey_'.$surveyid]['totalsteps']=1;
            break;
        case "G":
            if (isset($_SESSION['survey_'.$surveyid]['grouplist']))
            {
                $_SESSION['survey_'.$surveyid]['totalsteps']=count($_SESSION['survey_'.$surveyid]['grouplist']);
            }
            break;
        case "S":
            $_SESSION['survey_'.$surveyid]['totalsteps']=$totalquestions;
    }


    if ($totalquestions == 0)	//break out and crash if there are no questions!
    {
        sendCacheHeaders();
        doHeader();

        $redata = compact(array_keys(get_defined_vars()));
        echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[1914]');
        echo templatereplace(file_get_contents($sTemplatePath."survey.pstpl"),array(),$redata,'frontend_helper[1915]');
        echo "\t<div id='wrapper'>\n"
        ."\t<p id='tokenmessage'>\n"
        ."\t".$clang->gT("This survey does not yet have any questions and cannot be tested or completed.")."<br /><br />\n"
        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
        ."{$thissurvey['adminemail']}</a>)<br /><br />\n"
        ."\t</p>\n"
        ."\t</div>\n";

        echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[1925]');
        doFooter();
        exit;
    }

    //Perform a case insensitive natural sort on group name then question title of a multidimensional array
    //	usort($arows, 'groupOrderThenQuestionOrder');

    //3. SESSION VARIABLE - insertarray
    //An array containing information about used to insert the data into the db at the submit stage
    //4. SESSION VARIABLE - fieldarray
    //See rem at end..
    $_SESSION['survey_'.$surveyid]['token'] = $clienttoken;

    if ($thissurvey['anonymized'] == "N")
    {
        $_SESSION['survey_'.$surveyid]['insertarray'][]= "token";
    }

    if ($tokensexist == 1 && $thissurvey['anonymized'] == "N"  && tableExists('{{tokens_'.$surveyid.'}}'))
    {
        //Gather survey data for "non anonymous" surveys, for use in presenting questions
        $_SESSION['survey_'.$surveyid]['thistoken']=getTokenData($surveyid, $clienttoken);
    }
    $qtypes=getQuestionTypeList('','array');
    $fieldmap=createFieldMap($surveyid,'full',true,false,$_SESSION['survey_'.$surveyid]['s_lang']);


    // Randomization groups for groups
    $aRandomGroups=array();
    $aGIDCompleteMap=array();
    // first find all groups and their groups IDS
    $criteria = new CDbCriteria;
    $criteria->addColumnCondition(array('sid' => $surveyid, 'language' => $_SESSION['survey_'.$surveyid]['s_lang']));
    $criteria->addCondition("randomization_group != ''");
    $oData = Groups::model()->findAll($criteria);
    foreach($oData as $aGroup)
    {
        $aRandomGroups[$aGroup['randomization_group']][] = $aGroup['gid'];
    }
    // Shuffle each group and create a map for old GID => new GID
    foreach ($aRandomGroups as $sGroupName=>$aGIDs)
    {
        $aShuffledIDs=$aGIDs;
        shuffle($aShuffledIDs);
        $aGIDCompleteMap=$aGIDCompleteMap+array_combine($aGIDs,$aShuffledIDs);
    }
    $_SESSION['survey_' . $surveyid]['groupReMap'] = $aGIDCompleteMap;

    $randomized = false;    // So we can trigger reorder once for group and question randomization
    // Now adjust the grouplist
    if (count($aRandomGroups)>0)
    {
        $randomized = true;    // So we can trigger reorder once for group and question randomization
        // Now adjust the grouplist
        Yii::import('application.helpers.frontend_helper', true);   // make sure frontend helper is loaded
        UpdateGroupList($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);
        // ... and the fieldmap

        // First create a fieldmap with GID as key
        foreach ($fieldmap as $aField)
        {
            if (isset($aField['gid']))
            {
                $GroupFieldMap[$aField['gid']][]=$aField;
            } 
            else{
                $GroupFieldMap['other'][]=$aField;
            }
        }
        // swap it
        foreach ($GroupFieldMap as $iOldGid => $fields)
        {
            $iNewGid = $iOldGid;
            if (isset($aGIDCompleteMap[$iOldGid]))
            {
                $iNewGid = $aGIDCompleteMap[$iOldGid];
            }
            $newGroupFieldMap[$iNewGid] = $GroupFieldMap[$iNewGid];
        }
        $GroupFieldMap = $newGroupFieldMap;
        // and convert it back to a fieldmap
        unset($fieldmap);
        foreach($GroupFieldMap as $aGroupFields)
        {
            foreach ($aGroupFields as $aField)
            {
                if (isset($aField['fieldname'])) {
                    $fieldmap[$aField['fieldname']] = $aField;  // isset() because of the shuffled flag above
                }
            }
        }
        unset($GroupFieldMap);
    }

    // Randomization groups for questions

    // Find all defined randomization groups through question attribute values
    $randomGroups=array();
    if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv')))
    {
        $rgquery = "SELECT attr.qid, CAST(value as varchar(255)) as value FROM {{question_attributes}} as attr right join {{questions}} as quests on attr.qid=quests.qid WHERE attribute='random_group' and CAST(value as varchar(255)) <> '' and sid=$surveyid GROUP BY attr.qid, CAST(value as varchar(255))";
    }
    else
    {
        $rgquery = "SELECT attr.qid, value FROM {{question_attributes}} as attr right join {{questions}} as quests on attr.qid=quests.qid WHERE attribute='random_group' and value <> '' and sid=$surveyid GROUP BY attr.qid, value";
    }
    $rgresult = dbExecuteAssoc($rgquery);
    foreach($rgresult->readAll() as $rgrow)
    {
        // Get the question IDs for each randomization group
        $randomGroups[$rgrow['value']][] = $rgrow['qid'];
    }

    // If we have randomization groups set, then lets cycle through each group and
    // replace questions in the group with a randomly chosen one from the same group
    if (count($randomGroups) > 0)
    {
        $randomized   = true;    // So we can trigger reorder once for group and question randomization
        $copyFieldMap = array();
        $oldQuestOrder = array();
        $newQuestOrder = array();
        $randGroupNames = array();
        foreach ($randomGroups as $key=>$value)
        {
            $oldQuestOrder[$key] = $randomGroups[$key];
            $newQuestOrder[$key] = $oldQuestOrder[$key];
            // We shuffle the question list to get a random key->qid which will be used to swap from the old key
            shuffle($newQuestOrder[$key]);
            $randGroupNames[] = $key;
        }

        // Loop through the fieldmap and swap each question as they come up
        foreach ($fieldmap as $fieldkey => $fieldval)
        {
            $found = 0;
            foreach ($randomGroups as $gkey => $gval)
            {
                // We found a qid that is in the randomization group
                if (isset($fieldval['qid']) && in_array($fieldval['qid'],$oldQuestOrder[$gkey]))
                {
                    // Get the swapped question
                    $idx = array_search($fieldval['qid'],$oldQuestOrder[$gkey]);
                    foreach ($fieldmap as $key => $field)
                    {
                        if (isset($field['qid']) && $field['qid'] == $newQuestOrder[$gkey][$idx])
                        {
                            $field['random_gid'] = $fieldval['gid'];   // It is possible to swap to another group
                            $copyFieldMap[$key]  = $field;
                        }
                    }
                    $found = 1;
                    break;
                } else
                {
                    $found = 2;
                }
            }
            if ($found == 2)
            {
                $copyFieldMap[$fieldkey]=$fieldval;
            }
            reset($randomGroups);
        }
        $fieldmap = $copyFieldMap;
    }

    if ($randomized === true)
    {
        // reset the sequencing counts
        $gseq = -1;
        $_gid = -1;
        $qseq = -1;
        $_qid = -1;
        $copyFieldMap = array();
        foreach ($fieldmap as $key => $val)
        {
            if ($val['gid'] != '')
            {
                if (isset($val['random_gid']))
                {
                    $gid = $val['random_gid'];
                } else {
                    $gid = $val['gid'];
                }
                if ($gid != $_gid)
                {
                    $_gid = $gid;
                    ++$gseq;
                }
            }

            if ($val['qid'] != '' && $val['qid'] != $_qid)
            {
                $_qid = $val['qid'];
                ++$qseq;
            }

            if ($val['gid'] != '' && $val['qid'] != '')
            {
                $val['groupSeq']    = $gseq;
                $val['questionSeq'] = $qseq;
            }

            $copyFieldMap[$key] = $val;
        }
        $fieldmap = $copyFieldMap;
        unset($copyFieldMap);

        $_SESSION['survey_'.$surveyid]['fieldmap-' . $surveyid . $_SESSION['survey_'.$surveyid]['s_lang']] = $fieldmap;
        $_SESSION['survey_'.$surveyid]['fieldmap-' . $surveyid . '-randMaster'] = 'fieldmap-' . $surveyid . $_SESSION['survey_'.$surveyid]['s_lang'];
    }
    
    // TMSW Conditions->Relevance:  don't need hasconditions, or usedinconditions

    $_SESSION['survey_'.$surveyid]['fieldmap']=$fieldmap;
    foreach ($fieldmap as $field)
    {
        if (isset($field['qid']) && $field['qid']!='')
        {
            $_SESSION['survey_'.$surveyid]['fieldnamesInfo'][$field['fieldname']]=$field['sid'].'X'.$field['gid'].'X'.$field['qid'];
            $_SESSION['survey_'.$surveyid]['insertarray'][]=$field['fieldname'];
            //fieldarray ARRAY CONTENTS -
            //            [0]=questions.qid,
            //			[1]=fieldname,
            //			[2]=questions.title,
            //			[3]=questions.question
            //                 	[4]=questions.type,
            //			[5]=questions.gid,
            //			[6]=questions.mandatory,
            //			[7]=conditionsexist,
            //			[8]=usedinconditions
            //			[8]=usedinconditions
            //			[9]=used in group.php for question count
            //			[10]=new group id for question in randomization group (GroupbyGroup Mode)

            if (!isset($_SESSION['survey_'.$surveyid]['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]))
            {
                //JUST IN CASE : PRECAUTION!
                //following variables are set only if $style=="full" in createFieldMap() in common_helper.
                //so, if $style = "short", set some default values here!
                if (isset($field['title']))
                    $title = $field['title'];
                else
                    $title = "";

                if (isset($field['question']))
                    $question = $field['question'];
                else
                    $question = "";

                if (isset($field['mandatory']))
                    $mandatory = $field['mandatory'];
                else
                    $mandatory = 'N';

                if (isset($field['hasconditions']))
                    $hasconditions = $field['hasconditions'];
                else
                    $hasconditions = 'N';

                if (isset($field['usedinconditions']))
                    $usedinconditions = $field['usedinconditions'];
                else
                    $usedinconditions = 'N';
                $_SESSION['survey_'.$surveyid]['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]=array($field['qid'],
                $field['sid'].'X'.$field['gid'].'X'.$field['qid'],
                $title,
                $question,
                $field['type'],
                $field['gid'],
                $mandatory,
                $hasconditions,
                $usedinconditions);
            }
            if (isset($field['random_gid']))
            {
                $_SESSION['survey_'.$surveyid]['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']][10] = $field['random_gid'];
            }
        }

    }

    // Prefill questions/answers from command line params
    $reservedGetValues= array('token','sid','gid','qid','lang','newtest','action');
    $startingValues=array();
    if (isset($_GET))
    {
        foreach ($_GET as $k=>$v)
        {
            if (!in_array($k,$reservedGetValues) && isset($_SESSION['survey_'.$surveyid]['fieldmap'][$k]))
            {
                $startingValues[$k] = $v;
            }
        }
    }
    $_SESSION['survey_'.$surveyid]['startingValues']=$startingValues;

    if (isset($_SESSION['survey_'.$surveyid]['fieldarray'])) $_SESSION['survey_'.$surveyid]['fieldarray']=array_values($_SESSION['survey_'.$surveyid]['fieldarray']);

    //Check if a passthru label and value have been included in the query url
    $oResult=Survey_url_parameters::model()->getParametersForSurvey($surveyid);
    foreach($oResult->readAll() as $aRow)
    {
        if(isset($_GET[$aRow['parameter']]) && !$preview)
        {
            $_SESSION['survey_'.$surveyid]['urlparams'][$aRow['parameter']]=$_GET[$aRow['parameter']];
            if ($aRow['targetqid']!='')
            {
                foreach ($fieldmap as $sFieldname=>$aField)
                {
                    if ($aRow['targetsqid']!='')
                    {
                        if ($aField['qid']==$aRow['targetqid'] && $aField['sqid']==$aRow['targetsqid'])
                        {
                            $_SESSION['survey_'.$surveyid]['startingValues'][$sFieldname]=$_GET[$aRow['parameter']];
                            $_SESSION['survey_'.$surveyid]['startingValues'][$aRow['parameter']]=$_GET[$aRow['parameter']];
                        }
                    }
                    else
                    {
                        if ($aField['qid']==$aRow['targetqid'])
                        {
                            $_SESSION['survey_'.$surveyid]['startingValues'][$sFieldname]=$_GET[$aRow['parameter']];
                            $_SESSION['survey_'.$surveyid]['startingValues'][$aRow['parameter']]=$_GET[$aRow['parameter']];
                        }
                    }
                }

            }
        }
    }
}

function surveymover()
{
    //This function creates the form elements in the survey navigation bar
    //with "<<PREV" or ">>NEXT" in them. The "submit" value determines how the script moves from
    //one survey page to another. It is a hidden element, updated by clicking
    //on the  relevant button - allowing "NEXT" to be the default setting when
    //a user presses enter.
    //
    //Attribute accesskey added for keyboard navigation.
    global $thissurvey, $clang;
    global $surveyid, $presentinggroupdescription;

    $clang = Yii::app()->lang;
    $LEMsessid = Yii::app()->getConfig('surveyID');

    $surveymover = "";

    if ($thissurvey['navigationdelay'] > 0 && (
    isset($_SESSION['survey_'.$surveyid]['maxstep']) && $_SESSION['survey_'.$surveyid]['maxstep'] > 0 && $_SESSION['survey_'.$surveyid]['maxstep'] == $_SESSION['survey_'.$surveyid]['step']))
    {
        $disabled = "disabled=\"disabled\"";
        $surveymover .= "<script type=\"text/javascript\">\n"
        . "  navigator_countdown(" . $thissurvey['navigationdelay'] . ");\n"
        . "</script>\n";
    }
    else
    {
        $disabled = "";
    }

    if (isset($_SESSION['survey_'.$surveyid]['step']) && $_SESSION['survey_'.$surveyid]['step'] && ($_SESSION['survey_'.$surveyid]['step'] == $_SESSION['survey_'.$surveyid]['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
    {
        $surveymover .= "<input type=\"hidden\" name=\"move\" value=\"movesubmit\" id=\"movesubmit\" />";
    }
    else
    {
        $surveymover .= "<input type=\"hidden\" name=\"move\" value=\"movenext\" id=\"movenext\" />";
    }

    if (isset($_SESSION['survey_'.$surveyid]['step']) && $thissurvey['format'] != "A" && ($thissurvey['allowprev'] != "N" || $thissurvey['allowjumps'] == "Y") &&
    ($_SESSION['survey_'.$surveyid]['step'] > 0 || (!$_SESSION['survey_'.$surveyid]['step'] && $presentinggroupdescription && $thissurvey['showwelcome'] == 'Y')))
    {
        //To prevent too much complication in the if statement above I put it here...
        if ($thissurvey['showwelcome'] == 'N' && $_SESSION['survey_'.$surveyid]['step'] == 1) {
            //first step and we do not want to go back to the welcome screen since we don't show that...
            //so skip the prev button
        } else {
            $surveymover .= "<button class='submit' accesskey='p' type='button' onclick=\"javascript:document.limesurvey.move.value = 'moveprev'; $('#limesurvey').submit();\" value='"
            . $clang->gT("Previous")."' name='move2' id='moveprevbtn' $disabled>". $clang->gT("Previous")."</button>\n";
        }
    }

    if (isset($_SESSION['survey_'.$surveyid]['step']) && $_SESSION['survey_'.$surveyid]['step'] && (!$_SESSION['survey_'.$surveyid]['totalsteps'] || ($_SESSION['survey_'.$surveyid]['step'] < $_SESSION['survey_'.$surveyid]['totalsteps'])))
    {
        $surveymover .=  "\t<button class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\"
        value='".$clang->gT("Next")."' name='move2' id='movenextbtn' $disabled>".$clang->gT("Next")."</button>\n";
    }
    // here, in some lace, is where I must modify to turn the next button conditionable
    if (!isset($_SESSION['survey_'.$surveyid]['step']) || !$_SESSION['survey_'.$surveyid]['step'])
    {
        $surveymover .=  "\t<button class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\"
        value='".$clang->gT("Next")."' name='move2' id='movenextbtn' $disabled>".$clang->gT("Next")."</button>\n";
    }
    if (isset($_SESSION['survey_'.$surveyid]['step']) && $_SESSION['survey_'.$surveyid]['step'] && ($_SESSION['survey_'.$surveyid]['step'] == $_SESSION['survey_'.$surveyid]['totalsteps']) && $presentinggroupdescription == "yes")
    {
        $surveymover .=  "\t<button class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\"
        value='".$clang->gT("Next")."' name='move2' id=\"movenextbtn\" $disabled>".$clang->gT("Next")."</button>\n";
    }
    if (isset($_SESSION['survey_'.$surveyid]['step']) && ($_SESSION['survey_'.$surveyid]['step'] && ($_SESSION['survey_'.$surveyid]['step'] == $_SESSION['survey_'.$surveyid]['totalsteps']) && !$presentinggroupdescription) || $thissurvey['format'] == 'A')
    {
        $surveymover .= "\t<button class=\"submit\" type=\"submit\" accesskey=\"l\" onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\"
        value=\"".$clang->gT("Submit")."\" name=\"move2\" id=\"movesubmitbtn\" $disabled>".$clang->gT("Submit")."</button>\n";
    }

    return $surveymover;
}

/**
* Caculate assessement scores
*
* @param mixed $surveyid
* @param mixed $returndataonly - only returns an array with data
*/
function doAssessment($surveyid, $returndataonly=false)
{

    $clang = Yii::app()->lang;
    $baselang=Survey::model()->findByPk($surveyid)->language;
    if(Survey::model()->findByPk($surveyid)->assessments!="Y")
    {
        return false;
    }
    $total=0;
    if (!isset($_SESSION['survey_'.$surveyid]['s_lang']))
    {
        $_SESSION['survey_'.$surveyid]['s_lang']=$baselang;
    }
    $query = "SELECT * FROM {{assessments}}
    WHERE sid=$surveyid and language='".$_SESSION['survey_'.$surveyid]['s_lang']."'
    ORDER BY scope, id";

    if ($result = dbExecuteAssoc($query))   //Checked
    {
        $aResultSet=$result->readAll();
        if (count($aResultSet) > 0)
        {
            foreach($aResultSet as $row)
            {
                if ($row['scope'] == "G")
                {
                    $assessment['group'][$row['gid']][]=array("name"=>$row['name'],
                    "min"=>$row['minimum'],
                    "max"=>$row['maximum'],
                    "message"=>$row['message']);
                }
                else
                {
                    $assessment['total'][]=array( "name"=>$row['name'],
                    "min"=>$row['minimum'],
                    "max"=>$row['maximum'],
                    "message"=>$row['message']);
                }
            }
            $fieldmap=createFieldMap($surveyid, "full",false,false,$_SESSION['survey_'.$surveyid]['s_lang']);
            $i=0;
            $total=0;
            $groups=array();
            foreach($fieldmap as $field)
            {
                if (in_array($field['type'],array('1','F','H','W','Z','L','!','M','O','P')))
                {
                    $fieldmap[$field['fieldname']]['assessment_value']=0;
                    if (isset($_SESSION['survey_'.$surveyid][$field['fieldname']]))
                    {
                        if (($field['type'] == "M") || ($field['type'] == "P")) //Multiflexi choice  - result is the assessment attribute value
                        {
                            if ($_SESSION['survey_'.$surveyid][$field['fieldname']] == "Y")
                            {
                                $aAttributes=getQuestionAttributeValues($field['qid'],$field['type']);
                                $fieldmap[$field['fieldname']]['assessment_value']=(int)$aAttributes['assessment_value'];
                                $total=$total+(int)$aAttributes['assessment_value'];
                            }
                        }
                        else  // Single choice question
                        {
                            $usquery = "SELECT assessment_value FROM {{answers}} where qid=".$field['qid']." and language='$baselang' and code=".dbQuoteAll($_SESSION['survey_'.$surveyid][$field['fieldname']]);
                            $usresult = dbExecuteAssoc($usquery);          //Checked
                            if ($usresult)
                            {
                                $usrow = $usresult->read();
                                $fieldmap[$field['fieldname']]['assessment_value']=$usrow['assessment_value'];
                                $total=$total+$usrow['assessment_value'];
                            }
                        }
                    }
                    $groups[]=$field['gid'];
                }
                $i++;
            }

            $groups=array_unique($groups);

            foreach($groups as $group)
            {
                $grouptotal=0;
                foreach ($fieldmap as $field)
                {
                    if ($field['gid'] == $group && isset($field['assessment_value']))
                    {
                        //$grouptotal=$grouptotal+$field['answer'];
                        if (isset ($_SESSION['survey_'.$surveyid][$field['fieldname']]))
                        {
                            $grouptotal=$grouptotal+$field['assessment_value'];
                        }
                    }
                }
                $subtotal[$group]=$grouptotal;
            }
        }
        $assessments = "";
        if (isset($subtotal) && is_array($subtotal))
        {
            foreach($subtotal as $key=>$val)
            {
                if (isset($assessment['group'][$key]))
                {
                    foreach($assessment['group'][$key] as $assessed)
                    {
                        if ($val >= $assessed['min'] && $val <= $assessed['max'] && $returndataonly===false)
                        {
                            $assessments .= "\t<!-- GROUP ASSESSMENT: Score: $val Min: ".$assessed['min']." Max: ".$assessed['max']."-->
                            <table class='assessments' align='center'>
                            <tr>
                            <th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['name'])."
                            </th>
                            </tr>
                            <tr>
                            <td align='center'>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['message'])."
                            </td>
                            </tr>
                            </table><br />\n";
                        }
                    }
                }
            }
        }

        if (isset($assessment['total']))
        {
            foreach($assessment['total'] as $assessed)
            {
                if ($total >= $assessed['min'] && $total <= $assessed['max'] && $returndataonly===false)
                {
                    $assessments .= "\t\t\t<!-- TOTAL ASSESSMENT: Score: $total Min: ".$assessed['min']." Max: ".$assessed['max']."-->
                    <table class='assessments' align='center'><tr><th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), stripslashes($assessed['name']))."
                    </th></tr>
                    <tr>
                    <td align='center'>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), stripslashes($assessed['message']))."
                    </td>
                    </tr>
                    </table>\n";
                }
            }
        }
        if ($returndataonly==true)
        {
            return array('total'=>$total);
        }
        else
        {
            return $assessments;
        }
    }
}

function UpdateGroupList($surveyid, $language)
//1. SESSION VARIABLE: grouplist
//A list of groups in this survey, ordered by group name.

{


    $clang = Yii::app()->lang;
    unset ($_SESSION['survey_'.$surveyid]['grouplist']);
    $query = "SELECT * FROM {{groups}} WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
    $result = dbExecuteAssoc($query) or safeDie ("Couldn't get group list<br />$query<br />");  //Checked
    $groupList = array();
    foreach ($result->readAll() as $row)
    {
        $group = array(
            'gid'         => $row['gid'], 
            'group_name'  => $row['group_name'],
            'description' =>  $row['description']);
        $groupList[] = $group;
        $gidList[$row['gid']] = $group;
    }
    
    if (isset($_SESSION['survey_'.$surveyid]['groupReMap']) && count($_SESSION['survey_'.$surveyid]['groupReMap'])>0)
    {
        // Now adjust the grouplist
        $groupRemap = $_SESSION['survey_'.$surveyid]['groupReMap'];
        $groupListCopy = $groupList;
        foreach ($groupList as $gseq => $info) {
            $gid = $info['gid']; 
            if (isset($groupRemap[$gid])) {
                $gid = $groupRemap[$gid];
            }
            $groupListCopy[$gseq] = $gidList[$gid];
        }
        $groupList = $groupListCopy;
     }
     
     $_SESSION['survey_'.$surveyid]['grouplist'] = $groupList;
}

/**
* FieldArray contains all necessary information regarding the questions
* This function is needed to update it in case the survey is switched to another language
* @todo: Make 'fieldarray' obsolete by replacing with EM session info
*/
function UpdateFieldArray()
{
    global $surveyid;

    $clang = Yii::app()->lang;

    if (isset($_SESSION['survey_'.$surveyid]['fieldarray']))
    {
        reset($_SESSION['survey_'.$surveyid]['fieldarray']);
        while ( list($key) = each($_SESSION['survey_'.$surveyid]['fieldarray']) )
        {
            $questionarray =& $_SESSION['survey_'.$surveyid]['fieldarray'][$key];

            $query = "SELECT title, question FROM {{questions}} WHERE qid=".$questionarray[0]." AND language='".$_SESSION['survey_'.$surveyid]['s_lang']."'";
            $usrow = Yii::app()->db->createCommand($query)->queryRow();
            if ($usrow) 
            {
                $questionarray[2]=$usrow['title'];
                $questionarray[3]=$usrow['question'];
            }
            unset($questionarray);
        }
    }
}

/**
* checkQuota() returns quota information for the current survey
* @param string $checkaction - action the function must take after completing:
* 								enforce: Enforce the Quota action
* 								return: Return the updated quota array from getQuotaAnswers()
* @param string $surveyid - Survey identification number
* @return array - nested array, Quotas->Members->Fields, includes quota status and which members matched in session.
*/
function checkQuota($checkaction,$surveyid)
{
    global $clienttoken ;
    if (!isset($_SESSION['survey_'.$surveyid]['srid']))
    {
        return;
    }
    $thissurvey=getSurveyInfo($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);
    $sTemplatePath=getTemplatePath($thissurvey['templatedir']);

    $global_matched = false;
    $quota_info = getQuotaInformation($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);
    $x=0;

    $clang = Yii::app()->lang;

    if(count($quota_info) > 0) // Quota's have to exist
    {
        // Check each quota on saved data to see if it is full
        $querycond = array();
        foreach ($quota_info as $quota)
        {
            if (count($quota['members']) > 0) // Quota can't be empty
            {
                $fields_list = array(); // Keep a list of fields for easy reference
                $y=0;
                // We need to make the conditions for the select statement here
                unset($querycond);
                // fill the array of value and query for each fieldnames
                $fields_value_array = array();
                $fields_query_array = array();
                foreach($quota['members'] as $member)
                {
                    foreach($member['fieldnames'] as $fieldname)
                    {
                        if (!in_array($fieldname,$fields_list))
                        {
                            $fields_list[] = $fieldname;
                            $fields_value_array[$fieldname] = array();
                            $fields_query_array[$fieldname] = array();
                        }
                        $fields_value_array[$fieldname][]=$member['value'];
                        $fields_query_array[$fieldname][]= dbQuoteID($fieldname)." = '{$member['value']}'";
                    }
                }

                // fill the $querycond array with each fields_query grouped by fieldname
                foreach($fields_list as $fieldname)
                {
                    $select_query = " ( ".implode(' OR ',$fields_query_array[$fieldname]).' )';
                    $querycond[] = $select_query;
                }
                // Test if the fieldname is in the array of value in the session
                foreach($quota['members'] as $member)
                {
                    foreach($member['fieldnames'] as $fieldname)
                    {
                        if (isset($_SESSION['survey_'.$surveyid][$fieldname]))
                        {
                            if (in_array($_SESSION['survey_'.$surveyid][$fieldname],$fields_value_array[$fieldname])){
                                $quota_info[$x]['members'][$y]['insession'] = "true";
                            }
                        }
                    }
                    $y++;
                }
                unset($fields_query_array);unset($fields_value_array);

                // Lets only continue if any of the quota fields is in the posted page
                $matched_fields = false;
                if (isset($_POST['fieldnames']))
                {
                    $posted_fields = explode("|",$_POST['fieldnames']);
                    foreach ($fields_list as $checkfield)
                    {
                        if (in_array($checkfield,$posted_fields))
                        {
                            $matched_fields = true;
                            $global_matched = true;
                        }
                    }
                }

                // A field was submitted that is part of the quota
                if ($matched_fields == true)
                {
                    // Check the status of the quota, is it full or not
                    $sQuery = "SELECT count(id) FROM {{survey_".$surveyid."}}
                    WHERE ".implode(' AND ',$querycond)." "."
                    AND submitdate IS NOT NULL";
                    $iRowCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
                    if ($iRowCount >= $quota['Limit']) // Quota is full!!
                    {
                        // Now we have to check if the quota matches in the current session
                        // This will let us know if this person is going to exceed the quota
                        $counted_matches = 0;
                        foreach($quota_info[$x]['members'] as $member)
                        {
                            if (isset($member['insession']) && $member['insession'] == "true") $counted_matches++;
                        }

                        if($counted_matches == count($quota['members']))
                        {
                            // They are going to exceed the quota if data is submitted
                            $quota_info[$x]['status']="matched";
                        }
                        else
                        {
                            $quota_info[$x]['status']="notmatched";
                        }
                    }
                    else
                    {
                        // Quota is no in danger of being exceeded.
                        $quota_info[$x]['status']="notmatched";
                    }
                }
            }
            $x++;
        }
    }
    else
    {
        return false;
    }

    // Now we have all the information we need about the quotas and their status.
    // Lets see what we should do now
    if ($checkaction == 'return')
    {
        return $quota_info;
    }
    elseif ($global_matched == true && $checkaction == 'enforce')
    {
        // Need to add Quota action enforcement here.
        reset($quota_info);

        $tempmsg ="";
        $found = false;
        $redata = compact(array_keys(get_defined_vars()));
        foreach($quota_info as $quota)
        {
            $quota['Message'] = templatereplace($quota['Message'],array(),$redata);
            $quota['Url'] = passthruReplace($quota['Url'], $thissurvey);
            $quota['Url'] = templatereplace($quota['Url'],array(),$redata);
            $quota['UrlDescrip'] = templatereplace($quota['UrlDescrip'],array(),$redata);
            if ((isset($quota['status']) && $quota['status'] == "matched") && (isset($quota['Action']) && $quota['Action'] == "1"))
            {
                // If a token is used then mark the token as completed
                if (isset($clienttoken) && $clienttoken)
                {
                    submittokens(true);
                }

            sendCacheHeaders();
            if($quota['AutoloadUrl'] == 1 && $quota['Url'] != "")
            {
                header("Location: ".$quota['Url']);
                killSurveySession($surveyid);
            }
            doHeader();

            echo templatereplace(file_get_contents($sTemplatePath."/startpage.pstpl"),array(),$redata,'frontend_helper[2617]');
            echo "\t<div class='quotamessage'>\n";
            echo "\t".$quota['Message']."<br /><br />\n";
            echo "\t<a href='".$quota['Url']."'>".$quota['UrlDescrip']."</a><br />\n";
            echo "\t</div>\n";
            echo templatereplace(file_get_contents($sTemplatePath."/endpage.pstpl"),array(),$redata,'frontend_helper[2622]');
            doFooter();
            killSurveySession($surveyid);
            exit;
            }

            if ((isset($quota['status']) && $quota['status'] == "matched") && (isset($quota['Action']) && $quota['Action'] == "2"))
            {

                sendCacheHeaders();
                doHeader();

                $redata = compact(array_keys(get_defined_vars()));
                echo templatereplace(file_get_contents($sTemplatePath."/startpage.pstpl"),array(),$redata,'frontend_helper[2634]');
                echo "\t<div class='quotamessage'>\n";
                echo "\t".$quota['Message']."<br /><br />\n";
                echo "\t<a href='".$quota['Url']."'>".$quota['UrlDescrip']."</a><br />\n";
                echo CHtml::form(array("/survey/index"), 'post', array('id'=>'limesurvey','name'=>'limesurvey'))."
                <input type='hidden' name='move' value='movenext' id='movenext' />
                <button class='nav-button nav-button-icon-left ui-corner-all' class='submit' accesskey='p' onclick=\"javascript:document.limesurvey.move.value = 'moveprev'; document.limesurvey.submit();\" id='moveprevbtn'>".$clang->gT("Previous")."</button>
                <input type='hidden' name='thisstep' value='".($_SESSION['survey_'.$surveyid]['step'])."' id='thisstep' />
                <input type='hidden' name='sid' value='".returnGlobal('sid')."' id='sid' />
                <input type='hidden' name='token' value='".$clienttoken."' id='token' />
                </form>\n";
                echo "\t</div>\n";
                echo templatereplace(file_get_contents($sTemplatePath."/endpage.pstpl"),array(),$redata,'frontend_helper[2644]');
                doFooter();
                exit;
            }
        }
    }
    else
    {
        // Unknown value
        return false;
    }

}

/**
* put your comment there...
*
* @param mixed $mail
* @param mixed $text
* @param mixed $class
* @param mixed $params
*/
function encodeEmail($mail, $text="", $class="", $params=array())
{
    $encmail ="";
    for($i=0; $i<strlen($mail); $i++)
    {
        $encMod = rand(0,2);
        switch ($encMod)
        {
            case 0: // None
                $encmail .= substr($mail,$i,1);
                break;
            case 1: // Decimal
                $encmail .= "&#".ord(substr($mail,$i,1)).';';
                break;
            case 2: // Hexadecimal
                $encmail .= "&#x".dechex(ord(substr($mail,$i,1))).';';
                break;
        }
    }

    if(!$text)
    {
        $text = $encmail;
    }
    return $text;
}

/**
* GetReferringUrl() returns the referring URL
* @return string
*/
function GetReferringUrl()
{
    global $clang;

    $clang = Yii::app()->lang;

    // read it from server variable
    if(isset($_SERVER["HTTP_REFERER"]))
    {
        if(!preg_match('/'.$_SERVER["SERVER_NAME"].'/', $_SERVER["HTTP_REFERER"]))
        {
            if (!Yii::app()->getConfig('strip_query_from_referer_url'))
            {
                return $_SERVER["HTTP_REFERER"];
            }
            else
            {
                $aRefurl = explode("?",$_SERVER["HTTP_REFERER"]);
                return $aRefurl[0];
            }
        }
        else
        {
            return '-';
        }
    }
    else
    {
        return null;
    }
}

/**
* Shows the welcome page, used in group by group and question by question mode
*/
function display_first_page() {
    global $token, $surveyid, $thissurvey, $navigator;
	$totalquestions = $_SESSION['survey_'.$surveyid]['totalquestions'];

    $clang = Yii::app()->lang;

    // Fill some necessary var for template
    $navigator = surveymover();
    $sitename = Yii::app()->getConfig('sitename');
    $languagechanger=makeLanguageChangerSurvey($clang->langcode);

    sendCacheHeaders();
    doHeader();

    LimeExpressionManager::StartProcessingPage();
    LimeExpressionManager::StartProcessingGroup(-1, false, $surveyid);  // start on welcome page

    $redata = compact(array_keys(get_defined_vars()));
    $sTemplatePath=$_SESSION['survey_'.$surveyid]['templatepath'];

    echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"),array(),$redata,'frontend_helper[2757]');
    echo CHtml::form(array("/survey/index"), 'post', array('id'=>'limesurvey','name'=>'limesurvey','autocomplete'=>'off'));
    echo "\n\n<!-- START THE SURVEY -->\n";

    echo templatereplace(file_get_contents($sTemplatePath."welcome.pstpl"),array(),$redata,'frontend_helper[2762]')."\n";
    if ($thissurvey['anonymized'] == "Y")
    {
        echo templatereplace(file_get_contents($sTemplatePath."/privacy.pstpl"),array(),$redata,'frontend_helper[2765]')."\n";
    }
    echo templatereplace(file_get_contents($sTemplatePath."navigator.pstpl"),array(),$redata,'frontend_helper[2767]');
    if ($thissurvey['active'] != "Y")
    {
        echo "<p style='text-align:center' class='error'>".$clang->gT("This survey is currently not active. You will not be able to save your responses.")."</p>\n";
    }
    echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
    if (isset($token) && !empty($token)) {
        echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
    }
    echo "\n<input type='hidden' name='lastgroupname' value='_WELCOME_SCREEN_' id='lastgroupname' />\n"; //This is to ensure consistency with mandatory checks, and new group test
    $loadsecurity = returnGlobal('loadsecurity');
    if (isset($loadsecurity)) {
        echo "\n<input type='hidden' name='loadsecurity' value='$loadsecurity' id='loadsecurity' />\n";
    }
    $_SESSION['survey_'.$surveyid]['LEMpostKey'] = mt_rand();
    echo "<input type='hidden' name='LEMpostKey' value='{$_SESSION['survey_'.$surveyid]['LEMpostKey']}' id='LEMpostKey' />\n";
    echo "<input type='hidden' name='thisstep' id='thisstep' value='0' />\n";

    echo "\n</form>\n";
    echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"),array(),$redata,'frontend_helper[2782]');

    echo LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
    LimeExpressionManager::FinishProcessingPage();
    doFooter();
}

function killSurveySession($iSurveyID)
{
    // Unset the session
    unset($_SESSION['survey_'.$iSurveyID]);
    // Force EM to refresh
    LimeExpressionManager::SetDirtyFlag();    
}


/**
* Resets all question timers by expiring the related cookie - this needs to be called before any output is done
* @todo Make cookie survey ID aware
*/
function resetTimers()
{
    $cookie=new CHttpCookie('limesurvey_timers', '');
    $cookie->expire = time()- 3600;
    Yii::app()->request->cookies['limesurvey_timers'] = $cookie;
}

//For multilanguage surveys
// If null or 0 is given for $surveyid then the default language from config-defaults.php is returned
function SetSurveyLanguage($surveyid, $language)
{
    $surveyid=sanitize_int($surveyid);
    $default_language = Yii::app()->getConfig('defaultlang');

    if (isset($surveyid) && $surveyid>0)
    {
        $default_survey_language= Survey::model()->findByPk($surveyid)->language;
        $additional_survey_languages = Survey::model()->findByPk($surveyid)->getAdditionalLanguages();
        if (!isset($language) || ($language=='')
        || !( in_array($language,$additional_survey_languages) || $language==$default_survey_language)
        )
        {
            // Language not supported, fall back to survey's default language
            $_SESSION['survey_'.$surveyid]['s_lang'] = $default_survey_language;
        } else {
            $_SESSION['survey_'.$surveyid]['s_lang'] =  $language;
        }
        Yii::import('application.libraries.Limesurvey_lang', true);
        $clang = new limesurvey_lang($_SESSION['survey_'.$surveyid]['s_lang']);
        $thissurvey=getSurveyInfo($surveyid, @$_SESSION['survey_'.$surveyid]['s_lang']);
        Yii::app()->loadHelper('surveytranslator');
        $_SESSION['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);
        LimeExpressionManager::SetEMLanguage($_SESSION['survey_'.$surveyid]['s_lang']);
    }
    else
    {
        if(!$language)
        {
            $language=$default_language;
        }
        $_SESSION['survey_'.$surveyid]['s_lang'] = $language;
        Yii::import('application.libraries.Limesurvey_lang', true);
        $clang = new Limesurvey_lang($language);
    }

    $oApplication=Yii::app();
    $oApplication->lang=$clang;
    return $clang;
}


// Closing PHP tag intentionally left out - yes, it is okay
