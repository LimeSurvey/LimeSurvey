<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Export a VV survey file");?></h3>
        <div class="row">
            <div class="col-sm-6 content-right">

                <?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id'=>'vvexport', 'class'=>'form-horizontal'));?>

                <div class="panel panel-primary" id="pannel-1" style="opacity: 1; top: 0px;">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php eT("Export survey:");?>
                        </h4>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="surveyid" class="col-sm-2 control-label">
                                <?php eT("Survey id:");?>
                            </label>
                            <div class="col-sm-4">
                                <?php echo CHtml::textField('surveyid', $surveyid, array('size'=>10, 'readonly'=>'readonly', 'class'=>'form-control')); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="completionstate" class="col-sm-2 control-label">
                                <?php eT("Export:");?>
                            </label>
                            <div class="col-sm-4">
                                <?php  echo CHtml::dropDownList('completionstate', $selectincansstate, array(
                                        'complete' => gT("Completed responses only",'unescaped'),
                                        'all' => gT("All responses",'unescaped'),
                                        'incomplete' => gT("Incomplete responses only",'unescaped'),
                                        ), array('class'=>'form-control')); ?>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel panel-primary" id="pannel-1" style="opacity: 1; top: 0px;">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php eT("Format:");?>
                        </h4>
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
                                <?php  echo CHtml::dropDownList('vvversion', $vvversionseleted, array(
                                    '2' => gT("Last VV version",'unescaped'),
                                    '1' => gT("Old VV version",'unescaped'),
                                    ), array('class'=>'form-control'));; ?>

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
