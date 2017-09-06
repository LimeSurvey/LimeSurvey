<?php

/**
 * Array dual-scale, no dropdown

 * @var $answerwidth
 */

?>

<div class="no-more-tables no-more-tables-array-dual">
    <table class="no-more-tables table-dual-scale table question subquestion-list questions-list">
        <col class="col-answers" style='width: <?php echo $answerwidth; ?>%;' />
        <colgroup class="col-responses group-1">

            <?php foreach ($labelans0 as $ld): ?>
                <col style='width: <?php echo $cellwidth; ?>%;' />
            <?php endforeach; ?>

        </colgroup>

        <?php if (count($labelans1) > 1): ?>

            <col class="separator" <?php echo $separatorwidth; ?>/>
            <colgroup class="col-responses group-2">

                <?php foreach ($labelans1 as $ld): ?>
                    <col style="width: <?php $cellwidth; ?>%" />
                <?php endforeach; ?>

            </colgroup>

        <?php endif; ?>

        <?php if ($shownoanswer || $rightexists): ?>
            <col class="separator rigth_separator" <?php echo $rigthwidth; ?> />
        <?php endif; ?>

        <?php if ($shownoanswer): ?>
            <col class="col-no-answer"  style='width: <?php echo $cellwidth; ?>%;' />
        <?php endif; ?>

        <thead>
            <?php if ($leftheader != '' || $rightheader !=''): ?>
                <tr class="array1 groups header_row">
                    <th class="header_answer_text">&nbsp;</th>
                    <th colspan="<?php echo count($labelans0); ?>" class="dsheader text-center"><?php echo $leftheader; ?></th>

                    <?php if (count($labelans1) > 0): ?>
                        <td class="header_separator">&nbsp;</td>  <!-- // Separator -->
                        <th colspan="<?php echo count($labelans1); ?>" class="dsheader text-center"><?php echo $rightheader; ?></th>
                    <?php endif; ?>

                    <?php if($shownoanswer || $rightexists): ?>
                        <td class="header_separator <?php echo $rigthclass; ?>">&nbsp;</td>
                        <?php if($shownoanswer): ?>
                            <th class="header_no_answer">&nbsp;</th>
                        <?php endif; ?>
                    <?php endif; ?>
                </tr>
            <?php endif; ?>

            <!-- Render header -->
            <?php echo Yii::app()->getController()->renderPartial(
                    '/survey/questions/arrays/dualscale/answer_header',
                    array(
                        'labelans0'    => $labelans0,
                        'labelans1'    => $labelans1,
                        'shownoanswer' => $shownoanswer,
                        'rightexists'  => $rightexists,
                        'class'        => ''
                    ),
                    true
                );
            ?>

        </thead>

        <tbody>

            <!-- Loop all sub-questions -->
            <?php foreach ($aSubQuestions as $ansrow): ?>

                <!-- Check for repeat headings -->
                <?php if ($ansrow['repeatheadings']): ?>
                    <!-- Close body and open another one -->
                    </tbody>
                    <tbody>
                        <!-- Render repeated header -->
                        <?php echo Yii::app()->getController()->renderPartial(
                                '/survey/questions/arrays/dualscale/answer_header',
                                array(
                                    'labelans0'    => $labelans0,
                                    'labelans1'    => $labelans1,
                                    'shownoanswer' => $shownoanswer,
                                    'rightexists'  => $rightexists,
                                    'class'        => 'hidden-xs repeat headings'
                                ),
                                true
                            );
                        ?>
                <?php endif; ?>

                <!-- tr -->
                <?php echo $ansrow['htmltbody2']; ?>

                <th class="answertext">

                    <?php echo $ansrow['hiddenfield']; ?>

                    <?php if ($ansrow['showmandatoryviolation']): ?>
                        <div class="label label-danger">
                            <span class="fa fa-exclamation-circle" aria-hidden="true"></span>
                            <?php echo $ansrow['answertext']; ?>
                        </div>
                    <?php else: ?>
                        <?php echo $ansrow['answertext']; ?>
                    <?php endif; ?>


                    <input type="hidden" disabled="disabled" name="java<?php echo $ansrow['myfid0']; ?>"
                        id="java<?php echo $ansrow['myfid0']; ?>"
                        value="<?php echo $ansrow['sessionfname0']; ?>"
                    />

                    <?php if (count($labelans1) > 0): ?>
                        <input type="hidden" disabled="disabled" name="java<?php echo $ansrow['myfid1']; ?>"
                            id="java<?php echo $ansrow['myfid1']; ?>"
                            value="<?php echo $ansrow['sessionfname1']; ?>"
                        />
                    <?php endif; ?>

                </th>

                <!-- First label set -->
                <?php foreach ($labelcode0 as $j => $ld): ?>
                    <?php if ($j === 0 && $leftheader != ''): ?>
                        <td class='visible-xs'><em><?php echo $leftheader; ?></em></td>
                    <?php endif; ?>
                    <td class="answer_cell_1_<?php echo $ld; ?> answer-item <?php echo $answertypeclass; ?>-item text-center radio">
                        <input
                            class="radio"
                            type="radio"
                            name="<?php echo $ansrow['myfname0']; ?>"
                            value="<?php echo $ld; ?>"
                            id="answer<?php echo $ansrow['myfid0']; ?>-<?php echo $ld; ?>"
                            <?php echo $labelcode0_checked[$ansrow['title']][$ld]; ?>
                        />
                        <label for="answer<?php echo $ansrow['myfid0']; ?>-<?php echo $ld; ?>">
                            <span class="visible-xs-block label-text"><?php echo $labelans0[$j];?></span>
                        </label>
                    </td>
                <?php endforeach; ?>

                <?php if (count($labelans1) > 0):  // if second label set is used ?>
                    <td class="dual_scale_separator information-item">
                        <?php if ($shownoanswer): // No answer for accessibility and no javascript (but visible-xs-block visible-xs-block even with no js: need reworking) ?>
                            <label for='answer<?php echo $ansrow['myfid0']; ?>-'>
                                <input
                                    class='radio jshide read'
                                    type='radio'
                                    name='<?php echo $ansrow['myfname0']; ?>'
                                    value=''
                                    id='answer<?php echo $ansrow['myfid0']; ?>-'
                                    <?php echo $myfname0_notset; ?>
                                />
                            </label>
                        <?php endif; ?>
                    </td>

                    <!-- Second label set -->
                    <?php foreach ($labelcode1 as $k => $ld): ?>
                        <?php if ($k === 0 && $rightheader != ''): ?>
                            <td class='visible-xs'><em><?php echo $rightheader; ?></em></td>
                        <?php endif; ?>
                        <td class="answer_cell_2_<?php echo $ld; ?> answer-item radio-item text-center radio">
                            <input
                                class="radio"
                                type="radio"
                                name="<?php echo $ansrow['myfname1']; ?>"
                                value="<?php echo $ld; ?>"
                                id="answer<?php echo $ansrow['myfid1']; ?>-<?php echo $ld; ?>"
                                <?php echo $labelcode1_checked[$ansrow['title']][$ld]; ?>
                            />
                            <label for="answer<?php echo $ansrow['myfid1']; ?>-<?php echo $ld; ?>">
                                <span class="visible-xs-block label-text"><?php echo $labelans1[$k];?></span>
                            </label>
                        </td>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Separator for no-answer -->
                <?php if ($shownoanswer || $rightexists): ?>
                    <td class="answertextright dual_scale_separator information-item\">
                        <?php echo $answertextright; ?>
                    </td>
                <?php endif; ?>

                <!-- No answer column -->
                <?php if ($shownoanswer): ?>
                    <td class="dual_scale_no_answer answer-item radio-item noanswer-item text-center radio">
                        <?php if (count($labelans1) > 0): ?>
                                <input
                                    class='radio'
                                    type='radio'
                                    name='<?php echo $ansrow['myfname1']; ?>'
                                    value=''
                                    id='answer<?php echo $ansrow['myfid1']; ?>-'
                                    <?php echo $myfname1_notset; ?>
                                />
                            <label for='answer<?php echo $ansrow['myfid1']; ?>-'>
                                <span class="visible-xs-block label-text"><?php eT("No answer"); ?></span>
                            </label>
                        <?php else: ?>
                                <input
                                    class='radio'
                                    type='radio'
                                    name='<?php echo $ansrow['myfname0']; ?>'
                                    value=''
                                    id='answer<?php echo $ansrow['myfid0']; ?>-'
                                    <?php echo $myfname0_notset; ?>
                                />
                            <label for='answer<?php echo $ansrow['myfid0']; ?>-'>
                                <span class="visible-xs-block label-text"><?php eT("No answer"); ?></span>
                            </label>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>

                </tr>

            <?php endforeach; ?>

        </tbody>
    </table>
</div>
