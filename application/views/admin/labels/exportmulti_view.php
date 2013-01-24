<script type='text/javascript'>
    <!--
    var strSelectLabelset='<?php $clang->eT('You have to select at least one label set.','js');?>';
    //-->
</script>

<div class='header ui-widget-header'><?php $clang->eT('Export multiple label sets');?></div>
<?php echo CHtml::form(array("admin/export/sa/dumplabel"), 'post', array('id'=>'exportlabelset','class'=>'form30')); ?>
    <ul>
        <li><label for='labelsets'><?php $clang->eT('Please choose the label sets you want to export:');?><br /><?php $clang->eT('(Select multiple label sets by using the Ctrl key)');?></label>
            <select id='labelsets' multiple='multiple' name='lids[]' size='20'>
                <?php if (count($labelsets)>0)
                    {
                        foreach ($labelsets as $lb)
                        {
                            echo "<option value='{$lb[0]}'>{$lb[0]}: {$lb[1]}</option>\n";
                        }
                } ?>

            </select></li>
    </ul><p><input type='submit' id='btnDumpLabelSets' value='<?php $clang->eT('Export selected label sets');?>' />
    <input type='hidden' name='action' value='dumplabel' />
</form>
