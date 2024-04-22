<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\SurveyAggregateService\GeneralSettings;
use Permission;
use Survey;
use SurveyActivator;

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

        $survey = $this->survey->findByPk($surveyId);
        $aData['oSurvey'] = $survey;
        $aData['sidemenu']['state'] = false;
        $aData['aSurveysettings'] = getSurveyInfo($surveyId);
        $aData['surveyid'] = $surveyId;

        $openAccessMode = App()->request->getPost('openAccessMode', null);
        if (!is_null($survey)) {
            $survey->anonymized = App()->request->getPost('anonymized');
            $survey->datestamp = App()->request->getPost('datestamp');
            $survey->ipaddr = App()->request->getPost('ipaddr');
            $survey->ipanonymize = App()->request->getPost('ipanonymize');
            $survey->refurl = App()->request->getPost('refurl');
            $survey->savetimings = App()->request->getPost('savetimings');
            $survey->save();

            // Make sure the saved values will be picked up
            Survey::model()->resetCache();
            $survey->setOptions();
        }

        $surveyActivator = new SurveyActivator($survey);
        $result = $surveyActivator->activate();
        return $result;
    }
}
