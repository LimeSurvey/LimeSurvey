<?php

/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var CActiveDataProvider $oDataProvider Containing Quota objects*/
/* @var array $aEditUrls */
/* @var array $aDeleteUrls */
/* @var array $aQuotaItems */
/* @var integer $totalquotas */
/* @var integer $totalcompleted */
/* @var integer $iGridPageSize */
/* @var Quota $oQuota The last Quota as base for Massive edits */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings The last Quota LanguageSettings */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyQuotas');

?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-12 content-right">
            <h3>
                <?php eT("Survey quotas");?>
            </h3>

            <?php if (isset($sShowError)) :?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong><?php eT("Quota could not be added!", 'js'); ?></strong><br/> <?php eT("It is missing a quota message for the following languages:", 'js'); ?><br/><?php echo $sShowError; ?>
                </div>
            <?php endif; ?>

            <?php
            $massiveAction = '';
            if ($oDataProvider->itemCount > 0) {
                if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas', 'create')) {
                    $massiveAction =  $this->renderPartial(
                        '/admin/quotas/viewquotas_massive_selector',
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
                    <?php $this->widget('application.extensions.admin.grid.CLSGridView', array(
                        'dataProvider'  => $oDataProvider,
                        'id'            => 'quota-grid',
                        'ajaxUpdate'    => 'quota-grid',
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
                                'header'            => gT("Action"),
                                'value'             => function ($oQuota) use ($oSurvey, $aEditUrls, $aDeleteUrls, $aQuotaItems) {
                                    /** @var Quota $oQuota */
                                    return $this->renderPartial(
                                        '/admin/quotas/viewquotas_quota_actions',
                                        array(
                                            'oSurvey'     => $oSurvey,
                                            'oQuota'      => $oQuota,
                                            'editUrl'     => $aEditUrls[$oQuota->getPrimaryKey()],
                                            'deleteUrl'   => $aDeleteUrls[$oQuota->getPrimaryKey()],
                                            'aQuotaItems' => $aQuotaItems,
                                        ),
                                        true
                                    );
                                },
                                'type'              => 'raw',
                            ),
                            array(
                                'name'        => gT('Quota members'),
                                'type'        => 'raw',
                                'htmlOptions' => array('style' => 'vertical-align:top'),
                                'value'       => function ($oQuota) use ($oSurvey, $aQuotaItems) {
                                    /** @var Quota $oQuota */
                                    $out = '<p>' . $this->renderPartial(
                                        '/admin/quotas/viewquotas_quota_members',
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

                        ),
                    ));
                    ?>
                </div>
            <?php endif; ?>
                <?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas', 'create')) :?>
                    <div class="pull-right">
                        <?php echo CHtml::beginForm(array("admin/quotas/sa/newquota/surveyid/{$oSurvey->getPrimaryKey()}"), 'post'); ?>
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
