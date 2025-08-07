<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LSYii_Application;
use Permission;
use Survey;

class SurveyFieldnames implements CommandInterface
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
        ResponseFactory $responseFactory,
    ) {
        $this->app = $app;
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
    }

    public function run(Request $request)
    {
        $surveyId = (string)$request->getData('_id');
        if (!$this->permission->hasSurveyPermission($surveyId, 'fieldnames')) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $language = Survey::model()->findByPk($surveyId)->language;
        $survey = \Survey::model()->findByPk($surveyId);
        $fieldMap = createFieldMap($survey, 'full', true, false, $language);
        $fieldnames = [];

        foreach ($fieldMap as $key => $value) {
            if (strpos($key, 'Q') === 0) {
                if(strpos($value['fieldname'], 'comment') !== false)
                    continue;
                if(strpos($value['fieldname'], 'filecount') !== false)
                    continue;
                $fieldnames[$value['qid']][] = $value;
            }
        }

        return $this->responseFactory->makeSuccess($fieldnames);
    }
}
