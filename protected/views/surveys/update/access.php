<?php
echo TbHtml::well("Features below can be enabled / disabled while the survey is active.");

$expires = TbHtml::activeDateTimeLocalField($survey, 'expires', ['class' => 'form-control']);
$starts = TbHtml::activeDateTimeLocalField($survey, 'startdate', ['class' => 'form-control']);
echo $form->customControlGroup($expires, $survey, 'expires');

echo $form->customControlGroup($starts, $survey, 'startdate');

echo $form->dropDownListControlGroup($survey, 'usecaptcha', $survey->captchaOptions);
echo $form->checkBoxControlGroup($survey, 'bool_usecookie');

echo $form->checkBoxControlGroup($survey, 'bool_listpublic');