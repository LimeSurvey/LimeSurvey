<?php
/**
 * Replace default survey list page by a default survey
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2013 LimeSurvey Team <http://www.limesurvey.org>
 * @license GPL v3
 * @version 1.0
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
    class defaultSurvey extends PluginBase
    {
        static protected $description = 'A plugin to set a default survey to replace ';
        static protected $name = 'defaultSurvey';
        protected $storage = 'DbStorage';

        protected $settings = array(
            'iDefaultSurveyId' => array(
                'type' => 'text',
                'label' => 'Default survey (id)',
                'default'=> false
            )
        );

        public function __construct(PluginManager $manager, $id) {
            parent::__construct($manager, $id);
            $this->subscribe('beforeSurveyPage');
        }

        public function beforeSurveyPage()
        {
            $oEvent = $this->event;
            // Doing nothing if no survey is is set
            if($iDefaultSurveyId=(int)$this->get('iDefaultSurveyId'))
            {
                $iSurveyId= (Yii::app()->request->getParam('sid') || Yii::app()->request->getParam('surveyid')) ;
                if(!$iSurveyId)
                {
                    // Validate if this survey exist and is active
                    $oSurvey=Survey::model()->find("sid=:sid",array(':sid'=>$iDefaultSurveyId));
                    if($oSurvey && $oSurvey->active=="Y")
                        $_POST['sid']=$oSurvey->sid;
                }
            }
        }
    }
?>
