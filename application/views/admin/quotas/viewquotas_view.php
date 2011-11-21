<?php if(isset($showerror)) echo $showerror;?>

<div class="header ui-widget-header"><?php echo $clang->gT("Survey quotas");?></div>
  				<br />
<table id="quotalist" class="quotalist">
	<thead>
  		<tr>
    		<th width="20%"><?php echo $clang->gT("Quota name");?></th>
    		<th width="20%"><?php echo $clang->gT("Status");?></th>
    		<th width="30%"><?php echo $clang->gT("Quota action");?></th>
    		<th width="5%"><?php echo $clang->gT("Limit");?></th>
    		<th width="5%"><?php echo $clang->gT("Completed");?></th>
    		<th width="20%"><?php echo $clang->gT("Action");?></th>
  		</tr>
	</thead>

	<tfoot>
		<tr>
    		<td>&nbsp;</td>
    		<td align="center">&nbsp;</td>
    		<td align="center">&nbsp;</td>
    		<td align="center">&nbsp;</td>
    		<td align="center">&nbsp;</td>
    		<td align="center" style="padding: 3px;"><input type="button" value="<?php echo $clang->gT("Quick CSV report");?>" onClick="window.open('<?php echo $this->createUrl("admin/quotas/surveyid/$surveyid/quickreport/y") ?>', '_top')" /></td>
  		</tr>
	</tfoot>
	<tbody>