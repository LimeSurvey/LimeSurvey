<?php
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeDropDownListControlGroup($survey, 'format', $survey->formatOptions, $options);
echo TbHtml::hiddenField('id', $survey->sid);
echo TbHtml::closeTag('fieldset');
