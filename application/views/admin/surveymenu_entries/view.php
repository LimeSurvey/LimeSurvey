<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs=array(
	'Surveymenu Entries'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List SurveymenuEntries', 'url'=>array('index')),
	array('label'=>'Create SurveymenuEntries', 'url'=>array('create')),
	array('label'=>'Update SurveymenuEntries', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete SurveymenuEntries', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage SurveymenuEntries', 'url'=>array('admin')),
);
?>

<h1>View SurveymenuEntries #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'menu_id',
		'order',
		'name',
		'title',
		'menu_title',
		'menu_description',
		'menu_icon',
		'menu_icon_type',
		'menu_class',
		'menu_link',
		'action',
		'template',
		'partial',
		'classes',
		'permission',
		'permission_grade',
		'data',
		'getdatamethod',
		'language',
		'changed_at',
		'changed_by',
		'created_at',
		'created_by',
	),
)); ?>
