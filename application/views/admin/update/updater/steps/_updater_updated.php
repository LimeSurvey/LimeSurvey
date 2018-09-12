<?php
/**
 * This file display the Updater Update Message
 */
?>
<?php
    $urlContinue = Yii::app()->createUrl("admin/update", array("update"=>'welcome', 'destinationBuild'=>$destinationBuild));
?>
<h2 class="maintitle"><?php eT("ComfortUpdate needs to be updated");?></h2>
<div class="updater-background">
    <p class="success">
        <?php eT("ComfortUpdate has been updated!");?>
    </p>
    <p>
        <?php eT("You can now continue updating your LimeSurvey Installation.");?>
    </p>
</div>

<a id="backToMainMenu" class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/authentication/sa/logout"); ?>" role="button" aria-disabled="false">
    <?php eT('Click this button to log out.'); ?>
</a>
