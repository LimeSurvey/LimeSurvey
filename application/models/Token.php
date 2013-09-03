<?php
/**
 * 
 * For code completion we add the available scenario's here
 * 
 * @method incomplete() incomplete() Select only uncompleted tokens
 * @method Token usable() usable() Select usable tokens: valid daterange and userleft > 0
 */
	class Token extends Dynamic
	{
		public function __construct($scenario = 'insert', $surveyId = null)
		{
			parent::__construct($scenario, $surveyId);
		}
        
        /**
         * Get the token model
         * 
         * @param string $className Normally you specify null here
         * @param int $surveyId 
         * @return Token
         * @throws Exception
         */
		public static function model($className = null, $surveyId = null)
		{
			if (!is_numeric($surveyId))
			{
				throw new Exception('SurveyID must be numeric.');
			}
			return parent::model(get_class(), $surveyId);
		}

		public function scopes()
		{
			return array(
				'incomplete' => array(
					'condition' => 'completed = "N"'
				),
				'usable' => array(
					'condition' => 'usesleft > 0 AND COALESCE(validfrom, NOW()) >= NOW() AND COALESCE(validfrom, NOW()) <= NOW()'
				)
			);
		}
		public function summary()
		{
			$criteria = $this->getDbCriteria();
			$criteria->select = array(
				"COUNT(*) as count",
				"COUNT(CASE WHEN (token IS NULL OR token='') THEN 1 ELSE NULL END) as invalid",
				"COUNT(CASE WHEN (sent!='N' AND sent<>'') THEN 1 ELSE NULL END) as sent",
				"COUNT(CASE WHEN (emailstatus LIKE 'OptOut%') THEN 1 ELSE NULL END) as optout",
				"COUNT(CASE WHEN (completed!='N' and completed<>'') THEN 1 ELSE NULL END) as completed"
			);
			$command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(),$criteria);
			return $command->queryRow();
		}

		public function tableName() {
			return "{{tokens_{$this->tableName}}}";
		}

	}

?>