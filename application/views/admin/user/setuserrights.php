<div class="header ui-widget-header"><?php $clang->eT("Set User Rights");?></div>
<div id="tabs">
    <ul>
        <?php foreach($users as $user) { ?>
        <li><a href="#setuserright-<?php echo $user['uid']; ?>"><?php echo htmlspecialchars(sanitize_user($user['users_name']));?></a></li>
        <?php } ?>
    </ul>
<?php // Fill an array for label string
$RightLabels=array(
    'superadmin'=>$clang->gT("Super-Administrator"),
    'configurator'=>$clang->gT("Configurator"),
    'create_survey'=>$clang->gT("Create survey"),
    'participant_panel'=>$clang->gT("Participant panel"),
    'create_user'=>$clang->gT("Create user"),
    'delete_user'=>$clang->gT("Delete user"),
    'manage_template'=>$clang->gT("Use all/manage templates"),
    'manage_label'=>$clang->gT("Manage labels"),
);
?>
    <?php foreach($users as $user) { ?>
        <div id="setuserright-<?php echo $user['uid'];?>">
        <?php echo CHtml::form(array("admin/user/sa/userrights"), 'post', array('name'=>'moduserrightsform', 'id'=>'moduserrightsform','class'=>'form44')); ?>
            <ul>
            <?php foreach($allowedRights as $userright){
                if($userright=='superadmin')
                {
                    $labelclass=" warning warningtitle";
                    $inputclass=" superadmin";
                }
                else
                {
                    $labelclass="";
                    $inputclass=" withadmin";
                }?>
                <li>
                    <label for='<?php echo $userright; ?>' class='<?php echo $labelclass; ?>'><?php echo $RightLabels[$userright]; ?></label>
                    <?php echo CHtml::checkBox($userright,$user[$userright],array('value'=>$userright,'class'=>'checkboxbtn $inputclass')); ?>
                </li>
            <?php } ?>
            </ul>
            <p>
                
                <input class="standardbtn" type='submit' value='<?php $clang->eT("Save Now");?>' />
                <input type='hidden' name='action' value='userrights' />
                <input type='hidden' name='uid' value='<?php echo $user['uid'];?>' />
            </p>
            <?php echo CHtml::endForm();?>
        </div>
    <?php } ?>
</div>
