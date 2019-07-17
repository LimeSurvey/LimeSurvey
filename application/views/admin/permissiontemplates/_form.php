<?php
/* @var $this PermissiontemplatesController */
/* @var $model Permissiontemplates */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('TbActiveForm', array(
	'id'=>'permissiontemplates-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>
    <div class="row buttons ls-space top-10">
		<?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['class' => 'btn btn-primary pull-right ls-space padding left-25 right-25']); ?>
	</div>


	<?php echo $form->errorSummary($model); ?>

    <div class="row">
       	<?php echo $form->labelEx($model,'name'); ?>
       	<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>192)); ?>
       	<?php echo $form->error($model,'name'); ?>
    </div>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<?php echo $form->hiddenField($model,'renewed_last', ['value' => date('Y-m-d H:i:s')]); ?>
	
	<?php if($model->isNewRecord ) {
			echo $form->hiddenField($model,'created_at', ['value' =>  date('Y-m-d H:i:s')]); 
			echo $form->hiddenField($model,'created_by', ['value' =>Yii::app()->user->id]); 
		}
	?>

<?php $this->endWidget(); ?>

</div><!-- form -->