<?php
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, '', 'post', []);

echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeDropDownListControlGroup($survey, 'language', CHtml::listData(getLanguageData(), 'code', 'description'), ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);
App()->clientScript->registerCss('languages', '#Survey_additionalLanguages .checkbox { display:inline-block; width: 19%;}');
echo TbHtml::activeCheckBoxListControlGroup($survey, 'additionalLanguages', CHtml::listData(getLanguageData(), 'code', 'description'), [
    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
]);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save settings', [
    'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();