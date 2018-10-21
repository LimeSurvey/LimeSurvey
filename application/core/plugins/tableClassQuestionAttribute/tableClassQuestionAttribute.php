<?php
/**
 * tableClassQuestionAttribute Plugin for LimeSurvey
 * Set a class for table of answers in LimeSurvey, allowed : no-more-table, table-responsive, none (see what happen …)
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2018 LimeSurvey Team <http://www.limesurvey.org>
 * @license GPL v3
 * @version 1.0.0
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
 */
class tableClassQuestionAttribute extends PluginBase {
    protected $storage = 'DbStorage';

    static protected $description = 'Allow to choose table system for litlle screen : no-more-table, responsive, none .';
    static protected $name = 'noMoreTableAttribute';

    public function init() {
        $this->subscribe('beforeQuestionRender','setClassTableAttribute');
        $this->subscribe('newQuestionAttributes','addClassTableAttribute');
    }

    /**
     * @see https://manual.limesurvey.org/NewQuestionAttributes
     * Wrap the answer part with a div with needed class name
     * @return void
     */
    public function addClassTableAttribute() {
        $extraAttributes = array(
            'table_class'=>array(
                'types' => "ABCEF1:;",
                'category' => gT('Display'),
                'sortorder' => 200, /* Before cssclass */
                'inputtype' => 'singleselect',
                'options' => array(
                    'no-more-table' => gT("Usage of no more table."),
                    'table-responsive' => gT("Usage of table-responsive."),
                    'none' => gT("No specific class."),
                ),
                'default' => 'no-more-table',
                'help' => sprintf(
                    gT('In little screen, choose how you want to display table, using %s , %s or no specific class.'),
                    CHtml::link("No more table","https://css-tricks.com/responsive-data-tables/",array("rel"=>"help")),
                    CHtml::link("Responsive tables","https://getbootstrap.com/docs/3.3/css/#tables-responsive",array("rel"=>"help"))
                ),
                'caption' => gT('Table style for little screen'),
            ),
        );
        $this->getEvent()->append('questionAttributes', $extraAttributes);
    }

    /**
     * @see https://manual.limesurvey.org/BeforeQuestionRender
     * Wrap the answer part with a div with needed class name
     * @return void
     */
    public function setClassTableAttribute()
    {
        
        $aQuestionAttributes=QuestionAttribute::model()->getQuestionAttributes($this->getEvent()->get('qid'),Yii::app()->getLanguage());
        if(!empty($aQuestionAttributes['table_class'])) {
            switch ($aQuestionAttributes['table_class']) {
                case 'no-more-table':
                default:
                    $this->addAndRegisterPackage();
                    $className = "no-more-table";
                    break;
                case 'table-responsive':
                    $className = "table-responsive";
                    break;
                case 'none':
                    $className = "";
                    break;
            }
            if($className) {
                /* Wrap answers part in a div with needed class */
                $this->getEvent()->set("answers",CHtml::tag("div",array("class"=>$className),$this->getEvent()->get("answers")));
            }
        }
        
    }

    /**
     * register needed package (currently only no-more-table is needed)
     * @return void
     */
    public function addAndRegisterPackage()
    {
        /* Quit if is done */
        if(array_key_exists('no-more-table',Yii::app()->getClientScript()->packages)) {
            return;
        }
        /* Add package only if don't exist currently (can be replaced by user config) */
        if(!Yii::app()->clientScript->hasPackage('no-more-table')) {
            Yii::setPathOfAlias(get_class($this), dirname(__FILE__));
            Yii::app()->clientScript->addPackage('no-more-table', array(
                'basePath'    => get_class($this).'.assets',
                'css'          => array('no-more-table.css'),
                'depends'      =>array('limesurvey-public'),
            ));
        }
        Yii::app()->getClientScript()->registerPackage('no-more-table');
    }
}
