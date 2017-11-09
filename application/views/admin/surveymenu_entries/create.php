<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs=array(
	'Menu entries'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List meny entries', 'url'=>array('index')),
	array('label'=>'Manage menu entries', 'url'=>array('admin')),
);
?>

<h1>Create SurveymenuEntries</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>