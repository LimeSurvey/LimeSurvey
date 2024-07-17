<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */
/* @var $form CActiveForm */
?>

<?php $form = $this->beginWidget('TbActiveForm', array(
<<<<<<< HEAD
	'id' => 'surveymenu-entries-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation' => false,
	'htmlOptions' => ['class' => 'form '],
	'action' => Yii::app()->getController()->createUrl('admin/menuentries/sa/update', ['id' => $model->id])
=======
    'id' => 'surveymenu-entries-form',
    // Please note: When you enable ajax validation, make sure the corresponding
    // controller action is handling ajax validation correctly.
    // There is a call to performAjaxValidation() commented in generated controller code.
    // See class documentation of CActiveForm for details on this.
    'enableAjaxValidation' => false,
    'htmlOptions' => ['class' => 'form '],
    'action' => Yii::app()->getController()->createUrl('admin/menuentries/sa/update', ['id' => $model->id])
>>>>>>> master
));

$modalTitle = $model->isNewRecord ? gT('Create new survey menu entry') : gT('Edit survey menu entry');
Yii::app()->getController()->renderPartial(
	'/layouts/partial_modals/modal_header',
	['modalTitle' => $modalTitle]
);
?>
<div class="modal-body">
    <div class="container-fluid">

<<<<<<< HEAD
		<?php //Warn on edition of the main menu, though damaging it can do serious harm
		?>
		<?php if (!$model->isNewRecord && $model->menu_id == '1') : ?>
			<?php
			$this->widget('ext.AlertWidget.AlertWidget', [
				'text' => gT("You are editing an entry of the main menu!") . ' ' . gT("Please be very careful."),
				'type' => 'danger',
			]);
			?>
		<?php endif; ?>
=======
        <?php //Warn on edition of the main menu, though damaging it can do serious harm?>
        <?php if (!$model->isNewRecord && $model->menu_id == '1') :?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT("You are editing an entry of the main menu!") . ' ' . gT("Please be very careful."),
                'type' => 'danger',
            ]);
            ?>
        <?php endif; ?>
>>>>>>> master

        <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>

		<?php
		$this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]);
		?>

