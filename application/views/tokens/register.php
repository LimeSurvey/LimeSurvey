<div class="row"><div class="col-sm-12 col-md-6">
    <?php echo TbHtml::tag('h1', [], $survey->localizedTitle); ?>
</div></div><div class="row"><div class="col-sm-12 col-md-12">
<?php
$inputOptions =  [
    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'labelOptions' => ['class' => 'col-md-6'],
    'controlOptions' => ['class' => 'col-md-6'],
];
/** @var TbActiveForm $form */
$form = $this->beginWidget(TbActiveForm::class, [
    'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'enableClientValidation' => true,
    'enableAjaxValidation' => false,


]);

//echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, '', 'post');
//$token = Token::create($survey->sid);
/** @var Token $token */
echo $form->textFieldControlGroup($token, 'firstname', array_merge($inputOptions, ['error' => $token->getError('firstname')]));
echo $form->textFieldControlGroup($token, 'lastname', $inputOptions);
echo $form->textFieldControlGroup($token, 'email', array_merge($inputOptions, ['error' => $token->getError('email')]));
echo $form->textFieldControlGroup($token, 'captcha', $inputOptions);
echo $form->customControlGroup($this->widget('CCaptcha', [], true), $token, false, $inputOptions);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT('Register'), ['color' => 'primary']);
echo TbHtml::closeTag('div');
$this->endWidget();
?></div></div>