<?php
    App()->getClientScript()->registerPackage('jquery-selectboxes');
    App()->getClientScript()->registerScriptFile(Yii::app()->baseUrl.'/scripts/admin/comfortupdate/comfortupdate.js');
	App()->getClientScript()->registerScriptFile(Yii::app()->baseUrl.'/scripts/admin/comfortupdate/buildComfortButtons.js');
	App()->getClientScript()->registerScriptFile(Yii::app()->baseUrl.'/scripts/admin/comfortupdate/displayComfortStep.js');
?>
<script type="text/javascript">
    var msgAtLeastOneLanguageNeeded = '<?php eT("You must set at last one available language.",'js'); ?>';
</script>


<h3><?php eT("Global settings"); ?></h3>

<ul class="nav nav-tabs" id="settingTabs">
        <li role="presentation" class="active"><a data-toggle="tab" href='#overview'><?php eT("Overview"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#general'><?php eT("General"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#email'><?php eT("Email settings"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#bounce'><?php eT("Bounce settings"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#security'><?php eT("Security"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#presentation'><?php eT("Presentation"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#language'><?php eT("Language"); ?></a></li>
        <li role="presentation" ><a data-toggle="tab" href='#interfaces'><?php eT("Interfaces"); ?></a></li> 
        <li role="presentation" id="updatetab" ><a data-toggle="tab" href='#updates' id="update_tab">ComfortUpdate</a></li>
</ul>
																					         
<?php echo CHtml::form(array("admin/globalsettings"), 'post', array('class'=>'form30','id'=>'frmglobalsettings','name'=>'frmglobalsettings'));?>
<div class="tab-content">
	<div id="overview" class="tab-pane  in active">
			<?php $this->renderPartial("./global_settings/_overview", array( 'usercount'=>$usercount, 'surveycount'=>$surveycount ,'activesurveycount'=>$activesurveycount ,'deactivatedsurveys'=>$deactivatedsurveys ,'activetokens'=>$activetokens ,'deactivatedtokens'=>$deactivatedtokens)); ?>
	</div>
	
	<div id="general" class="tab-pane ">
			<?php $this->renderPartial("./global_settings/_general"); ?>
	</div>
	
	<div id="email" class="tab-pane ">
		<?php $this->renderPartial("./global_settings/_email"); ?>
	</div>
	
	<div id="bounce" class="tab-pane ">
		<?php $this->renderPartial("./global_settings/_bounce"); ?>
	</div>  
	
	<div id="security" class="tab-pane ">
		<?php $this->renderPartial("./global_settings/_security"); ?>
	</div> 
	
	<div id="presentation" class="tab-pane ">
		<?php $this->renderPartial("./global_settings/_presentation"); ?>
	</div> 
	
	<div id="language" class="tab-pane ">
		<?php $this->renderPartial("./global_settings/_language", array( 'restrictToLanguages'=>$restrictToLanguages, 'allLanguages'=>$allLanguages, 'excludedLanguages'=>$excludedLanguages)); ?>
	</div> 
	
	<div id="interfaces" class="tab-pane ">
		<?php $this->renderPartial("./global_settings/_interfaces"); ?>
	</div> 

	<div id='updates' class="tab-pane ">
		<?php $this->renderPartial("./update/_updateContainer", array( "thisupdatecheckperiod"=>getGlobalSetting('updatecheckperiod'),"updatelastcheck"=>$updatelastcheck,"UpdateNotificationForBranch"=>getGlobalSetting('updatenotification'))); ?>
	
	</div>        
    
</div>
	<input type='hidden' name='restrictToLanguages' id='restrictToLanguages' value='<?php implode(' ',$restrictToLanguages); ?>'/>
	<input type='hidden' name='action' value='globalsettingssave'/>
</form>    