<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthTokenSimple;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use DI\FactoryInterface;

class SurveyList implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected AuthTokenSimple $authTokenSimple;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthTokenSimple $authTokenSimple
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        AuthTokenSimple $authTokenSimple,
        TransformerOutputSurvey $transformerOutputSurvey,
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    ) {
        $this->survey = $diFactory->make(
            Survey::class,
            ['scenario' => 'search']
        );
        $this->authTokenSimple = $authTokenSimple;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey list command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');

        if (
            !$this->authTokenSimple
                ->checkKey($sessionKey)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        unset($this->survey->active);
        $dataProvider = $this->survey
            ->with('defaultlanguage')
            ->search([
                'pageSize' => $request->getData('pageSize'),
                // Yii pagination is zero based - so we must deduct 1
                'currentPage' => $request->getData('page') - 1,
            ]);

        $data = $this->transformerOutputSurvey
            ->transformAll($dataProvider->getData());

        return $this->responseFactory
            ->makeSuccess(['surveys' => $data]);
    }
}
