<?php
/* @var $this BoxesController */
/* @var $model Boxes */

?>
<div class="col-lg-12 list-surveys">

    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'savebutton' => array('form' => 'boxes-form'),
            'saveandclosebutton' => array('form' => 'boxes-form'),
            'closebutton' => array('url' => true)
        )
    )); ?>

    <h3><?php eT('Update Boxes');?> <em><?php echo $model->title; ?></em></h3>

    <div class="row">
        <div class="col-lg-12 content-right">


            <?php $form=$this->beginWidget('CActiveForm', array(
                'id'=>'boxes-form',
                // Please note: When you enable ajax validation, make sure the corresponding
                // controller action is handling ajax validation correctly.
                // There is a call to performAjaxValidation() commented in generated controller code.
                // See class documentation of CActiveForm for details on this.
                'enableAjaxValidation'=>false,
                'htmlOptions'=>array(
                    'class'=>"form-horizontal",
                )
            )); ?>
                <p class="note">Fields with <span class="required">*</span> are required.</p>


                <?php echo $form->errorSummary($model); ?>

                <div class="form-group">
                    <label class='control-label col-sm-2'><?php echo $form->labelEx($model,'position'); ?></label>
                    <div class='col-sm-2'>
                        <?php echo $form->textField($model,'position', array('class' => 'form-control')); ?>
                    </div>
                    <div class='col-sm-2'>
                        <?php echo $form->error($model,'position'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class='control-label col-sm-2'><?php echo $form->labelEx($model,'url'); ?></label>
                    <div class='col-sm-2'>
                        <?php echo $form->textField($model,'url',array('class' => 'form-control')); ?>
                    </div>
                    <div class='col-sm-2'>
                        <?php echo $form->error($model,'url'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class='control-label col-sm-2'><?php echo $form->labelEx($model,'title'); ?></label>
                    <div class='col-sm-2'>
                        <?php echo $form->textField($model,'title',array('class' => 'form-control')); ?>
                    </div>
                    <div class='col-sm-2'>
                        <?php echo $form->error($model,'title'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class='control-label col-sm-2'><?php echo $form->labelEx($model,'ico'); ?></label>
                    <div class='col-sm-2'>
                        <?php echo $form->textField($model,'ico',array('size'=>60,'maxlength'=>255, 'class' => 'form-control')); ?>
                    </div>
                    <div class='col-sm-2'>
                        <?php echo $form->error($model,'ico'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class='control-label col-sm-2'><?php echo $form->labelEx($model,'desc'); ?></label>
                    <div class='col-sm-4'>
                        <?php echo $form->textArea($model,'desc',array('rows'=>6, 'cols'=>50, 'class' => 'form-control')); ?>
                    </div>
                    <div class='col-sm-4'>
                        <?php echo $form->error($model,'desc'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo $form->hiddenField($model,'page',array()); ?>
                </div>

                <div class="form-group">
                    <label class='control-label col-sm-2'><?php echo $form->labelEx($model,'usergroup'); ?></label>
                    <div class='col-sm-2'>
                        <?php echo $form->textField($model,'usergroup', array('class' => 'form-control')); ?>
                    </div>
                    <div class='col-sm-2'>
                        <?php echo $form->error($model,'usergroup'); ?>
                    </div>
                </div>

                <div class="form-group buttons">
                    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'form-control hidden')); ?>
                </div>

            <?php $this->endWidget(); ?>


        </div>
    </div>

</div>
