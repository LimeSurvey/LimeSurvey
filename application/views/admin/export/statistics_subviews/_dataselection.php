<div class="panel panel-primary" id="pannel-1">
    <div class="panel-heading">
        <h4 class="panel-title"><?php eT("Data selection"); ?></h4>
    </div>

    <div class="panel-body">
        <div class='form-group'>
            <label for='completionstate' class="col-sm-4 control-label"><?php eT("Include:"); ?> </label>
            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'completionstate',
                'value'=> incompleteAnsFilterState(),
                'selectOptions'=>array(
                    "all"=>gT("All responses",'unescaped'),
                    "complete"=>gT("Complete only",'unescaped'),
                    "incomplete"=>gT("Incomplete only",'unescaped'),
                )
            ));?>
        </div>

        <div class='form-group'>
            <?php $sViewsummaryall = (int) Yii::app()->request->getPost('viewsummaryall');?>
            <label class="col-sm-4 control-label" for='viewsummaryall'><?php eT("View summary of all available fields:"); ?></label>
            <div class='col-sm-1'>
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'viewsummaryall', 'id'=>'viewsummaryall', 'value'=>$sViewsummaryall, 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
            </div>
        </div>

        <div class='form-group'>
            <?php $sNoncompleted = (int) Yii::app()->request->getPost('noncompleted');?>
            <label class="col-sm-4 control-label" id='noncompletedlbl' for='noncompleted' title='<?php eT("Count stats for each question based only on the total number of responses for which the question was displayed"); ?>'><?php eT("Subtotals based on displayed questions:"); ?></label>
            <div class='col-sm-1'>
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'noncompleted', 'id'=>'noncompleted', 'value'=>$sNoncompleted, 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
            </div>
        </div>

        <?php
            $language_options="";
            foreach ($survlangs as $survlang){
                $language_options .= "\t<option value=\"{$survlang}\"";
                if ( $survlang == $surveyinfo['language']){
                    $language_options .= " selected=\"selected\" " ;
                  }
                  $temp = getLanguageNameFromCode($survlang,true);
                  $language_options .= ">".$temp[1]."</option>\n";
                }
        ?>

        <div class='form-group'>
            <label for='statlang' class="col-sm-4 control-label" ><?php eT("Statistics report language:"); ?></label>
            <div class='col-sm-4'>
                <select name="statlang" id="statlang" class="form-control">
                    <?php echo $language_options; ?>
                </select>
            </div>
        </div>
    </div>
</div>
