<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\SurveyAggregateService\GeneralSettings;
use Permission;
use Survey;

class SurveyActivate
{
    private GeneralSettings $generalSettings;
    private Survey $survey;
    private Permission $permission;
    private $restMode = false;

    public function __construct(
        Survey $survey,
        Permission $permission,
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
    }

    public function activate($surveyId, $params)
    {
        if (!$this->permission->hasSurveyPermission($surveyId, 'surveyactivation', 'update')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }

        $survey = Survey::model()->findByPk($surveyId);
        $surveyActivator = new SurveyActivator($survey);
        $aData['oSurvey'] = $survey;
        $aData['sidemenu']['state'] = false;
        $aData['aSurveysettings'] = getSurveyInfo($surveyId);
        $aData['surveyid'] = $surveyId;

        $openAccessMode = Yii::app()->request->getPost('openAccessMode', null);

        if (!is_null($survey)) {
            $survey->anonymized = Yii::app()->request->getPost('anonymized');
            $survey->datestamp = Yii::app()->request->getPost('datestamp');
            $survey->ipaddr = Yii::app()->request->getPost('ipaddr');
            $survey->ipanonymize = Yii::app()->request->getPost('ipanonymize');
            $survey->refurl = Yii::app()->request->getPost('refurl');
            $survey->savetimings = Yii::app()->request->getPost('savetimings');
            $survey->save();

            // Make sure the saved values will be picked up
            Survey::model()->resetCache();
            $survey->setOptions();
        }
    }
}
