<?php
/** @var \ls\models\Survey $survey */
use ls\models\SurveyLanguageSetting;

/** @var TbActiveForm $form */
$form->layout = TbHtml::FORM_LAYOUT_VERTICAL;
echo TbHtml::openTag('fieldset', [
]);
$languageSetting = new SurveyLanguageSetting();
foreach([
    'invite',
    'remind',
    'register',
    'confirm',
    'notification',
    'responses'
] as $email) {


    echo $form->textFieldControlGroup($survey, "translatedFields[$language][email_{$email}_subj]", [
        'label' => $languageSetting->getAttributeLabel("surveyls_email_{$email}_subj")
    ]);
    echo $form->textAreaControlGroup($survey, "translatedFields[$language][email_{$email}]", [
        'class' => 'html',
        'label' => $languageSetting->getAttributeLabel("surveyls_email_{$email}")
    ]);

}
echo TbHtml::closeTag('fieldset');