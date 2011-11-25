
<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<div class='menubar-title-left'>
			<strong><?php echo $clang->gT("Administration");?></strong>
			<?php
			if(Yii::app()->session['loginID'])
	    	{ ?>
	 			 --  <?php echo $clang->gT("Logged in as:");?><strong>
		        <a href="#" onclick="window.open('<?php echo $this->createUrl("/admin/user/personalsettings");?>', '_top')" title="<?php echo $clang->gTview("Edit your personal preferences");?>">
		        <?php echo Yii::app()->session['user'];?> <img src='<?php echo Yii::app()->getConfig('imageurl');?>/profile_edit.png' name='ProfileEdit' alt='<?php echo $clang->gT("Edit your personal preferences");?>' /></a>
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
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("/admin");?>', '_top')" title="<?php echo $clang->gTview("Default Administration Page");?>">
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/home.png' name='HomeButton' alt='<?php echo $clang->gT("Default Administration Page");?>' width='40' height='40'/></a>

	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/blank.gif' alt='' width='11' />
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' />

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/user/editusers");?>', '_top')" title="<?php echo $clang->gTview("Create/Edit Users");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/security.png' name='AdminSecurity' alt='<?php echo $clang->gT("Create/Edit Users");?>' width='40' height='40'/></a>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/usergroups/view");?>', '_top')" title="<?php echo $clang->gTview("Create/Edit Groups");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/usergroup.png' alt='<?php echo $clang->gT("Create/Edit Groups");?>' width='40' height='40'/></a>

		<?php
		if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/globalsettings");?>', '_top')" title="<?php echo $clang->gTview("Global settings");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/global.png' name='GlobalSettings' alt='<?php echo $clang->gT("Global settings");?>' width='40' height='40'/></a>
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
		<?php }
		if(Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/checkintegrity");?>', '_top')" title="<?php echo $clang->gTview("Check Data Integrity");?>">
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/checkdb.png' name='CheckDataIntegrity' alt='<?php echo $clang->gT("Check Data Integrity");?>' width='40' height='40'/></a>
		<?php
        }
		if(Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1)
		{
		/*
	         if ($databasetype=='mysql' || $databasetype=='mysqli')
	        {
		 */
		?>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/dumpdb");?>', '_top')" title="<?php echo $clang->gTview("Backup Entire Database");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/backup.png' name='ExportDB' alt='<?php echo $clang->gT("Backup Entire Database");?>' width='40' height='40'/>
	    </a>
	    <?php
	    /*
	        }
	        else
	        {
	            $adminmenu  .= "<img src='{$imageurl}/backup_disabled.png' name='ExportDB' alt='". $clang->gT("The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump.")."' />";
	        }
	        $adminmenu.="<img src='{$imageurl}/seperator.gif' alt=''  border='0' hspace='0' />\n";
	    }
		*/
		?>
		<img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
		<?php
		}
	    if(Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
		{
	    ?>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/labels/view");?>', '_top')" title="<?php echo $clang->gTview("Edit label sets");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/labels.png' name='LabelsEditor' alt='<?php echo $clang->gT("Edit label sets");?>' width='40' height='40'/></a>
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
	    <?php }
	    if(Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/templates/view");?>', '_top')" title="<?php echo $clang->gTview("Template Editor");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/templates.png' name='EditTemplates' alt='<?php echo $clang->gT("Template Editor");?>' width='40' height='40'/></a>
	    <?php } ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
        <?php
        if(Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'] == 1)
		{ 	 ?>
            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/participants");?>', '_top')" title="<?php echo $clang->gTview("Participant panel");?>" >
	        <img src='<?php echo Yii::app()->getConfig('imageurl');?>/tokens.png' name='participantpanel' alt='<?php echo $clang->gT("Participant panel");?>' width='40' height='40'/></a>
        <?php } ?>
	</div>
	<div class='menubar-right'>
        <label for='surveylist'><?php echo $clang->gT("Surveys:");?></label>
	    <select id='surveylist' name='surveylist' onchange="window.open(this.options[this.selectedIndex].value,'_top')">
	    <?php echo getsurveylist(false, false, $surveyid); ?>
	    </select>
        <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/listsurveys");?>', '_top')" title="<?php echo $clang->gTview("Detailed list of surveys");?>" >
        <img src='<?php echo Yii::app()->getConfig('imageurl');?>/surveylist.png' name='ListSurveys' alt='<?php echo $clang->gT("Detailed list of surveys");?>' />
        </a>

	    <?php
	    if(Yii::app()->session['USER_RIGHT_CREATE_SURVEY'] == 1)
		{ ?>

	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/survey/newsurvey");?>', '_top')" title="<?php echo $clang->gTview("Create, import, or copy a survey");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/add.png' name='AddSurvey' alt='<?php echo $clang->gT("Create, import, or copy a survey");?>' /></a>
	    <?php } ?>


	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
	    <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/authentication/logout");?>', '_top')" title="<?php echo $clang->gTview("Logout");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/logout.png' name='Logout' alt='<?php echo $clang->gT("Logout");?>' /></a>

	    <a href="http://docs.limesurvey.org" title="<?php echo $clang->gTview("LimeSurvey online manual");?>" >
	    <img src='<?php echo Yii::app()->getConfig('imageurl');?>/showhelp.png' name='ShowHelp' alt='<?php echo $clang->gT("LimeSurvey online manual");?>' /></a>
	</div>
	</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>