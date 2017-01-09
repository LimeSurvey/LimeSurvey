<?php

    /**
     * Dynamic model used by plugins to access their table(s).
     */
    class PluginDynamic extends LSActiveRecord
    {
        private static $_models = array();

        private $_md;								// meta data
	
        protected $tableName;

        /**
         * @param string $scenario
         * @param string $sTableName
         */
        public function __construct($sTableName = null, $scenario = 'insert')
        {
            if (!isset($sTableName))
            {
                //Yii::trace('sTableName missing.');
                throw new Exception('sTableName missing.');
            }
            $this->tableName = $sTableName;
            parent::__construct($scenario);
        }


        protected function instantiate($attributes = null)
        {
            $class=get_class($this);
            $model=new $class($this->tableName(), null);
            return $model;
        }
        /**
         * We have a custom implementation here since the parents' implementation
         * does not create a new model for each table name.
         * @return Plugin
         */
        public static function model($sTableName = null)
        {
            if (isset($sTableName))
            {
                if (!isset(self::$_models[$sTableName]))
                {
                    $model = self::$_models[$sTableName] = new PluginDynamic($sTableName, null);
                    $model->_md = new CActiveRecordMetaData($model);
                    $model->attachBehaviors($model->behaviors());
                }
                return self::$_models[$sTableName];
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
                return $this->_md=self::model($this->tableName())->_md;

        }

    }

?>