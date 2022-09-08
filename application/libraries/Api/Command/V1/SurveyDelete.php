<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyDelete implements CommandInterface
{
    /**
     * Run survey delete command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            if (
                \Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'delete'
                )
            ) {
                \Survey::model()->deleteSurvey($iSurveyID, true);
                return new CommandResponse(array('status' => 'OK'));
            } else {
                return new CommandResponse(array('status' => 'No permission'));
            }
        } else {
            return new CommandResponse(
                array('status' => ApiSession::INVALID_SESSION_KEY)
            );
        }
    }
}
