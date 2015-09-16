<?php
/** @var Survey $survey */
foreach($survey->questions as $question) {
    echo \TbHtml::openTag('div');
    echo \TbHtml::tag('h2', [], $question->getDisplayLabel());
    // Check subquestions.
    if ($question->hasSubQuestions) {
        foreach($question->getSubQuestions() as $subQuestion) {
            echo TbHtml::tag('h3', [], $subQuestion->displayLabel);
        }
    }
//vd($question->getAnswers());
    foreach($question->getAnswers() as $code => $text) {
        if ($code != '{TEXTRIGHT}') {
            if ($text instanceof Answer) {
                $text = $text->answer;
            }
            echo TbHtml::tag('p', [], $text);
        }
    }
//    foreach($question->getFields() as $field) {
//                vd($field->getCode());
//                vd($field->getLabels());
//        vd($field->getCode() . ' -- ' .  $field->getQuestion()->getDisplayLabel());

//    }
    echo \TbHtml::closeTag('div');
}
