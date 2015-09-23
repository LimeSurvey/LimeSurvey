<?php
/** @var TbActiveForm $form */
/** @var Question $question */
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($question, 'preg');
if ($question->hasAttribute('time_limit')) {
    echo $form->numberFieldControlGroup($question, 'time_limit');
    echo $form->dropDownListControlGroup($question, 'time_limit_action', $question->timeLimitOptions);
    echo $form->checkBoxControlGroup($question, 'bool_time_limit_disable_next');
    echo $form->checkBoxControlGroup($question, 'bool_time_limit_disable_prev');
}

echo TbHtml::closeTag('fieldset');