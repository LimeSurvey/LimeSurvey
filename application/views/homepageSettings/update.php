<?php
/* @var HomepageSettingsController $this */

/* @var Box $model */
?>
<div class="col-12 list-surveys">
    <?php $this->renderPartial('_form',
        array(
            'model' => $model,
            'icons_length' => $model->icons_length,
            'icons' => $model->icons,
            'action' => 'update',
        )
    ); ?>

</div>
