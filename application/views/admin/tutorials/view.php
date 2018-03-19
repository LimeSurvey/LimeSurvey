<?php
/* @var $this TutorialsController */
/* @var $model Tutorial */

$this->breadcrumbs=array(
	'Tutorial'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Tutorial', 'url'=>array('index')),
	array('label'=>'Create Tutorial', 'url'=>array('create')),
	array('label'=>'Update Tutorial', 'url'=>array('update', 'id'=>$model->tid)),
	array('label'=>'Delete Tutorial', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->tid),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Tutorial', 'url'=>array('admin')),
);
?>

<h1>View Tutorial #<?php echo $model->tid; ?></h1>

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
