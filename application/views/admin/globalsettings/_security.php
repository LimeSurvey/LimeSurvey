<?php

/**
 * This view generate the 'security' tab inside global settings.
 *
 */

?>
<div class="container">
    <div class="row">
        <div class="col-6">
            <div class="mb-3">

                <label class=" form-label" for='surveyPreview_require_Auth'><?php eT("Survey preview only for administration users:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'surveyPreview_require_Auth',
                        'checkedOption' => App()->getConfig('surveyPreview_require_Auth'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='filterxsshtml'><?php eT("Filter HTML for XSS:");
                                                                echo ((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'filterxsshtml',
                        'checkedOption' => App()->getConfig('filterxsshtml'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
                <div class="help-block mt-1">
                    <?php
                    App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT("Note: XSS filtering is always disabled for the superadministrator."),
                        'type' => 'success',
                    ]);
                    ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='disablescriptwithxss'><?php eT("Disable question script for XSS restricted user:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'disablescriptwithxss',
                        'checkedOption' => App()->getConfig('disablescriptwithxss'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
                <div class="help-block mt-1">
                    <?php
                    App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("If you disable this option : user with XSS restriction still can add script. This allows user to add cross-site scripting javascript system."),
                    'type' => 'warning',
                    ]);
                    ?>
                </div>
            </div>
            <div class="mb-3">
                <label class=" form-label" for='usercontrolSameGroupPolicy'>
                    <?php eT("Group member can only see own group:"); ?>
                </label>
                <div class="">
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'usercontrolSameGroupPolicy',
                        'id'            => 'usercontrolSameGroupPolicy',
                        'checkedOption' => App()->getConfig('usercontrolSameGroupPolicy'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>
            <div class="mb-3">
                <label class=" form-label" for="x_frame_options">
                    <?php if (Yii::app()->getConfig("demoMode") == true) { ?>
                        <span class="text-danger asterisk"></span>
                    <?php }; ?>
                    <?php eT('IFrame embedding allowed:');
                    echo ((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'x_frame_options',
                        'checkedOption' => Yii::app()->getConfig('x_frame_options'),
                        'selectOptions' => [
                            "allow"      => gT("Allow", 'unescaped'),
                            "sameorigin" => gT("Same origin", 'unescaped')
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for="force_ssl">
                    <?php if (Yii::app()->getConfig("demoMode") == true) { ?>
                        <span class="text-danger asterisk"></span>
                    <?php }; ?>
                    <?php eT('Force HTTPS:');
                    echo ((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'force_ssl',
                        'checkedOption' => App()->getConfig('force_ssl'),
                        'selectOptions' => [
                            "on"  => gT("On", 'unescaped'),
                            "off" => gT("Off", 'unescaped')
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <span style='font-size:1em;'><?php echo sprintf(
                                                    gT('%sWarning:%s Before turning on HTTPS,%s check this link.%s'),
                                                    '<b>',
                                                    '</b>',
                                                    '<a href="https://' . $_SERVER['HTTP_HOST'] . $this->createUrl("admin/globalsettings/sa") . '" title="' . gT('Test if your server has SSL enabled by clicking on this link.') . '">',
                                                    '</a>'
                                                )
                                                    . '<br/> '
                                                    . gT("If the link does not work and you turn on HTTPS, you will not be able to access and use your LimeSurvey application!"); ?></span>
            </div>
        </div>
        <div class="clearfix"></div>

        <!-- Brute-force for admin -->
        <div class="col-6">

            <div class="">
                <h3><?= gT('Brute-force protection for administration'); ?></h3>

                <div class="mb-3">
                    <label class="form-label" for='loginIpWhitelist'>
                        <?php eT("IP allowlist:"); ?>
                    </label>
                    <textarea class="form-control" id='loginIpWhitelist' name='loginIpWhitelist'><?php echo htmlspecialchars((string) Yii::app()->getConfig('loginIpWhitelist')); ?></textarea>
                    <div class='form-text'><?php eT("List of IP addresses to exclude from the maximum login attempts check. Separate each IP address with a comma or a new line."); ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for='maxLoginAttempt'>
                        <?php eT("Maximum number of attempts:"); ?>
                    </label>
                    <div class="">
                        <input class="form-control" type="number" min="1" step="1" pattern="^\d*$" name="maxLoginAttempt" placeholder="<?= gT("Disabled") ?>"
                            value="<?= App()->getConfig('maxLoginAttempt') !== "" ? intval(App()->getConfig('maxLoginAttempt')) : "" ?>"
                        />
                        <div class="form-text"><?= gT("Set an empty value to disable brute force protection. Number of attempts are never checked.") ?></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for='timeOutTime'>
                        <?php eT("Lockout time in seconds (after maximum number of attempts):"); ?>
                    </label>
                    <div class="">
                        <input class="form-control" type="number" min="0" step="1" pattern="^\d*$" name="timeOutTime" placeholder="<?= gT("Disabled") ?>"
                            value="<?= App()->getConfig('timeOutTime') !== "" ? intval(App()->getConfig('timeOutTime')) : "" ?>"
                        />
                        <div class="form-text"><?= gT("Set an empty value or 0 to disable brute force protection. Number of attempts are deleted each time.") ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brute-force for participant -->
        <div class="col-6">
            <h3><?= gT('Brute-force protection for survey participation') ?></h3>

            <div class="mb-3">
                <label class="form-label" for='tokenIpWhitelist'>
                    <?php eT("IP allowlist:"); ?>
                </label>
                <textarea class="form-control" id='tokenIpWhitelist' name='tokenIpWhitelist'><?php echo htmlspecialchars((string) Yii::app()->getConfig('tokenIpWhitelist')); ?></textarea>
                <span class='form-text'>
                    <?php eT("List of IP addresses to exclude from the maximum token validation attempts check. Separate each IP address with a comma or a new line."); ?>
                </span>
            </div>

            <div class="mb-3">
                <label class="form-label" for='maxLoginAttemptParticipants'>
                    <?php eT("Maximum number of attempts:"); ?>
                </label>
                <div class="">
                    <input class="form-control" type="number" min="1" step="1" pattern="^\d*$" name="maxLoginAttemptParticipants" placeholder="<?= gT("Disabled") ?>"
                        value="<?= App()->getConfig('maxLoginAttemptParticipants') !== "" ? intval(App()->getConfig('maxLoginAttemptParticipants')) : "" ?>"
                    />
                    <div class="form-text"><?= gT("Set an empty value to disable brute force protection. Number of attempts are never checked.") ?></div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for='timeOutParticipants'>
                    <?php eT("Lockout time in seconds (after maximum number of attempts):"); ?>
                </label>
                <div class="">
                    <input class="form-control" type="number" min="0" step="1" pattern="^\d*$" name="timeOutParticipants" placeholder="<?= gT("Disabled") ?>"
                        value="<?= App()->getConfig('timeOutParticipants') !== "" ? intval(App()->getConfig('timeOutParticipants')) : "" ?>"
                    />
                    <div class="form-text"><?= gT("Set an empty value or 0 to disable brute force protection. Number of attempts are deleted each time.") ?></div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for='tokenIpWhitelist'>
                    <?php eT("Reset failed login attempts of participants to make survey accessible again:"); ?>
                </label>
                <div class="">
                    <a class='btn btn-large btn-warning' type="button" href='<?= \Yii::app()->createUrl('admin/globalsettings', ["sa" => "resetFailedLoginParticipants"]) ?>'>
                        <?php eT("Reset participant attempts"); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode") == true) : ?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
