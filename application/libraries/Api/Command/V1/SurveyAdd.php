<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyAdd implements CommandInterface
{
    /**
     * Run survey add command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $sSurveyTitle = (string) $request->getData('surveyTitle');
        $sSurveyLanguage = (string) $request->getData('surveyLanguage');
        $sformat = (string) $request->getData('format', 'G');

        $apiSession = new ApiSession;

        \Yii::app()->loadHelper("surveytranslator");
        if ($apiSession->checkKey($sSessionKey)) {
            if (\Permission::model()->hasGlobalPermission('surveys', 'create')) {
                if (
                    $sSurveyTitle == ''
                    || $sSurveyLanguage == ''
                    || !array_key_exists($sSurveyLanguage, getLanguageDataRestricted())
                    || !in_array($sformat, array('A', 'G', 'S'))
                ) {
                    return array('status' => 'Faulty parameters');
                }

                $aInsertData = array(
                    'template' => App()->getConfig('defaulttheme'),
                    'owner_id' => \Yii::app()->session['loginID'],
                    'active' => 'N',
                    'language' => $sSurveyLanguage,
                    'format' => $sformat
                );

                if (!is_null($iSurveyID)) {
                    $aInsertData['wishSID'] = $iSurveyID;
                }

                try {
                    $newSurvey = \Survey::model()->insertNewSurvey($aInsertData);
                    if (!$newSurvey->sid) {
                        return array('status' => 'Creation Failed'); // status are a string, another way to send errors ?
                    }
                    $iNewSurveyid = $newSurvey->sid;

                    $sTitle = html_entity_decode($sSurveyTitle, ENT_QUOTES, "UTF-8");

                    $aInsertData = array(
                        'surveyls_survey_id' => $iNewSurveyid,
                        'surveyls_title' => $sTitle,
                        'surveyls_language' => $sSurveyLanguage,
                    );

                    $langsettings = new \SurveyLanguageSetting();
                    $langsettings->insertNewSurvey($aInsertData);
                    \Permission::model()->giveAllSurveyPermissions(
                        \Yii::app()->session['loginID'],
                        $iNewSurveyid
                    );

                    return new CommandResponse((int) $iNewSurveyid);
                } catch (\Exception $e) {
                    return new CommandResponse(array('status' => $e->getMessage()));
                }
            } else {
                return new CommandResponse(array('status' => 'No permission'));
            }
        } else {
            return new CommandResponse(array('status' => ApiSession::INVALID_SESSION_KEY));
        }
    }
}
