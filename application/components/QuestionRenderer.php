<?php


class QuestionRenderer extends Renderer {

    public function render($step = null)
    {
        $step = isset($step) ? $step : $this->session->step;
        // Get the question we need to render:
        /** @var QuestionGroup $group */
        foreach($this->session->groups as $group) {
            if (count($group->questions) > $step) {
                $question = $group->questions[$step];
                break;
            } else {
                $step = $step - count($group->questions);
            }
        }
        $method = 'render' . strtr($question->type, [
            '|' => 'Text'
        ]);
        if (!method_exists($this, $method)) {
            throw new \Exception("Can't render question type {$question->type} ($method)");
        }
        $result = $this->renderLayout($this->$method($question));
        return $result;
    }



    protected function renderLayout($content) {
        return $content;
    }


    protected function renderO(Question $question)
    {
        $dropdownthreshold = Yii::app()->getConfig("dropdownthreshold");

        if ($this->session->survey->bool_nokeyboard) {
            includeKeypad();
            $kpclass = "text-keypad";
        } else {
            $kpclass = "";
        }

        $checkconditionFunction = "checkconditions";


        $aQuestionAttributes = $question->questionAttributes;
        $maxoptionsize = 35;

        //question attribute random order set?

        $answers = Answer::model()->findAllByAttributes([
            'qid' => $question->qid,
            'language' => $this->session->language,
            'scale_id' => 0
        ], [
            'order' => 'sortorder'
        ]);

        if (isset($aQuestionAttributes['random_order']) && $aQuestionAttributes['random_order']->value == 1) {
            shuffle($answers);
        } elseif (isset($aQuestionAttributes['random_order']) && $aQuestionAttributes['alphasort']->value == 1) {
            usort($answers, function (Answer $answer1, Answer $answer2) {
                return strcmp($answer1->answer, $answer2->answer);
            });
        }

        $hint_comment = gT('Please enter your comment here');
        $items = CHtml::listData($answers, 'code', 'answer');
        $selected = $this->session->response->{$question->sgqa};
        $options = [];

        if (isset($aQuestionAttributes['use_dropdown']) && $aQuestionAttributes['use_dropdown'] != 1 && count($answers) <= $dropdownthreshold) {
            if ($question->bool_other) {
                $items[''] = gT('No answer');
            }
            if (!isset($aQuestionAttributes['hide_tip']) || $aQuestionAttributes['hide_tip']->value != 1) {
                $options['help'] = gT('Choose one of the following answers');
            }
            $result = TbHtml::radioButtonListControlGroup($question->title, $selected,
                $items, $options);
            return $result;
        } else //Dropdown list
        {
            if ($question->bool_other) {
                $options['empty'] = gT('No answer');
            }
            if (!isset($aQuestionAttributes['hide_tip']) || $aQuestionAttributes['hide_tip']->value != 1) {
                $options['help'] = gT('Choose one of the following answers');
            }
            $result = TbHtml::dropDownListControlGroup("answers[{$question->qid}]", $selected, $items, $options);
            return $result;
        }
    }

    protected function renderP(Question $question) {
        $subQuestions = Question::model()->findAllByAttributes([
            'parent_qid' => $question->qid,
            'language' => $this->session->language,
        ], [
            'order' => 'question_order'
        ]);

        $aQuestionAttributes = $question->questionAttributes;
        if (isset($aQuestionAttributes['random_order']) && $aQuestionAttributes['random_order']->value == 1) {
            shuffle($answers);
        } elseif (isset($aQuestionAttributes['random_order']) && $aQuestionAttributes['alphasort']->value == 1) {
            usort($answers, function (Answer $answer1, Answer $answer2) {
                return strcmp($answer1->answer, $answer2->answer);
            });
        }

        $result = TbHtml::hiddenField("answers[{$question->qid}][choices]");
        foreach($subQuestions as $subQuestion) {
            $result .= TbHtml::checkBox("answers[{$question->qid}][choices][]", false, [
                'value' => $subQuestion->title,
                'label' => $subQuestion->question
            ]);
            $result .= TbHtml::textField("answers[{$question->qid}]['comments'][$subQuestion->title]");
        }
        return $result;
    }
}