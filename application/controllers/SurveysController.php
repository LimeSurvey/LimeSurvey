<?php
namespace ls\controllers;
use ls\import\ImportFactory;
use Survey;
use Zend\Diactoros\ServerRequest;

/**
 * This class will handle survey creation and manipulation.
 */
class SurveysController extends Controller
{
    public $layout = 'minimal';

    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);
        $this->defaultAction = App()->user->isGuest ? 'publicList' : 'index';
    }

    public function accessRules() {
        return array_merge([
            ['allow', 'actions' => ['index'], 'users' => ['@']],
            ['allow', 'actions' => ['publicList', 'run' ,'start']],

        ], parent::accessRules());
    }
    public function actionOrganize($surveyId)
    {
        $this->layout = 'main';
        $groups = QuestionGroup::model()->findAllByAttributes(array(
            'sid' => $surveyId
        ));
        $this->render('organize', compact('groups'));
    }

    public function actionIndex() {
        $this->layout = 'main';
        $filter = new \ls\models\filter\Survey();
        $filter->setAttributes(App()->request->getParam(\CHtml::modelName($filter)));
        $surveys = Survey::model()->accessible();
        $surveys->getDbCriteria()->mergeWith($filter->search());
        $surveys->with('languagesettings');
        $dataProvider = new \CActiveDataProvider($surveys);
        $dataProvider->pagination->pageSize = 100;
        $this->render('index', ['surveys' => $dataProvider, 'filter' => $filter]);
    }

    public function actionPublicList($sLanguage = null)
    {
        if (isset($sLanguage))
        {
            App()->setLanguage($sLanguage);
        }
        $this->render('publicSurveyList', array(
            'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
            'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

        ));
    }

    public function actionUpdate($id) {
        $survey = $this->loadModel($id, 'groups.questions');
        if (App()->request->isPostRequest && isset($survey)) {
            $survey->setAttributes($_POST['Survey']);
            if ($survey->save(true)) {
                App()->user->setFlash('success', gT("Survey settings updated."));
                $this->refresh();
            }
        }
        $this->layout = 'survey';
        $this->menus['survey'] = $survey;
        $this->render('update', ['survey' => $survey]);
    }

    public function actionActivate($id) {
        $this->layout = 'survey';
        $survey = $this->loadModel($id);
        if (App()->request->isPostRequest) {
            $survey->activate();
            App()->user->setFlash('succcess', "Survey activated.");
            $this->redirect(['surveys/update', 'id' => $survey->primaryKey]);
        }

        $this->render('activate', ['survey' => $survey]);
    }

    public function actionDeactivate($id) {
        $this->layout = 'survey';
        $survey = $this->loadModel($id);
        if (App()->request->isPostRequest) {
            $survey->deactivate();
            App()->user->setFlash('succcess', "Survey deactivated.");
            $this->redirect(['surveys/update', 'id' => $survey->sid]);
        }

        $this->menus['survey'] = $survey;
        $this->render('deactivate', ['survey' => $survey]);
    }
    public function filters()
    {
        return array_merge(parent::filters(), ['accessControl']);
    }

    /**
     * @param type $id
     * @return Survey
     * @throws CHttpException
     * @throws \CHttpException
     */
    protected function loadModel($id, $with = null) {
        $survey = Survey::model()->with($with)->findByPk($id);
        if (!isset($survey)) {
            throw new \CHttpException(404, "Survey not found.");
        } elseif (!App()->user->checkAccess('survey', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $id])) {
            throw new \CHttpException(403);
        }

        if ($this->layout == 'survey') {
            $this->menus['survey'] = $survey;
        }
        return $survey;
    }

    /**
     * This function starts the survey.
     * If a welcome screen is active it shows the welcome screen.
     * @param $id
     */
    public function actionStart($id, $token = null, array $pre = [])
    {
        /** @var Survey $survey */
        $survey = Survey::model()->findByPk($id);
        $this->layout = 'showsurvey';
        if (!$survey->isActive) {
            throw new \CHttpException(412, gT("The survey is not active."));
        } elseif ($survey->bool_usetokens && !isset($token)) {
            throw new \CHttpException(400, gT("Token required."));
        } elseif ($survey->bool_usetokens && null === $token = \Token::model($id)->findByAttributes(['token' => $token])) {
            throw new \CHttpException(404, gT("Token not found."));
        }

        $targetUrl = [
            'surveys/execute',
            'surveyId' => $id,
        ];
        if (App()->request->isPostRequest || $survey->format == Survey::FORMAT_ALL_IN_ONE || !$survey->bool_showwelcome) {
            // Create response.
            /**
             * @todo Check if we should resume an existing response instead.
             */
            $response = \Response::create($id);
            if (isset($token)) {
                /**
                 * @todo Update token and check for anonymous.
                 */
                if (!$survey->bool_anonymized) {
                    $response->token = $token->token;
                }
            }
            // Capture referer.
            if ($survey->bool_refurl && isset(App()->request->psr7->getServerParams()['HTTP_REFERER'])) {
                $response->url = App()->request->psr7->getServerParams()['HTTP_REFERER'];
            }

            // Check if there are parameters for prefilling.
            foreach($pre as $key => $value) {
                $response->setAnswer($key, $value, function() {
                    throw new \CHttpException(400, gT("Invalid answer code in URL"));
                });
            }
            $response->save();

            $session = App()->surveySessionManager->newSession($survey->primaryKey, $response->id);
            if (isset($token)) {
                $session->setToken($token);
            }

            $this->redirect(['survey/index', 'SSM' => $session->getId()]);

//            $this->redirect(['surveys/run', 'sessionId' => $session->id]);
        } else {
            $this->render('execute/welcome', ['survey' => $survey]);
        }
    }

   public function actionUnexpire($id) {
        $this->layout = 'survey';

        $survey = $this->loadModel($id);
        if (App()->request->isPostRequest && $survey->unexpire()) {
            App()->user->setFlash('success', gT("Survey expiry date removed."));
            $this->redirect(['surveys/update', 'id' => $id]);
        }
        $this->render('unexpire', ['survey' => $survey]);
    }

    public function actionExpire($id)
    {
        $survey = $this->loadModel($id);

        if (App()->request->isPostRequest) {
//                $survey->deactivate();
//                App()->user->setFlash('succcess', "Survey deactivated.");
//                $this->redirect(['surveys/update', 'id' => $survey->sid]);


        }
        $this->layout = 'survey';
        $this->menus['survey'] = $survey;
        $this->render('expire', ['survey' => $survey]);
    }


    public function actionImport() {
        $this->layout = 'main';
        $request = App()->request;
        /** @var \CUploadedFile $file */
        $file = \CUploadedFile::getInstanceByName('importFile');
        App()->loadHelper('admin.import');
        if (isset($file)) {
            $importer = ImportFactory::getForLss($file->getTempName());
            if (null !== $survey = $importer->run()) {

                App()->user->setFlash('success', "Survey imported ({$survey->groupCount}/{$survey->questionCount}).");
                $this->redirect(['surveys/update', 'id' => $survey->primaryKey]);
            } else {
                App()->user->setFlash('error', "Survey not imported.");
            }
        } else {
            $this->redirect(['surveys/create']);
        }

    }

    public function actionExport($id, $type = 'structure') {

        $survey = $this->loadModel($id);

//        $export = Survey::model()->with(
//            'groups',
//            'languagesettings'
//        )->findByPk($id);
//        $this->renderText('<pre>' . json_encode(($export->toArray(true, ['savedControls', 'surveyLinks', 'quota'])), JSON_PRETTY_PRINT) . '</pre>');
//        return;
//        $export->getRelated();
        App()->loadHelper('export');
        if ($type == 'structure') {
            App()->request->sendFile("survey_$id.lss", \surveyGetXMLData($id));
        }

    }
    public function actionCreate()
    {
        $this->layout = 'main';
        $survey = new Survey();
        $survey->owner_id = App()->user->id;
        $languageSetting = new \SurveyLanguageSetting();
        $request = App()->request;

        if ($request->isPostRequest) {
            $survey->setAttributes($request->getParam('Survey'));
            $languageSetting->setAttributes($request->getParam('SurveyLanguageSetting'));

            // Validate both before saving either.
            if ($survey->validate()
                && $languageSetting->validate()
                && $survey->save(false)
                && (($languageSetting->surveyls_survey_id = $survey->sid) != null)
                // Validate language setting again after setting survey id.
                && $languageSetting->save(true)
            ) {

                App()->user->setFlash('success', gT('Survey created'));
                return $this->redirect(['surveys/update', 'id' => $survey->sid]);
            }
        }
        $this->render('create', ['survey' => $survey, 'languageSetting' => $languageSetting]);
    }


    public function actionAbort()
    {
        $this->layout = 'bare';
        $ssm = App()->surveySessionManager;
        if ($ssm->active
            && !$ssm->current->isFinished
        ) {
            $surveyId = $ssm->current->surveyId;
            if (!isset($ssm->current->response) || $ssm->current->response->delete(true)) {
                $ssm->destroySession();

            }
        }

        $templatePath = \Template::getTemplatePath('default');
        return $this->render('abort', [
            'templatePath' => $templatePath,
            'surveyId' => isset($surveyId) ? $surveyId : null
        ]);
    }


    public function actionDelete($id) {
        $survey = $this->loadModel($id);
        if (!$survey->isActive
            && App()->user->checkAccess('survey', [
                'entity' => 'survey',
                'entity_id' => $survey->primaryKey,
                'crud' => 'delete'
            ])) {
            $survey->deleteDependent();
            App()->user->setFlash('success', gT("Survey deleted"));
            $this->redirect(['surveys/index']);
        } else {
            if ($survey->isActive) {
                App()->user->setFlash('danger', gT("Active surveys can not be deleted"));
            }
            $this->redirect(['surveys/update', 'id' => $survey->primaryKey]);
        }
    }

    public function actionDeleteMultiple(array $ids) {
        $count = 0;
        foreach($ids as $id) {
            $survey = $this->loadModel($id);
            if (!$survey->isActive
            && App()->user->checkAccess('survey', [
                'entity' => 'survey',
                'entity_id' => $survey->primaryKey,
                'crud' => 'delete'
            ])) {
                $survey->deleteDependent();
                $count++;

            }

        }
        App()->user->setFlash('success', gT("Surveys deleted") . " " . $count);
        $this->redirect(['surveys/index']);
    }
}