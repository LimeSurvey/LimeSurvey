<form action='<?php echo site_url("admin/user/usertemplates");?>' method='post'>
	<div class='header ui-widget-header'><?php echo $clang->gT('Edit template permissions');?></div>
    <table id="user-template-rights" width='50%' border='0' cellpadding='3' style='margin:5px auto 0 auto;'>
	<thead>
	<tr>
	<th colspan="2" style="background-color:#000; color:#fff;">
    <?php echo $clang->gT('Set templates that this user may access');?>: <?php echo $_POST['user'];?></th>
	</tr>
	<?php
    foreach ($userlist as $usr)
    {
        if ($usr['uid'] == $postuserid)
        {
            $templaterights = array();
            $squery = 'SELECT '.$this->db->escape('folder').','.$this->db->escape('use')." FROM ".$this->db->dbprefix("templates_rights")." WHERE uid={$usr['uid']}";
            $sresult = db_execute_assoc($squery) or safe_die($connect->ErrorMsg());//Checked
            foreach ($sresult->row_array() as $srow)
            {
                $templaterights[$srow["folder"]] = array("use"=>$srow["use"]);
            }
			?>
            <tr><th>
            <?php echo $clang->gT('Template name');?>
            <br />&nbsp;</th><th>
            <?php echo $clang->gT('Allowed');?>
            <br /><input type='checkbox' alt='<?php echo $clang->gT("Check or uncheck all items");?>' class='tipme' id='checkall' />
            </th></tr>
            </thead>
			
            <tfoot>
            <tr>
            <td colspan="3">
            <input type="submit" value="<?php echo $clang->gT('Save settings');?>" />
            <input type="hidden" name="action" value="usertemplates" />
            <input type="hidden" name="uid" value="<?php echo $postuserid;?>" />
            </td>
            </tr>
            </tfoot>
			
			<tbody>

            <?php $tquery = "SELECT * FROM ".$this->db->dbprefix("templates");
            $tresult = db_execute_assoc($tquery) or safe_die($connect->ErrorMsg()); //Checked

            $table_row_odd_even = 'odd';
            foreach ($tresult->result_array() as $trow)
            {
                if($table_row_odd_even == 'odd' )
                {
                    $row_class = ' class="row_odd"';
                    $table_row_odd_even = 'even';
                }
                else
                {
                    $row_class = ' class="row_even"';
                    $table_row_odd_even = 'odd';
                }
                echo "\t<tr$row_class>\n<td>".$trow["folder"]."</td>\n";
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
            continue;
        }
    }
?>