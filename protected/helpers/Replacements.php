<?php
namespace ls\helpers;

use \Yii;
use \CHtml;
use ls\models\Survey;
use \LimeExpressionManager;
use \TbHtml;
class Replacements
{
    /**
     * This function replaces keywords in a text and is mainly intended for templates
     * If you use this functions put your replacement strings into the $replacements variable
     * instead of using global variables
     * NOTE - Don't do any embedded replacements in this function.  Create the array of replacement values and
     * they will be done in batch at the end
     *
     * @param mixed $line Text to search in
     * @param mixed $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>
     * @param array $redata
     * @param $questionNum - needed to support dynamic JavaScript-based tailoring within questions
     * @return string Text with replaced strings
     * @throws CException
     * @internal param string $debugSrc
     * @internal param bool $bStaticReplacement - Default off, forces non-dynamic replacements without <SPAN> tags (e.g. for the Completed page)
     * @internal param array $registerdata
     */
    public static function templatereplace(
        $line,
        $replacements = [],
        $redata = [],
        $questionNum = null,
        \ls\components\SurveySession $session
    ) {
        bP();
        $survey = $session->survey;
        $clientScript = App()->clientScript;


        if (!empty($redata)) {
            throw new \Exception();
            vd($replacements);
            vdd($redata);

            foreach ([
                         'assessments',
                         'captchapath',
                         'completed',
                         'errormsg',
                         'groupdescription',
                         'groupname',
                         'imageurl',
                         'languagechanger',
                         'loadname',
                         'move',
                         'percentcomplete',
                         'privacy',
                         'saved_id',
                         'showgroupinfo',
                         'showqnumcode',
                         'showxquestions',
                         'sitename',
                         'surveylist',
                         'templatedir',
                         'token',
                         'totalBoilerplatequestions',
                         'totalquestions',
                     ] as $var) {
                if (isset($redata[$var])) {
                    $$var = $redata[$var];
                }
            }
        }

        if (!isset($showxquestions)) {
            $showxquestions = Yii::app()->getConfig('showxquestions');
        }



        $templateDir = $session->templateDir;
        $templateUrl = $session->templateUrl;

        // TEMPLATECSS
        $_templatecss = "";
        if (stripos($line, "{TEMPLATECSS}")) {
            if (file_exists($templateDir . DIRECTORY_SEPARATOR . 'jquery-ui-custom.css')) {
                $clientScript->registerCssFile("{$templateUrl}jquery-ui-custom.css");
            } elseif (file_exists($templateDir . DIRECTORY_SEPARATOR . 'jquery-ui.css')) {
                $clientScript->registerCssFile("{$templateUrl}jquery-ui.css");
            } else {
                $clientScript->registerCssFile(App()->publicUrl . '/styles-public/' . "jquery-ui.css");
            }

            $clientScript->registerCssFile("{$templateUrl}template.css");
            if (\ls\helpers\SurveyTranslator::getLanguageRTL(App()->language)) {
                $clientScript->registerCssFile("{$templateUrl}template-rtl.css");
            }
        }
        // surveyformat

        $surveyFormat = str_replace(["A", "S", "G"], ["allinone", "questionbyquestion", "groupbygroup"],
            $session->format);

        if ($session->getStep() % 2 && $surveyFormat != "allinone") {
            $surveyFormat .= " page-odd";
        }

        if ($survey->questionindex > 0 && $session->format != Survey::FORMAT_ALL_IN_ONE && $session->getStep() > 0) {
            $surveyFormat .= " withindex";
        }

        if ($survey->bool_showprogress) {
            $surveyFormat .= " showprogress";
        }

        if (isset($survey->showqnumcode)) {
            $surveyFormat .= " showqnumcode-" . $survey->showqnumcode;
        }
        // real survey contact
        if (isset($surveylist) && isset($surveylist['contact'])) {
            $surveyContact = $surveylist['contact'];
        } elseif (isset($surveylist) && !empty($survey->admin)) {
            $surveyContact = sprintf(gT("Please contact %s ( %s ) for further assistance."), $survey->admin,
                $survey->adminEmail);
        } else {
            $surveyContact = "";
        }

        // If there are non-bracketed replacements to be made do so above this line.
        // Only continue in this routine if there are bracketed items to replace {}
        if (strpos($line, "{") === false) {
            // process string anyway so that it can be pretty-printed
            $result = LimeExpressionManager::ProcessString($line, $questionNum, null, 1, 1, $session);
        } else {

            if (!isset($totalquestions)) {
                $totalquestions = 0;
            }
            $_totalquestionsAsked = $totalquestions;
            if (
                $showxquestions == 'show' ||
                ($showxquestions == 'choose' && !isset($survey->showxquestions)) ||
                ($showxquestions == 'choose' && $survey->bool_showxquestions)
            ) {
                if ($_totalquestionsAsked < 1) {
                    $_therearexquestions = gT("There are no questions in this survey"); // Singular
                } elseif ($_totalquestionsAsked == 1) {
                    $_therearexquestions = gT("There is 1 question in this survey"); //Singular
                } else {
                    $_therearexquestions = gT("There are {NUMBEROFQUESTIONS} questions in this survey.");    //Note this line MUST be before {NUMBEROFQUESTIONS}
                };
            } else {
                $_therearexquestions = '';
            };


            // Expiry
            if (isset($thissurvey['expiry'])) {
                $dateformatdetails = \ls\helpers\SurveyTranslator::getDateFormatData($survey->getLocalizedDateFormat());
                Yii::import('application.libraries.Date_Time_Converter', true);
                $datetimeobj = new Date_Time_Converter($survey->expiry, "Y-m-d");
                $_dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
            } else {
                $_dateoutput = '-';
            }

            $_submitbutton = "<input class='submit' type='submit' value=' " . gT("Submit") . " ' name='move2' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" />";

            if (!empty($survey->localizedEndUrl)) {
                if (!empty($survey->localizedEndUrlDescription)) {
                    $endLink = CHtml::link($survey->localizedEndUrlDescription, $survey->localizedEndUrl);
                } else {
                    $endLink = CHtml::link($survey->localizedEndUrl, $survey->localizedEndUrl);
                }
            } else {
                $endLink = '';
            }


            if (!$session->isFinished) {
                $_clearall = CHtml::htmlButton(gT("Exit and clear survey"), [
                    'type' => 'submit',
                    'formaction' => App()->surveySessionManager->createUrl('surveys/abort'),
                    'id' => "clearall",
                    'value' => 'clearall',
                    'name' => 'clearall',
                    'class' => 'clearall button',
                    'confirm' => gT("Are you sure you want to clear all your responses?")
                ]);
            } else {
                $_clearall = "";
            }

            if (isset(Yii::app()->session['datestamp'])) {
                $_datestamp = Yii::app()->session['datestamp'];
            } else {
                $_datestamp = '-';
            }

            if (!$survey->bool_allowprev) {
                $_strreview = "";
            } else {
                $_strreview = gT("If you want to check any of the answers you have made, and/or change them, you can do that now by clicking on the [<< prev] button and browsing through your responses.");
            }

            $restartParams = [];
            $restartParams['id'] = $survey->primaryKey;

            if (isset($session->response->token)) {
                $restartParams['token'] = $session->response->token;
            }
            if (null !== $lang = App()->request->getQuery('lang')) {
                $restartParams['lang'] = sanitize_languagecode($lang);
            } elseif (isset($session)) {
                $restartParams['lang'] = $session->language;
                $restartParams['id'] = $session->surveyId;
            }
            $restartParams['newtest'] = "Y";

            if (isset($session->response->token)) {
                $url = App()->createUrl("survey/index", [
                    'sid' => $session->surveyId,
                    'token' => $session->response->token
                ]);
            } else {
                $url = App()->createUrl("survey/index", [
                    'sid' => $session->surveyId,
                ]);
            }
            $_return_to_survey = "<a href='{$url}'>" . gT("Return to survey") . "</a>";

            // Save Form
            $_saveform = "<table class='save-survey-form'><tr class='save-survey-row save-survey-name'><td class='save-survey-label label-cell' align='right'><label for='savename'>" . gT("Name") . "</label>:</td><td class='save-survey-input input-cell'><input type='text' name='savename' id='savename' value='";
            if (isset($_POST['savename'])) {
                $_saveform .= HTMLEscape($_POST['savename']);
            }
            $_saveform .= "' /></td></tr>\n"
                . "<tr class='save-survey-row save-survey-password-1'><td class='save-survey-label label-cell' align='right'><label for='savepass'>" . gT("Password") . "</label>:</td><td class='save-survey-input input-cell'><input type='password' id='savepass' name='savepass' value='";
            if (isset($_POST['savepass'])) {
                $_saveform .= HTMLEscape($_POST['savepass']);
            }
            $_saveform .= "' /></td></tr>\n"
                . "<tr class='save-survey-row save-survey-password-2'><td class='save-survey-label label-cell' align='right'><label for='savepass2'>" . gT("Repeat password") . "</label>:</td><td class='save-survey-input input-cell'><input type='password' id='savepass2' name='savepass2' value='";
            if (isset($_POST['savepass2'])) {
                $_saveform .= HTMLEscape($_POST['savepass2']);
            }
            $_saveform .= "' /></td></tr>\n"
                . "<tr class='save-survey-row save-survey-email'><td class='save-survey-label label-cell' align='right'><label for='saveemail'>" . gT("Your email address") . "</label>:</td><td class='save-survey-input input-cell'><input type='text' id='saveemail' name='saveemail' value='";
            if (isset($_POST['saveemail'])) {
                $_saveform .= HTMLEscape($_POST['saveemail']);
            }
            $_saveform .= "' /></td></tr>\n";
            if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen',
                    $thissurvey['usecaptcha'])
            ) {
                $_saveform .= "<tr class='save-survey-row save-survey-captcha'><td class='save-survey-label label-cell' align='right'><label for='loadsecurity'>" . gT("Security question") . "</label>:</td><td class='save-survey-input input-cell'><table class='captcha-table'><tr><td class='captcha-image' valign='middle'><img alt='' src='" . Yii::app()->getController()->createUrl('/verification/image/sid/' . ((isset($surveyId)) ? $surveyId : '')) . "' /></td><td class='captcha-input' valign='middle' style='text-align:left'><input type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
            }
            $_saveform .= "<tr><td align='right'></td><td></td></tr>\n"
                . "<tr class='save-survey-row save-survey-submit'><td class='save-survey-label label-cell'><label class='hide jshide' for='savebutton'>" . gT("Save Now") . "</label></td><td class='save-survey-input input-cell'><input type='submit' id='savebutton' name='savesubmit' class='button' value='" . gT("Save Now") . "' /></td></tr>\n"
                . "</table>";

