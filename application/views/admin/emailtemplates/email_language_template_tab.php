
<div id='<?php echo "tab-$grouplang-$tab"; ?>'>
<ul>
    <li>
        <label for='email_<?php echo $tab; ?>_subj_<?php echo $grouplang; ?>'><?php echo $details['subject'] ?></label>
        
        <input type='text' size='80' name='<?php echo "email_{$tab}_subj_$grouplang"; ?>' id='<?php echo "email_{$tab}_subj_{$grouplang}"; ?>' value="<?php echo $esrow->{$details['field']['subject']}; ?>" />
        
        <input 
            type='button' 
            value='<?php $clang->eT("Reset"); ?>' 
            class="fillin"
            data-target="<?php echo "email_{$tab}_subj_{$grouplang}"; ?>"
            data-value="<?php echo $details['default']['subject']; ?>"
        />  
    </li>
    <li><label for='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>'><?php echo $details['body']; ?></label>
        <textarea cols='80' rows='20' name='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>' id='<?php echo "email_{$tab}_{$grouplang}"; ?>'><?php echo htmlspecialchars($esrow->{$details['field']['body']}); ?></textarea>
       <?php 
       echo getEditor("email-$tab","email_{$tab}_$grouplang", $details['body'].'('.$grouplang.')',$surveyid,'','','editemailtemplates'); 
       ?>
        <input 
            type='button' 
            value='<?php $clang->eT("Reset"); ?>' 
            class="fillin"
            data-target="<?php echo "email_{$tab}_{$grouplang}"; ?>"
            data-value="<?php 
            if ($tab=='admin_detailed_notification'){
                echo htmlspecialchars($details['default']['body'],ENT_QUOTES); 
            }
            else
            {
                echo htmlspecialchars(conditionalNewlineToBreak($details['default']['body'],$ishtml),ENT_QUOTES); 
            }
            ?>"
        />
    </li>
    <li>
        <label for="attachments_<?php echo "{$grouplang}-{$tab}"; ?>"><?php echo $details['attachments']; ?></label>
        <div style="float: left; width: 60%;">
        <button class="add-attachment" id="add-attachment-<?php echo "{$grouplang}-{$tab}"; ?>"><?php $clang->eT("Add file"); ?></button>
        
        <table data-template="[<?php echo $grouplang; ?>][<?php echo $tab ?>]" id ="attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" class="attachments" style="width: 500px">
            <tr>
                <th><?php $clang->eT("Action"); ?></th>
                <th><?php $clang->eT("File name"); ?></th>
                <th><?php $clang->eT("Size"); ?></th>
                <th><?php $clang->eT("Relevance"); ?></th>
            </tr>
            <?php
            
                if (isset($esrow->attachments[$tab]))
                {
                    $script = array();
                    foreach ($esrow->attachments[$tab] as $attachment)
                    {
                        
                        $script[] = sprintf("addAttachment($('#attachments-%s-%s'), %s, %s, %s );", $grouplang, $tab, json_encode($attachment['url']), json_encode($attachment['relevance']), json_encode($attachment['size']));
                    }
                    echo '<script type="text/javascript">';
                    echo '$(document).ready(function() {';
                    echo implode("\n", $script);
                    echo '});';
                    echo '</script>';
                }
            ?>
        </table>
        </div>
    </li>
</ul>
</div>


    
