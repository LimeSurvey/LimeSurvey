<?php

include_once('/classes/eval/LimeExpressionManager.php');

/**
 * This function replaces keywords in a text and is mainly intended for templates
 * If you use this functions put your replacement strings into the $replacements variable
 * instead of using global variables
 * NOTE - Don't do any embedded replacements in this function.  Create the array of replacement values and
 * they will be done in batch at the end
 *
 * @param string $line Text to search in
 * @param array $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>, where <stringtosearch> is NOT surrounded with curly braces
 * @param boolean $anonymized Determines if token data is being used or just replaced with blanks
 * @return string  Text with replaced strings
 */
function templatereplace($line, $replacements=array(), $anonymized=false)
{
    global $surveylist, $sitename, $clienttoken, $rooturl;
    global $thissurvey, $imageurl, $defaulttemplate;
    global $percentcomplete, $move;
    global $groupname, $groupdescription;
    global $question;
    global $showXquestions, $showgroupinfo, $showqnumcode;
    global $answer, $navigator;
    global $help, $surveyformat;
    global $completed, $register_errormsg;
    global $privacy, $surveyid;
    global $publicurl, $templatedir, $token;
    global $assessments, $s_lang;
    global $errormsg, $clang;
    global $saved_id;
    global $totalBoilerplatequestions, $relativeurl;
    global $languagechanger;
    global $captchapath, $loadname;

    // lets sanitize the survey template
    if (isset($thissurvey['templatedir']))
    {
        $_templatename = $thissurvey['templatedir'];
    }
    else
    {
        $_templatename = $defaulttemplate;
    }
    $_templatename = validate_templatedir($_templatename);

    // create absolute template URL and template dir vars
    $_templateurl = sGetTemplateURL($_templatename) . '/';
    $templatedir = sgetTemplatePath($_templatename);

    if (stripos($line, "</head>"))
    {
        $line = str_ireplace("</head>",
            "<script type=\"text/javascript\" src=\"$rooturl/scripts/survey_runtime.js\"></script>\n"
                        . use_firebug()
                        . "\t</head>", $line);
    }
    // Get some vars : move elsewhere ?
    // surveyformat
    if (isset($thissurvey['format']))
    {
        $surveyformat = str_replace(array("A", "S", "G"), array("allinone", "questionbyquestion", "groupbygroup"), $thissurvey['format']);
    }
    else
    {
        $surveyformat = "";
    }
    // real survey contact
    if (isset($surveylist['contact']))
    {
        $_surveycontact = $surveylist['contact'];
    }
    elseif (isset($thissurvey['admin']) && $thissurvey['admin'] != "")
    {
        $_surveycontact = sprintf($clang->gT("Please contact %s ( %s ) for further assistance."), $thissurvey['admin'], $thissurvey['adminemail']);
    }
    else
    {
        $_surveycontact = "";
    }

    // If there are non-bracketed replacements to be made do so above this line.
    // Only continue in this routine if there are bracketed items to replace {}
    if (strpos($line, "{") === false)
    {
        return $line;
    }

    if (
        $showgroupinfo == 'both' ||
	    $showgroupinfo == 'name' ||
	    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
	    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
	    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'N')
    )
    {
        $_groupname = $groupname;
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
        $_groupdescription = $groupdescription;
    }
    else
    {
        $_groupdescription = '';
    };

    if (is_array($question))
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
        $_question = $question;
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

    if (isset($_SESSION['therearexquestions']))
    {
        $_totalquestionsAsked = $_SESSION['therearexquestions'] - $totalBoilerplatequestions;
    }
    else {
        $_totalquestionsAsked = 0;
    }
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
        $_token = htmlentities($clienttoken, ENT_QUOTES, 'UTF-8');
        }
    else
    {
        $_token = '';
    }

    if (isset($thissurvey['surveyls_dateformat']))
    {
        $dateformatdetails = getDateFormatData($thissurvey['surveyls_dateformat']);
    }
    else {
        $dateformatdetails = getDateFormatData();
    }
    if (isset($thissurvey['expiry']))
    {
        $_datetimeobj = new Date_Time_Converter($thissurvey['expiry'], "Y-m-d");
        $_dateoutput = $_datetimeobj->convert($dateformatdetails['phpdate']);
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

    if (isset($clienttoken))
    {
        $token = $clienttoken;
    }
    else
    {
        $token = '';
    }

    if (!isset($_SESSION['s_lang']))
    {
        $_s_lang = 'en';
    }
    else
    {
        $_s_lang = $_SESSION['s_lang'];
    }

    $_clearall = "<input type='button' name='clearallbtn' value='" . $clang->gT("Exit and Clear Survey") . "' class='clearall' "
            . "onclick=\"if (confirm('" . $clang->gT("Are you sure you want to clear all your responses?", 'js') . "')) {\nwindow.open('{$publicurl}/index.php?sid=$surveyid&amp;move=clearall&amp;lang=" . $_s_lang;
        if (returnglobal('token'))
        {
        $_clearall .= "&amp;token=" . urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
        }
        $_clearall .= "', '_top')}\" />";

    if (isset($_SESSION['datestamp']))
    {
        $_datestamp = $_SESSION['datestamp'];
    }
    else
    {
        $_datestamp = '-';
        }
        //Set up save/load feature
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
		};
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

    $_templatecss = "<link rel='stylesheet' type='text/css' href='{$_templateurl}template.css' />\n";
        if (getLanguageRTL($clang->langcode))
        {
            $_templatecss.="<link rel='stylesheet' type='text/css' href='{$_templateurl}template-rtl.css' />\n";
        }

    if (FlattenText($help, true) != '')
        {
            If (!isset($helpicon))
            {
            if (file_exists($templatedir . '/help.gif'))
                {

                $helpicon = $_templateurl . 'help.gif';
                }
            elseif (file_exists($templatedir . '/help.png'))
                {

                $helpicon = $_templateurl . 'help.png';
                }
                else
                {
                $helpicon = $imageurl . "/help.gif";
                }
            }
            $_questionhelp =  "<img src='{$helpicon}' alt='Help' align='left' />".$help;
        }
    else
    {
        $_questionhelp = '';
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
        $_restart = "<a href='{$publicurl}/index.php?sid=$surveyid&amp;newtest=Y";
        if (isset($s_lang) && $s_lang != '') {
            $_restart.="&amp;lang=" . $s_lang;
        }
        $_restart.="'>" . $clang->gT("Restart this Survey") . "</a>";
    } else
        {
            $restart_extra = "";
            $restart_token = returnglobal('token');
        if (!empty($restart_token))
            $restart_extra .= "&amp;token=" . urlencode($restart_token);
        else
            $restart_extra = "&amp;newtest=Y";
        if (!empty($_GET['lang']))
            $restart_extra .= "&amp;lang=" . returnglobal('lang');
        $_restart = "<a href='{$publicurl}/index.php?sid=$surveyid" . $restart_extra . "'>" . $clang->gT("Restart this Survey") . "</a>";
        }
    if (isset($thissurvey['anonymized']) && $thissurvey['anonymized'] == 'Y')
    {
        $_savealert = $clang->gT("To remain anonymous please use a pseudonym as your username, also an email address is not required.");
        }
        else
        {
        $_savealert = "";
        }

        $_return_to_survey = "<a href='$relativeurl/index.php?sid=$surveyid";
        if (returnglobal('token'))
        {
        $_return_to_survey.= "&amp;token=" . urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
        }
    $_return_to_survey .= "'>" . $clang->gT("Return To Survey") . "</a>";

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
        $_saveform .="<tr><td align='right'>" . $clang->gT("Security Question") . ":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php?sid=$surveyid' alt='' /></td><td valign='middle' style='text-align:left'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
        $_saveform .= "<tr><td align='right'></td><td></td></tr>\n"
            . "<tr><td></td><td><input type='submit'  id='savebutton' name='savesubmit' value='" . $clang->gT("Save Now") . "' /></td></tr>\n"
        . "</table>";

    $_loadform = "<table><tr><td align='right'>" . $clang->gT("Saved name") . ":</td><td><input type='text' name='loadname' value='";
    if ($loadname)
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
        $_loadform .="<tr><td align='right'>" . $clang->gT("Security Question") . ":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php?sid=$surveyid' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' alt=''/></td></tr></table></td></tr>\n";
        }

        $_loadform .="<tr><td align='right'></td><td></td></tr>\n"
            . "<tr><td></td><td><input type='submit' id='loadbutton' value='" . $clang->gT("Load Now") . "' /></td></tr></table>\n";

    $_registerform = "<form method='post' action='{$publicurl}/register.php'>\n";
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
        $_registerform .="<tr><td align='right'>" . $clang->gT("Security Question") . ":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php?sid=$surveyid' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }


        /*      if(isset($thissurvey['attribute1']) && $thissurvey['attribute1'])
         {
         $_registerform .= "<tr><td align='right'>".$thissurvey['attribute1'].":</td>\n"
         ."<td align='left'><input class='text' type='text' name='register_attribute1'";
         if (isset($_POST['register_attribute1']))
         {
         $_registerform .= " value='".htmlentities(returnglobal('register_attribute1'),ENT_QUOTES,'UTF-8')."'";
         }
         $_registerform .= " /></td></tr>\n";
         }
         if(isset($thissurvey['attribute2']) && $thissurvey['attribute2'])
         {
         $_registerform .= "<tr><td align='right'>".$thissurvey['attribute2'].":</td>\n"
         ."<td align='left'><input class='text' type='text' name='register_attribute2'";
         if (isset($_POST['register_attribute2']))
         {
         $_registerform .= " value='".htmlentities(returnglobal('register_attribute2'),ENT_QUOTES,'UTF-8')."'";
         }
         $_registerform .= " /></td></tr>\n";
      } */
    $_registerform .= "<tr><td></td><td><input id='registercontinue' class='submit' type='submit' value='" . $clang->gT("Continue") . "' />"
            . "</td></tr>\n"
            . "</table>\n"
            . "</form>\n";

    if (!is_null($surveyid) && function_exists('doAssessment'))
    {
        $assessmentdata = doAssessment($surveyid, true);
        $_assessment_current_total = $assessmentdata['total'];
    }
    else
    {
        $_assessment_current_total = '';
    }

    // Set the array of replacement variables here - don't include curly braces
	$corecoreReplacements = array();
	$coreReplacements['ANSWER'] = $answer;  // global
	$coreReplacements['ANSWERSCLEARED'] = $clang->gT("Answers Cleared");
	$coreReplacements['ASSESSMENTS'] = $assessments;    // global
	$coreReplacements['ASSESSMENT_CURRENT_TOTAL'] = $_assessment_current_total;
	$coreReplacements['ASSESSMENT_HEADING'] = $clang->gT("Your Assessment");
	$coreReplacements['CHECKJAVASCRIPT'] = "<noscript><span class='warningjs'>".$clang->gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.")."</span></noscript>";
	$coreReplacements['CLEARALL'] = $_clearall;
	$coreReplacements['CLOSEWINDOW']  =  "<a href='javascript:%20self.close()'>".$clang->gT("Close this window")."</a>";
	$coreReplacements['COMPLETED'] = $completed;    // global
	$coreReplacements['DATESTAMP'] = $_datestamp;
	$coreReplacements['EXPIRY'] = $_dateoutput;
	$coreReplacements['GROUPDESCRIPTION'] = $_groupdescription;
	$coreReplacements['GROUPNAME'] = $_groupname;
	$coreReplacements['LANG'] = $clang->getlangcode();
	$coreReplacements['LANGUAGECHANGER'] = $languagechanger;    // global
	$coreReplacements['LOADERROR'] = $errormsg; // global
	$coreReplacements['LOADFORM'] = $_loadform;
	$coreReplacements['LOADHEADING'] = $clang->gT("Load A Previously Saved Survey");
	$coreReplacements['LOADMESSAGE'] = $clang->gT("You can load a survey that you have previously saved from this screen.")."<br />".$clang->gT("Type in the 'name' you used to save the survey, and the password.")."<br />";
	$coreReplacements['NAVIGATOR'] = $navigator;    // global
	$coreReplacements['NOSURVEYID'] = $surveylist['nosid']; // global
	$coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
	$coreReplacements['PERCENTCOMPLETE'] = $percentcomplete;    // global
	$coreReplacements['PRIVACY'] = $privacy;    // global
	$coreReplacements['PRIVACYMESSAGE'] = "<span style='font-weight:bold; font-style: italic;'>".$clang->gT("A Note On Privacy")."</span><br />".$clang->gT("This survey is anonymous.")."<br />".$clang->gT("The record kept of your survey responses does not contain any identifying information about you unless a specific question in the survey has asked for this. If you have responded to a survey that used an identifying token to allow you to access the survey, you can rest assured that the identifying token is not kept with your responses. It is managed in a separate database, and will only be updated to indicate that you have (or haven't) completed this survey. There is no way of matching identification tokens with survey responses in this survey.");
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
	$coreReplacements['REGISTERERROR'] = $register_errormsg;    // global
	$coreReplacements['REGISTERFORM'] = $_registerform;
	$coreReplacements['REGISTERMESSAGE1'] = $clang->gT("You must be registered to complete this survey");
	$coreReplacements['REGISTERMESSAGE2'] = $clang->gT("You may register for this survey if you wish to take part.")."<br />\n".$clang->gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");
	$coreReplacements['RESTART'] = $_restart;
	$coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
	$coreReplacements['SAVE'] = $_saveall;
	$coreReplacements['SAVEALERT'] = $_savealert;
	$coreReplacements['SAVEDID'] = $saved_id;   // global
	$coreReplacements['SAVEERROR'] = $errormsg; // global - same as LOADERROR
	$coreReplacements['SAVEFORM'] = $_saveform;
	$coreReplacements['SAVEHEADING'] = $clang->gT("Save Your Unfinished Survey");
	$coreReplacements['SAVEMESSAGE'] = $clang->gT("Enter a name and password for this survey and click save below.")."<br />\n".$clang->gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.")."<br /><br />\n".$clang->gT("If you give an email address, an email containing the details will be sent to you.")."<br /><br />\n".$clang->gT("After having clicked the save button you can either close this browser window or continue filling out the survey.");
	$coreReplacements['SGQ'] = $_question_sgq;
	$coreReplacements['SID'] = $surveyid;   // global
	$coreReplacements['SITENAME'] = $sitename;  // global
	$coreReplacements['SUBMITBUTTON'] = $_submitbutton;
	$coreReplacements['SUBMITCOMPLETE'] = "<strong>".$clang->gT("Thank you!")."<br /><br />".$clang->gT("You have completed answering the questions in this survey.")."</strong><br /><br />".$clang->gT("Click on 'Submit' now to complete the process and save your answers.");
	$coreReplacements['SUBMITREVIEW'] = $_strreview;
	$coreReplacements['SURVEYCONTACT'] = $_surveycontact;
	$coreReplacements['SURVEYDESCRIPTION'] = (isset($thissurvey['description']) ? $thissurvey['description'] : '');
	$coreReplacements['SURVEYFORMAT'] = $surveyformat;  // global
	$coreReplacements['SURVEYLANGAGE'] = $clang->langcode;
	$coreReplacements['SURVEYLIST'] = $surveylist['list'];  // global
	$coreReplacements['SURVEYLISTHEADING'] =  $surveylist['listheading'];   // global
	$coreReplacements['SURVEYNAME'] = $thissurvey['name'];  // global
	$coreReplacements['TEMPLATECSS'] = $_templatecss;
	$coreReplacements['TEMPLATEURL'] = $_templateurl;
	$coreReplacements['THEREAREXQUESTIONS'] = $_therearexquestions;
	if (!$anonymized) $coreReplacements['TOKEN'] = $_token;
	$coreReplacements['URL'] = $_linkreplace;
	$coreReplacements['WELCOME'] = (isset($thissurvey['welcome']) ? $thissurvey['welcome'] : '');

    $doTheseReplacements = array_merge($coreReplacements, $replacements);   // so $replacements overrides core values

    // Now do all of the replacements - either call it twice or do recursion within LimeExpressionManager
    $line = LimeExpressionManager::ProcessString($line, $doTheseReplacements, false, $anonymized);
    return LimeExpressionManager::ProcessString($line, $doTheseReplacements, false, $anonymized);
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
//    return LimeExpressionManager::ProcessString($line);
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
 * @param string $line  the string to iterate, and then return
 * @param boolean $anynomized  Sets if the underlying token data should be not used
 *
 * @return string This string is returned containing the substituted responses
 *
 */
