    <div class="panel panel-primary" id="pannel-1">
        <div class="panel-heading">
            <h4 class="panel-title"><?php eT("Response ID"); ?></h4>
        </div>
        <div class="panel-body">
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='idG'><?php eT("Greater than:"); ?></label>
                <div class='col-sm-2'>
                    <input type='number' id='idG' name='idG' size='10' value='<?php if (isset($_POST['idG'])){ echo  sanitize_int($_POST['idG']);} ?>' onkeypress="return goodchars(event,'0123456789')" />
                </div>
            </div>

            <div class='form-group'>
                <label class="col-sm-4 control-label" for='idL'><?php eT("Less than:"); ?></label>
                <div class='col-sm-2'>
                    <input type='number' id='idL' name='idL' size='10' value='<?php if (isset($_POST['idL'])) { echo sanitize_int($_POST['idL']);} ?>' onkeypress="return goodchars(event,'0123456789')" />
                </div>
            </div>
        </div>
    </div>
