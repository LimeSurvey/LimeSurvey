<div class="row">
    <div class="col-lg-12 content-right">
        <h3><?php eT("CintLink Integration");?></h3>
        <form class='form-horizontal'>
            <div class='col-sm-4'></div>
            <div class='col-sm-8'>
                <p class='help-block'><?php eT("Log in to your limesurvey.org account to buy participants."); ?></p>
            </div>
            <div class='form-group'>
                <label class='control-label col-sm-4'><?php eT("Username:"); ?></label>
                <div class='col-sm-4'>
                    <input class='form-control' type='text' name='username' />
                </div>
            </div>
            <div class='form-group'>
                <label class='control-label col-sm-4'><?php eT("Password:"); ?></label>
                <div class='col-sm-4'>
                    <input class='form-control' type='password' name='password' />
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-4'></div>
                <div class='col-sm-4'>
                    <input class='btn btn-default' type='submit' value='Login' />
                </div>
            </div>
        </form>
    </div>
</div>

<script>

// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.cintlink = LS.plugin.cintlink || {};

</script>
