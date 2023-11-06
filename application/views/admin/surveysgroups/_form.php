<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
/* @var $form CActiveForm */
?>
<div class="row ls-flex-row align-content-center align-items-center">
<div class="form col-sm-10">
<?php $updateRigth = $aRigths['update'] ?>

<?php
    $form=$this->beginWidget('TbActiveForm', array(
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

    <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>


    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->hiddenField($model,'owner_id'); ?>
    <?php echo $form->hiddenField($model,'gsid'); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100,'readonly' => !$model->isNewRecord || !$updateRigth)); ?>
        <?php echo $form->error($model,'name', array('errorCssClass' => 'text-danger')); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model,'title'); ?>
        <?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>100,'readonly' => !$updateRigth)); ?>
        <?php echo $form->error($model,'title', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'description'); ?>
        <?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50,'readonly' => !$updateRigth)); ?>
        <?php echo $form->error($model,'description', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <div class="row">
        <?php $model->sortorder = $model->sortorder ? $model->sortorder : $model->getNextOrderPosition(); ?>
        <?php echo $form->labelEx($model,'sortorder'); ?>
        <?php echo $form->textField($model,'sortorder', array('readonly' => !$updateRigth)); ?>
        <?php echo $form->error($model,'sortorder', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <!-- should be a selector based on group name -->
    <div class="row">
        <?php echo $form->labelEx($model,'parent_id'); ?>
        <?php echo $form->dropDownList($model,'parent_id',$model->getParentGroupOptions($model->gsid),array('disabled' => !$updateRigth)); ?>
        <?php echo $form->error($model,'parent_id', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <!-- User list -->
    <div class="row">
        <?php echo $form->labelEx($model,'owner_id'); ?>
        <?php echo $form->dropDownList($model,'owner_id',CHtml::listData($oUsers, 'uid', 'full_name'),array('disabled' => !$aRigths['owner_id'])); ?>
        <?php echo $form->error($model,'owner_id', array('errorCssClass' => 'text-danger')); ?>
    </div>

    <!-- User list -->
    <div class="row">
        <?php echo $form->labelEx($model,'alwaysavailable'); ?>
        <?php echo $form->checkBox($model,'alwaysavailable',array('disabled' => !$updateRigth && $model->gsid == 1)); ?>
        <?php echo $form->error($model,'alwaysavailable', array('errorCssClass' => 'text-danger')); ?>
        <p class="help-block"><?= gT("When public mode is active, any user can see the survey group. This allows any user to put surveys in this group."); ?></p>
        <?php if($model->gsid == 1) : ?>
        <p class="help-block"><?= gT("The default group is always public."); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($updateRigth) : ?>
    <div class="hidden">
            <?php echo TbHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'btn btn-success col-md-2 col-sm-4', "id"=>"surveys-groups-form-submit")); ?>
    </div>
    <?php endif?>

<?php $this->endWidget(); ?>

</div><!-- form -->
</div>
