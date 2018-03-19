<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs=array(
	'Menu entries'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List SurveymenuEntries', 'url'=>array('index')),
	array('label'=>'Create SurveymenuEntries', 'url'=>array('create')),
	array('label'=>'View SurveymenuEntries', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage SurveymenuEntries', 'url'=>array('admin')),
);
?>

<h1>Update SurveymenuEntries <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>