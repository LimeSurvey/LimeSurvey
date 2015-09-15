<script type='text/javascript'>
    var sEnterTitle = '<?php eT('Error: You have to enter a group title for each language.','js'); ?>';
</script>
<?php echo PrepareEditorScript(false, $this); $active = 1;?>


<div class="side-body" id="edit-survey-text-element">
	<h3><?php eT("Add question group"); ?></h3>
	
	<div class="row">

    
    <ul class="nav nav-tabs" >
        <?php foreach ($grplangs as $grouplang): ?>
            <li role="presentation" class="<?php if($active){ echo 'active'; $active=0; }?>"> 
                <a data-toggle="tab" href="#<?php echo $grouplang; ?>">
                        <?php echo getLanguageNameFromCode($grouplang,false);
                        if ($grouplang==$baselang) { ?>(<?php eT("Base language"); ?>) <?php } ?>
                </a>
            </li>
        <?php endforeach; ?>

    </ul>
    
    <?php echo CHtml::form(array("admin/questiongroups/sa/insert/surveyid/{$surveyid}"), 'post', array('id'=>'newquestiongroup', 'name'=>'newquestiongroup', 'class'=>'form30')); ?>
    <div class="tab-content">
        <?php
            $active=1;
            foreach ($grplangs as $grouplang)
            { ?>
            <div id="<?php echo $grouplang; ?>" class="tab-pane fade in <?php if($active){ echo 'active'; $active=0; }?> ">
                <ul class="list-unstyled">
                    <li>
                        <label for='group_name_<?php echo $grouplang; ?>'><?php eT("Title:"); ?></label>
                        <input type='text' size='80' maxlength='100' class='group_title' name='group_name_<?php echo $grouplang; ?>' id='group_name_<?php echo $grouplang; ?>' /><span class='annotation'> <?php eT("Required"); ?></span></li>
                    <li><label for='description_<?php echo $grouplang; ?>'><?php eT("Description:"); ?></label>
                        <div class="htmleditor">
                            <textarea cols='80' rows='8' id='description_<?php echo $grouplang; ?>' name='description_<?php echo $grouplang; ?>'></textarea>
                            <?php echo getEditor("group-desc","description_".$grouplang, "[".gT("Description:", "js")."](".$grouplang.")",$surveyid,'','',$action); ?>
                        </div>
                    </li>
                    <?php if ($grouplang==$baselang){?>
                        <li><label for='randomization_group'><?php eT("Randomization group:"); ?></label><input type='text' size='20' maxlength='20' name='randomization_group' id='randomization_group' /></li>
                        <li>
                            <label for='grelevance'><?php eT("Relevance equation:"); ?></label>
                            <textarea cols='1' class="form-control" rows='1' id='grelevance' name='grelevance'></textarea>
                        </li>
                        <?php } ?>
                </ul>
                <p><input type='submit' class="hidden" value='<?php eT("Save question group"); ?>' />
            </div>
            <?php } ?>
        </div>
       </form>

			
</div>