<?php
/**
 * If any error is returned by the server while checking for updates, they will shown through this view.
 * It is injected inside the li#udapteButtonsContainer, in the _checkButton view.
 * @var obj $serverAnswer the update information provided by the update server
 */
?>
<?php
    // First we check if the server provided a specific HTML message
    if(isset($serverAnswer->html))
        if($serverAnswer->html != "")
            echo $serverAnswer->html;

    $bError = true;
switch ($serverAnswer->error) {
    case 'php_curl_not_loaded':
        $sTile = gT('Error!');
        $sHeader = gT('PHP_CURL library not loaded');
        $sMessage = gT(
            "It seems that your server doesn't support PHP CURL Library. Please install it before proceeding to ComfortUpdate."
        );
        break;

    case 'no_server_answer':
        $sTile = gT('Error!');
        $sHeader = gT('No server answer!');
        $sMessage = gT(
            "It seems that the ComfortUpdate server is not responding. Please try again in few minutes or contact the LimeSurvey team."
        );
        break;

    case 'no_update_available_for_your_version':
        $bError = false;
        $sTile = gT('Up to date!');
        $sHeader = gT('No update available for your version.');
        $sMessage = gT('Your version is up to date!');
        break;

    case 'not_updatable':
        $sTile = gT('Error!');
        $sHeader = gT('Not updatable!');
        $sMessage = gT('Your version is not updatable via ComfortUpdate. Please update manually.');
        break;

    case 'update_disable':
        $sTile = gT('Error!');
        $sHeader = gT('Not updatable!');
        $sMessage = gT(
            'ComfortUpdate is disabled in your LimeSurvey configuration. Please contact your administrator for more information.'
        );
        break;

    case 'no_build':
        $sTile = gT('Error!');
        $sHeader = gT('No build version found!');
        $sMessage = gT(
            "It seems you're using a version coming from the LimeSurvey GitHub repository. You can't use ComfortUpdate."
        );
        break;

    case 'not_updatable':
        $sTile = gT('Error!');
        $sHeader = gT('No build version found!');
        $sMessage = gT("You disabled ComfortUpdate in your configuration file.");
        break;

    case 'maintenance':
        $sTile = gT('Maintenance!');
        $sHeader = gT('The ComfortUpdate service is currently undergoing maintenance.');
        $sMessage = gT("Please have patience and retry in 30 minutes. Thank you for your understanding.");
        break;

    default :
        $sTile = gT('Error!');
        $sHeader = gT('Unknown error!');
        $sMessage = gT('An unknown error occurred.') . ' ' . gT('Please contact the LimeSurvey team.');
        $sErrorCode = gT('Error code:') . ' ' . $serverAnswer->error;
        break;
}
?>

<?php
$errorCodeHtml = isset($sErrorCode) ? "<p>$sErrorCode</p>" : '';
$this->widget('ext.AlertWidget.AlertWidget', [
    'header' => $sTile,
        'text' => $sMessage . $errorCodeHtml,
    'type' => $bError ? 'danger' : 'info',
]);
?>
<p class="text-center">
    <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl("admin/"); ?>" role="button"><?php eT("Ok"); ?></a>
</p>
