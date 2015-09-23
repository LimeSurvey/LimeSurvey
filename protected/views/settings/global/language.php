<?php
/** @var TbActiveForm $form */
/** @var \ls\models\forms\Settings $settings */
echo TbHtml::openTag('fieldset', []);

$options = CHtml::listData(App()->getLocale()->data(), 'code', 'description');
echo $form->dropDownListControlGroup($settings, 'defaultlang', $options);

// Use checkbox list for better usability versus select2..
$attribute = 'disabledLanguages';
echo $form->checkBoxListControlGroup($settings, $attribute, $options);
\TbHtml::resolveNameID($settings, $attribute, $opts);
App()->clientScript->registerCss('languages', "#{$opts['id']} .checkbox { display:inline-block; width: 19%;}");
echo TbHtml::closeTag('fieldset');
?>