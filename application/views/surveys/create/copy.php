<?php

echo $form->textFieldControlGroup($languageSetting, 'surveyls_title');
echo $form->dropDownListControlGroup($survey, 'language', CHtml::listData(getLanguageData(), 'code', 'description'));
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Copy survey'), [
    'color' => 'primary',
    'name' => 'copy'
]);
echo TbHtml::closeTag('div');
