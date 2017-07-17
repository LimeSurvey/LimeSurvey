<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */

$this->breadcrumbs=array(
	'Template Options'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List TemplateOptions', 'url'=>array('index')),
	array('label'=>'Create TemplateOptions', 'url'=>array('create')),
	array('label'=>'View TemplateOptions', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage TemplateOptions', 'url'=>array('admin')),
);
?>

<h1>Update TemplateOptions <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>