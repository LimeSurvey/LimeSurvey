<?php

namespace LimeSurvey\Helpers\Update;

use Yii;
use Question;
use QuestionTheme;
use Answer;
use QuestionAttribute;
use LSActiveRecord;
use Condition;
use Survey;
use ArchivedTableSettings;
use QuestionL10n;
use SurveyLanguageSetting;
use QuotaLanguageSetting;
use QuestionGroupL10n;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Update_641 extends DatabaseUpdateBase
{
    protected $scriptMapping;

    /**
     * This function generates an array containing the fieldcode, and matching data in the same order as the activate script
     *
     * @param Survey $survey Survey ActiveRecord model
     * @param string $style 'short' (default) or 'full' - full creates extra information like default values
     * @param ?boolean $force_refresh - Forces to really refresh the array, not just take the session copy
     * @param bool|int $questionid Limit to a certain qid only (for question preview) - default is false
     * @param string $sLanguage The language to use
     * @param array $aDuplicateQIDs
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    protected function createOldFieldMap($survey, $style = 'short', $force_refresh = false, $questionid = false, $sLanguage = '', &$aDuplicateQIDs = array())
    {

        $sLanguage = sanitize_languagecode($sLanguage);
        $surveyid = $survey->sid;
        //checks to see if fieldmap has already been built for this page.
        if (isset(Yii::app()->session['fieldmap-' . $surveyid . $sLanguage]) && !$force_refresh && $questionid === false) {
            return Yii::app()->session['fieldmap-' . $surveyid . $sLanguage];
        }
        /* Check if $sLanguage is a survey valid language (else $fieldmap is empty) */
        if ($sLanguage == '' || !in_array($sLanguage, $survey->allLanguages)) {
            $sLanguage = $survey->language;
        }
        $fieldmap = [];
        $fieldmap["id"] = array("fieldname" => "id", 'sid' => $surveyid, 'type' => "id", "gid" => "", "qid" => "", "aid" => "");
        if ($style == "full") {
            $fieldmap["id"]['title'] = "";
            $fieldmap["id"]['question'] = gT("Response ID");
            $fieldmap["id"]['group_name'] = "";
        }

        $fieldmap["submitdate"] = array("fieldname" => "submitdate", 'type' => "submitdate", 'sid' => $surveyid, "gid" => "", "qid" => "", "aid" => "");
        if ($style == "full") {
            $fieldmap["submitdate"]['title'] = "";
            $fieldmap["submitdate"]['question'] = gT("Date submitted");
            $fieldmap["submitdate"]['group_name'] = "";
        }

        $fieldmap["lastpage"] = array("fieldname" => "lastpage", 'sid' => $surveyid, 'type' => "lastpage", "gid" => "", "qid" => "", "aid" => "");
        if ($style == "full") {
            $fieldmap["lastpage"]['title'] = "";
            $fieldmap["lastpage"]['question'] = gT("Last page");
            $fieldmap["lastpage"]['group_name'] = "";
        }

        $fieldmap["startlanguage"] = array("fieldname" => "startlanguage", 'sid' => $surveyid, 'type' => "startlanguage", "gid" => "", "qid" => "", "aid" => "");
        if ($style == "full") {
            $fieldmap["startlanguage"]['title'] = "";
            $fieldmap["startlanguage"]['question'] = gT("Start language");
            $fieldmap["startlanguage"]['group_name'] = "";
        }

        $fieldmap['seed'] = array('fieldname' => 'seed', 'sid' => $surveyid, 'type' => 'seed', 'gid' => '', 'qid' => '', 'aid' => '');
        if ($style == 'full') {
            $fieldmap["seed"]['title'] = "";
            $fieldmap["seed"]['question'] = gT("Seed");
            $fieldmap["seed"]['group_name'] = "";
        }

        //Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
        $prow = $survey->getAttributes(); //Checked

        if ($prow['anonymized'] == "N" && $survey->hasTokensTable) {
            $fieldmap["token"] = array("fieldname" => "token", 'sid' => $surveyid, 'type' => "token", "gid" => "", "qid" => "", "aid" => "");
            if ($style == "full") {
                $fieldmap["token"]['title'] = "";
                $fieldmap["token"]['question'] = gT("Access code");
                $fieldmap["token"]['group_name'] = "";
            }
        }
        if ($prow['datestamp'] == "Y") {
            $fieldmap["startdate"] = array("fieldname" => "startdate",
                'type' => "startdate",
                'sid' => $surveyid,
                "gid" => "",
                "qid" => "",
                "aid" => "");
            if ($style == "full") {
                $fieldmap["startdate"]['title'] = "";
                $fieldmap["startdate"]['question'] = gT("Date started");
                $fieldmap["startdate"]['group_name'] = "";
            }

            $fieldmap["datestamp"] = array("fieldname" => "datestamp",
                'type' => "datestamp",
                'sid' => $surveyid,
                "gid" => "",
                "qid" => "",
                "aid" => "");
            if ($style == "full") {
                $fieldmap["datestamp"]['title'] = "";
                $fieldmap["datestamp"]['question'] = gT("Date last action");
                $fieldmap["datestamp"]['group_name'] = "";
            }
        }
        if ($prow['ipaddr'] == "Y") {
            $fieldmap["ipaddr"] = array("fieldname" => "ipaddr",
                'type' => "ipaddress",
                'sid' => $surveyid,
                "gid" => "",
                "qid" => "",
                "aid" => "");
            if ($style == "full") {
                $fieldmap["ipaddr"]['title'] = "";
                $fieldmap["ipaddr"]['question'] = gT("IP address");
                $fieldmap["ipaddr"]['group_name'] = "";
            }
        }
        // Add 'refurl' to fieldmap.
        if ($prow['refurl'] == "Y") {
            $fieldmap["refurl"] = array("fieldname" => "refurl", 'type' => "url", 'sid' => $surveyid, "gid" => "", "qid" => "", "aid" => "");
            if ($style == "full") {
                $fieldmap["refurl"]['title'] = "";
                $fieldmap["refurl"]['question'] = gT("Referrer URL");
                $fieldmap["refurl"]['group_name'] = "";
            }
        }

        $sOldLanguage = App()->language;
        App()->setLanguage($sLanguage);
        // Collect all default values once so don't need separate query for each question with defaults
        // First collect language specific defaults

        $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, al10.defaultvalue"
            . " FROM {{defaultvalues}} as a "
            . " JOIN {{defaultvalue_l10ns}} as al10 ON a.dvid = al10.dvid " // We NEED a default value set
            . " JOIN {{questions}} as b ON a.qid = b.qid " // We NEED only question in this survey
            . " AND al10.language = '{$sLanguage}'"
            . " AND b.same_default=0"
            . " AND b.sid = " . $surveyid;
        $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();
        $defaultValues = array(); // indexed by question then subquestion
        foreach ($defaultResults as $dv) {
            if ($dv['specialtype'] != '') {
                $sq = $dv['specialtype'];
            } else {
                $sq = $dv['sqid'];
            }
            $defaultValues[$dv['qid'] . '~' . $sq] = $dv['defaultvalue'];
        }

        // Now overwrite language-specific defaults (if any) base language values for each question that uses same_defaults=1
        $baseLanguage = $survey->language;
        $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, al10.defaultvalue"
            . " FROM {{defaultvalues}} as a "
            . " JOIN {{defaultvalue_l10ns}} as al10 ON a.dvid = al10.dvid " // We NEED a default value set
            . " JOIN {{questions}} as b ON a.qid = b.qid " // We NEED only question in this survey
            . " AND al10.language = '{$baseLanguage}'"
            . " AND b.same_default=1"
            . " AND b.sid = " . $surveyid;
        $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();

        foreach ($defaultResults as $dv) {
            if ($dv['specialtype'] != '') {
                $sq = $dv['specialtype'];
            } else {
                $sq = $dv['sqid'];
            }
            $defaultValues[$dv['qid'] . '~' . $sq] = $dv['defaultvalue'];
        }

        // Main query
        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $aquery = "SELECT g.*, q.*, gls.*, qls.*"
            . " FROM $quotedGroups g"
            . ' JOIN {{questions}} q on q.gid=g.gid '
            . ' JOIN {{group_l10ns}} gls on gls.gid=g.gid '
            . ' JOIN {{question_l10ns}} qls on qls.qid=q.qid '
            . " WHERE qls.language='{$sLanguage}' and gls.language='{$sLanguage}' AND"
            . " g.sid={$surveyid} AND"
            . " q.parent_qid=0";
        if ($questionid !== false) {
            $aquery .= " and questions.qid={$questionid} ";
        }
        $aquery .= " ORDER BY group_order, question_order";
        /** @var Question[] $questions */
        $questions = Yii::app()->db->createCommand($aquery)->queryAll();
        $qids = [0];
        foreach ($questions as $q) {
            $qids[] = $q['qid'];
        }
        $rawQuestions = Question::model()->with('answers')->findAllByPk($qids);
        $qs = [];
        foreach ($rawQuestions as $rawQuestion) {
            $qs[$rawQuestion->qid] = $rawQuestion;
        }
        $questionSeq = -1; // this is incremental question sequence across all groups
        $groupSeq = -1;
        $_groupOrder = -1;

        $questionTypeMetaData = QuestionTheme::findQuestionMetaDataForAllTypes();
        foreach ($questions as $arow) {
            //For each question, create the appropriate field(s))

            ++$questionSeq;

            // fix fact that the group_order may have gaps
            if ($_groupOrder != $arow['group_order']) {
                $_groupOrder = $arow['group_order'];
                ++$groupSeq;
            }
            // Condition indicators are obsolete with EM.  However, they are so tightly coupled into LS code that easider to just set values to 'N' for now and refactor later.
            $conditions = 'N';
            $usedinconditions = 'N';

            // Check if answertable has custom setting for current question
            if (isset($arow['attribute']) && isset($arow['type']) && isset($arow['question_theme_name'])) {
                $answerColumnDefinition = QuestionTheme::getAnswerColumnDefinition($arow['question_theme_name'], $arow['type']);
            }

            // Field identifier
            // GXQXSXA
            // G=Group  Q=Question S=Subquestion A=Answer Option
            // If S or A don't exist then set it to 0
            // Implicit (subqestion intermal to a question type ) or explicit qubquestions/answer count starts at 1

            // Types "L", "!", "O", "D", "G", "N", "X", "Y", "5", "S", "T", "U"
            $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";

            if ($questionTypeMetaData[$arow['type']]['settings']->subquestions == 0 && $arow['type'] != Question::QT_R_RANKING && $arow['type'] != Question::QT_VERTICAL_FILE_UPLOAD) {
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                }

                $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => "{$arow['type']}", 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "");
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    if (isset($defaultValues[$arow['qid'] . '~0'])) {
                        $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~0'];
                    }
                }
                switch ($arow['type']) {
                    case Question::QT_L_LIST:  //RADIO LIST
                    case Question::QT_EXCLAMATION_LIST_DROPDOWN:  //DROPDOWN LIST
                        if ($arow['other'] == "Y") {
                            $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                            if (isset($fieldmap[$fieldname])) {
                                $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                            }

                            $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                                'type' => $arow['type'],
                                'sid' => $surveyid,
                                "gid" => $arow['gid'],
                                "qid" => $arow['qid'],
                                "aid" => "other");
                            if (isset($answerColumnDefinition)) {
                                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                            }

                            // dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
                            if ($style == "full") {
                                $fieldmap[$fieldname]['title'] = $arow['title'];
                                $fieldmap[$fieldname]['question'] = $arow['question'];
                                $fieldmap[$fieldname]['subquestion'] = gT("Other");
                                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                                if (isset($defaultValues[$arow['qid'] . '~other'])) {
                                    $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~other'];
                                }
                            }
                        }
                        break;
                    case Question::QT_O_LIST_WITH_COMMENT: //DROPDOWN LIST WITH COMMENT
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                        }

                        $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                            'type' => $arow['type'],
                            'sid' => $surveyid,
                            "gid" => $arow['gid'],
                            "qid" => $arow['qid'],
                            "aid" => "comment");
                        if (isset($answerColumnDefinition)) {
                            $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                        }

                        // dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion'] = gT("Comment");
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        }
                        break;
                }
            } elseif ($questionTypeMetaData[$arow['type']]['settings']->subquestions == 2 && $questionTypeMetaData[$arow['type']]['settings']->answerscales == 0) {
                // For Multi flexi question types
                $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
                //Now first process scale=1
                $answerset = array();
                $answerList = array();
                foreach ($abrows as $key => $abrow) {
                    if ($abrow['scale_id'] == 1) {
                        $answerset[] = $abrow;
                        $answerList[] = array(
                            'code' => $abrow['title'],
                            'answer' => $abrow['question'],
                        );
                        unset($abrows[$key]);
                    }
                }
                reset($abrows);
                foreach ($abrows as $abrow) {
                    foreach ($answerset as $answer) {
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}_{$answer['title']}";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                        }
                        $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                            'type' => $arow['type'],
                            'sid' => $surveyid,
                            "gid" => $arow['gid'],
                            "qid" => $arow['qid'],
                            "aid" => $abrow['title'] . "_" . $answer['title'],
                            "sqid" => $abrow['qid']);
                        if (isset($answerColumnDefinition)) {
                            $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                        }

                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion1'] = $abrow['question'];
                            $fieldmap[$fieldname]['subquestion2'] = $answer['question'];
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                            $fieldmap[$fieldname]['preg'] = $arow['preg'];
                            $fieldmap[$fieldname]['answerList'] = $answerList;
                            $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                        }
                    }
                }
                unset($answerset);
            } elseif ($arow['type'] == Question::QT_1_ARRAY_DUAL) {
                $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
                foreach ($abrows as $abrow) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#0";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }

                    $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'], "scale_id" => 0);
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['scale'] = gT('Scale 1');
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    }

                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#1";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'], "scale_id" => 1);
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['scale'] = gT('Scale 2');
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        // TODO SQrelevance for different scales? $fieldmap[$fieldname]['SQrelevance']=$abrow['relevance'];
                    }
                }
            } elseif ($arow['type'] == Question::QT_R_RANKING) {
                // Sub question by answer number OR attribute
                $answersCount = intval(Answer::model()->countByAttributes(array('qid' => $arow['qid'])));
                $maxDbAnswer = QuestionAttribute::model()->find("qid = :qid AND attribute = 'max_subquestions'", array(':qid' => $arow['qid']));
                $columnsCount = (!$maxDbAnswer || intval($maxDbAnswer->value) < 1) ? $answersCount : intval($maxDbAnswer->value);
                $columnsCount = min($columnsCount, $answersCount); // Can not be upper than current answers #14899
                for ($i = 1; $i <= $columnsCount; $i++) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $i);
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = sprintf(gT('Rank %s'), $i);
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                }
            } elseif ($arow['type'] == Question::QT_VERTICAL_FILE_UPLOAD) {
                $qidattributes = QuestionAttribute::model()->getQuestionAttributes($qs[$arow['qid']] ?? $arow['qid']);
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
                $fieldmap[$fieldname] = array(
                    "fieldname" => $fieldname,
                    'type' => $arow['type'],
                    'sid' => $surveyid,
                    "gid" => $arow['gid'],
                    "qid" => $arow['qid'],
                    "aid" => ''
                );
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['max_files'] = $qidattributes['max_num_of_files'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}" . "_filecount";
                $fieldmap[$fieldname] = array(
                    "fieldname" => $fieldname,
                    'type' => $arow['type'],
                    'sid' => $surveyid,
                    "gid" => $arow['gid'],
                    "qid" => $arow['qid'],
                    "aid" => "filecount"
                );
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = "filecount - " . $arow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
            } else {
                // Question types with subquestions and one answer per subquestion  (M/A/B/C/E/F/H/P)
                //MULTI ENTRY
                $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
                foreach ($abrows as $abrow) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}";

                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                        'type' => $arow['type'],
                        'sid' => $surveyid,
                        'gid' => $arow['gid'],
                        'qid' => $arow['qid'],
                        'aid' => $abrow['title'],
                        'sqid' => $abrow['qid']);
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['preg'] = $arow['preg'];
                        // get SQrelevance from DB
                        $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                        if (isset($defaultValues[$arow['qid'] . '~' . $abrow['qid']])) {
                            $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~' . $abrow['qid']];
                        }
                    }
                    if ($arow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}comment";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                        }
                        $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'] . "comment");
                        if (isset($answerColumnDefinition)) {
                            $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                        }
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion1'] = gT('Comment');
                            $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        }
                    }
                }
                if ($arow['other'] == "Y" && ($arow['type'] == Question::QT_M_MULTIPLE_CHOICE || $arow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS)) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "other");
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT('Other');
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['other'] = $arow['other'];
                    }
                    if ($arow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                        }
                        $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "othercomment");
                        if (isset($answerColumnDefinition)) {
                            $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                        }

                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion'] = gT('Other comment');
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                            $fieldmap[$fieldname]['other'] = $arow['other'];
                        }
                    }
                }
            }
            if (isset($fieldmap[$fieldname])) {
                //set question relevance (uses last SQ's relevance field for question relevance)
                $fieldmap[$fieldname]['relevance'] = $arow['relevance'];
                $fieldmap[$fieldname]['grelevance'] = $arow['grelevance'];
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                $fieldmap[$fieldname]['preg'] = $arow['preg'];
                $fieldmap[$fieldname]['other'] = $arow['other'];
                $fieldmap[$fieldname]['help'] = $arow['help'];

                // Set typeName
            } else {
                --$questionSeq; // didn't generate a valid $fieldmap entry, so decrement the question counter to ensure they are sequential
            }
        }
        App()->setLanguage($sOldLanguage);

        if ($questionid === false) {
            // If the fieldmap was randomized, the master will contain the proper order.  Copy that fieldmap with the new language settings.
            if (isset(Yii::app()->session['responses_' . $surveyid]['fieldmap-' . $surveyid . '-randMaster'])) {
                $masterFieldmap = Yii::app()->session['responses_' . $surveyid]['fieldmap-' . $surveyid . '-randMaster'];
                $mfieldmap = Yii::app()->session['responses_' . $surveyid][$masterFieldmap];
                foreach ($mfieldmap as $fieldname => $mf) {
                    if (isset($fieldmap[$fieldname])) {
                        // This array holds the keys of translatable attributes
                        $translatable = array_flip(array('question', 'subquestion', 'subquestion1', 'subquestion2', 'group_name', 'answerList', 'defaultValue', 'help'));
                        // We take all translatable attributes from the new fieldmap
                        $newText = array_intersect_key($fieldmap[$fieldname], $translatable);
                        // And merge them with the other values from the random fieldmap like questionSeq, groupSeq etc.
                        $mf = $newText + $mf;
                    }
                    $mfieldmap[$fieldname] = $mf;
                }
                $fieldmap = $mfieldmap;
            }

            Yii::app()->session['fieldmap-' . $surveyid . $sLanguage] = $fieldmap;
        }
        return $fieldmap;
    }

    public function doPreparations()
    {
        $scripts = [];
        switch (Yii::app()->db->getDriverName()) {
            case 'pgsql':
                $scripts[] =
                    "
                CREATE OR REPLACE FUNCTION show_create_table(table_name text, join_char text = E'\n' ) 
                  RETURNS text AS 
                \$BODY\$
                SELECT 'CREATE TABLE ' || $1 || ' (' || $2 || '' || 
                    string_agg(column_list.column_expr, ', ' || $2 || '') || 
                    '' || $2 || ');'
                FROM (
                  SELECT '    \"' || column_name || '\" ' || data_type || 
                       coalesce('(' || character_maximum_length || ')', '') || 
                       case when is_nullable = 'YES' then '' else ' NOT NULL' end as column_expr
                  FROM information_schema.columns
                  WHERE table_schema = 'public' AND table_name = $1
                  ORDER BY ordinal_position) column_list;
                \$BODY\$
                  LANGUAGE SQL STABLE
                ;
                "
                ;
                break;
        }
        foreach ($scripts as $script) {
            $this->db->createCommand($script)->execute();
        }
    }

    public function getResponsesScript()
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = DATABASE() AND
                      TABLE_NAME REGEXP '^.*survey_[0-9]*(_[0-9]*)?$'
                ";
            case 'pgsql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = current_database() AND
                      REGEXP_COUNT(TABLE_NAME, '^.*survey_[0-9]*(_[0-9]*)?$') > 0;
                ";
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = db_name() AND
                      TABLE_NAME LIKE '%survey_%' AND
                      TABLE_NAME NOT LIKE '%timings%' AND
                      RIGHT(TABLE_NAME, 1) NOT IN ('s', 'u');
                ";
            default:
                return "";
        }
    }

    public function getTimingScript()
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = DATABASE() AND
                      TABLE_NAME LIKE '%timings%';
                ";
            case 'pgsql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = current_database() AND
                      TABLE_NAME LIKE '%timings%';
                ";
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = db_name() AND
                      TABLE_NAME LIKE '%timings%';
                ";
            default:
                return "";
        }
    }

    public function getFieldsScript()
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.columns
                WHERE TABLE_SCHEMA = DATABASE() AND (
                      (
                          COLUMN_NAME REGEXP '^[0-9]*X[0-9]*X[0-9]*(.*)$' AND
                          (TABLE_NAME LIKE '%survey%')
                      ) OR
                      (
                          COLUMN_NAME REGEXP '^[0-9]*X[0-9]*(X[0-9]*)?(.*)$' AND
                          (TABLE_NAME LIKE '%survey%')
                      )
                )
                ORDER BY TABLE_NAME, COLUMN_NAME
                ";
            case 'pgsql':
                return "
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.columns
                WHERE TABLE_CATALOG = current_database() AND (
                      (
                          REGEXP_COUNT(COLUMN_NAME, '^[0-9]*X[0-9]*X[0-9]*(.*)$') > 0 AND
                          (TABLE_NAME LIKE '%survey%')
                      ) OR
                      (
                          REGEXP_COUNT(COLUMN_NAME, '^[0-9]*X[0-9]*(X[0-9]*)?(.*)$') > 0 AND
                          (TABLE_NAME LIKE '%survey%')
                      )
                )
                ORDER BY TABLE_NAME, COLUMN_NAME;
                ";
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.columns
                WHERE TABLE_CATALOG = db_name() AND (
                      (
					      TABLE_NAME LIKE '%survey_[0-9]%' AND
                          COLUMN_NAME LIKE '%X%'
                      )
                )
                ORDER BY TABLE_NAME, COLUMN_NAME;
                ";
            default:
                return "";
        }
    }

    public function showCreateTable(string $tableName)
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "SHOW CREATE TABLE " . $tableName;
            case 'pgsql':
                return "SELECT show_create_table('" . $tableName . "') AS \"Create Table\"";
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT
                	'CREATE TABLE '  + SCHEMA_NAME(t.schema_id) + '.' + t.name + ' (' +
                		STUFF ((
                			SELECT ', [' + c2.name + '] ' + type_name(c2.user_type_id) + 
							    CASE
								    WHEN type_name(c2.user_type_id) = 'nvarchar' THEN ' (256)'
									ELSE ''
								END +
                				CASE
                					WHEN c2.is_nullable = 1 THEN ' NULL'
                					ELSE ' NOT NULL'
                				END + 
                				CASE
                					WHEN c2.column_id = 1 AND c2.is_identity = 1 THEN ' IDENTITY (1,1)'
                					ELSE ''
                				END +
                				CASE
                					WHEN pk.column_id IS NOT NULL THEN ' PRIMARY KEY'
                					ELSE ''
                				END
                			FROM sys.columns c2
                			LEFT JOIN (
                				SELECT ic.object_id, ic.column_id, ic.index_column_id
                				FROM sys.index_columns ic
                				JOIN sys.indexes i ON 
                						i.object_id  = ic.object_id
                					AND i.index_id = ic.index_id
                				WHERE i.is_primary_key = 1
                			) pk ON 
                					pk.object_id = c2.object_id
                				AND pk.column_id = c2.column_id
                			WHERE c2.object_id = t.object_id
                			ORDER BY c2.column_id
                			FOR XML PATH (''), TYPE
                			).value('.', 'NVARCHAR(MAX)'), 1, 2, '') + 
                		')' AS [Create Table]
                FROM sys.tables t
                JOIN sys.schemas s ON t.schema_id = s.schema_id
                WHERE s.name = 'dbo' and t.name = '{$tableName}'
                ORDER BY t.name;
                ";
        }
    }

    public function adjustShowCreateTable(array $script, string $tableName)
    {
        if (strpos($tableName, "old") === false) {
            switch (Yii::app()->db->getDriverName()) {
                case 'pgsql':
                    $script["Create Table"] = str_replace("\"id\" integer NOT NULL", "\"id\" serial PRIMARY KEY", $script["Create Table"]);
                    break;
            }
        }
        switch (Yii::app()->db->getDriverName()) {
            case 'mssql':
            case 'sqlsrv':
                $script["Create Table"] = str_replace("[id] int NOT NULL PRIMARY KEY", "[id] int IDENTITY(1, 1) PRIMARY KEY", $script["Create Table"]);
                break;
        }
        return $script;
    }

    public function getFieldsFromTableScript($tableName)
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '{$tableName}'";
            case 'pgsql':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_CATALOG = current_database() AND TABLE_NAME = '{$tableName}'";
            case 'mssql':
            case 'sqlsrv':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_CATALOG = db_name() AND TABLE_NAME = '{$tableName}'";
            default:
                return "";
        }
    }

    /**
     * Fixes textual data, replacing old fieldname representation with new fieldname representation. We don't save the record even if changed here, because
     * outside of the method we may want to do additional things
     * @param LSActiveRecord $record the record whose fields are to be fixed
     * @param mixed $fields the fields that need to be fixed for the record
     * @param mixed $replacements the mapping of old fieldnames and new fieldnames
     * @return bool whether the record has changed and is likely to be saved
     */
    protected function fixText(LSActiveRecord &$record, $fields, $replacements)
    {
        $changed = false;
        foreach ($fields as $field) {
            $original = $record->{$field};
            foreach ($replacements as $old => $new) {
                if ($record->{$field}) {
                    $record->{$field} = str_replace($old, $new, $record->{$field});
                }
            }
            if ($original !== $record->{$field}) {
                $changed = true;
            }
        }
        return $changed;
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function up()
    {
        $leftSeparator = $rightSeparator = "`";
        if (Yii::app()->db->getDriverName() === 'pgsql') {
            $leftSeparator = $rightSeparator = '"';
        } elseif (in_array(Yii::app()->db->getDriverName(), ['mssql', 'sqlsrv'])) {
            $leftSeparator = "[";
            $rightSeparator = "]";
        }
        $this->doPreparations();
        $this->scriptMapping = [
            'responses' => $this->getResponsesScript(),
            'timings' => $this->getTimingScript(),
            'fields' => $this->getFieldsScript()
        ];
        $scripts = [];
        $responsesTables = $this->db->createCommand($this->scriptMapping['responses'])->queryAll();

        foreach ($responsesTables as $responsesTable) {
            if (((strpos($responsesTable['old_name'], "old_") === false) && (strpos($responsesTable['old_name'], "timing") === false))) {
                $parts = explode("_", $responsesTable['old_name']);
            }
            $scripts[$responsesTable['old_name']] = [
                'new_name' => $responsesTable['new_name'],
                'old_name' => $responsesTable['old_name'],
                'handled' => false
            ];
            $createTable = $this->adjustShowCreateTable($this->db->createCommand($this->showCreateTable($responsesTable['old_name']))->queryRow(), $responsesTable['old_name']);
            $scripts[$responsesTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$responsesTable['old_name']]['DROP'] = "DROP TABLE {$responsesTable['old_name']}";
            $scripts[$responsesTable['old_name']]['columns'] = $this->db->createCommand($this->getFieldsFromTableScript($responsesTable['old_name']))->queryAll();
        }
        $timingsTables = $this->db->createCommand($this->scriptMapping['timings'])->queryAll();
        foreach ($timingsTables as $timingsTable) {
            $scripts[$timingsTable['old_name']] = [
                'new_name' => $timingsTable['new_name'],
                'old_name' => $timingsTable['old_name'],
                'handled' => false
            ];
            $createTable = $this->adjustShowCreateTable($this->db->createCommand($this->showCreateTable($timingsTable['old_name']))->queryRow(), $responsesTable['old_name']);
            $scripts[$timingsTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$timingsTable['old_name']]['DROP'] = "DROP TABLE {$timingsTable['old_name']}";
            $scripts[$timingsTable['old_name']]['columns'] = $this->db->createCommand($this->getFieldsFromTableScript($timingsTable['old_name']))->queryAll();
        }
        $fields = $this->db->createCommand($this->scriptMapping['fields'])->queryAll();
        $fieldMap = [];
        foreach ($fields as $field) {
            if (!isset($field['TABLE_NAME'])) {
                if (isset($field['table_name'])) {
                    $field['TABLE_NAME'] = $field['table_name'];
                }
            }
            if (!isset($field['COLUMN_NAME'])) {
                if (isset($field['column_name'])) {
                    $field['COLUMN_NAME'] = $field['column_name'];
                }
            }
            $tableName = $field['TABLE_NAME'];
            if (!isset($fieldMap[$field['TABLE_NAME']])) {
                $fieldMap[$field['TABLE_NAME']] = [];
            }
            $fieldName = $field['COLUMN_NAME'];
            $split = explode("X", $fieldName);
            $sid = $split[0];
            $gid = $split[1];
            $qids = [];
            $position = 0;
            if (count($split) > 2) {
                while (($position < strlen($split[2])) && ctype_digit($split[2][$position])) {
                    $qids [] = (count($qids) ? ($qids[count($qids) - 1] . $split[2][$position]) : $split[2][$position]);
                    $position++;
                }
                $commaSeparatedQIDs = implode(",", $qids);
                $questions = Question::model()->with('answers')->findAll([
                    'condition' => "sid = {$sid} and gid = {$gid} and (t.qid in ({$commaSeparatedQIDs}) or parent_qid in ({$commaSeparatedQIDs}))"
                ]);
            }
            if (count($questions) || ((strpos($tableName, "timings") !== false) && ($split > 1))) {
                $fieldMap[$tableName][$fieldName] = getFieldName($tableName, $fieldName, $questions, (int)$sid, (int)$gid);
            }
        }
        $preinsert = "";
        $postinsert = "";
        foreach ($fieldMap as $TABLE_NAME => $fields) {
            if (in_array(Yii::app()->db->getDriverName(), ['mssql', 'sqlsrv'])) {
                $preinsert = "SET IDENTITY_INSERT {$scripts[$TABLE_NAME]['new_name']} ON;";
                $postinsert = "SET IDENTITY_INSERT {$scripts[$TABLE_NAME]['new_name']} OFF;";
            }
            $scripts[$TABLE_NAME]['handled'] = true;
            $scripts[$TABLE_NAME]['CREATE'] = str_replace("{$TABLE_NAME}", "{$scripts[$TABLE_NAME]['new_name']}", $scripts[$TABLE_NAME]['CREATE']);
            foreach ($fields as $oldField => $newField) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace($leftSeparator . "{$oldField}" . $rightSeparator, $leftSeparator . "{$newField}" . $rightSeparator, $scripts[$TABLE_NAME]['CREATE']);
            }
            $fromColumns = [];
            $toColumns = [];
            foreach ($scripts[$TABLE_NAME]['columns'] as $column) {
                if (!isset($column['COLUMN_NAME'])) {
                    if (isset($column['column_name'])) {
                        $column['COLUMN_NAME'] = $column['column_name'];
                    }
                }
                $fromColumns [] = $leftSeparator . $column['COLUMN_NAME'] . $rightSeparator;
                if (isset($fieldMap[$TABLE_NAME][$column['COLUMN_NAME']])) {
                    $toColumns [] = $leftSeparator . $fieldMap[$TABLE_NAME][$column['COLUMN_NAME']] . $rightSeparator;
                } else {
                    $toColumns [] = $leftSeparator . $column['COLUMN_NAME'] . $rightSeparator;
                }
            }
            $from = implode(",", $fromColumns);
            $to = implode(",", $toColumns);
            $scripts[$TABLE_NAME]['INSERT'] = "
                INSERT INTO {$scripts[$TABLE_NAME]['new_name']}({$to})
                SELECT {$from}
                FROM {$TABLE_NAME};
            ";
            $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
            $this->db->createCommand($preinsert . $scripts[$TABLE_NAME]['INSERT'] . $postinsert)->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
            if (count($fieldMap[$TABLE_NAME]) && (strpos($TABLE_NAME, "survey") !== false) && (strpos($TABLE_NAME, "timing") === false)) {
                $keys = array_keys($fieldMap[$TABLE_NAME]);
                arsort($keys);
                $names = [];
                $parts = explode("_", $TABLE_NAME);
                $index = count($parts) - ((strpos($TABLE_NAME, "old") === false) ? 1 : 2);
                $sid = $parts[$index];
                foreach ($keys as $oldName) {
                    $names[$oldName] = $fieldMap[$TABLE_NAME][$oldName];
                }
                $rawAdditionalNames = [];
                $questions = Question::model()->with('answers')->findAll("sid = :sid", [
                    ":sid" => $sid
                ]);
                $qids = [0];
                $gids = [0];
                foreach ($questions as $question) {
                    $rawAdditionalNames["{$question->sid}X{$question->gid}X{$question->qid}"] = "Q{$question->qid}";
                    $qids[] = $question->qid;
                    if (!in_array($question->gid, $gids)) {
                        $gids[] = (int)$question->gid;
                    }
                }
                $additionalNameKeys = array_keys($rawAdditionalNames);
                arsort($additionalNameKeys);
                $additionalNames = [];
                foreach ($additionalNameKeys as $additionalNameKey) {
                    $additionalNames[$additionalNameKey] = $rawAdditionalNames[$additionalNameKey];
                }
                $conditions = Condition::model()->findAll("qid in (" . implode(",", $qids) . ")");
                $fields = ["cfieldname", "value"];
                foreach ($conditions as $condition) {
                    if ($this->fixText($condition, $fields, $names) || $this->fixText($condition, $fields, $additionalNames)) {
                        $condition->save();
                    }
                }
                $localizedQuestions = QuestionL10n::model()->findAll("qid in (" . implode(",", $qids) . ")");
                $fields = ["question", "script"];
                foreach ($localizedQuestions as $localizedQuestion) {
                    if ($this->fixText($localizedQuestion, $fields, $names) || $this->fixText($localizedQuestion, $fields, $additionalNames)) {
                        $localizedQuestion->save();
                    }
                }
                $fields = ["title", "relevance"];
                foreach ($questions as $question) {
                    if ($this->fixText($question, $fields, $names) || $this->fixText($question, $fields, $additionalNames)) {
                        $question->save();
                    }
                }
                $surveyLanguageSettings = SurveyLanguageSetting::model()->findAll("surveyls_survey_id=" . $sid);
                $fields = ['surveyls_urldescription', 'surveyls_url'];
                foreach ($surveyLanguageSettings as $surveyLanguageSetting) {
                    if ($this->fixText($surveyLanguageSetting, $fields, $names) || $this->fixText($surveyLanguageSetting, $fields, $additionalNames)) {
                        $surveyLanguageSetting->save();
                    }
                }
                $fields = ['quotals_url', 'quotals_urldescrip'];
                $quotaLanguageSettings = QuotaLanguageSetting::model()->with('quota', array('condition' => 'sid=' . $sid))->together()->findAll();
                foreach ($quotaLanguageSettings as $quotaLanguageSetting) {
                    if ($this->fixText($quotaLanguageSetting, $fields, $names) || $this->fixText($quotaLanguageSetting, $fields, $additionalNames)) {
                        $quotaLanguageSetting->save();
                    }
                }
                $fields = ['description', 'group_name'];
                $model = new QuestionGroupL10n();
                $groups = $model->resetScope()->findAll("gid in (" . implode(",", $gids) . ")");
                foreach ($groups as $group) {
                    if ($this->fixText($group, $fields, $names) || $this->fixText($group, $fields, $additionalNames)) {
                        $group->save();
                    }
                }
            }
        }

        $passiveSurveys = Survey::model()->findAll("active <> 'Y'");
        foreach ($passiveSurveys as $passiveSurvey) {
            $qids = [0];
            $gids = [0];
            $questions = Question::model()->with('answers')->findAll("sid = :sid", [
                ":sid" => $passiveSurvey->sid
            ]);
            $rawAdditionalNames = [];
            foreach ($questions as $question) {
                $rawAdditionalNames["{$question->sid}X{$question->gid}X{$question->qid}"] = "Q{$question->qid}";
                $qids[] = $question->qid;
                if (!in_array($question->gid, $gids)) {
                    $gids[] = (int)$question->gid;
                }
            }
            $additionalNameKeys = array_keys($rawAdditionalNames);
            arsort($additionalNameKeys);
            $additionalNames = [];
            foreach ($additionalNameKeys as $additionalNameKey) {
                $additionalNames[$additionalNameKey] = $rawAdditionalNames[$additionalNameKey];
            }
            $oldFields = array_keys($this->createOldFieldMap($passiveSurvey));
            $newFields = [];
            foreach ($oldFields as $oldField) {
                if (strpos($oldField, "X") !== false) {
                    $split = explode("X", $oldField);
                    $sid = $split[0];
                    $gid = $split[1];
                    $tempqids = [];
                    $position = 0;
                    if (count($split) > 2) {
                        while (($position < strlen($split[2])) && ctype_digit($split[2][$position])) {
                            $tempqids [] = (count($tempqids) ? ($tempqids[count($tempqids) - 1] . $split[2][$position]) : $split[2][$position]);
                            $position++;
                        }
                        $commaSeparatedQIDs = implode(",", $tempqids);
                        $questionsTemp = Question::model()->with('answers')->findAll([
                            'condition' => "sid = {$sid} and gid = {$gid} and (t.qid in ({$commaSeparatedQIDs}) or parent_qid in ({$commaSeparatedQIDs}))"
                        ]);
                        $prefix = Yii::app()->db->tablePrefix ?? "";
                        if (count($questionsTemp)) {
                            $newFields[$oldField] = getFieldName($prefix . "survey_" . $passiveSurvey->sid, $oldField, $questionsTemp, (int)$sid, (int)$gid);
                        }
                    }
                }
            }
            $conditions = Condition::model()->findAll("qid in (" . implode(",", $qids) . ")");
            $fields = ["cfieldname", "value"];
            foreach ($conditions as $condition) {
                if ($this->fixText($condition, $fields, $newFields) || $this->fixText($condition, $fields, $additionalNames)) {
                    $condition->save();
                }
            }
            $localizedQuestions = QuestionL10n::model()->findAll("qid in (" . implode(",", $qids) . ")");
            $fields = ["question", "script"];
            foreach ($localizedQuestions as $localizedQuestion) {
                if ($this->fixText($localizedQuestion, $fields, $newFields) || $this->fixText($localizedQuestion, $fields, $additionalNames)) {
                    $localizedQuestion->save();
                }
            }
            $fields = ["title", "relevance"];
            foreach ($questions as $question) {
                if ($this->fixText($question, $fields, $newFields) || $this->fixText($question, $fields, $additionalNames)) {
                    $question->save();
                }
            }
            $surveyLanguageSettings = SurveyLanguageSetting::model()->findAll("surveyls_survey_id=" . $sid);
            $fields = ['surveyls_urldescription', 'surveyls_url'];
            foreach ($surveyLanguageSettings as $surveyLanguageSetting) {
                if ($this->fixText($surveyLanguageSetting, $fields, $newFields) || $this->fixText($surveyLanguageSetting, $fields, $additionalNames)) {
                    $surveyLanguageSetting->save();
                }
            }
            $fields = ['quotals_url', 'quotals_urldescrip'];
            $quotaLanguageSettings = QuotaLanguageSetting::model()->with('quota', array('condition' => 'sid=' . $sid))->together()->findAll();
            foreach ($quotaLanguageSettings as $quotaLanguageSetting) {
                if ($this->fixText($quotaLanguageSetting, $fields, $newFields) || $this->fixText($quotaLanguageSetting, $fields, $additionalNames)) {
                    $quotaLanguageSetting->save();
                }
            }
            $fields = ['description', 'group_name'];
            $model = new QuestionGroupL10n();
            $groups = $model->resetScope()->findAll("gid in (" . implode(",", $gids) . ")");
            foreach ($groups as $group) {
                if ($this->fixText($group, $fields, $newFields) || $this->fixText($group, $fields, $additionalNames)) {
                    $group->save();
                }
            }
        }

        foreach ($scripts as $TABLE_NAME => $content) {
            if (!$content['handled']) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace("{$TABLE_NAME}", "{$scripts[$TABLE_NAME]['new_name']}", $scripts[$TABLE_NAME]['CREATE']);
                $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
                $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
            }
        }

        $archivedSettings = ArchivedTableSettings::model()->findAll();
        foreach ($archivedSettings as $archivedSetting) {
            if (strpos($archivedSetting->tbl_name, 'survey') !== false) {
                if (strpos($archivedSetting->tbl_name, 'timings') !== false) {
                    $archivedSetting->tbl_name = str_replace('survey', 'timings', str_replace('_timings', '', $archivedSetting->tbl_name));
                } else {
                    $archivedSetting->tbl_name = str_replace('survey', 'responses', $archivedSetting->tbl_name);
                }
                $archivedSetting->save();
            }
        }
    }
}
