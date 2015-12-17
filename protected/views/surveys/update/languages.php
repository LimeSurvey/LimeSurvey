<?php

echo TbHtml::openTag('fieldset', []);

echo $form->dropDownListControlGroup($survey, 'language', CHtml::listData(\ls\helpers\SurveyTranslator::getLanguageData(), 'code', 'description'));

echo $form->checkBoxListControlGroup($survey, 'additionalLanguages', CHtml::listData(\ls\helpers\SurveyTranslator::getLanguageData(), 'code', 'description'), [
    'containerOptions' => [
        'class' => 'languageList'
    ]
]);
echo TbHtml::closeTag('fieldset');