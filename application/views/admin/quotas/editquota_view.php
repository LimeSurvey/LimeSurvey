<form action="<?php echo site_url("admin/quotas/$surveyid");?>" method="post">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F8FF">
		<tr>
			<td valign="top">
				<table width="100%" border="0">
					<tbody>
						<tr>
							<td colspan="2" class="header ui-widget-header"><?php echo $clang->gT("Edit quota");?></td>
						</tr>
						<tr class="evenrow">
							<td align="right"><blockquote>
								<p><strong><?php echo $clang->gT("Quota name");?>:</strong></p>
								</blockquote></td>
							<td align="left"> <input name="quota_name" type="text" size="30" maxlength="255" value="<?php echo $quotainfo['name'];?>" /></td>
						</tr>
						<tr class="evenrow">
							<td align="right"><blockquote>
								<p><strong><?php echo $clang->gT("Quota limit");?>:</strong></p>
								</blockquote></td>
							<td align="left"><input name="quota_limit" type="text" size="12" maxlength="8" value="<?php echo $quotainfo['qlimit'];?>" /></td>
						</tr>
						<tr class="evenrow">
							<td align="right"><blockquote>
								<p><strong><?php echo $clang->gT("Quota action");?>:</strong></p>
								</blockquote></td>
							<td align="left"> <select name="quota_action">
								<option value ="1" '<?php if($quotainfo['action'] == 1) echo "selected"; ?>'><?php echo $clang->gT("Terminate survey");?></option>
								<option value ="2" '<?php if($quotainfo['action'] == 2) echo "selected"; ?>'><?php echo $clang->gT("Terminate survey with warning");?></option>
								</select></td>
						</tr>
						<tr class="evenrow">
						    <td align="right"><blockquote>
						        <p><strong><?php echo $clang->gT("Autoload URL");?>:</strong></p>
						        </blockquote></td>
						    <td align="left"><input name="autoload_url" type="checkbox" value="1"<?php if($quotainfo['autoload_url'] == "1") {echo " checked";}?> /></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<div class="tab-pane" id="tab-pane-quota-<?php echo $surveyid;?>">