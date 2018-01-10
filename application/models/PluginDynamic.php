<?php

/**
 * Dynamic model used by plugins to access their table(s).
 */
class PluginDynamic extends LSActiveRecord
{
    private static $_models = array();

    /** @var CActiveRecordMetaData $_md meta data*/
    private $_md;

    /** @var null|string $tableName */
    protected $tableName;

    /**
     * @param string $sTableName
     * @param string $scenario
     * @throws Exception
     */
    public function __construct($sTableName = null, $scenario = 'insert')
    {
        if (!isset($sTableName)) {
            //Yii::trace('sTableName missing.');
            throw new Exception('sTableName missing.');
        }
        $this->tableName = $sTableName;
        parent::__construct($scenario);
    }

    /** @inheritdoc */
    protected function instantiate($attributes = null)
    {
        $class = get_class($this);
        $model = new $class($this->tableName(), null);
        return $model;
    }

    /**
     * We have a custom implementation here since the parents' implementation
     * does not create a new model for each table name.
     * @param string $sTableName
     * @return Plugin
     */
    public static function model($sTableName = null)
    {
        if (isset($sTableName)) {
            if (!isset(self::$_models[$sTableName])) {
                $model = self::$_models[$sTableName] = new PluginDynamic($sTableName, null);
                $model->_md = new CActiveRecordMetaData($model);
                $model->attachBehaviors($model->behaviors());
            }
            return self::$_models[$sTableName];
        }
    }

    /** @inheritdoc */
    public function tableName()
    {
        return $this->tableName;
    }

    /**
     * Override
     * @return CActiveRecordMetaData the meta for this AR class.
     */
    public function getMetaData()
    {
        if ($this->_md !== null) {
            return $this->_md;
        } else {
            /** @var CActiveRecordMetaData $md */
            $md = self::model($this->tableName())->_md;
            return $this->_md = $md;
        }

    }

}
