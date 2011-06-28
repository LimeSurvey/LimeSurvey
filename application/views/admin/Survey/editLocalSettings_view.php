<ul>
                <li><label for=''><?php echo $clang->gT("Survey title"); ?>:</label>
                <input type='text' size='80' name='short_title_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_title']; ?>" />
                </li>
                <li><label for=''><?php echo $clang->gT("Description:"); ?></label>
                <textarea cols='80' rows='15' name='description_<?php echo $esrow['surveyls_language']; ?>'><?php echo $esrow['surveyls_description']; ?></textarea>
                <?php echo getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
                </li>
                <li><label for=''><?php echo $clang->gT("Welcome message:"); ?></label>
                <textarea cols='80' rows='15' name='welcome_<?php echo $esrow['surveyls_language']; ?>'><?php echo $esrow['surveyls_welcometext']; ?></textarea>
                <?php echo getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
                </li>
                <li><label for=''><?php echo $clang->gT("End message:"); ?></label>
                <textarea cols='80' rows='15' name='endtext_<?php echo $esrow['surveyls_language']; ?>'><?php echo $esrow['surveyls_endtext']; ?></textarea>
                <?php echo getEditor("survey-endtext","endtext_".$esrow['surveyls_language'], "[".$clang->gT("End message:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
                </li>
                <li><label for=''><?php echo $clang->gT("End URL:"); ?></label>
                <input type='text' size='80' name='url_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_url']; ?>" />
                </li>
                <li><label for=''><?php echo $clang->gT("URL description:"); ?></label>
                <input type='text' size='80' name='urldescrip_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_urldescription']; ?>" />
                </li>
                <li><label for=''><?php echo $clang->gT("Date format:"); ?></label>
                <select size='1' name='dateformat_<?php echo $esrow['surveyls_language']; ?>'>
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
                <li><label for=''><?php echo $clang->gT("Decimal Point Format:"); ?></label>
                <select size='1' name='numberformat_<?php echo $esrow['surveyls_language']; ?>'>
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