<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id' => 'vvexport', 'class' => '')); ?>
    <div class="row">
        <div class="col-12">
            <div class="col-lg-6 text-start">
                <h4>
                    <?php eT("Export a VV survey file"); ?>
                </h4>
            </div>
        </div>
        <h3></h3>
    </div>

    <div class="row">
        <div class="col-md-6 content-right">
            <div class="card" id="panel-1" style="opacity: 1; top: 0px;">
                <div class="card-header bg-primary">
                    <div class="">
                        <?php eT("Export survey"); ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="surveyid" class="col-md-2 form-label">
                            <?php eT("Survey ID:"); ?>
                        </label>
                        <div class="col-md-4">
                            <?php echo CHtml::textField('surveyid', $surveyid, array('size' => 10, 'readonly' => 'readonly', 'class' => 'form-control')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="completionstate" class="col-md-2 form-label">
                            <?php eT("Export:"); ?>
                        </label>
                        <div class="col-md-8">
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default">
                                    <input name="completionstate" value="complete" type="radio" id="completionstate-complete" />
                                    <?php eT("Completed responses only"); ?>
                                </label>

                                <label class="btn btn-default active">
                                    <input name="completionstate" value="all" type="radio" checked='checked' id="completionstate-all" autofocus="true" />
                                    <?php eT("All responses"); ?>
                                </label>

                                <label class="btn btn-default">
                                    <input name="completionstate" value="incomplete" type="radio" id="completionstate-incomplete" />
                                    <?php eT("Incomplete responses only"); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="panel-extension" style="opacity: 1; top: 0px;">
                <div class="card-header bg-primary">
                    <div class="">
                        <?php eT("Format"); ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="extension" class="col-md-2 form-label" data-bs-toggle="tooltip" data-bs-placement="right" title='<?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'"); ?>'>
                            <?php eT("File extension:"); ?>
                        </label>
                        <div class="col-md-4">
                            <?php echo CHtml::textField('extension', 'csv', array('size' => 3, 'class' => 'form-control')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vvversion" class="col-md-2 form-label" data-bs-toggle="tooltip" data-bs-placement="right" title='<?php eT("If you want to import survey on old installation or if your survey have problem: use old version (automatically selected if some code are duplicated)."); ?>'>
                            <?php eT("VV export version:"); ?>
                        </label>
                        <div class="col-md-4">
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default <?php echo ($vvversionselected == 2 ? "active" : ""); ?>">
                                    <input name="vvversion" value="2" type="radio" id="vvversion-last" <?php echo ($vvversionselected == 2 ? "checked='checked'" : ""); ?> />
                                    <?php eT("Last VV version"); ?>
                                </label>

                                <label class="btn btn-default <?php echo ($vvversionselected == 1 ? "active" : ""); ?>">
                                    <input name="vvversion" value="1" type="radio" id="vvversion-old" <?php echo ($vvversionselected == 1 ? "checked='checked'" : ""); ?> />
                                    <?php eT("Old VV version"); ?>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php echo CHtml::submitButton(gT('Export results','unescaped'), array('class'=>'btn btn-default d-none')); ?>
            <?php echo CHtml::hiddenField('subaction','export'); ?>
            <form>
        </div>
    </div>
</div>

