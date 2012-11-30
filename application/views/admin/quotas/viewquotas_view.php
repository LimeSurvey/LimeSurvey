<?php if (isset($sShowError))  {?>
    <script type="text/javascript">
        <!--
        alert("<?php $clang->eT("Quota could not be added.", 'js'); ?>\n\n<?php $clang->eT("It is missing a quota message for the following languages:", 'js'); ?>\n<?php echo $sShowError; ?>");
        //-->
    </script>
    <?php } ?>

<div class="header ui-widget-header"><?php $clang->eT("Survey quotas");?></div>
<br />
<table id="quotalist" class="quotalist">
<thead>
    <tr>
        <th style="width:20%"><?php $clang->eT("Quota name");?></th>
        <th style="width:20%"><?php $clang->eT("Status");?></th>
        <th style="width:30%"><?php $clang->eT("Quota action");?></th>
        <th style="width:5%"><?php $clang->eT("Completed");?></th>
        <th style="width:5%"><?php $clang->eT("Limit");?></th>
        <th style="width:20%"><?php $clang->eT("Action");?></th>
    </tr>
</thead>

<tfoot>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td style="padding: 3px;"><input type="button" value="<?php $clang->eT("Quick CSV report");?>" onClick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$surveyid/quickreport/y") ?>', '_top')" /></td>
    </tr>
	</tfoot>
	<tbody>