            // Load Form
            $_loadform = "<table class='load-survey-form'><tr class='load-survey-row load-survey-name'><td class='load-survey-label label-cell' align='right'><label for='loadname'>" . gT("Saved name") . "</label>:</td><td class='load-survey-input input-cell'><input type='text' id='loadname' name='loadname' value='";
            if (isset($loadname)) {
                $_loadform .= HTMLEscape($loadname);
            }
            $_loadform .= "' /></td></tr>\n"
                . "<tr class='load-survey-row load-survey-password'><td class='load-survey-label label-cell' align='right'><label for='loadpass'>" . gT("Password") . "</label>:</td><td class='load-survey-input input-cell'><input type='password' id='loadpass' name='loadpass' value='";
            if (isset($loadpass)) {
                $_loadform .= HTMLEscape($loadpass);
            }
            $_loadform .= "' /></td></tr>\n";
            if (isset($thissurvey['usecaptcha']) && function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen',
                    $thissurvey['usecaptcha'])
            ) {
                $_loadform .= "<tr class='load-survey-row load-survey-captcha'><td class='load-survey-label label-cell' align='right'><label for='loadsecurity'>" . gT("Security question") . "</label>:</td><td class='load-survey-input input-cell'><table class='captcha-table'><tr><td class='captcha-image' valign='middle'><img src='" . Yii::app()->getController()->createUrl('/verification/image/sid/' . ((isset($surveyId)) ? $surveyId : '')) . "' alt='' /></td><td class='captcha-input' valign='middle'><input type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' alt=''/></td></tr></table></td></tr>\n";
            }
            $_loadform .= "<tr class='load-survey-row load-survey-submit'><td class='load-survey-label label-cell'><label class='hide jshide' for='loadbutton'>" . gT("Load now") . "</label></td><td class='load-survey-input input-cell'><input type='submit' id='loadbutton' class='button' value='" . gT("Load now") . "' /></td></tr></table>\n";

            // Assessments
            $assessmenthtml = "";
            if (isset($surveyId) && !is_null($surveyId) && function_exists('\ls\helpers\FrontEnd::doAssessment')) {
                $assessmentdata = \ls\helpers\FrontEnd::doAssessment($surveyId, true);
                $_assessment_current_total = $assessmentdata['total'];
                if (stripos($line, "{ASSESSMENTS}")) {
                    $assessmenthtml = \ls\helpers\FrontEnd::doAssessment($surveyId, false);
                }
            } else {
                $_assessment_current_total = '';
            }

            if (isset($thissurvey['googleanalyticsapikey']) && trim($thissurvey['googleanalyticsapikey']) != '') {
                $_googleAnalyticsAPIKey = trim($thissurvey['googleanalyticsapikey']);
            } else {
                $_googleAnalyticsAPIKey = trim(\ls\models\SettingGlobal::get('googleanalyticsapikey'));
            }
            $_googleAnalyticsStyle = (isset($thissurvey['googleanalyticsstyle']) ? $thissurvey['googleanalyticsstyle'] : '0');
            $_googleAnalyticsJavaScript = '';

            if ($_googleAnalyticsStyle != '' && $_googleAnalyticsStyle != 0 && $_googleAnalyticsAPIKey != '') {
                switch ($_googleAnalyticsStyle) {
                    case '1':
                        // Default Google Tracking
                        $_googleAnalyticsJavaScript = <<<EOD
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '$_googleAnalyticsAPIKey', 'auto');  // Replace with your property ID.
ga('send', 'pageview');

</script>

EOD;
                        break;
                    case '2':
                        // SurveyName-[SID]/[GSEQ]-GroupName - create custom GSEQ based upon page step
                        if (is_null($moveInfo)) {
                            $gseq = 'welcome';
                        } else {
                            if ($moveInfo['finished']) {
                                $gseq = 'finished';
                            } else {
                                if (isset($moveInfo['at_start']) && $moveInfo['at_start']) {
                                    $gseq = 'welcome';
                                } else {
                                    if (is_null($_groupname)) {
                                        $gseq = 'printanswers';
                                    } else {
                                        $gseq = $moveInfo['gseq'] + 1;
                                    }
                                }
                            }
                        }
                        $_trackURL = htmlspecialchars($thissurvey['name'] . '-[' . $surveyId . ']/[' . $gseq . ']-' . $_groupname);
                        $_googleAnalyticsJavaScript = <<<EOD
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '$_googleAnalyticsAPIKey', 'auto');  // Replace with your property ID.
ga('send', 'pageview');
ga('send', 'pageview', '$_trackURL');

