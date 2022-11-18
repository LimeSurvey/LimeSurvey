<?php

namespace LimeSurvey\Api\Command\V2;

use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class SurveyList implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;

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
        $page = (string) $request->getData('page', 1);
        $limit = (string) $request->getData('limit', 20);

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        $dataProvider = Survey::model()->search();
        $data = $dataProvider->getData();

        return $this->responseSuccess($data);
    }
}
