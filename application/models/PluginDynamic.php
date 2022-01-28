<?php

/**
 * Dynamic model used by plugins to access their table(s).
 */
class PluginDynamic extends LSActiveRecord
{
    private static $models = array();

    /** @var CActiveRecordMetaData $md meta data*/
    private $md;

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
     *
     * @param string $className Table name
     * @return Plugin
     */
    public static function model($className = null)
    {
        if (isset($className)) {
            if (!isset(self::$models[$className])) {
                $model = self::$models[$className] = new PluginDynamic($className, null);
                $model->md = new CActiveRecordMetaData($model);
                $model->attachBehaviors($model->behaviors());
            }
            return self::$models[$className];
        }
        return null;
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
        if ($this->md !== null) {
            return $this->md;
        } else {
            /** @var CActiveRecordMetaData $md */
            $md = self::model($this->tableName())->md;
            return $this->md = $md;
        }
    }
}