function tokenReplace($line, $anonymized=false)
{
    return $line;
//    return LimeExpressionManager::ProcessString($line,array(),false,$anonymized);
}

/**
 * passthruReplace() takes a string and looks for {PASSTHRULABEL}, {PASSTHRUVALUE} and {PASSTHRU:myarg} variables
 *  which it then substitutes for passthru data sent in the initial URL and stored
 *  in the session array containing responses
 *
 * @param mixed $line   string - the string to iterate, and then return
 * @param mixed $thissurvey     string - the string containing the surveyinformation
 * @return string This string is returned containing the substituted responses
 *
 */
function PassthruReplace($line, $thissurvey)
{
    $line=str_replace("{PASSTHRULABEL}", $thissurvey['passthrulabel'], $line);
    $line=str_replace("{PASSTHRUVALUE}", $thissurvey['passthruvalue'], $line);

    if (!isset($_SESSION['ls_initialquerystr'])) return $line;
    //  Replacement for variable passthru argument like {PASSTHRU:myarg}
    while (strpos($line,"{PASSTHRU:") !== false)
    {
        $p1 = strpos($line,"{PASSTHRU:"); // startposition
        $p2 = $p1 + 10; // position of the first arg char
        $p3 = strpos($line,"}",10); // position of the last arg char

        $cmd=substr($line,$p1,$p3-$p1+1); // extract the complete passthru like "{PASSTHRU:myarg}"
        $arg=substr($line,$p2,$p3-$p2); // extract the arg to passthru (like "myarg")

        // lookup for the fitting arg
        $qstring = $_SESSION['ls_initialquerystr']; // get initial query_string

        parse_str($qstring, $keyvalue); // split into key and value
        $match = 0; // prevent an endless loop if there is no arg in url
        foreach ($keyvalue as $key=>$value) // lookup loop
        {
            if ($key == $arg) // if match
            {
                $line=str_replace($cmd, $arg . "=" . $value, $line); // replace
                $match = 1;
                break;
            }

        }

        if ($match == 0)
        {
            $line=str_replace($cmd, $arg . "=", $line); // clears "{PASSTHRU:myarg} to "myarg=" if there was no myarg in calling url
        }
    }

    return $line;
}

?>
