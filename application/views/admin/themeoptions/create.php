<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */

$this->breadcrumbs=array(
	'Template Options'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List TemplateOptions', 'url'=>array('index')),
	array('label'=>'Manage TemplateOptions', 'url'=>array('admin')),
);
?>

<h1>Create TemplateOptions</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>