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

class SurveyGroupList implements CommandInterface
{
    /**
     * Run group list command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\Request\Request $request
     * @return LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $sLanguage = $request->getData('language');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new Response(
                    array('status' => 'Error: Invalid survey ID'),
                    new StatusErrorNotFound
                );
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'read')) {
                $oGroupList = QuestionGroup::model()->with('questiongroupl10ns')->findAllByAttributes(array("sid" => $iSurveyID));
                if (count($oGroupList) == 0) {
                    return new Response(
                        array('status' => 'No groups found'),
                        new StatusSuccess
                    );
                }

                if (is_null($sLanguage)) {
                    $sLanguage = $oSurvey->language;
                }

                foreach ($oGroupList as $oGroup) {
                    $L10ns = $oGroup->questiongroupl10ns[$sLanguage];
                    $tmp = array('id' => $oGroup->primaryKey) + $oGroup->attributes;
                    $tmp['group_name'] = $L10ns['group_name'];
                    $tmp['description'] = $L10ns['description'];
                    $tmp['language'] = $sLanguage;
                    $aData[] = $tmp;
                }
                return new Response(
                    $aData, 
                    new StatusSuccess
                );
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
