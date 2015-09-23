<?php
/** @var \Question $question */
/** @var TbActiveForm $form */
$form->layout = TbHtml::FORM_LAYOUT_VERTICAL;
$question->language = $language;
echo TbHtml::openTag('fieldset', [
]);
echo $form->textAreaControlGroup($question, "translatedFields[$language][question]", [
    'class' => 'html',
    'label' => $question->getAttributeLabel('question')
]);
echo $form->textAreaControlGroup($question, "translatedFields[$language][help]", [
    'class' => 'html',
    'label' => $question->getAttributeLabel('help')
]);
echo TbHtml::closeTag('fieldset');