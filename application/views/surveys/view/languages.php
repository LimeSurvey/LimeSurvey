<?php
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeDropDownListControlGroup($survey, 'language', CHtml::listData(getLanguageData(), 'code', 'description'), ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL]);
App()->clientScript->registerCss('languages', '#Survey_additionalLanguages .checkbox { display:inline-block; width: 19%;}');
echo TbHtml::activeCheckBoxListControlGroup($survey, 'additionalLanguages', CHtml::listData(getLanguageData(), 'code', 'description'), [
    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
]);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::closeTag('fieldset');