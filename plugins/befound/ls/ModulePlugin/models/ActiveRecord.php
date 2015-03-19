<?php
namespace befound\ls\ModulePlugin\models;

class ActiveRecord extends \CActiveRecord
{
    public static function model($className = null) {
        $className = !isset($className) ? get_called_class() : $className;
        return parent::model($className);
    }
}