<?php

/**
 * Plugin to redirect old url system (index.php?sid=surveyid) to the new url
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016-2020 LimeSurvey team <https://www.limesurvey.org>
 * @license GPL v3
 * @version 1.0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
class oldUrlCompat extends PluginBase
{
    protected static $name = 'oldUrlCompat';
    protected static $description = 'Old url (pre-2.0) compatible system';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    public function init()
    {
        $this->subscribe('afterPluginLoad', 'setUrlCompat');
    }

    /**
     * Forward survey controller if we are in default controller and a sid GET parameters is set
     * @return void
     */
    public function setUrlCompat()
    {
        if (App()->getController() && App()->getController()->getId() === "surveys" && App()->request->getQuery('sid')) {
            Yii::app()->getController()->forward('survey/index');
        }
    }
}
