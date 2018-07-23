<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
	'id'=>'surveymenu-entries-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => ['class' =>'form '],
	'action' => Yii::app()->getController()->createUrl('admin/menuentries/sa/update', ['id' => $model->id])
)); ?>

<div class="modal-header">
	<?php $model->isNewRecord ? eT('Create new survey menu entry') : eT('Edit survey menu entry') ?>
</div>
<div class="modal-body">
	<div class="container-fluid">

		<?php //Warn on edition of the main menu, though damaging it can do serious harm ?>
		<?php if(!$model->isNewRecord && $model->menu_id == '1'):?>
			<div class="alert alert-danger" role="alert"><?php echo gT("You are editing an entry of the main menu!").' '.gT("Please be very careful."); ?></div>
		<?php endif; ?>

		<p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>

		<?php echo $form->errorSummary($model); ?>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_id'); ?>
			<?php echo $form->dropDownList($model,'menu_id', $model->getMenuIdOptions()); ?>
			<?php echo $form->error($model,'menu_id'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'name'); ?>
			<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'name'); ?>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'title'); ?>
			<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'title'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'ordering'); ?>
			<?php echo $form->numberField($model,'ordering'); ?>
			<?php echo $form->error($model,'ordering'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_title'); ?>
			<?php echo $form->textField($model,'menu_title',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'menu_title'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_description'); ?>
			<?php echo $form->textArea($model,'menu_description',array('rows'=>6, 'cols'=>50)); ?>
			<?php echo $form->error($model,'menu_description'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_icon'); ?>
			<?php echo $form->textField($model,'menu_icon',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'menu_icon'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_icon_type'); ?>
			<?php echo $form->dropDownList($model,'menu_icon_type', $model->getMenuIconTypeOptions()); ?>
			<?php echo $form->error($model,'menu_icon_type'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_class'); ?>
			<?php echo $form->textField($model,'menu_class',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'menu_class'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'menu_link'); ?>
			<?php echo $form->textField($model,'menu_link',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'menu_link'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'permission'); ?>
			<?php echo $form->textField($model,'permission',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'permission'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'permission_grade'); ?>
			<?php echo $form->textField($model,'permission_grade',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'permission_grade'); ?>
		</div>

		<div class="row ls-space margin bottom-10">
			<button class="btn btn-warning pull-right " type="button" data-toggle="collapse" data-target="#collapseAdvancedOptions"><?php eT('Toggle advanced options') ?></button>
		</div>
		<!-- Start collapsed advanced options -->
		<div class="collapse" id="collapseAdvancedOptions">
            <div class="form-group">
				<?php echo $form->labelEx($model,'user_id'); ?>
				<?php echo $form->dropDownList($model,'user_id', $model->getUserIdOptions()); ?>
				<?php echo $form->error($model,'user_id'); ?>
            </div>
            
			<div class="form-group">
				<?php echo $form->labelEx($model,'action'); ?>
				<?php echo $form->textField($model,'action',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'action'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'template'); ?>
				<?php echo $form->textField($model,'template',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'template'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'partial'); ?>
				<?php echo $form->textField($model,'partial',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'partial'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'classes'); ?>
				<?php echo $form->textField($model,'classes',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'classes'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'data'); ?>
				<?php echo $form->textArea($model,'data',array('rows'=>6, 'cols'=>50)); ?>
				<?php echo $form->error($model,'data'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'getdatamethod'); ?>
				<?php echo $form->textField($model,'getdatamethod',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'getdatamethod'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'language'); ?>
				<?php echo $form->textField($model,'language',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'language'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'showincollapse'); ?>
				<?php echo $form->checkbox($model,'showincollapse'); ?>
				<?php echo $form->error($model,'showincollapse'); ?>
			</div>
		</div>

		<?php echo $form->hiddenField($model, 'changed_by', ['value' => $user]);?>
		<?php echo $form->hiddenField($model, 'changed_at', ['value' => date('Y-m-d H:i:s')]);?>
		<?php echo $form->hiddenField($model, 'created_by', ['value' => (empty($model->created_by) ? $user : $model->created_by)]);?>
		<?php echo $form->hiddenField($model, 'id');?>
		
	</div>
	<div class="modal-footer">
		<?php echo TbHtml::submitButton(($model->isNewRecord ? gT('Create') : gT('Save')), array('color' => TbHtml::BUTTON_COLOR_SUCCESS)); ?>
		<button type="button" class="btn btn-danger" data-dismiss="modal"><?=gT('Close')?></button>
	</div>

<?php $this->endWidget(); ?>
<!-- form -->
