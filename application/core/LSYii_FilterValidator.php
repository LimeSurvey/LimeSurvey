<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Extends CFilterValidator class to add PHP 8.1 compatibility. Based on Yii 2's FilterValidator
 * @inheritdoc
 */
class LSYii_FilterValidator extends CFilterValidator
{
    /**
     * @var bool whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     */
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    protected function validateAttribute($object, $attribute)
    {
        if ($this->skipOnEmpty && empty($object->$attribute)) {
            return;
        }
        return parent::validateAttribute($object, $attribute);
    }
}
