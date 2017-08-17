<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
/* @var Question $oQuestion */
?>

<div class='row'>
    <h2><?php echo sprintf(gT("New answer for quota '%s'"), $oQuota->name);?></h2>
    <p class="lead"><?php eT("Select answer:");?></p>
    <div class='form-group'>
        <div class='col-sm-5 col-sm-offset-4'>
            <textarea class='form-control' name="quota_anscode" rows='5' ></textarea>
        </div>
    </div>
    <div class='form-group'>
        <div class='col-sm-5 col-sm-offset-4'>
            <input class="btn btn btn-success" name="submit" type="submit" class="submit btn btn-default" value="<?php eT("Next");?>" />
        </div>
    </div>
    <div class='form-group'>
        <?php eT("Save this, then create another:");?>
        <input type="checkbox" name="createanother">
    </div>
</div>

