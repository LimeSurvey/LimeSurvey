<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3>
        <span style='font-weight:bold;'><?php eT('Saved responses'); ?></span>
        <?php echo flattenText($sSurveyName) . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?>
    </h3>

        <div class="row">
            <div class="col-lg-12 content-right">
                <div class="alert alert-info" role="alert">
                    <?php eT('Total:'); ?> <?php echo getSavedCount($iSurveyId); ?>
                </div>

                <p>
                    <table class='browsetable table' style='margin:0 auto; width:60%'>
                        <thead>
                            <tr>
                                <th><?php eT('ID'); ?></th>
                                <th><?php eT('Actions'); ?></th>
                                <th><?php eT('Identifier'); ?></th>
                                <th><?php eT('IP address'); ?></th>
                                <th><?php eT('Date saved'); ?></th>
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
                                            <span onclick="window.open('<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$iSurveyId}/id/{$oResult->srid}"); ?>', '_top')" title='<?php eT('Edit entry'); ?>'  class="fa fa-pencil text-success"></span>
                                            <?php }
                                            if (Permission::model()->hasSurveyPermission($iSurveyId,'responses','delete'))
                                            { ?>
                                            <span class="fa fa-trash text-warning" title='<?php eT('Delete entry'); ?>' onclick="if (confirm('<?php eT('Are you sure you want to delete this entry?', 'js'); ?>')) { window.open('<?php echo $this->createUrl("admin/saved/delete/surveyid/{$iSurveyId}/srid/{$oResult->srid}/scid/{$oResult->scid}"); ?>', '_top'); }" ></span>
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
