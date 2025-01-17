<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_628 extends DatabaseUpdateBase
{
    public function up()
    {
        $scripts = [];
        $responsesTables = $this->db->createCommand("
            SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
            FROM information_schema.tables
            WHERE TABLE_SCHEMA = DATABASE() AND
                  TABLE_NAME REGEXP '^.*survey_[0-9]*(_[0-9]*)?$'
        ")->queryAll();
        foreach ($responsesTables as $responsesTable) {
            $scripts[$responsesTable['old_name']] = [
                'new_name' => $responsesTable['new_name'],
                'old_name' => $responsesTable['old_name']
            ];
            /*$scripts [] = "
                CREATE TABLE {$responsesTable['new_name']} LIKE {$responsesTable['old_name']};
                INSERT INTO {$responsesTable['new_name']} SELECT * FROM {$responsesTable['old_name']};
                DROP TABLE {$responsesTable['old_name']};
            ";*/
            $createTable = $this->db->createCommand("SHOW CREATE TABLE {$responsesTable['old_name']}")->queryRow();
            $scripts[$responsesTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$responsesTable['old_name']]['DROP'] = "DROP TABLE {$responsesTable['old_name']}";
            $scripts[$responsesTable['old_name']]['columns'] = $this->db->createCommand("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '{$responsesTable['old_name']}'")->queryAll();
        }
        $timingsTables = $this->db->createCommand("
            SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
            FROM information_schema.tables
            WHERE TABLE_SCHEMA = DATABASE() AND
                  TABLE_NAME LIKE '%timings%';
        ")->queryAll();
        foreach ($timingsTables as $timingsTable) {
            $scripts[$timingsTable['old_name']] = [
                'new_name' => $timingsTable['new_name'],
                'old_name' => $timingsTable['old_name']
            ];
            /*$scripts [] = "
                CREATE TABLE {$timingsTable['new_name']} LIKE {$timingsTable['old_name']};
                INSERT INTO {$timingsTable['new_name']} SELECT * FROM {$timingsTable['old_name']};
                DROP TABLE {$timingsTable['old_name']};
            ";*/
            $createTable = $this->db->createCommand("SHOW CREATE TABLE {$timingsTable['old_name']}")->queryRow();
            $scripts[$timingsTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$timingsTable['old_name']]['DROP'] = "DROP TABLE {$timingsTable['old_name']}";
            $scripts[$timingsTable['old_name']]['columns'] = $this->db->createCommand("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '{$timingsTable['old_name']}'")->queryAll();
        }
        $fields = $this->db->createCommand("
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
        ")->queryAll();
        $fieldMap = [];
        foreach ($fields as $field) {
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
                $questions = \Question::model()->findAll([
                'condition' => "sid = {$sid} and gid = {$gid} and (qid in ({$commaSeparatedQIDs}) or parent_qid in ({$commaSeparatedQIDs}))"
                ]);
            }
            if (count($questions) || ((strpos($tableName, "timings") !== false) && ($split > 1))) {
                if (strpos($tableName, "timings") !== false) {
                    $X = explode("X", $fieldName);
                    $newFieldName = ((count($X) > 2) ? "Q" : "G") . $X[count($X) - 1];
                    $fieldMap[$tableName][$fieldName] = $newFieldName;
                } else {
                    $qid = ($questions[0]->parent_qid ? $questions[0]->parent_qid :  $questions[0]->qid);
                    switch ($questions[0]->type) {
                        case \Question::QT_1_ARRAY_DUAL:
                        case \Question::QT_5_POINT_CHOICE:
                        case \Question::QT_L_LIST:
                        case \Question::QT_M_MULTIPLE_CHOICE:
                        case \Question::QT_N_NUMERICAL:
                        case \Question::QT_O_LIST_WITH_COMMENT:
                            $currentQuestion = null;
                            $length = strlen("{$sid}X{$gid}X{$qid}");
                            $hashPos = strpos($fieldName, '#');
                            foreach ($questions as $question) {
                                if ($question->title === substr($fieldName, $length, ($hashPos !== false) ? ($hashPos - $length) : null)) {
                                    $currentQuestion = $question;
                                }
                            }
                            $hashTags = explode("#", $fieldName);
                            if ($currentQuestion === null) {
                                $newFieldName = "Q{$qid}";
                                if (strlen($fieldName) > strlen("{$sid}X{$gid}X{$qid}")) {
                                    $newFieldName .= "_C" . substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                                }
                            } else {
                                $newFieldName = "Q{$qid}_S{$currentQuestion->qid}";
                                if (count($hashTags)) {
                                    for ($index = 1; $index < count($hashTags); $index++) {
                                        $newFieldName .= "#{$hashTags[$index]}";
                                    }
                                }
                            }
                            $fieldMap[$tableName][$fieldName] = $newFieldName;
                            break;
                        case \Question::QT_A_ARRAY_5_POINT:
                        case \Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                        case \Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                        case \Question::QT_E_ARRAY_INC_SAME_DEC:
                        case \Question::QT_F_ARRAY:
                        case \Question::QT_H_ARRAY_COLUMN:
                        case \Question::QT_K_MULTIPLE_NUMERICAL:
                        case \Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                        case \Question::QT_Q_MULTIPLE_SHORT_TEXT:
                            $code = substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                            $commentText = false;
                            $currentQuestion = null;
                            $excludeSubquestion = false;
                            foreach ($questions as $question) {
                                if ($question->title === $code) {
                                    $currentQuestion = $question;
                                } elseif (in_array($code, ["other", "comment", "othercomment", $question->title . "other", $question->title . "comment", $question->title . "othercomment"])) {
                                    $currentQuestion = $question;
                                    $commentText = $code;
                                    if (strpos($code, $question->title) === 0) {
                                        $commentText = substr($code, strlen($question->title));
                                    } else {
                                        $excludeSubquestion = true;
                                    }
                                }
                            }
                            if ($currentQuestion) {
                                $newFieldName = "Q{$qid}" . ($excludeSubquestion ? "" : "_S{$currentQuestion->qid}");
                                if ($commentText) {
                                    $newFieldName = $newFieldName . "_C" . $commentText;
                                }
                                $fieldMap[$tableName][$fieldName] = $newFieldName;
                            }
                            break;
                        case \Question::QT_SEMICOLON_ARRAY_TEXT:
                        case \Question::QT_COLON_ARRAY_NUMBERS:
                            if (strpos($tableName, "timings") !== false) {
                                $newFieldName = "Q{$qid}_Ctime";
                            } else {
                                $suffix = explode("_", substr($fieldName, strlen("{$sid}X{$gid}X{$qid}")));
                                $scales = [];
                                foreach ($questions as $question) {
                                    if (($suffix[$question->scale_id] ?? null) === $question->title) {
                                        $scales[$question->scale_id] = $question->qid;
                                    }
                                }
                                $suffixText = "";
                                for ($index = 0; $index < count($scales); $index++) {
                                    $suffixText .= "_S" . $scales[$index];
                                }
                                $newFieldName = "Q{$qid}" . $suffixText;
                            }
                            $fieldMap[$tableName][$fieldName] = $newFieldName;
                            break;
                        case \Question::QT_D_DATE:
                        case \Question::QT_G_GENDER:
                        case \Question::QT_I_LANGUAGE:
                        case \Question::QT_S_SHORT_FREE_TEXT:
                        case \Question::QT_T_LONG_FREE_TEXT:
                        case \Question::QT_U_HUGE_FREE_TEXT:
                        case \Question::QT_X_TEXT_DISPLAY:
                        case \Question::QT_Y_YES_NO_RADIO:
                        case \Question::QT_EXCLAMATION_LIST_DROPDOWN:
                        case \Question::QT_VERTICAL_FILE_UPLOAD:
                        case \Question::QT_ASTERISK_EQUATION:
                            $isRoot = ((strpos($tableName, "timings") !== false) || (($questions[0]->parent_qid ?? 0) === 0));
                            $newFieldName = ($isRoot ? "Q{$qid}" : "Q{$questions[0]->parent_qid}");
                            $suffix = "";
                            $isComment = false;
                            if (!$isRoot) {
                                $length = strlen("{$sid}X{$gid}X{$qid}");
                                $hashPos = strpos($fieldName, '#');
                                $code = substr($fieldName, $length, ($hashPos !== false) ? ($hashPos - $length) : null);
                                $suffix = "_C{$code}";
                                foreach ($questions as $question) {
                                    if ($question->title === $code) {
                                        $suffix = "_S{$question->qid}";
                                    } elseif ($question->title . "comment" === $code) {
                                        $suffix = "_S{$question->qid}";
                                        $isComment = true;
                                    }
                                }
                            }
                            $newFieldName .= $suffix;
                            if (strpos($fieldName, "time") !== false) {
                                $newFieldName .= "_Ctime";
                            } elseif (strpos($fieldName, "filecount") !== false) {
                                $newFieldName .= "_Cfilecount";
                            }
                            if ($isComment) {
                                $newFieldName .= "_Ccomment";
                            }
                            $fieldMap[$tableName][$fieldName] = $newFieldName;
                            break;
                        case \Question::QT_R_RANKING:
                            $prefix = ((strpos($tableName, "timing") !== false) ? "C" : "R");
                            $newFieldName = "Q{$qid}_{$prefix}" . substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                            $fieldMap[$tableName][$fieldName] = $newFieldName;
                            break;
                    }
                }
            }
        }
        foreach ($fieldMap as $TABLE_NAME => $fields) {
            $scripts[$TABLE_NAME]['CREATE'] = str_replace("`{$TABLE_NAME}`", "`{$scripts[$TABLE_NAME]['new_name']}`", $scripts[$TABLE_NAME]['CREATE']);
            foreach ($fields as $oldField => $newField) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace("`{$oldField}`", "`{$newField}`", $scripts[$TABLE_NAME]['CREATE']);
            }
            $fromColumns = [];
            $toColumns = [];
            foreach ($scripts[$TABLE_NAME]['columns'] as $column) {
                $fromColumns [] = "`" . $column['COLUMN_NAME'] . "`";
                if (isset($fieldMap[$TABLE_NAME][$column['COLUMN_NAME']])) {
                    $toColumns [] = "`" . $fieldMap[$TABLE_NAME][$column['COLUMN_NAME']] . "`";
                } else {
                    $toColumns [] = "`" . $column['COLUMN_NAME'] . "`";
                }
            }
            $from = implode(",", $fromColumns);
            $to = implode(",", $toColumns);
            $scripts[$TABLE_NAME]['INSERT'] = "
                INSERT INTO `{$scripts[$TABLE_NAME]['new_name']}`({$to})
                SELECT {$from}
                FROM `{$TABLE_NAME}`
            ";
            $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['INSERT'])->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
        }
    }
}
