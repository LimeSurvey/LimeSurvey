<?php

/**
 * Global setting tab for user administration 
 * 
 * @var $sSendAdminCreationEmail
 * @var $sAdminCreationEmailSubject
 * @var $sAdminCreationEmailTemplate
 */

?>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label" for='sendadmincreationemail'><?php eT("Send email to new user administrators:"); ?></label>
            <div>
                <?php
                $this->widget(
                    'yiiwheels.widgets.switch.WhSwitch',
                    array(
                        'name' => 'sendadmincreationemail',
                        'htmlOptions' => array(
                            'class' => 'custom-data bootstrap-switch-boolean',
                            'uncheckValue' => false,
                        ),

                        'value' => isset($sSendAdminCreationEmail) ? $sSendAdminCreationEmail : 0,
                        'onLabel' => gT('On'),
                        'offLabel' => gT('Off')
                    )
                );
                ?>
            </div>
        </div>
    </div>
</div>
<div class="ls-space margin top-15">
    <div class="row">

        <div class="col-sm-12 col-lg-10">

            <div class="form-group">
                <label class=" control-label" for='admincreationemailsubject'><?php eT("Admin creation email subject"); ?>:</label><br>
                <small id="template help" class="form-text text-muted"><?php eT("Available placeholders") ?>: {SITENAME} </small>
                <div class="">
                    <input class="form-control" type='text' size='50' id='admincreationemailsubject' name='admincreationemailsubject' value="<?php echo htmlspecialchars($sAdminCreationEmailSubject); ?>" />
                </div>
            </div>
            <!-- admin default email template -->
            <div class="form-group">
                <label class=" control-label" for='admincreationemailtemplate'><?php eT("Admin creation email template"); ?>: </label><br>
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
