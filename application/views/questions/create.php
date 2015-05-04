<div class="col-md-6 col-sm-12">
<?php
/**
 * @var Question $question;
 */
// This is an update view so we use PUT.
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['questions/create', 'groupId' => $question->gid], 'post', []);
$options = ['formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL];
echo TbHtml::openTag('fieldset', []);
echo TbHtml::activeTextFieldControlGroup($question, 'title', array_merge($options, [
    'help' => "This is a suggestion based on the previous question title, feel free to change it."
]));
echo TbHtml::customActiveControlGroup($this->widget(WhSelect2::class, [
    'data' => CHtml::listData($question->group->questions, 'qid', function(Question $question) { return gT("Before") . ' ' . $question->displayLabel; }),
    'model' => $question,
    'attribute' => 'before',
    'htmlOptions'=> [
        'empty' => 'At end'
    ]
], true), $question, 'after', $options);
$questionTypeOptions = CHtml::listData($question->typeList(), 'type', function($details) {
    return [
        'class' => 'questionType',
        'data-preview' => App()->baseUrl . '/images/screenshots/' . strtr($details['type'], [
                ':' => 'COLON',
                '|' => 'PIPE',
                '*' => 'EQUATION'
            ]) . '.png'];
});
echo TbHtml::customActiveControlGroup($this->widget(WhSelect2::class, [
    'data' => CHtml::listData($question->typeList(), 'type', 'description', 'group'),
    'model' => $question,
    'htmlOptions' => [
        'key' => 'type',
        'options' => $questionTypeOptions,
    ],
    'attribute' => 'type'
], true), $question, 'type', $options);
App()->clientScript->registerScript('test', "
$('#Question_type').on('select2-close', function() {
    $('#preview').attr('src', $(this).find(':selected').data('preview'));
});
$(document).on('mouseenter', '.questionType', function() {
    $('#preview').attr('src', $($(this).data('select2Data').element).data('preview'));
 })");
//echo TbHtml::customActiveControlGroup("test", $question, 'title');
//echo TbHtml::activeDropDownListControlGroup($question, 'type', CHtml::listData($question->typeList(), 'type', 'description'), $options);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Create question', [
    'color' => 'primary'
]);
echo TbHtml::closeTag('div');
echo TbHtml::closeTag('fieldset');
echo TbHtml::endForm();
?>
</div>
<img class="col-md-6" id="preview" src="<?= $questionTypeOptions[$question->type]['data-preview']?>">
</div>