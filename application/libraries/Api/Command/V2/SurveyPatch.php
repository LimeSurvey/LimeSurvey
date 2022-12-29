<?php

namespace LimeSurvey\Api\Command\V2;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponse,
    Auth\AuthSession,
    Auth\AuthPermission
};

class SurveyPatch implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;

    /**
     * Run survey patch command
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $id = (string) $request->getData('_id');

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }


        $result = [];

        return $this->responseSuccess($result);
    }
}
