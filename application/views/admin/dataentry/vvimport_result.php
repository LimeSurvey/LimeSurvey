<div class='messagebox'>
	<div class='header <?php echo $class;?>'>
		<?php echo $title;?>
	</div>
	<dl>
	<?php if(isset($aResult['success']) && is_array($aResult['success'])) {?>
		<dt class='success'><?php $clang->eT("Success"); ?></dt>
		<?php foreach($aResult['success'] as $sSucces) { ?>
			<dd><?php echo $sSucces ?></dd>
		<?php }?>
	<?php } ?>
	<?php if(isset($aResult['errors']) && is_array($aResult['errors'])) {?>
		<dt class='error'><?php $clang->eT("Error"); ?></dt>
		<?php foreach($aResult['errors'] as $sError) { ?>
			<dd><?php echo $sError ?></dd>
		<?php }?>
	<?php } ?>
	<?php if(isset($aResult['warnings']) && is_array($aResult['warnings'])) {?>
		<dt class='warning'><?php $clang->eT("Warning"); ?></dt>
		<?php foreach($aResult['warnings'] as $sWarning) { ?>
			<dd><?php echo $sWarning ?></dd>
		<?php }?>
	<?php } ?>
	</dl>
	<?php //echo $message;?>
	<?php if(isset($aUrls) && count($aUrls)) {?>
		<?php foreach($aUrls as $url){ ?>
			<a class='limebutton submit' href='<?php echo $url['link'] ?>'><?php echo $url['text'] ?></a>
		<?php } ?>
	<?php }else{ ?>
			<a class='limebutton submit' href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$iSurveyId"); ?>'><?php $clang->eT("Browse responses") ?></a>
	<?php } ?>
</div>
