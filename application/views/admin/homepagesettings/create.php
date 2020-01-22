<?php
/* @var $this AdminController */
/* @var $model Boxes */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createNewBox');
?>
<div class="col-lg-12 list-surveys">

    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'savebutton' => array('form' => 'boxes-form'),
            'saveandclosebutton' => array('form' => 'boxes-form'),
            'closebutton' => array('url' => Yii::app()->createUrl('admin/homepagesettings'))
        )
    )); ?>

    <h3><?php eT('New box');?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

            <?php $this->renderPartial('homepagesettings/_form', array(
                'model'=>$model,
                'icons_length'=>$model->icons_length,
                'icons'=>$model->icons,
                'action'=>'create',
            )); ?>

        </div>
    </div>

</div>
