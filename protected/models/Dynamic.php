<?php
namespace ls\models;

use ls\models\ActiveRecord;

/**
 * This class implements the basis for dynamic models.
 * In this implementation class definitions are generated dynamically.
 * This class and its descendants should be declared abstract!
 */
abstract class Dynamic extends ActiveRecord
{
    private static $valid = [];
    /**
     * Prefixed with _ to not collide with column names.
     * @var int The dynamic part of the class name.
     *
     */
    protected $dynamicId;

    public function __construct($scenario = 'insert')
    {
        list(, $this->dynamicId) = explode('_', get_class($this));
        parent::__construct($scenario);
    }


    public static function constructTableName($id)
    {
        return '{{' . strtolower(get_called_class()) . "_$id}}";
    }

    public function tableName()
    {
        return static::constructTableName($this->dynamicId);
    }

    /**
     * @inheritdoc
     * @param int $className
     */
    public static function model($className = null)
    {
        if (!isset($className)) {
            $className = get_called_class();
        } elseif (is_numeric($className)) {
            $className = get_called_class() . '_' . $className;
        }
        if ($className == 'ls\models\Response') {
            throw new Exception('noo');
        }

        return parent::model($className);
    }

    /**
     * @param $id
     * @param string $scenario
     * @return static
     */
    public static function create($id, $scenario = 'insert')
    {
        $className = get_called_class() . '_' . $id;

        return new $className($scenario);
    }

    /**
     * This function checks if a table with the specified $id can be opened.
     * @param int $id
     * @return boolean Returns true if the table is found.
     */
    public static function valid($id, $refresh = false)
    {
        $tableName = static::constructTableName($id);
        if (is_numeric($id) && (!isset(self::$valid[$tableName]) || $refresh)) {
            bP('dynamic model table exists');
            self::$valid[$tableName] = App()->db->schema->tableExists($tableName);
            eP('dynamic model table exists');
        }
        return self::$valid[$tableName];
    }


}
