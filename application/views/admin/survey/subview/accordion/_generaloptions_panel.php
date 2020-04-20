<?php
/**
* General options
 * @var AdminController $this
 * @var Survey $oSurvey
*/
    $scriptVarsNeccessary = "
        var jsonUrl = '';
        var sAction = '';
        var sParameter = '';
        var sTargetQuestion = '';
        var sNoParametersDefined = '';
        var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
        var sURLParameters = '';
        var sAddParam = '';
    ";
    Yii::app()->getClientScript()->registerScript(
        "GeneralOption-variables",
        $scriptVarsNeccessary,
        LSYii_ClientScript::POS_BEGIN
    );
?>
<!-- General options -->
    <?php
    $yii = Yii::app();
    $controller = $yii->getController();
    $action = 'editsurveysettings';

    $formatSelectOptions = array(
        'S' => gT('Question by Question','unescaped'),
        'G' => gT('Group by Group','unescaped'),
        'A' => gT('All in one','unescaped')
    );
    if ($bShowInherited){
        $formatSelectOptions['I'] = gT('Inherit','unescaped').' ['. $oSurveyOptions->format . ']';
    }
?>
<?php if($action=='editsurveysettings'):?>
    <?php
    $sConfirmLanguageScript="
        $(document).on('submit','#globalsetting',function(){
        if(!ConfirmLanguageChange('".gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js")."')){
            return false;
        }
        });

        function ConfirmLanguageChange(confirmtxt) {

            if ($('#oldlanguages').val().trim()=='') {
                mylangs= [];
            } else {
                mylangs=$('#oldlanguages').val().split(' ');
            }

            if (checkSelect2Languages(mylangs)) {
                return true;
            } else {
                return confirm(confirmtxt);
            }
        }

        function checkSelect2Languages(mylangs) {
            newLanguages=$('#additional_languages').val();
            for (x = 0; x < mylangs.length; x++) {
                if ($.inArray(mylangs[x],newLanguages)==-1) {
                    return false;
                }
            }
            return true;
        }
    ";
    Yii::app()->getClientScript()->registerScript( "GeneralOption-confirm-language", $sConfirmLanguageScript, LSYii_ClientScript::POS_POSTSCRIPT) ;
    // var_dump($owner);
    ?>

