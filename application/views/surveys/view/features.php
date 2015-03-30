<?php
echo TbHtml::well("Features below can only be enabled / disabled when the survey is not active.");
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['surveys/update'], 'post', []);
$options = $survey->featureOptions;
echo TbHtml::openTag('fieldset', [
    'disabled' => $survey->active
]);
foreach ($options as $key => &$label) {
    $label .= ' ' . TbHtml::link(TbHtml::icon('info-sign'), 'https://manual.limesurvey.org/Feature:' . $key, ['target' => '_blank']);
}
echo TbHtml::activeCheckBoxListControlGroup($survey, 'features', $options, ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
    'color' => 'primary',
    'disabled' => !$survey->isActive
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();