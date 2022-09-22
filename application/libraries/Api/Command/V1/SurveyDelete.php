<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

class SurveyDelete implements CommandInterface
{
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

        $apiSession = new ApiSession();
        if ($apiSession->checkKey($sSessionKey)) {
            if (
                Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'delete'
                )
            ) {
                Survey::model()->deleteSurvey($iSurveyID, true);
                return new Response(
                    array('status' => 'OK'),
                    new StatusSuccess()
                );
            } else {
                return new Response(
                    array('status' => 'No permission'),
                    new StatusErrorUnauthorised()
                );
            }
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY),
                new StatusErrorUnauthorised()
            );
        }
    }
}
