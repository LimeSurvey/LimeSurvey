<?php
/* @var $this TutorialsController */
/* @var $model Tutorial */

$this->breadcrumbs=array(
	'Tutorial'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Tutorial', 'url'=>array('index')),
	array('label'=>'Manage Tutorial', 'url'=>array('admin')),
);
?>

<h1>Create Tutorial</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>