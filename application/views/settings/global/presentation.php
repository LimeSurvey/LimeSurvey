<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
$form->controlWidthClass = 'col-sm-8';
$form->labelWidthClass = 'col-sm-4';
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($settings, 'shownoanswer', $settings->getYesNoAdminOptions());
echo $form->textFieldControlGroup($settings, 'repeatheadings');
echo $form->dropDownListControlGroup($settings, 'showxquestions', $settings->getShowHideChooseOptions());

echo $form->dropDownListControlGroup($settings, 'showgroupinfo', $settings->getShowGroupInfoOptions());
echo $form->dropDownListControlGroup($settings, 'showqnumcode', $settings->getShowQuestionCodeOptions());

echo $form->textFieldControlGroup($settings, 'pdffontsize');
echo $form->checkBoxControlGroup($settings, 'pdfshowheader', ['uncheckedValue' => 'N', 'checkedValue' => 'Y']);
echo $form->textFieldControlGroup($settings, 'pdflogowidth');
echo $form->textFieldControlGroup($settings, 'pdfheadertitle');
echo $form->textFieldControlGroup($settings, 'pdfheaderstring');
echo TbHtml::closeTag('fieldset');
?>