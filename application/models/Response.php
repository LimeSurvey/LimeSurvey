<?php

/**
 * Dynamic model for the responses of a survey (table responses_<survey ID>).
 * Usage: Response::model($surveyId) for queries, Response::create($surveyId) for a new response.
 *
 * @property integer $id Response ID
 * @property string $token Access code of the participant
 * @property string $submitdate Date of submission, null if the response is not completed
 * @property integer $lastpage Last page the participant has seen
 * @property string $startlanguage
 * @property string $seed
 *
 * Relations
 * @property Survey $survey
 * @property Token $tokens The participant of this response. Only available if the survey has
 *     a participant table and the current user is allowed to read it (always in a console application).
 * @property SavedControl $saved_control
 *
 * @property string $completed HTML of the completed state, for the responses list
 * @property string[] $defaultColumns
 */
abstract class Response extends Dynamic
{
    /** @var string $completed_filter Filter of the responses list: Y for completed, N for incomplete responses */
    public $completed_filter;
    /** @var string $firstname_filter Filter of the responses list on the first name of the participant */
    public $firstname_filter;
    /** @var string $lastname_filter Filter of the responses list on the last name of the participant */
    public $lastname_filter;
    /** @var string $email_filter Filter of the responses list on the email address of the participant */
    public $email_filter;

