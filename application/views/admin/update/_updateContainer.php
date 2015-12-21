<?php
/**
 * This view generate all the structure needed for the ComfortUpdate.
 * If no step is requested (by url or by post), ajax will render the check buttons, else, it will show the ComfortUpdater (menus, etc.)
 *
 * @var int $thisupdatecheckperiod  : the current check period in days (0 => never ; 1 => everyday ; 7 => every week, etc..  )
 * @var $updatelastcheck TODO : check type
 * @var $UpdateNotificationForBranch TODO : check type
 *
 */
?>

<?php if (Permission::model()->hasGlobalPermission('superadmin')): ?>
    <!-- this view contain the input provinding to the js the inforamtion about wich content to load : check buttons or comfortUpdate -->
    <?php $this->renderPartial("./update/_ajaxVariables"); ?>

    <div id="updaterWrap">
        <!-- The check buttons : render by ajax only if no step is required by url or post -->
        <?php $this->renderPartial("./update/check_updates/_checkButtons", array( "thisupdatecheckperiod"=>$thisupdatecheckperiod, "updatelastcheck"=>$updatelastcheck,"UpdateNotificationForBranch"=>$UpdateNotificationForBranch )); ?>
        <!-- The updater  -->
        <?php $this->renderPartial("./update/updater/_updater"); ?>
    </div>
<?php else: ?>
    <div class="text-error">
        <p class="text-center">
            <?php eT("Only superadmin can update");?>
        </p>
    </div>
<?php endif; ?>
