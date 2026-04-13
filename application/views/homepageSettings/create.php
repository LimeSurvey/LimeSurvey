<?php
/* @var $this HomepageSettingsController */

/* @var $model Boxes */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createNewBox');
?>
<div class="col-12 list-surveys">
    <?php $this->renderPartial('_form',
        [
            'model' => $model,
            'icons_length' => $model->icons_length,
            'icons' => $model->icons,
            'action' => 'create',
        ]
    ); ?>
</div>
