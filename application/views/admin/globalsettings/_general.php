<?php
/**
 * This view generate the 'general' tab inside global settings.
 *
 */

use LimeSurvey\Libraries\FormExtension\FormExtensionWidget;
use LimeSurvey\Libraries\FormExtension\Inputs\GlobalSettingsRenderer;

?>
<?php
$thisdefaulttheme                = getGlobalSetting('defaulttheme');
$templatenames                   = array_keys(Template::getTemplateList());
$thisadmintheme                  = getGlobalSetting('admintheme');
$thisdefaulthtmleditormode       = getGlobalSetting('defaulthtmleditormode');
$thismaintenancemode             = !empty(getGlobalSetting('maintenancemode')) ? getGlobalSetting('maintenancemode') : 'off';
$thisdefaultquestionselectormode = getGlobalSetting('defaultquestionselectormode');
$thisdefaultthemeteeditormode    = getGlobalSetting('defaultthemeteeditormode');
$dateformatdata                  = getDateFormatData(Yii::app()->session['dateformat']);
?>

<div class="container">
<div class="row">
    <div class="col-6">
        <!-- Global sitename -->
        <div class="mb-3">
            <label class="col-12 form-label" for='sitename'>
                <?php eT("Site name:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='50' id='sitename' name='sitename' value="<?php echo htmlspecialchars((string) getGlobalSetting('sitename')); ?>"/>
            </div>
        </div>

        <!-- Default Template -->
        <div class="mb-3">
            <label class="col-12 form-label" for="defaulttheme">
                <?php eT("Default theme:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <select class="form-select" name="defaulttheme" id="defaulttheme">
                    <?php foreach ($templatenames as $templatename) : ?>
                        <option value='<?php echo CHtml::encode($templatename); ?>' <?php echo ($thisdefaulttheme == $templatename) ? "selected='selected'" : "" ?> >
                            <?php echo CHtml::encode($templatename); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Non-Numerical answer option prefix -->
        <div class="mb-3">
            <label class="col-12 form-label" for='non_numerical_answer_prefix'>
                <?php eT("Non-Numerical answer option prefix:"); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='35' id='non_numerical_answer_prefix' name='non_numerical_answer_prefix' value="<?php echo htmlspecialchars((string) getGlobalSetting('non_numerical_answer_prefix')); ?>"/>
            </div>
        </div>

        <!-- Non-Numerical subquestions prefix -->
        <div class="mb-3">
            <label class="col-12 form-label" for='non_numerical_subquestions_prefix'>
                <?php eT("Non-Numerical subquestions prefix:"); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='35' id='non_numerical_subquestions_prefix' name='non_numerical_subquestions_prefix' value="<?php echo htmlspecialchars((string) getGlobalSetting('non_numerical_subquestions_prefix')); ?>"/>
            </div>
        </div>

        <!-- Autocreate group and question -->
        <div class="mb-3">
            <label class="col-12 form-label" for="createsample">
                <?php eT("Create example question group and question:"); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'createsample',
                    'checkedOption' => App()->getConfig('createsample'),
                    'selectOptions' => [
                        '1' => gT('On'),
                        '0' => gT('Off'),
                    ],
                ]); ?>
            </div>
        </div>

        <!-- Administrative Template -->
        <div class="mb-3">
            <label class="col-12 form-label" for="admintheme">
                <?php eT("Administration theme:"); ?>
            </label>
            <div class="col-12">
                <select class="form-select" name="admintheme" id="admintheme">
                    <?php foreach ($aListOfThemeObjects as $templatename => $templateconfig): ?>
                        <option value='<?php echo CHtml::encode($templatename); ?>' <?php echo ($thisadmintheme == $templatename) ? "selected='selected'" : "" ?> >
                            <?php echo CHtml::encode($templateconfig->metadata->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')): ?>
                <div class="col-12 form-label ">
                    <span class="hint">
                    <?php eT("You can add your custom themes in upload/admintheme"); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Time difference -->
        <div class="mb-3">
            <label class="col-12 form-label" for='timeadjust'>
                <?php eT("Time difference (in hours):"); ?>
            </label>
            <div class="col-md-4">
                    <span>
                        <input class="form-control" type='text' id='timeadjust' name='timeadjust'
                               value="<?php echo htmlspecialchars((string) (str_replace(array('+', ' hours', ' minutes'), array('', '', ''), (string) getGlobalSetting('timeadjust')) / 60)); ?>"/>
                    </span>
            </div>
            <div class="col-md-8">
                <?php echo gT("Server time:") . ' ' . convertDateTimeFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s', $dateformatdata['phpdate'] . ' H:i')
                    . "<br>"
                    . gT("Corrected time:") . ' '
                    . convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')), 'Y-m-d H:i:s', $dateformatdata['phpdate'] . ' H:i'); ?>
            </div>
        </div>

        <?php if (isset(Yii::app()->session->connectionID)): ?>
            <div class="mb-3">
                <label class="col-12 form-label" for='iSessionExpirationTime'>
                    <?php eT("Session lifetime for surveys (seconds):"); ?>
                </label>
                <div class="col-12">
                    <input class="form-control" type='text' size='10' id='iSessionExpirationTime' name='iSessionExpirationTime'
                           value="<?php echo htmlspecialchars((string) getGlobalSetting('iSessionExpirationTime')); ?>"/>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="col-12 form-label" for='ipInfoDbAPIKey'>
                <?php eT("IP Info DB API Key:"); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='35' id='ipInfoDbAPIKey' name='ipInfoDbAPIKey' value="<?php echo htmlspecialchars((string) getGlobalSetting('ipInfoDbAPIKey')); ?>"/>
            </div>
        </div>

        <div class="mb-3">
            <label class="col-12 form-label" for='googleMapsAPIKey'>
                <?php eT("Google Maps API key:"); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='35' id='googleMapsAPIKey' name='googleMapsAPIKey' value="<?php echo htmlspecialchars((string) getGlobalSetting('googleMapsAPIKey')); ?>"/>
            </div>
        </div>

        <div class="mb-3">
            <label class="col-12 form-label" for='googleanalyticsapikey'>
                <?php eT("Google Analytics Tracking ID:"); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='35' id='googleanalyticsapikey' name='googleanalyticsapikey'
                       value="<?php echo htmlspecialchars((string) getGlobalSetting('googleanalyticsapikey')); ?>"/>
            </div>
        </div>

        <div class="mb-3">
            <label class="col-12 form-label" for='googletranslateapikey'>
                <?php eT("Google Translate API key:"); ?>
            </label>
            <div class="col-12">
                <input class="form-control" type='text' size='35' id='googletranslateapikey' name='googletranslateapikey'
                       value="<?php echo htmlspecialchars((string) getGlobalSetting('googletranslateapikey')); ?>"/>
            </div>
        </div>

        <div class="mb-3">
            <label class='col-12 form-label' for='characterset'>
                <?php eT("Character set for file import/export:") ?>
            </label>
            <div class='col-12'>
                <select class='form-select' name='characterset' id='characterset'>
                    <?php foreach ($aEncodings as $code => $charset): ?>
                        <option value='<?php echo $code; ?>'
                            <?php if (array_key_exists($thischaracterset, $aEncodings) && $code == $thischaracterset): ?>
                                selected='selected'
                            <?php elseif (!array_key_exists($thischaracterset, $aEncodings) && $code == "auto"): ?>
                                selected='selected'
                            <?php endif; ?>
                        >
                            <?php echo $charset; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-6">

        <!-- Maintenance mode -->
        <div class="mb-3">
            <label class="col-12 form-label" for="maintenancemode" title="<?php echo gT('Maintenance modes:
Off
Soft lock - participants are able to finish started surveys, no new participants are allowed
Full lock - none of participants are allowed to take survey, even if they already started to take it'
            ); ?> ">
                <?php eT("Maintenance mode:"); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'maintenancemode',
                    'checkedOption' => $thismaintenancemode,
                    'selectOptions' => [
                        "off"  => gT("Off", 'unescaped'),
                        "soft" => gT("Soft lock", 'unescaped'),
                        "hard" => gT("Full lock", 'unescaped')
                    ]
                ]); ?>
            </div>
        </div>

        <!-- Refresh assets -->
        <div class="mb-3">
            <label class="col-12 form-label" for='refreshassets'>
                <?php eT("Clear assets cache:"); ?> <small>(<?php echo getGlobalSetting('customassetversionnumber'); ?>)</small>
            </label>
            <div class="col-12">
                <a href="<?php echo App()->createUrl('admin/globalsettings', array("sa" => "refreshAssets")); ?>"
                   class="btn btn-outline-dark btn-large">
                   	<?php eT("Clear now"); ?>
                </a>
            </div>
        </div>

        <!-- Default Editor mode -->
        <div class="mb-3">
            <label class="col-12 form-label" for='defaulthtmleditormode'>
                <?php eT("Default HTML editor mode:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget',
                    [
                        'name'          => 'defaulthtmleditormode',
                        'checkedOption' => $thisdefaulthtmleditormode,
                        'selectOptions' => [
                            "inline" => gT("Inline", 'unescaped'),
                            "popup"  => gT("Popup", 'unescaped'),
                            "none"   => gT("HTML source", 'unescaped')
                        ]
                    ]); ?>
            </div>
        </div>

        <!-- Side menu behaviour -->
        <?php /* This setting is just remaining here for campatibility reasons. It is not yet implemented into the new admmin panel */ ?>
        <div class="mb-3" style="display: none;">
            <label class='col-12 form-label' for='sideMenuBehaviour'>
                <?php eT("Side-menu behaviour:"); ?>
            </label>
            <div class='col-md-4'>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'sideMenuBehaviour',
                    'checkedOption' => $sideMenuBehaviour,
                    'selectOptions' => [
                        "adaptive"     => gT("Adaptive", 'unescaped'),
                        "alwaysOpen"   => gT("Always open", 'unescaped'),
                        "alwaysClosed" => gT("Always closed", 'unescaped')
                    ]
                ]); ?>
            </div>
        </div>

        <!-- Default question type selector mode -->
        <div class="mb-3">
            <label class="col-12 form-label" for='defaultquestionselectormode'>
                <?php eT("Question type selector:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'defaultquestionselectormode',
                    'checkedOption' => $thisdefaultquestionselectormode,
                    'selectOptions' => [
                        "default" => gT("Full", 'unescaped'),
                        "none"    => gT("Simple", 'unescaped')
                    ]
                ]); ?>
            </div>
        </div>

        <!-- Default theme editor mode -->
        <div class="mb-3">
            <label class="col-12 form-label" for='defaultthemeteeditormode'>
                <?php eT("Template editor:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'defaultthemeteeditormode',
                    'checkedOption' => $thisdefaultthemeteeditormode,
                    'selectOptions' => [
                        "default" => gT("Full", 'unescaped'),
                        "none"    => gT("Simple", 'unescaped')
                    ]
                ]); ?>
            </div>
        </div>

        <!-- Default theme editor mode -->
        <div class="mb-3">
            <label class="col-12 form-label" for='javascriptdebugbcknd'>
                <?php eT("JS-Debug mode [Backend]:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'javascriptdebugbcknd',
                    'checkedOption' => App()->getConfig('javascriptdebugbcknd'),
                    'selectOptions' => [
                        '1' => gT('On'),
                        '0' => gT('Off'),
                    ],
                ]); ?>
            </div>
        </div>

        <!-- Default theme editor mode -->
        <div class="mb-3">
            <label class="col-12 form-label" for='javascriptdebugfrntnd'>
                <?php eT("JS-Debug mode [Frontend]:");
                echo((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
            </label>
            <div class="col-12">
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'javascriptdebugfrntnd',
                    'checkedOption' => App()->getConfig('javascriptdebugfrntnd'),
                    'selectOptions' => [
                        '1' => gT('On'),
                        '0' => gT('Off'),
                    ],
                ]); ?>
            </div>
        </div>

        <!-- Allow unstable extension updates (only visible for super admin)-->
        <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')): ?>
            <div class="mb-3">
                <label class="col-12 form-label" for='allow_unstable_extension_update'>
                    <?php eT('Allow unstable extension updates:'); ?>
                </label>
                <div class="col-12">
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name' => 'allow_unstable_extension_update',
                        'checkedOption' => App()->getConfig('allow_unstable_extension_update'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                    ]); ?>
                </div>
                <div class="col-12 form-label ">
                        <span class="hint">
                            <?php eT("Enabling unstable updates will allow you to try alpha and beta versions of extensions. Talk to the extension author for more information."); ?>
                        </span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="ls-flex-column ls-space padding left-5 right-5 col-md-7">
        <?= FormExtensionWidget::render(
            App()->formExtensionService->getAll('globalsettings.general'),
            new GlobalSettingsRenderer()
        ); ?>
    </div>

</div>
</div>

<?php if (Yii::app()->getConfig("demoMode") == true): ?>
    <p>
        <?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?>
    </p>
<?php endif; ?>
