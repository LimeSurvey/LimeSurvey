<?php
/* @var $this SurveymenuController */
/* @var $model Surveymenu */
/* @var $form CActiveForm */
?>

<?php 
	$form=$this->beginWidget('TbActiveForm', array(
	'id'=>'surveymenu-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => ['class' =>'form '],
	'action' => Yii::app()->getController()->createUrl('admin/menus/sa/update', ['id' => $model->id])
)); ?>
	<p><pre><?=$model->isNewRecord?></pre></p>
	<div class="modal-header">
		<?php $model->isNewRecord ? eT('Create new surveymenu') : eT('Edit surveymenu') ?>
	</div>
	<div class="modal-body">
		<div class="container-fluid">
			<?php //Warn on edition of the main menu, though damaging it can do serious harm ?>
			<?php if(!$model->isNewRecord && $model->id == '1'):?>
				<div class="alert alert-danger" role="alert"><?php printf(gT("You are editing the main menu! %s Please be very careful."), '<br/>') ?></div>
			<?php endif; ?>
			
			<p class="note"><?php printf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>
			<?php echo $form->errorSummary($model); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'parent_id'); ?>
				<?php echo $form->dropDownList($model,'parent_id', $model->getMenuIdOptions(), []); ?>
				<?php echo $form->error($model,'parent_id'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'survey_id'); ?>
				<?php echo $form->dropDownList($model,'survey_id', $model->getSurveyIdOptions()); ?>
				<?php echo $form->error($model,'survey_id'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'ordering'); ?>
				<?php $model->ordering = $model->getNextOrderPosition(); ?>
				<?php echo $form->dropDownList($model,'ordering', $model->getOrderOptions()); ?>
				<?php echo $form->error($model,'ordering'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'title'); ?>
				<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'title'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'description'); ?>
				<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'position'); ?>
				<?php echo $form->dropDownList($model,'position',$model->getPositionOptions()); ?>
				<?php echo $form->error($model,'position'); ?>
			</div>

		<?php echo $form->hiddenField($model, 'changed_by', ['value' => $user]);?>
		<?php echo $form->hiddenField($model, 'changed_at', ['value' => date('Y-m-d H:i:s')]);?>
		<?php echo $form->hiddenField($model, 'created_by', ['value' => (empty($model->created_by) ? $user : $model->created_by)]);?>
		<?php echo $form->hiddenField($model, 'id');?>
	</div>
	<div class="modal-footer">
		<?php echo TbHtml::submitButton((empty($model->id) ? 'Create' : 'Save'), array('color' => TbHtml::BUTTON_COLOR_SUCCESS)); ?>
		<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
	</div>
<?php $this->endWidget(); ?>
