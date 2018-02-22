<?php
/**
 * This view generate all the structure needed for the ComfortUpdate.
 * If no step is requested (by url or by post), ajax will render the check buttons, else, it will show the ComfortUpdater (menus, etc.)
 *
 * @var $this AdminController
 * @var int $thisupdatecheckperiod  : the current check period in days (0 => never ; 1 => everyday ; 7 => every week, etc..  )
 * @var $updatelastcheck TODO : check type
 * @var $UpdateNotificationForBranch TODO : check type
 *
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('update');
?>

<!-- this view contain the input provinding to the js the inforamtion about wich content to load : check buttons or comfortupdate -->
<?php
    $this->renderPartial("./update/_ajaxVariables");
?>

<div class="col-lg-12 list-surveys" id="comfortUpdateGeneralWrap">
    <h3>
        <span id="comfortUpdateIcon" class="icon-shield text-success"></span>
        <?php eT('ComfortUpdate'); ?>
        <?php if(YII_DEBUG):?>
            <small>Server:<em class="text-warning"> <?php echo Yii::app()->getConfig("comfort_update_server_url");?></em></small>
        <?php endif;?>
    </h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <div id="updaterWrap">
                <div id="preUpdaterContainer">
                <!-- The check buttons : render by ajax only if no step is required by url or post -->
                <?php // $this->renderPartial("./update/check_updates/_checkButtons", array( "thisupdatecheckperiod"=>$thisupdatecheckperiod, "updatelastcheck"=>$updatelastcheck,"UpdateNotificationForBranch"=>$UpdateNotificationForBranch )); ?>
                <?php
                    if( $serverAnswer->result )
                    {
                        unset($serverAnswer->result);
                        $this->renderPartial('./update/check_updates/update_buttons/_updatesavailable', array('updateInfos' => $serverAnswer));
                    }
                    else
                    {
                        // Error : we build the error title and messages
                        $this->renderPartial('./update/check_updates/update_buttons/_updatesavailable_error', array('serverAnswer' => $serverAnswer));
                    }
                ?>
                </div>

                <!-- The updater  -->
                <?php $this->renderPartial("./update/updater/_updater"); ?>
            </div>
        </div>
    </div>
</div>
