<table class='browsetable' align='center'>
            <thead><tr><th>SCID</th><th>
            <?php echo $clang->gT("Actions"); ?></th><th>
            <?php echo $clang->gT("Identifier"); ?></th><th>
            <?php echo $clang->gT("IP address"); ?></th><th>
            <?php echo $clang->gT("Date Saved"); ?></th><th>
            <?php echo $clang->gT("Email address"); ?></th>
            </tr></thead><tbody>
            <?php foreach($result->result_array() as $row)
            { ?>
                <tr>
    				<td><?php echo $row['scid']; ?></td>
    				<td align='center'>
    
                <?php if (bHasSurveyPermission($surveyid,'responses','update'))
                { ?>
                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $this->config->item('imageurl'); ?>/token_edit.png' title='
                    <?php echo $clang->gT("Edit entry"); ?>' onclick="window.open('{$scriptname}?action=dataentry&amp;subaction=edit&amp;id={$row['srid']}&amp;sid={$surveyid}', '_top')" />
                <?php } 
                if (bHasSurveyPermission($surveyid,'responses','delete'))
                { ?>
                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $this->config->item('imageurl'); ?>/token_delete.png' title='
                    <?php echo $clang->gT("Delete entry"); ?>' onclick="if (confirm('<?php echo $clang->gT("Are you sure you want to delete this entry?","js"); ?>')) { <?php echo get2post(site_url("admin/saved/delete")."?action=saved&amp;sid=$surveyid&amp;subaction=delete&amp;scid={$row['scid']}&amp;srid={$row['srid']}"); ?>}"  />
                 <?php } ?>
                 
                 </td>
                    <td> <?php echo $row['identifier']; ?></td>
                    <td>".$row['ip']; ?></td>
                    <td> <?php echo $row['saved_date']; ?></td>
                    <td><a href='mailto: <?php echo $row['email']; ?>'> <?php echo $row['email']; ?></td>
                   
    			   </tr>
             <?php } ?>
            </tbody></table><br />&nbsp
        