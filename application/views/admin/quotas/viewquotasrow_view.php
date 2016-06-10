<tr>
    <td colspan="6" style="background-color: #567081; height: 2px">
    </td>
</tr>
<tr>
    <td><?php echo FlattenText($quotalisting['name']);?></td>
    <td>
        <?php if ($quotalisting['active'] == 1)
            {
                echo '<font color="#48B150">'.gT("Active").'</font>';
            } else {
                echo '<font color="#B73838">'.gT("Not active").'</font>';
            }
        ?>
    </td>
    <td>
        <?php if ($quotalisting['action'] == 1) {
                eT("Terminate survey");
            } elseif ($quotalisting['action'] == 2) {
                eT("Terminate survey with warning");
        } ?>
    </td>
    <td <?php echo $highlight;?>><?php echo is_null($completed) ? gT("N/A"): $completed ;?></td>
    <td><?php echo $quotalisting['qlimit'];?></td>
    <td style="padding: 3px;">
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) { ?>
                <a href="<?php echo $editUrl; ?>" class="btn btn-default" data-toggle='tooltip' title='<?php eT("Edit"); ?>'>
                    <span class='glyphicon glyphicon-pencil'></span>
                </a>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','delete')) { ?>
                <a data-href="<?php echo $deleteUrl; ?>" class="btn btn-default" data-toggle="modal" data-target="#confirmation-modal" data-tooltip="true" title="<?php eT("Delete");?>" >
                    <span class='glyphicon glyphicon-trash text-danger'></span>
                </a>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) {
            echo CHtml::link(CHtml::tag('span', array(
                'class' => 'glyphicon glyphicon-ok-sign',
                ), ''
            ), array(
                'admin/validate',
                'sa'=>'quota',
                'sid'=>$surveyid,
                'quota'=>$quotalisting['id'],
            ), array(
                'class'=>'btn btn-default',
                'title'=>sprintf(gT("Validation of quota %s"),$quotalisting['name']),
                'target'=>'dialog',
                'data-tooltip' => 'true'
            ));
        } ?>
    </td></tr>

<tr class="evenrow">
    <td>&nbsp;</td>
    <td><strong><?php eT("Questions");?></strong></td>
    <td><strong><?php eT("Answers");?></strong></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td style="padding: 3px;">
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','update')) { ?>
            <?php echo CHtml::form(array("admin/quotas/sa/new_answer/surveyid/{$iSurveyId}"), 'post'); ?>
                <input name="submit" type="submit" class="quota_new btn btn-default"  value="<?php eT("Add answer");?>" />
                <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                <input type="hidden" name="action" value="quotas" />
                <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
                <input type="hidden" name="subaction" value="new_answer" />
            </form>
            <?php } ?>
    </td>
</tr>
