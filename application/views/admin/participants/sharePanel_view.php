<script type="text/javascript">
    var shareinfoUrl = "<?php echo $this->createUrl("admin/participants/getShareInfo_json");?>";
    var editurlshare = "<?php echo $this->createUrl("admin/participants/editShareInfo"); ?>";
    var isadmin = "<?php echo (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == '1' ? 1 : 0); ?>"
</script>
<div class='header ui-widget-header'><strong><?php echo $clang->gT("Share Panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
    <tr><td>&nbsp;</td></tr>
</table>
<div id="pager">
</div>
<br/>