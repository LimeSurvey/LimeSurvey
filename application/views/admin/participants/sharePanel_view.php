<script type="text/javascript">
    var shareinfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getShareInfo_json"); ?>";
    var editurlshare = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editShareInfo"); ?>";
    var isadmin = "<?php echo (Permission::model()->hasGlobalPermission('superadmin','read') ? 1 : 0); ?>";

    /* Colnames and heading for survey links subgrid */
    var firstNameText= "<?php eT("First name") ?>";
    var lastNameText = "<?php eT("Last name") ?>";
    var emailText    = "<?php eT("Email") ?>";
    var sharedWithText="<?php eT("Shared with") ?>";
    var sharedUidText= "<?php eT("Shared user id") ?>";
    var ownerText    = "<?php eT("Owner") ?>";
    var dateAddedText= "<?php eT("Date added") ?>";
    var canEditText  = "<?php eT("Can edit?") ?>";

    var sSearchMsg = "<?php eT("Search shared participants", 'js') ?>";
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sDeleteMsg = "<?php eT("Are you sure you want to un-share the selected participants?") ?>";
    var sDeleteShares="<?php eT("Un-share selected participants", 'js') ?>";
    var sSelectRowMsg = "<?php eT("Please select at least one participant.", 'js') ?>";
    var sWarningMsg = "<?php eT("Warning", 'js') ?>";
    var refreshListTxt="<?php eT("Refresh list", 'js') ?>";
    var pageViewTxt= "<?php eT("Page {0} of {1}", 'js') ?>";
    var viewRecordTxt= '<?php eT("View {0} - {1} of {2}",'js');?>';
    var sFindButtonCaption= "<?php eT("Find", 'js') ?>";
    var sResetButtonCaption= "<?php eT("Reset", 'js') ?>";
    var sSearchTitle= "<?php eT("Search...", 'js') ?>";
    var sOptionAnd= "<?php eT("AND", 'js') ?>";
    var sOptionOr= "<?php eT("OR", 'js') ?>";
    var sOperator1= '<?php eT("equal",'js');?>';
    var sOperator2= '<?php eT("not equal",'js');?>';
    var sOperator3= '<?php eT("less",'js');?>';
    var sOperator4= '<?php eT("less or equal",'js');?>';
    var sOperator5= '<?php eT("greater",'js');?>';
    var sOperator6= '<?php eT("greater or equal",'js');?>';
    var sOperator7= '<?php eT("begins with",'js');?>';
    var sOperator8= '<?php eT("does not begin with",'js');?>';
    var sOperator9= '<?php eT("is in",'js');?>';
    var sOperator10= '<?php eT("is not in",'js');?>';
    var sOperator11= '<?php eT("ends with",'js');?>';
    var sOperator12= '<?php eT("does not end with",'js');?>';
    var sOperator13= '<?php eT("contains",'js');?>';
    var sOperator14= '<?php eT("does not contain",'js');?>';
    
    

</script>
<div class='header ui-widget-header'><strong><?php eT("Share panel"); ?> </strong></div>
<br/>
<table id="sharePanel">
    <tr><td>&nbsp;</td></tr>
</table>
<div id="pager">
</div>
<br/>