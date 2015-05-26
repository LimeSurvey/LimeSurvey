<?php
echo TbHtml::openTag('fieldset', []);
echo $form->dropDownListControlGroup($survey, 'language', CHtml::listData(getLanguageData(), 'code', 'description'));
App()->clientScript->registerCss('languages', '#Survey_additionalLanguages .checkbox { display:inline-block; width: 19%;}');
echo $form->checkBoxListControlGroup($survey, 'additionalLanguages', CHtml::listData(getLanguageData(), 'code', 'description'));
echo TbHtml::closeTag('fieldset');