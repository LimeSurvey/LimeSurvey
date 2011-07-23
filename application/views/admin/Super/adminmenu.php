<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<div class='menubar-title-left'>
			<strong><?php echo $clang->gT("Administration");?></strong>
			<?php
			if($this->session->userdata('loginID'))
	    	{ ?>
	 			 --  <?php echo $clang->gT("Logged in as:");?><strong>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/user/personalsettings");?>', '_top')" title="<?php echo $clang->gTview("Edit your personal preferences");?>">
		        <?php echo $this->session->userdata('user');?> <img src='<?php echo $this->config->item('imageurl');?>/profile_edit.png' name='ProfileEdit' alt='<?php echo $clang->gT("Edit your personal preferences");?>' /></a>
		        </strong>
	        <?php } ?>
	    </div>
	    <?php
		if($showupdate)
    	{ ?>
    	<div class='menubar-title-right'><a href='<?php echo site_url("admin/globalsettings");?>'><?php echo sprintf($clang->gT('Update available: %s'),$updateversion."($updatebuild)");?></a></div>
    	<?php } ?>
	</div>
    <div class='menubar-main'>
    <div class='menubar-left'>
	    <a href="#" onclick="window.open('<?php echo site_url("admin");?>', '_top')" title="<?php echo $clang->gTview("Default Administration Page");?>">
	    <img src='<?php echo $this->config->item('imageurl');?>/home.png' name='HomeButton' alt='<?php echo $clang->gT("Default Administration Page");?>' /></a>
	
	    <img src='<?php echo $this->config->item('imageurl');?>/blank.gif' alt='' width='11' />
	    <img src='<?php echo $this->config->item('imageurl');?>/seperator.gif' alt='' />
	
	    <a href="#" onclick="window.open('<?php echo site_url("admin/user/editusers");?>', '_top')" title="<?php echo $clang->gTview("Create/Edit Users");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/security.png' name='AdminSecurity' alt='<?php echo $clang->gT("Create/Edit Users");?>' /></a>
	
	    <a href="#" onclick="window.open('<?php echo site_url("admin/usergroups/view");?>', '_top')" title="<?php echo $clang->gTview("Create/Edit Groups");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/usergroup.png' alt='<?php echo $clang->gT("Create/Edit Groups");?>' /></a>
		
		<?php
		if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo site_url("admin/globalsettings");?>', '_top')" title="<?php echo $clang->gTview("Global settings");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/global.png' name='GlobalSettings' alt='<?php echo $clang->gT("Global settings");?>' /></a>
	    <img src='<?php echo $this->config->item('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
		<?php }
		if($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo site_url("admin/checkintegrity");?>', '_top')" title="<?php echo $clang->gTview("Check Data Integrity");?>">
	    <img src='<?php echo $this->config->item('imageurl');?>/checkdb.png' name='CheckDataIntegrity' alt='<?php echo $clang->gT("Check Data Integrity");?>' /></a>
		<?php } ?>
		
	    <a href="#" onclick="window.open('<?php echo site_url("admin/survey/listsurveys");?>', '_top')" title="<?php echo $clang->gTview("List Surveys");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/surveylist.png' name='ListSurveys' alt='<?php echo $clang->gT("List Surveys");?>' />
	    </a>
	    
		<?php
		if($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
		{ 	
		/*
	         if ($databasetype=='mysql' || $databasetype=='mysqli')
	        {
		 */
		?>
	
	    <a href="#" onclick="window.open('<?php echo site_url("admin/dumpdb");?>', '_top')" title="<?php echo $clang->gTview("Backup Entire Database");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/backup.png' name='ExportDB' alt='<?php echo $clang->gT("Backup Entire Database");?>' />
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
		<img src='<?php echo $this->config->item('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
		<?php
		}
	    if($this->session->userdata('USER_RIGHT_MANAGE_LABEL') == 1)
		{ 
	    ?>
	    
	    <a href="#" onclick="window.open('<?php echo site_url("admin/labels/view");?>', '_top')" title="<?php echo $clang->gTview("Edit label sets");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/labels.png' name='LabelsEditor' alt='<?php echo $clang->gT("Edit label sets");?>' /></a>    
	    <img src='<?php echo $this->config->item('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
	    <?php }
	    if($this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1)
		{ ?>
	    <a href="#" onclick="window.open('<?php echo site_url("admin/templates/view");?>', '_top')" title="<?php echo $clang->gTview("Template Editor");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/templates.png' name='EditTemplates' alt='<?php echo $clang->gT("Template Editor");?>' /></a>   
	    <?php } ?>
	</div>
	<div class='menubar-right'><span class="boxcaption"><?php echo $clang->gT("Surveys");?>:</span>
	    <select onchange="window.open(this.options[this.selectedIndex].value,'_top')">
	    <?php echo getsurveylist(false, false, $surveyid); ?>
	    </select>
	    
	    <?php
	    if($this->session->userdata('USER_RIGHT_CREATE_SURVEY') == 1)
		{ ?>	    

	    <a href="#" onclick="window.open('<?php echo site_url("admin/survey/newsurvey");?>', '_top')" title="<?php echo $clang->gTview("Create, import, or copy a survey");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/add.png' name='AddSurvey'' alt='<?php echo $clang->gT("Create, import, or copy a survey");?>' /></a>   
	    <?php } ?>
	    
	    
	    <img src='<?php echo $this->config->item('imageurl');?>/seperator.gif' alt='' border='0' hspace='0' />
	    <a href="#" onclick="window.open('<?php echo site_url("admin/authentication/logout");?>', '_top')" title="<?php echo $clang->gTview("Logout");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/logout.png' name='Logout' alt='<?php echo $clang->gT("Logout");?>' /></a>
	    
	    <a href="http://docs.limesurvey.org" title="<?php echo $clang->gTview("LimeSurvey online manual");?>" >
	    <img src='<?php echo $this->config->item('imageurl');?>/showhelp.png' name='ShowHelp' alt='<?php echo $clang->gT("LimeSurvey online manual");?>' /></a>
	</div>
	</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>