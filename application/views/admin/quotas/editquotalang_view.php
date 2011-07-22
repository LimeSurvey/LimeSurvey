<div class="tab-page">
			  	 	 <h2 class="tab"><?php echo GetLanguageNameFromCode($lang,false);
if ($lang==$baselang) {echo '('.$clang->gT("Base language").')';} ?>
</h2>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F8FF">
			<tr>
				<td valign="top">
					<table width="100%" border="0">
						<tbody>
							<tr class="evenrow">
							    <td align="right" valign="top"><blockquote>
							        <p><strong><?php echo $clang->gT("Quota message");?>:</strong></p>
							        </blockquote></td>
							    <td align="left"> <textarea name="quotals_message_<?php echo $lang;?>" cols="60" rows="6"><?php echo $langquotainfo['quotals_message'];?></textarea></td>
							</tr>
							<tr class="evenrow">
								<td align="right"><blockquote>
    								<p><strong><?php echo $clang->gT("URL");?>:</strong></p>
  									</blockquote></td>
								<td align="left"> <input name="quotals_url_<?php echo $lang;?>" type="text" size="30" maxlength="255" value="<?php echo $langquotainfo['quotals_url'];?>" /></td>
							</tr>
							<tr class="evenrow">
								<td align="right"><blockquote>
    								<p><strong><?php echo $clang->gT("URL description");?>:</strong></p>
  									</blockquote></td>
								<td align="left"> <input name="quotals_urldescrip_<?php echo $lang;?>" type="text" size="30" maxlength="255" value="'<?php echo $langquotainfo['quotals_urldescrip'];?>" /></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
	</div>