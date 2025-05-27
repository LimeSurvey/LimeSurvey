<?php

namespace LimeSurvey\Api\Command\V1;

use Yii;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class PersonalSettings implements CommandInterface
{
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $userId = $request->getData('_id');
        if (!$userId) {
            return $this->responseFactory
                ->makeError('No user ID provided');
        }
        try {
            $answerOptionPrefix = \SettingsUser::getUserSettingValue('answeroptionprefix', $userId);
            $subquestionPrefix = \SettingsUser::getUserSettingValue('subquestionprefix', $userId);
            $showQuestionCodes = \SettingsUser::getUserSettingValue('showQuestionCodes', $userId);
        } catch (\Exception $e) {
            return $this->responseFactory
                ->makeException($e);
        }

        return $this->responseFactory
            ->makeSuccess([
                'answeroptionprefix' => $answerOptionPrefix,
                'subquestionprefix' => $subquestionPrefix,
                'showQuestionCodes' => $showQuestionCodes
            ]);
    }
}