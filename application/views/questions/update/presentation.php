<?php
/** @var TbActiveForm $form */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
//echo $form->textFieldControlGroup($question, 'title');
//echo $form->textFieldControlGroup($question, 'relevance');
//echo $form->textFieldControlGroup($question, 'random_group');
echo $form->checkBoxControlGroup($question, 'bool_other');
echo TbHtml::closeTag('fieldset');
?>