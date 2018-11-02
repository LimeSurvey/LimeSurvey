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
                return true;
            }
            return false;
        }
        /**
         *
         * @param mixed $className Either the classname or the survey id.
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
         * @param int $surveyId
         * @param string $scenario
         * @return Response Description
         */
        public static function create($surveyId, $scenario = 'insert')
        {
            return parent::create($surveyId, $scenario);
        }

        /**
         * Get all files related to this response and (optionally) question ID.
         *
         * @param int $qid
         * @return array
         */
        public function getFiles($qid = null)
        {
            $survey = Survey::model()->findByPk($this->dynamicId);
            $conditions = [
                'sid' => $this->dynamicId,
                'type' => '|',
                'language'=>$survey->language
            ];
            if ($qid !== null) {
                $conditions['qid'] = $qid;
            }
            $questions = Question::model()->findAllByAttributes($conditions);
            $files = array();
            foreach ($questions as $question) {
                $field = $question->sid.'X'.$question->gid.'X'.$question->qid;
                $data = json_decode(urldecode($this->getAttribute($field)), true);
                if (is_array($data)) {
                    $files = array_merge($files, $data);
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
            $aConditions = array('sid' => $this->dynamicId, 'type' => '|', 'language'=>$this->survey->language);
            if ($sQID > 0) {
                $aConditions['qid'] = $sQID;
            }
            $aQuestions = Question::model()->findAllByAttributes($aConditions);
            $files = array();
            foreach ($aQuestions as $question) {
                $field = $question->sid.'X'.$question->gid.'X'.$question->qid;
                $data = json_decode(stripslashes($this->getAttribute($field)), true);
                if (is_array($data)) {
                    $files[$field] = $data;
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
            $uploaddir = Yii::app()->getConfig('uploaddir')."/surveys/{$this->dynamicId}/files/";
            foreach ($this->getFiles() as $fileInfo) {
                $basename = basename($fileInfo['filename']);
                if (file_exists($uploaddir.$basename)) {
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
            $uploaddir = Yii::app()->getConfig('uploaddir')."/surveys/{$this->dynamicId}/files/";
            foreach ($this->getFiles() as $fileInfo) {
                $basename = basename($fileInfo['filename']);
                $result = @unlink($uploaddir.$basename);
                if (!$result) {
                    $errors[] = $fileInfo['filename'];
                }
            }

            return $errors;
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
            $uploaddir = Yii::app()->getConfig('uploaddir')."/surveys/{$this->dynamicId}/files/";
            $filesData = $this->getFilesAndSqga();
            foreach ($filesData as $sgqa => $fileInfos) {
                foreach ($fileInfos as $i => $fileInfo) {
                    $basename = basename($fileInfo['filename']);
                    $fullFilename = $uploaddir.$basename;

                    if (file_exists($fullFilename)) {
                        $result = @unlink($fullFilename);
                        if (!$result) {
                            $errors[] = $fileInfo['filename'];
                        } else {
                            //$filesData[$sgqa][$i]['filename'] = 'deleted';
                            $fileInfos[$i]['name'] = $fileInfo['name'].sprintf(' (%s)', gT('deleted'));
                            $this->$sgqa = json_encode($fileInfos);
                            $result = $this->save();
                            if ($result) {
                                $success++;
                            } else {
                                $errors[] = 'Could not update filename info for file '.$fileInfo['filename'];
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
                'token' => array(self::BELONGS_TO, 'Token_'.$this->dynamicId, array('token' => 'token')),
                'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}")
            );
            return $result;
        }
        public function tableName()
        {
            return '{{survey_'.$this->dynamicId.'}}';
        }
        /**
         * Get current surveyId for other model/function
         * @return int
         */
        public function getSurveyId() {
            return $this->getDynamicId();
        }

        public function browse()
        {
        }
        public function search()
        {

        }
    }
