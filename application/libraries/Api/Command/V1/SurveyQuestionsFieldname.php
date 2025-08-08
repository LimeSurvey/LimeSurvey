<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LSYii_Application;
use Permission;
use Survey;

class SurveyQuestionsFieldname implements CommandInterface
{
    protected Survey $survey;

    protected Permission $permission;

    protected LSYii_Application $app;

    protected ResponseFactory $responseFactory;


    /**
     * Constructor
     * @param \LSYii_Application $app
     * @param \Permission $permission
     * @param \LimeSurvey\Api\Command\Response\ResponseFactory $responseFactory
     */
    public function __construct(
        LSYii_Application $app,
        Permission $permission,
        ResponseFactory $responseFactory
    ) {
        $this->app = $app;
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
    }

    public function run(Request $request)
    {
        $surveyId = (string) $request->getData('_id');

        if (!$this->permission->hasSurveyPermission($surveyId, 'fieldnames')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        $surveyLanguage = Survey::model()->findByPk($surveyId)->language;
        $survey = Survey::model()->findByPk($surveyId);

        $fullFieldMap = createFieldMap($survey, 'full', true, false, $surveyLanguage);

        $questionsFieldMap = [];

        foreach ($fullFieldMap as $fieldCode => $fieldMeta) {
            if (strpos($fieldCode, 'Q') !== 0) {
                continue;
            }

            if (strpos($fieldMeta['fieldname'], 'comment') !== false) {
                continue;
            }

            if (strpos($fieldMeta['fieldname'], 'filecount') !== false) {
                continue;
            }

            $questionsFieldMap[$fieldMeta['qid']][] = $fieldMeta;
        }

        $structuredFieldnames = [];

        foreach ($questionsFieldMap as $questionId => $questionFields) {
            foreach ($questionFields as $field) {
                $structuredFieldnames[$questionId][$field['fieldname']] = $field;
            }
        }

        return $this->responseFactory->makeSuccess($structuredFieldnames);
    }
}
