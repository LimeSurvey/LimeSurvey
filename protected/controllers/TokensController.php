<?php
namespace ls\controllers;
use ls\models\Permission;
use ls\models\Survey;
use ls\models\Token;
use Yii;
use ls\pluginmanager\PluginEvent;
class TokensController extends Controller
{
    public function accessRules()
    {
        return array_merge([
            ['allow',
                'roles' => ['tokens' => [
                    'entity_id' => App()->request->getParam('surveyId'),
                    'entity' => 'survey',
                    'crud' => 'update'
                ]],
                'actions' => ['generate', 'update']
            ],
        ], parent::accessRules());

    }


    public function actions()
    {
        return [
            'captcha' => [
                'class' => \CCaptchaAction::class,
                'testLimit' => 1
            ]
        ];
    }

    public function actionResponses($id, $surveyId) {
        $this->layout = 'bare';
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
        $token = \ls\models\Token::model($survey->sid)->findByPk($id);
        $criteria = new \CDbCriteria();
        $criteria->order = 'submitdate DESC';
        $criteria->addColumnCondition(['token' => $token->token]);
        if (\ls\models\Response::valid($survey->sid)) {
            $dataProvider = new \CActiveDataProvider(\ls\models\Response::model($survey->sid), [
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
            }
            return;
        }

        echo "No responses for this token";
    }

    public function actionCreate($surveyId)
    {
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;
        if (!$survey->bool_usetokens) {
            throw new \CHttpException(412, "The survey you selected does not have tokens enabled");
        }

        $token = \ls\models\Token::create($survey->sid);
        if (App()->request->isPostRequest) {
            $token->setAttributes(App()->request->getPost(\CHtml::modelName($token)));

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
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
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
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;

        $token = $this->loadModel($id, $surveyId);

        if (App()->request->isPutRequest) {
            $token->setAttributes(App()->request->getPost(\CHtml::modelName($token)));

            // Validate & safe.
            if ($token->save()) {
                // On success.
                App()->user->setFlash('success', gT('Token updated'));
//                $this->redirect(['tokens/index', 'surveyId' => $survey->sid]);
            }
        }
        $this->render('update', ['token' => $token, 'survey' => $survey]);
    }

    public function actionIndex($surveyId)
    {
        /**
         * @todo Add permission check.
         */
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;
        if (!$survey->bool_usetokens) {
            throw new \CHttpException(412, "The survey you selected does not have tokens enabled.");
        }

        if (!\ls\models\Token::valid($survey->sid)) {
            \ls\models\Token::createTable($survey->sid);
        }

        $dataProvider = new \CActiveDataProvider(\ls\models\Token::model($survey->sid), [
            'pagination' => [
                'pageSize' => 20
            ]
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider, 'survey' => $survey]);
    }
    public function actionRegister($surveyId)
    {
        $this->layout = 'minimal';
        if (null === $survey = \ls\models\Survey::model()->findByPk($surveyId)) {
            throw new \CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } elseif (!$survey->bool_allowregister) {
            throw new \CHttpException(403, "The survey in which you are trying to register does not allow registration. It may have been updated or the link you were given is outdated or incorrect.");
        }
        $token = \ls\models\Token::create($survey->sid, 'register');
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
    protected function sendRegistrationEmail(\ls\models\Token $token){

        $sLanguage=App()->language;
        $iSurveyId = $token->surveyId;
        $aSurveyInfo=getSurveyInfo($iSurveyId ,$sLanguage);
        $aMail['subject']=$aSurveyInfo['email_register_subj'];
        $aMail['message']=$aSurveyInfo['email_register'];
        $aReplacementFields= [];
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
        $aReplacementFields["{SURVEYURL}"] = App()->createAbsoluteUrl("/survey/index/sid/{$iSurveyId}",
            ['lang'=>$sLanguage,'token'=> $token->token]);
        $aReplacementFields["{OPTOUTURL}"] = App()->createAbsoluteUrl("/optout/tokens/surveyid/{$iSurveyId}",
            ['langcode'=>$sLanguage,'token'=> $token->token]);
        $aReplacementFields["{OPTINURL}"] = App()->createAbsoluteUrl("/optin/tokens/surveyid/{$iSurveyId}",
            ['langcode'=>$sLanguage,'token'=> $token->token]);
        foreach(['OPTOUT', 'OPTIN', 'SURVEY'] as $key)
        {
            $url = $aReplacementFields["{{$key}URL}"];
            if ($useHtmlEmail)
                $aReplacementFields["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
            $aMail['subject'] = str_replace("@@{$key}URL@@", $url, $aMail['subject']);
            $aMail['message'] = str_replace("@@{$key}URL@@", $url, $aMail['message']);
        }
        // Replace the fields
        $aMail['subject']=\ls\helpers\Replacements::ReplaceFields($aMail['subject'], $aReplacementFields);
        $aMail['message']=\ls\helpers\Replacements::ReplaceFields($aMail['message'], $aReplacementFields);
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
     * @return \ls\models\Token
     * @throws \CHttpException
     */
    public function loadModel($id, $surveyId = null)
    {
        if (!isset($surveyId)) {
            throw new \InvalidArgumentException("SurveyID is required when loading token.");
        } elseif (!\ls\models\Token::valid($surveyId)) {
            throw new \CHttpException(404, gT("Token table not found"));
        } elseif (null === $result = \ls\models\Token::model($surveyId)->findByPk($id)) {
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


    /**
     * @param $id
     * @todo Add permission check.
     */
    public function actionImport($surveyId, array $items = null, array $map = null, $querySize = 1000)
    {
        $survey = \ls\models\Survey::model()->findByPk($surveyId);
        $this->menus['survey'] = $survey;
        if (!Token::valid($surveyId)) {
            throw new \CHttpException(404, gT("Token table not found"));
        }
        $model = Token::create($surveyId);
        if (App()->request->isAjaxRequest && isset($items, $map, $querySize)) {
            return $this->ajaxImport($surveyId, $items, $map, $querySize);
        } else {

            $this->render('import', ['model' => $model]);

        }
    }

    public function ajaxImport($surveyId, array $items, array $map, $querySize = 1000)
    {
        // Set response code so on errors (max execution time, memory limit) we don't get http 200.
        http_response_code(500);
        header('Content-Type: application/json');
        set_time_limit(20);
        ini_set('memory_limit', '64M');
        $return_bytes = function($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            switch($last) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        };

        $transaction = App()->getDb()->beginTransaction();
        $start = App()->request->psr7->getServerParams()['REQUEST_TIME_FLOAT'];
        $memoryLimit = $return_bytes(ini_get('memory_limit'));





        $model = Token::create($surveyId);

        $tableName = $model->tableName();

        $fields = array_flip($model->safeAttributeNames);

        $executeResults = [];
        $batchInserter = new \ls\components\Batch(function (array $batch, $category = null) use (&$executeResults) {
            if (!empty($batch)) {
                \Yii::beginProfile('query');
                try {
                    $command = App()->db->commandBuilder->createMultipleInsertCommand($category, $batch);
                } catch (\Exception $e) {
                    die("Error in query generation.");
                }
                try {
                    $executeResults [] = $command->execute();
                } catch (\Exception $e) {
                    die("Error in query execution.");
                }
                \Yii::endProfile('query');
            }
        }, 1000, $tableName);


        $initialAttributes = $model->getAttributes();
        $counters = [];
        foreach ($items as $row) {
            $model->setAttributes($initialAttributes, false);
            \Yii::beginProfile('alternative');
            foreach ($row as $key => $value) {
                if (isset($fields[$key])) {
                    $model->$key = $value;
                }
            }
            \Yii::endProfile('alternative');

            if ($model->validate()) {
                $batchInserter->add($model->getAttributes());
            } else {
                foreach ($model->errors as $field => $errors) {
                    foreach ($errors as $error) {
                        if (isset($counters[$field][$error])) {
                            $counters[$field][$error]++;
                        } else {
                            $counters[$field][$error] = 1;
                        }
                    }
                }
            }

            \Yii::endProfile('row');
        }
        \Yii::endProfile('import');



        $batchInserter->commit();
        $transaction->commit();
        http_response_code(200);
        echo json_encode([
            'memory' => memory_get_peak_usage() / $memoryLimit,
            'time' => (microtime(true) - $start) / ini_get('max_execution_time'),
            'queries' => $batchInserter->commitCount,
            'records' => $batchInserter->recordCount,
            'errors' => $counters,
            'results' => $executeResults
        ]);


    }

    /**
     * Generates tokens.
     * @throws \CHttpException Bad method exception if not post.
     * @param int Id of the survey.
     */
    public function actionGenerate($surveyId)
    {
        if (!App()->request->getIsPostRequest()) {
            throw new \CHttpException(405);
        }
        $survey = Survey::model()->findByPk($surveyId);
        $result = Token::model($surveyId)->generateTokens($survey->tokenlength);
        App()->user->setFlash('info', \Yii::t('', "Generated {n} token|Generated {n} tokens", $result));
        $this->redirect(['tokens/index', 'surveyId' => $surveyId]);

    }

}