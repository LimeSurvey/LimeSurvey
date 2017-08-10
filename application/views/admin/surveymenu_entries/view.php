<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs=array(
	'Menu entries'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List menu entries', 'url'=>array('index')),
	array('label'=>'Create menu entries', 'url'=>array('create')),
	array('label'=>'Update menu entries', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete menu entries', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage menu entries', 'url'=>array('admin')),
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
