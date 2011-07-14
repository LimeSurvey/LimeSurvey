<form method='post' action='<?php echo site_url("admin/user/userrights");?>'>
	
<table width='100%' border='0'>
<tr>
<td colspan='7' class='header ui-widget-header' align='center'>
<?php echo $clang->gT("Set User Rights");?>: <?php echo htmlspecialchars(sanitize_user($_POST['user']));?>
</td>
</tr>

<?php // HERE WE LIST FOR USER RIGHTS YOU CAN SET TO a USER
// YOU CAN ONLY SET AT MOST THE RIGHTS YOU have yourself
$userlist = getuserlist();
foreach ($userlist as $usr) { 
    if ($usr['uid'] == $postuserid) {
        $squery = "SELECT create_survey, configurator, create_user, delete_user, superadmin, manage_template, manage_label FROM ".$this->db->dbprefix("users")." WHERE uid=".$this->session->userdata['loginID'];	//		added by Dennis
        $sresult = db_select_limit_assoc($squery); //Checked
        $parent = $sresult->row_array();

        // Initial SuperAdmin has parent_id == 0
        $adminquery = "SELECT uid FROM ".$this->db->dbprefix("users")." WHERE parent_id=0";
        $adminresult = db_select_limit_assoc($adminquery, 1);
        $row=$adminresult->row_array();
		?>
		
        <tr>
         
        <?php // Only Initial SuperAdmin can give SuperAdmin rights
        if($row['uid'] == $this->session->userdata('loginID'))
        { // RENAMED AS SUPERADMIN
            echo "<th align='center' class='admincell'>".$clang->gT("SuperAdministrator")."</th>\n";
        }
        if($parent['create_survey']) {
            echo "<th align='center'>".$clang->gT("Create Survey")."</th>\n";
        }
        if($parent['configurator']) {
            echo "<th align='center'>".$clang->gT("Configurator")."</th>\n";
        }
        if($parent['create_user']) {
            echo "<th align='center'>".$clang->gT("Create User")."</th>\n";
        }
        if($parent['delete_user']) {
            echo "<th align='center'>".$clang->gT("Delete User")."</th>\n";
        }
        if($parent['manage_template']) {
            echo "<th align='center'>".$clang->gT("Use all/manage templates")."</th>\n";
        }
        if($parent['manage_label']) {
            echo "<th align='center'>".$clang->gT("Manage Labels")."</th>\n";
        }
		?>

        </tr>
        <tr>

        <?php // Only Initial SuperAdmmin can give SuperAdmin right
        if($row['uid'] ==  $this->session->userdata('loginID')) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"superadmin\" id=\"superadmin\" value=\"superadmin\"";
            if($usr['superadmin']) {
                echo " checked='checked' ";
            }
            echo "onclick=\"if (this.checked == true) {document.getElementById('create_survey').checked=true;document.getElementById('configurator').checked=true;document.getElementById('create_user').checked=true;document.getElementById('delete_user').checked=true;document.getElementById('manage_template').checked=true;document.getElementById('manage_label').checked=true;}\"";
            echo " /></td>\n";
        }
        if($parent['create_survey']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_survey\" id=\"create_survey\" value=\"create_survey\"";
            if($usr['create_survey']) {
                echo " checked='checked' ";
            }
            echo " /></td>\n";
        }
        if($parent['configurator']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"configurator\" id=\"configurator\" value=\"configurator\"";
            if($usr['configurator']) {
                echo " checked='checked' ";
            }
            echo " /></td>\n";
        }
        if($parent['create_user']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_user\" id=\"create_user\" value=\"create_user\"";
            if($usr['create_user']) {
                echo " checked='checked' ";
            }
            echo " /></td>\n";
        }
        if($parent['delete_user']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_user\" id=\"delete_user\" value=\"delete_user\"";
            if($usr['delete_user']) {
                echo " checked='checked' ";
            }
            echo " /></td>\n";
        }
        if($parent['manage_template']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_template\" id=\"manage_template\" value=\"manage_template\"";
            if($usr['manage_template']) {
                echo " checked='checked' ";
            }
            echo " /></td>\n";
        }
        if($parent['manage_label']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_label\" id=\"manage_label\" value=\"manage_label\"";
            if($usr['manage_label']) {
                echo " checked='checked' ";
            }
            echo " /></td>\n";
        }
		?>
        </tr>

        <tr>
        <td colspan='7' align='center'>
        <input type='submit' value='<?php echo $clang->gT("Save Now");?>' />
        <input type='hidden' name='action' value='userrights' />
        <input type='hidden' name='uid' value='<?php echo $postuserid;?>' />
        </td>
        </tr>
        </table>
        </form>
        <?php continue;
    }	// if
}	// foreach
?>