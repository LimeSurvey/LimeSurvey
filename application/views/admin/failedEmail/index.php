<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Failed e-mail notifications"); ?></h3>
    <p class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
        <span class="fa fa-info-circle"></span>
        <?php eT("Note that entries in this table will be automatically deleted after 30 days."); ?>
    </p>
    <!-- CGridView -->
    <?php $pageSizeTokenView = App()->user->getState('pageSizeTokenView', App()->params['defaultPageSize']); ?>
    <!-- Grid -->
    <div class="row">
        <div class="content-right">
            <?php
            $this->widget('ext.LimeGridView.LimeGridView', [
                'dataProvider'    => $model->search(),
                'filter'          => $model,
                'id'              => 'email-failed-notifications-grid',
                'emptyText'       => gT('No failed e-mail notifications found'),
                'template'        => "<div class='push-grid-pager'>{items}\n</div><div id='emailFailedNotificationsPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSizeEmailFailedNotificationsView',
                            $pageSizeTokenView,
                            App()->params['pageSizeOptionsTokens'],
                            ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto'])),
                'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                'columns'         => $model->attributesForGrid,
                'ajaxUpdate'      => 'token-grid',
                'ajaxType'        => 'POST',
                'afterAjaxUpdate' => 'onUpdateTokenGrid'
            ]);
            ?>
        </div>
    </div>
</div>