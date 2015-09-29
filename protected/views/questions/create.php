<div class="col-md-6 col-sm-12">
<?php
/**
 * @var Question $question;
 */
// This is a create view so we use POST.
use ls\models\Question;

/** @var TbActiveForm $form */
$form = $this->beginWidget(TbActiveForm::class, [
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'action' => ['questions/create', 'groupId' => $question->gid],
    'method' => 'post',
    'htmlOptions' => [
        'validateOnSubmit' => true
    ]
]);
echo TbHtml::openTag('fieldset', []);
echo $form->textFieldControlGroup($question, 'title', [
    'help' => "This is a suggestion based on the previous question title, feel free to change it."
]);
if (!empty($question->group->questions)) {
    echo $form->customControlGroup($this->widget(WhSelect2::class, [
        'data' => CHtml::listData($question->group->questions, 'qid', function (Question $question) {
            return gT("Before") . ' ' . $question->displayLabel;
        }),
        'model' => $question,
        'attribute' => 'before',
        'htmlOptions' => [
            'empty' => 'At end'
        ]
    ], true), $question, 'after');
}
$questionTypeOptions = CHtml::listData($question->typeList(), 'type', function($details) {
    return [
        'class' => 'questionType',
        'data-preview' => App()->baseUrl . '/images/screenshots/' . strtr($details['type'], [
                ':' => 'COLON',
                '|' => 'PIPE',
                '*' => 'EQUATION'
            ]) . '.png'];
});
echo $form->customControlGroup($this->widget(WhSelect2::class, [
    'data' => CHtml::listData($question->typeList(), 'type', 'description', 'group'),
    'model' => $question,
    'htmlOptions' => [
        'key' => 'type',
        'options' => $questionTypeOptions,
    ],
    'attribute' => 'type'
], true), $question, 'type');
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
$this->endWidget();
?>
</div>
<img class="col-md-6" id="preview" src="<?= $questionTypeOptions[$question->type]['data-preview']?>">
</div>