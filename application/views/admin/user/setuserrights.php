<div class="header ui-widget-header"><?php $clang->eT("Set User Rights");?></div>
<div id="tabs">
    <ul>
        <?php foreach($users as $user) { ?>
        <li><a href="#setuserright-<?php echo $user['uid']; ?>"><?php echo htmlspecialchars(sanitize_user($user['users_name']));?></a></li>
        <?php } ?>
    </ul>
<?php // Fill an array for label string
$RightText=array(
    'superadmin'=>array('label'=>$clang->gT("Super-Administrator"),'information'=>$clang->gT("Give all other rights, user have complete access to LimeSurvey except give Super-Administrator right")),
    'configurator'=>array('label'=>$clang->gT("Configurator"),'information'=>$clang->gT("Give access to global settings.")),
    'manage_survey'=>array('label'=>$clang->gT("Manage survey"),'information'=>$clang->gT("Give complete administration rights to all survey, except survey creation and change owner of survey.")),
    'create_survey'=>array('label'=>$clang->gT("Create survey"),'information'=>$clang->gT("Allow user to create survey. This user are the owner of the survey created ")),
    'participant_panel'=>array('label'=>$clang->gT("Participant panel"),'information'=>$clang->gT("Access and administration of the participant panel.")),
    'create_user'=>array('label'=>$clang->gT("Create user"),'information'=>$clang->gT("User can create new user.")),
    'delete_user'=>array('label'=>$clang->gT("Delete user"),'information'=>$clang->gT("User can delete the user he create.")),
    'manage_template'=>array('label'=>$clang->gT("Use all/manage templates"),'information'=>$clang->gT("User can manage template: modify, create or delete template.")),
    'manage_label'=>array('label'=>$clang->gT("Manage labels"),'information'=>$clang->gT("User can manage all label sets:  modify, create or delete label sets.")),
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
                    $inputclass=" with-superadmin";
                }?>
                <li>
                    <label for='<?php echo $userright; ?>' class='<?php echo $labelclass; ?>' title='<?php echo $RightText[$userright]['information']; ?>'><?php echo $RightText[$userright]['label']; ?></label>
                    <?php echo CHtml::checkBox($userright,$user[$userright],array('value'=>$userright,'class'=>"checkboxbtn {$inputclass}")); ?>
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
