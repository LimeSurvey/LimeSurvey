<?php
/* @var $this SurveysGroupsController */

/* @var $model SurveysGroups */
/* @var $form CActiveForm */
?>
<div class="form col-lg-5">
    <?php $updateRigth = $aRigths['update'] ?>

    <?php
    $form = $this->beginWidget('TbActiveForm', array(
        'id' => 'surveys-groups-form',
        'action' => $action,
        // Please note: When you enable ajax validation, make sure the corresponding
        // controller action is handling ajax validation correctly.
        // There is a call to performAjaxValidation() commented in generated controller code.
        // See class documentation of CActiveForm for details on this.
        'enableAjaxValidation' => false,
        'enableClientValidation' => $updateRigth,
    ));
    ?>

    <p aria-level="2" class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>


    <?php
    $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]);
    ?>

    <?php echo $form->hiddenField($model, 'owner_id'); ?>
    <?php echo $form->hiddenField($model, 'gsid'); ?>

    <div class="mb-3">
        <?php echo $form->labelEx($model, 'name'); ?>
        <?php echo $form->textField($model, 'name', array('size' => 60, 'maxlength' => 100, 'readonly' => !$model->isNewRecord || !$updateRigth)); ?>
        <?php echo $form->error($model, 'name', array('errorCssClass' => 'text-danger')); ?>
    </div>
    <div class="mb-3">
        <?php echo $form->labelEx($model, 'title'); ?>
        <?php echo $form->textField($model, 'title', array('size' => 60, 'maxlength' => 100, 'readonly' => !$updateRigth)); ?>
        <?php echo $form->error($model, 'title', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <div class="mb-3">
        <?php echo $form->labelEx($model, 'description'); ?>
        <?php echo $form->textArea($model, 'description', array('rows' => 6, 'cols' => 50, 'readonly' => !$updateRigth)); ?>
        <?php echo $form->error($model, 'description', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <div class="mb-3">
        <?php $model->sortorder = $model->sortorder ? $model->sortorder : $model->getNextOrderPosition(); ?>
        <?php echo $form->labelEx($model, 'sortorder'); ?>
        <?php echo $form->textField($model, 'sortorder', array('readonly' => !$updateRigth)); ?>
        <?php echo $form->error($model, 'sortorder', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <!-- should be a selector based on group name -->
    <div class="mb-3">
        <?php echo $form->labelEx($model, 'parent_id'); ?>
        <?php echo $form->dropDownList($model, 'parent_id', $model->getParentGroupOptions($model->gsid), array('disabled' => !$updateRigth, 'class' => 'form-select')); ?>
        <?php echo $form->error($model, 'parent_id', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <!-- User list -->
    <div class="mb-3">
        <?php echo $form->labelEx($model, 'owner_id'); ?>
        <?php echo $form->dropDownList($model, 'owner_id', CHtml::listData($oUsers, 'uid', 'full_name'), array('disabled' => !$aRigths['owner_id'], 'class' => 'form-select')); ?>
        <?php echo $form->error($model, 'owner_id', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <!-- User list -->
    <div class="form-check">
        <?php echo $form->checkBox($model, 'alwaysavailable', array('disabled' => !$updateRigth && $model->gsid == 1, 'class' => 'form-check-input')); ?>
        <?php echo $form->labelEx($model, 'alwaysavailable', array('class' => 'form-check-label')); ?>
        <?php echo $form->error($model, 'alwaysavailable', array('errorCssClass' => 'text-danger')); ?>
        <div class="form-text"><?= gT("When public mode is active, any user can see the survey group. This allows any user to put surveys in this group."); ?></div>
        <?php if ($model->gsid == 1) : ?>
            <div class="form-text"><?= gT("The default group is always public."); ?></div>
        <?php endif; ?>
    </div>

    <?php if ($updateRigth) : ?>
        <div class="d-none">
            <?php echo TbHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'btn btn-primary col-lg-2 col-md-4', "id" => "surveys-groups-form-submit")); ?>
        </div>
    <?php endif ?>

    <?php $this->endWidget(); ?>

</div><!-- form -->
