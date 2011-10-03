<div class='header ui-widget-header'><?php echo $clang->gT("Global settings");?></div>
<div id='tabs'>
    <ul>
        <li><a href='#overview'><?php echo $clang->gT("Overview & update");?></a></li>
        <li><a href='#general'><?php echo $clang->gT("General");?></a></li>
        <li><a href='#email'><?php echo $clang->gT("Email settings");?></a></li>
        <li><a href='#bounce'><?php echo $clang->gT("Bounce settings");?></a></li>
        <li><a href='#security'><?php echo $clang->gT("Security");?></a></li>
        <li><a href='#presentation'><?php echo $clang->gT("Presentation");?></a></li>
    </ul>
    <form class='form30' id='frmglobalsettings' name='frmglobalsettings' action='<?php echo site_url("admin/globalsettings");?>' method='post'>
        <div id='overview'>
            <?php echo $checksettings;?>

            <br /></p><div class='header ui-widget-header'><?php echo $clang->gT("Updates");?></div><ul>
                <li><label for='updatecheckperiod'><?php echo $clang->gT("Check for updates:");?></label>
                    <select name='updatecheckperiod' id='updatecheckperiod'>
                        <option value='0'
                            <?php if ($thisupdatecheckperiod==0) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Never");?></option>
                        <option value='1'
                            <?php if ($thisupdatecheckperiod==1) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Every day");?></option>
                        <option value='7'
                            <?php if ($thisupdatecheckperiod==7) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Every week");?></option>
                        <option value='14'
                            <?php if ($thisupdatecheckperiod==14) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Every 2 weeks");?></option>
                        <option value='30'
                            <?php if ($thisupdatecheckperiod==30) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Every month");?></option>
                    </select>&nbsp;<input type='button' onclick="window.open('<?php echo site_url("admin/globalsettings/updatecheck");?>', '_top')" value='<?php echo $clang->gT("Check now");?>' />&nbsp;<span id='lastupdatecheck'><?php echo sprintf($clang->gT("Last check: %s"),$updatelastcheck);?></span></li></ul><p>

                <?php
                    if (isset($updateavailable) && $updateavailable==1)
                    { ?>
                    <span style="font-weight: bold;"><?php echo sprintf($clang->gT('There is a LimeSurvey update available: Version %s'),$updateversion."($updatebuild)");?></span><br />
                    <?php echo sprintf($clang->gT('You can update %smanually%s or use the %s'),"<a href='http://docs.limesurvey.org/tiki-index.php?page=Upgrading+from+a+previous+version'>","</a>","<a href='".site_url('admin/update')."'>".$clang->gT('3-Click ComfortUpdate').'</a>');?><br />
                    <?php }
                    elseif (isset($updateinfo['errorcode']))
                    { echo sprintf($clang->gT('There was an error on update check (%s)'),$updateinfo['errorcode']);?><br />
                    <textarea readonly='readonly' style='width:35%; height:60px; overflow: auto;'><?php echo strip_tags($updateinfo['errorhtml']);?></textarea>

                    <?php }
                    else
                    {
                        echo $clang->gT('There is currently no newer LimeSurvey version available.');
                } ?>
            </p></div>

        <div id='general'>
            <ul>
                <li><label for='sitename'><?php echo $clang->gT("Site name:").(($this->config->item("demoModeOnly")==true)?'*':'');?></label>
                    <input type='text' size='50' id='sitename' name='sitename' value="<?php echo htmlspecialchars(getGlobalSetting('sitename'));?>" /></li>
                <li><label for='defaultlang'><?php echo $clang->gT("Default site language:").(($this->config->item("demoModeOnly")==true)?'*':'');?></label>
                    <select name='defaultlang' id='defaultlang'>
                        <?php
                            $actuallang=getGlobalSetting('defaultlang');
                            foreach (getLanguageData(true) as  $langkey2=>$langname)
                            {
                            ?>
                            <option value='<?php echo $langkey2;?>'
                                <?php
                                    if ($actuallang == $langkey2) { ?> selected='selected' <?php } ?>
                                ><?php echo $langname['nativedescription']." - ".$langname['description'];?></option>
                            <?php
                            }
                        ?>
                    </select></li><?php

                    $thisdefaulttemplate=getGlobalSetting('defaulttemplate');
                    $templatenames=array_keys(gettemplatelist());

                ?>

                <li><label for="defaulttemplate"><?php echo $clang->gT("Default template:");?></label>
                    <select name="defaulttemplate" id="defaulttemplate">
                        <?php
                            foreach ($templatenames as $templatename)
                            {
                                echo "<option value='$templatename'";
                                if ($thisdefaulttemplate==$templatename) { echo "selected='selected'";}
                                echo ">$templatename</option>";
                            }
                        ?>
                    </select></li>


                <?php $thisdefaulthtmleditormode=getGlobalSetting('defaulthtmleditormode'); ?>
                <li><label for='defaulthtmleditormode'><?php echo $clang->gT("Default HTML editor mode:").(($this->config->item("demoModeOnly")==true)?'*':'');?></label>
                    <select name='defaulthtmleditormode' id='defaulthtmleditormode'>
                        <option value='default'
                            <?php if ($thisdefaulthtmleditormode=='default') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Default HTML editor mode");?></option>
                        <option value='none'
                            <?php if ($thisdefaulthtmleditormode=='none') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("No HTML editor");?></option>
                        <option value='inline'
                            <?php if ($thisdefaulthtmleditormode=='inline') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Inline HTML editor");?></option>
                        <option value='popup'
                            <?php if ($thisdefaulthtmleditormode=='popup') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Popup HTML editor");?></option>
                    </select></li>
                <?php $dateformatdata=getDateFormatData($this->session->userdata('dateformat')); ?>
                <li><label for='timeadjust'><?php echo $clang->gT("Time difference (in hours):");?></label>
                    <span><input type='text' size='10' id='timeadjust' name='timeadjust' value="<?php echo htmlspecialchars(str_replace(array('+',' hours'),array('',''),getGlobalSetting('timeadjust')));?>" />
                        <?php echo $clang->gT("Server time:").' '.convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')." - ". $clang->gT("Corrected time :").' '.convertDateTimeFormat(date_shift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i');?>
                    </span></li>

                <?php $thisusepdfexport=getGlobalSetting('usepdfexport'); ?>
                <li><label for='usepdfexport'><?php echo $clang->gT("PDF export available:");?></label>
                    <select name='usepdfexport' id='usepdfexport'>
                        <option value='1'
                            <?php if ( $thisusepdfexport == true) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("On");?></option>
                        <option value='0'
                            <?php if ( $thisusepdfexport == false) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Off");?></option>
                    </select></li>

                <?php $thisaddTitleToLinks=getGlobalSetting('addTitleToLinks'); ?>
                <li><label for='addTitleToLinks'><?php echo $clang->gT("Screen reader compatibility mode:");?></label>
                    <select name='addTitleToLinks' id='addTitleToLinks'>
                        <option value='1'
                            <?php if ( $thisaddTitleToLinks == true) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("On");?></option>
                        <option value='0'
                            <?php if ( $thisaddTitleToLinks == false) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Off");?></option>
                    </select></li>
                <li><label for='sess_expiration'><?php echo $clang->gT("Session lifetime (seconds):");?></label>
                    <input type='text' size='10' id='sess_expiration' name='sess_expiration' value="<?php echo htmlspecialchars(getGlobalSetting('sess_expiration'));?>" /></li>
                <li><label for='ipInfoDbAPIKey'><?php echo $clang->gT("IP Info DB API Key:");?></label>
                    <input type='text' size='35' id='ipInfoDbAPIKey' name='ipInfoDbAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('ipInfoDbAPIKey'));?>" /></li>
                <li><label for='googleMapsAPIKey'><?php echo $clang->gT("Google Maps API key:");?></label>
                    <input type='text' size='35' id='googleMapsAPIKey' name='googleMapsAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('googleMapsAPIKey'));?>" /></li>
            </ul></div>


        <div id='email'><ul>
                <li><label for='siteadminemail'><?php echo $clang->gT("Default site admin email:");?></label>
                    <input type='text' size='50' id='siteadminemail' name='siteadminemail' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminemail'));?>" /></li>

                <li><label for='siteadminname'><?php echo $clang->gT("Administrator name:");?></label>
                    <input type='text' size='50' id='siteadminname' name='siteadminname' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminname'));?>" /><br /><br /></li>
                <li><label for='emailmethod'><?php echo $clang->gT("Email method:");?></label>
                    <select id='emailmethod' name='emailmethod'>
                        <option value='mail'
                            <?php if (getGlobalSetting('emailmethod')=='mail') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("PHP (default)");?></option>
                        <option value='smtp'
                            <?php if (getGlobalSetting('emailmethod')=='smtp') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("SMTP");?></option>
                        <option value='sendmail'
                            <?php if (getGlobalSetting('emailmethod')=='sendmail') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Sendmail");?></option>
                        <option value='qmail'
                            <?php if (getGlobalSetting('emailmethod')=='qmail') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Qmail");?></option>
                    </select></li>
                <li><label for="emailsmtphost"><?php echo $clang->gT("SMTP host:");?></label>
                    <input type='text' size='50' id='emailsmtphost' name='emailsmtphost' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtphost'));?>" />&nbsp;<font size='1'><?php echo $clang->gT("Enter your hostname and port, e.g.: my.smtp.com:25");?></font></li>
                <li><label for='emailsmtpuser'><?php echo $clang->gT("SMTP username:");?></label>
                    <input type='text' size='50' id='emailsmtpuser' name='emailsmtpuser' value="<?php echo htmlspecialchars(getGlobalSetting('emailsmtpuser'));?>" /></li>
                <li><label for='emailsmtppassword'><?php echo $clang->gT("SMTP password:");?></label>
                    <input type='password' size='50' id='emailsmtppassword' name='emailsmtppassword' value='somepassword' /></li>
                <li><label for='emailsmtpssl'><?php echo $clang->gT("SMTP SSL/TLS:");?></label>
                    <select id='emailsmtpssl' name='emailsmtpssl'>
                        <option value=''
                            <?php if (getGlobalSetting('emailsmtpssl')=='') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Off");?></option>
                        <option value='ssl'
                            <?php if (getGlobalSetting('emailsmtpssl')=='ssl' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("SSL");?></option>
                        <option value='tls'
                            <?php if (getGlobalSetting('emailsmtpssl')=='tls') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("TLS");?></option>
                    </select></li>
                <li><label for='emailsmtpdebug'><?php echo $clang->gT("SMTP debug mode:");?></label>
                    <select id='emailsmtpdebug' name='emailsmtpdebug'>
                        <option value=''
                            <?php if (getGlobalSetting('emailsmtpdebug')=='0') { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Off");?></option>
                        <option value='1'
                            <?php if (getGlobalSetting('emailsmtpdebug')=='1' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("On errors");?></option>
                        <option value='2'
                            <?php if (getGlobalSetting('emailsmtpdebug')=='2' || getGlobalSetting('emailsmtpssl')==1) { echo "selected='selected'";} ?>
                            ><?php echo $clang->gT("Always");?></option>
                    </select><br />&nbsp;</li>
                <li><label for='maxemails'><?php echo $clang->gT("Email batch size:");?></label>
                    <input type='text' size='5' id='maxemails' name='maxemails' value="<?php echo htmlspecialchars(getGlobalSetting('maxemails'));?>" /></li>
            </ul>

        </div>

        <div id='bounce'><ul>
                <li><label for='siteadminbounce'><?php echo $clang->gT("Default site bounce email:");?></label>
                    <input type='text' size='50' id='siteadminbounce' name='siteadminbounce' value="<?php echo htmlspecialchars(getGlobalSetting('siteadminbounce'));?>" /></li>
                <li><label for='bounceaccounttype'><?php echo $clang->gT("Server type:");?></label>
                    <select id='bounceaccounttype' name='bounceaccounttype'>
                        <option value='off'
                            <?php if (getGlobalSetting('bounceaccounttype')=='off') {echo " selected='selected'";}?>
                            ><?php echo $clang->gT("Off");?></option>
                        <option value='IMAP'
                            <?php if (getGlobalSetting('bounceaccounttype')=='IMAP') {echo " selected='selected'";}?>
                            ><?php echo $clang->gT("IMAP");?></option>
                        <option value='POP'
                            <?php if (getGlobalSetting('bounceaccounttype')=='POP') {echo " selected='selected'";}?>
                            ><?php echo $clang->gT("POP");?></option>
                    </select></li>

                <li><label for='bounceaccounthost'><?php echo $clang->gT("Server name & port:");?></label>
                <input type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccounthost'))?>" /><font size='1'><?php echo $clang->gT("Enter your hostname and port, e.g.: imap.gmail.com:995");?></font>

                <li><label for='bounceaccountuser'><?php echo $clang->gT("User name:");?></label>
                    <input type='text' size='50' id='bounceaccountuser' name='bounceaccountuser'
                        value="<?php echo htmlspecialchars(getGlobalSetting('bounceaccountuser'))?>" /></li>
                <li><label for='bounceaccountpass'><?php echo $clang->gT("Password:");?></label>
                    <input type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value='enteredpassword' /></li>
                <li><label for='bounceencryption'><?php echo $clang->gT("Encryption type:");?></label>
                    <select id='bounceencryption' name='bounceencryption'>
                        <option value='off'
                            <?php if (getGlobalSetting('bounceencryption')=='off') {echo " selected='selected'";}?>
                            ><?php echo $clang->gT("Off");?></option>
                        <option value='SSL'
                            <?php if (getGlobalSetting('bounceencryption')=='SSL') {echo " selected='selected'";}?>
                            ><?php echo $clang->gT("SSL");?></option>
                        <option value='TLS'
                            <?php if (getGlobalSetting('bounceencryption')=='TLS') {echo " selected='selected'";}?>
                            ><?php echo $clang->gT("TLS");?></option>
                    </select></li></ul>
        </div>

        <div id='security'><ul>
                <?php $thissurveyPreview_require_Auth=getGlobalSetting('surveyPreview_require_Auth'); ?>
                <li><label for='surveyPreview_require_Auth'><?php echo $clang->gT("Survey preview only for administration users");?></label>
                    <select id='surveyPreview_require_Auth' name='surveyPreview_require_Auth'>
                        <option value='1'
                            <?php if ($thissurveyPreview_require_Auth == true) { echo " selected='selected'";}?>
                            ><?php echo $clang->gT("Yes");?></option>
                        <option value='0'
                            <?php if ($thissurveyPreview_require_Auth == false) { echo " selected='selected'";}?>
                            ><?php echo $clang->gT("No");?></option>
                    </select></li>

                <?php $thisfilterxsshtml=getGlobalSetting('filterxsshtml'); ?>
                <li><label for='filterxsshtml'><?php echo $clang->gT("Filter HTML for XSS:").(($this->config->item("demoModeOnly")==true)?'*':'');?></label>
                    <select id='filterxsshtml' name='filterxsshtml'>
                        <option value='1'
                            <?php if ( $thisfilterxsshtml == true) { echo " selected='selected'";}?>
                            ><?php echo $clang->gT("Yes");?></option>
                        <option value='0'
                            <?php if ( $thisfilterxsshtml == false) { echo " selected='selected'";}?>
                            ><?php echo $clang->gT("No");?></option>
                    </select></li>

                <?php $thisusercontrolSameGroupPolicy=getGlobalSetting('usercontrolSameGroupPolicy'); ?>
                <li><label for='usercontrolSameGroupPolicy'><?php $clang->eT("Group member can only see own group:");?></label>
                    <select id='usercontrolSameGroupPolicy' name='usercontrolSameGroupPolicy'>
                        <option value='1'
                            <?php if ( $thisusercontrolSameGroupPolicy == true) { echo " selected='selected'";}?>
                            ><?php echo $clang->gT("Yes");?></option>
                        <option value='0'
                            <?php if ( $thisusercontrolSameGroupPolicy == false) { echo " selected='selected'";}?>
                            ><?php echo $clang->gT("No");?></option>
                    </select></li>

                <?php $thisforce_ssl = getGlobalSetting('force_ssl');
                    $opt_force_ssl_on = $opt_force_ssl_off = $opt_force_ssl_neither = '';
                    $warning_force_ssl = $clang->gT('Warning: Before turning on HTTPS, ')
                    . '<a href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"title="'
                    . $clang->gT('Test if your server has SSL enabled by clicking on this link.').'">'
                    . $clang->gT('check if this link works.').'</a><br/> '
                    . $clang->gT("If the link does not work and you turn on HTTPS, LimeSurvey will break and you won't be able to access it.");
                    switch($thisforce_ssl)
                    {
                        case 'on':
                            $warning_force_ssl = '&nbsp;';
                            break;
                        case 'off':
                        case 'neither':
                            break;
                        default:
                            $thisforce_ssl = 'neither';
                    };
                    $this_opt = 'opt_force_ssl_'.$thisforce_ssl;
                    $$this_opt = ' selected="selected"';
                ?><li><label for="force_ssl"><?php echo $clang->gT('Force HTTPS:');?></label>
                    <select name="force_ssl" id="force_ssl">
                        <option value="on" <?php echo $opt_force_ssl_on;?>><?php $clang->eT('On');?></option>
                        <option value="off" <?php echo $opt_force_ssl_off;?>><?php $clang->eT('Off');?></option>
                        <option value="neither" <?php echo $opt_force_ssl_neither;?>><?php $clang->eT("Don't force on or off");?></option>
                    </select></li>
                <li><span style='font-size:0.7em;'><?php echo $warning_force_ssl;?></span></li>
                <?php unset($thisforce_ssl,$opt_force_ssl_on,$opt_force_ssl_off,$opt_force_ssl_neither,$warning_force_ssl,$this_opt); ?>
            </ul></div>

        <div id='presentation'><ul>
                <?php $shownoanswer=getGlobalSetting('shownoanswer');
                    $sel_na = array( 0 => '' , 1 => '' , 2 => '');
                    $sel_na[$shownoanswer] = ' selected="selected"'; ?>
                <li><label for='shownoanswer'><?php $clang->eT("Show 'no answer' option for non-mandatory questions:");?></label>
                    <select id='shownoanswer' name='shownoanswer'>
                        <option value="1" <?php echo $sel_na[1];?> ><?php $clang->eT('Yes');?></option>
                        <option value="0" <?php echo $sel_na[0];?> ><?php $clang->eT('No');?></option>
                        <option value="2" <?php echo $sel_na[2];?> ><?php $clang->eT('Survey admin can choose');?></option>
                    </select></li>

                <?php $thisrepeatheadings=getGlobalSetting('repeatheadings');?>
                <li><label for='repeatheadings'><?php echo $clang->gT("Repeating headings in array questions every X subquestions:");?></label>
                    <input id='repeatheadings' name='repeatheadings' value='<?php echo $thisrepeatheadings;?>' size='4' maxlength='4' /></li>

                <?php
                    // showXquestions
                    $set_xq=getGlobalSetting('showXquestions');
                    $sel_xq = array( 'hide' => '' , 'show' => '' , 'choose' => '');
                    $sel_xq[$set_xq] = ' selected="selected"';
                    if( empty($sel_xq['hide']) && empty($sel_xq['show']) && empty($sel_xq['choose']))
                    {
                        $sel_xq['choose'] = ' selected="selected"';
                    };
                ?>
                <li><label for="showXquestions"><?php echo $clang->gT('Show "There are X questions in this survey"');?></label>
                    <select id="showXquestions" name="showXquestions">
                        <option value="show"<?php echo $sel_xq['show'];?>><?php echo $clang->gT('Yes');?></option>
                        <option value="hide"<?php echo $sel_xq['hide'];?>><?php echo $clang->gT('No');?></option>
                        <option value="choose"<?php echo $sel_xq['choose'];?>><?php echo $clang->gT('Survey admin can choose');?></option>
                    </select></li>
                <?php unset($set_xq,$sel_xq);
                    $set_gri=getGlobalSetting('showgroupinfo');
                    $sel_gri = array( 'both' => '' , 'choose' =>'' , 'description' => '' , 'name' => '' , 'none' => '' );
                    $sel_gri[$set_gri] = ' selected="selected"';
                    if( empty($sel_gri['both']) && empty($sel_gri['choose']) && empty($sel_gri['description']) && empty($sel_gri['name']) && empty($sel_gri['none']))
                    {
                        $sel_gri['choose'] = ' selected="selected"';
                    }; ?>
                <li><label for="showgroupinfo"><?php echo $clang->gT('Show question group name and/or description');?></label>
                    <select id="showgroupinfo" name="showgroupinfo">
                        <option value="both"<?php echo $sel_gri['both'];?>><?php echo $clang->gT('Show both');?></option>
                        <option value="name"<?php echo $sel_gri['name'];?>><?php echo $clang->gT('Show group name only');?></option>
                        <option value="description"<?php echo $sel_gri['description'];?>><?php echo $clang->gT('Show group description only');?></option>
                        <option value="none"<?php echo $sel_gri['none'];?>><?php echo $clang->gT('Hide both');?></option>
                        <option value="choose"<?php echo $sel_gri['choose'];?>><?php echo $clang->gT('Survey admin can choose');?></option>
                    </select></li><?php
                    unset($set_gri,$sel_gri);

                    // showqnumcode
                    $set_qnc=getGlobalSetting('showqnumcode');
                    $sel_qnc = array( 'both' => '' , 'choose' =>'' , 'number' => '' , 'code' => '' , 'none' => '' );
                    $sel_qnc[$set_qnc] = ' selected="selected"';
                    if( empty($sel_qnc['both']) && empty($sel_qnc['choose']) && empty($sel_qnc['number']) && empty($sel_qnc['code']) && empty($sel_qnc['none']))
                    {
                        $sel_qnc['choose'] = ' selected="selected"';
                    };
                ?>
                <li><label for="showqnumcode"><?php echo $clang->gT('Show question number and/or question code');?></label>
                    <select id="showqnumcode" name="showqnumcode">
                        <option value="both"<?php echo $sel_qnc['both'];?>><?php echo $clang->gT('Show both');?></option>
                        <option value="number"<?php echo $sel_qnc['number'];?>><?php echo $clang->gT('Show question number only');?></option>
                        <option value="code"<?php echo $sel_qnc['code'];?>><?php echo $clang->gT('Show question code only');?></option>
                        <option value="none"<?php echo $sel_qnc['none'];?>><?php echo $clang->gT('Hide both');?></option>
                        <option value="choose"<?php echo $sel_qnc['choose'];?>><?php echo $clang->gT('Survey admin can choose');?></option>
                    </select></li><?php
                    unset($set_qnc,$sel_qnc);
                ?>
            </ul>

        </div><input type='hidden' name='action' value='globalsettingssave'/></form>

</div>

<p><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php echo $clang->gT("Save settings");?>' /><br /></p>
<?php if ($this->config->item("demoModeOnly")==true)
    { ?>
    <p><?php echo $clang->gT("Note: Demo mode is activated. Marked (*) settings can't be changed.");?></p>
    <?php } ?>