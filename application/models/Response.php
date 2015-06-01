<?php

	/**
	 * Relations
	 * @property Token $token
     * @property int $surveyId
	 * @property Survey $survey
	 */
	abstract class Response extends Dynamic
	{
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
		public static function model($className = null) {
            if (!is_numeric($className)) {
                var_dump($className); die();
            }
			return parent::model($className);
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
            parent::delete();
        }
		public function relations()
		{
            $t = $this->getTableAlias();
			$result = array(
				'token' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('token' => 'token')),
				'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}" )
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
            $columns['id'] = 'string(36) NOT NULL';
            $columns += $survey->columns;
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
	}

?>
