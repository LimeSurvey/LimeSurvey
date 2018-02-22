<?php
/**
 * Add a group to survey
 * @var AdminController $this
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('addQuestionGroup');

?>

<!-- addGroup -->
<script type='text/javascript'>
    var sEnterTitle = '<?php eT('Error: You have to enter a group title for each language.','js'); ?>';
</script>

<?php echo PrepareEditorScript(false, $this); $active = 1;?>
<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="pagetitle h3"><?php eT("Add question group"); ?></div>
    <div class="row">

        <!-- Tabs -->
        <ul class="nav nav-tabs" >
            <?php foreach ($grplangs as $grouplang): ?>
                <li role="presentation" class="<?php if($active){ echo 'active'; $active=0; }?>">
                    <a role="tab" data-toggle="tab" href="#<?php echo $grouplang; ?>">
                            <?php echo getLanguageNameFromCode($grouplang,false);
                            if ($grouplang==$baselang) { ?> (<?php eT("Base language"); ?>) <?php } ?>
                    </a>
                </li>
            <?php endforeach; ?>

        </ul>

        <!-- form -->
        <?php echo CHtml::form(array("admin/questiongroups/sa/insert/surveyid/{$surveyid}"), 'post', array('id'=>'newquestiongroup', 'name'=>'newquestiongroup', 'class'=>'form30 ')); ?>

            <!-- tab content -->
            <div class="tab-content">

                <?php $active=1; foreach ($grplangs as $grouplang): ?>

                    <!-- Lang Content -->
                    <div id="<?php echo $grouplang; ?>" class="tab-pane fade in <?php if($active){ echo 'active'; $active=0; }?> ">
                        <div>

                            <!-- Title -->
                            <div class="form-group">
                                <label class="control-label " for='group_name_<?php echo $grouplang; ?>'><?php eT("Title:"); ?></label>
                                <div class="">
                                    <input class="form-control group_title" type='text' size='80' maxlength='100' name='group_name_<?php echo $grouplang; ?>' id='group_name_<?php echo $grouplang; ?>' /></li>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label class="control-label " for='description_<?php echo $grouplang; ?>'><?php eT("Description:"); ?></label>
                                <div class=" input-group">
                                    <?php echo CHtml::textArea("description_{$grouplang}","",array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"description_{$grouplang}")); ?>
                                    <?php echo getEditor("group-desc","description_".$grouplang, "[".gT("Description:", "js")."](".$grouplang.")",$surveyid,'','',$action); ?>
                                </div>
                            </div>

                            <?php if ($grouplang==$baselang){?>
                            <!-- Base Lang -->

                                <!-- Randomization group -->
                                <div class="form-group">
                                    <label class="control-label " for='randomization_group'><?php eT("Randomization group:"); ?></label>
                                    <div class="">
                                        <input class="form-control" type='text' size='20' maxlength='20' name='randomization_group' id='randomization_group' />
                                    </div>
                                </div>

                                <!-- Relevance equation -->
                                <div class="form-group">
                                    <label class="control-label " for='grelevance'><?php eT("Relevance equation:"); ?></label>
                                    <div class="">
                                        <textarea cols='1' class="form-control" rows='1' id='grelevance' name='grelevance'></textarea>
                                    </div>
                                </div>
                                <?php } ?>
                        </div>

                        <!-- Save question group -->
                        <p>
                            <input type='submit' class="hidden" value='<?php eT("Save question group"); ?>' />
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
       </form>
    </div>
</div>
