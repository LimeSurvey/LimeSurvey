<?php

/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
  *     Files Purpose: lots of common functions
 */
class SurveyDynamic extends LSActiveRecord
{
    /** @var string $completed_filter */
    public $completed_filter;
    /** @var string $firstname_filter */
    public $firstname_filter;
    /** @var string $lastname_filter */
    public $lastname_filter;
    /** @var string $email_filter */
    public $email_filter;
    /** @var integer $lastpage */
    public $lastpage;

    /** @var int $sid */
    protected static $sid = 0;

    /** @var Survey $survey */
    protected static $survey;

    /** @var  boolean $bHaveToken */
    protected $bHaveToken;

    /**
     * @inheritdoc
     * @return SurveyDynamic
     * @psalm-suppress ParamNameMismatch Ignore that $sid is $className in parent class
     */
    public static function model($sid = null)
    {
        $refresh = false;
        $survey = Yii::app()->db->createCommand()
            ->select('{{surveys.sid}}')
            ->from('{{surveys}}')
            ->where('{{surveys.sid}} = :sid', array(':sid' => $sid))
            ->queryRow();
        if ($survey) {
            self::sid($survey['sid']);
            $refresh = true;
        }

        /** @var self $model */
        $model = parent::model(__CLASS__);

        //We need to refresh if we changed sid
        if ($refresh === true) {
            $model->refreshMetaData();
        }

        return $model;
    }

