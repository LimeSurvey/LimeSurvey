<?php
/* @var $this SurveymenuEntriesController */
/* @var $model SurveymenuEntries */

$this->breadcrumbs=array(
	'Menu entries'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List menu entries', 'url'=>array('index')),
	array('label'=>'Create menu entries', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#surveymenu-entries-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage menu entries</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'surveymenu-entries-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'menu_id',
		'order',
		'name',
		'title',
		'menu_title',
		/*
		'menu_description',
		'menu_icon',
		'menu_icon_type',
		'menu_class',
		'menu_link',
		'action',
		'template',
		'partial',
		'classes',
		'permission',
		'permission_grade',
		'data',
		'getdatamethod',
		'language',
		'changed_at',
		'changed_by',
		'created_at',
		'created_by',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
