<?php
/* @var $this TutorialEntryController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Tutorial Entries',
);

$this->menu=array(
	array('label'=>'Create TutorialEntry', 'url'=>array('create')),
	array('label'=>'Manage TutorialEntry', 'url'=>array('admin')),
);
?>

<h1>Tutorial Entries</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