    /**
     * Sets the survey ID for the next model
     *
     * @static
     * @access public
     * @param int $sid
     * @return void
     */
    public static function sid($sid)
    {
        self::$sid = (int) $sid;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{responses_' . self::$sid . '}}';
    }

    /** @inheritdoc */
    public function relations()
    {
        if ($this->getbHaveToken()) {
            TokenDynamic::sid(self::$sid);
            return array(
                'survey'   => array(self::HAS_ONE, 'Survey', array(), 'condition' => ('sid = ' . self::$sid)),
                'tokens'   => array(self::HAS_ONE, 'TokenDynamic', array('token' => 'token')),
                'saved_control'   => array(self::HAS_ONE, 'SavedControl', array('srid' => 'id'), 'condition' => ('sid = ' . self::$sid))
            );
        } else {
            return array(
                'saved_control'   => array(self::HAS_ONE, 'SavedControl', array('srid' => 'id'), 'condition' => ('sid = ' . self::$sid))
            );
        }
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Insert records from $data array
     *
     * @access public
     * @param array $data
     * @return boolean
     * @deprecated Use setAttributes() and encryptSave()
     */
    public function insertRecords($data)
    {
        $record = new self();
        foreach ($data as $k => $v) {
            $search = array('`', "'");
            $k = str_replace($search, '', $k);
            $v = $v == null ? null : str_replace($search, '', (string) $v);
            $record->$k = $v;
        }

        $res = $record->encryptSave();
        return $res ? $record->id : $res;
    }

    /**
     * Deletes some records from survey's table
     * according to specific condition
     *
     * @static
     * @access public
     * @param array|bool $condition
     * @return int|CDbCriteria
     */
    public static function deleteSomeRecords($condition = false)
    {
        $survey = new SurveyDynamic();
        $criteria = new CDbCriteria();

        if ($condition) {
            foreach ($condition as $column => $value) {
                return $criteria->addCondition($column . "=`" . $value . "`");
            }
        }

        return $survey->deleteAll($criteria);
    }

    /**
     * Return criteria updated with the ones needed for including results from the timings table
     *
     * @param array $condition
     * @return CDbCriteria
     */
    public function addTimingCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);

        if ($criteria->select == '*') {
            $criteria->select = 't.*';
        }
        $alias = $this->getTableAlias();

        $newCriteria->join = "LEFT JOIN " . $this->survey->tokensTableName . " timings ON $alias.id = timings.id";
        $newCriteria->select = 'timings.*'; // Otherwise we don't get records from the survey participants table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    /**
     * Return criteria updated with the ones needed for including results from the survey participants table
     *
     * @param string $condition
     * @return CDbCriteria
     */
    public function addTokenCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);
        $aSelectFields = Yii::app()->db->schema->getTable($this->survey->responsesTableName)->getColumnNames();
        $aSelectFields = array_diff($aSelectFields, array('token'));
        $aSelect = array();
        $alias = $this->getTableAlias();
        foreach ($aSelectFields as $sField) {
            $aSelect[] = "$alias." . Yii::app()->db->schema->quoteColumnName($sField);
        }
        $aSelectFields = $aSelect;
        $aSelectFields[] = "$alias.token";

        if ($criteria->select == '*') {
            $criteria->select = $aSelectFields;
        }

        $newCriteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} tokens ON $alias.token = tokens.token";

        $aTokenFields = Yii::app()->db->schema->getTable($this->survey->tokensTableName)->getColumnNames();
        $aTokenFields = array_diff($aTokenFields, array('token'));

        $newCriteria->select = $aTokenFields; // Otherwise we don't get records from the survey participants table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    /**
     * @param integer $sid
     * @return array
     */
    public static function countAllAndPartial($sid)
    {
        $select = array(
            'count(*) AS cntall',
            'sum(CASE
                 WHEN ' . Yii::app()->db->quoteColumnName('submitdate') . ' IS NULL THEN 1
                          ELSE 0
                 END) AS cntpartial',
            );
        $result = Yii::app()->db->createCommand()->select($select)->from('{{responses_' . $sid . '}}')->queryRow();
        return $result;
    }

    /**
     * Return true if actual survey is completed
     *
     * @param integer $srid : actual save survey ID
     *
     * @return boolean
     */
    public function isCompleted($srid)
    {
        static $resultCache = array();

        $sid = self::$sid;
        if (array_key_exists($sid, $resultCache) && array_key_exists($srid, $resultCache[$sid])) {
            return $resultCache[$sid][$srid];
        }
        $completed = false;

        if (Yii::app()->db->schema->getTable($this->tableName())) {
            $data = Yii::app()->db->createCommand()
                ->select("submitdate")
                ->from($this->tableName())
                ->where('id=:id', array(':id' => $srid))
                ->queryRow();
            if ($data && $data['submitdate']) {
                $completed = true;
            }
        }
        $resultCache[$sid][$srid] = $completed;
        return $completed;
    }


    /**
     * For grid list
     * @return string
     */
    public function getCompleted()
    {
        return ($this->submitdate != '') ? '<span class="text-success ri-check-fill"></span>' : '<span class="text-danger ri-close-fill"></span>';
    }

    /**
     * Return the buttons columns
     * This is the button column for response table
     * @see https://www.yiiframework.com/doc/api/1.1/CButtonColumn
     * @see https://bugs.limesurvey.org/view.php?id=14219
     * @see https://bugs.limesurvey.org/view.php?id=14222: When deleting a single response : all page is reloaded (not only grid)
     * @return string
     */
    public function getGridButtons()
    {
        $sBrowseLanguage = sanitize_languagecode(Yii::app()->request->getParam('browseLang', ''));

        $permissionReponseUpdate = Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'update');
        $permissionReponseDelete = Permission::model()->hasSurveyPermission(self::$sid, 'responses', 'delete');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('View response details'),
            'iconClass'        => 'ri-eye-fill',
            'url'              => App()->createUrl(
                "responses/view",
                [
                    "surveyId" => self::$sid,
                    "id" => $this->id,
                    "browseLang" => $sBrowseLanguage
                ]
            ),
        ];
        $dropdownItems[] = [
            'title'            => gT('View response details as queXML PDF'),
            'iconClass'        => 'ri-file-pdf-line',
            'url'              => App()->createUrl(
                "responses/viewquexmlpdf",
                [
                    "surveyId" => self::$sid,
                    "id" => $this->id,
                    "browseLang" => $sBrowseLanguage
                ]
            ),
        ];
        $dropdownItems[] = [
            'title'            => gT('Edit this response'),
            'iconClass'        => 'ri-pencil-fill text-success',
            'url'              => App()->createUrl(
                "admin/dataentry/sa/editdata/subaction/edit",
                [
                    "surveyId" => self::$sid,
                    "id" => $this->id,
                    "browseLang" => $sBrowseLanguage
                ]
            ),
            'enabledCondition' => $permissionReponseUpdate,
        ];
        $fileExists = Response::model(self::$sid)->findByPk($this->id)->someFileExists();
        $dropdownItems[] = [
            'title'            => gT('Download all response files'),
            'iconClass'        => 'ri-download-fill text-success',
            'url'              => App()->createUrl(
                "responses/downloadfiles",
                ["surveyId" => self::$sid, "responseIds" => $this->id]
            ),
            'enabledCondition' => $permissionReponseUpdate && hasFileUploadQuestion(self::$sid) && $fileExists,
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete all response files'),
            'iconClass'        => 'ri-attachment-2 text-danger',
            'enabledCondition' => $permissionReponseUpdate && hasFileUploadQuestion(self::$sid) && $fileExists,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => "#confirmation-modal",
                'data-btnclass'  => 'btn-danger',
                'data-title'     => gT('Delete all response files'),
                'data-btntext'   => gT('Delete'),
                'data-post-url'  => App()->createUrl("responses/deleteAttachments"),
                'data-post-datas' => json_encode(['surveyId' => self::$sid, 'responseId' => $this->id]),
                'data-message'   => gT("Do you want to delete all files of this response?"),
            ]
        ];

        $dropdownItems[] = [
            'title'            => gT('Delete this response'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permissionReponseDelete,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => "#confirmation-modal",
                'data-btnclass'  => 'btn-danger',
                'data-title'     => gT('Delete this response'),
                'data-btntext'   => gT('Delete'),
                'data-post-url'  => App()->createUrl("responses/deleteSingle"),
                'data-post-datas' => json_encode(['surveyId' => self::$sid, 'responseId' => $this->id]),
                'data-message'   => gT("Do you want to delete this response?") . '<br/>' .
                    gT("Please note that if you delete an incomplete response during a running survey, the participant will not be able to complete it."),
            ]
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * Get buttons HTML for response browse view.
     * @deprecated , use getGridButtons ,
     *
     * @return string HTML
     */
    public function getButtons()
    {
        return "";
    }


    /**
     * @param string $colName
     * @param string $sLanguage
     * @param string $base64jsonFieldMap
     * @return string
     */
    public function getExtendedData($colName, $sLanguage, $base64jsonFieldMap)
    {
        $oFieldMap = json_decode(base64_decode($base64jsonFieldMap));
        $value     = $this->$colName;

        $sFullValue = viewHelper::flatten(getExtendedAnswer(self::$sid, $oFieldMap->fieldname, $value, $sLanguage));
        if (strlen((string) $sFullValue) > 50) {
            $sElipsizedValue = ellipsize($sFullValue, $this->ellipsize_question_value);
            $sValue          = '<span data-bs-toggle="tooltip" data-bs-placement="left" title="' . quoteText($sFullValue) . '">' . $sElipsizedValue . '</span>';
        } else {
            $sValue          = $sFullValue;
        }

        // Upload question
        if ($oFieldMap->type == Question::QT_VERTICAL_FILE_UPLOAD && strpos((string) $oFieldMap->fieldname, 'filecount') === false) {
            $sSurveyEntry = "<table class='table table-condensed upload-question'>";
            $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($oFieldMap->qid);
            $aFilesInfo = json_decode_ls($this->$colName);
            if (!empty($aFilesInfo)) {
                foreach ($aFilesInfo as $iFileIndex => $fileInfo) {
                    if (empty($fileInfo)) {
                        continue;
                    }
                    $sSurveyEntry .= '<tr>';
                    $url = App()->createUrl("responses/downloadfile", ["surveyId" => self::$sid, "responseId" => $this->id, "qid" => $oFieldMap->qid, "index" => $iFileIndex]);
                    $filename = CHtml::encode(rawurldecode($fileInfo['name']));
                    $size = "";
                    if ($fileInfo['size'] && strval(floatval($fileInfo['size'])) == strval($fileInfo['size'])) {
                        // avoid to throw PHP error if size is invalid
                        $size = sprintf('%s Mb', round($fileInfo['size'] / 1000, 2));
                    }
                    $sSurveyEntry .= '<td>' . CHtml::link($filename, $url) . '</td>';
                    $sSurveyEntry .= '<td>' . $size . '</td>';
                    if ($aQuestionAttributes['show_title']) {
                        if (!isset($fileInfo['title'])) {
                            $fileInfo['title'] = '';
                        }
                        $sSurveyEntry .= '<td>' . htmlspecialchars((string) $fileInfo['title'], ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    if ($aQuestionAttributes['show_comment']) {
                        if (!isset($fileInfo['comment'])) {
                            $fileInfo['comment'] = '';
                        }
                        $sSurveyEntry .= '<td>' . htmlspecialchars((string) $fileInfo['comment'], ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    $sSurveyEntry .= '</tr>';
                }
            }
            $sSurveyEntry .= '</table>';
            $sValue = $sSurveyEntry;
        }

        return $sValue;
    }

    /**
     * Return true if actual response exist in database
     *
     * @param integer $srid : actual save survey ID
     *
     * @return boolean
     */
    public function exist($srid)
    {
        $exist = false;

        if (Yii::app()->db->schema->getTable($this->tableName())) {
            $data = Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where('id=:id', array(':id' => $srid))
                ->queryRow();
            if ($data) {
                $exist = true;
            }
        }
        return $exist;
    }

    /**
     * Return next id if next response exist in database
     *
     * @param integer $srId : actual save survey ID
     * @param boolean $useFilterState
     *
     * @return integer
     */
    public function next($srId, $useFilterState = false)
    {
        $next = false;
        if ($useFilterState && incompleteAnsFilterState() == 'incomplete') {
            $whereFilterState = 'submitdate IS NULL';
        } elseif ($useFilterState && incompleteAnsFilterState() == 'complete') {
            $whereFilterState = 'submitdate IS NOT NULL';
        } else {
            $whereFilterState = '1=1';
        }

        if (Yii::app()->db->schema->getTable($this->tableName())) {
            $data = Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and', $whereFilterState, 'id > :id'), array(':id' => $srId))
                ->order('id ASC')
                ->queryRow();
            if ($data) {
                $next = $data['id'];
            }
        }
        return $next;
    }

    /**
     * Return previous id if previous response exist in database
     *
     * @param integer $srId : actual save survey ID
     * @param boolean $useFilterState
     *
     * @return integer
     */
    public function previous($srId, $useFilterState = false)
    {
        $previous = false;
        if ($useFilterState && incompleteAnsFilterState() == 'incomplete') {
            $whereFilterState = 'submitdate IS NULL';
        } elseif ($useFilterState && incompleteAnsFilterState() == 'complete') {
            $whereFilterState = 'submitdate IS NOT NULL';
        } else {
            $whereFilterState = '1=1';
        }

        if (Yii::app()->db->schema->getTable($this->tableName())) {
            $data = Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and', $whereFilterState, 'id < :id'), array(':id' => $srId))
                ->order('id DESC')
                ->queryRow();
            if ($data) {
                $previous = $data['id'];
            }
        }
        return $previous;
    }

    /**
     * Function that returns a time-line of the surveys submissions
     *
     * @param string $sType
     * @param string $dStart
     * @param string $dEnd
     *
     * @access public
     * @return array|boolean
     */
    public function timeline($sType, $dStart, $dEnd)
    {

        $sid = self::$sid;
        $oSurvey = Survey::model()->findByPk($sid);
        if ($oSurvey['datestamp'] != 'Y') {
            return false;
        } else {
            $criteria = new CDbCriteria();
            $criteria->select = 'submitdate';
            $criteria->addCondition('submitdate >= :dstart');
            $criteria->addCondition('submitdate <= :dend');
            $criteria->order = "submitdate";

            $criteria->params[':dstart'] = $dStart;
            $criteria->params[':dend'] = $dEnd;
            $oResult = $this->findAll($criteria);

            if ($sType == "hour") {
                $dFormat = "Y-m-d_G";
            } else {
                $dFormat = "Y-m-d";
            }

            $aRes = array();
            foreach ($oResult as $sResult) {
                $aRes[] = date($dFormat, strtotime((string) $sResult['submitdate']));
            }

            return array_count_values($aRes);
        }
    }

    /**
     * @return bool
     */
    private function getbHaveToken()
    {
        if (!isset($this->bHaveToken)) {
            $this->bHaveToken = tableExists('tokens_' . self::$sid) && Permission::model()->hasSurveyPermission(self::$sid, 'tokens', 'read'); // Boolean : show (or not) the token;
        }
        return $this->bHaveToken;
    }


    /**
     * @return string
     */
    public function getFirstNameForGrid()
    {
        // decrypt token information ( if needed )
        $tokens = $this->tokens;
        if (is_object($tokens)) {
            if (!empty($tokens)) {
                $tokens->decrypt();
            }
            return $tokens->firstname;
        }
    }

    /**
     * @return string
     */
    public function getLastNameForGrid()
    {
        // Last name is already decrypted in getFirstNameForGrid method, if we do it again it would try to decrypt it again ( and fail )
        $tokens = $this->tokens;
        if (is_object($tokens)) {
            return $tokens->lastname;
        }
    }

    /**
     * @return string
     */
    public function getTokenForGrid()
    {
        if (is_object($this->tokens) && !is_null($this->tokens->tid)) {
            $sToken = "<a class='btn btn-outline-secondary btn-xs edit-token' href='#' data-sid='" . self::$sid . "' data-tid='" . $this->tokens->tid . "'  data-url='" . App()->createUrl("admin/tokens", array("sa" => "edit", "iSurveyId" => self::$sid, "iTokenId" => $this->tokens->tid, 'ajax' => 'true')) . "' data-bs-toggle='tooltip' title='" . gT("Edit this survey participant") . "'>" . CHtml::encode($this->token) . "&nbsp;&nbsp;&nbsp;<span class='ri-pencil-fill'></span></a>";
        } else {
            $sToken = '<span class="badge rounded-pill">' . CHtml::encode($this->token) . '</span>';
        }

        return $sToken;
    }

    /**
     * Get the list of default columns for surveys
     * @return string[]
     */
    public function getDefaultColumns()
    {
        return array('id', 'token', 'submitdate', 'lastpage', 'startlanguage', 'completed', 'seed');
    }

    /**
     * Define what value to use to ellipsize the headers of the grid
     * It's using user state/default config, like for pagination
     * @see: http://www.yiiframework.com/wiki/324/cgridview-keep-state-of-page-and-sort/
     * @see: http://www.yiiframework.com/forum/index.php?/topic/8994-dropdown-for-pagesize-in-cgridview
     */
    // phpcs:ignore
    public function getEllipsize_header_value()
    {
        return Yii::app()->user->getState('defaultEllipsizeHeaderValue', Yii::app()->params['defaultEllipsizeHeaderValue']);
    }

    /**
     * Define what value to use to ellipsize the question in the grid
     * It's using user state/default config, like for pagination.
     * @see: http://www.yiiframework.com/wiki/324/cgridview-keep-state-of-page-and-sort/
     * @see: http://www.yiiframework.com/forum/index.php?/topic/8994-dropdown-for-pagesize-in-cgridview
     */
    // phpcs:ignore
    public function getEllipsize_question_value()
    {
        return Yii::app()->user->getState('defaultEllipsizeQuestionValue', Yii::app()->params['defaultEllipsizeQuestionValue']);
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {

        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $criteria = new LSDbCriteria();
        $sort     = new CSort();
        $sort->defaultOrder = 'id ASC';

        // Make all the model's columns sortable (default behaviour)
        $sort->attributes = array(
            '*',
        );

        // Join the survey participants table and filter tokens if needed
        if ($this->bHaveToken && $this->survey->anonymized != 'Y') {
            $this->joinWithToken($criteria, $sort);
        }

        // Basic filters
        $criteria->compare('t.lastpage', empty($this->lastpage) ? null : $this->lastpage, false);
        $criteria->compare('t.id', empty($this->id) ? null : $this->id, false);
        $criteria->compare('t.submitdate', $this->submitdate, true);
        $criteria->compare('t.startlanguage', $this->startlanguage, true);

        // Completed filters
        if ($this->completed_filter == "Y") {
            $criteria->addCondition('t.submitdate IS NOT NULL');
        }

        if ($this->completed_filter == "N") {
            $criteria->addCondition('t.submitdate IS NULL');
        }

        // When selection of responses come from statistics
        // TODO: This provide a first step to enable the old jQgrid selector system, and could be use for users and tokens
        if (Yii::app()->user->getState('sql_' . self::$sid) != null) {
            $criteria->addCondition(Yii::app()->user->getState('sql_' . self::$sid));
        }

        $this->filterColumns($criteria);


        $dataProvider = new LSCActiveDataProvider('SurveyDynamic', array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
        return $dataProvider;
    }

    /**
     * @param CDbCriteria $criteria
     * @param CSort $sort
     * @return void
     */
    protected function joinWithToken(CDbCriteria $criteria, CSort $sort)
    {
        $criteria->compare('t.token', $this->token, true);
        $criteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} as tokens ON t.token = tokens.token";
        $criteria->compare('tokens.firstname', $this->firstname_filter, true);
        $criteria->compare('tokens.lastname', $this->lastname_filter, true);
        $criteria->compare('tokens.email', $this->email_filter, true);

        // Add the related token model's columns sortable
        $aSortVirtualAttributes = array(
            'tokens.firstname' => array(
                'asc' => 'tokens.firstname ASC',
                'desc' => 'tokens.firstname DESC',
            ),
            'tokens.lastname' => array(
                'asc' => 'lastname ASC',
                'desc' => 'lastname DESC'
            ),
            'tokens.email' => array(
                'asc' => 'email ASC',
                'desc' => 'email DESC'
            ),
        );

        $sort->attributes = array_merge($sort->attributes, $aSortVirtualAttributes);
    }

    /**
     * Loop through columns and add filter if any value is given for this column
     * Used in responses grid
     * @param CdbCriteria $criteria
     * @return void
     */
    protected function filterColumns(CDbCriteria $criteria)
    {
        $dateFormatDetails = getDateFormatData(Yii::app()->session['dateformat']);

        // Filters for responses
        foreach ($this->metaData->columns as $column) {
            $isNotDefaultColumn = !in_array($column->name, $this->defaultColumns);
            if ($isNotDefaultColumn) {
                $c1 = (string) $column->name;
                $columnHasValue = !empty($this->$c1);
                if ($columnHasValue) {
                    $isDatetime = strpos((string) $column->dbType, 'timestamp') !== false || strpos((string) $column->dbType, 'datetime') !== false;
                    if ($column->dbType == 'decimal' || substr((string) $column->dbType, 0, 7) == 'numeric') {
                        $this->$c1 = (float) $this->$c1;
                        $criteria->compare(Yii::app()->db->quoteColumnName($c1), $this->$c1, false);
                    } elseif ($isDatetime) {
                        $s = DateTime::createFromFormat($dateFormatDetails['phpdate'], $this->$c1);
                        if ($s === false) {
                            // This happens when date is in wrong format
                            continue;
                        }
                        $s2 = $s->format('Y-m-d');
                        $criteria->addCondition('cast(' . Yii::app()->db->quoteColumnName($c1) . ' as date) = \'' . $s2 . '\'');
                    } else {
                        $criteria->compare(Yii::app()->db->quoteColumnName($c1), $this->$c1, true);
                    }
                }
            }
        }
    }

    /**
     * Get an array to find question data responsively
     * This should be part of the question object.
     * And in future development this should be part of the specific question type object
     *
     * @param Question $oQuestion
     * @param SurveyDynamic $oResponses
     * @param boolean $bHonorConditions
     * @param boolean $subquestion
     * @param boolean $getCommentOnly If should only returns the "comments" or "other" response.
     * @return array | boolean
     */
    public function getQuestionArray($oQuestion, $oResponses, $bHonorConditions, $subquestion = false, $getCommentOnly = false, $sLanguage = null)
    {

        $attributes = QuestionAttribute::model()->getQuestionAttributes($oQuestion->qid);

        if (
            !(LimeExpressionManager::QuestionIsRelevant($oQuestion->qid) && $bHonorConditions == true)
            || (is_array($attributes) && $attributes['hidden'] === 1)
        ) {
            return false;
        }

        // Use survey language is no language is specified
        if (empty($sLanguage)) {
            $sLanguage = $oQuestion->survey->language;
        }

        $aQuestionAttributes = $oQuestion->attributes;
        $aQuestionAttributes['language'] = $sLanguage;

        $aQuestionAttributes['questionSrc'] = $oQuestion->questionl10ns[$sLanguage]->question;
        $result = LimeExpressionManager::ProcessString($oQuestion->questionl10ns[$sLanguage]->question, 40, null, 1, 1);
        $aQuestionAttributes['question'] = $result;

        $aQuestionAttributes['helpSrc'] = $oQuestion->questionl10ns[$sLanguage]->help;
        $result = LimeExpressionManager::ProcessString($oQuestion->questionl10ns[$sLanguage]->help, 40, null, 1, 1);
        $aQuestionAttributes['help'] = $result;


        $aQuestionAttributes['questionSrc'] = $oQuestion->questionl10ns[$sLanguage]->question;
        $result = LimeExpressionManager::ProcessString($oQuestion->questionl10ns[$sLanguage]->question, 40, null, 1, 1);
        $aQuestionAttributes['question'] = $result;

        $aQuestionAttributes['helpSrc'] = $oQuestion->questionl10ns[$sLanguage]->help;
        $result = LimeExpressionManager::ProcessString($oQuestion->questionl10ns[$sLanguage]->help, 40, null, 1, 1);
        $aQuestionAttributes['help'] = $result;

        if (count($oQuestion->subquestions) > 0) {
            $aQuestionAttributes['subquestions'] = array();
            foreach ($oQuestion->subquestions as $oSubquestion) {
                //dont collect scale_id > 0
                if ($oSubquestion->scale_id > 0) {
                    continue;
                }

                $subQuestionArray = $this->getQuestionArray($oSubquestion, $oResponses, $bHonorConditions, true, false, $sLanguage);
                if ($oQuestion->type == "P") {
                    $subQuestionArray['comment'] = $this->getQuestionArray($oSubquestion, $oResponses, $bHonorConditions, true, true, $sLanguage);
                }

                $aQuestionAttributes['subquestions'][$oSubquestion->qid] = $subQuestionArray;
            }
            //Get other options
            if (in_array($oQuestion->type, ["M", "P"]) && $oQuestion->other == "Y") {
                $oOtherQuestionL10n = new QuestionL10n();
                $oOtherQuestionL10n->setAttributes($oQuestion->questionl10ns[$sLanguage]->attributes, false);
                $oOtherQuestionL10n->setAttributes(array(
                    "question" => !empty($attributes['other_replace_text'][$sLanguage]) ? $attributes['other_replace_text'][$sLanguage] : gT("Other"),
                ), false);
                $oOtherQuestion = new Question($oQuestion->attributes);
                $oOtherQuestion->setAttributes(array(
                    "sid" => $oQuestion->sid,
                    "gid" => $oQuestion->gid,
                    "type" => "T",
                    "parent_qid" => $oQuestion->qid,
                    "qid" => "other",
                    "title" => "other",
                ), false);
                $oOtherQuestion->questionl10ns = [$sLanguage => $oOtherQuestionL10n];

                $aQuestionAttributes['subquestions']["other"] = $this->getQuestionArray($oOtherQuestion, $oResponses, $bHonorConditions, true, false, $sLanguage);
                if ($oQuestion->type == "P") {
                    $aQuestionAttributes['subquestions']["other"]['comment'] = $this->getQuestionArray($oOtherQuestion, $oResponses, $bHonorConditions, true, true, $sLanguage);
                }
            }
        }

        $fieldname = $oQuestion->basicFieldName;
        //If question is of any Array-Type  or a subquestion
        if (
            in_array($oQuestion->type, ["F", "A", "B", "E", "C", "H", "Q", "K", "M", "P", ";",":","1"])
            || ($oQuestion->type == 'T' && $oQuestion->parent_qid != 0)
        ) {
            $fieldname .= $oQuestion->title;
        }


        if ($getCommentOnly) {
            $fieldname .= 'comment';
        }

        $aQuestionAttributes['fieldname'] = $fieldname;
        $aQuestionAttributes['questionclass'] = Question::getQuestionClass($oQuestion->type);

        if ($oQuestion->scale_id == 1) {
            return  $aQuestionAttributes;
        }

        if ($aQuestionAttributes['questionclass'] === 'date') {
            $aQuestionAttributes['dateformat'] = getDateFormatDataForQID($aQuestionAttributes, array_merge($this->survey->attributes, $oQuestion->survey->languagesettings[$sLanguage]->attributes));
        }

        $aQuestionAttributes['answervalue'] = $oResponses[$fieldname] ?? null;
        $aQuestionAttributes['answercode'] = $aQuestionAttributes['answervalue']; // Must keep original code for -oth- and maybe other
        if (
            (in_array($oQuestion->type, ["!", "L", "O", "F", "H"]))
            || ($oQuestion->type == 'T' && $oQuestion->parent_qid != 0)
        ) {
            $aAnswers = (
                $oQuestion->parent_qid == 0
                    ? $oQuestion->answers
                    : ($oQuestion->parent != null
                        ? $oQuestion->parent->answers
                        : []
                    )
                );

            $oSelectedAnswerOption = array_reduce($aAnswers, function ($carry, $oAnswer) use ($aQuestionAttributes) {
                return $aQuestionAttributes['answervalue'] == $oAnswer->code ? $oAnswer : $carry;
            });

            if ($oSelectedAnswerOption !== null) {
                $aQuestionAttributes['answeroption'] = array_merge(
                    $oSelectedAnswerOption->attributes,
                    $oSelectedAnswerOption->answerl10ns[$sLanguage]->attributes
                );
            } elseif ($oQuestion->other == 'Y') {
                $aQuestionAttributes['answervalue'] = !empty($attributes['other_replace_text'][$sLanguage]) ? $attributes['other_replace_text'][$sLanguage] : gT("Other");
                $aQuestionAttributes['answeroption']['answer'] = $oResponses[$fieldname . 'other'] ?? null;
            }
        }

        if ($aQuestionAttributes['questionclass'] === 'language') {
            $languageArray = getLanguageData(false, $aQuestionAttributes['answervalue']);
            $aQuestionAttributes['languageArray'] = $languageArray[$aQuestionAttributes['answervalue']];
        }

        if ($aQuestionAttributes['questionclass'] === 'upload-files') {
            $aQuestionAttributes['fileinfo'] = json_decode((string) $aQuestionAttributes['answervalue'], true);
        }

        if ($oQuestion->parent_qid != 0 && isset($oQuestion->parent['type']) && $oQuestion->parent['type'] === "1") {
            $aAnswers = (
                $oQuestion->parent != null
                ? $oQuestion->parent->answers
                : []
            );

            foreach ($aAnswers as $key => $value) {
                $aAnswerText[$value['scale_id']][$value['code']] = Answer::model()->getAnswerFromCode($value->qid, $value->code, $sLanguage, $value->scale_id);
            }

            $tempFieldname = $fieldname . '#0';
            $sAnswerCode = $oResponses[$tempFieldname] ?? null;
            $sAnswerText = isset($aAnswerText[0][$oResponses[$tempFieldname]]) ? $aAnswerText[0][$oResponses[$tempFieldname]] . ' (' . $sAnswerCode . ')' : null;
            $aQuestionAttributes['answervalues'][0] = $sAnswerText;

            $tempFieldname = $fieldname . '#1';
            $sAnswerCode = $oResponses[$tempFieldname] ?? null;
            $sAnswerText = isset($aAnswerText[1][$oResponses[$tempFieldname]]) ? $aAnswerText[1][$oResponses[$tempFieldname]] . ' (' . $sAnswerCode . ')' : null;
            $aQuestionAttributes['answervalues'][1] = $sAnswerText;
        }

        // Array dual scale headers
        if (isset($attributes['dualscale_headerA']) && !empty($attributes['dualscale_headerA'][$sLanguage])) {
            $aQuestionAttributes['dualscale_header'][0] =  $attributes['dualscale_headerA'][$sLanguage];
        }
        if (isset($attributes['dualscale_headerB']) && !empty($attributes['dualscale_headerB'][$sLanguage])) {
            $aQuestionAttributes['dualscale_header'][1] =  $attributes['dualscale_headerB'][$sLanguage];
        }

        if ($aQuestionAttributes['questionclass'] === 'ranking') {
            $aQuestionAttributes['answervalues'] = array();
            $iterator = 1;
            do {
                $currentResponse = $oResponses[$fieldname . $iterator];

                $oSelectedAnswerOption = array_reduce($oQuestion->answers, function ($carry, $oAnswer) use ($currentResponse) {
                    return $currentResponse == $oAnswer->code ? $oAnswer : $carry;
                });

                $option = '';
                if ($oSelectedAnswerOption !== null) {
                    $option = array_merge(
                        $oSelectedAnswerOption->attributes,
                        $oSelectedAnswerOption->answerl10ns[$sLanguage]->attributes
                    );
                }
                $aQuestionAttributes['answervalues'][] = ['value' => $currentResponse, 'option' => $option];

                $iterator++;
            } while (isset($oResponses[$fieldname . $iterator]));
        }

        /* Second (X) scale for array text and array number */
        if ($oQuestion->parent_qid != 0 && isset($oQuestion->parent['type']) && in_array($oQuestion->parent['type'], [";", ":"])) {
            $oScaleXSubquestions = Question::model()->with('questionl10ns')->findAll(array(
                'condition' => "parent_qid = :parent_qid and scale_id = :scale_id",
                'order' => "question_order ASC",
                'params' => array(':parent_qid' => $aQuestionAttributes['parent_qid'], ':scale_id' => 1),
            ));
            foreach ($oScaleXSubquestions as $oScaleSubquestion) {
                $tempFieldname = $fieldname . '_' . $oScaleSubquestion->title;
                $aQuestionAttributes['answervalues'][$oScaleSubquestion->title] = $oResponses[$tempFieldname] ?? null;
                /* Isue with language, need #15907 fixed */
                $aQuestionAttributes['answervalueslabels'][$oScaleSubquestion->title] = $oScaleSubquestion->questionl10ns[$sLanguage]->question ?? null;
            }
        }

        if ($oQuestion->type == 'N' || ($oQuestion->parent_qid != 0 && isset($oQuestion->parent['type']) && $oQuestion->parent['type'] === "K")) {
            if (strpos((string) $aQuestionAttributes['answervalue'], ".") !== false) { // Remove last 0 and last . ALWAYS (see \SurveyObj\getShortAnswer)
                $aQuestionAttributes['answervalue'] = rtrim(rtrim((string) $aQuestionAttributes['answervalue'], "0"), ".");
            }
        }

        // If trying to retrieve main question ($getCommentOnly = false), retrieve comment in a new attribute
        // Check if $getCommentOnly = false to avoid endless recursivity
        if ($oQuestion->type == 'O' && !$getCommentOnly) {
            $aQuestionAttributes['comment'] = $this->getQuestionArray($oQuestion, $oResponses, $bHonorConditions, true, true);
        }

        return $aQuestionAttributes;
    }

    /**
     * Decrypts all encrypted response values for output (e.g. printanswers, detailed admin info)
     *
     * @return void
     */
    public function decryptBeforeOutput()
    {
        //get response values which are encrypted
        $encryptedAttr = Response::getEncryptedAttributes($this->getSurveyId());
        $attributes = $this->attributes;
        $sodium = Yii::app()->sodium;
        foreach ($encryptedAttr as $key) {
            $this->setAttribute($key, $sodium->decrypt($attributes[$key]));
        }
    }

    public function getPrintAnswersArray($sSRID, $sLanguage, $bHonorConditions = false)
    {

        $oSurvey = $this->survey;
        $aGroupArray = array();

        $oResponses = SurveyDynamic::model($oSurvey->sid)->findByAttributes(array('id' => $sSRID));
        $oResponses->decryptBeforeOutput();

        $oGroupList = $oSurvey->groups;

        foreach ($oGroupList as $oGroup) {
            if (!(LimeExpressionManager::GroupIsRelevant($oGroup->gid) && $bHonorConditions == true)) {
                continue;
            }

            $aAnswersArray = array();
            $aQuestionArray = $oGroup->questions;
            foreach ($aQuestionArray as $oQuestion) {
                $aQuestionArray = $this->getQuestionArray($oQuestion, $oResponses, $bHonorConditions, false, false, $sLanguage);

                if ($aQuestionArray === false) {
                    continue;
                }

                $aAnswersArray[$oQuestion->qid] = $aQuestionArray;
            }

            $aGroupAttributes = $oGroup->attributes;
            $aGroupAttributes['answerArray'] = $aAnswersArray;
            $aGroupAttributes['debug'] = $oResponses->attributes;
            $aGroupAttributes['group_name'] = $oGroup->getGroupNameI10N($sLanguage);
            $aGroupAttributes['description'] = $oGroup->getGroupDescriptionI10N($sLanguage);
            $aGroupAttributes['language'] = $sLanguage;
            $aGroupArray[$oGroup->gid] = $aGroupAttributes;
        }

        return $aGroupArray;
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return self::$sid;
    }

    /**
     * Get current survey for other model/function
     * Using a getter to avoid query during model creation
     * @return Survey
     */
    public function getSurvey()
    {
        if (self::$sid && self::$survey === null) {
            self::$survey = Survey::model()->findByPk(self::$sid);
        }
        return self::$survey;
    }
}
