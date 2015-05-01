<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 5/1/15
 * Time: 12:00 PM
 */
/** @var \Question $question */
$options = ['formLayout' => TbHtml::FORM_LAYOUT_VERTICAL];
$question->language = $language;
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextAreaControlGroup($question, "translatedFields[$language][question]", array_merge($options, [
    'class' => 'html',
    'label' => $question->getAttributeLabel('question')
]));
echo TbHtml::activeTextAreaControlGroup($question, "translatedFields[$language][question]", array_merge($options, [
    'class' => 'html',
    'label' => $question->getAttributeLabel('help')
]));
echo TbHtml::closeTag('fieldset');