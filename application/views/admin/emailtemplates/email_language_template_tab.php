
<div id='<?php echo "tab-$grouplang-$tab"; ?>'>
<ul>
    <li>
        <label for='email_<?php echo $tab; ?>_subj_<?php echo $grouplang; ?>'><?php echo $details['subject'] ?></label>
        <?php echo CHtml::textField("email_{$tab}_subj_{$grouplang}",$esrow->$details['field']['subject'],array('size'=>80)); ?>
        <?php echo CHtml::button(gT("Reset"),array('class'=>'fillin','data-target'=>"email_{$tab}_subj_{$grouplang}",'data-value'=>$details['default']['subject'])); ?>
    </li>
    <li><label for='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>'><?php echo $details['body']; ?></label>
        <?php echo CHtml::textArea("email_{$tab}_{$grouplang}",$esrow->$details['field']['body'],array('cols'=>80,'rows'=>20)); ?>
        <?php echo getEditor("email-$tab","email_{$tab}_$grouplang", $details['body'].'('.$grouplang.')',$surveyid,'','','editemailtemplates'); ?>
        <?php 
            $details['default']['body']=($tab=='admin_detailed_notification') ? $details['default']['body'] : conditionalNewlineToBreak($details['default']['body'],$ishtml) ;
            echo CHtml::button(gT("Reset"),array('class'=>'fillin','data-target'=>"email_{$tab}_{$grouplang}",'data-value'=>$details['default']['body']));
        ?>
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


    
