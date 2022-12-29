<?php

namespace LimeSurvey\Api\Command\V1;

use Exception;
use Permission;
use Survey;
use SurveyLanguageSetting;
use Yii;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Response\{
    Response,
    Status\StatusSuccess
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponse,
    Auth\AuthSession,
    Auth\AuthPermission
};

class SurveyAdd implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;

    /**
     * Run survey add command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = $request->getData('surveyID');
        $sSurveyTitle = (string) $request->getData('surveyTitle');
        $sSurveyLanguage = (string) $request->getData('surveyLanguage');
        $sformat = (string) $request->getData('format', 'G');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'survey',
                'create'
            )
            ) !== true
        ) {
            return $response;
        }

        Yii::app()->loadHelper("surveytranslator");

        if (
            $sSurveyTitle == ''
            || $sSurveyLanguage == ''
            || !array_key_exists($sSurveyLanguage, getLanguageDataRestricted())
            || !in_array($sformat, ['A', 'G', 'S'])
        ) {
            return $this->responseErrorBadRequest(
                ['status' => 'Faulty parameters']
            );
        }

        $aInsertData = [
            'template' => App()->getConfig('defaulttheme'),
            'owner_id' => Yii::app()->session['loginID'],
            'active' => 'N',
            'language' => $sSurveyLanguage,
            'format' => $sformat
        ];

        if (!is_null($iSurveyID)) {
            $aInsertData['wishSID'] = $iSurveyID;
        }

        try {
            $newSurvey = Survey::model()->insertNewSurvey($aInsertData);
            if (!$newSurvey->sid) {
                // status are a string, another way to send errors ?
                return new Response(
                    ['status' => 'Creation Failed']
                );
            }
            $iNewSurveyid = $newSurvey->sid;

            $sTitle = html_entity_decode($sSurveyTitle, ENT_QUOTES, "UTF-8");

            $aInsertData = [
                'surveyls_survey_id' => $iNewSurveyid,
                'surveyls_title' => $sTitle,
                'surveyls_language' => $sSurveyLanguage,
            ];

            $langsettings = new SurveyLanguageSetting();
            $langsettings->insertNewSurvey($aInsertData);
            Permission::model()->giveAllSurveyPermissions(
                Yii::app()->session['loginID'],
                $iNewSurveyid
            );

            return $this->responseSuccess(
                (int) $iNewSurveyid,
                new StatusSuccess()
            );
        } catch (Exception $e) {
            return $this->responseException($e);
        }
    }
}
