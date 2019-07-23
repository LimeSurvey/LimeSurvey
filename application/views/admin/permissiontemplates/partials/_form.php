<?php
/* @var $this PermissiontemplatesController */
/* @var $model Permissiontemplates */
/* @var $form CActiveForm */
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle-addedit">
        <?php if($model->isNewRecord) {
            echo gT('Create permission role');
        } else {
            echo sprintf(gT('Update permission role %s'), $model->name);
        }?>
    </h4>
</div>
<div class="modal-body">
    <div class="container-center">
        <div class="form">

        <?php $form=$this->beginWidget('TbActiveForm', array(
            'id'=>'RoleControl--modalform',
            'action' => App()->createUrl('admin/roles/sa/applyedit'),
            'enableAjaxValidation'=>false,
        )); ?>
            <?php echo $form->hiddenField($model,'ptid'); ?>

            <div class="row">
                <?php echo $form->labelEx($model,'name'); ?>
                <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>192)); ?>
                <?php echo $form->error($model,'name'); ?>
            </div>

            <div class="row">
                <?php echo $form->labelEx($model,'description'); ?>
                <?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
                <?php echo $form->error($model,'description'); ?>
            </div>

            <?php echo $form->hiddenField($model,'renewed_last', ['value' => date('Y-m-d H:i:s')]); ?>
            
            <?php if($model->isNewRecord ) {
                    echo $form->hiddenField($model,'created_at', ['value' =>  date('Y-m-d H:i:s')]); 
                    echo $form->hiddenField($model,'created_by', ['value' =>Yii::app()->user->id]); 
                }
            ?>

            <div class="row ls-space margin top-15">
                <hr />
            </div>
            <div class="row ls-space margin top-5">
                <button type="submit" class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Save')?></button>
                <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1" id="exitForm"><?=gT('Cancel')?></button>
            </div>
        <?php $this->endWidget(); ?>
        </div><!-- form -->
    </div>
</div>