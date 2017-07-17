<?php
/* @var $this SurveymenuEntriesController */
/* @var $data SurveymenuEntries */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_id')); ?>:</b>
	<?php echo CHtml::encode($data->menu_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('order')); ?>:</b>
	<?php echo CHtml::encode($data->order); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('title')); ?>:</b>
	<?php echo CHtml::encode($data->title); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_title')); ?>:</b>
	<?php echo CHtml::encode($data->menu_title); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_description')); ?>:</b>
	<?php echo CHtml::encode($data->menu_description); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_icon')); ?>:</b>
	<?php echo CHtml::encode($data->menu_icon); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_icon_type')); ?>:</b>
	<?php echo CHtml::encode($data->menu_icon_type); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_class')); ?>:</b>
	<?php echo CHtml::encode($data->menu_class); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('menu_link')); ?>:</b>
	<?php echo CHtml::encode($data->menu_link); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('action')); ?>:</b>
	<?php echo CHtml::encode($data->action); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('template')); ?>:</b>
	<?php echo CHtml::encode($data->template); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('partial')); ?>:</b>
	<?php echo CHtml::encode($data->partial); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('classes')); ?>:</b>
	<?php echo CHtml::encode($data->classes); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('permission')); ?>:</b>
	<?php echo CHtml::encode($data->permission); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('permission_grade')); ?>:</b>
	<?php echo CHtml::encode($data->permission_grade); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('data')); ?>:</b>
	<?php echo CHtml::encode($data->data); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('getdatamethod')); ?>:</b>
	<?php echo CHtml::encode($data->getdatamethod); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('language')); ?>:</b>
	<?php echo CHtml::encode($data->language); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('changed_at')); ?>:</b>
	<?php echo CHtml::encode($data->changed_at); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('changed_by')); ?>:</b>
	<?php echo CHtml::encode($data->changed_by); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('created_at')); ?>:</b>
	<?php echo CHtml::encode($data->created_at); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('created_by')); ?>:</b>
	<?php echo CHtml::encode($data->created_by); ?>
	<br />

	*/ ?>

</div>