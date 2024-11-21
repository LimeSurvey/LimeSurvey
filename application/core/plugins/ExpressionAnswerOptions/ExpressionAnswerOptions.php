<?php

/**
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2021 Respondage <https://www.respondage.nl/>
 * @copyright 2021 Denis Chenu <https://www.sondages.pro>
 * @license GPL version 3
 * @version 0.2.2
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
class ExpressionAnswerOptions extends PluginBase
{
    protected static $description = 'Expression Script: make answer option text available.';
    protected static $name = 'ExpressionAnswerOptions';

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
     * @see https://manual.limesurvey.org/ExpressionManagerStart ExpressionManagerStart event
     * add the getAnswerOptionText static function to Expression Manager function
     * @return void
     */
    public function newValidFunctions()
    {
        Yii::setPathOfAlias("ExpressionAnswerOptions", dirname(__FILE__));
        $newFunctions = array(
            'getAnswerOptionText' => array(
                '\ExpressionAnswerOptions\AnswerOptionsFunctions::getAnswerOptionText',
                null, // No javascript function : set as static function
                $this->gT("Return the answer text related to a question by answer code"), // Description for admin
                'string getAnswerOptionText(QuestionCode, code[, scale = 0])', // Extra description
                'https://www.respondage.nl', // Help url
                2, // Number of argument : minimum 2, allow 3
                3
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
        $content = Yii::app()->twigRenderer->renderPartial('/expressionAnswerOptionsInfo.twig', array());
        $this->settings['information']['content'] = $content;
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