<!-- Container -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <?php if ($bShowAllOptions === true){ ?>
                <?php if(!$oSurvey->isNewRecord):?>
                <!-- Base language -->
                <div class="form-group">
                    <label class=" control-label" ><?php  eT("Base language:") ; ?></label>
                    <div class="" style="padding-top: 7px;">
                        <?php if($oSurvey->isNewRecord):?>
                        <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                            'asDropDownList' => true,
                            'data' => getLanguageDataRestricted (false,'short'),
                            'value' => $oSurvey->language,
                            'name' => 'language',
                            'pluginOptions' => array()
                        ));?>
                        <?php else:?>
                        <?php echo getLanguageNameFromCode($oSurvey->language,false); ?>
                        <?php endif;?>
                    </div>
                </div>
                <!-- Additional Languages -->
                <div class="form-group">
                    <label class=" control-label"  for='additional_languages'><?php  eT("Additional Languages"); ?>:</label>
                    <div class="">
                        <?php
                        $aAllLanguages=getLanguageDataRestricted (false,'short');
                        unset($aAllLanguages[$oSurvey->language]);

                        Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2', array(
                            'asDropDownList' => true,
                            'htmlOptions'=>array('multiple'=>'multiple','style'=>"width: 100%"),
                            'data' => $aAllLanguages,
                            'value' =>  $oSurvey->additionalLanguages,
                            'name' => 'additional_languages',
                            'pluginOptions' => array(
                                'placeholder' => gt('Select additional languages','unescaped'),
                        )));
                        ?>
                        <input type='hidden' name='oldlanguages' id='oldlanguages' value='<?php echo implode(' ', $oSurvey->additionalLanguages); ?>'>
                    </div>
                </div>
                <?php endif;?>
            <?php } ?>
            <!-- Survey owner -->
            <?php
            if (isset($oSurvey->owner_id) && Yii::app()->session['loginID']==$oSurvey->owner_id || Permission::model()->hasGlobalPermission('superadmin','read')):?>
                <div class="form-group">
                    <label class=" control-label"  for='owner_id'><?php  eT("Survey owner:"); ?></label>
                    <div class=""><?php
                        Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2', array(
                            'asDropDownList' => true,
                            'htmlOptions'=>array(
                                'style'=>'width:100%;'
                            ),
                            'data' => isset($users) ?  $users : [],
                            'value' => $oSurvey->owner_id,
                            'name' => 'owner_id',
                            'pluginOptions' => array(
                            )
                        ));
                        ?>
                    </div>
                </div>
                <?php endif;?>

            <!-- Administrator -->
            <div class="form-group">
                <?php //Switch for creation/editing ?>
                <?php $admin = $oSurvey->admin; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                        <label class=" control-label"  for='admin'><?php  eT("Administrator:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $admin === 'inherit' ? 'hide' : 'show'); ?>" type='text' size='50' id='admin' name='admin' value="<?php echo htmlspecialchars($admin); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $admin; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $admin === 'inherit' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->admin); ?>" readonly />
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                        <label class=" control-label content-center col-sm-12"  for='admin'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'adminbutton',
                            'value'=> ($bShowInherited && $admin === 'inherit' ? 'Y' : 'N'),
                            'selectOptions'=>$optionsOnOff,
                            'htmlOptions' => array(
                                'class' => 'text-option-inherit'
                                )
                            ));
                            ?>
                    </div>
                </div>
            </div>

            <!-- Admin email -->
            <div class="form-group">
                <?php //Switch for creation/editing ?>
                <?php $adminemail = $oSurvey->adminemail; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                        <label class=" control-label"  for='adminemail'><?php  eT("Administrator email address:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $adminemail === 'inherit' ? 'hide' : 'show'); ?>" type='<?php echo ($bShowInherited)?'text':'email'; ?>' size='50' id='adminemail' name='adminemail' value="<?php echo htmlspecialchars($adminemail); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $adminemail; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $adminemail === 'inherit' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->adminemail); ?>" readonly />
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                        <label class=" control-label content-center col-sm-12"  for='adminemail'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'adminemailbutton',
                            'value'=> ($bShowInherited && $adminemail === 'inherit' ? 'Y' : 'N'),
                            'selectOptions'=>$optionsOnOff,
                            'htmlOptions' => array(
                                'class' => 'text-option-inherit'
                                )
                            ));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Bounce email -->
            <div class="form-group">
                <?php //Switch for creation/editing ?>
                <?php $bounce_email = $oSurvey->bounce_email; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                        <label class=" control-label"  for='bounce_email'><?php  eT("Bounce email address:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $bounce_email === 'inherit' ? 'hide' : 'show'); ?>" type='<?php echo ($bShowInherited)?'text':'email'; ?>' size='50' id='bounce_email' name='bounce_email' value="<?php echo htmlspecialchars($bounce_email); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $bounce_email; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $bounce_email === 'inherit' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->bounce_email); ?>" readonly />
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                        <label class=" control-label content-center col-sm-12"  for='bounce_email'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'bounce_emailbutton',
                            'value'=> ($bShowInherited && $bounce_email === 'inherit' ? 'Y' : 'N'),
                            'selectOptions'=>$optionsOnOff,
                            'htmlOptions' => array(
                                'class' => 'text-option-inherit '
                                )
                            ));
                        ?>
                    </div>
                </div>
            </div>

            <?php if ($bShowAllOptions === true){ ?>
                <!-- Fax to -->
                <div class="form-group">
                    <label class=" control-label"  for='faxto'><?php  eT("Fax to:"); ?></label>
                    <div class="">
                        <?php echo CHtml::textField('faxto',$oSurvey->faxto,array(
                            'class' => 'form-control',
                            'id' => 'faxto',
                            'size' => '20',
                            'maxlength' => '20',
                        )); ?>
                    </div>
                </div>
            <?php } ?>


            <?php else: ?>
            <!-- End URL -->
            <div class="form-group">
                <label class=" control-label" for='url'><?php  eT("End URL:"); ?></label>
                <div class="">
                    <input type='text' class="form-control"  id='url' name='url' placeholder="http://example.com" />
                </div>
            </div>

            <!-- URL description -->
            <div class="form-group">
                <label class=" control-label" for='urldescrip'><?php  eT("URL description:") ; ?></label>
                <div class="">
                    <input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value=''  class="form-control"  placeholder="<?php eT('Some description text');?>" />
                </div>
            </div>

            <!-- Date format -->
            <div class="form-group">
                <label class=" control-label" for='dateformat'><?php  eT("Date format:") ; ?></label>
                <div class="">
                    <?php echo CHtml::listBox('dateformat',$sDateFormatDefault, $aDateFormatData, array('id'=>'dateformat','size'=>'1', 'class'=>'form-control')); ?>
                </div>
            </div>

            <!-- Decimal mark -->
            <div class="form-group">
                <label class=" control-label" for='numberformat'><?php  eT("Decimal mark:"); ?></label>
                <div class="">
                    <?php echo CHtml::listBox('numberformat',$sRadixDefault, $aRadixPointData, array('id'=>'numberformat','size'=>'1', 'class'=>'form-control')); ?>
                </div>
            </div>

            <!-- Administrator -->
            <div class="form-group">
                <label class=" control-label" for='admin'><?php  eT("Administrator:") ; ?></label>
                <div class="">
                    <input type='text' size='50' id='admin' name='admin'   class="form-control"  value='<?php echo $owner['full_name'] ; ?>' />
                </div>
            </div>

            <!-- Admin email -->
            <div class="form-group">
                <label class=" control-label" for='adminemail'><?php  eT("Administrator email address:") ; ?></label>
                <div class="">
                    <input type='email' size='30'   class="form-control"   id='adminemail' name='adminemail' value='<?php echo $owner['email'] ; ?>' />
                </div>
            </div>

            <!-- Bounce Email -->
            <div class="form-group">
                <label class=" control-label" for='bounce_email'><?php  eT("Bounce email address:") ; ?></label>
                <div class="">
                    <input type='email' size='50'  class="form-control"  id='bounce_email' name='bounce_email' value='<?php echo $owner['bounce_email'] ; ?>' />
                </div>
            </div>
            <?php endif;?>
    </div>
    <div class="col-md-6 col-sm-12">

        <!-- Survey Group -->
        <?php if ($bShowAllOptions === true){ ?>
            <div class="form-group">
                <label class=" control-label" for='gsid'><?php  eT("Group:"); ?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                        'asDropDownList' => true,
                        'htmlOptions'=>array('style'=>"width: 100%"),
                        'data' => isset($aSurveyGroupList) ?  $aSurveyGroupList : [],
                        'value' => $oSurvey->gsid,
                        'name' => 'gsid',
                        'pluginOptions' => array()
                    ));?>
                </div>
            </div>
            <?php } ?>

        <!-- Format -->
        <div class="form-group">
            <label class=" control-label" for='format'><?php  eT("Format:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'format',
                    'value'=> $oSurvey->format,
                    'selectOptions'=>$formatSelectOptions,
                ));?>
            </div>
        </div>
        <!-- Template -->

        <div class="form-group">
            <label class=" control-label" for='template'><?php  eT("Theme:"); ?></label>
            <div class="">
                <select id='template' class="form-control"  name='template' data-updateurl='<?php echo App()->createUrl('/admin/themeoptions/sa/getpreviewtag') ?>' data-inherit-template-name='<?php echo $oSurveyOptions->template?>'>
                    <?php if ($bShowInherited){ ?>
                        <option value="inherit" <?php echo ($oSurvey->template == 'inherit')?'selected="selected"':''; ?>><?php echo eT('Inherit').' ['. $oSurveyOptions->template . ']'; ?></option>
                    <?php } ?>
                    <?php
                    $aTemplateList = Template::getTemplateListWithPreviews();
                    foreach ($aTemplateList as $templateName => $preview) {
                        if (Permission::model()->hasGlobalPermission('templates','read') || Permission::model()->hasTemplatePermission($templateName) || $oSurvey->template==htmlspecialchars($templateName) ) { ?>
                            <option value='<?php echo $templateName; ?>'
                                <?php if ($oSurvey->template && htmlspecialchars($templateName) == $oSurvey->template && $oSurvey->template != 'inherit') { ?>
                                    selected='selected'
                                <?php   } elseif (!$oSurvey->template && $templateName == getGlobalSetting('defaulttheme') && $oSurvey->template != 'inherit') { ?>
                                selected='selected'
                                <?php } ?>
                                ><?php echo $templateName; ?></option>
                            <?php } ?>

                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-offset-3 template-img" style="margin-top: 13px;" id="preview-image-container">
                <?php echo TemplateConfiguration::getInstanceFromTemplateName(($oSurvey->template == 'inherit') ? $oSurveyOptions->template : $oSurvey->template)->getPreview() ?>
            </div>
        </div>
<?php
    /* @todo : add a js var with standard template list name */
    //$aStandardTemplateLis=Template::getStandardTemplateList();
?>

        </div>
    </div>
</div>