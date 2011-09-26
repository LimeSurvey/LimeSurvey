<script src="<?php echo $this->config->item('generalscripts')."jquery/jqGrid/js/i18n/grid.locale-en.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jqGrid/js/jquery.jqGrid.min.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jqGrid/js/jquery.ui.datepicker.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jqGrid/js/jquery.ui.core.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jqGrid/plugins/jquery.searchFilter.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jqGrid/src/grid.celledit.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('adminscripts')."sharePanel.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
    var shareinfoUrl = "<?php echo site_url("admin/participants/getShareInfo_json");?>";
    var editurlshare = "<?php echo site_url("admin/participants/editShareInfo"); ?>";
    var isadmin = "<?php echo ($this->session->userdata('USER_RIGHT_SUPERADMIN') == '1' ? 1 : 0); ?>"
</script>
<div class='header ui-widget-header'><strong><?php echo $clang->gT("Share Panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
</table>
<div id="pager">
</div>
<br/>