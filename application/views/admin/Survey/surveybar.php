<div class='menubar surveybar'>
	<div class='menubar-title ui-widget-header'>
		<strong><?php echo $clang->gT("Survey"); ?></strong>
		<span class='basic'><?php echo $surveyinfo['surveyls_title']."(".$clang->gT("ID").":".$surveyid.")"; ?></span>
	</div>
	<div class='menubar-main'>
		<div class='menubar-left'>
			<?php if($activated == "N") { ?>
				<img src='<?php echo $imageurl;?>/inactive.png' alt='<?php echo $clang->gT("This survey is currently not active"); ?>' />
				<?php if($canactivate) { ?>
					<a href="#" onclick="window.open('<?php echo site_url("admin/survey/activate/$surveyid");?>', '_top')"
						title="<?php echo $clang->gTview("Activate this Survey"); ?>" >
                    <img src='<?php echo $imageurl; ?>/activate.png' name='ActivateSurvey' alt='<?php echo $clang->gT("Activate this Survey"); ?>'/></a>
				<?php } else { ?>
					<img src='<?php echo $imageurl; ?>/activate_disabled.png'
						alt='<?php echo $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions."); ?>' />
				<?php } ?>
			<?php } else { ?>
				<?php if($expired) { ?>
					<img src='<?php echo $imageurl; ?>/expired.png' alt='<?php echo $clang->gT("This survey is active but expired."); ?>' />
				<?php } elseif($notstarted) { ?>
					<img src='<?php echo $imageurl; ?>/notyetstarted.png' alt='<?php echo $clang->gT("This survey is active but has a start date."); ?>' />
                <?php } else { ?>
                	<img src='<?php echo $imageurl; ?>/active.png' title='' alt='<?php echo $clang->gT("This survey is currently active."); ?>' />
 				<?php } 
				if($canactivate) { ?>
                    <a href="#" onclick="window.open('<?php echo site_url("admin/survey/deactivate/$surveyid"); ?>', '_top')"
                    	title="<?php echo $clang->gTview("Deactivate this Survey"); ?>" >
                    <img src='<?php echo $imageurl;?>/deactivate.png' alt='<?php echo $clang->gT("Deactivate this Survey"); ?>' /></a>
                <?php } else { ?>
                    <img src='<?php echo $imageurl; ?>/blank.gif' alt='' width='14' />
                <?php } ?>
			<?php } ?>
			<img src='<?php echo $imageurl;?>/seperator.gif' alt=''  />
        </div>
        <ul class='sf-menu'>
        	<?php if($onelanguage) { ?>
	        	<li><a href='#' accesskey='d' onclick="window.open('index.php?sid=<?php echo $surveyid;?>&amp;newtest=Y&amp;lang=<?php echo $baselang;?>', '_blank')" title="<?php echo $icontext2;?>" >
	            <img src='<?php echo $imageurl;?>/do.png' alt='<?php echo $icontext;?>' />
	            </a></li>
        	<?php } else { ?>
	        	<li><a href='#' title='<?php echo $icontext2;?>' accesskey='d'>
	            <img src='<?php echo $imageurl;?>/do.png' alt='<?php echo $icontext;?>' />
	            </a><ul>
	            <li><a accesskey='d' target='_blank' href='index.php?sid=<?php echo $surveyid;?>&amp;newtest=Y'>
	            <img src='<?php echo $imageurl;?>/do_30.png' /> <?php echo $icontext;?> </a><ul>
	            <?php foreach ($languagelist as $tmp_lang) { ?>
	                <li><a accesskey='d' target='_blank' href='index.php?sid=<?php echo $surveyid;?>&amp;newtest=Y&amp;lang=<?php echo $tmp_lang;?>'>
	                <img src='<?php echo $imageurl;?>/do_30.png' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
	            <?php } ?>
	            </ul></li>
	            </ul></li>
        	<?php } ?>
        	<li><a href='#'>
            <img src='<?php echo $imageurl;?>/edit.png' name='EditSurveyProperties' alt='<?php echo $clang->gT("Survey properties");?>' /></a><ul>
            	<?php if($surveylocale) { ?>
		            <li><a href='<?php echo site_url("admin/survey/editlocalsettings/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/edit_30.png' name='EditTextElements' /> <?php echo $clang->gT("Edit text elements");?></a></li>
		   		<?php } ?>
            	<?php if($surveysettings) { ?>
		            <li><a href='<?php echo site_url("admin/survey/editsurveysettings/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/token_manage_30.png' name='EditGeneralSettings' /> <?php echo $clang->gT("General settings");?></a></li>
		   		<?php } ?>
            	<?php if($surveysecurity) { ?>
		            <li><a href='<?php echo site_url("admin/surveysecurity/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/survey_security_30.png' name='SurveySecurity' /> <?php echo $clang->gT("Survey permissions");?></a></li>
		   		<?php } ?>
            	<?php if ($surveycontent) {
					if($activated == "Y") { ?>
		                <li><a href="#" onclick="alert('<?php echo $clang->gT("You can't reorder question groups if the survey is active.", "js");?>');" >
		                <img src='<?php echo $imageurl;?>/reorder_disabled_30.png' name='translate'/> <?php echo $clang->gT("Reorder question groups");?></a></li>
			        <?php } elseif ($groupsum) { ?>
		                <li><a href='<?php echo site_url("admin/questiongroup/order/$surveyid");?>'>
		                <img src='<?php echo $imageurl;?>/reorder_30.png' /> <?php echo $clang->gT("Reorder question groups");?></a></li>
			        <?php } else { ?>
			            <li><a href="#" onclick="alert('<?php echo $clang->gT("You can't reorder question groups if there is only one group.", "js");?>');" >
			            <img src='<?php echo $imageurl;?>/reorder_disabled_30.png' name='translate'/> <?php echo $clang->gT("Reorder question groups");?></a></li>
			        <?php }
			    } ?>
            	<?php if($quotas) { ?>
		            <li><a href='<?php echo site_url("admin/quotas/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/quota_30.png' /> <?php echo $clang->gT("Quotas");?></a></li>
		   		<?php } ?>
		   		<?php if($assessments) { ?>
		            <li><a href='<?php echo site_url("admin/assessments/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/assessments_30.png' /> <?php echo $clang->gT("Assessments");?></a></li>
		   		<?php } ?>
            	<?php if($surveylocale) { ?>
		            <li><a href='<?php echo site_url("admin/emailtemplates/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/emailtemplates_30.png' name='EditEmailTemplates' /> <?php echo $clang->gT("Email templates");?></a></li>
		   		<?php } ?>
            	</ul></li>
            	<li><a href="#">
                <img src='<?php echo $imageurl;?>/tools.png' name='SorveyTools' alt='<?php echo $clang->gT("Tools");?>' /></a><ul>
                <?php if ($surveydelete) { ?>
                	<li><a href="#" onclick="<?php echo get2post(site_url("admin/survey/delete")."?action=deletesurvey&amp;sid={$surveyid}");?>">
                	<img src='<?php echo $imageurl;?>/delete_30.png' name='DeleteSurvey' /> <?php echo $clang->gT("Delete survey");?></a></li>
		   		<?php } ?>
		   		<?php if ($surveytranslate) {
					if($hasadditionallanguages) { ?>
                		<li><a href="<?php echo site_url("admin/translate/$surveyid");?>">
                		<img src='<?php echo $imageurl;?>/translate_30.png' /> <?php echo $clang->gT("Quick-translation");?></a></li>
                	<?php } else { ?>
                		<li><a href="#" onclick="alert('<?php echo $clang->gT("Currently there are no additional languages configured for this survey.", "js");?>');" >
                		<img src='<?php echo $imageurl;?>/translate_disabled_30.png' /> <?php echo $clang->gT("Quick-translation");?></a></li>
		   			<?php } ?>
		   		<?php } ?>
		   		<?php if ($surveycontent) {
					if($conditionscount) { ?>
                		<li><a href="#" onclick="<?php echo get2post(base_url()."?action=resetsurveylogic&amp;sid=$surveyid");?>">
                		<img src='<?php echo $imageurl;?>/resetsurveylogic_30.png' name='ResetSurveyLogic' /> <?php echo $clang->gT("Reset conditions");?></a></li>
                	<?php } else { ?>
                		<li><a href="#" onclick="alert('<?php echo $clang->gT("Currently there are no conditions configured for this survey.", "js");?>');" >
                		<img src='<?php echo $imageurl;?>/resetsurveylogic_disabled_30.png' name='ResetSurveyLogic' /> <?php echo $clang->gT("Reset Survey Logic");?></a></li>
		   			<?php } ?>
		   		<?php } ?>
		   		</ul></li>
		   		<li><a href='#'>
            	<img src='<?php echo $imageurl;?>/display_export.png' name='DisplayExport' alt='<?php echo $clang->gT("Display / Export");?>' /></a><ul>
            	<?php if($surveyexport) { ?>
		            <li><a href='<?php echo site_url("admin/exportstructure/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/export_30.png' /> <?php echo $clang->gT("Export survey");?></a></li>
		   		<?php } ?>
		   		<?php if($onelanguage) { ?>
		            <li><a href='<?php echo site_url("admin/showprintablesurvey/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/print_30.png' name='ShowPrintableSurvey' /> <?php echo $clang->gT("Printable version");?></a></li>
		        <?php } else { ?>
		            <li><a href='<?php echo site_url("admin/showprintablesurvey/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/print_30.png' name='ShowPrintableSurvey' /> <?php echo $clang->gT("Printable version");?></a><ul>
		            <?php foreach ($languagelist as $tmp_lang) { ?>
		                <li><a accesskey='d' target='_blank' href='<?php echo site_url("admin/showprintablesurvey/index/$surveyid/$tmp_lang");?>'>
		                <img src='<?php echo $imageurl;?>/print_30.png' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
		            <?php } ?>
		            </ul></li>
		   		<?php } ?>
		   		<?php if($surveyexport) {
		   			if($onelanguage) { ?>
		            <li><a href='<?php echo site_url("admin/showquexmlsurvey/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/scanner_30.png' name='ShowPrintableScannableSurvey' /> <?php echo $clang->gT("QueXML export");?></a></li>
		        <?php } else { ?>
		            <li><a href='<?php echo site_url("admin/showquexmlsurvey/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/scanner_30.png' name='ShowPrintableScannableSurvey' /> <?php echo $clang->gT("QueXML export");?></a><ul>
		            <?php foreach ($languagelist as $tmp_lang) { ?>
		                <li><a accesskey='d' target='_blank' href='<?php echo site_url("admin/showquexmlsurvey/index/$surveyid/$tmp_lang");?>'>
		                <img src='<?php echo $imageurl;?>/scanner_30.png' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
		            <?php } ?>
		            </ul></li>
		   		<?php }
				} ?>
		   		</ul></li>
		   		<li><a href='#'><img src='<?php echo $imageurl;?>/responses.png' name='Responses' alt='<?php echo $clang->gT("Responses");?>' /></a><ul>
		   		<?php if($respstatsread) {
		   			if($canactivate) { ?>
		            <li><a href='<?php echo site_url("admin/browse/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/browse_30.png' name='BrowseSurveyResults' /> <?php echo $clang->gT("Responses & statistics");?></a></li>
		        <?php } else { ?>
               		<li><a href="#" onclick="alert('<?php echo $clang->gT("This survey is not active - no responses are available.","js");?>');" >
                	<img src='<?php echo $imageurl;?>/browse_disabled_30.png' name='BrowseSurveyResults' /> <?php echo $clang->gT("Responses & statistics");?></a></li>
		   		<?php }
				} ?>
		   		<?php if($responsescreate) {
		   			if($canactivate) { ?>
		            <li><a href='<?php echo site_url("admin/dataentry/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/dataentry_30.png' /> <?php echo $clang->gT("Data entry screen");?></a></li>
		        <?php } else { ?>
               		<li><a href="#" onclick="alert('<?php echo $clang->gT("This survey is not active, data entry is not allowed","js");?>');" >
                	<img src='<?php echo $imageurl;?>/dataentry_disabled_30.png' /> <?php echo $clang->gT("Data entry screen");?></a></li>
		   		<?php }
				} ?>
				<?php if($responsesread) {
		   			if($canactivate) { ?>
		            <li><a href='<?php echo site_url("admin/saved/index/$surveyid");?>' >
		            <img src='<?php echo $imageurl;?>/saved_30.png' name='BrowseSaved' /> <?php echo $clang->gT("Partial (saved) responses");?></a></li>
		        <?php } else { ?>
               		<li><a href="#" onclick="alert('<?php echo $clang->gT("This survey is not active - no responses are available","js");?>');" >
                	<img src='<?php echo $imageurl;?>/saved_disabled_30.png' name='PartialResponses' /> <?php echo $clang->gT("Partial (saved) responses");?></a></li>
		   		<?php }
				} ?>
				</ul></li>
				<?php if($tokenmanagement) { ?>
		            <li><a href="#" onclick="window.open('<?php echo site_url("admin/tokens/index/$surveyid");?>', '_top')"
						title="<?php echo $clang->gTview("Token management");?>" >
                    <img src='<?php echo $imageurl;?>/tokens.png' name='TokensControl' alt='<?php echo $clang->gT("Token management");?>'/></a></li>
		   		<?php } ?>
		   	</ul>

