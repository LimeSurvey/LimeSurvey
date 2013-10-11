<div class="tabpage_<?php echo $lang; ?>">
    <ul>
        <li><label for='quotals_message_<?php echo $lang;?>'><?php $clang->eT("Quota message");?>:</label><textarea  id="quotals_message_<?php echo $lang;?>" name="quotals_message_<?php echo $lang;?>" cols="60" rows="6"><?php echo htmlspecialchars($langquotainfo['quotals_message']);?></textarea></li>
        <li><label for='quotals_url_<?php echo $lang;?>'><?php $clang->eT("URL");?>:</label><input id="quotals_url_<?php echo $lang;?>" name="quotals_url_<?php echo $lang;?>" type="text" size="30" maxlength="255" value="<?php echo htmlspecialchars($langquotainfo['quotals_url']);?>" /></li>
        <li><label for='quotals_urldescrip_<?php echo $lang;?>'><?php $clang->eT("URL description");?>:</label><input id="quotals_urldescrip_<?php echo $lang;?>" name="quotals_urldescrip_<?php echo $lang;?>" type="text" size="30" maxlength="255" value="<?php echo htmlspecialchars($langquotainfo['quotals_urldescrip']);?>" /></li>
    </ul>
	</div>
