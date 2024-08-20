<?php
/**
 * @var AdminController $this
 * @var array $searchcondition
 * @var Participant $model
 * @var string $massiveAction
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('displayParticipants');

?>
<div id="pjax-content">
        <?php
        $hiddenFilterValues = "";
        if ($searchcondition) {
            echo "<div class='' id='ParticipantFilters'>"
                . "<div class='row'>"
                . "<div class='col-12'>"
                . gT("Active filters:")
                . "<span class=''>&nbsp;&nbsp;</span>"
                . "<button id='removeAllFilters' class='btn btn-warning btn-xs' data_url='" . App()->createUrl("admin/participants/sa/displayParticipants") . "'>" . gT("Remove filters") . "</button>"
                . "</div>"
                . "</div>";
            $i = 0;
            $iNumberOfConditions = (count($searchcondition) + 1) / 4;
            while ($i < $iNumberOfConditions) {
                $sFieldname = $searchcondition[$i * 4];
                $sOperator  = $searchcondition[($i * 4) + 1];
                switch ($sOperator) {
                    case 'equal':
                        $operator = gT('equals');
                        break;
                    case 'contains':
                    case 'beginswith':
                        $operator = gT("is like");
                        break;
                    case 'notequal':
                    case 'notcontains':
                        $operator = gT("is not like");
                        break;
                    case 'greaterthan':
                        $operator = gT("is greater than");
                        break;
                    case 'lessthan':
                        $operator = gT("is lesser than");
                        break;
                }
                $sValue = $searchcondition[($i * 4) + 2];

                echo "<div class='row'>"
                    . "<div class='col-12'>"
                    . " " . $model->getAttributeLabel($sFieldname) . " " . $operator . " " . $sValue
                    . "</div>"
                    . "</div>";
                $i++;
            }
            echo "</div>";
        }

        ?>
        <div class="row">
            <?php
            echo "<input type='hidden' id='searchcondition' name='searchcondition[]' value='" . join("||", $searchcondition) . "' />";

            $this->widget('application.extensions.admin.grid.CLSGridView', [
                'id'                       => 'list_central_participants',
                'dataProvider'             => $model->search(),
                'columns'                  => $model->columns,
                'massiveActionTemplate'    => $massiveAction,
                'lsAfterAjaxUpdate'        => ['LS.CPDB.bindButtons;', 'LS.CPDB.participantPanel();', 'bindListItemclick();', 'switchStatusOfListActions();'],
                'ajaxType'                 => 'POST',
                'rowHtmlOptionsExpression' => '["data-participant_id" => $data->id]',
                'beforeAjaxUpdate'         => 'insertSearchCondition',
                'filter'                   => $model,
                'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                    . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSizeParticipantView',
                            Yii::app()->user->getState('pageSizeParticipantView', Yii::app()->params['defaultPageSize']),
                            App()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
            ]);

            ?>
    </div>
    <span id="locator" data-location="participants">&nbsp;</span>
</div>
