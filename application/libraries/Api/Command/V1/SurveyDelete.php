<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait,
    Accessor\SurveyModelTrait
};

class SurveyDelete implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;
    use SurveyModelTrait;

    /**
     * Run survey delete command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('_id');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        if (
            ($response = $this->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'delete'
            )) !== true
        ) {
            return $response;
        }

        $this->deleteSurvey($iSurveyID);

        return $this->responseSuccess(
            ['status' => 'OK']
        );
    }

    /**
     * Delete Survey
     *
     * Implement as a protected method to allow mocking in unit tests.
     *
     * @param int $iSurveyID
     * @return bool
     */
    protected function deleteSurvey($iSurveyID)
    {
        return Survey::model()->deleteSurvey($iSurveyID, true);
    }
}
