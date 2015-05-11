<?php
echo TbHtml::well("Features below can be enabled / disabled while the survey is active.");

$expires = TbHtml::activeDateTimeLocalField($survey, 'expires', ['class' => 'form-control']);
$starts = TbHtml::activeDateTimeLocalField($survey, 'startdate', ['class' => 'form-control']);
echo TbHtml::customActiveControlGroup($expires, $survey, 'expires', ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);

echo TbHtml::customActiveControlGroup($starts, $survey, 'startdate', ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);

echo TbHtml::activeDropDownListControlGroup($survey, 'usecaptcha', $survey->captchaOptions, [
    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
]);
echo TbHtml::activeCheckBoxControlGroup($survey, 'bool_usecookie', ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);

echo TbHtml::activeCheckBoxControlGroup($survey, 'bool_listpublic', ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);