<<<<<<< HEAD
		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'title', array('for' => CHtml::activeId($model, 'title_1'))); ?>
			<?php echo $form->textField($model, 'title', array('id' => CHtml::activeId($model, 'title_1'), 'class' => 'selector__hasInfoBox', 'size' => 60, 'required' => true, 'maxlength' => 255)); ?>
			<?php echo $form->error($model, 'title'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'menu_id', array('for' => CHtml::activeId($model, 'menu_id_1'))); ?>
			<?php echo $form->dropDownList($model, 'menu_id', $model->getMenuIdOptions(), ['options' => ['2' => ['selected' => true]], 'class' => 'form-select', 'id' => CHtml::activeId($model, 'menu_id_1')]); ?>
			<?php echo $form->error($model, 'menu_id'); ?>
		</div>


		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'ordering', array('for' => CHtml::activeId($model, 'ordering_1'))); ?>
			<?php echo $form->numberField($model, 'ordering', ['id' => CHtml::activeId($model, 'ordering_1')]); ?>
			<?php echo $form->error($model, 'ordering'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'menu_description', array('for' => CHtml::activeId($model, 'menu_description_1'))); ?>
			<?php
			$this->widget('ext.AlertWidget.AlertWidget', [
				'text' => gT('This will be shown when hovering over the menu.'),
				'type' => 'info',
				'htmlOptions' => ['class' => 'selector_infoBox d-none']
			]);
			?>
			<?php echo $form->textArea($model, 'menu_description', array('class' => 'selector__hasInfoBox', 'rows' => 6, 'cols' => 50, 'id' => CHtml::activeId($model, 'menu_description_1'))); ?>
			<?php echo $form->error($model, 'menu_description'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'menu_icon', array('for' => CHtml::activeId($model, 'menu_icon_1'))); ?>
			<?php
			$this->widget('ext.AlertWidget.AlertWidget', [
				'text' => gT('Use a remix icon classname, or a link to the image.'),
				'type' => 'info',
				'htmlOptions' => ['class' => 'selector_infoBox d-none']
			]);
			?>
			<?php echo $form->textField($model, 'menu_icon', array('class' => 'selector__hasInfoBox', 'size' => 60, 'required' => true, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'menu_icon_1'))); ?>
			<?php echo $form->error($model, 'menu_icon'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'menu_icon_type', array('for' => CHtml::activeId($model, 'menu_icon_type_1'))); ?>
			<?php echo $form->dropDownList($model, 'menu_icon_type', $model->getMenuIconTypeOptions(), ['class' => 'form-select', 'required' => true,'id' => CHtml::activeId($model, 'menu_icon_type_1')]); ?>
			<?php echo $form->error($model, 'menu_icon_type'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'menu_link', array('for' => CHtml::activeId($model, 'menu_link_1'))); ?>
			<?php
			$this->widget('ext.AlertWidget.AlertWidget', [
				'text' => gT('If the external-option is not set, this will be appended to the current admin url.'),
				'type' => 'warning',
				'htmlOptions' => ['class' => 'selector_infoBox d-none']
			]);
			?>
			<?php echo $form->textField($model, 'menu_link', array('class' => 'selector__hasInfoBox', 'size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'menu_link_1'))); ?>
			<?php echo $form->error($model, 'menu_link'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'permission', array('for' => CHtml::activeId($model, 'permission_1'))); ?>
			<?php echo $form->dropDownList($model, 'permission', array_merge(['' => 'No restriction'], Permission::getPermissionList()), ['class' => 'form-select', 'id' => CHtml::activeId($model, 'permission_1')]); ?>
			<?php echo $form->error($model, 'permission'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<?php echo $form->labelEx($model, 'permission_grade', array('for' => CHtml::activeId($model, 'permission_grade_1'))); ?>
			<?php echo $form->dropDownList($model, 'permission_grade', array_merge(['' => 'No restriction'], Permission::getPermissionGradeList()), ['class' => 'form-select', 'id' => CHtml::activeId($model, 'permission_grade_1')]); ?>
			<?php echo $form->error($model, 'permission_grade'); ?>
		</div>

		<div class="ex-form-group mb-3">
			<ul class="list-group">
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="remove-link" type="checkbox" data-value="1" class="form-check-input checkbox selector__dataOptionModel selector__disable_following" data-priority="6" data-option='["render","link","placeholder"]' />
						<label class="form-check-label" for="remove-link"><?= gT("Remove link") ?></label>
					</div>
				</li>
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="external-link" type="checkbox" data-value="1" class="form-check-input checkbox selector__dataOptionModel selector__disable_following" data-priority="5" data-option='["render","link","external"]' />
						<label class="form-check-label" for="external-link"><?= gT("External Link") ?></label>
					</div>
				</li>
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="load-with-pjax" type="checkbox" data-value="1" class="form-check-input checkbox selector__dataOptionModel" checked="true" data-priority="4" data-option='["render","link","pjax"]' />
						<label class="form-check-label" for="load-with-pjax"><?= gT("Load with pjax") ?></label>
					</div>
				</li>
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="add-survey-id" type="checkbox" data-value='["survey", "sid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="3" data-option='["render","link","data","surveyid"]' />
						<label class="form-check-label" for="add-survey-id"><?= gT("Add SurveyId to link") ?></label>
					</div>
				</li>
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="add-survey-group-id" type="checkbox" data-value='["survey", "gsid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="3" data-option='["render","link","data","gsid"]' />
						<label class="form-check-label" for="add-survey-group-id"><?= gT("Add survey group ID to link") ?></label>
					</div>
				</li>
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="add-question-group-id" type="checkbox" data-value='["questiongroup", "gid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="2" data-option='["render","link","data","gid"]' />
						<label class="form-check-label" for="add-question-group-id"><?= gT("Add question group ID to link") ?></label>
					</div>
				</li>
				<li class="list-group-item col-md-6">
					<div class="form-check">
						<input id="add-question-id" type="checkbox" data-value='["question", "qid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="1" data-option='["render","link","data","qid"]' />
						<label class="form-check-label" for="add-question-id"><?= gT("Add question ID to link") ?></label>
					</div>
				</li>
			</ul>
		</div>
		<div class="row ls-space margin bottom-10">
			<button class="btn btn-warning float-end " type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvancedOptions"><?php eT('Toggle advanced options') ?></button>
		</div>
		<!-- Start collapsed advanced options -->
		<div class="collapse" id="collapseAdvancedOptions">

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'data', array('for' => CHtml::activeId($model, 'data_1'))); ?>
				<?php echo $form->textArea($model, 'data', array('rows' => 6, 'cols' => 50, 'id' => CHtml::activeId($model, 'data_1'))); ?>
				<?php echo $form->error($model, 'data'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'name', array('for' => CHtml::activeId($model, 'name_1'))); ?>
				<?php
				$this->widget('ext.AlertWidget.AlertWidget', [
					'text' => gT('The name must be unique for all menu entries throughout the software.'),
					'type' => 'warning',
					'htmlOptions' => ['class' => 'selector_infoBox d-none']
				]);
				?>
				<?php echo $form->textField($model, 'name', array('class' => 'selector__hasInfoBox', 'size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'name_1'))); ?>
				<?php echo $form->error($model, 'name'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'menu_title', array('for' => CHtml::activeId($model, 'menu_title_1'))); ?>
				<?php
				$this->widget('ext.AlertWidget.AlertWidget', [
					'text' => gT('This is the content of the menu link - leave blank to use the title.'),
					'type' => 'info',
					'htmlOptions' => ['class' => 'selector_infoBox d-none']
				]);
				?>
				<?php echo $form->textField($model, 'menu_title', array('class' => 'selector__hasInfoBox', 'size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'menu_title_1'), 'required' => 'true')); ?>
				<?php echo $form->error($model, 'menu_title'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'menu_class', array('for' => CHtml::activeId($model, 'menu_class_1'))); ?>
				<?php
				$this->widget('ext.AlertWidget.AlertWidget', [
					'text' => gT('If the link should have any extra classes, please insert them here.'),
					'type' => 'warning',
					'htmlOptions' => ['class' => 'selector_infoBox d-none']
				]);
				?>
				<?php echo $form->textField($model, 'menu_class', array('class' => 'selector__hasInfoBox', 'size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'menu_class_1'))); ?>
				<?php echo $form->error($model, 'menu_class'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'user_id', array('for' => CHtml::activeId($model, 'user_id_1'))); ?>
				<?php echo $form->dropDownList($model, 'user_id', $model->getUserIdOptions(), ['class' => 'form-select', 'id' => CHtml::activeId($model, 'user_id_1')]); ?>
				<?php echo $form->error($model, 'user_id'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'action', array('for' => CHtml::activeId($model, 'action_1'))); ?>
				<?php echo $form->textField($model, 'action', array('size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'action_1'))); ?>
				<?php echo $form->error($model, 'action'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'template', array('for' => CHtml::activeId($model, 'template_1'))); ?>
				<?php echo $form->textField($model, 'template', array('size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'template_1'))); ?>
				<?php echo $form->error($model, 'template'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'partial', array('for' => CHtml::activeId($model, 'partial_1'))); ?>
				<?php echo $form->textField($model, 'partial', array('size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'partial_1'))); ?>
				<?php echo $form->error($model, 'partial'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'classes', array('for' => CHtml::activeId($model, 'classes_1'))); ?>
				<?php echo $form->textField($model, 'classes', array('size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'classes_1'))); ?>
				<?php echo $form->error($model, 'classes'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'getdatamethod', array('for' => CHtml::activeId($model, 'getdatamethod_1'))); ?>
				<?php echo $form->textField($model, 'getdatamethod', array('size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'getdatamethod_1'))); ?>
				<?php echo $form->error($model, 'getdatamethod'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'language', array('for' => CHtml::activeId($model, 'language_1'))); ?>
				<?php echo $form->textField($model, 'language', array('size' => 60, 'maxlength' => 255, 'id' => CHtml::activeId($model, 'language_1'))); ?>
				<?php echo $form->error($model, 'language'); ?>
			</div>

			<div class="ex-form-group mb-3">
				<?php echo $form->labelEx($model, 'showincollapse', array('for' => CHtml::activeId($model, 'showincollapse_1'))); ?>
				<?php echo $form->checkbox($model, 'showincollapse', array('id' => CHtml::activeId($model, 'showincollapse_1'))); ?>
				<?php echo $form->error($model, 'showincollapse'); ?>
			</div>
		</div>

		<?php echo $form->hiddenField($model, 'changed_by', ['value' => $user]); ?>
		<?php echo $form->hiddenField($model, 'changed_at', ['value' => date('Y-m-d H:i:s')]); ?>
		<?php echo $form->hiddenField($model, 'created_by', ['value' => (empty($model->created_by) ? $user : $model->created_by)]); ?>
		<?php echo $form->hiddenField($model, 'id'); ?>
=======
        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'title'); ?>
            <?php echo $form->textField($model, 'title', array('class' => 'selector__hasInfoBox', 'size' => 60, 'required' => true, 'maxlength' => 255)); ?>
            <?php echo $form->error($model, 'title'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'menu_id'); ?>
            <?php echo $form->dropDownList($model, 'menu_id', $model->getMenuIdOptions(), ['class' => 'form-select', 'required' => true, 'empty' => gT("Please choose")]); ?>
            <?php echo $form->error($model, 'menu_id'); ?>
        </div>


        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'ordering'); ?>
            <?php echo $form->numberField($model, 'ordering'); ?>
            <?php echo $form->error($model, 'ordering'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'menu_description'); ?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('This will be shown when hovering over the menu.'),
                'type' => 'info',
                'htmlOptions' => ['class' => 'selector_infoBox d-none']
            ]);
            ?>
            <?php echo $form->textArea($model, 'menu_description', array('class' => 'selector__hasInfoBox', 'rows' => 6, 'cols' => 50)); ?>
            <?php echo $form->error($model, 'menu_description'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'menu_icon_type'); ?>
            <?php echo $form->dropDownList($model, 'menu_icon_type', $model->getMenuIconTypeOptions(), ['class' => 'form-select', 'required' => false, 'empty' => gT("None") ]); ?>
            <?php echo $form->error($model, 'menu_icon_type'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'menu_icon'); ?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('Use a remix icon classname or any other classname, font awesome partial classname or a link to the image.'),
                'type' => 'info',
                'htmlOptions' => ['class' => 'selector_infoBox d-none']
            ]);
            ?>
            <?php echo $form->textField($model, 'menu_icon', array('class' => 'selector__hasInfoBox', 'size' => 60, 'maxlength' => 255)); ?>
            <?php echo $form->error($model, 'menu_icon'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'menu_link'); ?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('If the external-option is not set, this will be appended to the current admin url.'),
                'type' => 'warning',
                'htmlOptions' => ['class' => 'selector_infoBox d-none']
            ]);
            ?>
            <?php echo $form->textField($model, 'menu_link', array('class' => 'selector__hasInfoBox', 'size' => 60,'maxlength' => 255)); ?>
            <?php echo $form->error($model, 'menu_link'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'permission'); ?>
            <?php echo $form->dropDownList($model, 'permission', array_merge(['' => 'No restriction'], Permission::getPermissionList()), ['class' => 'form-select']); ?>
            <?php echo $form->error($model, 'permission'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <?php echo $form->labelEx($model, 'permission_grade'); ?>
            <?php echo $form->dropDownList($model, 'permission_grade', array_merge(['' => 'No restriction'], Permission::getPermissionGradeList()), ['class' => 'form-select']); ?>
            <?php echo $form->error($model, 'permission_grade'); ?>
        </div>

        <div class="ex-form-group mb-3">
            <ul class="list-group">
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="remove-link" type="checkbox" data-value="1" class="form-check-input checkbox selector__dataOptionModel selector__disable_following" data-priority="6" data-option='["render","link","placeholder"]' />
                        <label class="form-check-label" for="remove-link"><?=gT("Remove link")?></label>
                    </div>
                </li>
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="external-link" type="checkbox" data-value="1" class="form-check-input checkbox selector__dataOptionModel selector__disable_following" data-priority="5" data-option='["render","link","external"]' />
                        <label class="form-check-label" for="external-link"><?=gT("External Link")?></label>
                    </div>
                </li>
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="load-with-pjax" type="checkbox" data-value="1" class="form-check-input checkbox selector__dataOptionModel" checked="true" data-priority="4" data-option='["render","link","pjax"]' />
                        <label class="form-check-label" for="load-with-pjax"><?=gT("Load with pjax")?></label>
                    </div>
                </li>
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="add-survey-id" type="checkbox" data-value='["survey", "sid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="3" data-option='["render","link","data","surveyid"]' />
                        <label class="form-check-label" for="add-survey-id"><?=gT("Add SurveyId to link")?></label>
                    </div>
                </li>
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="add-survey-group-id" type="checkbox" data-value='["survey", "gsid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="3" data-option='["render","link","data","gsid"]' />
                        <label class="form-check-label" for="add-survey-group-id"><?=gT("Add survey group ID to link")?></label>
                    </div>
                </li>
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="add-question-group-id" type="checkbox" data-value='["questiongroup", "gid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="2" data-option='["render","link","data","gid"]' />
                        <label class="form-check-label" for="add-question-group-id"><?=gT("Add question group ID to link")?></label>
                    </div>
                </li>
                <li class="list-group-item col-md-6">
                    <div class="form-check">
                        <input id="add-question-id" type="checkbox" data-value='["question", "qid"]' class="form-check-input checkbox selector__dataOptionModel" data-priority="1" data-option='["render","link","data","qid"]' />
                        <label class="form-check-label" for="add-question-id"><?=gT("Add question ID to link")?></label>
                    </div>
                </li>
            </ul>
        </div>
        <div class="row ls-space margin bottom-10">
            <button class="btn btn-warning float-end " type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvancedOptions"><?php eT('Toggle advanced options') ?></button>
        </div>
        <!-- Start collapsed advanced options -->
        <div class="collapse" id="collapseAdvancedOptions">

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'data'); ?>
                <?php echo $form->textArea($model, 'data', array('rows' => 6, 'cols' => 50 )); ?>
                <?php echo $form->error($model, 'data'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'name'); ?>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT('The name must be unique for all menu entries throughout the software.'),
                        'type' => 'warning',
                        'htmlOptions' => ['class' => 'selector_infoBox d-none']
                    ]);
                    ?>
                <?php echo $form->textField($model, 'name', array('class' => 'selector__hasInfoBox', 'size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'name'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'menu_title'); ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT('This is the content of the menu link - leave blank to use the title.'),
                    'type' => 'info',
                    'htmlOptions' => ['class' => 'selector_infoBox d-none']
                ]);
                ?>
                <?php echo $form->textField($model, 'menu_title', array('class' => 'selector__hasInfoBox', 'size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'menu_title'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'menu_class'); ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT('If the link should have any extra classes, please insert them here.'),
                    'type' => 'warning',
                    'htmlOptions' => ['class' => 'selector_infoBox d-none']
                ]);
                ?>
                <?php echo $form->textField($model, 'menu_class', array('class' => 'selector__hasInfoBox', 'size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'menu_class'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'user_id'); ?>
                <?php echo $form->dropDownList($model, 'user_id', $model->getUserIdOptions(), ['class' => 'form-select']); ?>
                <?php echo $form->error($model, 'user_id'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'action'); ?>
                <?php echo $form->textField($model, 'action', array('size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'action'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'template'); ?>
                <?php echo $form->textField($model, 'template', array('size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'template'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'partial'); ?>
                <?php echo $form->textField($model, 'partial', array('size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'partial'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'classes'); ?>
                <?php echo $form->textField($model, 'classes', array('size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'classes'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'getdatamethod'); ?>
                <?php echo $form->textField($model, 'getdatamethod', array('size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'getdatamethod'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'language'); ?>
                <?php echo $form->textField($model, 'language', array('size' => 60,'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'language'); ?>
            </div>

            <div class="ex-form-group mb-3">
                <?php echo $form->labelEx($model, 'showincollapse'); ?>
                <?php echo $form->checkbox($model, 'showincollapse'); ?>
                <?php echo $form->error($model, 'showincollapse'); ?>
            </div>
        </div>
>>>>>>> master

        <?php echo $form->hiddenField($model, 'changed_by', ['value' => $user]);?>
        <?php echo $form->hiddenField($model, 'changed_at', ['value' => date('Y-m-d H:i:s')]);?>
        <?php echo $form->hiddenField($model, 'created_by', ['value' => (empty($model->created_by) ? $user : $model->created_by)]);?>
        <?php echo $form->hiddenField($model, 'id');?>

    </div>
</div>
<<<<<<< HEAD
<div class="modal-footer">
	<button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
		<?= gT('Cancel') ?>
	</button>
	<?php echo TbHtml::submitButton(($model->isNewRecord ? gT('Create') : gT('Save')), array('class' => 'btn-primary')); ?>

</div>
=======
    <div class="modal-footer">
        <button
            type="button"
            class="btn btn-cancel"
            data-bs-dismiss="modal">
            <?=gT('Cancel')?>
        </button>
        <?php echo TbHtml::submitButton(($model->isNewRecord ? gT('Create') : gT('Save')), array('class' => 'btn-primary')); ?>

    </div>
>>>>>>> master

<?php $this->endWidget(); ?>
<!-- form -->
<script>
<<<<<<< HEAD
	SurveymenuEntriesFunctions()();
</script>
=======
    SurveymenuEntriesFunctions()();
</script>
>>>>>>> master
