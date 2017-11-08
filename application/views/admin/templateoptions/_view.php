<?php
/* @var $this TemplateOptionsController */
/* @var $data TemplateOptions */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('template_name')); ?>:</b>
	<?php echo CHtml::encode($data->template_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('sid')); ?>:</b>
	<?php echo CHtml::encode($data->sid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('gsid')); ?>:</b>
	<?php echo CHtml::encode($data->gsid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('uid')); ?>:</b>
	<?php echo CHtml::encode($data->uid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('files_css')); ?>:</b>
	<?php echo CHtml::encode($data->files_css); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('files_js')); ?>:</b>
	<?php echo CHtml::encode($data->files_js); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('files_print_css')); ?>:</b>
	<?php echo CHtml::encode($data->files_print_css); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('options')); ?>:</b>
	<?php echo CHtml::encode($data->options); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('cssframework_name')); ?>:</b>
	<?php echo CHtml::encode($data->cssframework_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('cssframework_css')); ?>:</b>
	<?php echo CHtml::encode($data->cssframework_css); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('cssframework_js')); ?>:</b>
	<?php echo CHtml::encode($data->cssframework_js); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('packages_to_load')); ?>:</b>
	<?php echo CHtml::encode($data->packages_to_load); ?>
	<br />

	*/ ?>

</div>
