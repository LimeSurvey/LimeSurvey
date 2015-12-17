<fieldset>
    <legend><?php eT("Select output format"); ?>:</legend>
    <div class="form-group col-sm-12">
        <label  class="col-sm-5 control-label" for='outputtypehtml'>HTML</label>
        <div class='col-sm-5'>
            <input type='radio' id="outputtypehtml" name='outputtype' value='html' checked='checked' />
        </div>
    </div>
    <div class="form-group col-sm-12">
        <label  class="col-sm-5 control-label" for='outputtypepdf'>PDF</label>
        <div class='col-sm-5'>
            <input type='radio' id="outputtypepdf" name='outputtype' value='pdf' />
        </div>
    </div>
    <div class="form-group col-sm-12">
        <label class="col-sm-5 control-label" for='outputtypexls'>Excel</label>
        <div class='col-sm-5'>
            <input type='radio' id="outputtypexls" onclick='nographs();' name='outputtype' value='xls' />
        </div>
    </div>
</fieldset>
