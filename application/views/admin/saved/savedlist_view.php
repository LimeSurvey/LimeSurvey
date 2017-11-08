<p>
    <table class='browsetable' style='margin:0 auto; width:60%'>
        <thead>
            <tr>
                <th><?php eT('ID'); ?></th>
                <th><?php eT('Actions'); ?></th>
                <th><?php eT('Identifier'); ?></th>
                <th><?php eT('IP address'); ?></th>
                <th><?php eT('Date Saved'); ?></th>
                <th><?php eT('Email address'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($aResults as $oResult)
                { ?>
                <tr>
                    <td><?php echo $oResult->scid; ?></td>
                    <td align='center'>

                        <?php if (Permission::model()->hasSurveyPermission($iSurveyId,'responses','update'))
                            { ?>
                            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $sImageURL; ?>edit_16.png'
                                title='<?php eT('Edit entry'); ?>' onclick="window.open('<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$iSurveyId}/id/{$oResult->srid}"); ?>', '_top')" />
                            <?php }
                            if (Permission::model()->hasSurveyPermission($iSurveyId,'responses','delete'))
                            { ?>
                            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $sImageURL; ?>token_delete.png'
                                title='<?php eT('Delete entry'); ?>' onclick="if (confirm('<?php eT('Are you sure you want to delete this entry?', 'js'); ?>')) { window.open('<?php echo $this->createUrl("admin/saved/delete/surveyid/{$iSurveyId}/srid/{$oResult->srid}/scid/{$oResult->scid}"); ?>', '_top'); }" />
                            <?php } ?>

                    </td>

                                    <td><?php echo htmlspecialchars($oResult->identifier); ?></td>
                                    <td><?php echo $oResult->ip; ?></td>
                                    <td><?php echo $oResult->saved_date; ?></td>
                                    <td><?php echo CHtml::link(htmlspecialchars($oResult->email),'mailto:'.htmlspecialchars($oResult->email)); ?></td>

                                </tr>
                                <?php } ?>
                        </tbody>
                    </table>
                    <br />&nbsp;
                </p>
</div></div></div>
