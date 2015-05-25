<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($settings, 'defaultlang', CHtml::listData(getLanguageData(), 'code', 'description'));

// Use checkbox list for better usability versus select2..
$attribute = 'disabledLanguages';
echo $form->checkBoxListControlGroup($settings, $attribute, CHtml::listData(getLanguageData(), 'code', 'description'));
\TbHtml::resolveNameID($settings, $attribute, $opts);
App()->clientScript->registerCss('languages', "#{$opts['id']} .checkbox { display:inline-block; width: 19%;}");
echo TbHtml::closeTag('fieldset');
?>