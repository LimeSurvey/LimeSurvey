<?php

    /**
     * Dynamic response timing model.
     */
    class Timing extends LSActiveRecord
    {
        private static $_models = array();

		private $_md;								// meta data

        protected $surveyId;

		/**
         * @param string $scenario
         * @param int $iSurveyId
         */
        public function __construct($iSurveyId = null, $scenario = 'insert')
        {
			
            if (!isset($iSurveyId))
            {
                $iSurveyId = Response::getLastSurveyId();
            }

			$this->surveyId = $iSurveyId;
			parent::__construct($scenario);
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
				if (!isset(self::$_models[$iSurveyId]))
                {
                    $model = self::$_models[$iSurveyId] = new self($iSurveyId, null);
                    $model->_md = new CActiveRecordMetaData($model);
                    $model->attachBehaviors($model->behaviors());
                }
                return self::$_models[$iSurveyId];
            }
			throw new Exception('iSurveyId missing in static call.');
        }


		public function relations() {
			return array(
				'response' => array(self::BELONGS_TO, 'Response', 'id')
			);
		}

		/**
         * Gets the tablename for the current model.
         */
        public function tableName() {
            return "{{survey_{$this->surveyId}_timings}}";
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
