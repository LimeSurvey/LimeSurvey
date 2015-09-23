<?php
/** @var \Survey $survey */
/** @var TbActiveForm $form */
$form->layout = TbHtml::FORM_LAYOUT_VERTICAL;
echo TbHtml::openTag('fieldset', [
]);
$languageSetting = new SurveyLanguageSetting();
echo $form->textFieldControlGroup($survey, "translatedFields[$language][surveyls_title]", [
    'label' => $languageSetting->getAttributeLabel('surveyls_title')
]);
echo $form->textAreaControlGroup($survey, "translatedFields[$language][surveyls_description]", [
    'class' => 'html',
    'label' => $languageSetting->getAttributeLabel('surveyls_description')
]);
echo $form->textAreaControlGroup($survey, "translatedFields[$language][surveyls_welcometext]", [
    'class' => 'html',
    'label' => $languageSetting->getAttributeLabel('surveyls_welcometext')
]);
echo $form->textAreaControlGroup($survey, "translatedFields[$language][surveyls_endtext]", [
    'class' => 'html',
    'label' => $languageSetting->getAttributeLabel('surveyls_endtext')
]);
echo $form->textFieldControlGroup($survey, "translatedFields[$language][surveyls_url]", [
    'label' => $languageSetting->getAttributeLabel('surveyls_url')
]);
echo $form->textFieldControlGroup($survey, "translatedFields[$language][surveyls_urldescription]", [
    'label' => $languageSetting->getAttributeLabel('surveyls_urldescription')
]);

echo $form->dropDownListControlGroup($survey, "translatedFields[$language][surveyls_dateformat]", $languageSetting->dateFormatOptions, [
    'label' => $languageSetting->getAttributeLabel('surveyls_dateformat')
]);
echo TbHtml::closeTag('fieldset');