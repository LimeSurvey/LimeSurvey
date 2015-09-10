<?php
/**
 * This view render the Update Notfication displayed on top of the LimeSurvey admin interface.
 * It is called from Survey_Common action which launch AdminController::_getUpdateNotification();
 * 
 * @var $security_update_available
 */

?>
<?php $urlUpdate = Yii::app()->createUrl("admin/update"); ?>

<div class="col-lg-12 content-right" id="update-container">

<?php if($security_update_available):?>
<div class="alert alert-warning alert-dismissible" role="alert" id="alert-security-update">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #84BD00;"><span aria-hidden="true">&times;</span></button>
		<strong>Security Update !</strong> a security update is available. <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>, <?php eT('or update manually'); ?>
</div>
<?php else:?>
<div class="alert alert-info alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<strong><?php eT("a new update is available");?> </strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>, <?php eT('or update manually'); ?>
</div>
<?php endif;?>

</div>

<script>
$(document).ready(function(){	
		if($('#absolute_notification').length){
			$('#update-container').css({
				position: 'absolute', 
				top : 60
			});		
		}
		
		if($('.side-body').length){
			//$('#update-container').removeClass();
			
			$('#update-container .alert').attr('style', 'margin-top: 20px');			
			$('#update-container .alert').prependTo('.side-body');
		}		
});
</script>