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

use \LimeSurvey\Helpers\questionHelper;

/**
* This function imports a LimeSurvey .lsg question group XML file
*
* @param string $sFullFilePath  The full filepath of the uploaded file
* @param integer $iNewSID The new survey id - the group will always be added after the last group in the survey
*/
function XMLImportGroup($sFullFilePath, $iNewSID, $bConvertInvalidQuestionCodes)
{
    $sBaseLanguage         = Survey::model()->findByPk($iNewSID)->language;
    $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection

    $sXMLdata              = file_get_contents($sFullFilePath);
    $xml                   = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);


    if ($xml === false || $xml->LimeSurveyDocType != 'Group') {
        safeDie('This is not a valid LimeSurvey group structure XML file.');
    }

    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements = array();
    $results['defaultvalues'] = 0;
    $results['answers'] = 0;
    $results['question_attributes'] = 0;
    $results['subquestions'] = 0;
    $results['conditions'] = 0;
    $results['groups'] = 0;

    $importlanguages = array();
    foreach ($xml->languages->language as $language) {
        $importlanguages[] = (string) $language;
    }

    if (!in_array($sBaseLanguage, $importlanguages)) {
        $results['fatalerror'] = gT("The languages of the imported group file must at least include the base language of this survey.");
        return $results;
    }
    // First get an overview of fieldnames - it's not useful for the moment but might be with newer versions
    /*
    $fieldnames=array();
    foreach ($xml->questions->fields->fieldname as $fieldname ){
    $fieldnames[]=(string)$fieldname;
    };*/


    // Import group table ===================================================================================
    $iGroupOrder = Yii::app()->db->createCommand()->select('MAX(group_order)')->from('{{groups}}')->where('sid=:sid', array(':sid'=>$iNewSID))->queryScalar();
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
                safeDie(gT("Error").": Failed to insert data [3]<br />");
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
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


    $results['questions'] = 0;
    if (isset($xml->questions)) {
        foreach ($xml->questions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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

            if ($insertdata) {
                XSSFilterArray($insertdata);
            }
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
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'q'.$sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                $index = 0;
                $rand = mt_rand(0, 1024);
                while (!$oQuestion->validate(array('title'))) {
                    $sNewTitle = 'r'.$rand.'q'.$index;
                    $index++;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;
                    if ($attempts > 10) {
                        safeDie(gT("Error").": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    safeDie(gT("Error while saving: ").print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                $results['questions']++;
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
            foreach ($row as $key=>$value) {
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
                switchMSSQLIdentityInsert('questions', false);
                // Try to fix question title for valid question code enforcement
                if (!$oQuestion->validate(array('title'))) {
                    $sOldTitle = $oQuestion->title;
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'sq'.$sNewTitle;
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

                    $sNewTitle = 'r'.$rand.'sq'.$index;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;

                    if ($attempts > 10) {
                        safeDie(gT("Error").": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    safeDie(gT("Error while saving: ").print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                ;
                $results['questions']++;
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
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

            foreach ($row as $key=>$value) {
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
                $oAnswer = Answer::model()->findByAttributes(['qid'=>$insertdata['qid'], 'code'=>$insertdata['code'], 'scale_id'=>$insertdata['scale_id']]);
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
            foreach ($row as $key=>$value) {
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

        foreach ($xml->question_attributes->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['qaid']);
            if (!isset($aQIDReplacements[(int) $insertdata['qid']])) {
                // Skip questions with invalid group id
                continue;
            }
            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the parent_qid


            if ($iDBVersion < 156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n']) {
                foreach ($importlanguages as $sLanguage) {
                    $insertdata['language'] = $sLanguage;
                    Yii::app()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                }
            } else {
                Yii::app()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
            }
            $results['question_attributes']++;
        }
    }


    // Import defaultvalues ------------------------------------------------------
    if (isset($xml->defaultvalues)) {
        $results['defaultvalues'] = 0;
        $aInsertData = array();
        foreach ($xml->defaultvalues->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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
            if ($insertdata) {
                XSSFilterArray($insertdata);
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
                    if ($iDvidOld > 0){
                        $aDvidReplacements[$iDvidOld] = $defaultValue->dvid;
                    }
                } else {
                    safeDie(gT("Error").": Failed to insert data[9]<br />");
                }
                $results['defaultvalues']++;
            }
            
        }

        // insert default values from LS v3 which doesn't have defaultvalue_l10ns
        if (!empty($aInsertData)){
            foreach($aInsertData as $qid => $aQid){
                foreach($aQid as $scaleId => $aScaleId){
                    foreach($aScaleId as $sqid => $aSqid){
                        foreach($aSqid as $specialtype => $aSpecialtype){
                            $oDefaultValue = new DefaultValue();
                            $oDefaultValue->setAttributes(array('qid' => $qid, 'scale_id' => $scaleId, 'sqid' => $sqid, 'specialtype' => $specialtype), false);
                            if ($oDefaultValue->save()){
                                $results['defaultvalues']++;
                                foreach($aSpecialtype as $language => $defaultvalue){
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['dvid'] = $aDvidReplacements[$insertdata['dvid']];
            unset($insertdata['id']);

            $oDefaultValueL10n = new DefaultValueL10n();
            $oDefaultValueL10n->setAttributes($insertdata, false);
            if (!$oDefaultValueL10n->save()) {
                safeDie(gT("Error").": Failed to insert data[19]<br />");
            }
        }
    }

    // Import conditions --------------------------------------------------------------
    if (isset($xml->conditions)) {
        foreach ($xml->conditions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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

            list($oldcsid, $oldcgid, $oldqidanscode) = explode("X", $insertdata["cfieldname"], 3);

            if ($oldcgid != $oldgid) {
                // this means that the condition is in another group (so it should not have to be been exported -> skip it
                continue;
            }

            unset($insertdata["cid"]);

            // recreate the cfieldname with the new IDs
            if (preg_match("/^\+/", $oldcsid)) {
                $newcfieldname = '+'.$iNewSID."X".$newgid."X".$insertdata["cqid"].substr($oldqidanscode, strlen($iOldQID));
            } else {
                $newcfieldname = $iNewSID."X".$newgid."X".$insertdata["cqid"].substr($oldqidanscode, strlen($iOldQID));
            }

            $insertdata["cfieldname"] = $newcfieldname;
            if (trim($insertdata["method"]) == '') {
                $insertdata["method"] = '==';
            }

            // now translate any links
            Yii::app()->db->createCommand()->insert('{{conditions}}', $insertdata);
            $results['conditions']++;
        }
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);

    $results['newgid'] = $newgid;
    $results['labelsets'] = 0;
    $results['labels'] = 0;

    libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
    return $results;
}

/**
* This function imports a LimeSurvey .lsq question XML file
*
* @param string $sFullFilePath  The full filepath of the uploaded file
* @param integer $iNewSID The new survey id
* @param mixed $newgid The new question group id -the question will always be added after the last question in the group
*/
function XMLImportQuestion($sFullFilePath, $iNewSID, $newgid, $options = array('autorename'=>false))
{
    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $sXMLdata = file_get_contents($sFullFilePath);
    $xml = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
    if ($xml->LimeSurveyDocType != 'Question') {
        safeDie('This is not a valid LimeSurvey question structure XML file.');
    }
    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements = array();
    $aSQIDReplacements = array(0=>0);

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
    // First get an overview of fieldnames - it's not useful for the moment but might be with newer versions
    /*
    $fieldnames=array();
    foreach ($xml->questions->fields->fieldname as $fieldname ){
    $fieldnames[]=(string)$fieldname;
    };*/


    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)


    $query = "SELECT MAX(question_order) AS maxqo FROM {{questions}} WHERE sid=$iNewSID AND gid=$newgid";
    $res = Yii::app()->db->createCommand($query)->query();
    $resrow = $res->read();
    $newquestionorder = $resrow['maxqo'] + 1;
    if (is_null($newquestionorder)) {
        $newquestionorder = 0;
    } else {
        $newquestionorder++;
    }
    foreach ($xml->questions->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key=>$value) {
            $insertdata[(string) $key] = (string) $value;
        }

        $iOldSID = $insertdata['sid'];
        $insertdata['sid'] = $iNewSID;
        $insertdata['gid'] = $newgid;
        $insertdata['question_order'] = $newquestionorder;
        $iOldQID = $insertdata['qid']; // save the old qid
        unset($insertdata['qid']);

        // now translate any links
        if (!isset($xml->question_l10ns->rows->row)) {
            $insertdata['question'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
            $insertdata['help'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
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
                        $results['fatalerror'] = CHtml::errorSummary($oQuestion, gT("The question could not be imported for the following reasons:"));
                        return $results;
                    }
                    $results['importwarnings'][] = sprintf(gT("Question code %s was updated to %s."), $sOldTitle, $sNewTitle);
                    unset($sNewTitle);
                    unset($sOldTitle);
                }
            }
        }
        if (isset($insertdata['qid'])) {
            switchMSSQLIdentityInsert('questions', true);
        }
        
        if (!$oQuestion->save()) {
            $results['fatalerror'] = CHtml::errorSummary($oQuestion, gT("The question could not be imported for the following reasons:"));
            return $results;
        }
        if (isset($insertdata['qid'])) {
            switchMSSQLIdentityInsert('questions', false);
            $aQIDReplacements[$iOldQID] = $oQuestion->qid;
            ;
            $results['questions']++;
        }
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
            foreach ($row as $key=>$value) {
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
            if ($insertdata) {
                XSSFilterArray($insertdata);
            }
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
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'sq'.$sNewTitle;
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

                    $sNewTitle = 'r'.$rand.'sq'.$index;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;

                    if ($attempts > 10) {
                        safeDie(gT("Error").": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    safeDie(gT("Error while saving: ").print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                ;
                $results['questions']++;
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
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

            foreach ($row as $key=>$value) {
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
            if ($oAnswer->save() && isset($xml->answer_l10ns->rows->row)) {
                $aAIDReplacements[$iOldAID] = $oAnswer->aid;
            }
            $results['answers']++;
            if (isset($oAnswerL10n)) {
                $oAnswer = Answer::model()->findByAttributes(['qid'=>$insertdata['qid'], 'code'=>$insertdata['code'], 'scale_id'=>$insertdata['scale_id']]);
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
            foreach ($row as $key=>$value) {
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
        }
    }

    // Import questionattributes --------------------------------------------------------------
    if (isset($xml->question_attributes)) {
        $aAllAttributes = questionHelper::getAttributesDefinitions();
        foreach ($xml->question_attributes->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['qaid']);
            $insertdata['qid'] = $aQIDReplacements[(integer) $insertdata['qid']]; // remap the parent_qid


            if ($iDBVersion < 156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n']) {
                foreach ($importlanguages as $sLanguage) {
                    $insertdata['language'] = $sLanguage;
                    $attributes = new QuestionAttribute;
                    if ($insertdata) {
                        XSSFilterArray($insertdata);
                    }
                    foreach ($insertdata as $k => $v) {
                        $attributes->$k = $v;
                    }

                    $attributes->save();
                }
            } else {
                $attributes = new QuestionAttribute;
                if ($insertdata) {
                    XSSFilterArray($insertdata);
                }
                foreach ($insertdata as $k => $v) {
                    $attributes->$k = $v;
                }

                $attributes->save();
            }
            $results['question_attributes']++;
        }
    }

    // Import defaultvalues ------------------------------------------------------
    if (isset($xml->defaultvalues)) {
        $results['defaultvalues'] = 0;
        $aInsertData = array();
        foreach ($xml->defaultvalues->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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
            if ($insertdata) {
                XSSFilterArray($insertdata);
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
                    if ($iDvidOld > 0){
                        $aDvidReplacements[$iDvidOld] = $defaultValue->dvid;
                    }
                } else {
                    safeDie(gT("Error").": Failed to insert data[9]<br />");
                }
                $results['defaultvalues']++;
            }
            
        }

        // insert default values from LS v3 which doesn't have defaultvalue_l10ns
        if (!empty($aInsertData)){
            foreach($aInsertData as $qid => $aQid){
                foreach($aQid as $scaleId => $aScaleId){
                    foreach($aScaleId as $sqid => $aSqid){
                        foreach($aSqid as $specialtype => $aSpecialtype){
                            $oDefaultValue = new DefaultValue();
                            $oDefaultValue->setAttributes(array('qid' => $qid, 'scale_id' => $scaleId, 'sqid' => $sqid, 'specialtype' => $specialtype), false);
                            if ($oDefaultValue->save()){
                                $results['defaultvalues']++;
                                foreach($aSpecialtype as $language => $defaultvalue){
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['dvid'] = $aDvidReplacements[$insertdata['dvid']];
            unset($insertdata['id']);

            $oDefaultValueL10n = new DefaultValueL10n();
            $oDefaultValueL10n->setAttributes($insertdata, false);
            if (!$oDefaultValueL10n->save()) {
                safeDie(gT("Error").": Failed to insert data[19]<br />");
            }
        }
    }
    
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
* @return
*/
function XMLImportLabelsets($sFullFilePath, $options)
{
    $sXMLdata = (string) file_get_contents($sFullFilePath);
    $xml = simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
    if ($xml->LimeSurveyDocType != 'Label set') {
        safeDie('This is not a valid LimeSurvey label set structure XML file.');
    }
    $iDBVersion = (int) $xml->DBVersion;
    $aLSIDReplacements = $results = [];
    $results['labelsets'] = 0;
    $results['labels'] = 0;
    $results['warnings'] = array();
    $aImportedLabelSetIDs = array();

    // Import label sets table ===================================================================================
    foreach ($xml->labelsets->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key=>$value) {
            $insertdata[(string) $key] = (string) $value;
        }
        $iOldLabelSetID = $insertdata['lid'];
        unset($insertdata['lid']); // save the old qid

        // Insert the new question
        $arLabelset = new LabelSet();
        $arLabelset->setAttributes($insertdata);
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
            foreach ($row as $key=>$value) {
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
            foreach ($row as $key=>$value) {
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
 * @param string $sFullFilePath
 * @param boolean $bTranslateLinksFields
 * @param string $sNewSurveyName
 * @param integer $DestSurveyID
 */
function importSurveyFile($sFullFilePath, $bTranslateLinksFields, $sNewSurveyName = null, $DestSurveyID = null)
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
                TemplateConfiguration::checkAndcreateSurveyConfig($aImportResults['newsid']);
            }
            return $aImportResults;
        case 'txt':
        case 'tsv':
            $aImportResults = TSVImportSurvey($sFullFilePath);
            if ($aImportResults && $aImportResults['newsid']) {
                TemplateConfiguration::checkAndcreateSurveyConfig($aImportResults['newsid']);
            }
            return $aImportResults;
        case 'lsa':
            // Import a survey archive
            Yii::import("application.libraries.admin.pclzip.pclzip", true);
            $pclzip = new PclZip(array('p_zipname' => $sFullFilePath));
            $aFiles = $pclzip->listContent();

            if ($pclzip->extract(PCLZIP_OPT_PATH, Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR, PCLZIP_OPT_BY_EREG, '/(lss|lsr|lsi|lst)$/') == 0) {
                unset($pclzip);
            }
            $aImportResults = [];
            // Step 1 - import the LSS file and activate the survey
            foreach ($aFiles as $aFile) {
                if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lss') {
                    //Import the LSS file
                    $aImportResults = XMLImportSurvey(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'], null, null, null, true, false);
                    if ($aImportResults && $aImportResults['newsid']) {
                        TemplateConfiguration::checkAndcreateSurveyConfig($aImportResults['newsid']);
                    }
                    // Activate the survey
                    Yii::app()->loadHelper("admin/activate");
                    $survey = Survey::model()->findByPk($aImportResults['newsid']);
                    $surveyActivator = new SurveyActivator($survey);
                    $surveyActivator->activate();
                    unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                    break;
                }
            }

            // Step 2 - import the responses file
            foreach ($aFiles as $aFile) {
                if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lsr') {
                    //Import the LSS file
                    $aResponseImportResults = XMLImportResponses(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'], $aImportResults['newsid'], $aImportResults['FieldReMap']);
                    $aImportResults = array_merge($aResponseImportResults, $aImportResults);
                    $aImportResults['importwarnings'] = array_merge($aImportResults['importwarnings'], $aImportResults['warnings']);
                    unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                    break;
                }
            }

            // Step 3 - import the tokens file - if exists
            foreach ($aFiles as $aFile) {
                if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lst') {
                    Yii::app()->loadHelper("admin/token");
                    $aTokenImportResults = [];
                    if (Token::createTable($aImportResults['newsid'])) {
                        $aTokenCreateResults = array('tokentablecreated' => true);
                        $aImportResults = array_merge($aTokenCreateResults, $aImportResults);
                        $aTokenImportResults = XMLImportTokens(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'], $aImportResults['newsid']);
                    } else {
                        $aTokenImportResults['warnings'][] = gT("Unable to create survey participants table");
                    }

                    $aImportResults = array_merge_recursive($aTokenImportResults, $aImportResults);
                    $aImportResults['importwarnings'] = array_merge($aImportResults['importwarnings'], $aImportResults['warnings']);
                    unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                    break;
                }
            }
            // Step 4 - import the timings file - if exists
            Yii::app()->db->schema->refresh();
            foreach ($aFiles as $aFile) {
                if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lsi' && tableExists("survey_{$aImportResults['newsid']}_timings")) {
                    $aTimingsImportResults = XMLImportTimings(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename'], $aImportResults['newsid'], $aImportResults['FieldReMap']);
                    $aImportResults = array_merge($aTimingsImportResults, $aImportResults);
                    unlink(Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$aFile['filename']);
                    break;
                }
            }
            return $aImportResults;
        default:
            // Unknow file , return null why not throw error ?
            return null;
    }
}

/**
* This function imports a LimeSurvey .lss survey XML file
*
* @param string $sFullFilePath  The full filepath of the uploaded file
* @param string $sXMLdata
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
    $aDvidReplacements = array();
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
    $sTemplateName = '';


    $aLanguagesSupported = array();
    foreach ($xml->languages->language as $language) {
        $aLanguagesSupported[] = (string) $language;
    }

    $results['languages'] = count($aLanguagesSupported);

    // Import surveys table ====================================================
    
    foreach ($xml->surveys->rows->row as $row) {
        $insertdata = array();

        foreach ($row as $key=>$value) {
            // Set survey group id to default if not a copy
            if ($key == 'gsid' & !$isCopying) {
                $value = 1;
            }
            if ($key == 'template') {
                $sTemplateName = (string)$value;
            }
            $insertdata[(string) $key] = (string) $value;
        }
        $iOldSID = $results['oldsid'] = $insertdata['sid'];
        // Fix#14609 wishSID overwrite sid
        if(!is_null($iDesiredSurveyId)) {
            $insertdata['sid'] = $iDesiredSurveyId;
        }

        if ($iDBVersion < 145) {
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

        /* Remove unknow column */
        $aSurveyModelsColumns = Survey::model()->attributes;
        $aSurveyModelsColumns['wishSID'] = null; // Can not be imported
        $aBadData = array_diff_key($insertdata, $aSurveyModelsColumns);
        $insertdata = array_intersect_key($insertdata, $aSurveyModelsColumns);
        // Fill a optionnal array of error
        foreach ($aBadData as $key=>$value) {
            $results['importwarnings'][] = sprintf(gT("This survey setting has not been imported: %s => %s"), $key, $value);
        }
        $newSurvey = Survey::model()->insertNewSurvey($insertdata);
        if ($newSurvey->sid) {
            $iNewSID = $results['newsid'] = $newSurvey->sid;
            $results['surveys']++;
        } else {
            $results['error'] = gT("Unable to import survey.");
            return $results;
        }
    }

    // Import survey languagesettings table ===================================================================================
    foreach ($xml->surveys_languagesettings->rows->row as $row) {
        $insertdata = array();
        foreach ($row as $key=>$value) {
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
        }

        if (isset($insertdata['surveyls_attributecaptions']) && substr($insertdata['surveyls_attributecaptions'], 0, 1) != '{') {
            unset($insertdata['surveyls_attributecaptions']);
        }

        SurveyLanguageSetting::model()->insertNewSurvey($insertdata) or safeDie(gT("Error").": Failed to insert data [2]<br />");
    }


    // Import groups table ===================================================================================
    if (isset($xml->groups->rows->row)) {
        foreach ($xml->groups->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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
                    safeDie(gT("Error").": Failed to insert data [3]<br />");
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
            foreach ($row as $key=>$value) {
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
            $insertdata['group_name'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['group_name']);
            if (isset($insertdata['description'])) {
                $insertdata['description'] = translateLinks('survey', $iOldSID, $iNewSID, $insertdata['description']);
            }
            // #14646: fix utf8 encoding issue
            if (!mb_detect_encoding($insertdata['group_name'], 'UTF-8', true)) {
                $insertdata['group_name'] = utf8_encode($insertdata['group_name']);
            }
            // Insert the new group
            $oQuestionGroupL10n = new QuestionGroupL10n();
            $oQuestionGroupL10n->setAttributes($insertdata, false);
            if (!$oQuestionGroupL10n->save()) {
                throw new Exception(gT("Error while saving group: ").print_r($oQuestionGroupL10n->errors, true));
            }
        }
    }
    
    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)
    $aQuestionsMapping = array(); // collect all old and new question codes for replacement
    if (isset($xml->questions)) {
        // There could be surveys without a any questions.
        foreach ($xml->questions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'q'.$sNewTitle;
                    }

                    $oQuestion->title = $sNewTitle;
                }

                $attempts = 0;
                // Try to fix question title for unique question code enforcement
                $index = 0;
                $rand = mt_rand(0, 1024);
                while (!$oQuestion->validate(array('title'))) {
                    $sNewTitle = 'r'.$rand.'q'.$index;
                    $index++;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;
                    if ($attempts > 10) {
                        safeDie(gT("Error").": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    safeDie(gT("Error while saving: ").print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                ;
                $results['questions']++;
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
            $aQuestionsMapping[$iOldSID.'X'.$iOldGID.'X'.$iOldQID] = $iNewSID.'X'.$oQuestion->gid.'X'.$oQuestion->qid;
        }
    }

    // Import subquestions -------------------------------------------------------
    if (isset($xml->subquestions)) {
        foreach ($xml->subquestions->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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
            if ($insertdata) {
                XSSFilterArray($insertdata);
            }
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
                    $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
                    if (is_numeric(substr($sNewTitle, 0, 1))) {
                        $sNewTitle = 'sq'.$sNewTitle;
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

                    $sNewTitle = 'r'.$rand.'sq'.$index;
                    $oQuestion->title = $sNewTitle;
                    $attempts++;

                    if ($attempts > 10) {
                        safeDie(gT("Error").": Failed to resolve question code problems after 10 attempts.<br />");
                    }
                }
                if (!$oQuestion->save()) {
                    safeDie(gT("Error while saving: ").print_r($oQuestion->errors, true));
                }
                $aQIDReplacements[$iOldQID] = $oQuestion->qid;
                ;
                $results['subquestions']++;
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            unset($insertdata['id']);
            // now translate any links
            $insertdata['question'] = isset($insertdata['question']) ? translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']) : '';
            $insertdata['help'] = isset($insertdata['help']) ? translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']) : '';
            if (isset($aQIDReplacements[$insertdata['qid']])) {
                $insertdata['qid'] = $aQIDReplacements[$insertdata['qid']];
            } else {
                continue; //Skip invalid question ID
            }

            // question codes in format "38612X105X3011" are collected for replacing
            $aQuestionsMapping[$iOldSID.'X'.$iOldGID.'X'.$iOldQID.$oQuestion->title] = $iNewSID.'X'.$oQuestion->gid.'X'.$oQuestion->qid.$oQuestion->title;
            $oQuestionL10n = new QuestionL10n();
            $oQuestionL10n->setAttributes($insertdata, false);
            $oQuestionL10n->save();
        }
    }

    // Import answers ------------------------------------------------------------
    if (isset($xml->answers)) {
        foreach ($xml->answers->rows->row as $row) {
            $insertdata = array();

            foreach ($row as $key=>$value) {
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
            $results['answers']++;
            if (isset($oAnswerL10n)) {
                $oAnswer = Answer::model()->findByAttributes(['qid'=>$insertdata['qid'], 'code'=>$insertdata['code'], 'scale_id'=>$insertdata['scale_id']]);
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
            foreach ($row as $key=>$value) {
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
        }
    }
    
    // Import questionattributes -------------------------------------------------
    if (isset($xml->question_attributes)) {
        $aAllAttributes = questionHelper::getAttributesDefinitions();
        foreach ($xml->question_attributes->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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

            $insertdata['qid'] = $aQIDReplacements[(integer) $insertdata['qid']]; // remap the qid
            if ($iDBVersion < 156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n']) {
                foreach ($aLanguagesSupported as $sLanguage) {
                    $insertdata['language'] = $sLanguage;

                    if ($insertdata) {
                        XSSFilterArray($insertdata);
                    }
                    $questionAttribute = new QuestionAttribute();
                    $questionAttribute->attributes = $insertdata;
                    if (!$questionAttribute->save()) {
                        safeDie(gT("Error").": Failed to insert data[7]<br />");
                    }
                }
            } else {
                $questionAttribute = new QuestionAttribute();
                $questionAttribute->attributes = $insertdata;
                if (!$questionAttribute->save()) {
                    safeDie(gT("Error").": Failed to insert data[8]<br />");
                }
            }

            $results['question_attributes']++;
        }
    }

    // Import defaultvalues ------------------------------------------------------
    if (isset($xml->defaultvalues)) {
        $results['defaultvalues'] = 0;
        $aInsertData = array();
        foreach ($xml->defaultvalues->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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
            if ($insertdata) {
                XSSFilterArray($insertdata);
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
                    if ($iDvidOld > 0){
                        $aDvidReplacements[$iDvidOld] = $defaultValue->dvid;
                    }
                } else {
                    safeDie(gT("Error").": Failed to insert data[9]<br />");
                }
                $results['defaultvalues']++;
            }
            
        }

        // insert default values from LS v3 which doesn't have defaultvalue_l10ns
        if (!empty($aInsertData)){
            foreach($aInsertData as $qid => $aQid){
                foreach($aQid as $scaleId => $aScaleId){
                    foreach($aScaleId as $sqid => $aSqid){
                        foreach($aSqid as $specialtype => $aSpecialtype){
                            $oDefaultValue = new DefaultValue();
                            $oDefaultValue->setAttributes(array('qid' => $qid, 'scale_id' => $scaleId, 'sqid' => $sqid, 'specialtype' => $specialtype), false);
                            if ($oDefaultValue->save()){
                                $results['defaultvalues']++;
                                foreach($aSpecialtype as $language => $defaultvalue){
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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['dvid'] = $aDvidReplacements[$insertdata['dvid']];
            unset($insertdata['id']);

            $oDefaultValueL10n = new DefaultValueL10n();
            $oDefaultValueL10n->setAttributes($insertdata, false);
            if (!$oDefaultValueL10n->save()) {
                safeDie(gT("Error").": Failed to insert data[19]<br />");
            }
        }
    }
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
            foreach ($row as $key=>$value) {
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

                list($oldcsid, $oldcgid, $oldqidanscode) = explode("X", $insertdata["cfieldname"], 3);

                // replace the gid for the new one in the cfieldname(if there is no new gid in the $aGIDReplacements array it means that this condition is orphan -> error, skip this record)
                if (!isset($aGIDReplacements[$oldcgid])) {
                    continue;
                }
            }

            unset($insertdata["cid"]);

            // recreate the cfieldname with the new IDs
            if ($insertdata['cqid'] != 0) {
                if (preg_match("/^\+/", $oldcsid)) {
                    $newcfieldname = '+'.$iNewSID."X".$aGIDReplacements[$oldcgid]."X".$insertdata["cqid"].substr($oldqidanscode, strlen($oldcqid));
                } else {
                    $newcfieldname = $iNewSID."X".$aGIDReplacements[$oldcgid]."X".$insertdata["cqid"].substr($oldqidanscode, strlen($oldcqid));
                }
            } else {
                // The cfieldname is a not a previous question cfield but a {XXXX} replacement field
                $newcfieldname = $insertdata["cfieldname"];
            }
            $insertdata["cfieldname"] = $newcfieldname;
            if (trim($insertdata["method"]) == '') {
                $insertdata["method"] = '==';
            }

            // Now process the value and replace @sgqa@ codes
            if (preg_match("/^@(.*)@$/", $insertdata["value"], $cfieldnameInCondValue)) {
                if (isset($aOldNewFieldmap[$cfieldnameInCondValue[1]])) {
                    $newvalue = '@'.$aOldNewFieldmap[$cfieldnameInCondValue[1]].'@';
                    $insertdata["value"] = $newvalue;
                }
            }

            // now translate any links
            $result = Condition::model()->insertRecords($insertdata) or safeDie(gT("Error").": Failed to insert data[10]<br />");
            $results['conditions']++;
        }
    }

    // Import assessments --------------------------------------------------------
    if (isset($xml->assessments)) {
        foreach ($xml->assessments->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
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

            $insertdata['sid'] = $iNewSID; // remap the survey id
            // now translate any links
            $result = Assessment::model()->insertRecords($insertdata) or safeDie(gT("Error").": Failed to insert data[11]<br />");

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
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['id']) || (int)$insertdata['id'] < 1) {
                continue;
            }
            $insertdata['sid'] = $iNewSID; // remap the survey id
            $oldid = $insertdata['id'];
            unset($insertdata['id']);
            // now translate any links
            $result = Quota::model()->insertRecords($insertdata) or safeDie(gT("Error").": Failed to insert data[12]<br />");
            $aQuotaReplacements[$oldid] = getLastInsertID('{{quota}}');
            $results['quota']++;
        }
    }

    // Import quota_members ------------------------------------------------------
    if (isset($xml->quota_members)) {
        foreach ($xml->quota_members->rows->row as $row) {
            $quotaMember = new QuotaMember();
            $insertdata = array();
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['quota_id']) || (int)$insertdata['quota_id'] < 1) {
                continue;
            }
            $insertdata['sid'] = $iNewSID; // remap the survey id
            $insertdata['qid'] = $aQIDReplacements[(int) $insertdata['qid']]; // remap the qid
            if (isset($insertdata['quota_id'])) {
                $insertdata['quota_id'] = $aQuotaReplacements[(int) $insertdata['quota_id']]; // remap the qid
            }
            unset($insertdata['id']);
            // now translate any links
            $quotaMember->attributes = $insertdata;
            if (!$quotaMember->save()) {
                safeDie(gT("Error").": Failed to insert data[13]<br />");
            }
            $results['quotamembers']++;
        }
    }

    // Import quota_languagesettings----------------------------------------------
    if (isset($xml->quota_languagesettings)) {
        foreach ($xml->quota_languagesettings->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            if (!isset($insertdata['quotals_quota_id']) || (int)$insertdata['quotals_quota_id'] < 1) {
                continue;
            }
            $insertdata['autoload_url'] = 0; // used to bypass urlValidator check in QuotaLanguageSetting model
            $insertdata['quotals_quota_id'] = $aQuotaReplacements[(int) $insertdata['quotals_quota_id']]; // remap the qid
            unset($insertdata['quotals_id']);
            $quotaLanguagesSetting = new QuotaLanguageSetting();
            $quotaLanguagesSetting->attributes = $insertdata;
            if (!$quotaLanguagesSetting->save()) {
                safeDie(gT("Error").": Failed to insert data<br />");
            }
            $results['quotals']++;
        }
    }

    // Import survey_url_parameters ----------------------------------------------
    if (isset($xml->survey_url_parameters)) {
        foreach ($xml->survey_url_parameters->rows->row as $row) {
            $insertdata = array();
            foreach ($row as $key=>$value) {
                $insertdata[(string) $key] = (string) $value;
            }
            $insertdata['sid'] = $iNewSID; // remap the survey id
            if (isset($insertdata['targetsqid']) && $insertdata['targetsqid'] != '') {
                $insertdata['targetsqid'] = $aQIDReplacements[(int) $insertdata['targetsqid']]; // remap the qid
            }
            if (isset($insertdata['targetqid']) && $insertdata['targetqid'] != '') {
                $insertdata['targetqid'] = $aQIDReplacements[(int) $insertdata['targetqid']]; // remap the qid
            }
            unset($insertdata['id']);
            $result = SurveyURLParameter::model()->insertRecord($insertdata) or safeDie(gT("Error").": Failed to insert data[14]<br />");
            $results['survey_url_parameters']++;
        }
    }

    // Import Survey plugins settings
    if (isset($xml->plugin_settings)) {
        $pluginNamesWarning = array(); // To shown not exist warning only one time.
        foreach ($xml->plugin_settings->rows->row as $row) {
            // Find plugin id
            if (isset($row->name)) {
                $oPlugin = Plugin::model()->find("name = :name", array(":name"=>$row->name));
                if ($oPlugin) {
                    $setting = new PluginSetting;
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
            $aTemplateConfiguration['theme_current']['options'] = (array)json_decode($oTemplateConfigurationCurrent->attributes['options']);
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
                                $sOldValue = isset($aTemplateConfiguration['theme_original']['options'][$key])?$aTemplateConfiguration['theme_original']['options'][$key]:'';
                                $sNewValue = isset($aTemplateConfiguration['theme_current']['options'][$key])?$aTemplateConfiguration['theme_current']['options'][$key]:'';
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
                $results['importwarnings'][] = gT("Error").": Failed to insert data[18]<br />";
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
        array_unshift($results['importwarnings'], "<span class='warningtitle'>".gT('Attention: Several question codes were updated. Please check these carefully as the update  may not be perfect with customized expressions.').'</span>');
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);
    return $results;
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
        $results['error'] = gT("This is not a valid token data XML file.");
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
        // Get a list of all fieldnames in the survey participants table
        $aTokenFieldNames = Yii::app()->db->getSchema()->getTable($survey->tokensTableName, true);
        $aTokenFieldNames = array_keys($aTokenFieldNames->columns);
        $aFieldsToCreate = array_diff($aXLMFieldNames, $aTokenFieldNames);
        if (!function_exists('db_upgrade_all')) {
            Yii::app()->loadHelper('update/updatedb');
        }

        foreach ($aFieldsToCreate as $sField) {
            if (strpos($sField, 'attribute') !== false) {
                addColumn($survey->tokensTableName, $sField, 'string');
            }
        }
    }

    switchMSSQLIdentityInsert('tokens_'.$iSurveyID, true);
    foreach ($xml->tokens->rows->row as $row) {
        $insertdata = array();

        foreach ($row as $key=>$value) {
            $insertdata[(string) $key] = (string) $value;
        }

        $token = Token::create($iSurveyID, 'allowinvalidemail');
        $token->setAttributes($insertdata, false);
        if (!$token->encryptSave()) {
            $results['warnings'][] = CHtml::errorSummary($token, gT("Skipped tokens entry:"));
        } else {
            $results['tokens']++;
        }
    }
    switchMSSQLIdentityInsert('tokens_'.$iSurveyID, false);
    if (Yii::app()->db->getDriverName() == 'pgsql') {
        try {
            Yii::app()->db->createCommand("SELECT pg_catalog.setval(pg_get_serial_sequence('{{tokens_".$iSurveyID."}}', 'tid'), (SELECT MAX(tid) FROM {{tokens_".$iSurveyID."}}))")->execute();
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

    switchMSSQLIdentityInsert('survey_'.$iSurveyID, true);
    $results = [];
    $results['responses'] = 0;

    libxml_disable_entity_loader(false);
    $oXMLReader = new XMLReader();
    $oXMLReader->open($sFullFilePath);
    libxml_disable_entity_loader(true);
    if (Yii::app()->db->schema->getTable($survey->responsesTableName) !== null){
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

                        SurveyDynamic::model($iSurveyID)->insertRecords($aInsertData) or safeDie(gT("Error").": Failed to insert data[16]<br />");
                        $results['responses']++;
                    }
                }
            }
        }
        $oXMLReader->close();

        switchMSSQLIdentityInsert('survey_'.$iSurveyID, false);
        if (Yii::app()->db->getDriverName() == 'pgsql') {
            try {
                Yii::app()->db->createCommand("SELECT pg_catalog.setval(pg_get_serial_sequence('".$survey->responsesTableName."', 'id'), (SELECT MAX(id) FROM ".$survey->responsesTableName."))")->execute();
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
    // Read the file
    $handle = fopen($sFullFilePath, "r"); // Need to be adapted for Mac ? in options ?
    if ($handle === false) {
        safeDie("Can't open file");
    }
    while (!feof($handle)) {
        $buffer = fgets($handle); //To allow for very long lines . Another option is fgetcsv (0 to length), but need mb_convert_encoding
        $aFileResponses[] = mb_convert_encoding($buffer, "UTF-8", $aOptions['sCharset']);
    }
    // Close the file
    fclose($handle);
    if ($aOptions['bDeleteFistLine']) {
        array_shift($aFileResponses);
    }

    $aRealFieldNames = Yii::app()->db->getSchema()->getTable(SurveyDynamic::model($iSurveyId)->tableName())->getColumnNames();
    //$aCsvHeader=array_map("trim",explode($aOptions['sSeparator'], trim(array_shift($aFileResponses))));
    $aCsvHeader = str_getcsv(array_shift($aFileResponses), $aOptions['sSeparator'], $aOptions['sQuoted']);
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
                    foreach ($aCsvHeader as $i=>$name) {
                        if (preg_match('/^\d+X\d+X\d+/', $name)) {
                            $csv_ans_start_index = $i;
                            break;
                        }
                    }
                }
                // find out where the answer data columns start in destination table
                if (!isset($table_ans_start_index)) {
                    foreach ($aRealFieldNames as $i=>$name) {
                        if (preg_match('/^\d+X\d+X\d+/', $name)) {
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
    $iIdKey = array_search('id', $aCsvHeader); // the id is allways needed and used a lot
    if (is_int($iIdKey)) {
        unset($aKeyForFieldNames['id']);
    }
    $iSubmitdateKey = array_search('submitdate', $aCsvHeader); // submitdate can be forced to null
    if (is_int($iSubmitdateKey)) {
        unset($aKeyForFieldNames['submitdate']);
    }
    $iIdReponsesKey = (is_int($iIdKey)) ? $iIdKey : 0; // The key for reponses id: id column or first column if not exist

    // Import each responses line here
    while ($sResponses = array_shift($aFileResponses)) {
        $iNbResponseLine++;
        $bExistingsId = false;
        $aResponses = str_getcsv($sResponses, $aOptions['sSeparator'], $aOptions['sQuoted']);
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
                        $oSurvey = new SurveyDynamic;
                        break;
                    case 'replaceanswers':
                        break;
                    case 'renumber':
                        SurveyDynamic::sid($iSurveyId);
                        $oSurvey = new SurveyDynamic;
                        break;
                    case 'skip':
                    case 'ignore':
                    default:
                        $oSurvey = false; // Remove existing survey : don't import again
                        break;
                }
            } else {
                SurveyDynamic::sid($iSurveyId);
                $oSurvey = new SurveyDynamic;
            }
        } else {
            SurveyDynamic::sid($iSurveyId);
            $oSurvey = new SurveyDynamic;
        }
        if ($oSurvey) {
            // First rule for id and submitdate
            if (is_int($iIdKey)) {
                // Rule for id: only if id exists in vvimport file
                if (!$bExistingsId) {
                    // If not exist : allways import it
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

            foreach ($aKeyForFieldNames as $sFieldName=>$iFieldKey) {
                if ($aResponses[$iFieldKey] == '{question_not_shown}') {
                    $oSurvey->$sFieldName = new CDbExpression('NULL');
                } else {
                    $sResponse = str_replace(array("{quote}", "{tab}", "{cr}", "{newline}", "{lbrace}"), array("\"", "\t", "\r", "\n", "{"), $aResponses[$iFieldKey]);
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
                    switchMSSQLIdentityInsert('survey_'.$iSurveyId, true);
                    $bSwitched = true;
                }
                if ($oSurvey->encryptSave()) {
                    $beforeDataEntryImport = new PluginEvent('beforeDataEntryImport');
                    $beforeDataEntryImport->set('iSurveyID', $iSurveyId);
                    $beforeDataEntryImport->set('oModel', $oSurvey);
                    App()->getPluginManager()->dispatchEvent($beforeDataEntryImport);

                    $oTransaction->commit();
                    if ($bExistingsId && $aOptions['sExistingId'] != 'renumber') {
                        $aResponsesUpdated[] = $aResponses[$iIdReponsesKey];
                    } else {
                        $aResponsesInserted[] = $aResponses[$iIdReponsesKey];
                    }
                } else {
                    // Actually can not be, leave it if we have a $oSurvey->validate() in future release
                    $oTransaction->rollBack();
                    $aResponsesError[] = $aResponses[$iIdReponsesKey];
                }
                if (isset($bSwitched) && $bSwitched == true) {
                    switchMSSQLIdentityInsert('survey_'.$iSurveyId, false);
                    $bSwitched = false;
                }
            } catch (Exception $oException) {
                $oTransaction->rollBack();
                $aResponsesError[] = $aResponses[$iIdReponsesKey];
                // Show some error to user ?
                $CSVImportResult['errors'][]=$oException->getMessage(); // Show it in view
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
    foreach ($xml->timings->rows->row as $row) {
        $insertdata = array();

        foreach ($row as $key=>$value) {
            if ($key[0] == '_') {
                $key = substr($key, 1);
            }
            if (isset($aFieldReMap[substr($key, 0, -4)])) {
                $key = $aFieldReMap[substr($key, 0, -4)].'time';
            }
            $insertdata[$key] = (string) $value;
        }

        SurveyTimingDynamic::model($iSurveyID)->insertRecords($insertdata) or safeDie(gT("Error").": Failed to insert data[17]<br />");

        $results['responses']++;
    }
    return $results;
}


function XSSFilterArray(&$array)
{
    if (Yii::app()->getConfig('filterxsshtml') && !Permission::model()->hasGlobalPermission('superadmin', 'read')) {
        $filter = new CHtmlPurifier();
        $filter->options = array('URI.AllowedSchemes'=>array(
        'http' => true,
        'https' => true,
        ));
        foreach ($array as &$value) {
            $value = $filter->purify($value);
        }
    }
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

    $handle = fopen($sFullFilePath, 'r');
    if ($handle === false) {
        safeDie("Can't open file");
    }
    $bom = fread($handle, 2);
    rewind($handle);
    $aAttributeList = array(); //QuestionAttribute::getQuestionAttributesSettings();

    // Excel tends to save CSV as UTF-16, which PHP does not properly detect
    if ($bom === chr(0xff).chr(0xfe) || $bom === chr(0xfe).chr(0xff)) {
        // UTF16 Byte Order Mark present
        $encoding = 'UTF-16';
    } else {
        $file_sample = (string) fread($handle, 1000).'e'; //read first 1000 bytes
        // + e is a workaround for mb_string bug
        rewind($handle);

        $encoding = mb_detect_encoding($file_sample, 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
    }
    if ($encoding !== false && $encoding != 'UTF-8') {
        stream_filter_append($handle, 'convert.iconv.'.$encoding.'/UTF-8');
    }

    $file = stream_get_contents($handle);
    fclose($handle);
    // fix Excel non-breaking space
    $file = str_replace("0xC20xA0", ' ', $file);
    // Replace all different newlines styles with \n
    $file = preg_replace('~\R~u', "\n", $file);
    $tmp = fopen('php://temp', 'r+');
    fwrite($tmp, $file);
    rewind($tmp);
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
            $val = (isset($row[$i]) ? $row[$i] : '');
            // if Excel was used, it surrounds strings with quotes and doubles internal double quotes.  Fix that.
            if (preg_match('/^".*"$/', $val)) {
                $val = str_replace('""', '"', substr($val, 1, -1));
            }
            $rowarray[$rowheaders[$i]] = $val;
        }
        $adata[] = $rowarray;
    }
    fclose($tmp);

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
    $asidNew = 0;
    $qseq = 0; // question_order
    $qtype = 'T';
    $aseq = 0; // answer sortorder
    $attribute_index = 0;
    $answer_index = 0;
    $default_index = 0;
    $quota_index = 0;
    
    $ginfo = array();
    $qinfo = array();
    $sqinfo = array();
    $asinfo = array();

    if (isset($surveyinfo['language'])) {
        $baselang = $surveyinfo['language']; // the base language
    }

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
                $gname = ((!empty($row['name']) ? $row['name'] : 'G'.$gseq));
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
                $sGroupseq = (!empty($row['type/scale']) ? $row['type/scale'] : 'G'.$iGroupcounter++);
                $group['group_name'] = $gname;
                $group['grelevance'] = (isset($row['relevance']) ? $row['relevance'] : '');
                $group['description'] = (isset($row['text']) ? $row['text'] : '');
                $group['language'] = $glang;
                $group['randomization_group'] = (isset($row['random_group']) ? $row['random_group'] : '');

                // For multi language survey: same gid/sort order across all languages
                if (isset($ginfo[$sGroupseq])) {
                    $gid = $ginfo[$sGroupseq]['gid'];
                    $group['gid'] = $gid;
                    $group['group_order'] = $ginfo[$sGroupseq]['group_order'];
                } else {
                    if (empty($row['id'])) {
                        $gidNew += 1;
                        $gid = $gidNew;
                    } else {
                        $gid = $row['id'];
                    }

                    $group['gid'] = $gid;
                    $group['group_order'] = $gseq;
                }

                if (!isset($ginfo[$sGroupseq])) {
                    //$gid = $gseq;
                    $ginfo[$sGroupseq]['gid'] = $gid;
                    $ginfo[$sGroupseq]['group_order'] = $gseq++;
                }
                $qseq = 0; // reset the question_order

                $groups[] = $group;

                break;

            case 'Q':
                $question = array();
                $question['sid'] = $iNewSID;
                $qtype = (isset($row['type/scale']) ? $row['type/scale'] : 'T');
                $qname = (isset($row['name']) ? $row['name'] : 'Q'.$qseq);
                $question['gid'] = $gid;
                $question['type'] = $qtype;
                $question['title'] = $qname;
                $question['question'] = (isset($row['text']) ? $row['text'] : '');
                $question['relevance'] = (isset($row['relevance']) ? $row['relevance'] : '');
                $question['preg'] = (isset($row['validation']) ? $row['validation'] : '');
                $question['help'] = (isset($row['help']) ? $row['help'] : '');
                $question['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                $question['mandatory'] = (isset($row['mandatory']) ? $row['mandatory'] : '');
                $lastother = $question['other'] = (isset($row['other']) ? $row['other'] : 'N'); // Keep trace of other settings for sub question
                $question['same_default'] = (isset($row['same_default']) ? $row['same_default'] : 0);
                $question['parent_qid'] = 0;

                // For multi numeric survey : same name, add the gid to have same name on different gid. Bad for EM.
                $fullqname = 'G'.$gid.'_'.$qname;
                if (isset($qinfo[$fullqname])) {
                    $qseq = $qinfo[$fullqname]['question_order'];
                    $qid = $qinfo[$fullqname]['qid'];
                    $question['qid'] = $qid;
                    $question['question_order'] = $qseq;
                } else {
                    if (empty($row['id'])) {
                        $qidNew += 1;
                        $qid = $qidNew;
                    } else {
                        $qid = $row['id'];
                    }
                    $question['question_order'] = $qseq;
                    $question['qid'] = $qid;
                }

                $questions[] = $question;

                if (!isset($qinfo[$fullqname])) {
                    $qinfo[$fullqname]['qid'] = $qid;
                    $qinfo[$fullqname]['question_order'] = $qseq++;
                }
                $aseq = 0; //reset the answer sortorder
                $sqseq = 0; //reset the sub question sortorder
                // insert question attributes
                foreach ($row as $key=>$val) {
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
                        case 'default':
                            break;
                        default:
                            if ($key != '' && $val != '') {
                                $attribute = array();
                                $attribute['qid'] = $qid;
                                // check if attribute is a i18n attribute. If yes, set language, else set language to null in attribute table
                                $aAttributeList[$qtype] = QuestionAttribute::getQuestionAttributesSettings($qtype);
                                if (!empty($aAttributeList[$qtype][$key]['i18n'])) {
                                    $attribute['language'] = (isset($row['language']) ? $row['language'] : $baselang);
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
                    $defaultvalue['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                    $defaultvalue['defaultvalue'] = $row['default'];
                    $defaultvalues[] = $defaultvalue;
                }
                break;

            case 'SQ':
                $sqname = (isset($row['name']) ? $row['name'] : 'SQ'.$sqseq);
                $sqid = '';
                if ($qtype == Question::QT_O_LIST_WITH_COMMENT || $qtype == Question::QT_VERTICAL_FILE_UPLOAD) {
                    ;   // these are fake rows to show naming of comment and filecount fields
                } elseif ($sqname == 'other' && $lastother == "Y") {
                    // If last question have other to Y : it's not a real SQ row
                    if ($qtype == Question::QT_EXCLAMATION_LIST_DROPDOWN || $qtype == Question::QT_L_LIST_DROPDOWN) {
                        // only used to set default value for 'other' in these cases
                        if (isset($row['default']) && $row['default'] != "") {
                            $defaultvalue = array();
                            $defaultvalue['qid'] = $qid;
                            $defaultvalue['sqid'] = $sqid;
                            $defaultvalue['specialtype'] = 'other';
                            $defaultvalue['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                            $defaultvalue['defaultvalue'] = $row['default'];
                            $defaultvalues[] = $defaultvalue;
                        }
                    }
                } else {
                    $scale_id = (isset($row['type/scale']) ? $row['type/scale'] : 0);
                    $subquestion = array();
                    $subquestion['sid'] = $iNewSID;
                    $subquestion['gid'] = $gid;
                    $subquestion['parent_qid'] = $qid;
                    $subquestion['type'] = $qtype;
                    $subquestion['title'] = $sqname;
                    $subquestion['question'] = (isset($row['text']) ? $row['text'] : '');
                    $subquestion['relevance'] = (isset($row['relevance']) ? $row['relevance'] : '');
                    $subquestion['preg'] = (isset($row['validation']) ? $row['validation'] : '');
                    $subquestion['help'] = (isset($row['help']) ? $row['help'] : '');
                    $subquestion['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                    $subquestion['mandatory'] = (isset($row['mandatory']) ? $row['mandatory'] : '');
                    $subquestion['scale_id'] = $scale_id;
                    // For multi nueric language, qid is needed, why not gid. name is not unique.
                    $fullsqname = 'G'.$gid.'Q'.$qid.'_'.$scale_id.'_'.$sqname;
                    if (isset($sqinfo[$fullsqname])) {
                        $qseq = $sqinfo[$fullsqname]['question_order'];
                        $sqid = $sqinfo[$fullsqname]['sqid'];
                        $subquestion['question_order'] = $qseq;
                        $subquestion['qid'] = $sqid;
                    } else {
                        $subquestion['question_order'] = $qseq;
                        if (empty($row['id'])) {
                            $qidNew += 1;
                            $sqid = $qidNew;
                        } else {
                            $sqid = $row['id'];
                        }
                        
                        $subquestion['qid'] = $sqid;
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
                        $defaultvalue['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                        $defaultvalue['defaultvalue'] = $row['default'];
                        $defaultvalues[] = $defaultvalue;
                    }
                }
                break;
            case 'A':
                $answer = array();
                $answer['qid'] = $qid;
                $answer['code'] = (isset($row['name']) ? $row['name'] : 'A'.$aseq);
                $answer['answer'] = (isset($row['text']) ? $row['text'] : '');
                $answer['scale_id'] = (isset($row['type/scale']) ? $row['type/scale'] : 0);
                $answer['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                $answer['assessment_value'] = (int) (isset($row['assessment_value']) ? $row['assessment_value'] : '');
                $answer['sortorder'] = ++$aseq;
                $answers[] = $answer;
                break;
            case 'AS':
                $assessment = array();
                $assessment['sid'] = $iNewSID;
                $assessment['scope'] = isset($row['type/scale'])?$row['type/scale']:'';
                $assessment['gid'] = $gid;
                $assessment['name'] = isset($row['name'])?$row['name']:'';
                $assessment['minimum'] = isset($row['min_num_value'])?$row['min_num_value']:'';
                $assessment['maximum'] = isset($row['max_num_value'])?$row['max_num_value']:'';
                $assessment['message'] = isset($row['text'])?$row['text']:'';
                $assessment['language'] = isset($row['language'])?$row['language']:'';
                $assessment['id'] = isset($row['id'])?$row['id']:'';
                $assessments[] = $assessment;
                break;
            case 'QTA':
                $quota = array();
                $quota['id'] = isset($row['id'])?$row['id']:'';
                $quota['sid'] = $iNewSID;
                $quota['name'] = isset($row['name'])?$row['name']:'';
                $quota['qlimit'] = isset($row['mandatory'])?$row['mandatory']:'';
                $quota['action'] = isset($row['other'])?$row['other']:'';
                $quota['active'] = isset($row['default'])?$row['default']:'';
                $quota['autoload_url'] = isset($row['same_default'])?$row['same_default']:'';
                $quotas[] = $quota;
                break;
            case 'QTAM':
                $quota_member = array();
                $quota_member['quota_id'] = isset($row['related_id'])?$row['related_id']:'';
                $quota_member['sid'] = $iNewSID;
                $quota_member['qid'] = $qid;
                $quota_member['code'] = isset($row['name'])?$row['name']:'';
                $quota_members[] = $quota_member;
                break;
            case 'QTALS':
                $quota_languagesetting = array();
                $quota_languagesetting['quotals_quota_id'] = isset($row['related_id'])?$row['related_id']:'';
                $quota_languagesetting['quotals_language'] = isset($row['language'])?$row['language']:'';
                //$quota_languagesetting['quotals_name'] = isset($row['name'])?$row['name']:'';
                $quota_languagesetting['quotals_message'] = isset($row['relevance'])?$row['relevance']:'';
                $quota_languagesetting['quotals_url'] = isset($row['text'])?$row['text']:'';
                $quota_languagesetting['quotals_urldescrip'] = isset($row['help'])?$row['help']:'';
                $quota_languagesettings[] = $quota_languagesetting;
                break;
            case 'C':
                $condition = array();
                $condition['qid'] = $qid;
                $condition['scenario'] = $row['type/scale'];
                $condition['cqid'] = isset($row['related_id'])?$row['related_id']:'';
                $condition['cfieldname'] = $row['name'];
                $condition['method'] = $row['relevance'];
                $condition['value'] = $row['text'];
                $conditions[] = $condition;
                break;
        }
    }

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
    $surveylanguage = array_key_exists('language', $aData['surveys']['rows']['row'])?(array)$aData['surveys']['rows']['row']['language']:array('en');
    $surveyAdditionalLanguages = array_key_exists('additional_languages', $aData['surveys']['rows']['row']) && !empty($aData['surveys']['rows']['row']['additional_languages']) ? explode(' ', $aData['surveys']['rows']['row']['additional_languages']) : array();
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
