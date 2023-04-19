<?php

namespace LimeSurvey\Api\Command\V2;

use Survey;
use LimeSurvey\Api\Command\V2\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    ResponseData\ResponseDataError
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait
};

class SurveyDetail implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;

    /**
     * Run survey detail command
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $surveyId = (string) $request->getData('_id');

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        $surveyModel = Survey::model()
            ->with(
                'languagesettings',
                'defaultlanguage',
                'groups',
                'groups.questiongroupl10ns',
                'groups.questions',
                'groups.questions.questionl10ns',
                'groups.questions.questionattributes',
                'groups.questions.answers',
                // Integrity constraint violation: 1052 Column 'parent_qid' in where clause is ambiguous
                'groups.questions.subquestions',
                'groups.questions.subquestions.questionl10ns',
                'groups.questions.subquestions.questionattributes',
                'groups.questions.subquestions.questionattributes',
                'groups.questions.subquestions.answers'
            )->findByPk($surveyId);

        if (!$surveyModel) {
            return $this->responseErrorNotFound(
                (new ResponseDataError('SURVEY_NOT_FOUND', 'Survey not found'))->toArray()
            );
        }

        $survey = (new TransformerOutputSurveyDetail)
            ->transform($surveyModel);

        return $this->responseSuccess(['survey' => $survey]);
    }
}
