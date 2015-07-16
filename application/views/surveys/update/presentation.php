<?php
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($survey, 'format', $survey->formatOptions);

echo $form->dropDownListControlGroup($survey, 'questionindex', $survey->indexOptions);
echo $form->checkBoxControlGroup($survey, 'bool_showwelcome');

echo TbHtml::closeTag('fieldset');
