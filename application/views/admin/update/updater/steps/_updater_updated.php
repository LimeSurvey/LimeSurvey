<?php
/**
 * This file display the Updater Update Message
 */
?>
<?php
    $urlContinue = Yii::app()->createUrl("admin/globalsettings", array("update"=>'welcome', 'destinationBuild'=>$destinationBuild));
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

<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlContinue;?>" role="button" aria-disabled="false">
    <span class="ui-button-text"><?php eT("Continue"); ?></span>
</a>
