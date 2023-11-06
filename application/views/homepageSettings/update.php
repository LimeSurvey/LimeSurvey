<?php
/* @var HomepageSettingsController $this */
/* @var Box $model */
?>
<div class="col-lg-12 list-surveys">

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('_form', array(
                'model' => $model,
                'icons_length' => $model->icons_length,
                'icons' => $model->icons,
                'action' => 'update',
            )); ?>
        </div>
    </div>

</div>