<div class='menubar-right'>
        <?php if ($permission)
        { ?> 
            <span class="boxcaption"><?php echo $clang->gT("Question groups"); ?>:</span>
            <select name='groupselect' onchange="window.open(this.options[this.selectedIndex].value,'_top')">
            
         <?php echo $groups; ?>
             </select>
        <?php } ?>
        
        <span class='arrow-wrapper'> 
        <?php if ($GidPrev != "")
        { ?>
            <a href='<?php echo site_url("admin/survey/view/$surveyid/$GidPrev"); ?>'>
            <img src='<?php echo $this->config->item('imageurl'); ?>/previous_20.png' title='' alt='<?php echo $clang->gT("Previous question group"); ?>'
            name='questiongroupprevious' <?php echo $clang->gT("Previous question group"); ?>/> </a>
        <?php }
        else
        { ?>
           
            <img src='<?php echo $this->config->item('imageurl'); ?>/previous_disabled_20.png' title='' alt='<?php echo $clang->gT("No previous question group"); ?>'
            name='noquestiongroupprevious' />
        <?php }

        
        if ($GidNext != "")
        { ?>
          
           
            <a href='<?php echo site_url("admin/survey/view/$surveyid/$GidNext"); ?>'>
            <img src='<?php echo $this->config->item('imageurl'); ?>/next_20.png' title='' alt='<?php echo $clang->gT("Next question group"); ?>'
            name='questiongroupnext' /> </a>
        <?php }
        else
        { ?>
           
            <img src='<?php echo $this->config->item('imageurl'); ?>/next_disabled_20.png' title='' alt='<?php echo $clang->gT("No next question group"); ?>'
            name='noquestiongroupnext' />
        <?php } ?>
		 </span>


        

        <?php if(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            if ($activated == "Y")
            { ?>
                <a href='#'>
                <img src='<?php echo $this->config->item('imageurl'); ?>/add_disabled.png' title='' alt='<?php echo $clang->gT("Disabled"); ?> - <?php echo $clang->gT("This survey is currently active."); ?>' 
                name='AddNewGroup' /></a>
            <?php }
            else
            { ?>
                
                 <a href="#" onclick="window.open('<?php echo site_url("admin/questiongroup/add/$surveyid"); ?>', '_top')"
                 title="<?php echo $clang->gTview("Add new group to survey"); ?>">
                 <img src='<?php echo $this->config->item('imageurl'); ?>/add.png' alt='<?php echo $clang->gT("Add new group to survey"); ?>' name='AddNewGroup' /></a>
            <?php }
        } ?>
         <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
         <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='15' alt='' />
         <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/minus.gif' title='<?php echo $clang->gT("Hide details of this Survey"); ?>'
         alt='<?php echo $clang->gT("Hide details of this Survey"); ?>' name='MinimiseSurveyWindow'
         onclick='document.getElementById("surveydetails").style.display="none";' />

         <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/plus.gif' title='<?php echo $clang->gT("Show details of this survey"); ?>' 
         alt='<?php echo $clang->gT("Show details of this survey"); ?>' name='MaximiseSurveyWindow' 
         onclick='document.getElementById("surveydetails").style.display="";' />

        <?php if (!$gid)
        { ?>
            
             <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/close.gif' title='<?php echo $clang->gT("Close this survey"); ?>' 
             alt='<?php echo $clang->gT("Close this survey"); ?>' name='CloseSurveyWindow' 
             onclick="window.open('<?php echo site_url("admin"); ?>', '_top')" />
        <?php }
        else
        { ?>
             <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='18' alt='' />
        <?php } ?>

</div>
</div>
</div>