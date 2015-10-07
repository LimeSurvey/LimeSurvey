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

    $bError = true;

    switch ($serverAnswer->error)
    {
        case 'php_curl_not_loaded':
            $sTitle = 'Error';
            $sHeader = 'PHP_CURL not loaded !';
            $sMessage = "it seems that your server doesn't support PHP CURL Library. Please install it before proceeding to ComfortUpdate";
            break;

        case 'no_server_answer':
            $sTitle = 'Error';
            $sHeader = 'No server answer';
            $sMessage = "it seems that the Comfort Updater is not responding for now. Please, try again in few minutes, or contact LimeSurvey team";
            break;

        case 'no_update_available_for_your_version':
            $sTitle = 'Up to date !';
            $sHeader = 'No update available for your version';
            $sMessage = 'Your version is up to date !';
            $bError = false;
            break;

        case 'not_updatable':
            $sTitle = 'Error';
            $sHeader = 'Not updatable !';
            $sMessage = 'Your version is not updatable via ComfortUpdate. Please, update manually.';
            break;

        default :
            $sTitle = 'Error';
            $sHeader = 'Unknown Error !';
            $sMessage = "An unknown error occured. Please, contact LimeSurvey team <br/> error code : ".$serverAnswer->error;
            break;
    }
?>

<div class="jumbotron message-box <?php if($bError) echo 'message-box-error'; ?>">
        <h2 class="<?php if($bError){echo 'text-danger';}else{echo 'text-success';} ?>"><?php eT($sTitle); ?></h2>
        <p class="lead"><?php eT($sHeader); ?></p>
        <p><?php echo eT($sMessage); ?></p>
        <p>
            <a class="btn btn-lg btn-success" href="<?php echo $this->createUrl("admin/"); ?>" role="button"><?php eT("Ok"); ?></a>
        </p>
</div>

