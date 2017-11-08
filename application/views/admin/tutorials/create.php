<?php
/* @var $this TutorialsController */
/* @var $model Tutorials */

$this->breadcrumbs=array(
	'Tutorials'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Tutorials', 'url'=>array('index')),
	array('label'=>'Manage Tutorials', 'url'=>array('admin')),
);
?>

<h1>Create Tutorials</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>