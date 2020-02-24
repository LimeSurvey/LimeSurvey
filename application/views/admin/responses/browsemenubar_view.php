<?php
/* @var Survey $oSurvey */
?>
<div class='menubar surveybar' id="browsermenubarid">
    <div class='row container-fluid'>
        <?php if(isset($menu) && isset($menu['edition']) && !$menu['edition']): ?>
            <div class="col-md-12">
            <!-- Show summary information -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')): ?>
                <a class="btn btn-default pjax" href='<?php echo $this->createUrl("admin/responses/sa/index/surveyid/$surveyid"); ?>' role="button">
                    <span class="fa fa-list-alt text-success"></span>
                    <?php eT("Summary"); ?>
                </a>
            <?php endif;?>

            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')): ?>
                <!-- Display Responses -->
                <?php if (count($oSurvey->allLanguages) < 2): ?>
                    <a class="btn btn-default pjax" href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid"); ?>' role="button">
                        <span class="fa fa-list text-success"></span>
                        <?php eT("Display responses"); ?>
                    </a>
                <?php else:?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="fa fa-list text-success"></span>
                        <?php eT("Responses"); ?> <span class="fa fa-caret-down"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                        <li>
                            <a class="pjax" href="<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid/browselang/$tmp_lang"); ?>" accesskey='b'>
                                <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                             </a>
                        </li>
                        <?php endforeach;?>
                    </ul>
                </div>
                <?php endif;?>
            <?php endif;?>


            <!-- Dataentry Screen for Survey -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'create')): ?>
                <a class="btn btn-default" href='<?php echo $this->createUrl("admin/dataentry/sa/view/surveyid/$surveyid"); ?>' role="button">
                    <span class="fa fa-keyboard-o text-success"></span>
                    <?php eT("Data entry"); ?>
                </a>
            <?php endif;?>

            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'statistics', 'read')): ?>
                <!-- Get statistics from these responses -->
                <a class="btn btn-default" href='<?php echo $this->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>' role="button">
                    <span class="fa fa-bar-chart text-success"></span>
                    <?php eT("Statistics"); ?>
                </a>

                <!-- Get time statistics from these responses -->
                <?php if ($thissurvey['savetimings'] == "Y"):?>
                    <a class="btn btn-default" href='<?php echo $this->createUrl("admin/responses/sa/time/surveyid/$surveyid"); ?>' role="button">
                        <span class="fa fa-clock-o text-success"></span>
                        <?php eT("Timing statistics"); ?>
                    </a>
                <?php endif;?>
            <?php endif;?>


            <!-- Export -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'export')): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-export text-success"></span>
                        <?php eT("Export"); ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">

                        <!-- Export results to application -->
                        <li>
                            <a href='<?php echo $this->createUrl("admin/export/sa/exportresults/surveyid/$surveyid"); ?>'>
                                <?php eT("Export responses"); ?>
                            </a>
                        </li>

                        <!-- Export results to a SPSS/PASW command file -->
                        <li>
                            <a href='<?php echo $this->createUrl("admin/export/sa/exportspss/sid/$surveyid"); ?>'>
                                <?php eT("Export responses to SPSS"); ?>
                            </a>
                        </li>

                        <!-- Export a VV survey file -->
                        <li>
                            <a href='<?php echo $this->createUrl("admin/export/sa/vvexport/surveyid/$surveyid"); ?>'>
                                <?php eT("Export a VV survey file"); ?>
                            </a>
                        </li>

                    </ul>
                </div>
            <?php endif;?>


            <!-- Import -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'create')): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-import text-success"></span>
                        <?php eT("Import"); ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">

                        <!-- Import responses from a deactivated survey table -->
                        <li>
                            <a href='<?php echo $this->createUrl("admin/dataentry/sa/import/surveyid/$surveyid"); ?>' role="button">
                                <?php eT("Import responses from a deactivated survey table"); ?>
                            </a>
                        </li>

                        <!-- Import a VV survey file -->
                        <li>
                            <a href='<?php echo $this->createUrl("admin/dataentry/sa/vvimport/surveyid/$surveyid"); ?>' role="button">
                                <?php eT("Import a VV survey file"); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif;?>


            <!-- View Saved but not submitted Responses -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')): ?>
                <a class="btn btn-default" href='<?php echo $this->createUrl("admin/saved/sa/view/surveyid/$surveyid"); ?>' role="button">
                    <span class="icon-saved text-success"></span>
                    <?php eT("View Saved but not submitted Responses"); ?>
                </a>
            <?php endif;?>


            <!-- Iterate survey -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete')): ?>
                <?php if ($thissurvey['anonymized'] == 'N' && $thissurvey['tokenanswerspersistence'] == 'Y'): ?>
                    <a class="btn btn-default" href='<?php echo $this->createUrl("admin/dataentry/sa/iteratesurvey/surveyid/$surveyid"); ?>' role="button">
                        <span class="fa fa-repeat text-success"></span>
                        <?php eT("Iterate survey"); ?>
                    </a>
                <?php endif;?>
            <?php endif;?>

            <!-- Batch deletion -->
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete')): ?>
                <a
                    id="response-batch-deletion"
                    href="<?php echo $this->createUrl("/admin/responses/sa/actionDelete/", array("surveyid" => $surveyid )); ?>"
                    data-post="{}"
                    data-show-text-area="true"
                    data-use-ajax="true"
                    data-grid-id="responses-grid"
                    data-grid-reload="true"
                    data-text="<?php eT('Enter a list of response IDs that are to be deleted, separated by comma.')?><br/><?= gT('Please note that if you delete an incomplete response during a running survey, the participant will not be able to complete it.'); ?>"
                    title="<?php eT('Batch deletion')?>"
                    class="btn btn-default selector--ConfirmModal">

                    <span class="fa fa-trash text-danger"></span>
                        <?php eT("Batch deletion"); ?>
                </a>
            <?php endif;?>

        </div>


        <?php else: ?>
        <div class="col-md-7 text-right col-md-offset-5">
            <?php if(isset($menu['save'])): ?>
                <a class="btn btn-success" href="#" role="button" id="save-button">
                    <span class="fa fa-floppy-o"></span>
                    <?php eT("Save");?>
                </a>
            <?php endif;?>

            <?php if(isset($menu['export'])): ?>
                <button class="btn btn-success" name="export-button" id="export-button" data-submit-form=1>
                    <span class="fa fa-download-alt"></span>
                    <?php eT("Export");?>
                </button>
            <?php endif;?>

            <?php if(isset($menu['import'])): ?>
                <button class="btn btn-success" name="import-button" id="import-button" data-submit-form=1>
                    <span class="fa fa-upload"></span>
                    <?php eT("Import");?>
                </button>
            <?php endif;?>

            <?php if(isset($menu['stats'])):?>
                <?php if (isset($menu['expertstats']) && $menu['expertstats'] =  true):?>
                    <a class="btn btn-info" href="<?php echo App()->createUrl('/admin/statistics/sa/index/surveyid/'.$surveyid); ?>" id="expert-mode">
                        <span class="fa fa-bar-chart"></span>
                        <?php eT("Expert mode"); ?>
                    </a>
                <?php else: ?>
                    <a class="btn btn-info" href="<?php echo App()->createUrl('/admin/statistics/sa/simpleStatistics/surveyid/'.$surveyid); ?>" id="simple-mode">
                        <span class="fa fa-bar-chart"></span>
                        <?php eT("Simple mode"); ?>
                    </a>
                    <button class="btn btn-success" name="view-button" id="view-button" data-submit-form=1>
                        <span class="fa"></span>
                        <?php eT("View statistics"); ?>
                    </button>

                    <a class="btn btn-default" href="<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/",array('surveyid'=>$surveyid)) ?>" id="clear-button">
                        <span class="fa fa-refresh text-success"></span>
                        <?php eT("Clear"); ?>
                    </a>
                <?php endif; ?>

            <?php endif;?>
            <?php if (isset($menu['view'])): ?>
                <?php if ($exist): ?>
                    <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update') && isset($rlanguage)): ?>
                    <a class="btn btn-default" href='<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}/lang/$rlanguage"); ?>' role="button">

                        <span class="fa fa-pencil text-success"></span>
                        <?php eT("Edit this entry"); ?>
                    </a>
                    <?php endif;?>
                    <?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete') && isset($rlanguage)): ?>
                    <a class="btn btn-default" href='#' role="button" onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?", "js"); ?>")) { <?php echo convertGETtoPOST($this->createUrl("admin/dataentry/sa/delete/", ['id' => $id, 'sid' => $surveyid])); ?>}'>
                        <span class="fa fa-trash text-warning"></span>
                        <?php eT("Delete this entry"); ?>
                    </a>
                    <?php endif;?>

                    <?php if ($bHasFile): ?>
                    <a class="btn btn-default" href='<?php echo Yii::app()->createUrl("admin/responses",array("sa"=>"actionDownloadfiles","surveyid"=>$surveyid,"sResponseId"=>$id)); ?>' role="button" >
                        <span class="fa  fa-download-alt text-success"></span>
                        <?php eT("Download files"); ?>
                    </a>
                    <?php endif;?>

                    <a class="btn btn-default" href='<?php echo $this->createUrl("admin/export/sa/exportresults/surveyid/$surveyid/id/$id"); ?>' role="button" >
                        <span class="icon-export text-success downloadfile"></span>
                        <?php eT("Export this response"); ?>
                    </a>
                <?php endif;?>

            <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/$previous"); ?>' title='<?php eT("Show previous..."); ?>'
                class="btn btn-default <?php if (!$previous) {echo 'disabled';}?>">
                <span class="icon-databack text-success" title='<?php eT("Show previous..."); ?>'></span> <?php eT("Show previous..."); ?>
            </a>
            <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/$next"); ?>' title='<?php eT("Show next..."); ?>'
            class="btn btn-default <?php if (!$next) {echo 'disabled';}?>">
                <span class="icon-dataforward text-success" title='<?php eT("Show next..."); ?>'></span> <?php eT("Show next..."); ?>
            </a>

            <?php endif;?>

            <?php if(isset($menu) && isset($menu['close']) && $menu['close']): ?>
                <a class="btn btn-danger" href="<?php echo  $menu['closeurl'];  ?>" role="button">
                    <span class="fa fa-close"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
        </div>
        <?php endif;?>
    </div>
</div>
