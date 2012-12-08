
<div class="header ui-widget-header"><?php $clang->eT("Set User Rights");?></div>
<div id="tabs">
    <ul>
        <li><a href="#setuserright-<?php echo $postuserid; ?>"><?php echo htmlspecialchars(sanitize_user($_POST['user']));?></a></li>
    </ul>

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
        <div id="setuserright-<?php echo $usr['uid'];?>">
        <?php echo CHtml::form(array("admin/user/sa/userrights"), 'post', array('name'=>'moduserrightsform', 'id'=>'moduserrightsform','class'=>'form44')); ?>
        <ul>
        <?php if($row['uid'] == Yii::app()->session['loginID']) { ?>
            <li>
                <label for='superadmin' class='warning warningtitle'><?php echo $clang->gT("Super-Administrator") ?></label>
                <?php echo CHtml::checkBox('superadmin',$usr['superadmin'],array('value'=>'superadmin','class'=>'checkboxbtn')) ?>
            </li>
        <?php } ?>
        <?php if($parent['participant_panel']) { ?>
            <li>
                <label for="participant_panel"><?php echo $clang->gT("Participant panel"); ?></label>
                <?php echo CHtml::checkBox('participant_panel',$usr['participant_panel'],array('value'=>'participant_panel','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
        <?php if($parent['create_survey']) { ?>
            <li>
                <label for="create_survey"><?php echo $clang->gT("Create survey"); ?></label>
                <?php echo CHtml::checkBox('create_survey',$usr['create_survey'],array('value'=>'create_survey','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
        <?php if($parent['configurator']) { ?>
            <li>
                <label for="configurator"><?php echo $clang->gT("Configurator"); ?></label>
                <?php echo CHtml::checkBox('configurator',$usr['configurator'],array('value'=>'configurator','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
        <?php if($parent['create_user']) { ?>
            <li>
                <label for="create_user"><?php echo $clang->gT("Create user"); ?></label>
                <?php echo CHtml::checkBox('create_user',$usr['create_user'],array('value'=>'create_user','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
        <?php if($parent['delete_user']) { ?>
            <li>
                <label for=""><?php echo $clang->gT("Delete user"); ?></label>
                <?php echo CHtml::checkBox('delete_user',$usr['delete_user'],array('value'=>'delete_user','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
        <?php if($parent['manage_template']) { ?>
            <li>
                <label for="manage_template"><?php echo $clang->gT("Use all/manage templates"); ?></label>
                <?php echo CHtml::checkBox('manage_template',$usr['manage_template'],array('value'=>'manage_template','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
        <?php if($parent['manage_label']) { ?>
            <li>
                <label for=""><?php echo $clang->gT("Manage labels"); ?></label>
                <?php echo CHtml::checkBox('manage_label',$usr['manage_label'],array('value'=>'manage_label','class'=>'checkboxbtn withadmin')) ?>
            </li>
        <?php } ?>
    </ul>
    <p>
        
        <input class="standardbtn" type='submit' value='<?php $clang->eT("Save Now");?>' />
        <input type='hidden' name='action' value='userrights' />
        <input type='hidden' name='uid' value='<?php echo $postuserid;?>' />
    </p>
    <?php echo CHtml::endForm();?>
    </div>
    <?php 
    }    // if
}    // foreach
?>
</div>
