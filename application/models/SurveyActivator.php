<?php


class SurveyActivator
{
    /** @var Survey */
    private $survey;
    /** @var array  */
    private $tableDefinition=[];
    /** @var array  */
    private $timingsTableDefinition=[];
    /** @var array  */
    private $fieldMap;
    /** @var string */
    private $collation;
    /** @var PluginEvent */
    private $event;
    /** @var string */
    private $error;
    /** @var bool */
    private $createSurveyDir = false;


    /** @var boolean */
    public $isSimulation;


    public function __construct($survey)
    {
        $this->survey = $survey;
    }

    public function activate(){

        $this->event = new PluginEvent('beforeSurveyActivate');
        $this->event->set('surveyId', $this->survey->primaryKey);
        $this->event->set('simulate', $this->isSimulation);
        App()->getPluginManager()->dispatchEvent($this->event);

        $this->showEventMessages();
        if(!empty($this->error)){
            return ['error'=>$this->error];
        }

        $this->prepareResponsesTable();

        if ($this->isSimulation) {
            return array('dbengine'=>Yii::app()->db->getDriverName(), 'dbtype'=>Yii::app()->db->driverName, 'fields'=>$this->tableDefinition);
        }

        // If last question is of type MCABCEFHP^QKJR let's get rid of the ending coma in createsurvey
        $this->createParticipantsTable();

        if ($this->survey->isSaveTimings) {
            $this->createTimingsTable();
        }

        if(!empty($this->error)){
            return ['error'=>$this->error];
        }



        $sQuery = "UPDATE {{surveys}} SET active='Y' WHERE sid=".$this->survey->primaryKey;
        Yii::app()->db->createCommand($sQuery)->query();

        $aResult = array(
            'status' => 'OK',
            'pluginFeedback' => $this->event->get('pluginFeedback')
        );
        if(!$this->createSurveyDirectory()){
            $aResult['warning'] = 'nouploadsurveydir';
        }

        return $aResult;
    }



