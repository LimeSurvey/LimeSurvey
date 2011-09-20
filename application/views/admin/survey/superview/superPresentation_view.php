<div id='presentation'><ul>


<li><label for='format'><?php echo $clang->gT("Format:"); ?></label>
        <select id='format' name='format'>
        <option value='S'
        <?php if ($esrow['format'] == "S" || !$esrow['format']) { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Question by Question"); ?>
        </option>
        <option value='G'
        <?php if ($esrow['format'] == "G") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Group by Group"); ?>
        </option>
        <option value='A'
        <?php if ($esrow['format'] == "A") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("All in one"); ?>
        </option>
        </select>
</li>


<li><label for='template'><?php echo $clang->gT("Template:"); ?></label>
            <select id='template' name='template'>
            <?php foreach (array_keys(gettemplatelist()) as $tname) {

                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1 || hasTemplateManageRights($this->session->userdata("loginID"), $tname) == 1) { ?>
                    <option value='<?php echo $tname; ?>'
            <?php if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) { ?>
                     selected='selected'
              <?php   } elseif (!$esrow['template'] && $tname == "default") { ?>
                     selected='selected'
                <?php } ?>
                    ><?php echo $tname; ?></option>
               <?php }
            } ?>
            </select>
</li>

<li><label for='preview'><?php echo $clang->gT("Template Preview:"); ?></label>
        <img alt='<?php echo $clang->gT("Template preview image"); ?>' id='preview' src='<?php echo sGetTemplateURL($esrow['template']); ?>/preview.png' />
</li>


<li><label for='showwelcome'><?php echo $clang->gT("Show welcome screen?") ; ?></label>
                <select id='showwelcome' name='showwelcome'>
                <option value='Y'
                    <?php if (!$esrow['showwelcome'] || $esrow['showwelcome'] == "Y") { ?>
                         selected='selected'
                    <?php } ?>
                    ><?php echo $clang->gT("Yes") ; ?>
                    </option>
                    <option value='N'
                    <?php if ($esrow['showwelcome'] == "N") { ?>
                         selected='selected'
                    <?php } ?>
                    ><?php echo $clang->gT("No") ; ?>
                    </option>
                </select>
</li>



<li><label for='navigationdelay'><?php echo $clang->gT("Navigation delay (seconds):"); ?></label>
        <input type='text' value="<?php echo $esrow['navigationdelay']; ?>" name='navigationdelay' id='navigationdelay' size='12' maxlength='2' onkeypress="return goodchars(event,'0123456789')" />
</li>


<li><label for='allowprev'><?php echo $clang->gT("Show [<< Prev] button"); ?></label>
        <select id='allowprev' name='allowprev'>
        <option value='Y'
        <?php if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") { ?>
            selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("No"); ?>
        </option>
        </select>
        </li>

