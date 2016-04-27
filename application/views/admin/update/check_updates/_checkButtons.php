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
    <p><?php eT('Please wait, data loading...');?></p>
    <img src="<?php echo Yii::app()->baseUrl;?>/images/ajax-loader.gif" alt="loading..."/>    <br/>
</div>

<div id="preUpdaterContainer">

    <div class='header ui-widget-header'><?php echo eT("Updates"); ?></div><br/>
    <ul>
        <li>
            <?php if(YII_DEBUG):?>
                <small>server:<em class="text-warning"> <?php echo Yii::app()->getConfig("comfort_update_server_url");?></em></small>
            <?php endif;?>
        </li>
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
