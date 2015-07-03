<?php
namespace ls\controllers;
use Token;
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

    public function actionResponses($id, $surveyId) {
        $this->layout = 'bare';
        $survey = \Survey::model()->findByPk($surveyId);
        $token = \Token::model($survey->sid)->findByPk($id);
        $criteria = new \CDbCriteria();
        $criteria->order = 'submitdate DESC';
        $criteria->addColumnCondition(['token' => $token->token]);
        $dataProvider = new \CActiveDataProvider(\Response::model($survey->sid), [
            'criteria' => $criteria,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => false
        ]);
        $this->menus['survey'] = $survey;

        if ($dataProvider->totalItemCount > 0) {
            $this->render('responses', [
                'dataProvider' => $dataProvider,
                'survey' => $survey,
//                'wrapper' => 'col-md-10 col-md-offset-2'
            ]);
        } else {
            echo "No responses for this token";
        }
    }
    public function actionCreate($surveyId)
    {
        $survey = \Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;
        if (!$survey->bool_usetokens) {
            throw new \CHttpException(412, "The survey you selected does not have tokens enabled");
        }

        $token = \Token::create($survey->sid);
        if (App()->request->isPostRequest) {
            $token->setAttributes(App()->request->getPost(get_class($token)));

            // Validate & safe.
            if ($token->save()) {
                // On success.
                App()->user->setFlash('success', 'Token created');
                $this->redirect(['tokens/index', 'surveyId' => $survey->sid]);
            }
        }
        $this->render('create', ['token' => $token, 'survey' => $survey]);
    }

    /**
     * @param $surveyId
     * @param $id
     */
    public function actionView($surveyId, $id) {
        $survey = \Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;
        $token = $this->loadModel($id, $surveyId);

        return $this->renderText($this->widget(\WhDetailView::class, [
            'data' => $token
        ], true));
    }

    /**
     * @param $surveyId
     * @param $id
     * @throws \CHttpException
     */
    public function actionUpdate($surveyId, $id)
    {
        /**
         * @todo Add permission check.
         */
        $survey = \Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;

        $token = $this->loadModel($id, $surveyId);

        if (App()->request->isPostRequest) {
            $token->setAttributes(App()->request->getPost(get_class($token)));

            // Validate & safe.
            if ($token->save()) {
                // On success.
                App()->user->setFlash('success', 'Token updated');
                $this->redirect(['tokens/index', 'surveyId' => $survey->sid]);
            }
        }
        $this->render('create', ['token' => $token, 'survey' => $survey]);
    }

    public function actionIndex($surveyId)
    {
        /**
         * @todo Add permission check.
         */
        $survey = \Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;
        if (!$survey->bool_usetokens) {
            throw new \CHttpException(412, "The survey you selected does not have tokens enabled.");
        }

        if (!\Token::valid($survey->sid)) {
            \Token::createTable($survey->sid);
        }

        $dataProvider = new \CActiveDataProvider(\Token::model($survey->sid), [
            'pagination' => [
                'pageSize' => 50
            ]
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider, 'survey' => $survey]);
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
        elseif (SendEmailMessage($aMail['message'], $aMail['subject'], $sTo, $sFrom, App()->name,$useHtmlEmail,$sBounce))
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

    /**
     * @param type $id
     * @param null $surveyId
     * @return Token
     * @throws \CHttpException
     */
    protected function loadModel($id, $surveyId = null)
    {
        if (!isset($surveyId)) {
            throw new \InvalidArgumentException("SurveyID is required when loading token.");
        } elseif (!\Token::valid($surveyId)) {
            throw new \CHttpException(404, gT("Token table not found"));
        } elseif (null === $result = \Token::model($surveyId)->findByPk($id)) {
            throw new \CHttpException(404, gT("Token not found"));
        }
        return $result;
    }


    /**
     * @param int $id
     * @param int  $surveyId
     * @todo Add permission check.
     */
    public function actionDelete($id, $surveyId) {
        if ($this->loadModel($id, $surveyId)->delete()) {
            App()->user->setFlash('success', gT("Token deleted"));
        } else {
            App()->user->setFlash('success', gT("Could not delete token"));
        }
        $this->redirect(['tokens/index', 'surveyId' => $surveyId]);
    }

}