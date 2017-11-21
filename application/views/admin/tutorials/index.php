<?php
/* @var $this TutorialsController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
    'Tutorials',
);

$this->menu=array(
    array('label'=>'Create Tutorials', 'url'=>array('create')),
    array('label'=>'Manage Tutorials', 'url'=>array('admin')),
);
?>

<h1>Tutorials</h1>

<?php $this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_view',
)); ?>
