<?php

function loadanswers()
{
    global $surveyid;
    global $thissurvey, $thisstep;
    global $clienttoken;
	$CI =& get_instance();
	$_POST = $CI->input->post();
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;
	//$_SESSION = $CI->session->userdata;

    $scid=returnglobal('scid');
    if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
    {
        $query = "SELECT * FROM ".$CI->db->dbprefix('saved_control')." INNER JOIN {$thissurvey['tablename']}
			ON ".$CI->db->dbprefix('saved_control').".srid = {$thissurvey['tablename']}.id
			WHERE ".$CI->db->dbprefix('saved_control').".sid=$surveyid\n";
        if (isset($scid)) //Would only come from email

        {
            $query .= "AND ".$CI->db->dbprefix('saved_control').".scid={$scid}\n";
        }
        $query .="AND ".$CI->db->dbprefix('saved_control').".identifier = '".auto_escape($_SESSION['holdname'])."' ";

		$databasetype = $CI->db->platform();
        if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $databasetype=='mssqlnative')
        {
            $query .="AND CAST(".$CI->db->dbprefix('saved_control').".access_code as varchar(32))= '".md5(auto_unescape($_SESSION['holdpass']))."'\n";
        }
        else
        {
            $query .="AND ".$CI->db->dbprefix('saved_control').".access_code = '".md5(auto_unescape($_SESSION['holdpass']))."'\n";
        }
    }
    elseif (isset($_SESSION['srid']))
    {
        $query = "SELECT * FROM {$thissurvey['tablename']}
			WHERE {$thissurvey['tablename']}.id=".$_SESSION['srid']."\n";
    }
    else
    {
        return;
    }
    $result = db_execute_assoc($query) or safe_die ("Error loading results<br />$query<br />");   //Checked
    if ($result->num_rows() < 1)
    {
        show_error($clang->gT("There is no matching saved survey")."<br />\n");
    }
    else
    {
        //A match has been found. Let's load the values!
        //If this is from an email, build surveysession first
        $row=$result->row_array();
        foreach ($row as $column => $value)
        {
            if ($column == "token")
            {
                $clienttoken=$value;
                $token=$value;
            }
            elseif ($column == "saved_thisstep" && $thissurvey['alloweditaftercompletion'] != 'Y' )
            {
                $_SESSION['step']=$value;
                $thisstep=$value-1;
            }
            elseif ($column =='lastpage' && isset($_GET['token']) && $thissurvey['alloweditaftercompletion'] != 'Y' )
            {
                if ($value<1) $value=1;
                $_SESSION['step']=$value;
                $thisstep=$value-1;
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
                $_SESSION['scid']=$value;
            }
            elseif ($column == "srid")
            {
                $_SESSION['srid']=$value;
            }
            elseif ($column == "datestamp")
            {
                $_SESSION['datestamp']=$value;
            }
            if ($column == "startdate")
            {
                $_SESSION['startdate']=$value;
            }
            else
            {
                //Only make session variables for those in insertarray[]
                if (in_array($column, $_SESSION['insertarray']))
                {
                    if (($_SESSION['fieldmap'][$column]['type'] == 'N' ||
                            $_SESSION['fieldmap'][$column]['type'] == 'K' ||
                            $_SESSION['fieldmap'][$column]['type'] == 'D') && $value == null)
                    {   // For type N,K,D NULL in DB is to be considered as NoAnswer in any case.
                        // We need to set the _SESSION[field] value to '' in order to evaluate conditions.
                        // This is especially important for the deletenonvalue feature,
                        // otherwise we would erase any answer with condition such as EQUALS-NO-ANSWER on such
                        // question types (NKD)
                        $_SESSION[$column]='';
                    }
                    else
                    {
                    $_SESSION[$column]=$value;
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
    global $publicurl, $js_header_includes, $css_header_includes;
	$CI =& get_instance();
	$clang = $CI->limesurvey_lang;
	//$_SESSION = $CI->session->userdata;

	$js_admin_includes = $CI->config->item("js_admin_includes");
	$js_header_includes[] = '/scripts/jquery/jquery-ui.js';
	$CI->config->set_item("js_admin_includes", $js_admin_includes);

	$css_admin_includes = $CI->config->item("js_admin_includes");
    $css_header_includes[]= '/scripts/jquery/css/start/jquery-ui.css';
    $css_header_includes[]= '/scripts/jquery/css/start/lime-progress.css';
	$CI->config->set_item("css_admin_includes", $css_admin_includes);

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

function makelanguagechanger()
{
    global $relativeurl;
    $CI =& get_instance();
	$_POST = $CI->input->post();
	$_REQUEST = $CI->input->get_post();

    if (!isset($surveyid))
    {
        $surveyid=returnglobal('sid');
    }
    if (isset($surveyid))
    {
        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    }

    $token = sanitize_token(returnglobal('token'));
    if ($token != '')
    {
        $tokenparam = "&token=$token";
    }
    else
    {
        $tokenparam = "";
    }
    $previewgrp = false;
    if (isset($_REQUEST['action']))
        if ($_REQUEST['action']=='previewgroup')
            $previewgrp = true;

    if (!empty($slangs))
    {
        if (isset($_SESSION['s_lang']) && $_SESSION['s_lang'] != '')
        {
            $lang = sanitize_languagecode($_SESSION['s_lang']);
        }
        else if(isset($_POST['lang']) && $_POST['lang']!='')
        {
            $lang = sanitize_languagecode($_POST['lang']);
        }
        else if (isset($_GET['lang']) && $_GET['lang'] != '')
        {
            $lang = sanitize_languagecode($_GET['lang']);
        }
        else
        $lang = GetBaseLanguageFromSurveyID($surveyid);

        $htmlcode ="<select name=\"select\" class='languagechanger' onchange=\"javascript:window.location=this.value\">\n";
        $sAddToURL = "";
        $sTargetURL = "$relativeurl/index.php";
        if ($previewgrp){
            $sAddToURL = "&amp;action=previewgroup&amp;gid={$_REQUEST['gid']}";
            $sTargetURL = "";
        }
        foreach ($slangs as $otherlang)
        {
            if($otherlang != $lang)
            $htmlcode .= "\t<option value=\"$sTargetURL?sid=". $surveyid ."&amp;lang=". $otherlang ."$tokenparam$sAddToURL\" >".getLanguageNameFromCode($otherlang,false)."</option>\n";
        }
        if($lang != GetBaseLanguageFromSurveyID($surveyid))
        {
            $htmlcode .= "<option value=\"$sTargetURL?sid=".$surveyid."&amp;lang=".GetBaseLanguageFromSurveyID($surveyid)."$tokenparam$sAddToURL\">".getLanguageNameFromCode(GetBaseLanguageFromSurveyID($surveyid),false)."</option>\n";
        }

        $htmlcode .= "</select>\n";
        //    . "</form>";

        return $htmlcode;
    } elseif (!isset($surveyid))
    {
    	$defaultlang = $CI->config->item("defaultlang");
        global $baselang;
        $htmlcode = "<select name=\"select\" class='languagechanger' onchange=\"javascript:window.location=this.value\">\n";
        $htmlcode .= "<option value=\"$relativeurl/index.php?lang=". $defaultlang ."$tokenparam\">".getLanguageNameFromCode($defaultlang,false)."</option>\n";
        foreach(getlanguagedata() as $key=>$val)
        {
            $htmlcode .= "\t<option value=\"$relativeurl/index.php?lang=".$key."$tokenparam\" ";
            if($key == $baselang)
            {
                $htmlcode .= " selected=\"selected\" ";
            }
            $htmlcode .= ">".getLanguageNameFromCode($key,false)."</option>\n";
        }
        $htmlcode .= "</select>\n";
        return $htmlcode;
    }
}

function checkgroupfordisplay($gid,$anonymized,$surveyid)
{
    //This function checks all the questions in a group to see if they have
    //conditions, and if the do - to see if the conditions are met.
    //If none of the questions in the group are set to display, then
    //the function will return false, to indicate that the whole group
    //should not display at all.
	$CI =& get_instance();
	//$_SESSION = $CI->session->userdata;

    $countQuestionsInThisGroup=0;
    $countConditionalQuestionsInThisGroup=0;
    $countQuestionsWithRelevanceIntThisGroup=0;

    // Initialize LimeExpressionManager for this group - this ensures that values from prior pages are available for assessing relevance on this page
    LimeExpressionManager::StartProcessingPage(false);
    LimeExpressionManager::StartProcessingGroup($gid,$anonymized,$surveyid);

    foreach ($_SESSION['fieldarray'] as $ia) //Run through all the questions

    {
        if ($ia[5] == $gid) //If the question is in the group we are checking:

        {
            // Check if this question is hidden
            $qidattributes=getQuestionAttributeValues($ia[0]);
            if ($qidattributes!==false && ($qidattributes['hidden']==0 || $ia[4]=='*'))
            {
                $countQuestionsInThisGroup++;
                if ($ia[7] == "Y") //This question is conditional

                {
                    $countConditionalQuestionsInThisGroup++;
                    $QuestionsWithConditions[]=$ia; //Create an array containing all the conditional questions
                }
                if (isset($qidattributes['relevance']) && ($qidattributes['relevance'] != 1))
                {
                    $countQuestionsWithRelevanceIntThisGroup++;
                    $QuestionsWithRelevance[]=$qidattributes['relevance'];  // Create an array containing all of the questions whose Relevance Equaation must be processed.
                }
            }
        }
    }
    if ($countQuestionsInThisGroup===0)
    {
        return false;
    }
    elseif ($countQuestionsInThisGroup != $countConditionalQuestionsInThisGroup || !isset($QuestionsWithConditions)
            && ($countQuestionsInThisGroup != $countQuestionsWithRelevanceIntThisGroup || !isset($QuestionsWithRelevance)))
    {
        //One of the questions in this group is NOT conditional, therefore
        //the group MUST be displayed
        return true;
    }
    else
    {
        //All of the questions in this group are conditional. Now we must
        //check every question, to see if the condition for each has been met.
        //If 1 or more have their conditions met, then the group should
        //be displayed.
        foreach ($QuestionsWithConditions as $cc)
        {
            if (checkquestionfordisplay($cc[0], $gid) === true)
            {
                return true;
            }
        }
        if (isset($QuestionsWithRelevance)) {
            foreach ($QuestionsWithRelevance as $relevance)
            {
                if (LimeExpressionManager::ProcessRelevance($relevance))
                {
                    return true;
                }
            }
        }
        //Since we made it this far, there mustn't have been any conditions met.
        //Therefore the group should not be displayed.
        return false;
    }
}

function checkconfield($value)
{
    global $surveyid,$thissurvey,$qattributes;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	//$_SESSION = $CI->session->userdata;

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
    //     the $_SESSION['fieldnamesInfo'] translation table
    if (isset($_SESSION['fieldnamesInfo'][$value]))
    {
        $masterFieldName = $_SESSION['fieldnamesInfo'][$value];
    }
    else
    { // for token refurl, ipaddr...
        $masterFieldName = 'token';
    }
    $value_qid=0;
    $value_type='';
    $value_isconditionnal='N';

    //$value is the fieldname for the field we are checking for conditions
    foreach ($_SESSION['fieldarray'] as $sfa) //Go through each field
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
        if ($value_isconditionnal  == "Y" && isset($_SESSION[$value]) ) //Do this if there is a condition based on this answer
        {

            $scenarioquery = "SELECT DISTINCT scenario FROM ".$CI->db->dbprefix("conditions")
            ." WHERE ".$CI->db->dbprefix("conditions").".qid=$sfa[0] ORDER BY scenario";
            $scenarioresult=db_execute_assoc($scenarioquery);
            $matchfound=0;
            //$scenario=1;
            //while ($scenario > 0)
            $evalNextScenario = true;
            foreach($scenarioresult->result_array() as $scenariorow)
            {
            	if($evalNextScenario !== true)
					break;
                $aAllCondrows=Array();
                $cqval=Array();
                $container=Array();

                $scenario = $scenariorow['scenario'];
                $currentcfield="";
                $query = "SELECT ".$CI->db->dbprefix('conditions').".*, ".$CI->db->dbprefix('questions').".type "
                . "FROM ".$CI->db->dbprefix('conditions').", ".$CI->db->dbprefix('questions')." "
                . "WHERE ".$CI->db->dbprefix('conditions').".cqid=".$CI->db->dbprefix('questions').".qid "
                . "AND ".$CI->db->dbprefix('conditions').".qid=$value_qid "
                . "AND ".$CI->db->dbprefix('conditions').".scenario=$scenario "
                . "AND ".$CI->db->dbprefix('conditions').".cfieldname NOT LIKE '{%' "
                . "ORDER BY ".$CI->db->dbprefix('conditions').".qid,".$CI->db->dbprefix('conditions').".cfieldname";
                $result=db_execute_assoc($query) or safe_die($query."<br />".$connect->ErrorMsg());         //Checked
                $conditionsfound = $result->num_rows();

                $querytoken = "SELECT ".$CI->db->dbprefix('conditions').".*, '' as type "
                . "FROM ".$CI->db->dbprefix('conditions')." "
                . "WHERE "
                . " ".$CI->db->dbprefix('conditions').".qid=$value_qid "
                . "AND ".$CI->db->dbprefix('conditions').".scenario=$scenario "
                . "AND ".$CI->db->dbprefix('conditions').".cfieldname LIKE '{%' "
                . "ORDER BY ".$CI->db->dbprefix('conditions').".qid,".$CI->db->dbprefix('conditions').".cfieldname";
                $resulttoken=db_execute_assoc($querytoken) or safe_die($querytoken."<br />".$connect->ErrorMsg());         //Checked
                $conditionsfoundtoken = $resulttoken->num_rows();
                $conditionsfound = $conditionsfound + $conditionsfoundtoken;

                foreach($resulttoken->result_array() as $Condrow)
                {
                    $aAllCondrows[] = $Condrow;
                }
                foreach($result->result_array() as $Condrow)
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
                                if (isset($_SESSION[$targetconditionfieldname[1]]))
                                {
                                    $cqv["matchvalue"] = $_SESSION[$targetconditionfieldname[1]];
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
                                if (isset($_SESSION['token']) && in_array(strtolower($targetconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                                {
                                    $cqv["matchvalue"] = GetAttributeValue($surveyid,strtolower($targetconditiontokenattr[1]),$_SESSION['token']);
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
                                    if (isset($_SESSION[$cqv['matchfield']]))
                                    {
                                        $comparisonLeftOperand =  $_SESSION[$cqv['matchfield']];
                                    }
                                    else
                                    {
                                        $comparisonLeftOperand = null;
                                    }
                                }
                                elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$cqv['cfieldname'],$sourceconditiontokenattr))
                                {
                                    if ( isset($_SESSION['token']) &&
                                    in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                                    {
                                        $comparisonLeftOperand = GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['token']);
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
                $_SESSION[$value]="";
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

function checkmandatorys($move, $backok=null)
{
    global $thisstep;
	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;

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
                            $_SESSION['step'] = $thisstep;
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
            if (isset($_SESSION[$cm]) && ($_SESSION[$cm] == "0" || $_SESSION[$cm]))
            {
            }
            elseif ((!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtcm]) || $_POST[$dtcm] == "on"))
            {
                //One of the mandatory questions hasn't been asnwered
                    $_SESSION['step'] = $thisstep;
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
                    $_SESSION['step'] = $thisstep;
                }
                if (isset($move) && $move == "movenext")
                {
                    $_SESSION['step'] = $thisstep;
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

function checkconditionalmandatorys($move, $backok=null)
{
    global $thisstep;
	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;

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
                            $_SESSION['step'] = $thisstep;
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
            if (isset($_SESSION[$ccm]) && ($_SESSION[$ccm] == "0" || $_SESSION[$ccm]) && isset($_POST[$dccm]) && $_POST[$dccm] == "on") //There is an answer

            {
                //The question has an answer, and the answer was displaying
            }
            elseif ((isset($_POST[$dccm]) && $_POST[$dccm] == "on") && (!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtccm]) || $_POST[$dtccm] == "on")) // Question and Answers is on, there is no answer, but it's a multiple

            {
                if (isset($move) && $move == "moveprev")
                {
                    $_SESSION['step'] = $thisstep;
                }
                if (isset($move) && $move == "movenext")
                {
                    $_SESSION['step'] = $thisstep;
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
                    $_SESSION['step'] = $thisstep;
                }
                if (isset($move) && $move == "movenext")
                {
                    $_SESSION['step'] = $thisstep;
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

	$CI =& get_instance();
	$_POST = $CI->input->post();
    $clang = $CI->limesurvey_lang;
	//$_SESSION = $CI->session->userdata;

    if (!isset($backok) || $backok != "Y")
    {
        global $dbprefix;
        $fieldmap = createFieldMap($surveyid);

        if (isset($_POST['fieldnames']) && $_POST['fieldnames']!="")
        {
            $fields = explode("|", $_POST['fieldnames']);

            foreach ($fields as $field)
            {
                if ($fieldmap[$field]['type'] == "|" && !strrpos($fieldmap[$field]['fieldname'], "_filecount"))
                {
                    $validation = array();

                    $query = "SELECT * FROM ".$CI->db->dbprefix('question_attributes')." WHERE qid = ".$fieldmap[$field]['qid'];
                    $result = db_execute_assoc($query);
                    foreach($result->row_array() as $row)
                        $validation[$row['attribute']] = $row['value'];

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

                    if (isset($validation['min_num_of_files']) && $filecount < $validation['min_num_of_files'] && checkquestionfordisplay($fieldmap[$field]['qid']))
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
                $_SESSION['step'] = $thisstep;
            if (isset($move) && $move == "movenext")
                $_SESSION['step'] = $thisstep;
            return $filenotvalidated;
        }
    }
    if (!isset($filenotvalidated))
        return false;
    else
        return $filenotvalidated;
}

function aCheckInput($surveyid, $move,$backok=null)
{
    global $connect, $thisstep, $thissurvey;
	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;

    if (!isset($backok) || $backok != "Y")
    {
        global $dbprefix;
        $fieldmap=createFieldMap($surveyid);
        if (isset($_POST['fieldnames']))
        {
            $fields=explode("|", $_POST['fieldnames']);
            foreach ($fields as $field)
            {
                //Get question information
                if (isset($_POST[$field]) && isset($_SESSION['s_lang']) && ($_POST[$field] == "0" || $_POST[$field])) //Only do this if there is an answer

                {
                    $fieldinfo=$fieldmap[$field];
                    $pregquery="SELECT preg\n"
                    ."FROM ".$CI->db->dbprefix('questions')."\n"
                    ."WHERE qid=".$fieldinfo['qid']." "
                    . "AND language='".$_SESSION['s_lang']."'";
                    $pregresult=db_execute_assoc($pregquery) or safe_die("ERROR: $pregquery<br />".$connect->ErrorMsg());      //Checked
                    foreach($pregresult->result_array() as $pregrow)
                    {
                        $preg=trim($pregrow['preg']);
                    } // while
                    if (isset($preg) && $preg)
                    {
                        if (!@preg_match($preg, $_POST[$field]))
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                    }

                    // check for other question attributes
                    $qidattributes=getQuestionAttributeValues($fieldinfo['qid'],$fieldinfo['type']);

                    if ($fieldinfo['type'] == 'N')
                    {
                        $neg = true;
                        if (trim($qidattributes['max_num_value_n'])!='' &&
                            $qidattributes['max_num_value_n'] >= 0)
                        {
                            $neg = false;
                        }

                        if (trim($qidattributes['num_value_int_only'])==1 &&
                        !preg_match("/^" . ($neg? "-?": "") . "[0-9]+$/", $_POST[$field]))
                        {
                            $notvalidated[]=$field;
                            continue;
                        }

                        if (trim($qidattributes['max_num_value_n'])!='' &&
                            $_POST[$field] > $qidattributes['max_num_value_n'])
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                        if (trim($qidattributes['min_num_value_n'])!='' &&
                            $_POST[$field] < $qidattributes['min_num_value_n'])
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                    }
                    elseif ($fieldinfo['type'] == 'D')
                    {
                        // $_SESSION[$fieldinfo['fieldname']] now contains the crappy value parsed by
                        // Date_Time_Converter in save.php. We can leave it there. We just do validation here.
                        $dateformatdetails = aGetDateFormatDataForQid($qidattributes, $thissurvey);
                        $datetimeobj = DateTime::createFromFormat($dateformatdetails['phpdate'], $_POST[$field]);
                        if(!$datetimeobj)
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                    }
                }
            }
        }
        //The following section checks for question attribute validation, looking for values in a particular field
        if (isset($_POST['qattribute_answer']))
        {
            foreach ($_POST['qattribute_answer'] as $maxvalueanswer)
            {
                //$maxvalue_answername="maxvalue_answer".$maxvalueanswer;
                if (!empty($_POST['qattribute_answer'.$maxvalueanswer]) && $_POST['display'.$maxvalueanswer] == "on")
                {
                        $_SESSION['step'] = $thisstep;
                    $notvalidated[]=$maxvalueanswer;
                    return $notvalidated;
                }
            }
        }

        if (isset($notvalidated) && is_array($notvalidated))
        {
            if (isset($move) && $move == "moveprev")
            {
                $_SESSION['step'] = $thisstep;
            }
            if (isset($move) && $move == "movenext")
            {
                $_SESSION['step'] = $thisstep;
            }
            return $notvalidated;
        }
    }
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
    global $thissurvey, $timeadjust, $emailcharset ;
    global $surveyid;
    global $thistpl, $clienttoken;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;
	$sitename = $CI->config->item("sitename");

    // Shift the date due to global timeadjust setting
    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);

    // check how many uses the token has left
    $usesquery = "SELECT usesleft FROM {$dbprefix}tokens_$surveyid WHERE token=".$CI->db->escape($clienttoken);
    $usesresult = db_execute_assoc($usesquery);
    $usesrow = $usesresult->row_array();
    if (isset($usesrow)) { $usesleft = $usesrow['usesleft']; }

    $utquery = "UPDATE {$dbprefix}tokens_$surveyid\n";
    if ($quotaexit==true)
    {
        $utquery .= "SET completed='Q', usesleft=usesleft-1\n";
    }
    elseif (bIsTokenCompletedDatestamped($thissurvey))
    {
        if (isset($usesleft) && $usesleft<=1)
        {
			$utquery .= "SET usesleft=usesleft-1, completed='$today'\n";
    }
    else
    {
			$utquery .= "SET usesleft=usesleft-1\n";
    }
    }
    else
    {
        if (isset($usesleft) && $usesleft<=1)
        {
			$utquery .= "SET usesleft=usesleft-1, completed='Y'\n";
		}
		else
		{
			$utquery .= "SET usesleft=usesleft-1\n";
		}
    }
    $utquery .= "WHERE token=".$CI->db->escape($clienttoken)."";

    $utresult = db_execute_assoc($utquery) or safe_die ("Couldn't update tokens table!<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked

    if ($quotaexit==false)
    {
        // TLR change to put date into sent and completed
        $cnfquery = "SELECT * FROM ".$CI->db->dbprefix("tokens_$surveyid")." WHERE token=".$CI->db->escape($clienttoken)." AND completed!='N' AND completed!=''";

        $cnfresult = db_execute_assoc($cnfquery);       //Checked
        $cnfrow = $cnfresult->row_array();
        if (isset($cnfrow))
        {
            $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
            $to = $cnfrow['email'];
            $subject=$thissurvey['email_confirm_subj'];

            $fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
            $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
            $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
            $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
            $fieldsarray["{FIRSTNAME}"]=$cnfrow['firstname'];
            $fieldsarray["{LASTNAME}"]=$cnfrow['lastname'];
            $fieldsarray["{TOKEN}"]=$clienttoken;
            $attrfieldnames=GetAttributeFieldnames($surveyid);
            foreach ($attrfieldnames as $attr_name)
            {
                $fieldsarray["{".strtoupper($attr_name)."}"]=$cnfrow[$attr_name];
            }

            $dateformatdatat=getDateFormatData($thissurvey['surveyls_dateformat']);
            $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);
            $fieldsarray["{EXPIRY}"]=convertDateTimeFormat($thissurvey["expiry"],'Y-m-d H:i:s',$dateformatdatat['phpdate']);

            $subject=ReplaceFields($subject, $fieldsarray, true);

            if ($thissurvey['anonymized'] == "N")
            {
                // Survey is not anonymous, we can translate insertAns placeholder
                $subject=dTexts::run($subject);
            }

            $subject=html_entity_decode($subject,ENT_QUOTES,$emailcharset);

            if (getEmailFormat($surveyid) == 'html')
            {
                $ishtml=true;
            }
            else
            {
                $ishtml=false;
            }

            if (trim(strip_tags($thissurvey['email_confirm'])) != "")
            {
                $message=$thissurvey['email_confirm'];
                $message=ReplaceFields($message, $fieldsarray, true);

                if ($thissurvey['anonymized'] == "N")
                {
                    // Survey is not anonymous, we can translate insertAns placeholder
                    $message=dTexts::run($message);
                }

                if (!$ishtml)
                {
                    $message=strip_tags(br2nl(html_entity_decode($message,ENT_QUOTES,$emailcharset)));
                }
                else
                {
                    $message=html_entity_decode($message,ENT_QUOTES, $emailcharset );
                }

                //Only send confirmation email if there is a valid email address
                if (validate_email($cnfrow['email']))
                {
                    SendEmailMessage($message, $subject, $to, $from, $sitename,$ishtml);
                }
            }
            else
            {
                //There is nothing in the message, so don't send a confirmation email
                //This section only here as placeholder to indicate new feature :-)
            }
        }
    }
}

/**
* Send a submit notification to the email address specified in the notifications tab in the survey settings
*/
function SendSubmitNotifications()
{
    global $thissurvey, $debug;
    global $emailcharset;
    global $homeurl, $surveyid, $publicurl, $maildebug, $tokensexist;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;
	$sitename = $CI->config->item("sitename");

    $bIsHTML = ($thissurvey['htmlemail'] == 'Y');

    $aReplacementVars=array();

    if ($thissurvey['allowsave'] == "Y" && isset($_SESSION['scid']))
    {
        $aReplacementVars['RELOADURL']="{$publicurl}/index.php?sid={$surveyid}&loadall=reload&scid=".$_SESSION['scid']."&loadname=".urlencode($_SESSION['holdname'])."&loadpass=".urlencode($_SESSION['holdpass']);
        if ($bIsHTML)
        {
            $aReplacementVars['RELOADURL']="<a href='{$aReplacementVars['RELOADURL']}'>{$aReplacementVars['RELOADURL']}</a>";
        }
    }
    else
    {
        $aReplacementVars['RELOADURL']='';
    }

    $aReplacementVars['ADMINNAME'] = $thissurvey['adminname'];
    $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
    $aReplacementVars['VIEWRESPONSEURL']="{$homeurl}/admin.php?action=browse&sid={$surveyid}&subaction=id&id={$_SESSION['srid']}";
    $aReplacementVars['EDITRESPONSEURL']="{$homeurl}/admin.php?action=dataentry&sid={$surveyid}&subaction=edit&surveytable=survey_{$surveyid}&id=".$_SESSION['srid'];
    $aReplacementVars['STATISTICSURL']="{$homeurl}/admin.php?action=statistics&sid={$surveyid}";
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
                $sRecipient=dTexts::run($sRecipient);
                if(validate_email($sRecipient))
                {
                    $aEmailNotificationTo[]=$sRecipient;
                }
            }
        }
    }

    if (!empty($thissurvey['emailresponseto']))
    {
		if (isset($_SESSION['token']) && $_SESSION['token'] != '' && db_tables_exist($dbprefix.'tokens_'.$surveyid))
        {
            //Gather token data for tokenised surveys
            $_SESSION['thistoken']=getTokenData($surveyid, $_SESSION['token']);
        }
        // there was no token used so lets remove the token field from insertarray
        elseif ($_SESSION['insertarray'][0]=='token')
        {
            unset($_SESSION['insertarray'][0]);
        }
        //Make an array of email addresses to send to
        $aRecipient=explode(";", $thissurvey['emailresponseto']);
        {
            foreach($aRecipient as $sRecipient)
            {
                $sRecipient=dTexts::run($sRecipient);
                if(validate_email($sRecipient))
                {
                    $aEmailResponseTo[]=$sRecipient;
                }
            }
        }

        $aFullResponseTable=aGetFullResponseTable($surveyid,$_SESSION['srid'],$_SESSION['s_lang']);
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

$redata = array(
	'answer' => &$answer,
	'assessments' => &$assessments,
	'captchapath' => &$captchapath,
	'clienttoken' => &$clienttoken,
	'completed' => &$completed,
	'errormsg' => &$errormsg,
	'groupdescription' => &$groupdescription,
	'groupname' => &$groupname,
	'help' => &$help,
	'imageurl' => &$imageurl,
	'languagechanger' => &$languagechanger,
	'loadname' => &$loadname,
	'move' => &$move,
	'navigator' => &$navigator,
	'percentcomplete' => &$percentcomplete,
	'privacy' => &$privacy,
	'question' => &$question,
	'register_errormsg' => &$register_errormsg,
	'relativeurl' => &$relativeurl,
	's_lang' => &$s_lang,
	'saved_id' => &$saved_id,
	'showgroupinfo' => &$showgroupinfo,
	'showqnumcode' => &$showqnumcode,
	'showXquestions' => &$showXquestions,
	'sitename' => &$sitename,
	'surveylist' => &$surveylist,
	'templatedir' => &$templatedir,
	'thissurvey' => &$thissurvey,
	'token' => &$token,
	'totalBoilerplatequestions' => &$totalBoilerplatequestions,
	'totalquestions' => &$totalquestions,
);

    if (count($aEmailNotificationTo)>0)
    {
        $sMessage=templatereplace($thissurvey['email_admin_notification'],$aReplacementVars,$redata,($thissurvey['anonymized'] == "Y"));
        $sSubject=templatereplace($thissurvey['email_admin_notification_subj'],$aReplacementVars,$redata,($thissurvey['anonymized'] == "Y"));
        foreach ($aEmailNotificationTo as $sRecipient)
        {
            if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML, getBounceEmail($surveyid)))
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
        $sMessage=templatereplace($thissurvey['email_admin_responses'],$aReplacementVars,$redata,($thissurvey['anonymized'] == "Y"));
        $sSubject=templatereplace($thissurvey['email_admin_responses_subj'],$aReplacementVars,$redata,($thissurvey['anonymized'] == "Y"));
        foreach ($aEmailResponseTo as $sRecipient)
        {
            if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML, getBounceEmail($surveyid)))
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
    global $thistpl, $subquery, $surveyid;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

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
        foreach ($_SESSION['insertarray'] as $value)
        {
            $email .= "$value: {$_SESSION[$value]}\n";
        }
        $email .= "\n".$clang->gT("SQL CODE THAT FAILED","unescaped").":\n"
        . "$subquery\n\n"
        . $clang->gT("ERROR MESSAGE","unescaped").":\n"
        . $errormsg."\n\n";
        SendEmailMessage($email, $clang->gT("Error saving results","unescaped"), $thissurvey['adminemail'], $thissurvey['adminemail'], "LimeSurvey", false, getBounceEmail($surveyid));
        //echo "<!-- EMAIL CONTENTS:\n$email -->\n";
        //An email has been sent, so we can kill off this session.
        session_unset();
        session_destroy();
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
*
* @returns  $totalquestions Total number of questions in the survey
*
*/
function buildsurveysession($surveyid)
{
    global $thissurvey, $secerror, $clienttoken, $databasetype;
    global $tokensexist, $thistpl;
    //global $surveyid;
    global $register_errormsg;
    global $totalBoilerplatequestions, $totalquestions;
    global $templang, $move, $rooturl, $publicurl;


	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

    if (!isset($templang) || $templang=='')
    {
        $templang=$thissurvey['language'];
    }

    $totalBoilerplatequestions = 0;
    $loadsecurity = returnglobal('loadsecurity');

$redata = array(
//	'answer' => &$answer,
//	'assessments' => &$assessments,
//	'captchapath' => &$captchapath,
	'clienttoken' => &$clienttoken,
//	'completed' => &$completed,
//	'errormsg' => &$errormsg,
//	'groupdescription' => &$groupdescription,
//	'groupname' => &$groupname,
//	'help' => &$help,
//	'imageurl' => &$imageurl,
//	'languagechanger' => &$languagechanger,
//	'loadname' => &$loadname,
	'move' => &$move,
//	'navigator' => &$navigator,
//	'percentcomplete' => &$percentcomplete,
//	'privacy' => &$privacy,
//	'question' => &$question,
		'register_errormsg' => &$register_errormsg,
//	'relativeurl' => &$relativeurl,
//	's_lang' => &$s_lang,
//	'saved_id' => &$saved_id,
//	'showgroupinfo' => &$showgroupinfo,
//	'showqnumcode' => &$showqnumcode,
//	'showXquestions' => &$showXquestions,
//	'sitename' => &$sitename,
//	'surveylist' => &$surveylist,
//	'templatedir' => &$templatedir,
	'thissurvey' => &$thissurvey,
//	'token' => &$token,
	'totalBoilerplatequestions' => &$totalBoilerplatequestions,
	'totalquestions' => &$totalquestions,
);


    // NO TOKEN REQUIRED BUT CAPTCHA ENABLED FOR SURVEY ACCESS
    if ($tokensexist == 0 &&
    captcha_enabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
        if (!isset($loadsecurity) ||
        !isset($_SESSION['secanswer']) ||
        $loadsecurity != $_SESSION['secanswer'])
        {
            sendcacheheaders();
            doHeader();
            // No or bad answer to required security question

            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
            //echo makedropdownlist();
            echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata);

            if (isset($loadsecurity))
            { // was a bad answer
                echo "<font color='#FF0000'>".$clang->gT("The answer to the security question is incorrect.")."</font><br />";
            }

            echo "<p class='captcha'>".$clang->gT("Please confirm access to survey by answering the security question below and click continue.")."</p>
			        <form class='captcha' method='post' action='".site_url("$surveyid")."'>
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
						<input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
						<input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
						<input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
            }

            echo "
				        </td>
			        </tr>";
            if (function_exists("ImageCreate") && captcha_enabled('surveyaccessscreen', $thissurvey['usecaptcha']))
            {
                echo "<tr>
				                <td align='center' valign='middle'><label for='captcha'>".$clang->gT("Security question:")."</label></td><td align='left' valign='middle'><table><tr><td valign='middle'><img src='$rooturl/verification.php?sid=$surveyid' alt='captcha' /></td>
                                <td valign='middle'><input id='captcha' type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table>
				                </td>
			                </tr>";
            }
            echo "<tr><td colspan='2' align='center'><input class='submit' type='submit' value='".$clang->gT("Continue")."' /></td></tr>
		        </table>
		        </form>";

            echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
            doFooter();
            exit;
        }
    }

    //BEFORE BUILDING A NEW SESSION FOR THIS SURVEY, LET'S CHECK TO MAKE SURE THE SURVEY SHOULD PROCEED!
    // TOKEN REQUIRED BUT NO TOKEN PROVIDED
    if ($tokensexist == 1 && !$clienttoken)
    {

        if ($thissurvey['nokeyboard']=='Y')
        {
            vIncludeKeypad();
            $kpclass = "text-keypad";
        }
        else
        {
            $kpclass = "";
        }

        // DISPLAY REGISTER-PAGE if needed
        // DISPLAY CAPTCHA if needed
        sendcacheheaders();
        doHeader();

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
        //echo makedropdownlist();
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata);
        if (isset($thissurvey) && $thissurvey['allowregister'] == "Y")
        {
            echo templatereplace(file_get_contents("$thistpl/register.pstpl"),array(),$redata);
        }
        else
        {
            if (isset($secerror)) echo "<span class='error'>".$secerror."</span><br />";
            echo '<div id="wrapper"><p id="tokenmessage">'.$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br />";
            echo $clang->gT("If you have been issued a token, please enter it in the box below and click continue.")."</p>
            <script type='text/javascript'>var focus_element='#token';</script>
	        <form id='tokenform' method='post' action='".site_url("$surveyid")."'>
                <ul>
                <li>
            <label for='token'>".$clang->gT("Token")."</label><input class='text $kpclass' id='token' type='text' name='token' />";

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
					<input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
					<input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
					<input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
            }
            echo "</li>";

            if (function_exists("ImageCreate") && captcha_enabled('surveyaccessscreen', $thissurvey['usecaptcha']))
            {
                echo "<li>
			                <label for='captchaimage'>".$clang->gT("Security Question")."</label><img id='captchaimage' src='$rooturl/verification.php?sid=$surveyid' alt='captcha' /><input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
		                  </li>";
            }
            echo "<li>
                        <input class='submit' type='submit' value='".$clang->gT("Continue")."' />
                      </li>
            </ul>
	        </form></div>";
        }

        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
        doFooter();
        exit;
    }
    // TOKENS REQUIRED, A TOKEN PROVIDED
    // SURVEY WITH NO NEED TO USE CAPTCHA
    elseif ($tokensexist == 1 && $clienttoken &&
    !captcha_enabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        //check if tokens actually haven't been already used
		$areTokensUsed = usedTokens(trim(strip_tags($clienttoken)));
        //check if token actually does exist
	    // check also if it is allowed to change survey after completion
		if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
          $tkquery = "SELECT COUNT(*) FROM ".$CI->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$CI->db->escape(trim(strip_tags($clienttoken)))." ";
		} else {
        	$tkquery = "SELECT COUNT(*) FROM ".$CI->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$CI->db->escape(trim(strip_tags($clienttoken)))." AND (completed = 'N' or completed='')";
		}

        $tkresult = db_execute_assoc($tkquery);    //Checked
        $tkexist = reset($tkresult->row_array());
        if (!$tkexist || $areTokensUsed)
        {
            //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

            killSession();
            sendcacheheaders();
            doHeader();

            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
            echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata);
            echo '<div id="wrapper"><p id="tokenmessage">'.$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
            ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."<br />\n"
            ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
            ." (<a href='mailto:{$thissurvey['adminemail']}'>"
            ."{$thissurvey['adminemail']}</a>)</p></div>\n";

            echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
            doFooter();
            exit;
        }
    }
    // TOKENS REQUIRED, A TOKEN PROVIDED
    // SURVEY CAPTCHA REQUIRED
    elseif ($tokensexist == 1 && $clienttoken && captcha_enabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        // IF CAPTCHA ANSWER IS CORRECT
        if (isset($loadsecurity) &&
        isset($_SESSION['secanswer']) &&
        $loadsecurity == $_SESSION['secanswer'])
        {
            //check if tokens actually haven't been already used
            $areTokensUsed = usedTokens(trim(strip_tags($clienttoken)));
            //check if token actually does exist
            if ($thissurvey['alloweditaftercompletion'] == 'Y' )
            {
                $tkquery = "SELECT COUNT(*) FROM ".$CI->db->dbprefix('tokens_'.$surveyid)." WHERE token='".$this->db->escape(trim(sanitize_xss_string(strip_tags($clienttoken))))."'";
            }
            else
            {
                $tkquery = "SELECT COUNT(*) FROM ".$CI->db->dbprefix('tokens_'.$surveyid)." WHERE token='".$this->db->escape(trim(sanitize_xss_string(strip_tags($clienttoken))))."' AND (completed = 'N' or completed='')";
            }
            $tkresult = db_execute_assoc($tkquery);     //Checked
            list($tkexist) = $tkresult->row_array();
            if (!$tkexist || ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y') )
            {
                sendcacheheaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
                echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata);
                echo "\t<div id='wrapper'>\n"
                ."\t<p id='tokenmessage'>\n"
                ."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
                ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."<br/>\n"
                ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
                ." (<a href='mailto:{$thissurvey['adminemail']}'>"
                ."{$thissurvey['adminemail']}</a>)\n"
                ."\t</p>\n"
                ."\t</div>\n";

                echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
                doFooter();
                exit;
            }
        }
        // IF CAPTCHA ANSWER IS NOT CORRECT
        else if (!isset($move) || is_null($move))
        {
            unset($_SESSION['srid']);
            $gettoken = $clienttoken;
            sendcacheheaders();
            doHeader();
            // No or bad answer to required security question
            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
            echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata);
            // If token wasn't provided and public registration
            // is enabled then show registration form
            if ( !isset($gettoken) && isset($thissurvey) && $thissurvey['allowregister'] == "Y")
            {
                echo templatereplace(file_get_contents("$thistpl/register.pstpl"),array(),$redata);
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
			            <form id='tokenform' method='get' action='{$publicurl}/index.php'>
                        <ul>
                        <li>
					        <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
						    <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                    if (isset($_GET['loadall']) && isset($_GET['scid'])
                    && isset($_GET['loadname']) && isset($_GET['loadpass']))
                    {
                        echo "<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
						        <input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
						        <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
						        <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                    }

                    echo '<label for="token">'.$clang->gT("Token")."</label><input class='text' type='text' id='token' name='token'></li>";
                }
                else
                {
                    echo $clang->gT("Please confirm the token by answering the security question below and click continue.")."</p>
			            <form id='tokenform' method='get' action='{$publicurl}/index.php'>
                        <ul>
			            <li>
					            <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
						        <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                    if (isset($_GET['loadall']) && isset($_GET['scid'])
                    && isset($_GET['loadname']) && isset($_GET['loadpass']))
                    {
                        echo "<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
                              <input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
                              <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
                              <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                    }
                    echo '<label for="token">'.$clang->gT("Token:")."</label><span id='token'>$gettoken</span>"
                    ."<input type='hidden' name='token' value='$gettoken'></li>";
                }


                if (function_exists("ImageCreate") && captcha_enabled('surveyaccessscreen', $thissurvey['usecaptcha']))
                {
                    echo "<li>
                            <label for='captchaimage'>".$clang->gT("Security Question")."</label><img id='captchaimage' src='$rooturl/verification.php?sid=$surveyid' alt='captcha' /><input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
                          </li>";
                }
                echo "<li><input class='submit' type='submit' value='".$clang->gT("Continue")."' /></li>
		                </ul>
		                </form>
		                </id>";
            }

            echo '</div>'.templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
            doFooter();
            exit;
        }
    }

    //RESET ALL THE SESSION VARIABLES AND START AGAIN
    unset($_SESSION['grouplist']);
    unset($_SESSION['fieldarray']);
    unset($_SESSION['insertarray']);
    unset($_SESSION['thistoken']);
    unset($_SESSION['fieldnamesInfo']);
    $_SESSION['fieldnamesInfo'] = Array();


    //RL: multilingual support

	if (isset($_GET['token']) && db_tables_exist($dbprefix.'tokens_'.$surveyid))
    {

        //get language from token (if one exists)
        $tkquery2 = "SELECT * FROM ".$CI->db->dbprefix('tokens_'.$surveyid)." WHERE token='".db_quote($clienttoken)."' AND (completed = 'N' or completed='')";
        //echo $tkquery2;
        $result = db_execute_assoc($tkquery2) or safe_die ("Couldn't get tokens<br />$tkquery<br />".$connect->ErrorMsg());    //Checked
        foreach ($result->result_array() as $rw)
        {
            $tklanguage=$rw['language'];
        }
    }
    if (returnglobal('lang'))
    {
        $language_to_set=returnglobal('lang');
    } elseif (isset($tklanguage))
    {
        $language_to_set=$tklanguage;
    }
    else
    {
        $language_to_set = $thissurvey['language'];
    }

    if (!isset($_SESSION['s_lang']))
    {
        SetSurveyLanguage($surveyid, $language_to_set);
    }


    UpdateSessionGroupList($surveyid, $_SESSION['s_lang']);



    // Optimized Query
    // Change query to use sub-select to see if conditions exist.
    $query = "SELECT ".$CI->db->dbprefix('questions').".*, ".$CI->db->dbprefix('groups').".*,\n"
    ." (SELECT count(1) FROM ".$CI->db->dbprefix('conditions')."\n"
    ." WHERE ".$CI->db->dbprefix('questions').".qid = ".$CI->db->dbprefix('conditions').".qid) AS hasconditions,\n"
    ." (SELECT count(1) FROM ".$CI->db->dbprefix('conditions')."\n"
    ." WHERE ".$CI->db->dbprefix('questions').".qid = ".$CI->db->dbprefix('conditions').".cqid) AS usedinconditions\n"
    ." FROM ".$CI->db->dbprefix('groups')." INNER JOIN ".$CI->db->dbprefix('questions')." ON ".$CI->db->dbprefix('groups').".gid = ".$CI->db->dbprefix('questions').".gid\n"
    ." WHERE ".$CI->db->dbprefix('questions').".sid=".$surveyid."\n"
    ." AND ".$CI->db->dbprefix('groups').".language='".$_SESSION['s_lang']."'\n"
    ." AND ".$CI->db->dbprefix('questions').".language='".$_SESSION['s_lang']."'\n"
    ." AND ".$CI->db->dbprefix('questions').".parent_qid=0\n"
    ." ORDER BY ".$CI->db->dbprefix('groups').".group_order,".$CI->db->dbprefix('questions').".question_order";

    //var_dump($_SESSION);
    $result = db_execute_assoc($query);    //Checked

    $arows = $result->result_array();

    $totalquestions = $result->num_rows();

    $redata = array_merge($redata, array('totalquestions' => &$totalquestions));

    //2. SESSION VARIABLE: totalsteps
    //The number of "pages" that will be presented in this survey
    //The number of pages to be presented will differ depending on the survey format
    switch($thissurvey['format'])
    {
        case "A":
            $_SESSION['totalsteps']=1;
            break;
        case "G":
            if (isset($_SESSION['grouplist']))
            {
                $_SESSION['totalsteps']=count($_SESSION['grouplist']);
            }
            break;
        case "S":
            $_SESSION['totalsteps']=$totalquestions;
    }


    if ($totalquestions == "0")	//break out and crash if there are no questions!
    {
        sendcacheheaders();
        doHeader();

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata);
        echo "\t<div id='wrapper'>\n"
        ."\t<p id='tokenmessage'>\n"
        ."\t".$clang->gT("This survey does not yet have any questions and cannot be tested or completed.")."<br /><br />\n"
        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
        ."{$thissurvey['adminemail']}</a>)<br /><br />\n"
		."\t</p>\n"
        ."\t</div>\n";

        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
        doFooter();
        exit;
    }

    //Perform a case insensitive natural sort on group name then question title of a multidimensional array
    //	usort($arows, 'GroupOrderThenQuestionOrder');

    //3. SESSION VARIABLE - insertarray
    //An array containing information about used to insert the data into the db at the submit stage
    //4. SESSION VARIABLE - fieldarray
    //See rem at end..
    $_SESSION['token'] = $clienttoken;

    if ($thissurvey['anonymized'] == "N")
    {
        $_SESSION['insertarray'][]= "token";
    }

	if ($tokensexist == 1 && $thissurvey['anonymized'] == "N"  && db_tables_exist($dbprefix.'tokens_'.$surveyid))
    {
        //Gather survey data for "non anonymous" surveys, for use in presenting questions
        $_SESSION['thistoken']=getTokenData($surveyid, $clienttoken);
    }
    $qtypes=getqtypelist('','array');
    $fieldmap=createFieldMap($surveyid,'full',false,false,$_SESSION['s_lang']);

    // Randomization Groups

    // Find all defined randomization groups through question attribute values
    $randomGroups=array();
    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $databasetype=='mssqlnative')
    {
        $rgquery = "SELECT attr.qid, CAST(value as varchar(255)) FROM ".$CI->db->dbprefix('question_attributes')." as attr right join ".$CI->db->dbprefix('questions')." as quests on attr.qid=quests.qid WHERE attribute='random_group' and CAST(value as varchar(255)) <> '' and sid=$surveyid GROUP BY attr.qid, CAST(value as varchar(255))";
    }
    else
    {
        $rgquery = "SELECT attr.qid, value FROM ".$CI->db->dbprefix('question_attributes')." as attr right join ".$CI->db->dbprefix('questions')." as quests on attr.qid=quests.qid WHERE attribute='random_group' and value <> '' and sid=$surveyid GROUP BY attr.qid, value";
    }
    $rgresult = db_execute_assoc($rgquery);
    foreach($rgresult->result_array() as $rgrow)
    {
        // Get the question IDs for each randomization group
        $randomGroups[$rgrow['value']][] = $rgrow['qid'];
    }

    // If we have randomization groups set, then lets cycle through each group and
    // replace questions in the group with a randomly chosen one from the same group
    if (count($randomGroups) > 0)
    {
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
        while (list($fieldkey,$fieldval) = each($fieldmap))
        {
            $found = 0;
            foreach ($randomGroups as $gkey=>$gval)
            {
                // We found a qid that is in the randomization group
                if (isset($fieldval['qid']) && in_array($fieldval['qid'],$oldQuestOrder[$gkey]))
                {
                    // Get the swapped question
                    $oldQuestFlip = array_flip($oldQuestOrder[$gkey]);
                    $qfieldmap = createFieldMap($surveyid,'full',true,$newQuestOrder[$gkey][$oldQuestFlip[$fieldval['qid']]],$_SESSION['s_lang']);
                    unset($qfieldmap['id']);
                    unset($qfieldmap['submitdate']);
                    unset($qfieldmap['lastpage']);
                    unset($qfieldmap['lastpage']);
                    unset($qfieldmap['token']);
                    foreach ($qfieldmap as $tkey=>$tval)
                    {
                        // Assign the swapped question (Might be more than one field)
                        $tval['random_gid'] = $fieldval['gid'];
                        //$tval['gid'] = $fieldval['gid'];
                        $copyFieldMap[$tkey]=$tval;
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
        $fieldmap=$copyFieldMap;

    }
//die(print_r($fieldmap));

    $_SESSION['fieldmap']=$fieldmap;
    foreach ($fieldmap as $field)
    {
        if (isset($field['qid']) && $field['qid']!='')
        {
            $_SESSION['fieldnamesInfo'][$field['fieldname']]=$field['sid'].'X'.$field['gid'].'X'.$field['qid'];
            $_SESSION['insertarray'][]=$field['fieldname'];
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
            if (!isset($_SESSION['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]))
            {
                $_SESSION['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]=array($field['qid'],
                $field['sid'].'X'.$field['gid'].'X'.$field['qid'],
                $field['title'],
                $field['question'],
                $field['type'],
                $field['gid'],
                $field['mandatory'],
                $field['hasconditions'],
                $field['usedinconditions']);
            }
            if (isset($field['random_gid']))
            {
                $_SESSION['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']][10] = $field['random_gid'];
            }
        }

    }

    // Prefill question/answer from defaultvalues
    foreach ($fieldmap as $field)
    {
        if (isset($field['defaultvalue']))
        {
            $_SESSION[$field['fieldname']]=$field['defaultvalue'];
        }
    }
    // Prefill questions/answers from command line params
    if (isset($_SESSION['insertarray']))
    {
        foreach($_SESSION['insertarray'] as $field)
        {
            if (isset($_GET[$field]) && $field!='token')
            {
                $_SESSION[$field]=$_GET[$field];
            }
        }
    }

    $_SESSION['fieldarray']=array_values($_SESSION['fieldarray']);

    //Check if a passthru label and value have been included in the query url
    $CI->load->model('survey_url_parameters_model');
    $oResult=$CI->survey_url_parameters_model->getParametersForSurvey($surveyid);
    foreach($oResult->result_array() as $aRow)
    {
        DebugBreak();
        if(isset($_GET[$aRow['parameter']]))
        {
            $_SESSION['urlparams'][$aRow['parameter']]=$_GET[$aRow['parameter']];
            if ($aRow['targetqid']!='')
            {
                foreach ($fieldmap as $sFieldname=>$aField)
                {
                   if ($aRow['targetsqid']!='')
                   {
                       if ($aField['qid']==$aRow['targetqid'] && $aField['sqid']==$aRow['targetsqid'])
                       {
                           $_SESSION[$sFieldname]=$_GET[$aRow['parameter']];
                       }
                   }
                   else
                   {
                       if ($aField['qid']==$aRow['targetqid'])
                       {
                           $_SESSION[$sFieldname]=$_GET[$aRow['parameter']];
                       }
                   }
                }

            }
        }
    }

    // Fix totalquestions by substracting Test Display questions
    $sNoOfTextDisplayQuestions=(int) reset(db_execute_assoc("SELECT count(*)\n"
        ." FROM ".$CI->db->dbprefix('questions')
        ." WHERE type in ('X','*')\n"
        ." AND sid={$surveyid}"
        ." AND language='".$_SESSION['s_lang']."'"
        ." AND parent_qid=0")->row_array());

    $_SESSION['therearexquestions'] = $totalquestions - $sNoOfTextDisplayQuestions; // must be global for THEREAREXQUESTIONS replacement field to work

    return $totalquestions-$sNoOfTextDisplayQuestions;

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

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

    $surveymover = "";

    if ($thissurvey['navigationdelay'] > 0 && (
        isset($_SESSION['maxstep']) && $_SESSION['maxstep'] > 0 && $_SESSION['maxstep'] == $_SESSION['step']))
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

    if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
    {
        $surveymover .= "<input type=\"hidden\" name=\"move\" value=\"movesubmit\" id=\"movesubmit\" />";
    }
    else
    {
        $surveymover .= "<input type=\"hidden\" name=\"move\" value=\"movenext\" id=\"movenext\" />";
    }

    if (isset($_SESSION['step']) && $thissurvey['format'] != "A" && ($thissurvey['allowprev'] != "N" || $thissurvey['allowjumps'] == "Y") &&
	($_SESSION['step'] > 0 || (!$_SESSION['step'] && $presentinggroupdescription && $thissurvey['showwelcome'] == 'Y')))
    {
        //To prevent too much complication in the if statement above I put it here...
        if ($thissurvey['showwelcome'] == 'N' && $_SESSION['step'] == 1) {
           //first step and we do not want to go back to the welcome screen since we don't show that...
           //so skip the prev button
        } else {
            $surveymover .= "<input class='submit' accesskey='p' type='button' onclick=\"javascript:document.limesurvey.move.value = 'moveprev'; $('#limesurvey').submit();\" value=' &lt;&lt; "
            . $clang->gT("Previous")." ' name='move2' id='moveprevbtn' $disabled />\n";
        }
    }

    if (isset($_SESSION['step']) && $_SESSION['step'] && (!$_SESSION['totalsteps'] || ($_SESSION['step'] < $_SESSION['totalsteps'])))
    {
        $surveymover .=  "\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\" value=' "
        . $clang->gT("Next")." &gt;&gt; ' name='move2' id='movenextbtn' $disabled />\n";
    }
    // here, in some lace, is where I must modify to turn the next button conditionable
    if (!isset($_SESSION['step']) || !$_SESSION['step'])
    {
        $surveymover .=  "\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\" value=' "
        . $clang->gT("Next")." &gt;&gt; ' name='move2' id='movenextbtn' $disabled />\n";
    }
    if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && $presentinggroupdescription == "yes")
    {
        $surveymover .=  "\t<input class='submit' type='submit' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\" value=' "
        . $clang->gT("Next")." &gt;&gt; ' name='move2' id=\"movenextbtn\" $disabled />\n";
    }
    if (($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription) || $thissurvey['format'] == 'A')
    {
        $surveymover .= "\t<input class=\"submit\" type=\"submit\" accesskey=\"l\" onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" value=\""
        . $clang->gT("Submit")."\" name=\"move2\" id=\"movesubmitbtn\" $disabled />\n";
    }

    //	$surveymover .= "<input type='hidden' name='PHPSESSID' value='".session_id()."' id='PHPSESSID' />\n";
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
    global $thistpl;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

    $baselang=GetBaseLanguageFromSurveyID($surveyid);
    $total=0;
    if (!isset($_SESSION['s_lang']))
    {
        $_SESSION['s_lang']=$baselang;
    }
    $query = "SELECT * FROM ".$CI->db->dbprefix('assessments')."
			  WHERE sid=$surveyid and language='{$_SESSION['s_lang']}'
			  ORDER BY scope, id";
    if ($result = db_execute_assoc($query))   //Checked

    {
        if ($result->num_rows() > 0)
        {
            foreach($result->result_array() as $row)
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
            $fieldmap=createFieldMap($surveyid, "full");
            $i=0;
            $total=0;
            $groups=array();
            foreach($fieldmap as $field)
            {
                if (in_array($field['type'],array('1','F','H','W','Z','L','!','M','O','P',":")))
                {
                    $fieldmap[$field['fieldname']]['assessment_value']=0;
                    if (isset($_SESSION[$field['fieldname']]))
                    {
                        if ($field['type']==':') //Multiflexi numbers  - result is the assessment value

                        {
                            $fieldmap[$field['fieldname']]['assessment_value']=$_SESSION[$field['fieldname']];
                            $total=$total+$_SESSION[$field['fieldname']];
                        }
                        else
                        {

                                    $usquery = "SELECT assessment_value FROM ".$CI->db->dbprefix("answers")." where qid=".$field['qid']." and language='$baselang' and code=".db_quoteall($_SESSION[$field['fieldname']]);
                            $usresult = db_execute_assoc($usquery);          //Checked
                            if ($usresult)
                            {
                                $usrow = $usresult->row_array();

                                if (($field['type'] == "M") || ($field['type'] == "P"))
                                {
                                    if ($_SESSION[$field['fieldname']] == "Y")     // for Multiple choice type questions
                                    {
                                        $aAttributes=getQuestionAttributeValues($field['qid'],$field['type']);
                                        $fieldmap[$field['fieldname']]['assessment_value']=(int)$aAttributes['assessment_value'];
                                        $total=$total+$usrow['assessment_value'];
                                    }
                                }
                                else     // any other type of question

                                {
                                    $fieldmap[$field['fieldname']]['assessment_value']=$usrow['assessment_value'];
                                    $total=$total+$usrow['assessment_value'];
                                }
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
                        if (isset ($_SESSION[$field['fieldname']]))
                        {
                            if (($field['type'] == "M") and ($_SESSION[$field['fieldname']] == "Y")) 	// for Multiple choice type questions
                            $grouptotal=$grouptotal+$field['assessment_value'];
                            else																		// any other type of question
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

function UpdateSessionGroupList($surveyid, $language)
//1. SESSION VARIABLE: grouplist
//A list of groups in this survey, ordered by group name.

{
	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;
    unset ($_SESSION['grouplist']);
    $query = "SELECT * FROM ".$CI->db->dbprefix('groups')." WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
    $result = db_execute_assoc($query) or safe_die ("Couldn't get group list<br />$query<br />".$connect->ErrorMsg());  //Checked
    foreach ($result->result_array() as $row)
    {
        $_SESSION['grouplist'][]=array($row['gid'], $row['group_name'], $row['description']);
    }
	//$CI->session->userdata = $_SESSION;
}

function UpdateFieldArray()
//The FieldArray contains all necessary information regarding the questions
//This function is needed to update it in case the survey is switched to another language

{
    global $surveyid;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

    if (isset($_SESSION['fieldarray']))
    {
        reset($_SESSION['fieldarray']);
        while ( list($key) = each($_SESSION['fieldarray']) )
        {
            $questionarray =& $_SESSION['fieldarray'][$key];

            $query = "SELECT * FROM ".$CI->db->dbprefix('questions')." WHERE qid=".$questionarray[0]." AND language='".$_SESSION['s_lang']."'";
            $result = db_execute_assoc($query) or safe_die ("Couldn't get question <br />$query<br />".$connect->ErrorMsg());      //Checked
            $row = $result->row_array();
            $questionarray[2]=$row['title'];
            $questionarray[3]=$row['question'];
            unset($questionarray);
        }
    }

}

/**
 * check_quota() returns quota information for the current survey
 * @param string $checkaction - action the function must take after completing:
 * 								enforce: Enforce the Quota action
 * 								return: Return the updated quota array from getQuotaAnswers()
 * @param string $surveyid - Survey identification number
 * @return array - nested array, Quotas->Members->Fields, includes quota status and which members matched in session.
 */
function check_quota($checkaction,$surveyid)
{
    if (!isset($_SESSION['s_lang'])){
        return;
    }
    global $thistpl, $clang, $clienttoken, $publicurl;
    $global_matched = false;
    $quota_info = getQuotaInformation($surveyid, $_SESSION['s_lang']);
    $x=0;

    $CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

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
                        $fields_query_array[$fieldname][]= db_quote_id($fieldname)." = '{$member['value']}'";
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
                	if (isset($_SESSION[$fieldname]))
                        {
                        if (in_array($_SESSION[$fieldname],$fields_value_array[$fieldname])){
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
                    $querysel = "SELECT id FROM ".$CI->db->dbprefix('survey_'.$surveyid)."
					             WHERE ".implode(' AND ',$querycond)." "."
								 AND submitdate IS NOT NULL";

                    $result = db_execute_assoc($querysel) or safe_die($connect->ErrorMsg());    //Checked
                    $quota_check = $result->row_array();

                    if ($result->num_rows() >= $quota['Limit']) // Quota is full!!

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

                        } else
                        {
                            $quota_info[$x]['status']="notmatched";
                        }

                    } else
                    {
                        // Quota is no in danger of being exceeded.
                        $quota_info[$x]['status']="notmatched";
                    }
                }

            }
            $x++;
        }

    } else
    {
        return false;
    }

    // Now we have all the information we need about the quotas and their status.
    // Lets see what we should do now
    if ($checkaction == 'return')
    {
        return $quota_info;
    } else if ($global_matched == true && $checkaction == 'enforce')
    {
        // Need to add Quota action enforcement here.
        reset($quota_info);

$redata = array(
	'answer' => &$answer,
	'assessments' => &$assessments,
	'captchapath' => &$captchapath,
	'clienttoken' => &$clienttoken,
	'completed' => &$completed,
	'errormsg' => &$errormsg,
	'groupdescription' => &$groupdescription,
	'groupname' => &$groupname,
	'help' => &$help,
	'imageurl' => &$imageurl,
	'languagechanger' => &$languagechanger,
	'loadname' => &$loadname,
	'move' => &$move,
	'navigator' => &$navigator,
	'percentcomplete' => &$percentcomplete,
	'privacy' => &$privacy,
	'question' => &$question,
	'register_errormsg' => &$register_errormsg,
	'relativeurl' => &$relativeurl,
	's_lang' => &$s_lang,
	'saved_id' => &$saved_id,
	'showgroupinfo' => &$showgroupinfo,
	'showqnumcode' => &$showqnumcode,
	'showXquestions' => &$showXquestions,
	'sitename' => &$sitename,
	'surveylist' => &$surveylist,
	'templatedir' => &$templatedir,
	'thissurvey' => &$thissurvey,
	'token' => &$token,
	'totalBoilerplatequestions' => &$totalBoilerplatequestions,
	'totalquestions' => &$totalquestions,
);



        $tempmsg ="";
        $found = false;
        foreach($quota_info as $quota)
        {
            if ((isset($quota['status']) && $quota['status'] == "matched") && (isset($quota['Action']) && $quota['Action'] == "1"))
            {
                // If a token is used then mark the token as completed
                if (isset($clienttoken) && $clienttoken)
                {
                    submittokens(true);
                }
                session_destroy();
                sendcacheheaders();
                if($quota['AutoloadUrl'] == 1 && $quota['Url'] != "")
                {
                    header("Location: ".$quota['Url']);
                }
                doHeader();
                echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
                echo "\t<div class='quotamessage'>\n";
                echo "\t".$quota['Message']."<br /><br />\n";
                echo "\t<a href='".$quota['Url']."'>".$quota['UrlDescrip']."</a><br />\n";
                echo "\t</div>\n";
                echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
                doFooter();
                exit;
            }

            if ((isset($quota['status']) && $quota['status'] == "matched") && (isset($quota['Action']) && $quota['Action'] == "2"))
            {

                sendcacheheaders();
                doHeader();
                echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
                echo "\t<div class='quotamessage'>\n";
                echo "\t".$quota['Message']."<br /><br />\n";
                echo "\t<a href='".$quota['Url']."'>".$quota['UrlDescrip']."</a><br />\n";
                echo "<form method='post' action='{$publicurl}/index.php' id='limesurvey' name='limesurvey'><input type=\"hidden\" name=\"move\" value=\"movenext\" id=\"movenext\" /><button class='nav-button nav-button-icon-left ui-corner-all' class='submit' accesskey='p' onclick=\"javascript:document.limesurvey.move.value = 'moveprev'; document.limesurvey.submit();\" name='move2'><span class='ui-icon ui-icon-seek-prev'></span>".$clang->gT("Previous")."</button>
					<input type='hidden' name='thisstep' value='".($_SESSION['step'])."' id='thisstep' />
					<input type='hidden' name='sid' value='".returnglobal('sid')."' id='sid' />
					<input type='hidden' name='token' value='".$clienttoken."' id='token' />
					</form>\n";
                echo "\t</div>\n";
                echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
                doFooter();
                exit;
            }
        }


    } else
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
 * GetReferringUrl() returns the reffering URL
 */
function GetReferringUrl()
{
    global $clang,$stripQueryFromRefurl;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

    if (isset($_SESSION['refurl']))
    {
        return; // do not overwrite refurl
    }

    // refurl is not set in session, read it from server variable
    if(isset($_SERVER["HTTP_REFERER"]))
    {
        if(!preg_match('/'.$_SERVER["SERVER_NAME"].'/', $_SERVER["HTTP_REFERER"]))
        {
            if (!isset($stripQueryFromRefurl) || !$stripQueryFromRefurl)
            {
                $_SESSION['refurl'] = $_SERVER["HTTP_REFERER"];
            }
            else
            {
                $aRefurl = explode("?",$_SERVER["HTTP_REFERER"]);
                $_SESSION['refurl'] = $aRefurl[0];
            }
        }
        else
        {
            $_SESSION['refurl'] = '-';
        }
    }
    else
    {
        $_SESSION['refurl'] = null;
    }
	//$CI->session->userdata = $_SESSION;
}

/**
 * Shows the welcome page, used in group by group and question by question mode
 */
 function display_first_page() {
    global $thistpl, $token, $surveyid, $thissurvey, $navigator,$publicurl, $totalquestions;

	$CI =& get_instance();
	$_POST = $CI->input->post();
	//$_SESSION = $CI->session->userdata;
	$dbprefix = $CI->db->dbprefix;
	$clang = $CI->limesurvey_lang;

    $navigator = surveymover();

$redata = array(
//	'answer' => &$answer,
//	'assessments' => &$assessments,
//	'captchapath' => &$captchapath,
//	'clienttoken' => &$clienttoken,
//	'completed' => &$completed,
//	'errormsg' => &$errormsg,
//	'groupdescription' => &$groupdescription,
//	'groupname' => &$groupname,
//	'help' => &$help,
//	'imageurl' => &$imageurl,
//	'languagechanger' => &$languagechanger,
//	'loadname' => &$loadname,
//	'move' => &$move,
	'navigator' => &$navigator,
//	'percentcomplete' => &$percentcomplete,
//	'privacy' => &$privacy,
//	'question' => &$question,
//	'register_errormsg' => &$register_errormsg,
//	'relativeurl' => &$relativeurl,
//	's_lang' => &$s_lang,
//	'saved_id' => &$saved_id,
//	'showgroupinfo' => &$showgroupinfo,
//	'showqnumcode' => &$showqnumcode,
//	'showXquestions' => &$showXquestions,
//	'sitename' => &$sitename,
//	'surveylist' => &$surveylist,
//	'templatedir' => &$templatedir,
	'thissurvey' => &$thissurvey,
	'token' => &$token,
//	'totalBoilerplatequestions' => &$totalBoilerplatequestions,
	'totalquestions' => &$totalquestions,
);



    sendcacheheaders();
    doHeader();

    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata);
    echo "\n<form method='post' action='".site_url("survey")."' id='limesurvey' name='limesurvey' autocomplete='off'>\n";

    echo "\n\n<!-- START THE SURVEY -->\n";

    echo templatereplace(file_get_contents("$thistpl/welcome.pstpl"),array(),$redata)."\n";
    if ($thissurvey['anonymized'] == "Y")
    {
        echo templatereplace(file_get_contents("$thistpl/privacy.pstpl"),array(),$redata)."\n";
    }
    echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"),array(),$redata);
    if ($thissurvey['active'] != "Y")
    {
        echo "<p style='text-align:center' class='error'>".$clang->gT("This survey is currently not active. You will not be able to save your responses.")."</p>\n";
    }
    echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
    if (isset($token) && !empty($token)) {
        echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
    }
    echo "\n<input type='hidden' name='lastgroupname' value='_WELCOME_SCREEN_' id='lastgroupname' />\n"; //This is to ensure consistency with mandatory checks, and new group test
    $loadsecurity = returnglobal('loadsecurity');
    if (isset($loadsecurity)) {
        echo "\n<input type='hidden' name='loadsecurity' value='$loadsecurity' id='loadsecurity' />\n";
    }
    echo "\n</form>\n";
    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata);
    doFooter();
}

function get_current_ip_address()
{
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
           $sIPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif(!empty($_SERVER['REMOTE_ADDR']))
        {
           $sIPAddress = $_SERVER['REMOTE_ADDR'];
        }
        else{
            $sIPAddress='';
        }

        return  $sIPAddress;
}

// Closing PHP tag intentionally left out - yes, it is okay
