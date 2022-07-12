<?php
/**
 * @var FailedEmail $failedEmailModel
 * @var int $pageSizeTokenView
 * @var string $massiveAction
 */
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Failed e-mail notifications"); ?></h3>
    <p class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
        <span class="fa fa-info-circle"></span>
        <?php eT("Note that entries in this table will be automatically deleted after 30 days."); ?>
    </p>
    <!-- Grid -->
    <div class="row">
        <div class="content-right">
            <?php

            $this->widget('bootstrap.widgets.TbGridView', [
                'dataProvider'    => $failedEmailModel->search(),
                'filter'          => $failedEmailModel,
                'id'              => 'failedemail-grid',
                'emptyText'       => gT('No failed e-mail notifications found'),
                'template'        => "<div class='push-grid-pager'>{items}\n</div><div id='emailFailedEmailPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSizeFailedEmailView',
                            $pageSizeTokenView,
                            App()->params['pageSizeOptionsTokens'],
                            ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto'])),
                'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                'columns'         => $failedEmailModel->getColumns(),
                'ajaxUpdate'      => 'failedemail-grid',
                'ajaxType'        => 'POST',
//                'afterAjaxUpdate' => 'failedemail-grid'
            ]);
            ?>
        </div>
    </div>
</div>
<?php
