<?php
/*
* LimeSurvey
* Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
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
     * This widget display a position selection for a question inside a group. It's used for now in "add new question".
     * It has two modes :
     *
     * - static mode (display = form_group) : just render a select with "At end", "At Bengining", "After question..." for each question of the group
     *   this mode imply that the group can't be change in the same view that the widget
     *
     * - ajax mode (display = ajax_form_group): In ajax mode, the widget will first render an hidden input where is called.
     *    This hidden input contains the necessary datas to generate a static position selector.
     *    Then it will register some js, that will insert after this hidden input a static position selector based on the datas of the hidden input.
     *    If the question group selector change, it will update the datas of the hidden input, and regenerate a new position selector
     */
    class PositionWidget extends CWidget
    {
        public $display             = 'form_group';                                     // What kind of rendering to use. For now, only form_group, to display inside right menu
        public $oQuestionGroup      = '';                                               // Which question group the position is related to
        public $oSurvey             = '';
        public $reloadAction        = 'admin/questions/sa/ajaxReloadPositionWidget';    // In ajax mode, name of the controller/action to call to reload the widget. Update this value if you want to use the widget outside of the Questions controller (that should never happen, and if it happens, then it would be better to update this widget to a Yii module)
        public $dataGroupSelectorId = 'gid';                                            // In ajax mode, the id of the group selector the widget is listening to.
        public $classes             = '';

        public function run()
        {
            // We first check if a question group object has been provided
            if ( is_a($this->oQuestionGroup, 'QuestionGroup') || is_a($this->oSurvey, 'Survey') )
            {
                // If oQuestionGroup is not defined, we take the first group in the survey
                if (!is_a($this->oQuestionGroup, 'QuestionGroup'))
                {
                    $aGroups              = $this->oSurvey->groups;

                    if (count($aGroups) > 0)
                    {
                        $this->oQuestionGroup = $aGroups[0];
                    }
                    else
                    {
                        return;
                    }

                }

                $aQuestions = $this->oQuestionGroup->questions; // Get the list of questions in this group


                // We check if the required view exists. In the future, if we want other type of rendering could be useful
                if ($this->isView($this->display))
                {
                    $this->render($this->display, array('aQuestions' => $aQuestions));

                    // In ajax mode, we need to register this JS file
                    if ($this->display=='ajax_form_group')
                    {
                        Yii::app()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/reload_position.js'));
                    }
                }
                else
                {
                    return $this->render('unkown_view');
                }
            }
            else
            {
                return $this->render('no_group');
            }
        }

        private function isView($display)
        {
            return in_array($display, array('form_group', 'ajax_form_group'));
        }
    }
