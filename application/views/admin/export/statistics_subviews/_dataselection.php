<!--// TODO header        -->
<h4 class=""><?php
    eT("Data selection"); ?></h4>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <label for='completionstate' class="control-label"><?php
                eT("Include:"); ?> </label>
            <div class="">
                <?php
                $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'completionstate',
                    'value' => incompleteAnsFilterState(),
                    'selectOptions' => array(
                        "all" => gT("All responses", 'unescaped'),
                        "complete" => gT("Complete only", 'unescaped'),
                        "incomplete" => gT("Incomplete only", 'unescaped'),
                    )
                )); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <?php
            $sViewsummaryall = (int)Yii::app()->request->getPost('viewsummaryall'); ?>
            <label class="control-label" for='viewsummaryall'><?php
                eT("View summary of all available fields:"); ?></label>
            <div class=''>
                <?php
                $this->widget(
                    'yiiwheels.widgets.switch.WhSwitch',
                    array(
                        'name' => 'viewsummaryall',
                        'id' => 'viewsummaryall',
                        'value' => $sViewsummaryall,
                        'onLabel' => gT('On'),
                        'offLabel' => gT('Off')
                    )
                ); ?>
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
            <div class=''>
                <?php
                $this->widget('yiiwheels.widgets.switch.WhSwitch',
                    array(
                        'name' => 'noncompleted',
                        'id' => 'noncompleted',
                        'value' => $sNoncompleted,
                        'onLabel' => gT('On'),
                        'offLabel' => gT('Off')
                    )
                ); ?>
            </div>
        </div>

        <?php
        $language_options = "";
        foreach ($survlangs as $survlang) {
            $language_options .= "\t<option value=\"{$survlang}\"";
            if ($survlang == Survey::model()->findByPk($surveyid)->language) {
                $language_options .= " selected=\"selected\" ";
            }
            $temp = getLanguageNameFromCode($survlang, true);
            $language_options .= ">" . $temp[1] . "</option>\n";
        }
        ?>

        <div class='form-group' <?php
        if (count($survlangs) == 1) {
            echo "style='display:none'";
        } ?>>
            <label for='statlang' class="control-label"><?php
                eT("Statistics report language:"); ?></label>
            <div class=''>
                <select name="statlang" id="statlang" class="form-control">
                    <?php
                    echo $language_options; ?>
                </select>
            </div>
        </div>
    </div>
</div>
