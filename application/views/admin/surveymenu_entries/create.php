<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs=array(
	'Surveymenu Entries'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List SurveymenuEntries', 'url'=>array('index')),
	array('label'=>'Manage SurveymenuEntries', 'url'=>array('admin')),
);
?>

<h1>Create SurveymenuEntries</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>