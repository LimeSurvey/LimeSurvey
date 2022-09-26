<?php

namespace LimeSurvey\Api\Command\V1;

use Exception;
use Permission;
use Question;
use QuestionGroup;
use Survey;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\ApiSession;

// Todo: Test. This command has not been tested.

class QuestionImport implements CommandInterface
{
    /**
     * Run survey question import command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('surveyID');
        $iGroupID = (int) $request->getData('groupID');
        $sImportData = (string) $request->getData('importData');
        $sImportDataType = (string) $request->getData('importDataType');
        $sMandatory = (string) $request->getData('mandatory', 'N');
        $sNewQuestionTitle = (string) $request->getData('newQuestionTitle', null);
        $sNewQuestion = (string) $request->getData('newQuestion', null);
        $sNewQuestionHelp = (string) $request->getData('newQuestionHelp', null);

        $apiSession = new ApiSession();
        $bOldEntityLoaderState = null;
        if ($apiSession->checkKey($sSessionKey)) {
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return new Response(
                    array('status' => 'Error: Invalid survey ID')
                );
            }

            if (
                Permission::model()->hasSurveyPermission(
                    $iSurveyID,
                    'survey',
                    'update'
                )
            ) {
                if ($oSurvey->isActive) {
                    return new Response(
                        array('status' => 'Error:Survey is Active and not editable')
                    );
                }

                $oGroup = QuestionGroup::model()
                    ->findByAttributes(array('gid' => $iGroupID));
                if (!isset($oGroup)) {
                    return new Response(
                        array('status' => 'Error: Invalid group ID')
                    );
                }

                $sGroupSurveyID = $oGroup['sid'];
                if ($sGroupSurveyID != $iSurveyID) {
                    return new Response(
                        array('status' => 'Error: Missmatch in surveyid and groupid')
                    );
                }

                if (!strtolower($sImportDataType) == 'lsq') {
                    return new Response(array('status' => 'Invalid extension'));
                }
                libxml_use_internal_errors(true);
                Yii::app()->loadHelper('admin/import');
                // First save the data to a temporary file
                $sFullFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40) . '.' . $sImportDataType;
                file_put_contents($sFullFilePath, base64_decode(chunk_split($sImportData)));

                if (strtolower($sImportDataType) == 'lsq') {
                    if (\PHP_VERSION_ID < 80000) {
                        $bOldEntityLoaderState = libxml_disable_entity_loader(true);
                        // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
                    }
                    $sXMLdata = file_get_contents($sFullFilePath);
                    $xml = @simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
                    if (!$xml) {
                        unlink($sFullFilePath);
                        if (\PHP_VERSION_ID < 80000) {
                            libxml_disable_entity_loader($bOldEntityLoaderState);
                            // Put back entity loader to its original state, to avoid contagion to other applications on the server
                        }
                        return new Response(array('status' => 'Error: Invalid LimeSurvey question structure XML '));
                    }
                    $aImportResults = XMLImportQuestion($sFullFilePath, $iSurveyID, $iGroupID);
                } else {
                    if (
                        \PHP_VERSION_ID < 80000
                    ) {
                        libxml_disable_entity_loader($bOldEntityLoaderState);
                        // Put back entity loader to its original state, to avoid
                        // contagion to other applications on the server
                    }
                    return new Response(array('status' => 'Really Invalid extension')); //just for symmetry!
                }

                unlink($sFullFilePath);

                if (isset($aImportResults['fatalerror'])) {
                    if (\PHP_VERSION_ID < 80000) {
                        libxml_disable_entity_loader($bOldEntityLoaderState);
                        // Put back entity loader to its original state,
                        // to avoid contagion to other applications on the server
                    }
                    return new Response(array('status' => 'Error: ' . $aImportResults['fatalerror']));
                } else {
                    fixLanguageConsistency($iSurveyID);
                    $iNewqid = $aImportResults['newqid'];

                    $oQuestion = Question::model()
                        ->findByAttributes(array(
                            'sid' => $iSurveyID,
                            'gid' => $iGroupID,
                            'qid' => $iNewqid
                        ));
                    if ($sNewQuestionTitle != null) {
                        $oQuestion->setAttribute('title', $sNewQuestionTitle);
                    }
                    if ($sNewQuestion != '') {
                        $oQuestion->setAttribute('question', $sNewQuestion);
                    }
                    if ($sNewQuestionHelp != '') {
                        $oQuestion->setAttribute('help', $sNewQuestionHelp);
                    }
                    if (in_array($sMandatory, array('Y', 'S', 'N'))) {
                        $oQuestion->setAttribute('mandatory', $sMandatory);
                    } else {
                        $oQuestion->setAttribute('mandatory', 'N');
                    }

                    if (\PHP_VERSION_ID < 80000) {
                        libxml_disable_entity_loader($bOldEntityLoaderState);
                        // Put back entity loader to its original state, to avoid contagion
                        // to other applications on the server
                    }

                    try {
                        $oQuestion->save();
                    } catch (Exception $e) {
                        // no need to throw exception
                    }
                    return new Response((int) $aImportResults['newqid']);
                }
            } else {
                return new Response(array('status' => 'No permission'));
            }
        } else {
            return new Response(array('status' => ApiSession::INVALID_SESSION_KEY));
        }
    }
}
