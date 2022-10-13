<?php

namespace LimeSurvey\Api\Command\V1;

use QuestionGroup;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;
use LimeSurvey\Api\Command\Mixin\Accessor\SurveyModel;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionGroupModelCollectionWithLn10sBySid;

class QuestionGroupList implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;
    use SurveyModel;
    use QuestionGroupModelCollectionWithLn10sBySid;

    /**
     * Run group list command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $sLanguage = $request->getData('language');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        $oSurvey = $this->getSurveyModel($iSurveyID);
        if (!isset($oSurvey)) {
            return $this->responseErrorNotFound(
                array('status' => 'Error: Invalid survey ID')
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

        $oGroupList = $this->getQuestionGroupModelCollectionWithLn10sBySid($iSurveyID);
        if (count($oGroupList) == 0) {
            // In future version on the API this should simply return an empty array
            return $this->responseSuccess(
                array('status' => 'No groups found')
            );
        }

        if (is_null($sLanguage)) {
            $sLanguage = $oSurvey->language;
        }

        $aData = array();
        foreach ($oGroupList as $oGroup) {
            $L10ns = $oGroup->questiongroupl10ns[$sLanguage];
            $tmp = array('id' => $oGroup->primaryKey) + $oGroup->attributes;
            $tmp['group_name'] = $L10ns['group_name'];
            $tmp['description'] = $L10ns['description'];
            $tmp['language'] = $sLanguage;
            $aData[] = $tmp;
        }
        return $this->responseSuccess(
            $aData
        );
    }
}
