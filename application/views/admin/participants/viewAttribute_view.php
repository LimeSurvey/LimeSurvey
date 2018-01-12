<script type="text/javascript">
    var url = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getAttributeBox"); ?>";
    var attname = "<?php eT("Attribute name:"); ?>";
    removeitem = new Array(); // Array to hold values that are to be removed from langauges option
</script>

<?php
    $aOptions = array();
    $aOptions[''] = gT('Select...','unescaped');
    foreach (getLanguageData(false, Yii::app()->session['adminlang']) as $langkey2 => $langname)
    {
        $aOptions[$langkey2] = $langname['description'];
    }
?>

<div class="col-lg-12 list-surveys">
    <h3><?php eT("Attribute settings"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

            <?php echo CHtml::beginForm(Yii::app()->getController()->createUrl('admin/participants/sa/saveAttribute/aid/' . Yii::app()->request->getQuery('aid')) . '/', "post",array('class'=>' col-md-6  col-md-offset-3', 'role' => 'form')); ?>

            <div class="form-group">
                <label for="defaultname" class='control-label col-sm-3'><?php eT('Default attribute name:'); ?></label>
                <div class='col-sm-3'>
                    <?php echo CHtml::textField('defaultname', $attributes['defaultname'],array('required'=>'required', 'class' => 'form-control')); ?>
                </div>
            </div>

            <div class="form-group ">
                <label for="atttype" class='col-sm-3 control-label'><?php eT('Attribute type:'); ?></label>
                <div class='col-sm-3'>
                    <?php
                        echo CHtml::dropDownList('attribute_type', $attributes['attribute_type'], array(
                            'TB' => 'Text box',
                            'DD' => 'Drop-down list',
                            'DP' => 'Date'),
                            array('class'=>'form-control')
                        );
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label for='attvisible' id='attvisible' class='col-sm-3 control-label'><?php eT('Attribute visible:') ?></label>
                <div class='col-sm-3'>
                    <?php  echo CHtml::checkbox('visible', ($attributes['visible'] == "TRUE"),array('value'=>'TRUE','uncheckValue'=>'FALSE')); ?>
                </div>
            </div>


<div id='ddtable' style='display: none'>
    <table class='hovertable table table-striped'>
        <thead>
            <tr>
                <th colspan='2'><?php eT('Values:'); ?></th>
            </tr>
        </thead>
        <?php
            foreach ($attributevalues as $row => $value)
            {
            ?>
            <tr>
                <td class='data' data-text='<?php echo $value['value']; ?>' data-id='<?php echo $value['value_id']; ?>'>
                    <div class=editable id="<?php echo $value['value_id']; ?>">
                        <?php
                            echo $value['value'];
                        ?>
                    </div>
                </td>
                <td class='actions'>
                    <span data-toggle='tooltip' data-placement='bottom' class="fa fa-remove-circle text-warning cancel ui-pg-button" title="<?php eT('Cancel editing'); ?>"></span>
                    <span data-toggle='tooltip' data-placement='bottom' class="fa fa-pencil text-success edit ui-pg-button" name="<?php echo $value['value_id']; ?>" title="<?php eT('Edit value'); ?>"></span>
                    <a href="<?php echo $this->createUrl('admin/participants/sa/delAttributeValues/aid/' . $attributes['attribute_id'] . '/vid/' . $value['value_id']); ?>" title="<?php eT('Delete value'); ?>" >
                        <span data-toggle='tooltip' data-placement='bottom' class="fa fa-trash text-warning delete ui-pg-button" title="<?php eT('Delete value'); ?>"></span>
                    </a>
                </td>
            </tr>
            <?php
            }
        ?>
    </table>
    <a href='#' class='add' id='add_new_attribute'>
        <span data-toggle='tooltip' data-placement='bottom' class="icon-add text-success" title='<?php eT("Add value") ?>' id='addsign' name='addsign'></span>
    </a>
</div>

<div id="addlang" class='form-group'>
    <label class='control-label col-sm-3'><?php eT('Add a language:'); ?></label>
    <div class='col-sm-3'>
        <?php echo CHtml::dropDownList('langdata', '', $aOptions, array('class'=>'form-control')); ?>
    </div>
    <span data-toggle='tooltip' data-placement='bottom' class="ui-pg-button icon-add text-success" id="add" title="<?php eT('Add language'); ?>" ></span>
</div>
<br />

<div id='tabs'>
<ul class="nav nav-tabs" id="">
    <?php foreach ($attributenames as $key => $value): ?>
        <li role="presentation" <?php if($key==0){ echo 'class="active"'; }?>>
            <a data-toggle="tab" href='#<?php echo $value['lang']; ?>'>
                <?php echo $aOptions[$value['lang']] ?>
            </a>
            <script type='text/javascript'>
                removeitem.push('<?php echo $value['lang'] ?>');
            </script>
        </li>
    <?php endforeach;?>
    </ul>
    <?php //var_dump($attributenames); ?>
    <?php
        foreach ($attributenames as $key => $value)
        {
        ?>
        <div class='commonsettings'>
            <div id="<?php echo $value['lang'] ?>" class='form-group' style='padding-top: 1em;'>
                    <label class='control-label col-sm-3' for='attname' id='attname'>
                        <?php eT('Attribute name:'); ?>
                    </label>
                    <div class='col-sm-3'>
                        <?php echo CHtml::textField('lang[' . $value['lang'] . ']', $value['attribute_name'], array('class'=>'languagesetting form-control')); ?>
                    </div>
            </div>
        </div>
        <?php
        }
        echo CHtml::hiddenField('attname', $value['attribute_name']);
    ?>
</div>

<div class='form-group'>
    <div class='col-sm-3 col-sm-offset-0'>
        <?php
            echo CHtml::submitButton('submit', array('value' => gT('Save'), 'class' => 'btn btn-default'));
        ?>
    </div>
</div>
<?php echo CHtml::endForm(); ?>



        </div>
    </div>
</div>
