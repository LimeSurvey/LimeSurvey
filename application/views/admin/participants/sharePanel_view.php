<script type="text/javascript">
    var shareinfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getShareInfo_json"); ?>";
    var editurlshare = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editShareInfo"); ?>";
    var isadmin = "<?php echo (Permission::model()->hasGlobalPermission('superadmin','read') ? 1 : 0); ?>";

    /* Colnames and heading for survey links subgrid */
    var firstNameText= "<?php $clang->eT("First name") ?>";
    var lastNameText = "<?php $clang->eT("Last name") ?>";
    var emailText    = "<?php $clang->eT("Email") ?>";
    var sharedWithText="<?php $clang->eT("Shared with") ?>";
    var sharedUidText= "<?php $clang->eT("Shared user id") ?>";
    var ownerText    = "<?php $clang->eT("Owner") ?>";
    var dateAddedText= "<?php $clang->eT("Date added") ?>";
    var canEditText  = "<?php $clang->eT("Can edit?") ?>";

    var sSearchMsg = "<?php $clang->eT("Search shared participants", 'js') ?>";
    var sLoadText = '<?php $clang->eT("Loading...",'js');?>';
    var sDeleteMsg = "<?php $clang->eT("Are you sure you want to un-share the selected participants?") ?>";
    var sDeleteShares="<?php $clang->eT("Un-share selected participants", 'js') ?>";
    var sSelectRowMsg = "<?php $clang->eT("Please select at least one participant.", 'js') ?>";
    var sWarningMsg = "<?php $clang->eT("Warning", 'js') ?>";
    var refreshListTxt="<?php $clang->eT("Refresh list", 'js') ?>";
    var pageViewTxt= "<?php $clang->eT("Page {0} of {1}", 'js') ?>";
    var viewRecordTxt= '<?php $clang->eT("View {0} - {1} of {2}",'js');?>';
    var sFindButtonCaption= "<?php $clang->eT("Find", 'js') ?>";
    var sResetButtonCaption= "<?php $clang->eT("Reset", 'js') ?>";
    var sSearchTitle= "<?php $clang->eT("Search...", 'js') ?>";
    var sOptionAnd= "<?php $clang->eT("AND", 'js') ?>";
    var sOptionOr= "<?php $clang->eT("OR", 'js') ?>";
    var sOperator1= '<?php $clang->eT("equal",'js');?>';
    var sOperator2= '<?php $clang->eT("not equal",'js');?>';
    var sOperator3= '<?php $clang->eT("less",'js');?>';
    var sOperator4= '<?php $clang->eT("less or equal",'js');?>';
    var sOperator5= '<?php $clang->eT("greater",'js');?>';
    var sOperator6= '<?php $clang->eT("greater or equal",'js');?>';
    var sOperator7= '<?php $clang->eT("begins with",'js');?>';
    var sOperator8= '<?php $clang->eT("does not begin with",'js');?>';
    var sOperator9= '<?php $clang->eT("is in",'js');?>';
    var sOperator10= '<?php $clang->eT("is not in",'js');?>';
    var sOperator11= '<?php $clang->eT("ends with",'js');?>';
    var sOperator12= '<?php $clang->eT("does not end with",'js');?>';
    var sOperator13= '<?php $clang->eT("contains",'js');?>';
    var sOperator14= '<?php $clang->eT("does not contain",'js');?>';
    
    

</script>
<div class='header ui-widget-header'><strong><?php $clang->eT("Share panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
    <tr><td>&nbsp;</td></tr>
</table>
<div id="pager">
</div>
<br/>