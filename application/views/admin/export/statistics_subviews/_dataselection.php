<div class="panel panel-primary" id="panel-1">
    <div class="panel-heading">
        <div class="panel-title h4"><?php eT("Data selection"); ?></div>
    </div>

    <div class="panel-body">
        <div class="row">
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
        </div>
        <div class="row">
            <div class='form-group col-sm-6'>
                <?php $sViewsummaryall = (int) Yii::app()->request->getPost('viewsummaryall');?>
                <label class="col-lg-8 control-label" for='viewsummaryall'><?php eT("View summary of all available fields:"); ?></label>
                <div class='col-lg-4'>
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'viewsummaryall', 'id'=>'viewsummaryall', 'value'=>$sViewsummaryall, 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                </div>
            </div>

            <div class='form-group col-sm-6'>
                <?php $sNoncompleted = (int) Yii::app()->request->getPost('noncompleted');?>
                <label class="col-lg-8 control-label" id='noncompletedlbl' for='noncompleted' title='<?php eT("Count stats for each question based only on the total number of responses for which the question was displayed"); ?>'><?php eT("Subtotals based on displayed questions:"); ?></label>
                <div class='col-lg-4'>
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', 
                        array(
                            'name' => 'noncompleted', 
                            'id'=>'noncompleted', 
                            'value'=>$sNoncompleted,
                            'onLabel'=>gT('On'),
                            'offLabel'=>gT('Off')
                        )
                    );?>
                </div>
            </div>
        </div>
        <div class="row">
            <?php
                $language_options="";
                foreach ($survlangs as $survlang){
                    $language_options .= "\t<option value=\"{$survlang}\"";
                    if ( $survlang == Survey::model()->findByPk($surveyid)->language){
                        $language_options .= " selected=\"selected\" " ;
                    }
                    $temp = getLanguageNameFromCode($survlang,true);
                    $language_options .= ">".$temp[1]."</option>\n";
                    }
            ?>

            <div class='form-group'>
                <label for='statlang' class="col-lg-4 control-label" ><?php eT("Statistics report language:"); ?></label>
                <div class='col-lg-8'>
                    <select name="statlang" id="statlang" class="form-control">
                        <?php echo $language_options; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
