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
        <col class="answertext" style='width: <?php echo $answerwidth; ?>%;' />

        <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
            <colgroup style='width: <?php echo $cellwidth; ?>%;' >
        <?php endif; ?>

            <?php if ($ddprefix != ''): ?>
                <col class="ddprefix" />
            <?php endif; ?>

            <col class="dsheader" <?php echo $headcolwidth; ?> />

            <?php if ($ddsuffix != ''): ?>
                <col class="ddsuffix" />
            <?php endif; ?>

            <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
                </colgroup>
            <?php endif; ?>

        <col class="ddarrayseparator" style='width: <?php echo $separatorwidth; ?>%'/>

        <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
            <colgroup style='width: <?php echo $cellwidth; ?>%;' >
        <?php endif; ?>

            <?php if ($ddprefix != ''): ?>
                <col class="ddprefix" />
            <?php endif; ?>

            <col class="dsheader" <?php echo $headcolwidth; ?> />

            <?php if ($ddsuffix != ''): ?>
                <col class="ddsuffix" />
            <?php endif; ?>

        <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
            </colgroup>
        <?php endif; ?>
        <!-- Header -->

        <thead>
            <tr>
                <th>&nbsp;</th>
                <th  class='th-14' <?php echo $colspan; ?>><?php echo $leftheader; ?></th>
                <th>&nbsp;</th>
                <th class='th-15' <?php echo $colspan; ?>><?php echo $rightheader; ?></th>
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
                            'class'=>"answers-list radio-list array{$ansrow['zebra']}".(($ansrow['mandatoryviolation']) ? " has-error error-mandatory" : "") ,
                            'role'=>"radiogroup",
                        ),
                        "",
                        false
                    );
                ?>
                    <!-- Answer text (actual question) -->

                    <th class="answertext control-label">
                        <label for="answer<?php echo $ansrow['myfid0']; ?>" id="answertext<?php echo $ansrow['myfname']; ?>">
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

                    <!-- Prefix -->

                    <?php if ($ddprefix != ''): ?>
                        <td class="ddprefix information-item">
                            <?php echo $ddprefix; ?>
                        </td>
                    <?php endif; ?>

                    <!-- First dropdown -->

                    <td class="answer-item dropdown-item">
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
                    </td>

                    <!-- Suffix -->

                    <?php if($ddsuffix != ''): ?>
                        <td class="ddsuffix information-item"><?php echo $ddsuffix; ?></td>
                    <?php endif; ?>

                    <!-- Separator -->

                    <td class="ddarrayseparator information-item"><?php echo $interddSep; ?></td>

                    <!-- Prefix -->

                    <?php if ($ddprefix != ''): ?>
                        <td class="ddprefix information-item text-right">
                            <?php echo $ddprefix; ?>
                        </td>
                    <?php endif; ?>


                    <!-- Second dropdown -->

                    <td class="answer-item dropdown-item">
                        <label class="visible-xs-block" for="answer<?php echo $ansrow['myfid1']; ?>">
                            <?php echo $ansrow['question']; ?>
                        </label>

                        <select class='form-control' name="<?php echo $ansrow['myfname1']; ?>" id="answer<?php echo $ansrow['myfid1']; ?>">
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
                    </td>

                    <!-- Suffix -->

                    <?php if($ddsuffix != ''): ?>
                        <td class="ddsuffix information-item"><?php echo $ddsuffix; ?></td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>

        </tbody>

    </table>
</div>
