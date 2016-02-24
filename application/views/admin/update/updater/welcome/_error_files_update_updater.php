<?php
/**
 * This view displays the Step 1 : pre-installation checks.
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 *
 * @var object $localChecks an object containing all the checks results
 * @var int $destinationBuild the destination build
 */
?>

<h2 class="text-danger"><?php eT("Write error!"); ?></h2>
<?php
	if( isset($serverAnswer->html) )
		echo $serverAnswer->html;
?>
<p>
	<strong><?php eT("Those files/directories are not writable:")?></strong>
	<br/>
	<?php foreach( $localChecks->readOnly as $readonly ):?>
		<?php echo $readonly."<br/>";?>
	<?php endforeach; ?>
	<br/>

	<a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
		<?php eT("Cancel"); ?>
	</a>
</p>

