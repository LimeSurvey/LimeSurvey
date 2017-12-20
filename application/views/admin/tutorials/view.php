<?php
/* @var $this TutorialsController */
/* @var $model Tutorials */

$this->breadcrumbs=array(
	'Tutorials'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Tutorials', 'url'=>array('index')),
	array('label'=>'Create Tutorials', 'url'=>array('create')),
	array('label'=>'Update Tutorials', 'url'=>array('update', 'id'=>$model->tid)),
	array('label'=>'Delete Tutorials', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->tid),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Tutorials', 'url'=>array('admin')),
);
?>

<h1>View Tutorials #<?php echo $model->tid; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'tid',
		'name',
		'description',
		'active',
		'permission',
		'permission_grade',
	),
)); ?>
