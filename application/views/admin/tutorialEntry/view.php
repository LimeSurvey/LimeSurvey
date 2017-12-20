<?php
/* @var $this TutorialEntryController */
/* @var $model TutorialEntry */

$this->breadcrumbs=array(
	'Tutorial Entries'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List TutorialEntry', 'url'=>array('index')),
	array('label'=>'Create TutorialEntry', 'url'=>array('create')),
	array('label'=>'Update TutorialEntry', 'url'=>array('update', 'id'=>$model->teid)),
	array('label'=>'Delete TutorialEntry', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->teid),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage TutorialEntry', 'url'=>array('admin')),
);
?>

<h1>View TutorialEntry #<?php echo $model->teid; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'teid',
		'tid',
		'title',
		'content',
		'settings',
	),
)); ?>
