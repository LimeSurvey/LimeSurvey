<?php
/* @var $this TutorialsController */
/* @var $model Tutorial */

$this->breadcrumbs=array(
	'Tutorial'=>array('index'),
	$model->name=>array('view','id'=>$model->tid),
	'Update',
);

$this->menu=array(
	array('label'=>'List Tutorial', 'url'=>array('index')),
	array('label'=>'Create Tutorial', 'url'=>array('create')),
	array('label'=>'View Tutorial', 'url'=>array('view', 'id'=>$model->tid)),
	array('label'=>'Manage Tutorial', 'url'=>array('admin')),
);
?>

<h1>Update Tutorial <?php echo $model->tid; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>