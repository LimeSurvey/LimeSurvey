<?php
/* @var $this TutorialsController */
/* @var $model Tutorials */

$this->breadcrumbs=array(
	'Tutorials'=>array('index'),
	$model->name=>array('view','id'=>$model->tid),
	'Update',
);

$this->menu=array(
	array('label'=>'List Tutorials', 'url'=>array('index')),
	array('label'=>'Create Tutorials', 'url'=>array('create')),
	array('label'=>'View Tutorials', 'url'=>array('view', 'id'=>$model->tid)),
	array('label'=>'Manage Tutorials', 'url'=>array('admin')),
);
?>

<h1>Update Tutorials <?php echo $model->tid; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>