    /**
     * For each question, create the appropriate field(s)
     */
    private function prepareTableDefinition(){
        $sFieldMap = $this->fieldMap;

        foreach ($sFieldMap as $j=>$aRow) {
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
                case "N":  //Numerical
                case "K":  //Multiple Numerical
                    $aTableDefinition[$aRow['fieldname']] = "decimal (30,10)";
                    break;
                case "S":  //SHORT TEXT
                    $aTableDefinition[$aRow['fieldname']] = "text";
                    break;
                case "L":  //LIST (RADIO)
                case "!":  //LIST (DROPDOWN)
                case "M":  //Multiple choice
                case "P":  //Multiple choice with comment
                case "O":  //DROPDOWN LIST WITH COMMENT
                    if ($aRow['aid'] != 'other' && strpos($aRow['aid'], 'comment') === false && strpos($aRow['aid'], 'othercomment') === false) {
                        $aTableDefinition[$aRow['fieldname']] = "string(5)";
                    } else {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "U":  //Huge text
                case "Q":  //Multiple short text
                case "T":  //LONG TEXT
                case ";":  //Multi Flexi
                case ":":  //Multi Flexi
                    $aTableDefinition[$aRow['fieldname']] = "text";
                    break;
                case "D":  //DATE
                    $aTableDefinition[$aRow['fieldname']] = "datetime";
                    break;
                case "5":  //5 Point Choice
                case "G":  //Gender
                case "Y":  //YesNo
                case "X":  //Boilerplate
                    $aTableDefinition[$aRow['fieldname']] = "string(1)";
                    break;
                case "I":  //Language switch
                    $aTableDefinition[$aRow['fieldname']] = "string(20)";
                    break;
                case "|":
                    $this->createSurveyDir = true;
                    if (strpos($aRow['fieldname'], "_")) {
                        $aTableDefinition[$aRow['fieldname']] = "integer";
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
                    $aTableDefinition[$aRow['fieldname']] = 'string(35)'.$this->collation;
                    break;
                case '*': // Equation
                    $aTableDefinition[$aRow['fieldname']] = "text";
                    break;
                case 'R':
                    /**
                     * See bug #09828: Ranking question : update allowed can broke Survey DB
                     * If max_subquestions is not set or is invalid : set it to actual answers numbers
                     */

                    $nrOfAnswers = Answer::model()->countByAttributes(
                        array('qid' => $aRow['qid'], 'language'=>$this->survey->language)
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
                    $aTableDefinition[$aRow['fieldname']] = "string(5)";
                    break;
                default:
                    $aTableDefinition[$aRow['fieldname']] = "string(5)";
            }
            if (!$this->survey->isAnonymized && !array_key_exists('token', $aTableDefinition)) {
                $aTableDefinition['token'] = 'string(35)'.$this->collation;
            }
        }
        $this->tableDefinition = $aTableDefinition;

    }

    private function prepareTimingsTable(){
        $timingsfieldmap = createTimingsFieldMap($this->survey->primaryKey, "full", false, false, $this->survey->language);
        $aTimingTableDefinition = array();
        $aTimingTableDefinition['id'] = $this->tableDefinition;
        foreach ($timingsfieldmap as $field=>$fielddata) {
            $aTimingTableDefinition[$field] = 'FLOAT';
        }
        $this->timingsTableDefinition = $aTimingTableDefinition;
    }



    private function prepareCollation(){
        // Specify case sensitive collations for the token
        $this->collation = '';
        if (Yii::app()->db->driverName == 'mysqli' || Yii::app()->db->driverName == 'mysql') {
            $this->collation = " COLLATE 'utf8mb4_bin'";
        }
        if (Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib' || Yii::app()->db->driverName == 'mssql') {
            $this->collation = " COLLATE SQL_Latin1_General_CP1_CS_AS";
        }
    }


    private function prepareSimulateQuery(){
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


    private function prepareResponsesTable(){
        $this->prepareCollation();
        //Check for any additional fields for this survey and create necessary fields (token and datestamp)
        $this->survey->fixInvalidQuestions();
        //Get list of questions for the base language
        $this->fieldMap = createFieldMap($this->survey, 'full', true, false, $this->survey->language);
        $this->prepareTableDefinition();
        $this->prepareSimulateQuery();
    }



    /**
     * @return array
     */
    private function createParticipantsTable(){
        $sTableName = $this->survey->responsesTableName;
        Yii::app()->loadHelper("database");
        try {
            Yii::app()->db->createCommand()->createTable($sTableName, $this->tableDefinition);
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
        } catch (Exception $e) {
            if (App()->getConfig('debug')) {
                $this->error = $e->getMessage();
            } else {
                $this->error = 'surveytablecreation';
            }
        }
        try {
            if (isset($aTableDefinition['token'])) {
                Yii::app()->db->createCommand()->createIndex("idx_survey_token_{$this->survey->primaryKey}_".rand(1, 50000), $sTableName, 'token');
            }
        } catch (\Exception $e) {
        }

        $this->createParticipantsTableKeys();

    }


    private function showEventMessages(){
        $success = $this->event->get('success');
        $message = $this->event->get('message');

        if ($success === false) {
            Yii::app()->user->setFlash('error', $message);
            $this->error = 'plugin';
        } else if (!empty($message)) {
            Yii::app()->user->setFlash('info', $message);
        }

    }

    private function createParticipantsTableKeys(){
        $sQuery = "SELECT autonumber_start FROM {{surveys}} WHERE sid={$this->survey->primaryKey}";
        $iAutoNumberStart = Yii::app()->db->createCommand($sQuery)->queryScalar();
        //if there is an autonumber_start field, start auto numbering here
        if ($iAutoNumberStart !== false && $iAutoNumberStart > 0) {
            if (Yii::app()->db->driverName == 'mssql' || Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib') {
                mssql_drop_primary_index($this->survey->responsesTableName);
                mssql_drop_constraint('id', $this->survey->responsesTableName);
                $sQuery = "ALTER TABLE {$this->survey->responsesTableName} drop column id ";
                Yii::app()->db->createCommand($sQuery)->execute();
                $sQuery = "ALTER TABLE {$this->survey->responsesTableName} ADD [id] int identity({$iAutoNumberStart},1)";
                Yii::app()->db->createCommand($sQuery)->execute();
                // Add back the primaryKey

                Yii::app()->db->createCommand()->addPrimaryKey('PRIMARY_'.rand(1, 50000), $this->survey->responsesTableName, 'id');
            } elseif (Yii::app()->db->driverName == 'pgsql') {
                $sQuery = "SELECT setval(pg_get_serial_sequence('{$this->survey->responsesTableName}', 'id'),{$iAutoNumberStart},false);";
                @Yii::app()->db->createCommand($sQuery)->execute();
            } else {
                $sQuery = "ALTER TABLE {$this->survey->responsesTableName} AUTO_INCREMENT = {$iAutoNumberStart}";
                @Yii::app()->db->createCommand($sQuery)->execute();
            }
        }

    }

    private function createTimingsTable(){
        $this->prepareTimingsTable();
        $sTableName = $this->survey->timingsTableName;
        try {
            Yii::app()->db->createCommand()->createTable($sTableName, $this->timingsTableDefinition);
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
        } catch (\Exception $e) {
            $this->error = 'timingstablecreation';
        }
    }


    private function createSurveyDirectory(){
        $iSurveyID = $this->survey->primaryKey;
        // create the survey directory where the uploaded files can be saved
        if ($this->createSurveyDir) {
            if (!file_exists(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files")) {
                if (!(mkdir(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files", 0777, true))) {
                    return false;
                } else {
                    file_put_contents(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files/index.html", '<html><head></head><body></body></html>');
                }
            }
        }
        return true;

    }


}