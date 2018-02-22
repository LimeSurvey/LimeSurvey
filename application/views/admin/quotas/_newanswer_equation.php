<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
/* @var Question $oQuestion */
?>

<div class='row'>
    <h2><?php echo sprintf(gT("New answer for quota '%s'"), $oQuota->name);?></h2>
    <p class="lead"><?php eT("Set equation value");?></p>
    <div class='form-group'>
        <div class='col-sm-5 col-sm-offset-4'>
            <input type="text" class='form-control' name="quota_anscode" />
        </div>
    </div>
</div>

