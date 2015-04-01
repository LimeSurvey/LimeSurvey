<?php
namespace ls\controllers;
use Yii;
use ls\pluginmanager\PluginEvent;
class TokensController extends Controller
{
    public function actions() {
        return [
            'captcha' => [
                'class' => \CCaptchaAction::class,
                'testLimit' => 1
            ]
        ];
    }

    public function actionRegister($surveyId)
    {
        $this->layout = 'minimal';
        if (null === $survey = \Survey::model()->findByPk($surveyId)) {
            throw new \CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } elseif (!$survey->bool_allowregister) {
            throw new \CHttpException(403, "The survey in which you are trying to register does not allow registration. It may have been updated or the link you were given is outdated or incorrect.");
        }
        $token = \Token::create($survey->sid, 'register');
        if (App()->request->isPostRequest) {
            $token->setAttributes(App()->request->getPost(get_class($token)));
            $token->generateToken();
            if ($token->save()) {
                $this->renderText($this->sendRegistrationEmail($token));
                return;
            }
        }
        $this->render('register', ['survey' => $survey, 'token' => $token]);
    }

    /**
     * Send the register email with $_POST value
     * @param $iSurveyId Survey Id to register
     * @return boolean : if email is set to sent (before SMTP problem)
     */
    protected function sendRegistrationEmail(\Token $token){

        $sLanguage=App()->language;
        $iSurveyId = $token->surveyId;
        $aSurveyInfo=getSurveyInfo($iSurveyId ,$sLanguage);
        $aMail['subject']=$aSurveyInfo['email_register_subj'];
        $aMail['message']=$aSurveyInfo['email_register'];
        $aReplacementFields=array();
        $aReplacementFields["{ADMINNAME}"]=$aSurveyInfo['adminname'];
        $aReplacementFields["{ADMINEMAIL}"]=$aSurveyInfo['adminemail'];
        $aReplacementFields["{SURVEYNAME}"]=$aSurveyInfo['name'];
        $aReplacementFields["{SURVEYDESCRIPTION}"]=$aSurveyInfo['description'];
        $aReplacementFields["{EXPIRY}"]=$aSurveyInfo["expiry"];
        foreach($token->attributes as $attribute=>$value){
            $aReplacementFields["{".strtoupper($attribute)."}"]=$value;
        }
        $useHtmlEmail = (getEmailFormat($iSurveyId) == 'html');
        $aMail['subject']=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$aMail['subject']);
        $aMail['message']=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$aMail['message']);
        $aReplacementFields["{SURVEYURL}"] = App()->createAbsoluteUrl("/survey/index/sid/{$iSurveyId}",array('lang'=>$sLanguage,'token'=> $token->token));
        $aReplacementFields["{OPTOUTURL}"] = App()->createAbsoluteUrl("/optout/tokens/surveyid/{$iSurveyId}",array('langcode'=>$sLanguage,'token'=> $token->token));
        $aReplacementFields["{OPTINURL}"] = App()->createAbsoluteUrl("/optin/tokens/surveyid/{$iSurveyId}",array('langcode'=>$sLanguage,'token'=> $token->token));
        foreach(array('OPTOUT', 'OPTIN', 'SURVEY') as $key)
        {
            $url = $aReplacementFields["{{$key}URL}"];
            if ($useHtmlEmail)
                $aReplacementFields["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
            $aMail['subject'] = str_replace("@@{$key}URL@@", $url, $aMail['subject']);
            $aMail['message'] = str_replace("@@{$key}URL@@", $url, $aMail['message']);
        }
        // Replace the fields
        $aMail['subject']=ReplaceFields($aMail['subject'], $aReplacementFields);
        $aMail['message']=ReplaceFields($aMail['message'], $aReplacementFields);
        $sFrom = "{$aSurveyInfo['adminname']} <{$aSurveyInfo['adminemail']}>";
        $sBounce=getBounceEmail($iSurveyId);
        $sTo=$token->email;
        // Plugin event for email handling (Same than admin token but with register type)
        $event = new PluginEvent('beforeTokenEmail');
        $event->set('type', 'register');
        $event->set('subject', $aMail['subject']);
        $event->set('to', $sTo);
        $event->set('body', $aMail['message']);
        $event->set('from', $sFrom);
        $event->set('bounce',$sBounce );
        $event->set('token', $token->attributes);
        $aMail['subject'] = $event->get('subject');
        $aMail['message'] = $event->get('body');
        $sTo = $event->get('to');
        $sFrom = $event->get('from');
        if ($event->get('send', true) == false)
        {
            $message = $event->get('message', '');
            if($event->get('error')==null){// mimic token system, set send to today
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
                $token->sent = $today;
                $token->save();
            }
        }
        elseif (SendEmailMessage($aMail['message'], $aMail['subject'], $sTo, $sFrom, Yii::app()->getConfig('sitename'),$useHtmlEmail,$sBounce))
        {
            // TLR change to put date into sent
            $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
            $token->sent = $today;
            $token->save();
            $message = "<div id='wrapper' class='message tokenmessage'>"
                . "<p>".gT("Thank you for registering to participate in this survey.")."</p>\n"
                . "<p>{$this->sMailMessage}</p>\n"
                . "<p>".sprintf(gT("Survey administrator %s (%s)"),$aSurveyInfo['adminname'],$aSurveyInfo['adminemail'])."</p>"
                . "</div>\n";
        }
        else
        {
            $message = "<div id='wrapper' class='message tokenmessage'>"
                . "<p>".gT("Thank you for registering to participate in this survey.")."</p>\n"
                . "<p>".gT("You are registred but an error happen when trying to send the email, please contact the survey administrator.")."</p>\n"
                . "<p>".sprintf(gT("Survey administrator %s (%s)"),$aSurveyInfo['adminname'],$aSurveyInfo['adminemail'])."</p>"
                . "</div>\n";
        }
        return $message;
    }
}