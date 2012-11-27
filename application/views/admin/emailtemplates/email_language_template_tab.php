
<div id='<?php echo "tab-$grouplang-$tab"; ?>'>
<ul>
    <li>
        <label for='email_<?php echo $tab; ?>_subj_<?php echo $grouplang; ?>'><?php echo $details['subject'] ?></label>
        
        <input type='text' size='80' name='<?php echo "email_{$tab}_subj_$grouplang"; ?>' id='<?php echo "email_{$tab}_subj_{$grouplang}"; ?>' value="<?php echo $esrow->{$details['field']['subject']}; ?>" />
        
        <input 
            type='button' 
            value='<?php $clang->eT("Use default"); ?>' 
            class="fillin"
            data-target="<?php echo "email_{$tab}_subj_{$grouplang}"; ?>"
            data-value="<?php echo $details['default']['subject']; ?>"
        />
    </li>
    <li><label for='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>'><?php echo $details['body']; ?></label>
        <textarea cols='80' rows='20' name='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>' id='<?php echo "email_{$tab}_{$grouplang}"; ?>'><?php echo htmlspecialchars($esrow->{$details['field']['body']}); ?></textarea>
       <?php 
       echo getEditor("email-$tab","email_{$tab}_$grouplang", "[".$clang->gT("Admin notification email:", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); 
       ?>
        <input 
            type='button' 
            value='<?php $clang->eT("Use default"); ?>' 
            class="fillin"
            data-target="<?php echo "email_{$tab}_{$grouplang}"; ?>"
            data-value="<?php echo htmlspecialchars(conditionalNewlineToBreak($details['default']['body'],$ishtml),ENT_QUOTES); ?>"
        />
    </li>
    <li>
        <label for="attachments_<?php echo $grouplang; ?>-invitation"><?php $clang->eT("Invitation attachments:"); ?></label>
        <div style="float: left; width: 60%;">
        <button class="add-attachment" id="add-attachment-<?php echo $grouplang; ?>-invitation">Add file</button>
        <table data-template="<?php echo $grouplang; ?>-invitation" class="attachments" style="width: 500px">
            <tr>
                <th>Action</th>
                <th>Filename</th>
                <th>Size</th>
                <th>Relevance</th>


            </tr>
        </table>
        </div>
    </li>
</ul>
</div>


    
