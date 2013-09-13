<?php
abstract class Response2 extends Dynamic2
	{
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