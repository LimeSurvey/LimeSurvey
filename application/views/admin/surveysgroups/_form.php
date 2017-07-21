<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'surveys-groups-form',
    // Please note: When you enable ajax validation, make sure the corresponding
    // controller action is handling ajax validation correctly.
    // There is a call to performAjaxValidation() commented in generated controller code.
    // See class documentation of CActiveForm for details on this.
    'enableAjaxValidation'=>false,
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>


    <?php echo $form->errorSummary($model); ?>


    <?php echo $form->hiddenField($model,'name'); ?>
    <?php echo $form->hiddenField($model,'owner_uid'); ?>
    <?php echo $form->hiddenField($model,'gsid'); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'title'); ?>
        <?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>100)); ?>
        <?php echo $form->error($model,'title'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'description'); ?>
        <?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model,'description'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'order'); ?>
        <?php echo $form->textField($model,'order'); ?>
        <?php echo $form->error($model,'order'); ?>
    </div>

    <!-- should be a selector based on group name -->
    <div class="row">
        <?php echo $form->labelEx($model,'parent_id'); ?>
        <?php echo $form->textField($model,'parent_id'); ?>
        <?php echo $form->error($model,'parent_id'); ?>
    </div>


    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
