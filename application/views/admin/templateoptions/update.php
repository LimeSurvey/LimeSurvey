<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */

?>
<div class="col-lg-12 list-surveys">
    <h3><?php eT('Template options'); ?></h3>
</div>
<div class="row">
    <div class="col-sm-12 content-right">

        <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'template-options-form',
            // Please note: When you enable ajax validation, make sure the corresponding
            // controller action is handling ajax validation correctly.
            // There is a call to performAjaxValidation() commented in generated controller code.
            // See class documentation of CActiveForm for details on this.
            'enableAjaxValidation'=>false,
        )); ?>

            <?php echo $form->errorSummary($model); ?>

            <?php echo $form->hiddenField($model,'templates_name'); ?>
            <?php echo $form->hiddenField($model,'sid'); ?>
            <?php echo $form->hiddenField($model,'gsid'); ?>
            <?php echo $form->hiddenField($model,'uid'); ?>
            <?php echo $form->hiddenField($model,'files_css'); ?>
            <?php echo $form->hiddenField($model,'files_js'); ?>
            <?php echo $form->hiddenField($model,'files_print_css'); ?>
            <?php echo $form->hiddenField($model,'options'); ?>
            <?php echo $form->hiddenField($model,'cssframework_name'); ?>
            <?php echo $form->hiddenField($model,'cssframework_css'); ?>
            <?php echo $form->hiddenField($model,'cssframework_js'); ?>
            <?php echo $form->hiddenField($model,'packages_to_load'); ?>


        <?php $this->endWidget(); ?>
    </div>
</div>
