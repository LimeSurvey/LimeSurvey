<?php
/** @var \ls\models\QuestionGroup $group */
$options = ['formLayout' => TbHtml::FORM_LAYOUT_VERTICAL];
$group->language = $language;
echo TbHtml::openTag('fieldset', [
    'class' => 'col-md-12'
]);
echo TbHtml::activeTextFieldControlGroup($group, "translatedFields[$language][group_name]", array_merge($options, [
    'class' => 'html',
    'label' => $group->getAttributeLabel('group_name')
]));
echo TbHtml::activeTextAreaControlGroup($group, "translatedFields[$language][description]", array_merge($options, [
    'class' => 'html',
    'label' => $group->getAttributeLabel('description')
]));
echo TbHtml::closeTag('fieldset');