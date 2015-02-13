<?php
    $bDemoMode=Yii::app()->getConfig("demoMode");
    $sStringDemoMode=$bDemoMode?'*':'';
    $sClassDemoMode=$bDemoMode?'demomode':null;
    App()->bootstrap->register();
?>
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
    <?php
        echo TbHtml::beginFormTb('horizontal', ["admin/globalsettings"], 'post', ['name' => 'frmglobalsettings', 'autocomplete' => 'off']);
        ?>
        <div id='overview'>
        <?php
            // Overview in 2 part : summary and update
            // Summary
            $sContentSummary = [
                gT("Users") => $usercount,
                gT("Surveys") => $surveycount,
                gT("Active surveys") => $activesurveycount,
                gT("Deactivated result tables") => $deactivatedsurveys,
                gT("Active token tables") => $activetokens
            ]; 
            if(Yii::app()->getConfig('iFileUploadTotalSpaceMB')>0)
            {
                $fUsed=calculateTotalFileUploadUsage();
                $sContentSummary[gT("Used/free space for file uploads")] = sprintf('%01.2F',$fUsed)." MB / ".sprintf('%01.2F',Yii::app()->getConfig('iFileUploadTotalSpaceMB')-$fUsed);
            }
          

            if (!App()->user->checkAccess('superadmin')) {
                $sContentSummary['phpinfo'] = [
                    'type'=>'link',
                    'label'=> gT('Show PHPInfo'),
                    'link'=> ['admin/globalsettings', 'sa'=>'showphpinfo'],
                    'text'=>gT('PHPInfo'),
                    'htmlOptions' => ['target' => '_blank']
                ];
            }
            
            $this->widget('SettingsWidget', array(
                //'id'=>'summary',
                'title'=>gt("System overview"),
                //'prefix' => 'globalSettings',
                'form' => false,
                'inlist'=>true,
                'settings' => $sContentSummary,
            ));

            // Update
            $aSettingsUpdate=array(
                'updatecheckperiod'=>array(
                    'type'=>'select',
                    'label'=>gT("Automatically check for updates"),
                    'options'=>array('0'=>gT("Never"),'1'=>gT("Every day"),'7'=>gT("Every week"),'14'=>gT("Every 2 weeks"),'30'=>gT("Every month")),
                    'current'=>$thisupdatecheckperiod,
                    'help'=>CHtml::link(gT("Check now"),$this->createUrl("admin/globalsettings",array('sa'=>"updatecheck")),array("class"=>"btn btn-link"))." ".CHtml::tag("em",array('id'=>"lastupdatecheck"),sprintf(gT("Last check: %s"),$updatelastcheck)),
                ),
                'updatenotification'=>array(
                    'type'=>'select',
                    'label'=>gT("Show update notifications"),
                    'options'=>array('never'=>gT("Never"),'stable'=>gT("For stable versions"),'both'=>gT("For stable and unstable versions")),
                    'current'=>$sUpdateNotification,
                ),
            );
            if (isset($updateavailable) && $updateavailable==1 && is_array($aUpdateVersions))
            {
                $aSettingsUpdate['updateavailable']=array(
                    'type'=>'info',
                    'content'=>CHtml::tag('strong',array(),gT('The following LimeSurvey updates are available:')),
                    );
                $aUpdateAvailable=array();
                foreach($aUpdateVersions as $aUpdateVersion)
                {
                    $sName="update_{$aUpdateVersion['versionnumber']}_{$aUpdateVersion['build']}";
                    $sLabel="{$aUpdateVersion['versionnumber']} ({$aUpdateVersion['build']}) ".($aUpdateVersion['branch']!='master'?gT('(unstable)'):gT('(stable)'));
                    $sDownloadLink=$aUpdateVersion['branch']!='master' ? "http://www.limesurvey.org/en/unstable-release/viewcategory/26-unstable-releases" : "http://www.limesurvey.org/en/stable-release";
                    $aSettingsUpdate[$sName]=array(
                        'type'=>'link',
                        'label'=>$sLabel,
                        'text'=>gt("Use ComfortUpdate"),
                        'link'=>$this->createUrl("admin/update",array('sa'=>'index','build'=>$aUpdateVersion['build'])),// Build is not needed
                        'htmlOptions'=>array(
                            'class'=>'button',
                            'target'=>'_top',
                        ),
                        'help'=>sprintf(gT('You can %s and % manually or use the %s'),
                            CHtml::link(gT("download"),$sDownloadLink,array('target'=>'_blank')),
                            CHtml::link(gT("update"),"http://manual.limesurvey.org/Upgrading_from_a_previous_version",array('target'=>'_blank')),
                            CHtml::link(gT("3-Click ComfortUpdate"),"http://manual.limesurvey.org/ComfortUpdate",array('target'=>'_blank'))
                        ),
                    );
                }
            }
            elseif(isset($updateinfo['errorcode']))
            {
                $aSettingsUpdate['updateavailable']=array(
                    'type'=>'info',
                    'label'=>sprintf(gT('There was an error on update check (%s)'),$updateinfo['errorcode']),
                    'content'=>CHtml::tag('pre',array(),strip_tags($updateinfo['errorhtml'])),
                    );
            }
            elseif ($updatable)
            {
                $aSettingsUpdate['updateavailable']=array(
                    'type'=>'info',
                    'content'=>gT('There is currently no newer LimeSurvey version available.'),
                );
            }
            else
            {
                $aSettingsUpdate['updateavailable']=array(
                    'type'=>'info',
                    'content'=>sprintf(gT('This is an unstable version and cannot be updated using ComfortUpdate. Please check %s regularly for a newer version.'),CHtml::link(gT("our website"),"http://www.limesurvey.org")),
                );
            }

            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                //'id'=>'update',
                'title'=>gt("Updates"),
                //'prefix' => 'globalSettings',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'inlist'=>true,
                'settings' => $aSettingsUpdate,
            ));
        ?>
        </div>
        <?php
            // General seetings in one part
            // Preparing array 
            $aTemplateNames=array_keys(getTemplateList());
            $aAdminThemes=array_keys(getAdminThemeList());
            $dateformatdata=getDateFormatData(Yii::app()->session['dateformat']);

            $aGeneralSettings=array(
                'info_general'=>array(
                    // A place to put information : alternate solution use array_slice/array_push ?
                ),
                'sitename'=>array(
                    'type'=>'string',
                    'label'=>gT("Site name").$sStringDemoMode,
                    'labelOptions'=>array(
                        'class'=>$sClassDemoMode,
                    ),
                    'current'=>getGlobalSetting('sitename'),
                    'htmlOptions'=>array(
                        'readonly'=>$bDemoMode,
                    ),
                ),
                'defaulttemplate'=>array(
                    'type'=>'select',
                    'label'=>gT('Default template').$sStringDemoMode,
                    'labelOptions'=>array(
                        'class'=>$sClassDemoMode,
                    ),
                    'htmlOptions'=>array(
                        'readonly'=>$bDemoMode,
                    ),
                    'options'=>array_combine($aTemplateNames,$aTemplateNames),
                    'current'=>getGlobalSetting('defaulttemplate'),
                ),
                'admintheme'=>array(
                    'type'=>'select',
                    'label'=>gT('Administration template').$sStringDemoMode,
                    'labelOptions'=>array(
                        'class'=>$sClassDemoMode,
                    ),
                    'htmlOptions'=>array(
                        'readonly'=>$bDemoMode,
                    ),
                    'options'=>array_combine($aAdminThemes,$aAdminThemes),
                    'current'=>getGlobalSetting('admintheme'),
                ),
                'defaulthtmleditormode'=>array(
                    'type'=>'select',
                    'label'=>gT('Default HTML editor mode').$sStringDemoMode,
                    'labelOptions'=>array(
                        'class'=>$sClassDemoMode,
                    ),
                    'htmlOptions'=>array(
                        'readonly'=>$bDemoMode,
                    ),
                    'options'=>array(
                        'none'=>gT("No HTML editor"),
                        'inline'=>gT("Inline HTML editor (default)"),
                        'popup'=>gT("Popup HTML editor")
                    ),
                    'current'=>getGlobalSetting('defaulthtmleditormode'),
                ),
                'defaultquestionselectormode'=>array(
                    'type'=>'select',
                    'label'=>gT('Question type selector').$sStringDemoMode,
                    'labelOptions'=>array(
                        'class'=>$sClassDemoMode,
                    ),
                    'htmlOptions'=>array(
                        'readonly'=>$bDemoMode,
                    ),
                    'options'=>array(
                        'default'=>gT("Full selector (default)"),
                        'none'=>gT("Simple selector"),
                    ),
                    'current'=>getGlobalSetting('defaultquestionselectormode'),
                ),
                'defaulttemplateeditormode'=>array(
                    'type'=>'select',
                    'label'=>gT('Template editor').$sStringDemoMode,
                    'labelOptions'=>array(
                        'class'=>$sClassDemoMode,
                    ),
                    'htmlOptions'=>array(
                        'readonly'=>$bDemoMode,
                    ),
                    'options'=>array(
                        'default'=>gT("Full template editor (default)"),
                        'none'=>gT("Simple template editor"),
                    ),
                    'current'=>getGlobalSetting('defaulttemplateeditormode'),
                ),
//                'timeadjust'=>array(
//                    'type'=>'float',
//                    'label'=>gt("Time difference (in hours)"),
//                    'current'=>str_replace(array('+',' hours',' minutes'),array('','',''),getGlobalSetting('timeadjust'))/60,
//                    'help'=>sprintf(gT("Server time: %s - Corrected time: %s"),convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'),convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'))
//                ),
                'iSessionExpirationTime'=>array(
                    // A place to put iSessionExpirationTime if needed
                ),
                'GeoNamesUsername'=>array(
                    'type'=>'string',
                    'label'=>'GeoNames username for API',
                    'current'=>getGlobalSetting('GeoNamesUsername'),
                    'htmlOptions'=>array(
                        'size'=>'35',
                    )
                ),
                'googleMapsAPIKey'=>array(
                    'type'=>'string',
                    'label'=>'Google Maps API key',
                    'current'=>getGlobalSetting('googleMapsAPIKey'),
                    'htmlOptions'=>array(
                        'size'=>'35',
                    )
                ),
                'ipInfoDbAPIKey'=>array(
                    'type'=>'string',
                    'label'=>'IP Info DB API Key',
                    'current'=>getGlobalSetting('ipInfoDbAPIKey'),
                    'htmlOptions'=>array(
                        'size'=>'35',
                    )
                ),
                'googleanalyticsapikey'=>array(
                    'type'=>'string',
                    'label'=>'Google Analytics API key',
                    'current'=>getGlobalSetting('googleanalyticsapikey'),
                    'htmlOptions'=>array(
                        'size'=>'35',
                    )
                ),
               'googletranslateapikey'=>array(
                    'type'=>'string',
                    'label'=>'Google Translate API key',
                    'current'=>getGlobalSetting('googletranslateapikey'),
                    'htmlOptions'=>array(
                        'size'=>'35',
                    )
                ),
            );

            if(isset(Yii::app()->session->connectionID))
            {
                $aGeneralSettings["iSessionExpirationTime"] = array(
                    'type'=>'int',
                    'label'=>'Session lifetime for surveys (seconds)',
                    'current'=>getGlobalSetting('iSessionExpirationTime'),
                    'htmlOptions'=>array(
                        'style'=>'width:10em',
                        'min'=>1,
                    )
                );
            }
            if($bDemoMode)
            {
                $aGeneralSettings['info_general']=array(
                    'type'=>'info',
                    'class'=>'alert',
                    'label'=>gt("Note"),
                    'content'=>gt("Demo mode is activated. Some settings can't be changed."),
                );
            }
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'id'=>'general',
                //'title'=>gt("General"),
                //'prefix' => 'globalSettings',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'inlist'=>true,
                'settings' => $aGeneralSettings,
            ));
        ?>

        <div id='email'>
        <?php
            // Email in 2 part : User and SMTP
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                //'id'=>'email',
                //'title'=>gt("SMTP settings"),
                //'prefix' => 'globalSettings',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'inlist'=>true,
                'settings' => array(
                    'siteadminemail'=>array(
                        'type'=>'email',
                        'label'=>gt("Default site admin email"),
                        'current'=>getGlobalSetting('siteadminemail'),
                    ),
                    'siteadminname'=>array(
                        'type'=>'string',
                        'label'=>gt("Administrator name"),
                        'current'=>getGlobalSetting('siteadminname'),
                    ),
                ),
            ));
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'title'=>gt("SMTP configuration"),
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'inlist'=>true,
                'settings' => array(
                    'emailmethod'=>array(
                        'type'=>'select',
                        'label'=>gt("Email method"),
                        'options'=>array(
                            'mail'=>gT("PHP (default)"),'smtp'=>gT("SMTP"),'sendmail'=>gT("Sendmail"),'qmail'=>gT("Qmail"),
                        ),
                        'current'=>getGlobalSetting('emailmethod'),
                    ),
                    'emailsmtphost'=>array(
                        'type'=>'string',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP host"),
                        'current'=>getGlobalSetting('emailsmtphost'),
                        'help'=>gT("Enter your hostname and port, e.g.: my.smtp.com:25"),
                    ),
                    'emailsmtpuser'=>array(
                        'type'=>'string',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP username"),
                        'current'=>getGlobalSetting('emailsmtpuser'),
                    ),
                    'emailsmtppassword'=>array(
                        'type'=>'password',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP password"),
                        'current'=>getGlobalSetting('emailsmtppassword'),
                    ),
                    'emailsmtpssl'=>array(
                        'type'=>'select',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP SSL/TLS"),
                        'options'=>array(''=>gT("Off"),'ssl'=>gT("SSL"),'tls'=>gT("TLS")),
                        'current'=>getGlobalSetting('emailsmtpssl'),
                    ),
                    'emailsmtpdebug'=>array(
                        'type'=>'select',
                        'label'=>gt("SMTP debug mode"),
                        'options'=>array('0'=>gT("Off"),'1'=>gT("On errors"),'2'=>gT("Always")),
                        'current'=>getGlobalSetting('emailsmtpdebug'),

                    ),
                    'maxemails'=>array(
                        'type'=>'int',
                        'label'=>gt("Email batch size"),
                        'current'=>getGlobalSetting('maxemails'),
//                        'htmlOptions'=>array(
//                            'min'=>'1',
//                        ),
                    ),
                ),
            ));
        ?>
        </div>

        <?php 
            // Bounce settings in one part
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'id'=>'bounce',
                'title'=>gt("SMTP configuration"),
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'inlist'=>true,
                'settings' => array(
                    'siteadminbounce'=>array(
                        'type'=>'string',
                        'label'=>gT("Default site bounce email"),
                        'current'=>getGlobalSetting('siteadminbounce'),
                    ),
                    'bounceaccounttype'=>array(
                        'type'=>'select',
                        'label'=>gT("Server type"),
                        'options'=>array('off'=>gT("Off"),'IMAP'=>gT("IMAP"),'POP'=>gT("POP")),
                        'current'=>getGlobalSetting('bounceaccounttype'),
                    ),
                    'bounceaccounthost'=>array(
                        'type'=>'string',
                        'label'=>gT("Server name & port"),
                        'current'=>getGlobalSetting('bounceaccounthost'),
                        'help'=>sprintf(gt("Enter your hostname and port, e.g.: %s"),"imap.example.com:995"),
                    ),
                    'bounceaccountuser'=>array(
                        'type'=>'string',
                        'label'=>gT("Bounce account user"),
                        'current'=>getGlobalSetting('bounceaccountuser'),
                    ),
                    'bounceaccountpass'=>array(
                        'type'=>'password',
                        'label'=>gT("Bounce account password"),
                        'current'=>'enteredpassword', //getGlobalSetting('bounceaccountpass'),
                    ),
                    'bounceencryption'=>array(
                        'type'=>'select',
                        'label'=>gT("Encryption type"),
                        'options'=>array('off'=>gT("Off"),'SSL'=>gT("SSL"),'TLS'=>gT("TLS")),
                        'current'=>getGlobalSetting('bounceencryption'),
                    ),
                )
            ));
        ?>
        <?php
            // Security settings in one part
            if(getGlobalSetting('force_ssl')!='on')
            {
                $sForceSslHelp = CHtml::tag("div",array('class'=>'alert'),sprintf(gT('Warning: Before turning on HTTPS,%s .'),CHtml::link(gt("check if this link works"),array("admin/globalsettings"),array('title'=>gT('Test if your server has SSL enabled by clicking on this link.')))))
                               . CHtml::tag("div",array('class'=>'alert alert-error'),gT("If the link does not work and you turn on HTTPS, LimeSurvey will break and you won't be able to access it."));
            }
            else
            {
                $sForceSslHelp=null;
            }
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'id'=>'security',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'settings'=>array(
                    'surveyPreview_require_Auth'=>array(
                        'type'=>'select',
                        'label'=>gt("Survey preview only for administration users"),
                        'options'=>array('1'=>gT("Yes"),'0'=>gT("No")),
                        'current'=>getGlobalSetting('surveyPreview_require_Auth'),
                    ),
                    'filterxsshtml'=>array(
                        'type'=>'select',
                        'label'=>gt("Survey preview only for administration users").$sStringDemoMode,
                        'labelOptions'=>array(
                            'class'=>$sClassDemoMode,
                        ),
                        'options'=>array('1'=>gT("Yes"),'0'=>gT("No")),
                        'current'=>getGlobalSetting('surveyPreview_require_Auth'),
                        'htmlOptions'=>array(
                            'readonly'=>$bDemoMode,
                        ),
                        'help'=>gT("(XSS filtering is always disabled for the superadministrator.)")
                    ),
                    'usercontrolSameGroupPolicy'=>array(
                        'type'=>'select',
                        'label'=>gt("Group member can only see own group"),
                        'options'=>array('1'=>gT("Yes"),'0'=>gT("No")),
                        'current'=>getGlobalSetting('usercontrolSameGroupPolicy'),
                    ),
                    'force_ssl'=>array(
                        'type'=>'select',
                        'label'=>gt("Force HTTPS"),
                        'options'=>array('neither'=>gT("Don't force on or off"),'on'=>gT("On"),'off'=>gT('Off')),
                        'current'=>getGlobalSetting('force_ssl'),
                        'help'=>$sForceSslHelp,
                    ),
                ),
            ));
        ?>
        <?php
            // Survey presentation settings in one part
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'id'=>'presentation',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'settings'=>array(
                    'shownoanswer'=>array(
                        'type'=>'select',
                        'label'=>gT("Show 'no answer' option for non-mandatory questions"),
                        'options'=>array("1"=>gT('Yes'),"0"=>gT('No'),"2"=>gT('Survey admin can choose')),
                        'current'=>getGlobalSetting('shownoanswer'),
                    ),
                    'repeatheadings'=>array(
                        'type'=>'int',
                        'label'=>gT("Repeating headings in array questions every X subquestions"),
                        'current'=>getGlobalSetting('repeatheadings'),
                        'htmlOptions'=>array(
                            'style'=>'width:5em',
                            'min'=>0,
                        ),
                    ),
                    'showxquestions'=>array(
                        'type'=>'select',
                        'label'=>gT('Show "There are X questions in this survey"'),
                        'options'=>array('choose'=>gT('Survey admin can choose'),'show'=>gT('Yes'),'hide'=>gT('No')),
                        'current'=>getGlobalSetting('showxquestions'),
                    ),
                    'showgroupinfo'=>array(
                        'type'=>'select',
                        'label'=>gT('Show question group name and/or description'),
                        'options'=>array('choose'=>gT('Survey admin can choose'),'show'=>gT('Show both'),'name'=>gT('Show group name only'),'description'=>gT('Show group description only'),'none'=>gT('Hide both')),
                        'current'=>getGlobalSetting('showgroupinfo'),
                    ),
                    'showqnumcode'=>array(
                        'type'=>'select',
                        'label'=>gT('Show question number and/or question code'),
                        'options'=>array('choose'=>gT('Survey admin can choose'),'show'=>gT('Show both'),'number'=>gT('Show question number only'),'code'=>gT('Show question code only'),'none'=>gT('Hide both')),
                        'current'=>getGlobalSetting('showqnumcode'),
                    ),
                    'pdffontsize'=>array(
                        'type'=>'int',
                        'label'=>gT("Font size of answers export PDFs"),
                        'current'=>getGlobalSetting('pdffontsize'),
                        'htmlOptions'=>array(
                            'size'=>'5',
                        ),
                    ),
                    'pdfshowheader'=>array(
                        'type'=>'int',
                        'label'=>gT("Show header in answers export PDFs?"),
                        'options'=>array(
                            'Y' => gT("Yes"),
                            'N' => gT("No"),
                        ),
                        'current'=>getGlobalSetting('pdfshowheader'),
                    ),
                    'pdflogowidth'=>array(
                        'type'=>'int',
                        'label'=>gT("Wigth of PDF header logo"),
                        'current'=>getGlobalSetting('pdflogowidth'),
                    ),
                    'pdfheadertitle'=>array(
                        'type'=>'string',
                        'label'=>gT("PDF header title (if empty, site name will be used)"),
                        'current'=>getGlobalSetting('pdfheadertitle'),
                        'htmlOptions'=>array(
                            'maxlength'=>'256',
                        ),
                    ),
                    'pdfheaderstring'=>array(
                        'type'=>'string',
                        'label'=>gT("PDF header string (if empty, survey name will be used)"),
                        'current'=>getGlobalSetting('pdfheaderstring'),
                        'htmlOptions'=>array(
                            'maxlength'=>'256',
                        ),
                    ),
                )
            ));
        ?>
        <?php
            // Language settings
            $aLanguages=array();
            foreach (getLanguageData(true) as  $sLanguage=>$aLanguage)
            {
                $aLanguages[$sLanguage]="{$aLanguage['description']} (".html_entity_decode($aLanguage['nativedescription'], ENT_NOQUOTES, 'UTF-8').")";
            }
            $aAvailableLang=getLanguageDataRestricted ();
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'id'=>'language',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'settings'=>array(
                    'defaultlang'=>array(
                        'type'=>'select',
                        'label'=>gT("Default site language").$sStringDemoMode,
                        'labelOptions'=>array(
                            'class'=>$sClassDemoMode,
                        ),
                        'options'=>$aLanguages,
                        'current'=>getGlobalSetting('defaultlang'),
                    ),
                    'restrictToLanguages'=>array(
                        'type'=>'select',
                        'label'=>gT("Available languages").$sStringDemoMode,
                        'options'=>$aLanguages,
                        'current'=>array_keys(getLanguageDataRestricted ()),
                        'htmlOptions'=>array(
                            'multiple'=>true,
                        ),
                        'selectOptions'=>array(
                            'width'=>'100%',
                        ),
                    ),
                ),
            ));
        ?>
            <?php
                // Remote control
                $this->widget('ext.SettingsWidget.SettingsWidget', array(
                    'id'=>'interfaces',
                    'form' => false,
                    'formHtmlOptions'=>array(
                        'class'=>'form-core',
                    ),
                    'settings'=>array(
                        'RPCInterface'=>array(
                            'type'=>'select',
                            'label'=>gT("RPC interface enabled"),
                            'options'=>array('off'=>gT("Off"),'json'=>gT("JSON-RPC"),'xml'=>gT("XML-RPC")),
                            'current'=>getGlobalSetting('RPCInterface'),
                        ),
                        'RPCurl'=>array(
                            'type'=>'info',
                            'label'=>gt("URL of API"),
                            'content'=>CHtml::tag('code',array(),$this->createAbsoluteUrl("admin/remotecontrol")),
                        ),
                        'rpc_publish_api'=>array(
                            'type'=>'select',
                            'label'=>gT("Publish API on /admin/remotecontrol"),
                            'options'=>array('0'=>gT("No"),'1'=>gT("Yes")),
                            'current'=>getGlobalSetting('rpc_publish_api'),
                        ),
                    )
                ));
            ?>
        <div class="hidden hide" id="submitglobalbutton">
            <p>
                <?php if(Yii::app()->session['refurl']) { ?>
                <button type="submit" name="action" value='savequit'><?php eT("Save and quit"); ?></button>
                <?php } ?>
                <button type="submit" name="action" value='save'><?php eT("Save"); ?></button>
            </p>
        </div>
    </form>

</div>

<div data-copy="submitglobalbutton"></div>
<?php if (Yii::app()->getConfig("demoMode")==true)
    { ?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php } ?>
