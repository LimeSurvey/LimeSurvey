<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* This function replaces keywords in a text and is mainly intended for templates
* If you use this functions put your replacement strings into the $replacements variable
* instead of using global variables
* NOTE - Don't do any embedded replacements in this function.  Create the array of replacement values and
* they will be done in batch at the end
*
* @param mixed $line Text to search in
* @param mixed $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>
* @param boolean $anonymized Determines if token data is being used or just replaced with blanks
* @param questionNum - needed to support dynamic JavaScript-based tailoring within questions
* @return string  Text with replaced strings
*/
function templatereplace($line, $replacements=array(),&$redata=array(), $debugSrc='Unspecified', $anonymized=false, $questionNum=NULL)
{
    $CI =& get_instance();

    /*
    global $clienttoken,$token,$sitename,$move,$showXquestions,$showqnumcode,$questioncode,$register_errormsg;
    global $s_lang,$errormsg,$saved_id, $totalBoilerplatequestions, $relativeurl, $languagechanger,$captchapath,$loadname;
    */
    /*
    $allowedvars = array('surveylist', 'sitename', 'clienttoken', 'rooturl', 'thissurvey', 'imageurl', 'defaulttemplate',
    'percentcomplete', 'move', 'groupname', 'groupdescription', 'question', 'showXquestions',
    'showgroupinfo', 'showqnumcode', 'questioncode', 'answer', 'navigator', 'help', 'totalquestions',
    'surveyformat', 'completed', 'register_errormsg', 'notanswered', 'privacy', 'surveyid', 'publicurl',
    'templatedir', 'token', 'assessments', 's_lang', 'errormsg', 'clang', 'saved_id', 'usertemplaterootdir',
    'totalBoilerplatequestions', 'relativeurl', 'languagechanger', 'printoutput', 'captchapath', 'loadname');
    */
    $allowedvars = array(
    'answer',
    'assessments',
    'captchapath',
    'clienttoken',
    'completed',
    'errormsg',
    'groupdescription',
    'groupname',
    'help',
    'imageurl',
    'languagechanger',
    'loadname',
    'move',
    'navigator',
    'percentcomplete',
    'privacy',
    'question',
    'register_errormsg',
    'relativeurl',
    's_lang',
    'saved_id',
    'showgroupinfo',
    'showqnumcode',
    'showXquestions',
    'sitename',
    'surveylist',
    'templatedir',
    'thissurvey',
    'token',
    'totalBoilerplatequestions',
    'totalquestions',
    );

    $varsPassed = array();
    foreach($allowedvars as $var)
    {
        if(isset($redata[$var])) {
            $$var = $redata[$var];
            $varsPassed[] = $var;
        }
    }
    if (count($varsPassed) > 0) {
        log_message('debug', 'templatereplace() called from ' . $debugSrc . ' contains: ' . implode(', ', $varsPassed));
    }
    //    extract($redata);   // creates variables for each of the keys in the array

    // Local over-rides in case not set above
    if (!isset($showgroupinfo)) { $showgroupinfo = 'Y'; }
    if (!isset($showqnumcode)) { $showqnumcode = ''; }
    $_surveyid = (isset($surveyid) ? $surveyid : 0);
    if (!isset($totalBoilerplatequestions)) { $totalBoilerplatequestions = 0; }
    if (!isset($showXquestions)) { $showXquestions = 'choose'; }
    if (!isset($relativeurl)) { $relativeurl = $CI->config->item("relativeurl"); }
    if (!isset($s_lang)) { $s_lang = (isset($_SESSION['s_lang']) ? $_SESSION['s_lang'] : 'en'); }
    if (!isset($captchapath)) { $captchapath = ''; }

    if (file_exists($line))
    {
        $line = file_get_contents($line);
    }


    $clang = $CI->limesurvey_lang;
    $CI->load->helper('surveytranslator');
    $questiondetails = array('sid' => 0, 'gid' => 0, 'qid' => 0, 'aid' =>0);
    if(isset($question) && isset($question['sgq'])) $questiondetails=getsidgidqidaidtype($question['sgq']); //Gets an array containing SID, GID, QID, AID and Question Type)

    if (isset($thissurvey['sid'])) {
        $surveyid = $thissurvey['sid'];
    }

    // lets sanitize the survey template
    if(isset($thissurvey['templatedir']))
    {
        $templatename=$thissurvey['templatedir'];
    }
    else
    {
        $templatename=$CI->config->item('defaulttemplate');
    }
    $templatename=validate_templatedir($templatename);
    if(!isset($templatedir)) $templatedir = sGetTemplatePath($templatename);
    if(!isset($templateurl)) $templateurl = sGetTemplateURL($templatename)."/";

    if (stripos ($line,"</head>"))
    {
        $line=str_ireplace("</head>",
        "<script type=\"text/javascript\" src=\"".$CI->config->item('generalscripts')."survey_runtime.js\"></script>\n"
        .use_firebug()
        ."\t</head>", $line);
    }

    // surveyformat
    if (isset($thissurvey['format']))
    {
        $surveyformat = str_replace(array("A", "S", "G"), array("allinone", "questionbyquestion", "groupbygroup"), $thissurvey['format']);
    }
    else
    {
        $surveyformat = "";
    }
    if (isset($thissurvey['showprogress']) && $thissurvey['showprogress']=="Y" && $surveyformat!="allinone" && (isset($_SESSION['step']) && $_SESSION['step']>0)){
        $surveyformat .= " withindex";
    }
    if (isset($thissurvey['showprogress']) && $thissurvey['showprogress']=="Y"){
        $surveyformat .= " showprogress";
    }
    if (isset($thissurvey['showqnumcode'])){
        $surveyformat .= " showqnumcode-".$thissurvey['showqnumcode'];
    }
    // real survey contact
    if (isset($surveylist) && isset($surveylist['contact']))
    {
        $surveycontact = $surveylist['contact'];
    }
    elseif (isset($surveylist) && isset($thissurvey['admin']) && $thissurvey['admin']!="")
    {
        $surveycontact=sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['admin'],$thissurvey['adminemail']);
    }
    else
    {
        $surveycontact="";
    }

    // If there are non-bracketed replacements to be made do so above this line.
    // Only continue in this routine if there are bracketed items to replace {}
    if (strpos($line, "{") === false) {
        // process string anyway so that it can be pretty-printed
        return LimeExpressionManager::ProcessString($line, $questionNum, NULL, false, 1, 1, true);
    }

    if (
    $showgroupinfo == 'both' ||
    $showgroupinfo == 'name' ||
    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'N')
    )
    {
        $_groupname = isset($groupname) ? $groupname : '';
    }
    else
    {
        $_groupname = '';
    };
    if (
    $showgroupinfo == 'both' ||
    $showgroupinfo == 'description' ||
    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'D')
    )
    {
        $_groupdescription = isset($groupdescription) ? $groupdescription : '';
    }
    else
    {
        $_groupdescription = '';
    };

    if (isset($question) && is_array($question))
    {
        $_question = $question['all'];
        $_question_text = $question['text'];
        $_question_help = $question['help'];
        $_question_mandatory = $question['mandatory'];
        $_question_man_message = $question['man_message'];
        $_question_valid_message = $question['valid_message'];
        $_question_file_valid_message = $question['file_valid_message'];
        $_question_sgq = (isset($question['sgq']) ? $question['sgq'] : '');
        $_question_essentials = $question['essentials'];
        $_question_class = $question['class'];
        $_question_man_class = $question['man_class'];
        $_question_input_error_class = $question['input_error_class'];
    }
    else
    {
        $_question = isset($question) ? $question : '';
        $_question_text = '';
        $_question_help = '';
        $_question_mandatory = '';
        $_question_man_message = '';
        $_question_valid_message = '';
        $_question_file_valid_message = '';
        $_question_sgq = '';
        $_question_essentials = '';
        $_question_class = '';
        $_question_man_class = '';
        $_question_input_error_class = '';
    };

    if (
    $showqnumcode == 'both' ||
    $showqnumcode == 'number' ||
    ($showqnumcode == 'choose' && !isset($thissurvey['showqnumcode'])) ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'B') ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'N')
    )
    {
        $_question_number = $question['number'];
    }
    else
    {
        $_question_number = '';
    };
    if (
    $showqnumcode == 'both' ||
    $showqnumcode == 'code' ||
    ($showqnumcode == 'choose' && !isset($thissurvey['showqnumcode'])) ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'B') ||
    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'C')
    )
    {
        $_question_code = $question['code'];
    }
    else
    {
        $_question_code = '';
    }

    if(!isset($totalquestions)) $totalquestions = 0;
    $_totalquestionsAsked = $totalquestions - $totalBoilerplatequestions;
    if (
    $showXquestions == 'show' ||
    ($showXquestions == 'choose' && !isset($thissurvey['showXquestions'])) ||
    ($showXquestions == 'choose' && $thissurvey['showXquestions'] == 'Y')
    )
    {
        if ($_totalquestionsAsked < 1)
        {
            $_therearexquestions = $clang->gT("There are no questions in this survey"); // Singular
        }
        elseif ($_totalquestionsAsked == 1)
        {
            $_therearexquestions = $clang->gT("There is 1 question in this survey"); //Singular
        }
        else
        {
            $_therearexquestions = $clang->gT("There are {NUMBEROFQUESTIONS} questions in this survey.");    //Note this line MUST be before {NUMBEROFQUESTIONS}
        };
    }
    else
    {
        $_therearexquestions = '';
    };


    if (isset($token))
    {
        $_token = $token;
    }
    elseif (isset($clienttoken))
    {
        $_token = htmlentities($clienttoken, ENT_QUOTES, 'UTF-8');  // or should it be URL-encoded?
    }
    else
    {
        $_token = '';
    }

    // Expiry
    if (isset($thissurvey['expiry']))
    {
        $dateformatdetails=getDateFormatData($thissurvey['surveyls_dateformat']);
        $items = array($thissurvey['expiry'],"Y-m-d");
        $CI->load->library('Date_Time_Converter',$items);
        $datetimeobj = $CI->date_time_converter ;
        $_dateoutput=$datetimeobj->convert($dateformatdetails['phpdate']);
    }
    else
    {
        $_dateoutput = '-';
    }

    $_submitbutton = "<input class='submit' type='submit' value=' " . $clang->gT("Submit") . " ' name='move2' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" />";

    if (isset($thissurvey['surveyls_url']) and $thissurvey['surveyls_url'] != "")
    {
        if (trim($thissurvey['surveyls_urldescription']) != '')
        {
            $_linkreplace = "<a href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_urldescription']}</a>";
        }
        else
        {
            $_linkreplace = "<a href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_url']}</a>";
        }
    }
    else
    {
        $_linkreplace='';
    }

    if (isset($surveyid)) {
        $_clearall = "<input type='button' name='clearallbtn' value='" . $clang->gT("Exit and Clear Survey") . "' class='clearall' "
        . "onclick=\"if (confirm('" . $clang->gT("Are you sure you want to clear all your responses?", 'js') . "')) {\nwindow.open('".$CI->config->item('publicurl')."/index.php?sid=$surveyid&amp;move=clearall&amp;lang=" . $s_lang;
        if (returnglobal('token'))
        {
            $_clearall .= "&amp;token=" . urlencode(trim(sanitize_token(strip_tags(returnglobal('token')))));
        }
        $_clearall .= "', '_self')}\" />";
    }
    else
    {
        $_clearall = "";
    }

    if (isset($_SESSION['datestamp']))
    {
        $_datestamp = $_SESSION['datestamp'];
    }
    else
    {
        $_datestamp = '-';
    }

    if (isset($thissurvey['allowsave']) and $thissurvey['allowsave'] == "Y")
    {
        // Find out if the user has any saved data
        if ($thissurvey['format'] == 'A')
        {
            if ($thissurvey['tokenanswerspersistence'] != 'Y')
            {
                $_saveall = "\t\t\t<input type='submit' name='loadall' value='" . $clang->gT("Load Unfinished Survey") . "' class='saveall' " . (($thissurvey['active'] != "Y") ? "disabled='disabled'" : "") . "/>"
                . "\n\t\t\t<input type='button' name='saveallbtn' value='" . $clang->gT("Resume Later") . "' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" " . (($thissurvey['active'] != "Y") ? "disabled='disabled'" : "") . "/>";  // Show Save So Far button
            }
            else
            {
                $_saveall = "\t\t\t<input type='button' name='saveallbtn' value='" . $clang->gT("Resume Later") . "' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" " . (($thissurvey['active'] != "Y") ? "disabled='disabled'" : "") . "/>";  // Show Save So Far button
            };
        }
        elseif (!isset($_SESSION['step']) || !$_SESSION['step'])
        {  //First page, show LOAD
            if ($thissurvey['tokenanswerspersistence'] != 'Y')
            {
                $_saveall = "\t\t\t<input type='submit' name='loadall' value='" . $clang->gT("Load Unfinished Survey") . "' class='saveall' " . (($thissurvey['active'] != "Y") ? "disabled='disabled'" : "") . "/>";
            }
            else
            {
                $_saveall = '';
            }
        }
        elseif (isset($_SESSION['scid']) && (isset($move) && $move == "movelast"))
        {  //Already saved and on Submit Page, dont show Save So Far button
            $_saveall = '';
        }
        else
        {
            $_saveall = "<input type='button' name='saveallbtn' value='" . $clang->gT("Resume Later") . "' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" " . (($thissurvey['active'] != "Y") ? "disabled='disabled'" : "") . "/>";  // Show Save So Far button
        }
    }
    else
    {
        $_saveall = "";
    }

    $_templatecss = "<link rel='stylesheet' type='text/css' href='{$templateurl}template.css' />\n";
    if (getLanguageRTL($clang->langcode))
    {
        $_templatecss.="<link rel='stylesheet' type='text/css' href='{$templateurl}template-rtl.css' />\n";
    }

    if(!isset($help)) $help = "";
    if (FlattenText($help, true) != '')
    {
        if (!isset($helpicon))
        {
            if (file_exists($templatedir . '/help.gif'))
            {
                $helpicon = $templateurl . 'help.gif';
            }
            elseif (file_exists($templatedir . '/help.png'))
            {
                $helpicon = $templateurl . 'help.png';
            }
            else
            {
                $helpicon=$CI->config->item('imageurl')."/help.gif";
            }
        }
        $_questionhelp =  "<img src='{$helpicon}' alt='Help' align='left' />".$help;
    }
    else
    {
        $_questionhelp = $help;
    }

    if (isset($thissurvey['allowprev']) && $thissurvey['allowprev'] == "N")
    {
        $_strreview = "";
    }
    else
    {
        $_strreview = $clang->gT("If you want to check any of the answers you have made, and/or change them, you can do that now by clicking on the [<< prev] button and browsing through your responses.");
    }

    if (isset($thissurvey['active']) and $thissurvey['active'] == "N")
    {
        $_restart= "<a href='".site_url("survey/sid/$surveyid/newtest/Y");
        if (isset($s_lang) && $s_lang!='') $_restart.="/lang/".$s_lang;
        $_restart.="'>".$clang->gT("Restart this Survey")."</a>";
    }
    else if (isset($surveyid))
        {
            $restart_extra = "";
            $restart_token = returnglobal('token');
            if (!empty($restart_token)) $restart_extra .= "/token/".urlencode($restart_token);
            else $restart_extra = "/newtest/Y";
            if (!empty($_GET['lang'])) $restart_extra .= "/lang/".returnglobal('lang');
            $_restart = "<a href='".site_url("survey/sid/$surveyid$restart_extra")."'>".$clang->gT("Restart this Survey")."</a>";
    }
    else
    {
        $_restart = "";
    }

    if (isset($thissurvey['anonymized']) && $thissurvey['anonymized'] == 'Y')
    {
        $_savealert = $clang->gT("To remain anonymous please use a pseudonym as your username, also an email address is not required.");
    }
    else
    {
        $_savealert = "";
    }

    if (isset($surveyid))
    {
        $_return_to_survey = "<a href='$relativeurl/index.php?sid=$surveyid";
        if (returnglobal('token'))
        {
            $_return_to_survey.= "&amp;token=" . urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
        }
        $_return_to_survey .= "'>" . $clang->gT("Return To Survey") . "</a>";
    }
    else
    {
        $_return_to_survey = "";
    }

    // Save Form
    $_saveform = "<table><tr><td align='right'>" . $clang->gT("Name") . ":</td><td><input type='text' name='savename' value='";
    if (isset($_POST['savename']))
    {
        $_saveform .= html_escape(auto_unescape($_POST['savename']));
    }
    $_saveform .= "' /></td></tr>\n"
    . "<tr><td align='right'>" . $clang->gT("Password") . ":</td><td><input type='password' name='savepass' value='";
    if (isset($_POST['savepass']))
    {
        $_saveform .= html_escape(auto_unescape($_POST['savepass']));
    }
    $_saveform .= "' /></td></tr>\n"
    . "<tr><td align='right'>" . $clang->gT("Repeat Password") . ":</td><td><input type='password' name='savepass2' value='";
    if (isset($_POST['savepass2']))
    {
        $_saveform .= html_escape(auto_unescape($_POST['savepass2']));
    }
    $_saveform .= "' /></td></tr>\n"
    . "<tr><td align='right'>" . $clang->gT("Your Email") . ":</td><td><input type='text' name='saveemail' value='";
    if (isset($_POST['saveemail']))
    {
        $_saveform .= html_escape(auto_unescape($_POST['saveemail']));
    }
    $_saveform .= "' /></td></tr>\n";
    if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && captcha_enabled('saveandloadscreen', $thissurvey['usecaptcha']))
    {
        $_saveform .="<tr><td align='right'>" . $clang->gT("Security Question") . ":</td><td><table><tr><td valign='middle'><img src='".site_url('/verification/image')."' alt='' /></td><td valign='middle' style='text-align:left'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
    }
    $_saveform .= "<tr><td align='right'></td><td></td></tr>\n"
    . "<tr><td></td><td><input type='submit'  id='savebutton' name='savesubmit' value='" . $clang->gT("Save Now") . "' /></td></tr>\n"
    . "</table>";

    // Load Form
    $_loadform = "<table><tr><td align='right'>" . $clang->gT("Saved name") . ":</td><td><input type='text' name='loadname' value='";
    if (isset($loadname))
    {
        $_loadform .= html_escape(auto_unescape($loadname));
    }
    $_loadform .= "' /></td></tr>\n"
    . "<tr><td align='right'>" . $clang->gT("Password") . ":</td><td><input type='password' name='loadpass' value='";
    if (isset($loadpass))
    {
        $_loadform .= html_escape(auto_unescape($loadpass));
    }
    $_loadform .= "' /></td></tr>\n";
    if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && captcha_enabled('saveandloadscreen', $thissurvey['usecaptcha']))
    {
        $_loadform .="<tr><td align='right'>" . $clang->gT("Security Question") . ":</td><td><table><tr><td valign='middle'><img src='".site_url('/verification/image')."' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' alt=''/></td></tr></table></td></tr>\n";
    }
    $_loadform .="<tr><td align='right'></td><td></td></tr>\n"
    . "<tr><td></td><td><input type='submit' id='loadbutton' value='" . $clang->gT("Load Now") . "' /></td></tr></table>\n";

    // Registration Form
    if (isset($surveyid))
    {
        $_registerform = "<form method='post' action='".$CI->config->item('publicurl')."/register.php'>\n";
        if (!isset($_REQUEST['lang']))
        {
            $_reglang = GetBaseLanguageFromSurveyID($surveyid);
        }
        else
        {
            $_reglang = returnglobal('lang');
        }
        $_registerform .= "<input type='hidden' name='lang' value='" . $_reglang . "' />\n";
        $_registerform .= "<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";

        $_registerform.="<table class='register' summary='Registrationform'>\n"
        . "<tr><td align='right'>"
        . $clang->gT("First name") . ":</td>"
        . "<td align='left'><input class='text' type='text' name='register_firstname'";
        if (isset($_POST['register_firstname']))
        {
            $_registerform .= " value='" . htmlentities(returnglobal('register_firstname'), ENT_QUOTES, 'UTF-8') . "'";
        }
        $_registerform .= " /></td></tr>"
        . "<tr><td align='right'>" . $clang->gT("Last name") . ":</td>\n"
        . "<td align='left'><input class='text' type='text' name='register_lastname'";
        if (isset($_POST['register_lastname']))
        {
            $_registerform .= " value='" . htmlentities(returnglobal('register_lastname'), ENT_QUOTES, 'UTF-8') . "'";
        }
        $_registerform .= " /></td></tr>\n"
        . "<tr><td align='right'>" . $clang->gT("Email address") . ":</td>\n"
        . "<td align='left'><input class='text' type='text' name='register_email'";
        if (isset($_POST['register_email']))
        {
            $_registerform .= " value='" . htmlentities(returnglobal('register_email'), ENT_QUOTES, 'UTF-8') . "'";
        }
        $_registerform .= " /></td></tr>\n";
        if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && captcha_enabled('registrationscreen', $thissurvey['usecaptcha']))
        {
            $_registerform .="<tr><td align='right'>" . $clang->gT("Security Question") . ":</td><td><table><tr><td valign='middle'><img src='".site_url('/verification/image')."' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
        $_registerform .= "<tr><td></td><td><input id='registercontinue' class='submit' type='submit' value='" . $clang->gT("Continue") . "' />"
        . "</td></tr>\n"
        . "</table>\n"
        . "</form>\n";
    }
    else
    {
        $_registerform = "";
    }

    // Assessments
    if (isset($surveyid) && !is_null($surveyid) && function_exists('doAssessment'))
    {
        $assessmentdata = doAssessment($surveyid, true);
        $_assessment_current_total = $assessmentdata['total'];
    }
    else
    {
        $_assessment_current_total = '';
    }


    // Set the array of replacement variables here - don't include curly braces
    $coreReplacements = array();
    $coreReplacements['AID'] = isset($questiondetails['aid']) ? $questiondetails['aid'] : '';
    $coreReplacements['ANSWER'] = isset($answer) ? $answer : '';  // global
    $coreReplacements['ANSWERSCLEARED'] = $clang->gT("Answers Cleared");
    $coreReplacements['ASSESSMENTS'] = isset($assessments) ? $assessments : '';    // global
    $coreReplacements['ASSESSMENT_CURRENT_TOTAL'] = $_assessment_current_total;
    $coreReplacements['ASSESSMENT_HEADING'] = $clang->gT("Your Assessment");
    $coreReplacements['CHECKJAVASCRIPT'] = "<noscript><span class='warningjs'>".$clang->gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.")."</span></noscript>";
    $coreReplacements['CLEARALL'] = $_clearall;
    $coreReplacements['CLOSEWINDOW']  =  "<a href='javascript:%20self.close()'>".$clang->gT("Close this window")."</a>";
    $coreReplacements['COMPLETED'] = isset($completed) ? $completed : '';    // global
    $coreReplacements['DATESTAMP'] = $_datestamp;
    $coreReplacements['EXPIRY'] = $_dateoutput;
    $coreReplacements['GID'] = isset($questiondetails['gid']) ? $questiondetails['gid']: '';
    $coreReplacements['GROUPDESCRIPTION'] = $_groupdescription;
    $coreReplacements['GROUPNAME'] = $_groupname;
    $coreReplacements['LANG'] = $clang->getlangcode();
    $coreReplacements['LANGUAGECHANGER'] = isset($languagechanger) ? $languagechanger : '';    // global
    $coreReplacements['LOADERROR'] = isset($errormsg) ? $errormsg : ''; // global
    $coreReplacements['LOADFORM'] = $_loadform;
    $coreReplacements['LOADHEADING'] = $clang->gT("Load A Previously Saved Survey");
    $coreReplacements['LOADMESSAGE'] = $clang->gT("You can load a survey that you have previously saved from this screen.")."<br />".$clang->gT("Type in the 'name' you used to save the survey, and the password.")."<br />";
    $coreReplacements['NAVIGATOR'] = isset($navigator) ? $navigator : '';    // global
    $coreReplacements['NOSURVEYID'] = (isset($surveylist))?$surveylist['nosid']:'';
    $coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
    $coreReplacements['PERCENTCOMPLETE'] = isset($percentcomplete) ? $percentcomplete : '';    // global
    $coreReplacements['PRIVACY'] = isset($privacy) ? $privacy : '';    // global
    $coreReplacements['PRIVACYMESSAGE'] = "<span style='font-weight:bold; font-style: italic;'>".$clang->gT("A Note On Privacy")."</span><br />".$clang->gT("This survey is anonymous.")."<br />".$clang->gT("The record kept of your survey responses does not contain any identifying information about you unless a specific question in the survey has asked for this. If you have responded to a survey that used an identifying token to allow you to access the survey, you can rest assured that the identifying token is not kept with your responses. It is managed in a separate database, and will only be updated to indicate that you have (or haven't) completed this survey. There is no way of matching identification tokens with survey responses in this survey.");
    $coreReplacements['QID'] = isset($questiondetails['qid']) ? $questiondetails['qid'] : '';
    $coreReplacements['QUESTION'] = $_question;
    $coreReplacements['QUESTIONHELP'] = $_questionhelp;
    $coreReplacements['QUESTIONHELPPLAINTEXT'] = strip_tags(addslashes($help)); // global
    $coreReplacements['QUESTION_CLASS'] = $_question_class;
    $coreReplacements['QUESTION_CODE'] = $_question_code;
    $coreReplacements['QUESTION_ESSENTIALS'] = $_question_essentials;
    $coreReplacements['QUESTION_FILE_VALID_MESSAGE'] = $_question_file_valid_message;
    $coreReplacements['QUESTION_HELP'] = $_question_help;
    $coreReplacements['QUESTION_INPUT_ERROR_CLASS'] = $_question_input_error_class;
    $coreReplacements['QUESTION_MANDATORY'] = $_question_mandatory;
    $coreReplacements['QUESTION_MAN_CLASS'] = $_question_man_class;
    $coreReplacements['QUESTION_MAN_MESSAGE'] = $_question_man_message;
    $coreReplacements['QUESTION_NUMBER'] = $_question_number;
    $coreReplacements['QUESTION_TEXT'] = $_question_text;
    $coreReplacements['QUESTION_VALID_MESSAGE'] = $_question_valid_message;
    $coreReplacements['REGISTERERROR'] = isset($register_errormsg) ? $register_errormsg : '';    // global
    $coreReplacements['REGISTERFORM'] = $_registerform;
    $coreReplacements['REGISTERMESSAGE1'] = $clang->gT("You must be registered to complete this survey");
    $coreReplacements['REGISTERMESSAGE2'] = $clang->gT("You may register for this survey if you wish to take part.")."<br />\n".$clang->gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");
    $coreReplacements['RESTART'] = $_restart;
    $coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
    $coreReplacements['SAVE'] = $_saveall;
    $coreReplacements['SAVEALERT'] = $_savealert;
    $coreReplacements['SAVEDID'] = isset($saved_id) ? $saved_id : '';   // global
    $coreReplacements['SAVEERROR'] = isset($errormsg) ? $errormsg : ''; // global - same as LOADERROR
    $coreReplacements['SAVEFORM'] = $_saveform;
    $coreReplacements['SAVEHEADING'] = $clang->gT("Save Your Unfinished Survey");
    $coreReplacements['SAVEMESSAGE'] = $clang->gT("Enter a name and password for this survey and click save below.")."<br />\n".$clang->gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.")."<br /><br />\n".$clang->gT("If you give an email address, an email containing the details will be sent to you.")."<br /><br />\n".$clang->gT("After having clicked the save button you can either close this browser window or continue filling out the survey.");
    $coreReplacements['SGQ'] = $_question_sgq;
    $coreReplacements['SID'] = (isset($surveyid) ? $surveyid : (isset($questiondetails['sid']) ? $questiondetails['sid'] : ''));
    $coreReplacements['SITENAME'] = isset($sitename) ? $sitename : '';  // global
    $coreReplacements['SUBMITBUTTON'] = $_submitbutton;
    $coreReplacements['SUBMITCOMPLETE'] = "<strong>".$clang->gT("Thank you!")."<br /><br />".$clang->gT("You have completed answering the questions in this survey.")."</strong><br /><br />".$clang->gT("Click on 'Submit' now to complete the process and save your answers.");
    $coreReplacements['SUBMITREVIEW'] = $_strreview;
    $coreReplacements['SURVEYCONTACT'] = $surveycontact;
    $coreReplacements['SURVEYDESCRIPTION'] = (isset($thissurvey['description']) ? $thissurvey['description'] : '');
    $coreReplacements['SURVEYFORMAT'] = isset($surveyformat) ? $surveyformat : '';  // global
    $coreReplacements['SURVEYLANGAGE'] = $clang->langcode;
    $coreReplacements['SURVEYLIST'] = (isset($surveylist))?$surveylist['list']:'';
    $coreReplacements['SURVEYLISTHEADING'] =  (isset($surveylist))?$surveylist['listheading']:'';
    $coreReplacements['SURVEYNAME'] = (isset($thissurvey['name']) ? $thissurvey['name'] : '');
    $coreReplacements['TEMPLATECSS'] = $_templatecss;
    $coreReplacements['TEMPLATEURL'] = $templateurl;
    $coreReplacements['THEREAREXQUESTIONS'] = $_therearexquestions;
    if (!$anonymized) $coreReplacements['TOKEN'] = $_token;
    $coreReplacements['URL'] = $_linkreplace;
    $coreReplacements['WELCOME'] = (isset($thissurvey['welcome']) ? $thissurvey['welcome'] : '');

    if (!is_null($replacements) && is_array($replacements))
    {
        $doTheseReplacements = array_merge($coreReplacements, $replacements);   // so $replacements overrides core values
    }
    else
    {
        $doTheseReplacements = $coreReplacements;
    }

    // Now do all of the replacements - do recursion within LimeExpressionManager
    $line = LimeExpressionManager::ProcessString($line, $questionNum, $doTheseReplacements, false, 2, 1);
    return $line;

}

