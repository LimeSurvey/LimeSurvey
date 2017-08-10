<?php
/* @var $this TemplateOptionsController */
/* @var $model TemplateOptions */

$this->breadcrumbs=array(
	'Template Options'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List TemplateOptions', 'url'=>array('index')),
	array('label'=>'Create TemplateOptions', 'url'=>array('create')),
	array('label'=>'Update TemplateOptions', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete TemplateOptions', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage TemplateOptions', 'url'=>array('admin')),
);
?>

<h1>View TemplateOptions #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'templates_name',
		'sid',
		'gsid',
		'uid',
		'files_css',
		'files_js',
		'files_print_css',
		'options',
		'cssframework_name',
		'cssframework_css',
		'cssframework_js',
		'packages_to_load',
	),
)); ?>
