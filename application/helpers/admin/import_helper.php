<?php

/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

use LimeSurvey\Helpers\questionHelper;
use LimeSurvey\Models\Services\SurveyAccessModeService;

/**
 * This function imports a LimeSurvey .lsg question group XML file
 *
 * @param string  $sFullFilePath The full filepath of the uploaded file
 * @param integer $iNewSID       The new survey ID - the page will always be added after the last page in the survey
 * @param boolean $bTranslateLinksFields
 *
 * @return mixed
 */
function XMLImportGroup($sFullFilePath, $iNewSID, $bTranslateLinksFields)
{
    $sBaseLanguage         = Survey::model()->findByPk($iNewSID)->language;
    if (\PHP_VERSION_ID < 80000) {
        $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
    }

    $sXMLdata              = file_get_contents($sFullFilePath);
    $xml                   = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);


    if ($xml === false || $xml->LimeSurveyDocType != 'Group') {
        throw new Exception('This is not a valid LimeSurvey group structure XML file.');
    }

    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements = array();
    $aQuestionCodeReplacements = array();
    $results['defaultvalues'] = 0;
    $results['answers'] = 0;
    $results['question_attributes'] = 0;
    $results['subquestions'] = 0;
    $results['conditions'] = 0;
    $results['groups'] = 0;
    $results['importwarnings'] = [];

    $importlanguages = array();
    foreach ($xml->languages->language as $language) {
        $importlanguages[] = (string) $language;
    }

    if (!in_array($sBaseLanguage, $importlanguages)) {
        $results['fatalerror'] = gT("The languages of the imported group file must at least include the base language of this survey.");
        return $results;
    }

    // Import group table ===================================================================================
    $iGroupOrder = Yii::app()->db->createCommand()->select('MAX(group_order)')->from('{{groups}}')->where('sid=:sid', array(':sid' => $iNewSID))->queryScalar();
    if ($iGroupOrder === false) {
        $iNewGroupOrder = 0;
    } else {
        $iNewGroupOrder = $iGroupOrder + 1;
    }

    foreach ($xml->groups->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key => $value) {
            $insertdata[(string) $key] = (string) $value;
        }
        $iOldSID = $insertdata['sid'];
        $insertdata['sid'] = $iNewSID;
        $insertdata['group_order'] = $iNewGroupOrder;
        $oldgid = $insertdata['gid'];
        unset($insertdata['gid']); // save the old qid
        $aDataL10n = array();

        if (!isset($xml->group_l10ns->rows->row)) {
            $aDataL10n['group_name'] = $insertdata['group_name'];
            $aDataL10n['description'] = $insertdata['description'];
            $aDataL10n['language'] = $insertdata['language'];
            unset($insertdata['group_name']);
            unset($insertdata['description']);
            unset($insertdata['language']);
        }
        if (!isset($aGIDReplacements[$oldgid])) {
            $questionGroup = new QuestionGroup();
            $questionGroup->sid = $insertdata['sid'];
            $questionGroup->group_order = $insertdata['group_order'];
            $questionGroup->randomization_group = $insertdata['randomization_group'];
            $questionGroup->grelevance = $insertdata['grelevance'];
            if (!$questionGroup->save()) {
                throw new Exception(gT("Error") . ": Failed to insert data [3]<br />");
            }

            $newgid = $questionGroup->gid;

            $aGIDReplacements[$oldgid] = $newgid; // add old and new qid to the mapping array
            $results['groups']++;
        }
        if (!empty($aDataL10n)) {
            $aDataL10n['gid'] = $aGIDReplacements[$oldgid];
            $oQuestionGroupL10n = new QuestionGroupL10n();
            $oQuestionGroupL10n->setAttributes($aDataL10n, false);
            $oQuestionGroupL10n->save();
        }
    }

    if (isset($xml->group_l10ns->rows->row)) {
        foreach ($xml->group_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            // TODO: Should this depend on $bTranslateLinksFields?
            $insertdata['group_name'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['group_name']);
            $insertdata['description'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['description']);
            if (isset($aGIDReplacements[$insertdata['gid']])) {
                $insertdata['gid'] = $aGIDReplacements[$insertdata['gid']];
            } else {
                continue; //Skip invalid group ID
            }
            $oQuestionGroupL10n = new QuestionGroupL10n();
            $oQuestionGroupL10n->setAttributes($insertdata, false);
            $oQuestionGroupL10n->save();
        }
    }

    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)

    /** @var Question[] */
    $importedQuestions = [];
    $results['questions'] = 0;
    if (isset($xml->questions)) {
        foreach ($xml->questions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($aGIDReplacements[$insertdata['gid']])) {
                // Skip questions with invalid group id
                continue;
            }
            if (!isset($insertdata['mandatory']) || trim($insertdata['mandatory']) == '') {
                $insertdata['mandatory'] = 'N';
            }
            $iOldSID = $insertdata['sid'];
            $insertdata['sid'] = $iNewSID;
            $insertdata['gid'] = $aGIDReplacements[$insertdata['gid']];
            $iOldQID = $insertdata['qid']; // save the old qid
            unset($insertdata['qid']);
            if (!isset($xml->question_l10ns->rows->row)) {
                if ($bTranslateLinksFields) {
                    $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                    $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
                }
                $oQuestionL10n = new QuestionL10n();
                $oQuestionL10n->question = $insertdata['question'];
                $oQuestionL10n->help = $insertdata['help'];
                $oQuestionL10n->language = $insertdata['language'];
                unset($insertdata['question']);
                unset($insertdata['help']);
                unset($insertdata['language']);
            }

            if (!$bTranslateLinksFields) {
                $sScenario = 'archiveimport';
            } else {
                $sScenario = 'import';
            }

            $oQuestion = new Question($sScenario);
            $oQuestion->setAttributes($insertdata, false);

            if (!isset($aQIDReplacements[$iOldQID])) {
                // Try to fix question title for valid question code enforcement
                if (!$oQuestion->validate(array('title'))) {
                    $sOldTitle = $oQuestion->title;
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', (string) $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'q' . $sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                $index = 0;
                $rand = mt_rand(0, 1024);
                while (!$oQuestion->validate(array('title'))) {
                    $sNewTitle = 'r' . $rand . 'q' . $index;
                    $index++;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;
                    if ($attempts > 10) {
                        throw new Exception(gT("Error") . ": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    throw new Exception(gT("Error while saving: ") . print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                $results['questions']++;
                $importedQuestions[$aQIDReplacements[$iOldQID]] = $oQuestion;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a question_l10ns section.
            if (!$bTranslateLinksFields && !isset($xml->question_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->question)) {
                    $results['importwarnings'][] = sprintf(gT("Question %s has outdated links."), $oQuestion->title);
                }
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->help)) {
                    $results['importwarnings'][] = sprintf(gT("Help text for question %s has outdated links."), $oQuestion->title);
                }
            }

            if (isset($oQuestionL10n)) {
                $oQuestionL10n->qid = $aQIDReplacements[$iOldQID];
                $oQuestionL10n->save();
                unset($oQuestionL10n);
            }
            // Set a warning if question title was updated
            if (isset($sNewTitle) && isset($sOldTitle)) {
                $results['importwarnings'][] = sprintf(gT("Question code %s was updated to %s."), $sOldTitle, $sNewTitle);
                $aQuestionCodeReplacements[$sOldTitle] = $sNewTitle;
                unset($sNewTitle);
                unset($sOldTitle);
            }
        }
    }

    // Import subquestions -------------------------------------------------------
    if (isset($xml->subquestions)) {
        foreach ($xml->subquestions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }

            if ($insertdata['gid'] == 0) {
                continue;
            }
            if (!isset($insertdata['mandatory']) || trim($insertdata['mandatory']) == '') {
                $insertdata['mandatory'] = 'N';
            }
            $iOldSID = $insertdata['sid'];
            $insertdata['sid'] = $iNewSID;
            $insertdata['gid'] = $aGIDReplacements[(int) $insertdata['gid']];
            $iOldQID = (int) $insertdata['qid'];
            unset($insertdata['qid']); // save the old qid
            $insertdata['parent_qid'] = $aQIDReplacements[(int) $insertdata['parent_qid']]; // remap the parent_qid
            if (!isset($insertdata['help'])) {
                $insertdata['help'] = '';
            }            // now translate any links
            if (!isset($xml->question_l10ns->rows->row)) {
                if ($bTranslateLinksFields) {
                    $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                    $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
                }
                $oQuestionL10n = new QuestionL10n();
                $oQuestionL10n->question = $insertdata['question'];
                $oQuestionL10n->help = $insertdata['help'];
                $oQuestionL10n->language = $insertdata['language'];
                unset($insertdata['question']);
                unset($insertdata['help']);
                unset($insertdata['language']);
            }
            if (!$bTranslateLinksFields) {
                $sScenario = 'archiveimport';
            } else {
                $sScenario = 'import';
            }

            $oQuestion = new Question($sScenario);
            $oQuestion->setAttributes($insertdata, false);

            if (!isset($aQIDReplacements[$iOldQID])) {
                switchMSSQLIdentityInsert('questions', false);
                // Try to fix question title for valid question code enforcement
                if (!$oQuestion->validate(array('title'))) {
                    $sOldTitle = $oQuestion->title;
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', (string) $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'sq' . $sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                while (!$oQuestion->validate(array('title'))) {
                    if (!isset($index)) {
                        $index = 0;
                        $rand = mt_rand(0, 1024);
                    } else {
                        $index++;
                    }

                    $sNewTitle = 'r' . $rand . 'sq' . $index;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;

                    if ($attempts > 10) {
                        throw new Exception(gT("Error") . ": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    throw new Exception(gT("Error while saving: ") . print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                ;
                $results['questions']++;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a question_l10ns section.
            if (!$bTranslateLinksFields && !isset($xml->question_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->question)) {
                    $parentQuestion = $importedQuestions[$insertdata['parent_qid']];
                    $results['importwarnings'][] = sprintf(gT("Subquestion %s of question %s has outdated links."), $oQuestion->title, $parentQuestion->title);
                }
            }

            if (isset($oQuestionL10n)) {
                $oQuestionL10n->qid = $aQIDReplacements[$iOldQID];
                $oQuestionL10n->save();
                unset($oQuestionL10n);
            }

            // Set a warning if question title was updated
            if (isset($sNewTitle) && isset($sOldTitle)) {
                $results['importwarnings'][] = sprintf(gT("Title of subquestion %s was updated to %s."), $sOldTitle, $sNewTitle); // Maybe add the question title ?
                $aQuestionCodeReplacements[$sOldTitle] = $sNewTitle;
                unset($sNewTitle);
                unset($sOldTitle);
            }
        }
    }


    //  Import question_l10ns
    if (isset($xml->question_l10ns->rows->row)) {
        foreach ($xml->question_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            // TODO: Should this depend on $bTranslateLinksFields?
            $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
            $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[$insertdata['qid']];
            } else {
                continue; //Skip invalid group ID
            }
            $oQuestionL10n = new QuestionL10n();
            $oQuestionL10n->setAttributes($insertdata, false);
            $oQuestionL10n->save();
        }
    }

    // Import answers ------------------------------------------------------------
    if (isset($xml->answers)) {
        foreach ($xml->answers->rows->row as $row) {
            $insertdata = array();

            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (isset($xml->answer_l10ns->rows->row)) {
                $iOldAID = $insertdata['aid'];
                unset($insertdata['aid']);
            }
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                continue;
            }

            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the parent_qid

            if (!isset($xml->answer_l10ns->rows->row)) {
                $oAnswerL10n = new AnswerL10n();
                $oAnswerL10n->answer = $insertdata['answer'];
                $oAnswerL10n->language = $insertdata['language'];
                unset($insertdata['answer']);
                unset($insertdata['language']);
            }

            $oAnswer = new Answer();
            $oAnswer->setAttributes($insertdata, false);
            if ($oAnswer->save() && isset($xml->answer_l10ns->rows->row)) {
                $aAIDReplacements[$iOldAID] = $oAnswer->aid;
            }
            $results['answers']++;
            if (isset($oAnswerL10n)) {
                $oAnswer = Answer::model()->findByAttributes(['qid' => $insertdata['qid'], 'code' => $insertdata['code'], 'scale_id' => $insertdata['scale_id']]);
                $oAnswerL10n->aid = $oAnswer->aid;
                $oAnswerL10n->save();
                unset($oAnswerL10n);
            }
        }
    }

    //  Import answer_l10ns
    if (isset($xml->answer_l10ns->rows->row)) {
        foreach ($xml->answer_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            if (isset($aAIDReplacements[$insertdata['aid']])) {
                $insertdata['aid'] = $aAIDReplacements[$insertdata['aid']];
            } else {
                continue; //Skip invalid answer ID
            }
            $oAnswerL10n = new AnswerL10n();
            $oAnswerL10n->setAttributes($insertdata, false);
            $oAnswerL10n->save();
        }
    }


    // Import questionattributes --------------------------------------------------------------
    if (isset($xml->question_attributes)) {
        $aAllAttributes = questionHelper::getAttributesDefinitions();

        /** @var array<integer,array<string,mixed>> List of "answer order" related attributes, grouped by qid */
        $answerOrderAttributes = [];
        foreach ($xml->question_attributes->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['qaid']);
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                // Skip questions with invalid group id
                continue;
            }
            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the parent_qid

            // Question theme was previously stored as a question attribute ('question_template'), but now it
            // is a normal attribute of the Question model. So we must check if the imported question has the
            // 'question_template' attribute and use it for overriding 'question_theme_name' instead of saving
            // as QuestionAttribute.
            if ($insertdata['attribute'] == 'question_template') {
                $oQuestion = Question::model()->findByPk($insertdata['qid']);
                if (!empty($oQuestion)) {
                    $oQuestion->question_theme_name = $insertdata['value'];
                    $oQuestion->save();
                }
                continue;
            }

            // Keep "answer order" related attributes in an array to process later (because we need to combine two attributes)
            if (
                $insertdata['attribute'] == 'alphasort'
                || (
                    $insertdata['attribute'] == 'random_order'
                    && in_array($importedQuestions[$insertdata['qid']]->type, ['!', 'L', 'O', 'R'])
                )
            ) {
                $answerOrderAttributes[$insertdata['qid']][$insertdata['attribute']] = $insertdata['value'];
                continue;
            }

            if (
                $iDBVersion < 156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) &&
                $aAllAttributes[$insertdata['attribute']]['i18n']
            ) {
                foreach ($importlanguages as $sLanguage) {
                    $insertdata['language'] = $sLanguage;
                    App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                }
            } else {
                App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
            }
            $results['question_attributes']++;
        }

        // Process "answer order" attributes
        foreach ($answerOrderAttributes as $importedQid => $questionAttributes) {
            if (!empty($questionAttributes['random_order'])) {
                $insertdata = [
                    'qid' => $importedQid,
                    'attribute' => 'answer_order',
                    'value' => 'random',
                ];
                App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                $results['question_attributes']++;
                continue;
            }
            if (!empty($questionAttributes['alphasort'])) {
                $insertdata = [
                    'qid' => $importedQid,
                    'attribute' => 'answer_order',
                    'value' => 'alphabetical',
                ];
                App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                $results['question_attributes']++;
            }
        }
    }


    // Import defaultvalues ------------------------------------------------------
    importDefaultValues($xml, $importlanguages, $aQIDReplacements, $results);

    // Import conditions --------------------------------------------------------------
    if (isset($xml->conditions)) {
        foreach ($xml->conditions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            // replace the qid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this condition is orphan -> error, skip this record)
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[$insertdata['qid']]; // remap the qid
            } else {
                // a problem with this answer record -> don't consider
                continue;
            }
            if (isset($aQIDReplacements[$insertdata['cqid']])) {
                $insertdata['cqid'] = $aQIDReplacements[$insertdata['cqid']]; // remap the qid
            } else {
                // a problem with this answer record -> don't consider
                continue;
            }

            list($oldcsid, $oldcgid, $oldqidanscode) = explode("X", (string) $insertdata["cfieldname"], 3);

            if ($oldcgid != $oldgid) {
                // this means that the condition is in another group (so it should not have to be been exported -> skip it
                continue;
            }

            unset($insertdata["cid"]);

            // recreate the cfieldname with the new IDs
            if (preg_match("/^\+/", $oldcsid)) {
                $newcfieldname = '+' . $iNewSID . "X" . $newgid . "X" . $insertdata["cqid"] . substr($oldqidanscode, strlen((string) $iOldQID));
            } else {
                $newcfieldname = $iNewSID . "X" . $newgid . "X" . $insertdata["cqid"] . substr($oldqidanscode, strlen((string) $iOldQID));
            }

            $insertdata["cfieldname"] = $newcfieldname;
            if (trim((string) $insertdata["method"]) == '') {
                $insertdata["method"] = '==';
            }

            // now translate any links
            Yii::app()->db->createCommand()->insert('{{conditions}}', $insertdata);
            $results['conditions']++;
        }
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);

    if (count($aQuestionCodeReplacements)) {
        array_unshift(
            $results['importwarnings'],
            "<span class='warningtitle'>"
            . gT('Attention: Several question codes were updated. Please check these carefully as the update  may not be perfect with customized expressions.')
            . '</span>'
        );
    }

    $results['newgid'] = $newgid;
    $results['labelsets'] = 0;
    $results['labels'] = 0;

    if (\PHP_VERSION_ID < 80000) {
        libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
    }
    return $results;
}

/**
 * This function imports a LimeSurvey .lsq question XML file
 *
 * @param string $sFullFilePath The full filepath of the uploaded file
 * @param integer $iNewSID The new survey ID
 * @param $iNewGID
 * @param bool[] $options
 * @return array
 * @throws CException
 */
