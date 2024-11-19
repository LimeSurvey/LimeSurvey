<!-- First we show the welcome message -->
<?php
	// TODO : move to the controler
	$urlContinue = Yii::app()->createUrl("admin/update", array("update"=>'welcome', 'destinationBuild'=>$_POST["destinationBuild"]));
?>
<h3 class="maintitle"><?php eT("Key update");?></h3>
<?php 
	if( isset($serverAnswer->html) )
		echo $serverAnswer->html;
?>
<div>
    <p><br><?php eT('Your key has been updated and validated! You can now use ComfortUpdate.'); ?></p>
</div>

<a class="btn btn-outline-secondary" href="<?php echo $urlContinue;?>" role="button" aria-disabled="false">
	<?php eT("Continue"); ?>
</a>


