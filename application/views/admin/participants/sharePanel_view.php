<script type="text/javascript">
    var shareinfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getShareInfo_json"); ?>";
    var editurlshare = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editShareInfo"); ?>";
    var isadmin = "<?php echo (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == '1' ? 1 : 0); ?>";

    /* Colnames and heading for survey links subgrid */
    var firstNameText= "<?php $clang->eT("First name") ?>";
    var lastNameText = "<?php $clang->eT("Last name") ?>";
    var emailText    = "<?php $clang->eT("E-Mail") ?>";
    var sharedWithText="<?php $clang->eT("Shared with") ?>";
    var sharedUidText= "<?php $clang->eT("Shared user id") ?>";
    var ownerText    = "<?php $clang->eT("Owner") ?>";
    var dateAddedText= "<?php $clang->eT("Date added") ?>";
    var canEditText  = "<?php $clang->eT("Can edit?") ?>";


</script>
<div class='header ui-widget-header'><strong><?php $clang->eT("Share panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
    <tr><td>&nbsp;</td></tr>
</table>
<div id="pager">
</div>
<br/>