function XMLImportQuestion($sFullFilePath, $iNewSID, $iNewGID, $options = array('autorename' => false,'translinkfields' => true))
{
    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $sXMLdata = file_get_contents($sFullFilePath);
    $xml = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
    if ($xml->LimeSurveyDocType != 'Question') {
        throw new \CHttpException(500, 'This is not a valid LimeSurvey question structure XML file.');
    }
    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements = array();

    $results['defaultvalues'] = 0;
    $results['answers'] = 0;
    $results['question_attributes'] = 0;
    $results['subquestions'] = 0;

    $importlanguages = array();
    foreach ($xml->languages->language as $language) {
        $importlanguages[] = (string) $language;
    }

    if (!in_array($sBaseLanguage, $importlanguages)) {
        $results['fatalerror'] = gT("The languages of the imported question file must at least include the base language of this survey.");
        return $results;
    }

    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)

    $query = "SELECT MAX(question_order) AS maxqo FROM {{questions}} WHERE sid=$iNewSID AND gid=$iNewGID";
    $res = Yii::app()->db->createCommand($query)->query();
    $resrow = $res->read();
    $newquestionorder = $resrow['maxqo'] + 1;
    if (is_null($newquestionorder)) {
        $newquestionorder = 0;
    } else {
        $newquestionorder++;
    }


    $aLanguagesSupported = array();
    foreach ($xml->languages->language as $language) {
        $aLanguagesSupported[] = (string) $language;
    }

    foreach ($xml->questions->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key => $value) {
            $insertdata[(string) $key] = (string) $value;
        }

        $iOldSID = $insertdata['sid'];
        $insertdata['sid'] = $iNewSID;
        $insertdata['gid'] = $iNewGID;
        $insertdata['question_order'] = $newquestionorder;
        $iOldQID = $insertdata['qid']; // save the old qid
        unset($insertdata['qid']);

        // now translate any links
        if (!isset($xml->question_l10ns->rows->row)) {
            // TODO: Should this depend on $options['translinkfields']?
            $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
            $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
            // @todo Should only be executed based on dbversion of the file, otherwise this and possible in new format could be imported at the same time
            $oQuestionL10n = new QuestionL10n();
            $oQuestionL10n->question = $insertdata['question'];
            $oQuestionL10n->help = $insertdata['help'];
            $oQuestionL10n->language = $insertdata['language'];
            unset($insertdata['question']);
            unset($insertdata['help']);
            unset($insertdata['language']);
        }

        $oQuestion = new Question('import');
        $oQuestion->setAttributes($insertdata, false);

        if (!isset($aQIDReplacements[$iOldQID])) {
            if (!$oQuestion->validate(array('title')) && $options['autorename']) {
                if (isset($sNewTitle)) {
                    $oQuestion->title = $sNewTitle;
                } else {
                    $sOldTitle = $oQuestion->title;
                    $oQuestion->title = $sNewTitle = $oQuestion->getNewTitle();
                    if (!$sNewTitle) {
                        $results['fatalerror'] = CHtml::errorSummary(
                            $oQuestion,
                            gT("The question could not be imported for the following reasons:")
                        );
                        return $results;
                    }
                    $results['importwarnings'][] = sprintf(
                        gT("Question code %s was updated to %s."),
                        $sOldTitle,
                        $sNewTitle
                    );
                    unset($sNewTitle);
                    unset($sOldTitle);
                }
            }
            if (isset($insertdata['qid'])) {
                switchMSSQLIdentityInsert('questions', true);
            }

            if (!$oQuestion->save()) {
                $results['fatalerror'] = CHtml::errorSummary(
                    $oQuestion,
                    gT("The question could not be imported for the following reasons:")
                );
                return $results;
            }

            switchMSSQLIdentityInsert('questions', false);
            $aQIDReplacements[$iOldQID] = $oQuestion->qid;

            $newqid = $oQuestion->qid;
        }

        $results['questions'] = isset($results['questions']) ? $results['questions'] + 1 : 1;

        if (isset($oQuestionL10n)) {
            $oQuestionL10n->qid = $aQIDReplacements[$iOldQID];
            $oQuestionL10n->save();
            unset($oQuestionL10n);
        }
    }

    // Import subquestions -------------------------------------------------------
    if (isset($xml->subquestions)) {
        foreach ($xml->subquestions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }

            if (!isset($xml->question_l10ns->rows->row)) {
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }
            }
            if ($insertdata['gid'] == 0) {
                continue;
            }
            if (!isset($insertdata['mandatory']) || trim($insertdata['mandatory']) == '') {
                $insertdata['mandatory'] = 'N';
            }
            $iOldSID = $insertdata['sid'];
            $insertdata['sid'] = $iNewSID;
            $insertdata['gid'] = $iNewGID;
            $iOldQID = (int) $insertdata['qid'];
            unset($insertdata['qid']); // save the old qid
            $insertdata['parent_qid'] = $aQIDReplacements[(int) $insertdata['parent_qid']]; // remap the parent_qid
            if (!isset($insertdata['help'])) {
                $insertdata['help'] = '';
            }            // now translate any links
            if (!isset($xml->question_l10ns->rows->row)) { //when does subquestions are stored in xml file in tag "question_l10ns"?
                if ($options['translinkfields']) {
                    $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                    $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
                }
                $oQuestionL10n = new QuestionL10n();
                $oQuestionL10n->question = $insertdata['question'];
                $oQuestionL10n->help = $insertdata['help'];
                $oQuestionL10n->language = $insertdata['language'];
                unset($insertdata['question']);
                unset($insertdata['help']);
                unset($insertdata['language']);
            } elseif (isset($insertdata['question'])) {
                $oQuestionL10n = new QuestionL10n();
                $oQuestionL10n->question = $insertdata['question'];
                $oQuestionL10n->help = $insertdata['help'];
                $oQuestionL10n->language = $insertdata['language'];
            }
            if (!$options['autorename']) {
                $sScenario = 'archiveimport';
            } else {
                $sScenario = 'import';
            }

            $oQuestion = new Question($sScenario);
            $oQuestion->setAttributes($insertdata, false);

            if (!isset($aQIDReplacements[$iOldQID])) {
                // Try to fix question title for valid question code enforcement
                if (!$oQuestion->validate(array('title'))) {
                    $sOldTitle = $oQuestion->title;
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', (string) $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'sq' . $sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                while (!$oQuestion->validate(array('title'))) {
                    if (!isset($index)) {
                        $index = 0;
                        $rand = mt_rand(0, 1024);
                    } else {
                        $index++;
                    }

                    $sNewTitle = 'r' . $rand . 'sq' . $index;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;

                    if ($attempts > 10) {
                        throw new Exception(gT("Error") . ": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    throw new Exception(gT("Error while saving: ") . print_r($oQuestion->errors, true));
                }

                $aQIDReplacements[$iOldQID] = $oQuestion->qid;

                $results['questions']++;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a question_l10ns section.
            if (!$options['translinkfields'] && !isset($xml->question_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->question)) {
                    $results['importwarnings'][] = sprintf(gT("Subquestion %s has outdated links."), $oQuestion->title);
                }
            }

            if (isset($oQuestionL10n)) {
                $oQuestionL10n->qid = $aQIDReplacements[$iOldQID];
                $oQuestionL10n->save();
                unset($oQuestionL10n);
            }

            // Set a warning if question title was updated
            if (isset($sNewTitle) && isset($sOldTitle)) {
                $results['importwarnings'][] = sprintf(gT("Title of subquestion %s was updated to %s."), $sOldTitle, $sNewTitle); // Maybe add the question title ?
                $aQuestionCodeReplacements[$sOldTitle] = $sNewTitle;
                unset($sNewTitle);
                unset($sOldTitle);
            }
        }
    }

    //  Import question_l10ns
    if (isset($xml->question_l10ns->rows->row)) {
        foreach ($xml->question_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            // TODO: Should this depend on $options['translinkfields']?
            $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
            $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[$insertdata['qid']];
            } else {
                continue; //Skip invalid question ID
            }
            $oQuestionL10n = new QuestionL10n();
            $oQuestionL10n->setAttributes($insertdata, false);
            $oQuestionL10n->save();
        }
    }

    // Import answers ------------------------------------------------------------
    if (isset($xml->answers)) {
        foreach ($xml->answers->rows->row as $row) {
            $insertdata = array();

            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (isset($xml->answer_l10ns->rows->row)) {
                $iOldAID = $insertdata['aid'];
                unset($insertdata['aid']);
            }
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                continue;
            }

            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the parent_qid

            if (!isset($xml->answer_l10ns->rows->row)) {
                // now translate any links
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }
                if ($options['translinkfields']) {
                    $insertdata['answer'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['answer']);
                }
                $oAnswerL10n = new AnswerL10n();
                $oAnswerL10n->answer = $insertdata['answer'];
                $oAnswerL10n->language = $insertdata['language'];
                unset($insertdata['answer']);
                unset($insertdata['language']);
            }

            $oAnswer = new Answer();
            $oAnswer->setAttributes($insertdata, false);
            if ($oAnswer->save() && isset($xml->answer_l10ns->rows->row)) {
                $aAIDReplacements[$iOldAID] = $oAnswer->aid;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a answer_l10ns section.
            if (!$options['translinkfields'] && !isset($xml->answer_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oAnswerL10n->answer)) {
                    $results['importwarnings'][] = sprintf(gT("Answer option %s has outdated links."), $insertdata['code']);
                }
            }

            $results['answers']++;
            if (isset($oAnswerL10n)) {
                $oAnswer = Answer::model()->findByAttributes(
                    [
                        'qid' => $insertdata['qid'],
                        'code' => $insertdata['code'],
                        'scale_id' => $insertdata['scale_id']
                    ]
                );
                $oAnswerL10n->aid = $oAnswer->aid;
                $oAnswerL10n->save();
                unset($oAnswerL10n);
            }
        }
    }

    //  Import answer_l10ns
    if (isset($xml->answer_l10ns->rows->row)) {
        foreach ($xml->answer_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            if ($options['translinkfields']) {
                $insertdata['answer'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['answer']);
            }
            if (isset($aAIDReplacements[$insertdata['aid']])) {
                $insertdata['aid'] = $aAIDReplacements[$insertdata['aid']];
            } else {
                continue; //Skip invalid answer ID
            }
            $oAnswerL10n = new AnswerL10n();
            $oAnswerL10n->setAttributes($insertdata, false);
            $oAnswerL10n->save();

            // If translate links is disabled, check for old links.
            if (!$options['translinkfields']) {
                if (checkOldLinks('survey', $iOldSID, $oAnswerL10n->answer)) {
                    $results['importwarnings'][] = sprintf(gT("Answer option %s has outdated links."), $insertdata['code']);
                }
            }
        }
    }

    // Import questionattributes --------------------------------------------------------------
    if (isset($xml->question_attributes)) {
        $aAllAttributes = questionHelper::getAttributesDefinitions();
        /** @var array<string,mixed> List of "answer order" related attributes */
        $answerOrderAttributes = [];
        foreach ($xml->question_attributes->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['qaid']);
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the parent_qid
            }

            // Question theme was previously stored as a question attribute ('question_template'), but now it
            // is a normal attribute of the Question model. So we must check if the imported question has the
            // 'question_template' attribute and use it for overriding 'question_theme_name' instead of saving
            // as QuestionAttribute.
            if ($insertdata['attribute'] == 'question_template') {
                $oQuestion = Question::model()->findByPk($insertdata['qid']);
                if (!empty($oQuestion)) {
                    $oQuestion->question_theme_name = $insertdata['value'];
                    $oQuestion->save();
                }
                continue;
            }

            // Keep "answer order" related attributes in an array to process later (because we need to combine two attributes)
            if (
                $insertdata['attribute'] == 'alphasort'
                || (
                    $insertdata['attribute'] == 'random_order'
                    && in_array($oQuestion->type, ['!', 'L', 'O', 'R'])
                )
            ) {
                $answerOrderAttributes[$insertdata['attribute']] = $insertdata['value'];
                continue;
            }

            if (
                $iDBVersion < 156 &&
                isset($aAllAttributes[$insertdata['attribute']]['i18n']) &&
                $aAllAttributes[$insertdata['attribute']]['i18n']
            ) {
                foreach ($importlanguages as $sLanguage) {
                    $insertdata['language'] = $sLanguage;
                    $attributes = new QuestionAttribute();
                    foreach ($insertdata as $k => $v) {
                        $attributes->$k = $v;
                    }

                    $attributes->save();
                }
            } else {
                $attributes = new QuestionAttribute();
                foreach ($insertdata as $k => $v) {
                    $attributes->$k = $v;
                }

                $attributes->save();
            }
            checkWrongQuestionAttributes($insertdata['qid']);
            $results['question_attributes']++;
        }
    }

    // Process "answer order" attributes
    if (!empty($answerOrderAttributes['random_order'])) {
        $insertdata = [
            'qid' => $newqid,
            'attribute' => 'answer_order',
            'value' => 'random',
        ];
        App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
        $results['question_attributes']++;
    } elseif (!empty($answerOrderAttributes['alphasort'])) {
        $insertdata = [
            'qid' => $newqid,
            'attribute' => 'answer_order',
            'value' => 'alphabetical',
        ];
        App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
        $results['question_attributes']++;
    }

    // Import defaultvalues ------------------------------------------------------
    importDefaultValues($xml, $aLanguagesSupported, $aQIDReplacements, $results);

    LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

    $results['newqid'] = $newqid;
    $results['questions'] = 1;
    $results['labelsets'] = 0;
    $results['labels'] = 0;
    return $results;
}

/**
* XMLImportLabelsets()
* Function resp[onsible to import a labelset from XML format.
* @param string $sFullFilePath
* @param mixed $options
* @return array Array with count of imported labelsets, labels, warning, etc.
*/
function XMLImportLabelsets($sFullFilePath, $options)
{
    $sXMLdata = (string) file_get_contents($sFullFilePath);
    $xml = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
    if ($xml->LimeSurveyDocType != 'Label set') {
        throw new Exception('This is not a valid LimeSurvey label set structure XML file.');
    }
    $aLSIDReplacements = $results = [];
    $results['labelsets'] = 0;
    $results['labels'] = 0;
    $results['warnings'] = array();
    $aImportedLabelSetIDs = array();

    // Import label sets table ===================================================================================
    foreach ($xml->labelsets->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key => $value) {
            $insertdata[(string) $key] = (string) $value;
        }
        $iOldLabelSetID = $insertdata['lid'];
        unset($insertdata['lid']); // save the old qid

        // Insert the new question
        $arLabelset = new LabelSet();
        $arLabelset->setAttributes($insertdata);
        $arLabelset->setAttribute('owner_id', App()->user->getId());
        $arLabelset->save();
        $aLSIDReplacements[$iOldLabelSetID] = $arLabelset->lid; // add old and new lsid to the mapping array
        $results['labelsets']++;
        $aImportedLabelSetIDs[] = $arLabelset->lid;
    }

    // Import labels table ===================================================================================
    if (isset($xml->labels->rows->row)) {
        foreach ($xml->labels->rows->row as $row) {
            $insertdata = [];
            $insertdataLS = [];
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['lid'] = $aLSIDReplacements[$insertdata['lid']];
            if (!isset($xml->label_l10ns->rows->row)) {
                $insertdataLS['title'] = $insertdata['title'];
                $insertdataLS['language'] = $insertdata['language'];
                unset($insertdata['title']);
                unset($insertdata['language']);
            } else {
                $iOldLabelID = $insertdata['id'];
            }
            unset($insertdata['id']);

            if (!isset($xml->label_l10ns->rows->row)) {
                $findLabel = Label::model()->findByAttributes($insertdata);
                if (empty($findLabel)) {
                    $arLabel = new Label();
                    $arLabel->setAttributes($insertdata);
                    $arLabel->save();
                    $insertdataLS['label_id'] = $arLabel->id;
                } else {
                    $insertdataLS['label_id'] = $findLabel->id;
                }
                $arLabelL10n = new LabelL10n();
                $arLabelL10n->setAttributes($insertdataLS);
                $arLabelL10n->save();
            } else {
                $arLabel = new Label();
                $arLabel->setAttributes($insertdata);
                $arLabel->save();
                $aLIDReplacements[$iOldLabelID] = $arLabel->id;
            }

            $results['labels']++;
        }
    }

    // Import label_l10ns table ===================================================================================
    if (isset($xml->label_l10ns->rows->row)) {
        foreach ($xml->label_l10ns->rows->row as $row) {
            $insertdata = [];
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['label_id'] = $aLIDReplacements[$insertdata['label_id']];
            $arLabelL10n = new LabelL10n();
            $arLabelL10n->setAttributes($insertdata);
            $arLabelL10n->save();
        }
    }

    //CHECK FOR DUPLICATE LABELSETS

    if ($options['checkforduplicates'] == 'on') {
        $aLabelSetCheckSums = buildLabelSetCheckSumArray();
        $aCounts = array_count_values($aLabelSetCheckSums);
        foreach ($aImportedLabelSetIDs as $iLabelSetID) {
            if ($aCounts[$aLabelSetCheckSums[$iLabelSetID]] > 1) {
                LabelSet::model()->deleteLabelSet($iLabelSetID);
            }
        }

        //END CHECK FOR DUPLICATES
    }
    return $results;
}

/**
 * @param int|string  $newsid
 * @param string|null $baselang
 */
function finalizeSurveyImportFile($newsid, $baselang)
{
    if ($baselang) {
        $survey = Survey::model()->findByPk($newsid);
        $supportedLanguages = explode(" ", $survey->language . " " . $survey->additional_languages);
        $found = in_array($baselang, $supportedLanguages);
        if (!$found) {
            $baselang = explode("-", $baselang)[0];
            $found = in_array($baselang, $supportedLanguages);
        }
        if ($found) {
            $survey->language = $baselang;
            $survey->additional_languages = '';
            $survey->save();
            fixLanguageConsistency($newsid);
        }
    }
}

/**
 * Returns the tables which
 * @param int $sid
 * @param string $baseTable
 * @return array
 */
function getTableArchivesAndTimestamps(int $sid, string $baseTable = 'old_survey')
{
    $tables = dbGetTablesLike("%old%\_{$sid}\_%");
    $result = [];

    foreach ($tables as $table) {
        $parts = explode("_", $table);
        $timestamp = $parts[count($parts) - 1];
        if (!isset($result[$timestamp])) {
            $result[$timestamp] = [
                'tables' => $table,
                'timestamp' => $timestamp,
                'cnt' => 0
            ];
        } else {
            $result[$timestamp]['tables'] .= ",{$table}";
        }
        if (strpos($table, 'survey') !== false) {
            $result[$timestamp]['cnt'] = (int) Yii::app()->db->createCommand("select count(*) as cnt from " . Yii::app()->db->quoteTableName($table))->queryScalar();
        }
    }

    $keys = array_keys($result);
    asort($keys);
    $finalResult = [];
    foreach ($keys as $key) {
        $finalResult []= $result[$key];
    }
    return $finalResult;
}

/**
 * @param string       $sFullFilePath
 * @param boolean      $bTranslateLinksFields
 * @param string|null  $sNewSurveyName
 * @param integer|null $DestSurveyID
 * @param string|null  $baselang
 */
function importSurveyFile($sFullFilePath, $bTranslateLinksFields, $sNewSurveyName = null, $DestSurveyID = null, $baselang = null)
{
    $aPathInfo = pathinfo($sFullFilePath);
    if (isset($aPathInfo['extension'])) {
        $sExtension = strtolower($aPathInfo['extension']);
    } else {
        $sExtension = "";
    }
    switch ($sExtension) {
        case 'lss':
            $aImportResults = XMLImportSurvey($sFullFilePath, null, $sNewSurveyName, $DestSurveyID, $bTranslateLinksFields);
            if (!empty($aImportResults['newsid'])) {
                $SurveyIntegrity = new LimeSurvey\Models\Services\SurveyIntegrity(Survey::model()->findByPk($aImportResults['newsid']));
                $SurveyIntegrity->fixSurveyIntegrity();
                finalizeSurveyImportFile($aImportResults['newsid'], $baselang);
            }
            return $aImportResults;
        case 'txt':
        case 'tsv':
            $aImportResults = TSVImportSurvey($sFullFilePath);
            if ($aImportResults && $aImportResults['newsid']) {
                $SurveyIntegrity = new LimeSurvey\Models\Services\SurveyIntegrity(Survey::model()->findByPk($aImportResults['newsid']));
                $SurveyIntegrity->fixSurveyIntegrity();
                finalizeSurveyImportFile($aImportResults['newsid'], $baselang);
            }
            return $aImportResults;
        case 'lsa':
            // Import a survey archive
            $zipExtractor = new \LimeSurvey\Models\Services\ZipExtractor($sFullFilePath);
            // If file extension is not lss, lsr, lsi or lst, skip it
            $zipExtractor->setFilterCallback(fn($file) => preg_match('/(lss|lsr|lsi|lst)$/', $file['name']));
            $zipExtractor->extractTo(Yii::app()->getConfig('tempdir'));

            $extractResults = $zipExtractor->getExtractResult();
            $files = array_map(fn($file) => $file['name'], $extractResults);

            $aImportResults = [];

            if (empty($files)) {
                $aImportResults['error'] = gT("This is not a valid LimeSurvey LSA file.");
                return $aImportResults;
            }
            // Step 1 - import the LSS file and activate the survey
            foreach ($files as $filename) {
                if (pathinfo((string) $filename, PATHINFO_EXTENSION) == 'lss') {
                    //Import the LSS file
                    $aImportResults = XMLImportSurvey(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename, null, $sNewSurveyName, null, true, false);
                    if ($aImportResults && $aImportResults['newsid']) {
                        $SurveyIntegrity = new LimeSurvey\Models\Services\SurveyIntegrity(Survey::model()->findByPk($aImportResults['newsid']));
                        $SurveyIntegrity->fixSurveyIntegrity();
                    }
                    // Activate the survey
                    Yii::app()->loadHelper("admin.activate");
                    $survey = Survey::model()->findByPk($aImportResults['newsid']);
                    $surveyActivator = new SurveyActivator($survey);
                    $surveyActivator->activate();
                    unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename);
                    break;
                }
            }

            // Step 2 - import the responses file
            foreach ($files as $filename) {
                if (pathinfo((string) $filename, PATHINFO_EXTENSION) == 'lsr') {
                    //Import the LSS file
                    $aResponseImportResults = XMLImportResponses(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename, $aImportResults['newsid'], $aImportResults['FieldReMap']);
                    $aImportResults = array_merge($aResponseImportResults, $aImportResults);
                    $aImportResults['importwarnings'] = array_merge($aImportResults['importwarnings'], $aImportResults['warnings']);
                    unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename);
                    break;
                }
            }

            // Step 3 - import the tokens file - if exists
            foreach ($files as $filename) {
                if (pathinfo((string) $filename, PATHINFO_EXTENSION) == 'lst') {
                    Yii::app()->loadHelper("admin.token");
                    $aTokenImportResults = [];
                    if (Token::createTable($aImportResults['newsid'])) {
                        $aTokenCreateResults = array('tokentablecreated' => true);
                        $aImportResults = array_merge($aTokenCreateResults, $aImportResults);
                        $aTokenImportResults = XMLImportTokens(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename, $aImportResults['newsid']);
                    } else {
                        $aTokenImportResults['warnings'][] = gT("Unable to create survey participant list");
                    }

                    $aImportResults = array_merge_recursive($aTokenImportResults, $aImportResults);
                    $aImportResults['importwarnings'] = array_merge($aImportResults['importwarnings'], $aImportResults['warnings']);
                    unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename);
                    break;
                }
            }
            // Step 4 - import the timings file - if exists
            Yii::app()->db->schema->refresh();
            foreach ($files as $filename) {
                if (pathinfo((string) $filename, PATHINFO_EXTENSION) == 'lsi' && tableExists("survey_{$aImportResults['newsid']}_timings")) {
                    $aTimingsImportResults = XMLImportTimings(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename, $aImportResults['newsid'], $aImportResults['FieldReMap']);
                    $aImportResults = array_merge($aTimingsImportResults, $aImportResults);
                    unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $filename);
                    break;
                }
            }
            if ($aImportResults && isset($aImportResults['newsid'])) {
                finalizeSurveyImportFile($aImportResults['newsid'], $baselang);
            }
            return $aImportResults;
        default:
            // Unknow file , return null why not throw error ?
            return null;
    }
}

