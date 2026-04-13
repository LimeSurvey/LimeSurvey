<?php

/*
 * LimeSurvey
 * Copyright (C) 2020 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Disable update of a specific column, used for Question->script in 4.0.0
 * @author Denis Chenu
 * @since 4.0.0-RC13
 */

class LSYii_NoUpdateValidator extends CValidator
{

    /**
     * @inheritdoc
     * Filter attribute transparently to disallow update
     * Used for script with XSS activate currently
     * @link : https://bugs.limesurvey.org/view.php?id=15690
     */
    public function validateAttribute($object, $attribute)
    {
        if ($object->isNewRecord) {
            $object->$attribute = '';
            return;
        }
        if (empty($object->getPrimaryKey())) {
            throw new \InvalidArgumentException('Unable to use LSYii_NoUpdateValidator without PrimaryKey');
        }
        $classOfObject = get_class($object);
        $originalObject = $classOfObject::model()->findByPk($object->getPrimaryKey());
        $object->$attribute = $originalObject->$attribute;
    }
}