<li><label for='allowjumps'><?php echo $clang->gT("Show question index / allow jumping"); ?></label>
        <select id='allowjumps' name='allowjumps'>
        <option value='Y'
        <?php if (!isset($esrow['allowjumps']) || !$esrow['allowjumps'] || $esrow['allowjumps'] == "Y") { ?>

            selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (isset($esrow['allowjumps']) && $esrow['allowjumps'] == "N") { ?>
         selected='selected'
         <?php } ?>
        ><?php echo $clang->gT("No"); ?>
        </option>
        </select>
</li>


<li><label for='nokeyboard'><?php echo $clang->gT("Keyboard-less operation"); ?></label>
        <select id='nokeyboard' name='nokeyboard'>
        <option value='Y'
        <?php if (!isset($esrow['nokeyboard']) || !$esrow['nokeyboard'] || $esrow['nokeyboard'] == "Y") { ?>
        selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (isset($esrow['nokeyboard']) && $esrow['nokeyboard'] == "N") { ?>
        selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("No"); ?>
        </option>
        </select>
</li>

<li><label for='showprogress'><?php echo $clang->gT("Show progress bar"); ?></label>
        <select id='showprogress' name='showprogress'>
        <option value='Y'
        <?php if (!isset($esrow['showprogress']) || !$esrow['showprogress'] || $esrow['showprogress'] == "Y") { ?>
             selected='selected'
       <?php } ?>
	   ><?php echo $clang->gT("Yes"); ?>
       </option>
       <option value='N'
	   <?php if (isset($esrow['showprogress']) && $esrow['showprogress'] == "N") { ?>
             selected='selected'
        <?php } ?>
	   ><?php echo $clang->gT("No"); ?></option>
        </select>
</li>


<li><label for='printanswers'><?php echo $clang->gT("Participants may print answers?"); ?></label>
        <select id='printanswers' name='printanswers'>
        <option value='Y'
        <?php if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("No"); ?>
        </option>
        </select>
</li>


<li><label for='publicstatistics'><?php echo $clang->gT("Public statistics?"); ?></label>
        <select id='publicstatistics' name='publicstatistics'>
        <option value='Y'
        <?php if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("No"); ?>
        </option>
        </select>
</li>


<li><label for='publicgraphs'><?php echo $clang->gT("Show graphs in public statistics?"); ?></label>
        <select id='publicgraphs' name='publicgraphs'>
        <option value='Y'
        <?php if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("No"); ?></option>
        </select>
</li>


<li><label for='autoredirect'><?php echo $clang->gT("Automatically load URL when survey complete?"); ?></label>
        <select id='autoredirect' name='autoredirect'>
        <option value='Y'
        <?php if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("Yes"); ?>
        </option>
        <option value='N'
        <?php if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") { ?>
             selected='selected'
        <?php } ?>
        ><?php echo $clang->gT("No"); ?>
        </option>
        </select>
</li>

<!-- $show_dis_pre =<li><label for="dis_showXquestions"><?php echo $clang->gT('Show "There are X questions in this survey"'); ?></label> <input type="hidden" name="showXquestions" id="" value="
	    $show_dis_mid = " /> <input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="
	    $show_dis_post = " size="70" /></li> -->
        <?php switch ($showXquestions) {
		case 'show': ?>
		   <li><label for="dis_showXquestions"><?php echo $clang->gT('Show "There are X questions in this survey"'); ?></label> <input type="hidden" name="showXquestions" id="" value="Y" /> <input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="
            <?php echo $clang->gT('Yes (Forced by the system administrator)'); ?>
            " size="70" /></li>
		 <?php   break;
		case 'hide': ?>
		   <li><label for="dis_showXquestions"><?php echo $clang->gT('Show "There are X questions in this survey"'); ?></label> <input type="hidden" name="showXquestions" id="" value="N" /> <input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="
            <?php echo $clang->gT('No (Forced by the system administrator)'); ?>
            " size="70" /></li>
		  <?php  break;
	    	case 'choose':
		default:
		    $sel_showxq = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['showXquestions'])) {
		    	$set_showxq = $esrow['showXquestions'];
			$sel_showxq[$set_showxq] = ' selected="selected"';
		    }
                if (empty($sel_showxq['Y']) && empty($sel_showxq['N'])) {
		    	$sel_showxq['Y'] = ' selected="selected"';
		    }; ?>
<li><label for="showXquestions"><?php echo $clang->gT('Show "There are X questions in this survey"'); ?></label>
		    <select id="showXquestions" name="showXquestions">
		    <option value="Y" <?php echo $sel_showxq['Y'].'>'.$clang->gT('Yes'); ?></option>
		    <option value="N" <?php echo $sel_showxq['N'].'>'.$clang->gT('No'); ?></option>
		    </select>
</li>
		    <?php unset($sel_showxq,$set_showxq);
		    break;
	    }; ?>