/**
 * Creates a table based on another
 *
 * @param string $table
 * @param string $pattern
 * @param array $columns
 * @param array $where
 * @return integer number of rows affected by the execution.
 * @throws CDbException execution failed
 */
function createTableFromPattern($table, $pattern, $columns = [], $where = [])
{
    if (!is_array($columns)) {
        $columns = [];
    }
    if (!is_array($where)) {
        $where = [];
    }
    $whereClause = "";
    $criterias = [];
    if (count($where)) {
        foreach ($where as $field => $value) {
            $criterias[] = Yii::app()->db->quoteColumnName($field) . " = " . Yii::app()->db->quoteValue($value);
        }
        $whereClause = " WHERE " . implode(" AND ", $criterias);
    }
    if (count($columns)) {
        foreach ($columns as $index => $column) {
            if (!ctype_alnum($column)) {
                $columns[$index] = Yii::app()->db->quoteColumnName($column);
            }
        }
        $command = "";
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
            case 'pgsql':
            $command = "CREATE TABLE " . Yii::app()->db->quoteTableName($table) . " AS SELECT " . implode(",", $columns) . " FROM " . Yii::app()->db->quoteTableName($pattern) . $whereClause;
            break;
            case 'mssql':
            case 'sqlsrv':
            $command = "SELECT " . implode(",", $columns) . " into " . Yii::app()->db->quoteTableName($table) . " FROM " . Yii::app()->db->quoteTableName($pattern) . $whereClause;
            break;
        }
    } else {
        $command = "";
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
            $command = "CREATE TABLE " . Yii::app()->db->quoteTableName($table) . " LIKE " . Yii::app()->db->quoteTableName($pattern) . ";";
            break;
            case 'pgsql':
            $command = "CREATE TABLE " . Yii::app()->db->quoteTableName($table) . " (LIKE " . Yii::app()->db->quoteTableName($pattern) . " INCLUDING ALL);";
            break;
            case 'mssql':
            case 'sqlsrv':
            $command = "SELECT * into " . Yii::app()->db->quoteTableName($table) . " FROM " . Yii::app()->db->quoteTableName($pattern) . " where 1=0";
            break;
        }
    }
    return Yii::app()->db->createCommand($command)->execute();
}

function polyfillSUBSTRING_INDEX($driver) {
    switch ($driver) {
        case 'pgsql':
            Yii::app()->db->createCommand('CREATE OR REPLACE FUNCTION public.SUBSTRING_INDEX (
                    str text,
                    delim text,
                    count integer = 1,
                    out SUBSTRING_INDEX text
                )
                RETURNS text AS
                $body$
                BEGIN
                    IF count > 0 THEN
                        SUBSTRING_INDEX = array_to_string((string_to_array(str, delim))[:count], delim);
                    ELSE
                        DECLARE
                        _array TEXT[];
                         BEGIN
                             _array = string_to_array(str, delim);
                             SUBSTRING_INDEX = array_to_string(_array[array_length(_array, 1) + count + 1:], delim);    
                         END;  
                    END IF;
                END;
                $body$
                LANGUAGE \'plpgsql\'
                IMMUTABLE
                CALLED ON NULL INPUT
                SECURITY INVOKER
                COST 5;')->execute();
        break;
        case 'mssql':
            case 'sqlsrv':
                Yii::app()->db->createCommand(
    <<<EOD
    IF OBJECT_ID('dbo.SUBSTRING_INDEX') IS NOT NULL
      DROP FUNCTION SUBSTRING_INDEX
    EOD
                )->execute();
                Yii::app()->db->createCommand(
    <<<EOD
                    CREATE FUNCTION dbo.SUBSTRING_INDEX (
                        @str NVARCHAR(4000),
                        @delim NVARCHAR(1),
                        @count INT
                  )
                  RETURNS NVARCHAR(4000)
                  WITH SCHEMABINDING
                  BEGIN
                  DECLARE @XmlSourceString XML;
                  SET @XmlSourceString = (SELECT N'<root><row>' + REPLACE( (SELECT @str AS '*' FOR XML PATH('')) , @delim, N'</row><row>' ) + N'</row></root>');
                  RETURN STUFF
                  (
                    ((
                    SELECT  @delim + x.XmlCol.value(N'(text())[1]', N'NVARCHAR(4000)') AS '*'
                    FROM    @XmlSourceString.nodes(N'(root/row)[position() <= sql:variable("@count")]') x(XmlCol)
                    FOR XML PATH(N''), TYPE
                    ).value(N'.', N'NVARCHAR(4000)')),
                  1, 1, N''
                  );
                  END
    EOD
                )->execute();
        break;
    
    }
}

/**
 * Generates a temporary table creation script
 *
 * @param string $source
 * @param string $destination
 * @param int $sid
 * @return string
 */
function generateTemporaryTableCreate(string $source, string $destination, int $sid)
{
    switch (Yii::app()->db->getDriverName()) {
        case 'mysqli':
        case 'mysql':
        return  "
            CREATE TEMPORARY TABLE {$destination}
            SELECT *
            FROM (
                SELECT SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 1) AS sid,
                       SUBSTRING_INDEX(SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 2), 'X', -1) AS gid,
                       SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', -1) AS qidsuffix,
                       temp.COLUMN_NAME
                FROM information_schema.columns temp
                WHERE temp.TABLE_SCHEMA = DATABASE() AND 
                      temp.TABLE_NAME = '{$source}'
            ) t;
        ";
        case 'pgsql':
        polyfillSUBSTRING_INDEX(Yii::app()->db->getDriverName());
        return "
            CREATE TEMPORARY TABLE {$destination}
            AS
            SELECT *
            FROM (
                SELECT SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 1) AS sid,
                       SUBSTRING_INDEX(SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 2), 'X', -1) AS gid,
                       SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', -1) AS qidsuffix,
                       temp.COLUMN_NAME
                FROM information_schema.columns temp
                WHERE temp.TABLE_CATALOG = current_database() AND 
                      temp.TABLE_NAME = '{$source}'
            ) t;
        ";
        case 'mssql':
        case 'sqlsrv':
        polyfillSUBSTRING_INDEX(Yii::app()->db->getDriverName());
        $destination .= "_" . $sid;
        return "
            SELECT *
            INTO {$destination}
            FROM (
                SELECT dbo.SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 1) AS sid,
                       dbo.SUBSTRING_INDEX(SUBSTRING(temp.COLUMN_NAME, 2 + LEN(dbo.SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 1)), 2000), 'X', 1) AS gid,
                       SUBSTRING(temp.COLUMN_NAME, charindex('X', temp.COLUMN_NAME, (charindex('X', temp.COLUMN_NAME, 1))+1) + 1, 2000) AS qidsuffix,
                       temp.COLUMN_NAME
                FROM information_schema.columns temp
                WHERE temp.TABLE_CATALOG = db_name() AND
                      temp.TABLE_NAME = '{$source}'
            ) t;
        ";
    }
    //unsupported
    return '';
}

/**
 * Generates a drop statement for a temporary table
 *
 * @param string $name
 * @param int $sid
 * @return string
 */
function generateTemporaryTableDrop(string $name, int $sid)
{
    switch (Yii::app()->db->getDriverName()) {
        case 'mysqli':
        case 'mysql':
        return "DROP TEMPORARY TABLE {$name};";
        case 'pgsql':
        return "DROP TABLE {$name};";
        case 'mssql':
        case 'sqlsrv':
        return "DROP TABLE {$name}_{$sid};";
    }
    //unsupported
    return '';
}

/**
 * Gets the unchanged columns
 *
 * @param int $sid
 * @param int $qTimestamp
 * @param int $sTimestamp
 * @return array all rows of the query result. Each array element is an array representing a row.
 * An empty array is returned if the query results in nothing.
 * @throws CException execution failed
 */
function getUnchangedColumns($sid, $sTimestamp, $qTimestamp)
{
    $sourceTables = [
        Yii::app()->db->tablePrefix . "survey_" . $sid,
        Yii::app()->db->tablePrefix . "survey_" . $sid,
        Yii::app()->db->tablePrefix . "survey_" . $sid,
        Yii::app()->db->tablePrefix . "old_survey_{$sid}_{$sTimestamp}",
        Yii::app()->db->tablePrefix . "old_survey_{$sid}_{$sTimestamp}",
        Yii::app()->db->tablePrefix . "old_survey_{$sid}_{$sTimestamp}",
    ];
    $destinationTables = [
        'new_s_c',
        'new_parent1',
        'new_parent2',
        'old_s_c',
        'old_parent1',
        'old_parent2'
    ];
    Yii::app()->db->createCommand(implode("\n\n", generateTemporaryTableCreates($sourceTables, $destinationTables, $sid)))->execute();
    $command = "";
    switch (Yii::app()->db->getDriverName()) {
        case 'mysqli':
        case 'mysql':
        $command = "
        SELECT old_s_c.COLUMN_NAME AS old_c, new_s_c.COLUMN_NAME AS new_c
        FROM " . Yii::app()->db->tablePrefix . "old_questions_" . $sid . "_" . $qTimestamp . " old_q
        JOIN " . Yii::app()->db->tablePrefix . "questions new_q
        ON old_q.qid = new_q.qid AND old_q.type = new_q.type
        JOIN new_s_c
        ON new_s_c.sid = new_q.sid AND
           new_s_c.gid = new_q.gid AND
           new_s_c.qidsuffix like concat(new_q.qid, '%')
        JOIN old_s_c
        ON old_s_c.sid = old_q.sid AND
           old_s_c.gid = old_q.gid AND
           old_s_c.qidsuffix LIKE CONCAT(old_q.qid, '%') AND
           old_s_c.qidsuffix = new_s_c.qidsuffix
        LEFT JOIN new_parent1
        ON new_s_c.sid = new_parent1.sid AND
           new_s_c.gid = new_parent1.gid AND
           new_s_c.qidsuffix <> new_parent1.qidsuffix AND
           new_parent1.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
        LEFT JOIN new_parent2
        ON new_s_c.sid = new_parent2.sid AND
           new_s_c.gid = new_parent2.gid AND
           new_s_c.qidsuffix <> new_parent2.qidsuffix AND new_parent1.qidsuffix <> new_parent2.qidsuffix AND
           new_parent2.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
        LEFT JOIN old_parent1
        ON old_s_c.sid = old_parent1.sid AND
           old_s_c.gid = old_parent1.gid AND
           old_s_c.qidsuffix <> old_parent1.qidsuffix AND
           old_parent1.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
        LEFT JOIN old_parent2
           ON old_s_c.sid = old_parent2.sid AND
              old_s_c.gid = old_parent2.gid AND
              old_s_c.qidsuffix <> old_parent2.qidsuffix AND old_parent1.qidsuffix <> old_parent2.qidsuffix AND
              old_parent2.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
        WHERE (new_parent2.sid IS NULL) AND
              (old_parent2.sid IS NULL) AND
              (((new_parent1.sid IS NULL) AND (old_parent1.sid IS NULL)) OR
               (
                (new_parent1.sid = old_parent1.sid) AND
                (new_parent1.gid = old_parent1.gid) AND
                (new_parent1.qidsuffix = old_parent1.qidsuffix)
               )
              )
        ;
        "
        ;
        break;
        case 'pgsql':
            $command = "
            SELECT old_s_c.COLUMN_NAME AS old_c, new_s_c.COLUMN_NAME AS new_c
            FROM " . Yii::app()->db->tablePrefix . "old_questions_" . $sid . "_" . $qTimestamp . " old_q
            JOIN " . Yii::app()->db->tablePrefix . "questions new_q
            ON old_q.qid = new_q.qid AND old_q.type = new_q.type
            JOIN new_s_c
            ON new_s_c.sid::text = new_q.sid::text AND
               new_s_c.gid::text = new_q.gid::text AND
               new_s_c.qidsuffix like concat(new_q.qid, '%')
            JOIN old_s_c
            ON old_s_c.sid::text = old_q.sid::text AND
               old_s_c.gid::text = old_q.gid::text AND
               old_s_c.qidsuffix LIKE CONCAT(old_q.qid, '%') AND
               old_s_c.qidsuffix = new_s_c.qidsuffix
            LEFT JOIN new_parent1
            ON new_s_c.sid = new_parent1.sid AND
               new_s_c.gid = new_parent1.gid AND
               new_s_c.qidsuffix <> new_parent1.qidsuffix AND
               new_parent1.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
            LEFT JOIN new_parent2
            ON new_s_c.sid = new_parent2.sid AND
               new_s_c.gid = new_parent2.gid AND
               new_s_c.qidsuffix <> new_parent2.qidsuffix AND new_parent1.qidsuffix <> new_parent2.qidsuffix AND
               new_parent2.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
            LEFT JOIN old_parent1
            ON old_s_c.sid = old_parent1.sid AND
               old_s_c.gid = old_parent1.gid AND
               old_s_c.qidsuffix <> old_parent1.qidsuffix AND
               old_parent1.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
            LEFT JOIN old_parent2
               ON old_s_c.sid = old_parent2.sid AND
                  old_s_c.gid = old_parent2.gid AND
                  old_s_c.qidsuffix <> old_parent2.qidsuffix AND old_parent1.qidsuffix <> old_parent2.qidsuffix AND
                  old_parent2.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
            WHERE (new_parent2.sid IS NULL) AND
                  (old_parent2.sid IS NULL) AND
                  (((new_parent1.sid IS NULL) AND (old_parent1.sid IS NULL)) OR
                   (
                    (new_parent1.sid = old_parent1.sid) AND
                    (new_parent1.gid = old_parent1.gid) AND
                    (new_parent1.qidsuffix = old_parent1.qidsuffix)
                   )
                  )
            ;
            "
            ;
        break;
        case 'mssql':
        case 'sqlsrv':
            $command = "
            SELECT old_s_c.COLUMN_NAME AS old_c, new_s_c.COLUMN_NAME AS new_c
                    FROM " . Yii::app()->db->tablePrefix . "old_questions_" . $sid . "_" . $qTimestamp . " old_q
                    JOIN " . Yii::app()->db->tablePrefix . "questions new_q
                    ON old_q.qid = new_q.qid AND old_q.type = new_q.type
                    JOIN new_s_c_{$sid} new_s_c
                    ON new_s_c.sid = convert(nvarchar(255), new_q.sid) AND
                       new_s_c.gid = convert(nvarchar(255), new_q.gid) AND
                       new_s_c.qidsuffix like concat(new_q.qid, '%')
                    JOIN old_s_c_{$sid} old_s_c
                    ON old_s_c.sid = convert(nvarchar(255), old_q.sid) AND
                       old_s_c.gid = convert(nvarchar(255), old_q.gid) AND
                       old_s_c.qidsuffix LIKE CONCAT(old_q.qid, '%') AND
                       old_s_c.qidsuffix = new_s_c.qidsuffix
                    LEFT JOIN new_parent1_{$sid} new_parent1
                    ON new_s_c.sid = new_parent1.sid AND
                       new_s_c.gid = new_parent1.gid AND
                       new_s_c.qidsuffix <> new_parent1.qidsuffix AND
                       new_parent1.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
                    LEFT JOIN new_parent2_{$sid} new_parent2
                    ON new_s_c.sid = new_parent2.sid AND
                       new_s_c.gid = new_parent2.gid AND
                       new_s_c.qidsuffix <> new_parent2.qidsuffix AND new_parent1.qidsuffix <> new_parent2.qidsuffix AND
                       new_parent2.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
                    LEFT JOIN old_parent1_{$sid} old_parent1
                    ON old_s_c.sid = old_parent1.sid AND
                       old_s_c.gid = old_parent1.gid AND
                       old_s_c.qidsuffix <> old_parent1.qidsuffix AND
                       old_parent1.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
                    LEFT JOIN old_parent2_{$sid} old_parent2
                       ON old_s_c.sid = old_parent2.sid AND
                          old_s_c.gid = old_parent2.gid AND
                          old_s_c.qidsuffix <> old_parent2.qidsuffix AND old_parent1.qidsuffix <> old_parent2.qidsuffix AND
                          old_parent2.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
                    WHERE (new_parent2.sid IS NULL) AND
                          (old_parent2.sid IS NULL) AND
                          (((new_parent1.sid IS NULL) AND (old_parent1.sid IS NULL)) OR
                           (
                            (new_parent1.sid = old_parent1.sid) AND
                            (new_parent1.gid = old_parent1.gid) AND
                            (new_parent1.qidsuffix = old_parent1.qidsuffix)
                           )
                          )
            ;            
            "
            ;
            break;
    }

    $rawResults = Yii::app()->db->createCommand($command)->queryAll();
    $results = ['old_c' => [], 'new_c' => []];
    foreach ($rawResults as $rawResult) {
        $results['old_c'][] = $rawResult['old_c'];
        $results['new_c'][] = $rawResult['new_c'];
    }
    Yii::app()->db->createCommand(implode("\n\n", generateTemporaryTableDrops($destinationTables, $sid)))->execute();
    return $results;
}

/**
 * Generates temporary table creation scripts from the arrays received and returns the scripts that were generated,
 * we expect count($sourceTables) and count($destinationTables) to be the same
 *
 * @param array $sourceTables
 * @param array $destinationTables
 * @param int $sid
 * @return array
 */
function generateTemporaryTableCreates(array $sourceTables, array $destinationTables, int $sid)
{
    $output = [];
    for ($index = 0; $index < count($sourceTables); $index++) {
        $output [] = generateTemporaryTableCreate($sourceTables[$index], $destinationTables[$index], $sid);
    }
    return $output;
}

/**
 * Generates temporary table drops for the tables received and returns the scripts
 *
 * @param array $tables
 * @param int $sid
 * @return array
 */
function generateTemporaryTableDrops(array $tables, int $sid)
{
    $output = [];
    foreach ($tables as $table) {
        $output [] = generateTemporaryTableDrop($table, $sid);
    }
    return $output;
}

/**
 * Finds the newest archive table from each kind
 *
 * @param int $sid
 * @return array all rows of the query result. Each array element is an array representing a row.
 * An empty array is returned if the query results in nothing.
 * @throws CException execution failed
 */
function getDeactivatedArchives($sid)
{
    $sid = intval($sid);
    $command = "";
    switch (Yii::app()->db->getDriverName()) {
        case 'mysqli':
        case 'mysql':
        $command = "
        SELECT n, GROUP_CONCAT(TABLE_NAME) AS table_name
        FROM
        (SELECT n, TABLE_NAME
        FROM information_schema.tables
        JOIN (
            SELECT 'survey' AS n
            UNION
            SELECT 'tokens' AS n
            UNION
            SELECT 'timings' AS n
            UNION
            SELECT 'questions' AS n
        ) t
        ON TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE CONCAT('%', n, '%') AND TABLE_NAME LIKE '%old%' AND TABLE_NAME LIKE '%{$sid}%' AND
        ((n <> 'survey') OR (TABLE_NAME NOT LIKE '%timings%'))
        ORDER BY TABLE_NAME) t
        GROUP BY n;
        ";
        break;
        case 'pgsql':
        $command = "
        SELECT n, array_to_string(array_agg(TABLE_NAME), ',') AS table_name
        FROM
        (SELECT n, TABLE_NAME
        FROM information_schema.tables
        JOIN (
            SELECT 'survey' AS n
            UNION
            SELECT 'tokens' AS n
            UNION
            SELECT 'timings' AS n
            UNION
            SELECT 'questions' AS n
        ) t
        ON TABLE_CATALOG = current_database() AND TABLE_NAME LIKE CONCAT('%', n, '%') AND TABLE_NAME LIKE '%old%' AND TABLE_NAME LIKE '%{$sid}%' AND
        ((n <> 'survey') OR (TABLE_NAME NOT LIKE '%timings%'))
        ORDER BY TABLE_NAME) t
        GROUP BY n;
            "
            ;
        break;
        case 'mssql':
        case 'sqlsrv':
        $command = "
		SELECT n, STRING_AGG(TABLE_NAME, ',') AS table_name
        FROM
        (SELECT n, TABLE_NAME
        FROM information_schema.tables
        JOIN (
            SELECT 'survey' AS n
            UNION
            SELECT 'tokens' AS n
            UNION
            SELECT 'timings' AS n
            UNION
            SELECT 'questions' AS n
        ) t
        ON TABLE_CATALOG = db_name() AND TABLE_NAME LIKE CONCAT('%', n, '%') AND TABLE_NAME LIKE '%old%' AND TABLE_NAME LIKE '%{$sid}%' AND
        ((n <> 'survey') OR (TABLE_NAME NOT LIKE '%timings%'))
        ) t
        GROUP BY n;
        "
        ;
        break;
    }
    $rawResults = Yii::app()->db->createCommand($command)->queryAll();
    $results = [];
    foreach ($rawResults as $rawResult) {
        $results[$rawResult['n']] = $rawResult["table_name"];
    }
    return $results;
}

