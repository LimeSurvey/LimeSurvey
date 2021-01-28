<?php

/**
 * Class StaticModel
 * A general class to use in case of non-db models
 */
class StaticModel extends CModel
{

    /**
     * Models attributes as array indexed by primary key
     * @return array
     */
    public static function modelsAttributes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeNames()
    {
        return [];
    }

    /**
     * @param string $pk primary key of model
     * @return null|static
     */
    public static function findOne($pk)
    {
        $modelsAttributes = static::modelsAttributes();
        if (isset($modelsAttributes[$pk])) {
            $model = new static();
            $model->attributes = $modelsAttributes[$pk];
            return $model;
        }
        return null;
    }
}
