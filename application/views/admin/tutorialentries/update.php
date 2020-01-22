<?php
/* @var $this TutorialEntryController */
/* @var $model TutorialEntry */

$this->breadcrumbs=array(
	'Tutorial Entries'=>array('index'),
	$model->title=>array('view','id'=>$model->teid),
	'Update',
);

$this->menu=array(
	array('label'=>'List TutorialEntry', 'url'=>array('index')),
	array('label'=>'Create TutorialEntry', 'url'=>array('create')),
	array('label'=>'View TutorialEntry', 'url'=>array('view', 'id'=>$model->teid)),
	array('label'=>'Manage TutorialEntry', 'url'=>array('admin')),
);
?>

<h1>Update TutorialEntry <?php echo $model->teid; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>