<?php
/**
 * This view displays the tabs for the question creation
 */
?>
<?php PrepareEditorScript(true, $this); ?>
<?php if($eqrow['title']) {$sPattern="^([a-zA-Z][a-zA-Z0-9]*|{$eqrow['title']})$";}else{$sPattern="^[a-zA-Z][a-zA-Z0-9]*$";} ?>

<!-- New question language tabs -->
<ul class="nav nav-tabs" style="margin-right: 8px;" >
    <li role="presentation" class="active">
        <a data-toggle="tab" href="#<?php echo $eqrow['language']; ?>">
            <?php echo getLanguageNameFromCode($eqrow['language'],false); ?>
            (<?php eT("Base language"); ?>)
        </a>
    </li>
    <?php foreach  ($addlanguages as $addlanguage):?>
        <li role="presentation">
            <a data-toggle="tab" href="#<?php echo $addlanguage; ?>">
                <?php echo getLanguageNameFromCode($addlanguage,false); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Editors for each languages -->
<div class="tab-content">

    <!-- Base Language tab-pane -->
    <div id="<?php echo $eqrow['language']; ?>" class="tab-pane fade in active">

        <!-- Question Code -->
        <div class="form-group">
                <label class="col-sm-3 control-label"  for='title'><?php eT("Code:"); ?></label>
                <div class="col-sm-9">
                    <?php echo CHtml::textField("title",$eqrow['title'],array('class'=>'form-control','size'=>"20",'maxlength'=>'20','pattern'=>$sPattern,"autofocus"=>"autofocus",'id'=>"title")); ?>
                    <span class='text-warning'><?php  eT("Required"); ?> </span>
                </div>
        </div>

        <!-- Question Text -->
        <div class="form-group">
                <label class="col-sm-3 control-label" for='question_<?php echo $eqrow['language']; ?>'><?php eT("Question:"); ?></label>
                <div class="col-sm-9">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("question_{$eqrow['language']}",$eqrow['question'],array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$eqrow['language']}")); ?>
                    <?php echo getEditor("question-text","question_".$eqrow['language'], "[".gT("Question:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                </div>
                </div>
        </div>

        <!-- Question Help -->
        <div class="form-group">
                <label class="col-sm-3 control-label" for='help_<?php echo $eqrow['language']; ?>'><?php eT("Help:"); ?></label>
                <div class="col-sm-9">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("help_{$eqrow['language']}",$eqrow['help'],array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$eqrow['language']}")); ?>
                    <?php echo getEditor("question-help","help_".$eqrow['language'], "[".gT("Help:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                </div>
                </div>
        </div>
    </div>

    <!-- Other languages tab-panes -->
    <?php if (!$adding):?>
        <?php foreach ($aqresult as $aqrow): ?>
            <?php $aqrow = $aqrow->attributes;?>
                <div id="<?php echo $aqrow['language']; ?>" class="tab-pane fade">
                    <div class="form-group">
                            <label class="col-sm-3 control-label" for='question_<?php echo $aqrow['language']; ?>'><?php eT("Question:"); ?></label>
                            <div class="col-sm-9">
                            <div class="htmleditor input-group">
                                <?php echo CHtml::textArea("question_{$aqrow['language']}",$aqrow['question'],array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$aqrow['language']}")); ?>
                                <?php echo getEditor("question-text","question_".$aqrow['language'], "[".gT("Question:", "js")."](".$aqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                            </div>
                            </div>
                    </div>

                    <div class="form-group">
                            <label class="col-sm-3 control-label" for='help_<?php echo $aqrow['language']; ?>'><?php eT("Help:"); ?></label>
                            <div class="col-sm-9">
                            <div class="htmleditor input-group">
                                <?php echo CHtml::textArea("help_{$aqrow['language']}",$aqrow['help'],array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$aqrow['language']}")); ?>
                                <?php echo getEditor("question-help","help_".$aqrow['language'], "[".gT("Help:", "js")."](".$aqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                            </div>
                            </div>
                        </div>
                </div>
        <?php endforeach;?>
    <?php else:?>

        <?php foreach  ($addlanguages as $addlanguage): ?>
            <div id="<?php echo $addlanguage; ?>"  class="tab-pane fade">

                <div class="form-group">
                        <label class="col-sm-3 control-label"  for='question_<?php echo $addlanguage; ?>'><?php eT("Question:");?></label>
                        <div class="col-sm-9">
                        <div class="htmleditor input-group">
                            <?php echo CHtml::textArea("question_{$addlanguage}","",array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$addlanguage}")); ?>
                            <?php echo getEditor("question-text","question_".$addlanguage, "[".gT("Question:", "js")."](".$addlanguage.")",$surveyid,$gid,$qid,$action); ?>
                        </div>
                        </div>
                </div>

                <div class="form-group">
                        <label class="col-sm-3 control-label" for='help_<?php echo $addlanguage; ?>'><?php eT("Help:"); ?></label>
                        <div class="col-sm-9">
                        <div class="htmleditor input-group">
                            <?php echo CHtml::textArea("help_{$addlanguage}","",array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$addlanguage}")); ?>
                            <?php echo getEditor("question-help","help_".$addlanguage, "[".gT("Help:", "js")."](".$addlanguage.")",$surveyid,$gid,$qid,$action); ?>
                        </div>
                        </div>
                </div>

            </div>
        <?php endforeach; ?>

    <?php endif;?>
</div>

<div id='questionactioncopy' class='extra-action'>
    <button type='submit' class="btn btn-primary saveandreturn hidden"  name="redirection" value="edit"><?php eT("Save") ?> </button>
    <input type='submit' value='<?php eT("Save and close"); ?>'  class="btn btn-default hidden"/>
</div>
