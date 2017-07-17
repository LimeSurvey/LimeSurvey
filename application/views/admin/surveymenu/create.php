<?php
/* @var $this SurveymenuController */
/* @var $model Surveymenu */

$this->breadcrumbs=array(
	'Surveymenus'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Surveymenu', 'url'=>array('index')),
	array('label'=>'Manage Surveymenu', 'url'=>array('admin')),
);
?>

<h1>Create Surveymenu</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>