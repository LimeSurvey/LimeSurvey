<?php
/**
 * This file generate the hidden inputs required by the ajax process.
 * If a step is defined in the url's getters, then we parse it in an hidden input
 * comfortupdater.js will check this hidden input, and load the correct step.
 * Most steps also need to know the destination build
 */
?>

<?php if(isset($_GET['update'])):?>
    <input type="hidden" id="update_step" value="<?php echo $_GET['update']; ?>"/>
<?php else:?>
     <input type="hidden" id="update_step" value=""/>
<?php endif;?>

<?php if(isset($_GET['destinationBuild'])):?>
    <input type="hidden" id="destinationBuildForAjax" value="<?php echo $_GET['destinationBuild']; ?>"/>
<?php endif;?>

<?php if(isset($_GET['access_token'])):?>
    <input type="hidden" id="access_tokenForAjax" value="<?php echo $_GET['access_token']; ?>"/>
<?php endif;?>
