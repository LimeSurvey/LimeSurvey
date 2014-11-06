<?php

	/**
	 * Relations
	 * @property Token $token
	 * @property Survey $survey
	 */
	abstract class Response extends Dynamic
	{

		/**
		 *
		 * @param mixed $className Either the classname or the survey id.
		 * @return Response
		 */
		public static function model($className = null) {
			return parent::model($className);
		}

		/**
		 *
		 * @param int $surveyId
		 * @param string $scenario
		 * @return Response Description
		 */
		public static function create($surveyId, $scenario = 'insert') {
			return parent::create($surveyId, $scenario);
		}

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
			$result = array(
				'token' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('token' => 'token')),
				'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}" )
			);
			return $result;
		}

		public function tableName()
		{
			return '{{survey_' . $this->dynamicId . '}}';
		}
	}

?>