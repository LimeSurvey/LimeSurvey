<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\Accessor\SurveyModel;
use LimeSurvey\Api\Command\Mixin\CommandResponse;


class SurveyPropertiesGet implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use SurveyModel;
    use CommandResponse;

    /**
     * Run survey properties get command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $aSurveySettings = $request->getData('surveySettings', []);

        Yii::app()->loadHelper('surveytranslator');

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
                'surveysettings',
                'read'
            )) !== true
        ) {
            return $response;
        }

        $aBasicDestinationFields = Survey::model()
            ->tableSchema
            ->columnNames;
        if (!empty($aSurveySettings)) {
            $aSurveySettings = array_intersect(
                $aSurveySettings,
                $aBasicDestinationFields
            );
        } else {
            $aSurveySettings = $aBasicDestinationFields;
        }

        if (empty($aSurveySettings)) {
            return $this->responseErrorBadRequest(
                ['status' => 'No valid Data']
            );
        }

        $aResult = [];
        foreach ($aSurveySettings as $sPropertyName) {
            $aResult[$sPropertyName] = $oSurvey->$sPropertyName;
        }

        return $this->responseSuccess(
            $aResult,
        );
    }
}
