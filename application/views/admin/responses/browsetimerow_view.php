<?php /*
<tr class='<?php echo $bgcc; ?>' valign='top'>
    <td align='center'><input type='checkbox' class='cbResponseMarker' value='<?php echo $dtrow['id']; ?>' name='markedresponses[]' /></td>
    <td align='center'>
        <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/{$dtrow['id']}"); ?>'>
            <span title='<?php eT('View response details'); ?>' class="fa fa-list-alt text-success"></span>
        </a>

        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$dtrow['id']}"); ?>'>
                <span class="fa fa-pencil text-success" title="<?php eT('Edit this response'); ?>" ></span>
            </a>
        <?php } ?>
        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete'))
            { ?>
            <a>
                <span id='deleteresponse_<?php echo $dtrow['id']; ?>' title='<?php eT('Delete this response'); ?>' class='deleteresponse fa fa-trash text-warning'></span>
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
*/ ?>

<?php
    $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider' => $model->search($iSurveyID, $language),

        'id' => 'time-grid',
        'emptyText'=>gT('No surveys found.'),
        'itemsCssClass' => 'table-striped',
        'htmlOptions' => array('class' => 'time-statistics-table'),

        'ajaxUpdate' => 'time-grid',
        'afterAjaxUpdate' => 'window.LS.doToolTip',

        // Number of row per page selection
        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSize',
                $pageSize,
                Yii::app()->params['pageSizeOptions'],
                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

        'columns' => array_merge(
            array(array(
                'header' => '',
                'name' => 'actions',
                'value'=>'$data->buttons',
                'type'=>'raw',
                'htmlOptions' => array('class' => 'time-statistics-row-buttons'),
            )),
            $columns)
    ));
