<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'template_name'); ?>
		<?php echo $form->textField($model,'template_name',array('size'=>60,'maxlength'=>150)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'sid'); ?>
		<?php echo $form->textField($model,'sid'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'gsid'); ?>
		<?php echo $form->textField($model,'gsid'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'uid'); ?>
		<?php echo $form->textField($model,'uid'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'files_css'); ?>
		<?php echo $form->textArea($model,'files_css',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'files_js'); ?>
		<?php echo $form->textArea($model,'files_js',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'files_print_css'); ?>
		<?php echo $form->textArea($model,'files_print_css',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'options'); ?>
		<?php echo $form->textArea($model,'options',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'cssframework_name'); ?>
		<?php echo $form->textField($model,'cssframework_name',array('size'=>45,'maxlength'=>45)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'cssframework_css'); ?>
		<?php echo $form->textArea($model,'cssframework_css',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'cssframework_js'); ?>
		<?php echo $form->textArea($model,'cssframework_js',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'packages_to_load'); ?>
		<?php echo $form->textArea($model,'packages_to_load',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
