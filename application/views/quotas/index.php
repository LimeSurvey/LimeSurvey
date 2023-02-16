<?php

extract($quotasData);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyQuotas');

?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-12 content-right">
            <h3>
                <?php eT("Survey quotas");?>
            </h3>
            <?php
            $massiveAction = '';
            if ($oDataProvider->itemCount > 0) {
                if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas', 'create')) {
                    $massiveAction =  $this->renderPartial(
                        'viewquotas_massive_selector',
                        array(
                           'oSurvey' => $oSurvey,
                           'oQuota' => $oQuota,
                           'aQuotaLanguageSettings' => $aQuotaLanguageSettings,
                        ),
                        true
                    );
                }
            }
            ?>

            <?php if ($oDataProvider->itemCount > 0) :?>
            <!-- Grid -->
            <div class="row">
                <div class="col-12 content-right">
                    <?php $this->widget('application.extensions.admin.grid.CLSGridView', array( //done
                        'dataProvider'  => $oDataProvider,
                        'id'            => 'quota-grid',
                        'ajaxUpdate'    => 'quota-grid',
                        'afterAjaxUpdate' => 'bindListItemclick',
                        'emptyText'     => gT('No quotas'),
                        'massiveActionTemplate' => $massiveAction,
                        'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                            gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $iGridPageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array(
                                        'class' => 'changePageSize form-select',
                                        'style' => 'display: inline; width: auto',
                                        'onchange' => "$.fn.yiiGridView.update('quota-grid',{ data:{ pageSize: $(this).val() }})"
                                    )
                            )
                        ),
                        'columns'       => array(
                            array(
                                'id'             => 'id',
                                'class'          => 'CCheckBoxColumn',
                                'selectableRows' => '100',
                                'htmlOptions'    => array('style' => 'vertical-align:top'),
                            ),
                            array(
                                'name'        => gT('Quota members'),
                                'type'        => 'raw',
                                'htmlOptions' => array('style' => 'vertical-align:top'),
                                'value'       => function ($oQuota) use ($oSurvey, $aQuotaItems) {
                                    /** @var Quota $oQuota */
                                    $out = '<p>' . $this->renderPartial(
                                        '/quotas/viewquotas_quota_members',
                                        array(
                                                'oSurvey'     => $oSurvey,
                                                'oQuota'      => $oQuota,
                                                'aQuotaItems' => $aQuotaItems,
                                        ),
                                        true
                                    ) . '<p>';
                                    return $out;
                                },
                            ),
                            array(
                                'name'        => 'completeCount',
                                'header'      => gT('Completed'),
                                'type'        => 'raw',
                                'htmlOptions' => array('style' => 'vertical-align:top'),
                                'footer'      => $totalcompleted,
                            ),
                            array(
                                'name'        => 'qlimit',
                                'header'      => gT('Limit'),
                                'htmlOptions' => array('style' => 'vertical-align:top'),
                                'footer'      => $totalquotas,
                            ),
                            array(
                                'header'            => gT("Action"),
                                'name'        => 'actions',
                                'type'        => 'raw',
                                'value'       => '$data->buttons',
                                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                                'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
                            ),

                        ),
                    ));
                    ?>
                </div>
            <?php endif; ?>
                <?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas', 'create')) :?>
                    <div class="float-end">
                        <?php echo CHtml::beginForm(array("quotas/newquota/surveyid/{$oSurvey->getPrimaryKey()}"), 'post'); ?>
                        <?php echo CHtml::hiddenField('sid', $oSurvey->getPrimaryKey());?>
                        <?php echo CHtml::hiddenField('action', 'quotas');?>
                        <?php echo CHtml::hiddenField('subaction', 'new_quota');?>
                        <?php echo CHtml::endForm();?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
Yii::app()->getClientScript()->registerScript('quotas_load_validationmodal', "
    $('.selector__quota_open_validation').remoteModal({
        saveButton: false,
    }, {
        closeIcon : '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"></button>',
        closeButton : '<button type=\"button\" class=\"btn btn-cancel\" data-bs-dismiss=\"modal\">".gT("Close")."</button>',
        saveButton : '<button type=\"button\" class=\"btn btn-primary\">".gT("Close")."</button>'
    })
", LSYii_ClientScript::POS_POSTSCRIPT);

?>
