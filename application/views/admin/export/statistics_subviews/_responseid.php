<h4 class="h4"><?php
    eT("Response ID"); ?></h4>
<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <label class="control-label" for='idG'><?php
                eT("Greater than:"); ?></label>
            <div class=''>
                <input class="form-control" type='number' id='idG' name='idG' size='10' value='<?php
                if (isset($_POST['idG'])) {
                    echo sanitize_int($_POST['idG']);
                } ?>' onkeypress="returnwindow.LS.goodchars(event,'0123456789')"/>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <label class="control-label" for='idL'><?php
                eT("Less than:"); ?></label>
            <div class=''>
                <input class="form-control" type='number' id='idL' name='idL' size='10' value='<?php
                if (isset($_POST['idL'])) {
                    echo sanitize_int($_POST['idL']);
                } ?>' onkeypress="returnwindow.LS.goodchars(event,'0123456789')"/>
            </div>
        </div>
    </div>
</div>
