<tr class='<?php echo $bgcc; ?>' valign='top'>
    <td align='center'><input type='checkbox' class='cbResponseMarker' value='<?php echo $dtrow['id']; ?>' name='markedresponses[]' /></td>
    <td align='center'>
        <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/{$dtrow['id']}"); ?>'><img src='<?php echo $sImageURL; ?>/token_viewanswer.png' alt='<?php $clang->eT('View response details'); ?>'/></a>

        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$dtrow['id']}"); ?>'><img src='<?php echo $sImageURL; ?>/edit_16.png' alt='<?php $clang->eT('Edit this response'); ?>'/></a>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete'))
            { ?>
            <a><img id='deleteresponse_<?php echo $dtrow['id']; ?>' src='<?php echo $sImageURL; ?>/token_delete.png' alt='<?php $clang->eT('Delete this response'); ?>' class='deleteresponse'/></a>
        <?php } ?>
    </td>
    <?php
        $i = 0;
        for ($i; $i < $fncount; $i++)
        {
            echo "<td align='center'>{$browsedatafield[$i]}</td>";
        }
    ?>

</tr>
