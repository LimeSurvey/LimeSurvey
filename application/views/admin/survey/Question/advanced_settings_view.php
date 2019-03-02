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
                <a class="panel-title h4 selector--questionEdit-collapse" id="button-collapse<?php echo $categoryNum ?>"  role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-cat<?php echo $categoryNum ?>" aria-expanded="false" aria-controls="collapse-cat<?php echo $categoryNum ?>">
                    <?php echo $aAttribute['category']; ?>
                </a>
            </div>
            <div id="collapse-cat<?php echo $categoryNum ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="button-collapse<?php echo $categoryNum ?>">
                <div class="panel-body">
                    <div>

        <?php $currentfieldset=$aAttribute['category']; ?>
    <?php endif; ?>
    <div class="form-group">
    <!-- Form Group -->
        <!-- Label -->
        <label class="control-label" for='<?php echo $aAttribute['name'];?>'>
            <?php
                echo $aAttribute['caption'];
                if ($aAttribute['i18n']==true) { ?> (<?php echo $aAttribute['language'] ?>)<?php }
            ?>
            <?php if (!empty($aAttribute['help'])): ?>
            <a class="text-primary show-help" data-toggle="collapse" href="#help<?php echo $aAttribute['name'];?>" aria-expanded="false" aria-controls="help<?php echo $aAttribute['name'];?>" aria-hidden=true>
                <span class="fa fa-info-circle" ></span>
            </a>
            <?php endif; ?>
        </label>
        <?php if (!empty($aAttribute['help'])): ?>
        <p class="help-block collapse" id="help<?php echo $aAttribute['name'];?>"><?php echo $aAttribute['help'];?></p>
        <?php endif; ?>
        <!-- Input -->
        <div class="">
            <?php
                $readonly = ( $aAttribute['readonly'] || ($aAttribute['readonly_when_active'] && $bIsActive) );
                switch ($aAttribute['inputtype'])
                {
                    // Switch
                    case 'switch':
                         $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => $aAttribute['name'],
                            'value'=> $aAttribute['value'],
                            'onLabel'=>gT('On'),
                            'offLabel'=>gT('Off'),
                            'htmlOptions'=>array(
                                'disabled'=>$readonly,
                                'aria-describedby'=>"help{$aAttribute['name']}",
                            ),
                        ));
                        break;
                        // Button group
                    case 'buttongroup':
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => $aAttribute['name'],
                            'value'=> $aAttribute['value'] ,
                            'selectOptions'=>$aAttribute['options'],
                            'htmlOptions'=>array(
                                'disabled'=>$readonly,
                                'aria-describedby'=>"help{$aAttribute['name']}",
                            ),
                        ));
                        break;
                        // Single select
                    case 'singleselect':
                        echo CHtml::dropDownList($aAttribute['name'],$aAttribute['value'],$aAttribute['options'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'encode'=>false, // gt encode it by default
                            'aria-describedby'=>"help{$aAttribute['name']}",
                        ));
                        break;
                        // Text
                    case 'text':
                        if($aAttribute['expression']>=2) {
                            echo CHtml::tag('div',array('class'=>"input-group"),"",false);
                            echo CHtml::tag('div',array('class'=>"input-group-addon"),"{");
                        }
                         echo CHtml::textField($aAttribute['name'],$aAttribute['value'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'aria-describedby'=>"help{$aAttribute['name']}",
                        ));
                        if($aAttribute['expression']>=2) {
                            echo CHtml::tag('div',array('class'=>"input-group-addon"),"}");
                            echo CHtml::closeTag('div');
                        }
                        break;

                    // Integer
                    case 'integer':
                        echo CHtml::numberField($aAttribute['name'],$aAttribute['value'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'step'=>1,
                            'pattern'=>'\d+',
                            'min'=>(isset($aAttribute['min'])?$aAttribute['min']:1),
                            'max'=>(isset($aAttribute['max'])?$aAttribute['max']:null),
                            'aria-describedby'=>"help{$aAttribute['name']}",
                        ));
                        break;

                    // Float
                    case 'float':
                        echo CHtml::numberField($aAttribute['name'],$aAttribute['value'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'step'=>1,
                            'pattern'=>'^[-+]?[0-9]*\.[0-9]+$',
                            'min'=>(isset($aAttribute['min'])?$aAttribute['min']:null),
                            'max'=>(isset($aAttribute['max'])?$aAttribute['max']:null)
                        ));
                        break;


                    // Columns
                    case 'columns':
                        echo CHtml::numberField($aAttribute['name'],$aAttribute['value'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'step'=>1,
                            'pattern'=>'\d+',
                            'min'=>1,
                            'max'=>12,
                            'aria-describedby'=>"help{$aAttribute['name']}",
                        ));
                        break;
                    // Textarea
                    case 'textarea':
                        if ($aAttribute['expression']>=2) {
                            echo CHtml::tag('div',array('class'=>"input-group"),"",false);
                            echo CHtml::tag('div',array('class'=>"input-group-addon"),"{");
                        }
                        echo CHtml::textArea($aAttribute['name'],$aAttribute['value'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'aria-describedby'=>"help{$aAttribute['name']}",
                        ));
                        if ($aAttribute['expression']>=2) {
                            echo CHtml::tag('div',array('class'=>"input-group-addon"),"}");
                            echo CHtml::closeTag('div');
                        }
                        break;

                    // Question template selector
                    case 'question_template':
                        echo CHtml::dropDownList($aAttribute['name'],$aAttribute['value'],$aQuestionTemplates,array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'aria-describedby'=>"help{$aAttribute['name']}",
                        ));
                        break;

                }
                ?>
            </div>
        </div>
<?php endforeach;?>
<input type='hidden' name='advancedquestionsettingsLoaded' value="ok" />
 </div></div></div></div>
<?php
/* Launch all needed script (here after load) needed for widget */
foreach (Yii::app()->clientScript->scripts as $index=>$script)
{
    // Add specific view script
    $script[] = "$('.show-help').tooltip({ html:true, title : function() { return $($(this).attr('href')).html(); }, trigger: 'hover' });";
    echo CHtml::script(implode("\n",$script));
}
Yii::app()->clientScript->reset();
?>

<!-- end of Advanced Settings -->
