<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyTranslations implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected ResponseFactory $responseFactory;
    protected Permission $permission;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param ResponseFactory $responseFactory
     * @param Permission $permission
     */
    public function __construct(
        Survey $survey,
        ResponseFactory $responseFactory,
        Permission $permission
    ) {
        $this->survey = $survey;
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (string) $request->getData('_id');
        $hasPermission = $this->permission->hasSurveyPermission(
            (int)$surveyId,
            'survey',
            'read'
        );
        if (!$hasPermission) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $surveyModel = $this->survey->findByPk($surveyId);
        if (!$surveyModel) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }

        $translations = [];
        $surveyLanguages = $surveyModel->allLanguages;
        foreach($surveyLanguages as $language) {
            // TODO: here is the place to get all the translations and convert them to the expected format
            $translations[$language]['Structure'] = 'Structure_' . $language;
        }

        return $this->responseFactory
            ->makeSuccess(['surveyTranslations' => $translations]);
    }
}