/**
 * Copying all data from source table to a target table having the same structure
 *
 * @param string $source
 * @param string $destination
 * @param bool $preserveIDs
 * @return integer number of rows affected by the execution.
 * @throws CDbException execution failed
 */
function copyFromOneTableToTheOther($source, $destination, $preserveIDs = false)
{
    $customFilter = [
        'mysql' => 'a.TABLE_SCHEMA = b.TABLE_SCHEMA and a.TABLE_SCHEMA = DATABASE()',
        'mysqli' => 'a.TABLE_SCHEMA = b.TABLE_SCHEMA and a.TABLE_SCHEMA = DATABASE()',
        'pgsql' => 'a.TABLE_CATALOG = b.TABLE_CATALOG and a.TABLE_CATALOG = current_database()',
        'mssql' => 'a.TABLE_CATALOG = b.TABLE_CATALOG and a.TABLE_CATALOG = db_name()',
        'sqlsrv' => 'a.TABLE_CATALOG = b.TABLE_CATALOG and a.TABLE_CATALOG = db_name()',
    ];
    $filter = ($customFilter[Yii::app()->db->getDriverName()] ?? '');
    $command = "
        select a.COLUMN_NAME as cname
        from information_schema.columns a
        join information_schema.columns b
        on {$filter} and
          a.TABLE_NAME = '" . $destination . "' and
          b.TABLE_NAME = '" . $source . "' and
          a.COLUMN_NAME = b.COLUMN_NAME
    ";
    if (!$preserveIDs) {
        $command .= " where a.COLUMN_NAME not in ('id', 'tid')";
    }
    $rawResults = Yii::app()->db->createCommand($command)->queryAll();
    $columns = [];
    foreach ($rawResults as $rawResult) {
        $columns[] = Yii::app()->db->quoteColumnName($rawResult['cname']);
    }
    $timings = count($columns) ? Yii::app()->db->createCommand("INSERT INTO " . Yii::app()->db->quoteTableName($destination) . "(" . implode(",", $columns) . ") SELECT " . implode(",", $columns) . " FROM " . Yii::app()->db->quoteTableName($source))->execute() : 0;
    if ((!$preserveIDs) && (strpos($destination, 'timings') !== false)) {
        $oldResponsesTable = str_replace('_timings', '', $source);
        $command = "
            SELECT t1.id as rid, t2.id as tid
            FROM {$oldResponsesTable} t1
            LEFT JOIN {$source} t2
            ON t1.id = t2.id
            order by t1.id
        ";
        $newResponsesTable = str_replace('_timings', '', $destination);
        $rawResults = Yii::app()->db->createCommand($command)->queryAll();
        $offset = 0;
        foreach ($rawResults as $rawResult) {
            if (intval($rawResult['tid']) > 0) {
                $responseidresult = Yii::app()->db->createCommand()
                ->select('id ')
                ->from($newResponsesTable)
                ->limit(1, $offset)
                ->query()
                ->readAll();
                $newID = $responseidresult[0]['id'];
                $oldID = $offset + 1;
                $command = "
                    UPDATE {$destination}
                    SET id = {$newID}
                    WHERE id = {$oldID}
                ";
                Yii::app()->db->createCommand($command)->execute();
            }
            $offset++;
        }
    }
    return $timings;
}

/**
 * Recovers archived survey responses
 *
 * @param int $surveyId survey ID
 * @param string $archivedResponseTableName archived response table name to be imported
 * @param bool $preserveIDs if archived response IDs should be preserved
 * @param array $validatedColumns the columns that are validated and can be inserted again
 * @return integer number of rows affected by the execution.
 * @throws Exception execution failed
 */
function recoverSurveyResponses(int $surveyId, string $archivedResponseTableName, $preserveIDs, array $validatedColumns = []): int
{
    if (!is_array($validatedColumns)) {
        $validatedColumns = [];
    }
    $pluginDynamicArchivedResponseModel = PluginDynamic::model($archivedResponseTableName);
    $targetSchema = SurveyDynamic::model($surveyId)->getTableSchema();
    $encryptedAttributes = Response::getEncryptedAttributes($surveyId);
    if ((App()->db->tablePrefix) && (strpos($archivedResponseTableName, App()->db->tablePrefix) === 0)) {
        $tbl_name = str_replace('old_survey', 'old_tokens', substr($archivedResponseTableName, strlen(App()->db->tablePrefix)));
    } else {
        $tbl_name = str_replace('old_survey', 'old_tokens', $archivedResponseTableName);
    }
    $archivedTableSettings = ArchivedTableSettings::model()->findByAttributes(['tbl_name' => $tbl_name, 'tbl_type' => 'response']);
    $archivedEncryptedAttributes = [];
    if ($archivedTableSettings) {
        $archivedEncryptedAttributes = json_decode($archivedTableSettings->properties, true);
    }
    $archivedResponses = new CDataProviderIterator(new CActiveDataProvider($pluginDynamicArchivedResponseModel), 500);

    $tableName = "{{survey_$surveyId}}";
    $importedResponses = 0;
    $batchData = [];
    foreach ($archivedResponses as $archivedResponse) {
        $dataRow = [];
        // Using plugindynamic model because I dont trust surveydynamic.
        $targetResponse = new PluginDynamic($tableName);
        if ($preserveIDs) {
            $targetResponse->id = $archivedResponse->id;
            $dataRow['id'] = $archivedResponse->id;
        }

        $to = 'new_c';
        $from = 'old_c';
        for ($index = 0; $index < count($validatedColumns[$to]); $index++) {
            $source = $validatedColumns[$from][$index];
            $target = $validatedColumns[$to][$index];
            $targetResponse->{$target} = $archivedResponse[$source];
            if (in_array($source, $archivedEncryptedAttributes, false) && !in_array($target, $encryptedAttributes, false)) {
                $targetResponse->{$target} = $archivedResponse->decryptSingle($archivedResponse[$source]);
            } elseif (!in_array($source, $archivedEncryptedAttributes, false) && in_array($target, $encryptedAttributes, false)) {
                $targetResponse->{$target} = $archivedResponse->encryptSingle($archivedResponse[$source]);
            } else {
                $targetResponse->{$target} = $archivedResponse[$source];
            }
            $dataRow[$target] = $targetResponse->{$target};
        }

        $additionalFields = [
            'token',
            'submitdate',
            'lastpage',
            'startlanguage',
            'seed',
            'startdate',
            'datestamp',
            'version_number'
        ];

        if (isset($targetSchema->columns['startdate']) && empty($targetResponse['startdate'])) {
            $targetResponse->{'startdate'} = date("Y-m-d H:i", (int)mktime(0, 0, 0, 1, 1, 1980));
            $dataRow['startdate'] = $targetResponse->{'startdate'};
        }

        if (isset($targetSchema->columns['datestamp']) && empty($targetResponse['datestamp'])) {
            $targetResponse->{'datestamp'} = date("Y-m-d H:i", (int)mktime(0, 0, 0, 1, 1, 1980));
            $dataRow['datestamp'] = $targetResponse->{'datestamp'};
        }

        foreach ($additionalFields as $additionalField) {
            if (isset($archivedResponse->{$additionalField}) && isset($targetSchema->columns[$additionalField])) {
                $dataRow[$additionalField] = $archivedResponse->{$additionalField};
            }
        }

        $beforeDataEntryImport = new PluginEvent('beforeDataEntryImport');
        $beforeDataEntryImport->set('iSurveyID', $surveyId);
        $beforeDataEntryImport->set('oModel', $targetResponse);
        App()->getPluginManager()->dispatchEvent($beforeDataEntryImport);

        if ($targetResponse->validate()){
            $batchData[] = $dataRow;
        }
        if (count($batchData) % 500 === 0) {
            if ($preserveIDs) {
                switchMSSQLIdentityInsert("survey_$surveyId", true);
            }
            $builder = App()->db->getCommandBuilder();
            $command = $builder->createMultipleInsertCommand($tableName, $batchData);
            $importedResponses += $command->execute();
            if ($preserveIDs) {
                switchMSSQLIdentityInsert("survey_$surveyId", false);
            }
            $batchData = [];
        }

        unset($targetResponse);
    }

    if (count($batchData)) {
        if ($preserveIDs) {
            switchMSSQLIdentityInsert("survey_$surveyId", true);
        }
        $builder = App()->db->getCommandBuilder();
        $command = $builder->createMultipleInsertCommand($tableName, $batchData);
        $importedResponses += $command->execute();
        if ($preserveIDs) {
            switchMSSQLIdentityInsert("survey_$surveyId", false);
        }
    }
    return $importedResponses;
}


/**
 * Imports a survey from an XML file or XML data string.
 *
 * This function processes the XML data to import a survey, including its questions, groups, and language settings.
 * It handles various aspects such as translating links, converting question codes, and managing attachments.
 *
 * @param string $sFullFilePath The full file path to the XML file (optional if $sXMLdata is provided)
 * @param string|null $sXMLdata The XML data as a string (optional if $sFullFilePath is provided)
 * @param string|null $sNewSurveyName The new name for the survey if it's being copied
 * @param int|null $iDesiredSurveyId The desired ID for the new survey (optional)
 * @param bool $bTranslateInsertansTags Whether to translate insertans tags (default true)
 * @param bool $bConvertInvalidQuestionCodes Whether to convert invalid question codes (default true)
 * @return array An array containing the results of the import process, including:
 *               - 'error': Any error message if the import failed
 *               - 'newsid': The ID of the newly created survey
 *               - 'oldsid': The ID of the original survey in the XML
 *               - Various counters for imported elements (questions, groups, etc.)
 *               - 'importwarnings': An array of warning messages
 * @todo Use transactions to prevent orphaned data and clean rollback on errors
 */
