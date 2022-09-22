<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use QuestionGroup;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

class QuestionGroupDelete implements CommandInterface
{
    /**
     * Run group delete command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iGroupID = (int) $request->getData('groupID');

        $apiSession = new ApiSession();
        if ($apiSession->checkKey($sSessionKey)) {
            $oGroup = QuestionGroup::model()
                ->findByAttributes(array('gid' => $iGroupID));
            if (!isset($oGroup)) {
                return new Response(
                    array('status' => 'Error:Invalid group ID'),
                    new StatusErrorNotFound()
                );
            }
            $iSurveyID = $oGroup->sid;

            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new Response(
                    array('status' => 'Error: Invalid survey ID'),
                    new StatusErrorNotFound()
                );
            }

            if (
                Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'surveycontent',
                    'delete'
                )
            ) {
                if ($oSurvey->isActive) {
                    return new Response(
                        array('status' => 'Error:Survey is active and not editable'),
                        new StatusError()
                    );
                }

                $dependantOn = getGroupDepsForConditions(
                    $oGroup->sid,
                    "all",
                    $iGroupID,
                    "by-targgid"
                );
                if (isset($dependantOn)) {
                    return new Response(
                        array('status' => 'Group with dependencies - deletion not allowed'),
                        new StatusError()
                    );
                }

                $iGroupsDeleted = QuestionGroup::deleteWithDependency(
                    $iGroupID,
                    $iSurveyID
                );

                if ($iGroupsDeleted === 1) {
                    QuestionGroup::model()
                        ->updateGroupOrder($iSurveyID);
                    return new Response(
                        (int) $iGroupID,
                        new StatusSuccess()
                    );
                } else {
                    return new Response(
                        array('status' => 'Group deletion failed'),
                        new StatusError()
                    );
                }
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
