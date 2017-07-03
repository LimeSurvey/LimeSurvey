<?php
/**
 * This view generate the advanced question attributes
 */
$currentfieldset='';
$categoryNum=0;
?>
<!-- Advanced Settings -->
<?php foreach ($attributedata as $index=>$aAttribute):?>

    <!-- Fieldsets -->
    <?php if ($currentfieldset!=$aAttribute['category']): ?>
        <?php $categoryNum++; ?>
        <?php if ($currentfieldset!=''): ?>
            </div></div></div></div>
        </div>
        <?php endif; ?>
        <div class="panel panel-default panel-advancedquestionsettings">
            <div class="panel-heading" role="tab">
                <h4 class="panel-title">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion">
                        <span class="fa fa-chevron-left"></span>
                    </a>
                    <a id="button-collapse<?php echo $categoryNum ?>" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-cat<?php echo $categoryNum ?>" aria-expanded="false" aria-controls="collapse-cat<?php echo $categoryNum ?>">
                        <?php echo $aAttribute['category']; ?>
                    </a>
                </h4>
            </div>
            <div id="collapse-cat<?php echo $categoryNum ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="button-collapse<?php echo $categoryNum ?>">
                <div class="panel-body">
                    <div>

        <?php $currentfieldset=$aAttribute['category']; ?>
    <?php endif; ?>
    <div class="form-group">
    <!-- Form Group -->
        <!-- Label -->
        <label class="col-sm-4 control-label" for='<?php echo $aAttribute['name'];?>' title='<?php echo $aAttribute['help'];?>'>
            <?php
                echo $aAttribute['caption'];
                if ($aAttribute['i18n']==true) { ?> (<?php echo $aAttribute['language'] ?>)<?php }
            ?>:
        </label>

        <!-- Input -->
        <div class="col-sm-8">
            <?php
                if ($aAttribute['readonly'] && $bIsActive)
                {
                    echo $aAttribute['value'];
                }
                else
                {
                    switch ($aAttribute['inputtype'])
                    {
                        // Switch
                        case 'switch':
                             $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => $aAttribute['name'],
                                'value'=> $aAttribute['value'],
                                'onLabel'=>gT('On'),
                                'offLabel'=>gT('Off')
                            ));
                            break;
                            // Button group
                        case 'buttongroup':
                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                'name' => $aAttribute['name'],
                                'value'=> $aAttribute['value'] ,
                                'selectOptions'=>$aAttribute['options']
                            ));
                            break;
                            // Single select
                        case 'singleselect':
                            echo "<select class='form-control' id='{$aAttribute['name']}' name='{$aAttribute['name']}'>";
                            foreach($aAttribute['options'] as $sOptionvalue=>$sOptiontext)
                            {
                                echo "<option value='{$sOptionvalue}' ";
                                if ($aAttribute['value']==$sOptionvalue)
                                {
                                    echo " selected='selected' ";
                                }
                                echo ">{$sOptiontext}</option>";
                            }
                            echo "</select>";
                            break;

                            // Text
                        case 'text':?>
                            <?php if(isset($aAttribute['expression']) && $aAttribute['expression']>=2){?>
                                <div class="input-group">
                                    <div class="input-group-addon">{</div>
                            <?php } ?>
                            <input type='text' class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo htmlspecialchars($aAttribute['value'],ENT_QUOTES, 'UTF-8');?>' />
                            <?php if(isset($aAttribute['expression']) && $aAttribute['expression']>=2){?>
                                    <div class="input-group-addon">}</div>
                                </div>
                            <?php } ?>
                            <?php
                            break;

                        // Integer
                        case 'integer':?>
                            <?php echo CHtml::numberField($aAttribute['name'],$aAttribute['value'],array(
                                'class'=>"form-control",
                                'step'=>1,
                                'pattern'=>'\d+',
                                'min'=>(isset($aAttribute['min'])?$aAttribute['min']:1),
                                'max'=>(isset($aAttribute['max'])?$aAttribute['max']:null)
                            )); ?>
                            <?php
                            break;

                        // Interger
                        case 'columns':?>
                            <input type='number' min="1" max="12" step="1" class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo $aAttribute['value'];?>' />
                            <?php
                            break;


                        // Textarea
                        case 'textarea':?>
                            <?php if(isset($aAttribute['expression']) && $aAttribute['expression']>=2){?>
                                <div class="input-group">
                                    <div class="input-group-addon">{</div>
                            <?php } ?>
                            <textarea class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>'><?php echo $aAttribute['value'];?></textarea>
                            <?php if(isset($aAttribute['expression']) && $aAttribute['expression']>=2){?>
                                    <div class="input-group-addon">}</div>
                                </div>
                            <?php } ?>
                            <?php
                            break;

                        // Question template selector
                        case 'question_template':
                            echo "<select class='form-control' id='{$aAttribute['name']}' name='{$aAttribute['name']}'>";
                            foreach($aQuestionTemplates as $sOptionvalue=>$sOptiontext)
                            {
                                echo "<option value='{$sOptionvalue}' ";
                                if ($aAttribute['value']==$sOptionvalue)
                                {
                                    echo " selected='selected' ";
                                }
                                echo ">{$sOptiontext}</option>";
                            }
                            echo "</select>";
                            break;

                    }
                }?>
            </div>
        </div>
<?php endforeach;?>
 </div></div></div></div>
<?php
/* Launch all needed script (here after load) needed for widget */
foreach (Yii::app()->clientScript->scripts as $index=>$script)
{
    echo CHtml::script(implode("\n",$script));
}
Yii::app()->clientScript->reset();
?>
<!-- end of Advanced Settings -->
