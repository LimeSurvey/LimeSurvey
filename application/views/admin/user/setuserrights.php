<?php echo CHtml::form(array("admin/user/sa/userrights"), 'post', array('name'=>'moduserrightsform', 'id'=>'moduserrightsform','class'=>'form44')); ?>
<div class="header ui-widget-header"><?php $clang->eT("Set User Rights");?>:<?php echo htmlspecialchars(sanitize_user($_POST['user']));?></div>
<div id="setuserright" class="ui-widget-content ui-corner-bottom">

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
        <ul>
        <?php if($row['uid'] == Yii::app()->session['loginID']) { ?>
            <li>
                <label for='superadmin'><?php echo $clang->gT("Super-Administrator") ?></label>
                <input type='checkbox' class='checkboxbtn' name='superadmin' id='superadmin' value='superadmin' <?php if($usr['superadmin']) {echo " checked='checked' ";} ?> 
                            onclick="if (this.checked == true) { document.getElementById('create_survey').checked=true;document.getElementById('configurator').checked=true;document.getElementById('participant_panel').checked=true;document.getElementById('configurator').checked=true;document.getElementById('create_user').checked=true;document.getElementById('delete_user').checked=true;document.getElementById('manage_template').checked=true;document.getElementById('manage_label').checked=true;}"
                />
            </li>
        <?php } ?>
        <?php if($parent['participant_panel']) { ?>
            <li>
                <label for="participant_panel"><?php echo $clang->gT("Participant panel"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='participant_panel' id='participant_panel' value='participant_panel' <?php if($usr['participant_panel']) {echo " checked='checked' ";} ?> >
            </li>
        <?php } ?>
        <?php if($parent['create_survey']) { ?>
            <li>
                <label for="create_survey"><?php echo $clang->gT("Create survey"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='create_survey' id='create_survey' value='create_survey' <?php if($usr['create_survey']) {echo " checked='checked' ";} ?> />
            </li>
        <?php } ?>
        <?php if($parent['configurator']) { ?>
            <li>
                <label for="configurator"><?php echo $clang->gT("Configurator"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='configurator' id='configurator' value='configurator' <?php if($usr['configurator']) {echo " checked='checked' ";} ?> />
            </li>
        <?php } ?>
        <?php if($parent['create_user']) { ?>
            <li>
                <label for="create_user"><?php echo $clang->gT("Create user"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='create_user' id='create_user' value='create_user' <?php if($usr['create_user']) {echo " checked='checked' ";} ?> />
            </li>
        <?php } ?>
        <?php if($parent['delete_user']) { ?>
            <li>
                <label for=""><?php echo $clang->gT("Delete user"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='delete_user' id='delete_user' value='delete_user' <?php if($usr['delete_user']) {echo " checked='checked' ";} ?> />
            </li>
        <?php } ?>
        <?php if($parent['manage_template']) { ?>
            <li>
                <label for="manage_template"><?php echo $clang->gT("Use all/manage templates"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='manage_template' id='manage_template' value='manage_template' <?php if($usr['manage_template']) {echo " checked='checked' ";} ?> />
            </li>
        <?php } ?>
        <?php if($parent['manage_label']) { ?>
            <li>
                <label for=""><?php echo $clang->gT("Manage labels"); ?></label>
                <input type='checkbox' class='checkboxbtn' name='manage_label' id='manage_label' value='manage_label' <?php if($usr['manage_label']) {echo " checked='checked' ";} ?> />
            </li>
        <?php } ?>
    </ul>
    <?php 
    }    // if
}    // foreach
?>
    <p>
        
        <input class="standardbtn" type='submit' value='<?php $clang->eT("Save Now");?>' />
        <input type='hidden' name='action' value='userrights' />
        <input type='hidden' name='uid' value='<?php echo $postuserid;?>' />
    </p>
</div>
