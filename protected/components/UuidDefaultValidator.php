<?php
namespace ls\components;

use CDefaultValueValidator;

class UuidDefaultValidator extends CDefaultValueValidator
{
    protected function validateAttribute($object, $attribute)
    {
        $this->value = \Cake\Utility\Text::uuid();
        parent::validateAttribute($object, $attribute);
    }

}