<?php

/**
 * Array dual-scale, no dropdown

 * @var $answerwidth
 */

?>

<table class="<?php echo $coreClass; ?> table table-bordered table-hover" role="group" aria-labelledby="ls-question-text-<?php echo $basename ?>">
    <col class="col-answers" style='width: <?php echo $answerwidth; ?>%;' >
    <?php if ($leftheader != '' || $rightheader !=''): ?>
        <col style='width: 0%;'  class="hidden"> <!-- see https://bugs.limesurvey.org/view.php?id=11863 -->
    <?php endif; ?>
    <colgroup class="col-responses group-1">
        <?php foreach ($labelans0 as $ld): ?>
            <col style='width: <?php echo $cellwidth; ?>%;' >
        <?php endforeach; ?>
    </colgroup>
    <?php if (count($labelans1) > 1): ?>
        <col class="separator" style="width: <?php echo $separatorwidth; ?>">
        <?php if ($leftheader != '' || $rightheader !=''): ?>
            <col style='width: 0%;'  class="hidden"> <!-- see https://bugs.limesurvey.org/view.php?id=11863 -->
        <?php endif; ?>
        <colgroup class="col-responses group-2">
            <?php foreach ($labelans1 as $ld): ?>
                <col style='width: <?php echo $cellwidth; ?>%;' >
            <?php endforeach; ?>
        </colgroup>
    <?php endif; ?>
    <?php if ($shownoanswer || $rightexists): ?>
        <col class="separator right_separator" style="width: <?php echo $rightwidth; ?>%">
    <?php endif; ?>
    <?php if ($shownoanswer): ?>
        <col class="col-no-answer"  style="width: <?php echo $cellwidth; ?>%;" />
    <?php endif; ?>

    <thead>
        <?php if ($leftheader != '' || $rightheader !=''): ?>
            <tr class="ls-heading groups header_row">
                <th class="header_answer_text"></th>
                <?php if ($leftheader != '' || $rightheader !=''): ?>
                    <td class="hidden"></td>
                <?php endif; ?>
                <th colspan="<?php echo count($labelans0); ?>" class="dsheader"><?php echo $leftheader; ?></th>
                <?php if (count($labelans1) > 0): ?>
                    <td class="header_separator"></td>  <!-- // Separator -->
                    <?php if ($leftheader != '' || $rightheader !=''): ?>
                        <td class="hidden"></td>
                    <?php endif; ?>
                    <th colspan="<?php echo count($labelans1); ?>" class="dsheader"><?php echo $rightheader; ?></th>
                <?php endif; ?>

                <?php if($shownoanswer || $rightexists): ?>
                    <td class="header_separator <?php echo $rightclass; ?>"></td>
                    <?php if($shownoanswer): ?>
                        <th class="header_no_answer"></th>
                    <?php endif; ?>
                <?php endif; ?>
            </tr>
        <?php endif; ?>

        <!-- Render header -->
        <?php echo Yii::app()->getController()->renderPartial(
                '/survey/questions/answer/arrays/dualscale/answer_header',
                array(
                    'labelans0'    => $labelans0,
                    'labelans1'    => $labelans1,
                    'shownoanswer' => $shownoanswer,
                    'rightexists'  => $rightexists,
                    'leftheader'   => $leftheader,
                    'rightheader'  => $rightheader,
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
                            '/survey/questions/answer/arrays/dualscale/answer_header',
                            array(
                                'labelans0'    => $labelans0,
                                'labelans1'    => $labelans1,
                                'shownoanswer' => $shownoanswer,
                                'rightexists'  => $rightexists,
                                'leftheader'   => $leftheader,
                                'rightheader'  => $rightheader,
                                'class'        => 'hidden-xs repeat headings'
                            ),
                            true
                        );
                    ?>
            <?php endif; ?>

            <!-- tr -->
            <tr id="javatbd<?php echo $ansrow['myfname']; ?>" role="group" aria-labelledby="answertext<?php echo $ansrow['myfname']; ?>"
                class="answers-list radio-list <?php echo ($ansrow['odd']) ? "ls-odd" : "ls-even"; ?><?php echo ($ansrow['showmandatoryviolation']) ? " ls-error-mandatory has-error" : ""; ?>"
            >
            <th id="answertext<?php echo $ansrow['myfname']; ?>" class="answertext control-label<?php echo ($answerwidth==0)? " sr-only":""; ?>">
                <?php echo $ansrow['answertext']; ?>
                <?php
                /* Value for expression manager javascript (use id) ; no need to submit */
                echo \CHtml::hiddenField("java{$ansrow['myfid0']}",$ansrow['sessionfname0'],array(
                    'id' => "java{$ansrow['myfid0']}",
                    'disabled' => true,
                ));
                ?>
                <?php if (count($labelans1) > 0):
                    echo \CHtml::hiddenField("java{$ansrow['myfid1']}",$ansrow['sessionfname1'],array(
                        'id' => "java{$ansrow['myfid1']}",
                        'disabled' => true,
                    ));
                endif; ?>

            </th>

            <!-- First label set -->
            <?php foreach ($labelcode0 as $j => $ld): ?>
                <?php if ($j === 0 && ($leftheader != '' || $rightheader !='')): ?>
                    <td class='visible-xs leftheader information-item'><?php echo $leftheader; ?></td>
                <?php endif; ?>
                <td class="answer_cell_1_<?php echo $ld; ?> answer-item radio-item">
                    <input
                        type="radio"
                        name="<?php echo $ansrow['myfname0']; ?>"
                        value="<?php echo $ld; ?>"
                        id="answer<?php echo $ansrow['myfid0']; ?>-<?php echo $ld; ?>"
                        <?php echo $labelcode0_checked[$ansrow['title']][$ld]; ?>
                    />
                    <label for="answer<?php echo $ansrow['myfid0']; ?>-<?php echo $ld; ?>" class="ls-label-xs-visibility">
                        <?php echo $labelans0[$j];?>
                    </label>
                </td>
            <?php endforeach; ?>

            <?php if (count($labelans1) > 0):  // if second label set is used ?>
                <td class="dual_scale_separator information-item <?php if($shownoanswer): ?>answer_cell_1_ radio-item noanswer-item <?php endif; ?><?php echo ($separatorwidth==0)? " sr-only":""; ?>">
                    <?php if ($shownoanswer): // No answer for accessibility and no javascript (but visible-xs-block visible-xs-block even with no js: need reworking) ?>
                    <div class="ls-js-hidden">
                        <input
                            type='radio'
                            name='<?php echo $ansrow['myfname0']; ?>'
                            value=''
                            id='answer<?php echo $ansrow['myfid0']; ?>-'
                            <?php echo $myfname0_notset; ?>
                        />
                        <label for='answer<?php echo $ansrow['myfid0']; ?>-' class='ls-label-xs-visibility'>
                            <?php eT("No answer"); ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    <?php echo $ansrow['answertextcenter']; ?>
                </td>

                <!-- Second label set -->
                <?php foreach ($labelcode1 as $k => $ld): ?>
                    <?php if ($k === 0 && ($leftheader != '' || $rightheader !='')): ?>
                        <td class='visible-xs rightheader information-item'><?php echo $rightheader; ?></td>
                    <?php endif; ?>
                    <td class="answer_cell_2_<?php echo $ld; ?> answer-item radio-item">
                        <input
                            type="radio"
                            name="<?php echo $ansrow['myfname1']; ?>"
                            value="<?php echo $ld; ?>"
                            id="answer<?php echo $ansrow['myfid1']; ?>-<?php echo $ld; ?>"
                            <?php echo $labelcode1_checked[$ansrow['title']][$ld]; ?>
                        />
                        <label for="answer<?php echo $ansrow['myfid1']; ?>-<?php echo $ld; ?>" class="ls-label-xs-visibility">
                            <?php echo $labelans1[$k];?>
                        </label>
                    </td>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Separator for no-answer -->
            <?php if ($shownoanswer || $rightexists): ?>
                <td class="answertextright dual_scale_separator information-item"><?php echo $ansrow['answertextright']; ?></td>
            <?php endif; ?>

            <!-- No answer column -->
            <?php if ($shownoanswer): ?>
                <td class="answer_cell_2_ answer-item radio-item noanswer-item">
                    <?php if (count($labelans1) > 0): ?>
                            <input
                                type='radio'
                                name='<?php echo $ansrow['myfname1']; ?>'
                                value=''
                                id='answer<?php echo $ansrow['myfid1']; ?>-'
                                <?php echo $myfname1_notset; ?>
                            />
                        <label for='answer<?php echo $ansrow['myfid1']; ?>-' class="ls-label-xs-visibility">
                            <?php eT("No answer"); ?>
                        </label>
                    <?php else: ?>
                            <input
                                type='radio'
                                name='<?php echo $ansrow['myfname0']; ?>'
                                value=''
                                id='answer<?php echo $ansrow['myfid0']; ?>-'
                                <?php echo $myfname0_notset; ?>
                            />
                        <label for='answer<?php echo $ansrow['myfid0']; ?>-' class="ls-label-xs-visibility">
                            <?php eT("No answer"); ?>
                        </label>
                    <?php endif; ?>
                </td>
            <?php endif; ?>

            </tr>

        <?php endforeach; ?>

    </tbody>
</table>
