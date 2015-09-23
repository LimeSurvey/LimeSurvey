<?php
namespace befound\ls\ModulePlugin\models;

class Email extends ActiveRecord
{
    public function tableName() {
        return '{{email}}';
    }
    

}