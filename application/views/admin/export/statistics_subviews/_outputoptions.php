<fieldset>
    <legend><?php eT("Output options"); ?></legend>
        <div class='form-group'>
            <label for='showtextinline' class="col-sm-5 control-label" ><?php eT("Show text responses inline:") ?></label>
            <div class='col-sm-5'>
                <input type='checkbox' id='showtextinline' name='showtextinline'<?php if(isset($showtextinline) && $showtextinline == 1) {echo "checked='checked'"; } ?> />
            </div>
        </div>

        <div class='form-group'>
            <label for='usegraph'  class="col-sm-5 control-label" ><?php eT("Show graphs"); ?></label>
            <div class='col-sm-5'>
                <input type='checkbox' id='usegraph' name='usegraph' <?php if (isset($usegraph) && $usegraph == 1) { echo "checked='checked'"; } ?> />
            </div>
            <?php if($error != '') { echo "<div id='grapherror' style='display:none'>$error<hr /></div>"; } ?>
        </div>

        <div class="form-group col-sm-12">
            <div class="col-sm-offset-1 alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>×</span></button>
                <?php eT("Each question has its own graph type defined in its advanced settings.");?>
                <br/>
                <?php eT("With chart type selector, you can force the use of graph type for all selected questions)");?>
            </div>
        </div>

        <div class="form-group col-sm-12">
            <label for='charttype' class="col-sm-5 control-label">
                <?php eT('Chart type:');?>
            </label>

            <div class='col-sm-5'>
                <select name="charttype" id='charttype' class="form-control">
                    <option value="default" selected="selected"><?php eT('as defined in questions\'s advanced setting');?></option>
                    <option value="0" ><?php eT('bar chart');?></option>
                    <option value="1"><?php eT('pie chart');?></option>
                    <option value="2"><?php eT('radar chart');?></option>
                    <option value="3"><?php eT('line chart');?></option>
                    <option value="4"><?php eT('polar chart');?></option>
                    <option value="5"><?php eT('doughnut chart');?></option>
                </select>
            </div>
        </div>
</fieldset>
