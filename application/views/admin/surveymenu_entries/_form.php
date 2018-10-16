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

		<?php //Warn on edition of the main menu, though damaging it can do serious harm?>
		<?php if (!$model->isNewRecord && $model->menu_id == '1'):?>
			<div class="alert alert-danger" role="alert"><?php echo gT("You are editing an entry of the main menu!").' '.gT("Please be very careful."); ?></div>
		<?php endif; ?>

		<p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>

		<?php echo $form->errorSummary($model); ?>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'title'); ?>
			<?php echo $form->textField($model, 'title', array('class' => 'selector__hasInfoBox', 'size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model, 'title'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'menu_id'); ?>
			<?php echo $form->dropDownList($model, 'menu_id', $model->getMenuIdOptions(), ['options' => ['2' => ['selected' => true]]]); ?>
			<?php echo $form->error($model, 'menu_id'); ?>
		</div>
		

		<div class="form-group">
			<?php echo $form->labelEx($model, 'ordering'); ?>
			<?php echo $form->numberField($model, 'ordering'); ?>
			<?php echo $form->error($model, 'ordering'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'menu_description'); ?>
			<p class="selector_infoBox alert alert-info hidden"><?=gT('This will be shown when hovering over the menu.')?></p>
			<?php echo $form->textArea($model, 'menu_description', array('class' => 'selector__hasInfoBox', 'rows'=>6, 'cols'=>50)); ?>
			<?php echo $form->error($model, 'menu_description'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'menu_icon'); ?>
			<p class="selector_infoBox alert alert-info hidden"><?=gT('Use a fontawesome classname, or a link to the image.')?></p>
			<?php echo $form->textField($model, 'menu_icon', array('class' => 'selector__hasInfoBox', 'size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model, 'menu_icon'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'menu_icon_type'); ?>
			<?php echo $form->dropDownList($model, 'menu_icon_type', $model->getMenuIconTypeOptions()); ?>
			<?php echo $form->error($model, 'menu_icon_type'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'menu_link'); ?>
			<p class="selector_infoBox alert alert-warning hidden"><?=gT('If the external-option is not set, this will be appended to the current admin url.')?></p>
			<?php echo $form->textField($model, 'menu_link', array('class' => 'selector__hasInfoBox', 'size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model, 'menu_link'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'permission'); ?>
			<?php echo $form->dropDownList($model, 'permission', array_merge( ['' => 'No restriction'], Permission::getPermissionList())); ?>
			<?php echo $form->error($model, 'permission'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model, 'permission_grade'); ?>
			<?php echo $form->dropDownList($model, 'permission_grade', array_merge( ['' => 'No restriction'], Permission::getPermissionGradeList())); ?>
			<?php echo $form->error($model, 'permission_grade'); ?>
		</div>

		<div class="form-group">
			<div class="list-group">
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value="1" class="checkbox selector__dataOptionModel selector__disable_following" data-priority="6" data-option='["render","link","placeholder"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("Remove link")?></div>
				</label>
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value="1" class="checkbox selector__dataOptionModel selector__disable_following" data-priority="5" data-option='["render","link","external"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("External Link")?></div>
				</label>
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value="1" class="checkbox selector__dataOptionModel" checked="true" data-priority="4" data-option='["render","link","pjax"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("Load with pjax")?></div>
				</label>
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value='["survey", "sid"]' class="checkbox selector__dataOptionModel" data-priority="3" data-option='["render","link","data","surveyid"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("Add SurveyId to link")?></div>
				</label>
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value='["survey", "gsid"]' class="checkbox selector__dataOptionModel" data-priority="3" data-option='["render","link","data","gsid"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("Add survey group ID to link")?></div>
				</label>
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value='["questiongroup", "gid"]' class="checkbox selector__dataOptionModel" data-priority="2" data-option='["render","link","data","gid"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("Add question group ID to link")?></div>
				</label>
				<label class="list-group-item col-sm-6">
					<div class="col-sm-1 text-right"><input type="checkbox" data-value='["question", "qid"]' class="checkbox selector__dataOptionModel" data-priority="1" data-option='["render","link","data","qid"]' /></div>
					<div class="col-sm-10 text-left"><?=gT("Add question ID to link")?></div>
				</label>
			</div>
		</div>
		<div class="row ls-space margin bottom-10">
			<button class="btn btn-warning pull-right " type="button" data-toggle="collapse" data-target="#collapseAdvancedOptions"><?php eT('Toggle advanced options') ?></button>
		</div>
		<!-- Start collapsed advanced options -->
		<div class="collapse" id="collapseAdvancedOptions">
			
			<div class="form-group">
				<?php echo $form->labelEx($model, 'data'); ?>
				<?php echo $form->textArea($model, 'data', array('rows'=>6, 'cols'=>50 )); ?>
				<?php echo $form->error($model, 'data'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'name'); ?>
				<p class="selector_infoBox alert alert-info hidden"><?=gT('The name must be unique for all menu entries throughout the software.')?></p>
				<?php echo $form->textField($model, 'name', array('class' => 'selector__hasInfoBox', 'size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'name'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model, 'menu_title'); ?>
				<p class="selector_infoBox alert alert-info hidden"><?=gT('This is the content of the menu link - leave blank to use the title.')?></p>
				<?php echo $form->textField($model, 'menu_title', array('class' => 'selector__hasInfoBox', 'size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'menu_title'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'menu_class'); ?>
				<p class="selector_infoBox alert alert-warning hidden"><?=gT('If the link should have any extra classes, please insert them here.')?></p>
				<?php echo $form->textField($model, 'menu_class', array('class' => 'selector__hasInfoBox', 'size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'menu_class'); ?>
			</div>

            <div class="form-group">
				<?php echo $form->labelEx($model, 'user_id'); ?>
				<?php echo $form->dropDownList($model, 'user_id', $model->getUserIdOptions()); ?>
				<?php echo $form->error($model, 'user_id'); ?>
            </div>
            
			<div class="form-group">
				<?php echo $form->labelEx($model, 'action'); ?>
				<?php echo $form->textField($model, 'action', array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'action'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'template'); ?>
				<?php echo $form->textField($model, 'template', array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'template'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'partial'); ?>
				<?php echo $form->textField($model, 'partial', array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'partial'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'classes'); ?>
				<?php echo $form->textField($model, 'classes', array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'classes'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'getdatamethod'); ?>
				<?php echo $form->textField($model, 'getdatamethod', array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'getdatamethod'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model, 'language'); ?>
				<?php echo $form->textField($model, 'language', array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model, 'language'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model, 'showincollapse'); ?>
				<?php echo $form->checkbox($model, 'showincollapse'); ?>
				<?php echo $form->error($model, 'showincollapse'); ?>
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
<script>
	SurveymenuEntriesFunctions()();
</script>