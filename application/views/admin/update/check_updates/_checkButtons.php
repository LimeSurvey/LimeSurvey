<?php
/**
 * This view display the ckeck buttons, it is the first to be loaded when no step is setted.
 * It give the possibility to user to choose how often the updates must be checked, wich branches (stable and/or unstable)
 * and provides the button to check available updates.
 *
 * @var int $thisupdatecheckperiod  : the current check period in days (0 => never ; 1 => everyday ; 7 => every week, etc..  )
 * @var obj $clang : the translate object, now moved to global function TODO : remove it
 * @var $updatelastcheck
 * @var $UpdateNotificationForBranch
 *
 */
?>
<div id="ajaxupdaterLayoutLoading">
    <p><?php eT('Please wait, connecting to server....');?>...</p>
    <img src="<?php echo Yii::app()->baseUrl;?>/images/ajax-loader.gif" alt="loading..."/>    <br/>
</div>

<div id="preUpdaterContainer">

    <div class='header ui-widget-header'><?php echo eT("Updates"); ?></div><br/>
    <ul>
<?php /*
    <li>
        <label for='updatecheckperiod'><?php echo eT("Automatically check for updates:"); ?></label>
        <select name='updatecheckperiod' id='updatecheckperiod'>
            <option value='0'
                <?php if ($thisupdatecheckperiod==0) { echo "selected='selected'";} ?>
                ><?php echo eT("Never"); ?></option>
            <option value='1'
                <?php if ($thisupdatecheckperiod==1) { echo "selected='selected'";} ?>
                ><?php echo eT("Every day"); ?></option>
            <option value='7'
                <?php if ($thisupdatecheckperiod==7) { echo "selected='selected'";} ?>
                ><?php echo eT("Every week"); ?></option>
            <option value='14'
                <?php if ($thisupdatecheckperiod==14) { echo "selected='selected'";} ?>
                ><?php echo eT("Every 2 weeks"); ?></option>
            <option value='30'
                <?php if ($thisupdatecheckperiod==30) { echo "selected='selected'";} ?>
                ><?php echo eT("Every month"); ?></option>
        </select>
        <input type='button' id="ajaxcheckupdate" value='<?php eT("Check now"); ?>' />&nbsp;<span id='ajaxlastupdatecheck'><?php echo sprintf(gT("Last check: %s"),$updatelastcheck); ?></span>
    </li>

    <li>
        <label for='updatenotificationforbranch'><?php echo eT("Show update notifications:"); ?></label>
        <select name='updatenotificationforbranch' id='updatenotificationforbranch'>
            <option value='never'
                <?php if ($UpdateNotificationForBranch=='never') { echo "selected='selected'";} ?>
                ><?php echo eT("Never"); ?></option>
            <option value='stable'
                <?php if ($UpdateNotificationForBranch=='stable') { echo "selected='selected'";} ?>
                ><?php echo eT("For stable versions"); ?></option>
            <option value='both'
                <?php if ($UpdateNotificationForBranch=='both') { echo "selected='selected'";} ?>
                ><?php echo eT("For stable and unstable versions"); ?></option>
        </select>
    </li>

    <!-- FOR AJAX REQUEST -->
<!--
    <input type="hidden" id="updateBranch" value="<?php echo $UpdateNotificationForBranch;?>"/>

    <!-- The js will inject inside this li the HTML of the update buttons -->
*/
?>
    <li id="udapteButtonsContainer">
        <div id="ajaxLoading" style="display: none;">
            <p><?php eT('Please wait, looking for updates....');?>...</p>
            <img src="<?php echo Yii::app()->baseUrl;?>/images/ajax-loader.gif" alt="loading..."/>
        </div>
         <br/>
        <span id="updatesavailable"></span>
    </li>
    </ul>
</div>