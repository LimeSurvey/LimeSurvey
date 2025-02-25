<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_Application;
use Permission;
use Survey;
use SurveyDeactivator;
use ArchivedTableSettings;
use SurveyLink;
use SavedControl;
use Response;

class SurveyDeactivate
{
    private Survey $survey;
    private Permission $permission;
    private SurveyDeactivator $surveyDeactivator;
    private LSYii_Application $app;
    /** @Inject("archivedTokenSettings") */
    private ArchivedTableSettings $archivedTokenSettings;
    /** @Inject("archivedTimingsSettings") */
    private ArchivedTableSettings $archivedTimingsSettings;
    /** @Inject("archivedResponseSettings") */
    private ArchivedTableSettings $archivedResponseSettings;
    private SurveyLink $surveyLink;
    private SavedControl $savedControl;

    public function __construct(
        Survey $survey,
        Permission $permission,
        SurveyDeactivator $surveyDeactivator,
        LSYii_Application $app,
        SurveyLink $surveyLink,
        SavedControl $savedControl
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
        $this->surveyDeactivator = $surveyDeactivator;
        $this->app = $app;
        $this->surveyLink = $surveyLink;
        $this->savedControl = $savedControl;
    }

    /**
     * @param int $surveyId
     * @param array $isOk
     * @return array
     * @throws PermissionDeniedException
     */
    public function deactivate(int $iSurveyID, $params = [])
    {
        if (!$this->permission->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update')) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
        $survey = $this->survey->findByPk($iSurveyID);
        $datestamp = time();
        $date = date('YmdHis', $datestamp); //'His' adds 24hours+minutes to name to allow multiple deactiviations in a day
        $DBDate = date('Y-m-d H:i:s', $datestamp);
        $userID = $this->app->user->getId();
        $aData = array();
        $aData['aSurveysettings'] = getSurveyInfo($iSurveyID);
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['topBar']['hide'] = true;
        $beforeDeactivate = $this->surveyDeactivator->setSurvey($survey)->beforeDeactivate();
        $result = [
            "beforeDeactivate" => [
                "success" => $beforeDeactivate->get('success'),
                "message" => $beforeDeactivate->get('message')
            ]
        ];
        if (!empty($result["beforeDeactivate"]["message"])) {
            return $result;
        }
        $result["surveyTableExists"] = tableExists('survey_' . $iSurveyID);
        if (!$result["surveyTableExists"]) {
            return $result;
        }
        if (!is_array($params) || (($params['ok'] ?? '') == '')) {
            if (!empty($this->app->session->get('sNewSurveyTableName'))) {
                $this->app->session->remove('sNewSurveyTableName');
            }
            $this->app->session->add('sNewSurveyTableName', $this->app->db->tablePrefix . "old_survey_{$iSurveyID}_{$date}");
            $aData['surveyid'] = $iSurveyID;
            $aData['date'] = $date;
            $aData['dbprefix'] = $this->app->db->tablePrefix;
            $aData['sNewSurveyTableName'] = $this->app->session->get('sNewSurveyTableName');
            $aData['step1'] = true;
        } else {
            //See if there is a tokens table for this survey
            if (tableExists("{{tokens_{$iSurveyID}}}")) {
                $this->archiveToken($iSurveyID, $date, $userID, $DBDate, $aData);
            }
            $this->handleSurveyTable($iSurveyID, $date, $aData, $userID, $DBDate);
            $this->handleTimingTable($iSurveyID, $date, $aData, $userID, $DBDate);
            $this->surveyDeactivator->afterDeactivate();
            $aData['surveyid'] = $iSurveyID;
            $this->app->db->schema->refresh();
            //after deactivation redirect to survey overview and show message...
            //$this->redirect(['surveyAdministration/view', 'surveyid' => $iSurveyID]);
            $this->app->session->remove('sNewSurveyTableName');
        }
        $result['aData'] = $aData;
        return $result;
    }

    /**
     * Marks a survey as expired
     * @param int $iSurveyID
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @return void
     */
    public function expire(int $iSurveyID)
    {
        if (!$this->permission->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
            throw new PermissionDeniedException('Access denied');
        }
        $this->survey->expire($iSurveyID);
    }

    /**
     * Archives token table
     *
     * @param int $iSurveyID
     * @param string $date
     * @param int $userID
     * @param string $DBDate
     * @param array &$aData
     *
     * @return void
     */
    protected function archiveToken($iSurveyID, $date, $userID, $DBDate, &$aData)
    {
        $toldtable = $this->app->db->tablePrefix . "tokens_{$iSurveyID}";
        $tnewtable = $this->app->db->tablePrefix . "old_tokens_{$iSurveyID}_{$date}";
        if ($this->app->db->getDriverName() == 'pgsql') {
            // Find out the trigger name for tid column
            $tidDefault = $this->app->db->createCommand("SELECT pg_get_expr(adbin, adrelid) as adsrc FROM pg_attribute JOIN pg_class ON (pg_attribute.attrelid=pg_class.oid) JOIN pg_attrdef ON(pg_attribute.attrelid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum) WHERE pg_class.relname='$toldtable' and pg_attribute.attname='tid'")->queryScalar();
            if (preg_match("/nextval\('(tokens_\d+_tid_seq\d*)'::regclass\)/", (string) $tidDefault, $matches)) {
                $oldSeq = $matches[1];
                $this->app->db->createCommand()->renameTable($oldSeq, $tnewtable . '_tid_seq');
                $setsequence = "ALTER TABLE " . $this->app->db->quoteTableName($toldtable) . " ALTER COLUMN tid SET DEFAULT nextval('{$tnewtable}_tid_seq'::regclass);";
                $this->app->db->createCommand($setsequence)->query();
            }
        }

        $this->app->db->createCommand()->renameTable($toldtable, $tnewtable);

        $this->archiveTable(
            $iSurveyID,
            $userID,
            "old_tokens_{$iSurveyID}_{$date}",
            'token',
            $DBDate,
            $aData['aSurveysettings']['tokenencryptionoptions'],
            json_encode($aData['aSurveysettings']['attributedescriptions'])
        );

        $aData['tnewtable'] = $tnewtable;
        $aData['toldtable'] = $toldtable;
    }

    /**
     * Archives a table
     *
     * @param int $iSurveyID
     * @param int $userID
     * @param string $tableName
     * @param string $tableType
     * @param string $DBDate
     * @param string $properties
     * @param string $attributes JSON encoded attributes
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function archiveTable($iSurveyID, $userID, $tableName, $tableType, $DBDate, $properties, $attributes = null)
    {
        switch ($tableType) {
            case 'token':
                $model = $this->archivedTokenSettings;
                break;
            case 'timings':
                $model = $this->archivedTimingsSettings;
                break;
            case 'response':
                $model = $this->archivedResponseSettings;
                break;
            default:
                throw new \InvalidArgumentException('Unknown table type: ' . $tableType);
        }
        $model->survey_id = $iSurveyID;
        $model->user_id = $userID;
        $model->tbl_name = $tableName;
        $model->tbl_type = $tableType;
        $model->created = $DBDate;
        $model->properties = $properties;
        if ($attributes) {
            $model->attributes = $attributes;
        }
        $model->save();
    }

    /**
     * Handles survey table
     *
     * @param int $iSurveyID
     * @param string $date
     * @param array &$aData
     * @param int $userID
     * @param string $DBDate
     *
     * @return void
     */
    protected function handleSurveyTable($iSurveyID, $date, &$aData, $userID, $DBDate)
    {
        // Reset the session of the survey when deactivating it
        killSurveySession($iSurveyID);
        //Remove any survey_links to the CPDB
        $this->surveyLink->deleteLinksBySurvey($iSurveyID);
        // IF there are any records in the saved_control table related to this survey, they have to be deleted
        $this->savedControl->deleteSomeRecords(array('sid' => $iSurveyID)); //Yii::app()->db->createCommand($query)->query();
        $sOldSurveyTableName = $this->app->db->tablePrefix . "survey_{$iSurveyID}";
        if (empty($this->app->session->get('sNewSurveyTableName'))) {
            $this->app->session->add('sNewSurveyTableName', $this->app->db->tablePrefix . "old_survey_{$iSurveyID}_{$date}");
        }
        $sNewSurveyTableName = $this->app->session->get('sNewSurveyTableName');
        $aData['sNewSurveyTableName'] = $sNewSurveyTableName;

        $query = "SELECT id FROM " . $this->app->db->quoteTableName($sOldSurveyTableName) . " ORDER BY id desc";
        $sLastID = $this->app->db->createCommand($query)->limit(1)->queryScalar();
        //Update the autonumber_start in the survey properties
        $new_autonumber_start = $sLastID + 1;
        $survey = $this->survey->findByAttributes(array('sid' => $iSurveyID));
        $survey->autonumber_start = $new_autonumber_start;
        $survey->save();
        if ($this->app->db->getDriverName() == 'pgsql') {
            $idDefault = $this->app->db->createCommand("SELECT pg_get_expr(pg_attrdef.adbin, pg_attrdef.adrelid) FROM pg_attribute JOIN pg_class ON (pg_attribute.attrelid=pg_class.oid) JOIN pg_attrdef ON(pg_attribute.attrelid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum) WHERE pg_class.relname='$sOldSurveyTableName' and pg_attribute.attname='id'")->queryScalar();
            if (preg_match("/nextval\('(survey_\d+_id_seq\d*)'::regclass\)/", (string) $idDefault, $matches)) {
                $oldSeq = $matches[1];
                $this->app->db->createCommand()->renameTable($oldSeq, $sNewSurveyTableName . '_id_seq');
                $setsequence = "ALTER TABLE " . $this->app->db->quoteTableName($sOldSurveyTableName) . " ALTER COLUMN id SET DEFAULT nextval('{{{$sNewSurveyTableName}}}_id_seq'::regclass);";
                $this->app->db->createCommand($setsequence)->query();
            }
        }

        $this->app->db->createCommand()->renameTable($sOldSurveyTableName, $sNewSurveyTableName);
        $this->archiveTable($iSurveyID, $userID, "old_tokens_{$iSurveyID}_{$date}", 'response', $DBDate, json_encode(Response::getEncryptedAttributes($iSurveyID)));
        // Load the active record again, as there have been sporadic errors with the dataset not being updated
        $survey = $this->survey->findByAttributes(array('sid' => $iSurveyID));
        $survey->scenario = 'activationStateChange';
        $survey->active = 'N';
        $survey->save();
    }

    /**
     * Handles survey table
     *
     * @param int $iSurveyID
     * @param string $date
     * @param array &$aData
     * @param int $userID
     * @param string $DBDate
     *
     * @return void
     */
    protected function handleTimingTable($iSurveyID, $date, &$aData, $userID, $DBDate)
    {
        $prow = $this->survey->find('sid = :sid', array(':sid' => $iSurveyID));
        if ($prow->savetimings == "Y") {
            $sOldTimingsTableName = $this->app->db->tablePrefix . "survey_{$iSurveyID}_timings";
            $sNewTimingsTableName = $this->app->db->tablePrefix . "old_survey_{$iSurveyID}_timings_{$date}";
            $this->app->db->createCommand()->renameTable($sOldTimingsTableName, $sNewTimingsTableName);
            $aData['sNewTimingsTableName'] = $sNewTimingsTableName;
        }
        $this->archiveTable($iSurveyID, $userID, "old_survey_{$iSurveyID}_timings_{$date}", 'timings', $DBDate, '');
    }
}
