<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSurveyPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class SurveyDelete implements CommandInterface
{
    use AuthSession;
    use AuthSurveyPermission;
    use CommandResponse;

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
        $iSurveyID = (int) $request->getData('surveyID');

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

        Survey::model()->deleteSurvey($iSurveyID, true);
        return $this->responseSuccess(
            array('status' => 'OK')
        );
    }
}
