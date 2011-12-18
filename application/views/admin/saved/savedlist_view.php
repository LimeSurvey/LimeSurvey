<p>
    <table class='browsetable' align='center'>
        <thead>
            <tr>
                <th><?php $clang->eT('ID'); ?></th>
                <th><?php $clang->eT('Actions'); ?></th>
                <th><?php $clang->eT('Identifier'); ?></th>
                <th><?php $clang->eT('IP address'); ?></th>
                <th><?php $clang->eT('Date Saved'); ?></th>
                <th><?php $clang->eT('Email address'); ?></th>
            </tr>
        </thead>

        <tbody>
        <?php foreach($aResults as $oResult)
        { ?>
            <tr>
                <td><?php echo $oResult->scid; ?></td>
                <td align='center'>

                    <?php if (bHasSurveyPermission($iSurveyId,'responses','update'))
                    { ?>
                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/token_edit.png'
                           title='<?php $clang->eT('Edit entry'); ?>' onclick="window.open('<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$iSurveyId}/id/{$oResult->srid}"); ?>', '_top')" />
                    <?php }
                    if (bHasSurveyPermission($iSurveyId,'responses','delete'))
                    { ?>
                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/token_delete.png'
                           title='<?php $clang->eT('Delete entry'); ?>' onclick="if (confirm('<?php $clang->eT('Are you sure you want to delete this entry?', 'js'); ?>')) { window.open('<?php echo $this->createUrl("admin/saved/sa/delete/surveyid/{$iSurveyId}/srid/{$oResult->srid}/scid/{$oResult->scid}"); ?>', '_top'); }" />
                    <?php } ?>

                </td>

                <td><?php echo $oResult->identifier; ?></td>
                <td><?php echo $oResult->ip; ?></td>
                <td><?php echo $oResult->saved_date; ?></td>
                <td><a href='mailto: <?php echo $oResult->email; ?>'> <?php echo $oResult->email; ?></td>

            </tr>
        <?php } ?>
        </tbody>
    </table>
    <br />&nbsp
</p>