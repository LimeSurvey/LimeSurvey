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
*/


/**
 * Class SurveyLight
 *
 * This is a light version of the normal Survey model, without the afterFindSurvey event.
 * It was created because for mass queries, the afterFindSurvey event is most times not needed and it slows down the process.
 *
 * @property integer $sid Survey ID
 * @property integer $owner_id
 * @property integer $gsid survey group id, from which this survey belongs to and inherits values from when set to 'I'
 * @property string $admin Survey Admin's full name
 * @property string $active Whether survey is acive or not (Y/N)
 * @property string $expires Expiry date (YYYY-MM-DD hh:mm:ss)
 * @property string $startdate Survey Start date (YYYY-MM-DD hh:mm:ss)
 * @property string $adminemail Survey administrator email address
 * @property string $anonymized Whether survey is anonymized or not (Y/N)
 * @property string $format A : All in one, G : Group by group, Q : question by question, I : inherit value from survey group
 * @property string $savetimings Whether survey timings are saved (Y/N)
 * @property string $template Template name
 * @property string $language Survey base language
 * @property string $additional_languages Survey additional languages delimited by space ' '
 * @property string $datestamp Whether respondents' datestamps will be saved (Y/N)
 * @property string $usecookie Are cookies used to prevent repeated participation (Y/N)
 * @property string $allowregister Allow public registration (Y/N)
 * @property string $allowsave Is participant allowed save and resume later (Y/N)
 * @property integer $autonumber_start
 * @property integer $tokenlength Token length: MIN:5 MAX:36
 * @property string $autoredirect Automatically load URL when survey complete: (Y/N)
 * @property string $allowprev Allow backwards navigation (Y/N)
 * @property string $printanswers Participants may print answers: (Y/N)
 * @property string $ipaddr Whether Participants IP address will be saved: (Y/N)
 * @property string $ipanonymize Whether id addresses should be anonymized (Y/N)
 * @property string $refurl Save referrer URL: (Y/N)
 * @property string $datecreated Date survey was created (YYYY-MM-DD hh:mm:ss)
 * @property string $publicstatistics Public statistics: (Y/N)
 * @property string $publicgraphs Show graphs in public statistics: (Y/N)
 * @property string $listpublic List survey publicly: (Y/N)
 * @property string $htmlemail Use HTML format for token emails: (Y/N)
 * @property string $sendconfirmation Send confirmation emails:(Y/N)
 * @property string $tokenanswerspersistence Enable token-based response persistence: (Y/N)
 * @property string $assessments Enable assessment mode: (Y/N)
 * @property string $usecaptcha
 * @property string $usetokens
 * @property string $bounce_email Bounce email address
 * @property string $attributedescriptions
 * @property string $emailresponseto e-mail address to send detailed admin notification email to
 * @property string $emailnotificationto Email address to send basic admin notification email to
 * @property string $showxquestions Show "There are X questions in this survey": (Y/N)
 * @property string $showgroupinfo Show group name and/or group description: (Y/N)
 * @property string $shownoanswer Show "No answer": (Y/N)
 * @property string $showqnumcode Show question number and/or code: (Y/N)
 * @property integer $bouncetime
 * @property string $bounceprocessing
 * @property string $bounceaccounttype
 * @property string $bounceaccounthost
 * @property string $bounceaccountpass
 * @property string $bounceaccountencryption
 * @property string $bounceaccountuser
 * @property string $showwelcome Show welcome screen: (Y/N)
 * @property string $showprogress how progress bar: (Y/N)
 * @property integer $questionindex Show question index / allow jumping (0: diabled; 1: Incremental; 2: Full)
 * @property integer $navigationdelay Navigation delay (seconds) (It shows the number of seconds before the previous,
 * next, and submit buttons are enabled. If none is specified, the option will use the default value, which is "0" (seconds))
 * @property string $nokeyboard Show on-screen keyboard: (Y/N)
 * @property string $alloweditaftercompletion Allow multiple responses or update responses with one token: (Y/N)
 * @property string $googleanalyticsstyle Google Analytics style: (0: off; 1:Default; 2:Survey-SID/Group)
 * @property string $googleanalyticsapikey Google Analytics Tracking ID
 * @property string $tokenencryptionoptions Token encryption options
 *
 * @property Permission[] $permissions
 * @property SurveyLanguageSetting[] $languagesettings
 * @property User $owner
 * @property QuestionGroup[] $groups
 * @property Quota[] $quotas
 * @property Question[] $allQuestions All survey questions including subquestions
 * @property Question[] $baseQuestions Survey questions NOT including subquestions
 * @property Question[] $quotableQuestions
 *
 * @property integer $countFullAnswers Full-answers count
 * @property integer $countPartialAnswers Full-answers count
 * @property integer $countTotalAnswers Total-answers count
 * @property integer $groupsCount Number of groups in a survey (in base language)
 * @property array $surveyinfo
 * @property SurveyLanguageSetting $currentLanguageSettings Survey languagesettings in currently active language
 * @property string[] $allLanguages
 * @property string[] $additionalLanguages Additional survey languages
 * @property array $tokenAttributes Additional token attribute names
 * @property string $creationDate Creation date formatted according to user format
 * @property string $startDateFormatted Start date formatted according to user format
 * @property string $expiryDateFormatted Expiry date formatted according to user format
 * @property string $tokensTableName Name of survey tokens table
 * @property string $responsesTableName Name of survey resonses table
 * @property string $timingsTableName Name of survey timings table
 * @property boolean $hasTokensTable Whether survey has a tokens table or not
 * @property boolean $hasResponsesTable Wheteher the survey responses (data) table exists in DB
 * @property boolean $hasTimingsTable Wheteher the survey timings table exists in DB
 * @property string $googleanalyticsapikeysetting Returns the value for the SurveyEdit GoogleAnalytics API-Key UseGlobal Setting
 * @property integer $countTotalQuestions Count of questions (in that language, without subquestions)
 * @property integer $countInputQuestions Count of questions that need input (skipping text-display etc.)
 * @property integer $countNoInputQuestions Count of questions that DO NOT need input (skipping text-display etc.)
 *
 * All Y/N columns in the model can be accessed as boolean values:
 * @property bool $isActive Whether Survey is active
 * @property bool $isAnonymized Whether survey is anonymized or not
 * @property bool $isSaveTimings Whether survey timings are saved
 * @property bool $isDateStamp Whether respondents' datestamps will be saved
 * @property bool $isUseCookie Are cookies used to prevent repeated participation
 * @property bool $isAllowRegister Allow public registration
 * @property bool $isAllowSave Is participant allowed save and resume later
 * @property bool $isAutoRedirect Automatically load URL when survey complete
 * @property bool $isAllowPrev Allow backwards navigation
 * @property bool $isPrintAnswers Participants may print answers
 * @property bool $isIpAddr Whether Participants IP address will be saved
 * @property bool $isIpAnonymize Whether Participants IP address will be saved
 * @property bool $isRefUrl Save referrer URL
 * @property bool $isPublicStatistics Public statistics
 * @property bool $isPublicGraphs Show graphs in public statistics
 * @property bool $isListPublic List survey publicly
 * @property bool $isHtmlEmail Use HTML format for token emails
 * @property bool $isSendConfirmation Send confirmation emails
 * @property bool $isTokenAnswersPersistence Enable token-based response persistence
 * @property bool $isAssessments Enable assessment mode
 * @property bool $isShowXQuestions Show "There are X questions in this survey"
 * @property bool $isShowGroupInfo Show group name and/or group description
 * @property bool $isShowNoAnswer Show "No answer"
 * @property bool $isShowQnumCode Show question number and/or code
 * @property bool $isShowWelcome Show welcome screen
 * @property bool $isShowProgress how progress bar
 * @property bool $showsurveypolicynotice Show the security notice
 * @property bool $isNoKeyboard Show on-screen keyboard
 * @property bool $isAllowEditAfterCompletion Allow multiple responses or update responses with one token
 * @property SurveyLanguageSetting $defaultlanguage
 * @property boolean $isDateExpired Whether survey is expired depending on the current time and survey configuration status
 * @method mixed active()
 */
class SurveyLight extends Survey
{
    /**
     * This event is left empty on purpose because we don't  in this SurveyLight
     * model we don't want the inherited costly afterFindSurvey event
     */
    public function afterFindSurvey()
    {
    }
}
