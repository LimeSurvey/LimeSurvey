<?php
    // Build the options for additional languages
    $aLanguageNames=array();
    foreach ($aLanguages as $sCode => $sName)
    {
        $aLanguageNames[] = $sCode . ":" . str_replace(";", " -", $sName);
    }
    $aLanguageNames = implode(";", $aLanguageNames);

?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <input type='hidden' name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>' />
    <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>' />
    <h3><?php eT("Survey participants"); ?></h3>

        <p class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
            <span class="fa fa-info-circle"></span>
            <?php eT("You can use operators in the search filters (eg: >, <, >=, <=, = )");?>
        </p>

    <!-- CGridView -->
    <?php $pageSizeTokenView=Yii::app()->user->getState('pageSizeTokenView',Yii::app()->params['defaultPageSize']);?>

        <!-- Todo : search boxes -->

        <!-- Grid -->
        <div class="row">
            <div class="content-right scrolling-wrapper"    >
                <?php
                    $this->widget('ext.LimeGridView.LimeGridView', array(
                        'dataProvider' => $model->search(),
                        'filter'=>$model,
                        'id' => 'token-grid',
                        'emptyText'=>gT('No survey participants found.'),
                        'template'  => "<div class='push-grid-pager'>{items}\n</div><div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSizeTokenView',
                                $pageSizeTokenView,
                                Yii::app()->params['pageSizeOptionsTokens'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
                        'itemsCssClass' =>'table-striped',
                        'columns' => $model->attributesForGrid,
                        'ajaxUpdate' => 'token-grid',
                        'ajaxType'=>'POST',
                        'afterAjaxUpdate' => 'onUpdateTokenGrid'
                    ));
                ?>
            </div>
        </div>

        <?php 
        // To update rows per page via ajax 
        App()->getClientScript()->registerScript("Tokens:neccesaryVars", "
        var postUrl = '".App()->createUrl('admin/tokens/sa/prepExportToCPDB/sid/'.$_GET['surveyid'])."';
        ", LSYii_ClientScript::POS_BEGIN);         
        App()->getClientScript()->registerScript("Tokens:updateRowsPerPage", "
            if($('#token-grid').length > 0){
                reinstallParticipantsFilterDatePicker();
                jQuery(document).on('change', '#pageSizeTokenView', function(){
                    $.fn.yiiGridView.update('token-grid',{ data:{ pageSizeTokenView: $(this).val() }});
                });
            }
            ", LSYii_ClientScript::POS_POSTSCRIPT); 
        ?>
    </div>
</div>


<!-- Edit Token Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="editTokenModal">
    <div class="modal-dialog" style="width: 1100px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php eT('Edit survey participant');?></h4>
            </div>
            <div class="modal-body">
                <!-- the ajax loader -->
                <div id="ajaxContainerLoading2" class="ajaxLoading" >
                    <p><?php eT('Please wait, loading data...');?></p>
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
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
                <button type="button" class="btn btn-primary" id="save-edittoken"><?php eT("Save");?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div style="display: none;">
<?php
Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
    'name' => "no",
    'id'   => "no",
    'value' => '',

));
?>
</div>
