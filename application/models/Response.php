<?php

    /**
     * Dynamic response model.
     */
    class Response extends LSActiveRecord
    {
        private static $_models = array();

		/**
		 * Store the last initialized model / survey id here.
		 * Yii has some bugs in CSort that initialize the model while not allowing
		 * for customization; this should fix it in most cases.
		 */
		private static $lastSurveyId;

        private $_md;								// meta data

        protected $surveyId;

		public function attributeNames() {
			if (isset($this->surveyId))
			{
				return parent::attributeNames();
			}
			else
			{
				return array();
			}
		}
        /**
         * @param string $scenario
         * @param int $iSurveyId
         */
        public function __construct($iSurveyId = null, $scenario = 'insert')
        {
            if (!isset($iSurveyId))
            {
                $iSurveyId = self::$lastSurveyId;
            }

			$this->surveyId = $iSurveyId;
			parent::__construct($scenario);
        }

		public static function getLastSurveyId()
		{
			return self::$lastSurveyId;
		}

		protected function instantiate($attributes)
        {
            $class=get_class($this);
            $model=new $class($this->surveyId, null);
            return $model;
        }
        /**
         * We have a custom implementation here since the parents' implementation
         * does not create a new model for each table name.
         * @param int $iSurveyId
         * @return Response
         */
        public static function model($iSurveyId = null)
        {
			if (is_numeric($iSurveyId))
            {
				self::$lastSurveyId = $iSurveyId;
				if (!isset(self::$_models[$iSurveyId]))
                {
                    $model = self::$_models[$iSurveyId] = new self($iSurveyId, null);
                    $model->_md = new CActiveRecordMetaData($model);
                    $model->attachBehaviors($model->behaviors());
                }
                return self::$_models[$iSurveyId];
            }
			new Exception('iSurveyId missing in static call.');
        }


		public function relations() {
			return array(
				'timing' => array(self::HAS_ONE, 'Timing', 'id')
			);
		}

		/**
         * Gets the tablename for the current model.
         */
        public function tableName() {
            return "{{survey_{$this->surveyId}}}";
        }

        /**
         * Override
         * @return CActiveRecordMetaData the meta for this AR class.
         */
        public function getMetaData()
        {
            if(isset($this->_md))
                return $this->_md;
			else
                return $this->_md=self::model($this->surveyId)->_md;
        }
    }

?>