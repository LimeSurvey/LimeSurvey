<script type="text/javascript">
    var url = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getAttributeBox"); ?>";
    var attname = "<?php eT("Attribute name:"); ?>";
    removeitem = new Array(); // Array to hold values that are to be removed from langauges option
</script>

<?php
    $aOptions = array();
    $aOptions[''] = gT('Select...');
    foreach (getLanguageData(false, Yii::app()->session['adminlang']) as $langkey2 => $langname)
    {
        $aOptions[$langkey2] = $langname['description'];
    }
?>

<div class="col-lg-12 list-surveys">
    <h3><?php eT("Attribute settings"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

    <?php echo CHtml::beginForm(Yii::app()->getController()->createUrl('admin/participants/sa/saveAttribute/aid/' . Yii::app()->request->getQuery('aid')) . '/', "post",array('class'=>'form-inline col-md-6  col-md-offset-3')); ?>

    <div class="form-group"><label for="atttype"><?php eT('Default attribute name:'); ?></label>
        <?php echo CHtml::textField('defaultname', $attributes['defaultname'],array('required'=>'required')); ?>
    </div>
    <div class="form-group "><label for="atttype"><?php eT('Attribute type:'); ?></label>
        <?php 
            echo CHtml::dropDownList('attribute_type', $attributes['attribute_type'], array(
                'TB' => 'Text box',
                'DD' => 'Drop-down list',
                'DP' => 'Date'),
                array('class'=>'form-control')
                
                );
        ?>
    </div>
    <div class="form-group"><label for='attvisible' id='attvisible'><?php eT('Attribute visible:') ?></label>
        <?php  echo CHtml::checkbox('visible', ($attributes['visible'] == "TRUE"),array('value'=>'TRUE','uncheckValue'=>'FALSE')); ?>
    </div>
    

<div id='ddtable' style='display: none'>
    <br/><br/>
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
                    <?php
                        $edit = array('src' => Yii::app()->getConfig('adminimageurl') . 'cancel_16.png',
                            'alt' => gT("Cancel editing"),
                            'width' => '16',
                            'class' => 'cancel',
                            'height' => '16',
                            'title' => gT("Cancel editing"));
                        echo CHtml::image($edit['src'], $edit['alt'], array_slice($edit, 2));
                        $edit = array('src' => Yii::app()->getConfig('adminimageurl') . 'edit_16.png',
                            'alt' => gT("Edit value"),
                            'width' => '15',
                            'class' => 'edit',
                            'name' => $value['value_id'],
                            'height' => '15',
                            'title' => gT("Edit value"));
                        echo CHtml::image($edit['src'], $edit['alt'], array_slice($edit, 2));
                        $del = array('src' => Yii::app()->getConfig('adminimageurl') . 'delete.png',
                            'alt' => gT("Delete value"),
                            'width' => '15',
                            'height' => '15',
                            'class'=> 'delete',
                            'title' => gT("Delete value"));
                        echo CHtml::link(CHtml::image($del['src'], $del['alt'], array_slice($del, 2)), $this->createUrl('admin/participants/sa/delAttributeValues/aid/' . $attributes['attribute_id'] . '/vid/' . $value['value_id']));
                ?></td>
            </tr>
            <?php
            }
        ?>
    </table>
    <table>
        <tr>
            <td></td>
            <td class='actions'>
                <a href='#' class='add'>
                    <img src = "<?php echo Yii::app()->getConfig('adminimageurl'); ?>plus.png" alt='<?php eT("Add value") ?>' title='<?php eT("Add value") ?>' id='addsign' name='addsign'>
                </a>
            </td>
        </tr>
    </table>
</div>

<div id="addlang">
    <table width='400' >
        <tr>
            <th colspan='2'>
                <?php eT('Add a language:'); ?>
            </th>
        </tr>
        <tr>
            <td class='data'>
                <?php
                    $plus = array('src' => Yii::app()->getConfig('adminimageurl') . "plus.png",
                        'alt' => gT('Add language'),
                        'title' => gT('Add language'),
                        'id' => 'add',
                        'hspace' => 2,
                        'vspace' => -6);

                    echo CHtml::dropDownList('langdata', '', $aOptions, array('class'=>'form-control'));
                ?>
            </td>
            <td class='actions'>
                <?php
                    echo CHtml::image($plus['src'], $plus['alt'], array_slice($plus, 2));
                ?>
            </td>
        </tr>
    </table>
</div>


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



<div class="tab-content">
        <?php foreach ($attributenames as $key => $value): ?>
                <div id="<?php echo $value['lang'] ?>" class="tab-pane fade in <?php if($key==0){ echo 'active'; }?>">
                    <table width='400' class='nudgeleft'>
                        <tr>
                            <th>
                                <label for='attname' id='attname'>
                                    <?php eT('Attribute name:'); ?>
                                </label>
                            </th>
                        </tr>
                        <tr>
                            <td class='data'>
                                <?php echo CHtml::textField($value['lang'], $value['attribute_name'], array('class'=>'languagesetting', 'style'=>'border: 1px solid #ccc')); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php echo CHtml::hiddenField('attname', $value['attribute_name']); ?>
        <?php endforeach;?>
</div>



<p>
    <?php
        echo CHtml::submitButton('submit', array('value' => gT('Save')));
        echo CHtml::endForm();
    ?>
</p>



        </div>
    </div>
</div>
            


