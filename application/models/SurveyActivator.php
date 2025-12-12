<?php

/**
 * Service class to activate survey.
 * @todo Move to models/services/survey/ folder.
 */
class SurveyActivator
{
    /** @var Survey */
    protected $survey;

    /** @var array  */
    protected $tableDefinition = [];

    /** @var array  */
    protected $timingsTableDefinition = [];

    /** @var string */
    protected $error;

    /** @var bool */
    protected $createSurveyDir = false;

    /** @var boolean */
    public $isSimulation = false;

    /**
     * @param Survey $survey
     */
    public function __construct($survey = null)
    {
        $this->survey = $survey;
    }

    /**
     * @param Survey $survey
     * @return SurveyActivator
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;
        return $this;
    }

    /**
     * Sets a survey into "activate" state.
     * Creates necessary tables "responseTable", "timingTable".
     * Fires events "beforeSurveyActivate" and "afterSurveyActivation"
     *
     * @return array
     * @throws CException
     */
    public function activate()
    {
        EmCacheHelper::init(['sid' => $this->survey->sid, 'active' => 'Y']);
        EmCacheHelper::flush();

        $event = new PluginEvent('beforeSurveyActivate');
        $event->set('surveyId', $this->survey->primaryKey);
        $event->set('simulate', $this->isSimulation);
        App()->getPluginManager()->dispatchEvent($event);

        $this->setMySQLDefaultEngine(Yii::app()->getConfig('mysqlEngine'));

        if (!$this->showEventMessages($event)) {
            return ['error' => 'plugin'];
        }

        $this->prepareResponsesTable();

        if ($this->isSimulation) {
            return array(
                'dbengine' => Yii::app()->db->getDriverName(),
                'dbtype' => Yii::app()->db->driverName,
                'fields' => $this->tableDefinition
            );
        }

        if (!$this->createResponseTable()) {
            return ['error' => $this->error];
        }

        if (!$this->createTimingsTable()) {
            return ['error' => 'timingstablecreation'];
        }

        if (!empty($this->error)) {
            return ['error' => $this->error];
        }

        $survey = Survey::model()->findByAttributes(array('sid' => $this->survey->primaryKey));
        $survey->scenario = 'activationStateChange';
        $survey->active = 'Y';
        $survey->save();

        $aResult = array(
            'status' => 'OK',
            'pluginFeedback' => $event->get('pluginFeedback'),
            'isAllowRegister' => $survey->isAllowRegister
        );
        if (!$this->createSurveyDirectory()) {
            $aResult['warning'] = 'nouploadsurveydir';
        }

        LimeExpressionManager::SetDirtyFlag();

        $event = new PluginEvent('afterSurveyActivate');
        $event->set('surveyId', $this->survey->sid);
        $event->set('simulate', $this->isSimulation);
        App()->getPluginManager()->dispatchEvent($event);

        return $aResult;
    }

    /**
     * See bug #09828: Ranking question : update allowed can broke Survey DB
     * If max_subquestions is not set or is invalid : set it to actual answers numbers
     *
     * @return void
     */
    public function fixQuestionAttributeForRanking($sFieldMap)
    {
        foreach ($sFieldMap as $aRow) {
            switch ($aRow['type']) {
                case Question::QT_R_RANKING:
                    $nrOfAnswers = Answer::model()->countByAttributes(
                        array('qid' => $aRow['qid'])
                    );
                    $oQuestionAttribute = QuestionAttribute::model()->find(
                        "qid = :qid AND attribute = 'max_subquestions'",
                        array(':qid' => $aRow['qid'])
                    );
                    if (empty($oQuestionAttribute)) {
                        $oQuestionAttribute = new QuestionAttribute();
                        $oQuestionAttribute->qid = $aRow['qid'];
                        $oQuestionAttribute->attribute = 'max_subquestions';
                        $oQuestionAttribute->value = $nrOfAnswers;
                        $oQuestionAttribute->save();
                    } elseif (intval($oQuestionAttribute->value) < 1) {
                        // Fix it if invalid : disallow 0, but need a sub question minimum for EM
                        $oQuestionAttribute->value = $nrOfAnswers;
                        $oQuestionAttribute->save();
                    }
            }
        }
    }

