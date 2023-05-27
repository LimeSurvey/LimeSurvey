<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthPermissionTrait
};

class SurveyList implements CommandInterface
{
    use AuthPermissionTrait;
    use CommandResponseTrait;

    protected ?AuthSession $authSession = null;
    protected ?TransformerOutputSurvey $transformerOutputSurvey = null;
    protected ?ResponseFactory $responseFactory = null;

    /**
     * Constructor
     *
     * @param TransformerOutputSurvey $transformerOutputSurvey
     */
    public function __construct(
        AuthSession $authSession,
        TransformerOutputSurvey $transformerOutputSurvey,
        ResponseFactory $responseFactory
    )
    {
        $this->authSession = $authSession;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey list command
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $pageSize = (string) $request->getData('pageSize', 20);
        $page = (string) $request->getData('page', 1);

        if (
            (
                $response = $this->authSession
                    ->checkKey($sessionKey)
            ) !== true
        ) {
            return $response;
        }

        $dataProvider = Survey::model()
        ->with('defaultlanguage')
        ->search([
            'pageSize' => $pageSize,
            'currentPage' => $page + 1 // one based rather than zero based
        ]);

        $data = $this->transformerOutputSurvey
            ->transformAll($dataProvider->getData());

        return $this->responseFactory
            ->makeSuccess(['surveys' => $data]);
    }
}
