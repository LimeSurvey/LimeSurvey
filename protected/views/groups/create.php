<div class="col-sm-12">
<?php
/**
 * @var QuestionGroup $group;
 */
use ls\models\QuestionGroup;

echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['groups/create', 'surveyId' => $group->sid], 'post', []);
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($group, 'group_name', array_merge($options, [
    'help' => "This is a suggestion based on the previous group title, feel free to change it."
]));
if (!empty($group->survey->groups)) {
    echo TbHtml::customActiveControlGroup($this->widget(WhSelect2::class, [
        'data' => CHtml::listData($group->survey->groups, 'id', function (QuestionGroup $group) {
            return gT("Before") . ' ' . $group->displayLabel;
        }),
        'model' => $group,
        'attribute' => 'before',
        'htmlOptions' => [
            'empty' => 'At end'
        ]
    ], true), $group, 'after', $options);
}

echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Create group'), [
    'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();
?>
</div>
