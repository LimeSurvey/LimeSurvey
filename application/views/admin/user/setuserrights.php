<?php echo CHtml::form(array("admin/user/sa/userrights"), 'post', array('name'=>'moduserrightsform', 'id'=>'moduserrightsform')); ?>
<table width='100%' border='0'>
<tr>
<td colspan='8' class='header ui-widget-header' align='center'>
<?php $clang->eT("Set User Rights");?>:<?php echo htmlspecialchars(sanitize_user($_POST['user']));?>
</td>
</tr>

<?php // HERE WE LIST FOR USER RIGHTS YOU CAN SET TO a USER
// YOU CAN ONLY SET AT MOST THE RIGHTS YOU have yourself
$userlist = getUserList();
foreach ($userlist as $usr) {
    if ($usr['uid'] == $postuserid) {
        $squery = "SELECT create_survey, configurator, create_user, delete_user, superadmin, participant_panel,manage_template, manage_label FROM {{users}} WHERE uid=".Yii::app()->session['loginID'];    //        added by Dennis
        $parent = Yii::app()->db->createCommand($squery)->queryRow();

        // Initial SuperAdmin has parent_id == 0
        $adminquery = "SELECT uid FROM {{users}} WHERE parent_id=0";
        $row = Yii::app()->db->createCommand($adminquery)->queryRow();
        ?>

        <tr>

        <?php // Only Initial SuperAdmin can give SuperAdmin rights
        if($row['uid'] == Yii::app()->session['loginID'])
        { // RENAMED AS SUPERADMIN
            echo "<th align='center' class='admincell'>".$clang->gT("Super-Administrator")."</th>\n";
        }
        if($parent['participant_panel']) {
            echo "<th align='center' >".$clang->gT("Participant panel")."</th>\n";
        }
        if($parent['create_survey']) {
            echo "<th align='center'>".$clang->gT("Create survey")."</th>\n";
        }
        if($parent['configurator']) {
            echo "<th align='center'>".$clang->gT("Configurator")."</th>\n";
        }
        if($parent['create_user']) {
            echo "<th align='center'>".$clang->gT("Create user")."</th>\n";
        }
        if($parent['delete_user']) {
            echo "<th align='center'>".$clang->gT("Delete user")."</th>\n";
        }
        if($parent['manage_template']) {
            echo "<th align='center'>".$clang->gT("Use all/manage templates")."</th>\n";
        }
        if($parent['manage_label']) {
            echo "<th align='center'>".$clang->gT("Manage labels")."</th>\n";
        }
        ?>

        </tr>
        <tr>

        <?php
        //// Only Initial SuperAdmmin can give SuperAdmin right
        if($row['uid'] ==  Yii::app()->session['loginID']) {
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"superadmin\" id=\"superadmin\" value=\"superadmin\"";
            if($usr['superadmin']) {
                echo " checked='checked' ";
            }
            echo "onclick=\"if (this.checked == true) { document.getElementById('create_survey').checked=true;document.getElementById('configurator').checked=true;document.getElementById('participant_panel').checked=true;document.getElementById('configurator').checked=true;document.getElementById('create_user').checked=true;document.getElementById('delete_user').checked=true;document.getElementById('manage_template').checked=true;document.getElementById('manage_label').checked=true;}\"";
            echo " />\n";
        }
        
        if($parent['participant_panel']) {
            echo "</td>\n";
            // Only Initial SuperAdmmin can give Participant Panel's right
            echo "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"participant_panel\" id=\"participant_panel\" value=\"participant_panel\"";
            if($usr['participant_panel']) {
                echo " checked='checked' ";
            }
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
        <input type='submit' value='<?php $clang->eT("Save Now");?>' />
        <input type='hidden' name='action' value='userrights' />
        <input type='hidden' name='uid' value='<?php echo $postuserid;?>' />
        </td>
        </tr>
        </table>
        </form>
        <?php continue;
    }    // if
}    // foreach
?>