<?php
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($survey, 'format', $survey->formatOptions);

echo $form->dropDownListControlGroup($survey, 'questionindex', $survey->indexOptions);
echo TbHtml::closeTag('fieldset');
