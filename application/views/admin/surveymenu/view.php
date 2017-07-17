<?php
/* @var $this SurveymenuController */
/* @var $model Surveymenu */

$this->breadcrumbs=array(
	'Surveymenus'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List Surveymenu', 'url'=>array('index')),
	array('label'=>'Create Surveymenu', 'url'=>array('create')),
	array('label'=>'Update Surveymenu', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Surveymenu', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Surveymenu', 'url'=>array('admin')),
);
?>

<h1>View Surveymenu #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'parent_id',
		'survey_id',
		'order',
		'level',
		'title',
		'description',
		'changed_at',
		'changed_by',
		'created_at',
		'created_by',
		'position',
	),
)); ?>
