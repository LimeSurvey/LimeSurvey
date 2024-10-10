<div class='side-body'>
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
                <div class="card-header ">
                    <div class="">
                        <?php eT("Export survey"); ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label text-end" for="surveyid"><?php eT("Survey ID:"); ?></label>
                        <div class="col-sm-9">
                            <?php echo CHtml::textField('surveyid', $surveyid, array('size' => 10, 'readonly' => 'readonly', 'class' => 'form-control')); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label text-end" for="completionstate"><?php eT("Export:"); ?></label>
                        <div class="col-sm-9">
                            <select class="form-select" name="completionstate" id="completionstate">
                                <option value="complete"  id="completionstate-complete">
                                    <?php eT("Completed responses only"); ?>
                                </option>
                                <option value="all" id="completionstate-all" selected>
                                    <?php eT("All responses"); ?>
                                </option>
                                <option value="incomplete" id="completionstate-incomplete">
                                    <?php eT("Incomplete responses only"); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="panel-extension" style="opacity: 1; top: 0px;">
                <div class="card-header ">
                    <div class="">
                        <?php eT("Format"); ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label for="extension" class="col-sm-3 col-form-label text-end" data-bs-toggle="tooltip" data-bs-placement="right" title='<?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'"); ?>'>
                            <?php eT("File extension:"); ?>
                        </label>
                        <div class="col-sm-9">
                            <?php echo CHtml::textField('extension', 'csv', array('size' => 3, 'class' => 'form-control')); ?>
                            <p class="help-block"><?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'"); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="vvversion" class="col-sm-3 col-form-label text-end" data-bs-toggle="tooltip" data-bs-placement="right" title='<?php eT("If you want to import survey on old installation or if your survey have problem: use old version (automatically selected if some code are duplicated)."); ?>'>
                            <?php eT("VV export version:"); ?>
                        </label>
                        <div class="col-sm-9">
                            <div class="btn-group">
                                <input class="btn-check" name="vvversion" value="2" type="radio" id="vvversion-last" <?php echo ($vvversionselected == 2 ? "checked='checked'" : ""); ?> />
                                <label for="vvversion-last" class="btn btn-outline-secondary">
                                    <?php eT("Last VV version"); ?>
                                </label>

                                <input class="btn-check" name="vvversion" value="1" type="radio" id="vvversion-old" <?php echo ($vvversionselected == 1 ? "checked='checked'" : ""); ?> />
                                <label for="vvversion-old" class="btn btn-outline-secondary">
                                    <?php eT("Old VV version"); ?>
                                </label>
                            </div>
                            <p class="help-block"><?php eT("If you want to import the response data from an older version or if your survey has an integrity problem, please use the old export version (automatically selected if there are duplicate codes)."); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="qseparator" class="col-sm-3 col-control-label text-end">
                            <?php eT("Separator between question and subquestion:"); ?>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select" name="qseparator" id="qseparator">
                                <option value="newline"><?php eT("New line (use with care)"); ?></option>
                                <option value="parenthesis" selected><?php eT("Subquestion wrapped by parentheses"); ?></option>
                                <option value="dash"><?php printf(gT("Single dash (%s)"), ' - '); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="abbreviatedtextto" class="col-sm-3 col-control-label text-end">
                            <?php eT("Number of characters:"); ?>
                        </label>
                        <div class="col-sm-9">
                            <?php echo CHtml::numberField('abbreviatedtextto', '', array('min' => "1", 'step' => "1", 'class' => 'form-control')); ?>
                            <p class="help-block"><?php eT("Leave empty if you want the complete question text."); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo CHtml::submitButton(gT('Export results','unescaped'), array('class'=>'btn btn-outline-secondary d-none')); ?>
            <?php echo CHtml::hiddenField('subaction','export'); ?>
            <form>
        </div>
    </div>
</div>

