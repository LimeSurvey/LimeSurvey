<?php
echo TbHtml::openTag('fieldset', []);
$key = 'bool_alloweditaftercompletion';
echo $form->checkBoxControlGroup($survey, $key, [
    'label' => $survey->attributeLabels()[$key] . ' ' . TbHtml::link(TbHtml::icon('info-sign'), 'https://manual.limesurvey.org/Feature:' . $key, ['target' => '_blank'])
]);
echo TbHtml::closeTag('fieldset');
