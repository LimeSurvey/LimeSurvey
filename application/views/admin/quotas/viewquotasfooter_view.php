<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><?php echo $totalcompleted;?></td>
    <td><?php echo $totalquotas;?></td>
    <td style="padding: 3px;">
        <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','create')) { ?>
            <?php echo CHtml::form(array("admin/quotas/sa/newquota/surveyid/{$iSurveyId}"), 'post'); ?>
            <input name="submit" type="submit" class="quota_new btn btn-default" value="<?php eT("Add new quota");?>" />
            <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
            <input type="hidden" name="action" value="quotas" />
            <input type="hidden" name="subaction" value="new_quota" />
            </form>

            <?php } ?>
    </td>
    	</tr>
	</tbody>
</table>

</div></div></div>