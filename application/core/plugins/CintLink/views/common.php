<?php

/**
 * HTML common for both index and global index
 */

?>

<div id="ajaxContainerLoading2" class="ajaxLoading ajaxLoadingTransparent" >
    <h2><b><?php eT('Please wait, loading data...');?></b></h2>
    <div class="preloader loading" style="margin-top: 10px;">
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
    </div>
</div>

<script>

// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.cintlink = LS.plugin.cintlink || {};

LS.plugin.cintlink.pluginBaseUrl = '<?php echo $pluginBaseUrl; ?>';

<?php if(isset($surveyId)): ?>
    LS.plugin.cintlink.surveyId = '<?php echo $surveyId; ?>';
<?php endif; ?>

LS.plugin.cintlink.lang = {}
LS.plugin.cintlink.lang.orderPlacedOnHold = '<?php echo $plugin->gT('Order placed on hold. Please pay to start the review process.'); ?>';
LS.plugin.cintlink.lang.couldNotLogin = '<?php echo $plugin->gT('Could not login. Please make sure username and password is correct.'); ?>';

</script>
