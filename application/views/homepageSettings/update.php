<?php
/* @var $this HomepageSettingsController*/
/* @var $model Boxes */
?>
<div class="col-lg-12 list-surveys" style="margin-top: 10px;">

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('_form', array(
                'model'=>$model,
                'icons_length'=>$model->icons_length,
                'icons'=>$model->icons,
                'action'=>'update',
            )); ?>
        </div>
    </div>

</div>
