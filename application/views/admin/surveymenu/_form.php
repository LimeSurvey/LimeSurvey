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
));

 $modalTitle = $model->isNewRecord ? gT('Create new survey menu') : gT('Edit survey menu');
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => $modalTitle]
);
?>
	<div class="modal-body">
		<div class="container-fluid">
			<?php //Warn on edition of the main menu, though damaging it can do serious harm ?>
			<?php if(!$model->isNewRecord && $model->id == '1'):?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'header' => gT("You are editing the main menu!"),
                    'text' =>  gT("Please be very careful."),
                    'type' => 'danger',
                ]);
                ?>
			<?php endif; ?>

			<p class="note"><?php printf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]);
            ?>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'parent_id', ['for' => 'parent_id_label']); ?>
				<?php echo $form->dropDownList($model,'parent_id', $model->getMenuIdOptions(), ['class' => 'form-select', 'id' => 'parent_id_label']); ?>
				<?php echo $form->error($model,'parent_id'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'survey_id', ['for' => 'survey_id_label']); ?>
				<?php echo $form->dropDownList($model,'survey_id', $model->getSurveyIdOptions(), ['class' => 'form-select', 'id' => 'survey_id_label']); ?>
				<?php echo $form->error($model,'survey_id'); ?>
            </div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'user_id', ['for' => 'user_id_label']); ?>
				<?php echo $form->dropDownList($model,'user_id', $model->getUserIdOptions(), ['class' => 'form-select', 'id' => 'user_id_label']); ?>
				<?php echo $form->error($model,'user_id'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'ordering', ['for' => 'ordering_label']); ?>
				<?php $model->ordering = $model->getNextOrderPosition(); ?>
				<?php echo $form->dropDownList($model,'ordering', $model->getOrderOptions(), ['class' => 'form-select', 'id' => 'ordering_label']); ?>
				<?php echo $form->error($model,'ordering'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'showincollapse', ['for' => 'showincollapse_label']); ?>
				<?php echo $form->checkbox($model,'showincollapse', ['id' => 'showincollapse_label']); ?>
				<?php echo $form->error($model,'showincollapse'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'name', array('for' => 'name_label')); ?>
				<?php echo $form->textField($model,'name',array('title'=>gT('Lowercase characters and digits, starting with a character - length from 6 to 60 characters'), 'required'=>true, 'size'=>60,'maxlength'=>255, 'pattern' => '[a-z][a-z0-9]{5,59}', 'id' => 'name_label')); ?>
				<?php echo $form->error($model,'name'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'title', array('for' => 'title_label')); ?>
				<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255, 'id' => 'title_label')); ?>
				<?php echo $form->error($model,'title'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'description', array('for' => 'description_label')); ?>
				<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50, 'id' => 'description_label')); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>

			<div class="mb-3">
				<?php echo $form->labelEx($model,'position', array('for' => 'position_label')); ?>
				<?php echo $form->dropDownList($model,'position',$model->getPositionOptions(), ['class' => 'form-select', 'id' => 'position_label']); ?>
				<?php echo $form->error($model,'position'); ?>
			</div>

		<?php echo $form->hiddenField($model, 'changed_by', ['value' => $user]);?>
		<?php echo $form->hiddenField($model, 'changed_at', ['value' => date('Y-m-d H:i:s')]);?>
		<?php echo $form->hiddenField($model, 'created_by', ['value' => (empty($model->created_by) ? $user : $model->created_by)]);?>
		<?php echo $form->hiddenField($model, 'id');?>
	</div>
    </div>
	<div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel');?></button>
		<?php echo TbHtml::submitButton((empty($model->id) ? gT('Create') : gT('Save')), array('class' => 'btn-primary')); ?>
	</div>
<?php $this->endWidget(); ?>
