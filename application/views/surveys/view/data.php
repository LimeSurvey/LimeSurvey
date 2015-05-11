<?php
//echo TbHtml::well("Features below can only be enabled / disabled when the survey is not active.");

echo TbHtml::openTag('fieldset', [
//    'disabled' => $survey->isActive
]);
//foreach ($options as $key => &$label) {
//    $label .= ' ' . TbHtml::link(TbHtml::icon('info-sign'), 'https://manual.limesurvey.org/Feature:' . $key, ['target' => '_blank']);
//}
$key = 'bool_alloweditaftercompletion';
echo TbHtml::activeCheckBoxControlGroup($survey, $key, [
    'label' => $survey->attributeLabels()[$key] . ' ' . TbHtml::link(TbHtml::icon('info-sign'), 'https://manual.limesurvey.org/Feature:' . $key, ['target' => '_blank'])
]);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::closeTag('fieldset');
