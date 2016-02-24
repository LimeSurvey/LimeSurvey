<tr class='<?php echo $bgcc; ?>' valign='top'>
    <td align='center'><input type='checkbox' class='cbResponseMarker' value='<?php echo $dtrow['id']; ?>' name='markedresponses[]' /></td>
    <td align='center'>
        <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/{$dtrow['id']}"); ?>'>
            <span title='<?php eT('View response details'); ?>' class="glyphicon glyphicon-list-alt text-success"></span>
        </a>

        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$dtrow['id']}"); ?>'>
                <span class="glyphicon glyphicon-pencil text-success" title="<?php eT('Edit this response'); ?>" ></span>
            </a>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete'))
            { ?>
            <a>
                <span id='deleteresponse_<?php echo $dtrow['id']; ?>' title='<?php eT('Delete this response'); ?>' class='deleteresponse glyphicon glyphicon-trash text-warning'></span>
            </a>
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
