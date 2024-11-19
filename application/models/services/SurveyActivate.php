<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_Application;
use Permission;
use Survey;
use SurveyActivator;

class SurveyActivate
{
    private Survey $survey;
    private Permission $permission;
    private SurveyActivator $surveyActivator;
    private LSYii_Application $app;

    public function __construct(
        Survey $survey,
        Permission $permission,
        SurveyActivator $surveyActivator,
        LSYii_Application $app
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
        $this->surveyActivator = $surveyActivator;
        $this->app = $app;
    }

    /**
     * @param $surveyId
     * @param array $params
     * @return array
     * @throws PermissionDeniedException
     */
    public function activate($surveyId, array $params = []): array
    {
        if (!$this->permission->hasSurveyPermission($surveyId, 'surveyactivation', 'update')) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }

        if (!is_array($params)) {
            $params = [];
        }

        $survey = $this->survey->findByPk($surveyId);
        $aData['oSurvey'] = $survey;
        $aData['sidemenu']['state'] = false;
        $aData['aSurveysettings'] = getSurveyInfo($surveyId);
        $aData['surveyid'] = $surveyId;

        if (!is_null($survey)) {
            $fields = [
                'anonymized',
                'datestamp',
                'ipaddr',
                'ipanonymize',
                'refurl',
                'savetimings'
            ];
            foreach ($fields as $field) {
                $survey->{$field} = $this->app->request->getPost($field, $params[$field] ?? null);
            }
            $survey->save();

            // Make sure the saved values will be picked up
            $this->survey->resetCache();
            $survey->setOptions();
        }

        $result = $this->surveyActivator->setSurvey($survey)->activate();
        return $result;
    }
}
