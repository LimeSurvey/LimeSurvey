<?php
/* @var $this BoxesController */
/* @var $model Boxes */

$this->breadcrumbs=array(
	'Boxes'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Boxes', 'url'=>array('index')),
	array('label'=>'Manage Boxes', 'url'=>array('admin')),
);
?>

<h1>Create Boxes</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>