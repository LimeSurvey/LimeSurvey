<?php

/**
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019-2022 LimeSurvey - Denis Chenu
 * @license GPL version 3
 * @version 0.2.0
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
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
class statFunctions extends PluginBase
{
    protected static $description = 'Add some function in ExpressionScript Engine to get count from other responses';
    protected static $name = 'statCountFunctions';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    public function init()
    {
        $this->subscribe('ExpressionManagerStart', 'newValidFunctions');
    }

    public function newValidFunctions()
    {
        Yii::setPathOfAlias(get_class($this), dirname(__FILE__));
        $newFunctions = array(
            'statCountIf' => array(
                '\statFunctions\countFunctions::statCountIf',
                null, // No javascript function : set as static function
                $this->gT("Count the number of complete responses  with a value equal to a specific value"), // Description for admin
                'integer statCountIf(QuestionCode.sgqa, value[, submitted = true][, self = true])', // Extra description
                'https://manual.limesurvey.org/StatFunctions', // Help url
                2, 3, 4 // Number of arguments : 2 , 3 or 4
            ),
            'statCount' => array(
                '\statFunctions\countFunctions::statCount',
                null, // No javascript function : set as static function
                $this->gT("Count the number of complete responses which are not empty"), // Description for admin
                'integer statCount(QuestionCode.sgqa[, submitted = true][, self = true])', // Extra description
                'https://manual.limesurvey.org/StatFunctions', // Help url
                1, 2, 3 // Number of argument : 1 , 2 or 3
            ),
        );
        $this->getEvent()->append('functions', $newFunctions);
    }
}
