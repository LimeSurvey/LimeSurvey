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

<div class="row">
    <div class="col-lg-12 content-right">
        <h3><?php eT("CintLink Integration");?></h3>
        <div id='cintlink-container'>
        </div>
    </div>
</div>

<script>

// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.cintlink = LS.plugin.cintlink || {};

LS.plugin.cintlink.pluginBaseUrl = '<?php echo $pluginBaseUrl; ?>';
LS.plugin.cintlink.surveyId = '<?php echo $surveyId; ?>';

</script>
