<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
/* @var Question $oQuestion */
?>

<div class='row'>
    <h2><?php echo sprintf(gT("New answer for quota '%s'"), CHtml::encode($oQuota->name));?></h2>
    <p class="lead"><?php eT("Set equation value");?></p>
    <div class='mb-3'>
        <div class='col-md-5 offset-md-4'>
            <input type="text" class='form-control' name="quota_anscode" />
        </div>
    </div>
</div>

