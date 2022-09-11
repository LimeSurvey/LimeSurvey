<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SurveyQuestionPropertiesGet implements CommandInterface
{
    /**
     * Run survey question delete command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iQuestionID = (int) $request->getData('questionID');
        $aQuestionSettings = $request->getData('questionSettings');
        $sLanguage = (string) $request->getData('language');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sSessionKey)) {
            \Yii::app()->loadHelper("surveytranslator");
            $oQuestion = \Question::model()->findByAttributes(array('qid' => $iQuestionID));
            if (!isset($oQuestion)) {
                return new CommandResponse(array('status' => 'Error: Invalid questionid'));
            }

            $iSurveyID = $oQuestion->sid;

            if (\Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'read')) {
                if (is_null($sLanguage)) {
                    $sLanguage = \Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return new CommandResponse(array('status' => 'Error: Invalid language'));
                }

                $oQuestion = \Question::model()->with('questionl10ns')
                    ->find(
                        't.qid = :qid and questionl10ns.language = :language',
                        array(':qid' => $iQuestionID, ':language' => $sLanguage)
                    );
                if (!isset($oQuestion)) {
                    return new CommandResponse(array('status' => 'Error: Invalid questionid'));
                }

                $aBasicDestinationFields = \Question::model()->tableSchema->columnNames;
                array_push($aBasicDestinationFields, 'available_answers');
                array_push($aBasicDestinationFields, 'subquestions');
                array_push($aBasicDestinationFields, 'attributes');
                array_push($aBasicDestinationFields, 'attributes_lang');
                array_push($aBasicDestinationFields, 'answeroptions');
                array_push($aBasicDestinationFields, 'defaultvalue');
                if (!empty($aQuestionSettings)) {
                    $aQuestionSettings = array_intersect(
                        $aQuestionSettings,
                        $aBasicDestinationFields
                    );
                } else {
                    $aQuestionSettings = $aBasicDestinationFields;
                }

                if (empty($aQuestionSettings)) {
                    return new CommandResponse(array('status' => 'No valid Data'));
                }

                $aResult = array();
                foreach ($aQuestionSettings as $sPropertyName) {
                    if ($sPropertyName == 'available_answers' || $sPropertyName == 'subquestions') {
                        $oSubQuestions = \Question::model()->with('questionl10ns')
                            ->findAll(
                                't.parent_qid = :parent_qid and questionl10ns.language = :language',
                                array(':parent_qid' => $iQuestionID, ':language' => $sLanguage),
                                array('order' => 'title')
                            );

                        if (count($oSubQuestions) > 0) {
                            $aData = array();
                            foreach ($oSubQuestions as $oSubQuestion) {
                                if ($sPropertyName == 'available_answers') {
                                    $aData[$oSubQuestion['title']] = array_key_exists(
                                        $sLanguage,
                                        $oSubQuestion->questionl10ns
                                    ) ? $oSubQuestion->questionl10ns[$sLanguage]->question : '';
                                } else {
                                    $aData[$oSubQuestion['qid']]['title'] = $oSubQuestion['title'];
                                    $aData[$oSubQuestion['qid']]['question'] = array_key_exists(
                                        $sLanguage,
                                        $oSubQuestion->questionl10ns
                                    ) ? $oSubQuestion->questionl10ns[$sLanguage]->question : '';
                                    $aData[$oSubQuestion['qid']]['scale_id'] = $oSubQuestion['scale_id'];
                                }
                            }

                            $aResult[$sPropertyName] = $aData;
                        } else {
                            $aResult[$sPropertyName] = 'No available answers';
                        }
                    } elseif ($sPropertyName == 'attributes') {

                        $questionAttributeHelper = new \LimeSurvey\Models\Services\QuestionAttributeHelper();
                        $questionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, null, null, true);
                        $data = [];
                        foreach ($questionAttributes as $attributeName => $attributeData) {
                            if (empty($attributeData['i18n'])) {
                                $data[$attributeName] = $attributeData['value'];
                            }
                        }
                        if (count($data) > 0) {
                            ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
                            $aResult['attributes'] = $data;
                        } else {
                            $aResult['attributes'] = 'No available attributes';
                        }
                    } elseif ($sPropertyName == 'attributes_lang') {
                        $questionAttributeHelper = new \LimeSurvey\Models\Services\QuestionAttributeHelper();
                        $questionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, $sLanguage, null, true);
                        $data = [];
                        foreach ($questionAttributes as $attributeName => $attributeData) {
                            if (!empty($attributeData['i18n'])) {
                                $data[$attributeName] = $attributeData[$sLanguage]['value'];
                            }
                        }
                        if (count($data) > 0) {
                            ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
                            $aResult['attributes_lang'] = $data;
                        } else {
                            $aResult['attributes_lang'] = 'No available attributes';
                        }
                    } elseif ($sPropertyName == 'answeroptions') {
                        $oAttributes = \Answer::model()->with('answerl10ns')
                            ->findAll(
                                't.qid = :qid and answerl10ns.language = :language',
                                array(':qid' => $iQuestionID, ':language' => $sLanguage),
                                array('order' => 'sortorder')
                            );
                        if (count($oAttributes) > 0) {
                            $aData = array();
                            foreach ($oAttributes as $oAttribute) {
                                $aData[$oAttribute['code']]['answer'] = array_key_exists(
                                    $sLanguage,
                                    $oAttribute->answerl10ns
                                ) ? $oAttribute->answerl10ns[$sLanguage]->answer : '';
                                $aData[$oAttribute['code']]['assessment_value'] = $oAttribute['assessment_value'];
                                $aData[$oAttribute['code']]['scale_id'] = $oAttribute['scale_id'];
                                $aData[$oAttribute['code']]['order'] = $oAttribute['sortorder'];
                            }
                            $aResult['answeroptions'] = $aData;
                        } else {
                            $aResult['answeroptions'] = 'No available answer options';
                        }
                    } elseif ($sPropertyName == 'answeroptions_multiscale') {
                        $oAttributes = \Answer::model()->findAllByAttributes(
                            array('qid' => $iQuestionID, 'language' => $sLanguage),
                            array('order' => 'sortorder')
                        );
                        if (count($oAttributes) > 0) {
                            $aData = array();
                            foreach ($oAttributes as $oAttribute) {
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['code'] = $oAttribute['code'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['answer'] = $oAttribute['answer'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['assessment_value'] = $oAttribute['assessment_value'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['scale_id'] = $oAttribute['scale_id'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['order'] = $oAttribute['sortorder'];
                            }
                            $aResult['answeroptions'] = $aData;
                        } else {
                            $aResult['answeroptions'] = 'No available answer options';
                        }
                    } elseif ($sPropertyName == 'defaultvalue') {
                        $aResult['defaultvalue'] = \DefaultValue::model()->with('defaultvaluel10ns')
                            ->find(
                                'qid = :qid AND defaultvaluel10ns.language = :language',
                                array(':qid' => $iQuestionID, ':language' => $sLanguage)
                            )
                            ->defaultvalue;
                    } else {
                        $aResult[$sPropertyName] = $oQuestion->$sPropertyName;
                    }
                }
                return new CommandResponse($aResult);
            } else {
                return new CommandResponse(array('status' => 'No permission'));
            }
        } else {
            return new CommandResponse(array('status' => ApiSession::INVALID_SESSION_KEY));
        }
    }
}
