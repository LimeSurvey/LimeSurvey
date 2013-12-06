<?php echo CHtml::form(array("admin/user/sa/usertemplates"), 'post', array('name'=>'modtemplaterightsform', 'id'=>'modtemplaterightsform')); ?>

	<div class='header ui-widget-header'><?php $clang->eT('Edit template permissions');?></div>
    <table id="user-template-permissions" class="activecell" style="margin:0 auto;">
	<thead>
	<tr>
	<th colspan="2" class="header">
    <?php $clang->eT('Set templates that this user may access');?>: <?php echo htmlspecialchars(sanitize_user($_POST['user']));?></th>
	</tr>
	<?php
    foreach ($list as $data)
    {
        ?>
        <tr><th>
        <?php $clang->eT('Template name');?>
        <br />&nbsp;</th><th>
        <?php $clang->eT('Allowed');?>
        <br /><input type='checkbox' alt='<?php $clang->eT("Check or uncheck all items");?>' class='tipme' id='checkall' />
        </th></tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="3">
                    <input type="submit" value="<?php $clang->eT('Save settings');?>" />
                    <input type="hidden" name="action" value="usertemplates" />
                    <input type="hidden" name="uid" value="<?php echo $postuserid; ?>" />
                </td>
            </tr>
        </tfoot>

        <tbody>

        <?php
        $templaterights=$data['templaterights'];
        $table_row_odd_even = 'odd';
        foreach ($data['templates'] as $trow)
        {
            if($table_row_odd_even == 'odd' )
            {
                $table_row_odd_even = 'even';
            }
            else
            {
                $table_row_odd_even = 'odd';
            }
            echo "\t<tr class=\"$table_row_odd_even\">\n<td>".$trow["folder"]."</td>\n";
            echo "<td><input type=\"checkbox\" class=\"checkboxbtn\" name=\"".$trow["folder"]."_use\" value=\"".$trow["folder"]."_use\"";

            if(isset($templaterights[$trow['folder']]) && $templaterights[$trow['folder']]['use'] == 1)
            {
                echo ' checked="checked"';
            }
            echo " /></td>\n\t</tr>\n";
        } ?>
        </tbody>
        </table>
        </form>
<?php
    }
?>
