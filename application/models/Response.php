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
		
		public function relations()
		{
			$result = array(
				'token' => array(self::BELONGS_TO, 'Token_' . $this->id, array('token' => 'token')),
				'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->id}" )
			);
			return $result;
		}

		public function tableName()
		{
			return '{{survey_' . $this->id . '}}';
		}
	}

?>