</script>
EOD;
                        break;
                }
            }

            $_endtext = '';
            if (isset($thissurvey['surveyls_endtext']) && trim($thissurvey['surveyls_endtext']) != '') {
                $_endtext = $thissurvey['surveyls_endtext'];
            }

            // Set the array of replacement variables here - don't include curly braces
            $coreReplacements = [];
            if(!empty($survey->startdate))
                $coreReplacements['REGISTERMESSAGE2'] = sprintf(gT("You may register for this survey but you have to wait for the %s before starting the survey."), $survey->startdate)."<br />\n".gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");
            else
                $coreReplacements['REGISTERMESSAGE2'] = gT("You may register for this survey if you wish to take part.")."<br />\n".gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");


            $coreReplacements['ACTIVE'] = (isset($thissurvey['active']) && !(!$survey->bool_active));
            $coreReplacements['ANSWERSCLEARED'] = gT("Answers cleared");
            $coreReplacements['ASSESSMENTS'] = $assessmenthtml;
            $coreReplacements['ASSESSMENT_CURRENT_TOTAL'] = $_assessment_current_total;
            $coreReplacements['ASSESSMENT_HEADING'] = gT("Your assessment");
            $coreReplacements['CHECKJAVASCRIPT'] = "<noscript><span class='warningjs'>" . gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.") . "</span></noscript>";
            $coreReplacements['CLEARALL'] = $_clearall;
            $coreReplacements['COMPLETED'] = isset($redata['completed']) ? $redata['completed'] : '';    // global
            $coreReplacements['DATESTAMP'] = $_datestamp;
            $coreReplacements['ENDTEXT'] = $_endtext;
            $coreReplacements['EXPIRY'] = $_dateoutput;
            $coreReplacements['REGISTERERROR']  = '';
            $coreReplacements['REGISTERMESSAGE1'] = gT("You must be registered to complete this survey");
            $coreReplacements['CLOSEWINDOW']  =  "<a href='javascript:%20self.close()'>".gT("Close this window")."</a>";
            $coreReplacements['REGISTERFORM'] = '';
            $coreReplacements['GID'] = Yii::app()->getConfig('gid',
                '');// Use the gid of the question, except if we are not in question (Randomization group name)
            $coreReplacements['GOOGLE_ANALYTICS_API_KEY'] = $_googleAnalyticsAPIKey;
            $coreReplacements['GOOGLE_ANALYTICS_JAVASCRIPT'] = $_googleAnalyticsJavaScript;
            $coreReplacements['LANG'] = App()->language;
            $coreReplacements['LANGUAGECHANGER'] = isset($languagechanger) ? $languagechanger : '';    // global
            $coreReplacements['LOADERROR'] = isset($errormsg) ? $errormsg : ''; // global
            $coreReplacements['LOADFORM'] = $_loadform;
            $coreReplacements['LOADHEADING'] = gT("Load a previously saved survey");
            $coreReplacements['LOADMESSAGE'] = gT("You can load a survey that you have previously saved from this screen.") . "<br />" . gT("Type in the 'name' you used to save the survey, and the password.") . "<br />";
            $coreReplacements['NAVIGATOR'] = FrontEnd::surveyNavigator($session);
            $coreReplacements['NOSURVEYID'] = (isset($surveylist)) ? $surveylist['nosid'] : '';
            $coreReplacements['NUMBEROFQUESTIONS'] = $_totalquestionsAsked;
            $coreReplacements['PERCENTCOMPLETE'] = isset($percentcomplete) ? $percentcomplete : '';    // global
            $coreReplacements['PRIVACY'] = isset($privacy) ? $privacy : '';    // global
            $coreReplacements['PRIVACYMESSAGE'] = "<span class='privacynote'>" . gT("A Note On Privacy") . "</span><br />" . gT("This survey is anonymous.") . "<br />" . gT("The record of your survey responses does not contain any identifying information about you, unless a specific survey question explicitly asked for it.") . ' ' . gT("If you used an identifying token to access this survey, please rest assured that this token will not be stored together with your responses. It is managed in a separate database and will only be updated to indicate whether you did (or did not) complete this survey. There is no way of matching identification tokens with survey responses.");
            $coreReplacements['RESTART'] = TbHtml::link(gT("Restart this survey"),
                App()->createUrl("surveys/start", $restartParams));
            $coreReplacements['RETURNTOSURVEY'] = $_return_to_survey;
            $coreReplacements['SAVE'] = $survey->bool_allowsave ? self::doHtmlSaveAll(isset($move) ? $move : null,
                $survey) : '';
            $coreReplacements['SAVEALERT'] = $survey->bool_anonymized ? gT("To remain anonymous please use a pseudonym as your username, also an email address is not required.") : '';
            $coreReplacements['SAVEDID'] = isset($saved_id) ? $saved_id : '';   // global
            $coreReplacements['SAVEERROR'] = isset($errormsg) ? $errormsg : ''; // global - same as LOADERROR
            $coreReplacements['SAVEFORM'] = $_saveform;
            $coreReplacements['SAVEHEADING'] = gT("Save your unfinished survey");
            $coreReplacements['SAVEMESSAGE'] = gT("Enter a name and password for this survey and click save below.") . "<br />\n" . gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.") . "<br /><br />\n<span class='emailoptional'>" . gT("If you give an email address, an email containing the details will be sent to you.") . "</span><br /><br />\n" . gT("After having clicked the save button you can either close this browser window or continue filling out the survey.");
            $coreReplacements['SID'] = Yii::app()->getConfig('surveyID', '');// Allways use surveyID from config
            $coreReplacements['SITENAME'] = App()->name;
            $coreReplacements['SUBMITBUTTON'] = $_submitbutton;
            $coreReplacements['SUBMITCOMPLETE'] = "<strong>" . gT("Thank you!") . "<br /><br />" . gT("You have completed answering the questions in this survey.") . "</strong><br /><br />" . gT("Click on 'Submit' now to complete the process and save your answers.");
            $coreReplacements['SUBMITREVIEW'] = $_strreview;
            $coreReplacements['SURVEYCONTACT'] = $surveyContact;
            $coreReplacements['SURVEYDESCRIPTION'] = $survey->localizedDescription;
            $coreReplacements['SURVEYFORMAT'] = isset($surveyFormat) ? $surveyFormat : '';  // global
            $coreReplacements['SURVEYLANGUAGE'] = App()->language;
            $coreReplacements['SURVEYLIST'] = (isset($surveylist)) ? $surveylist['list'] : '';
            $coreReplacements['SURVEYLISTHEADING'] = (isset($surveylist)) ? $surveylist['listheading'] : '';
            $coreReplacements['SURVEYNAME'] = $survey->getLocalizedTitle();
            $coreReplacements['TEMPLATECSS'] = $_templatecss;
            $coreReplacements['TEMPLATEJS'] = CHtml::tag('script', ['type' => 'text/javascript', 'src' => $templateUrl . 'template.js'], '');
            $coreReplacements['TEMPLATEURL'] = $templateUrl;
            $coreReplacements['THEREAREXQUESTIONS'] = $_therearexquestions;
            $coreReplacements['TOKEN'] = !$survey->bool_anonymized ? $session->getToken() : null;// Silently replace TOKEN by empty string
            $coreReplacements['URL'] = $endLink;
            $coreReplacements['WELCOME'] = $survey->getLocalizedWelcomeText();
            $allReplacements = array_merge($coreReplacements,
                $replacements);   // so $replacements overrides core values
            /**
             * Manual replacements (always static).
             */
            $manual = ["{}" => ""];
            foreach ($allReplacements as $key => $value) {
                if (is_object($value)) {
                    $value = (string)$value;
                }
                $manual["{" . $key . "}"] = $value;
            }
            if (strpos($line, '{QID}') !== false) {
                vd($manual);
                vdd($line);
            }
            $oldLine = null;
            $newLine = $line;
            $i = 0;
            while ($newLine != $oldLine && $i < 10) {
                $oldLine = $newLine;
                $newLine = str_replace(array_keys($manual), array_values($manual), $oldLine);
                $i++;
            }
            // Now do all of the replacements - In rare cases, need to do 3 deep recursion, that that is default
            bP('replace-em');
            $result = LimeExpressionManager::ProcessString($newLine, $questionNum, $allReplacements, 3, 1, $session);
            eP('replace-em');
        }
        \Yii::trace('\ls\helpers\Replacements::templatereplace');
        eP();

        return $result;

    }


    // This function replaces field names in a text with the related values
    // (e.g. for email and template functions)
    public static function ReplaceFields($text, $fieldsarray, $bReplaceInsertans = true, $staticReplace = true)
    {

        if ($bReplaceInsertans) {
            $replacements = [];
            foreach ($fieldsarray as $key => $value) {
                $replacements[substr($key, 1, -1)] = $value;
            }
            $text = LimeExpressionManager::ProcessString($text, null, $replacements, 2, 1, $session);
        } else {
            foreach ($fieldsarray as $key => $value) {
                $text = str_replace($key, $value, $text);
            }
        }

        return $text;
    }


    /**
     * passthruReplace() takes a string and looks for {PASSTHRU:myarg} variables
     *  which it then substitutes for parameter data sent in the initial URL and stored
     *  in the session array containing responses
     *
     * @param mixed $line string - the string to iterate, and then return
     * @return string This string is returned containing the substituted responses
     *
     */
    public static function passthruReplace($line, Survey $survey)
    {
        while (strpos($line, "{PASSTHRU:") !== false) {
            $p1 = strpos($line, "{PASSTHRU:"); // startposition
            $p2 = $p1 + 10; // position of the first arg char
            $p3 = strpos($line, "}", $p1); // position of the last arg char

            $cmd = substr($line, $p1, $p3 - $p1 + 1); // extract the complete passthru like "{PASSTHRU:myarg}"
            $arg = substr($line, $p2, $p3 - $p2); // extract the arg to passthru (like "myarg")

            // lookup for the fitting arg
            $sValue = '';
            if (isset($_SESSION['survey_' . $survey->sid]['urlparams'][$arg])) {
                $sValue = urlencode($_SESSION['survey_' . $survey->sid]['urlparams'][$arg]);
            }
            $line = str_replace($cmd, $sValue, $line); // replace
        }

        return $line;
    }

    /**
     * doHtmlSaveAll return HTML part of saveall button in survey
     * @param string $move :
     * @return string
     **/
    protected static function doHtmlSaveAll($move = "", \ls\models\Survey $survey)
    {

        static $aSaveAllButtons = [];
        if (isset($aSaveAllButtons[$move])) {
            return $aSaveAllButtons[$move];
        }


        $aHtmlOptionsLoadall = array(
            'type' => 'submit',
            'id' => 'loadallbtn',
            'value' => 'loadall',
            'name' => 'loadall',
            'class' => "saveall submit button"
        );
        $aHtmlOptionsSaveall = array(
            'type' => 'submit',
            'id' => 'saveallbtn',
            'value' => 'saveall',
            'name' => 'saveall',
            'class' => "saveall submit button"
        );
        if (!$survey->bool_active) {
            $aHtmlOptionsLoadall['disabled'] = 'disabled';
            $aHtmlOptionsSaveall['disabled'] = 'disabled';
        }
        $sLoadButton = CHtml::htmlButton(gT("Load unfinished survey"), $aHtmlOptionsLoadall);
        $sSaveButton = CHtml::htmlButton(gT("Resume later"), $aHtmlOptionsSaveall);
        // Fill some test here, more clear ....
        $bTokenanswerspersistence = $survey->bool_tokenanswerspersistence;
        $bAlreadySaved = isset($_SESSION['survey_' . $survey->primaryKey]['scid']);
        $iSessionStep = isset(App()->surveySessionManager->current) ? App()->surveySessionManager->current->step : 0;
        $iSessionMaxStep = isset(App()->surveySessionManager->current) ? App()->surveySessionManager->current->maxStep : 0;

        $sSaveAllButtons = "";
        // Find out if the user has any saved data
        if ($survey->format == Survey::FORMAT_ALL_IN_ONE) {
            if (!$bTokenanswerspersistence && !$bAlreadySaved) {
                $sSaveAllButtons .= $sLoadButton;
            }
            $sSaveAllButtons .= CHtml::htmlButton(gT("Resume later"), $aHtmlOptionsSaveall);
        } elseif (!$iSessionStep) //Welcome page, show load (but not save)
        {
            if (!$bTokenanswerspersistence && !$bAlreadySaved) {
                $sSaveAllButtons .= $sLoadButton;
            }
            if (!$survey->bool_showwelcome) {
                $sSaveAllButtons .= $sSaveButton;
            }
        } elseif ($iSessionMaxStep == 1 && !$survey->bool_showwelcome)//First page, show LOAD and SAVE
        {
            if (!$bTokenanswerspersistence && !$bAlreadySaved) {
                $sSaveAllButtons .= $sLoadButton;
            }
            $sSaveAllButtons .= $sSaveButton;
        } elseif ($move != "movelast") // Not on last page or submited survey
        {
            $sSaveAllButtons .= $sSaveButton;
        }
        $aSaveAllButtons[$move] = $sSaveAllButtons;

        return $aSaveAllButtons[$move];
    }

}