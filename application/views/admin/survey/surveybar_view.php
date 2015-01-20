<div class='menubar surveybar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Survey"); ?></strong>
        <span class='basic'><?php echo $surveyinfo['surveyls_title']."(".$clang->gT("ID").":".$surveyid.")"; ?></span>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <?php if(!$activated) { ?>
                <img src='<?php echo $sImageURL;?>inactive.png' alt='<?php $clang->eT("This survey is currently not active"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                <?php if($canactivate) { ?>
                    <a href="<?php echo $this->createUrl("admin/survey/sa/activate/surveyid/$surveyid"); ?>">
                        <img src='<?php echo $sImageURL; ?>activate.png' alt='<?php $clang->eT("Activate this Survey"); ?>'/></a>
                    <?php } else { ?>
                    <img src='<?php echo $sImageURL; ?>activate_disabled.png'
                        alt='<?php $clang->eT("Survey cannot be activated. Either you have no permission or there are no questions."); ?>' />
                    <?php } ?>
                <?php } else { ?>
                <?php if($expired) { ?>
                    <img src='<?php echo $sImageURL; ?>expired.png' alt='<?php $clang->eT("This survey is active but expired."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php } elseif($notstarted) { ?>
                    <img src='<?php echo $sImageURL; ?>notyetstarted.png' alt='<?php $clang->eT("This survey is active but has a start date."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php } else { ?>
                    <img src='<?php echo $sImageURL; ?>active.png' title='' alt='<?php $clang->eT("This survey is currently active."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php }
                    if($canactivate) { ?>
                    <a href="<?php echo $this->createUrl("admin/survey/sa/deactivate/surveyid/$surveyid"); ?>">
                        <img src='<?php echo $sImageURL;?>deactivate.png' alt='<?php $clang->eT("Stop this survey"); ?>' /></a>
                    <?php } else { ?>
                    <img src='<?php echo $sImageURL; ?>blank.gif' alt='' width='14' />
                    <?php } ?>
                <?php } ?>
            <img src='<?php echo $sImageURL;?>separator.gif' class='separator' alt=''  />
        </div>
        <ul class='sf-menu'>
            <?php if($activated || $surveycontent) { ?>
                <?php if($onelanguage) { ?>
                    <li><a accesskey='d' target='_blank' href="<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$baselang"); ?>" >
                            <img src='<?php echo $sImageURL;?>do.png' alt='<?php echo $icontext;?>' />
                        </a></li>
                    <?php } else { ?>
                    <li><a accesskey='d' target='_blank' href="<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$baselang"); ?>" >
                            <img src='<?php echo $sImageURL;?>do.png' alt='<?php echo $icontext;?>' />
                        </a><ul>
                            <?php foreach ($languagelist as $tmp_lang) { ?>
                                <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$tmp_lang");?>'>
                                    <img src='<?php echo $sImageURL;?>do_30.png' alt=''/> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
                <?php } ?>
                
            <?php if($surveylocale || $surveysettings || $surveysecurity || $quotas || $assessments || $surveycontent) { ?>
            <li><a href='#'>
                    <img src='<?php echo $sImageURL;?>edit.png' alt='<?php $clang->eT("Survey properties");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a><ul>
                    <?php if($surveylocale) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/survey/sa/editlocalsettings/surveyid/$surveyid");?>'>
                            <img src='<?php echo $sImageURL;?>edit_30.png' alt=''/> <?php $clang->eT("Edit text elements");?></a></li>
                        <?php } ?>
                    <?php if($surveysettings) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/survey/sa/editsurveysettings/surveyid/$surveyid");?>' >
                            <img src='<?php echo $sImageURL;?>survey_settings_30.png' alt=''/> <?php $clang->eT("General settings");?></a></li>
                        <?php } ?>
                    <?php if($surveysecurity) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/surveypermission/sa/view/surveyid/$surveyid");?>' >
                            <img src='<?php echo $sImageURL;?>survey_security_30.png' alt=''/> <?php $clang->eT("Survey permissions");?></a></li>
                        <?php } ?>

                    <?php if($quotas) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$surveyid/");?>' >
                            <img src='<?php echo $sImageURL;?>quota_30.png' alt=''/> <?php $clang->eT("Quotas");?></a></li>
                        <?php } ?>
                    <?php if($assessments) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/assessments/sa/index/surveyid/$surveyid");?>' >
                            <img src='<?php echo $sImageURL;?>assessments_30.png' alt=''/> <?php $clang->eT("Assessments");?></a></li>
                        <?php } ?>
                    <?php if($surveylocale) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/emailtemplates/sa/index/surveyid/$surveyid");?>' >
                            <img src='<?php echo $sImageURL;?>emailtemplates_30.png' alt=''/> <?php $clang->eT("Email templates");?></a></li>
                        <?php } ?>
                    <?php if($surveycontent) { ?>
                        <?php if($onelanguage) { ?>
                            <li><a target='_blank' href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/$surveyid/");?>' >
                                <img src='<?php echo $sImageURL;?>quality_assurance_30.png' alt='' /> <?php $clang->eT("Survey logic file");?></a></li>
                            <?php } else { ?>
                            <li><a target='_blank' href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/$surveyid/");?>' >
                                <img src='<?php echo $sImageURL;?>quality_assurance_30.png' alt='' /> <?php $clang->eT("Survey logic file");?></a><ul>
                                    <?php foreach ($languagelist as $tmp_lang) { ?>
                                        <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/$surveyid/lang/$tmp_lang");?>'>
                                            <img src='<?php echo $sImageURL;?>quality_assurance.png' alt='' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                        <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                        <?php } ?>
                	</ul>
                </li>
            <?php } ?>
            
            <?php if($surveydelete || $surveytranslate || Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) { ?>            
            <li><a href="#">
                    <img src='<?php echo $sImageURL;?>tools.png' alt='<?php $clang->eT("Tools");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a><ul>
                    <?php if ($surveydelete) { ?>
                        <li><a href="<?php echo $this->createUrl("admin/survey/sa/delete/surveyid/{$surveyid}"); ?>">
                            <img src='<?php echo $sImageURL;?>delete_30.png' alt=''/> <?php $clang->eT("Delete survey");?></a></li>
                        <?php } ?>
                    <?php if ($surveytranslate) {
                            if($hasadditionallanguages) { ?>
                            <li><a href="<?php echo $this->createUrl("admin/translate/sa/index/surveyid/{$surveyid}");?>">
                                <img src='<?php echo $sImageURL;?>translate_30.png' alt=''/> <?php $clang->eT("Quick-translation");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("Currently there are no additional languages configured for this survey.", "js");?>');" >
                                <img src='<?php echo $sImageURL;?>translate_disabled_30.png' alt=''/> <?php $clang->eT("Quick-translation");?></a></li>
                            <?php } ?>
                        <?php } ?>
                    <?php if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) { ?>
                        <li><a href="<?php echo $this->createUrl("admin/expressions"); ?>">
                            <img src='<?php echo $sImageURL;?>expressionmanager_30.png' alt=''/> <?php $clang->eT("Expression Manager");?></a></li>
                        <?php } ?>
                    <?php if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')) { ?>
                        <li>
                            <?php if ($conditionscount>0){?>
                                <a href="<?php echo $this->createUrl("/admin/conditions/sa/index/subaction/resetsurveylogic/surveyid/{$surveyid}"); ?>">
                                <img src='<?php echo $sImageURL;?>resetsurveylogic_30.png' alt=''/><?php $clang->eT("Reset conditions");?></a>
                                <?php } else {?>
                                <a href="#" onclick="alert('<?php $clang->eT("Currently there are no conditions configured for this survey.", "js"); ?>');" >
                                <img src='<?php echo $sImageURL;?>resetsurveylogic_disabled_30.png' alt=''/> <?php $clang->eT("Reset conditions");?></a>
                            <?php } ?>
                        </li>
                        <?php if(!$activated) { ?>
                        <li>
                            <a href="#">
                            <img src='<?php echo $sImageURL;?>resetsurveylogic_30.png' alt=''/><?php $clang->eT("Regenerate question codes");?></a>
                            <ul>
                                <li>
                                    <a href="<?php echo $this->createUrl("/admin/survey/regenquestioncodes/surveyid/{$surveyid}/subaction/straight"); ?>">
                                    <img src='<?php echo $sImageURL;?>resetsurveylogic_30.png' alt=''/><?php $clang->eT("Straight");?></a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->createUrl("/admin/survey/regenquestioncodes/surveyid/{$surveyid}/subaction/bygroup"); ?>">
                                    <img src='<?php echo $sImageURL;?>resetsurveylogic_30.png' alt=''/><?php $clang->eT("By question group");?></a>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>
                        <?php } ?>
                	</ul>
                </li>
            <?php } ?> 
            
            <?php if($surveyexport || Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) { ?>
            <li><a href='#'>
                    <img src='<?php echo $sImageURL;?>display_export.png' alt='<?php $clang->eT("Display / Export");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
                    <ul>
                    <?php if($surveyexport) { ?>
                        <li><a href='#'>
                            <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("Export...");?></a>
                            <?php } ?>
                       		<ul>
                            <?php if($surveyexport) { ?>
                                <li><a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructurexml/surveyid/$surveyid");?>' >
                                    <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("Survey structure (.lss)");?></a>
                                </li>
                                <?php } ?>
                            <?php if($respstatsread && $surveyexport) {
                                    if ($activated){?>
                                    <li><a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportarchive/surveyid/$surveyid");?>' >
                                        <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("Survey archive (.lsa)");?></a></li>
                                    <?php }
                                    else
                                    {?>
                                    <li><a href="#" onclick="alert('<?php $clang->eT("You can only archive active surveys.", "js");?>');" >
                                        <img src='<?php echo $sImageURL;?>export_disabled_30.png' alt='' /> <?php $clang->eT("Survey archive (.lsa)");?></a></li><?php
                                    }
                            }?>
                            <?php if($surveyexport) { ?>
                                <li><a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructurequexml/surveyid/$surveyid");?>' >
                                    <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("queXML format (*.xml)");?></a>
                                </li>
                                <li><a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructuretsv/surveyid/$surveyid");?>' >
                                    <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("Tab-separated-values format (*.txt)");?></a>
                                </li>
                                <?php } ?>

                        </ul>
                    </li>
                    <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')) { ?>
                        <?php if($onelanguage) { ?>
                            <li><a target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$surveyid");?>' >
                                <img src='<?php echo $sImageURL;?>print_30.png' alt='' /> <?php $clang->eT("Printable version");?></a></li>
                            <?php } else { ?>
                            <li><a target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$surveyid");?>' >
                                <img src='<?php echo $sImageURL;?>print_30.png' alt='' /> <?php $clang->eT("Printable version");?></a><ul>
                                    <?php foreach ($languagelist as $tmp_lang) { ?>
                                        <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$surveyid/lang/$tmp_lang");?>'>
                                            <img src='<?php echo $sImageURL;?>print_30.png' alt='' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                        <?php } ?>
                                </ul></li>
                            <?php } ?>
                        <?php } ?>
                    <?php if($surveyexport) {
                            if($onelanguage) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/export/sa/showquexmlsurvey/surveyid/$surveyid");?>' >
                                <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("QueXML export");?></a></li>
                            <?php } else { ?>
                            <li><a href='<?php echo $this->createUrl("admin/export/sa/showquexmlsurvey/surveyid/$surveyid");?>' >
                                <img src='<?php echo $sImageURL;?>export_30.png' alt='' /> <?php $clang->eT("QueXML export");?></a><ul>
                                    <?php foreach ($languagelist as $tmp_lang) { ?>
                                        <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/export/sa/showquexmlsurvey/surveyid/$surveyid/lang/$tmp_lang");?>'>
                                            <img src='<?php echo $sImageURL;?>export_30.png' alt=''/> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                        <?php } ?>
                                </ul></li>
                            <?php }
                    } ?>
                	</ul>
                </li>
            <?php } ?> 
            
            <?php if($respstatsread || $responsescreate || $responsesread) { ?>
            	<li><a href='#'><img src='<?php echo $sImageURL;?>responses.png' alt='<?php $clang->eT("Responses");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
            		<ul>
                    <?php if($respstatsread) {
                            if($activated) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/responses/sa/index/surveyid/$surveyid/");?>' >
                                <img src='<?php echo $sImageURL;?>browse_30.png' alt='' /> <?php $clang->eT("Responses & statistics");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("This survey is not active - no responses are available.","js");?>');" >
                                <img src='<?php echo $sImageURL;?>browse_disabled_30.png' alt='' /> <?php $clang->eT("Responses & statistics");?></a></li>
                            <?php }
                    } ?>
                    <?php if($responsescreate) {
                            if($activated) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/dataentry/sa/view/surveyid/$surveyid");?>' >
                                <img src='<?php echo $sImageURL;?>dataentry_30.png' alt='' /> <?php $clang->eT("Data entry screen");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("This survey is not active, data entry is not allowed","js");?>');" >
                                <img src='<?php echo $sImageURL;?>dataentry_disabled_30.png' alt='' /> <?php $clang->eT("Data entry screen");?></a></li>
                            <?php }
                    } ?>
                    <?php if($responsesread) {
                            if($activated) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/saved/sa/view/surveyid/$surveyid");?>' >
                                <img src='<?php echo $sImageURL;?>saved_30.png' alt='' /> <?php $clang->eT("Partial (saved) responses");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("This survey is not active - no responses are available","js");?>');" >
                                <img src='<?php echo $sImageURL;?>saved_disabled_30.png' alt='' /> <?php $clang->eT("Partial (saved) responses");?></a></li>
                            <?php }
                    } ?>
                	</ul>
                </li>
            <?php } ?>    

            <?php if($surveycontent)
                {
                    if ($activated)
                    { ?><li><a href='#'>
                            <img src='<?php echo $sImageURL; ?>organize_disabled.png' title='' alt='<?php $clang->eT("Question group/question organizer disabled"); ?> - <?php $clang->eT("This survey is currently active."); ?>'
                                width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a></li>
                    <?php }
                    else
                    { ?><li>

                        <a href="<?php echo $this->createUrl("admin/survey/sa/organize/surveyid/$surveyid"); ?>">
                            <img src='<?php echo $sImageURL; ?>organize.png' alt='<?php $clang->eT("Reorder question groups / questions"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a></li>
                    <?php }
            } ?>

            <?php if($tokenmanagement) { ?>
                <li><a href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>">
                        <img src='<?php echo $sImageURL;?>tokens.png' alt='<?php $clang->eT("Token management");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
                </li>
                <?php } ?>
        </ul>

        <div class='menubar-right'>
            <?php if ($permission)
                { ?>
                <label for='groupselect'><?php $clang->eT("Question groups:"); ?></label>
                <select name='groupselect' id='groupselect' onchange="window.open(this.options[this.selectedIndex].value,'_top')">

                    <?php echo $groups; ?>
                </select>

                <span class='arrow-wrapper' style='font-size:0;'>
                    <?php if ($GidPrev != "")
                        { ?>
                        <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$GidPrev"); ?>'>
                        <img src='<?php echo $sImageURL; ?>previous_20.png' title='' alt='<?php $clang->eT("Previous question group"); ?>'/> </a>
                        <?php }
                        else
                        { ?>
                        <img src='<?php echo $sImageURL; ?>previous_disabled_20.png' title='' alt='<?php $clang->eT("No previous question group"); ?>' />
                        <?php }


                        if ($GidNext != "")
                        { ?>


                        <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$GidNext"); ?>'>
                        <img src='<?php echo $sImageURL; ?>next_20.png' title='' alt='<?php $clang->eT("Next question group"); ?>'/> </a>
                        <?php }
                        else
                        { ?>

                        <img src='<?php echo $sImageURL; ?>next_disabled_20.png' title='' alt='<?php $clang->eT("No next question group"); ?>'/>
                        <?php } ?>
                </span>
                <?php } ?>




            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
                {
                    if ($activated)
                    { ?>
                    <img src='<?php echo $sImageURL; ?>add_disabled.png' title='' alt='<?php $clang->eT("Disabled"); ?> - <?php $clang->eT("This survey is currently active."); ?>'
                        width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php }
                    else
                    { ?>

                    <a href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/$surveyid"); ?>">
                        <img src='<?php echo $sImageURL; ?>add.png' alt='<?php $clang->eT("Add new group to survey"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
                    <?php }
            } ?>
            <img id='separator3' src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
            <input type='image' src='<?php echo $sImageURL; ?>minimize.png' title='<?php $clang->eT("Hide details of this Survey"); ?>'
                alt='<?php $clang->eT("Hide details of this Survey"); ?>' onclick='$("#surveydetails").hide();' />

            <input type='image' src='<?php echo $sImageURL; ?>maximize.png' title='<?php $clang->eT("Show details of this survey"); ?>'
                alt='<?php $clang->eT("Show details of this survey"); ?>' onclick='$("#surveydetails").show();' />


            <input type='image' src='<?php echo $sImageURL; ?>close.png' title='<?php $clang->eT("Close this survey"); ?>'
                alt='<?php $clang->eT("Close this survey"); ?>' onclick="window.open('<?php echo $this->createUrl("/admin/index"); ?>','_top');"

                <?php if (!$gid){?>
                    style='visibility:hidden;'
                    <?php } ?>
                >
        </div>
    </div>
</div>
