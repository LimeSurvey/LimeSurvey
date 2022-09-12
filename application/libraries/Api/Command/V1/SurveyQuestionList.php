<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyQuestionList implements CommandInterface
{
    /**
     * Run survey question list command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $iGroupID = $request->getData('groupID');
        $sLanguage = $request->getData('language');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            \Yii::app()->loadHelper("surveytranslator");
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = \Survey::model()->findByPk($iSurveyID);

            if (empty($oSurvey)) {
                return new CommandResponse(
                    ['status' => 'Error: Invalid survey ID']
                );
            }

            if (
                \Permission::model()
                ->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'read'
                )
            ) {
                if (is_null($sLanguage)) {
                    $sLanguage = $oSurvey->language;
                }

                if (
                    !array_key_exists(
                        $sLanguage,
                        getLanguageDataRestricted()
                    ) || !in_array($sLanguage, $oSurvey->allLanguages)
                ) {
                    return new CommandResponse(
                        ['status' => 'Error: Invalid language']
                    );
                }

                if ($iGroupID != null) {
                    $iGroupID = (int) $iGroupID;
                    $oGroup = \QuestionGroup::model()
                        ->findByPk($iGroupID);

                    if (empty($oGroup)) {
                        return new CommandResponse(
                            ['status' => 'Error: group not found']
                        );
                    }

                    if ($oGroup->sid != $oSurvey->sid) {
                        return new CommandResponse(
                            ['status' => 'Error: Mismatch in surveyid and groupid']
                        );
                    } else {
                        $aQuestionList = $oGroup->allQuestions;
                    }
                } else {
                    $aQuestionList = $oSurvey->allQuestions;
                }

                if (count($aQuestionList) == 0) {
                    return new CommandResponse(
                        ['status' => 'No questions found']
                    );
                }

                foreach ($aQuestionList as $oQuestion) {
                    $L10ns = $oQuestion->questionl10ns[$sLanguage];
                    $aData[] = array_merge(
                        [
                            'id' => $oQuestion->primaryKey,
                            'question' => $L10ns->question,
                            'help' => $L10ns->help,
                            'language' => $sLanguage,
                        ],
                        $oQuestion->attributes
                    );
                }
                return new CommandResponse($aData);
            } else {
                return new CommandResponse(
                    ['status' => 'No permission']
                );
            }
        } else {
            return new CommandResponse(
                ['status' => ApiSession::INVALID_SESSION_KEY]
            );
        }
    }
}
