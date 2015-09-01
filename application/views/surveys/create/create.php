<?php

echo $form->textFieldControlGroup($languageSetting, 'surveyls_title');
echo $form->dropDownListControlGroup($survey, 'language', CHtml::listData(\ls\helpers\SurveyTranslator::getLanguageData(), 'code', 'description'));
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Create survey'), [
    'color' => 'primary',
    'name' => 'create'
]);
echo TbHtml::closeTag('div');
