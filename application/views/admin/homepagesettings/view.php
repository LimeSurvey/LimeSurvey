<?php
/* @var $this BoxesController */
/* @var $model Boxes */

?>

<h1>View Boxes #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
    'data'=>$model,
    'attributes'=>array(
        'id',
        'position',
        'url',
        'title',
        'ico',
        'desc',
        'page',
        'usergroup',
    ),
)); ?>
