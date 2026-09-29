<?php

/* @var $surveyid int */
/* @var $aPermissions array has all permissions in */
/* @var $isUserGroup bool indicates that permissions for a user group should be set */
/* @var $id int */
/* @var $name string */

/**
 * This page shows the permissions that could be set for a user or a user group.
 */

?>

<div id='edit-permission' class='side-body'>
    <h3>
        <?php
        if ($isUserGroup) {
            echo sprintf(gT("Edit survey permissions for user group %s"), "<em>" . \CHtml::encode($name) . "</em>");
        } else {
            echo sprintf(gT("Edit survey permissions for user %s"), "<em>" . \CHtml::encode($name) . "</em>");
        }
        ?>
    </h3>
    <div class="row" id="trigger-save-button">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(
                array("surveyPermissions/savePermissions/surveyid/{$surveyid}")
            );
            echo App()->getController()->widget(
                'ext.UserPermissionsWidget.UserPermissionsWidget',
                ['aPermissions' => $aPermissions],
                true
            );?>
            <input class='btn btn-outline-secondary d-none'  type='submit' value='<?=gT("Save Now") ?>' />
            <?php
            if ($isUserGroup) { ?>
                    <input type='hidden' name='ugid' value="<?= $id?>" />
                    <input type='hidden' name='action' value='usergroup' />
                <?php
            } else {?>
                    <input type='hidden' name='uid' value="<?= $id?>" />
                    <input type='hidden' name='action' value='user' />
            <?php }
            ?>
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>
