<?php
/* @var $this TutorialsController */
/* @var $model Tutorial */

?>

<div class="container">
	<h1 class="pagetitle">Update Tutorial <?php echo $model->tid; ?></h1>
	<?php $this->renderPartial('/admin/tutorials/_form', array('model'=>$model)); ?>
</div>