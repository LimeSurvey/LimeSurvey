<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php  eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>' 
    var sURLParameters = '';
    var sAddParam = '';
</script>


<ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
	<li role="presentation" class="active">
		<a data-toggle="tab" href='#general'>
			<?php  eT("General"); ?>
		</a>
	</li>

	<li role="presentation">
		<a data-toggle="tab" href="#presentation">
			<?php  eT("Presentation & navigation"); ?>
		</a>
	</li>

	<li role="presentation">
		<a data-toggle="tab" href="#publication">
			<?php  eT("Publication & access control"); ?>
		</a>
	</li>

	<li role="presentation">
		<a data-toggle="tab" href="#notification">
			<?php  eT("Notification & data management"); ?>
		</a>
	</li>	

	<li role="presentation">
		<a data-toggle="tab" href="#tokens">
			<?php  eT("Tokens"); ?>
		</a>
	</li>

	<?php if ($action == "newsurvey"): ?>
		<li role="presentation">
			<a data-toggle="tab" href="#import">
				<?php  eT("Import"); ?>
			</a>
		</li>
		
		<li role="presentation">
			<a data-toggle="tab" href="#copy">
				<?php  eT("Copy"); ?>
			</a>
		</li>
	<?php elseif($action == "editsurveysettings"): ?>
		<li role="presentation">
			<a data-toggle="tab" href="#panelintegration">
				<?php  eT("Panel integration"); ?>
			</a>
		</li>
		<li role="presentation">
			<a data-toggle="tab" href="#resources">
				<?php  eT("Resources"); ?>
			</a>
		</li>
		<?php if(isset($pluginSettings)): ?>
			<li role="presentation">
				<a data-toggle="tab" href="#pluginsettings">
					<?php  eT("Plugins"); ?>
				</a>
			</li>			
		<?php endif;?>		
	<?php endif; ?>			
	<li role="presentation">
		<a data-toggle="tab" href="#">
		</a>
	</li>	
</ul>	