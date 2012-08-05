<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'potensi-form-index-form',
    'enableAjaxValidation'=>false,
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'provinsiid'); ?>
        <?php echo $form->textField($model,'provinsiid'); ?>
        <?php echo $form->error($model,'provinsiid'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kabupatenid'); ?>
        <?php echo $form->textField($model,'kabupatenid'); ?>
        <?php echo $form->error($model,'kabupatenid'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kecamatanid'); ?>
        <?php echo $form->textField($model,'kecamatanid'); ?>
        <?php echo $form->error($model,'kecamatanid'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'desaid'); ?>
        <?php echo $form->textField($model,'desaid'); ?>
        <?php echo $form->error($model,'desaid'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'katAll'); ?>
        <?php echo $form->textField($model,'katAll'); ?>
        <?php echo $form->error($model,'katAll'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat3'); ?>
        <?php echo $form->textField($model,'kat3'); ?>
        <?php echo $form->error($model,'kat3'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat4'); ?>
        <?php echo $form->textField($model,'kat4'); ?>
        <?php echo $form->error($model,'kat4'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat5'); ?>
        <?php echo $form->textField($model,'kat5'); ?>
        <?php echo $form->error($model,'kat5'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat6'); ?>
        <?php echo $form->textField($model,'kat6'); ?>
        <?php echo $form->error($model,'kat6'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat7'); ?>
        <?php echo $form->textField($model,'kat7'); ?>
        <?php echo $form->error($model,'kat7'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat8'); ?>
        <?php echo $form->textField($model,'kat8'); ?>
        <?php echo $form->error($model,'kat8'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat9'); ?>
        <?php echo $form->textField($model,'kat9'); ?>
        <?php echo $form->error($model,'kat9'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat10'); ?>
        <?php echo $form->textField($model,'kat10'); ?>
        <?php echo $form->error($model,'kat10'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'kat12'); ?>
        <?php echo $form->textField($model,'kat12'); ?>
        <?php echo $form->error($model,'kat12'); ?>
    </div>


    <div class="row buttons">
        <?php echo CHtml::submitButton('Submit'); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->