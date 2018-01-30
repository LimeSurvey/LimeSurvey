<?php
/* @var $this TutorialsController */
/* @var $data Tutorial */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('tid')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->tid), array('view', 'id'=>$data->tid)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('active')); ?>:</b>
	<?php echo CHtml::encode($data->active); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('permission')); ?>:</b>
	<?php echo CHtml::encode($data->permission); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('permission_grade')); ?>:</b>
	<?php echo CHtml::encode($data->permission_grade); ?>
	<br />


</div>