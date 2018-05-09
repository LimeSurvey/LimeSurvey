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
                <div class="panel-title h4">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion">
                        <span class="fa fa-chevron-left"></span>
                        <span class="sr-only"><?php eT("Expand/Collapse");?></span>
                    </a>
                    <a id="button-collapse<?php echo $categoryNum ?>" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-cat<?php echo $categoryNum ?>" aria-expanded="false" aria-controls="collapse-cat<?php echo $categoryNum ?>">
                        <?php echo $aAttribute['category']; ?>
                    </a>
                </div>
            </div>
            <div id="collapse-cat<?php echo $categoryNum ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="button-collapse<?php echo $categoryNum ?>">
                <div class="panel-body">
                    <div>

        <?php $currentfieldset=$aAttribute['category']; ?>
    <?php endif; ?>
    <div class="form-group">
    <!-- Form Group -->
        <!-- Label -->
        <label class="control-label" for='<?php echo $aAttribute['name'];?>' title='<?php echo $aAttribute['help'];?>'>
            <?php
                echo $aAttribute['caption'];
                if ($aAttribute['i18n']==true) { ?> (<?php echo $aAttribute['language'] ?>)<?php }
            ?>:
        </label>

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
                            ),
                        ));
                        break;
                        // Single select
                    case 'singleselect':
                        echo CHtml::dropDownList($aAttribute['name'],$aAttribute['value'],$aAttribute['options'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'encode'=>false, // gt encode it by default
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
                            'max'=>(isset($aAttribute['max'])?$aAttribute['max']:null)
                        ));
                        break;

                    // Interger
                    case 'columns':
                        echo CHtml::numberField($aAttribute['name'],$aAttribute['value'],array(
                            'class'=>"form-control",
                            'disabled'=>$readonly,
                            'step'=>1,
                            'pattern'=>'\d+',
                            'min'=>1,
                            'max'=>12
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
                        ));
                        break;

                }
                ?>
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
