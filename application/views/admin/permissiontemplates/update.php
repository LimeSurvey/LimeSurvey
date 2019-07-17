<?php
/* @var $this PermissiontemplatesController */
/* @var $model Permissiontemplates */

$this->breadcrumbs=array(
	'Permissiontemplates'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Permissiontemplates', 'url'=>array('index')),
	array('label'=>'Create Permissiontemplates', 'url'=>array('create')),
	array('label'=>'View Permissiontemplates', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Permissiontemplates', 'url'=>array('admin')),
);
?>

<h1>Update Permissiontemplates <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>