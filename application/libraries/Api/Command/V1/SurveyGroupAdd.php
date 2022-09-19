<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use QuestionGroup;
use QuestionGroupL10n;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

class SurveyGroupAdd implements CommandInterface
{
    /**
     * Run group add command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $sGroupTitle = (string) $request->getData('groupTitle');
        $sGroupDescription = (string) $request->getData('groupDescription');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            if (
                Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'update'
                )
            ) {
                $iSurveyID = (int) $iSurveyID;
                $oSurvey = Survey::model()->findByPk($iSurveyID);
                if (!isset($oSurvey)) {
                    return new Response(
                        array('status' => 'Error: Invalid survey ID'),
                        new StatusErrorBadRequest
                    );
                }

                if ($oSurvey->isActive) {
                    return new Response(
                        array('status' => 'Error: Survey is active and not editable'),
                        new StatusErrorBadRequest
                    );
                }

                $oGroup = new QuestionGroup();
                $oGroup->sid = $iSurveyID;
                $oGroup->group_order = getMaxGroupOrder($iSurveyID);
                if (!$oGroup->save()) {
                    return new Response(
                        array('status' => 'Creation Failed'),
                        new StatusError
                    );
                }

                $oQuestionGroupL10n = new QuestionGroupL10n();
                $oQuestionGroupL10n->group_name = $sGroupTitle;
                $oQuestionGroupL10n->description = $sGroupDescription;
                $oQuestionGroupL10n->language = \Survey::model()->findByPk($iSurveyID)->language;
                $oQuestionGroupL10n->gid = $oGroup->gid;

                if ($oQuestionGroupL10n->save()) {
                    return new Response(
                        (int) $oGroup->gid,
                        new StatusSuccess
                    );
                } else {
                    return new Response(
                        array('status' => 'Creation Failed'),
                        new StatusError
                    );
                }
            } else {
                return new Response(
                    array('status' => 'No permission'),
                    new StatusErrorUnauthorised
                );
            }
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY),
                new StatusErrorUnauthorised
            );
        }
    }
}
