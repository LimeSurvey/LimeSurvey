<?php
/* @var $this TutorialEntryController */
/* @var $data TutorialEntry */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('teid')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->teid), array('view', 'id'=>$data->teid)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('tid')); ?>:</b>
	<?php echo CHtml::encode($data->tid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('title')); ?>:</b>
	<?php echo CHtml::encode($data->title); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('content')); ?>:</b>
	<?php echo CHtml::encode($data->content); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('settings')); ?>:</b>
	<?php echo CHtml::encode($data->settings); ?>
	<br />


</div>