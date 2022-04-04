<h4 class="h4"><?php
    eT("Output options"); ?></h4>
<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <label for='showtextinline' class="control-label"><?php
                eT("Show text responses inline:") ?></label>
            <div>
                <?php
                $sShowtextinline = (int)Yii::app()->request->getPost('showtextinline'); ?>
                <?php
                $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'showtextinline',
                    'id' => 'showtextinline',
                    'value' => $sShowtextinline,
                    'selectOptions' => array(
                        '1' => gT('On', 'unescaped'),
                        '0' => gT('Off', 'unescaped'),
                    ),
                    'htmlOptions' => array(
                        'class' => 'text-option-inherit'
                    )
                ));
                ?>
            </div>
        </div>
        <div class='form-group'>
            <?php
            $sNoncompleted = (int)Yii::app()->request->getPost('noncompleted'); ?>
            <label class="control-label" id='noncompletedlbl' for='noncompleted' title='<?php
            eT(
                "Count stats for each question based only on the total number of responses for which the question was displayed"
            ); ?>'><?php
                eT("Subtotals based on displayed questions:"); ?></label>
            <div>
                <?php
                $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'noncompleted',
                    'id' => 'noncompleted',
                    'value' => $sNoncompleted,
                    'selectOptions' => array(
                        '1' => gT('On', 'unescaped'),
                        '0' => gT('Off', 'unescaped'),
                    ),
                    'htmlOptions' => array(
                        'class' => 'text-option-inherit'
                    )
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for='charttype' class="control-label">
                <?php
                eT('Chart type:'); ?>
            </label>
            <div class=''>
                <select name="charttype" id='charttype' class="form-control">
                    <option value="default" selected="selected"><?php
                        eT("As defined in question settings"); ?></option>
                    <option value="0"><?php
                        eT('Bar chart'); ?></option>
                    <option value="1"><?php
                        eT('Pie chart'); ?></option>
                    <option value="2"><?php
                        eT('Radar chart'); ?></option>
                    <option value="3"><?php
                        eT('Line chart'); ?></option>
                    <option value="4"><?php
                        eT('Polar chart'); ?></option>
                    <option value="5"><?php
                        eT('Doughnut chart'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <label for='stats_columns' class="control-label"><?php
                eT("Number of columns:") ?></label>
            <div class="">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default">
                        <input name="stats_columns" value="1" type="radio" class="selected">
                        <?php
                        eT('One'); ?>
                    </label>
                    <label class="btn btn-default active">
                        <input name="stats_columns" value="2" type="radio" checked>
                        <?php
                        eT('Two'); ?>
                    </label>
                    <label class="btn btn-default">
                        <input name="stats_columns" value="3" class="active" type="radio">
                        <?php
                        eT('Three'); ?>
                    </label>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='graph_labels' class="control-label"><?php
                eT("Graph labels:") ?></label>
            <div class="">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default active">
                        <input name="graph_labels" value="qcode" type="radio" checked="checked">
                        <?php
                        eT('Question code'); ?>
                    </label>
                    <label class="btn btn-default">
                        <input name="graph_labels" value="qtext" type="radio">
                        <?php
                        eT('Question text'); ?>
                    </label>
                    <label class="btn btn-default">
                        <input name="graph_labels" value="both" class="active" type="radio">
                        <?php
                        eT('Both'); ?>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>X</span>
                </button>
                <?php
                eT("Each question has its own graph type defined in its advanced settings."); ?>
                <br/>
                <?php
                eT("Using the chart type selector you can force the graph type for all selected questions."); ?>
            </div>
        </div>
    </div>
</div>
<hr>
