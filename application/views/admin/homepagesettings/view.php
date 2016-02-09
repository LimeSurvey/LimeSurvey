<?php
/* @var $this BoxesController */
/* @var $model Boxes */

$this->breadcrumbs=array(
	'Boxes'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List Boxes', 'url'=>array('index')),
	array('label'=>'Create Boxes', 'url'=>array('create')),
	array('label'=>'Update Boxes', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Boxes', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Boxes', 'url'=>array('admin')),
);
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
