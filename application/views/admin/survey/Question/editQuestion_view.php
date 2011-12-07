<script type='text/javascript'>
    var attr_url = "<?php echo $this->createUrl('/admin/question/sa/ajaxquestionattributes'); ?>";
    var imgurl = '<?php echo Yii::app()->getConfig('imageurl'); ?>';
</script>
<?php PrepareEditorScript(true, $this); ?>

<script type='text/javascript'><?php echo $qTypeOutput; ?></script>

<div class='header ui-widget-header'>
    <?php if ($adding) { ?>
        <?php $clang->eT("Add a new question"); ?>
        <?php } elseif ($copying) { ?>
        <?php $clang->eT("Copy question"); ?>
        <?php } else { ?>
        <?php $clang->eT("Edit question"); ?>
        <?php } ?>
</div>

<div id='tabs'>
    <ul>

        <li><a href="#<?php echo $eqrow['language']; ?>"><?php echo getLanguageNameFromCode($eqrow['language'],false); ?>
                (<?php $clang->eT("Base language"); ?>)
            </a></li>
        <?php
            $addlanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
            foreach  ($addlanguages as $addlanguage)
            { ?>
            <li><a href="#<?php echo $addlanguage; ?>"><?php echo getLanguageNameFromCode($addlanguage,false); ?>
                </a></li>
            <?php }
        ?>
    </ul>
    <form name='frmeditquestion' id='frmeditquestion' class='form30' action='<?php echo $this->createUrl("admin/database/index"); ?>' method='post' onsubmit="return isEmpty(document.getElementById('title'), '<?php $clang->eT("Error: You have to enter a question code.",'js'); ?>');">
        <div id="<?php echo $eqrow['language']; ?>">
            <?php $eqrow  = array_map('htmlspecialchars', $eqrow); ?>
            <ul><li>
                    <label for='title'> <?php $clang->eT("Code:"); ?></label><input type='text' size='20' maxlength='20' id='title' name='title' value="<?php echo $eqrow['title']; ?>" /> <?php if ($copying) $clang->eT("Note: You MUST enter a new question code!"); ?>
                </li><li>
                    <label for='question_<?php echo $eqrow['language']; ?>'><?php $clang->eT("Question:"); ?></label>
                    <textarea cols='50' rows='4' id='question_<?php echo $eqrow['language']; ?>' name='question_<?php echo $eqrow['language']; ?>'><?php echo $eqrow['question']; ?></textarea>
                    <?php echo getEditor("question-text","question_".$eqrow['language'], "[".$clang->gT("Question:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                </li><li>
                    <label for='help_<?php echo $eqrow['language']; ?>'><?php $clang->eT("Help:"); ?></label>
                    <textarea cols='50' rows='4' id='help_<?php echo $eqrow['language']; ?>' name='help_<?php echo $eqrow['language']; ?>'><?php echo $eqrow['help']; ?></textarea>
                    <?php echo getEditor("question-help","help_".$eqrow['language'], "[".$clang->gT("Help:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                </li>
            </ul>
        </div>


        <?php if (!$adding)
            {

                foreach ($aqresult->readAll() as $aqrow)
                { ?>

                <div id="<?php echo $aqrow['language']; ?>">
                    <ul>
                        <?php $aqrow  = array_map('htmlspecialchars', $aqrow); ?>
                        <li>
                            <label for='question_<?php echo $aqrow['language']; ?>'><?php $clang->eT("Question:"); ?></label>
                            <textarea cols='50' rows='4' id='question_<?php echo $aqrow['language']; ?>' name='question_<?php echo $aqrow['language']; ?>'><?php echo $aqrow['question']; ?></textarea>
                            <?php echo getEditor("question-text","question_".$aqrow['language'], "[".$clang->gT("Question:", "js")."](".$aqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                        </li><li>
                            <label for='help_<?php echo $aqrow['language']; ?>'><?php $clang->eT("Help:"); ?></label>
                            <textarea cols='50' rows='4' id='help_<?php echo $aqrow['language']; ?>' name='help_<?php echo $aqrow['language']; ?>'><?php echo $aqrow['help']; ?></textarea>
                            <?php echo getEditor("question-help","help_".$aqrow['language'], "[".$clang->gT("Help:", "js")."](".$aqrow['language'].")",$surveyid,$gid,$qid,$action); ?>
                        </li>
                    </ul>
                </div>
                <?php }
            }
            else
            {
                $addlanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
                foreach  ($addlanguages as $addlanguage)
                { ?>
                <div id="<?php echo $addlanguage; ?>">
                    <ul>
                        <li>
                            <label for='question_<?php echo $addlanguage; ?>'><?php $clang->eT("Question:"); ?></label>
                            <textarea cols='50' rows='4' id='question_<?php echo $addlanguage; ?>' name='question_<?php echo $addlanguage; ?>'></textarea>
                            <?php echo getEditor("question-text","question_".$addlanguage, "[".$clang->gT("Question:", "js")."](".$addlanguage.")",$surveyid,$gid,$qid,$action); ?>
                        </li><li>
                            <label for='help_<?php echo $addlanguage; ?>'><?php $clang->eT("Help:"); ?></label>
                            <textarea cols='50' rows='4' id='help_<?php echo $addlanguage; ?>' name='help_<?php echo $addlanguage; ?>'></textarea>
                            <?php echo getEditor("question-help","help_".$addlanguage, "[".$clang->gT("Help:", "js")."](".$addlanguage.")",$surveyid,$gid,$qid,$action); ?>
                        </li></ul>
                </div>
                <?php }
        } ?>
        <div id='questionbottom'>
            <ul>
                <li><label for='question_type'><?php $clang->eT("Question Type:"); ?></label>
                    <?php if ($activated != "Y")
                        { ?>

                        <select id='question_type' style='margin-bottom:5px' name='type' class='<?php echo $selectormodeclass; ?>'>
                            <?php echo getqtypelist($eqrow['type'],'group'); ?>

                        </select>
                        <?php }
                        else
                        {

                            $qtypelist=getqtypelist('','array');
                            echo "{$qtypelist[$eqrow['type']]['description']} - ".$clang->gT("Cannot be changed (survey is active)"); ?>
                        <input type='hidden' name='type' id='question_type' value='<?php echo $eqrow['type']; ?>' />
                        <?php } ?>

                </li>



                <?php if ($activated != "Y")
                    { ?>
                    <li>
                        <label for='gid'><?php $clang->eT("Question group:"); ?></label>
                        <select name='gid' id='gid'>

                            <?php echo getgrouplist3($eqrow['gid'],$surveyid); ?>
                        </select></li>
                    <?php }
                    else
                    { ?>
                    <li>
                        <label><?php $clang->eT("Question group:"); ?></label>
                        <?php echo $eqrow['group_name']." - ".$clang->gT("Cannot be changed (survey is active)"); ?>
                        <input type='hidden' name='gid' value='<?php echo $eqrow['gid']; ?>' />
                    </li>
                    <?php } ?>
                <li id='OtherSelection'>
                    <label><?php $clang->eT("Option 'Other':"); ?></label>

                    <?php if ($activated != "Y")
                        { ?>
                        <label for='OY'><?php $clang->eT("Yes"); ?></label><input id='OY' type='radio' class='radiobtn' name='other' value='Y'
                            <?php if ($eqrow['other'] == "Y") { ?>
                                checked
                                <?php } ?>
                            />&nbsp;&nbsp;
                        <label for='ON'><?php $clang->eT("No"); ?></label><input id='ON' type='radio' class='radiobtn' name='other' value='N'
                            <?php if ($eqrow['other'] == "N" || $eqrow['other'] == "" ) { ?>
                                checked='checked'
                                <?php } ?>
                            />
                        <?php }
                        else
                        {
                            echo "[{$eqrow['other']}] - ".$clang->gT("Cannot be changed (survey is active)"); ?>
                        <input type='hidden' name='other' value="<?php echo $eqrow['other']; ?>" />
                        <?php } ?>
                </li>

                <li id='MandatorySelection'>
                    <label><?php $clang->eT("Mandatory:"); ?></label>
                    <label for='MY'><?php $clang->eT("Yes"); ?></label><input id='MY' type='radio' class='radiobtn' name='mandatory' value='Y'
                        <?php if ($eqrow['mandatory'] == "Y") { ?>
                            checked='checked'
                            <?php } ?>
                        />&nbsp;&nbsp;
                    <label for='MN'><?php $clang->eT("No"); ?></label><input id='MN' type='radio' class='radiobtn' name='mandatory' value='N'
                        <?php if ($eqrow['mandatory'] != "Y") { ?>
                            checked='checked'
                            <?php } ?>
                        />
                </li>
                <li>
                    <label for='relevance'><?php $clang->eT("Relevance equation:"); ?></label>
                    <textarea cols='50' rows='1' id='relevance' name='relevance'><?php echo $eqrow['relevance']; ?></textarea>
                </li>

                <li id='Validation'>
                    <label for='preg'><?php $clang->eT("Validation:"); ?></label>
                    <input type='text' id='preg' name='preg' size='50' value="<?php echo $eqrow['preg']; ?>" />
                </li>


                <?php if ($adding) {
                        if ($oqresult->getRowCount()) { ?>

                        <li>
                            <label for='questionposition'><?php $clang->eT("Position:"); ?></label>
                            <select name='questionposition' id='questionposition'>
                                <option value=''><?php $clang->eT("At end"); ?></option>
                                <option value='0'><?php $clang->eT("At beginning"); ?></option>
                                <?php foreach ($oqresult->readAll() as $oq)
                                    { ?>
                                    <?php $question_order_plus_one = $oq['question_order']+1; ?>
                                    <option value='<?php echo $question_order_plus_one; ?>'><?php $clang->eT("After"); ?>: <?php echo $oq['title']; ?></option>
                                    <?php } ?>
                            </select>
                        </li>
                        <?php }
                        else
                        { ?>
                        <input type='hidden' name='questionposition' value='' />
                        <?php }
                } elseif ($copying) { ?>

					<li>
						<label for='copysubquestions'><?php $clang->eT("Copy subquestions?"); ?></label>
						<input type='checkbox' class='checkboxbtn' checked='checked' id='copysubquestions' name='copysubquestions' value='Y' />
					</li>
					<li>
						<label for='copyanswers'><?php $clang->eT("Copy answer options?"); ?></label>
						<input type='checkbox' class='checkboxbtn' checked='checked' id='copyanswers' name='copyanswers' value='Y' />
					</li>
					<li>
						<label for='copyattributes'><?php $clang->eT("Copy advanced settings?"); ?></label>
						<input type='checkbox' class='checkboxbtn' checked='checked' id='copyattributes' name='copyattributes' value='Y' />
					</li>

				<?php } ?>

            </ul>

			<?php if (!$copying) { ?>
				<p><a id="showadvancedattributes"><?php $clang->eT("Show advanced settings"); ?></a><a id="hideadvancedattributes" style="display:none;"><?php $clang->eT("Hide advanced settings"); ?></a></p>
				<div id="advancedquestionsettingswrapper" style="display:none;">
					<div class="loader"><?php $clang->eT("Loading..."); ?></div>
					<div id="advancedquestionsettings"></div>
				</div>
			<?php } ?>

                <?php if ($adding)
                    { ?>
                    <input type='hidden' name='action' value='insertquestion' />
                    <input type='hidden' name='gid' value='<?php echo $eqrow['gid']; ?>' />
					<p><input type='submit' value='<?php $clang->eT("Add question"); ?>' />
                    <?php }
                    elseif ($copying)
                    { ?>
                    <input type='hidden' name='action' value='copyquestion' />
                    <input type='hidden' name='gid' value='<?php echo $eqrow['gid']; ?>' />
                    <input type='hidden' id='oldqid' name='oldqid' value='<?php echo $qid; ?>' />
					<p><input type='submit' value='<?php $clang->eT("Copy question"); ?>' />
                    <?php }
                    else
                    { ?>
                    <input type='hidden' name='action' value='updatequestion' />
                    <input type='hidden' id='qid' name='qid' value='<?php echo $qid; ?>' />
					<p><input type='submit' value='<?php $clang->eT("Update question"); ?>' />
                    <?php } ?>
                <input type='hidden' id='sid' name='sid' value='<?php echo $surveyid; ?>' /></p>
        </div></form></div>



<?php if ($adding)
    {


        if (bHasSurveyPermission($surveyid,'surveycontent','import'))
        { ?>
        <br /><div class='header ui-widget-header'><?php $clang->eT("...or import a question"); ?></div>
        <form enctype='multipart/form-data' id='importquestion' name='importquestion' action='<?php echo $this->createUrl('admin/question/sa/import'); ?>' method='post' onsubmit='return validatefilename(this,"<?php $clang->eT('Please select a file to import!','js'); ?>");'>
            <ul>
                <li>
                    <label for='the_file'><?php $clang->eT("Select LimeSurvey question file (*.lsq/*.csv)"); ?>:</label>
                    <input name='the_file' id='the_file' type="file" size="50" />
                </li>
                <li>
                    <label for='translinksfields'><?php $clang->eT("Convert resource links?"); ?></label>
                    <input name='translinksfields' id='translinksfields' type='checkbox' checked='checked'/>
                </li>
            </ul>
            <p>
            <input type='submit' value='<?php $clang->eT("Import Question"); ?>' />
            <input type='hidden' name='action' value='importquestion' />
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
        </form>

        <?php } ?>

    <script type='text/javascript'>
        <!--
        document.getElementById('title').focus();
        //-->
    </script>

    <?php } ?>