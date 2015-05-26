<?php
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($survey, 'format', $survey->formatOptions);
echo TbHtml::closeTag('fieldset');
