<?php


class SurveyActivator
{
    /** @var Survey */
    private $survey;
    /** @var boolean */
    public $isSimulation;

    public function __construct($survey)
    {
        $this->survey = $survey;
    }

    public function activate(){
        $event = new PluginEvent('beforeSurveyActivate');
        $iSurveyID = $this->survey->primaryKey;
        $simulate = $this->isSimulation;
        $oSurvey = $this->survey;

        $event->set('surveyId', $iSurveyID);
        $event->set('simulate', $simulate);
        App()->getPluginManager()->dispatchEvent($event);
        $success = $event->get('success');
        $message = $event->get('message');
        if ($success === false) {
            Yii::app()->user->setFlash('error', $message);
            return array('error' => 'plugin');
        } else if (!empty($message)) {
            Yii::app()->user->setFlash('info', $message);
        }

        $aTableDefinition = array();
        $bCreateSurveyDir = false;
        // Specify case sensitive collations for the token
        $sCollation = '';
        if (Yii::app()->db->driverName == 'mysqli' || Yii::app()->db->driverName == 'mysql') {
            $sCollation = " COLLATE 'utf8mb4_bin'";
        }
        if (Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib' || Yii::app()->db->driverName == 'mssql') {
            $sCollation = " COLLATE SQL_Latin1_General_CP1_CS_AS";
        }
        //Check for any additional fields for this survey and create necessary fields (token and datestamp)
        $oSurvey->fixInvalidQuestions();
        //Get list of questions for the base language
        $sFieldMap = createFieldMap($oSurvey, 'full', true, false, $oSurvey->language);
        //For each question, create the appropriate field(s)
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
                    $bCreateSurveyDir = true;
                    if (strpos($aRow['fieldname'], "_")) {
                        $aTableDefinition[$aRow['fieldname']] = "integer";
                    } else {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "ipaddress":
                    if ($oSurvey->ipaddr == "Y") {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "url":
                    if ($oSurvey->refurl == "Y") {
                        $aTableDefinition[$aRow['fieldname']] = "text";
                    }
                    break;
                case "token":
                    $aTableDefinition[$aRow['fieldname']] = 'string(35)'.$sCollation;
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
                        array('qid' => $aRow['qid'], 'language'=>Survey::model()->findByPk($iSurveyID)->language)
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
            if ($oSurvey->anonymized == 'N' && !array_key_exists('token', $aTableDefinition)) {
                $aTableDefinition['token'] = 'string(35)'.$sCollation;
            }
            if ($simulate) {
                $tempTrim = trim($aTableDefinition);
                $brackets = strpos($tempTrim, "(");
                if ($brackets === false) {
                    $type = substr($tempTrim, 0, 2);
                } else {
                    $type = substr($tempTrim, 0, 2);
                }
                $arrSim[] = array($type);
            }
        }

        if ($simulate) {
            return array('dbengine'=>Yii::app()->db->getDriverName(), 'dbtype'=>Yii::app()->db->driverName, 'fields'=>$arrSim);
        }

        // If last question is of type MCABCEFHP^QKJR let's get rid of the ending coma in createsurvey

        $sTableName = "{{survey_{$iSurveyID}}}";
        Yii::app()->loadHelper("database");
        try {
            Yii::app()->db->createCommand()->createTable($sTableName, $aTableDefinition);
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
        } catch (CDbException $e) {
            if (App()->getConfig('debug')) {
                return array('error'=>$e->getMessage());
            } else {
                return array('error'=>'surveytablecreation');
            }
        }
        try {
            if (isset($aTableDefinition['token'])) {
                Yii::app()->db->createCommand()->createIndex("idx_survey_token_{$iSurveyID}_".rand(1, 50000), $sTableName, 'token');
            }
        } catch (CDbException $e) {
        }

        $sQuery = "SELECT autonumber_start FROM {{surveys}} WHERE sid={$iSurveyID}";
        $iAutoNumberStart = Yii::app()->db->createCommand($sQuery)->queryScalar();
        //if there is an autonumber_start field, start auto numbering here
        if ($iAutoNumberStart !== false && $iAutoNumberStart > 0) {
            if (Yii::app()->db->driverName == 'mssql' || Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib') {
                mssql_drop_primary_index('survey_'.$iSurveyID);
                mssql_drop_constraint('id', 'survey_'.$iSurveyID);
                $sQuery = "ALTER TABLE {{survey_{$iSurveyID}}} drop column id ";
                Yii::app()->db->createCommand($sQuery)->execute();
                $sQuery = "ALTER TABLE {{survey_{$iSurveyID}}} ADD [id] int identity({$iAutoNumberStart},1)";
                Yii::app()->db->createCommand($sQuery)->execute();
                // Add back the primaryKey

                Yii::app()->db->createCommand()->addPrimaryKey('PRIMARY_'.rand(1, 50000), $oSurvey->responsesTableName, 'id');
            } elseif (Yii::app()->db->driverName == 'pgsql') {
                $sQuery = "SELECT setval(pg_get_serial_sequence('{{survey_{$iSurveyID}}}', 'id'),{$iAutoNumberStart},false);";
                @Yii::app()->db->createCommand($sQuery)->execute();
            } else {
                $sQuery = "ALTER TABLE {{survey_{$iSurveyID}}} AUTO_INCREMENT = {$iAutoNumberStart}";
                @Yii::app()->db->createCommand($sQuery)->execute();
            }
        }

        if ($oSurvey->savetimings == "Y") {
            $timingsfieldmap = createTimingsFieldMap($iSurveyID, "full", false, false, $oSurvey->language);

            $aTimingTableDefinition = array();
            $aTimingTableDefinition['id'] = $aTableDefinition['id'];
            foreach ($timingsfieldmap as $field=>$fielddata) {
                $aTimingTableDefinition[$field] = 'FLOAT';
            }

            $sTableName = "{{survey_{$iSurveyID}_timings}}";
            try {
                Yii::app()->db->createCommand()->createTable($sTableName, $aTimingTableDefinition);
                Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
            } catch (CDbException $e) {
                return array('error'=>'timingstablecreation');
            }

        }
        $aResult = array(
            'status' => 'OK',
            'pluginFeedback' => $event->get('pluginFeedback')
        );
        // create the survey directory where the uploaded files can be saved
        if ($bCreateSurveyDir) {
            if (!file_exists(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files")) {
                if (!(mkdir(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files", 0777, true))) {
                    $aResult['warning'] = 'nouploadsurveydir';
                } else {
                    file_put_contents(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files/index.html", '<html><head></head><body></body></html>');
                }
            }
        }
        $sQuery = "UPDATE {{surveys}} SET active='Y' WHERE sid=".$iSurveyID;
        Yii::app()->db->createCommand($sQuery)->query();
        return $aResult;

    }


}