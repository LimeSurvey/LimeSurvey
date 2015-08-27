<?php
/** @var TbActiveForm $form */
/** @var Question $question */
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($question, 'preg');
if ($question->hasAttribute('em_validation_q')) {
    echo $form->textFieldControlGroup($question, 'em_validation_q');
    echo $form->textFieldControlGroup($question, 'em_validation_q_tip');
}

if ($question->hasAttribute('validation_sq')) {
    echo $form->textFieldControlGroup($question, 'validation_sq');
    echo $form->textFieldControlGroup($question, 'validation_sq_tip');
}

if ($question->hasAttribute('min_answers')) {
    echo $form->numberFieldControlGroup($question, 'min_answers');
}
if ($question->hasAttribute('max_answers')) {
    echo $form->numberFieldControlGroup($question, 'max_answers');
}

questionAttributes();

if ($question->hasAttribute('date_min')) {
    echo $form->textFieldControlGroup($question, 'date_min');
}
echo TbHtml::closeTag('fieldset');