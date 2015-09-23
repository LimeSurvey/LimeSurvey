<?php

	/**
	 * Relations
	 * @property Survey $survey
	 */
	abstract class Timing extends Dynamic
	{
        /**
		 *
		 * @param mixed $className Either the classname or the survey id.
		 * @return Timing
		 */
		public static function model($className = null) {
			return parent::model($className);
		}

		/**
		 *
		 * @param int $surveyId
		 * @param string $scenario
		 * @return Timing Description
		 */
		public static function create($surveyId, $scenario = 'insert') {
			return parent::create($surveyId, $scenario);
		}

		public function relations()
		{
            $t = $this->getTableAlias();
			$result = array(
				'timing' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('id' => 'id')),
				'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}" )
			);
			return $result;
		}

        public function tableName()
		{
			return '{{survey_' . $this->dynamicId . '}}';
		}
        
        
        public static function createTable(Survey $survey, &$messages = [])
        {
            return;
        }
	}


