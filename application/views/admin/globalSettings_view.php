<?php
    App()->getClientScript()->registerPackage('jquery-selectboxes');

?>
<script type="text/javascript">
    var msgAtLeastOneLanguageNeeded = '<?php eT("You must set at last one available language.",'js'); ?>';
</script>
<div class='header ui-widget-header'><?php eT("Global settings"); ?></div>
<div id='tabs'>
    <ul>
        <li><a href='#overview'><?php eT("Overview & update"); ?></a></li>
        <li><a href='#general'><?php eT("General"); ?></a></li>
        <li><a href='#email'><?php eT("Email settings"); ?></a></li>
        <li><a href='#bounce'><?php eT("Bounce settings"); ?></a></li>
        <li><a href='#security'><?php eT("Security"); ?></a></li>
        <li><a href='#presentation'><?php eT("Presentation"); ?></a></li>
        <li><a href='#language'><?php eT("Language"); ?></a></li>
        <li><a href='#interfaces'><?php eT("Interfaces"); ?></a></li>
    </ul>
    <?php echo CHtml::form(array("admin/globalsettings"), 'post', array('class'=>'form30','id'=>'frmglobalsettings','name'=>'frmglobalsettings','autocomplete'=>'off'));?>
        <div id='overview'>
            <div class='header ui-widget-header'><?php eT("System overview"); ?></div>
            <br /><table class='statisticssummary'>
                <tr>
                    <th ><?php eT("Users"); ?>:</th><td><?php echo $usercount; ?></td>
                </tr>
                <tr>
                    <th ><?php eT("Surveys"); ?>:</th><td><?php echo $surveycount; ?></td>
                </tr>
                <tr>
                    <th ><?php eT("Active surveys"); ?>:</th><td><?php echo $activesurveycount; ?></td>
                </tr>
                <tr>
                    <th ><?php eT("Deactivated result tables"); ?>:</th><td><?php echo $deactivatedsurveys; ?></td>
                </tr>
                <tr>
                    <th ><?php eT("Active token tables"); ?>:</th><td><?php echo $activetokens; ?></td>
                </tr>
                <tr>
                    <th ><?php eT("Deactivated token tables"); ?>:</th><td><?php echo $deactivatedtokens; ?></td>
                </tr>
                <?php
                    if (Yii::app()->getConfig('iFileUploadTotalSpaceMB')>0)
                    {
                        $fUsed=calculateTotalFileUploadUsage();
                    ?>
                    <tr>
                        <th ><?php eT("Used/free space for file uploads"); ?>:</th><td><?php echo sprintf('%01.2F',$fUsed); ?> MB / <?php echo sprintf('%01.2F',Yii::app()->getConfig('iFileUploadTotalSpaceMB')-$fUsed); ?> MB</td>
                    </tr>
                    <?php
                    }
                ?>
            </table>
            <?php
                if (Permission::model()->hasGlobalPermission('superadmin','read'))
                {
                ?>
                    <p><a href="<?php echo $this->createUrl('admin/globalsettings',array('sa'=>'showphpinfo')) ?>" target="blank" class="button"><?php eT("Show PHPInfo"); ?></a></p>
                <?php
                }
                ?>

            <div class='header ui-widget-header'><?php echo eT("Updates"); ?></div>
                <ul>
                <li><label for='updatecheckperiod'><?php echo eT("Automatically check for updates:"); ?></label>
                    <?php echo CHtml::dropDownList('updatecheckperiod',$thisupdatecheckperiod,array('0'=>gT("Never"),'1'=>gT("Every day"),'7'=>gT("Every week"),'14'=>gT("Every 2 weeks"),'30'=>gT("Every month"))) ?>
                    <a class='button' href='<?php echo $this->createUrl("admin/globalsettings/sa/updatecheck"); ?>'><?php eT("Check now"); ?></a><!-- It not save actual setings, the it's not a button -->
                    <span id='lastupdatecheck'><?php echo sprintf(gT("Last check: %s"),$updatelastcheck); ?></span>
                </li>
                <li><label for='updatenotification'><?php echo eT("Show update notifications:"); ?></label>
                    <?php echo CHtml::dropDownList('updatenotification',$sUpdateNotification,array('never'=>gT("Never"),'stable'=>gT("For stable versions"),'both'=>gT("For stable and unstable versions"))) ?>
                 </li>
                <li>
                <?php
                    if (isset($updateavailable) && $updateavailable==1 && is_array($aUpdateVersions))
                    { ?>
                    <label><span style="font-weight: bold;"><?php echo gT('The following LimeSurvey updates are available:');?></span></label>
                        <table>
                        <?php foreach ($aUpdateVersions as $aUpdateVersion) { ?>
                           <tr><td>
                            <?php echo $aUpdateVersion['versionnumber'];?> (<?php echo $aUpdateVersion['build'];?>) <?php if ($aUpdateVersion['branch']!='master') eT('(unstable)'); else eT('(stable)');?>
                           </td>
                           <td>
                                <input type='button' onclick="window.open('<?php echo $this->createUrl("admin/update/sa/index",array('build'=>$aUpdateVersion['build'])); ?>', '_top')" value='<?php eT("Use ComfortUpdate"); ?>' />
                                <?php if ($aUpdateVersion['branch']!='master') {?> <input type='button' onclick="window.open('http://www.limesurvey.org/en/unstable-release/viewcategory/26-unstable-releases', '_blank')" value='<?php eT("Download"); ?>' /> <?php } 
                                else {?> <input type='button' onclick="window.open('http://www.limesurvey.org/en/stable-release', '_blank')" value='<?php eT("Download"); ?>' /> <?php }?>
                           </td></tr>
                        <?php };?>
                        </table>
                    <p><?php echo sprintf(gT('You can %s download and update manually %s or use the %s.'),"<a href='http://manual.limesurvey.org//Upgrading_from_a_previous_version'>","</a>","<a href='http://manual.limesurvey.org/ComfortUpdate'>".gT('3-Click ComfortUpdate').'</a>'); ?></p>
                    <?php } elseif (isset($updateinfo['errorcode'])) { 
                    echo sprintf(gT('There was an error on update check (%s)'),$updateinfo['errorcode']); ?><br />
                    <textarea readonly='readonly' style='width:35%; height:60px; overflow: auto;'><?php echo strip_tags($updateinfo['errorhtml']); ?></textarea>
                    <?php } elseif ($updatable)
                    {
                        eT('There is currently no newer LimeSurvey version available.');
                    }
                    else
                    {
                        printf(gT('This is an unstable version and cannot be updated using ComfortUpdate. Please check %sour website%s regularly for a newer version.'),"<a href='http://www.limesurvey.org'>","</a>");
                    } ?>
                </li>
            </ul>
        </div>

        <div id='general'>
            <ul>
                <li><label for='sitename'><?php eT("Site name:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <?php echo CHtml::textField('sitename',getGlobalSetting('sitename'),array('size'=>'50')); ?>
                </li>

                <li><label for="defaulttemplate"><?php eT("Default template:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <?php
                        $templatenames=array_keys(getTemplateList());
                        echo CHtml::dropDownList('defaulttemplate',getGlobalSetting('defaulttemplate'),array_combine($templatenames,$templatenames));
                    ?>
                </li>

                <li><label for="admintheme"><?php eT("Administration template:"); ?></label>
                    <?php
                       $adminthemes=array_keys(getAdminThemeList());
                        echo CHtml::dropDownList('admintheme',getGlobalSetting('admintheme'),array_combine($adminthemes,$adminthemes));
                    ?>
                </li>

                <li><label for='defaulthtmleditormode'><?php eT("Default HTML editor mode:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <?php echo CHtml::dropDownList('defaulthtmleditormode',getGlobalSetting('defaulthtmleditormode'),array('none'=>gT("No HTML editor"),'inline'=>gT("Inline HTML editor (default)"),'popup'=>gT("Popup HTML editor"))); ?>
                </li>

                <li><label for='defaultquestionselectormode'><?php eT("Question type selector:"); echo((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <?php echo CHtml::dropDownList('defaultquestionselectormode',getGlobalSetting('defaultquestionselectormode'),array('default'=>gT("Full selector (default)"),'none'=>gT("Simple selector"))); ?>
                </li>

                <li><label for='defaulttemplateeditormode'><?php eT("Template editor:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <?php echo CHtml::dropDownList('defaulttemplateeditormode',getGlobalSetting('defaulttemplateeditormode'),array('default'=>gT("Full template editor (default)"),'none'=>gT("Simple template editor"))); ?>
                </li>

                <?php $dateformatdata=getDateFormatData(Yii::app()->session['dateformat']); ?>
                <li><label for='timeadjust'><?php eT("Time difference (in hours):"); ?></label>
                    <span>
                        <?php echo CHtml::numberField('timeadjust',str_replace(array('+',' hours',' minutes'),array('','',''),getGlobalSetting('timeadjust'))/60,array('style'=>'width:10em')); ?>
                        <?php echo gT("Server time:").' '.convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')." - ". gT("Corrected time:").' '.convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'); ?>
                    </span>
                </li>
                <?php if(isset(Yii::app()->session->connectionID)) { ?>
                    <li>
                        <label for='iSessionExpirationTime'><?php eT("Session lifetime for surveys (seconds):"); ?></label>
                        <?php echo CHtml::numberField('iSessionExpirationTime',getGlobalSetting('iSessionExpirationTime'),array('style'=>'width:10em','min'=>'1')); ?>
                    </li>
                <?php } ?>

                <li>
                    <label for='GeoNamesUsername'><?php eT("GeoNames username for API:"); ?></label>
                    <?php echo CHtml::textField('GeoNamesUsername',getGlobalSetting('GeoNamesUsername'),array('size'=>'35')); ?>
                </li>
                <li><label for='googleMapsAPIKey'><?php eT("Google Maps API key:"); ?></label>
                    <?php echo CHtml::textField('googleMapsAPIKey',getGlobalSetting('googleMapsAPIKey'),array('size'=>'35')); ?>
                </li>
                <li><label for='ipInfoDbAPIKey'><?php eT("IP Info DB API Key:"); ?></label>
                    <?php echo CHtml::textField('ipInfoDbAPIKey',getGlobalSetting('ipInfoDbAPIKey'),array('size'=>'35')); ?>
                </li>
                <li><label for='googleanalyticsapikey'><?php eT("Google Analytics API key:"); ?></label>
                    <?php echo CHtml::textField('googleanalyticsapikey',getGlobalSetting('googleanalyticsapikey'),array('size'=>'35')); ?>
                </li>
                <li><label for='googletranslateapikey'><?php eT("Google Translate API key:"); ?></label>
                    <?php echo CHtml::textField('googletranslateapikey',getGlobalSetting('googletranslateapikey'),array('size'=>'35')); ?>
                </li>
            </ul>
        </div>


        <div id='email'>
            <ul>
                <li><label for='siteadminemail'><?php eT("Default site admin email:"); ?></label>
                    <?php echo CHtml::emailField('siteadminemail',getGlobalSetting('siteadminemail'),array('size'=>'50')); ?>
                </li>

                <li><label for='siteadminname'><?php eT("Administrator name:"); ?></label>
                    <?php echo CHtml::textField('siteadminname',getGlobalSetting('siteadminname'),array('size'=>'50')); ?>
                </li>
                <li><hr /><li>
                <li><label for='emailmethod'><?php eT("Email method:"); ?></label>
                    <?php echo CHtml::dropDownList('emailmethod',getGlobalSetting('emailmethod'),array('mail'=>gT("PHP (default)"),'smtp'=>gT("SMTP"),'sendmail'=>gT("Sendmail"),'qmail'=>gT("Qmail"))); ?>
                </li>
                <li><label for="emailsmtphost"><?php eT("SMTP host:"); ?></label>
                    <?php echo CHtml::textField('emailsmtphost',getGlobalSetting('emailsmtphost'),array('size'=>'50')); ?>
                    <span class='hint'><?php eT("Enter your hostname and port, e.g.: my.smtp.com:25"); ?></span>
                </li>
                <li><label for='emailsmtpuser'><?php eT("SMTP username:"); ?></label>
                    <?php echo CHtml::textField('emailsmtpuser',getGlobalSetting('emailsmtpuser'),array('size'=>'50')); ?>
                </li>
                <li><label for='emailsmtppassword'><?php eT("SMTP password:"); ?></label>
                    <?php echo CHtml::passwordField('emailsmtppassword','somepassword',array('size'=>'50','autocomplete'=>'off')); ?>
                </li>
                <li><label for='emailsmtpssl'><?php eT("SMTP SSL/TLS:"); ?></label>
                    <?php echo CHtml::dropDownList('emailsmtpssl',getGlobalSetting('emailsmtpssl'),array(''=>gT("Off"),'ssl'=>gT("SSL"),''=>gT("TLS"))) ?>
                </li>

                <li><label for='emailsmtpdebug'><?php eT("SMTP debug mode:"); ?></label>
                    <?php echo CHtml::dropDownList('emailsmtpdebug',getGlobalSetting('emailsmtpdebug'),array('0'=>gT("Off"),'1'=>gT("On errors"),'2'=>gT("Always"))) ?>
                </li>
                <li><label for='maxemails'><?php eT("Email batch size:"); ?></label>
                    <?php echo CHtml::numberField('maxemails',getGlobalSetting('maxemails'),array('style'=>'width:5em','min'=>'0')); ?>
                </li>
            </ul>
        </div>

        <div id='bounce'>
            <ul>
                <li><label for='siteadminbounce'><?php eT("Default site bounce email:"); ?></label>
                    <?php echo CHtml::textField('siteadminbounce',getGlobalSetting('siteadminbounce'),array('size'=>'50')); ?>
                </li>
                <li><label for='bounceaccounttype'><?php eT("Server type:"); ?></label>
                    <?php echo CHtml::dropDownList('bounceaccounttype',getGlobalSetting('bounceaccounttype'),array('off'=>gT("Off"),'IMAP'=>gT("IMAP"),'POP'=>gT("POP"))) ?>
                </li>
                <li><label for='bounceaccounthost'><?php eT("Server name & port:"); ?></label>
                    <?php echo CHtml::textField('bounceaccounthost',getGlobalSetting('bounceaccounthost'),array('size'=>'50')); ?>
                     <span class='hint'><?php eT("Enter your hostname and port, e.g.: imap.gmail.com:995"); ?></span>
                </li>
                <li><label for='bounceaccountuser'><?php eT("User name:"); ?></label>
                    <?php echo CHtml::textField('bounceaccountuser',getGlobalSetting('bounceaccountuser'),array('size'=>'50')); ?>
                </li>
                <li><label for='bounceaccountpass'><?php eT("Password:"); ?></label>
                    <?php echo CHtml::passwordField('bounceaccountpass','enteredpassword',array('size'=>'50','autocomplete'=>'off')); ?>
                </li>
                <li><label for='bounceencryption'><?php eT("Encryption type:"); ?></label>
                    <?php echo CHtml::dropDownList('bounceencryption',getGlobalSetting('bounceencryption'),array('off'=>gT("Off"),'SSL'=>gT("SSL"),'TLS'=>gT("TLS"))); ?>
                </li>
            </ul>
        </div>

        <div id='security'>
            <ul>
                <li><label for='surveyPreview_require_Auth'><?php eT("Survey preview only for administration users"); ?></label>
                    <?php echo CHtml::dropDownList('surveyPreview_require_Auth',getGlobalSetting('surveyPreview_require_Auth'),array('1'=>gT("Yes"),'0'=>gT("No"))); ?>
                </li>
                <li><label for='filterxsshtml'><?php eT("Filter HTML for XSS:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <?php echo CHtml::dropDownList('filterxsshtml',getGlobalSetting('filterxsshtml'),array('1'=>gT("Yes"),'0'=>gT("No"))); ?>
                    <span class='hint'><?php eT("(XSS filtering is always disabled for the superadministrator.)"); ?></span>
                </li>
                <li><label for='usercontrolSameGroupPolicy'><?php eT("Group member can only see own group:"); ?></label>
                    <?php echo CHtml::dropDownList('usercontrolSameGroupPolicy',getGlobalSetting('usercontrolSameGroupPolicy'),array('1'=>gT("Yes"),'0'=>gT("No"))); ?>
                 </li>
                <li><label for="force_ssl"><?php eT('Force HTTPS:'); ?></label>
                    <?php echo CHtml::dropDownList('force_ssl',getGlobalSetting('force_ssl'),array('neither'=>gT("Don't force on or off"),'on'=>gT("On"),'off'=>gT('Off')),array('encode'=>false)); ?>
                    <?php if(getGlobalSetting('force_ssl')!='on') { ?>
                        <div class='hint'><?php echo sprintf(gT('Warning: Before turning on HTTPS,%s check if this link works.%s'),'<a href="https://'.$_SERVER['HTTP_HOST'].$this->createUrl("admin/globalsettings").'" title="'. gT('Test if your server has SSL enabled by clicking on this link.').'">','</a>') ?></div>
                        <div class='hint'><?php eT("If the link does not work and you turn on HTTPS, LimeSurvey will break and you won't be able to access it."); ?></div>
                    <?php }?>
            </ul>
        </div>

        <div id='presentation'>
            <ul>
                <li><label for='shownoanswer'><?php eT("Show 'no answer' option for non-mandatory questions:"); ?></label>
                    <?php echo CHtml::dropDownList('shownoanswer',getGlobalSetting('shownoanswer'),array("1"=>gT('Yes'),"0"=>gT('No'),"2"=>gT('Survey admin can choose'))) ?>
                </li>
                <li><label for='repeatheadings'><?php eT("Repeating headings in array questions every X subquestions:"); ?></label>
                    <?php echo CHtml::numberField('repeatheadings',getGlobalSetting('repeatheadings'),array('style'=>'width:5em','min'=>0,'max'=>999,'step'=>1,)); ?>
                </li>

                <li><label for="showxquestions"><?php eT('Show "There are X questions in this survey"'); ?></label>
                    <?php echo CHtml::dropDownList('showxquestions',getGlobalSetting('showxquestions'),array('choose'=>gT('Survey admin can choose'),'show'=>gT('Yes'),'hide'=>gT('No'))) ?>
                </li>
                <li><label for="showgroupinfo"><?php eT('Show question group name and/or description'); ?></label>
                    <?php echo CHtml::dropDownList('showgroupinfo',getGlobalSetting('showgroupinfo'),array('choose'=>gT('Survey admin can choose'),'show'=>gT('Show both'),'name'=>gT('Show group name only'),'description'=>gT('Show group description only'),'none'=>gT('Hide both'))) ?>
                </li>
                <li><label for="showqnumcode"><?php eT('Show question number and/or question code'); ?></label>
                    <?php echo CHtml::dropDownList('showqnumcode',getGlobalSetting('showqnumcode'),array('choose'=>gT('Survey admin can choose'),'show'=>gT('Show both'),'number'=>gT('Show question number only'),'code'=>gT('Show question code only'),'none'=>gT('Hide both'))) ?>
                </li>
                <li><label for='pdffontsize'><?php eT("Font size of answers export PDFs"); ?></label>
                    <input type='text' size='5' id='pdffontsize' name='pdffontsize' value="<?php echo htmlspecialchars(getGlobalSetting('pdffontsize')); ?>" />
                </li>
                <li><label for="pdfshowheader"><?php eT('Show header in answers export PDFs?'); ?></label>
                    <?php echo CHtml::dropDownList('pdfshowheader', getGlobalSetting('pdfshowheader'), array(
                        'Y' => gT("Yes"),
                        'N' => gT("No")
                    ));
                    ?>
                </li>
                <li><label for='pdflogowidth'><?php eT("Wigth of PDF header logo"); ?></label>
                    <input type='text' size='5' id='pdflogowidth' name='pdflogowidth' value="<?php echo htmlspecialchars(getGlobalSetting('pdflogowidth')); ?>" />
                </li>
                <li><label for='pdfheadertitle'><?php eT("PDF header title (if empty, site name will be used)"); ?></label>
                    <input type='text' id='pdfheadertitle' size='50' maxlength='256' name='pdfheadertitle' value="<?php echo htmlspecialchars(getGlobalSetting('pdfheadertitle')); ?>" />
                </li>
                <li><label for='pdfheaderstring'><?php eT("PDF header string (if empty, survey name will be used)"); ?></label>
                    <input type='text' id='pdfheaderstring' size='50' maxlength='256' name='pdfheaderstring' value="<?php echo htmlspecialchars(getGlobalSetting('pdfheaderstring')); ?>" />
                </li>
            </ul>
        </div>
        <div id='language'>
            <ul>
                <li><label for='defaultlang'><?php eT("Default site language:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
                    <select name='defaultlang' id='defaultlang'>
                        <?php
                            $actuallang=getGlobalSetting('defaultlang');
                            foreach (getLanguageData(true) as  $langkey2=>$langname)
                            {
                            ?>
                            <option value='<?php echo $langkey2; ?>'
                                <?php
                                    if ($actuallang == $langkey2) { ?> selected='selected' <?php } ?>
                                ><?php echo $langname['nativedescription']." - ".$langname['description']; ?></option>
                            <?php
                            }
                        ?>
                    </select>
                </li>
                <li><label for='includedLanguages'><?php eT("Available languages:"); ?></label>
                    <table id='languageSelection'>
                        <tr>
                            <td>
                                <select style='min-width:220px;' size='5' id='includedLanguages' name='includedLanguages' multiple='multiple'><?php
                                        foreach ($restrictToLanguages as $sLanguageCode) {?>
                                        <option value='<?php echo $sLanguageCode; ?>'><?php echo $allLanguages[$sLanguageCode]['description']; ?></option>
                                        <?php
                                    }?>

                                </select>
                            </td>
                            <td >
                                <button id="btnAdd" type="button"><span class="ui-icon ui-icon-carat-1-w" style="float:left"></span><?php eT("Add"); ?></button><br /><button type="button" id="btnRemove"><span class="ui-icon ui-icon-carat-1-e" style="float:right"></span><?php eT("Remove"); ?></button>
                            </td>
                            <td >
                                <select size='5' style='min-width:220px;' id='excludedLanguages' name='excludedLanguages' multiple='multiple'>
                                    <?php foreach ($excludedLanguages as $sLanguageCode) {
                                        ?><option value='<?php echo $sLanguageCode; ?>'><?php echo $allLanguages[$sLanguageCode]['description']; ?></option><?php
                                    } ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </li>
            </ul>
        </div>
        <div id='interfaces'>
            <ul>
                <li><label for='RPCInterface'><?php eT("RPC interface enabled:"); ?></label>
                    <?php echo CHtml::dropDownList('RPCInterface',getGlobalSetting('RPCInterface'),array('off'=>gT("Off"),'json'=>gT("JSON-RPC"),'xml'=>gT("XML-RPC"))); ?>
                </li>
                <li>
                    <span class='label'><?php eT("URL:"); ?></span>
                    <?php echo $this->createAbsoluteUrl("admin/remotecontrol"); ?>
                </li>
                <li><label for='rpc_publish_api'><?php eT("Publish API on /admin/remotecontrol:"); ?></label>
                    <?php echo CHtml::dropDownList('rpc_publish_api',getGlobalSetting('rpc_publish_api'),array('0'=>gT("No"),'1'=>gT("Yes"))) ?>
                </li>
            </ul>
        </div>
        <input type='hidden' name='restrictToLanguages' id='restrictToLanguages' value='<?php implode(' ',$restrictToLanguages); ?>'/>
        <div class="hidden hide" id="submitglobalbutton">
            <p><button type="submit" name="action" value='globalsettingssave'><?php eT("Save"); ?></button></p>
        </div>
    </form>

</div>

<div data-copy="submitglobalbutton"></div>
<?php if (Yii::app()->getConfig("demoMode")==true)
    { ?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php } ?>
