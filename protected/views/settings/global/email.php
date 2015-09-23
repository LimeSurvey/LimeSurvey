<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($settings, 'siteadminemail');
echo $form->textFieldControlGroup($settings, 'siteadminname');
echo $form->dropDownListControlGroup($settings, 'emailmethod', $settings->getEmailMethodOptions());
echo $form->textFieldControlGroup($settings, 'emailsmtphost');

/*
 * Fake input field to prevent prefilling.
 * http://stackoverflow.com/questions/15738259/disabling-chrome-autofill
 */
echo TbHtml::passwordField('','', ['style' => 'display:none;']);


echo $form->textFieldControlGroup($settings, 'emailsmtpuser');
echo $form->passwordFieldControlGroup($settings, 'emailsmtppassword');

echo $form->dropDownListControlGroup($settings, 'emailsmtpssl', $settings->getEmailSmtpSslOptions());
echo $form->dropDownListControlGroup($settings, 'emailsmtpdebug', $settings->getEmailSmtpDebugOptions());
echo $form->numberFieldControlGroup($settings, 'maxemails');

echo TbHtml::closeTag('fieldset');
?>