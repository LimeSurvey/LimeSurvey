<?php

/**
 * @copyright 2022 LimeSurvey Team <https://limesurvey.org>
 * @author Denis Chenu <denis@sondages.pro>
 * @license GPL version 3
 * @version 0.1.0
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
class FunctionStatic extends PluginBase
{
    protected static $description = 'The function is used to return a static value of any expression.';
    protected static $name = 'FunctionStatic';

    /** @inheritdoc this plugin settings are update during getSettings */
    protected $settings = array(
        'information' => array(
            'type' => 'info',
            'content' => '',
            'default' => false
        ),
    );

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    public function init()
    {
        $this->subscribe('ExpressionManagerStart', 'newValidFunctions');
    }

    /**
     * @see https://www.limesurvey.org/manual/ExpressionManagerStart ExpressionManagerStart event
     * add the getAnswerOptionText static function to Expression Manager function
     * @return void
     */
    public function newValidFunctions()
    {
        Yii::setPathOfAlias("FunctionStatic", dirname(__FILE__));
        $newFunctions = array(
            'getStatic' => array(
                '\FunctionStatic\StaticFunctions::getStatic',
                null, // No javascript function : set as static function
                $this->gT("Return the equation as a static value even if question are in same group."), // Description for admin
                'string getStatic(expression)', // Extra description
                'https://limesurvey.org', // Help url
                1
            ),
        );
        $this->getEvent()->append('functions', $newFunctions);
    }

    /**
     * @inheritdoc
     * Update the information content
     */
    public function getPluginSettings($getValues = true)
    {
        $this->subscribe('getPluginTwigPath');
        $content = Yii::app()->twigRenderer->renderPartial('/StaticFunctionsInfo.twig', array());
        $this->settings['information']['content'] = $content;
        /* Just for langiuage po automatic system , core plugin. Translation from core*/
        $lang = array(
            "The function is used to return a static value of any expression." => gT("The function is used to return a static value of any expression."),
            "Simple usage to get the value of current response before any update: %s" => gT("Simple usage to get the value of current response before any update: %s")
        );
        return parent::getPluginSettings($getValues);
    }

    /**
     * Add some views for this and other plugin
     */
    public function getPluginTwigPath()
    {
        $viewPath = dirname(__FILE__) . "/views";
        $this->getEvent()->append('add', array($viewPath));
    }

    /**
     * @inheritdoc
     * But do nothing
     */
    public function saveSettings($settings)
    {
        // Nothing saved, not needed
    }
}
