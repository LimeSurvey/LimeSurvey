<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
echo TbHtml::openTag('fieldset', []);
echo $form->checkBoxControlGroup($settings, 'surveyPreview_require_Auth');
echo $form->checkBoxControlGroup($settings, 'filterxsshtml');
echo $form->checkBoxControlGroup($settings, 'usercontrolSameGroupPolicy');
echo $form->dropDownListControlGroup($settings, 'force_ssl', $settings->getForceSslOptions());
echo TbHtml::closeTag('fieldset');
?>