function XMLImportSurvey($sFullFilePath, $sXMLdata = null, $sNewSurveyName = null, $iDesiredSurveyId = null, $bTranslateInsertansTags = true, $bConvertInvalidQuestionCodes = true)
{
    $isCopying = ($sNewSurveyName != null);
    Yii::app()->loadHelper('database');
    $results = [];
    $aGIDReplacements = array();
    if ($sXMLdata === null) {
        $sXMLdata = (string) file_get_contents($sFullFilePath);
    }

    $xml = @simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET | LIBXML_PARSEHUGE);

    if (!$xml || $xml->LimeSurveyDocType != 'Survey') {
        $results['error'] = gT("This is not a valid LimeSurvey survey structure XML file.");
        return $results;
    }

    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements = array();
    $aQuestionCodeReplacements = array();
    $aQuotaReplacements = array();
    $results['defaultvalues'] = 0;
    $results['answers'] = 0;
    $results['surveys'] = 0;
    $results['questions'] = 0;
    $results['subquestions'] = 0;
    $results['question_attributes'] = 0;
    $results['groups'] = 0;
    $results['assessments'] = 0;
    $results['quota'] = 0;
    $results['quotals'] = 0;
    $results['quotamembers'] = 0;
    $results['plugin_settings'] = 0;
    $results['themes'] = 0;
    $results['survey_url_parameters'] = 0;
    $results['importwarnings'] = array();
    $results['theme_options_original_data'] = '';
    $results['theme_options_differences'] = array();
    $results['access_mode'] = SurveyAccessModeService::$ACCESS_TYPE_OPEN;
    $sTemplateName = '';

    /** @var bool Indicates if the email templates have attachments with untranslated URLs or not */
    $hasOldAttachments = false;

    $aLanguagesSupported = array();
    foreach ($xml->languages->language as $language) {
        $aLanguagesSupported[] = (string) $language;
    }

    $results['languages'] = count($aLanguagesSupported);

    // Import survey entry  ====================================================
    if (!isset($xml->surveys->rows->row)) {
        $results['error'] = gT("XML Parsing Error: Missing or malformed element of type 'survey'");
        return $results;
    }
    foreach ($xml->surveys->rows->row as $row) {
        $insertdata = array();

        foreach ($row as $key => $value) {
            // Set survey group id to default if not a copy
            if ($key == 'gsid' & !$isCopying) {
                $value = 1;
            }
            if ($key == 'template') {
                $sTemplateName = (string)$value;
            }
            if ($key == 'access_mode') {
                $results['access_mode'] = (string)$value;
            }
            $insertdata[(string) $key] = (string) $value;
        }
        $iOldSID = $results['oldsid'] = $insertdata['sid'];
        if (!is_null($iDesiredSurveyId)) {
            $insertdata['sid'] = $iDesiredSurveyId;
        }
        if ($iDBVersion < 145) {
            // Convert to new field names
            if (isset($insertdata['private'])) {
                $insertdata['anonymized'] = $insertdata['private'];
            }
            unset($insertdata['private']);
            unset($insertdata['notification']);
        }

        //Make sure it is not set active
        $insertdata['active'] = 'N';
        //Set current user to be the owner
        $insertdata['owner_id'] = Yii::app()->session['loginID'];

        if (isset($insertdata['bouncetime']) && $insertdata['bouncetime'] == '') {
            $insertdata['bouncetime'] = null;
        }

        if (isset($insertdata['showXquestions'])) {
            $insertdata['showxquestions'] = $insertdata['showXquestions'];
            unset($insertdata['showXquestions']);
        }

        if (isset($insertdata['googleAnalyticsStyle'])) {
            $insertdata['googleanalyticsstyle'] = $insertdata['googleAnalyticsStyle'];
            unset($insertdata['googleAnalyticsStyle']);
        }

        if (isset($insertdata['googleAnalyticsAPIKey'])) {
            $insertdata['googleanalyticsapikey'] = $insertdata['googleAnalyticsAPIKey'];
            unset($insertdata['googleAnalyticsAPIKey']);
        }

        if (isset($insertdata['allowjumps'])) {
            $insertdata['questionindex'] = ($insertdata['allowjumps'] == "Y") ? 1 : 0;
            unset($insertdata['allowjumps']);
        }

        if (isset($insertdata['tokenlength']) && $insertdata['tokenlength'] > Token::MAX_LENGTH) {
            $insertdata['tokenlength'] = Token::MAX_LENGTH;
        }
        /* Remove unknow column */
        $aSurveyModelsColumns = Survey::model()->attributes;
        $aSurveyModelsColumns['wishSID'] = null; // Can not be imported
        $aBadData = array_diff_key($insertdata, $aSurveyModelsColumns);
        $insertdata = array_intersect_key($insertdata, $aSurveyModelsColumns);
        // Fill a optional array of error
        foreach ($aBadData as $key => $value) {
            $results['importwarnings'][] = sprintf(gT("This survey setting has not been imported: %s => %s"), $key, $value);
        }
        $newSurvey = Survey::model()->insertNewSurvey($insertdata);
        if ($newSurvey->sid) {
            $iNewSID = $results['newsid'] = $newSurvey->sid;
            $results['surveys']++;
            if (!empty($iDesiredSurveyId) && $iNewSID != $iDesiredSurveyId) {
                $results['importwarnings'][] = gT("The desired survey ID was already in use, therefore a random one was assigned.");
            }
        } else {
            $results['error'] = CHtml::errorSummary($newSurvey, gT("Unable to import survey."));
            return $results;
        }
    }

    // Single flag to indicate if the attachements format is wrong, to avoid showing the warning multiple times
    $wrongAttachmentsFormat = false;

    // Import survey languagesettings table ===================================================================================
    foreach ($xml->surveys_languagesettings->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key => $value) {
            $insertdata[(string) $key] = (string) $value;
        }

        if (!in_array($insertdata['surveyls_language'], $aLanguagesSupported)) {
            continue;
        }

        // Assign new survey ID
        $insertdata['surveyls_survey_id'] = $iNewSID;

        // Assign new survey name (if a copy)
        if ($isCopying) {
            $insertdata['surveyls_title'] = $sNewSurveyName;
        }

        if ($bTranslateInsertansTags) {
            $insertdata['surveyls_title'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_title']);
            if (isset($insertdata['surveyls_description'])) {
                $insertdata['surveyls_description'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_description']);
            }
            if (isset($insertdata['surveyls_welcometext'])) {
                $insertdata['surveyls_welcometext'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_welcometext']);
            }
            if (isset($insertdata['surveyls_urldescription'])) {
                $insertdata['surveyls_urldescription'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_urldescription']);
            }
            if (isset($insertdata['surveyls_email_invite'])) {
                $insertdata['surveyls_email_invite'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_invite']);
            }
            if (isset($insertdata['surveyls_email_remind'])) {
                $insertdata['surveyls_email_remind'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_remind']);
            }
            if (isset($insertdata['surveyls_email_register'])) {
                $insertdata['surveyls_email_register'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_register']);
            }
            if (isset($insertdata['surveyls_email_confirm'])) {
                $insertdata['surveyls_email_confirm'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_confirm']);
            }
        } else {
            if (checkOldLinks('survey', $iOldSID, $insertdata['surveyls_title'])) {
                $results['importwarnings'][] = gT("Survey title has outdated links.");
            }
            if (isset($insertdata['surveyls_description']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_description'])) {
                $results['importwarnings'][] = gT("Survey description has outdated links.");
            }
            if (isset($insertdata['surveyls_welcometext']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_welcometext'])) {
                $results['importwarnings'][] = gT("Welcome text has outdated links.");
            }
            if (isset($insertdata['surveyls_urldescription']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_urldescription'])) {
                $results['importwarnings'][] = gT("URL description has outdated links.");
            }
            if (isset($insertdata['surveyls_email_invite']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_email_invite'])) {
                $results['importwarnings'][] = gT("Invitation email template has outdated links.");
            }
            if (isset($insertdata['surveyls_email_remind']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_email_remind'])) {
                $results['importwarnings'][] = gT("Reminder email template has outdated links.");
            }
            if (isset($insertdata['surveyls_email_register']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_email_register'])) {
                $results['importwarnings'][] = gT("Registration email template has outdated links.");
            }
            if (isset($insertdata['surveyls_email_confirm']) && checkOldLinks('survey', $iOldSID, $insertdata['surveyls_email_confirm'])) {
                $results['importwarnings'][] = gT("Confirmation email template has outdated links.");
            }
        }

        // Email attachments are with relative paths on the file, but are currently expected to be saved as absolute.
        // Transforming them from relative paths to absolute paths.
        if (!empty($insertdata['attachments'])) {
            // NOTE: Older LSS files have attachments as a serialized array, while newer ones have it as a JSON string.
            // Serialized attachments are not supported anymore.
            $attachments = json_decode($insertdata['attachments'], true);
            if (!empty($attachments) && is_array($attachments)) {
                $uploadDir = realpath(Yii::app()->getConfig('uploaddir'));
                foreach ($attachments as &$template) {
                    foreach ($template as &$attachment) {
                        if (!isAbsolutePath($attachment['url'])) {
                            $attachment['url'] = $uploadDir . DIRECTORY_SEPARATOR . $attachment['url'];
                        }
                        if ($bTranslateInsertansTags) {
                            $attachment['url'] = translateLinks('survey', $iOldSID, $iNewSID, $attachment['url'], true);
                        }
                    }
                    // If links are not translated and the email templates have attachments, we need to show a warning
                    if (!$bTranslateInsertansTags && !empty($template)) {
                        $hasOldAttachments = true;
                    }
                }
            } elseif (is_null($attachments)) {
                // JSON decode failed. Most probably the attachments were in the PHP serialization format.
                $wrongAttachmentsFormat = true;
            }
            $insertdata['attachments'] = serialize($attachments);
        }

        if (isset($insertdata['surveyls_attributecaptions']) && substr((string) $insertdata['surveyls_attributecaptions'], 0, 1) != '{') {
            unset($insertdata['surveyls_attributecaptions']);
        }
        $aColumns = SurveyLanguageSetting::model()->attributes;
        $insertdata = array_intersect_key($insertdata, $aColumns);

        $surveyLanguageSetting = new SurveyLanguageSetting();
        $surveyLanguageSetting->setAttributes($insertdata, false);
        try {
            // Clear alias if it was already in use
            $surveyLanguageSetting->checkAliasUniqueness();
            if ($surveyLanguageSetting->hasErrors('surveyls_alias')) {
                $languageData = getLanguageData();
                $results['importwarnings'][] = sprintf(
                    gT("The survey alias for '%s' has been cleared because it was already in use by another survey."),
                    $languageData[$insertdata['surveyls_language']]['description']
                );
                unset($surveyLanguageSetting->surveyls_alias);
                $surveyLanguageSetting->clearErrors('surveyls_alias');
            }
            if (!$surveyLanguageSetting->save()) {
                $errors = $surveyLanguageSetting->errors;
                // Clean up 
                Survey::model()->deleteSurvey($iNewSID);
                $errorsStr = '';
                foreach ($errors as $attribute => $error) {
                    $errorsStr.= $error[0]. "\n";
                }
                throw new Exception(gT("Error: Failed to import survey language settings.") . " " . $errorsStr);
            }
        } catch (CDbException $e) {
            throw new Exception(gT("Error") . ": Failed to import survey language settings - data is invalid.");
        }
    }

    if ($wrongAttachmentsFormat) {
        $results['importwarnings'][] = gT("The email attachments have not been imported because they were in an old format.");
    }

    if ($hasOldAttachments) {
        $results['importwarnings'][] = gT("Email templates have attachments but the resources have not been copied. Please update the attachments manually.");
    }

    // Import groups table ===================================================================================
    if (isset($xml->groups->rows->row)) {
        foreach ($xml->groups->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $iOldSID = $insertdata['sid'];
            $insertdata['sid'] = $iNewSID;
            $oldgid = $insertdata['gid'];
            unset($insertdata['gid']); // save the old qid
            $aDataL10n = array();
            if (!isset($xml->group_l10ns->rows->row)) {
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }
                // now translate any links
                if ($bTranslateInsertansTags) {
                    $insertdata['group_name'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['group_name']);
                    $insertdata['description'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['description']);
                } else {
                    if (checkOldLinks('survey', $iOldSID, $insertdata['group_name'])) {
                        $results['importwarnings'][] = gT("Group name has outdated links.");
                    }
                    if (checkOldLinks('survey', $iOldSID, $insertdata['description'])) {
                        $results['importwarnings'][] = gT("Group description has outdated links.");
                    }
                }
                $aDataL10n['group_name'] = $insertdata['group_name'];
                $aDataL10n['description'] = $insertdata['description'];
                $aDataL10n['language'] = $insertdata['language'];
                unset($insertdata['group_name']);
                unset($insertdata['description']);
                unset($insertdata['language']);
            }
            if (!isset($aGIDReplacements[$oldgid])) {
                $questionGroup = new QuestionGroup();
                $questionGroup->attributes = $insertdata;
                $questionGroup->sid = $iNewSID;
                if (!$questionGroup->save()) {
                    throw new Exception(gT("Error") . ": Failed to insert data [3]<br /> " . json_encode($questionGroup->getErrors()));
                }
                $newgid = $questionGroup->gid;
                $aGIDReplacements[$oldgid] = $newgid; // add old and new qid to the mapping array
                $results['groups']++;
            }
            if (!empty($aDataL10n)) {
                $aDataL10n['gid'] = $aGIDReplacements[$oldgid];
                $oQuestionGroupL10n = new QuestionGroupL10n();
                $oQuestionGroupL10n->setAttributes($aDataL10n, false);
                $oQuestionGroupL10n->save();
            }
        }
    }
    if (isset($xml->group_l10ns->rows->row)) {
        foreach ($xml->group_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                continue;
            }
            if (isset($aGIDReplacements[$insertdata['gid']])) {
                $insertdata['gid'] = $aGIDReplacements[$insertdata['gid']];
            } else {
                continue; //Skip invalid group ID
            }
            // now translate any links
            // TODO: Should this depend on $bTranslateLinksFields?
            $insertdata['group_name'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['group_name']);
            if (isset($insertdata['description'])) {
                $insertdata['description'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['description']);
            }
            // #14646: fix utf8 encoding issue
            if (!mb_detect_encoding($insertdata['group_name'], 'UTF-8', true)) {
                $insertdata['group_name'] = mb_convert_encoding($insertdata['group_name'], 'UTF-8', 'ISO-8859-1');
            }
            // Insert the new group
            $oQuestionGroupL10n = new QuestionGroupL10n();
            $oQuestionGroupL10n->setAttributes($insertdata, false);
            if (!$oQuestionGroupL10n->save()) {
                throw new Exception(gT("Error while saving group: ") . print_r($oQuestionGroupL10n->errors, true));
            }
        }
    }

    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)
    $aQuestionsMapping = array(); // collect all old and new question codes for replacement
    /** @var Question[] */
    $importedQuestions = [];
    if (isset($xml->questions)) {
        // There could be surveys without a any questions.
        foreach ($xml->questions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }

            if (!isset($xml->question_l10ns->rows->row)) {
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }
            }
            if ($insertdata['gid'] == 0) {
                continue;
            }
            if (!isset($insertdata['mandatory']) || trim($insertdata['mandatory']) == '') {
                $insertdata['mandatory'] = 'N';
            }

            $iOldSID = $insertdata['sid'];
            $iOldGID = $insertdata['gid'];
            $insertdata['sid'] = $iNewSID;
            $insertdata['gid'] = $aGIDReplacements[$insertdata['gid']];
            $iOldQID = $insertdata['qid']; // save the old qid
            unset($insertdata['qid']);

            // now translate any links
            if (!isset($xml->question_l10ns->rows->row)) {
                if ($bTranslateInsertansTags) {
                    $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                    $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
                }
                $oQuestionL10n = new QuestionL10n();
                $oQuestionL10n->question = $insertdata['question'];
                $oQuestionL10n->help = $insertdata['help'];
                $oQuestionL10n->language = $insertdata['language'];
                unset($insertdata['question']);
                unset($insertdata['help']);
                unset($insertdata['language']);
            }
            if (!$bConvertInvalidQuestionCodes) {
                $sScenario = 'archiveimport';
            } else {
                $sScenario = 'import';
            }

            $oQuestion = new Question($sScenario);
            $oQuestion->setAttributes($insertdata, false);

            if (!isset($aQIDReplacements[$iOldQID])) {
                // Try to fix question title for valid question code enforcement
                if (!$oQuestion->validate(array('title'))) {
                    $sOldTitle = $oQuestion->title;
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', (string) $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'q' . $sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                $index = 0;
                $rand = mt_rand(0, 1024);
                while (!$oQuestion->validate(array('title'))) {
                    $sNewTitle = 'r' . $rand . 'q' . $index;
                    $index++;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;
                    if ($attempts > 10) {
                        throw new Exception(gT("Error") . ": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    throw new Exception(gT("Error while saving: ") . print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                $results['questions']++;
                $importedQuestions[$aQIDReplacements[$iOldQID]] = $oQuestion;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a question_l10ns section.
            if (!$bTranslateInsertansTags && !isset($xml->question_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->question)) {
                    $results['importwarnings'][] = sprintf(gT("Question %s has outdated links."), $oQuestion->title);
                }
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->help)) {
                    $results['importwarnings'][] = sprintf(gT("Help text for question %s has outdated links."), $oQuestion->title);
                }
            }

            if (isset($oQuestionL10n)) {
                $oQuestionL10n->qid = $aQIDReplacements[$iOldQID];
                $oQuestionL10n->save();
                unset($oQuestionL10n);
            }
            // Set a warning if question title was updated
            if (isset($sNewTitle) && isset($sOldTitle)) {
                $results['importwarnings'][] = sprintf(gT("Question code %s was updated to %s."), $sOldTitle, $sNewTitle);
                $aQuestionCodeReplacements[$sOldTitle] = $sNewTitle;
                unset($sNewTitle);
                unset($sOldTitle);
            }

            // question codes in format "38612X105X3011" are collected for replacing
            $aQuestionsMapping[$iOldSID . 'X' . $iOldGID . 'X' . $iOldQID] = $iNewSID . 'X' . $oQuestion->gid . 'X' . $oQuestion->qid;
        }
    }

    // Import subquestions -------------------------------------------------------
    /** @var Question[] */
    $importedSubQuestions = [];
    if (isset($xml->subquestions)) {
        foreach ($xml->subquestions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }

            if (!isset($xml->question_l10ns->rows->row)) {
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }
            }
            if ($insertdata['gid'] == 0) {
                continue;
            }
            if (!isset($insertdata['mandatory']) || trim($insertdata['mandatory']) == '') {
                $insertdata['mandatory'] = 'N';
            }
            $iOldSID = $insertdata['sid'];
            $insertdata['sid'] = $iNewSID;
            $insertdata['gid'] = $aGIDReplacements[(int) $insertdata['gid']];
            $iOldQID = (int) $insertdata['qid'];
            unset($insertdata['qid']); // save the old qid
            $insertdata['parent_qid'] = $aQIDReplacements[(int) $insertdata['parent_qid']]; // remap the parent_qid
            if (!isset($insertdata['help'])) {
                $insertdata['help'] = '';
            }            // now translate any links
            if (!isset($xml->question_l10ns->rows->row)) {
                if ($bTranslateInsertansTags) {
                    $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                    $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
                }
                $oQuestionL10n = new QuestionL10n();
                $oQuestionL10n->question = $insertdata['question'];
                $oQuestionL10n->help = $insertdata['help'];
                $oQuestionL10n->language = $insertdata['language'];
                unset($insertdata['question']);
                unset($insertdata['help']);
                unset($insertdata['language']);
            }
            if (!$bConvertInvalidQuestionCodes) {
                $sScenario = 'archiveimport';
            } else {
                $sScenario = 'import';
            }

            $oQuestion = new Question($sScenario);
            $oQuestion->setAttributes($insertdata, false);

            if (!isset($aQIDReplacements[$iOldQID])) {
                // Try to fix question title for valid question code enforcement
                if (!$oQuestion->validate(array('title'))) {
                    $sOldTitle = $oQuestion->title;
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', (string) $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'sq' . $sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                while (!$oQuestion->validate(array('title'))) {
                    if (!isset($index)) {
                        $index = 0;
                        $rand = mt_rand(0, 1024);
                    } else {
                        $index++;
                    }

                    $sNewTitle = 'r' . $rand . 'sq' . $index;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;

                    if ($attempts > 10) {
                        throw new Exception(gT("Error") . ": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    throw new Exception(gT("Error while saving: ") . print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                $results['subquestions']++;
                $importedSubQuestions[$aQIDReplacements[$iOldQID]] = $oQuestion;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a question_l10ns section.
            if (!$bTranslateInsertansTags && !isset($xml->question_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->question)) {
                    $parentQuestion = $importedQuestions[$insertdata['parent_qid']];
                    $results['importwarnings'][] = sprintf(gT("Subquestion %s of question %s has outdated links."), $oQuestion->title, $parentQuestion->title);
                }
            }

            if (isset($oQuestionL10n)) {
                $oQuestionL10n->qid = $aQIDReplacements[$iOldQID];
                $oQuestionL10n->save();
                unset($oQuestionL10n);
            }

            // Set a warning if question title was updated
            if (isset($sNewTitle) && isset($sOldTitle)) {
                $results['importwarnings'][] = sprintf(gT("Title of subquestion %s was updated to %s."), $sOldTitle, $sNewTitle); // Maybe add the question title ?
                $aQuestionCodeReplacements[$sOldTitle] = $sNewTitle;
                unset($sNewTitle);
                unset($sOldTitle);
            }
        }
    }

    //  Import question_l10ns
    if (isset($xml->question_l10ns->rows->row)) {
        foreach ($xml->question_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            if ($bTranslateInsertansTags) {
                $insertdata['question'] = isset($insertdata['question']) ? translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']) : '';
                $insertdata['help'] = isset($insertdata['help']) ? translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']) : '';
            }
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[$insertdata['qid']];
            } else {
                continue; //Skip invalid question ID
            }

            // question codes in format "38612X105X3011" are collected for replacing
            $aQuestionsMapping[$iOldSID . 'X' . $iOldGID . 'X' . $iOldQID . $oQuestion->title] = $iNewSID . 'X' . $oQuestion->gid . 'X' . $oQuestion->qid . $oQuestion->title;
            $oQuestionL10n = new QuestionL10n();
            $oQuestionL10n->setAttributes($insertdata, false);
            $oQuestionL10n->save();

            // If translate links is disabled, check for old links.
            if (!$bTranslateInsertansTags) {
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->question)) {
                    // The question_l10ns includes L10n data for both questions and subquestions.
                    // If it's a normal question, it should be in $importedQuestions.
                    if (isset($importedQuestions[$insertdata['qid']])) {
                        $question = $importedQuestions[$insertdata['qid']];
                        $results['importwarnings'][] = sprintf(gT("Question %s has outdated links."), $question->title);
                    } elseif (isset($importedSubQuestions[$insertdata['qid']])) {
                        $subquestion = $importedSubQuestions[$insertdata['qid']];
                        $parentQuestion = $importedQuestions[$subquestion->parent_qid];
                        $results['importwarnings'][] = sprintf(gT("Subquestion %s of question %s has outdated links."), $subquestion->title, $parentQuestion->title);
                    }
                }
                if (checkOldLinks('survey', $iOldSID, $oQuestionL10n->help)) {
                    // If it's a normal question, it should be in $importedQuestions. Subquestions are not
                    // supposed to have a help text.
                    if (isset($importedQuestions[$insertdata['qid']])) {
                        $question = $importedQuestions[$insertdata['qid']];
                        $results['importwarnings'][] = sprintf(gT("Help text for question %s has outdated links."), $question->title);
                    }
                }
            }
        }
    }

    // Import answers ------------------------------------------------------------
    if (isset($xml->answers)) {
        foreach ($xml->answers->rows->row as $row) {
            $insertdata = array();

            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (isset($xml->answer_l10ns->rows->row) && !empty($insertdata['aid'])) {
                $iOldAID = $insertdata['aid'];
                unset($insertdata['aid']);
            }
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                continue;
            }

            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the parent_qid

            if (!isset($xml->answer_l10ns->rows->row)) {
                // now translate any links
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }
                if ($bTranslateInsertansTags) {
                    $insertdata['answer'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['answer']);
                }
                $oAnswerL10n = new AnswerL10n();
                $oAnswerL10n->answer = $insertdata['answer'];
                $oAnswerL10n->language = $insertdata['language'];
                unset($insertdata['answer']);
                unset($insertdata['language']);
            }

            $oAnswer = new Answer();
            $oAnswer->setAttributes($insertdata, false);
            if ($oAnswer->save() && isset($xml->answer_l10ns->rows->row) && isset($iOldAID)) {
                $aAIDReplacements[$iOldAID] = $oAnswer->aid;
            }

            // If translate links is disabled, check for old links.
            // We only do it here if the XML doesn't have a answer_l10ns section.
            if (!$bTranslateInsertansTags && !isset($xml->answer_l10ns->rows->row)) {
                if (checkOldLinks('survey', $iOldSID, $oAnswerL10n->answer)) {
                    $question = $importedQuestions[$insertdata['qid']];
                    $results['importwarnings'][] = sprintf(gT("Answer option %s of question %s has outdated links."), $insertdata['code'], $question->title);
                }
            }

            $results['answers']++;
            if (isset($oAnswerL10n)) {
                $oAnswer = Answer::model()->findByAttributes(['qid' => $insertdata['qid'], 'code' => $insertdata['code'], 'scale_id' => $insertdata['scale_id']]);
                if (isset($oAnswer->aid)) {
                    $oAnswerL10n->aid = $oAnswer->aid;
                }
                $oAnswerL10n->save();
                unset($oAnswerL10n);
            }
        }
    }

    //  Import answer_l10ns
    if (isset($xml->answer_l10ns->rows->row)) {
        foreach ($xml->answer_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            if ($bTranslateInsertansTags) {
                $insertdata['answer'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['answer']);
            }
            if (isset($aAIDReplacements[$insertdata['aid']])) {
                $insertdata['aid'] = $aAIDReplacements[$insertdata['aid']];
            } else {
                continue; //Skip invalid answer ID
            }
            $oAnswerL10n = new AnswerL10n();
            $oAnswerL10n->setAttributes($insertdata, false);
            $oAnswerL10n->save();

            // If translate links is disabled, check for old links.
            if (!$bTranslateInsertansTags) {
                if (checkOldLinks('survey', $iOldSID, $oAnswerL10n->answer)) {
                    $question = $importedQuestions[$insertdata['qid']];
                    $results['importwarnings'][] = sprintf(gT("Answer option %s of question %s has outdated links."), $insertdata['code'], $question->title);
                }
            }
        }
    }

    // Import questionattributes -------------------------------------------------
    if (isset($xml->question_attributes)) {
        $aAllAttributes = questionHelper::getAttributesDefinitions();
        /** @var array<integer,array<string,mixed>> List of "answer order" related attributes, grouped by qid */
        $answerOrderAttributes = [];
        foreach ($xml->question_attributes->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }

            // take care of renaming of date min/max adv. attributes fields
            if ($iDBVersion < 170) {
                if (isset($insertdata['attribute'])) {
                    if ($insertdata['attribute'] == 'dropdown_dates_year_max') {
                        $insertdata['attribute'] = 'date_max';
                    }

                    if ($insertdata['attribute'] == 'dropdown_dates_year_min') {
                        $insertdata['attribute'] = 'date_min';
                    }
                }
            }

            unset($insertdata['qaid']);
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                continue;
            }

            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the qid

            // Question theme was previously stored as a question attribute ('question_template'), but now it
            // is a normal attribute of the Question model. So we must check if the imported question has the
            // 'question_template' attribute and use it for overriding 'question_theme_name' instead of saving
            // as QuestionAttribute.
            if ($insertdata['attribute'] == 'question_template') {
                $oQuestion = Question::model()->findByPk($insertdata['qid']);
                if (!empty($oQuestion)) {
                    $oQuestion->question_theme_name = $insertdata['value'];
                    $oQuestion->save();
                }
                continue;
            }

            // Keep "answer order" related attributes in an array to process later (because we need to combine two attributes)
            if (
                $insertdata['attribute'] == 'alphasort'
                || (
                $insertdata['attribute'] == 'random_order'
                && in_array($importedQuestions[$insertdata['qid']]->type, ['!', 'L', 'O', 'R'])
                )
            ) {
                $answerOrderAttributes[$insertdata['qid']][$insertdata['attribute']] = $insertdata['value'];
                continue;
            }

            if ($iDBVersion < 156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n']) {
                foreach ($aLanguagesSupported as $sLanguage) {
                    $insertdata['language'] = $sLanguage;

                    $questionAttribute = new QuestionAttribute();
                    $questionAttribute->attributes = $insertdata;
                    if (!$questionAttribute->save()) {
                        throw new Exception(gT("Error") . ": Failed to insert data[7]<br />");
                    }
                }
            } else {
                $questionAttribute = new QuestionAttribute();
                $questionAttribute->attributes = $insertdata;
                if (!$questionAttribute->save()) {
                    throw new Exception(gT("Error") . ": Failed to insert data[8]<br />");
                }
            }
            checkWrongQuestionAttributes($insertdata['qid']);
            $results['question_attributes']++;
        }

        // Process "answer order" attributes
        foreach ($answerOrderAttributes as $importedQid => $questionAttributes) {
            if (!empty($questionAttributes['random_order'])) {
                $insertdata = [
                'qid' => $importedQid,
                'attribute' => 'answer_order',
                'value' => 'random',
                ];
                App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                $results['question_attributes']++;
                continue;
            }
            if (!empty($questionAttributes['alphasort'])) {
                $insertdata = [
                'qid' => $importedQid,
                'attribute' => 'answer_order',
                'value' => 'alphabetical',
                ];
                App()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                $results['question_attributes']++;
            }
        }
    }

    // Import defaultvalues ------------------------------------------------------
    importDefaultValues($xml, $aLanguagesSupported, $aQIDReplacements, $results);

    $aOldNewFieldmap = reverseTranslateFieldNames($iOldSID, $iNewSID, $aGIDReplacements, $aQIDReplacements);

    // Import conditions ---------------------------------------------------------
    if (isset($xml->conditions)) {
        $results['conditions'] = 0;
        $oldcqid = 0;
        $oldqidanscode = 0;
        $oldcgid = 0;
        $oldcsid = 0;
        foreach ($xml->conditions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            // replace the qid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this condition is orphan -> error, skip this record)
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[$insertdata['qid']]; // remap the qid
            } else {
                // a problem with this answer record -> don't consider
                continue;
            }
            if ($insertdata['cqid'] != 0) {
                if (isset($aQIDReplacements[$insertdata['cqid']])) {
                    $oldcqid = $insertdata['cqid']; //Save for cfield transformation
                    $insertdata['cqid'] = $aQIDReplacements[$insertdata['cqid']]; // remap the qid
                } else {
                    // a problem with this answer record -> don't consider
                    continue;
                }

                list($oldcsid, $oldcgid, $oldqidanscode) = explode("X", (string) $insertdata["cfieldname"], 3);

                // replace the gid for the new one in the cfieldname(if there is no new gid in the $aGIDReplacements array it means that this condition is orphan -> error, skip this record)
                if (!isset($aGIDReplacements[$oldcgid])) {
                    continue;
                }
            }

            unset($insertdata["cid"]);

            // recreate the cfieldname with the new IDs
            if ($insertdata['cqid'] != 0) {
                if (preg_match("/^\+/", $oldcsid)) {
                    $newcfieldname = '+' . $iNewSID . "X" . $aGIDReplacements[$oldcgid] . "X" . $insertdata["cqid"] . substr($oldqidanscode, strlen((string) $oldcqid));
                } else {
                    $newcfieldname = $iNewSID . "X" . $aGIDReplacements[$oldcgid] . "X" . $insertdata["cqid"] . substr($oldqidanscode, strlen((string) $oldcqid));
                }
            } else {
                // The cfieldname is a not a previous question cfield but a {XXXX} replacement field
                $newcfieldname = $insertdata["cfieldname"];
            }
            $insertdata["cfieldname"] = $newcfieldname;
            if (trim((string) $insertdata["method"]) == '') {
                $insertdata["method"] = '==';
            }

            // Now process the value and replace @sgqa@ codes
            if (preg_match("/^@(.*)@$/", (string) $insertdata["value"], $cfieldnameInCondValue)) {
                if (isset($aOldNewFieldmap[$cfieldnameInCondValue[1]])) {
                    $newvalue = '@' . $aOldNewFieldmap[$cfieldnameInCondValue[1]] . '@';
                    $insertdata["value"] = $newvalue;
                }
            }

            // now translate any links
            $result = Condition::model()->insertRecords($insertdata);
            if (!$result) {
                throw new Exception(gT("Error") . ": Failed to insert data[10]<br />");
            }
            $results['conditions']++;
        }
    }

    // Import assessments --------------------------------------------------------
    if (isset($xml->assessments)) {
        $aASIDReplacements = [];
        foreach ($xml->assessments->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['id']) || (int)$insertdata['id'] < 1) {
                continue;
            }
            $oldasid = $insertdata['id'];
            unset($insertdata['id']);

            if (isset($aASIDReplacements[$oldasid])) {
                $insertdata['id'] = $aASIDReplacements[$oldasid];
            }

            if ($insertdata['gid'] > 0) {
                $insertdata['gid'] = $aGIDReplacements[(int) $insertdata['gid']]; // remap the qid
            }

            $insertdata['sid'] = $iNewSID; // remap the survey ID
            // now translate any links
            $result = Assessment::model()->insertRecords($insertdata);
            if (!$result) {
                throw new Exception(gT("Error") . ": Failed to insert data[11]<br />");
            }

            if (!isset($aASIDReplacements[$oldasid])) {
                $aASIDReplacements[$oldasid] = $result->id; // add old and new id to the mapping array
                $results['assessments']++;
            }
        }
    }

    // Import quota --------------------------------------------------------------
    if (isset($xml->quota)) {
        foreach ($xml->quota->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['id']) || (int)$insertdata['id'] < 1) {
                continue;
            }
            $insertdata['sid'] = $iNewSID; // remap the survey ID
            $oldid = $insertdata['id'];
            unset($insertdata['id']);
            // now translate any links
            $result = Quota::model()->insertRecords($insertdata);
            if (!$result) {
                throw new Exception(gT("Error") . ": Failed to insert data[12]<br />");
            }
            $aQuotaReplacements[$oldid] = getLastInsertID('{{quota}}');
            $results['quota']++;
        }
    }

    // Import quota_members ------------------------------------------------------
    if (isset($xml->quota_members)) {
        foreach ($xml->quota_members->rows->row as $row) {
            $quotaMember = new QuotaMember();
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['quota_id']) || (int)$insertdata['quota_id'] < 1) {
                continue;
            }
            $insertdata['sid'] = $iNewSID; // remap the survey ID
            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the qid
            if (isset($insertdata['quota_id'])) {
                $insertdata['quota_id'] = $aQuotaReplacements[(int) $insertdata['quota_id']]; // remap the qid
            }
            unset($insertdata['id']);
            // now translate any links
            $quotaMember->setAttributes($insertdata, false);

            if (!$quotaMember->validate()) {
                // Display validation errors
                foreach ($quotaMember->errors as $attribute => $errors) {
                    $errorText = '';
                    foreach ($errors as $error) {
                        $errorText .= 'Field "' . $attribute . '": ' . $error . " Value: '{$quotaMember->$attribute}'\n";
                    }
                    throw new Exception(gT("Error:") . " Failed to insert quota member" . "\n" . $errorText);
                }
            }
            if (!$quotaMember->save()) {
                throw new Exception(gT("Error:") . " Failed to insert quota member database entry\n");
            }
            $results['quotamembers']++;
        }
    }

    // Import quota_languagesettings----------------------------------------------
    if (isset($xml->quota_languagesettings)) {
        foreach ($xml->quota_languagesettings->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['quotals_quota_id']) || (int)$insertdata['quotals_quota_id'] < 1) {
                continue;
            }
            $insertdata['quotals_quota_id'] = $aQuotaReplacements[(int) $insertdata['quotals_quota_id']]; // remap the qid
            unset($insertdata['quotals_id']);
            $quotaLanguagesSetting = new QuotaLanguageSetting('import');
            $quotaLanguagesSetting->setAttributes($insertdata, false);
            if (!$quotaLanguagesSetting->save()) {
                $header = sprintf(gT("Unable to insert quota language settings for quota %s"), $insertdata['quotals_quota_id']);
                if (isset($insertdata['quotals_language'])) {
                    $header = sprintf(gT("Unable to insert quota language settings for quota %s and language %s"), $insertdata['quotals_quota_id'], $insertdata['quotals_language']);
                }
                $results['importwarnings'][] = CHtml::errorSummary($quotaLanguagesSetting, $header);
            }
            $results['quotals']++;
        }
    }

    // Import survey_url_parameters ----------------------------------------------
    if (isset($xml->survey_url_parameters)) {
        foreach ($xml->survey_url_parameters->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['sid'] = $iNewSID; // remap the survey ID
            if (isset($insertdata['targetsqid']) && $insertdata['targetsqid'] != '') {
                $insertdata['targetsqid'] = $aQIDReplacements[(int) $insertdata['targetsqid']]; // remap the qid
            }
            if (isset($insertdata['targetqid']) && $insertdata['targetqid'] != '') {
                $insertdata['targetqid'] = $aQIDReplacements[(int) $insertdata['targetqid']]; // remap the qid
            }
            unset($insertdata['id']);
            $result = SurveyURLParameter::model()->insertRecord($insertdata);
            if (!$result) {
                throw new Exception(gT("Error") . ": Failed to insert data[14]<br />");
            }
            $results['survey_url_parameters']++;
        }
    }

    // Import Survey plugins settings
    if (isset($xml->plugin_settings)) {
        $pluginNamesWarning = array(); // To shown not exist warning only one time.
        foreach ($xml->plugin_settings->rows->row as $row) {
            // Find plugin id
            if (isset($row->name)) {
                $oPlugin = Plugin::model()->find("name = :name", array(":name" => $row->name));
                if ($oPlugin) {
                    $setting = new PluginSetting();
                    $setting->plugin_id = $oPlugin->id;
                    $setting->model = "Survey";
                    $setting->model_id = $iNewSID;
                    $setting->key = (string) $row->key;
                    $setting->value = (string) $row->value;
                    if ($setting->save()) {
                        $results['plugin_settings']++;
                    } else {
                        $results['importwarnings'][] = sprintf(gT("Error when saving %s for plugin %s"), CHtml::encode($row->key), CHtml::encode($row->name));
                    }
                } elseif (!isset($pluginNamesWarning[(string) $row->name])) {
                    $results['importwarnings'][] = sprintf(gT("Plugin %s didn't exist, settings not imported"), CHtml::encode($row->name));
                    $pluginNamesWarning[(string) $row->name] = 1;
                }
            }
        }
    }

    //// Import Survey theme settings
    $aTemplateConfiguration = array();

    // original theme
    if (isset($xml->themes_inherited)) {
        foreach ($xml->themes_inherited->theme as $theme_key => $theme_row) {
            if ((string)$theme_row->template_name === $sTemplateName) {
                $aTemplateConfiguration['theme_original']['options'] = (array)$theme_row->config->options;
                $results['theme_original'] = json_encode($theme_row->config->options);
            }
        }
    }


    if (isset($xml->themes)) {
        // current theme options
        if (!empty($sTemplateName)) {
            $oTemplateConfigurationCurrent = TemplateConfiguration::getInstance($sTemplateName);
            //$oTemplateConfigurationCurrent->bUseMagicInherit = true;
            $aTemplateConfiguration['theme_current']['options'] = json_decode((string) $oTemplateConfigurationCurrent->attributes['options'], true);
        }

        // survey theme options
        foreach ($xml->themes->theme as $theme_key => $theme_row) {
            // skip if theme doesn't exist
            if (!Template::checkIfTemplateExists((string)$theme_row->template_name)) {
                // show warning if survey theme doesn't exist
                if ((string)$theme_row->template_name === $sTemplateName) {
                    $results['template_deleted'] = '1';
                }
                continue;
            }
            // insert into Template configuration table
            $result = TemplateManifest::importManifestLss($iNewSID, $theme_row);
            if ($result) {
                $results['themes']++;
                if ((string)$theme_row->template_name === $sTemplateName) {
                    if (isset($theme_row->config->options)) {
                        $options = $theme_row->config->options;

                        // set each key value to 'inherit' if options are set to 'inherit'
                        if ((string)$options === 'inherit' && isset($aTemplateConfiguration['theme_current']['options'])) {
                            $options = $aTemplateConfiguration['theme_current']['options'];
                            $options = array_fill_keys(array_keys($options), 'inherit');
                        }

                        $aThemeOptionsData = array();
                        foreach ((array)$options as $key => $value) {
                            if ($value == 'inherit') {
                                $sOldValue = $aTemplateConfiguration['theme_original']['options'][$key] ?? '';
                                $sNewValue = $aTemplateConfiguration['theme_current']['options'][$key] ?? '';
                                if (!empty($sOldValue) && !empty($sNewValue) && $sOldValue !== $sNewValue) {
                                    // used to send original theme options data to controller action if client wants to restore original theme options
                                    $aThemeOptionsData[$key] = $aTemplateConfiguration['theme_original']['options'][$key];
                                    // used to display difference between options
                                    $aThemeOptionsDifference = array();
                                    $aThemeOptionsDifference['option'] = $key;
                                    $aThemeOptionsDifference['current_value'] = $aTemplateConfiguration['theme_current']['options'][$key];
                                    $aThemeOptionsDifference['original_value'] = $aTemplateConfiguration['theme_original']['options'][$key];
                                    $results['theme_options_differences'][] = $aThemeOptionsDifference;
                                }
                            }
                        }

                        $results['theme_options_original_data'] = json_encode($aThemeOptionsData);
                    }

                    $aTemplateConfiguration['theme_survey']['options'] = (array)$theme_row->config->options;
                }
            } else {
                $results['importwarnings'][] = gT("Error") . ": Failed to insert data[18]<br />";
            }
        }
    }

    // Set survey rights
    Permission::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'], $iNewSID);
    $aOldNewFieldmap = reverseTranslateFieldNames($iOldSID, $iNewSID, $aGIDReplacements, $aQIDReplacements);
    $results['FieldReMap'] = $aOldNewFieldmap;
    LimeExpressionManager::SetSurveyId($iNewSID);
    translateInsertansTags($iNewSID, $iOldSID, $aOldNewFieldmap);
    replaceExpressionCodes($iNewSID, $aQuestionCodeReplacements);
    replaceExpressionCodes($iNewSID, $aQuestionsMapping); // replace question codes in format "38612X105X3011"
    if (count($aQuestionCodeReplacements)) {
        array_unshift($results['importwarnings'], "<span class='warningtitle'>" . gT('Attention: Several question codes were updated. Please check these carefully as the update  may not be perfect with customized expressions.') . '</span>');
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);
    return $results;
}

