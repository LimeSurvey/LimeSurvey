<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id' => 'vvexport', 'class' => '')); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6 text-left">
                <h4>
                    <?php eT("Export a VV survey file"); ?>
                </h4>
            </div>
        </div>
        <h3></h3>
    </div>

    <div class="row">
        <div class="col-sm-6 content-right">
            <div class="panel panel-primary" id="panel-1" style="opacity: 1; top: 0px;">
                <div class="panel-heading">
                    <div class="panel-title h4">
                        <?php eT("Export survey"); ?>
                    </div>
                </div>

                <div class="panel-body form-horizontal">
                    <div class="form-group">
                        <label for="surveyid" class="col-sm-4 control-label">
                            <?php eT("Survey ID:"); ?>
                        </label>
                        <div class="col-sm-8">
                            <?php echo CHtml::textField('surveyid', $surveyid, array('size' => 10, 'readonly' => 'readonly', 'class' => 'form-control')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="completionstate" class="col-sm-4 control-label">
                            <?php eT("Export:"); ?>
                        </label>
                        <div class="col-sm-8">
                            <select class="form-control" name="completionstate" id="completionstate">
                                <option value="complete"  id="completionstate-complete"><?php eT("Completed responses only"); ?></option>
                                <option value="all" id="completionstate-all" selected><?php eT("All responses"); ?></option>
                                <option value="incomplete" id="completionstate-incomplete"><?php eT("Incomplete responses only"); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-primary" id="panel-extension" style="opacity: 1; top: 0px;">
                <div class="panel-heading">
                    <div class="panel-title h4">
                        <?php eT("Format"); ?>
                    </div>
                </div>
                <div class="panel-body form-horizontal">
                    <div class="form-group">
                        <label for="extension" class="col-sm-4 control-label">
                            <?php eT("File extension:"); ?>
                        </label>
                        <div class="col-sm-8">
                            <?php echo CHtml::textField('extension', 'csv', array('size' => 3, 'class' => 'form-control')); ?>
                            <p class="help-block"><?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'"); ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vvversion" class="col-sm-4 control-label">
                            <?php eT("VV export version:"); ?>
                        </label>
                        <div class="col-sm-8">
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
                            <p class="help-block"><?php eT("If you want to import the response data from an older version or if your survey has an integrity problem, please use the old export version (automatically selected if there are duplicate codes)."); ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="qseparator" class="col-sm-4 control-label">
                            <?php eT("Separator between question and subquestion:"); ?>
                        </label>
                        <div class="col-sm-8">
                            <select class="form-control" name="qseparator" id="qseparator">
                                <option value="newline"><?php eT("New line (use with care)"); ?></option>
                                <option value="parenthesis" selected><?php eT("Subquestion wrapped by parentheses"); ?></option>
                                <option value="dash"><?php printf(gT("Single dash (%s)"), ' - '); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="abbreviatedtextto" class="col-sm-4 control-label">
                            <?php eT("Number of characters:"); ?>
                        </label>
                        <div class="col-sm-8">
                            <?php echo CHtml::numberField('abbreviatedtextto', '', array('min' => "1", 'step' => "1", 'class' => 'form-control')); ?>
                            <p class="help-block"><?php eT("Leave empty if you want the complete question text."); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo CHtml::submitButton(gT('Export results','unescaped'), array('class'=>'btn btn-default hidden')); ?>
            <?php echo CHtml::hiddenField('subaction','export'); ?>
            <form>
        </div>
    </div>
</div>
