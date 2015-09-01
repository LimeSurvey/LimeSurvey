<div class="row">
    <div class="col-md-4 col-md-offset-4">
<?php
/** @var TbActiveForm $form */
$form = $this->beginWidget(TbActiveForm::class, [
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'layout' => TbHtml::FORM_LAYOUT_VERTICAL,
    'method' => 'put',
    'htmlOptions' => [
        'validateOnSubmit' => true
    ]
]);

echo $form->textFieldControlGroup($participant, 'firstname');
echo $form->textFieldControlGroup($participant, 'lastname');
echo $form->emailFieldControlGroup($participant, 'email');
echo $form->dropDownListControlGroup($participant, 'language', Html::listData(\ls\helpers\SurveyTranslator::getLanguageData(), 'code', 'description'), [
    'empty' => gT('Choose language')
]);
echo $form->checkBoxControlGroup($participant, 'blacklisted', ['uncheckValue' => 'N', 'value' => 'Y']);

echo TbHtml::openTag('div', ['style' => 'overflow: auto;']);
echo TbHtml::submitButton(gT('Save participant'), [
    'color' => 'primary',
    'class' => 'pull-right'
]);
echo TbHtml::closeTag('div');
$this->endWidget();
?>
        </div></div>