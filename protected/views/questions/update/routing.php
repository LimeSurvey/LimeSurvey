<?php
/** @var TbActiveForm $form */
/** @var Question $question */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($question, 'title');
echo $form->textFieldControlGroup($question, 'relevance');
if ($question->hasAttribute('random_group')) {
    echo $form->textFieldControlGroup($question, 'random_group');
}
echo $form->checkBoxControlGroup($question, 'bool_mandatory');
echo $form->checkBoxControlGroup($question, 'bool_hidden');
if ($question->hasAttribute('other_comment_mandatory')) {
    echo $form->checkBoxControlGroup($question, 'bool_other_comment_mandatory');
}
echo TbHtml::closeTag('fieldset');
?>