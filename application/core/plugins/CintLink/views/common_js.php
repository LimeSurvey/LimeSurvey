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
LS.plugin.cintlink.lang.orderPlacedOnHold = '<?php echo $plugin->gT('Order placed on hold. Please pay to start the review process. Make sure the survey is activated before you pay.'); ?>';
LS.plugin.cintlink.lang.couldNotLogin = '<?php echo $plugin->gT('Could not login. Please make sure username and password is correct.'); ?>';

</script>