    /**
     * For each question, create the appropriate field(s)
     *
     * @param string $collation
     * @return void
     */
    protected function prepareTableDefinition(string $collation, array $sFieldMap)
    {
        foreach ($sFieldMap as $aRow) {
            switch ($aRow['type']) {
                case 'seed':
                    $aTableDefinition[$aRow['fieldname']] = "string(31)";
                    break;
                case 'startlanguage':
                    $aTableDefinition[$aRow['fieldname']] = "string(20) NOT NULL";
                    break;
                case 'id':
                    $aTableDefinition[$aRow['fieldname']] = "pk";
                    break;
                case "startdate":
                case "datestamp":
                    $aTableDefinition[$aRow['fieldname']] = "datetime NOT NULL";
                    break;
                case "submitdate":
                    $aTableDefinition[$aRow['fieldname']] = "datetime";
                    break;
                case "lastpage":
                    $aTableDefinition[$aRow['fieldname']] = "integer";
                    break;
                case Question::QT_N_NUMERICAL:
                case Question::QT_K_MULTIPLE_NUMERICAL:
                    $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "decimal (30,10)");
                    break;
                case Question::QT_S_SHORT_FREE_TEXT:
                    $aTableDefinition[$aRow['fieldname']] = isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "text";
                    break;
                case Question::QT_L_LIST:
                case Question::QT_EXCLAMATION_LIST_DROPDOWN:
                case Question::QT_M_MULTIPLE_CHOICE:
                case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                case Question::QT_O_LIST_WITH_COMMENT:
                    if ($aRow['aid'] != 'Cother' && strpos((string) $aRow['aid'], 'comment') === false) {
                        $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "string(5)") ;
                    } else {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case Question::QT_U_HUGE_FREE_TEXT:
                case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                case Question::QT_T_LONG_FREE_TEXT:
                case Question::QT_SEMICOLON_ARRAY_TEXT:
                case Question::QT_COLON_ARRAY_NUMBERS:
                    $aTableDefinition[$aRow['fieldname']] = isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "text";
                    break;
                case Question::QT_D_DATE:
                    $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "datetime");
                    break;
                case Question::QT_5_POINT_CHOICE:
                case Question::QT_G_GENDER:
                case Question::QT_Y_YES_NO_RADIO:
                case Question::QT_X_TEXT_DISPLAY:
                    $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "string(1)");
                    break;
                case Question::QT_I_LANGUAGE:
                    $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "string(20)");
                    break;
                case Question::QT_VERTICAL_FILE_UPLOAD:
                    $this->createSurveyDir = true;
                    if (strpos((string) $aRow['fieldname'], "_")) {
                        $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "integer");
                    } else {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "ipaddress":
                    if ($this->survey->isIpAddr) {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "url":
                    if ($this->survey->isRefUrl) {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "token":
                    $aTableDefinition[$aRow['fieldname']] = 'string(' . Token::MAX_LENGTH . ')' . $collation;
                    break;
                case Question::QT_ASTERISK_EQUATION:
                    $aTableDefinition[$aRow['fieldname']] = isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "text";
                    break;
                case Question::QT_R_RANKING:
                    $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "string(5)");
                    break;                                                                                                                                                                                                                                                                 default:
                    $aTableDefinition[$aRow['fieldname']] = (array_key_exists('encrypted', $aRow) && $aRow['encrypted'] == 'Y') ? "text" : (isset($aRow['answertabledefinition']) && !empty($aRow['answertabledefinition']) ? $aRow['answertabledefinition'] : "string(5)");
            }
            if (!$this->survey->isAnonymized && !array_key_exists('token', $aTableDefinition)) {
                $aTableDefinition['token'] = 'string(' . Token::MAX_LENGTH . ')' . $collation;
            }
        }
        $this->tableDefinition = $aTableDefinition;
    }

    /**
     * @return void
     */
    protected function prepareTimingsTable()
    {
        $timingsfieldmap = createTimingsFieldMap(
            $this->survey->primaryKey,
            "full",
            false,
            false,
            $this->survey->language
        );
        $aTimingTableDefinition = array();
        $aTimingTableDefinition['id'] = $this->tableDefinition['id'];
        foreach (array_keys($timingsfieldmap) as $field) {
            $aTimingTableDefinition[$field] = 'FLOAT';
        }
        $this->timingsTableDefinition = $aTimingTableDefinition;
    }

    /**
     * @return string
     */
    protected function getCollation()
    {
        // Specify case sensitive collations for the token
        $collation = '';
        if (Yii::app()->db->driverName == 'mysqli' || Yii::app()->db->driverName == 'mysql') {
            $collation = " COLLATE 'utf8mb4_bin'";
        }
        if (
            Yii::app()->db->driverName == 'sqlsrv'
            || Yii::app()->db->driverName == 'dblib'
            || Yii::app()->db->driverName == 'mssql'
        ) {
            $collation = " COLLATE SQL_Latin1_General_CP1_CS_AS";
        }
        return $collation;
    }

    /**
     * @return void
     */
    protected function prepareSimulateQuery()
    {
        if ($this->isSimulation) {
            $tempTrim = trim($this->tableDefinition);
            $brackets = strpos($tempTrim, "(");
            if ($brackets === false) {
                $type = substr($tempTrim, 0, 2);
            } else {
                $type = substr($tempTrim, 0, 2);
            }
            $arrSim[] = array($type);
            $this->tableDefinition = $arrSim;
        }
    }

    /**
     * @return void
     */
    protected function prepareResponsesTable()
    {
        /** @var string */
        $collation = $this->getCollation();
        //Check for any additional fields for this survey and create necessary fields (token and datestamp)
        $this->survey->fixInvalidQuestions();
        //Get list of questions for the base language
        $sFieldMap = createFieldMap($this->survey, 'full', true, false, $this->survey->language);
        $this->fixQuestionAttributeForRanking($sFieldMap);
        $this->prepareTableDefinition($collation, $sFieldMap);
        $this->prepareSimulateQuery();
    }

    /**
     * @return boolean
     * @throws CDbException
     * @throws CException
     */
    protected function createResponseTable()
    {
        $sTableName = $this->survey->responsesTableName;
        Yii::app()->loadHelper("database");
        try {
            Yii::app()->db->createCommand()->createTable($sTableName, $this->tableDefinition);
            // Refresh schema cache just in case the table existed in the past
            Yii::app()->db->schema->getTable($sTableName, true);
        } catch (Exception $e) {
                $this->error = 'surveytablecreation';
            if (App()->getConfig('debug')) {
                $this->error = $e->getMessage();
            } else {
                $this->error = 'surveytablecreation';
            }
            return false;
        }
        try {
            if (isset($this->tableDefinition['token'])) {
                Yii::app()->db->createCommand()->createIndex(
                    "idx_survey_token_{$this->survey->primaryKey}_" . rand(1, 50000),
                    $sTableName,
                    'token'
                );
            }
        } catch (\Exception $e) {
        }

        $this->createResponseTableKeys();
        return true;
    }

    /**
     * @param PluginEvent $event
     * @return boolean
     */
    protected function showEventMessages($event)
    {
        $success = $event->get('success');
        $message = $event->get('message');

        if ($success === false) {
            Yii::app()->user->setFlash('error', $message);
            return false;
        } elseif (!empty($message)) {
            Yii::app()->user->setFlash('info', $message);
        }
        return true;
    }

    /**
     * @return void
     * @throws CDbException
     * @throws CException
     */
    protected function createResponseTableKeys()
    {

        $iAutoNumberStart = Yii::app()->db->createCommand()
            ->select('autonumber_start')
            ->from(Survey::model()->tableName())
            ->where('sid=:sid', [':sid' => $this->survey->primaryKey])
            ->queryScalar();

        //if there is an autonumber_start field, start auto numbering here
        if ($iAutoNumberStart !== false && $iAutoNumberStart > 0) {
            if (Yii::app()->db->driverName == 'mssql' || Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib') {
                if (!function_exists('fixNumbering')) {
                    Yii::app()->loadHelper("admin.activate"); // needed for mssql_drop_column_with_constraints
                }
                mssql_drop_column_with_constraints($this->survey->responsesTableName, 'id');
                $sQuery = "ALTER TABLE {$this->survey->responsesTableName} ADD [id] int identity({$iAutoNumberStart},1)";
                Yii::app()->db->createCommand($sQuery)->execute();
                // Add back the primaryKey

                Yii::app()->db->createCommand()->addPrimaryKey('PRIMARY_' . rand(1, 50000), $this->survey->responsesTableName, 'id');
            } elseif (Yii::app()->db->driverName == 'pgsql') {
                $sQuery = "SELECT setval(pg_get_serial_sequence('{$this->survey->responsesTableName}', 'id'),{$iAutoNumberStart},false);";
                // FIXME @ not good
                @Yii::app()->db->createCommand($sQuery)->execute();
            } else {
                $sQuery = "ALTER TABLE {$this->survey->responsesTableName} AUTO_INCREMENT = {$iAutoNumberStart}";
                // FIXME @ not good
                @Yii::app()->db->createCommand($sQuery)->execute();
            }
        }
    }

    /**
     * @return boolean
     */
    protected function createTimingsTable()
    {
        /**
         * CT-1121: Needed a fix because $this->survey->isSaveTimings is incorrectly N even if it's Y in the database
         * We will need to look into that problem later and restore the earlier code changed in 28bdcc3fde1e758756d2f4a4984e29a4105d3950
         * once $this->survey->isSaveTimings becomes reliable again
         * The idea for the fix was to load this value from the database for the time being until the session creation at the Question Editor is fixed
         */
        $prow = $this->survey->find('sid = :sid', array(':sid' => $this->survey->sid));
        if ($prow->savetimings == "Y") {
            $this->prepareTimingsTable();
            $sTableName = $this->survey->timingsTableName;
            try {
                Yii::app()->db->createCommand()->createTable($sTableName, $this->timingsTableDefinition);
                // Refresh schema cache just in case the table existed in the past
                Yii::app()->db->schema->getTable($sTableName, true);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function createSurveyDirectory()
    {
        $iSurveyID = $this->survey->primaryKey;
        // create the survey directory where the uploaded files can be saved
        if ($this->createSurveyDir) {
            if (!file_exists(Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files")) {
                if (!(mkdir(Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files", 0777, true))) {
                    return false;
                } else {
                    file_put_contents(Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files/index.html", '<html><head></head><body></body></html>');
                }
            }
        }
        return true;
    }

    /**
     * Set the default_storage_engine for mysql DB
     * @param string $dbEngine
     */
    private function setMySQLDefaultEngine($dbEngine)
    {
        /* empty dbEngine : out */
        if (empty($dbEngine)) {
            return;
        }
        $db = Yii::app()->db;
        /* not DB : out */
        if (empty($db)) {
            return;
        }
        /* not mysql : out */
        if (!in_array($db->driverName, [InstallerConfigForm::DB_TYPE_MYSQL, InstallerConfigForm::DB_TYPE_MYSQLI])) {
            return;
        }
        /* seems OK, sysadmin allowed to broke system */
        $db->createCommand(new CDbExpression(sprintf('SET default_storage_engine=%s;', $dbEngine)))
            ->execute();
    }

    /**
     * Checks if the survey is in close access mode.
     *
     * @return bool
     */
    public function isCloseAccessMode()
    {
        return $this->survey->access_mode === 'C';
    }
}
