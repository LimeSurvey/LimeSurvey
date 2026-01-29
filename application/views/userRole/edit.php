<?php
/* @var $this PermissiontemplatesController */
/* @var $model Permissiontemplates */
?>

<div class="ls-flex-column ls-space padding left-35 right-35">
    <div class="col-12 h1 pagetitle">
        <?php if($model->isNewRecord) {
            echo gT('Create permission role');
        } else {
            echo sprintf(gT('Update permission role %s'), CHtml::encode($model->name));
        }?>
    </div>
    <div class="col-12 ls-space margin top-15">
        <?php $this->renderPartial('permissiontemplates/partials/_form', array('model'=>$model)); ?>
    </div>
</div>