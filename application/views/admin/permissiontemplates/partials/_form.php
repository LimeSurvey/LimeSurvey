<?php
/* @var $this PermissiontemplatesController */
/* @var $model Permissiontemplates */
/* @var $form CActiveForm */


if ($model->isNewRecord) {
    $modalTitle =  gT('Create permission role');
} else {
    $modalTitle = sprintf(gT('Update permission role %s'), $model->name);
}
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => $modalTitle]
);
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'RoleControl--modalform',
    'action' => App()->createUrl('admin/roles/sa/applyEdit'),
    'enableAjaxValidation'=>false,
)); ?>
<div class="modal-body">
    <div class="container-center">

            <div class="row ls-space margin top-5 bottom-5 hidden" id="RoleControl--errors">
            </div>
            <?php echo $form->hiddenField($model,'ptid'); ?>

            <div class="row">
                <?php echo $form->labelEx($model,'New User Role'); ?>
                <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>192, 'required' => 'required')); ?>
                <?php echo $form->error($model,'name'); ?>
            </div>

            <div class="row">
                <?php echo $form->labelEx($model,'description'); ?>
                <?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50, 'required' => 'required')); ?>
                <?php echo $form->error($model,'description'); ?>
            </div>

            <?php echo $form->hiddenField($model,'renewed_last', ['value' => date('Y-m-d H:i:s')]); ?>
            
            <?php if ($model->isNewRecord ) {
                    echo $form->hiddenField($model,'created_at', ['value' =>  date('Y-m-d H:i:s')]);
                    echo $form->hiddenField($model,'created_by', ['value' =>Yii::app()->user->id]);
                }
            ?>
    </div>
</div>
<div class="modal-footer modal-footer-buttons" style="margin-top: 15px; ">
    <button class="btn btn-error " id="exitForm"><?=gT('Cancel')?></button>
    <button type="submit" class="btn btn-success " id="submitForm"><?=gT('Save')?></button>
</div>
<?php $this->endWidget(); ?>
