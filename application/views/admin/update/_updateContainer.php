<?php
/**
 * This view generate all the structure needed for the comfort updater.
 * If no step is requested (by url or by post), ajax will render the check buttons, else, it will show the comfort updater (menus, etc.)
 * 
 * @var int $thisupdatecheckperiod  : the current check period in days (0 => never ; 1 => everyday ; 7 => every week, etc..  )
 * @var $updatelastcheck TODO : check type 
 * @var $UpdateNotificationForBranch TODO : check type
 * 
 */
?>

<!-- this view contain the input provinding to the js the inforamtion about wich content to load : check buttons or comfortUpdater -->
<?php

    App()->getClientScript()->registerScriptFile(Yii::app()->baseUrl.'/scripts/admin/comfortupdater/comfortupdater.js');
	App()->getClientScript()->registerScriptFile(Yii::app()->baseUrl.'/scripts/admin/comfortupdater/buildComfortButtons.js');
	App()->getClientScript()->registerScriptFile(Yii::app()->baseUrl.'/scripts/admin/comfortupdater/displayComfortStep.js'); 
	$this->renderPartial("./update/_ajaxVariables"); 
?>

<div class="col-lg-12 list-surveys">
	<h3>
		<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/big/fff/shield-update.png" style="height : 1em; margin-right : 0.5em;"/>
		<?php eT('ComfortUpdate'); ?>
	</h3>

	<div class="row">
		<div class="col-lg-12 content-right">
            <div id="updaterWrap">
            	<div id="preUpdaterContainer">
            	<!-- The check buttons : render by ajax only if no step is required by url or post -->
            	<?php // $this->renderPartial("./update/check_updates/_checkButtons", array( "thisupdatecheckperiod"=>$thisupdatecheckperiod, "updatelastcheck"=>$updatelastcheck,"UpdateNotificationForBranch"=>$UpdateNotificationForBranch )); ?>
            	<?php 	
            		if( $serverAnswer->result )
            		{
            			unset($serverAnswer->result);
            			$this->renderPartial('./update/check_updates/update_buttons/_updatesavailable', array('updateInfos' => $serverAnswer));
            		}
            		else 
            		{
            			// Error : we build the error title and messages 
            			$this->renderPartial('./update/check_updates/update_buttons/_updatesavailable_error', array('serverAnswer' => $serverAnswer));			
            		}
            	?>
            	</div>
            	
            	<!-- The updater  -->
            	<?php $this->renderPartial("./update/updater/_updater"); ?> 
            </div>
		</div>
	</div>
</div>

<?php
/**
 * TODO : move to CSS file, with all the inline styles
 */
?>

<style media="screen" type="text/css">
h3.maintitle{
	background-color : transparent;
	color: #328637;
	border-bottom : 1px solid #328637;
}


.on {
   font-weight: bold;
   font-size: 1em;
   padding-left : 0.5em;
   padding-top : 0.5em;
}

.on span{
	display : block;
	background-color :#328637;
   	color : #fff;
	padding : 0.1em;
	padding-left : 0.5em;
	width: 40%;
}

.off {
	font-size: 0.9em;
	padding-left : 0.5em;
	padding-top : 0.5em;
}
</style>