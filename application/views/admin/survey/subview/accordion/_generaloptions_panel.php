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
        var sAdminEmailAddressNeeded = '" . gT("If you are using surveys with a closed participant group or notifications emails you need to set an administrator email address.", 'js') . "'
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
    'S' => gT('Question by Question', 'unescaped'),
    'G' => gT('Group by Group', 'unescaped'),
    'A' => gT('All in one', 'unescaped')
);
$bGlobalSettings = !empty($this->currentModuleAction) && $this->currentModuleAction == 'globalsettings';
if ($bShowInherited) {
    $formatSelectOptions['I'] = $oSurveyOptions->format . " ᴵ";
}
?>
<?php if ($action == 'editsurveysettings'): ?>
<?php
$sConfirmLanguageScript = "
        $(document).on('submit','#globalsetting',function(){
        if(!ConfirmLanguageChange('" . gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js") . "')){
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
Yii::app()->getClientScript()->registerScript("GeneralOption-confirm-language", $sConfirmLanguageScript, LSYii_ClientScript::POS_POSTSCRIPT);
// var_dump($owner);
?>

<!-- Container -->
<div class="row">
    <div class="col-lg-6 col-12">
        <?php if ($bShowAllOptions === true) { ?>
            <?php if (!$oSurvey->isNewRecord): ?>
                <!-- Survey Languages -->
                <div class="mb-3">
                    <label class=" form-label" for='additional_languages'><?php eT("Survey languages:"); ?></label>
                    <div class="">
                        <?php
                        $aAllLanguages = getLanguageDataRestricted(false, 'short');

                        Yii::app()->getController()->widget('ext.admin.survey.LanguagesWidget.LanguagesWidget',
                            array(
                                'asDropDownList' => true,
                                'htmlOptions' => array('multiple' => 'multiple', 'style' => "width: 100%", 'class' => 'form-select'),
                                'data' => $aAllLanguages,
                                'value' => $oSurvey->allLanguages,
                                'name' => 'additional_languages',
                                'pluginOptions' => array(
                                    'placeholder' => gT('Select additional languages', 'unescaped'),
                                    'baselanguage' => $oSurvey->language,
                                    'baselanguage_selector' => '#language',
                                )
                            )
                        );
                        ?>
                        <input type='hidden' name='oldlanguages' id='oldlanguages' value='<?php echo implode(' ', $oSurvey->additionalLanguages); ?>'>
                    </div>
                </div>
                <!-- Base language -->
                <div class="mb-3">
                    <label class=" form-label"><?php eT("Base language:"); ?></label>
                    <div class="">
                        <?php $this->widget('yiiwheels.widgets.select2.WhSelect2',
                            array(
                                'asDropDownList' => true,
                                'htmlOptions' => array('style' => "width: 100%"),
                                'data' => array_intersect_key($aAllLanguages, array_flip($oSurvey->allLanguages)),
                                'value' => $oSurvey->language,
                                'name' => 'language',
                                'pluginOptions' => array()
                            )
                        ); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php } ?>
        <!-- Survey owner -->
        <?php
        if (isset($oSurvey->owner_id) && Yii::app()->session['loginID'] == $oSurvey->owner_id || Permission::model()->hasGlobalPermission('superadmin', 'read')):?>
            <div class="mb-3">
                <label class=" form-label" for='owner_id'><?php eT("Survey owner:"); ?></label>
                <div class=""><?php
                    Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2',
                        array(
                            'asDropDownList' => true,
                            'htmlOptions' => array(
                                'style' => 'width:100%;'
                            ),
                            'data' => isset($users) ? $users : [],
                            'value' => $oSurvey->owner_id,
                            'name' => 'owner_id',
                            'pluginOptions' => array()
                        )
                    );
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Administrator -->
        <div class="mb-3">
            <?php //Switch for creation/editing ?>
            <?php $admin = $oSurvey->admin; ?>
            <div class="row">
                <div class="col-12 col-lg-8 content-right">
                    <label class=" form-label" for='admin'><?php eT("Administrator:"); ?></label>
                    <input class="form-control inherit-edit <?php echo($bShowInherited && $admin === 'inherit' ? 'd-none' : 'd-block'); ?>" type='text' size='50' id='admin' name='admin'
                           value="<?php echo htmlspecialchars((string) $admin); ?>" data-inherit-value="inherit" data-saved-value="<?php echo htmlspecialchars((string) $admin); ?>"/>
                    <input class="form-control inherit-readonly <?php echo($bShowInherited && $admin === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50'
                           value="<?php echo htmlspecialchars((string) $oSurveyOptions->admin); ?>" readonly/>
                </div>
                <div class="col-12 col-lg-4 content-right <?php echo($bShowInherited ? 'd-block' : 'd-none'); ?>">
                    <label class=" form-label content-center col-12" for='admin'><?php eT("Inherit:"); ?></label>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'adminbutton',
                        'checkedOption' => ($bShowInherited && $admin === 'inherit' ? 'Y' : 'N'),
                        'selectOptions' => $optionsOnOff,
                        'htmlOptions'   => [
                            'class' => 'text-option-inherit'
                        ]
                    ]); ?>
                </div>
            </div>
        </div>

        <!-- Admin email -->
        <div class="mb-3">
            <?php //Switch for creation/editing ?>
            <?php $adminemail = $oSurvey->adminemail; ?>
            <div class="row">
                <div class="col-12 col-lg-8 content-right">
                    <label class=" form-label" for='adminemail'><?php eT("Administrator email address:"); ?></label>
                    <input class="form-control inherit-edit <?php echo($bShowInherited && $adminemail === 'inherit' ? 'd-none' : 'd-block'); ?>" type='<?php echo ($bShowInherited) ? 'text' : 'email'; ?>'
                           size='50' id='adminemail' name='adminemail' value="<?php echo htmlspecialchars((string) $adminemail); ?>" data-inherit-value="inherit" data-saved-value="<?php echo htmlspecialchars((string) $adminemail); ?>"/>
                    <input class="form-control inherit-readonly <?php echo($bShowInherited && $adminemail === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50'
                           value="<?php echo htmlspecialchars((string) $oSurveyOptions->adminemail); ?>" readonly/>
                </div>
                <div class="col-12 col-lg-4 content-right <?php echo($bShowInherited ? 'd-block' : 'd-none'); ?>">
                    <label class=" form-label content-center col-12" for='adminemail'><?php eT("Inherit:"); ?></label>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'adminemailbutton',
                        'checkedOption' => ($bShowInherited && $adminemail === 'inherit' ? 'Y' : 'N'),
                        'selectOptions' => $optionsOnOff,
                        'htmlOptions'   => [
                            'class' => 'text-option-inherit'
                        ]
                    ]); ?>
                </div>
            </div>
        </div>

        <!-- Bounce email -->
        <div class="mb-3">
            <?php //Switch for creation/editing ?>
            <?php $bounce_email = $oSurvey->bounce_email; ?>
            <div class="row">
                <div class="col-12 col-lg-8 content-right">
                    <label class=" form-label" for='bounce_email'><?php eT("Bounce email address:"); ?></label>
                    <input class="form-control inherit-edit <?php echo($bShowInherited && $bounce_email === 'inherit' ? 'd-none' : 'd-block'); ?>"
                           type='<?php echo ($bShowInherited) ? 'text' : 'email'; ?>' size='50' id='bounce_email' name='bounce_email' value="<?php echo htmlspecialchars((string) $bounce_email); ?>"
                           data-inherit-value="inherit" data-saved-value="<?php echo htmlspecialchars((string) $bounce_email); ?>"/>
                    <input class="form-control inherit-readonly <?php echo($bShowInherited && $bounce_email === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50'
                           value="<?php echo htmlspecialchars((string) $oSurveyOptions->bounce_email); ?>" readonly/>
                </div>
                <div class="col-12 col-lg-4 content-right <?php echo($bShowInherited ? 'd-block' : 'd-none'); ?>">
                    <label class=" form-label content-center col-12" for='bounce_email'><?php eT("Inherit:"); ?></label>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'bounce_emailbutton',
                        'checkedOption' => ($bShowInherited && $bounce_email === 'inherit' ? 'Y' : 'N'),
                        'selectOptions' => $optionsOnOff,
                        'htmlOptions'   => [
                            'class' => 'text-option-inherit '
                        ]
                    ]); ?>
                </div>
            </div>
        </div>

            <?php else: ?>
            <!-- End URL -->
            <div class="mb-3">
                <label class=" form-label" for='url'><?php eT("End URL:"); ?></label>
                <div class="">
                    <input type='text' class="form-control" id='url' name='url' placeholder="http://example.com"/>
                </div>
            </div>

            <!-- URL description -->
            <div class="mb-3">
                <label class=" form-label" for='urldescrip'><?php eT("URL description:"); ?></label>
                <div class="">
                    <input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='' class="form-control" placeholder="<?php eT('Some description text'); ?>"/>
                </div>
            </div>

            <!-- Date format -->
            <div class="mb-3">
                <label class=" form-label" for='dateformat'><?php eT("Date format:"); ?></label>
                <div class="">
                    <?php echo CHtml::listBox('dateformat', $sDateFormatDefault, $aDateFormatData, array('id' => 'dateformat', 'size' => '1', 'class' => 'form-control')); ?>
                </div>
            </div>

            <!-- Decimal mark -->
            <div class="mb-3">
                <label class=" form-label" for='numberformat'><?php eT("Decimal mark:"); ?></label>
                <div class="">
                    <?php echo CHtml::listBox('numberformat', $sRadixDefault, $aRadixPointData, array('id' => 'numberformat', 'size' => '1', 'class' => 'form-control')); ?>
                </div>
            </div>

            <!-- Administrator -->
            <div class="mb-3">
                <label class=" form-label" for='admin'><?php eT("Administrator:"); ?></label>
                <div class="">
                    <input type='text' size='50' id='admin' name='admin' class="form-control" value='<?php echo $owner['full_name']; ?>'/>
                </div>
            </div>

            <!-- Admin email -->
            <div class="mb-3">
                <label class=" form-label" for='adminemail'><?php eT("Administrator email address:"); ?></label>
                <div class="">
                    <input type='email' size='30' class="form-control" id='adminemail' name='adminemail' value='<?php echo $owner['email']; ?>'/>
                </div>
            </div>

            <!-- Bounce Email -->
            <div class="mb-3">
                <label class=" form-label" for='bounce_email'><?php eT("Bounce email address:"); ?></label>
                <div class="">
                    <input type='email' size='50' class="form-control" id='bounce_email' name='bounce_email' value='<?php echo $owner['bounce_email']; ?>'/>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-6 col-12">

        <!-- Survey Group -->
        <?php if ($bShowAllOptions === true) { ?>
            <div class="mb-3">
                <label class=" form-label" for='gsid'><?php eT("Group:"); ?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.select2.WhSelect2',
                        array(
                            'asDropDownList' => true,
                            'htmlOptions' => array('style' => "width: 100%"),
                            'data' => isset($aSurveyGroupList) ? $aSurveyGroupList : [],
                            'value' => $oSurvey->gsid,
                            'name' => 'gsid',
                            'pluginOptions' => array()
                        )
                    ); ?>
                </div>
            </div>
        <?php } ?>

        <!-- Format -->
        <div class="mb-3">
            <label class=" form-label" for='format'><?php eT("Format:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'format',
                    'checkedOption'         => $oSurvey->format,
                    'selectOptions' => $formatSelectOptions,
                    'ariaLabel'    => gT(' format'),
                ]); ?>
            </div>
        </div>
        <?php
            $themeConf = TemplateConfiguration::getInstanceFromTemplateName(($oSurvey->template === 'inherit') ? $oSurveyOptions->template : $oSurvey->template);
            $inheritedThemeName = $oSurvey->oOptions->template;
        ?>
        <!-- Theme -->
        <div class="mb-3" >
            <label class=" form-label" for='template'><?php eT("Theme:"); ?></label>
            <div class="">
                <select id='template' style="width:100%;" class="form-select activate-search" name='template' data-updateurl='<?php echo App()->createUrl('themeOptions/getPreviewTag') ?>'
                        data-inherit-template-name='<?= $themeConf->template_name ?>'>
                    <?php if ($bShowInherited || $bGlobalSettings) : ?>
                        <option value="inherit" <?= ($oSurvey->template == 'inherit') ? 'selected="selected"' : ''; ?>>
                            <?= gT('Inherit') . ' [' . CHtml::encode($inheritedThemeName) . ']' ?>
                        </option>
                    <?php endif; ?>
                    <?php
                    $aTemplateList = Template::getTemplateList();
                    foreach ($aTemplateList as $templateName => $folder) {
                        if (Permission::model()->hasGlobalPermission('templates', 'read') || Permission::model()->hasTemplatePermission($templateName
                            ) || $oSurvey->template == htmlspecialchars((string) $templateName)) { ?>
                            <option value='<?php echo CHtml::encode($templateName); ?>'
                                <?php if ($oSurvey->template && htmlspecialchars((string) $templateName) === $themeConf->template_name && $oSurvey->template !== 'inherit') { ?>
                                    selected='selected'
                                <?php } elseif (!$oSurvey->template && $templateName === App()->getConfig('defaulttheme') && $oSurvey->template !== 'inherit') { ?>
                                    selected='selected'
                                <?php } ?>
                            ><?php echo CHtml::encode($templateName); ?></option>
                        <?php } ?>

                    <?php } ?>
                </select>
            </div>
            <div class="col-md-6 mt-4 w-100" id="preview-image-container">
                <?php echo $themeConf->getPreview() ?>
            </div>
        </div>
        <?php
        /* @todo : add a js var with standard template list name */

        //$aStandardTemplateLis=Template::getStandardTemplateList();
        ?>

    </div>
</div>
<?php $this->renderPartial('/surveyAdministration/_inherit_sub_footer'); ?>
