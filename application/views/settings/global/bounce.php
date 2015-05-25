<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($settings, 'siteadminbounce');
echo $form->dropDownListControlGroup($settings, 'bounceaccounttype', $settings->getBounceAccountTypeOptions());
echo $form->textFieldControlGroup($settings, 'bounceaccounthost');
echo $form->textFieldControlGroup($settings, 'bounceaccountuser');
echo $form->passwordFieldControlGroup($settings, 'bounceaccountpass');
echo $form->dropDownListControlGroup($settings, 'bounceencryption', $settings->getEmailSmtpSslOptions());
echo TbHtml::closeTag('fieldset');
?>