<?php 
/**
 * This view displays the welcome message provided by the controller. 
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like all the further steps)
 * 
 * @var obj $serverAnswer the object returned by the server 
 */
?>

<?php $urlNew = Yii::app()->createUrl("admin/update", array("update"=>'newKey', 'destinationBuild' => $serverAnswer->destinationBuild)); ?>
<h3 class="maintitle"><?php eT($serverAnswer->title);?></h3>

<?php 
	if( isset($serverAnswer->html) )
		echo $serverAnswer->html;
?>

<!-- Welcome Message -->
<div class="row">
	<div style="border-right:1px solid #EEE" class="col-lg-6">
		<?php
			echo gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br /><br />';
			echo '<ul><li>'.gT('The following steps will be done by this update:').'</li>';
			echo '<li>'.gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>';
			echo '<li>'.gT('New files will be downloaded and installed.').'</li>';
			echo '<li>'.gT('If necessary the database will be updated.').'</li></ul>';
		?>
		

	</div>
	
	<!-- The key informations-->
	<div style="padding-left: 1em;" class="col-lg-6">
			<h4><?php eT('Update Key Informations'); ?></strong></h4>
			<strong><?php eT('Your update key is'); ?>: </strong><?php echo $serverAnswer->key_infos->keyid; ?><br/>
			<strong><?php eT('Your key is valid until'); ?> : </strong><?php echo $serverAnswer->key_infos->validuntil; ?><br/>
			<?php eT('It still has'); ?> <strong><?php echo  $serverAnswer->key_infos->remaining_updates; ?></strong> update<?php if($serverAnswer->key_infos->remaining_updates > 1 ){echo 's';}?> <br/>

	</div>
</div>
<div class="row">
	<div style="border-right:1px solid #EEE" class="col-lg-6">
			<!-- The form launching the first step : control local errors. -->
			<?php echo CHtml::beginForm('update/sa/checkLocalErrors', 'post', array('id'=>'launchCheckLocalErrorsForm')); ?>
				<?php  echo CHtml::hiddenField('destinationBuild' , $serverAnswer->destinationBuild); ?>
				<?php  echo CHtml::hiddenField('access_token' , $serverAnswer->access_token); ?>  

				<a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button">
					<?php eT("Cancel"); ?>
				</a>

				<button type="submit" class="btn btn-default ajax_button launch_update">
					<?php eT("Continue");?>
				</button>
								
			<?php echo CHtml::endForm(); ?>
	</div>
	<div style="padding-left: 1em;" class="col-lg-6">
		    <a class="btn btn-default" href="https://www.limesurvey.org/en/" role="button" aria-disabled="false" target="_blank">
		        <span class="ui-button-text"><?php eT("Renew this key"); ?></span>
		    </a>
		
		    <a class="btn btn-default" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
		        <span class="ui-button-text"><?php eT("Enter a new key"); ?></span>
		    </a>
	</div>
</div>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdater for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdater/comfortUpdateNextStep.js"></script>
<script>
	$('#launchCheckLocalErrorsForm').comfortUpdateNextStep({'step': 0});	
</script>
