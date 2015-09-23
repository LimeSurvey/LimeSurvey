<?php
namespace befound\ls\ModulePlugin\models;

class User extends ActiveRecord
{
    public function tableName() {
        return '{{user}}';
    }
}