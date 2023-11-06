<h4 class=""><?php
    eT("Main options"); ?></h4>
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
        <div class='form-group'>
            <label for='outputtype' class="control-label"><?php
                eT("Output format:") ?></label>
            <div>
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default active">
                        <input name="outputtype" value="html" type="radio" checked='checked' id="outputtypehtml">
                        <?php
                        eT('HTML'); ?>
                    </label>
                    <label class="btn btn-default">
                        <input name="outputtype" value="pdf" type="radio" id="outputtypepdf"><?php
                        eT('PDF'); ?>
                    </label>
                    <label class="btn btn-default">
                        <input name="outputtype" value="xls" class="active" type="radio" id="outputtypexls"
                               onclick='nographs();'><?php
                        eT('Excel'); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class='form-group'>
            <?php
            $sViewsummaryall = (int)Yii::app()->request->getPost('viewsummaryall'); ?>
            <label class="control-label" for='viewsummaryall'><?php
                eT("View summary of all available fields:"); ?></label>
            <div>
                <?php
                $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'viewsummaryall',
                    'id' => 'viewsummaryall',
                    'value' => $sViewsummaryall,
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
            <label for='usegraph' class="control-label"><?php
                eT("Show graphs:"); ?></label>
            <div class=''>
                <?php
                $sUsegraph = (int)Yii::app()->request->getPost('usegraph'); ?>
                <?php
                $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'usegraph',
                    'id' => 'usegraph',
                    'value' => $sUsegraph,
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
<hr>
