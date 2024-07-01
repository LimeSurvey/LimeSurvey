<?php

    /**
     * Relations
     * @property Token $token
     * @property Survey $survey
     */
abstract class Response extends Dynamic
{
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
            $field = $question->sid . 'X' . $question->gid . 'X' . $question->qid;
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
            $field = $question->sid . 'X' . $question->gid . 'X' . $question->qid;
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
            SurveyTimingDynamic::model($this->dynamicId)->deleteByPk($this->id);
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

    public function delete($deleteFiles = false)
    {
        if ($deleteFiles) {
            $this->deleteFiles();
        }
        return parent::delete();
    }
    public function relations()
    {
        $result = array(
            'token' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('token' => 'token')),
            'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}")
        );
        return $result;
    }
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

    public function browse()
    {
    }
    public function search()
    {
    }

    public static function getEncryptedAttributes($surveyid = 0)
    {
        $survey = Survey::model()->findByPk($surveyid);
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
}
