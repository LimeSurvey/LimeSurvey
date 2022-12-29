<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait
};
use LimeSurvey\Api\Command\Mixin\Accessor\{
    SurveyModelTrait,
    QuestionGroupModelCollectionWithL10nsBySidTrait
};

class QuestionGroupList implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;
    use SurveyModelTrait;
    use QuestionGroupModelCollectionWithL10nsBySidTrait;

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
        $iSurveyID = (int) $request->getData('_id');
        $sLanguage = $request->getData('language');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        $oSurvey = $this->getSurveyModel($iSurveyID);
        if (!isset($oSurvey)) {
            return $this->responseErrorNotFound(
                ['status' => 'Error: Invalid survey ID']
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

        $oGroupList = $this->getQuestionGroupModelCollectionWithL10nsBySid($iSurveyID);
        if (count($oGroupList) == 0) {
            // In future version on the API this should simply return an empty array
            return $this->responseSuccess(
                ['status' => 'No groups found']
            );
        }

        if (is_null($sLanguage)) {
            $sLanguage = $oSurvey->language;
        }

        $aData = [];
        foreach ($oGroupList as $oGroup) {
            $tmp = ['id' => $oGroup->primaryKey] + $oGroup->attributes;
            $L10ns = $oGroup->questiongroupl10ns[$sLanguage] ?? null;
            if ($L10ns) {
                $tmp['group_name'] = $L10ns['group_name'];
                $tmp['description'] = $L10ns['description'];
            }
            $tmp['language'] = $sLanguage;
            $aData[] = $tmp;
        }

        return $this->responseSuccess(
            $aData
        );
    }
}
