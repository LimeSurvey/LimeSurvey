<?php
/**
 * If any error is returned by the server while checking for updates, they will shown through this view.  
 * It is injected inside the li#udapteButtonsContainer, in the _checkButton view.
 * @var obj $serverAnswer the update informations provided by the update server 
 */
?>
<?php
    // First we check if the server provided a specific HTML message
    if(isset($serverAnswer->html)) 
        if($serverAnswer->html != "")
            echo $serverAnswer->html.'<br/>';
        
    switch ($serverAnswer->error) 
    {
        case 'php_curl_not_loaded':
            $sTile = 'Error';
            $sHeader = 'PHP_CURL library not loaded !';
            $sMessage = "it seems that your server doesn't support PHP CURL Library. Please install it before proceeding to ComfortUpdate";
            break;          
        
        case 'no_server_answer':
            $sTile = 'Error';
            $sHeader = 'No server answer';
            $sMessage = "it seems that the Comfort Updater is not responding for now. Please, try again in few minutes, or contact LimeSurvey team";
            break;
        
        case 'no_update_available_for_your_version':
            $sTile = 'Up to date !';
            $sHeader = 'No update available for your version';
            $sMessage = 'Your version is up to date !';
            break;

        case 'not_updatable':
            $sTile = 'Error';
            $sHeader = 'Not updatable !';
            $sMessage = 'Your version is not updatable via ComfortUpdate. Please, update manually.';
            break;
                    
        case 'no_build':
            $sTile = 'Error';
            $sHeader = 'No build version found !';
            $sMessage = "It seems you're using a version coming from the LimeSurvey GitHub repository. You can't use ComfortUpdate.";
            break;
                    
        default :
            $sTile = 'Error';
            $sHeader = 'Unknown Error !';
            $sMessage = 'An unknown error occured. Please, contact LimeSurvey team.';
            $sErrorCode = 'error code : '.$serverAnswer->error;
            break;
    }
?>


<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php eT($sTile); ?></div>
    <div class='warningheader'><?php eT($sHeader); ?></div>
    <?php echo eT($sMessage); ?><br />
    <?php if(isset($sErrorCode)):?>
        <?php echo eT($sErrorCode); ?><br />
    <?php endif;?>
    <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
        <span class="ui-button-text"><?php eT("Ok"); ?></span>
    </a>
</div>