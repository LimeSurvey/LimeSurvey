<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
//$form->layout = TbHtml::FORM_LAYOUT_HORIZONTAL;
echo TbHtml::openTag('fieldset', []);
echo $form->checkBoxControlGroup($settings, 'blacklistallsurveys', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo $form->checkBoxControlGroup($settings, 'blacklistnewsurveys', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo $form->checkBoxControlGroup($settings, 'blockaddingtosurveys', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo $form->checkBoxControlGroup($settings, 'hideblacklisted', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo $form->checkBoxControlGroup($settings, 'allowunblacklist', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo $form->checkBoxControlGroup($settings, 'deleteblacklisted', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo TbHtml::closeTag('fieldset');