<!--            // Show {GROUPNAME} and/or {GROUPDESCRIPTION} block
	    $show_dis_pre =<li><label for="dis_showgroupinfo"><?php echo $clang->gT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="
            $show_dis_mid = " /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value=" -->
        <?php switch ($showgroupinfo) {
		case 'both': ?>
		   <li><label for="dis_showgroupinfo"><?php echo $clang->gT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="B" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
            <?php echo $clang->gT('Show both (Forced by the system administrator)'); ?>

            " size="70" /></li>
		<?php    break;
		case 'name': ?>
		   <li><label for="dis_showgroupinfo"><?php echo $clang->gT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="N" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
            <?php echo $clang->gT('Show group name only (Forced by the system administrator)'); ?>

            " size="70" /></li>
		<?php     break;
		case 'description': ?>
		   <li><label for="dis_showgroupinfo"><?php echo $clang->gT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="D" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
            <?php echo $clang->gT('Show group description only (Forced by the system administrator)'); ?>

            " size="70" /></li>
		<?php    break;
		case 'none': ?>
		   <li><label for="dis_showgroupinfo"><?php echo $clang->gT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="X" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
            <?php echo $clang->gT('Hide both (Forced by the system administrator)'); ?>

            " size="70" /></li>
		    break;
	    <?php	case 'choose':
		default:
		    $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showgroupinfo'])) {
		    	$set_showgri = $esrow['showgroupinfo'];
                $sel_showgri[$set_showgri] = ' selected="selected"';
		    }
                if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X'])) {
		    	$sel_showgri['C'] = ' selected="selected"';
		    }; ?>
		   <li><label for="showgroupinfo"><?php echo $clang->gT('Show group name and/or group description'); ?></label>
		    <select id="showgroupinfo" name="showgroupinfo">
		    <option value="B"<?php echo $sel_showgri['B'].'>'.$clang->gT('Show both'); ?></option>
		    <option value="N"<?php echo $sel_showgri['N'].'>'.$clang->gT('Show group name only'); ?></option>
		    <option value="D"<?php echo $sel_showgri['D'].'>'.$clang->gT('Show group description only'); ?></option>
		    <option value="X"<?php echo $sel_showgri['X'].'>'.$clang->gT('Hide both'); ?></option>
		    </select></li>
		    <?php unset($sel_showgri,$set_showgri);
		    break;
	    }; ?>


	    <!--$show_dis_pre =<li><label for="dis_showqnumcode"><?php echo $clang->gT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="
            $show_dis_mid = " /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value=" -->
        <?php switch ($showqnumcode) {
		case 'none': ?>
           <li><label for="dis_showqnumcode"><?php echo $clang->gT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="X" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
            <?php echo $clang->gT('Hide both (Forced by the system administrator)'); ?>
            " size="70" /></li>
		<?php    break;
		case 'number': ?>
		   <li><label for="dis_showqnumcode"><?php echo $clang->gT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="N" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
            <?php echo $clang->gT('Show question number only (Forced by the system administrator)') ; ?>
            " size="70" /></li>
		<?php    break;
		case 'code': ?>
		   <li><label for="dis_showqnumcode"><?php echo $clang->gT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="C" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
            <?php echo $clang->gT('Show question code only (Forced by the system administrator)'); ?>
            " size="70" /></li>
		<?php    break;
		case 'both': ?>
		   <li><label for="dis_showqnumcode"><?php echo $clang->gT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="B" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
            <?php echo $clang->gT('Show both (Forced by the system administrator)'); ?>
            " size="70" /></li>
		<?php    break;
	    	case 'choose':
		default:
		    $sel_showqnc = array( 'B' => '' , 'C' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showqnumcode'])) {
		    	$set_showqnc = $esrow['showqnumcode'];
			$sel_showqnc[$set_showqnc] = ' selected="selected"';
		    }
                if (empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N']) && empty($sel_showqnc['X'])) {
		    	$sel_showqnc['X'] = ' selected="selected"';
		    }; ?>
		    <li><label for="showqnumcode"><?php echo $clang->gT('Show question number and/or code'); ?></label>
		    <select id="showqnumcode" name="showqnumcode">
		    <option value="B"<?php echo $sel_showqnc['B'].'>'.$clang->gT('Show both'); ?></option>
		    <option value="N"<?php echo $sel_showqnc['N'].'>'.$clang->gT('Show question number only'); ?></option>
		    <option value="C"<?php echo $sel_showqnc['C'].'>'.$clang->gT('Show question code only'); ?></option>
		    <option value="X"<?php echo $sel_showqnc['X'].'>'.$clang->gT('Hide both'); ?></option>
		    </select></li>
		    <?php unset($sel_showqnc,$set_showqnc);
		    break;
	    }; ?>



	<!--    $show_dis_pre =<li><label for="dis_shownoanswer"><?php echo $clang->gT('Show "No answer"'); ?></label> <input type="hidden" name="shownoanswer" id="shownoanswer" value="
            $show_dis_mid = " /> <input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value=" -->
        <?php switch ($shownoanswer) {
	    	case 0: ?>
		         <li><label for="dis_shownoanswer"><?php echo $clang->gT('Show "No answer"'); ?></label> <input type="hidden" name="shownoanswer" id="shownoanswer" value="N" /> <input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="<?php echo $clang->gT('Off (Forced by the system administrator)'); ?>
                  " size="70" /></li>
		  <?php  break;
	        case 2:
		    $sel_showno = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['shownoanswer'])) {
		    	$set_showno = $esrow['shownoanswer'];
			$sel_showno[$set_showno] = ' selected="selected"';
		    };
                if (empty($sel_showno)) {
		    	$sel_showno['Y'] = ' selected="selected"';
		    }; ?>
	    	   <li><label for="shownoanswer"><?php echo $clang->gT('Show "No answer"'); ?></label>
		    <select id="shownoanswer" name="shownoanswer">
		    <option value="Y"<?php echo $sel_showno['Y'].'>'.$clang->gT('Yes'); ?></option>
		    <option value="N"<?php echo $sel_showno['N'].'>'.$clang->gT('No'); ?></option>
		    </select></li>
		  <?php  break;
		default: ?>
		   <li><label for="dis_shownoanswer"><?php echo $clang->gT('Show "No answer"'); ?></label> <input type="hidden" name="shownoanswer" id="shownoanswer"
           value="Y" /> <input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="<?php echo $clang->gT('On (Forced by the system administrator)'); ?>
            " size="70" /></li>
		<?php    break;
	    }; ?>


            </ul></div>