<?php
/**
 * This view generate all the structure needed for the comfort updater.
 * If no step is requested (by url or by post), ajax will render the check buttons, else, it will show the comfort updater (menus, etc.)
 * 
 * @var int $thisupdatecheckperiod  : the current check period in days (0 => never ; 1 => everyday ; 7 => every week, etc..  )
 * @var $updatelastcheck TODO : check type 
 * @var $UpdateNotificationForBranch TODO : check type
 * 
 */
?>

<!-- this view contain the input provinding to the js the inforamtion about wich content to load : check buttons or comfortUpdate -->
<?php $this->renderPartial("./update/_ajaxVariables"); ?>

<div id="updaterWrap">
    <!-- The check buttons : render by ajax only if no step is required by url or post -->
    <?php $this->renderPartial("./update/check_updates/_checkButtons", array( "thisupdatecheckperiod"=>$thisupdatecheckperiod, "updatelastcheck"=>$updatelastcheck,"UpdateNotificationForBranch"=>$UpdateNotificationForBranch )); ?>
    <!-- The updater  -->
    <?php $this->renderPartial("./update/updater/_updater"); ?> 
</div>

<?php
/**
 * TODO : move to CSS file, with all the inline styles
 */
?>

<style media="screen" type="text/css">
.on {
   font-weight: bold;
   font-size: 1em;
   padding-left : 0.5em;
   padding-top : 0.5em;
}

.off {
    font-size: 0.9em;
    padding-left : 0.5em;
    padding-top : 0.5em;
}

#localChecksContainer {
    min-height: 190px;
}

/*Quick hack for LS 2.06 surveys menu*/
label, legend {
    display : inline-block;
    font-family: verdana, arial, sans-serif;
    text-align: left;
    background-color: #fefefe;
    color: #666;
}
</style>