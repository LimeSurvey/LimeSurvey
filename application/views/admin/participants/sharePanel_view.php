<script type="text/javascript">
    var shareinfoUrl = "<?php echo site_url("admin/participants/getShareInfo_json");?>";
    var editurlshare = "<?php echo site_url("admin/participants/editShareInfo"); ?>";
    var isadmin = "<?php echo ($this->session->userdata('USER_RIGHT_SUPERADMIN') == '1' ? 1 : 0); ?>"
</script>
<div class='header ui-widget-header'><strong><?php echo $clang->gT("Share Panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
    <tr><td>&nbsp;</td></tr>
</table>
<div id="pager">
</div>
<br/>