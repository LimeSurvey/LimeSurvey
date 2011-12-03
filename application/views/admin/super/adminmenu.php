
<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<div class='menubar-title-left'>
			<strong><?php $clang->eT("Administration");?></strong>
			<?php
			if(Yii::app()->session['loginID'])
	    	{ ?>
	 			 --  <?php $clang->eT("Logged in as:");?><strong>
		        <a href="#" onclick="window.open('<?php echo $this->createUrl("/admin/user/sa/personalsettings");?>', '_top')" title="<?php $clang->eTview("Edit your personal preferences");?>">
		        <?php echo Yii::app()->session['user'];?> <img src='<?php echo Yii::app()->getConfig('imageurl');?>/profile_edit.png' name='ProfileEdit' alt='<?php $clang->eT("Edit your personal preferences");?>' /></a>
		        </strong>
	        <?php } ?>
	    </div>
	    <?php
		if($showupdate)
    	{ ?>
    	<div class='menubar-title-right'><a href='<?php echo $this->createUrl("admin/globalsettings");?>'><?php echo sprintf($clang->gT('Update available: %s'),$updateversion."($updatebuild)");?></a></div>
    	<?php } ?>
	</div>
    <div class='menubar-main'>
    <div class='menubar-left'>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("/admin");?>', '_top')" title="<?php $clang->eTview("Default Administration Page");?>">
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/home.png' name='HomeButton' alt='<?php $clang->eT("Default Administration Page");?>' width='40' height='40'/></a>

	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/blank.gif' alt='' width='11' />
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' />

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/user/sa/editusers");?>', '_top')" title="<?php $clang->eTview("Create/Edit Users");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/security.png' name='AdminSecurity' alt='<?php $clang->eT("Create/Edit Users");?>' width='40' height='40'/></a>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/usergroups/view");?>', '_top')" title="<?php $clang->eTview("Create/Edit Groups");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/usergroup.png' alt='<?php $clang->eT("Create/Edit Groups");?>' width='40' height='40'/></a>

		<?php
		if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/globalsettings");?>', '_top')" title="<?php $clang->eTview("Global settings");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/global.png' name='GlobalSettings' alt='<?php $clang->eT("Global settings");?>' width='40' height='40'/></a>
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
		<?php }
		if(Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/checkintegrity");?>', '_top')" title="<?php $clang->eTview("Check Data Integrity");?>">
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/checkdb.png' name='CheckDataIntegrity' alt='<?php $clang->eT("Check Data Integrity");?>' width='40' height='40'/></a>
		<?php
        }
		if(Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1)
		{

	        if (in_array(Yii::app()->db->getDriverName(), array('mysql', 'mysqli')) || Yii::app()->getConfig('demoMode') == true)
	        {

				?>

				<a href="#" onclick="window.open('<?php echo $this->createUrl("admin/dumpdb");?>', '_top')" title="<?php $clang->eTview("Backup Entire Database");?>" >
				<img src='<?php echo Yii::app()->getConfig('imageurl');?>/backup.png' name='ExportDB' alt='<?php $clang->eT("Backup Entire Database");?>' width='40' height='40'/>
				</a>

	        <?php } else { ?>
	            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/backup_disabled.png' name='ExportDB' alt='<?php $clang->eT("The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump."); ?>' />
	        <?php } ?>

	        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' border='0' hspace='0' />

			<?php
		}
	    if(Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
		{
	    ?>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/labels/sa/view");?>', '_top')" title="<?php $clang->eTview("Edit label sets");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/labels.png' name='LabelsEditor' alt='<?php $clang->eT("Edit label sets");?>' width='40' height='40'/></a>
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
	    <?php }
	    if(Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/templates/sa/view");?>', '_top')" title="<?php $clang->eTview("Template Editor");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/templates.png' name='EditTemplates' alt='<?php $clang->eT("Template Editor");?>' width='40' height='40'/></a>
	    <?php } ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
        <?php
        if(Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'] == 1)
		{ 	 ?>
            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/participants/sa/index");?>', '_top')" title="<?php $clang->eTview("Participant panel");?>" >
	        <img src='<?php echo Yii::app()->getConfig('imageurl');?>/tokens.png' name='participantpanel' alt='<?php $clang->eT("Participant panel");?>' width='40' height='40'/></a>
        <?php } ?>
	</div>
	<div class='menubar-right'>
        <label for='surveylist'><?php $clang->eT("Surveys:");?></label>
	    <select id='surveylist' name='surveylist' onchange="window.open(this.options[this.selectedIndex].value,'_top')">
	    <?php echo getsurveylist(false, false, $surveyid); ?>
	    </select>
        <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/listsurveys");?>', '_top')" title="<?php $clang->eTview("Detailed list of surveys");?>" >
        <img src='<?php echo Yii::app()->getConfig('imageurl');?>/surveylist.png' name='ListSurveys' alt='<?php $clang->eT("Detailed list of surveys");?>' />
        </a>

	    <?php
	    if(Yii::app()->session['USER_RIGHT_CREATE_SURVEY'] == 1)
		{ ?>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/newsurvey");?>', '_top')" title="<?php $clang->eTview("Create, import, or copy a survey");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/add.png' name='AddSurvey' alt='<?php $clang->eT("Create, import, or copy a survey");?>' /></a>
	    <?php } ?>


	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/authentication/logout");?>', '_top')" title="<?php $clang->eTview("Logout");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/logout.png' name='Logout' alt='<?php $clang->eT("Logout");?>' /></a>

	    <a href="http://docs.limesurvey.org" title="<?php $clang->eTview("LimeSurvey online manual");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/showhelp.png' name='ShowHelp' alt='<?php $clang->eT("LimeSurvey online manual");?>' /></a>
	</div>
	</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>