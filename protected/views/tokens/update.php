<div class="row"><div class="col-md-6 col-md-offset-3">
<?php
/** @var \ls\models\Token $token */
/** @var TbActiveForm $form */
$form = $this->beginWidget(TbActiveForm::class, [
    'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'enableClientValidation' => true,
    'enableAjaxValidation' => false,
    'clientOptions' => [
        'validateOnSubmit' => true
    ],
    'method' => 'put'

]);
$inputOptions = [
    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
];
echo $form->textFieldControlGroup($token, 'firstname', $inputOptions);
echo $form->textFieldControlGroup($token, 'lastname', $inputOptions);
echo $form->textFieldControlGroup($token, 'email', $inputOptions);
echo $form->textFieldControlGroup($token, 'token', $inputOptions);
echo $form->dropDownListControlGroup($token, 'language', $survey->allLanguages, $inputOptions);
echo $form->textFieldControlGroup($token, 'sent', $inputOptions);
echo $form->textFieldControlGroup($token, 'remindersent', $inputOptions);
echo $form->textFieldControlGroup($token, 'completed', $inputOptions);
echo $form->numberFieldControlGroup($token, 'usesleft', $inputOptions);
echo $form->dateFieldControlGroup($token, 'validfrom');
echo $form->dateFieldControlGroup($token, 'validuntil');
foreach ($token->attributeNames() as $attribute) {
    if (substr_compare('attribute_', $attribute, 0, 10) === 0) {
        echo $form->textFieldControlGroup($token, $attribute, $inputOptions);
    }
}


echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Save token'), ['color' => 'primary']);
echo TbHtml::closeTag('div');
$this->endWidget();
?></div></div>