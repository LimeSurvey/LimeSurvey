<script type='text/javascript'>
    var sEnterTitle = '<?php eT('Error: You have to enter a group title for each language.','js'); ?>';
</script>
<?php echo PrepareEditorScript(false, $this);?>
<div class='header ui-widget-header'><?php eT("Add question group"); ?></div>
<div id='tabs'><ul>
        <?php foreach ($grplangs as $grouplang)
            { ?>
            <li><a href="#<?php echo $grouplang; ?>"><?php echo getLanguageNameFromCode($grouplang,false);
                        if ($grouplang==$baselang) { ?>(<?php eT("Base language"); ?>) <?php } ?>
                </a></li>
            <?php }
            if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','import'))
            { ?>
            <li><a href="#import"><?php eT("Import question group"); ?></a></li>

            <?php } ?>
    </ul>

    <?php echo CHtml::form(array("admin/questiongroups/sa/insert/surveyid/{$surveyid}"), 'post', array('id'=>'newquestiongroup', 'name'=>'newquestiongroup', 'class'=>'form30')); ?>
        <?php
            foreach ($grplangs as $grouplang)
            { ?>
            <div id="<?php echo $grouplang; ?>">
                <ul>
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
                            <textarea cols='50' rows='1' id='grelevance' name='grelevance'></textarea>
                        </li>
                        <?php } ?>
                </ul>
                <p><input type='submit' value='<?php eT("Save question group"); ?>' />
            </div>
            <?php } ?>

    </form>

    <?php if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','import'))
        { ?>
        <div id="import">
            <?php echo CHtml::form(array("admin/questiongroups/sa/import"), 'post', array('id'=>'importgroup', 'name'=>'importgroup', 'class'=>'form30', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'.gT('Please select a file to import!','js').'");')); ?>
                <ul>
                    <li>
                        <label for='the_file'><?php eT("Select question group file (*.lsg):"); ?></label>
                        <input id='the_file' name="the_file" type="file" /></li>
                    <li><label for='translinksfields'><?php eT("Convert resource links?"); ?></label>
                        <input id='translinksfields' name="translinksfields" type="checkbox" checked="checked"/></li></ul>
                <p><input type='submit' value='<?php eT("Import question group"); ?>' />
                <input type='hidden' name='action' value='importgroup' />
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            </form>

        </div>
        <?php } ?>

    </div>