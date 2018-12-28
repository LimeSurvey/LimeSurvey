<?php
/**
 * @var $this AdminController
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyResponsesBrowse');

?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT('Survey responses'); ?></h3>
    <!-- Display mode -->
    <div class="text-right in-title">
        <div class="pull-right">
            <div class="form text-right">
                <form action="<?=App()->createUrl('/admin/responses/sa/browse/', ['surveyid' => $surveyid])?>" class="pjax" method="POST" id="change-display-mode-form">
                    <div class="form-group">
                        <label for="display-mode">
                            <?php
                                eT('Display mode:');
                            ?>
                        </label>
                        <?php
                            $state = Yii::app()->user->getState('responsesGridSwitchDisplayState') == "" ? 'compact' : Yii::app()->user->getState('responsesGridSwitchDisplayState');
                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup',
                            array(
                            'name' => 'displaymode',
                            'value'=> $state,
                            'selectOptions'=>array(
                                'extended'=>gT('Extended'),
                                'compact'=>gT('Compact')
                                ),
                            'htmlOptions' => array(
                                'classes' => 'selector__action-change-display-mode'
                                )
                            )
                        );
                        ?>
                        <input type="hidden" name="surveyid" value="<?=$surveyid?>" />
                        <input type="hidden" name="<?=Yii::app()->request->csrfTokenName?>" value="<?=Yii::app()->request->csrfToken?>" />
                        <input type="submit" class="hidden" name="submit" value="submit" />
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">
            <div id='top-scroller' class="content-right scrolling-wrapper"    >
            <div id='fake-content'>&nbsp;</div>
            </div>
            <div id='bottom-scroller' class="content-right scrolling-wrapper"    >
                <input type='hidden' name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>' />
                <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>' />

                <?php if (Yii::app()->user->getState('sql_'.$surveyid) != null ):?>
                    <!-- Filter is on -->
                    <?php eT("Showing filtered results"); ?>

                    <a class="btn btn-default" href="<?php echo Yii::app()->createUrl('admin/responses', array("sa"=>'browse','surveyid'=>$surveyid, 'filters'=>'reset')); ?>" role="button">
                        <?php eT("View without the filter."); ?>
                        <span aria-hidden="true">&times;</span>
                    </a>

                <?php endif;?>

                <?php $this->widget('application.widgets.grid.ResponseGridView', array(
                    'model' => $model,
                    'survey' => $survey,
                    'language' => $language,
                    'pageSize' => $pageSize
                )); ?>
            </div>

            <!-- To update rows per page via ajax setSession-->
            <?php

            $scriptVars = '
                var postUrl = "'.Yii::app()->getController()->createUrl("admin/responses/", array("sa" => "setSession")).'"; // For massive export
                ';
            $script = '
                var postUrl = "'.Yii::app()->getController()->createUrl("admin/responses/", array("sa" => "setSession")).'"; // For massive export
                jQuery(document).on("change", "#pageSize", function(){
                    $.fn.yiiGridView.update("responses-grid",{ data:{ pageSize: $(this).val() }});
                });
                $(".grid-view [data-toggle=\'popover\']").popover({container:\'body\'});
                ';
            App()->getClientScript()->registerScript('listresponses', $scriptVars, LSYii_ClientScript::POS_BEGIN);
            App()->getClientScript()->registerScript('listresponses', $script, LSYii_ClientScript::POS_POSTSCRIPT);
            ?>
        </div>
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
    <?php $this->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
        'name' => "no",
        'id'   => "no",
        'value' => '',
    )); ?>
</div>
