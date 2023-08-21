<h4 class="h4"><?php
    eT("Output options"); ?></h4>
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class='mb-3'>
            <label for='showtextinline' class="form-label"><?php
                eT("Show text responses inline:") ?></label>
            <div>
                <?php $sShowtextinline = (int)Yii::app()->request->getPost('showtextinline'); ?>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'showtextinline',
                    'checkedOption' => $sShowtextinline,
                    'selectOptions' => [
                        '1' => gT('On', 'unescaped'),
                        '0' => gT('Off', 'unescaped'),
                    ],
                    'htmlOptions'   => [
                        'class' => 'text-option-inherit'
                    ]
                ]); ?>
            </div>
        </div>
        <div class='mb-3'>
            <?php
            $sNoncompleted = (int)Yii::app()->request->getPost('noncompleted'); ?>
            <label class="form-label" id='noncompletedlbl' for='noncompleted' title='<?php
            eT(
                "Count stats for each question based only on the total number of responses for which the question was displayed"
            ); ?>'><?php
                eT("Subtotals based on displayed questions:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'noncompleted',
                    'checkedOption' => $sNoncompleted,
                    'selectOptions' => [
                        '1' => gT('On', 'unescaped'),
                        '0' => gT('Off', 'unescaped'),
                    ],
                    'htmlOptions'   => [
                        'class' => 'text-option-inherit'
                    ]
                ]); ?>
            </div>
        </div>
        <div class="mb-3">
            <label for='charttype' class="form-label">
                <?php
                eT('Chart type:'); ?>
            </label>
            <div class=''>
                <select name="charttype" id='charttype' class="form-select">
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
    <div class="col-lg-6 col-md-12">
        <div class='mb-3'>
        <?php
            $sStatsColumn = Yii::app()->request->getPost('stats_columns', '2'); ?>
            <label for='stats_columns' class="form-label"><?php
                eT("Number of columns:") ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'stats_columns',
                    'checkedOption' =>  $sStatsColumn,
                    'selectOptions' => [
                        '1' => gT('One'),
                        '2' => gT('Two'),
                        '3' => gT('Three'),
                    ],
                ]); ?>
            </div>
        </div>
        <div class='mb-3'>
            <?php
                $sGraphLabels = Yii::app()->request->getPost('graph_labels', 'qcode'); ?>
            <label for='graph_labels' class="form-label"><?php
                eT("Graph labels:") ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'graph_labels',
                    'checkedOption' => $sGraphLabels,
                    'selectOptions' => [
                        'qcode' => gT('Question code'),
                        'qtext' => gT('Question text'),
                        'both'  => gT('Both'),
                    ],
                ]); ?>
            </div>
        </div>
        <div class="mb-3">
            <?php
            $message = gT("Each question has its own graph type defined in its advanced settings.") .
                '<br>' .
                gT("Using the chart type selector you can force the graph type for all selected questions.");
            $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $message,
            'type' => 'info',
            ]);
            ?>
        </div>
    </div>
</div>
<hr>
