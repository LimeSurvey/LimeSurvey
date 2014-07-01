<script type="text/javascript">
    var url = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getAttributeBox"); ?>";
    var attname = "<?php $clang->eT("Attribute name:"); ?>";
    removeitem = new Array(); // Array to hold values that are to be removed from langauges option
</script>
<div class='header ui-widget-header'><strong><?php $clang->eT("Attribute settings"); ?></strong></div><br/>
<?php
    $aOptions = array();
    $aOptions[''] = $clang->gT('Select...');
    foreach (getLanguageData(false, Yii::app()->session['adminlang']) as $langkey2 => $langname)
    {
        $aOptions[$langkey2] = $langname['description'];
    }
    echo CHtml::beginForm(Yii::app()->getController()->createUrl('admin/participants/sa/saveAttribute/aid/' . Yii::app()->request->getQuery('aid')) . '/', "post",array('class'=>'form44'));
?>
<ul>
    <li><label for="atttype"><?php $clang->eT('Default attribute name:'); ?></label>
        <?php echo CHtml::textField('defaultname', $attributes['defaultname'],array('required'=>'required')); ?>
    </li>
    <li><label for="atttype"><?php $clang->eT('Attribute type:'); ?></label>
        <?php 
            echo CHtml::dropDownList('attribute_type', $attributes['attribute_type'], array(
                'TB' => 'Text box',
                'DD' => 'Drop-down list',
                'DP' => 'Date'));
        ?>
    </li>
    <li><label for='attvisible' id='attvisible'><?php $clang->eT('Attribute visible:') ?></label>
        <?php  echo CHtml::checkbox('visible', ($attributes['visible'] == "TRUE"),array('value'=>'TRUE','uncheckValue'=>'FALSE')); ?>
    </li>
</ul>

<div id='ddtable' style='display: none'>
    <table class='hovertable'>
        <tr>
            <th colspan='2'><?php $clang->eT('Values:'); ?></th>
        </tr>
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
                            'alt' => $clang->gT("Cancel editing"),
                            'width' => '16',
                            'class' => 'cancel',
                            'height' => '16',
                            'title' => $clang->gT("Cancel editing"));
                        echo CHtml::image($edit['src'], $edit['alt'], array_slice($edit, 2));
                        $edit = array('src' => Yii::app()->getConfig('adminimageurl') . 'edit_16.png',
                            'alt' => $clang->gT("Edit value"),
                            'width' => '15',
                            'class' => 'edit',
                            'name' => $value['value_id'],
                            'height' => '15',
                            'title' => $clang->gT("Edit value"));
                        echo CHtml::image($edit['src'], $edit['alt'], array_slice($edit, 2));
                        $del = array('src' => Yii::app()->getConfig('adminimageurl') . 'delete.png',
                            'alt' => $clang->gT("Delete value"),
                            'width' => '15',
                            'height' => '15',
                            'class'=> 'delete',
                            'title' => $clang->gT("Delete value"));
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
                    <img src = "<?php echo Yii::app()->getConfig('adminimageurl'); ?>plus.png" alt='<?php $clang->eT("Add value") ?>' title='<?php $clang->eT("Add value") ?>' id='addsign' name='addsign'>
                </a>
            </td>
        </tr>
    </table>
</div>

<div id="addlang">
    <table width='400' >
        <tr>
            <th colspan='2'>
                <?php $clang->eT('Add a language:'); ?>
            </th>
        </tr>
        <tr>
            <td class='data'>
                <?php
                    $plus = array('src' => Yii::app()->getConfig('adminimageurl') . "plus.png",
                        'alt' => $clang->gT('Add language'),
                        'title' => $clang->gT('Add language'),
                        'id' => 'add',
                        'hspace' => 2,
                        'vspace' => -6);

                    echo CHtml::dropDownList('langdata', '', $aOptions);
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
<div id='tabs'>
    <ul>
        <?php
            foreach ($attributenames as $key => $value)
            {
            ?>
            <li>
                <a href="#<?php echo $value['lang']; ?>">
                    <?php echo $aOptions[$value['lang']] ?>
                </a>
            </li>
            <script type='text/javascript'>
                removeitem.push('<?php echo $value['lang'] ?>');
            </script>
            <?php
            }
        ?>
    </ul>
    <?php
        foreach ($attributenames as $key => $value)
        {
        ?>
        <div class='commonsettings'>
            <div id="<?php echo $value['lang'] ?>">
                <table width='400' class='nudgeleft'>
                    <tr>
                        <th>
                            <label for='attname' id='attname'>
                                <?php $clang->eT('Attribute name:'); ?>
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
        </div>

        <?php
        }
        echo CHtml::hiddenField('attname', $value['attribute_name']);
    ?>
    <br />
</div>

<br/>
<p>
    <?php
        echo CHtml::submitButton('submit', array('value' => $clang->gT('Save')));
        echo CHtml::endForm();
    ?>
</p>

