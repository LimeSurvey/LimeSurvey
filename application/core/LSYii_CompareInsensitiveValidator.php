<?php
/*
 * LSYii_CompareInsensitiveValidator class file.
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
/*
 * LSYii_CompareInsensitiveValidator compares the specified attribute value as string with another value and validates if they are insensitive equal.
 */
class LSYii_CompareInsensitiveValidator extends CValidator
{
    /**
     * @var string the constant value to be compared with
     */
    public $compareValue;
    /**
     * @var boolean whether the attribute value can be null or empty. Defaults to false.
     * If this is true, it means the attribute is considered valid when it is empty.
     */
    public $allowEmpty = false;
    /**
     * @var string the operator for comparison. Defaults to '='.
     * The followings are valid operators:
     * <ul>
     * <li>'=' or '==': validates to see if the two values are equal. If {@link strict} is true, the comparison
     * will be done in strict mode (i.e. checking value type as well).</li>
     * <li>'!=': validates to see if the two values are NOT equal. If {@link strict} is true, the comparison
     * will be done in strict mode (i.e. checking value type as well).</li>
     * </ul>
     */
    public $operator = '=';

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     * @throws CException if invalid operator is used
     */
    protected function validateAttribute($object, $attribute)
    {
        $value = strtolower($object->$attribute);
        if ($this->allowEmpty && $this->isEmpty($value)) {
                    return;
        }
        if ($this->compareValue !== null) {
            $compareTo = $this->compareValue;
            $compareValue = strtolower($compareTo);
        } else {
                throw new CException('compareValue must be set when using LSYii_CompareInsensitiveValidator');
        }
        switch ($this->operator) {
            case '=':
            case '==':
                if ($value != $compareValue) {
                                    $message = $this->message !== null ? $this->message : sprintf(gT('%s must be case-insensitive equal to %s'), $attribute, $compareTo);
                }
                break;
            case '!=':
                if ($value == $compareValue) {
                                    $message = $this->message !== null ? $this->message : sprintf(gT('%s must not be case-insensitive equal to %s'), $attribute, $compareTo);
                }
                break;
            default:
                throw new CException(Yii::t('yii', 'Invalid operator "{operator}".', array('{operator}'=>$this->operator)));
        }
        if (!empty($message)) {
            $this->addError($object, $attribute, $message, array('{compareAttribute}'=>$compareTo, '{compareValue}'=>$compareValue));
        }
    }
}
