<?php
/* @var $this BoxesController */
/* @var $model Boxes */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'boxes-form',
    // Please note: When you enable ajax validation, make sure the corresponding
    // controller action is handling ajax validation correctly.
    // There is a call to performAjaxValidation() commented in generated controller code.
    // See class documentation of CActiveForm for details on this.
    'enableAjaxValidation'=>false,
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'position'); ?>
        <?php echo $form->textField($model,'position'); ?>
        <?php echo $form->error($model,'position'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'url'); ?>
        <?php echo $form->textArea($model,'url',array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model,'url'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'title'); ?>
        <?php echo $form->textArea($model,'title',array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model,'title'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'ico'); ?>
        <?php echo $form->textField($model,'ico',array('size'=>60,'maxlength'=>255)); ?>
        <?php echo $form->error($model,'ico'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'desc'); ?>
        <?php echo $form->textArea($model,'desc',array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model,'desc'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'page'); ?>
        <?php echo $form->textArea($model,'page',array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model,'page'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'usergroup'); ?>
        <?php echo $form->textField($model,'usergroup'); ?>
        <?php echo $form->error($model,'usergroup'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
