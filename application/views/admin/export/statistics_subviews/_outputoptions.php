    <div class="panel panel-primary" id="panel-1">
        <div class="panel-heading">
            <div class="panel-title h4"><?php eT("Output options"); ?></div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class='form-group col-md-6'>
                    <label for='showtextinline' class="col-lg-8 control-label" ><?php eT("Show text responses inline:") ?></label>
                    <div class='col-lg-4'>
                        <?php $sShowtextinline = (int) Yii::app()->request->getPost('showtextinline');?>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', 
                        array('name' => 'showtextinline', 'id'=>'showtextinline', 'value'=>$sShowtextinline, 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                    </div>
                </div>

                <div class='form-group col-md-6'>
                    <label for='usegraph'  class="col-lg-8 control-label" ><?php eT("Show graphs:"); ?></label>
                    <div class='col-lg-4'>
                        <?php $sUsegraph = (int) Yii::app()->request->getPost('usegraph');?>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', 
                        array('name' => 'usegraph', 'id'=>'usegraph', 'value'=>$sUsegraph, 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                    </div>
                    <?php if($error != '') { echo "<div id='grapherror' style='display:none'>$error<hr /></div>"; } ?>
                </div>
            </div>

            <div class="row">
                <div class='form-group'>
                    <label for='stats_columns' class="col-lg-5 control-label" ><?php eT("Number of columns:") ?></label>
                    <div class="btn-group " data-toggle="buttons">
                        <label class="btn btn-default">
                            <input name="stats_columns" value="1" type="radio" class="selected" >
                            <?php eT('One');?>
                        </label>
                        <label class="btn btn-default active">
                            <input name="stats_columns" value="2" type="radio" checked>
                            <?php eT('Two');?>
                        </label>
                        <label class="btn btn-default">
                            <input name="stats_columns" value="3" class="active" type="radio">
                            <?php eT('Three');?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class='form-group'>
                    <label for='graph_labels' class="col-lg-5 control-label" ><?php eT("Graph labels:") ?></label>
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input name="graph_labels" value="qcode" type="radio" checked="checked">
                            <?php eT('Question code');?>
                        </label>
                        <label class="btn btn-default">
                            <input name="graph_labels" value="qtext" type="radio">
                            <?php eT('Question text');?>
                        </label>
                        <label class="btn btn-default">
                            <input name="graph_labels" value="both" class="active" type="radio">
                            <?php eT('Both');?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <div class="col-lg-offset-1 alert alert-info alert-dismissible" role="alert">
                        <button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>×</span></button>
                        <?php eT("Each question has its own graph type defined in its advanced settings.");?>
                        <br/>
                        <?php eT("Using the chart type selector you can force the graph type for all selected questions.");?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label for='charttype' class="col-lg-5 control-label">
                        <?php eT('Chart type:');?>
                    </label>

                    <div class='col-lg-5'>
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
    </div>
