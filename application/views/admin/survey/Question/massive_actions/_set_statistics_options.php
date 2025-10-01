<?php
/**
 * Set subquestion/answer order
 */

/** @var AdminController $this */
/** @var Question $model */
?>
<form class="custom-modal-datas form-horizontal">

    <!-- Public statistics -->
    <div  class="mb-3" id="PublicStatistcs">
        <label class="col-md-4 form-label" for="public_statistics"><?php eT("Show in public statistics:"); ?></label>
        <div class="col-md-8">
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'public_statistics',
                'checkedOption' => '0',
                'selectOptions' => [
                    '1' => gT('On'),
                    '0' => gT('Off'),
                ],
                'htmlOptions'   => [
                    'class' => 'custom-data attributes-to-update'
                ],
            ]); ?>
        </div>
    </div>
    <!-- Display charts -->
    <div  class="mb-3" id="StatisticsShowgraph">
        <label class="col-md-4 form-label" for="statistics_showgraph"><?php eT("Display chart:"); ?></label>
        <div class="col-md-8">
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'statistics_showgraph',
                'checkedOption' => '0',
                'selectOptions' => [
                    '1' => gT('On'),
                    '0' => gT('Off'),
                ],
                'htmlOptions'   => [
                    'class' => 'custom-data attributes-to-update'
                ],
            ]); ?>
        </div>
    </div>
    <!-- Display charts -->
    <div  class="mb-3" id="StatisticsGraphType">
        <label class="col-md-4 form-label" for="statistics_showgraph"><?php eT("Chart type:"); ?></label>
        <div class="col-md-8">
            <select class="form-select custom-data attributes-to-update" id="statistics_graphtype" name="statistics_graphtype">
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
