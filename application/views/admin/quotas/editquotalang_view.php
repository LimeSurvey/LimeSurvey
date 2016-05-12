<div class="tabpage_<?php echo $lang; ?>">

    <div class='form-group'>
        <label class='control-label col-sm-3' for='quotals_message_<?php echo $lang;?>'><?php eT("Quota message");?>:</label>
        <div class='col-sm-9'>
            <textarea  id="quotals_message_<?php echo $lang;?>" name="quotals_message_<?php echo $lang;?>" cols="60" rows="6"><?php echo htmlspecialchars($langquotainfo['quotals_message']);?></textarea>
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-sm-3' for='quotals_url_<?php echo $lang;?>'><?php eT("URL");?>:</label>
        <div class='col-sm-9'>
            <input id="quotals_url_<?php echo $lang;?>" name="quotals_url_<?php echo $lang;?>" class='form-control' type="text" size="30" maxlength="255" value="<?php echo htmlspecialchars($langquotainfo['quotals_url']);?>" />
        </div>
    </div>

    <div class='form-group'>
        <label class='control-label col-sm-3' for='quotals_urldescrip_<?php echo $lang;?>'><?php eT("URL description");?>:</label>
        <div class='col-sm-9'>
            <input id="quotals_urldescrip_<?php echo $lang;?>" name="quotals_urldescrip_<?php echo $lang;?>" type="text" class='form-control' size="30" maxlength="255" value="<?php echo htmlspecialchars($langquotainfo['quotals_urldescrip']);?>" />
        </div>
    </div>

</div>
