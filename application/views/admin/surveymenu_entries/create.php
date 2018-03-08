<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs = array(
    gT('Menu entries') => array('index'),
    gT('Create')
);

$this->menu = array(
    array('label'=>gT('List menu entries'), 'url'=>array('index')),
    array('label'=>gT('Manage menu entries'), 'url'=>array('admin')),
);
?>

<h1><?php eT('Create survey menu entries'); ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>
