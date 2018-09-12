    <div class="panel panel-primary " id="pannel-1">
        <div class="panel-heading">
            <h4 class="panel-title"><?php eT("Output format"); ?></h4>
        </div>
        <div class="panel-body">
            <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default active">
                    <input name="outputtype" value="html" type="radio" checked='checked' id="outputtypehtml" autofocus="true" ><?php eT('HTML');?>
                </label>
                <label class="btn btn-default">
                    <input name="outputtype" value="pdf" type="radio" id="outputtypepdf"><?php eT('PDF');?>
                </label>
                <label class="btn btn-default">
                    <input name="outputtype" value="xls" class="active" type="radio" id="outputtypexls"  onclick='nographs();'><?php eT('Excel'); ?>
                </label>
            </div>
        </div>
    </div>
