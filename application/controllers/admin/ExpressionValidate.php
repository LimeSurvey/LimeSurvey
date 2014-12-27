<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Offer some way to validate Expression in survey
 * 
 * @copyright 2014 The LimeSurvey Project Team
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @todo : Add any expression
 * @version : 1.1
 */
class ExpressionValidate extends Survey_Common_Action {

    /**
    * @var string : Default layout is popup : less header, no footer
    */
    public $layout = 'popup';

    /**
    * @var integer : The survey id to start to fill know vars
    */
    private $iSurveyId;
    /**
    * @var string : The language for the survey
    */
    private $sLang;

    public function index()
    {
        throw new CHttpException(400);
    }

    /**
    * Check the Expression in quota
    * @param integer $iSurveyId : the survey id : can be sid/surveyid url GET parameters
    * @param integer $quota : the quota id
    * @param string $lang : the survey language, optional : if not set get all language of survey
    * 
    * @author Denis Chenu
    * @version 1.0
    */
    public function quota($iSurveyId,$quota,$lang=null)
    {
        if(!Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','read'))
            throw new CHttpException(401,"401 Unauthorized");
        $iQuotaId=$quota;
        if(is_string($lang))
        {
            $oValidator= new LSYii_Validators;
            $aLangs=array($oValidator->languageFilter($lang));
        }
        else
        {
            $aLangs = Survey::model()->findByPk($iSurveyId)->getAllLanguages();
        }
        $aExpressions=array();
        $this->iSurveyId=$iSurveyId;
        foreach($aLangs as $sLang)
        {
            $oQuotaLanguageSetting=QuotaLanguageSetting::model()->find("quotals_quota_id =:quota_id and quotals_language=:language",array(':quota_id'=>$iQuotaId,':language'=>$sLang));
            // We don't need to go to step since new feature #8823, maybe need to be fixed ?
            if($oQuotaLanguageSetting)
            {
                $this->sLang=$sLang;
                $aExpressions['name_'.$sLang]=array(
                    'title'=>sprintf("Quota name (%s)",$sLang),
                    'expression'=> $this->getHtmlExpression($oQuotaLanguageSetting->quotals_name,array(),__METHOD__), 
                );
                $aExpressions['message_'.$sLang]=array(
                    'title'=>sprintf("Quota message (%s)",$sLang),
                    'expression'=> $this->getHtmlExpression($oQuotaLanguageSetting->quotals_message,array(),__METHOD__), 
                );
                $aExpressions['url_'.$sLang]=array(
                    'title'=>sprintf("URL (%s)",$sLang),
                    'expression'=> $this->getHtmlExpression($oQuotaLanguageSetting->quotals_url,array(),__METHOD__), 
                );
                $aExpressions['urldescrip_'.$sLang]=array(
                    'title'=>sprintf("URL description (%s)",$sLang),
                    'expression'=> $this->getHtmlExpression($oQuotaLanguageSetting->quotals_urldescrip,array(),__METHOD__), 
                );
            }
        }
        $aData=array(
            'aExpressions'=>$aExpressions,
        );
        $this->getController()->layout=$this->layout;
        $this->getController()->pageTitle=gt("Validate quota");

        $this->getController()->render("/admin/expressions/validationList", $aData);
    }
    /**
    * Check the Expression in email
    * @param integer $iSurveyId : the survey id : can be sid/surveyid url GET parameters
    * @param string $lang : the mail language
    * 
    * @author Denis Chenu
    * @version 1.1
    */
    public function email($iSurveyId,$lang)
    {
        if(!Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'read'))
            throw new CHttpException(401,"401 Unauthorized");
        $sType=Yii::app()->request->getQuery('type');
        $this->sLang=$sLang=$lang;
        $this->iSurveyId=$iSurveyId; // This start the survey before Expression : is this allways needed ?

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
        $aSurveyInfo=getSurveyInfo($iSurveyId,$sLang);
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
        //$oSurveyLanguage=SurveyLanguageSetting::model()->find("surveyls_survey_id=:sid and surveyls_language=:language",array(":sid"=>$iSurveyId,":language"=>$sLang));
        $aExpressions=array();
        foreach($aTypeAttributes[$sType] as $key=>$aAttribute)
        {
            $sAttribute=$aAttribute['attribute'];
            // Email send do : templatereplace + ReplaceField to the Templatereplace done : we need 2 in one
            // $LEM::ProcessString($oSurveyLanguage->$sAttribute,null,$aReplacement,false,1,1,false,false,true); // This way : ProcessString don't replace coreReplacements
            $aExpressions[$key]=array(
                'title'=>$aAttribute['title'],
                'expression'=> $this->getHtmlExpression($aSurveyInfo[$sAttribute],$aReplacement,__METHOD__),
            );
        }
        $aData['aExpressions']=$aExpressions;
        $this->getController()->layout=$this->layout;
        $this->getController()->pageTitle=sprintf(gt("Validate expression in email : %s"),$sType);

        $this->getController()->render("/admin/expressions/validationList", $aData);
    }

    /**
    * Get the complete HTML from a string
    * @param string $sExpression : the string to parse
    * @param array $aReplacement : optionnal array of replacemement
    * @param string $sDebugSource : optionnal debug source (for templatereplace)
    * @uses ExpressionValidate::$iSurveyId
    * @uses ExpressionValidate::$sLang
    *
    * @author Denis Chenu
    * @version 1.0
    */
    private function getHtmlExpression($sExpression,$aReplacement=array(),$sDebugSource=__CLASS__)
    {
        $LEM =LimeExpressionManager::singleton();
        $LEM::SetDirtyFlag();// Not sure it's needed
        $LEM::SetPreviewMode('logic');

        $aReData=array();
        if($this->iSurveyId)
        {
            $LEM::StartSurvey($this->iSurveyId, 'survey', array('hyperlinkSyntaxHighlighting'=>true));// replace QCODE
            $aReData['thissurvey']=getSurveyInfo($this->iSurveyId,$this->sLang);
        }
        // TODO : Find error in class name, style etc ....
        // need: templatereplace without any filter and find if there are error but $bHaveError=$LEM->em->HasErrors() is Private
        $oFilter = new CHtmlPurifier();
        templatereplace($oFilter->purify(viewHelper::filterScript($sExpression)), $aReplacement,$aReData,$sDebugSource,false,null,array(),true);

        return $LEM::GetLastPrettyPrintExpression();
    }
}
