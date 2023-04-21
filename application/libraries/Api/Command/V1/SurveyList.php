<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait
};

class SurveyList implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;

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
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        $dataProvider = Survey::model()
        ->with('defaultlanguage')
        ->search([
            'pageSize' => $pageSize,
            'currentPage' => $page + 1 // one based rather than zero based
        ]);

        $data = (new TransformerOutputSurvey)
            ->transformAll($dataProvider->getData());

        return $this->responseSuccess(['surveys' => $data]);
    }
}
