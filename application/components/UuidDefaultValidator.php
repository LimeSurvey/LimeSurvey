<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 6/1/15
 * Time: 7:24 PM
 */

class UuidDefaultValidator extends CDefaultValueValidator
{
    protected function validateAttribute($object, $attribute)
    {
        $this->value = \Cake\Utility\Text::uuid();
        parent::validateAttribute($object, $attribute);
    }

}