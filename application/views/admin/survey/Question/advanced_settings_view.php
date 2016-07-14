<?php
/**
 * This view generate the advanced question attributes
 */
$currentfieldset='';
?>
<!-- Advanced Settings -->
<?php foreach ($attributedata as $index=>$aAttribute):?>

    <!-- Fieldsets -->
    <?php if ($currentfieldset!=$aAttribute['category']): ?>
        <?php if ($currentfieldset!=''): ?>
            </fieldset>
        <?php endif; ?>
        <?php $currentfieldset=$aAttribute['category']; ?>
        <fieldset>
        <legend><?php echo $aAttribute['category'];?></legend>
    <?php endif; ?>

    <!-- Form Group -->
    <div class="form-group">

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
                            <input type='text' class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo htmlspecialchars($aAttribute['value'],ENT_QUOTES, 'UTF-8');?>' />
                            <?php
                            break;

                        // Integer
                        case 'integer':?>
                            <input type='text' class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo $aAttribute['value'];?>' />
                            <?php
                            break;

                        // Interger
                        case 'columns':?>
                            <input type='number' min="1" max="12" class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo $aAttribute['value'];?>' />
                            <?php
                            break;


                        // Textarea
                        case 'textarea':?>
                            <textarea class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>'><?php echo $aAttribute['value'];?></textarea>
                            <?php
                            break;
                    }
                }?>
            </div>
        </div>
<?php endforeach;
foreach (Yii::app()->clientScript->scripts as $index=>$script)
{
    echo CHtml::script(implode("\n",$script));
}
Yii::app()->clientScript->reset();
?>
</fieldset>
<!-- end of Advanced Settings -->
