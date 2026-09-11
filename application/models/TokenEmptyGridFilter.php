<?php

/*
   * LimeSurvey
   * Copyright (C) 2013-2026 The LimeSurvey Project Team
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
 * Class TokenEmptyGridFilter
 *
 * Lightweight form model used as the filter for the empty participant grid shown before
 * the participant table exists. The real grid filters with TokenDynamic, but that model
 * can't be instantiated without a physical table. This model just needs to expose the
 * grid's column names as attributes so CGridView can render the standard filter inputs.
 * The inputs are only presentational here — there's no data to filter yet.
 */
class TokenEmptyGridFilter extends CFormModel
{
    /** @var array<string,mixed> attribute name => value */
    private $attributeValues = array();

    /** @var string[] ordered attribute names */
    private $attributeNamesList = array();

    /**
     * @param string[] $attributeNames the grid column names to expose as filterable attributes
     */
    public function __construct(array $attributeNames)
    {
        $this->attributeNamesList = $attributeNames;
        foreach ($attributeNames as $name) {
            $this->attributeValues[$name] = null;
        }
        parent::__construct();
    }

    /** @inheritdoc */
    public function attributeNames()
    {
        return $this->attributeNamesList;
    }

    /** @inheritdoc */
    public function rules()
    {
        if (empty($this->attributeNamesList)) {
            return array();
        }
        return array(
            array(implode(',', $this->attributeNamesList), 'safe'),
        );
    }

    /** @inheritdoc */
    public function __get($name)
    {
        if (array_key_exists($name, $this->attributeValues)) {
            return $this->attributeValues[$name];
        }
        return parent::__get($name);
    }

    /** @inheritdoc */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->attributeValues)) {
            $this->attributeValues[$name] = $value;
            return;
        }
        parent::__set($name, $value);
    }

    /** @inheritdoc */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->attributeValues)) {
            return isset($this->attributeValues[$name]);
        }
        return parent::__isset($name);
    }
}
