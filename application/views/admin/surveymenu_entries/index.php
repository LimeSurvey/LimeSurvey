<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
$massiveAction = App()->getController()->renderPartial('/admin/surveymenu_entries/massive_action/_selector', [], true, false);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyMenuEntries');

?>


<div class="ls-flex-row">
    <div class="col-12 ls-flex-item">
        <?php
        $this->widget(
            'bootstrap.widgets.TbGridView',
            [
                'dataProvider'             => $model->search(),
                'id'                       => 'surveymenu-entries-grid',
                'columns'                  => $model->getColumns(),
                'filter'                   => $model,
                'emptyText'                => gT('No customizable entries found.'),
                'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'surveymenuentriesPageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                        )
                    ),
                'rowHtmlOptionsExpression' => '["data-surveymenu-entry-id" => $data->id]',
                'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
                'ajaxType'                 => 'POST',
                'ajaxUpdate'               => 'surveymenu-entries-grid',
                'template'                 => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                'afterAjaxUpdate'          => 'surveyMenuEntryFunctions',
            ]
        );
        ?>
    </div>
</div>

<input type="hidden" id="surveymenu_open_url_selected_entry" value=""/>
<!-- modal! -->

<div class="modal fade" id="editcreatemenuentry" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>

<div class="modal fade" id="deletemodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
          <?php
          Yii::app()->getController()->renderPartial(
              '/layouts/partial_modals/modal_header',
              ['modalTitle' => gT('Delete this survey menu entry')]
          );
          ?>
        <div class="modal-body">
          <?php eT("Please be careful - if you delete default entries you may not be able access some parts of the application."); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <?php eT('Cancel'); ?>
          </button>
          <button type="button" id="deletemodalentry-confirm" class="btn btn-danger">
            <?php eT('Delete'); ?>
          </button>
        </div>
      </div>
    </div>
</div>



