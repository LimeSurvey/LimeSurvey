<?php
/**
 * This view generate the presentation tab inside global settings.
 *
 *
 */
?>
    <?php $shownoanswer=getGlobalSetting('shownoanswer');
        $sel_na = array( 0 => '' , 1 => '' , 2 => '');
        $sel_na[$shownoanswer] = ' selected="selected"'; ?>
    <div class="form-group">
            <label class="col-sm-6 control-label"  for='shownoanswer'><?php eT("Show 'no answer' option for non-mandatory questions:"); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id='shownoanswer' name='shownoanswer'>
            <option value="1" <?php echo $sel_na[1]; ?> ><?php eT('Yes'); ?></option>
            <option value="0" <?php echo $sel_na[0]; ?> ><?php eT('No'); ?></option>
            <option value="2" <?php echo $sel_na[2]; ?> ><?php eT('Survey admin can choose'); ?></option>
        </select>
        </div>
    </div>


    <?php $thisrepeatheadings=getGlobalSetting('repeatheadings'); ?>
    <div class="form-group">
            <label class="col-sm-6 control-label"  for='repeatheadings'><?php eT("Repeating headings in array questions every X subquestions:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  id='repeatheadings' name='repeatheadings' value='<?php echo $thisrepeatheadings; ?>' size='4' maxlength='4' />
        </div>
    </div>


    <?php
        // showxquestions
        $set_xq=getGlobalSetting('showxquestions');
        $sel_xq = array( 'hide' => '' , 'show' => '' , 'choose' => '');
        $sel_xq[$set_xq] = ' selected="selected"';
        if( empty($sel_xq['hide']) && empty($sel_xq['show']) && empty($sel_xq['choose']))
        {
            $sel_xq['choose'] = ' selected="selected"';
        };
    ?>
    <div class="form-group">
            <label class="col-sm-6 control-label"  for="showxquestions"><?php eT('Show "There are X questions in this survey":'); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id="showxquestions" name="showxquestions">
            <option value="show"<?php echo $sel_xq['show']; ?>><?php eT('Yes'); ?></option>
            <option value="hide"<?php echo $sel_xq['hide']; ?>><?php eT('No'); ?></option>
            <option value="choose"<?php echo $sel_xq['choose']; ?>><?php eT('Survey admin can choose'); ?></option>
        </select>
        </div>
    </div>

    <?php unset($set_xq,$sel_xq);
        $set_gri=getGlobalSetting('showgroupinfo');
        $sel_gri = array( 'both' => '' , 'choose' =>'' , 'description' => '' , 'name' => '' , 'none' => '' );
        $sel_gri[$set_gri] = ' selected="selected"';
        if( empty($sel_gri['both']) && empty($sel_gri['choose']) && empty($sel_gri['description']) && empty($sel_gri['name']) && empty($sel_gri['none']))
        {
            $sel_gri['choose'] = ' selected="selected"';
        }; ?>
    <div class="form-group">
            <label class="col-sm-6 control-label"  for="showgroupinfo"><?php eT('Show question group name and/or description:'); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id="showgroupinfo" name="showgroupinfo">
            <option value="both"<?php echo $sel_gri['both']; ?>><?php eT('Show both'); ?></option>
            <option value="name"<?php echo $sel_gri['name']; ?>><?php eT('Show group name only'); ?></option>
            <option value="description"<?php echo $sel_gri['description']; ?>><?php eT('Show group description only'); ?></option>
            <option value="none"<?php echo $sel_gri['none']; ?>><?php eT('Hide both'); ?></option>
            <option value="choose"<?php echo $sel_gri['choose']; ?>><?php eT('Survey admin can choose'); ?></option>
        </select>
        </div>
    </div>
            <?php
        unset($set_gri,$sel_gri);

        // showqnumcode
        $set_qnc=getGlobalSetting('showqnumcode');
        $sel_qnc = array( 'both' => '' , 'choose' =>'' , 'number' => '' , 'code' => '' , 'none' => '' );
        $sel_qnc[$set_qnc] = ' selected="selected"';
        if( empty($sel_qnc['both']) && empty($sel_qnc['choose']) && empty($sel_qnc['number']) && empty($sel_qnc['code']) && empty($sel_qnc['none']))
        {
            $sel_qnc['choose'] = ' selected="selected"';
        };
    ?>
    <div class="form-group">
            <label class="col-sm-6 control-label"  for="showqnumcode"><?php eT('Show question number and/or question code:'); ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id="showqnumcode" name="showqnumcode">
            <option value="both"<?php echo $sel_qnc['both']; ?>><?php eT('Show both'); ?></option>
            <option value="number"<?php echo $sel_qnc['number']; ?>><?php eT('Show question number only'); ?></option>
            <option value="code"<?php echo $sel_qnc['code']; ?>><?php eT('Show question code only'); ?></option>
            <option value="none"<?php echo $sel_qnc['none']; ?>><?php eT('Hide both'); ?></option>
            <option value="choose"<?php echo $sel_qnc['choose']; ?>><?php eT('Survey admin can choose'); ?></option>
        </select>
        </div>
    </div>
            <?php
        unset($set_qnc,$sel_qnc);
    ?>
    <div class="form-group">
            <label class="col-sm-6 control-label"  for='pdffontsize'><?php eT("Font size of PDFs:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='5' id='pdffontsize' name='pdffontsize' value="<?php echo htmlspecialchars(getGlobalSetting('pdffontsize')); ?>" />

        </div>
    </div>

    <div class="form-group">
            <label class="col-sm-6 control-label"  for='pdfshowheader'><?php eT("Show header in answers export PDFs:") ; ?></label>
            <div class="col-sm-6">
                <select class="form-control"  id='pdfshowheader' name='pdfshowheader'>
            <option value='Y'
                <?php if (getGlobalSetting('pdfshowheader') == "Y") { ?>
                    selected='selected'
                    <?php } ?>
                ><?php eT("Yes") ; ?>
            </option>
            <option value='N'
                <?php if (getGlobalSetting('pdfshowheader') != "Y") { ?>
                    selected='selected'
                    <?php } ?>
                ><?php eT("No") ; ?>
            </option>
        </select>

        </div>
    </div>

    <div class="form-group">
            <label class="col-sm-6 control-label"  for='pdflogowidth'><?php eT("Width of PDF header logo:"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' size='5' id='pdflogowidth' name='pdflogowidth' value="<?php echo htmlspecialchars(getGlobalSetting('pdflogowidth')); ?>" />

        </div>
    </div>

    <div class="form-group">
            <label class="col-sm-6 control-label"  for='pdfheadertitle'><?php eT("PDF header title (if empty, site name will be used):"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' id='pdfheadertitle' size='50' maxlength='256' name='pdfheadertitle' value="<?php echo htmlspecialchars(getGlobalSetting('pdfheadertitle')); ?>" />

        </div>
    </div>

    <div class="form-group">
            <label class="col-sm-6 control-label"  for='pdfheaderstring'><?php eT("PDF header string (if empty, survey name will be used):"); ?></label>
            <div class="col-sm-6">
                <input class="form-control"  type='text' id='pdfheaderstring' size='50' maxlength='256' name='pdfheaderstring' value="<?php echo htmlspecialchars(getGlobalSetting('pdfheaderstring')); ?>" />

        </div>
    </div>
<?php
    $bPdfQuestionFill=getGlobalSetting('bPdfQuestionFill');
    $selPdfQuestionFill = array( 0 => '' , 1 => '');
    $selPdfQuestionFill[$bPdfQuestionFill] = ' selected="selected"';
	?>
    <div class="form-group">
	    <label class="col-sm-6 control-label"  for='bPdfQuestionFill'><?php eT("Add gray background to questions in PDF:"); ?></label>
		   <div class="col-sm-6">
               <select class="form-control"  id='pdfQuestionFill' name='bPdfQuestionFill'>
			       <option value="1" <?php echo $selPdfQuestionFill[1]; ?> ><?php eT('Yes'); ?></option>
				   <option value="0" <?php echo $selPdfQuestionFill[0]; ?> ><?php eT('No'); ?></option>
			  </select>
		</div>
    </div>

<?php
    $bPdfQuestionBold=getGlobalSetting('bPdfQuestionBold');
    $selPdfQuestionBold = array( 0 => '' , 1 => '');
    $selPdfQuestionBold[$bPdfQuestionBold] = ' selected="selected"';
	?>
    <div class="form-group">
		  <label class="col-sm-6 control-label"  for='bPdfQuestionBold'><?php eT("PDF questions in bold:"); ?></label>
		   <div class="col-sm-6">
                <select class="form-control"  id='bPdfQuestionBold' name='bPdfQuestionBold'>
				    <option value="1" <?php echo $selPdfQuestionBold[1]; ?> ><?php eT('Yes'); ?></option>
					<option value="0" <?php echo $selPdfQuestionBold[0]; ?> ><?php eT('No'); ?></option>
				</select>
			</div>
    </div>

<?php
    $bPdfQuestionBorder=getGlobalSetting('bPdfQuestionBorder');
    $selPdfQuestionBorder = array( 0 => '' , 1 => '');
    $selPdfQuestionBorder[$bPdfQuestionBorder] = ' selected="selected"';
	?>
    <div class="form-group">
		  <label class="col-sm-6 control-label"  for='bPdfQuestionBorder'><?php eT("Borders around questions in PDF:"); ?></label>
		   <div class="col-sm-6">
                <select class="form-control"  id='bPdfQuestionBorder' name='bPdfQuestionBorder'>
				    <option value="1" <?php echo $selPdfQuestionBorder[1]; ?> ><?php eT('Yes'); ?></option>
					<option value="0" <?php echo $selPdfQuestionBorder[0]; ?> ><?php eT('No'); ?></option>
				</select>
			</div>
    </div>

<?php
    $bPdfResponseBorder=getGlobalSetting('bPdfResponseBorder');
    $selPdfResponseBorder = array( 0 => '' , 1 => '');
    $selPdfResponseBorder[$bPdfResponseBorder] = ' selected="selected"';
	?>
    <div class="form-group">
	    <label class="col-sm-6 control-label"  for='bPdfResponseBorder'><?php eT("Borders around responses in PDF:"); ?></label>
		    <div class="col-sm-6">
			    <select class="form-control"  id='bPdfResponseBorder' name='bPdfResponseBorder'>
				    <option value="1" <?php echo $selPdfResponseBorder[1]; ?> ><?php eT('Yes'); ?></option>
					<option value="0" <?php echo $selPdfResponseBorder[0]; ?> ><?php eT('No'); ?></option>
				</select>
			</div>
    </div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>

