<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Export a VV survey file");?></h3>
        <div class="row">
            <div class="col-sm-6 content-right">

                <?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id'=>'vvexport', 'class'=>''));?>

                <div class="panel panel-primary" id="panel-1" style="opacity: 1; top: 0px;">
                    <div class="panel-heading">
                        <div class="panel-title h4">
                            <?php eT("Export survey");?>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="surveyid" class="col-sm-2 control-label">
                                <?php eT("Survey ID:");?>
                            </label>
                            <div class="col-sm-4">
                                <?php echo CHtml::textField('surveyid', $surveyid, array('size'=>10, 'readonly'=>'readonly', 'class'=>'form-control')); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="completionstate" class="col-sm-2 control-label">
                                <?php eT("Export:");?>
                            </label>
                            <div class="col-sm-8">
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default">
                                        <input
                                            name="completionstate"
                                            value="complete"
                                            type="radio"
                                            id="completionstate-complete"
                                        />
                                        <?php eT("Completed responses only");?>
                                    </label>

                                    <label class="btn btn-default active">
                                        <input
                                            name="completionstate"
                                            value="all"
                                            type="radio"
                                            checked='checked'
                                            id="completionstate-all"
                                            autofocus="true"
                                        />
                                        <?php eT("All responses");?>
                                    </label>

                                    <label class="btn btn-default">
                                        <input
                                            name="completionstate"
                                            value="incomplete"
                                            type="radio"
                                            id="completionstate-incomplete"
                                        />
                                        <?php eT("Incomplete responses only");?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-primary" id="panel-1" style="opacity: 1; top: 0px;">
                    <div class="panel-heading">
                        <div class="panel-title h4">
                            <?php eT("Format");?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="extension" class="col-sm-2 control-label" data-toggle="tooltip" data-placement="right" title='<?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'");?>'>
                                <?php eT("File extension:");?>
                            </label>
                            <div class="col-sm-4">
                                <?php echo CHtml::textField('extension', 'csv',array('size'=>3, 'class'=>'form-control')); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="vvversion" class="col-sm-2 control-label"  data-toggle="tooltip" data-placement="right" title='<?php eT("If you want to import survey on old installation or if your survey have problem: use old version (automatically selected if some code are duplicated).");?>' >
                                <?php eT("VV export version:");?>
                            </label>
                            <div class="col-sm-4">
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default <?php echo ($vvversionseleted==2 ? "active" : "");?>">
                                        <input
                                            name="vvversion"
                                            value="2"
                                            type="radio"
                                            id="vvversion-last"
                                            <?php echo ($vvversionseleted==2 ? "checked='checked'" : ""); ?>
                                        />
                                        <?php eT("Last VV version");?>
                                    </label>

                                    <label class="btn btn-default <?php echo ($vvversionseleted==1 ? "active" : "");?>">
                                        <input
                                            name="vvversion"
                                            value="1"
                                            type="radio"
                                            id="vvversion-old"
                                            <?php echo ($vvversionseleted==1 ? "checked='checked'" : ""); ?>
                                        />
                                        <?php eT("Old VV version");?>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
    <p>
        <?php echo CHtml::submitButton(gT('Export results','unescaped'), array('class'=>'btn btn-default hidden')); ?>
        <?php echo CHtml::hiddenField('subaction','export'); ?>
    </p>
<form>
</div></div></div>