/**
 * This function checks if there are set wrong values ('Y' or 'N') into table
 * question_attributes. These are set to 1 and 0 if needed.
 *
 * @param $questionId
 */
function checkWrongQuestionAttributes($questionId)
{
    //these attributes could be wrongly set to 'Y' or 'N' instead of 1 and 0
    $attributesTobeChecked = ['statistics_showgraph', 'public_statistics' , 'page_break' , 'other_numbers_only',
        'other_comment_mandatory', 'hide_tip' , 'hidden', 'exclude_all_others_auto',
        'commented_checkbox_auto', 'num_value_int_only', 'alphasort', 'use_dropdown',
        'slider_default_set', 'slider_layout', 'slider_middlestart', 'slider_reset',
        'slider_reversed', 'slider_showminmax', 'value_range_allows_missing'];
    $questionAttributes = QuestionAttribute::model()->findAllByAttributes(['qid' => $questionId]);
    foreach ($questionAttributes as $questionAttribute) {
        if (in_array($questionAttribute->attribute, $attributesTobeChecked)) {
            //now check if value is 0 or 1 (if not then reset the wrong values ('Y' or 'N')
            if ($questionAttribute->value === 'Y') {
                $questionAttribute->value = 1;
                $questionAttribute->save();
            } elseif ($questionAttribute->value === 'N') {
                $questionAttribute->value = 0;
                $questionAttribute->save();
            }
        }
    }
}

/**
 * @param string $sFullFilePath
 * @return mixed
 */
function XMLImportTokens($sFullFilePath, $iSurveyID, $sCreateMissingAttributeFields = true)
{
    Yii::app()->loadHelper('database');
    $survey = Survey::model()->findByPk($iSurveyID);
    $sXMLdata = (string) file_get_contents($sFullFilePath);
    $xml = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
    $results = [];
    $results['warnings'] = array();
    if ($xml->LimeSurveyDocType != 'Tokens') {
        $results['error'] = gT("This is not a valid participant data XML file.");
        return $results;
    }

    if (!isset($xml->tokens->fields)) {
        $results['tokens'] = 0;
        return $results;
    }

    $results['tokens'] = 0;
    $results['tokenfieldscreated'] = 0;

    if ($sCreateMissingAttributeFields) {
        // Get a list with all fieldnames in the XML
        $aXLMFieldNames = array();
        foreach ($xml->tokens->fields->fieldname as $sFieldName) {
            $aXLMFieldNames[] = (string) $sFieldName;
        }
        // Get a list of all fieldnames in the survey participant list
        $aTokenFieldNames = Yii::app()->db->getSchema()->getTable($survey->tokensTableName, true);
        $aTokenFieldNames = array_keys($aTokenFieldNames->columns);
        $aFieldsToCreate = array_diff($aXLMFieldNames, $aTokenFieldNames);
        if (!function_exists('db_upgrade_all')) {
            Yii::app()->loadHelper('update.updatedb');
        }

        foreach ($aFieldsToCreate as $sField) {
            if (strpos($sField, 'attribute') !== false) {
                addColumn($survey->tokensTableName, $sField, 'string');
            }
        }
    }

    switchMSSQLIdentityInsert('tokens_' . $iSurveyID, true);
    foreach ($xml->tokens->rows->row as $row) {
        $insertdata = array();

        foreach ($row as $key => $value) {
            $insertdata[(string) $key] = (string) $value;
        }

        $token = Token::create($iSurveyID, 'allowinvalidemail');
        $token->setAttributes($insertdata, false);
        if (!$token->encryptSave(true)) {
            $results['warnings'][] = CHtml::errorSummary($token, gT("Skipped participant entry:"));
        } else {
            $results['tokens']++;
        }
    }
    switchMSSQLIdentityInsert('tokens_' . $iSurveyID, false);
    if (Yii::app()->db->getDriverName() == 'pgsql') {
        try {
            Yii::app()->db->createCommand("SELECT pg_catalog.setval(pg_get_serial_sequence('{{tokens_" . $iSurveyID . "}}', 'tid'), (SELECT MAX(tid) FROM {{tokens_" . $iSurveyID . "}}))")->execute();
        } catch (Exception $oException) {
        };
    }
    return $results;
}


/**
 * @param string $sFullFilePath
 * @return mixed
 */
function XMLImportResponses($sFullFilePath, $iSurveyID, $aFieldReMap = array())
{
    Yii::app()->loadHelper('database');
    $survey = Survey::model()->findByPk($iSurveyID);

    switchMSSQLIdentityInsert('survey_' . $iSurveyID, true);
    $results = [];
    $results['responses'] = 0;

    if (\PHP_VERSION_ID < 80000) {
        libxml_disable_entity_loader(false);
    }
    $oXMLReader = new XMLReader();
    $oXMLReader->open($sFullFilePath);
    if (\PHP_VERSION_ID < 80000) {
        libxml_disable_entity_loader(true);
    }
    if (Yii::app()->db->schema->getTable($survey->responsesTableName) !== null) {
        // Refresh metadata to make sure it reflects the current survey
        SurveyDynamic::model($iSurveyID)->refreshMetadata();
        $DestinationFields = Yii::app()->db->schema->getTable($survey->responsesTableName)->getColumnNames();
        while ($oXMLReader->read()) {
            if ($oXMLReader->name === 'LimeSurveyDocType' && $oXMLReader->nodeType == XMLReader::ELEMENT) {
                $oXMLReader->read();
                if ($oXMLReader->value != 'Responses') {
                    $results['error'] = gT("This is not a valid response data XML file.");
                    return $results;
                }
            }
            if ($oXMLReader->name === 'rows' && $oXMLReader->nodeType == XMLReader::ELEMENT) {
                while ($oXMLReader->read()) {
                    if ($oXMLReader->name === 'row' && $oXMLReader->nodeType == XMLReader::ELEMENT) {
                        $aInsertData = array();
                        while ($oXMLReader->read() && $oXMLReader->name != 'row') {
                            $sFieldname = $oXMLReader->name;
                            if ($sFieldname[0] == '_') {
                                $sFieldname = substr($sFieldname, 1);
                            }
                            $sFieldname = str_replace('-', '#', $sFieldname);
                            if (isset($aFieldReMap[$sFieldname])) {
                                $sFieldname = $aFieldReMap[$sFieldname];
                            }
                            if (!$oXMLReader->isEmptyElement) {
                                $oXMLReader->read();
                                if (in_array($sFieldname, $DestinationFields)) {
                                    // some old response tables contain invalid column names due to old bugs
                                    $aInsertData[$sFieldname] = $oXMLReader->value;
                                }
                                $oXMLReader->read();
                            } else {
                                if (in_array($sFieldname, $DestinationFields)) {
                                    $aInsertData[$sFieldname] = '';
                                }
                            }
                        }
                        try {
                            SurveyDynamic::sid($iSurveyID);
                            $response = new SurveyDynamic();
                            $response->setAttributes($aInsertData, false);
                            if (!$response->encryptSave()) {
                                throw new Exception("Failed to save response data.");
                            }
                        } catch (Exception $e) {
                            throw new Exception(gT("Error") . ": Failed to insert data in response table<br />");
                        }
                        $results['responses']++;
                    }
                }
            }
        }
        $oXMLReader->close();

        switchMSSQLIdentityInsert('survey_' . $iSurveyID, false);
        if (Yii::app()->db->getDriverName() == 'pgsql') {
            try {
                Yii::app()->db->createCommand("SELECT pg_catalog.setval(pg_get_serial_sequence('" . $survey->responsesTableName . "', 'id'), (SELECT MAX(id) FROM " . $survey->responsesTableName . "))")->execute();
            } catch (Exception $oException) {
            };
        }
        $results['warnings'] = [];
        return $results;
    } else {
        $results['warnings'][] = gT("The survey response table could not be created.") . '<br>' . gT("Usually this is caused by having too many (sub-)questions in your survey. Please try removing questions from your survey.");
        return $results;
    }
}

/**
 * This function imports a CSV file into the response table
 * CSV file is deleted during process
 *
 * @param string $sFullFilePath
 * @param integer $iSurveyId
 * @param array $aOptions
 * Return array $result ("errors","warnings","success")
 */
