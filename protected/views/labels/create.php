<?php


/** @var TbActiveForm $form */
$form = $this->beginWidget(TbActiveForm::class, [
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'method' => $model->isNewRecord ? 'POST': 'PUT',
    'htmlOptions' => [
        'validateOnSubmit' => true
    ]
]);

echo $form->textFieldControlGroup($model, 'label_name');
$options = CHtml::listData(App()->getLocale()->data(), 'code', 'description');
echo $form->checkBoxListControlGroup($model, 'languageArray', $options, [
    'containerOptions' => [
        'class' => 'languageList'
    ]
]);

echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Save'), [
    'color' => 'primary'
]);
echo TbHtml::closeTag('div');

$this->endWidget();

