<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
/* @var $form CActiveForm */
?>
<div class="row ls-flex-row align-content-center align-items-center">
<div class="form col-sm-10">

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'surveys-groups-form',
    // Please note: When you enable ajax validation, make sure the corresponding
    // controller action is handling ajax validation correctly.
    // There is a call to performAjaxValidation() commented in generated controller code.
    // See class documentation of CActiveForm for details on this.
    'enableAjaxValidation'=>false,
)); ?>

    <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>


    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->hiddenField($model,'owner_id'); ?>
    <?php echo $form->hiddenField($model,'gsid'); ?>

    <?php if($model->isNewRecord): ?>
        <div class="row">
            <?php echo $form->labelEx($model,'name'); ?>
            <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100)); ?>
            <?php echo $form->error($model,'name'); ?>
        </div>
    <?php else: ?>
        <?php echo $form->hiddenField($model,'name'); ?>
    <?php endif; ?>
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
        <?php $model->sortorder = $model->sortorder ? $model->sortorder : $model->getNextOrderPosition(); ?>
        <?php echo $form->labelEx($model,'sortorder'); ?>
        <?php echo $form->textField($model,'sortorder'); ?>
        <?php echo $form->error($model,'sortorder'); ?>
    </div>

    <!-- should be a selector based on group name -->
    <div class="row">
        <?php echo $form->labelEx($model,'parent_id'); ?>
        <?php echo $form->dropDownList($model,'parent_id',$model->getParentGroupOptions($model->gsid)); ?>
        <?php echo $form->error($model,'parent_id'); ?>
    </div>


    <div class="hidden">
            <?php echo TbHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'btn btn-success col-md-2 col-sm-4', "id"=>"surveys-groups-form-submit")); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
</div>
