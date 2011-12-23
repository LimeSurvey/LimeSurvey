<script type="text/javascript">
    var shareinfoUrl = "<?php echo Yii::app()->createUrl("admin/participants/sa/getShareInfo_json"); ?>";
    var editurlshare = "<?php echo Yii::app()->createUrl("admin/participants/sa/editShareInfo"); ?>";
    var isadmin = "<?php echo (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == '1' ? 1 : 0); ?>"
</script>
<div class='header ui-widget-header'><strong><?php $clang->eT("Share panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
    <tr><td>&nbsp;</td></tr>
</table>
<div id="pager">
</div>
<br/>