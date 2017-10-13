<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'template-options-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'template_name'); ?>
		<?php echo $form->textField($model,'template_name',array('size'=>60,'maxlength'=>150)); ?>
		<?php echo $form->error($model,'template_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sid'); ?>
		<?php echo $form->textField($model,'sid'); ?>
		<?php echo $form->error($model,'sid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gsid'); ?>
		<?php echo $form->textField($model,'gsid'); ?>
		<?php echo $form->error($model,'gsid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uid'); ?>
		<?php echo $form->textField($model,'uid'); ?>
		<?php echo $form->error($model,'uid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'files_css'); ?>
		<?php echo $form->textArea($model,'files_css',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'files_css'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'files_js'); ?>
		<?php echo $form->textArea($model,'files_js',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'files_js'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'files_print_css'); ?>
		<?php echo $form->textArea($model,'files_print_css',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'files_print_css'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'options'); ?>
		<?php echo $form->textArea($model,'options',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'options'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'cssframework_name'); ?>
		<?php echo $form->textField($model,'cssframework_name',array('size'=>45,'maxlength'=>45)); ?>
		<?php echo $form->error($model,'cssframework_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'cssframework_css'); ?>
		<?php echo $form->textArea($model,'cssframework_css',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'cssframework_css'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'cssframework_js'); ?>
		<?php echo $form->textArea($model,'cssframework_js',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'cssframework_js'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'packages_to_load'); ?>
		<?php echo $form->textArea($model,'packages_to_load',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'packages_to_load'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
