<?php
/**
 * This view generate the presentation tab inside global settings.
 *
 *
 */
?>
<ul>
    <?php $shownoanswer=getGlobalSetting('shownoanswer');
        $sel_na = array( 0 => '' , 1 => '' , 2 => '');
        $sel_na[$shownoanswer] = ' selected="selected"'; ?>
    <li><label for='shownoanswer'><?php eT("Show 'no answer' option for non-mandatory questions:"); ?></label>
        <select id='shownoanswer' name='shownoanswer'>
            <option value="1" <?php echo $sel_na[1]; ?> ><?php eT('Yes'); ?></option>
            <option value="0" <?php echo $sel_na[0]; ?> ><?php eT('No'); ?></option>
            <option value="2" <?php echo $sel_na[2]; ?> ><?php eT('Survey admin can choose'); ?></option>
        </select></li>

    <?php $thisrepeatheadings=getGlobalSetting('repeatheadings'); ?>
    <li><label for='repeatheadings'><?php eT("Repeating headings in array questions every X subquestions:"); ?></label>
        <input type='text' id='repeatheadings' name='repeatheadings' value='<?php echo $thisrepeatheadings; ?>' size='4' maxlength='4' /></li>

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
    <li><label for="showxquestions"><?php eT('Show "There are X questions in this survey"'); ?></label>
        <select id="showxquestions" name="showxquestions">
            <option value="show"<?php echo $sel_xq['show']; ?>><?php eT('Yes'); ?></option>
            <option value="hide"<?php echo $sel_xq['hide']; ?>><?php eT('No'); ?></option>
            <option value="choose"<?php echo $sel_xq['choose']; ?>><?php eT('Survey admin can choose'); ?></option>
        </select></li>
    <?php unset($set_xq,$sel_xq);
        $set_gri=getGlobalSetting('showgroupinfo');
        $sel_gri = array( 'both' => '' , 'choose' =>'' , 'description' => '' , 'name' => '' , 'none' => '' );
        $sel_gri[$set_gri] = ' selected="selected"';
        if( empty($sel_gri['both']) && empty($sel_gri['choose']) && empty($sel_gri['description']) && empty($sel_gri['name']) && empty($sel_gri['none']))
        {
            $sel_gri['choose'] = ' selected="selected"';
        }; ?>
    <li><label for="showgroupinfo"><?php eT('Show question group name and/or description'); ?></label>
        <select id="showgroupinfo" name="showgroupinfo">
            <option value="both"<?php echo $sel_gri['both']; ?>><?php eT('Show both'); ?></option>
            <option value="name"<?php echo $sel_gri['name']; ?>><?php eT('Show group name only'); ?></option>
            <option value="description"<?php echo $sel_gri['description']; ?>><?php eT('Show group description only'); ?></option>
            <option value="none"<?php echo $sel_gri['none']; ?>><?php eT('Hide both'); ?></option>
            <option value="choose"<?php echo $sel_gri['choose']; ?>><?php eT('Survey admin can choose'); ?></option>
        </select></li><?php
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
    <li><label for="showqnumcode"><?php eT('Show question number and/or question code'); ?></label>
        <select id="showqnumcode" name="showqnumcode">
            <option value="both"<?php echo $sel_qnc['both']; ?>><?php eT('Show both'); ?></option>
            <option value="number"<?php echo $sel_qnc['number']; ?>><?php eT('Show question number only'); ?></option>
            <option value="code"<?php echo $sel_qnc['code']; ?>><?php eT('Show question code only'); ?></option>
            <option value="none"<?php echo $sel_qnc['none']; ?>><?php eT('Hide both'); ?></option>
            <option value="choose"<?php echo $sel_qnc['choose']; ?>><?php eT('Survey admin can choose'); ?></option>
        </select></li><?php
        unset($set_qnc,$sel_qnc);
    ?>
    <li><label for='pdffontsize'><?php eT("Font size of PDFs"); ?></label>
        <input type='text' size='5' id='pdffontsize' name='pdffontsize' value="<?php echo htmlspecialchars(getGlobalSetting('pdffontsize')); ?>" />
    </li>
    <li><label for='pdfshowheader'><?php eT("Show header in answers export PDFs?") ; ?></label>
        <select id='pdfshowheader' name='pdfshowheader'>
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
    </li>
    <li><label for='pdflogowidth'><?php eT("Width of PDF header logo"); ?></label>
        <input type='text' size='5' id='pdflogowidth' name='pdflogowidth' value="<?php echo htmlspecialchars(getGlobalSetting('pdflogowidth')); ?>" />
    </li>
    <li><label for='pdfheadertitle'><?php eT("PDF header title (if empty, site name will be used)"); ?></label>
        <input type='text' id='pdfheadertitle' size='50' maxlength='256' name='pdfheadertitle' value="<?php echo htmlspecialchars(getGlobalSetting('pdfheadertitle')); ?>" />
    </li>
    <li><label for='pdfheaderstring'><?php eT("PDF header string (if empty, survey name will be used)"); ?></label>
        <input type='text' id='pdfheaderstring' size='50' maxlength='256' name='pdfheaderstring' value="<?php echo htmlspecialchars(getGlobalSetting('pdfheaderstring')); ?>" />
    </li>
</ul>

<p><br/><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php eT("Save settings"); ?>' /><br /></p>
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>

