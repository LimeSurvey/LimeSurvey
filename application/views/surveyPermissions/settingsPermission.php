<?php

/* @var $surveyid int */
/* @var $aPermissions array has all permissions in */

/**
 * This page shows the permissions that could be set for a user or a user group.
 */

?>

<div id='edit-permission' class='side-body  <?= getSideBodyClass(false) ?> "'>
    <h3> <?= gT("Edit survey permissions for ...") ?> </h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(
                array("surveyPermissions/savePermissions/surveyid/{$surveyid}")
            );
            App()->getController()->widget(
                'ext.UserPermissionsWidget.UserPermissionsWidget',
                ['aPermissions' => $aPermissions],
                true
            );?>
            <input class='btn btn-default hidden'  type='submit' value='<?=gT("Save Now") ?>' />"
            <input type='hidden' name='action' value='surveyrights' />
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>