function CSVImportResponses($sFullFilePath, $iSurveyId, $aOptions = array())
{

    // Default optional
    if (!isset($aOptions['bDeleteFistLine'])) {
        $aOptions['bDeleteFistLine'] = true;
    } // By default delete first line (vvimport)
    if (!isset($aOptions['sExistingId'])) {
        $aOptions['sExistingId'] = "ignore";
    } // By default exclude existing id
    if (!isset($aOptions['bNotFinalized'])) {
        $aOptions['bNotFinalized'] = false;
    } // By default don't change finalized part
    if (!isset($aOptions['sCharset']) || !$aOptions['sCharset']) {
        $aOptions['sCharset'] = "utf8";
    }
    if (!isset($aOptions['sSeparator'])) {
        $aOptions['sSeparator'] = "\t";
    }
    if (!isset($aOptions['sQuoted'])) {
        $aOptions['sQuoted'] = "\"";
    }
    // Fix some part
    if (!array_key_exists($aOptions['sCharset'], aEncodingsArray())) {
        $aOptions['sCharset'] = "utf8";
    }

    // Prepare an array of sentence for result
    $CSVImportResult = array();
    $tmpVVFile = fileCsvToUtf8($sFullFilePath, $aOptions['sCharset']);
    $aFileResponses = array();
    while (($aLineResponse = fgetcsv($tmpVVFile, 0, $aOptions['sSeparator'], $aOptions['sQuoted'])) !== false) {
        $aFileResponses[] = $aLineResponse;
    }
    if (empty($aFileResponses)) {
        $CSVImportResult['errors'][] = sprintf(gT("File is empty or you selected an invalid character set (%s)."), $aOptions['sCharset']);
        return $CSVImportResult;
    }
    if ($aOptions['bDeleteFistLine']) {
        array_shift($aFileResponses);
    }

    $aRealFieldNames = Yii::app()->db->getSchema()->getTable(SurveyDynamic::model($iSurveyId)->tableName())->getColumnNames();
    $aCsvHeader = array_shift($aFileResponses);
    LimeExpressionManager::SetDirtyFlag(); // Be sure survey EM code are up to date
    $aLemFieldNames = LimeExpressionManager::getLEMqcode2sgqa($iSurveyId);
    $aKeyForFieldNames = array(); // An array assicated each fieldname with corresponding responses key
    if (empty($aCsvHeader)) {
        $CSVImportResult['errors'][] = gT("File seems empty or has only one line");
        return $CSVImportResult;
    }
    // Assign fieldname with $aFileResponses[] key
    foreach ($aRealFieldNames as $sFieldName) {
        if (in_array($sFieldName, $aCsvHeader)) {
            // First pass : simple associated
            $aKeyForFieldNames[$sFieldName] = array_search($sFieldName, $aCsvHeader);
        } elseif (in_array($sFieldName, $aLemFieldNames)) {
            // Second pass : LEM associated
            $sLemFieldName = array_search($sFieldName, $aLemFieldNames);
            if (in_array($sLemFieldName, $aCsvHeader)) {
                $aKeyForFieldNames[$sFieldName] = array_search($sLemFieldName, $aCsvHeader);
            } elseif ($aOptions['bForceImport']) {
                // as fallback just map questions in order of apperance

                // find out where the answer data columns start in CSV
                if (!isset($csv_ans_start_index)) {
                    foreach ($aCsvHeader as $i => $name) {
                        if (preg_match('/^\d+X\d+X\d+/', (string) $name)) {
                            $csv_ans_start_index = $i;
                            break;
                        }
                    }
                }
                // find out where the answer data columns start in destination table
                if (!isset($table_ans_start_index)) {
                    foreach ($aRealFieldNames as $i => $name) {
                        if (preg_match('/^\d+X\d+X\d+/', (string) $name)) {
                            $table_ans_start_index = $i;
                            break;
                        }
                    }
                }

                // map answers in order
                if (isset($table_ans_start_index, $csv_ans_start_index)) {
                    $csv_index = (array_search($sFieldName, $aRealFieldNames) - $table_ans_start_index) + $csv_ans_start_index;
                    if ($csv_index < count($aCsvHeader)) {
                        $aKeyForFieldNames[$sFieldName] = $csv_index;
                    } else {
                        $force_import_failed = true;
                        break;
                    }
                }
            }
        }
    }
    // check if forced error failed
    if (isset($force_import_failed)) {
        $CSVImportResult['errors'][] = gT("Import failed: Forced import was requested but the input file doesn't contain enough columns to fill the survey.");
        return $CSVImportResult;
    }

    // make sure at least one answer was imported before commiting
    $isAnswerMapped = array_key_exists('id', $aKeyForFieldNames) ? (count($aKeyForFieldNames) > 1) : (count($aKeyForFieldNames) > 0);
    if (!$isAnswerMapped) {
        $CSVImportResult['errors'][] = gT("Import failed: No answers could be mapped.");
        return $CSVImportResult;
    }

    // Now it's time to import
    // Some var to return
    $iNbResponseLine = 0;
    $aResponsesInserted = array();
    $aResponsesUpdated = array();
    $aResponsesError = array();
    $aExistingsId = array();

    $iMaxId = 0; // If we set the id, keep the max
    // Some specific header (with options)
    $iIdKey = array_search('id', $aCsvHeader); // the id is always needed and used a lot
    if (is_int($iIdKey)) {
        unset($aKeyForFieldNames['id']);
        /* Unset it if option is ignore */
        if ($aOptions['sExistingId'] == 'ignore') {
            $iIdKey = false;
        }
    }
    $iSubmitdateKey = array_search('submitdate', $aCsvHeader); // submitdate can be forced to null
    if (is_int($iSubmitdateKey)) {
        unset($aKeyForFieldNames['submitdate']);
    }
    $iIdResponsesKey = (is_int($iIdKey)) ? $iIdKey : 0; // The key for responses id: id column or first column if not exist

    // Import each responses line here
    while ($aResponses = array_shift($aFileResponses)) {
        $iNbResponseLine++;
        $bExistingsId = false;
        if ($iIdKey !== false) {
            $oSurvey = SurveyDynamic::model($iSurveyId)->findByPk($aResponses[$iIdKey]);
            if ($oSurvey) {
                $bExistingsId = true;
                $aExistingsId[] = $aResponses[$iIdKey];
                // Do according to option
                switch ($aOptions['sExistingId']) {
                    case 'replace':
                        SurveyDynamic::model($iSurveyId)->deleteByPk($aResponses[$iIdKey]);
                        SurveyDynamic::sid($iSurveyId);
                        $oSurvey = new SurveyDynamic();
                        break;
                    case 'replaceanswers':
                        break;
                    case 'skip':
                        $oSurvey = false; // Remove existing survey : don't import again
                        break;
                    case 'renumber':
                    default: // Must not happen, keep it in case
                        SurveyDynamic::sid($iSurveyId);
                        $oSurvey = new SurveyDynamic();
                        break;
                }
            } else {
                SurveyDynamic::sid($iSurveyId);
                $oSurvey = new SurveyDynamic();
            }
        } else {
            SurveyDynamic::sid($iSurveyId);
            $oSurvey = new SurveyDynamic();
        }
        if ($oSurvey) {
            // First rule for id and submitdate
            if (is_int($iIdKey)) {
                // Rule for id: only if id exists in vvimport file
                if (!$bExistingsId) {
                    // If not exist : always import it
                    $oSurvey->id = $aResponses[$iIdKey];
                    $iMaxId = ($aResponses[$iIdKey] > $iMaxId) ? $aResponses[$iIdKey] : $iMaxId;
                } elseif ($aOptions['sExistingId'] == 'replace' || $aOptions['sExistingId'] == 'replaceanswers') {
                    // Set it depending with some options
                    $oSurvey->id = $aResponses[$iIdKey];
                }
            }
            if ($aOptions['bNotFinalized']) {
                $oSurvey->submitdate = new CDbExpression('NULL');
            } elseif (is_int($iSubmitdateKey)) {
                if ($aResponses[$iSubmitdateKey] == '{question_not_shown}' || trim($aResponses[$iSubmitdateKey] == '')) {
                    $oSurvey->submitdate = new CDbExpression('NULL');
                } else {
                    // Maybe control valid date : see http://php.net/manual/en/function.checkdate.php#78362 for example
                    $oSurvey->submitdate = $aResponses[$iSubmitdateKey];
                }
            }

            foreach ($aKeyForFieldNames as $sFieldName => $iFieldKey) {
                if ($aResponses[$iFieldKey] == '{question_not_shown}') {
                    $oSurvey->$sFieldName = new CDbExpression('NULL');
                } else {
                    $sResponse = str_replace(array("{quote}", "{tab}", "{cr}", "{newline}", "{lbrace}"), array("\"", "\t", "\r", "\n", "{"), (string) $aResponses[$iFieldKey]);
                    $oSurvey->$sFieldName = $sResponse;
                }
            }

            //Check if datestamp is set => throws no default error on importing
            if ($oSurvey->hasAttribute('datestamp') && !isset($oSurvey->datestamp)) {
                $oSurvey->datestamp = '1980-01-01 00:00:01';
            }
            //Check if startdate is set => throws no default error on importing
            if ($oSurvey->hasAttribute('startdate') && !isset($oSurvey->startdate)) {
                $oSurvey->startdate = '1980-01-01 00:00:01';
            }

            // We use transaction to prevent DB error
            $oTransaction = Yii::app()->db->beginTransaction();
            try {
                if (isset($oSurvey->id) && !is_null($oSurvey->id)) {
                    switchMSSQLIdentityInsert('survey_' . $iSurveyId, true);
                    $bSwitched = true;
                }
                if ($oSurvey->encryptSave()) {
                    $beforeDataEntryImport = new PluginEvent('beforeDataEntryImport');
                    $beforeDataEntryImport->set('iSurveyID', $iSurveyId);
                    $beforeDataEntryImport->set('oModel', $oSurvey);
                    App()->getPluginManager()->dispatchEvent($beforeDataEntryImport);

                    $oTransaction->commit();
                    if ($bExistingsId && $aOptions['sExistingId'] != 'renumber') {
                        $aResponsesUpdated[] = $aResponses[$iIdResponsesKey];
                    } else {
                        $aResponsesInserted[] = $aResponses[$iIdResponsesKey];
                    }
                } else {
                    // Actually can not be, leave it if we have a $oSurvey->validate() in future release
                    $oTransaction->rollBack();
                    $aResponsesError[] = $aResponses[$iIdResponsesKey];
                }
                if (isset($bSwitched) && $bSwitched == true) {
                    switchMSSQLIdentityInsert('survey_' . $iSurveyId, false);
                    $bSwitched = false;
                }
            } catch (Exception $oException) {
                $oTransaction->rollBack();
                $aResponsesError[] = $aResponses[$iIdResponsesKey];
                // Show some error to user ?
                $CSVImportResult['errors'][] = $oException->getMessage(); // Show it in view
                tracevar($oException->getMessage());// Show it in console (if debug is set)
            }
        }
    }
    // Fix max next id (for pgsql)
    // mysql dot need fix, but what for mssql ?
    // Do a model function for this can be a good idea (see activate_helper/activateSurvey)
    if (Yii::app()->db->driverName == 'pgsql') {
        $sSequenceName = Yii::app()->db->getSchema()->getTable("{{survey_{$iSurveyId}}}")->sequenceName;
        $iActualSerial = Yii::app()->db->createCommand("SELECT last_value FROM  {$sSequenceName}")->queryScalar();
        if ($iActualSerial < $iMaxId) {
            $sQuery = "SELECT setval(pg_get_serial_sequence('{{survey_{$iSurveyId}}}', 'id'),{$iMaxId},false);";
            try {
                Yii::app()->db->createCommand($sQuery)->execute();
            } catch (Exception $oException) {
            };
        }
    }

    // End of import
    // Construction of returned information
    if ($iNbResponseLine) {
        $CSVImportResult['success'][] = sprintf(gT("%s response lines in your file."), $iNbResponseLine);
    } else {
        $CSVImportResult['errors'][] = gT("No response lines in your file.");
    }
    if (count($aResponsesInserted)) {
        $CSVImportResult['success'][] = sprintf(gT("%s responses were inserted."), count($aResponsesInserted));
        // Maybe add implode aResponsesInserted array
    }
    if (count($aResponsesUpdated)) {
        $CSVImportResult['success'][] = sprintf(gT("%s responses were updated."), count($aResponsesUpdated));
    }
    if (count($aResponsesError)) {
        $CSVImportResult['errors'][] = sprintf(gT("%s responses cannot be inserted or updated."), count($aResponsesError));
    }
    if (count($aExistingsId) && ($aOptions['sExistingId'] == 'skip' || $aOptions['sExistingId'] == 'ignore')) {
        $CSVImportResult['warnings'][] = sprintf(gT("%s responses already exist."), count($aExistingsId));
    }
    return $CSVImportResult;
}


/**
 * @param string $sFullFilePath
 */
function XMLImportTimings($sFullFilePath, $iSurveyID, $aFieldReMap = array())
{
    Yii::app()->loadHelper('database');

    $sXMLdata = (string) file_get_contents($sFullFilePath);
    $xml = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
    $results = [];
    if ($xml->LimeSurveyDocType != 'Timings') {
        $results['error'] = gT("This is not a valid timings data XML file.");
        return $results;
    }

    $results['responses'] = 0;

    $aLanguagesSupported = array();

    foreach ($xml->languages->language as $language) {
        $aLanguagesSupported[] = (string) $language;
    }

    $results['languages'] = count($aLanguagesSupported);
    // Return if there are no timing records to import
    if (!isset($xml->timings->rows)) {
        return $results;
    }
    switchMSSQLIdentityInsert('survey_' . $iSurveyID . '_timings', true);
    foreach ($xml->timings->rows->row as $row) {
        $insertdata = array();

        foreach ($row as $key => $value) {
            if ($key[0] == '_') {
                $key = substr($key, 1);
            }
            if (isset($aFieldReMap[substr($key, 0, -4)])) {
                $key = $aFieldReMap[substr($key, 0, -4)] . 'time';
            }
            $insertdata[$key] = (string) $value;
        }

        if (!SurveyTimingDynamic::model($iSurveyID)->insertRecords($insertdata)) {
            throw new Exception(gT("Error") . ": Failed to insert timings data");
        }

        $results['responses']++;
    }
    switchMSSQLIdentityInsert('survey_' . $iSurveyID . '_timings', false);
    return $results;
}

