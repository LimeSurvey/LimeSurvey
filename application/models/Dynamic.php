<?php
/**
 * This class implements the basis for dynamic models.
 * In this implementation class definitions are generated dynamically.
 * This class and its descendants should be declared abstract!
 */
abstract class Dynamic extends LSActiveRecord
{
    /**
     * Prefixed with _ to not collide with column names.
     * @var int The dynamic part of the class name.
     *
     */
    protected $dynamicId;

    /**
     * Dynamic constructor.
     * @param string $scenario
     */
    public function __construct($scenario = 'insert')
    {
        list(,$this->dynamicId) = explode('_', get_class($this));
        parent::__construct($scenario);
    }

    /**
     * @inheritdoc
     * @return Dynamic
     */
    public static function model($className = null)
    {
        if (!isset($className)) {
            $className = get_called_class();
        } elseif (is_numeric($className)) {
            $className = get_called_class().'_'.$className;
        }
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * @param string $scenario
     * @param integer $id
     * @return mixed
     */
    public static function create($id, $scenario = 'insert')
    {
        $className = get_called_class().'_'.$id;
        return new $className($scenario);
    }

    public function getDynamicId() {
        return $this->dynamicId;
    }
}
