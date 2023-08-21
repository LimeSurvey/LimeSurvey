<h4 class=""><?php
    eT("Main options"); ?></h4>
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class='mb-3'>
            <label for='completionstate' class="form-label"><?php
                eT("Include:"); ?> </label>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                    'name' => 'completionstate',
                    'checkedOption' => incompleteAnsFilterState(),
                    'selectOptions' => array(
                        "all" => gT("All responses", 'unescaped'),
                        "complete" => gT("Complete only", 'unescaped'),
                        "incomplete" => gT("Incomplete only", 'unescaped'),
                    )
                )); ?>
            </div>
        </div>
        <div class='mb-3'>
            <label for='outputtype' class="form-label"><?php
                eT("Output format:") ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'outputtype',
                    'checkedOption' => 'html',
                    'selectOptions' => [
                        'html' => gT('HTML'),
                        'pdf' => gT('PDF'),
                        'xls' => gT('Excel'),
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class='mb-3'>
            <?php
            $sViewsummaryall = (int)Yii::app()->request->getPost('viewsummaryall'); ?>
            <label class="form-label" for='viewsummaryall'><?php
                eT("View summary of all available fields:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'viewsummaryall',
                    'checkedOption' => $sViewsummaryall,
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
            <label for='usegraph' class="form-label"><?php
                eT("Show graphs:"); ?></label>
            <div class=''>
                <?php $sUsegraph = (int)Yii::app()->request->getPost('usegraph'); ?>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'usegraph',
                    'checkedOption' => $sUsegraph,
                    'selectOptions' => [
                        '1' => gT('On', 'unescaped'),
                        '0' => gT('Off', 'unescaped'),
                    ],
                    'htmlOptions'   => [
                        'class' => 'text-option-inherit'
                    ]
                ]); ?>
            </div>
            <?php
            if ($error != '') {
                echo "<div id='grapherror' style='display:none'>$error<hr /></div>";
            } ?>
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
        <div class='mb-3' <?php
        if (count($survlangs) == 1) {
            echo "style='display:none'";
        } ?>>
            <label for='statlang' class="form-label"><?php
                eT("Statistics report language:"); ?></label>
            <div class=''>
                <select name="statlang" id="statlang" class="form-select">
                    <?php
                    echo $language_options; ?>
                </select>
            </div>
        </div>
    </div>
</div>
<hr>