/**
* Import survey from an TSV file template that does not require assigning of GID or QID values.
* If ID's are presented, they would be respected and used
* Multilanguage imports are supported
* Original function is changed to allow generating of XML instead of creating database objects directly
* Generated XML code is send to existing lss import function
* @param string $sFullFilePath
* @return string XML data
*
* @author TMSWhite
*/
function TSVImportSurvey($sFullFilePath)
{
    $baselang = 'en'; // TODO set proper default

    $aAttributeList = array(); //QuestionAttribute::getQuestionAttributesSettings();
    $tmp = fileCsvToUtf8($sFullFilePath);

    $rowheaders = fgetcsv($tmp, 0, "\t", '"');
    $rowheaders = array_map('trim', $rowheaders);
    // remove BOM from the first header cell, if needed
    $rowheaders[0] = preg_replace("/^\W+/", "", $rowheaders[0]);
    if (preg_match('/class$/', $rowheaders[0])) {
        $rowheaders[0] = 'class'; // second attempt to remove BOM
    }

    $adata = array();
    $iHeaderCount = count($rowheaders);
    while (($row = fgetcsv($tmp, 0, "\t", '"')) !== false) {
        $rowarray = array();
        for ($i = 0; $i < $iHeaderCount; ++$i) {
            $val = ($row[$i] ?? '');
            // if Excel was used, it surrounds strings with quotes and doubles internal double quotes.  Fix that.
            if (preg_match('/^".*"$/', (string) $val)) {
                $val = str_replace('""', '"', substr((string) $val, 1, -1));
            }
            if (mb_strlen((string) $val) > 0) {
                $rowarray[$rowheaders[$i]] = $val;
            }
        }
        $adata[] = $rowarray;
    }
    fclose($tmp);
    unset($rowheaders);
    unset($rowarray) ;

    // collect information about survey and its language settings
    $surveyinfo = array();
    $surveyls = array();
    $groups = array();
    $questions = array();
    $attributes = array();
    $subquestions = array();
    $defaultvalues = array();
    $answers = array();
    $assessments = array();
    $quotas = array();
    $quota_members = array();
    $quota_languagesettings = array();
    $output = array();
    foreach ($adata as $row) {
        switch ($row['class']) {
            case 'S':
                if (isset($row['text']) && $row['name'] != 'datecreated') {
                    $surveyinfo[$row['name']] = $row['text'];
                }
                break;
            case 'SL':
                /*if (!isset($surveyls[$row['language']])) {
                    $surveyls[$row['language']] = array($baselang);
                }*/
                if (isset($row['text'])) {
                    $surveyls[$row['language']][$row['name']] = $row['text'];
                }
                break;
        }
    }


    // Create the survey entry
    $surveyinfo['startdate'] = null;
    $surveyinfo['active'] = 'N';
    // unset($surveyinfo['datecreated']);

    // Set survey group id to 1. Makes no sense to import it without the actual survey group.
    $surveyinfo['gsid'] = 1;

    if (array_key_exists('sid', $surveyinfo)) {
        $iNewSID = $surveyinfo['sid'];
    } else {
        $iNewSID = randomChars(6, '123456789');
    }


    $gidNew = 0;
    $gid = 0;
    $gseq = 1; // group_order
    $qid = 1;
    $qidNew = 0;
    $qseq = 0; // question_order
    $qtype = 'T';
    $aseq = 0; // answer sortorder

    $ginfo = array();
    $qinfo = array();
    $sqinfo = array();
    $asinfo = array();

    if (isset($surveyinfo['language'])) {
        $baselang = $surveyinfo['language']; // the base language
    }
    /* Keep track of id for group */
    $groupIds = [];
    /* Keep track of id for question (can come from tsv and can be broken : issue #17980 */
    $questionsIds = [];
    $rownumber = 1;
    $lastglang = '';
    $lastother = 'N';
    $qseq = 1;
    $iGroupcounter = 1;
    foreach ($adata as $row) {
        $rownumber += 1;
        switch ($row['class']) {
            case 'G':
                // insert group
                $group = array();
                $group['sid'] = $iNewSID;
                $gname = ((!empty($row['name']) ? $row['name'] : 'G' . $gseq));
                $glang = (!empty($row['language']) ? $row['language'] : $baselang);
                // when a multi-lang tsv-file without information on the group id/number (old style) is imported,
                // we make up this information by giving a number 0..[numberofgroups-1] per language.
                // the number and order of groups per language should be the same, so we can also import these files
                if ($lastglang != $glang) {
                    //reset counter on language change
                    $iGroupcounter = 0;
                }
                $lastglang = $glang;
                //use group id/number from file. if missing, use an increasing number (s.a.)
                $sGroupseq = (!empty($row['type/scale']) ? $row['type/scale'] : 'G' . $iGroupcounter++);
                $group['group_name'] = $gname;
                $group['grelevance'] = ($row['relevance'] ?? '');
                $group['description'] = ($row['text'] ?? '');
                $group['language'] = $glang;
                $group['randomization_group'] = ($row['random_group'] ?? '');

                // For multi language survey: same gid/sort order across all languages
                if (isset($ginfo[$sGroupseq])) {
                    $gid = $ginfo[$sGroupseq]['gid'];
                    $group['gid'] = $gid;
                    $group['group_order'] = $ginfo[$sGroupseq]['group_order'];
                } else {
                    /* Get the new gid from file if it's number and not already set*/
                    if (!empty($row['id']) && ctype_digit((string) $row['id']) && !in_array($row['id'], $groupIds)) {
                        $gid = $row['id'];
                    } else {
                        $gidNew += 1;
                        $gid = $gidNew;
                    }
                    $group['gid'] = $gid;
                    $groupIds[] = $gid;
                    $group['group_order'] = $gseq;
                }

                if (!isset($ginfo[$sGroupseq])) {
                    $ginfo[$sGroupseq]['gid'] = $gid;
                    $ginfo[$sGroupseq]['group_order'] = $gseq++;
                }
                $qseq = 0; // reset the question_order

                $groups[] = $group;

                break;

            case 'Q':
                $question = array();
                $question['sid'] = $iNewSID;
                $qtype = ($row['type/scale'] ?? 'T');
                $qname = ($row['name'] ?? 'Q' . $qseq);
                $question['gid'] = $gid;
                $question['type'] = $qtype;
                $question['title'] = $qname;
                $question['question'] = ($row['text'] ?? '');
                $question['relevance'] = ($row['relevance'] ?? '');
                $question['preg'] = ($row['validation'] ?? '');
                $question['help'] = ($row['help'] ?? '');
                $question['language'] = ($row['language'] ?? $baselang);
                $question['mandatory'] = ($row['mandatory'] ?? '');
                $question['encrypted'] = ($row['encrypted'] ?? 'N');
                $lastother = $question['other'] = ($row['other'] ?? 'N'); // Keep trace of other settings for sub question
                $question['same_default'] = ($row['same_default'] ?? 0);
                $question['question_theme_name'] = ($row['question_theme_name'] ?? '');
                $question['same_script'] = ($row['same_script'] ?? 0);
                $question['parent_qid'] = 0;

                // For multi language survey : same name, add the gid to have same name on different gid. Bad for EM.
                $fullqname = 'G' . $gid . '_' . $qname;
                if (isset($qinfo[$fullqname])) {
                    $qseq = $qinfo[$fullqname]['question_order'];
                    $qid = $qinfo[$fullqname]['qid'];
                    $question['qid'] = $qid;
                    $question['question_order'] = $qseq;
                } else {
                    /* Get the new qid from file if it's number and not already set*/
                    if (!empty($row['id']) && ctype_digit((string) $row['id']) && !in_array($row['id'], $questionsIds)) {
                        $qid = $row['id'];
                    } else {
                        $qidNew += 1;
                        $qid = $qidNew;
                    }
                    $question['question_order'] = $qseq;
                    $question['qid'] = $qid;
                    $questionsIds[] = $qid;
                }

                $questions[] = $question;

                if (!isset($qinfo[$fullqname])) {
                    $qinfo[$fullqname]['qid'] = $qid;
                    $qinfo[$fullqname]['question_order'] = $qseq++;
                }
                $aseq = 0; //reset the answer sortorder
                $sqseq = 0; //reset the sub question sortorder
                // insert question attributes
                foreach ($row as $key => $val) {
                    switch ($key) {
                        case 'class':
                        case 'type/scale':
                        case 'name':
                        case 'text':
                        case 'validation':
                        case 'relevance':
                        case 'help':
                        case 'language':
                        case 'mandatory':
                        case 'other':
                        case 'same_default':
                        case 'question_theme_name':
                        case 'same_script':
                        case 'default':
                            break;
                        default:
                            if ($key != '' && $val != '') {
                                $attribute = array();
                                $attribute['qid'] = $qid;
                                // check if attribute is a i18n attribute. If yes, set language, else set language to null in attribute table
                                $aAttributeList[$qtype] = QuestionAttribute::getQuestionAttributesSettings($qtype);
                                if (!empty($aAttributeList[$qtype][$key]['i18n'])) {
                                    $attribute['language'] = ($row['language'] ?? $baselang);
                                } else {
                                    $attribute['language'] = null;
                                }
                                $attribute['attribute'] = $key;
                                $attribute['value'] = $val;

                                $attributes[] = $attribute;
                            }
                            break;
                    }
                }

                // insert default value
                if (isset($row['default']) && $row['default'] !== "") {
                    $defaultvalue = array();
                    $defaultvalue['qid'] = $qid;
                    $defaultvalue['sqid'] = '';
                    $defaultvalue['language'] = ($row['language'] ?? $baselang);
                    $defaultvalue['defaultvalue'] = $row['default'];
                    $defaultvalues[] = $defaultvalue;
                }
                break;

            case 'SQ':
                $sqname = ($row['name'] ?? 'SQ' . $sqseq);
                $sqid = '';
                if ($qtype == Question::QT_O_LIST_WITH_COMMENT || $qtype == Question::QT_VERTICAL_FILE_UPLOAD) {
                    ;   // these are fake rows to show naming of comment and filecount fields
                } elseif ($sqname == 'other' && $lastother == "Y") {
                    // If last question have other to Y : it's not a real SQ row
                    if ($qtype == Question::QT_EXCLAMATION_LIST_DROPDOWN || $qtype == Question::QT_L_LIST) {
                        // only used to set default value for 'other' in these cases
                        if (isset($row['default']) && $row['default'] != "") {
                            $defaultvalue = array();
                            $defaultvalue['qid'] = $qid;
                            $defaultvalue['sqid'] = $sqid;
                            $defaultvalue['specialtype'] = 'other';
                            $defaultvalue['language'] = ($row['language'] ?? $baselang);
                            $defaultvalue['defaultvalue'] = $row['default'];
                            $defaultvalues[] = $defaultvalue;
                        }
                    }
                } else {
                    $scale_id = ($row['type/scale'] ?? 0);
                    $subquestion = array();
                    $subquestion['sid'] = $iNewSID;
                    $subquestion['gid'] = $gid;
                    $subquestion['parent_qid'] = $qid;
                    $subquestion['type'] = $qtype;
                    $subquestion['title'] = $sqname;
                    $subquestion['question'] = ($row['text'] ?? '');
                    $subquestion['relevance'] = ($row['relevance'] ?? '');
                    $subquestion['preg'] = ($row['validation'] ?? '');
                    $subquestion['help'] = ($row['help'] ?? '');
                    $subquestion['language'] = ($row['language'] ?? $baselang);
                    $subquestion['mandatory'] = ($row['mandatory'] ?? '');
                    $subquestion['scale_id'] = $scale_id;
                    // For multi language, qid is needed, why not gid. name is not unique.
                    $fullsqname = 'G' . $gid . 'Q' . $qid . '_' . $scale_id . '_' . $sqname;
                    if (isset($sqinfo[$fullsqname])) {
                        $qseq = $sqinfo[$fullsqname]['question_order'];
                        $sqid = $sqinfo[$fullsqname]['sqid'];
                        $subquestion['question_order'] = $qseq;
                        $subquestion['qid'] = $sqid;
                    } else {
                        $subquestion['question_order'] = $qseq;
                        /* Get the new qid from file if it's number and not already set : subquestion are question*/
                        if (!empty($row['id']) && ctype_digit((string) $row['id']) && !in_array($row['id'], $questionsIds)) {
                            $sqid = $row['id'];
                        } else {
                            $qidNew += 1;
                            $sqid = $qidNew;
                        }
                        $subquestion['qid'] = $sqid;
                        $questionsIds[] = $sqid;
                    }
                    $subquestions[] = $subquestion;

                    if (!isset($sqinfo[$fullsqname])) {
                        $sqinfo[$fullsqname]['question_order'] = $qseq++;
                        $sqinfo[$fullsqname]['sqid'] = $sqid;
                    }

                    // insert default value
                    if (isset($row['default']) && $row['default'] != "") {
                        $defaultvalue = array();
                        $defaultvalue['qid'] = $qid;
                        $defaultvalue['sqid'] = $sqid;
                        $defaultvalue['scale_id'] = $scale_id;
                        $defaultvalue['language'] = ($row['language'] ?? $baselang);
                        $defaultvalue['defaultvalue'] = $row['default'];
                        $defaultvalues[] = $defaultvalue;
                    }
                }
                break;
            case 'A':
                $answer = array();
                $answer['qid'] = $qid;
                $answer['code'] = ($row['name'] ?? 'A' . $aseq);
                $answer['answer'] = ($row['text'] ?? '');
                $answer['scale_id'] = ($row['type/scale'] ?? 0);
                $answer['language'] = ($row['language'] ?? $baselang);
                $answer['assessment_value'] = (int) ($row['assessment_value'] ?? '');
                $answer['sortorder'] = ++$aseq;
                $answers[] = $answer;
                break;
            case 'AS':
                $assessment = array();
                $assessment['sid'] = $iNewSID;
                $assessment['scope'] = $row['type/scale'] ?? '';
                $assessment['gid'] = $gid;
                $assessment['name'] = $row['name'] ?? '';
                $assessment['minimum'] = $row['min_num_value'] ?? '';
                $assessment['maximum'] = $row['max_num_value'] ?? '';
                $assessment['message'] = $row['text'] ?? '';
                $assessment['language'] = $row['language'] ?? '';
                $assessment['id'] = $row['id'] ?? '';
                $assessments[] = $assessment;
                break;
            case 'QTA':
                $quota = array();
                $quota['id'] = $row['id'] ?? '';
                $quota['sid'] = $iNewSID;
                $quota['name'] = $row['name'] ?? '';
                $quota['qlimit'] = $row['mandatory'] ?? '';
                $quota['action'] = $row['other'] ?? '';
                $quota['active'] = $row['default'] ?? '';
                $quota['autoload_url'] = $row['same_default'] ?? '';
                $quotas[] = $quota;
                break;
            case 'QTAM':
                $quota_member = array();
                $quota_member['quota_id'] = $row['related_id'] ?? '';
                $quota_member['sid'] = $iNewSID;
                $quota_member['qid'] = $qid;
                $quota_member['code'] = $row['name'] ?? '';
                $quota_members[] = $quota_member;
                break;
            case 'QTALS':
                $quota_languagesetting = array();
                $quota_languagesetting['quotals_quota_id'] = $row['related_id'] ?? '';
                $quota_languagesetting['quotals_language'] = $row['language'] ?? '';
                //$quota_languagesetting['quotals_name'] = isset($row['name'])?$row['name']:'';
                $quota_languagesetting['quotals_message'] = $row['relevance'] ?? '';
                $quota_languagesetting['quotals_url'] = $row['text'] ?? '';
                $quota_languagesetting['quotals_urldescrip'] = $row['help'] ?? '';
                $quota_languagesettings[] = $quota_languagesetting;
                break;
            case 'C':
                $condition = array();
                $condition['qid'] = $qid;
                $condition['scenario'] = $row['type/scale'];
                $condition['cqid'] = $row['related_id'] ?? '';
                $condition['cfieldname'] = $row['name'];
                $condition['method'] = $row['relevance'];
                $condition['value'] = $row['text'] ?? '';
                $conditions[] = $condition;
                break;
        }
    }
    unset($adata);
    // combine all xml data into $output variable
    if (!empty($surveyinfo)) {
        $output['surveys']['fields']['fieldname'] = array_keys($surveyinfo);
        $output['surveys']['rows']['row'] = $surveyinfo;
    }

    if (!empty($surveyls)) {
        $output['surveys_languagesettings']['fields']['fieldname'] = array_keys($surveyls[$baselang]);
        $output['surveys_languagesettings']['rows']['row'] = $surveyls;
    }

    if (!empty($groups)) {
        $output['groups']['fields']['fieldname'] = array_keys($groups[0]);
        $output['groups']['rows']['row'] = $groups;
    }

    if (!empty($questions)) {
        $output['questions']['fields']['fieldname'] = array_keys($questions[0]);
        $output['questions']['rows']['row'] = $questions;
    }

    if (!empty($attributes)) {
        $output['question_attributes']['fields']['fieldname'] = array_keys($attributes[0]);
        $output['question_attributes']['rows']['row'] = $attributes;
    }

    if (!empty($defaultvalues)) {
        $output['defaultvalues']['fields']['fieldname'] = array_keys($defaultvalues[0]);
        $output['defaultvalues']['rows']['row'] = $defaultvalues;
    }

    if (!empty($subquestions)) {
        $output['subquestions']['fields']['fieldname'] = array_keys($subquestions[0]);
        $output['subquestions']['rows']['row'] = $subquestions;
    }

    if (!empty($answers)) {
        $output['answers']['fields']['fieldname'] = array_keys($answers[0]);
        $output['answers']['rows']['row'] = $answers;
    }

    if (!empty($assessments)) {
        $output['assessments']['fields']['fieldname'] = array_keys($assessments[0]);
        $output['assessments']['rows']['row'] = $assessments;
    }


    if (!empty($quotas)) {
        $output['quota']['fields']['fieldname'] = array_keys($quotas[0]);
        $output['quota']['rows']['row'] = $quotas;
    }

    if (!empty($quota_members)) {
        $output['quota_members']['fields']['fieldname'] = array_keys($quota_members[0]);
        $output['quota_members']['rows']['row'] = $quota_members;
    }

    if (!empty($quota_languagesettings)) {
        $output['quota_languagesettings']['fields']['fieldname'] = array_keys($quota_languagesettings[0]);
        $output['quota_languagesettings']['rows']['row'] = $quota_languagesettings;
    }

    if (!empty($conditions)) {
        $output['conditions']['fields']['fieldname'] = array_keys($conditions[0]);
        $output['conditions']['rows']['row'] = $conditions;
    }

    // generate xml document
    $xml = createXMLfromData($output);
    // send xml document into XMLImportSurvey function and display results
    return XMLImportSurvey('null', $xml);
}

function createXMLfromData($aData = array())
{
    // get survey languages
    $surveylanguage = array_key_exists('language', $aData['surveys']['rows']['row']) ? (array)$aData['surveys']['rows']['row']['language'] : array('en');
    $surveyAdditionalLanguages = array_key_exists('additional_languages', $aData['surveys']['rows']['row']) && !empty($aData['surveys']['rows']['row']['additional_languages']) ? explode(' ', (string) $aData['surveys']['rows']['row']['additional_languages']) : array();
    if (count($surveyAdditionalLanguages) == 0) {
        $surveylanguages = $surveylanguage;
    } else {
        $surveylanguages = array_merge($surveylanguage, $surveyAdditionalLanguages);
    }
    $i = 0;
    if (array_key_exists('surveys_languagesettings', $aData)) {
        foreach ($aData['surveys_languagesettings']['rows']['row'] as $language => $value) {
            if (!array_key_exists('surveyls_title', $value)) {
                $aData['surveys_languagesettings']['rows']['row'][$language]['surveyls_title'] = 'Missing Title';
            }
            if (!array_key_exists('surveyls_language', $value)) {
                $aData['surveys_languagesettings']['rows']['row'][$language]['surveyls_language'] = $language;
            }
            $i += 1;
        }
    }
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);

    //header
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType', 'Survey');
    $xml->writeElement('DBVersion', App()->getConfig("DBVersion"));

    $xml->startElement('languages');
    foreach ($surveylanguages as $surveylanguage) {
        $xml->writeElement('language', $surveylanguage);
    }
    $xml->endElement();

    $index3 = 0;
    foreach ($aData as $key1 => $value1) {
        $xml->startElement($key1);
        foreach ($value1 as $key2 => $value2) {
            $xml->startElement($key2);
            foreach ($value2 as $key3 => $value3) {
                $index3 = 0;
                if (is_array($value3)) {
                    foreach ($value3 as $key4 => $value4) {
                        if (is_array($value4)) {
                            //$xml->startElement('row');
                            $xml->startElement($key3);
                            foreach ($value4 as $key5 => $value5) {
                                if (!is_array($value5)) {
                                    $xml->startElement($key5);
                                    $xml->writeCdata($value5);
                                    $xml->endElement();
                                }
                            }
                            $xml->endElement();
                        } else {
                            if (is_integer($key4)) {
                                $xml->writeElement($key3, $value4);
                            } else {
                                if ($key3 == 'row') {
                                    if ($index3 === 0) {
                                        $xml->startElement($key3);
                                    }
                                    $xml->startElement($key4);
                                    $xml->writeCdata($value4);
                                    $xml->endElement();
                                    $index3 += 1;
                                    if ($index3 === count($value3)) {
                                        $xml->endElement();
                                    }
                                } else {
                                    $xml->writeElement($key3, $key4);
                                }
                            }
                        }
                    }
                } else {
                }
            }
            $xml->endElement();
        }
        $xml->endElement();
    }

    $xml->endElement();
    $xml->endDocument();
    return $xml->outputMemory(true);
}

/**
 * Import default values inside $xml, record process in $results
 * Also imports defaultvalue_l10ns.
 *
 * @param SimpleXMLElement $xml
 * @param array $aLanguagesSupported
 * @param array &$results
 * @return void
 */
function importDefaultValues(SimpleXMLElement $xml, $aLanguagesSupported, $aQIDReplacements, array &$results)
{
    // Default value id replacements
    $aDvidReplacements = [];

    if (isset($xml->defaultvalues)) {
        $results['defaultvalues'] = 0;
        $aInsertData = array();
        foreach ($xml->defaultvalues->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (isset($xml->defaultvalue_l10ns->rows->row) && !empty($insertdata['dvid'])) {
                $iDvidOld = $insertdata['dvid'];
                unset($insertdata['dvid']);
            }
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                continue;
            }

            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the qid
            if (isset($aQIDReplacements[(int) $insertdata['sqid']])) {
                // remap the subquestion id
                $insertdata['sqid'] = $aQIDReplacements[(int) $insertdata['sqid']];
            }

            if (!isset($xml->defaultvalue_l10ns->rows->row)) {
                if (!in_array($insertdata['language'], $aLanguagesSupported)) {
                    continue;
                }

                $aInsertData[$insertdata['qid']][$insertdata['scale_id']][$insertdata['sqid']][$insertdata['specialtype']][$insertdata['language']] = [$insertdata['defaultvalue']];
            } else {
                $defaultValue = new DefaultValue();
                $defaultValue->setAttributes($insertdata, false);
                if ($defaultValue->save()) {
                    if ($iDvidOld > 0) {
                        $aDvidReplacements[$iDvidOld] = $defaultValue->dvid;
                    }
                } else {
                    throw new Exception(gT("Error") . ": Failed to insert data[9]<br />");
                }
                $results['defaultvalues']++;
            }
        }

        // insert default values from LS v3 which doesn't have defaultvalue_l10ns
        if (!empty($aInsertData)) {
            foreach ($aInsertData as $qid => $aQid) {
                foreach ($aQid as $scaleId => $aScaleId) {
                    foreach ($aScaleId as $sqid => $aSqid) {
                        foreach ($aSqid as $specialtype => $aSpecialtype) {
                            $oDefaultValue = new DefaultValue();
                            $oDefaultValue->setAttributes(array('qid' => $qid, 'scale_id' => $scaleId, 'sqid' => $sqid, 'specialtype' => $specialtype), false);
                            if ($oDefaultValue->save()) {
                                $results['defaultvalues']++;
                                foreach ($aSpecialtype as $language => $defaultvalue) {
                                    $oDefaultValueL10n = new DefaultValueL10n();
                                    $oDefaultValueL10n->dvid = $oDefaultValue->dvid;
                                    $oDefaultValueL10n->language = $language;
                                    $oDefaultValueL10n->defaultvalue = $defaultvalue[0];
                                    $oDefaultValueL10n->save();
                                    unset($oDefaultValueL10n);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Import defaultvalue_l10ns ------------------------------------------------------
    if (isset($xml->defaultvalue_l10ns)) {
        foreach ($xml->defaultvalue_l10ns->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key => $value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['dvid'] = $aDvidReplacements[$insertdata['dvid']];
            unset($insertdata['id']);

            $oDefaultValueL10n = new DefaultValueL10n();
            $oDefaultValueL10n->setAttributes($insertdata, false);
            if (!$oDefaultValueL10n->save()) {
                throw new Exception(gT("Error") . ": Failed to insert data[19]<br />");
            }
        }
    }
}

/**
 * Read a csv file and return a tmp resources to same file in utf8
 * CSV file is deleted during process
 *
 * @param string $fullfilepath
 * @param string $encoding from
 * @return resource
 */
function fileCsvToUtf8($fullfilepath, $encoding = 'auto')
{
    $handle = fopen($fullfilepath, 'r');
    if ($handle === false) {
        throw new Exception("Can't open file");
    }
    $aEncodings = aEncodingsArray();
    if (!array_key_exists($encoding, $aEncodings)) {
        $encoding = 'auto';
    }
    if ($encoding == 'auto') {
        $bom = fread($handle, 2);
        rewind($handle);
        // Excel tends to save CSV as UTF-16, which PHP does not properly detect
        if ($bom === chr(0xff) . chr(0xfe) || $bom === chr(0xfe) . chr(0xff)) {
            // UTF16 Byte Order Mark present
            $encoding = 'UTF-16';
        } else {
            $file_sample = (string) fread($handle, 10000) . 'e'; //read first 1000 bytes
            // + e is a workaround for mb_string bug
            rewind($handle);
            $encoding = mb_detect_encoding(
                $file_sample,
                'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP'
            );
        }
        if ($encoding === false) {
            $encoding = 'utf8';
        }
    }
    if ($encoding != 'utf8' && $encoding != 'UTF-8') {
        stream_filter_append($handle, 'convert.iconv.' . $encoding . '/UTF-8');
    }

    $file = stream_get_contents($handle);
    fclose($handle);
    // fix Excel non-breaking space
    $file = str_replace("0xC20xA0", ' ', $file);
    // Replace all different newlines styles with \n
    $file = preg_replace('~\R~u', "\n", $file);
    $tmp = fopen('php://temp', 'r+');
    fwrite($tmp, $file);
    // Release the file, otherwise it will stay in memory
    unset($file);
    // Delete not needed file
    unlink($fullfilepath);
    /* Return the tempory ressource */
    rewind($tmp);
    return $tmp;
}
