<?php
/* @var $this HomepageSettingsController */
/* @var $model Boxes */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createNewBox');
?>
<div class="col-lg-12 list-surveys">

    <div class="row">
        <div class="col-lg-12 content-right">

            <?php $this->renderPartial('_form', array(
                'model'=>$model,
                'icons_length'=>$model->icons_length,
                'icons'=>$model->icons,
                'action'=>'create',
            )); ?>

        </div>
    </div>

</div>
