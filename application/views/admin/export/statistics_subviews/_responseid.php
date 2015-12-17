<fieldset id='left'>
    <legend><?php eT("Response ID"); ?></legend>
    <div class='form-group'>
        <label class="col-sm-5 control-label" for='idG'><?php eT("Greater than:"); ?></label>
        <div class='col-sm-5'>
            <input type='text' id='idG' name='idG' size='10' value='<?php if (isset($_POST['idG'])){ echo  sanitize_int($_POST['idG']);} ?>' onkeypress="return goodchars(event,'0123456789')" />
        </div>
    </div>

    <div class='form-group'>
        <label class="col-sm-5 control-label" for='idL'><?php eT("Less than:"); ?></label>
        <div class='col-sm-5'>
            <input type='text' id='idL' name='idL' size='10' value='<?php if (isset($_POST['idL'])) { echo sanitize_int($_POST['idL']);} ?>' onkeypress="return goodchars(event,'0123456789')" />
        </div>
    </div>
</fieldset>
