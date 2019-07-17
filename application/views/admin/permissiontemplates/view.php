<?php
/* @var $this PermissiontemplatesController */
/* @var $model Permissiontemplates */

$this->breadcrumbs=array(
	'Permissiontemplates'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Permissiontemplates', 'url'=>array('index')),
	array('label'=>'Create Permissiontemplates', 'url'=>array('create')),
	array('label'=>'Update Permissiontemplates', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Permissiontemplates', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Permissiontemplates', 'url'=>array('admin')),
);
?>

<h1>View Permissiontemplates #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'description',
		'renewed_last',
		'created_at',
		'created_by',
	),
)); ?>
