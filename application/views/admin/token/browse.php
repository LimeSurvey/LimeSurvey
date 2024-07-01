<?php
/**
 * @var $aLanguages array
 * @var $model TokenDynamic
 * @var $dateformatdetails array
 * @var $massiveAction string
 */
// Build the options for additional languages
$aLanguageNames = [];
foreach ($aLanguages as $sCode => $sName) {
    $aLanguageNames[] = $sCode . ":" . str_replace(";", " -", (string) $sName);
}
$aLanguageNames = implode(";", $aLanguageNames);

?>

<div class='side-body'>
    <input type='hidden' id="dateFormatDetails" name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>'/>
    <input type="hidden" id="locale" name="locale" value="<?= convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']) ?>"/>
    <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>'/>
    <h3><?php eT("Survey participants"); ?></h3>
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
                'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSizeTokenView',
                            $pageSizeTokenView,
                            Yii::app()->params['pageSizeOptionsTokens'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto'])),
                'columns'               => $model->getAttributesForGrid(),
                'ajaxUpdate'            => 'token-grid',
                'ajaxType'              => 'POST',
                'lsAfterAjaxUpdate'       => ['onUpdateTokenGrid();']
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
        LSYii_ClientScript::POS_POSTSCRIPT);
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
