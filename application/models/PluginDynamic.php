<?php

    /**
     * Dynamic model used by plugins to access their table(s).
     */
    class PluginDynamic extends LSActiveRecord
    {
        private static $_models = array();

        protected $tableName;

        /**
         * @param string $scenario
         * @param string $sTableName
         */
        public function __construct($sTableName, $scenario = 'insert')
        {
            parent::__construct($scenario);
            $this->tableName = $sTableName;
        }

        /**
         * We have a custom implementation here since the parents' implementation
         * does not create a new model for each table name.
         * @param type $className
         * @return Plugin
         */
        public static function model($sTableName = null)
        {
            if (isset($sTableName))
            {
                if (!isset(self::$_models[$sTableName]))
                {
                    $model = self::$_models[$sTableName] = new PluginDynamic($sTableName);
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
            parent::tableName();
        }


    }

?>