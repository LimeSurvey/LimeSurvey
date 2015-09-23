<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($settings, 'sitename');
echo $form->dropDownListControlGroup($settings, 'defaulttemplate', $settings->getDefaultTemplateOptions());
echo $form->dropDownListControlGroup($settings, 'admintheme', $settings->getAdminThemeOptions());
echo $form->dropDownListControlGroup($settings, 'defaulthtmleditormode', $settings->getDefaultHtmlEditorModeOptions());
echo $form->dropDownListControlGroup($settings, 'defaultquestionselectormode', $settings->getDefaultQuestionSelectorModeOptions());
echo $form->dropDownListControlGroup($settings, 'defaulttemplateeditormode', $settings->getDefaultTemplateEditorModeOptions());
echo $form->textFieldControlGroup($settings, 'GeoNamesUsername');
echo $form->textFieldControlGroup($settings, 'googleMapsAPIKey');
echo $form->textFieldControlGroup($settings, 'ipInfoDbAPIKey');
echo $form->textFieldControlGroup($settings, 'googleanalyticsapikey');
echo $form->textFieldControlGroup($settings, 'googletranslateapikey');
if(isset(Yii::app()->session->connectionID)) {
    echo $form->textFieldControlGroup($settings, 'iSessionExpirationTime');
}

echo TbHtml::closeTag('fieldset');
?>