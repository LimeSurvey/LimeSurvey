    <div class="panel panel-primary" id="pannel-1">
        <div class="panel-heading">
            <h4 class="panel-title"><?php eT("Output options"); ?></h4>
        </div>
        <div class="panel-body">
            <div class='form-group'>
                <label for='showtextinline' class="col-sm-5 control-label" ><?php eT("Show text responses inline:") ?></label>
                <div class='col-sm-1'>
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'showtextinline', 'id'=>'showtextinline', 'value'=>($showtextinline==1), 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                </div>
            </div>

            <div class='form-group'>
                <label for='usegraph'  class="col-sm-5 control-label" ><?php eT("Show graphs:"); ?></label>
                <div class='col-sm-1'>
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'usegraph', 'id'=>'usegraph', 'value'=>($usegraph==1), 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                </div>
                <?php if($error != '') { echo "<div id='grapherror' style='display:none'>$error<hr /></div>"; } ?>
            </div>

            <div class="form-group col-sm-12">
                <div class="col-sm-offset-1 alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>×</span></button>
                    <?php eT("Each question has its own graph type defined in its advanced settings.");?>
                    <br/>
                    <?php eT("Using the chart type selector you can force the graph type for all selected questions.");?>
                </div>
            </div>

            <div class="form-group col-sm-12">
                <label for='charttype' class="col-sm-5 control-label">
                    <?php eT('Chart type:');?>
                </label>

                <div class='col-sm-5'>
                    <select name="charttype" id='charttype' class="form-control">
                        <option value="default" selected="selected"><?php eT("As defined in question settings");?></option>
                        <option value="0"><?php eT('Bar chart');?></option>
                        <option value="1"><?php eT('Pie chart');?></option>
                        <option value="2"><?php eT('Radar chart');?></option>
                        <option value="3"><?php eT('Line chart');?></option>
                        <option value="4"><?php eT('Polar chart');?></option>
                        <option value="5"><?php eT('Doughnut chart');?></option>
                    </select>
                </div>
            </div>

        </div>
    </div>
