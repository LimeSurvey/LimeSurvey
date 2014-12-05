<div class='header ui-widget-header'><?php $clang->eT('Edit default answer values') ?></div>
<?php echo CHtml::form(array("admin/database/index"), 'post',array('class'=>'form30','id'=>'frmdefaultvalues','name'=>'frmdefaultvalues')); ?>
    <div id="tabs">
        <ul>
            <?php
                foreach ($questlangs as $language)
                {
                ?>
                <li><a href='#df_<?php echo $language ?>'><?php echo getLanguageNameFromCode($language, false) ?></a></li>
                <?php
                }
            ?>
        </ul>
        <?php
            foreach ($questlangs as $language)
            {
            ?>
            <div id='df_<?php echo $language ?>'>
                <ul>
                    <?php
                        if ($qtproperties[$questionrow['type']]['answerscales'] > 0)
                        {
                            for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['answerscales']; $scale_id++)
                            {
                                $opts = $langopts[$language][$questionrow['type']][$scale_id];
                            ?>
                            <li>
                                <label for='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'>
                                    <?php
                                        $qtproperties[$questionrow['type']]['answerscales'] > 1 ? printf($clang->gT('Default answer for scale %s:'), $scale_id) : printf($clang->gT('Default answer value:'), $scale_id) ?>
                                </label>

                                <select name='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>' id='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'>

                                    <option value=''<?php is_null($opts['defaultvalue']) ? ' selected="selected"' : '' ?>>
                                        <?php $clang->eT('<No default value>') ?>
                                    </option>
                                    <?php
                                        foreach ($opts['answers'] as $answer)
                                        {
                                            $answer = $answer->attributes;
                                        ?>                          <option<?php if ($answer['code'] == $opts['defaultvalue']){ ?> selected="selected" <?php } ?> value="<?php echo $answer['code'] ?>"><?php echo $answer['answer'] ?></option>
                                        <?php
                                        }
                                    ?>
                                </select>
                            </li>
                            <?php
                                if ($questionrow['other'] == 'Y')
                                {
                                ?>
                                <li>
                                    <label for='other_<?php echo "{$scale_id}_{$language}" ?>'>
                                        <?php $clang->eT("Default value for option 'Other':")?>
                                    </label>
                                    <input type='text' name='other_<?php echo "{$scale_id}_{$language}" ?>' value='<?php echo $langopts[$language][$questionrow['type']]['Ydefaultvalue'] ?>' id='other_<?php echo "{$scale_id}_{$language}" ?>'>
                                </li>
                                <?php
                                }
                            }
                        }

                        // If there are subquestions and no answerscales
                        if ($qtproperties[$questionrow['type']]['answerscales'] == 0 && $qtproperties[$questionrow['type']]['subquestions'] > 0)
                        {
                            for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['subquestions']; $scale_id++)
                            {
                                $opts = $langopts[$language][$questionrow['type']][$scale_id];

                                if ($qtproperties[$questionrow['type']]['subquestions'] > 1)
                                {
                                ?>
                                <div class='header ui-widget-header'>
                                    <?php echo sprintf($clang->gT('Default answer for scale %s:'), $scale_id) ?>
                                </div>
                                <?php
                                }
                            ?>
                            <ul>
                                <?php
                                    switch($questionrow['type'])
                                    {
                                        case 'L':
                                        case 'M':
                                        case 'O':
                                        case 'P':
                                        case '!':
                                            $inputStyle='enum';
                                            break;
                                        case 'K':
                                        case 'Q':
                                            $inputStyle='text';
                                            break;
                                    }
                                    if ($inputStyle == 'enum')
                                    {
                                        foreach ($opts['sqresult'] as $aSubquestion)
                                        {
                                        ?>
                                        <li>
                                            <label for='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                <?php echo "{$aSubquestion['title']}: " . flattenText($aSubquestion['question']) ?>
                                            </label>
                                            <select name='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'
                                                id='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                <?php
                                                    foreach ($aSubquestion['options'] as $value => $label)
                                                    {
                                                    ?>
                                                    <option value="<?php echo $value ?>"<?php echo ($value == $aSubquestion['defaultvalue'] ? ' selected="selected"' : ''); ?>><?php echo $label ?></option>
                                                    <?php
                                                    }
                                                ?>
                                            </select>
                                        </li>
                                        <?php
                                        }
                                    }
                                    if ($inputStyle == 'text')
                                    {
                                        foreach ($opts['sqresult'] as $aSubquestion)
                                        {
                                        ?>
                                        <li>
                                            <label for='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                <?php echo "{$aSubquestion['title']}: " . flattenText($aSubquestion['question']) ?>
                                            </label>
                                            <textarea cols='50' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'
                                                id='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'><?php echo $aSubquestion['defaultvalue'] ?></textarea>
                                        </li>
                                        <?php
                                        }
                                    }
                                ?>
                            </ul>
                            <?php
                            }
                        }
                        if ($qtproperties[$questionrow['type']]['answerscales']==0 && $qtproperties[$questionrow['type']]['subquestions']==0)
                        {
                            /*
                            case 'D':
                            case 'N':
                            case 'S':
                            case 'T':
                            case 'U':*
                            */
                        ?>
                            <?php
                            /**
                             * Call default value widget for yes/no question type
                             * This is fast insert rewrite of this view follows
                             */
                            $widgetOptions = array(
                                'language' =>$language ,
                                'questionrow' => $questionrow,
                                'qtproperties' => $qtproperties,
                                'langopts' => $langopts,
                                'clang' => $clang
                             );
                            $this->widget('application.views.admin.survey.Question.yesNo_defaultvalue_widget', array('widgetOptions'=>$widgetOptions));
                            ?>
                            <?php if ($questionrow['type'] != 'Y'): //temporary solution - until everything is move to widgets?>
                        <li>
                            <label for='defaultanswerscale_<?php echo "0_{$language}_0" ?>'>
                                <?php $clang->eT("Default value:")?>
                            </label>

                            <textarea cols='50' name='defaultanswerscale_<?php echo "0_{$language}_0" ?>'
                                id='defaultanswerscale_<?php echo "0_{$language}_0" ?>'><?php
                                echo htmlspecialchars($langopts[$language][$questionrow['type']][0]); ?></textarea>
                        </li>
                        <?php endif;  //temporary solution?>
                        <?php
                        }

                        if ($language == $baselang && count($questlangs) > 1)
                        {
                        echo '<li>';
                        echo CHtml::label($clang->gT('Use same default value across languages:'), 'samedefault'); // use gT - eT is not working, causes a wrong replacement in label
                        echo CHtml::checkBox('samedefault', $questionrow['same_default']);
                        echo '</li>';
                        ?>

                        <?php
                        }
                    ?>
                </ul>
            </div>
            <?php
            }
        ?>
    </div>
    <input type='hidden' id='action' name='action' value='updatedefaultvalues' />
    <input type='hidden' id='sid' name='sid' value='<?php echo $surveyid ?>' />
    <input type='hidden' id='gid' name='gid' value='<?php echo $gid ?>' />
    <input type='hidden' id='qid' name='qid' value='<?php echo $qid ?>' />
    <p><input type='submit' value='<?php $clang->eT('Save') ?>'/></p>
    </form>
