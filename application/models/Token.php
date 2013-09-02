<?php

	class Token extends Dynamic
	{
		public function __construct($scenario = 'insert', $surveyId = null)
		{
			parent::__construct($scenario, $surveyId);
		}
		public static function model($className = null, $surveyId = null)
		{
			return parent::model(get_class(), $surveyId);
		}

		public function scopes()
		{
			return array(
				'incomplete' => array(
					'condition' => 'completed = "N"'
				),
				'usable' => array(
					'condition' => 'usesleft > 0'
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