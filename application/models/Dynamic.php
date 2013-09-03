<?php

    /**
     * Dynamic model usable for multiple tables.
     */
    class Dynamic extends CActiveRecord
    {
        private static $_models = array();

        private $_md;								// meta data

        private $tableName;

        /**
         * @param string $scenario
         * @param string $tableName
         */
        public function __construct($scenario = 'insert', $tableName = null)
        {
            if (!isset($tableName))
            {
                //Yii::trace('sTableName missing.');
                throw new Exception('$tableName missing.');
            }
            $this->tableName = $tableName;
            parent::__construct($scenario);
        }


        protected function instantiate($attributes)
        {
			$class = get_class($this);
			$model = new $class(null, $this->tableName);
            return $model;
        }
        /**
         * We have a custom implementation here since the parents' implementation
         * does not create a new model for each table name.
         * @param type $className
         * @return Plugin
         */
        public static function model($className = __CLASS__, $tableName = null)
        {
            if (isset($tableName))
            {
                if (!isset(self::$_models[$tableName]))
                {
                    $model = self::$_models[$tableName] = new $className(null, $tableName);
                    $model->_md = new CActiveRecordMetaData($model);
                    $model->attachBehaviors($model->behaviors());
                }
                return self::$_models[$tableName];
            }
			else
			{
				throw new Exception('$tableName missing.');
			}
        }

        /**
         * Gets the tablename for the current model.
         */
        public function tableName() {
            return $this->tableName;
        }

        /**
         * Override
         * @return CActiveRecordMetaData the meta for this AR class.
         */
        public function getMetaData()
        {
            if($this->_md!==null)
                return $this->_md;
            else
                return $this->_md = self::model(get_class($this), $this->tableName)->_md;
        }

    }

?>