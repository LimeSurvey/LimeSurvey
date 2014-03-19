<div id='presentation'><ul>


        <li><label for='format'><?php $clang->eT("Format:"); ?></label>
            <select id='format' name='format'>
                <option value='S'
                    <?php if ($esrow['format'] == "S" || !$esrow['format']) { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Question by Question"); ?>
                </option>
                <option value='G'
                    <?php if ($esrow['format'] == "G") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Group by Group"); ?>
                </option>
                <option value='A'
                    <?php if ($esrow['format'] == "A") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("All in one"); ?>
                </option>
            </select>
        </li>


        <li><label for='template'><?php $clang->eT("Template:"); ?></label>
            <select id='template' name='template'>
                <?php foreach (array_keys(getTemplateList()) as $tname) {

                        if (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','read') || hasTemplateManageRights(Yii::app()->session["loginID"], $tname) == 1 || $esrow['template']==htmlspecialchars($tname) ) { ?>
                        <option value='<?php echo $tname; ?>'
                            <?php if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) { ?>
                                selected='selected'
                                <?php   } elseif (!$esrow['template'] && $tname == Yii::app()->getConfig('defaulttemplate')) { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php echo $tname; ?></option>
                        <?php }
                } ?>
            </select>
        </li>

        <li><label for='preview'><?php $clang->eT("Template Preview:"); ?></label>
            <img alt='<?php $clang->eT("Template preview image"); ?>' name='preview' id='preview' src='<?php echo getTemplateURL($esrow['template']); ?>/preview.png' />
        </li>


        <li><label for='showwelcome'><?php $clang->eT("Show welcome screen?") ; ?></label>
            <select id='showwelcome' name='showwelcome'>
                <option value='Y'
                    <?php if (!$esrow['showwelcome'] || $esrow['showwelcome'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes") ; ?>
                </option>
                <option value='N'
                    <?php if ($esrow['showwelcome'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No") ; ?>
                </option>
            </select>
        </li>



        <li><label for='navigationdelay'><?php $clang->eT("Navigation delay (seconds):"); ?></label>
            <input type='text' value="<?php echo $esrow['navigationdelay']; ?>" name='navigationdelay' id='navigationdelay' size='12' maxlength='2' onkeypress="return goodchars(event,'0123456789')" />
        </li>


        <li><label for='allowprev'><?php $clang->eT("Show [<< Prev] button"); ?></label>
            <select id='allowprev' name='allowprev'>
                <option value='Y'
                    <?php if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?>
                </option>
            </select>
        </li>

        <li><label for='questionindex'><?php $clang->eT("Show question index / allow jumping"); ?></label>
			<?php
				$data = array(
					0 => gT('Disabled'),
					1 => gT('Incremental'),
					2 => gT('Full')
				);
				echo CHtml::dropDownList('questionindex', $esrow['questionindex'], $data, array(
					'id' => 'questionindex'
				));
			?>
        </li>


        <li><label for='nokeyboard'><?php $clang->eT("Keyboard-less operation"); ?></label>
            <select id='nokeyboard' name='nokeyboard'>
                <option value='Y'
                    <?php if (!isset($esrow['nokeyboard']) || !$esrow['nokeyboard'] || $esrow['nokeyboard'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['nokeyboard']) && $esrow['nokeyboard'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?>
                </option>
            </select>
        </li>

        <li><label for='showprogress'><?php $clang->eT("Show progress bar"); ?></label>
            <select id='showprogress' name='showprogress'>
                <option value='Y'
                    <?php if (!isset($esrow['showprogress']) || !$esrow['showprogress'] || $esrow['showprogress'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['showprogress']) && $esrow['showprogress'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select>
        </li>


        <li><label for='printanswers'><?php $clang->eT("Participants may print answers?"); ?></label>
            <select id='printanswers' name='printanswers'>
                <option value='Y'
                    <?php if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?>
                </option>
            </select>
        </li>


        <li><label for='publicstatistics'><?php $clang->eT("Public statistics?"); ?></label>
            <select id='publicstatistics' name='publicstatistics'>
                <option value='Y'
                    <?php if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?>
                </option>
            </select>
        </li>


        <li><label for='publicgraphs'><?php $clang->eT("Show graphs in public statistics?"); ?></label>
            <select id='publicgraphs' name='publicgraphs'>
                <option value='Y'
                    <?php if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select>
        </li>


        <li><label for='autoredirect'><?php $clang->eT("Automatically load URL when survey complete?"); ?></label>
            <select id='autoredirect' name='autoredirect'>
                <option value='Y'
                    <?php if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?>
                </option>
            </select>
        </li>

        <!-- $show_dis_pre =<li><label for="dis_showxquestions"><?php $clang->eT('Show "There are X questions in this survey"'); ?></label> <input type="hidden" name="showxquestions" id="" value="
        $show_dis_mid = " /> <input type="text" name="dis_showxquestions" id="dis_showxquestions" disabled="disabled" value="
        $show_dis_post = " size="70" /></li> -->
        <?php switch ($showxquestions) {
                case 'show': ?>
                <li><label for="dis_showxquestions"><?php $clang->eT('Show "There are X questions in this survey"'); ?></label> <input type="hidden" name="showxquestions" id="" value="Y" /> <input type="text" name="dis_showxquestions" id="dis_showxquestions" disabled="disabled" value="
                        <?php $clang->eT('Yes (Forced by the system administrator)'); ?>
                        " size="70" /></li>
                <?php   break;
                case 'hide': ?>
                <li><label for="dis_showxquestions"><?php $clang->eT('Show "There are X questions in this survey"'); ?></label> <input type="hidden" name="showxquestions" id="" value="N" /> <input type="text" name="dis_showxquestions" id="dis_showxquestions" disabled="disabled" value="
                        <?php $clang->eT('No (Forced by the system administrator)'); ?>
                        " size="70" /></li>
                <?php  break;
                case 'choose':
                default:
                    $sel_showxq = array( 'Y' => '' , 'N' => '' );
                    if (isset($esrow['showxquestions'])) {
                        $set_showxq = $esrow['showxquestions'];
                        $sel_showxq[$set_showxq] = ' selected="selected"';
                    }
                    if (empty($sel_showxq['Y']) && empty($sel_showxq['N'])) {
                        $sel_showxq['Y'] = ' selected="selected"';
                    }; ?>
                <li><label for="showxquestions"><?php $clang->eT('Show "There are X questions in this survey"'); ?></label>
                    <select id="showxquestions" name="showxquestions">
                        <option value="Y" <?php echo $sel_showxq['Y']; ?>><?php $clang->eT('Yes'); ?></option>
                        <option value="N" <?php echo $sel_showxq['N']; ?>><?php $clang->eT('No'); ?></option>
                    </select>
                </li>
                <?php unset($sel_showxq,$set_showxq);
                    break;
            }; ?>

        <!--            // Show {GROUPNAME} and/or {GROUPDESCRIPTION} block
        $show_dis_pre =<li><label for="dis_showgroupinfo"><?php $clang->eT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="
        $show_dis_mid = " /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value=" -->
        <?php switch ($showgroupinfo) {
                case 'both': ?>
                <li><label for="dis_showgroupinfo"><?php $clang->eT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="B" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
                        <?php $clang->eT('Show both (Forced by the system administrator)'); ?>

                        " size="70" /></li>
                <?php    break;
                case 'name': ?>
                <li><label for="dis_showgroupinfo"><?php $clang->eT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="N" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
                        <?php $clang->eT('Show group name only (Forced by the system administrator)'); ?>

                        " size="70" /></li>
                <?php     break;
                case 'description': ?>
                <li><label for="dis_showgroupinfo"><?php $clang->eT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="D" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
                        <?php $clang->eT('Show group description only (Forced by the system administrator)'); ?>

                        " size="70" /></li>
                <?php    break;
                case 'none': ?>
                <li><label for="dis_showgroupinfo"><?php $clang->eT('Show group name and/or group description'); ?></label> <input type="hidden" name="showgroupinfo" id="showgroupinfo" value="X" /> <input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="
                        <?php $clang->eT('Hide both (Forced by the system administrator)'); ?>

                        " size="70" /></li>

                <?php    break;
                case 'choose':
                default:
                    $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' );
                    if (isset($esrow['showgroupinfo'])) {
                        $set_showgri = $esrow['showgroupinfo'];
                        $sel_showgri[$set_showgri] = ' selected="selected"';
                    }
                    if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X'])) {
                        $sel_showgri['C'] = ' selected="selected"';
                    }; ?>
                <li><label for="showgroupinfo"><?php $clang->eT('Show group name and/or group description'); ?></label>
                    <select id="showgroupinfo" name="showgroupinfo">
                        <option value="B"<?php echo $sel_showgri['B']; ?>><?php $clang->eT('Show both'); ?></option>
                        <option value="N"<?php echo $sel_showgri['N']; ?>><?php $clang->eT('Show group name only'); ?></option>
                        <option value="D"<?php echo $sel_showgri['D']; ?>><?php $clang->eT('Show group description only'); ?></option>
                        <option value="X"<?php echo $sel_showgri['X']; ?>><?php $clang->eT('Hide both'); ?></option>
                    </select></li>
                <?php unset($sel_showgri,$set_showgri);
                    break;
            }; ?>


        <!--$show_dis_pre =<li><label for="dis_showqnumcode"><?php $clang->eT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="
        $show_dis_mid = " /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value=" -->
        <?php switch ($showqnumcode) {
                case 'none': ?>
                <li><label for="dis_showqnumcode"><?php $clang->eT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="X" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
                        <?php $clang->eT('Hide both (Forced by the system administrator)'); ?>
                        " size="70" /></li>
                <?php    break;
                case 'number': ?>
                <li><label for="dis_showqnumcode"><?php $clang->eT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="N" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
                        <?php $clang->eT('Show question number only (Forced by the system administrator)') ; ?>
                        " size="70" /></li>
                <?php    break;
                case 'code': ?>
                <li><label for="dis_showqnumcode"><?php $clang->eT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="C" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
                        <?php $clang->eT('Show question code only (Forced by the system administrator)'); ?>
                        " size="70" /></li>
                <?php    break;
                case 'both': ?>
                <li><label for="dis_showqnumcode"><?php $clang->eT('Show question number and/or code'); ?></label> <input type="hidden" name="showqnumcode" id="showqnumcode" value="B" /> <input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="
                        <?php $clang->eT('Show both (Forced by the system administrator)'); ?>
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
                <li><label for="showqnumcode"><?php $clang->eT('Show question number and/or code'); ?></label>
                    <select id="showqnumcode" name="showqnumcode">
                        <option value="B"<?php echo $sel_showqnc['B']; ?>><?php $clang->eT('Show both'); ?></option>
                        <option value="N"<?php echo $sel_showqnc['N']; ?>><?php $clang->eT('Show question number only'); ?></option>
                        <option value="C"<?php echo $sel_showqnc['C']; ?>><?php $clang->eT('Show question code only'); ?></option>
                        <option value="X"<?php echo $sel_showqnc['X']; ?>><?php $clang->eT('Hide both'); ?></option>
                    </select></li>
                <?php unset($sel_showqnc,$set_showqnc);
                    break;
            }; ?>



        <!--    $show_dis_pre =<li><label for="dis_shownoanswer"><?php $clang->eT('Show "No answer"'); ?></label> <input type="hidden" name="shownoanswer" id="shownoanswer" value="
        $show_dis_mid = " /> <input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value=" -->
        <?php switch ($shownoanswer) {
                case 0: ?>
                <li><label for="dis_shownoanswer"><?php $clang->eT('Show "No answer"'); ?></label> <input type="hidden" name="shownoanswer" id="shownoanswer" value="N" /> <input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="<?php $clang->eT('Off (Forced by the system administrator)'); ?>
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
                <li><label for="shownoanswer"><?php $clang->eT('Show "No answer"'); ?></label>
                    <select id="shownoanswer" name="shownoanswer">
                        <option value="Y"<?php echo $sel_showno['Y']; ?>><?php $clang->eT('Yes'); ?></option>
                        <option value="N"<?php echo $sel_showno['N']; ?>><?php $clang->eT('No'); ?></option>
                    </select></li>
                <?php  break;
                default: ?>
                <li><label for="dis_shownoanswer"><?php $clang->eT('Show "No answer"'); ?></label> <input type="hidden" name="shownoanswer" id="shownoanswer"
                        value="Y" /> <input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="<?php $clang->eT('On (Forced by the system administrator)'); ?>
                        " size="70" /></li>
                <?php    break;
            }; ?>


    </ul></div>
