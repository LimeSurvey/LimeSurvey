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
     * @param mixed $className Either the classname or the dynamic part of it (e.g. the survey ID).
     * @return Dynamic
     */
    public static function model($className = null)
    {
        if (!isset($className)) {
            $className = get_called_class();
        } elseif (is_numeric($className)) {
            $className = self::baseClassName() . '_' . $className;
        }
        /** @var self $model */
        $model = parent::model($className);
        $model->refreshOutdatedMetaData();
        return $model;
    }

    /**
     * @param string $scenario
     * @param integer $id
     * @return mixed
     */
    public static function create($id, $scenario = 'insert')
    {
        $className = self::baseClassName() . '_' . $id;
        // Make sure the new record gets the current structure of the table
        self::model($className);
        return new $className($scenario);
    }

    /**
     * The table structure of a model is cached per class for the rest of the request, but the
     * table of a dynamic model can be created, changed or replaced within a request (activating
     * a survey, adding a participant attribute, ...).
     * So the structure is read again when the schema cache of the database connection was
     * refreshed for this table since the model has read it.
     *
     * @return void
     */
    protected function refreshOutdatedMetaData()
    {
        $tableSchema = $this->getDbConnection()->getSchema()->getTable($this->tableName());
        if ($tableSchema !== null && $tableSchema !== $this->getMetaData()->tableSchema) {
            $this->refreshMetaData();
        }
    }

    /**
     * The name of the called class without the dynamic part, e.g. "Token" for "Token_123".
     * This allows calling model() and create() from within a generated class too.
     * @return string
     */
    protected static function baseClassName()
    {
        return explode('_', get_called_class())[0];
    }

    /**
     * Makes the model of the given ID read its meta data (table structure, relations) again on next use.
     * This happens automatically when the schema cache of the table of the model was refreshed, see
     * refreshOutdatedMetaData(). Only needed if the meta data depends on something else.
     *
     * @param integer $id The dynamic part of the class name (e.g. the survey ID)
     * @return void
     */
    public static function resetMetaData($id)
    {
        $className = self::baseClassName() . '_' . $id;
        // A class that was never loaded can not hold outdated meta data
        if (class_exists($className, false)) {
            (new $className(null))->refreshMetaData();
        }
    }

    /**
     * Returns the HTML name of a model, see CHtml::modelName().
     * Dynamic models use the name of their base class (e.g. "Token" instead of "Token_123"),
     * so the names of form fields and grid parameters do not depend on the survey.
     *
     * @param CModel|string $model The model or its class name
     * @return string
     */
    public static function htmlModelName($model)
    {
        $className = is_object($model) ? get_class($model) : (string) $model;
        if (is_subclass_of($className, self::class)) {
            $parentClassName = get_parent_class($className);
            // Only for the generated classes, which are named after their parent
            if (strpos($className, $parentClassName . '_') === 0) {
                $className = $parentClassName;
            }
        }
        return trim(str_replace('\\', '_', $className), '_');
    }

    /**
     * @return string The dynamic part of the class name (e.g. the survey ID)
     */
    public function getDynamicId()
    {
        return $this->dynamicId;
    }
}
