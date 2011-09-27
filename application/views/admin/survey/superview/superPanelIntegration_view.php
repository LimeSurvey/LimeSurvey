<script type="text/javascript">
    var jsonUrl = "<?php echo site_url("admin/survey/getUrlParamsJSON/{$surveyid}");?>";
    var imageUrl = "<?php echo $this->config->item("imageurl");?>";
</script>
<div id='panelintegration'>

        <table id="urlparams" style='margin:0 auto;'></table>
        <div id="pagerurlparams"></div>

</div>