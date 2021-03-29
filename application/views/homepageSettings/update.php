<?php
/* @var $this HomepageSettingsController*/
/* @var $model Boxes */
?>
<div class="col-lg-12 list-surveys">

    <?php $this->renderPartial('//admin/super/fullpagebar_view', array(
        'fullpagebar' => array(
            'savebutton' => array('form' => 'boxes-form'),
            'saveandclosebutton' => array('form' => 'boxes-form'),
            'closebutton' => array('url' => Yii::app()->createUrl('homepageSettings/index'))
        )
    )); ?>

    <h3><?php printf('Update box %s',"<em>".htmlspecialchars($model->title)."</em>");?> </h3>

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
