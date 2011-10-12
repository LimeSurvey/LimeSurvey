<ul>
    <li><label for='short_title_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("Survey title"); ?>:</label>
        <input type='text' size='80' id='short_title_<?php echo $esrow['surveyls_language']; ?>' name='short_title_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_title']; ?>" />
    </li>
    <li><label for='description_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("Description:"); ?></label>
        <textarea cols='80' rows='15' id='description_<?php echo $esrow['surveyls_language']; ?>' name='description_<?php echo $esrow['surveyls_language']; ?>'><?php echo $esrow['surveyls_description']; ?></textarea>
        <?php echo getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
    </li>
    <li><label for='welcome_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("Welcome message:"); ?></label>
        <textarea cols='80' rows='15' id='welcome_<?php echo $esrow['surveyls_language']; ?>' name='welcome_<?php echo $esrow['surveyls_language']; ?>'><?php echo $esrow['surveyls_welcometext']; ?></textarea>
        <?php echo getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
    </li>
    <li><label for='endtext_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("End message:"); ?></label>
        <textarea cols='80' rows='15' id='endtext_<?php echo $esrow['surveyls_language']; ?>' name='endtext_<?php echo $esrow['surveyls_language']; ?>'><?php echo $esrow['surveyls_endtext']; ?></textarea>
        <?php echo getEditor("survey-endtext","endtext_".$esrow['surveyls_language'], "[".$clang->gT("End message:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
    </li>
    <li><label for='url_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("End URL:"); ?></label>
        <input type='text' size='80' id='url_<?php echo $esrow['surveyls_language']; ?>' name='url_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_url']; ?>" />
    </li>
    <li><label for='urldescrip_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("URL description:"); ?></label>
        <input type='text' id='urldescrip_<?php echo $esrow['surveyls_language']; ?>' size='80' name='urldescrip_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_urldescription']; ?>" />
    </li>
    <li><label for='dateformat_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("Date format:"); ?></label>
        <select size='1' id='dateformat_<?php echo $esrow['surveyls_language']; ?>' name='dateformat_<?php echo $esrow['surveyls_language']; ?>'>
            <?php foreach (getDateFormatData() as $index=>$dateformatdata)
                { ?>
                <option value='<?php echo $index; ?>'
                    <?php if ($esrow['surveyls_dateformat']==$index) { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php echo $dateformatdata['dateformat']; ?></option>
                <?php } ?>
        </select>
    </li>
    <li><label for='numberformat_<?php echo $esrow['surveyls_language']; ?>'><?php echo $clang->gT("Decimal Point Format:"); ?></label>
        <select size='1' id='numberformat_<?php echo $esrow['surveyls_language']; ?>' name='numberformat_<?php echo $esrow['surveyls_language']; ?>'>
            <?php foreach (getRadixPointData() as $index=>$radixptdata)
                { ?>
                <option value='<?php echo $index; ?>'
                    <?php if ($esrow['surveyls_numberformat']==$index) { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php echo $radixptdata['desc']; ?></option>
                <?php } ?>
        </select>
    </li>
</ul>