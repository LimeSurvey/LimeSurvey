<?php

/**
 * Global setting tab for user administration
 *
 * @var $sSendAdminCreationEmail
 * @var $sAdminCreationEmailSubject
 * @var $sAdminCreationEmailTemplate
 */

?>

<div class="container">
    <div class="row">
        <div class="col-6">
            <div class="mb-3">
                <label class="form-label" for='sendadmincreationemail'><?php eT("Send email to new user administrators:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'sendadmincreationemail',
                        'checkedOption' => $sSendAdminCreationEmail ?? 0,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                        'htmlOptions'   => [
                            'class'        => 'custom-data bootstrap-switch-boolean',
                        ]
                    ]); ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for='admincreationemailsubject'><?php eT("Admin creation email subject"); ?>:</label>
                <br/>
                <small id="template help" class="form-text text-muted"><?php eT("Available placeholders") ?>: {SITENAME}</small>
                <input class="form-control" type='text' size='50' id='admincreationemailsubject' name='admincreationemailsubject' value="<?php echo htmlspecialchars((string) $sAdminCreationEmailSubject); ?>" />
            </div>
            <!-- admin default email template -->
            <div class="mb-3">
                <label class=" form-label" for='admincreationemailtemplate'><?php eT("Admin creation email template"); ?>: </label>
                <br/>
                <small id="template help" class="form-text text-muted"><?php eT("Available placeholders") ?>: {SITENAME}, {SITEADMINEMAIL}, {USERNAME}, {FULLNAME}, {LOGINURL} </small>
                <div class="top-15">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("admincreationemailtemplate", $sAdminCreationEmailTemplate, ['class' => 'form-control', 'cols' => '80', 'rows' => '20', 'id' => "admincreationemailtemplate"]); ?>
                        <?php echo getEditor("admincreationemailtemplate", "admincreationemailtemplate", "[" . gT("Admin email template:", "js") . "]"); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
