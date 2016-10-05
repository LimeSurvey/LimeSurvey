<?php

/**
 * Dual-scale array with dropdown representation

 * @var $answerwidth
 * @var $cellwidth
 * @var $ddprefix
 * @var $ddsuffix
 */

?>

<div class="no-more-tables no-more-tables-array-dual-dropdown-layout">
    <table class="table table-in-qanda-10 question subquestion-list questions-list dropdown-list">

        <!-- Column groups -->
        <colgroup>
            <col class="answertext" style='width: <?php echo $answerwidth; ?>%;' />
            <col class="dsheader" style='width: <?php echo $cellwidth; ?>%;' />
            <col class="ddarrayseparator" style='width: <?php echo $separatorwidth; ?>%'/>
            <col class="dsheader"  style='width: <?php echo $cellwidth; ?>%;' />
        </colgroup>
        <!-- Header -->

        <thead>
            <tr>
                <td>&nbsp;</td>
                <th class='left-header'><?php echo $leftheader; ?></th>
                <td>&nbsp;</td>
                <th class='right-header'><?php echo $rightheader; ?></th>
            </tr>
        </thead>


        <tbody>

            <!-- Sub questions -->

            <?php foreach ($aSubQuestions as $ansrow): ?>
                <!-- <tr> -->
                <?php
                    echo CHtml::tag(
                        "tr",
                        array(
                            'id'=> "javatbd{$ansrow['myfname']}",
                            'class'=>"answers-list radio-list array{$ansrow['zebra']}".(($ansrow['mandatoryviolation']) ? " has-error" : "") ,
                            'role'=>"group",
                        ),
                        "",
                        false
                    );
                ?>
                    <!-- Answer text (actual question) -->

                    <th class="answertext control-label" id="answertext<?php echo $ansrow['myfname']; ?>">
                        <label class="control-label" for="answer<?php echo $ansrow['myfid0']; ?>" id="label-<?php echo $ansrow['myfname']; ?>">
                            <?php echo $ansrow['question']; ?>
                        </label>
                        <input
                            type="hidden"
                            disabled="disabled"
                            name="java<?php echo $ansrow['myfid0']; ?>"
                            id="java<?php echo $ansrow['myfid0']; ?>"
                            value="<?php echo $ansrow['sActualAnswer0']; ?>"
                        />
                        <input
                            type="hidden"
                            disabled="disabled"
                            name="java<?php echo $ansrow['myfid1']; ?>"
                            id="java<?php echo $ansrow['myfid1']; ?>"
                            value="<?php echo $ansrow['sActualAnswer1']; ?>"
                        />
                    </th>

                    <!-- First dropdown -->

                    <td class="answer-item dropdown-item">
                        <?php if ($ddsuffix != '' || $ddprefix != ''): ?>
                            <div class="input-group">
                        <?php endif; ?>
                        <?php if ($ddprefix != ''): ?>
                            <div class="ddprefix ls-input-group-extra">
                                <?php echo $ddprefix; ?>
                            </div>
                        <?php endif; ?>
                        <select
                            class='form-control'
                            name="<?php echo $ansrow['myfname0']; ?>"
                            id="answer<?php echo $ansrow['myfid0']; ?>"
                            aria-labelledby="answertext<?php echo $ansrow['myfname']; ?>"
                        >
                            <!-- Please choose... -->
                            <?php if ($ansrow['sActualAnswer0'] == ''): ?>
                                <option value="" <?php echo SELECTED; ?> >
                                    <?php eT('Please choose...'); ?>
                                </option>
                            <?php endif; ?>

                            <!-- First label set -->
                            <?php foreach ($labels0 as $lrow): ?>
                                <option
                                    value="<?php echo $lrow['code']; ?>"
                                    <?php if ($ansrow['sActualAnswer0'] == $lrow['code']): echo SELECTED; endif; ?>
                                >

                                    <?php echo flattenText($lrow['title']); ?>

                                </option>
                            <?php endforeach; ?>

                            <!-- No answer -->
                            <?php if ($ansrow['showNoAnswer0'] && $ansrow['sActualAnswer0'] != ''): ?>
                                <option value=""><?php eT('No answer'); ?></option>
                            <?php endif; ?>
                        </select>
                        <?php if ($ddsuffix != ''): ?>
                            <div class="ddsuffix ls-input-group-extra">
                                <?php echo $ddsuffix; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($ddsuffix != '' || $ddprefix != ''): ?>
                            </div>
                        <?php endif; ?>
                    </td>


                    <!-- Separator -->

                    <td class="ddarrayseparator information-item"><?php echo $interddSep; ?></td>

                    <!-- Second dropdown -->

                    <td class="answer-item dropdown-item">
                        <!-- We don't need another label : aria-labelledby for accessibility, and we have only 2 line in phone and no-more-table -->
                        <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
                            <div class="input-group">
                        <?php endif; ?>
                        <?php if ($ddprefix != ''): ?>
                            <div class="ddprefix ls-input-group-extra">
                                <?php echo $ddprefix; ?>
                            </div>
                        <?php endif; ?>
                        <select class='form-control' name="<?php echo $ansrow['myfname1']; ?>" id="answer<?php echo $ansrow['myfid1']; ?>" aria-labelledby="label-<?php echo $ansrow['myfname']; ?>"">
                            <!-- Please choose... -->
                            <?php if ($ansrow['sActualAnswer1'] == ''): ?>
                                <option value="" <?php echo SELECTED; ?> ><?php eT('Please choose...'); ?></option>
                            <?php endif; ?>

                            <!-- Second label set -->
                            <?php foreach ($labels1 as $lrow): ?>
                                <option
                                    value="<?php echo $lrow['code']; ?>"
                                    <?php if ($ansrow['sActualAnswer1'] == $lrow['code']): echo SELECTED; endif; ?>
                                >
                                    <?php echo flattenText($lrow['title']); ?>

                                </option>
                            <?php endforeach; ?>

                            <!-- No answer -->
                            <?php if ($ansrow['showNoAnswer1'] && $ansrow['sActualAnswer1'] != ''): ?>
                                <option value=""><?php eT('No answer'); ?></option>
                            <?php endif; ?>

                        </select>
                        <?php if ($ddsuffix != ''): ?>
                            <div class="ddsuffix ls-input-group-extra">
                                <?php echo $ddsuffix; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>

    </table>
</div>
