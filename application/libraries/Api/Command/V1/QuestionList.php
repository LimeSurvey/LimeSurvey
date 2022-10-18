<?php

namespace LimeSurvey\Api\Command\V1;

use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;
use LimeSurvey\Api\Command\Mixin\Accessor\SurveyModel;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionGroupModel;

class QuestionList implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;
    use SurveyModel;
    use QuestionGroupModel;

    /**
     * Run survey question list command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $iGroupID = $request->getData('groupID');
        $sLanguage = $request->getData('language');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        Yii::app()->loadHelper("surveytranslator");

        $oSurvey = $this->getSurveyModel($iSurveyID);
        if (empty($oSurvey)) {
            return $this->responseErrorNotFound(
                ['status' => 'Error: Invalid survey ID'],
                new StatusErrorNotFound()
            );
        }

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'survey',
                'read'
            )
            ) !== true
        ) {
            return $response;
        }

        if (is_null($sLanguage)) {
            $sLanguage = $oSurvey->language;
        }

        if (
            !array_key_exists(
                $sLanguage,
                getLanguageDataRestricted()
            ) || !in_array($sLanguage, $oSurvey->allLanguages ?? [])
        ) {
            return $this->responseErrorBadRequest(
                ['status' => 'Error: Invalid language']
            );
        }

        if ($iGroupID != null) {
            $iGroupID = (int) $iGroupID;
            $oGroup = $this->getQuestionGroupModel($iGroupID);

            if (empty($oGroup)) {
                return $this->responseErrorNotFound(
                    ['status' => 'Error: group not found']
                );
            }

            if ($oGroup->sid != $oSurvey->sid) {
                return $this->responseErrorNotFound(
                    ['status' => 'Error: Mismatch in surveyid and groupid']
                );
            } else {
                $aQuestionList = $oGroup->allQuestions;
            }
        } else {
            $aQuestionList = $oSurvey->allQuestions;
        }

        if (count($aQuestionList) == 0) {
            return $this->responseSuccess(
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

        return $this->responseSuccess($aData);
    }
}
