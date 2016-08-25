<form id='cintlink-login-form' class='form-horizontal'>

    <div class='col-sm-4'></div>
    <div class='col-sm-6'>
        <div class='alert alert-info'>
            <div class='row'>
                <div class='col-sm-1'>
                    <span class='fa fa-info-circle fa-2x'></span>
                </div>
                <div class='col-sm-11'>
                <?php
                    echo sprintf($plugin->gT("Log in to your <b>%s</b> account to buy participants. If you do not have an account, click %s to register.", 'js'),
                        '<a href="https://www.limesurvey.org">limesurvey.org</a>',
                        '<a href="https://www.limesurvey.org/cb-registration/registers">' . $plugin->gT('here') . '</a>'
                    );
                ?>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-2'></div>

    <div class='form-group'>
        <label class='control-label col-sm-4'><?php echo $plugin->gT("Username (limesurvey.org):"); ?></label>
        <div class='col-sm-4'>
            <input class='form-control' type='text' name='username' />
        </div>
    </div>
    <div class='form-group'>
        <label class='control-label col-sm-4'><?php echo $plugin->gT("Password (limesurvey.org):"); ?></label>
        <div class='col-sm-4'>
            <input class='form-control' type='password' name='password' />
        </div>
    </div>
    <div class='form-group'>
        <div class='col-sm-4'></div>
        <div class='col-sm-4'>
            <input id='cintlink-login-submit' class='btn btn-default' type='submit' value='<?php echo $plugin->gT('Log in'); ?>' />
            <button id='cintlink-login-cancel' class='btn btn-default' onclick='LS.plugin.cintlink.showDashboard(); return false;'><?php echo $plugin->gT('Cancel'); ?></button>
        </div>
    </div>
</form>
