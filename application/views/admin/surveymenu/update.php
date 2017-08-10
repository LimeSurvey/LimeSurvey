<?php
/* @var $this SurveymenuController */
/* @var $model Surveymenu */

$this->breadcrumbs=array(
	'Surveymenus'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Surveymenu', 'url'=>array('index')),
	array('label'=>'Create Surveymenu', 'url'=>array('create')),
	array('label'=>'View Surveymenu', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Surveymenu', 'url'=>array('admin')),
);
?>

<h1>Update Surveymenu <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>