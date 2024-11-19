<?php
/**
 * @var AdminController $this
 * @var Survey $oSurvey
 * @var array $queries
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyParticipantsIndex');

?>
<div class='side-body survey-response-page'>
    <h1> <?= gT("Survey participants") ?> </h1>
    <div class="mt-4">
        <div class="accordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                        <span class="summary-title py-1"><?php eT("Survey participant summary"); ?></span>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col summary-detail">
                                        <?php eT("Total records"); ?>
                                    </div>
                                    <div class="col">
                                        <?php echo $queries['count']; ?>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col summary-detail">
                                        <?php eT("Total with no unique participant access code"); ?>
                                    </div>
                                    <div class="col">
                                        <?php echo $queries['invalid']; ?>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col summary-detail">
                                        <?php eT("Total invitations sent"); ?>
                                    </div>
                                    <div class="col">
                                        <?php echo $queries['sent']; ?>
                                    </div>

                                </div>
                            </div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="col summary-detail">
                                        <?php eT("Total opted out"); ?>
                                    </div>
                                    <div class="col">
                                        <?php echo $queries['optout']; ?>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col summary-detail">
                                        <?php eT("Total screened out"); ?>
                                    </div>
                                    <div class="col">
                                        <?php echo $queries['screenout']; ?>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col summary-detail">
                                        <?php eT("Total surveys completed"); ?>
                                    </div>
                                    <div class="col">
                                        <?php echo $queries['completed']; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h2 class="summary-title mt-4 pb-2 mb-3"><?php eT("All participants"); ?></h2>
    <div class='side-body'>
        <input type='hidden' id="dateFormatDetails" name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>' />
        <input type="hidden" id="locale" name="locale" value="<?= convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']) ?>" />
        <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>' />
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'tag'  => 'p',
            'text' => gT(
                "You can use operators in the search filters (eg: >, <, >=, <=, = )"
            ),
            'type' => 'info',
        ]);
        ?>

        <!-- CGridView -->
        <?php $pageSizeTokenView = Yii::app()->user->getState(
            'pageSizeTokenView',
            Yii::app()->params['defaultPageSize']
        ); ?>

        <!-- Todo : search boxes -->

        <!-- Grid -->
        <div class="row">
            <div class="content-right">
                <?php
                $this->widget('application.extensions.admin.grid.CLSGridView', [
                    'dataProvider'          => $model->search(),
                    'filter'                => $model,
                    'id'                    => 'token-grid',
                    'emptyText'             => gT('No survey participants found.'),
                    'massiveActionTemplate' => $massiveAction,
                    'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSizeTokenView',
                            $pageSizeTokenView,
                            Yii::app()->params['pageSizeOptionsTokens'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
                    'columns'               => $model->getAttributesForGrid(),
                    'ajaxUpdate'            => 'token-grid',
                    'ajaxType'              => 'POST',
                    'lsAfterAjaxUpdate'       => ['onUpdateTokenGrid();', 'switchStatusOfListActions();']
                ]);
                ?>
            </div>
        </div>

        <?php
        // To update rows per page via ajax
        App()->getClientScript()->registerScript(
            "Tokens:neccesaryVars",
            "var postUrl = '" . App()->createUrl('admin/tokens/sa/prepExportToCPDB/sid/' . $_GET['surveyid']) . "';",
            LSYii_ClientScript::POS_BEGIN
        );
        App()->getClientScript()->registerScript(
            "Tokens:updateRowsPerPage",
            "if($('#token-grid').length > 0){
            reinstallParticipantsFilterDatePicker();
        }",
            LSYii_ClientScript::POS_POSTSCRIPT
        );
        ?>
    </div>


    <!-- Edit Token Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="editTokenModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php eT('Edit survey participant'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- the ajax loader -->
                    <div id="ajaxContainerLoading2" class="ajaxLoading">
                        <p><?php eT('Please wait, loading data...'); ?></p>
                        <div class="preloader loading">
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                        </div>
                    </div>
                    <div id="modal-content">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                    <button role="button" type="button" class="btn btn-primary" id="save-edittoken">
                        <?php eT("Save"); ?>
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>
