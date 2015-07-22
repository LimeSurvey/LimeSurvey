<?php
    $bDemoMode=Yii::app()->getConfig("demoMode");
    $sStringDemoMode=$bDemoMode?'*':'';
    $sClassDemoMode=$bDemoMode?'demomode':null;
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
    <?php echo CHtml::form(array("admin/globalsettings"), 'post', array('id'=>'frmglobalsettings','name'=>'frmglobalsettings','autocomplete'=>'off'));?>
        <div id='overview'>
        <?php
            // Overview in 2 part : summary and update
            // Summary
            $sContentSummary= CHtml::openTag('table',array('class'=>'statisticssummary'))
                            . CHtml::openTag('tr').CHtml::tag('th',array(),gT("Users")).CHtml::tag('td',array(),$usercount).CHtml::closeTag('tr')
                            . CHtml::openTag('tr').CHtml::tag('th',array(),gT("Surveys")).CHtml::tag('td',array(),$surveycount).CHtml::closeTag('tr')
                            . CHtml::openTag('tr').CHtml::tag('th',array(),gT("Active surveys")).CHtml::tag('td',array(),$activesurveycount).CHtml::closeTag('tr')
                            . CHtml::openTag('tr').CHtml::tag('th',array(),gT("Deactivated result tables")).CHtml::tag('td',array(),$deactivatedsurveys).CHtml::closeTag('tr')
                            . CHtml::openTag('tr').CHtml::tag('th',array(),gT("Active token tables")).CHtml::tag('td',array(),$activetokens).CHtml::closeTag('tr');
            if(Yii::app()->getConfig('iFileUploadTotalSpaceMB')>0)
            {
                $fUsed=calculateTotalFileUploadUsage();
                $sContentSummary.= CHtml::openTag('tr').CHtml::tag('th',array(),gT("Used/free space for file uploads")).CHtml::tag('td',array(),sprintf('%01.2F',$fUsed)." MB / ".sprintf('%01.2F',Yii::app()->getConfig('iFileUploadTotalSpaceMB')-$fUsed)).CHtml::closeTag('tr');
            }
            $sContentSummary.= CHtml::closeTag('table',array('class'=>'statisticssummary'));

            $aOverviewSettings=array(
                'summary'=>array(
                    'type'=>'info',
                    'content'=>$sContentSummary,
                ),
                'phpinfo'=>array(
                    'type'=>'link',
                    'label'=>gT('Show PHPInfo'),
                    'link'=>$this->createUrl('admin/globalsettings',array('sa'=>'showphpinfo')),
                    'text'=>gT('PHPInfo'),
                    'htmlOptions'=>array(
                        'target'=>'blank',
                    ),
                ),
            );

            if (!Permission::model()->hasGlobalPermission('superadmin'))
            {
                unset($aOverviewSettings['phpinfo']);
            }
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                //'id'=>'summary',
                'title'=>gt("System overview"),
                //'prefix' => 'globalSettings',
                'form' => false,
                'formHtmlOptions'=>array(
                    'class'=>'form-core',
                ),
                'inlist'=>true,
                'settings' => $aOverviewSettings,
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
                    'options'=>array('never'=>gT("Never",'unescaped'),'stable'=>gT("For stable versions",'unescaped'),'both'=>gT("For stable and unstable versions",'unescaped')),
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
                    'current'=>Template::templateNameFilter(getGlobalSetting('defaulttemplate')),
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
                        'none'=>gT("No HTML editor",'unescaped'),
                        'inline'=>gT("Inline HTML editor (default)",'unescaped'),
                        'popup'=>gT("Popup HTML editor",'unescaped')
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
                        'default'=>gT("Full selector (default)",'unescaped'),
                        'none'=>gT("Simple selector",'unescaped'),
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
                        'default'=>gT("Full template editor (default)",'unescaped'),
                        'none'=>gT("Simple template editor",'unescaped'),
                    ),
                    'current'=>getGlobalSetting('defaulttemplateeditormode'),
                ),
                'timeadjust'=>array(
                    'type'=>'float',
                    'label'=>gt("Time difference (in hours)"),
                    'current'=>str_replace(array('+',' hours',' minutes'),array('','',''),getGlobalSetting('timeadjust'))/60,
                    'help'=>sprintf(gT("Server time: %s - Corrected time: %s"),convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'),convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'))
                ),
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
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
                    ),
                    'siteadminname'=>array(
                        'type'=>'string',
                        'label'=>gt("Administrator name"),
                        'current'=>getGlobalSetting('siteadminname'),
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
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
                            'mail'=>gT("PHP (default)",'unescaped'),'smtp'=>gT("SMTP",'unescaped'),'sendmail'=>gT("Sendmail",'unescaped'),'qmail'=>gT("Qmail",'unescaped'),
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
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
                        'help'=>gT("Enter your hostname and port, e.g.: my.smtp.com:25"),
                    ),
                    'emailsmtpuser'=>array(
                        'type'=>'string',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP username"),
                        'current'=>getGlobalSetting('emailsmtpuser'),
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
                    ),
                    'emailsmtppassword'=>array(
                        'type'=>'password',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP password"),
                        'current'=>getGlobalSetting('emailsmtppassword'),
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
                    ),
                    'emailsmtpssl'=>array(
                        'type'=>'select',
                        'class'=>array(
                            'smtp-on',
                        ),
                        'label'=>gt("SMTP SSL/TLS"),
                        'options'=>array(''=>gT("Off",'unescaped'),'ssl'=>gT("SSL",'unescaped'),'tls'=>gT("TLS",'unescaped')),
                        'current'=>getGlobalSetting('emailsmtpssl'),
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
                    ),
                    'emailsmtpdebug'=>array(
                        'type'=>'select',
                        'label'=>gt("SMTP debug mode"),
                        'options'=>array('0'=>gT("Off",'unescaped'),'1'=>gT("On errors",'unescaped'),'2'=>gT("Always",'unescaped')),
                        'current'=>getGlobalSetting('emailsmtpdebug'),
                        'htmlOptions'=>array(
                            'size'=>'50',
                        ),
                    ),
                    'maxemails'=>array(
                        'type'=>'int',
                        'label'=>gt("Email batch size"),
                        'current'=>getGlobalSetting('maxemails'),
                        'htmlOptions'=>array(
                            'min'=>'1',
                            'style'=>'width:5em',
                        ),
                    ),
                ),
            ));
        ?>
        </div>

        <?php 
            // Bounce settings in one part
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'id'=>'bounce',
                //'title'=>gt("Bounce settings"),
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
                        'options'=>array('off'=>gT("Off",'unescaped'),'IMAP'=>gT("IMAP",'unescaped'),'POP'=>gT("POP",'unescaped')),
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
                        'options'=>array('off'=>gT("Off",'unescaped'),'SSL'=>gT("SSL",'unescaped'),'TLS'=>gT("TLS",'unescaped')),
                        'current'=>getGlobalSetting('bounceencryption'),
                    ),
                )
            ));
        ?>
        <?php
            // Security settings in one part
            if(getGlobalSetting('force_ssl')!='on')
            {
                $sForceSslHelp = CHtml::tag("div",array('class'=>'alert'),sprintf(gT('Warning: Before turning on HTTPS,%s .'),CHtml::link(gt("check if this link works"),App()->createAbsoluteUrl("admin/globalsettings",array(),'https'),array('title'=>gT('Test if your server has SSL enabled by clicking on this link.','unescaped')))))
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
                        'options'=>array('1'=>gT("Yes",'unescaped'),'0'=>gT("No",'unescaped')),
                        'current'=>getGlobalSetting('surveyPreview_require_Auth'),
                    ),
                    'filterxsshtml'=>array(
                        'type'=>'select',
                        'label'=>gt("Filter HTML for XSS").$sStringDemoMode,
                        'labelOptions'=>array(
                            'class'=>$sClassDemoMode,
                        ),
                        'options'=>array('1'=>gT("Yes",'unescaped'),'0'=>gT("No",'unescaped')),
                        'current'=>getGlobalSetting('filterxsshtml'),
                        'htmlOptions'=>array(
                            'readonly'=>$bDemoMode,
                        ),
                        'help'=>gT("XSS filtering is always disabled for the superadministrator.")
                    ),
                    'usercontrolSameGroupPolicy'=>array(
                        'type'=>'select',
                        'label'=>gt("Group member can only see own group"),
                        'options'=>array('1'=>gT("Yes",'unescaped'),'0'=>gT("No",'unescaped')),
                        'current'=>getGlobalSetting('usercontrolSameGroupPolicy'),
                    ),
                    'force_ssl'=>array(
                        'type'=>'select',
                        'label'=>gt("Force HTTPS"),
                        'options'=>array('neither'=>gT("Don't force on or off",'unescaped'),'on'=>gT("On",'unescaped'),'off'=>gT('Off','unescaped')),
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
                        'options'=>array("1"=>gT('Yes','unescaped'),"0"=>gT('No','unescaped'),"2"=>gT('Survey admin can choose','unescaped')),
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
                        'options'=>array('choose'=>gT('Survey admin can choose','unescaped'),'show'=>gT('Yes','unescaped'),'hide'=>gT('No','unescaped')),
                        'current'=>getGlobalSetting('showxquestions'),
                    ),
                    'showgroupinfo'=>array(
                        'type'=>'select',
                        'label'=>gT('Show question group name and/or description'),
                        'options'=>array('choose'=>gT('Survey admin can choose','unescaped'),'both'=>gT('Show both','unescaped'),'name'=>gT('Show group name only','unescaped'),'description'=>gT('Show group description only','unescaped'),'none'=>gT('Hide both','unescaped')),
                        'current'=>getGlobalSetting('showgroupinfo'),
                    ),
                    'showqnumcode'=>array(
                        'type'=>'select',
                        'label'=>gT('Show question number and/or question code'),
                        'options'=>array('choose'=>gT('Survey admin can choose','unescaped'),'both'=>gT('Show both','unescaped'),'number'=>gT('Show question number only','unescaped'),'code'=>gT('Show question code only','unescaped'),'none'=>gT('Hide both','unescaped')),
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
                        'type'=>'select',
                        'label'=>gT("Show header in answers export PDFs?"),
                        'options'=>array(
                            'Y' => gT("Yes",'unescaped'),
                            'N' => gT("No",'unescaped'),
                        ),
                        'current'=>getGlobalSetting('pdfshowheader'),
                    ),
                    'pdflogowidth'=>array(
                        'type'=>'int',
                        'label'=>gT("Width of PDF header logo",'unescaped'),
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
                $aLanguages[$sLanguage]=html_entity_decode($aLanguage['description'], ENT_QUOTES, 'UTF-8')." (".html_entity_decode($aLanguage['nativedescription'], ENT_QUOTES, 'UTF-8').")";
            }
            $aAvailableLang=getLanguageDataRestricted ();
            // Help for restrictToLanguages is a checkbox select all
            $sScriptAllLanguage="$(document).on('click','#restrictToLanguages_select_all',function(e){\n"
                . " if($(this).is(':checked')) { $('#restrictToLanguages > option').attr('selected','selected'); }\n"
                . " else { $('#restrictToLanguages > option').removeAttr('selected'); }\n"
                . " $('#restrictToLanguages').trigger('change');\n"
                . "});\n";
            App()->clientScript->registerScript('sScriptAllLanguage', $sScriptAllLanguage,CClientScript::POS_END);
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
                        'selectOptions'=>array(
                            'minimumResultsForSearch'=>15,
                        ),
                        'current'=>getGlobalSetting('defaultlang'),
                    ),
                    'restrictToLanguages'=>array(
                        'type'=>'select',
                        'label'=>gT("Available languages").$sStringDemoMode,
                        'help'=>CHtml::label(gt("Check/Uncheck All"),"restrictToLanguages_select_all").CHtml::checkBox('restrictToLanguages_select_all',count($aLanguages)==count($aAvailableLang)),
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
                            'options'=>array('off'=>gT("Off",'unescaped'),'json'=>gT("JSON-RPC",'unescaped'),'xml'=>gT("XML-RPC",'unescaped')),
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
                            'options'=>array('0'=>gT("No",'unescaped'),'1'=>gT("Yes",'unescaped')),
                            'current'=>getGlobalSetting('rpc_publish_api'),
                        ),
                    )
                ));
            ?>
        <div class="hidden hide" id="submitglobalbutton">
            <p>
                <?php if(Yii::app()->session['refurl']) { ?>
                <button type="submit" name="action" value='savequit'><?php eT("Save and exit"); ?></button>
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