    /**
     * @inheritdoc
     * Also deletes the uploaded files and the timings of the response
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteFiles();
            $this->deleteTimings();
            return true;
        }
        return false;
    }
    /**
     *
     * @param mixed $className Either the classname or the survey ID.
     * @return Response
     */
    public static function model($className = null)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }
    /**
     *
     * @param int $id Survey id in this class
     * @param string $scenario
     * @return Response Description
     */
    public static function create($id, $scenario = 'insert')
    {
        return parent::create($id, $scenario);
    }

    /** @inheritdoc
     * Must be set by DB, adding by security here
     * @see https://bugs.limesurvey.org/view.php?id=17208
     **/
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @inheritdoc
     * Also stores the multiple choice Other tick that goes with the Other text
     */
    public function encryptSave($runValidation = true, $attributes = null)
    {
        $attributes = Survey::setOtherCheckedFromText($this, $attributes);
        return parent::encryptSave($runValidation, $attributes);
    }

    /**
     * Get all files related to this response and (optionally) question ID.
     *
     * @param int $qid
     * @return array[]
     */
    public function getFiles($qid = null)
    {
        $survey = Survey::model()->findByPk($this->dynamicId);
        $criteria = new CDbCriteria();
        $criteria->compare('sid', $this->dynamicId);
        $criteria->compare('type', Question::QT_VERTICAL_FILE_UPLOAD);
        $criteria->compare('ql10ns.language', $survey->language);
        if ($qid !== null) {
            $criteria->compare('t.qid', $qid);
        }

        $questions = Question::model()
            ->with(array('questionl10ns' => array('alias' => 'ql10ns')))
            ->findAll($criteria);
        $files = array();
        foreach ($questions as $question) {
            $field = 'Q' . $question->qid;
            $fieldDataJson = $this->getAttribute($field);
            if ($question->encrypted === 'Y') {
                $fieldDataJson = self::decryptSingle($fieldDataJson);
            }
            $fieldData = json_decode(urldecode((string) $fieldDataJson), true);
            if (is_array($fieldData)) {
                /* adding the title and qid to fileinfo , see #14659 */
                $index = 0;
                $fieldData = array_map(function ($fileInfo) use (&$index, $question) {
                    return array_merge($fileInfo, array(
                        'question' => array(
                            'title' => $question->title,
                            'qid' => $question->qid,
                        ),
                        'index' => $index++,
                    ));
                }, $fieldData);
                $files = array_merge($files, $fieldData);
            }
        }
        return $files;
    }

    /**
     * Like getFiles() but returns array with key sgqa and value file data.
     * @param integer $sQID The question ID - optional - Default 0
     * @return array [string $sgqa, array $fileData]
     */
    public function getFilesAndSqga($sQID = 0)
    {
        $aConditions = array('sid' => $this->dynamicId, 'type' => '|');
        if ($sQID > 0) {
            $aConditions['qid'] = $sQID;
        }
        $aQuestions = Question::model()
            ->with(['questionl10ns' => ['language' => $this->survey->language]])
            ->findAllByAttributes($aConditions);
        $files = array();
        foreach ($aQuestions as $question) {
            $encrypted = $question->encrypted === 'Y';
            $field = 'Q' . $question->qid;
            $fieldDataJson = $this->getAttribute($field);
            if ($encrypted) {
                $fieldDataJson = self::decryptSingle($fieldDataJson);
            }
            $fieldData = json_decode(stripslashes((string) $fieldDataJson), true);
            if (is_array($fieldData)) {
                $files[$field] = array(
                    'files' => $fieldData,
                    'encrypted' => $encrypted,
                );
            }
        }
        return $files;
    }

    /**
     * Returns true if any uploaded file still exists on the filesystem.
     * @return boolean
     */
    public function someFileExists()
    {
        $uploaddir = Yii::app()->getConfig('uploaddir') . "/surveys/{$this->dynamicId}/files/";
        foreach ($this->getFiles() as $fileInfo) {
            $basename = basename((string) $fileInfo['filename']);
            if (file_exists($uploaddir . $basename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete all uploaded files for this response.
     * @return string[] Name of files that could not be removed.
     */
    public function deleteFiles()
    {
        $errors = array();
        $uploaddir = Yii::app()->getConfig('uploaddir') . "/surveys/{$this->dynamicId}/files/";
        foreach ($this->getFiles() as $fileInfo) {
            $basename = basename((string) $fileInfo['filename']);
            $result = @unlink($uploaddir . $basename);
            if (!$result) {
                $errors[] = $fileInfo['filename'];
            }
        }

        return $errors;
    }

    /**
     * Delete timings if savetimings is set.
     */
    public function deleteTimings()
    {
        if (Survey::model()->findByPk($this->dynamicId)->isSaveTimings) {
            Timing::model($this->dynamicId)->deleteByPk($this->id);
        }
    }

    /**
     * Delete uploaded files for this response AND modify
     * response data to reflect all changes.
     * Keep comment and title of file, but remove name/filename.
     * @return array Number of successfully moved files and names of files that could not be removed/failed
     */
    public function deleteFilesAndFilename()
    {
        $errors = array();
        $success = 0;
        $uploaddir = Yii::app()->getConfig('uploaddir') . "/surveys/{$this->dynamicId}/files/";
        $filesData = $this->getFilesAndSqga();

        foreach ($filesData as $sgqa => $aQuestion) {
            [
                'files' => $files,
                'encrypted' => $encrypted,
            ] = $aQuestion;

            foreach ($files as $i => $fileInfo) {
                $basename = basename((string) $fileInfo['filename']);
                $fullFilename = $uploaddir . $basename;

                if (file_exists($fullFilename)) {
                    $result = @unlink($fullFilename);
                    if (!$result) {
                        $errors[] = $fileInfo['filename'];
                    } else {
                        $files[$i]['name'] = $fileInfo['name'] . sprintf(' (%s)', gT('deleted'));

                        $sEncoded = json_encode($files);

                        if ($encrypted) {
                            $sEncoded = self::encryptSingle($sEncoded);
                        }

                        $this->$sgqa = $sEncoded;
                        $result = $this->save();
                        if ($result) {
                            $success++;
                        } else {
                            $errors[] = 'Could not update filename info for file ' . $fileInfo['filename'];
                        }
                    }
                } else {
                    // TODO: Internal error - wrong filename saved?
                }
            }
        }

        return array($success, $errors);
    }

    /**
     * Check if any question of this response has a value. The response meta data (dates, language, token, seed, ...) is ignored.
     * Empty values are not encrypted, so the check works with encrypted values too.
     * @return boolean
     */
    public function hasAnswers()
    {
        $survey = Survey::model()->findByPk($this->dynamicId);
        foreach (createFieldMap($survey, 'short', false, false) as $field) {
            if (empty($field['qid'])) {
                continue;
            }
            $value = $this->getAttribute($field['fieldname']);
            if (!is_null($value) && $value !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     * @param boolean $deleteFiles Unused: The uploaded files are always deleted, see beforeDelete()
     */
    public function delete($deleteFiles = false)
    {
        return parent::delete();
    }

    /** @inheritdoc */
    public function relations()
    {
        $surveyId = intval($this->dynamicId);
        $relations = array(
            'survey' => array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$surveyId}"),
            'saved_control' => array(self::HAS_ONE, 'SavedControl', array('srid' => 'id'), 'condition' => "sid = {$surveyId}"),
        );
        if ($this->getHaveToken()) {
            // The column "token" hides a relation of the same name, so the participant is "tokens"
            $relations['tokens'] = array(self::HAS_ONE, 'Token_' . $surveyId, array('token' => 'token'));
        }
        return $relations;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{responses_' . $this->dynamicId . '}}';
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return $this->getDynamicId();
    }

    /**
     * Get all encrypted questions for a survey, as array of fieldnames
     *
     * @param int $surveyid Survey ID
     * @return string[] Fieldnames of the encrypted questions; empty if the survey does not exist (e.g. archiving the responses table of a deleted survey)
     */
    public static function getEncryptedAttributes($surveyid = 0)
    {
        $survey = Survey::model()->findByPk($surveyid);
        if (!$survey) {
            return array();
        }
        $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);
        $aAttributes = array();
        foreach ($fieldmap as $field) {
            if (array_key_exists('encrypted', $field) &&  $field['encrypted'] == 'Y') {
                $aAttributes[] = $field['fieldname'];
            }
        }
        return $aAttributes;
    }

    /**
     * Set all the specified attributes.
     * If any attribute doesn't exist in the DB, an Exception is thrown.
     */
    public function setAllAttributes($values)
    {
        if (!is_array($values)) {
            return;
        }
        $attributes = array_flip($this->attributeNames());
        foreach ($values as $name => $value) {
            if (isset($attributes[$name])) {
                $this->$name = $value;
            } else {
                throw new Exception(sprintf("Attribute '%s' not found in the model.", $name));
            }
        }
    }

    /**
     * Insert records from $data array
     * Still used in em_manager_helper when create response in _UpdateValuesInDatabase function (2026-04-20)
     *
     * @access public
     * @deprecated Use setAttributes() and encryptSave()
     * @param array $data
     * @return integer|false
     */
    public function insertRecords($data)
    {
        $record = Response::create($this->dynamicId);
        foreach ($data as $k => $v) {
            $search = array('`', "'");
            $k = str_replace($search, '', $k);
            $v = $v == null ? null : str_replace($search, '', (string) $v);
            $record->$k = $v;
        }
        if ($record->encryptSave()) {
            return $record->id;
        }
        /* If error: Return false */
        return false;
    }

    /**
     * Return criteria updated with the ones needed for including results from the survey participant list
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

        $newCriteria->join = "LEFT JOIN {{tokens_" . $this->dynamicId . "}} tokens ON $alias.token = tokens.token";

        $aTokenFields = Yii::app()->db->schema->getTable($this->survey->tokensTableName)->getColumnNames();
        $aTokenFields = array_diff($aTokenFields, array('token'));

        $newCriteria->select = $aTokenFields; // Otherwise we don't get records from the survey participant list
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    /**
     * Return true if actual survey is completed
     *
     * @param integer $srid Response ID
     *
     * @return boolean
     */
    public function isCompleted($srid)
    {
        static $resultCache = array();

        $sid = $this->dynamicId;
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
     * @see https://bugs.limesurvey.org/view.php?id=14222: When deleting a single response: All page is reloaded (not only grid)
     * @return string
     */
    public function getGridButtons()
    {
        $sBrowseLanguage = \LSYii_Validators::languageCodeFilter(Yii::app()->request->getParam('browseLang', ''));

        $permissionReponseUpdate = Permission::model()->hasSurveyPermission($this->dynamicId, 'responses', 'update');
        $permissionReponseDelete = Permission::model()->hasSurveyPermission($this->dynamicId, 'responses', 'delete');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('View response details'),
            'iconClass'        => 'ri-eye-fill',
            'url'              => App()->createUrl(
                "responses/view",
                [
                    "surveyId" => $this->dynamicId,
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
                    "surveyId" => $this->dynamicId,
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
                    "surveyId" => $this->dynamicId,
                    "id" => $this->id,
                    "browseLang" => $sBrowseLanguage
                ]
            ),
            'enabledCondition' => $permissionReponseUpdate,
        ];
        $fileExists = Response::model($this->dynamicId)->findByPk($this->id)->someFileExists();
        $dropdownItems[] = [
            'title'            => gT('Download all response files'),
            'iconClass'        => 'ri-download-fill text-success',
            'url'              => App()->createUrl(
                "responses/downloadfiles",
                ["surveyId" => $this->dynamicId, "responseIds" => $this->id]
            ),
            'enabledCondition' => $permissionReponseUpdate && hasFileUploadQuestion($this->dynamicId) && $fileExists,
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete all response files'),
            'iconClass'        => 'ri-attachment-2 text-danger',
            'enabledCondition' => $permissionReponseUpdate && hasFileUploadQuestion($this->dynamicId) && $fileExists,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => "#confirmation-modal",
                'data-btnclass'  => 'btn-danger',
                'data-title'     => gT('Delete all response files'),
                'data-btntext'   => gT('Delete'),
                'data-post-url'  => App()->createUrl("responses/deleteAttachments"),
                'data-post-datas' => json_encode(['surveyId' => $this->dynamicId, 'responseId' => $this->id]),
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
                'data-post-datas' => json_encode(['surveyId' => $this->dynamicId, 'responseId' => $this->id]),
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
     * @param string $colName
     * @param string $sLanguage
     * @param string $base64jsonFieldMap
     * @return string
     */
    public function getExtendedData($colName, $sLanguage, $base64jsonFieldMap)
    {
        $oFieldMap = json_decode(base64_decode($base64jsonFieldMap));
        $value     = $this->$colName;

        $sFullValue = viewHelper::responseValueToText(getExtendedAnswer($this->dynamicId, $oFieldMap->fieldname, $value, $sLanguage), $value);
        if (strlen((string) $sFullValue) > 50) {
            $sElipsizedValue = ellipsize($sFullValue, $this->ellipsize_question_value, 1, '…', false);
            $sValue          = '<span data-bs-toggle="tooltip" data-bs-placement="left" title="' . quoteText($sFullValue) . '">' . CHtml::encode($sElipsizedValue) . '</span>';
        } else {
            $sValue          = CHtml::encode($sFullValue);
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
                    $url = App()->createUrl("responses/downloadfile", ["surveyId" => $this->dynamicId, "responseId" => $this->id, "qid" => $oFieldMap->qid, "index" => $iFileIndex]);
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
     * @param integer $srid Response ID
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
     * @param integer $srId Response ID
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
     * @param integer $srId Response ID
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

        $sid = $this->dynamicId;
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
     * Whether the participant data can be shown with the responses:
     * The survey has a participant table and the current user is allowed to read it.
     * In a console application, the participant table is enough.
     * @return bool
     */
    private function getHaveToken()
    {
        if (!tableExists('tokens_' . $this->dynamicId)) {
            return false;
        }
        // A console application has no user, and permissions can not be tested there
        if (Yii::app() instanceof CConsoleApplication) {
            return true;
        }
        return Permission::model()->hasSurveyPermission($this->dynamicId, 'tokens', 'read');
    }

    /**
     * Returns the participant of this response.
     *
     * @return Token|null Null if the response has no participant, or if the participants are not available
     *     (no participant table, or the current user is not allowed to read it)
     */
    private function getParticipant()
    {
        return isset($this->tokens) ? $this->tokens : null;
    }

    /**
     * Returns the first name of the participant, for the responses list.
     * Also decrypts the participant, so it has to be called before the getters of last name and email.
     *
     * @return string|null Null if there is no participant
     */
    public function getFirstNameForGrid()
    {
        $participant = $this->getParticipant();
        if ($participant === null) {
            return null;
        }
        $participant->decrypt();
        return $participant->firstname;
    }

    /**
     * Returns the last name of the participant, for the responses list.
     * The participant is already decrypted by getFirstNameForGrid(): Doing it again would fail.
     *
     * @return string|null Null if there is no participant
     */
    public function getLastNameForGrid()
    {
        $participant = $this->getParticipant();
        return $participant === null ? null : $participant->lastname;
    }

    /**
     * Returns the email address of the participant, for the responses list.
     * The participant is already decrypted by getFirstNameForGrid(): Doing it again would fail.
     *
     * @return string|null Null if there is no participant
     */
    public function getEmailForGrid()
    {
        $participant = $this->getParticipant();
        return $participant === null ? null : $participant->email;
    }

    /**
     * Returns the access code of this response for the responses list:
     * A button to edit the participant, or only the code if there is no participant.
     *
     * @return string HTML
     */
    public function getTokenForGrid()
    {
        $participant = $this->getParticipant();
        if ($participant !== null && !is_null($participant->tid)) {
            $sToken = "<a class='btn btn-outline-secondary btn-xs edit-token' href='#' data-sid='" . $this->dynamicId . "' data-tid='" . $participant->tid . "'  data-url='" . App()->createUrl("admin/tokens", array("sa" => "edit", "iSurveyId" => $this->dynamicId, "iTokenId" => $participant->tid, 'ajax' => 'true')) . "' data-bs-toggle='tooltip' title='" . gT("Edit this survey participant") . "'>" . CHtml::encode($this->token) . "&nbsp;&nbsp;&nbsp;<span class='ri-pencil-fill'></span></a>";
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
     * Search function, used by the grid of the responses list
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

        // Join the survey participant list and filter tokens if needed
        if ($this->getHaveToken() && $this->survey->anonymized != 'Y') {
            $this->joinWithToken($criteria, $sort);
        }

        // Basic filters
        $criteria->compare('t.lastpage', empty($this->lastpage) ? null : $this->lastpage, false);
        $criteria->compare('t.id', empty($this->id) ? null : $this->id, false);
        $criteria->compare('t.submitdate', $this->submitdate, true);
        $criteria->compare('t.startlanguage', $this->startlanguage, true);
        $criteria->compare('t.seed', $this->seed, true);

        // Completed filters
        if ($this->completed_filter == "Y") {
            $criteria->addCondition('t.submitdate IS NOT NULL');
        }

        if ($this->completed_filter == "N") {
            $criteria->addCondition('t.submitdate IS NULL');
        }

        // When selection of responses come from statistics
        // TODO: This provide a first step to enable the old jQgrid selector system, and could be use for users and tokens
        if (Yii::app()->user->getState('sql_' . $this->dynamicId) != null) {
            $criteria->addCondition(Yii::app()->user->getState('sql_' . $this->dynamicId));
        }

        $this->filterColumns($criteria);


        $dataProvider = new LSCActiveDataProvider(get_class($this), array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
        return $dataProvider;
    }

    /**
     * Returns the ids of all responses matching the filters of the responses grid,
     * as used by "Select all" massive actions.
     *
     * @param int $surveyId
     * @param string $filterQuery Serialized filter inputs of the responses grid
     * @param array $excludedIds Response ids deselected by the user
     * @param int|null $limit Maximum number of responses (lowest ids first), null for no limit
     * @return array
     */
    public static function getIdsByGridFilter(int $surveyId, string $filterQuery, array $excludedIds = [], ?int $limit = null): array
    {
        $model = self::model($surveyId);
        $model->bEncryption = true;

        parse_str($filterQuery, $parsedFilterQuery);
        $filters = $parsedFilterQuery['Response'] ?? null;
        $filters = is_array($filters) ? $filters : [];
        $model->setAttributes($filters, false);
        // These filters are no table columns, so setAttributes() skips them
        foreach (['completed_filter', 'firstname_filter', 'lastname_filter', 'email_filter'] as $filterName) {
            $model->$filterName = $filters[$filterName] ?? null;
        }

        $criteria = $model->search()->criteria;
        $criteria->select = 't.id';
        if ($limit !== null) {
            $criteria->order = 't.id ASC';
            $criteria->limit = $limit;
        }

        $responseIds = $model->getCommandBuilder()
            ->createFindCommand($model->tableSchema, $criteria)
            ->queryColumn();
        if (empty($excludedIds)) {
            return $responseIds;
        }

        $excludedIds = array_flip(array_map('strval', $excludedIds));
        return array_values(array_filter($responseIds, function ($responseId) use ($excludedIds) {
            return !isset($excludedIds[(string) $responseId]);
        }));
    }

    /**
     * @param CDbCriteria $criteria
     * @param CSort $sort
     * @return void
     */
    protected function joinWithToken(CDbCriteria $criteria, CSort $sort)
    {
        $criteria->compare('t.token', $this->token, true);
        $criteria->join = "LEFT JOIN {{tokens_" . $this->dynamicId . "}} as tokens ON t.token = tokens.token";
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
     * Builds the display/print data for a single question (and its subquestions), used to
     * render the print-answers page. Returns false when the question must not be shown,
     * i.e. it is not relevant while conditions are honored, or it carries the "hidden"
     * question attribute ("Always hide this question").
     * This should be part of the question object.
     * And in future development this should be part of the specific question type object
     *
     * @param Question $oQuestion The question (or subquestion) to build data for
     * @param Response $oResponses The response row the answers are read from
     * @param boolean $bHonorConditions Whether relevance/conditions should be honored; if false, relevance is not checked
     * @param boolean $subquestion Whether $oQuestion is a subquestion of another question; when true, its own (array_filter) relevance is checked instead of only its parent's
     * @param boolean $getCommentOnly If should only returns the "comments" or "other" response.
     * @param string|null $sLanguage Language to use; defaults to the survey's language when null
     * @return array|false Question display data, or false if the question must be hidden
     */
    public function getQuestionArray($oQuestion, $oResponses, $bHonorConditions, $subquestion = false, $getCommentOnly = false, $sLanguage = null)
    {

        $attributes = QuestionAttribute::model()->getQuestionAttributes($oQuestion->qid);

        $fieldname = $oQuestion->basicFieldName;
        //If question is of any Array-Type  or a subquestion
        if (
            in_array($oQuestion->type, ["F", "A", "B", "E", "C", "H", "Q", "K", "M", "P", ";",":","1"])
            || ($oQuestion->type == 'T' && $oQuestion->parent_qid != 0)
        ) {
            $fieldname .= "_S{$oQuestion->qid}";
        }

        $isRelevant = $subquestion
            ? LimeExpressionManager::SubQuestionOrQuestionIsRelevant($fieldname, $oQuestion->qid)
            : LimeExpressionManager::QuestionIsRelevant($oQuestion->qid);

        if (
            ($bHonorConditions && !$isRelevant)
            || (is_array($attributes) && $attributes['hidden'] == 1)
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

        if (count($oQuestion->subquestions) > 0) {
            $aQuestionAttributes['subquestions'] = array();
            foreach ($oQuestion->subquestions as $oSubquestion) {
                //dont collect scale_id > 0
                if ($oSubquestion->scale_id > 0) {
                    continue;
                }

                $subQuestionArray = $this->getQuestionArray($oSubquestion, $oResponses, $bHonorConditions, true, false, $sLanguage);
                if ($subQuestionArray === false) {
                    continue;
                }
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

        if ($getCommentOnly) {
            $fieldname .= '_Ccomment';
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
                $aQuestionAttributes['answeroption']['answer'] = $oResponses[$fieldname . '_Cother'] ?? null;
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
            $sAnswerText = isset($aAnswerText[0][$sAnswerCode]) ? $aAnswerText[0][$sAnswerCode] . ' (' . $sAnswerCode . ')' : null;
            $aQuestionAttributes['answervalues'][0] = $sAnswerText;

            $tempFieldname = $fieldname . '#1';
            $sAnswerCode = $oResponses[$tempFieldname] ?? null;
            $sAnswerText = isset($aAnswerText[1][$sAnswerCode]) ? $aAnswerText[1][$sAnswerCode] . ' (' . $sAnswerCode . ')' : null;
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
            // Ranking is stored as a JSON list of subquestion codes in the Q{qid} column, ordered by rank
            $rankedCodes = json_decode((string) $aQuestionAttributes['answervalue'], true);
            if (is_array($rankedCodes)) {
                $subQuestions = Question::model()->with('questionl10ns')->findAllByAttributes(
                    array('parent_qid' => $oQuestion->qid),
                    array('order' => 'question_order')
                );
                $subQuestionTexts = [];
                foreach ($subQuestions as $oSubQuestion) {
                    $subQuestionTexts[$oSubQuestion->title] = $oSubQuestion->questionl10ns[$sLanguage]->question ?? $oSubQuestion->title;
                }
                foreach ($rankedCodes as $rankedCode) {
                    if (!is_scalar($rankedCode) || (string) $rankedCode === '') {
                        continue;
                    }
                    $rankedCode = (string) $rankedCode;
                    $answerText = $subQuestionTexts[$rankedCode] ?? $rankedCode;
                    $aQuestionAttributes['answervalues'][] = [
                        'value' => $rankedCode,
                        'subquestion' => $answerText,
                        'answertext' => $answerText
                    ];
                }
            }
        }

        /* Second (X) scale for array text and array number */
        if ($oQuestion->parent_qid != 0 && isset($oQuestion->parent['type']) && in_array($oQuestion->parent['type'], [";", ":"])) {
            $oScaleXSubquestions = Question::model()->with('questionl10ns')->findAll(array(
                'condition' => "parent_qid = :parent_qid and scale_id = :scale_id",
                'order' => "question_order ASC",
                'params' => array(':parent_qid' => $aQuestionAttributes['parent_qid'], ':scale_id' => 1),
            ));
            foreach ($oScaleXSubquestions as $oScaleSubquestion) {
                $tempFieldname = $fieldname . '_S' . $oScaleSubquestion->qid;
                $aQuestionAttributes['answervalues'][$oScaleSubquestion->title] = $oResponses[$tempFieldname] ?? null;
                /* Issue with language, need #15907 fixed */
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

    /**
     * Builds the grouped question/answer data for a response, used to render the print-answers page
     *
     * @param integer $sSRID Response ID
     * @param string $sLanguage Language to use for group and question texts
     * @param boolean $bHonorConditions Whether to skip groups and questions that are not relevant; if false, all are included
     * @return array Group data keyed by group ID, each holding its questions in 'answerArray'
     */
    public function getPrintAnswersArray($sSRID, $sLanguage, $bHonorConditions = false)
    {

        $oSurvey = $this->survey;
        $aGroupArray = array();

        $oResponses = Response::model($oSurvey->sid)->findByAttributes(array('id' => $sSRID));
        $oResponses->decryptBeforeOutput();

        $oGroupList = $oSurvey->groups;

        foreach ($oGroupList as $oGroup) {
            if ($bHonorConditions && !LimeExpressionManager::GroupIsRelevant($oGroup->gid)) {
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
}
