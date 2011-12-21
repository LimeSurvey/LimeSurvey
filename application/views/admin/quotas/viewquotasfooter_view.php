		<tr>
    		<td align="center">&nbsp;</td>
    		<td align="center">&nbsp;</td>
    		<td align="center">&nbsp;</td>
    		<td align="center"><?php echo $totalquotas;?></td>
    		<td align="center"><?php echo $totalcompleted;?></td>
    		<td align="center" style="padding: 3px;">
<?php if (bHasSurveyPermission($iSurveyId, 'quotas','create')) { ?>
    <form action="<?php echo $this->createUrl("admin/quotas/surveyid/$iSurveyId/subaction/new_quota");?>" method="post">
                    <input name="submit" type="submit" class="quota_new" value="<?php echo $clang->gT("Add New Quota");?>" />
                    <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                    <input type="hidden" name="action" value="quotas" />
                    <input type="hidden" name="subaction" value="new_quota" />
    </form>

<?php } ?>
</td>
    	</tr>
	</tbody>
</table>
