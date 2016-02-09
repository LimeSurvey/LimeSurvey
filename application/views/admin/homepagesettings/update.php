<?php
/* @var $this BoxesController */
/* @var $model Boxes */

$this->breadcrumbs=array(
	'Boxes'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Boxes', 'url'=>array('index')),
	array('label'=>'Create Boxes', 'url'=>array('create')),
	array('label'=>'View Boxes', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Boxes', 'url'=>array('admin')),
);
?>

<h1>Update Boxes <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>