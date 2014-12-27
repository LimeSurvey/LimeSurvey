<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Offer some way to validate Expression in survey
 * 
 * @copyright 2014 The LimeSurvey Project Team
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @todo : Add quota, add any expression
 */
class ExpressionValidate extends Survey_Common_Action {

    public $layout = 'popup';

    public function index()
    {
        throw new CHttpException(400);
    }

    /**
    * Check the Expression in email
    * @param $iSurveyId : the survey id : can be sid/surveyid url GET parameters
    * @param $lang : the mail language
    * 
    * @author Denis Chenu
    * @version 1.0
    */
    public function email($iSurveyId,$lang)
    {
        if(!Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update'))
            throw new CHttpException(401,"401 Unauthorized");
        $sType=Yii::app()->request->getQuery('type');
        $sLang=$lang;
        $LEM =LimeExpressionManager::singleton();

        $LEM::SetDirtyFlag();// Not sure it's needed
        $LEM::SetPreviewMode('logic');
        $aSurveyInfo=getSurveyInfo($iSurveyId,$sLang);
        $aSurveyOptions = array(
            'hyperlinkSyntaxHighlighting'=>true,
        );
        $LEM::StartSurvey($iSurveyId, 'survey', $aSurveyOptions);// QCODE are replaced : see #9424 : to be moved only for after survey email after fixed
        $aTypeAttributes=array(
            'invitation'=>array(
                'subject'=>array(
                    'attribute'=>'surveyls_email_invite_subj',
                    'title'=>gt('Invitation email subject'),
                ),
                'message'=>array(
                    'attribute'=>'surveyls_email_invite',
                    'title'=>gt('Invitation email body'),
                ),
            ),
            'reminder'=>array(
                'subject'=>array(
                    'attribute'=>'surveyls_email_remind_subj',
                    'title'=>gt('Reminder email subject'),
                ),
                'message'=>array(
                    'attribute'=>'surveyls_email_remind',
                    'title'=>gt('Reminder email body'),
                ),
            ),
            'confirmation'=>array(
                'subject'=>array(
                    'attribute'=>'surveyls_email_confirm_subj',
                    'title'=>gt('Confirmation email subject'),
                ),
                'message'=>array(
                    'attribute'=>'surveyls_email_confirm',
                    'title'=>gt('Confirmation email body'),
                ),
            ),
            'registration'=>array(
                'subject'=>array(
                    'attribute'=>'surveyls_email_register_subj',
                    'title'=>gt('Registration email subject'),
                ),
                'message'=>array(
                    'attribute'=>'surveyls_email_register',
                    'title'=>gt('Registration email body'),
                ),
            ),
            'admin_notification'=>array(
                'subject'=>array(
                    'attribute'=>'email_admin_notification_subj',
                    'title'=>gt('Basic admin notification subject'),
                ),
                'message'=>array(
                    'attribute'=>'email_admin_notification',
                    'title'=>gt('Basic admin notification body'),
                ),
            ),
            'admin_detailed_notification'=>array(
                'subject'=>array(
                    'attribute'=>'email_admin_responses_subj',
                    'title'=>gt('Detailed admin notification subject'),
                ),
                'message'=>array(
                    'attribute'=>'email_admin_responses',
                    'title'=>gt('Detailed admin notification body'),
                ),
            ),
        );
        // Replaced before email edit
        $aReplacement=array(
            'ADMINNAME'=> $aSurveyInfo['admin'],
            'ADMINEMAIL'=> $aSurveyInfo['adminemail'],
        );
        // Not needed : templatereplace do the job : but this can/must be fixed for invitaton/reminder/registration (#9424)
        $aReplacement["SURVEYNAME"] = gT("Name of the survey");
        $aReplacement["SURVEYDESCRIPTION"] =  gT("Description of the survey");
        // Replaced when sending email with Survey 
        $aAttributes = getTokenFieldsAndNames($iSurveyId,true);
        $aReplacement["TOKEN"] = gt("Token code for this participant");
        $aReplacement["TOKEN:EMAIL"] = gt("Email from the token");
        $aReplacement["TOKEN:FIRSTNAME"] = gt("First name from token");
        $aReplacement["TOKEN:LASTNAME"] = gt("Last name from token");
        $aReplacement["TOKEN:TOKEN"] = gt("Token code for this participant");
        $aReplacement["TOKEN:LANGUAGE"] = gt("language of token");
        foreach ($aAttributes as $sAttribute=>$aAttribute)
        {
            $aReplacement['TOKEN:'.strtoupper($sAttribute).'']=sprintf(gT("Token attribute: %s"), $aAttribute['description']);
        }

        switch ($sType)
        {
            case 'invitation' :
            case 'reminder' :
            case 'registration' :
                // Replaced when sending email (registration too ?)
                $aReplacement["EMAIL"] = gt("Email from the token");
                $aReplacement["FIRSTNAME"] = gt("First name from token");
                $aReplacement["LASTNAME"] = gt("Last name from token");
                $aReplacement["LANGUAGE"] = gt("language of token");
                $aReplacement["OPTOUTURL"] = gt("URL for a respondent to opt-out of this survey");
                $aReplacement["OPTINURL"] = gt("URL for a respondent to opt-in to this survey");
                $aReplacement["SURVEYURL"] = gt("URL of the survey");
                foreach ($aAttributes as $sAttribute=>$aAttribute)
                {
                    $aReplacement['' . strtoupper($sAttribute) . ''] = sprintf(gT("Token attribute: %s"), $aAttribute['description']);
                }
                break;
            case 'confirmation' :
                $aReplacement["EMAIL"] = gt("Email from the token");
                $aReplacement["FIRSTNAME"] = gt("First name from token");
                $aReplacement["LASTNAME"] = gt("Last name from token");
                $aReplacement["SURVEYURL"] = gt("URL of the survey");
                foreach ($aAttributes as $sAttribute=>$aAttribute)
                {
                    $aReplacement['' . strtoupper($sAttribute) . ''] = sprintf(gT("Token attribute: %s"), $aAttribute['description']);
                }
                // $moveResult = LimeExpressionManager::NavigateForwards(); // Seems OK without, nut need $LEM::StartSurvey
                break;
            case 'admin_notification' :
            case 'admin_detailed_notification' :
                $aReplacement["RELOADURL"] = gT("Reload URL");
                $aReplacement["VIEWRESPONSEURL"] = gT("View response URL");
                $aReplacement["EDITRESPONSEURL"] = gT("Edit response URL");
                $aReplacement["STATISTICSURL"] = gT("Statistics URL");
                $aReplacement["ANSWERTABLE"] = gT("Answers from this response");
                // $moveResult = LimeExpressionManager::NavigateForwards(); // Seems OK without, nut need $LEM::StartSurvey
                break;
            default:
                throw new CHttpException(400,gt('Invalid type.'));
                break;
        }

        $aData=array();
        $aReData=array(
            'thissurvey'=>getSurveyInfo($iSurveyId,$sLang),
        );
        //$oSurveyLanguage=SurveyLanguageSetting::model()->find("surveyls_survey_id=:sid and surveyls_language=:language",array(":sid"=>$iSurveyId,":language"=>$sLang));
        $aExpressions=array();
        foreach($aTypeAttributes[$sType] as $key=>$aAttribute)
        {
            $sAttribute=$aAttribute['attribute'];
            // Email send do : templatereplace + ReplaceField to the Templatereplace done : we need 2 in one
            // $LEM::ProcessString($oSurveyLanguage->$sAttribute,null,$aReplacement,false,1,1,false,false,true); // This way : ProcessString don't replace coreReplacements
            templatereplace($aSurveyInfo[$sAttribute], $aReplacement,$aReData,'ExpressionValidate::email',false,null,array(),true);
            $aExpressions[$key]=array(
                'title'=>$aAttribute['title'],
                'expression'=> $LEM::GetLastPrettyPrintExpression(),
            );
        }
        $aData['aExpressions']=$aExpressions;
        $this->getController()->layout=$this->layout;
        $this->getController()->pageTitle=sprintf("Validate expression in email : %s",$sType);

        $this->getController()->render("/admin/expressions/email", $aData);
    }
}
