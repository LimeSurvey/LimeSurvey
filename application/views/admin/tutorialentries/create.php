<?php
/* @var $this TutorialEntryController */
/* @var $model TutorialEntry */

$this->breadcrumbs=array(
	'Tutorial Entries'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List TutorialEntry', 'url'=>array('index')),
	array('label'=>'Manage TutorialEntry', 'url'=>array('admin')),
);
?>

<h1>Create TutorialEntry</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>