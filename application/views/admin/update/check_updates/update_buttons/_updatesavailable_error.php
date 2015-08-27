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
            $sTile = gT('Error!');
            $sHeader = gT('PHP_CURL library not loaded');
            $sMessage = gT("It seems that your server doesn't support PHP CURL Library. Please install it before proceeding to ComfortUpdate.");
            break;

        case 'no_server_answer':
            $sTile = gT('Error!');
            $sHeader = gT('No server answer');
            $sMessage = gT("It seems that the ComfortUpdate server is not responding. Please try again in few minutes or contact the LimeSurvey team.");
            break;

        case 'no_update_available_for_your_version':
            $sTile = gT('Up to date!');
            $sHeader = gT('No update available for your version.');
            $sMessage = gT('Your version is up to date!');
            break;

        case 'not_updatable':
            $sTile = gT('Error!');
            $sHeader = gT('Not updatable!');
            $sMessage = gT('Your version is not updatable via ComfortUpdate. Please update manually.');
            break;

        case 'no_build':
            $sTile = gT('Error!');
            $sHeader = gT('No build version found!');
            $sMessage = gT("It seems you're using a version coming from the LimeSurvey GitHub repository. You can't use ComfortUpdate.");
            break;

        default :
            $sTile = gT('Error!');
            $sHeader = gT('Unknown error!');
            $sMessage = gT('An unknown error occured.').' '.gT('Please contact the LimeSurvey team.');
            $sErrorCode = gT('Error code:').' '.$serverAnswer->error;
            break;
    }
?>


<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $sTile; ?></div>
    <div class='warningheader'><?php echo $sHeader; ?></div>
    <?php echo $sMessage; ?><br />
    <?php if(isset($sErrorCode)):?>
        <?php echo $sErrorCode; ?><br />
    <?php endif;?>
    <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
        <span class="ui-button-text"><?php eT("Ok"); ?></span>
    </a>
</div>