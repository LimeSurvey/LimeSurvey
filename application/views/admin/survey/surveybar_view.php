<div class='menubar surveybar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Survey"); ?></strong>
        <span class='basic'><?php echo $surveyinfo['surveyls_title']."(".$clang->gT("ID").":".$surveyid.")"; ?></span>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <?php if(!$activated) { ?>
                <img src='<?php echo $imageurl;?>inactive.png' alt='<?php $clang->eT("This survey is currently not active"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                <?php if($canactivate) { ?>
                    <a href="<?php echo $this->createurl("admin/survey/activate/surveyid/$surveyid"); ?>"
                        title="<?php $clang->eTview("Activate this Survey"); ?>" >
                        <img src='<?php echo $imageurl; ?>activate.png' alt='<?php $clang->eT("Activate this Survey"); ?>'/></a>
                    <?php } else { ?>
                    <img src='<?php echo $imageurl; ?>activate_disabled.png'
                        alt='<?php $clang->eT("Survey cannot be activated. Either you have no permission or there are no questions."); ?>' />
                    <?php } ?>
                <?php } else { ?>
                <?php if($expired) { ?>
                    <img src='<?php echo $imageurl; ?>expired.png' alt='<?php $clang->eT("This survey is active but expired."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php } elseif($notstarted) { ?>
                    <img src='<?php echo $imageurl; ?>notyetstarted.png' alt='<?php $clang->eT("This survey is active but has a start date."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php } else { ?>
                    <img src='<?php echo $imageurl; ?>active.png' title='' alt='<?php $clang->eT("This survey is currently active."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php }
                    if($canactivate) { ?>
                    <a href="<?php echo $this->createUrl("admin/survey/deactivate/surveyid/$surveyid"); ?>"
                        title="<?php $clang->eTview("Stop this survey"); ?>" >
                        <img src='<?php echo $imageurl;?>deactivate.png' alt='<?php $clang->eT("Stop this survey"); ?>' /></a>
                    <?php } else { ?>
                    <img src='<?php echo $imageurl; ?>blank.gif' alt='' width='14' />
                    <?php } ?>
                <?php } ?>
            <img src='<?php echo $imageurl;?>seperator.gif' alt=''  />
        </div>
        <ul class='sf-menu'>
            <?php if($onelanguage) { ?>
                <li><a accesskey='d' target='_blank' href="<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$baselang"); ?>" title="<?php echo $icontext2;?>" >
                        <img src='<?php echo $imageurl;?>do.png' alt='<?php echo $icontext;?>' />
                    </a></li>
                <?php } else { ?>
                <li><a href='#' title='<?php echo $icontext2;?>' accesskey='d'>
                        <img src='<?php echo $imageurl;?>do.png' alt='<?php echo $icontext;?>' />
                    </a><ul>
                        <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y"); ?>'>
                            <img src='<?php echo $imageurl;?>do_30.png' alt=''/> <?php echo $icontext;?> </a><ul>
                                <?php foreach ($languagelist as $tmp_lang) { ?>
                                    <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$tmp_lang");?>'>
                                        <img src='<?php echo $imageurl;?>do_30.png' alt=''/> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                    <?php } ?>
                            </ul></li>
                    </ul></li>
                <?php } ?>
            <li><a href='#'>
                    <img src='<?php echo $imageurl;?>edit.png' alt='<?php $clang->eT("Survey properties");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a><ul>
                    <?php if($surveylocale) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/survey/editlocalsettings/surveyid/$surveyid");?>'>
                            <img src='<?php echo $imageurl;?>edit_30.png' alt=''/> <?php $clang->eT("Edit text elements");?></a></li>
                        <?php } ?>
                    <?php if($surveysettings) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/survey/editsurveysettings/surveyid/$surveyid");?>' >
                            <img src='<?php echo $imageurl;?>surveysettings_30.png' alt=''/> <?php $clang->eT("General settings");?></a></li>
                        <?php } ?>
                    <?php if($surveysecurity) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/surveypermission/view/surveyid/$surveyid");?>' >
                            <img src='<?php echo $imageurl;?>survey_security_30.png' alt=''/> <?php $clang->eT("Survey permissions");?></a></li>
                        <?php } ?>

                    <?php if($quotas) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/quotas/index/surveyid/$surveyid/");?>' >
                            <img src='<?php echo $imageurl;?>quota_30.png' alt=''/> <?php $clang->eT("Quotas");?></a></li>
                        <?php } ?>
                    <?php if($assessments) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/assessments/index/surveyid/$surveyid");?>' >
                            <img src='<?php echo $imageurl;?>assessments_30.png' alt=''/> <?php $clang->eT("Assessments");?></a></li>
                        <?php } ?>
                    <?php if($surveylocale) { ?>
                        <li><a href='<?php echo $this->createUrl("admin/emailtemplates/index/surveyid/$surveyid");?>' >
                            <img src='<?php echo $imageurl;?>emailtemplates_30.png' alt=''/> <?php $clang->eT("Email templates");?></a></li>
                        <?php } ?>
                    <?php if($onelanguage) { ?>
                        <li><a target='_blank' href='<?php echo $this->createUrl("admin/expressions/survey_logic_file/sid/$surveyid/");?>' >
                            <img src='<?php echo $imageurl;?>quality_assurance.png' alt='' /> <?php $clang->eT("Survey logic file");?></a></li>
                        <?php } else { ?>
                        <li><a target='_blank' href='<?php echo $this->createUrl("admin/expressions/survey_logic_file/sid/$surveyid/");?>' >
                            <img src='<?php echo $imageurl;?>quality_assurance.png' alt='' /> <?php $clang->eT("Survey logic file");?></a><ul>
                                <?php foreach ($languagelist as $tmp_lang) { ?>
                                    <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/expressions/survey_logic_file/sid/$surveyid/lang/$tmp_lang");?>'>
                                        <img src='<?php echo $imageurl;?>quality_assurance.png' alt='' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                    <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>
                </ul></li>
            <li><a href="#">
                    <img src='<?php echo $imageurl;?>tools.png' alt='<?php $clang->eT("Tools");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a><ul>
                    <?php if ($surveydelete) { ?>
                        <li><a href="<?php echo $this->createUrl("admin/survey/delete/surveyid/$surveyid"); ?>">
                            <img src='<?php echo $imageurl;?>delete_30.png' alt=''/> <?php $clang->eT("Delete survey");?></a></li>
                        <?php } ?>
                    <?php if ($surveytranslate) {
                            if($hasadditionallanguages) { ?>
                            <li><a href="<?php echo $this->createUrl("admin/translate/index/surveyid/$surveyid");?>">
                                <img src='<?php echo $imageurl;?>translate_30.png' alt=''/> <?php $clang->eT("Quick-translation");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("Currently there are no additional languages configured for this survey.", "js");?>');" >
                                <img src='<?php echo $imageurl;?>translate_disabled_30.png' alt=''/> <?php $clang->eT("Quick-translation");?></a></li>
                            <?php } ?>
                        <?php } ?>
                    <?php if (hasSurveyPermission($surveyid,'surveycontent','update')) { ?>
                        <li><a href="<?php echo $this->createUrl("admin/expressions"); ?>">
                            <img src='<?php echo $imageurl;?>expressionmanager_30.png' alt=''/> <?php $clang->eT("Expression Manager");?></a></li>
                        <?php } ?>
                </ul></li>
            <li><a href='#'>
                    <img src='<?php echo $imageurl;?>display_export.png' alt='<?php $clang->eT("Display / Export");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a><ul>
                    <?php if($surveyexport) { ?>
                        <li><a href='#'>
                            <img src='<?php echo $imageurl;?>export_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Export...");?></a>
                            <?php } ?>
                        <ul>
                            <?php if($surveyexport) { ?>
                                <li><a href='<?php echo $this->createUrl("admin/export/survey/action/exportstructurexml/surveyid/$surveyid");?>' >
                                    <img src='<?php echo $imageurl;?>export_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Survey structure (.lss)");?></a>
                                </li>
                                <?php } ?>
                            <?php if($respstatsread && $surveyexport) {
                                    if ($activated){?>
                                    <li><a href='<?php echo $this->createUrl("admin/export/survey/action/exportarchive/surveyid/$surveyid");?>' >
                                        <img src='<?php echo $imageurl;?>export_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Survey archive (.zip)");?></a></li>
                                    <?php }
                                    else
                                    {?>
                                    <li><a href="#" onclick="alert('<?php $clang->eT("You can only archive active surveys.", "js");?>');" >
                                        <img src='<?php echo $imageurl;?>export_disabled_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Survey archive (.zip)");?></a></li><?php
                                    }
                            }?>
                            <?php if($surveyexport) { ?>
                                <li><a href='<?php echo $this->createUrl("admin/export/survey/action/exportstructurequexml/surveyid/$surveyid");?>' >
                                    <img src='<?php echo $imageurl;?>export_30.png' alt='' width="30" height="30"/> <?php $clang->eT("queXML format (*.xml)");?></a>
                                </li>
                                <li><a href='<?php echo $this->createUrl("admin/export/survey/action/exportstructureexcel/surveyid/$surveyid");?>' >
                                    <img src='<?php echo $imageurl;?>export_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Excel format (*.xls)");?></a>
                                </li>
                                <?php } ?>

                        </ul>
                    </li>
                    <?php if($onelanguage) { ?>
                        <li><a target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/index/surveyid/$surveyid");?>' >
                            <img src='<?php echo $imageurl;?>print_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Printable version");?></a></li>
                        <?php } else { ?>
                        <li><a target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/index/surveyid/$surveyid");?>' >
                            <img src='<?php echo $imageurl;?>print_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Printable version");?></a><ul>
                                <?php foreach ($languagelist as $tmp_lang) { ?>
                                    <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/index/surveyid/$surveyid/lang/$tmp_lang");?>'>
                                        <img src='<?php echo $imageurl;?>print_30.png' alt='' /> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                    <?php } ?>
                            </ul></li>
                        <?php } ?>
                    <?php if($surveyexport) {
                            if($onelanguage) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/export/showquexmlsurvey/surveyid/$surveyid");?>' >
                                <img src='<?php echo $imageurl;?>scanner_30.png' alt='' width="30" height="30"/> <?php $clang->eT("QueXML export");?></a></li>
                            <?php } else { ?>
                            <li><a href='<?php echo $this->createUrl("admin/export/showquexmlsurvey/surveyid/$surveyid");?>' >
                                <img src='<?php echo $imageurl;?>scanner_30.png' alt='' width="30" height="30"/> <?php $clang->eT("QueXML export");?></a><ul>
                                    <?php foreach ($languagelist as $tmp_lang) { ?>
                                        <li><a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/export/showquexmlsurvey/surveyid/$surveyid/lang/$tmp_lang");?>'>
                                            <img src='<?php echo $imageurl;?>scanner_30.png' alt=''/> <?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
                                        <?php } ?>
                                </ul></li>
                            <?php }
                    } ?>
                </ul></li>
            <li><a href='#'><img src='<?php echo $imageurl;?>responses.png' alt='<?php $clang->eT("Responses");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a><ul>
                    <?php if($respstatsread) {
                            if($activated) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/browse/index/surveyid/$surveyid/");?>' >
                                <img src='<?php echo $imageurl;?>browse_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Responses & statistics");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("This survey is not active - no responses are available.","js");?>');" >
                                <img src='<?php echo $imageurl;?>browse_disabled_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Responses & statistics");?></a></li>
                            <?php }
                    } ?>
                    <?php if($responsescreate) {
                            if($activated) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/dataentry/view/surveyid/$surveyid");?>' >
                                <img src='<?php echo $imageurl;?>dataentry_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Data entry screen");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("This survey is not active, data entry is not allowed","js");?>');" >
                                <img src='<?php echo $imageurl;?>dataentry_disabled_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Data entry screen");?></a></li>
                            <?php }
                    } ?>
                    <?php if($responsesread) {
                            if($activated) { ?>
                            <li><a href='<?php echo $this->createUrl("admin/saved/view/surveyid/$surveyid");?>' >
                                <img src='<?php echo $imageurl;?>saved_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Partial (saved) responses");?></a></li>
                            <?php } else { ?>
                            <li><a href="#" onclick="alert('<?php $clang->eT("This survey is not active - no responses are available","js");?>');" >
                                <img src='<?php echo $imageurl;?>saved_disabled_30.png' alt='' width="30" height="30"/> <?php $clang->eT("Partial (saved) responses");?></a></li>
                            <?php }
                    } ?>
                </ul></li>

            <?php if($surveycontent)
                {
                    if ($activated)
                    { ?><li><a href='#'>
                            <img src='<?php echo $imageurl; ?>organize_disabled.png' title='' alt='<?php $clang->eT("Question group/question organizer disabled"); ?> - <?php $clang->eT("This survey is currently active."); ?>'
                                width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a></li>
                    <?php }
                    else
                    { ?><li>

                        <a href="<?php echo $this->createUrl("admin/survey/organize/surveyid/$surveyid"); ?>">
                            <img src='<?php echo $imageurl; ?>organize.png' alt='<?php $clang->eT("Reorder question groups / questions"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a></li>
                    <?php }
            } ?>

            <?php if($tokenmanagement) { ?>
                <li><a href="<?php echo $this->createUrl("admin/tokens/index/surveyid/$surveyid"); ?>"
                        title="<?php $clang->eTview("Token management");?>" >
                        <img src='<?php echo $imageurl;?>tokens.png' alt='<?php $clang->eT("Token management");?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
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

                <span class='arrow-wrapper'>
                    <?php if ($GidPrev != "")
                        { ?>
                        <a href='<?php echo $this->createUrl("admin/survey/view/surveyid/$surveyid/gid/$GidPrev"); ?>'>
                        <img src='<?php echo $imageurl; ?>previous_20.png' title='' alt='<?php $clang->eT("Previous question group"); ?>' width="20" height="20"/> </a>
                        <?php }
                        else
                        { ?>
                        <img src='<?php echo $imageurl; ?>previous_disabled_20.png' title='' alt='<?php $clang->eT("No previous question group"); ?>' width="20" height="20"/>
                        <?php }


                        if ($GidNext != "")
                        { ?>


                        <a href='<?php echo $this->createUrl("admin/survey/view/surveyid/$surveyid/gid/$GidNext"); ?>'>
                            <img src='<?php echo $imageurl; ?>next_20.png' title='' alt='<?php $clang->eT("Next question group"); ?>'
                            width="20" height="20"/> </a>
                        <?php }
                        else
                        { ?>

                        <img src='<?php echo $imageurl; ?>next_disabled_20.png' title='' alt='<?php $clang->eT("No next question group"); ?>'
                            width="20" height="20"/>
                        <?php } ?>
                </span>
                <?php } ?>




            <?php if(hasSurveyPermission($surveyid,'surveycontent','create'))
                {
                    if ($activated)
                    { ?>
                    <img src='<?php echo $imageurl; ?>add_disabled.png' title='' alt='<?php $clang->eT("Disabled"); ?> - <?php $clang->eT("This survey is currently active."); ?>'
                        width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/>
                    <?php }
                    else
                    { ?>

                    <a href="<?php echo $this->createUrl("admin/questiongroup/add/surveyid/$surveyid"); ?>"
                        title="<?php $clang->eTview("Add new group to survey"); ?>">
                        <img src='<?php echo $imageurl; ?>add.png' alt='<?php $clang->eT("Add new group to survey"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
                    <?php }
            } ?>
            <img src='<?php echo $imageurl; ?>seperator.gif' alt='' />
            <img src='<?php echo $imageurl; ?>blank.gif' width='15' alt='' />
            <input type='image' src='<?php echo $imageurl; ?>minimize.png' title='<?php $clang->eT("Hide details of this Survey"); ?>'
                alt='<?php $clang->eT("Hide details of this Survey"); ?>' onclick='$("#surveydetails").hide();' />

            <input type='image' src='<?php echo $imageurl; ?>maximize.png' title='<?php $clang->eT("Show details of this survey"); ?>'
                alt='<?php $clang->eT("Show details of this survey"); ?>' onclick='$("#surveydetails").show();' />

            <?php if (!$gid)
                { ?>

                <input type='image' src='<?php echo $imageurl; ?>close.png' title='<?php $clang->eT("Close this survey"); ?>'
                    alt='<?php $clang->eT("Close this survey"); ?>' onclick="window.open('<?php echo $this->createUrl("/admin/index"); ?>','_top');" />
                <?php }
                else
                { ?>
                <img src='<?php echo $imageurl; ?>blank.gif' width='21' alt='' />
                <?php } ?>

        </div>
    </div>
</div>