/**
* insertAnsReplace() takes a string and looks for any {INSERTANS:xxxx} variables
*  which it then, one by one, substitutes the SGQA code with the relevant answer
*  from the session array containing responses
*
*  The operations of this function were previously in the templatereplace function
*  but have been moved to a function of their own to make it available
*  to other areas of the script.
*
* @param mixed $line   string - the string to iterate, and then return
*
* @return string This string is returned containing the substituted responses
*
*/
function insertansReplace($line)
{
    return $line;
}

/**
* tokenReplace() takes a string and looks for any {TOKEN:xxxx} variables
*  which it then, one by one, substitutes the TOKEN code with the relevant token
*  from the session array containing token information
*
*  The operations of this function were previously in the templatereplace function
*  but have been moved to a function of their own to make it available
*  to other areas of the script.
*
* @param mixed $line   string - the string to iterate, and then return
*
* @return string This string is returned containing the substituted responses
*
*/
function tokenReplace($line)
{
    return $line;
}

// This function replaces field names in a text with the related values
// (e.g. for email and template functions)
function ReplaceFields ($text,$fieldsarray, $bReplaceInsertans=false)
{

    foreach ( $fieldsarray as $key => $value )
    {
        $text=str_replace($key, $value, $text);
    }

    if ($bReplaceInsertans)
    {
        $text = insertansReplace($text);
    }
    return $text;
}


/**
* passthruReplace() takes a string and looks for {PASSTHRU:myarg} variables
*  which it then substitutes for parameter data sent in the initial URL and stored
*  in the session array containing responses
*
* @param mixed $line   string - the string to iterate, and then return
* @param mixed $thissurvey     string - the string containing the surveyinformation
* @return string This string is returned containing the substituted responses
*
*/
function PassthruReplace($line, $thissurvey)
{
    while (strpos($line,"{PASSTHRU:") !== false)
    {
        $p1 = strpos($line,"{PASSTHRU:"); // startposition
        $p2 = $p1 + 10; // position of the first arg char
        $p3 = strpos($line,"}",10); // position of the last arg char

        $cmd=substr($line,$p1,$p3-$p1+1); // extract the complete passthru like "{PASSTHRU:myarg}"
        $arg=substr($line,$p2,$p3-$p2); // extract the arg to passthru (like "myarg")

        // lookup for the fitting arg
        $sValue='';
        if (isset($_SESSION['urlparams'][$arg]))
        {
            $sValue=urlencode($_SESSION['urlparams'][$arg]);
        }
        $line=str_replace($cmd, $sValue, $line); // replace
    }

    return $line;
}


// Closing PHP tag intentionally omitted - yes, it is okay
