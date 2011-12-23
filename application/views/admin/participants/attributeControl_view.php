<script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery-ui.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts') . "attributeControl.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
    var saveVisibleMsg = "<?php $clang->eT("Attribute visiblity changed") ?>";
    var saveVisible = "<?php echo Yii::app()->baseUrl . "/index.php/admin/participants/sa/saveVisible"; ?>";
</script>
<div class='header ui-widget-header'>
    <strong>
        <?php $clang->eT("Attribute control"); ?>
    </strong>
</div>
<?php
$attribute = array('class' => 'form44');
echo CHtml::beginForm('storeAttributes', 'post', $attribute);
?>
<br></br>
<ul>
    <li>
        <table id='atttable'class='hovertable'>
            <tr>
                <th><?php $clang->eT("Attribute name"); ?></th>
                <th><?php $clang->eT("Attribute type"); ?></th>
                <th><?php $clang->eT("Visible in participant panel"); ?></th>
                <th><?php $clang->eT("Actions"); ?></th>
            </tr>
            <?php
            foreach ($result as $row => $value)
            {
                ?>
                <tr>
                    <td>
                        <?php
                        echo $value['attribute_name'];
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($value['attribute_type'] == 'DD')
                        {
                            $clang->eT("Drop-down list");
                        }
                        elseif ($value['attribute_type'] == 'DP')
                        {
                            $clang->eT("Date");
                        }
                        else
                        {
                            $clang->eT("Text box");
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($value['visible'] == "TRUE")
                        {
                            $data = array('name' => 'visible_' . $value['attribute_id'],
                                'id' => 'visible_' . $value['attribute_id'],
                                'value' => 'TRUE',
                                'checked' => TRUE);
                        }
                        else
                        {
                            $data = array('name' => 'visible_' . $value['attribute_id'],
                                'id' => 'visible_' . $value['attribute_id'],
                                'value' => 'TRUE',
                                'checked' => FALSE);
                        }
                        echo CHtml::checkbox($data['name'], $data['checked'], array('value' => $data['value']));
                        ?>
                    </td>
                    <td>
                        <?php
                        $edit = array('src' => Yii::app()->baseUrl . '/images/token_edit.png',
                            'alt' => 'Edit',
                            'width' => '15',
                            'height' => '15',
                            'title' => 'Edit attribute');
                        echo CHtml::link(CHtml::image($edit['src'], $edit['alt'], array_slice($edit, 2)), 'viewAttribute/aid/' . $value['attribute_id']);
                        $del = array('src' => Yii::app()->baseUrl . '/images/error_notice.png',
                            'alt' => 'Delete',
                            'width' => '15',
                            'height' => '15',
                            'title' => 'Delete attribute');
                        echo CHtml::link(CHtml::image($del['src'], $del['alt'], array_slice($del, 2)), 'delAttribute/aid/' . $value['attribute_id']);
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </li>
    <li>
        <a href="#" class="add"><img src = "<?php echo Yii::app()->baseUrl . '/images/plus.png' ?>" alt="Add attribute" width="25" height="25" title="Add attribute" id="add" name="add" /></a>
    </li>
</ul>
<br/>
<p><input type="submit" name="Save" value="Save" /></p>
<?php echo CHtml::endForm(); ?>
