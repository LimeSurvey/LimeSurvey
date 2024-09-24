<?php

/*
 * LimeSurvey
 * Copyright (C) 2023 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Disable update of a specific column
 * @author Denis Chenu
 */

class LSYii_DisableUpdateValidator extends CValidator
{
    /**
     * @inheritdoc
     * Disable update of an attribute
     * @link : https://bugs.limesurvey.org/view.php?id=18725
     */
    public function validateAttribute($object, $attribute)
    {
        if ($object->isNewRecord) {
            $object->$attribute = null;
            return;
        }
        if (empty($object->getPrimaryKey())) {
            throw new \InvalidArgumentException('Unable to use LSYii_DisableUpdateValidator without PrimaryKey');
        }
        $classOfObject = get_class($object);
        $originalObject = $classOfObject::model()->findByPk($object->getPrimaryKey());
        /* loose compare : 1 and '1' is same value for DB */
        if ($object->$attribute != $originalObject->$attribute) {
            $label = $object->getAttributeLabel($attribute);
            $this->addError($object, $attribute, sprintf(gT("%s can not be updated."), $label));
        }
    }
}
