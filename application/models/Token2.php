<?php
	/**
	 *
	 * For code completion we add the available scenario's here
	 *
	 * @method Token incomplete() incomplete() Select only uncompleted tokens
	 * @method Token usable() usable() Select usable tokens: valid daterange and userleft > 0
	 * @property Survey $survey The survey this token belongs to.
	 */
	abstract class Token2 extends Dynamic2
	{

		public function beforeDelete() {
			$result = parent::beforeDelete();
			if ($result && isset($this->surveylink))
			{
				if (!$this->surveylink->delete())
				{
					throw new CException('Could not delete survey link. Token was not deleted.');
				}
				return true;
			}
			return $result;
		}
		public function generateToken()
		{
			$length = $this->survey->tokenlength;

			$this->token = randomChars($length);
			$counter = 0;
			while (!$this->validate('token'))
			{
				$this->token = randomChars($length);
				$counter++;
				// This is extremely unlikely.
				if ($counter > 10)
				{
					throw new CHttpException(500, 'Failed to create unique token in 10 attempts.');
				}
			}
		}

		public function relations()
		{
			$result = array(
				'responses' => array(self::HAS_MANY, 'Response_' . $this->id, array('token' => 'token')),
				'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->id}"),
				'surveylink' => array(self::HAS_ONE, 'SurveyLink', 'token_id', 'on' => "survey_id = {$this->id}")
			);
			return $result;
		}

		public function rules()
		{
			
			return array(
				array('token', 'unique', 'allowEmpty' => true),
				array(implode(',', $this->tableSchema->columnNames), 'safe')
			);
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

		public function tableName()
		{
			return '{{tokens_' . $this->id . '}}';
		}

	}

?>
