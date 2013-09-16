<?php

	Yii::import('application.helpers.ClassFactory');

	/**
	 * This class implements the basis for dynamic models.
	 * In this implementation class definitions are generated dynamically.
	 * This class and its descendants should be declared abstract!
	 */
	abstract class Dynamic2 extends LSActiveRecord
	{
		protected $id;

		public function __construct($scenario = 'insert') {
			parent::__construct($scenario);
			$aTemp=explode('_', get_class($this));
			$this->id = $aTemp[1];
		}

		public static function model($className = null) {
			$className = !isset($className) ? get_called_class() : $className;
			return parent::model($className);
		}

	}

?>
