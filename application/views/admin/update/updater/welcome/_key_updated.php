<!-- First we show the welcome message -->
<?php
    // TODO : move to the controler
    $urlContinue = Yii::app()->createUrl("admin/globalsettings", array("update"=>'welcome', 'destinationBuild'=>$_POST["destinationBuild"]));
?>
<h2 class="maintitle"><?php eT("Key update");?></h2>
<?php
    if( isset($serverAnswer->html) )
        echo $serverAnswer->html;
?>
<div>
    <p><br><?php eT('Your key has been updated and validated! You can now use ComfortUpdate.'); ?></p>
</div>

<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlContinue;?>" role="button" aria-disabled="false">
    <span class="ui-button-text"><?php eT("Continue"); ?></span>
</a>


