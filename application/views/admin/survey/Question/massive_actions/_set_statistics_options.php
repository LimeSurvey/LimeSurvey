<?php
/**
 * Set subquestion/answer order
 */
?>
<form class="custom-modal-datas form-horizontal">

    <!-- Public statistics -->
    <div  class="form-group" id="PublicStatistcs">
        <label class="col-sm-4 control-label" for="public_statistics"><?php eT("Show in public statistics:"); ?></label>
        <div class="col-sm-8">
            <?php $this->widget(
                    'yiiwheels.widgets.switch.WhSwitch',
                    array(
                        'name' => 'public_statistics',
                        'htmlOptions'=> array(
                            'class'=>'custom-data attributes-to-update bootstrap-switch-integer'
                        ),
                    'value'=> '',
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')));
            ?>
        </div>
    </div>
    <!-- Display charts -->
    <div  class="form-group" id="StatisticsShowgraph">
        <label class="col-sm-4 control-label" for="statistics_showgraph"><?php eT("Display chart:"); ?></label>
        <div class="col-sm-8">
            <?php $this->widget(
                'yiiwheels.widgets.switch.WhSwitch',
                array(
                    'name' => 'statistics_showgraph',
                    'htmlOptions'=>array(
                        'class'=>'custom-data attributes-to-update bootstrap-switch-integer'
                    ),
                'value'=> '',
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')));
            ?>
        </div>
    </div>
    <!-- Display charts -->
    <div  class="form-group" id="StatisticsGraphType">
        <label class="col-sm-4 control-label" for="statistics_showgraph"><?php eT("Chart type:"); ?></label>
        <div class="col-sm-8">
            <select class="form-control custom-data attributes-to-update" id="statistics_graphtype" name="statistics_graphtype">
                <option value="0" selected="selected">Bar chart</option>
                <option value="1"><?php eT('Pie chart'); ?></option>
                <option value="2"><?php eT('Radar'); ?></option>
                <option value="3"><?php eT('Line'); ?></option>
                <option value="4"><?php eT('PolarArea'); ?></option>
                <option value="5"><?php eT('Doughnut'); ?></option>
            </select>
        </div>
    </div>

    <input type="hidden" name="sid" value="<?php echo (int) Yii::app()->request->getParam('surveyid',0); ?>" class="custom-data"/>
    <input type="hidden" name="aValidQuestionTypes" value="15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*" class="custom-data"/>
</form>
