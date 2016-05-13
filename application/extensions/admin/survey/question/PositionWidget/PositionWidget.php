<?php

    class PositionWidget extends CWidget
    {
        public $display         = 'form_group';                         // What kind of rendering to use. For now, only form_group, to display inside right menu
        public $oQuestionGroup  = '';                                   // Which question group the position is related to
        public $reloadAction    = 'admin/questions/sa/ajaxReloadPositionWidget';  // In ajax mode, name of the controller/action to call to reload the widget

        public function run()
        {
            if ( is_a($this->oQuestionGroup, 'QuestionGroup') )
            {
                $aQuestions = $this->oQuestionGroup->questions; // Get the list of questions in this group
                if ($this->isView($this->display))
                {
                    $this->render($this->display, array('aQuestions' => $aQuestions));

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
