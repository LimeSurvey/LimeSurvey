<?php

	/**
	 * Relations
	 * @property Token $tokenObject
     * @property int $surveyId
     * @property Question[] $questions
	 * @property Survey $survey
	 */
	abstract class Response extends Dynamic
	{
        private $_questions;
        /**
         * @return Question[]
         */
        public function getQuestions() {
            if (!isset($this->_questions)) {
                $this->_questions = Question::model()->findAllByAttributes([
                    'sid' => $this->surveyId,
                    'parent_qid' => 0
                ], ['index' => 'title']);
            }
            return $this->_questions;
        }
        /**
         * This adds support for translating SGQA to a label that uses the question code.
         * This is not really efficient since it gets all question codes individually.
         * @param string $attribute
         * @return string
         */
        public function getAttributeLabel($attribute)
        {
            if (preg_match('/\d+X\d+X(\d+)(.+)?/', $attribute, $matches)) {
                /**
                 * Cache for 1 second so the query doesn't run again within this request,
                 * but the caching is unlikely to cause trouble later.
                 */
                $code = Question::model()->cache(1)->findByPk($matches[1])->title;
                return $code . (isset($matches[2]) ? " {$matches[2]}" : "");
            } else {
                return parent::getAttributeLabel($attribute);
            }
        }


        public function beforeDelete() {
            if (parent::beforeDelete())
            {
                $this->deleteFiles();
                return true;
            }
            return false;
        }

        /**
         * @return int
         */
        public function getSurveyId()
        {
            return $this->dynamicId;
        }
        /**
		 *
		 * @param mixed $className Either the classname or the survey id.
		 * @return Response
		 */
		public static function model($surveyId = null) {
            if (!is_numeric($surveyId)) {
                throw new \InvalidArgumentException("Survey ID must be numeric");
            }
			return parent::model($surveyId);
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
         * Delete all files related to this repsonse.
         */
        public function getFiles()
        {
            $questions = Question::model()->findAllByAttributes(array('sid' => $this->dynamicId,'type' => '|'));
            $files = array();
            foreach ($questions as $question)
            {

                $field = "{$question->sid}X{$question->gid}X{$question->qid}";
                $data = json_decode(stripslashes($this->getAttribute($field)), true);
                if (is_array($data))
                {
                    $files = array_merge($files, $data);
                }
            }

            return $files;
        }

        public function deleteFiles()
        {
            $uploaddir = Yii::app()->getConfig('uploaddir') ."/surveys/{$this->dynamicId}/files/";
            foreach ($this->getFiles() as $fileInfo)
            {
                @unlink($uploaddir . basename($fileInfo['filename']));
            }
        }
        public function delete($deleteFiles = false) {
            if ($deleteFiles)
            {
                $this->deleteFiles();
            }
            return parent::delete();
        }
		public function relations()
		{
            $t = $this->getTableAlias();
			$result = array(
                // Since we have a field named token as well.
				'tokenObject' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('token' => 'token')),
				'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}" ),
			);
			return $result;
		}

        public function rules() {
            $rules = [
                ['id', 'default', 'value' => \Cake\Utility\Text::uuid()],
            ];
            if ($this->hasAttribute('series_id')) {
                $rules[] = ['series_id', 'default', 'value' => \Cake\Utility\Text::uuid()];
            }


            return $rules;
        }
        public function scopes() {
            return [
                'complete' => [
                    'condition' => 'submitdate IS NOT NULL',
                ],
                'incomplete' => [
                    'condition' => 'submitdate IS NULL'
                ]
            ];
        }
		public static function constructTableName($id)
		{
			return '{{survey_' . $id . '}}';
		}
        
        
        public static function createTable(Survey $survey, &$messages = [])
        {
            $db = App()->db;
            //Check for any additional fields for this survey and create necessary fields (token and datestamp)
            $columns = $survey->columns;
            $tableName = "{{survey_{$survey->sid}}}";

            $createdTables = [];
            // Check if table exists with same column names.
            if ((null !== $table = $db->schema->getTable($tableName, true)) && Response::model($survey->sid)->count() == 0) {
                // Table exists but is empty..
                $db->createCommand()->dropTable($tableName);
                unset($table);
                $messages[] = gT("Old empty response table deleted.");
            } elseif (isset($table) && $table->columnNames !== array_keys($columns)) {
                // Table exists, but does not have the same columns / same order. This is fatal.
                throw new \Exception("The response table already exists and does not have the same columns as the one we were trying to create.");
            }

            if (!isset($table)) {
                // Table does not exist, create it.
                $db->createCommand()->createTable($tableName, $columns);
                $db->createCommand()->addPrimaryKey('', $tableName, ['id']);
                $createdTables[] = $tableName;
                $messages[] = gT("Response table created.");
            }

            if ($survey->bool_usetokens) {
                App()->db->createCommand()->createIndex("token_{$survey->sid}", $tableName, ['token']);
            }

            // Refresh the table schema
            App()->db->schema->getTable($tableName, true);
        }

        /**
         * Create a new response that belongs to the same series.
         * If copy is true then the response data from this response is copied to the new one.
         * @return self
         */
        public function append($copy = false)
        {
            $result = self::create($this->surveyId);
            $result->series_id = $this->series_id;
            if ($copy) {
                $result->setAttributes($this->attributes, false);
                // Unset progress information.
                $result->setAttributes([
                    'submitdate' => null,
                    'lastpage' => null,
                    'id' => null
                ], false);
            }
            return $result;


        }


        /**
         * Returns the full answer for the question that matches $fieldName
         * and the answer that matches the $answerCode.  If a match cannot
         * be made then false is returned.
         *
         * The name of the variable $answerCode is not strictly an answerCode
         * but could also be a comment entered by a participant.
         *
         * @param string $fieldName
         * @param string $answerCode
         * @param string $language
         * @return string
         */
        public function getLongAnswer($fieldName)
        {
            if (!preg_match('/\^d+X\d+X\d+.*$/', $fieldName)) {
                return $this->$fieldName;
            }
            $fullAnswer = null;
            var_dump($fieldName);
            die();
            $fieldType = $this->survey;
            $question = $this->fieldMap[$fieldName];
            $questionId = $question['qid'];
            $answer = null;
            if ($questionId)
            {
                $answers = $this->getAnswers($questionId);
                if (isset($answers[$answerCode]))
                {
                    $answer = $answers[$answerCode]['answer'];
                }
            }

            //echo "\n$fieldName: $fieldType = $answerCode";
            switch ($fieldType)
            {
                case 'R':   //RANKING TYPE
                    $fullAnswer = $answer;
                    break;

                case '1':   //Array dual scale
                    if (mb_substr($fieldName, -1) == 0)
                    {
                        $answers = $this->getAnswers($questionId, 0);
                    }
                    else
                    {
                        $answers = $this->getAnswers($questionId, 1);
                    }
                    if (array_key_exists($answerCode, $answers))
                    {
                        $fullAnswer = $answers[$answerCode]['answer'];
                    }
                    else
                    {
                        $fullAnswer = null;
                    }
                    break;

                case 'L':   //DROPDOWN LIST
                case '!':
                    if (mb_substr($fieldName, -5, 5) == 'other')
                    {
                        $fullAnswer = $answerCode;
                    }
                    else
                    {
                        if ($answerCode == '-oth-')
                        {
                            $fullAnswer = $translator->translate('Other', $sLanguageCode);
                        }
                        else
                        {
                            $fullAnswer = $answer;
                        }
                    }
                    break;

                case 'O':   //DROPDOWN LIST WITH COMMENT
                    if (isset($answer))
                    {
                        //This is one of the dropdown list options.
                        $fullAnswer = $answer;
                    }
                    else
                    {
                        //This is a comment.
                        $fullAnswer = $answerCode;
                    }
                    break;

                case 'Y':   //YES/NO
                    switch ($answerCode)
                    {
                        case 'Y':
                            $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                            break;

                        case 'N':
                            $fullAnswer = $translator->translate('No', $sLanguageCode);
                            break;

                        default:
                            $fullAnswer = $translator->translate('N/A', $sLanguageCode);
                    }
                    break;

                case 'G':
                    switch ($answerCode)
                    {
                        case 'M':
                            $fullAnswer = $translator->translate('Male', $sLanguageCode);
                            break;

                        case 'F':
                            $fullAnswer = $translator->translate('Female', $sLanguageCode);
                            break;

                        default:
                            $fullAnswer = $translator->translate('N/A', $sLanguageCode);
                    }
                    break;

                case 'M':   //MULTIOPTION
                case 'P':
                    if (mb_substr($fieldName, -5, 5) == 'other' || mb_substr($fieldName, -7, 7) == 'comment')
                    {
                        //echo "\n -- Branch 1 --";
                        $fullAnswer = $answerCode;
                    }
                    else
                    {
                        if ($answerCode == 'Y')
                        {
                            $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                        }
                        elseif ($answerCode == 'N' || $answerCode === '')   // Strict check for empty string to find null values
                        {
                            $fullAnswer = $translator->translate('No', $sLanguageCode);
                        } else {
                            $fullAnswer = $translator->translate('N/A', $sLanguageCode);
                        }
                    }
                    break;

                case 'C':
                    switch ($answerCode)
                    {
                        case 'Y':
                            $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                            break;

                        case 'N':
                            $fullAnswer = $translator->translate('No', $sLanguageCode);
                            break;

                        case 'U':
                            $fullAnswer = $translator->translate('Uncertain', $sLanguageCode);
                            break;
                    }
                    break;

                case 'E':
                    switch ($answerCode)
                    {
                        case 'I':
                            $fullAnswer = $translator->translate('Increase', $sLanguageCode);
                            break;

                        case 'S':
                            $fullAnswer = $translator->translate('Same', $sLanguageCode);
                            break;

                        case 'D':
                            $fullAnswer = $translator->translate('Decrease', $sLanguageCode);
                            break;
                    }
                    break;

                case 'F':
                case 'H':
                    $answers = $this->getAnswers($questionId, 0);
                    $fullAnswer = (isset($answers[$answerCode])) ? $answers[$answerCode]['answer'] : "";
                    break;

                default:

                    $fullAnswer .= $answerCode;
            }

            return $fullAnswer;
        }

        /**
         * Sets the value for a specific question code to the passed value.
         * If code cannot be resolved to a column will call the $invalidCode callback.
         *
         * @param string $code
         * @param string $value
         * @param callable $invalidCode
         * @return boolean True if the value was set, false if it was not set (invalid code or invalid value);
         */
        public function setAnswer($code, $value, callable $invalidCode = null)
        {
            // Get column.
            $column = $this->getColumn($code);
            if (!$this->hasAttribute($column)) {
                $invalidCode();
                $result = false;
            } else {
                /**
                 * @todo Validate the value.
                 */
                $result = $this->setAttribute($column, $value);
            }
            return $result;

        }

        public function getColumn($code) {
            if (strpos($code, '_') !== false) {
                list($title, $rest) = explode('_', $code);
                $rest = "_" . $rest;
            } else {
                $title = $code;
                $rest = '';
            }
            // Get all questions.
            if (isset($this->questions[$title])) {
                $question = $this->questions[$title];
                // Check if what column we need.
                $column = "{$this->surveyId}X{$question->gid}X{$question->qid}";
                return $column;
            }

        }


	}

?>
