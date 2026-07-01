<?php

namespace LimeSurvey\Helpers\Update;

use Yii;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Update_700 extends DatabaseUpdateBase
{
    protected $scriptMapping;
    protected $questionCodes = [];
    /** @var array Survey IDs that have already been processed for INSERTANS conversion */
    protected $insertansProcessedSids = [];

    /**
     * equivalent of dbQuoteFields())
     * Quote one or more (comma-separated) field names.
     * @param array|string $fields
     * @return string
     */
    protected function dbQuoteFields($fields): string
    {
        if (is_string($fields)) {
            if (strpos($fields, ',') !== false) {
                $fields = explode(',', $fields);
            } else {
                $fields = [$fields];
            }
        }

        if (is_array($fields) && count($fields) > 0) {
            $driver = App()->db->getDriverName();
            switch ($driver) {
                case 'mysql':
                case 'mysqli':
                    $fields = array_map(function ($f) {
                        return '`' . trim($f) . '`';
                    }, $fields);
                    break;
                case 'dblib':
                case 'mssql':
                case 'sqlsrv':
                    $fields = array_map(function ($f) {
                        return '[' . trim($f) . ']';
                    }, $fields);
                    break;
                case 'pgsql':
                    $fields = array_map(function ($f) {
                        return '"' . trim($f) . '"';
                    }, $fields);
                    break;
                default:
                    $fields = array_map('trim', $fields);
                    break;
            }
        }
        return implode(', ', $fields);
    }

    /**
     * equivalent of getSubQuestions
     * Returns all subquestions for a survey+question in the given language.
     *
     * @param int $sid
     * @param int $qid
     * @param string $sLanguage
     * @return array
     */
    protected function getSubQuestionsData(int $sid, int $qid, string $sLanguage): array
    {
        static $subquestions;

        if (!isset($subquestions[$sid])) {
            $subquestions[$sid] = [];
        }
        if (!isset($subquestions[$sid][$sLanguage])) {
            $query = "SELECT sq.*, ls.question, q.other FROM {{questions}} as sq
        JOIN {{questions}} as q on sq.parent_qid=q.qid
        JOIN {{question_l10ns}} as ls on ls.qid=sq.qid"
                . " WHERE sq.parent_qid=q.qid AND ls.language='{$sLanguage}' AND q.sid=" . $sid
                . " ORDER BY sq.parent_qid, q.question_order,sq.scale_id, sq.question_order";

            $query = Yii::app()->db->createCommand($query)->query();

            $resultset = [];
            foreach ($query->readAll() as $row) {
                $resultset[$row['parent_qid']][] = $row;
            }
            $subquestions[$sid][$sLanguage] = $resultset;
        }
        if (isset($subquestions[$sid][$sLanguage][$qid])) {
            return $subquestions[$sid][$sLanguage][$qid];
        }
        return [];
    }

    /**
     * equivalent of findQuestionMetaDataForAllTypes())
     * Returns an array indexed by question_type, each element having a 'settings'
     * stdClass with at least 'subquestions' and 'answerscales' properties.
     * Equivalent of QuestionTheme::findQuestionMetaDataForAllTypes().
     *
     * @return array
     */
    protected function getQuestionTypeMetaData(): array
    {
        // Getting all question_types which are NOT extended
        $baseQuestions = Yii::app()->db->createCommand()
            ->select('question_type, settings')
            ->from('{{question_themes}}')
            ->where("extends = ''")
            ->queryAll();
        $aQuestionsIndexedByType = [];

        foreach ($baseQuestions as $baseQuestion) {
            $baseQuestionObject = new \stdClass();
            $baseQuestionObject->question_type = $baseQuestion['question_type'];
            $baseQuestionObject->settings = json_decode($baseQuestion['settings']);
            $aQuestionsIndexedByType[$baseQuestion['question_type']] = $baseQuestionObject;
        }
        return $aQuestionsIndexedByType;
    }

    /**
     * equivalent of getFieldName())
     * Computes the new (v7) field name for a given old SGQA field name.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param array $rawQuestions Array of question rows (each row is an array)
     * @param int $sid
     * @param int $gid
     * @param bool $cd
     * @return string the field's name
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getFieldName(string $tableName, string $fieldName, array $rawQuestions, int $sid, int $gid, bool $cd = false): string
    {
        $db = Yii::app()->db;
        $newFieldName = "";
        if (strpos($tableName, "timings") !== false) {
            $X = explode("X", $fieldName);
            $newFieldName = ((count($X) > 2) ? "Q" : "G") . $X[count($X) - 1];
        } else {
            $rootQuestions = [];
            foreach ($rawQuestions as $q) {
                if (($q['gid'] == $gid) && (!$q['parent_qid'])) {
                    $rootQuestions[] = $q;
                }
            }
            usort($rootQuestions, function ($a, $b) {
                return $b['qid'] - $a['qid'];
            });
            foreach ($rootQuestions as $rootQuestion) {
                $questions = [$rootQuestion];
                foreach ($rawQuestions as $rawQuestion) {
                    if ($rawQuestion['parent_qid'] == $rootQuestion['qid']) {
                        $questions[] = $rawQuestion;
                    }
                }
                $qid = $rootQuestion['qid'];
                $type = $rootQuestion['type'];
                switch ($type) {
                    case '1': // QT_1_ARRAY_DUAL
                    case '5': // QT_5_POINT_CHOICE
                    case 'L': // QT_L_LIST
                    case 'M': // QT_M_MULTIPLE_CHOICE
                    case 'N': // QT_N_NUMERICAL
                    case 'O': // QT_O_LIST_WITH_COMMENT
                    case '!': // QT_EXCLAMATION_LIST_DROPDOWN
                        $currentQuestion = null;
                        $length = strlen("{$sid}X{$gid}X{$qid}");
                        $hashPos = strpos($fieldName, '#');
                        foreach ($questions as $question) {
                            if ($hashPos && ($question['title'] === substr($fieldName, $length, ($hashPos !== false) ? ($hashPos - $length) : null))) {
                                $currentQuestion = $question;
                            } elseif ($question['title'] === substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"))) {
                                $currentQuestion = $question;
                            }
                        }
                        $hashTags = explode('#', $fieldName);
                        if ((!$currentQuestion) && ($type === 'M') && ($length < strlen($hashTags[0]))) {
                            $row = $db->createCommand()
                                ->select('qid, title')
                                ->from('{{questions}}')
                                ->where('parent_qid = :qid AND title = :title', [
                                    ':qid'   => $qid,
                                    ':title' => substr($hashTags[0], $length)
                                ])
                                ->queryRow();
                            if ($row) {
                                $currentQuestion = $row;
                            }
                        }
                        if (!$currentQuestion) {
                            $newFieldName = "Q{$qid}";
                            if (strlen($fieldName) > strlen("{$sid}X{$gid}X{$qid}")) {
                                $newFieldName .= '_C' . substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                            }
                        } else {
                            $newFieldName = "Q{$qid}_S{$currentQuestion['qid']}";
                            for ($index = 1; $index < count($hashTags); $index++) {
                                $newFieldName .= "#{$hashTags[$index]}";
                            }
                        }
                        break;
                    case 'A': // QT_A_ARRAY_5_POINT
                    case 'B': // QT_B_ARRAY_10_CHOICE_QUESTIONS
                    case 'C': // QT_C_ARRAY_YES_UNCERTAIN_NO
                    case 'E': // QT_E_ARRAY_INC_SAME_DEC
                    case 'F': // QT_F_ARRAY
                    case 'H': // QT_H_ARRAY_COLUMN
                    case 'K': // QT_K_MULTIPLE_NUMERICAL
                    case 'P': // QT_P_MULTIPLE_CHOICE_WITH_COMMENTS
                    case 'Q': // QT_Q_MULTIPLE_SHORT_TEXT
                        $code = substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                        if ($code === '') {
                            return "Q{$qid}";
                        }
                        $commentText = false;
                        $currentQuestion = null;
                        $excludeSubquestion = false;
                        foreach ($questions as $question) {
                            if ($question['title'] === $code) {
                                $currentQuestion = $question;
                            } elseif (in_array(
                                $code,
                                [
                                    'other',
                                    'comment',
                                    'othercomment',
                                    $question['title'] . 'other',
                                    $question['title'] . 'comment',
                                    $question['title'] . 'othercomment'
                                ]
                            )) {
                                $currentQuestion = $question;
                                $commentText = $code;
                                if (strpos($code, $question['title']) === 0) {
                                    $commentText = substr($code, strlen($question['title']));
                                } else {
                                    $excludeSubquestion = true;
                                }
                            }
                        }
                        if ($currentQuestion) {
                            $newFieldName = 'Q' . $qid . ($excludeSubquestion ? '' : '_S' . $currentQuestion['qid']);
                            if ($commentText) {
                                $newFieldName .= '_C' . $commentText;
                            }
                        }
                        break;
                    case ';': // QT_SEMICOLON_ARRAY_TEXT
                    case ':': // QT_COLON_ARRAY_NUMBERS
                        $scales = [
                            0 => [],
                            1 => []
                        ];
                        foreach ($questions as $question) {
                            if ($question['parent_qid'] != 0) {
                                $scales[$question['scale_id']][$question['title']] = $question['qid'];
                            }
                        }
                        $partialFieldName = substr($fieldName, 0, strlen("{$sid}X{$gid}X{$qid}"));
                        foreach ($scales[0] as $title1 => $qid1) {
                            if (count($scales[1])) {
                                foreach ($scales[1] as $title2 => $qid2) {
                                    if ($fieldName === "{$partialFieldName}{$title1}_{$title2}") {
                                        return "Q{$qid}_S{$qid1}_S{$qid2}";
                                    }
                                }
                            } elseif ($fieldName === "{$partialFieldName}{$title1}") {
                                return "Q{$qid}_S{$qid1}";
                            }
                        }
                        break;
                    case 'D': // QT_D_DATE
                    case 'G': // QT_G_GENDER
                    case 'I': // QT_I_LANGUAGE
                    case 'S': // QT_S_SHORT_FREE_TEXT
                    case 'T': // QT_T_LONG_FREE_TEXT
                    case 'U': // QT_U_HUGE_FREE_TEXT
                    case 'X': // QT_X_TEXT_DISPLAY
                    case 'Y': // QT_Y_YES_NO_RADIO
                    case '|': // QT_VERTICAL_FILE_UPLOAD
                    case '*': // QT_ASTERISK_EQUATION
                        $isRoot = (($rootQuestion['parent_qid'] ?? 0) == '0');
                        $newFieldName = ($isRoot ? "Q{$qid}" : "Q{$rootQuestion['parent_qid']}");
                        $suffix = '';
                        $isComment = false;
                        if (!$isRoot) {
                            $length = strlen("{$sid}X{$gid}X{$qid}");
                            $hashPos = strpos($fieldName, '#');
                            $code = substr($fieldName, $length, ($hashPos !== false) ? ($hashPos - $length) : 2000);
                            $suffix = "_C{$code}";
                            foreach ($questions as $question) {
                                if ($question['title'] === $code) {
                                    $suffix = "_S{$question['qid']}";
                                } elseif ($question['title'] . 'comment' === $code) {
                                    $suffix = "_S{$question['qid']}";
                                    $isComment = true;
                                }
                            }
                        }
                        $clearIfLong = true;
                        $newFieldName .= $suffix;
                        if (strpos($fieldName, 'time') !== false) {
                            $newFieldName .= '_Ctime';
                            $clearIfLong = false;
                        } elseif (strpos($fieldName, 'filecount') !== false) {
                            $newFieldName .= '_Cfilecount';
                            $clearIfLong = false;
                        }
                        if ($isComment) {
                            $newFieldName .= '_Ccomment';
                        } elseif ($isRoot && $clearIfLong && (strlen($fieldName) > strlen("{$sid}X{$gid}X{$qid}"))) {
                            $newFieldName = '';
                        }
                        break;
                    case 'R': // QT_R_RANKING
                        $prefix = ((strpos($tableName, 'timing') !== false) ? 'C' : 'S');
                        try {
                            $rankingSuffix = substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                            $iRankingSuffix = intval($rankingSuffix);
                            $subQuestions = $db->createCommand()
                                ->select('qid, title, question_order')
                                ->from('{{questions}}')
                                ->where('parent_qid = :qid', [':qid' => $qid])
                                ->order('question_order')
                                ->queryAll();
                            if (($iRankingSuffix > 0) && isset($subQuestions[($iRankingSuffix - 1)])) {
                                $sqid = $cd ? $rankingSuffix : $subQuestions[($iRankingSuffix - 1)]['qid'];
                                $newFieldName = "Q{$rootQuestion['qid']}_{$prefix}" . $sqid;
                            } elseif (count($subQuestions)) {
                                $minSortOrder = $subQuestions[0]['question_order'];
                                $diff = 0;
                                if ($minSortOrder === 0) {
                                    $diff = -1;
                                } elseif ($minSortOrder > 1) {
                                    $diff = $minSortOrder;
                                }
                                foreach ($subQuestions as $question) {
                                    if (($rankingSuffix == $question['title']) || ((intval($iRankingSuffix) > 0) && ($rankingSuffix + $diff == $question['question_order']))) {
                                        return "Q{$rootQuestion['qid']}_{$prefix}{$question['qid']}";
                                    }
                                }
                            }
                        } catch (\Exception $ex) {
                            // Ignore inconsistencies in archive rankings
                            if (strpos($tableName, 'old') === false) {
                                throw $ex;
                            }
                        }
                        break;
                }
                if ($newFieldName) {
                    return $newFieldName;
                }
            }
        }
        return $newFieldName ? $newFieldName : $fieldName;
    }
    // -------------------------------------------------------------------------
    // createOldFieldMap – self-contained (no AR models)
    // -------------------------------------------------------------------------
    /**
     * Generates the old SGQA fieldmap for a survey.
     *
     * @param array $survey Associative array with survey columns
     * @param string $style 'short' (default) or 'full'
     * @param bool $force_refresh
     * @param bool|int $questionid
     * @param string $sLanguage
     * @param array $aDuplicateQIDs
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    protected function createOldFieldMap($survey, $style = 'short', $force_refresh = false, $questionid = false, $sLanguage = '', &$aDuplicateQIDs = [])
    {
        $sLanguage = \LSYii_Validators::languageCodeFilter($sLanguage);
        $surveyid = $survey['sid'];
        if (isset(Yii::app()->session['fieldmap-' . $surveyid . $sLanguage]) && !$force_refresh && $questionid === false) {
            return Yii::app()->session['fieldmap-' . $surveyid . $sLanguage];
        }
        // Build allLanguages from survey row data
        $additionalLangs = !empty($survey['additional_languages'])
            ? array_filter(explode(' ', trim($survey['additional_languages'])))
            : [];
        $allLanguages = array_merge([$survey['language']], array_values($additionalLangs));
        if ($sLanguage === '' || !in_array($sLanguage, $allLanguages)) {
            $sLanguage = $survey['language'];
        }
        $fieldmap = [];
        $fieldmap['id'] = [
            'fieldname' => 'id',
            'sid'       => $surveyid,
            'type'      => 'id',
            'gid'       => '',
            'qid'       => '',
            'aid'       => ''
        ];
        if ($style == 'full') {
            $fieldmap['id']['title'] = '';
            $fieldmap['id']['question'] = gT('Response ID');
            $fieldmap['id']['group_name'] = '';
        }
        $fieldmap['submitdate'] = [
            'fieldname' => 'submitdate',
            'type'      => 'submitdate',
            'sid'       => $surveyid,
            'gid'       => '',
            'qid'       => '',
            'aid'       => ''
        ];
        if ($style == 'full') {
            $fieldmap['submitdate']['title'] = '';
            $fieldmap['submitdate']['question'] = gT('Date submitted');
            $fieldmap['submitdate']['group_name'] = '';
        }
        $fieldmap['lastpage'] = [
            'fieldname' => 'lastpage',
            'sid'       => $surveyid,
            'type'      => 'lastpage',
            'gid'       => '',
            'qid'       => '',
            'aid'       => ''
        ];
        if ($style == 'full') {
            $fieldmap['lastpage']['title'] = '';
            $fieldmap['lastpage']['question'] = gT('Last page');
            $fieldmap['lastpage']['group_name'] = '';
        }
        $fieldmap['startlanguage'] = [
            'fieldname' => 'startlanguage',
            'sid'       => $surveyid,
            'type'      => 'startlanguage',
            'gid'       => '',
            'qid'       => '',
            'aid'       => ''
        ];
        if ($style == 'full') {
            $fieldmap['startlanguage']['title'] = '';
            $fieldmap['startlanguage']['question'] = gT('Start language');
            $fieldmap['startlanguage']['group_name'] = '';
        }
        $fieldmap['seed'] = [
            'fieldname' => 'seed',
            'sid'       => $surveyid,
            'type'      => 'seed',
            'gid'       => '',
            'qid'       => '',
            'aid'       => ''
        ];
        if ($style == 'full') {
            $fieldmap['seed']['title'] = '';
            $fieldmap['seed']['question'] = gT('Seed');
            $fieldmap['seed']['group_name'] = '';
        }
        // Check for tokens table
        $tokensTableName = Yii::app()->db->tablePrefix . 'tokens_' . $surveyid;
        $hasTokensTable = in_array($tokensTableName, Yii::app()->db->schema->getTableNames());
        if ($survey['anonymized'] == 'N' && $hasTokensTable) {
            $fieldmap['token'] = [
                'fieldname' => 'token',
                'sid'       => $surveyid,
                'type'      => 'token',
                'gid'       => '',
                'qid'       => '',
                'aid'       => ''
            ];
            if ($style == 'full') {
                $fieldmap['token']['title'] = '';
                $fieldmap['token']['question'] = gT('Access code');
                $fieldmap['token']['group_name'] = '';
            }
        }
        if ($survey['datestamp'] == 'Y') {
            $fieldmap['startdate'] = [
                'fieldname' => 'startdate',
                'type'      => 'startdate',
                'sid'       => $surveyid,
                'gid'       => '',
                'qid'       => '',
                'aid'       => ''
            ];
            if ($style == 'full') {
                $fieldmap['startdate']['title'] = '';
                $fieldmap['startdate']['question'] = gT('Date started');
                $fieldmap['startdate']['group_name'] = '';
            }
            $fieldmap['datestamp'] = [
                'fieldname' => 'datestamp',
                'type'      => 'datestamp',
                'sid'       => $surveyid,
                'gid'       => '',
                'qid'       => '',
                'aid'       => ''
            ];
            if ($style == 'full') {
                $fieldmap['datestamp']['title'] = '';
                $fieldmap['datestamp']['question'] = gT('Date last action');
                $fieldmap['datestamp']['group_name'] = '';
            }
        }
        if ($survey['ipaddr'] == 'Y') {
            $fieldmap['ipaddr'] = [
                'fieldname' => 'ipaddr',
                'type'      => 'ipaddress',
                'sid'       => $surveyid,
                'gid'       => '',
                'qid'       => '',
                'aid'       => ''
            ];
            if ($style == 'full') {
                $fieldmap['ipaddr']['title'] = '';
                $fieldmap['ipaddr']['question'] = gT('IP address');
                $fieldmap['ipaddr']['group_name'] = '';
            }
        }
        if ($survey['refurl'] == 'Y') {
            $fieldmap['refurl'] = [
                'fieldname' => 'refurl',
                'type'      => 'url',
                'sid'       => $surveyid,
                'gid'       => '',
                'qid'       => '',
                'aid'       => ''
            ];
            if ($style == 'full') {
                $fieldmap['refurl']['title'] = '';
                $fieldmap['refurl']['question'] = gT('Referrer URL');
                $fieldmap['refurl']['group_name'] = '';
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
        $defaultValues = [];
        foreach (Yii::app()->db->createCommand($defaultsQuery)->queryAll() as $dv) {
            $sq = ($dv['specialtype'] != '') ? $dv['specialtype'] : $dv['sqid'];
            $defaultValues[$dv['qid'] . '~' . $sq] = $dv['defaultvalue'];
        }

        // Now overwrite language-specific defaults (if any) base language values for each question that uses same_defaults=1
        $baseLanguage = $survey['language'];
        $defaultsQuery2 = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, al10.defaultvalue"
            . " FROM {{defaultvalues}} as a "
            . " JOIN {{defaultvalue_l10ns}} as al10 ON a.dvid = al10.dvid"
            . " JOIN {{questions}} as b ON a.qid = b.qid"
            . " AND al10.language = '{$baseLanguage}'"
            . " AND b.same_default=1"
            . " AND b.sid = " . $surveyid;
        foreach (Yii::app()->db->createCommand($defaultsQuery2)->queryAll() as $dv) {
            $sq = ($dv['specialtype'] != '') ? $dv['specialtype'] : $dv['sqid'];
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
        $aquery .= ' ORDER BY group_order, question_order';
        $questions = Yii::app()->db->createCommand($aquery)->queryAll();
        $questionTypeMetaData = $this->getQuestionTypeMetaData();
        $questionSeq = -1;
        $groupSeq = -1;
        $_groupOrder = -1;
        foreach ($questions as $arow) {
            ++$questionSeq;
            if ($_groupOrder != $arow['group_order']) {
                $_groupOrder = $arow['group_order'];
                ++$groupSeq;
            }
            $conditions = 'N';
            $usedinconditions = 'N';

            // Field identifier
            // GXQXSXA
            // G=Group  Q=Question S=Subquestion A=Answer Option
            // If S or A don't exist then set it to 0
            // Implicit (subqestion intermal to a question type ) or explicit qubquestions/answer count starts at 1

            // Types "L", "!", "O", "D", "G", "N", "X", "Y", "5", "S", "T", "U"
            $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";

            if ($questionTypeMetaData[$arow['type']]->settings->subquestions == 0 && $arow['type'] != 'R' && $arow['type'] != '|') {
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = [
                        'fieldname' => $fieldname,
                        'question'  => $arow['question'],
                        'gid'       => $arow['gid']
                    ];
                }
                $fieldmap[$fieldname] = [
                    'fieldname' => $fieldname,
                    'type'      => "{$arow['type']}",
                    'sid'       => $surveyid,
                    'gid'       => $arow['gid'],
                    'qid'       => $arow['qid'],
                    'aid'       => ''
                ];
                if ($style == 'full') {
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
                    case 'L': // QT_L_LIST
                    case '!': // QT_EXCLAMATION_LIST_DROPDOWN
                        if ($arow['other'] == 'Y') {
                            $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                            if (isset($fieldmap[$fieldname])) {
                                $aDuplicateQIDs[$arow['qid']] = [
                                    'fieldname' => $fieldname,
                                    'question'  => $arow['question'],
                                    'gid'       => $arow['gid']
                                ];
                            }
                            $fieldmap[$fieldname] = [
                                'fieldname' => $fieldname,
                                'type'      => $arow['type'],
                                'sid'       => $surveyid,
                                'gid'       => $arow['gid'],
                                'qid'       => $arow['qid'],
                                'aid'       => 'other'
                            ];
                            if ($style == 'full') {
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
                                if (isset($defaultValues[$arow['qid'] . '~other'])) {
                                    $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~other'];
                                }
                            }
                        }
                        break;
                    case 'O': // QT_O_LIST_WITH_COMMENT
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = [
                                'fieldname' => $fieldname,
                                'question'  => $arow['question'],
                                'gid'       => $arow['gid']
                            ];
                        }
                        $fieldmap[$fieldname] = [
                            'fieldname' => $fieldname,
                            'type'      => $arow['type'],
                            'sid'       => $surveyid,
                            'gid'       => $arow['gid'],
                            'qid'       => $arow['qid'],
                            'aid'       => 'comment'
                        ];
                        if ($style == 'full') {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion'] = gT('Comment');
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
            } elseif ($questionTypeMetaData[$arow['type']]->settings->subquestions == 2 && $questionTypeMetaData[$arow['type']]->settings->answerscales == 0) {
                // Multi-flexi question types
                $abrows = $this->getSubQuestionsData((int)$surveyid, (int)$arow['qid'], $sLanguage);
                $answerset = [];
                $answerList = [];
                foreach ($abrows as $key => $abrow) {
                    if ($abrow['scale_id'] == 1) {
                        $answerset[] = $abrow;
                        $answerList[] = [
                            'code'   => $abrow['title'],
                            'answer' => $abrow['question']
                        ];
                        unset($abrows[$key]);
                    }
                }
                reset($abrows);
                foreach ($abrows as $abrow) {
                    foreach ($answerset as $answer) {
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}_{$answer['title']}";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = [
                                'fieldname' => $fieldname,
                                'question'  => $arow['question'],
                                'gid'       => $arow['gid']
                            ];
                        }
                        $fieldmap[$fieldname] = [
                            'fieldname' => $fieldname,
                            'type'      => $arow['type'],
                            'sid'       => $surveyid,
                            'gid'       => $arow['gid'],
                            'qid'       => $arow['qid'],
                            'aid'       => $abrow['title'] . '_' . $answer['title'],
                            'sqid'      => $abrow['qid']
                        ];
                        if ($style == 'full') {
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
            } elseif ($arow['type'] == '1') { // QT_1_ARRAY_DUAL
                $abrows = $this->getSubQuestionsData((int)$surveyid, (int)$arow['qid'], $sLanguage);
                foreach ($abrows as $abrow) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#0";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = [
                            'fieldname' => $fieldname,
                            'question'  => $arow['question'],
                            'gid'       => $arow['gid']
                        ];
                    }
                    $fieldmap[$fieldname] = [
                        'fieldname' => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $surveyid,
                        'gid'       => $arow['gid'],
                        'qid'       => $arow['qid'],
                        'aid'       => $abrow['title'],
                        'scale_id'  => 0
                    ];
                    if ($style == 'full') {
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
                        $aDuplicateQIDs[$arow['qid']] = [
                            'fieldname' => $fieldname,
                            'question'  => $arow['question'],
                            'gid'       => $arow['gid']
                        ];
                    }
                    $fieldmap[$fieldname] = [
                        'fieldname' => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $surveyid,
                        'gid'       => $arow['gid'],
                        'qid'       => $arow['qid'],
                        'aid'       => $abrow['title'],
                        'scale_id'  => 1
                    ];
                    if ($style == 'full') {
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
                    }
                }
            } elseif ($arow['type'] == 'R') { // QT_R_RANKING
                $answersCount = intval(
                    Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('{{answers}}')
                        ->where('qid = :qid', [':qid' => $arow['qid']])
                        ->queryScalar()
                );
                $maxSubqValue = Yii::app()->db->createCommand()
                    ->select('value')
                    ->from('{{question_attributes}}')
                    ->where("qid = :qid AND attribute = 'max_subquestions'", [':qid' => $arow['qid']])
                    ->queryScalar();
                $columnsCount = (!$maxSubqValue || intval($maxSubqValue) < 1) ? $answersCount : intval($maxSubqValue);
                $columnsCount = min($columnsCount, $answersCount);
                for ($i = 1; $i <= $columnsCount; $i++) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = [
                            'fieldname' => $fieldname,
                            'question'  => $arow['question'],
                            'gid'       => $arow['gid']
                        ];
                    }
                    $fieldmap[$fieldname] = [
                        'fieldname' => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $surveyid,
                        'gid'       => $arow['gid'],
                        'qid'       => $arow['qid'],
                        'aid'       => $i
                    ];
                    if ($style == 'full') {
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
            } elseif ($arow['type'] == '|') { // QT_VERTICAL_FILE_UPLOAD
                $maxNumFiles = Yii::app()->db->createCommand()
                    ->select('value')
                    ->from('{{question_attributes}}')
                    ->where("qid = :qid AND attribute = 'max_num_of_files' AND (language = '' OR language IS NULL)", [':qid' => $arow['qid']])
                    ->queryScalar();
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
                $fieldmap[$fieldname] = [
                    'fieldname' => $fieldname,
                    'type'      => $arow['type'],
                    'sid'       => $surveyid,
                    'gid'       => $arow['gid'],
                    'qid'       => $arow['qid'],
                    'aid'       => ''
                ];
                if ($style == 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['max_files'] = $maxNumFiles;
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}_filecount";
                $fieldmap[$fieldname] = [
                    'fieldname' => $fieldname,
                    'type'      => $arow['type'],
                    'sid'       => $surveyid,
                    'gid'       => $arow['gid'],
                    'qid'       => $arow['qid'],
                    'aid'       => 'filecount'
                ];
                if ($style == 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = 'filecount - ' . $arow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
            } else {
                // Question types with subquestions (M/A/B/C/E/F/H/P/Q/etc.)
                $abrows = $this->getSubQuestionsData((int)$surveyid, (int)$arow['qid'], $sLanguage);
                foreach ($abrows as $abrow) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = [
                            'fieldname' => $fieldname,
                            'question'  => $arow['question'],
                            'gid'       => $arow['gid']
                        ];
                    }
                    $fieldmap[$fieldname] = [
                        'fieldname' => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $surveyid,
                        'gid'       => $arow['gid'],
                        'qid'       => $arow['qid'],
                        'aid'       => $abrow['title'],
                        'sqid'      => $abrow['qid']
                    ];
                    if ($style == 'full') {
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
                        $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                        if (isset($defaultValues[$arow['qid'] . '~' . $abrow['qid']])) {
                            $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~' . $abrow['qid']];
                        }
                    }
                    if ($arow['type'] == 'P') { // QT_P_MULTIPLE_CHOICE_WITH_COMMENTS
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}comment";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = [
                                'fieldname' => $fieldname,
                                'question'  => $arow['question'],
                                'gid'       => $arow['gid']
                            ];
                        }
                        $fieldmap[$fieldname] = [
                            'fieldname' => $fieldname,
                            'type'      => $arow['type'],
                            'sid'       => $surveyid,
                            'gid'       => $arow['gid'],
                            'qid'       => $arow['qid'],
                            'aid'       => $abrow['title'] . 'comment'
                        ];
                        if ($style == 'full') {
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
                if ($arow['other'] == 'Y' && ($arow['type'] == 'M' || $arow['type'] == 'P')) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = [
                            'fieldname' => $fieldname,
                            'question'  => $arow['question'],
                            'gid'       => $arow['gid']
                        ];
                    }
                    $fieldmap[$fieldname] = [
                        'fieldname' => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $surveyid,
                        'gid'       => $arow['gid'],
                        'qid'       => $arow['qid'],
                        'aid'       => 'other'
                    ];
                    if ($style == 'full') {
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
                    if ($arow['type'] == 'P') { // QT_P_MULTIPLE_CHOICE_WITH_COMMENTS
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = [
                                'fieldname' => $fieldname,
                                'question'  => $arow['question'],
                                'gid'       => $arow['gid']
                            ];
                        }
                        $fieldmap[$fieldname] = [
                            'fieldname' => $fieldname,
                            'type'      => $arow['type'],
                            'sid'       => $surveyid,
                            'gid'       => $arow['gid'],
                            'qid'       => $arow['qid'],
                            'aid'       => 'othercomment'
                        ];
                        if ($style == 'full') {
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
                        $translatable = array_flip([
                            'question',
                            'subquestion',
                            'subquestion1',
                            'subquestion2',
                            'group_name',
                            'answerList',
                            'defaultValue',
                            'help'
                        ]);
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

    /**
     * execute Db scripts for specific database types
     * @return void
     */
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
                ";
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
                      TABLE_NAME ~ '^.*survey_[0-9]*(_[0-9]*)?$';
                ";
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
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
            case 'dblib':
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
                          (COLUMN_NAME ~ '^[0-9]*X[0-9]*X[0-9]*(.*)$') AND
                          (TABLE_NAME LIKE '%survey%')
                      ) OR
                      (
                          (COLUMN_NAME ~ '^[0-9]*X[0-9]*(X[0-9]*)?(.*)$') AND
                          (TABLE_NAME LIKE '%survey%')
                      )
                )
                ORDER BY TABLE_NAME, COLUMN_NAME;
                ";
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
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
            case 'dblib':
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
        if (strpos($tableName, 'old') === false) {
            switch (Yii::app()->db->getDriverName()) {
                case 'pgsql':
                    $script['Create Table'] = str_replace('"id" integer NOT NULL', '"id" serial PRIMARY KEY', $script['Create Table']);
                    break;
            }
        }
        switch (Yii::app()->db->getDriverName()) {
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
                $script['Create Table'] = str_replace('[id] int NOT NULL PRIMARY KEY', '[id] int IDENTITY(1, 1) PRIMARY KEY', $script['Create Table']);
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
            case 'dblib':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_CATALOG = db_name() AND TABLE_NAME = '{$tableName}'";
            default:
                return '';
        }
    }

    /**
     * Removes any existing subquestions of ranking questions before
     * the ranking answers are converted into subquestions, to avoid duplicates.
     * @return string
     */
    public function deleteRankingSubquestions()
    {
        return "DELETE FROM {{questions}} WHERE parent_qid IN (SELECT qid FROM (SELECT qid FROM {{questions}} WHERE type = 'R' AND parent_qid = 0) AS rankingquestions)";
    }

    /**
     * Creating subquestions for ranking instead of its answers
     * @return string
     */
    public function insertRankingSubquestions()
    {
        return "
            INSERT INTO {{questions}}(parent_qid, sid, gid, type, title, question_order, relevance)
            SELECT q.qid, q.sid, q.gid, 'R', a.code, a.sortorder, '1'
            FROM {{answers}} a
            JOIN {{questions}} q
            ON a.qid = q.qid and q.type = 'R'
            LEFT JOIN {{questions}} existent
            ON existent.parent_qid = q.qid and existent.title = a.code
            WHERE existent.qid IS NULL
        ";
    }

    /**
     * Creating subquestion l10ns for ranking instead of its answers
     * @return string
     */
    public function insertRankingSubquestionsL10ns()
    {
        return "
            INSERT INTO {{question_l10ns}}(qid, question, language)
            SELECT target.qid, al.answer, al.language
            FROM {{answer_l10ns}} al
            JOIN {{answers}} a
            ON al.aid = a.aid
            JOIN {{questions}} q
            ON a.qid = q.qid and q.type = 'R'
            JOIN {{questions}} target
            ON target.parent_qid = q.qid and target.title = a.code
            LEFT JOIN {{question_l10ns}} existent
            ON existent.qid = target.qid and existent.language = al.language
            WHERE existent.qid IS NULL
        ";
    }

    /**
     * Cleanup for ranking answers
     * @return string
     */
    public function deleteRankingAnswers()
    {
        return "DELETE FROM {{answers}} WHERE EXISTS (SELECT qid FROM {{questions}} WHERE type ='R' AND {{questions}}.qid = {{answers}}.qid)";
    }

    /**
     * Cleanup for translated ranking answers
     * @return string
     */
    public function deleteTranslatedRankingAnswers()
    {
        return "DELETE FROM {{answer_l10ns}} WHERE NOT EXISTS (SELECT aid FROM {{answers}} WHERE {{answer_l10ns}}.aid = {{answers}}.aid)";
    }

    /**
     * Updates ranking question attributes from answer_order to subquestion_order
     * @return string
     */
    public function updateRankingAnswerOrderAttribute()
    {
        return "
            UPDATE {{question_attributes}}
            SET attribute = 'subquestion_order'
            WHERE attribute = 'answer_order'
            AND qid IN (SELECT qid FROM {{questions}} WHERE type = 'R')
        ";
    }
    // -------------------------------------------------------------------------
    // INSERTANS conversion helpers – self-contained (no AR models)
    // -------------------------------------------------------------------------
    /**
     * Fixes textual data in an array record by replacing old fieldname representations.
     *
     * @param array $record Record row (passed by reference)
     * @param array $fields Field names to inspect
     * @param array $replacements Mapping of old => new field names
     * @return bool Whether the record was changed
     */
    protected function fixText(array &$record, $fields, $replacements): bool
    {
        $changed = false;
        foreach ($fields as $field) {
            if (!array_key_exists($field, $record)) {
                continue;
            }
            $original = $record[$field];
            foreach ($replacements as $old => $new) {
                if ($record[$field]) {
                    $record[$field] = str_replace($old, $new, $record[$field]);
                }
            }
            if ($original !== $record[$field]) {
                $changed = true;
            }
        }
        return $changed;
    }

    /**
     * Convert legacy INSERTANS tags to modern question code references.
     *
     * The result is wrapped in curly braces only when the source tag also used curly braces.
     *
     * When the QID+suffix boundary is ambiguous (e.g. purely numeric suffixes in array
     * questions like {INSERTANS:136212X36X10921}), the function progressively tries
     * shorter QID values until a known QID is found, treating the remainder as suffix.
     *
     * @param string $text The text containing INSERTANS tags
     * @param array $questionCodesByQid Mapping of QID => question title
     * @return string The text with INSERTANS tags converted to qcode.shown format
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function convertLegacyInsertans($text, array $questionCodesByQid)
    {
        if (empty($text) || strpos($text, 'INSERTANS:') === false || empty($questionCodesByQid)) {
            return $text;
        }
        return preg_replace_callback(
            '/(\{?)INSERTANS:([^}\s]+)\}?/',
            static function ($matches) use ($questionCodesByQid) {
                $hasBraces = $matches[1] === '{';
                $rawField = $matches[2];

                // Only old SGQA format is supported: SIDXGIDXQID[suffix]
                if (!preg_match('/^(\d+)X(\d+)X(\d+)(.*)$/', $rawField, $m)) {
                    return $matches[0]; // Not a recognised format – leave unchanged
                }
                $qidPart = $m[3];
                $suffix = $m[4];

                // Resolve QID: the regex greedily captures all digits into $qidPart,
                // but for array-type questions the suffix can be purely numeric
                // (e.g. "10921" = QID 1092 + suffix "1"). Try the full number first,
                // then progressively shorten $qidPart, moving trailing digits to $suffix.
                $parentTitle = null;
                $resolvedSuffix = $suffix;
                for ($i = strlen($qidPart); $i >= 1; $i--) {
                    $tryQid = (int)substr($qidPart, 0, $i);
                    $trySuffix = substr($qidPart, $i) . $suffix;
                    if (isset($questionCodesByQid[$tryQid])) {
                        $parentTitle = $questionCodesByQid[$tryQid];
                        $resolvedSuffix = $trySuffix;
                        break;
                    }
                }
                if ($parentTitle === null) {
                    return $matches[0]; // QID not found – leave unchanged
                }

                // Dual-scale arrays use '#' as separator (e.g. "money#0"), convert to '_'
                $resolvedSuffix = str_replace('#', '_', $resolvedSuffix);
                if ($resolvedSuffix === '') {
                    $qcode = $parentTitle . '.shown';
                } else {
                    // Suffix is a subquestion code (e.g. SQ001, other, comment, F1, 1, ls1)
                    // If the suffix already starts with '_' (e.g. "_filecount"), don't add another one
                    $separator = (strpos($resolvedSuffix, '_') === 0) ? '' : '_';
                    $qcode = $parentTitle . $separator . $resolvedSuffix . '.shown';
                }
                return $hasBraces ? '{' . $qcode . '}' : $qcode;
            },
            $text
        );
    }

    /**
     * Applies convertLegacyInsertans() to the specified fields of an array record.
     *
     * @param array $record Record row (passed by reference)
     * @param array $fields Field names where INSERTANS tags may occur
     * @param int|string $sid Survey ID (used to lazy-load question codes)
     * @return bool Whether the record was changed
     */
    protected function handleInsertans(array &$record, $fields, $sid): bool
    {
        $changed = false;
        $sid = (int)$sid;

        // Lazy-load question codes for this survey (QID → title).
        // Only parent questions (parent_qid = 0) are included, because INSERTANS
        // references parent QIDs with the subquestion code as suffix.
        if (!isset($this->questionCodes[$sid])) {
            $this->questionCodes[$sid] = [];
            $questions = Yii::app()->db->createCommand()
                ->select('qid, title')
                ->from('{{questions}}')
                ->where('sid = :sid AND parent_qid = 0', [':sid' => $sid])
                ->queryAll();
            foreach ($questions as $question) {
                $this->questionCodes[$sid][$question['qid']] = $question['title'];
            }
        }
        foreach ($fields as $field) {
            $text = $record[$field] ?? '';
            if ($text === '' || strpos($text, 'INSERTANS:') === false) {
                continue;
            }
            $converted = $this->convertLegacyInsertans($text, $this->questionCodes[$sid]);
            if ($converted !== $text) {
                $record[$field] = $converted;
                $changed = true;
            }
        }
        return $changed;
    }

    /**
     * Convert INSERTANS tags and fix SGQA references in all text fields of a survey.
     * Uses direct DB queries and UPDATE statements – no AR models required.
     *
     * @param int|string $sid Survey ID
     * @param array $questions Parent question rows (each with an 'answers' sub-array)
     * @param array $fieldNames SGQA=>new field name map for fixText (may be empty)
     * @param array $additionalNames SGQA=>Q{qid} map for fixText (may be empty)
     * @param bool $guardRelevance When true, skip question relevance updates if survey is missing
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function convertSurveyInsertans($sid, array $questions, array $fieldNames = [], array $additionalNames = [], $guardRelevance = false)
    {
        $db = Yii::app()->db;
        $sid = (int)$sid;
        $qids = [0];
        $gids = [0];
        $aids = [0];
        foreach ($questions as $question) {
            $qids[] = (int)$question['qid'];
            if (!in_array((int)$question['gid'], $gids)) {
                $gids[] = (int)$question['gid'];
            }
            foreach ($question['answers'] ?? [] as $answer) {
                $aids[] = (int)$answer['aid'];
            }
        }
        $qidList = implode(',', $qids);
        $gidList = implode(',', $gids);
        $aidList = implode(',', $aids);
        // Collect dvids
        $dvids = [0];
        $dvRows = $db->createCommand()
            ->select('dvid')
            ->from('{{defaultvalues}}')
            ->where("qid IN ({$qidList})")
            ->queryAll();
        foreach ($dvRows as $dv) {
            $dvids[] = (int)$dv['dvid'];
        }
        $dvidList = implode(',', $dvids);
        // Guard check
        $surveyExists = true;
        if ($guardRelevance) {
            $surveyExists = (bool)$db->createCommand()
                ->select('COUNT(*)')
                ->from('{{surveys}}')
                ->where('sid = :sid', [':sid' => $sid])
                ->queryScalar();
        }
        // Helper closure to apply all transformations to a row and optionally save
        $applyAndSave = function (array &$row, array $fields, callable $saveFn) use ($fieldNames, $additionalNames, $sid) {
            $changed = $this->handleInsertans($row, $fields, $sid);
            if (!empty($fieldNames)) {
                $changed = $this->fixText($row, $fields, $fieldNames) || $changed;
            }
            if (!empty($additionalNames)) {
                $changed = $this->fixText($row, $fields, $additionalNames) || $changed;
            }
            if ($changed) {
                $saveFn($row);
            }
        };
        // Conditions
        $conditions = $db->createCommand()
            ->select('cid, cfieldname, value')
            ->from('{{conditions}}')
            ->where("qid IN ({$qidList})")
            ->queryAll();
        foreach ($conditions as &$row) {
            $applyAndSave($row, [
                'cfieldname',
                'value'
            ], function ($r) use ($db) {
                $db->createCommand()->update('{{conditions}}', [
                    'cfieldname' => $r['cfieldname'],
                    'value'      => $r['value']
                ], 'cid = :id', [':id' => $r['cid']]);
            });
        }
        unset($row);
        // question_l10ns
        $ql10ns = $db->createCommand()
            ->select('id, question, script, help')
            ->from('{{question_l10ns}}')
            ->where("qid IN ({$qidList})")
            ->queryAll();
        foreach ($ql10ns as &$row) {
            $applyAndSave($row, [
                'question',
                'script',
                'help'
            ], function ($r) use ($db) {
                $db->createCommand()->update('{{question_l10ns}}', [
                    'question' => $r['question'],
                    'script'   => $r['script'],
                    'help'     => $r['help']
                ], 'id = :id', [':id' => $r['id']]);
            });
        }
        unset($row);
        // Questions (title, relevance)
        foreach ($questions as &$question) {
            $fields = [
                'title',
                'relevance'
            ];
            $changed = $this->handleInsertans($question, $fields, $sid);
            if (!empty($fieldNames)) {
                $changed = $this->fixText($question, $fields, $fieldNames) || $changed;
            }
            if (!empty($additionalNames)) {
                $changed = $this->fixText($question, $fields, $additionalNames) || $changed;
            }
            if ($changed) {
                if ($guardRelevance && !$surveyExists) {
                    continue;
                }
                $db->createCommand()->update('{{questions}}', [
                    'title'     => $question['title'],
                    'relevance' => $question['relevance']
                ], 'qid = :id', [':id' => $question['qid']]);
            }
        }
        unset($question);
        // surveys_languagesettings
        $sls = $db->createCommand()
            ->select('surveyls_survey_id, surveyls_language, surveyls_urldescription, surveyls_url')
            ->from('{{surveys_languagesettings}}')
            ->where('surveyls_survey_id = :sid', [':sid' => $sid])
            ->queryAll();
        foreach ($sls as &$row) {
            $applyAndSave($row, [
                'surveyls_urldescription',
                'surveyls_url'
            ], function ($r) use ($db) {
                $db->createCommand()->update(
                    '{{surveys_languagesettings}}',
                    [
                        'surveyls_urldescription' => $r['surveyls_urldescription'],
                        'surveyls_url'            => $r['surveyls_url']
                    ],
                    'surveyls_survey_id = :sid AND surveyls_language = :lang',
                    [
                        ':sid'  => $r['surveyls_survey_id'],
                        ':lang' => $r['surveyls_language']
                    ]
                );
            });
        }
        unset($row);
        // quota_languagesettings
        $qls = $db->createCommand()
            ->select('qls.quotals_id, qls.quotals_url, qls.quotals_urldescrip')
            ->from('{{quota_languagesettings}} qls')
            ->join('{{quota}} q', 'qls.quotals_quota_id = q.id')
            ->where('q.sid = :sid', [':sid' => $sid])
            ->queryAll();
        foreach ($qls as &$row) {
            $applyAndSave($row, [
                'quotals_url',
                'quotals_urldescrip'
            ], function ($r) use ($db) {
                $db->createCommand()->update(
                    '{{quota_languagesettings}}',
                    [
                        'quotals_url'        => $r['quotals_url'],
                        'quotals_urldescrip' => $r['quotals_urldescrip']
                    ],
                    'quotals_id = :id',
                    [':id' => $r['quotals_id']]
                );
            });
        }
        unset($row);
        // group_l10ns
        $gl10ns = $db->createCommand()
            ->select('id, description, group_name')
            ->from('{{group_l10ns}}')
            ->where("gid IN ({$gidList})")
            ->queryAll();
        foreach ($gl10ns as &$row) {
            $applyAndSave($row, [
                'description',
                'group_name'
            ], function ($r) use ($db) {
                $db->createCommand()->update('{{group_l10ns}}', [
                    'description' => $r['description'],
                    'group_name'  => $r['group_name']
                ], 'id = :id', [':id' => $r['id']]);
            });
        }
        unset($row);
        // assessments
        $assessments = $db->createCommand()
            ->select('id, language, name, message')
            ->from('{{assessments}}')
            ->where('sid = :sid', [':sid' => $sid])
            ->queryAll();
        foreach ($assessments as &$row) {
            $applyAndSave($row, [
                'name',
                'message'
            ], function ($r) use ($db) {
                $db->createCommand()->update(
                    '{{assessments}}',
                    [
                        'name'    => $r['name'],
                        'message' => $r['message']
                    ],
                    'id = :id AND language = :lang',
                    [
                        ':id'   => $r['id'],
                        ':lang' => $r['language']
                    ]
                );
            });
        }
        unset($row);
        // defaultvalue_l10ns
        $dvl10ns = $db->createCommand()
            ->select('id, defaultvalue')
            ->from('{{defaultvalue_l10ns}}')
            ->where("dvid IN ({$dvidList})")
            ->queryAll();
        foreach ($dvl10ns as &$row) {
            $applyAndSave($row, ['defaultvalue'], function ($r) use ($db) {
                $db->createCommand()->update('{{defaultvalue_l10ns}}', ['defaultvalue' => $r['defaultvalue']], 'id = :id', [':id' => $r['id']]);
            });
        }
        unset($row);
        // answer_l10ns
        $al10ns = $db->createCommand()
            ->select('id, answer')
            ->from('{{answer_l10ns}}')
            ->where("aid IN ({$aidList})")
            ->queryAll();
        foreach ($al10ns as &$row) {
            $applyAndSave($row, ['answer'], function ($r) use ($db) {
                $db->createCommand()->update('{{answer_l10ns}}', ['answer' => $r['answer']], 'id = :id', [':id' => $r['id']]);
            });
        }
        unset($row);
    }

    /**
     * helper function to get questions with answers
     * Load questions for a survey as plain DB row arrays, each with an 'answers' sub-array.
     *
     * @param int $sid
     * @param string $extraCondition Optional extra WHERE clause (no leading AND)
     * @param array $params
     * @return array
     */
    protected function loadQuestionsWithAnswers(int $sid, string $extraCondition = '', array $params = []): array
    {
        // Maximum number of qids per IN (...) batch.
        // Keeps individual query result sets small and avoids driver bind-parameter
        // limits (e.g. SQL Server caps at 2100 parameters per statement).
        $chunksize = 500;

        $db = Yii::app()->db;
        $params[':sid'] = $sid;
        $questions = $db->createCommand()
            ->select('*')
            ->from('{{questions}}')
            ->where('sid = :sid' . ($extraCondition ? " AND {$extraCondition}" : ''), $params)
            ->queryAll();

        if (empty($questions)) {
            return [];
        }

        // Build a grouped map of answers fetched in fixed-size batches.
        // Chunking prevents a single enormous IN (...) list from exhausting
        // the DB driver's result buffer or hitting parameter-count limits,
        // and lets us free each batch result set immediately after grouping.
        $qids = array_column($questions, 'qid');
        $answersByQid = [];
        foreach (array_chunk($qids, $chunksize) as $qidChunk) {
            $placeholders = implode(',', array_fill(0, count($qidChunk), '?'));
            $chunkAnswers = $db->createCommand()
                ->select('*')
                ->from('{{answers}}')
                ->where("qid IN ({$placeholders})", $qidChunk)
                ->order('qid, sortorder')
                ->queryAll();

            foreach ($chunkAnswers as $answer) {
                $answersByQid[$answer['qid']][] = $answer;
            }
            unset($chunkAnswers); // free the raw batch result immediately
        }
        unset($qids);

        // Attach grouped answers to each question, then discard the map.
        foreach ($questions as &$question) {
            $question['answers'] = $answersByQid[$question['qid']] ?? [];
        }
        unset($question, $answersByQid);

        return $questions;
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function up()
    {
        $this->db->createCommand($this->deleteRankingSubquestions())->execute();
        $this->db->createCommand($this->insertRankingSubquestions())->execute();
        $this->db->createCommand($this->insertRankingSubquestionsL10ns())->execute();
        $this->doPreparations();
        $this->scriptMapping = [
            'responses' => $this->getResponsesScript(),
            'timings'   => $this->getTimingScript(),
            'fields'    => $this->getFieldsScript()
        ];
        $scripts = [];
        $responsesTables = $this->db->createCommand($this->scriptMapping['responses'])->queryAll();
        foreach ($responsesTables as $responsesTable) {
            if (((strpos($responsesTable['old_name'], 'old_') === false) && (strpos($responsesTable['old_name'], 'timing') === false))) {
                $parts = explode('_', $responsesTable['old_name']);
            }
            $scripts[$responsesTable['old_name']] = [
                'new_name' => $responsesTable['new_name'],
                'old_name' => $responsesTable['old_name'],
                'handled'  => false
            ];
            $createTable = $this->adjustShowCreateTable($this->db->createCommand($this->showCreateTable($responsesTable['old_name']))->queryRow(), $responsesTable['old_name']);
            $scripts[$responsesTable['old_name']]['CREATE'] = $createTable['Create Table'];
            $scripts[$responsesTable['old_name']]['DROP'] = "DROP TABLE {$responsesTable['old_name']}";
            $scripts[$responsesTable['old_name']]['columns'] = $this->db->createCommand($this->getFieldsFromTableScript($responsesTable['old_name']))->queryAll();
        }
        $timingsTables = $this->db->createCommand($this->scriptMapping['timings'])->queryAll();
        foreach ($timingsTables as $timingsTable) {
            $scripts[$timingsTable['old_name']] = [
                'new_name' => $timingsTable['new_name'],
                'old_name' => $timingsTable['old_name'],
                'handled'  => false
            ];
            $createTable = $this->adjustShowCreateTable($this->db->createCommand($this->showCreateTable($timingsTable['old_name']))->queryRow(), $timingsTable['old_name']);
            $scripts[$timingsTable['old_name']]['CREATE'] = $createTable['Create Table'];
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
            $split = explode('X', $fieldName);
            $sid = $split[0];
            $gid = $split[1];
            $qids = [];
            $position = 0;
            $questionsToPass = [];
            if (count($split) > 2) {
                while (($position < strlen($split[2])) && ctype_digit($split[2][$position])) {
                    $qids[] = (count($qids) ? ($qids[count($qids) - 1] . $split[2][$position]) : $split[2][$position]);
                    $position++;
                }
                $commaSeparatedQIDs = implode(',', $qids);
                $questionsToPass = $this->loadQuestionsWithAnswers(
                    (int)$sid,
                    "((qid IN ({$commaSeparatedQIDs}) AND gid = :gid) OR parent_qid IN ({$commaSeparatedQIDs}))",
                    [':gid' => (int)$gid]
                );
            }
            if (count($questionsToPass) || ((strpos($tableName, 'timings') !== false) && (count($split) > 1))) {
                $fieldMap[$tableName][$fieldName] = $this->getFieldName($tableName, $fieldName, $questionsToPass, (int)$sid, (int)$gid);
            }
        }
        $preinsert = "";
        $postinsert = "";
        foreach ($fieldMap as $TABLE_NAME => $fields) {
            if (in_array(Yii::app()->db->getDriverName(), [
                'mssql',
                'sqlsrv',
                'dblib'
            ])) {
                $preinsert = "SET IDENTITY_INSERT {$scripts[$TABLE_NAME]['new_name']} ON;";
                $postinsert = "SET IDENTITY_INSERT {$scripts[$TABLE_NAME]['new_name']} OFF;";
            }
            $scripts[$TABLE_NAME]['handled'] = true;
            if (!isset($scripts[$TABLE_NAME]['new_name'])) {
                continue;
            }
            $scripts[$TABLE_NAME]['CREATE'] = str_replace("{$TABLE_NAME}", "{$scripts[$TABLE_NAME]['new_name']}", $scripts[$TABLE_NAME]['CREATE']);
            foreach ($fields as $oldField => $newField) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace($this->dbQuoteFields($oldField), $this->dbQuoteFields($newField), $scripts[$TABLE_NAME]['CREATE']);
            }
            $fromColumns = [];
            $toColumns = [];
            foreach ($scripts[$TABLE_NAME]['columns'] as $column) {
                if (!isset($column['COLUMN_NAME'])) {
                    if (isset($column['column_name'])) {
                        $column['COLUMN_NAME'] = $column['column_name'];
                    }
                }
                $fromColumns[] = $this->dbQuoteFields($column['COLUMN_NAME']);
                if (isset($fields[$column['COLUMN_NAME']])) {
                    $toColumns[] = $this->dbQuoteFields($fields[$column['COLUMN_NAME']]);
                } else {
                    $toColumns[] = $this->dbQuoteFields($column['COLUMN_NAME']);
                }
            }
            $from = implode(",", $fromColumns);
            $to = implode(",", $toColumns);
            $scripts[$TABLE_NAME]['INSERT'] = "
                INSERT INTO {$scripts[$TABLE_NAME]['new_name']}({$to})
                SELECT {$from}
                FROM {$TABLE_NAME};
            ";
            try {
                $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
                $this->db->createCommand($preinsert . $scripts[$TABLE_NAME]['INSERT'] . $postinsert)->execute();
                $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
            } catch (\Exception $ex) {
                if (strpos($TABLE_NAME, "old") !== false) {
                    continue;
                } else {
                    throw $ex;
                }
            }
            if (count($fields) && (strpos($TABLE_NAME, 'survey') !== false) && (strpos($TABLE_NAME, 'timing') === false)) {
                $keys = array_keys($fields);
                arsort($keys);
                $names = [];
                $parts = explode("_", $TABLE_NAME);
                $index = count($parts) - ((strpos($TABLE_NAME, "old") === false) ? 1 : 2);
                $sid = $parts[$index];
                $this->insertansProcessedSids[$sid] = true;
                foreach ($keys as $oldName) {
                    $names[$oldName] = $fields[$oldName];
                }
                $questions = $this->loadQuestionsWithAnswers((int)$sid);
                $rawAdditionalNames = [];
                foreach ($questions as $question) {
                    $rawAdditionalNames["{$question['sid']}X{$question['gid']}X{$question['qid']}"] = "Q{$question['qid']}";
                }
                $additionalNameKeys = array_keys($rawAdditionalNames);
                arsort($additionalNameKeys);
                $additionalNames = [];
                foreach ($additionalNameKeys as $additionalNameKey) {
                    $additionalNames[$additionalNameKey] = $rawAdditionalNames[$additionalNameKey];
                }
                $this->convertSurveyInsertans($sid, $questions, $names, $additionalNames, true);
            }
        }
        // Inactive surveys
        $passiveSurveyRows = $this->db->createCommand()
            ->select('*')
            ->from('{{surveys}}')
            ->where("active <> 'Y'")
            ->queryAll();
        foreach ($passiveSurveyRows as $passiveSurvey) {
            $sid = $passiveSurvey['sid'];
            $this->insertansProcessedSids[$sid] = true;
            $questions = $this->loadQuestionsWithAnswers((int)$sid);
            $rawAdditionalNames = [];
            foreach ($questions as $question) {
                $rawAdditionalNames["{$question['sid']}X{$question['gid']}X{$question['qid']}"] = "Q{$question['qid']}";
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
                if (strpos($oldField, 'X') !== false) {
                    $split = explode('X', $oldField);
                    $fieldSid = $split[0];
                    $fieldGid = $split[1];
                    $tempqids = [];
                    $position = 0;
                    if (count($split) > 2) {
                        while (($position < strlen($split[2])) && ctype_digit($split[2][$position])) {
                            $tempqids[] = (count($tempqids) ? ($tempqids[count($tempqids) - 1] . $split[2][$position]) : $split[2][$position]);
                            $position++;
                        }
                        $commaSeparatedQIDs = implode(',', $tempqids);
                        $questionsTemp = $this->loadQuestionsWithAnswers(
                            (int)$fieldSid,
                            "((qid IN ({$commaSeparatedQIDs}) AND gid = :gid) OR parent_qid IN ({$commaSeparatedQIDs}))",
                            [':gid' => (int)$fieldGid]
                        );
                        $prefix = Yii::app()->db->tablePrefix ?? '';
                        if (count($questionsTemp)) {
                            $newFieldName = $this->getFieldName($prefix . 'survey_' . $passiveSurvey['sid'], $oldField, $questionsTemp, (int)$fieldSid, (int)$fieldGid);
                            if ($newFieldName) {
                                $newFields[$oldField] = $newFieldName;
                            }
                        }
                    }

                }
            }
            $this->convertSurveyInsertans($sid, $questions, $newFields, $additionalNames);
        }

        // Catch-all: convert INSERTANS tags for any surveys not already processed above.
        // The active-survey loop only processes surveys that have response tables with SGQA columns,
        // so active surveys without such columns would be missed.
        $allSurveyRows = $this->db->createCommand()
            ->select('sid')
            ->from('{{surveys}}')
            ->queryAll();
        foreach ($allSurveyRows as $surveyRow) {
            $sid = $surveyRow['sid'];
            if (isset($this->insertansProcessedSids[$sid])) {
                continue;
            }
            $questions = $this->loadQuestionsWithAnswers((int)$sid);
            $this->convertSurveyInsertans($sid, $questions);
        }
        foreach ($scripts as $TABLE_NAME => $content) {
            if (!$content['handled']) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace("{$TABLE_NAME}", "{$scripts[$TABLE_NAME]['new_name']}", $scripts[$TABLE_NAME]['CREATE']);
                $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
                $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
            }
        }
        // Update archived table settings
        $archivedRows = $this->db->createCommand()
            ->select('id, tbl_name')
            ->from('{{archived_table_settings}}')
            ->queryAll();
        foreach ($archivedRows as $row) {
            $tblName = $row['tbl_name'];
            if (strpos($tblName, 'survey') !== false) {
                if (strpos($tblName, 'timings') !== false) {
                    $newName = str_replace('survey', 'timings', str_replace('_timings', '', $tblName));
                } else {
                    $newName = str_replace('survey', 'responses', $tblName);
                }
                $this->db->createCommand()->update(
                    '{{archived_table_settings}}',
                    ['tbl_name' => $newName],
                    'id = :id',
                    [':id' => $row['id']]
                );
            }
        }
        $this->db->createCommand($this->deleteRankingAnswers())->execute();
        $this->db->createCommand($this->deleteTranslatedRankingAnswers())->execute();
        $this->db->createCommand($this->updateRankingAnswerOrderAttribute())->execute();
    }
}
