<h4 class="h4"><?php
    eT("Output format"); ?></h4>
<div class="row">
    <div class='form-group'>
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default active">
                <input name="outputtype" value="html" type="radio" checked='checked' id="outputtypehtml">
                <?php
                eT('HTML'); ?>
            </label>
            <label class="btn btn-default">
                <input name="outputtype" value="pdf" type="radio" id="outputtypepdf"><?php
                eT('PDF'); ?>
            </label>
            <label class="btn btn-default">
                <input name="outputtype" value="xls" class="active" type="radio" id="outputtypexls"
                       onclick='nographs();'><?php
                eT('Excel'); ?>
            </label>
        </div>
    </div>
</div>
