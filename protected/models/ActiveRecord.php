<?php
namespace ls\models;

use ls\interfaces\DependentRecordInterface;
use ls\traits\DependentRecordTrait;

/**
 * Class ActiveRecord
 * @package ls\models
 */
class ActiveRecord extends \CActiveRecord implements \JsonSerializable, DependentRecordInterface
{
    use DependentRecordTrait;
    /**
     * Lists the behaviors of this model
     *
     * Below is a list of all behaviors we register:
     * @see CTimestampBehavior
     * @see PluginEventBehavior
     * @return array
     */
    public function behaviors()
    {
        $result = [
            'PluginEventBehavior' => [
                'class' => \ls\pluginmanager\PluginEventBehavior::class
            ]
        ];

        return $result;
    }



    /**
     * Return the max value for a field
     *
     * This is a convenience method, that uses the primary key of the model to
     * retrieve the highest value.
     *
     * @param string $field The field that contains the Id, when null primary key is used if it is a single field
     * @param boolean $forceRefresh Don't use value from static cache but always requery the database
     * @return false|int
     */
    public function getMaxId($field = null)
    {
        if (is_null($field)) {
            $primaryKey = $this->primaryKey();
            if (is_string($primaryKey)) {
                $field = $primaryKey;
            } else {
                // Composite key, throw a warning to the programmer
                throw new Exception(sprintf('Table %s has a composite primary key, please explicitly state what field you need the max value for.',
                    $this->tableName()));
            }
        }

        return $this->dbConnection->createCommand()
            ->select('MAX(' . $this->dbConnection->quoteColumnName($field) . ')')
            ->from($this->tableName())
            ->queryScalar();


    }

    /**
     * @todo This should also be moved to the behavior at some point.
     * This method overrides the parent in order to raise PluginEvents for Bulk delete operations.
     *
     * Filter Criteria are wrapped into a CDBCriteria instance so we have a single instance responsible for holding the filter criteria
     * to be passed to the PluginEvent,
     * this also enables us to pass the fully configured CDBCriteria instead of the original Parameters.
     *
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer number of rows affected by the execution.
     */
    public function deleteAllByAttributes($attributes, $condition = '', $params = array())
    {
        $builder = $this->getCommandBuilder();
        $table = $this->getTableSchema();
        $criteria = $builder->createColumnCriteria($table, $attributes, $condition, $params);
        $this->dispatchPluginModelEvent('before' . get_class($this) . 'DeleteMany', $criteria);
        $this->dispatchPluginModelEvent('beforeModelDeleteMany', $criteria);

        return parent::deleteAllByAttributes(array(), $criteria, array());
    }

    /*
     * Creates a (nested) array of this objects' attributes and relations.
     */
    public function toArray($related = true, $exclude = [])
    {
        $result = $this->attributes;
        if ($related) {
            $result['related'] = [];
            $dependentRelations = array_diff(
                method_exists($this, 'dependentRelations') ? $this->dependentRelations() : [],
                $exclude
            );
            foreach ($dependentRelations as $name) {
                if (!in_array($name, $exclude)) {
                    if (is_array($this->$name)) {
                        $relatedArray = [];
                        foreach ($this->$name as $child) {
                            if (method_exists($child, 'toArray')) {
                                $relatedArray[] = $child->toArray();
                            } else {
                                $relatedArray[] = $child->attributes;
                            }
                        }
                        $result['related'][$name] = $relatedArray;
                    } else {
                        $result['related'][$name] = [$this->$name];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * unserialize() checks for the presence of a function with the magic name __wakeup.
     * If present, this function can reconstruct any resources that the object may have.
     * The intended use of __wakeup is to reestablish any database connections that may have been lost during
     * serialization and perform other reinitialization tasks.
     *
     * @return void
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.sleep
     */
    public function __wakeup()
    {
        // Re-attach behaviors.
        $this->attachBehaviors($this->behaviors());
    }


    public function __sleep()
    {
        $this->detachBehaviors();
        foreach (get_class_methods($this) as $method) {
            if (substr_compare($method, 'on', 0, 2) === 0) {
                /** @var CList $list */
                $list = $this->getEventHandlers($method);
                if ($list->getCount() > 0) {
                    vdd($list);
                    throw new \Exception("Cannot serialize AR with events.");
                }
            }
        }
        $result = array_flip(parent::__sleep());

        // Don't serialize validators.
        unset($result[chr(0) . 'CModel' . chr(0) . '_validators']);
        // Don't serialize behaviors.
        unset($result[chr(0) . 'CComponent' . chr(0) . '_m']);
        // Don't serialize events.
        unset($result[chr(0) . 'CComponent' . chr(0) . '_e']);

        return array_keys($result);
        // TODO: Implement serialize() method.
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->attributes;
    }

    /**
     * @return array List of relation names that contain only dependent records.
     */
    public function dependentRelations() {
        return [];
    }

    /**
     * Returns the static model of the specified AR class.
     * The model returned is a static instance of the AR class.
     * It is provided for invoking class-level methods (something similar to static class methods.)
     *
     * EVERY derived AR class must override this method as follows,
     * <pre>
     * public static function model($className=__CLASS__)
     * {
     *     return parent::model($className);
     * }
     * </pre>
     *
     * @param string $className active record class name.
     * @return static active record model instance.
     */
    public static function model($className = null)
    {
        return parent::model(!isset($className) ? get_called_class() :$className);
    }


}