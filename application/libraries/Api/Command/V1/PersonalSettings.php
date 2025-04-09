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
        return $this->responseFactory
            ->makeSuccess([
                'answeroptionprefix' => Yii::app()->getConfig('answeroptionprefix'),
                'subquestionprefix' => Yii::app()->getConfig('subquestionprefix'),
                'showQuestionCodes' => Yii::app()->getConfig('showQuestionCodes')
            ]);
    }
}