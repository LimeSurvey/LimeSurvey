<?php 
/**
 * This view displays the Step 1 : pre-installation checks. 
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 * 
 * @var object $localChecks an object containing all the checks results
 * @var int $destinationBuild the destination build 
 */
?>

<?php 
		$urlNew = Yii::app()->createUrl("admin/update", array("update"=>'checkLocalErrors', 'destinationBuild' => $destinationBuild, 'access_token' => $access_token));
		$errors = FALSE; 
		//var_dump($localChecks); die();
?>

<h3 class="maintitle"><?php eT('Checking basic requirements...'); ?></h3>

<?php 
	if( isset($localChecks->html) )
		echo $localChecks->html;
?>


<?php // foreach($localChecks as $check):?>
	
<?php foreach($localChecks->files as $file):?>
	<div class="row" style="margin-bottom : 1em;  ">
		<div class="col-lg-12">
			<strong><?php echo $file->name;?> :</strong>
			
			<div class="row">
				<?php if($file->writable !== 'pass'): ?>
						<div class="col-lg-6"><?php eT('is writable'); ?> :</div>  
						<?php if($file->writable): ?>
								<div class="col-lg-6 text-right text-success"><?php eT('Ok');?></div>
						<?php else: ?>
								<div class="col-lg-6 text-right text-warning"><?php eT('is not writable'); ?> !</div>
								<?php $errors = TRUE; ?>
						<?php endif;?>
				<?php endif;?>
				<?php if($file->freespace !== 'pass'): ?>
					<div class="row">
						<div class="col-lg-6"><?php eT('has enough space');?> :</div>
						<?php if($file->freespace): ?>
							<div class="col-lg-6 text-right text-success"><?php eT('Ok');?></div>
						<?php else: ?>
							<div class="col-lg-6 text-right text-warning"><?php eT('not enough space'); ?> !</div>
							<?php $errors = TRUE; ?>
						<?php endif;?>
					</div>			
				<?php endif;?>
			</div>
			
		</div>		
	</div>
<?php endforeach; ?>


	<div class="row" style="margin-bottom : 1em;   ">
		<div class="col-lg-12">
			<strong>PHP <?php echo $localChecks->php->php_ver;?> :</strong>
			<div class="row">
				<div class="col-lg-6"><?php eT('required');?> :</div>  
				<?php if($localChecks->php->result):?>
					<div class="col-lg-6 text-right text-success"><?php eT('OK'); ?></div>
				<?php else:?>
					<div class="col-lg-6 text-right text-warning"><?php eT('your PHP version is only');?> <?php echo $localChecks->php->local_php_ver;?></div>
					<?php $errors = TRUE; ?>
				<?php endif;?>
			</div>
		</div>

	</div>

<?php foreach($localChecks->php_modules as $name => $module):?>
	<div class="row" style="margin-bottom : 1em;   ">
		<div class="col-lg-12">
			<strong><?php echo $name;?> :</strong>
				
	
	
			<div class="row">
					<div class="row">
						<div class="col-lg-6"><?php if(isset($module->optional)){ eT('optional');}else{eT('required');}?> :</div>
						<?php if($module->installed): ?>
								<div class="col-lg-6 text-right text-success"><?php eT('is installed'); ?></div>
						<?php else: ?>
							<?php if(isset($module->required)): ?>
								<div class="col-lg-6 text-right text-warning"><?php eT('is not installed'); ?> !</div>
								<?php $errors = TRUE; ?>
							<?php elseif(isset($module->optional)): ?>
								<div class="col-lg-6 text-right text-warning"><?php eT('is not installed (but optional)'); ?></div>
							<?php endif;?>						
						<?php endif;?>
					</div>
			</div>
		</div>
	</div>
<?php endforeach; ?>



<?php if($errors): ?>
<p>
	<strong><?php eT('When checking your installation we found one or more problems. Please check for any error messages above and fix these before you can proceed.'); ?></strong>
	<?php // TODO : a new step request by url... ?>
</p>
<p>
	<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
		<span class="ui-button-text"><?php eT("Cancel"); ?></span>
	</a>	
	<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
	    <span class="ui-button-text"><?php eT('Check again');?></span>
	</a>
</p>



<?php else:?>
<p>
	<span class="text-success"><?php echo gT('Everything looks alright. Please proceed to the next step.');?></span>

	<?php 
		$formUrl = Yii::app()->getController()->createUrl("admin/update/sa/changeLog/");
		echo CHtml::beginForm($formUrl, 'post', array("id"=>"launchChangeLogForm"));
		echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
		echo CHtml::hiddenField('access_token' , $access_token);
	?>
		<a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
			<span class="ui-button-text"><?php eT("Cancel"); ?></span>
		</a>		 


		<button type="submit" class="btn btn-default ajax_button launch_update" id='step1launch'>
			<?php echo sprintf(gT('Proceed to step %s'),'1');?>
		</button>		 
	
	<?php 
		echo CHtml::endForm(); 
	?> 		
</p>

<?php endif;?>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdater for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdater/comfortUpdateNextStep.js"></script>
<script>
$('#launchChangeLogForm').comfortUpdateNextStep({'step': 1});	
</script>