<div id='edit-question-body' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oQuestion'=>$oQuestion, 'active'=>gT("Edit default answer values") )); ?>
    <h3>
        <?php eT('Edit default answer values') ?>
    </h3>
    <div class="row">
        <div class="col-lg-8 content-right">
            <?php echo CHtml::form(array("admin/database/index"), 'post',array('class'=>'form-horizontal','id'=>'frmdefaultvalues','name'=>'frmdefaultvalues')); ?>

                    <ul class="nav nav-tabs">
                        <?php
                            foreach ($questlangs as $i=>$language)
                            {
                            ?>
                            <li role="presentation" <?php if($i==0){echo 'class="active"';}?> >
                                <a data-toggle="tab" href='#df_<?php echo $language ?>'><?php echo getLanguageNameFromCode($language, false) ?></a>
                            </li>
                            <?php
                            }
                        ?>
                    </ul>
                    <div class="tab-content">
                        <?php
                            foreach ($questlangs as $i => $language)
                            {
                            ?>
                            <div id='df_<?php echo $language ?>' class="tab-pane fade in <?php if($i==0){echo 'active';}?>">

                                    <?php
                                        if ($qtproperties[$questionrow['type']]['answerscales'] > 0)
                                        {
                                            for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['answerscales']; $scale_id++)
                                            {
                                                $opts = $langopts[$language][$questionrow['type']][$scale_id];
                                            ?>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"for='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'>
                                                    <?php
                                                        $qtproperties[$questionrow['type']]['answerscales'] > 1 ? printf(gT('Default answer for scale %s:'), $scale_id) : printf(gT('Default answer value:'), $scale_id) ?>
                                                </label>
                                            <div class="col-sm-10">

                                                <select class='form-control' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>' id='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'>

                                                    <option value=''<?php is_null($opts['defaultvalue']) ? ' selected="selected"' : '' ?>>
                                                        <?php eT('<No default value>') ?>
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
                                            </div></div>
                                            <?php
                                                if ($questionrow['other'] == 'Y')
                                                {
                                                ?>
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label"for='other_<?php echo "{$scale_id}_{$language}" ?>'>
                                                        <?php eT("Default value for option 'Other':")?>
                                                    </label>
                                            <div class="col-sm-10">
                                                    <input type='text' name='other_<?php echo "{$scale_id}_{$language}" ?>' value='<?php echo $langopts[$language][$questionrow['type']]['Ydefaultvalue'] ?>' id='other_<?php echo "{$scale_id}_{$language}" ?>'>
                                                </div></div>
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
                                                    <?php echo sprintf(gT('Default answer for scale %s:'), $scale_id) ?>
                                                </div>
                                                <?php
                                                }
                                            ?>

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
                                                        <div class="form-group">
                                                            <label class="col-sm-2 control-label"for='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                                <?php echo "{$aSubquestion['title']}: " . flattenText($aSubquestion['question']) ?>
                                                            </label>
                                            <div class="col-sm-10">
                                                            <select class='form-control' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'
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
                                                        </div></div>
                                                        <?php
                                                        }
                                                    }
                                                    if ($inputStyle == 'text')
                                                    {
                                                        foreach ($opts['sqresult'] as $aSubquestion)
                                                        {
                                                        ?>
                                                        <div class="form-group">
                                                            <label class="col-sm-2 control-label"for='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                                <?php echo "{$aSubquestion['title']}: " . flattenText($aSubquestion['question']) ?>
                                                            </label>
                                            <div class="col-sm-10">
                                                            <textarea cols='50' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'
                                                                id='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'><?php echo $aSubquestion['defaultvalue'] ?></textarea>
                                                        </div></div>
                                                        <?php
                                                        }
                                                    }
                                                ?>
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
                                             );
                                            $this->widget('application.views.admin.survey.Question.yesNo_defaultvalue_widget', array('widgetOptions'=>$widgetOptions));
                                            ?>
                                            <?php if ($questionrow['type'] != 'Y'): //temporary solution - until everything is move to widgets?>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"for='defaultanswerscale_<?php echo "0_{$language}_0" ?>'>
                                                <?php eT("Default value:")?>
                                            </label>
                                            <div class="col-sm-10">

                                            <textarea cols='50' name='defaultanswerscale_<?php echo "0_{$language}_0" ?>'
                                                id='defaultanswerscale_<?php echo "0_{$language}_0" ?>'><?php
                                                echo htmlspecialchars($langopts[$language][$questionrow['type']][0]); ?></textarea>
                                        </div></div>
                                        <?php endif;  //temporary solution?>
                                        <?php
                                        }

                                        if ($language == $baselang && count($questlangs) > 1)
                                        {
                                        ?>
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label"for='samedefault'>
                                                <?php eT('Use same default value across languages:') ?>
                                            </label>
                                            <div class="col-sm-8">
                                            <input type='checkbox' name='samedefault' id='samedefault'<?php $questionrow['same_default'] ? ' checked="checked"' : '' ?> />
                                        </div></div>
                                        <?php
                                        }
                                    ?>

                            </div>
                            <?php
                            }
                        ?>
                    </div>
                <input type='hidden' id='action' name='action' value='updatedefaultvalues' />
                <input type='hidden' id='sid' name='sid' value='<?php echo $surveyid ?>' />
                <input type='hidden' id='gid' name='gid' value='<?php echo $gid ?>' />
                <input type='hidden' id='qid' name='qid' value='<?php echo $qid ?>' />
                <p><input class="hidden" type='submit' value='<?php eT('Save') ?>'/></p>
                </form>
        </div>
    </div>
</div>
